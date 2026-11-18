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

- `/facturas`: listado de facturas del propietario autenticado.
- `/facturas/nueva`: alta de factura; se registra inicialmente como `Pendiente`.
- `/facturas/{id}`: detalle y registro de pago total.
- `/facturas/informe`: totales y tablas de facturas pendientes y pagadas.

El pago registra fecha, monto total y medio de pago; no admite pagos parciales ni adjuntos digitales en este alcance. La migración `AddInvoiceServicePeriodUniqueKey` evita duplicar una factura por servicio asociado y período.

Al registrar o modificar un inmueble, su superficie debe ser de al menos
50 m². Esta validación no modifica registros existentes.

### Estado operativo de un vínculo

Cada vínculo entre inmueble y servicio tiene un estado operativo propio:
`Activo`, `Pausado` o `En Revisión`. La baja lógica se guarda por separado
en `estado`, de modo que dar de baja un vínculo no borra su último estado
operativo ni sus facturas. Solo un vínculo operativo activo y vigente, cuyo
inmueble no esté inactivo, permite registrar facturas nuevas. Las facturas
ya registradas permanecen consultables.

Se permiten cambios manuales entre los tres estados, pero no se puede activar
un vínculo cuando su inmueble está inactivo. Si un inmueble se da de baja,
el estado operativo de sus vínculos no cambia automáticamente.

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

Estas equivalencias reflejan el trigger `bi_servicios_unidad_medida` presente
en la base local. Ese trigger y la restricción de categorías no están incluidos
en las migraciones actuales: si se modifican, también deben actualizarse las
equivalencias de la aplicación para evitar diferencias entre lo mostrado y lo
guardado.
