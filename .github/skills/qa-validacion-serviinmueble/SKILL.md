---
name: qa-validacion-serviinmueble
description: "Derivar y ejecutar pruebas funcionales, regresión, aceptación, permisos, validaciones y flujos end-to-end para ServiInmueble."
---

# QA y validación

## Antes de empezar

Aplicar `serviinmueble-contexto`; leer criterios de aceptación y los tests existentes. No convertir supuestos ni resultados deseados en requisitos confirmados.

## Procedimiento

1. Trazar cada criterio a uno o más casos `CP-xx` con precondición, datos ficticios, pasos y resultado esperado.
2. Cubrir positivos, negativos, límites, obligatoriedad, duplicados, estado vacío, registros inactivos, permisos y propiedad de datos según aplique.
3. Para ABMC, verificar alta/consulta/modificación/baja lógica e historial.
4. Para formularios, verificar validación cliente y servidor; probar servidor con datos que omitan o falseen la validación de interfaz.
5. Para informes, comparar filas, filtros y totales con cálculos independientes; comprobar aislamiento por usuario.
6. Ejecutar primero pruebas focalizadas del cambio y después regresión del flujo afectado. Usar los comandos disponibles en el proyecto; no inventar comandos ni afirmar ejecución no realizada.
7. Verificar el flujo R1, si está dentro del alcance: acceso → catálogo → inmueble → servicio asociado → factura → informe.
8. Registrar resultado real, estado, defecto, evidencia y HU/US relacionado. Distinguir bloqueante, no bloqueante y no probado.

## Sesiones y aprobación de roles

Validar el ciclo completo con cuentas ficticias:

- Registro sin rol y con intento de enviar un rol privilegiado: siempre queda `Usuario_Nuevo`.
- Login correcto e incorrecto con mensajes no enumerables; regeneración de sesión al autenticar y destrucción al cerrar sesión.
- Usuario pendiente ve el aviso de espera; rutas/acciones administrativas y operativas rechazan su acceso.
- Administrador existente consulta solicitudes, aprueba una solicitud pendiente como `Propietario`; un segundo intento y cualquier intento de un rol no administrador son rechazados.
- Una sesión que estaba pendiente refleja el nuevo rol al siguiente request; el home y sus opciones corresponden al rol vigente.
- CSRF ausente/inválido en alta, login, aprobación y cierre de sesión.

## Casos de seguridad funcional

Incluir acceso privado sin sesión, rol incorrecto, intento de elevar rol en registro e intento de acceder a inmueble/datos de otro propietario. Probar con datos de prueba no sensibles.

## Salida

Matriz breve de criterios ↔ CP, comandos ejecutados, resultado, defectos y cobertura pendiente. Una historia no se declara Done si falta validar criterios, permisos, scripts de base de datos o regresión que aplique.
