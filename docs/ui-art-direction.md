# Criterio visual: iconos y controles

## Dirección

Interfaz de telemetría y gestión de carreras: precisa, sobria y funcional. Los iconos son monocromos, lineales y de trazo uniforme; el color se reserva para indicar intención o estado. Evitar emojis, pictogramas multicolor y SVG pegados desde repositorios con estilos propios.

## Reglas

- Usar `x-ui-icon` para iconografía funcional y ampliar su catálogo antes de pegar SVG nuevos en vistas.
- Mantener iconos a 20 px en controles normales y usar `icon-action` (36 × 36 px) para acciones de tabla.
- Toda acción solo con icono debe declarar un `label` comprensible; el control ya lo expone como nombre accesible y tooltip.
- Usar `ui-button` para acciones con texto, `ui-button--primary` para la acción principal y `ui-button--danger` para operaciones destructivas.
- Usar el color de peligro solo para borrar o deshacer; navegación y edición permanecen neutras.
- Mantener foco visible, objetivo de clic de al menos 36 px y estados hover con fondo sutil.
- Enlazar cada icono con verbos familiares (`edit`, `delete`, `add`, `refresh`, `close`, `external`); no añadir icono decorativo junto a texto que ya comunica lo mismo.

## Implementación

Los tokens usan las variables del tema existente para respetar los temas oscuro, claro y de equipo. Las clases y componentes compartidos viven en `resources/css/app.css` y `resources/views/components`.
