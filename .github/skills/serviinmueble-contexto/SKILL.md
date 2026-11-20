---
name: serviinmueble-contexto
description: "Contexto y reglas del proyecto ServiInmueble 2026. Usar antes de analizar, diseñar o implementar requisitos, historias, datos, UX, backend, pruebas o alcance."
---

# Contexto de ServiInmueble 2026

## Cuándo usarla

Siempre que una tarea pertenezca a este proyecto. Es la base común para las demás skills.

## Dominio y actores

Plataforma web para centralizar inmuebles y sus servicios asociados (electricidad, agua, gas, internet, tasas y expensas), facturas, consumos, vencimientos, pagos e informes.

- **Administrador:** gestiona usuarios y catálogo de tipos de servicio; consulta información general y, si está validado, auditoría y métricas globales.
- **Propietario/Gestor:** gestiona sus inmuebles y servicios asociados; registra y consulta facturas y accede a informes propios.
- **Visitante:** puede registrarse e iniciar sesión.

### Aprobación de cuentas y acceso

- El registro público siempre asigna `Usuario_Nuevo`; nunca acepta un rol enviado por el navegador ni permite crear administradores.
- Una cuenta `Usuario_Nuevo` puede autenticarse, pero solo ve el estado de su solicitud y la opción de cerrar sesión. No accede a las funciones de otros roles.
- Un administrador existente aprueba la solicitud y el usuario pasa a `Propietario`. La aprobación se ejecuta en el servidor y solo es válida mientras la solicitud siga pendiente.
- Las funciones y el inicio de sesión reflejan el rol vigente en la base de datos, no un rol guardado como autoridad en la sesión. La navegación del home se adapta al rol, y cada ruta protegida aplica autorización de servidor.
- Tras autenticarse se regenera el identificador de sesión; al cerrar sesión se destruye la sesión. Los formularios que cambian estado usan protección CSRF.

Vocabulario: Usuario, Rol, Inmueble, TipoServicio (catálogo), ServicioAsociado (vínculo de un inmueble con un tipo), Factura, Pago y Comprobante.

## Alcance y reglas de producto

- El plan compartido define **R1 — aprobación de cursada**: acceso, ABMC de usuarios/tipos/inmuebles, servicios asociados, alta y consulta de facturas e informes tabulares. **R2 — aprobación final**: comprobantes, pagos, mora/avisos, dashboard y analítica avanzada.
- Mantener primero el flujo núcleo: tipo de servicio → inmueble → servicio asociado → factura → informe de pendientes/pagadas.
- No adelantar landing pública, dashboard, gráficos ni funciones R2 antes de cerrar y probar R1.
- Baja lógica para usuarios, tipos de servicio, inmuebles y servicios asociados; conservar historial. Confirmar antes de bajas o acciones difíciles de revertir.
- Validar en cliente y servidor. Ocultar una opción en la interfaz nunca reemplaza autorización del servidor.
- No permitir elegir el rol Administrador durante el registro público.
- Usar páginas propias para módulos/formularios extensos y modales para confirmaciones o acciones rápidas.

## Stack y convenciones

Stack indicado: HTML5, JavaScript, PHP, CodeIgniter, MySQL; CSS3. Bootstrap, jQuery, Git/GitHub, Trello y Figma/Stitch están permitidos. Revisar primero la versión, estructura y prácticas efectivamente existentes en el repositorio; no asumir que la arquitectura propuesta ya está implementada.

Preferir nombres de tablas y columnas en español sin tildes y `snake_case` si el proyecto no establece otra convención. Mantener compatibilidad con el código existente.

## Trazabilidad y decisiones

- Todo cambio funcional debe apuntar a una historia y a requisitos/casos de prueba cuando existan.
- Los IDs son referencias, no equivalencias: preservar el prefijo y número de la fuente usada (`HU`, `US`, `RF`, `RNF`, `RN`, `OQ`, `CP`). No renumerar ni fusionar.
- Marcar lo no confirmado como `[SUPUESTO]` o `[A VALIDAR]`; formular una OQ concreta, indicar impacto y recomendación sin presentarla como requisito aprobado.
- Las OQ 01–17 del material compartido son pendientes, no decisiones aprobadas. Considerar especialmente OQ-03 (propiedad de inmuebles), OQ-05 (unicidad de factura por período), OQ-07 (vencimiento que determina mora), OQ-10/OQ-15 (pagos y estado pagada en R1), OQ-13 (CPE/Pagar), OQ-14 (auditoría) y OQ-17 (fecha de entrega).
- Al responderse una OQ, reflejar la decisión en las historias, modelo, implementación y pruebas que afecte.

## Inconsistencia de fuentes — no resolver en silencio

El material de contexto aportado describe `US-01` a `US-15` y releases, pero el índice existente [`../../../.docs/HU/README.md`](../../../.docs/HU/README.md) enumera `HU-01` a `HU-22`, con otra distribución de historias y prioridades. No asumir correspondencias entre IDs ni elegir una lista como reemplazo de la otra. Antes de planificar o citar una historia, identificar la fuente del ID; si la equivalencia o el alcance cambia el trabajo, registrar una OQ y pedir confirmación. El material de contexto no reemplaza los documentos vigentes del repositorio.

## Procedimiento

1. Identificar actor, problema, módulo, historia fuente y release.
2. Leer el documento de historia/requisito existente y el código/patrones relevantes.
3. Revisar OQ y dependencias; separar hechos, decisiones y supuestos.
4. Elegir las skills de rol necesarias y producir una salida verificable y trazable.
5. Si falta una regla que afecta comportamiento o datos, no codificar una interpretación como definitiva: plantear la OQ y, si se autoriza avanzar, aislar el supuesto para cambiarlo con facilidad.

## Explicación didáctica de cambios

Al proponer o entregar cualquier cambio, explicar con detalle suficiente para que el desarrollador comprenda no solo qué código cambió, sino qué concepto del sistema representa y qué necesidad satisface. Describir las piezas principales y cómo se relacionan en el flujo (por ejemplo, qué entidad o responsabilidad modelan y cómo se conecta con las demás), las decisiones importantes y las consecuencias relevantes para datos, permisos, interfaz y pruebas. Explicar términos técnicos cuando sean necesarios para entender la solución; no asumir que enumerar archivos o describir sintaxis explica el propósito. Ajustar la profundidad al cambio y evitar el detalle rutinario que no aporte comprensión.
