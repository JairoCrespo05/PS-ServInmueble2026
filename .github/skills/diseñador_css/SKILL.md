---
name: diseñador_css
description: Habilidad para diseñar y estilizar páginas web utilizando CSS. Diseña los botones de Alta, Baja, Modificacion y Consulta para todas las funcionalidades de la aplicación, evitando que se rompa la consistencia visual y estética.
---

## Antes de empezar

Aplica los principios de diseño y consistencia visual en todas las páginas de la aplicación, asegurándote de que los botones y otros elementos de la interfaz mantengan un estilo uniforme y estéticamente agradable.

Considera aspectos como la paleta de colores, la tipografía, los márgenes y el espaciado, así como los estados de los botones (normal, hover, activo, deshabilitado) para garantizar una experiencia de usuario coherente y profesional.

Asegúrate de probar los estilos en diferentes navegadores y dispositivos para mantener la consistencia visual y la accesibilidad en toda la aplicación.

## Buenas prácticas

- Mantén un sistema de diseño coherente con variables CSS para colores, tipografía y espaciado.
- Utiliza clases reutilizables para los botones y otros elementos de la interfaz.
- Aplica transiciones suaves para los estados de los botones (hover, activo) para mejorar la experiencia del usuario.
- Evita el uso excesivo de estilos en línea; prefiere hojas de estilo externas para mantener el código limpio y mantenible.
- Documenta los estilos y componentes para facilitar la colaboración y el mantenimiento del proyecto.
- Realiza revisiones periódicas de los estilos para asegurar que se mantenga la consistencia visual a medida que la aplicación evoluciona.
- Asegúrate de que los estilos cumplan con las pautas de accesibilidad, incluyendo contraste de colores y navegabilidad mediante teclado.
- Realiza pruebas de accesibilidad utilizando herramientas y técnicas adecuadas para identificar y corregir posibles problemas.
- Mantén actualizados los estilos y componentes conforme a las mejores prácticas de accesibilidad y diseño web.

## Diseño de los botones

Al diseñar los botones de Alta, Baja, Modificación y Consulta, considera los siguientes aspectos:

- **Consistencia visual:** Asegúrate de que todos los botones compartan un estilo coherente en términos de colores, tipografía, bordes y espaciado.
- **Estados del botón:** Define estilos claros para los diferentes estados de los botones (normal, hover, activo, deshabilitado) para mejorar la experiencia del usuario.
- **Accesibilidad:** Garantiza un contraste adecuado entre el texto y el fondo del botón, y asegúrate de que los botones sean navegables mediante teclado.
- **Reutilización:** Utiliza clases CSS reutilizables para los botones, evitando la duplicación de estilos y facilitando el mantenimiento.
- **Transiciones suaves:** Aplica transiciones CSS para los cambios de estado de los botones, proporcionando una experiencia de usuario más agradable.
- **Pruebas en diferentes dispositivos:** Verifica que los botones se vean y funcionen correctamente en distintos navegadores y tamaños de pantalla.

## Para este proyecto (usarlo para modificacion de elementos existentes o para la creacion de nuevos elementos)

### Implementación compartida

- Antes de modificar una pantalla, revisar sus vistas, CSS y JavaScript existentes; conservar textos, rutas, permisos, confirmaciones, CSRF, estados y selectores funcionales. Limitar los cambios al diseño, sin modificar funcionalidades.
- Incluir `public/css/crud-buttons.css` después de los estilos de la pantalla y reutilizar `crud-button` con la variante `crud-button--create`, `crud-button--edit`, `crud-button--delete` o `crud-button--detail`. Para acciones solo con icono, agregar `crud-button--icon`.
- Para iconos de entidad y acción, usar `view('partials/crud_icon', ['entity' => 'home', 'action' => 'edit'])`, reemplazando los nombres por los símbolos Material correspondientes. Cargar Material Symbols Outlined como en las vistas existentes.
- Los botones de icono necesitan `aria-label` descriptivo y `title`; los iconos son decorativos y no reemplazan el nombre accesible. Mantener foco visible, contraste, estados deshabilitados y compatibilidad con movimiento reducido.
- Aplicar estas clases a las acciones existentes y a los nuevos módulos; no cambiar indiscriminadamente los botones de filtros, navegación, confirmación o guardado. El alta pública de usuarios y la aprobación mantienen sus reglas de seguridad.
- Validar las vistas y sus flujos afectados, además de teclado y tamaños de pantalla cuando sea posible. Informar las comprobaciones que no se hayan ejecutado.

Botones de Alta:
- Color de fondo: verde
- Color de texto: blanco
- Bordes: redondeados
- Espaciado: padding de 14px izquierda y derecha
- Transición: suave para hover y activo
- Texto: conservar el texto existente; en elementos nuevos, usar Nuevo/Nueva <Elemento a crear> cuando describa correctamente la acción.
- Simbolo: icono representativo del alta (por ejemplo, un signo de más) antes del texto.

Botones de Baja:
- Forma cuadrada, con simbolo representativo de la baja.
- Color de fondo: rojo
- Bordes: redondeados
- Transición: suave para hover y activo
- Simbolo: icono representativo de la baja. Para dar de baja un usuario, utiliza un icono de usuario con una línea diagonal. Para la baja de un inmueble, utiliza un icono de inmueble con una línea diagonal. Para la baja de un vinculo inmueble-servicio, utiliza un icono de vinculo con una línea diagonal.
- Color de simbolo: blanco.

Botones de Modificación:
- Forma cuadrada, con simbolo representativo de la modificación.
- Color de fondo: azul
- Bordes: redondeados
- Transición: suave para hover y activo
- Simbolo: icono representativo de la modificación. Para modificar un usuario, utiliza un icono de usuario con un lápiz. Para la modificación de un inmueble, utiliza un icono de inmueble con un lápiz. Para la modificación de un vinculo inmueble-servicio, utiliza un icono de vinculo con un lápiz.
- Color de simbolo: blanco.

Botones de Ver Detalle:
- Forma cuadrada, con simbolo representativo de la verificación de detalles.
- Color de fondo: gris
- Bordes: redondeados
- Transición: suave para hover y activo
- Simbolo: icono representativo de la verificación de detalles. Para ver los detalles de un usuario, utiliza un icono de usuario con un ojo. Para los detalles de un inmueble, utiliza un icono de inmueble con un ojo. Para los detalles de un vinculo inmueble-servicio, utiliza un icono de vinculo con un ojo.
- Color de simbolo: blanco.

## Estados de elementos

- Texto: de color oscuro (dependiendo del fondo) para todos los estados. 
- Fondo: de color suave representativo del estado.
- En las etiquetas existentes, conservar el tamaño de letra, las dimensiones y la forma; cambiar únicamente los colores, sin agregar etiquetas donde solo hay texto.
- Cargar `public/css/state-colors.css` después de los estilos de la vista. Agregar `state-color`, `data-state-domain` (`inmueble`, `usuario`, `vinculo` o `factura`) y `data-state` con el estado mostrado en minúsculas. Mantener el texto visible y un color neutro para estados desconocidos.
- Dar prioridad a la baja del inmueble al colorear su etiqueta y usar el estado efectivo del vínculo ya provisto por la aplicación. No calcular vencimientos ni crear estados o reglas de negocio.

Estados de inmuebles:
- Disponible: Color azul suave
- Ocupado: Color morado suave
- En mantenimiento: Color naranja suave
- Dado de baja: Color rojo suave

Estados de usuarios:
- Aprobada: Color verde suave
- Pendiente: Color amarillo suave
- Rechazada: Color rojo suave
- Baja: Color gris suave

Estados de vinculos inmueble-servicio:
- Activo: Color verde suave
- Pausado: Color azul suave
- En revisión: Color naranja suave
- Inactivo: Color rojo suave

Estados de facturas:
- Pendiente: Color amarillo suave
- Pagada: Color verde suave
- Vencida: Color rojo suave

