---
name: backend-codeigniter-serviinmueble
description: "Implementar o revisar controladores, modelos, rutas, validaciones y lógica de negocio PHP/CodeIgniter para ServiInmueble, incluidos ABMC, autenticación y facturas."
---

# Backend CodeIgniter

## Antes de empezar

Aplicar `serviinmueble-contexto`, y las skills de requisitos, datos o seguridad cuando correspondan. Revisar versión de CodeIgniter, rutas, filtros, controladores, modelos, migraciones y estilo existentes antes de diseñar una solución.

## Procedimiento

1. Identificar HU/US, actor, release, contrato de entrada/salida, permisos y criterios de aceptación.
2. Validar entradas en servidor con las herramientas del proyecto; validar relaciones y estado de registros referenciados.
3. Aplicar autorización en cada ruta/controlador. Comprobar propiedad del recurso consultando con el usuario autenticado; no aceptar `usuario_id` ni rol como autoridad desde el cliente.
4. Usar Query Builder/consultas parametrizadas y transacciones donde una operación de varios pasos necesite atomicidad.
5. Implementar baja lógica para entidades de dominio; conservar historial y restringir referencias según el esquema. No usar borrado físico como atajo.
6. Manejar fallos explícitamente, con respuestas/mensajes acordes al patrón del proyecto; no ocultar excepciones ni devolver éxitos ficticios.
7. Respetar CSRF, sesiones, codificación/escape de salida y manejo de errores del framework.
8. Probar camino feliz, entradas inválidas, duplicados, sesión ausente, rol incorrecto, acceso a recursos ajenos y estados inactivos.

## Login, aprobación y home por rol

- En el alta pública, asignar `Usuario_Nuevo` exclusivamente desde el servidor. No aceptar rol ni estado de aprobación del cliente.
- Permitir que la cuenta pendiente inicie sesión solo para ver su estado; proteger cada ruta funcional del lado servidor y no confiar en ocultar enlaces.
- Al autenticar, regenerar el ID de sesión. Al cerrar sesión, destruirla. No usar el rol almacenado en sesión como fuente de autoridad: actualizar la identidad/rol desde la base en rutas protegidas para que una aprobación se refleje inmediatamente.
- La aprobación debe requerir un administrador autenticado, cambiar únicamente una solicitud aún pendiente y ser atómica. Según la regla confirmada del proyecto, la aprobación asigna `Propietario`.
- Mantener una única implementación compartida para obtener identidad/rol y aplicar filtros de autenticación/rol; no duplicar controles distintos en cada vista.

## Reglas del dominio a confirmar

No materializar como definitivas las propuestas aún abiertas: unicidad de factura por servicio/período (OQ-05), fecha de mora (OQ-07), estado pagada sin módulo de pagos en R1 (OQ-10/OQ-15), campos CPE/Pagar (OQ-13) y alcance de auditoría (OQ-14). Si existe autorización para avanzar con un supuesto, mantenerlo centralizado y documentado.

## Entrega

Relacionar el cambio con HU/US, RN/RF y CP disponibles; señalar migraciones requeridas, validaciones ejecutadas y decisiones pendientes. No modificar estructura de datos sin coordinar con la skill de datos.
