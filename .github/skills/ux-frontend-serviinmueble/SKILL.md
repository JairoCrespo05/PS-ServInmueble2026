---
name: ux-frontend-serviinmueble
description: "Diseñar o implementar UX, wireframes, vistas, formularios, tablas, navegación, HTML, CSS y JavaScript del frontend de ServiInmueble."
---

# UX y frontend

## Antes de empezar

Aplicar `serviinmueble-contexto` y `analisis-requerimientos` cuando el comportamiento, actor o criterios de aceptación no estén claros. Leer la vista, estilos, scripts y rutas existentes antes de editar.

## Procedimiento

1. Confirmar historia, rol, release, criterios de aceptación, permisos, estados y datos mostrados.
2. Mantener consistencia con el diseño existente; identidad propuesta: azul profundo `#102A43`, azul `#245B86`, celeste `#75A9CC`, fondo `#F1F5F9`, superficies blancas. Verde/amarillo/rojo se reservan para estados.
3. Diseñar pantallas independientes para módulos extensos. Usar modales solo para confirmaciones o acciones rápidas.
4. Implementar HTML semántico y accesible: etiquetas visibles, `label` asociado, foco, contraste, mensajes junto a campos y nombres apropiados para inputs.
5. Validar en cliente para orientar al usuario; mantener la validación del servidor como autoridad. No confiar en campos ocultos, parámetros ni botones deshabilitados para autorizar acciones.
6. Cubrir estados de carga, vacío con acción útil, éxito, error de validación, error de permisos y filtro sin resultados.
7. Presentar estados de factura con texto además de color. No inventar reglas de vencimiento/pago; respetar los estados que definan los requisitos.
8. Probar responsive y navegación por teclado cuando el entorno lo permita; verificar regresión de los flujos existentes.

## Login, aprobación y home por rol

- Mostrar a `Usuario_Nuevo` una pantalla de estado clara que indique que la solicitud espera aprobación administrativa, qué rol recibirá al aprobarse (`Propietario`) y que las funciones permanecen bloqueadas hasta entonces.
- Adaptar la navegación y el contenido del home al rol vigente: no mostrar funciones administrativas a Propietario ni funciones operativas a cuentas pendientes. La vista acompaña, pero no sustituye, la autorización del servidor.
- Dar al administrador una lista de solicitudes pendientes con acción explícita para aprobar; mostrar estados vacío, éxito y error, y describir el rol que se asignará antes de la acción.
- Mantener formularios de sesión/registro/aprobación compatibles con CSRF y accesibles por teclado.

## Criterios

- Evitar scroll infinito; preferir listados paginados según el comportamiento aprobado.
- Mostrar errores del servidor de forma clara y conservar valores/contexto cuando sea seguro.
- Escapar datos en vistas mediante los mecanismos del proyecto; nunca insertar contenido no confiable como HTML.
- No introducir una biblioteca, sistema de diseño o dependencia sin necesidad y compatibilidad comprobadas.

## Entrega

Indicar HU/US y criterios cubiertos, pantallas/archivos modificados, estados probados, limitaciones y cualquier `[SUPUESTO]` u OQ. No declarar completa una validación visual que no se haya ejecutado.
