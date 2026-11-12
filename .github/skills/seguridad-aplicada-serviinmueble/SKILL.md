---
name: seguridad-aplicada-serviinmueble
description: "Aplicar controles de seguridad en autenticación, sesiones, autorización por rol, propiedad de datos, validación, SQL, XSS y CSRF en ServiInmueble."
---

# Seguridad aplicada

## Cuándo usarla

Al tocar login/registro, roles, rutas privadas, consultas o mutaciones de datos, manejo de sesión, formularios, archivos o datos personales. Aplicar `serviinmueble-contexto` y revisar la versión/configuración real de CodeIgniter.

## Controles obligatorios

- Asignar rol desde el servidor; nunca aceptar un rol privilegiado enviado por el registro.
- Validar autenticación y rol en servidor en cada ruta protegida; aplicar denegación por defecto.
- Para cada lectura o mutación de propietario, comprobar la propiedad del recurso dentro de la consulta/servicio y no solo en la interfaz. Probar IDs ajenos (IDOR).
- Almacenar contraseñas con hash seguro (`password_hash`/equivalente soportado por el proyecto) y verificarlas con el mecanismo correspondiente; no registrar credenciales.
- Usar consultas parametrizadas/Query Builder; validar tipo, formato, rango y relaciones en servidor.
- Escapar la salida HTML según contexto; no concatenar entrada no confiable en HTML o JavaScript.
- Mantener CSRF habilitado en formularios y peticiones que mutan estado según el framework.
- Regenerar el identificador de sesión tras autenticar y destruir la sesión al cerrar; usar mensajes genéricos para credenciales inválidas.
- En el registro, asignar `Usuario_Nuevo` desde servidor; no permitir elevar rol ni saltarse la aprobación manipulando campos o rutas.
- Para cuentas pendientes, permitir solo la pantalla de estado y cierre de sesión. Cada operación funcional requiere autorización de servidor y el rol debe leerse/actualizarse desde la fuente persistente para evitar permisos obsoletos en la sesión.
- Restringir la aprobación a administradores; cambiar en una transacción solo usuarios que sigan pendientes y asignar `Propietario` conforme a la decisión vigente del proyecto.
- No exponer secretos ni datos sensibles en URLs, logs o respuestas innecesarias.
- Para carga de archivos futura/R2: validar MIME real, extensión y tamaño, usar nombre aleatorio y almacenamiento no ejecutable/no público con autorización de descarga.

## Verificación

Probar ruta privada sin sesión, ruta de administrador como propietario, rol `admin` forjado en el alta, acceso operativo como usuario pendiente, aprobación repetida o por rol no administrador, rol actualizado en una sesión ya abierta, ID de inmueble ajeno, entrada inválida y solicitudes sin token CSRF. Usar cuentas y datos ficticios.

No declarar seguridad completa por ocultar botones o por una revisión estática aislada. Informar controles no verificados y su impacto. Si se solicita explícitamente una auditoría de vulnerabilidades, seguir el proceso de revisión de seguridad disponible; esta skill guía la implementación segura, no autoriza pruebas contra sistemas ajenos.
