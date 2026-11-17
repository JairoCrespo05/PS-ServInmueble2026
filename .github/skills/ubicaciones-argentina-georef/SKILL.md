---
name: ubicaciones-argentina-georef
description: "Implementar y validar selección de provincia y ciudad/localidad argentina en direcciones de inmuebles con GeoRef."
---

# Ubicaciones argentinas con GeoRef

## Cuándo usarla

Usar al agregar o modificar provincia y ciudad/localidad en direcciones de inmuebles de ServiInmueble. Aplicar primero `serviinmueble-contexto` y sumar las skills de frontend, backend, datos y QA que correspondan.

## Fuente y persistencia

- Si el alcance confirmado usa GeoRef, consultar `https://apis.datos.gob.ar/georef/api/` y no reemplazar su catálogo por una lista reducida o ingresada a mano.
- Presentar las 24 jurisdicciones devueltas por GeoRef, incluida la Ciudad Autónoma de Buenos Aires. Al elegir una, cargar únicamente sus localidades.
- Persistir provincia y ciudad en `direcciones`; usar una migración y conservar las direcciones históricas. Si los registros existentes no tienen provincia, mantenerla nula en la migración y pedir una selección válida cuando se modifiquen.
- No guardar IDs de GeoRef en lugar de los nombres si el esquema acordado persiste nombres; mantener el formato y longitudes de la tabla existentes.

## Backend e integración

- Servir el catálogo mediante rutas autenticadas y autorizadas para el rol que gestiona inmuebles. No depender de llamadas directas del navegador a otro origen si la CSP o CORS no lo permiten.
- Mantener la verificación TLS habilitada, usar timeout explícito, validar el estado y la estructura JSON, y devolver un error visible si GeoRef no responde. No usar `verify=false` ni aceptar una selección como válida solo porque la interfaz la haya ofrecido.
- Cachear respuestas exitosas con vencimiento razonable para reducir llamadas repetidas, sin convertir errores de red en catálogos vacíos o datos exitosos.
- En alta y modificación, validar en servidor que la provincia existe y que la localidad pertenece a esa provincia antes de persistir. Rechazar valores faltantes, manipulados o de otra jurisdicción.
- Guardar provincia y localidad dentro de la misma transacción de persistencia de la dirección, respetando Query Builder, autorización por propietario y CSRF del formulario.

### Certificados CA en WAMP (Windows)

Si PHP falla con errores como `unable to get local issuer certificate` y el endpoint del catálogo devuelve 502, configurar una cadena de confianza válida en PHP. No desactivar la verificación TLS.

1. Descargar el bundle CA en formato PEM desde la fuente oficial de cURL: `https://curl.se/ca/cacert.pem`. No descargar ni confiar solamente en el certificado final del servidor GeoRef.
2. Guardarlo fuera del directorio público del sitio, por ejemplo en `C:\wamp64\bin\php\php<version>\extras\ssl\cacert.pem`. Reemplazar `<version>` por la versión de PHP activa en WAMP y crear la carpeta `ssl` si no existe.
3. Editar el archivo INI que carga Apache para esa versión (en WAMP suele ser `phpForApache.ini`; confirmar desde el menú de WAMP o la configuración de Apache). No asumir que Apache y la CLI usan el mismo INI. Descomentar o ajustar estas directivas, evitando duplicarlas:

   ```ini
   curl.cainfo = "C:/wamp64/bin/php/php<version>/extras/ssl/cacert.pem"
   openssl.cafile = "C:/wamp64/bin/php/php<version>/extras/ssl/cacert.pem"
   ```

4. Si también se necesita que los comandos CLI, como `php spark`, confíen en el mismo bundle, configurar las directivas en el `php.ini` usado por CLI.
5. Reiniciar todos los servicios desde WAMP para que Apache cargue la configuración nueva.
6. Con una sesión autorizada, consultar el endpoint de provincias de la aplicación. Debe responder HTTP 200 con JSON que incluya `provincias`; luego comprobar que al elegir una provincia se cargan sus localidades. Si continúa el 502, revisar que la ruta del PEM exista y consultar `writable/logs` y los logs de PHP/Apache.

## UX del formulario

- Mantener dos controles `<select>` con etiquetas visibles: Provincia y Ciudad / localidad.
- Deshabilitar localidad hasta que se elija provincia; limpiar la selección anterior al cambiar de provincia y mostrar estados de carga, error y reintento.
- En edición, cargar la provincia guardada y después la localidad correspondiente. Preservar datos enviados cuando hay errores de validación.
- Para direcciones antiguas sin provincia, explicar que debe elegirse nuevamente una provincia y localidad; nunca inventar una correspondencia a partir del nombre de ciudad.
- Mantener accesibilidad: controles asociados a `label`, estado anunciado con `aria-live` y mensajes junto al campo.

## Pruebas y entrega

Validar como mínimo:

1. Se cargan las 24 jurisdicciones y el catálogo de localidades corresponde a la provincia elegida.
2. Cambiar provincia borra la localidad anterior y la reemplaza por opciones de la nueva provincia.
3. Alta y modificación guardan ambos nombres; la edición vuelve a seleccionar los valores persistidos.
4. Un POST manipulado con una ciudad de otra provincia es rechazado también sin JavaScript.
5. Si GeoRef falla, el formulario muestra el error y no guarda una ubicación no validada.
6. La migración se aplica sobre un esquema existente sin eliminar direcciones ni afectar otras migraciones pendientes.

Al informar el resultado, distinguir pruebas ejecutadas de no probadas e indicar cualquier dependencia operativa de red, `ext-curl` o certificados CA de PHP.
