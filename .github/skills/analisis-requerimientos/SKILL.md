---
name: analisis-requerimientos
description: "Relevar, redactar, refinar y trazar requisitos e historias de usuario para ServiInmueble. Usar ante ambigüedades, nuevas funciones, backlog, OQ, criterios de aceptación o conflictos de alcance."
---

# Ingeniería de requerimientos

## Antes de empezar

Aplicar `serviinmueble-contexto`. Verificar los documentos vigentes del repositorio y no suponer que los IDs `US` del material de planificación equivalen a los `HU` del índice.

## Procedimiento

1. Precisar objetivo, actor, necesidad, disparador, resultado esperado y release.
2. Revisar fuentes, dependencias, permisos, estados, errores, duplicados, bajas lógicas y datos vacíos.
3. Detectar términos vagos, omisiones, contradicciones, duplicados y decisiones abiertas. No convertir una recomendación en regla aprobada.
4. Registrar cada duda decisiva como `OQ-xx`: pregunta accionable, contexto, opciones, recomendación `[SUPUESTO]`, impacto, estado y fuente/fecha cuando estén disponibles.
5. Redactar el requisito atómico, necesario, consistente, factible, verificable y trazable. Usar `RF-xx` para funcional, `RNF-xx` para calidad, `RN-xx` para negocio, `RC-xx` para restricción y `RI-xx` para interfaz.
6. Refinar la historia en formato Como/quiero/para; asignar prioridad/release solo según fuente validada. Identificar dependencias sin reemplazar la historia por tareas técnicas.
7. Asociar criterios comprobables y casos `CP-xx`; marcar qué queda fuera de alcance y qué OQ bloquea.
8. Presentar conflictos y decisiones requeridas antes de modificar documentos o código.

## Calidad de requisitos e historias

- Criterios observables, condiciones y mensajes verificables; evitar “rápido”, “fácil”, “adecuado”, “etc.” y “según corresponda”.
- Para flujos críticos, usar Gherkin con camino feliz, validaciones, permisos, duplicados, estado vacío y baja lógica.
- Usar INVEST y DoR: objetivo claro, criterios completos, prioridad/estimación y dependencias identificadas, sin OQ bloqueantes sin tratamiento y wireframe cuando corresponda.
- No asignar una estimación numérica ni cerrar una ambigüedad por inferencia.
- Considerar la inconsistencia conocida entre el plan `US-01..15` y el índice de repositorio `HU-01..22`; conservar ambos IDs hasta la resolución.

## Salida

Entregar los IDs fuente, release afectado, requisitos/historias redactados, supuestos, OQ abiertas, dependencias y trazabilidad a pantalla/datos/pruebas. Distinguir claramente propuesta, borrador y validado.
