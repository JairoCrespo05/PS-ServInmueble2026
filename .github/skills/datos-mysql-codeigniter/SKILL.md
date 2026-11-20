---
name: datos-mysql-codeigniter
description: "Modelar o modificar esquema, migraciones, relaciones, restricciones, índices y datos semilla MySQL/MariaDB para ServiInmueble con CodeIgniter."
---

# Modelado y persistencia de datos

## Antes de empezar

Aplicar `serviinmueble-contexto` y revisar migraciones, modelos y convenciones presentes. Confirmar el motor/versionado efectivo; no asumir que el modelo conceptual sugerido ya es el esquema implementado.

## Procedimiento

1. Trazar las entidades y columnas hasta las HU/US, RF/RN y OQ que las justifican.
2. Definir PK, FK, nulabilidad, tipos, longitudes, unicidad e índices según consultas y reglas confirmadas.
3. Preservar datos históricos mediante baja lógica. Usar restricciones referenciales coherentes; evitar `DELETE` físico como estrategia de baja.
4. Versionar cambios mediante el mecanismo de migraciones existente; comprobar aplicación y reversión si el framework lo admite.
5. Usar tipos precisos: montos `DECIMAL`, lecturas/consumos decimales y fechas como fechas. Definir la representación del período en conjunto con requisitos antes de imponerla.
6. Mantener separadas la baja lógica del servicio asociado y su estado operativo (por ejemplo, activo/pausado/en revisión), si ambos conceptos forman parte del requerimiento validado.
7. Revisar aislamiento de datos: un propietario solo puede acceder a sus registros por relaciones verificables; las FK no sustituyen autorización.
8. Preparar datos semilla reproducibles y no incluir credenciales reales. Limitar fixtures a datos ficticios.
9. Verificar instalación desde cero, restricciones, migraciones repetidas cuando aplique, consultas principales y conservación histórica.

## Decisiones no confirmadas

No fijar sin validación: relación 1:N o N:M de inmueble/usuario (OQ-03); multiplicidad de tipo de servicio por inmueble (OQ-04); unicidad de factura por servicio/período (OQ-05); pagos parciales (OQ-06); atributos/forma de CPE/Pagar (OQ-13). Las recomendaciones del material son supuestos, no requisitos aprobados.

## Entrega

Informar tablas/campos afectados, migración/rollback, constraints, datos de prueba, pruebas realizadas y trazabilidad a los IDs fuente, sin renumerar HU/US.
