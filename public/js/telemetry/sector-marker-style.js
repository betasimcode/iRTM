window.IRTM_SECTOR_MARKER_STYLE = {
    // Etiquetas de los sectores: conservar los valores actuales.
    fontFamily: 'Orbitron',
    fontSize: 8,
    fontWeight: 700,
    textColor: '#252525',

    backgroundColor: '#11182000',
    borderColor: '#FFD43B00',
    borderWidth: 3,
    borderRadius: 2,

    paddingX: 16,
    paddingY: 1,

    // Distancia de la etiqueta respecto a la trazada.
    labelOffset: 22,

    // Estilo de las líneas transversales de los sectores.
    sectorLine: {
        halfLength: 8,
        offset: 0,

        lineColor: '#13131325',
        lineWidth: 0.9,
        outlineColor: '#11182000',
        outlineWidth: 0
    },

    // Línea de meta ajedrezada.
    finishLine: {
        enabled: true,
        halfLength: 10,
        bandThickness: 1,
        squareSize: 1,
        lightColor: '#FFFFFF',
        darkColor: '#171717',
        borderColor: '#111820',
        borderWidth: 0.1,
        offset: 0
    }
};
