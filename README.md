# PS-ServInmueble2026

Proyecto final de la materia Proyecto de Software: gestión de inmuebles, servicios y facturas.

## Base de datos

El proyecto utiliza MySQL/MariaDB por defecto. Para configurar una instalación local:

1. Use la base de datos `bd_serviinmueble` (o el nombre que configure en `.env`).
2. Copie `.env.example` como `.env` y ajuste usuario, contraseña, host y puerto.
3. Ejecute las migraciones:

   ```sh
   php spark migrate
   ```
4. En el entorno local de desarrollo, cargue el administrador de prueba:

   ```sh
   php spark db:seed DevelopmentAdminSeeder
   ```

   Credenciales locales: `admin.pruebas@example.com` / `Passw0rd!`. La contraseña
   se guarda con hash. El seeder no se ejecuta fuera de `development`, no cambia
   una cuenta existente y falla si ese correo ya está asociado a otro rol.

Para cargar un escenario completo de presentación en desarrollo, con el catálogo,
dos propietarios, una solicitud pendiente, inmuebles, servicios asociados e
historial de facturas, ejecute:

```sh
php spark db:seed DemoDataSeeder
```

El seeder también crea el administrador de prueba. Las cuentas ficticias de
propietario usan `lucia.demo@example.test` y `mateo.demo@example.test`; la cuenta
pendiente es `emilia.pendiente@example.test`. Las tres usan `Demo2026!`. La clave
del administrador sigue siendo `Passw0rd!`. Son credenciales públicas de
demostración: no las reutilice fuera de una instalación local aislada.

La carga es idempotente: volver a ejecutarla no duplica el escenario. Si una
cuenta o registro reservado para la demo ya existe con datos diferentes, el
seeder se detiene sin sobrescribirlo. La ejecución está limitada a
`development`.

Para revertir la estructura cuando las migraciones involucradas lo permitan:

```sh
php spark migrate:rollback
```

La migración `AddEstadoVinculacion` no admite reversión automática: el campo
puede contener datos anteriores a la migración o cambios posteriores. Para
volver a una versión anterior, planifique la restauración desde una copia de
seguridad y la compatibilidad de los datos antes de modificar la base.

La migración crea las tablas que todavía no existan:

- `direcciones` e `inmuebles`, con una relación uno a uno.
- `usuarios` e `inmuebles`, con una relación uno a muchos mediante `inmuebles.usuario_id`; el vínculo puede quedar vacío mientras un inmueble no tenga propietario asignado.
- `servicios` y `inmueble_servicios`, que registran los servicios contratados por cada inmueble y sus datos de cuenta/medidor.
- `facturas`, vinculadas al registro de servicio del inmueble y con sus datos de vencimiento y pago.

Los modelos CRUD están disponibles en `App\Models`: `DireccionModel`, `InmuebleModel`, `ServicioModel`, `InmuebleServicioModel` y `FacturaModel`.

El módulo de facturas permite consultar el listado y el detalle, cargar nuevas facturas y ver el informe de pendientes y pagadas:

- `/facturas`: listado de facturas del propietario autenticado, con búsqueda por inmueble, servicio o proveedor y filtro por estado (`Pendiente`/`Pagada`). Los filtros se aplican antes de paginar.
- `/facturas/nueva`: alta de factura; se registra inicialmente como `Pendiente`.
- `/facturas/{id}`: detalle y registro de pago total.
- `/facturas/informe`: totales globales y tablas de facturas pendientes y pagadas, con búsqueda por inmueble, servicio o proveedor. La búsqueda filtra las filas, no los totales.

El pago registra fecha, monto total y medio de pago; no admite pagos parciales ni adjuntos digitales en este alcance. La migración `AddInvoiceServicePeriodUniqueKey` evita duplicar una factura por servicio asociado y período.

Al registrar o modificar un inmueble, su superficie debe ser de al menos
50 m². Esta validación no modifica registros existentes.

### Estado de los inmuebles

Los estados seleccionables son `Disponible`, `Ocupado` y `En Mantenimiento`;
al registrar uno nuevo se propone `Disponible`. La columna `estado_actividad`
separa la vigencia (`Activo` o `Inactivo`) de esa situación. La baja lógica
cambia únicamente la actividad y conserva la situación y el historial. Para
reactivar un inmueble dado de baja, al modificarlo se debe elegir o conservar
una situación válida y confirmar la reactivación. Sus vínculos vigentes y
activos pueden volver a admitir facturas nuevas.

La migración `ConvertirEstadoActivoInmuebles` convierte los inmuebles con
estado anterior `Activo` a `Disponible` y traslada las bajas a
`estado_actividad = Inactivo`. Como su situación anterior se perdió, se guarda
`Desconocido` solamente para esas bajas históricas; al reactivarlas es
obligatorio elegir una de las tres situaciones. Si encuentra valores no
reconocidos o incompatibles, se detiene para su revisión. No
admite reversión automática, porque no puede distinguir los inmuebles que ya
eran disponibles; antes de aplicarla, revise los registros y prepare una copia
de seguridad. Después de convertirlos, el propietario debe corregir los que
en realidad estén ocupados o en mantenimiento.
Antes de ejecutar `php spark migrate`, revise las migraciones pendientes: el
comando aplica todas las pendientes, no solo esta conversión.
En MySQL/MariaDB, la nueva columna es obligatoria y tiene `Activo` por defecto.
En SQLite queda nullable para evitar una reconstrucción de tabla que puede
dañar las claves foráneas; la aplicación siempre la establece al crear.

### Estado operativo de un vínculo

Cada vínculo entre inmueble y servicio tiene un estado operativo propio:
`Activo`, `Pausado` o `En Revisión`. La baja lógica se guarda por separado
en `estado`, de modo que dar de baja un vínculo no borra su último estado
operativo ni sus facturas. Solo un vínculo operativo activo y vigente, cuyo
inmueble no esté inactivo, permite registrar facturas nuevas. Las facturas
ya registradas permanecen consultables.

Se permiten cambios manuales entre los tres estados, pero no se puede activar
un vínculo cuando su inmueble está inactivo. Si un inmueble se da de baja,
sus vínculos vigentes pasan a `estado = Inactivo` con
`motivo_suspension = BajaInmueble`; el estado operativo no cambia. Al reactivar
el inmueble, solo se reactivan los vínculos dados de baja por ese motivo. Una
baja manual del vínculo usa `estado = Inactivo` sin motivo de inmueble y no se
revierte al reactivar la propiedad.

La migración reutiliza `estado_vinculacion` si ya existe y conserva sus
valores; cuando está vacío, convierte los vínculos anteriores `Activo` a
`Activo` y `Suspendido` a `Pausado`. Para las bajas históricas sin estado
operativo registrado lo deja desconocido. Si detecta estados no reconocidos,
detiene la migración para revisión.

### Alta de tipos de servicio

En el catálogo del administrador, la categoría seleccionada determina la unidad:
Agua y Gas usan `m3`, Electricidad usa `kWh`, e Internet, Telefonía,
Televisión y Expensas usan `Mes`. El servidor calcula la unidad al registrar
el servicio, sin confiar en el valor enviado por el navegador. La edición de
servicios existentes conserva su comportamiento anterior.
`ServiciosSeeder` usa las mismas unidades y corrige sus filas precargadas de
Internet de la unidad anterior `Mbps` a `Mes`.

Estas equivalencias reflejan el trigger `bi_servicios_unidad_medida` presente
en la base local. Ese trigger y la restricción de categorías no están incluidos
en las migraciones actuales: si se modifican, también deben actualizarse las
equivalencias de la aplicación para evitar diferencias entre lo mostrado y lo
guardado.
