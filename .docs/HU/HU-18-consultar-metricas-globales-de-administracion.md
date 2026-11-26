# HU-18 — Consultar métricas globales de administración

**Estado:** Definición funcional validada para implementación  
**Fuente del identificador:** Índice `.docs/HU/README.md`  
**Épica:** Consultas, métricas e informes  
**Prioridad / release:** Final  
**Actor:** Administrador
**Validación de decisiones:** 25/11/2026

## Historia

Como **administrador**, quiero consultar un dashboard con métricas globales de los propietarios, sus inmuebles, servicios asociados y facturas, para conocer el estado general de la información gestionada en la plataforma e identificar facturas vencidas impagas.

## Objetivo y alcance

El dashboard consolida información de todos los propietarios aprobados; no presenta información operativa de un propietario particular ni permite modificar los datos desde esta pantalla.

Incluye:

- Indicadores globales de propietarios aprobados, inmuebles y servicios asociados.
- Resumen de facturas por estado y un porcentaje de mora calculado por cantidad de facturas.
- Un único gráfico anual con selectores de año y métrica: inmuebles, propietarios, servicios asociados o tipos de servicio del catálogo.
- Un detalle de facturas pendientes vencidas que identifica al propietario, el inmueble y el servicio asociado.
- Separación de registros activos e inactivos en los totales de inmuebles y servicios asociados, conservando el contexto histórico de facturas.

La expresión **factura vencida impaga** se usa en esta historia en lugar de “cuota demorada”: el modelo actual registra facturas con pago total y no contempla cuotas parciales.

## Reglas y definiciones confirmadas

- El gráfico muestra **altas mensuales** para el año seleccionado, no un acumulado al cierre de cada mes.
- La métrica de usuarios del gráfico cuenta **propietarios aprobados**.
- Los propietarios se agrupan por la fecha de creación de su cuenta.
- El gráfico ofrece por separado las altas de servicios asociados y las altas de tipos de servicio del catálogo.
- El porcentaje de mora se calcula por **cantidad de facturas**, no por suma de montos:

  `facturas pendientes vencidas / total de facturas pendientes × 100`

- El detalle de mora incluye solo facturas que continúan pendientes y cuyo vencimiento ya pasó; no incluye facturas pagadas fuera de término.
- Los inmuebles y servicios asociados inactivos se distinguen de los activos, sin borrarlos de la información histórica.
- Si no hay facturas pendientes, el porcentaje muestra **“Sin datos”**.

## Requisitos funcionales

### RF-HU18-01 — Acceso administrativo

El sistema debe permitir consultar el dashboard únicamente a un usuario cuyo rol vigente en la base de datos sea `Administrador`. Debe aplicar autorización en el servidor tanto a la pantalla como a los endpoints que entreguen sus métricas.

### RF-HU18-02 — Indicadores globales

El dashboard debe consolidar datos de todos los propietarios aprobados y mostrar, como mínimo:

- cantidad de propietarios aprobados;
- cantidad de inmuebles, diferenciando activos e inactivos;
- cantidad de servicios asociados, diferenciando activos e inactivos;
- cantidad de facturas pendientes y pagadas;
- porcentaje y cantidad de facturas pendientes vencidas.

Las cifras deben representar el alcance global y no depender del propietario que haya iniciado sesión anteriormente ni de filtros de otro módulo.

### RF-HU18-03 — Porcentaje de mora

El sistema debe calcular el porcentaje usando el número de facturas pendientes vencidas como numerador y el número total de facturas pendientes como denominador. Se considera vencida una factura pendiente cuya fecha de segundo vencimiento ya pasó, o cuya fecha de primer vencimiento ya pasó cuando no tiene segundo vencimiento.

El dashboard debe mostrar el porcentaje junto con la cantidad de facturas vencidas y el total de facturas pendientes que se usó en el cálculo, para que el indicador sea interpretable. Cuando no haya facturas pendientes, debe mostrar “Sin datos”.

### RF-HU18-04 — Gráfico anual interactivo

El dashboard debe presentar un único gráfico con doce períodos mensuales del año seleccionado y ofrecer controles para seleccionar:

1. el año;
2. una métrica: inmuebles, propietarios aprobados, servicios asociados o tipos de servicio del catálogo.

Al cambiar cualquiera de los controles, el sistema debe actualizar ese mismo gráfico sin cambiar de módulo. Cada punto debe representar las altas registradas en el mes correspondiente. Los meses sin altas deben permanecer visibles con valor cero. Para propietarios aprobados se utiliza el mes de creación de la cuenta; para las otras series se utiliza la fecha de creación de la entidad contabilizada.

El selector de año debe permitir consultar años con información disponible. La opción de catálogo cuenta los registros de tipos de servicio administrados por el sistema; la opción de servicios asociados cuenta sus vínculos con inmuebles.

### RF-HU18-05 — Detalle de facturas pendientes vencidas

El dashboard debe mostrar las facturas pendientes vencidas correspondientes a los propietarios aprobados. Cada fila debe identificar:

- propietario (nombre y apellido);
- inmueble;
- servicio asociado y proveedor;
- período de facturación;
- monto de la factura;
- fecha de vencimiento aplicable;
- días transcurridos desde el vencimiento.

El cálculo de vencimiento y días transcurridos debe usar el segundo vencimiento cuando exista y, en caso contrario, el primero. Las facturas pagadas fuera de término no deben aparecer en este detalle.

### RF-HU18-06 — Estados de pantalla

El dashboard debe informar claramente cuando no haya datos para mostrar, cuando el año seleccionado no tenga altas y cuando no existan facturas pendientes vencidas. Si falla la carga, debe mostrar un error comprensible y permitir reintentar sin presentar cifras parciales como si fueran un resultado completo.

## Requisitos de interfaz y calidad

- **RI-HU18-01:** Los controles de año y métrica deben tener etiquetas visibles y ser utilizables por teclado. El gráfico debe contar con un resumen textual accesible de la métrica, el año y los valores representados.
- **RI-HU18-02:** Los estados de factura y actividad deben comunicarse con texto, no solo mediante color.
- **RNF-HU18-01:** Los datos globales y el detalle de facturas deben estar disponibles únicamente para administradores autorizados; ocultar enlaces en la interfaz no reemplaza la autorización del servidor.
- **RNF-HU18-02:** Los datos personales y financieros del propietario deben limitarse a lo necesario para identificar las facturas incluidas en el dashboard.

## Criterios de aceptación

### CP-HU18-01 — Acceso según rol

```gherkin
Dado un usuario autenticado con rol vigente Administrador
Cuando consulta el dashboard de métricas globales
Entonces el sistema muestra las métricas consolidadas
Y permite acceder al gráfico anual y al detalle de facturas vencidas
```

```gherkin
Dado un usuario cuyo rol vigente no es Administrador
Cuando intenta consultar el dashboard o sus datos mediante una ruta directa
Entonces el servidor deniega el acceso
Y no entrega métricas ni datos de facturas globales
```

### CP-HU18-02 — Consolidación y estados

```gherkin
Dado que existen varios propietarios aprobados con inmuebles, servicios asociados y facturas
Cuando el administrador abre el dashboard
Entonces los indicadores consolidan los registros de todos esos propietarios
Y muestran por separado los inmuebles y servicios asociados activos e inactivos
Y presentan las facturas según su estado
```

### CP-HU18-03 — Cálculo del porcentaje de mora

```gherkin
Dado un conjunto de facturas pendientes que incluye facturas vencidas
Cuando el sistema calcula el porcentaje de mora
Entonces divide la cantidad de facturas pendientes vencidas por la cantidad total de facturas pendientes
Y muestra el porcentaje y ambas cantidades usadas en el cálculo
```

### CP-HU18-04 — Selección de año y métrica

```gherkin
Dado que el administrador visualiza el gráfico anual
Cuando selecciona otro año o cambia la métrica entre inmuebles, propietarios, servicios asociados y tipos de servicio del catálogo
Entonces se actualiza el mismo gráfico con las altas mensuales de la selección
Y conserva los doce meses, incluidos los que tienen valor cero
```

### CP-HU18-05 — Identificación del detalle vencido

```gherkin
Dado que existen facturas pendientes vencidas de distintos propietarios
Cuando el administrador consulta el detalle de mora
Entonces cada fila identifica al propietario, el inmueble, el servicio y proveedor, el período, el monto, el vencimiento y los días transcurridos
Y no incluye facturas pagadas fuera de término
```

### CP-HU18-06 — Sin datos y errores

```gherkin
Dado que no existen registros para uno de los indicadores, el año seleccionado o el detalle de mora
Cuando el administrador consulta el dashboard
Entonces el sistema muestra un estado vacío explícito para esa sección
Y mantiene disponibles las demás secciones que sí pudieron cargarse correctamente
```

```gherkin
Dado que falla la carga de las métricas
Cuando el administrador consulta o reintenta el dashboard
Entonces el sistema informa el error sin mostrar resultados parciales como completos
Y ofrece una acción para volver a intentar
```

## Dependencias

- HU-03 — Administrar usuarios.
- HU-05 a HU-10 — Inmuebles y servicios asociados.
- HU-11 y HU-13 — Facturas y registro de pagos.
- HU-15 — Identificar pagos fuera de término.
- Regla validada para OQ-07 sobre la fecha que determina la mora.
- Resolución de OQ-10/OQ-15 sobre pagos y estado pagada, según el contexto vigente del proyecto.

## Decisiones validadas

### OQ-07 — Fecha que determina una factura vencida

Se considera vencida usando el segundo vencimiento cuando existe y el primero cuando no existe. Validada para esta HU; coincide con el comportamiento actual de `FacturaModel`.

### OQ-HU18-01 — Fecha de alta mensual de propietarios

Los propietarios aprobados se agrupan por el mes de creación de su cuenta. No requiere agregar una fecha de aprobación.

### OQ-HU18-02 — Métrica “servicios” del gráfico

El selector ofrece por separado servicios asociados a inmuebles y registros del catálogo de tipos de servicio.

### OQ-HU18-03 — Denominador vacío

Cuando no existen facturas pendientes, el KPI muestra “Sin datos”.

Las decisiones anteriores se confirmaron para implementar esta HU el 25/11/2026. Las OQ-10/OQ-15 del contexto general siguen pendientes y no se resuelven mediante esta historia.

## Trazabilidad

| Necesidad | Requisito | Criterio |
|---|---|---|
| Consultar información administrativa global | RF-HU18-01, RF-HU18-02 | CP-HU18-01, CP-HU18-02 |
| Conocer el porcentaje y detalle de mora | RF-HU18-03, RF-HU18-05 | CP-HU18-03, CP-HU18-05 |
| Comparar altas por año y tipo de registro | RF-HU18-04 | CP-HU18-04 |
| Entender estados vacíos y fallas | RF-HU18-06 | CP-HU18-06 |
