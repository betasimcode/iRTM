
<style>
    #telemetry-floating-panel-host > #telemetry-main-panel {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 1;
        pointer-events: auto;
        padding: 8px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: var(--card);
        box-shadow: 0 4px 16px rgb(0 0 0 / 15%);
        touch-action: none;
    }


    /* Distribución compacta del panel desacoplado */
    #telemetry-main-panel.telemetry-compact {
        width: 270px;
        max-width: calc(100vw - 32px);
        padding: 10px;
        border-color: rgb(100 116 139 / 45%);
        border-radius: 12px;
        background: rgb(15 23 42 / 96%);
        color: #e2e8f0;
        box-shadow:
            0 12px 30px rgb(0 0 0 / 30%),
            0 2px 6px rgb(0 0 0 / 20%);
        backdrop-filter: blur(8px);
        touch-action: none;
    }

    /* Grid principal: marcha | velocidad | volante */
    #telemetry-main-panel.telemetry-compact > div.min-w-52 {
        display: grid;
        grid-template-columns: 52px minmax(70px, 1fr) 64px;
        grid-template-rows: auto auto auto;
        align-items: center;
        justify-content: stretch;
        gap: 8px;
        min-width: 0;
        width: 100%;
    }

    /* Marcha */
    #telemetry-main-panel.telemetry-compact
    > div.min-w-52 > div:nth-child(1) {
        grid-column: 1;
        grid-row: 1;
        min-width: 0;
        height: 38px;
        width: 100%;
        margin: 0;
    }

    /* Velocidad */
    #telemetry-main-panel.telemetry-compact
    > div.min-w-52 > div:nth-child(2) {
        grid-column: 2;
        grid-row: 1;
        min-width: 0;
        max-width: 80px;
        height: 38px;
        width: 100%;
        margin: 0;
    }

    /* Volante */
    #telemetry-main-panel.telemetry-compact
    > div.min-w-52 > div:nth-child(5) {
        grid-column: 3;
        grid-row: 1;
        min-width: 0;
        width: 100%;
        margin: 0;
        padding: 2px;
        gap: 2px;
    }

    /* Tiempo de vuelta */
    #telemetry-main-panel.telemetry-compact
    > div.min-w-52 > div:nth-child(3) {
        grid-column: 1 / -1;
        grid-row: 2;
        min-width: 0;
        width: 100%;
        margin: 0;
        padding: 5px 8px;
        border: 1px solid rgb(100 116 139 / 30%);
        border-radius: 7px;
        background: var(--digital);
    }

    /* Pedales: tercera fila, siempre visibles */
    /* Pedales verticales: tercera fila cuando están activos */
    #telemetry-main-panel.telemetry-compact
    > div.min-w-52 > div:nth-child(4) {
        grid-column: 1 / -1;
        grid-row: 3;
        justify-self: center;
        width: 100%;
        margin: 0;
        padding: 4px 0 0;
        /* border-top: 1px solid rgb(100 116 139 / 25%); */
    }

    /* Superficies digitales */
    #telemetry-main-panel.telemetry-compact
    > div.min-w-52 > div:nth-child(1) > div,
    #telemetry-main-panel.telemetry-compact
    > div.min-w-52 > div:nth-child(2) > div,
    #telemetry-main-panel.telemetry-compact
    > div.min-w-52 > div:nth-child(3) {
        border-color: rgb(100 116 139 / 35%);
        border-radius: 7px;
    }

    /* Tamaño de las cifras */
    #telemetry-main-panel.telemetry-compact #telemetry-current-gear {
        font-size: 1.6rem;
        line-height: 1.8rem;
    }

    #telemetry-main-panel.telemetry-compact #telemetry-current-speed {
        font-size: 1.6rem;
        line-height: 1.8rem;
    }

    #telemetry-main-panel.telemetry-compact #telemetry-current-lap-time {
        font-size: 1.35rem;
        line-height: 1.8rem;
        text-align: center;
        letter-spacing: .025em;
    }

    /* Volante más pequeño */
    #telemetry-main-panel.telemetry-compact #telemetry-steering-wheel-rotor {
        width: 2.5rem;
        height: 2.5rem;
    }

    #telemetry-main-panel.telemetry-compact #telemetry-current-steering-value {
        display: block;
        font-size: .7rem;
        line-height: 1rem;
        color: #cbd5e1;
    }

    /* Barras de pedales compactas */
    #telemetry-main-panel.telemetry-compact
    > div.min-w-52 > div:nth-child(4) > div {
        width: 22px;
        gap: 3px;
    }

    #telemetry-main-panel.telemetry-compact
    > div.min-w-52 > div:nth-child(4) > div > div.min-w-52 {
        width: 22px;
        height: 38px;
        border-radius: 4px;
        background: rgb(2 6 23 / 80%);
    }

    #telemetry-main-panel.telemetry-compact
    > div.min-w-52 > div:nth-child(4) > div > span {
        font-size: .65rem;
        line-height: .9rem;
        color: #cbd5e1;
    }

    /* Arrastre */
    #telemetry-floating-panel-host > #telemetry-main-panel {
        cursor: grab;
    }

    #telemetry-floating-panel-host > #telemetry-main-panel.is-dragging {
        cursor: grabbing;
        user-select: none;
    }


    #telemetry-floating-panel-host > #telemetry-main-panel {
        cursor: grab;
    }

    #telemetry-floating-panel-host > #telemetry-main-panel.is-dragging {
        cursor: grabbing;
        user-select: none;
    }


    /* Miniheader del panel flotante */
    #telemetry-floating-panel-host > #telemetry-main-panel
    .telemetry-floating-toolbar {
        display: flex;
        align-items: center;
        gap: 6px;
        width: 100%;
        min-height: 30px;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgb(100 116 139 / 25%);
        cursor: grab;
        touch-action: none;
    }

    .telemetry-floating-drag-handle {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        color: #cbd5e1;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .08em;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: grab;
    }

    #telemetry-floating-panel-host > #telemetry-main-panel
    .telemetry-floating-toolbar button {
        flex: 0 0 auto;
        padding: 4px 8px;
        border: 1px solid rgb(100 116 139 / 40%);
        border-radius: 6px;
        background: rgb(30 41 59 / 90%);
        color: #e2e8f0;
        font-size: 11px;
        line-height: 1.4;
        cursor: pointer;
    }

    #telemetry-floating-panel-host > #telemetry-main-panel
    .telemetry-floating-toolbar button:hover {
        background: rgb(51 65 85 / 95%);
    }

    #telemetry-floating-panel-host > #telemetry-main-panel.is-dragging
    .telemetry-floating-toolbar {
        cursor: grabbing;
    }

    #telemetry-floating-panel-host > #telemetry-main-panel
    .telemetry-floating-toolbar {
        display: flex;
        align-items: center;
        gap: 6px;
        width: 100%;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgb(100 116 139 / 25%);
        cursor: grab;
        touch-action: none;
    }

    .telemetry-floating-drag-handle {
        flex: 1;
        min-width: 0;
        color: #cbd5e1;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .06em;
        white-space: nowrap;
        cursor: grab;
    }

    .telemetry-floating-toolbar #telemetry-panel-controls {
        display: flex;
        flex: 0 0 auto;
        gap: 5px;
    }

    .telemetry-floating-toolbar #telemetry-panel-controls button {
        padding: 4px 7px;
        font-size: 11px;
        line-height: 1.4;
        white-space: nowrap;
    }

    /* El host deja pasar eventos al mapa, pero el panel debe ser interactivo. */
    #telemetry-floating-panel-host > #telemetry-main-panel {
        pointer-events: auto !important;
    }

    #telemetry-floating-panel-host > #telemetry-main-panel
    .telemetry-floating-toolbar,
    #telemetry-floating-panel-host > #telemetry-main-panel
    .telemetry-floating-toolbar button {
        pointer-events: auto !important;
    }

    /* INPUTS y volante comparten la fila inferior del panel flotante */
    #telemetry-main-panel.telemetry-compact
    #telemetry-bottom-controls {
        grid-column: 1 / -1;
        grid-row: 3;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-width: 0;
        width: 100%;
        margin: 0;
        padding-top: 6px;
        /* border-top: 1px solid rgb(100 116 139 / 25%); */
    }

    /* INPUTS ocupa la zona izquierda */
    #telemetry-main-panel.telemetry-compact
    #telemetry-mini-controls {
        flex: 1 1 auto;
        min-width: 0;
        width: auto;
        padding: 6px 8px;
    }

    /* El volante permanece a la derecha */
    #telemetry-main-panel.telemetry-compact
    #telemetry-steering-control {
        flex: 0 0 64px;
        width: 64px;
        min-width: 0;
        padding: 0;
    }
</style>




<script>
(() => {
    const initTelemetryPanel = () => {
        const panel = document.getElementById('telemetry-main-panel');
        const host = document.getElementById('telemetry-floating-panel-host');
        const detachButton = document.getElementById('telemetry-panel-detach');

        if (!panel || !host || !detachButton) {
            console.error('[TelemetryPanel] Faltan elementos necesarios.', {
                panel: !!panel,
                host: !!host,
                detachButton: !!detachButton,
            });
            return;
        }

        // Evitar registrar los listeners más de una vez.
        if (panel.dataset.presentationReady === '1') return;
        panel.dataset.presentationReady = '1';

        // Recordar la ubicación original del panel.
        const panelPlaceholder = document.createComment('panel-origin');
        panel.parentNode.insertBefore(panelPlaceholder, panel);

        // Miniheader para arrastrar el panel flotante.
        const toolbar = document.createElement('div');
        toolbar.className = 'telemetry-floating-toolbar';

        const dragHandle = document.createElement('span');
        dragHandle.className = 'telemetry-floating-drag-handle';
        dragHandle.textContent = 'IRTM · TELEMETRÍA';
        dragHandle.title = 'Arrastrar instrumentos';

        toolbar.appendChild(dragHandle);

        let detached = panel.parentElement === host;
        let drag = null;
        let position = { x: 12, y: 12 };

        const updateButton = () => {
            const label = detachButton.querySelector('span:last-child');

            // Un solo botón: muestra la acción disponible.
            if (label) {
                label.textContent = detached ? 'Acoplar' : 'Desacoplar';
            } else {
                detachButton.textContent = detached ? 'Acoplar' : 'Desacoplar';
            }

            detachButton.title = detached
                ? 'Acoplar instrumentos en su posición original'
                : 'Desacoplar instrumentos sobre el mapa';

            detachButton.setAttribute('aria-pressed', String(detached));
            detachButton.setAttribute(
                'aria-label',
                detached ? 'Acoplar instrumentos' : 'Desacoplar instrumentos'
            );
        };

        const applyPosition = () => {
            const maxX = Math.max(0, host.clientWidth - panel.offsetWidth);
            const maxY = Math.max(0, host.clientHeight - panel.offsetHeight);

            position.x = Math.max(0, Math.min(position.x, maxX));
            position.y = Math.max(0, Math.min(position.y, maxY));

            panel.style.left = `${position.x}px`;
            panel.style.top = `${position.y}px`;
        };

        const setDetached = value => {
            if (value === detached) return;

            if (value) {
                // Desacoplar: mover el panel al contenedor flotante.
                host.appendChild(panel);
                panel.insertBefore(toolbar, panel.firstChild);

                panel.classList.add('telemetry-compact');
                panel.hidden = false;
                panel.style.position = 'absolute';
                panel.style.display = '';

                detached = true;

                applyPosition();
            } else {
                // Acoplar: devolverlo exactamente a su posición original.
                if (!panelPlaceholder.parentNode) {
                    console.error(
                        '[TelemetryPanel] No se encuentra el marcador original del panel.'
                    );
                    return;
                }

                panelPlaceholder.parentNode.insertBefore(
                    panel,
                    panelPlaceholder.nextSibling
                );

                toolbar.remove();

                panel.classList.remove('telemetry-compact', 'is-dragging');
                panel.style.left = '';
                panel.style.top = '';
                panel.style.position = '';
                panel.style.display = '';
                panel.style.touchAction = '';
                panel.hidden = false;

                detached = false;
            }

            updateButton();

            console.info('[TelemetryPanel] Estado actualizado:', {
                detached,
                parent: panel.parentElement?.id ?? null,
            });
        };

        // El mismo botón alterna ambos estados.
        detachButton.type = 'button';

        detachButton.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();

            setDetached(!detached);
        });

        // Arrastrar desde el miniheader, sin interferir con los botones.
        toolbar.addEventListener('pointerdown', event => {
            if (!detached || event.button !== 0) return;

            if (event.target.closest('button')) {
                event.stopPropagation();
                return;
            }

            const rect = panel.getBoundingClientRect();
            const hostRect = host.getBoundingClientRect();

            drag = {
                pointerId: event.pointerId,
                offsetX: event.clientX - rect.left,
                offsetY: event.clientY - rect.top,
                hostLeft: hostRect.left,
                hostTop: hostRect.top,
            };

            panel.classList.add('is-dragging');
            toolbar.setPointerCapture(event.pointerId);
            event.preventDefault();
        });

        toolbar.addEventListener('pointermove', event => {
            if (!drag || event.pointerId !== drag.pointerId) return;

            position.x = event.clientX - drag.hostLeft - drag.offsetX;
            position.y = event.clientY - drag.hostTop - drag.offsetY;

            applyPosition();
        });

        const endDrag = event => {
            if (!drag || event.pointerId !== drag.pointerId) return;

            drag = null;
            panel.classList.remove('is-dragging');
        };

        toolbar.addEventListener('pointerup', endDrag);
        toolbar.addEventListener('pointercancel', endDrag);

        window.addEventListener('resize', () => {
            if (detached) applyPosition();
        });

        // Reflejar el estado inicial real del panel.
        if (detached && !panel.contains(toolbar)) {
            panel.insertBefore(toolbar, panel.firstChild);
            panel.classList.add('telemetry-compact');
            panel.style.position = 'absolute';
        }

        updateButton();

        console.info('[TelemetryPanel] Inicializado correctamente.');
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTelemetryPanel, {
            once: true,
        });
    } else {
        initTelemetryPanel();
    }
})();
</script>



