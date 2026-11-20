# ServiInmueble 2026 — instrucciones del workspace

- Trabajá en español de Argentina y, antes de analizar, diseñar o implementar una tarea del proyecto, aplicá la skill `serviinmueble-contexto`.
- Cargá además la skill del rol implicado: análisis de requisitos, UX/frontend, backend, datos, seguridad, QA o gestión del proyecto. Si la tarea cruza roles, aplicá las skills necesarias.
- No inventes reglas: identificá las decisiones pendientes como OQ, distinguí los supuestos y pedí confirmación cuando una decisión cambie alcance, datos o comportamiento.
- Conservá la trazabilidad entre necesidad, HU/US, requisito, cambio y caso de prueba. No unas ni renumeres identificadores incompatibles sin validación.
- Priorizá el flujo núcleo de Release 1. No adelantes funcionalidades de Release 2 sin pedido y validación explícitos.
- Antes de modificar código, inspeccioná la estructura y los patrones existentes; respetá el stack PHP, CodeIgniter, MySQL, HTML, JavaScript y CSS presentes en el repositorio.
- En acceso: registro público asigna `Usuario_Nuevo`; solo un administrador existente puede aprobarlo como `Propietario`. La cuenta pendiente solo ve el estado de espera. Refrescá el rol desde la base de datos en cada ruta protegida, aplicá autorización de servidor, CSRF, regeneración de sesión al autenticar y destrucción al cerrar.
- En el home, mostrale a cada rol únicamente su navegación y contenido. Para cualquier cambio en este flujo, seguí las skills de backend, seguridad aplicada, UX y QA, y cubrí registro → espera → aprobación → acceso por rol → cierre de sesión.
- Al explicar una propuesta o un cambio, hacelo de forma detallada y didáctica para que el desarrollador entienda tanto qué se hizo como qué representa conceptualmente y qué problema resuelve. Explicá el propósito de las piezas importantes, cómo colaboran en el flujo, las decisiones relevantes y sus efectos en datos, interfaz, permisos y pruebas cuando apliquen. No te limites a enumerar archivos o líneas ni des por obvio el concepto detrás del código; evitá, a la vez, extenderte sobre detalles rutinarios que no ayuden a comprenderlo.
