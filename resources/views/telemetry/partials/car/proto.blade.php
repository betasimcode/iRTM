const vehicleColor = getActiveLapColor();
const vehicleShadow = shadeHexColor(vehicleColor, 0.78);
const vehicleDark = shadeHexColor(vehicleColor, 0.55);

function shadeHexColor(hex, factor) {
    const value = hex.replace('#', '');

    if (!/^[0-9a-fA-F]{6}$/.test(value)) {
        return hex;
    }

    const channels = [0, 2, 4].map(index =>
        parseInt(value.slice(index, index + 2), 16)
    );

    return '#' + channels
        .map(channel =>
            Math.round(channel * factor)
                .toString(16)
                .padStart(2, '0')
        )
        .join('');
}
const updateVehicleColor = () => {
    const vehicleColor = getActiveLapColor();
    const vehicleShadow = shadeHexColor(vehicleColor, 0.78);
    const vehicleDark = shadeHexColor(vehicleColor, 0.55);

    vehicleSvg.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(`
<svg xmlns="http://www.w3.org/2000/svg" viewBox="277 30 225 459" width="225" height="459">
  <ellipse cx="475.756" cy="304.304" rx="86.448" ry="114.076" transform="matrix(1.1995 0 0 0.53835 -181.36153 254.55797)" fill="${vehicleColor}" fill-rule="evenodd" stroke="${vehicleColor}" stroke-width="2"/>
  <rect x="246.401" y="67.688" width="249.982" height="454.448" transform="matrix(0.82961 0 0 0.72847 81.19741 38.01922)" fill="${vehicleColor}" fill-rule="evenodd" stroke="${vehicleColor}" stroke-width="2"/>
  <rect x="285.614" y="38.979" width="207.387" height="48.349" transform="translate(0.00008 -0.00008)" fill="#14141D" stroke="none" stroke-width="2"/>
  <ellipse cx="181.63" cy="270.404" rx="83.677" ry="43.764" transform="matrix(1 0 0 1.36508 207.67798 -49.83087)" fill="#141419" stroke="none" stroke-width="2"/>
  <ellipse cx="447.017" cy="198.631" rx="88.579" ry="48.316" transform="matrix(1 0 0 0.94245 -57.70877 105.24274)" fill="${vehicleColor}" fill-rule="evenodd" stroke="${vehicleColor}" stroke-width="2"/>
</svg>
    `);
};

updateVehicleColor();

