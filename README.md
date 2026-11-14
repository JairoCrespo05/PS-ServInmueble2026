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

Para revertir la estructura:

```sh
php spark migrate:rollback
```

La migración crea las tablas que todavía no existan:

- `direcciones` e `inmuebles`, con una relación uno a uno.
- `servicios` y `inmueble_servicios`, que registran los servicios contratados por cada inmueble y sus datos de cuenta/medidor.
- `facturas`, vinculadas al registro de servicio del inmueble y con sus datos de vencimiento y pago.

Los modelos CRUD están disponibles en `App\Models`: `DireccionModel`, `InmuebleModel`, `ServicioModel`, `InmuebleServicioModel` y `FacturaModel`.
