#!/usr/bin/env python3
"""Reconstruct a preliminary track-map SVG from two iRacing telemetry lap JSON files.

Expected JSON structure: {"samples": [{"lap_dist_pct": ..., "position": {"lat": ..., "lon": ...}}]}
No third-party dependencies required.
"""
from __future__ import annotations
import argparse
import json
import math
import statistics
import sys
from pathlib import Path

EARTH_RADIUS_M = 6_371_000.0


def load_points(path: Path):
    with path.open('r', encoding='utf-8-sig') as f:
        data = json.load(f)
    raw = data.get('samples')
    if not isinstance(raw, list) or len(raw) < 10:
        raise ValueError(f'{path}: no se encuentra una lista samples válida')
    points = []
    skipped = 0
    for sample in raw:
        try:
            pct = float(sample['lap_dist_pct'])
            pos = sample['position']
            lat, lon = float(pos['lat']), float(pos['lon'])
            if not (math.isfinite(pct) and math.isfinite(lat) and math.isfinite(lon)):
                raise ValueError
            if not (-90 <= lat <= 90 and -180 <= lon <= 180):
                raise ValueError
            points.append({'pct': pct % 1.0, 'lat': lat, 'lon': lon})
        except (KeyError, TypeError, ValueError):
            skipped += 1
    if len(points) < 10:
        raise ValueError(f'{path}: solo quedan {len(points)} posiciones GPS válidas')
    # Use a local equirectangular projection, accurate for a single circuit-sized area.
    lat0 = statistics.mean(p['lat'] for p in points)
    lon0 = statistics.mean(p['lon'] for p in points)
    cos_lat = math.cos(math.radians(lat0))
    for p in points:
        p['x'] = EARTH_RADIUS_M * math.radians(p['lon'] - lon0) * cos_lat
        p['y'] = EARTH_RADIUS_M * math.radians(p['lat'] - lat0)
    # Sorting by lap progress repairs the wrap at the start/finish line.
    points.sort(key=lambda p: p['pct'])
    # Collapse exact duplicate progress values to avoid zero-width interpolation intervals.
    dedup = []
    for p in points:
        if dedup and abs(p['pct'] - dedup[-1]['pct']) < 1e-10:
            old = dedup[-1]
            old['x'] = (old['x'] + p['x']) / 2
            old['y'] = (old['y'] + p['y']) / 2
        else:
            dedup.append(p.copy())
    return data, dedup, skipped


def interp_periodic(points, pct):
    """Interpolate x/y against cyclic lap progress, including the seam interval."""
    n = len(points)
    # binary search over sorted pct values
    lo, hi = 0, n
    while lo < hi:
        mid = (lo + hi) // 2
        if points[mid]['pct'] <= pct:
            lo = mid + 1
        else:
            hi = mid
    i = lo - 1
    if i < 0:
        a, b = points[-1], points[0]
        pa, pb = a['pct'] - 1.0, b['pct']
        if pct > b['pct']:
            pct -= 1.0
    elif i >= n - 1:
        a, b = points[-1], points[0]
        pa, pb = a['pct'], b['pct'] + 1.0
    else:
        a, b = points[i], points[i + 1]
        pa, pb = a['pct'], b['pct']
    if pb <= pa:
        return a['x'], a['y']
    t = (pct - pa) / (pb - pa)
    return a['x'] + t * (b['x'] - a['x']), a['y'] + t * (b['y'] - a['y'])


def dist(a, b):
    return math.hypot(a[0] - b[0], a[1] - b[1])


def percentile(values, q):
    if not values:
        return None
    s = sorted(values)
    pos = (len(s) - 1) * q
    lo = int(pos); hi = min(lo + 1, len(s) - 1)
    return s[lo] + (s[hi] - s[lo]) * (pos - lo)


def smooth(points, window):
    """Moving-average smoothing without wrapping across the start/finish seam.

    The input may contain a real GPS gap at lap closure. Treating the point list
    as cyclic smears that gap into otherwise valid points and creates a visible
    spike/flat artifact on the map.
    """
    if window <= 1:
        return points
    if window % 2 == 0:
        window += 1
    r = window // 2
    out = []
    n = len(points)
    for i in range(n):
        lo = max(0, i - r)
        hi = min(n, i + r + 1)
        near = points[lo:hi]
        out.append((statistics.mean(p[0] for p in near), statistics.mean(p[1] for p in near)))
    return out


def path_d(points, project, close=False):
    coords = [project(x, y) for x, y in points]
    if not coords:
        return ''
    d = f'M {coords[0][0]:.2f},{coords[0][1]:.2f} '
    d += ' '.join(f'L {x:.2f},{y:.2f}' for x, y in coords[1:])
    return d + (' Z' if close else '')


def main():
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument('--lap1', required=True, type=Path, help='JSON de la primera vuelta')
    ap.add_argument('--lap2', required=True, type=Path, help='JSON de la segunda vuelta')
    ap.add_argument('--out', type=Path, default=Path('track-reconstruction'), help='Prefijo de salida (sin extensión)')
    ap.add_argument('--samples', type=int, default=3000, help='Puntos de muestreo de la reconstrucción (mínimo 200)')
    ap.add_argument('--smooth', type=int, default=5, help='Ventana de suavizado impar; 1 desactiva')
    ap.add_argument('--width', type=int, default=1600, help='Ancho del SVG en píxeles')
    ap.add_argument('--padding', type=int, default=40, help='Margen visual en píxeles')
    args = ap.parse_args()
    if args.samples < 200:
        ap.error('--samples debe ser al menos 200')
    if args.smooth < 1:
        ap.error('--smooth debe ser >= 1')

    d1, p1, skip1 = load_points(args.lap1)
    d2, p2, skip2 = load_points(args.lap2)
    # Reproject both laps into a common local coordinate system based on both datasets.
    all_lat = [p['lat'] for p in p1 + p2]
    all_lon = [p['lon'] for p in p1 + p2]
    lat0, lon0 = statistics.mean(all_lat), statistics.mean(all_lon)
    cos_lat = math.cos(math.radians(lat0))
    for points in (p1, p2):
        for p in points:
            p['x'] = EARTH_RADIUS_M * math.radians(p['lon'] - lon0) * cos_lat
            p['y'] = EARTH_RADIUS_M * math.radians(p['lat'] - lat0)

    # The seam diagnostic uses the actual first/last valid samples, not the interpolated seam.
    seam1 = dist((p1[0]['x'], p1[0]['y']), (p1[-1]['x'], p1[-1]['y']))
    seam2 = dist((p2[0]['x'], p2[0]['y']), (p2[-1]['x'], p2[-1]['y']))
    n = args.samples
    pct_grid = [(i + 0.5) / n for i in range(n)]
    a = [interp_periodic(p1, pct) for pct in pct_grid]
    b = [interp_periodic(p2, pct) for pct in pct_grid]
    center = [((x1+x2)/2, (y1+y2)/2) for (x1,y1),(x2,y2) in zip(a,b)]
    center = smooth(center, args.smooth)
    sep = [dist(x, y) for x, y in zip(a, b)]

    # Keep the two source traces as separate lines; do not falsely close the SVG paths.
    source1 = [(p['x'], p['y']) for p in p1]
    source2 = [(p['x'], p['y']) for p in p2]
    all_xy = source1 + source2 + center
    minx = min(x for x, y in all_xy); maxx = max(x for x, y in all_xy)
    miny = min(y for x, y in all_xy); maxy = max(y for x, y in all_xy)
    world_w = max(maxx-minx, 1.0); world_h = max(maxy-miny, 1.0)
    height = max(400, int((args.width - 2*args.padding) * world_h / world_w + 2*args.padding))
    scale = min((args.width-2*args.padding)/world_w, (height-2*args.padding)/world_h)
    used_w, used_h = world_w*scale, world_h*scale
    offx = (args.width-used_w)/2; offy = (height-used_h)/2
    def project(x,y):
        return offx+(x-minx)*scale, height-(offy+(y-miny)*scale)

    svg = [f'<svg xmlns="http://www.w3.org/2000/svg" width="{args.width}" height="{height}" viewBox="0 0 {args.width} {height}">',
           '<rect width="100%" height="100%" fill="#10151d"/>',
           '<g fill="none" stroke-linecap="round" stroke-linejoin="round">',
           f'<path d="{path_d(source1, project)}" stroke="#4da3ff" stroke-width="2.0" opacity="0.82"/>',
           f'<path d="{path_d(source2, project)}" stroke="#ffb454" stroke-width="2.0" opacity="0.82"/>',
           f'<path d="{path_d(center, project)}" stroke="#f4f7fb" stroke-width="2.4"/>',
           '</g>',
           '<g font-family="Arial,sans-serif" fill="#e8edf4">',
           '<text x="24" y="30" font-size="18" font-weight="bold">iRTM · Reconstrucción preliminar de pista</text>',
           '<text x="24" y="54" font-size="13" fill="#aeb8c7">Azul: vuelta 1 · Naranja: vuelta 2 · Blanco: punto medio estimado</text>',
           f'<text x="24" y="{height-18}" font-size="12" fill="#aeb8c7">Separación entre trazadas: mediana {statistics.median(sep):.2f} m · P10 {percentile(sep,.1):.2f} m · P90 {percentile(sep,.9):.2f} m</text>',
           '</g>', '</svg>']
    svg_path = args.out.with_suffix('.svg')
    report_path = args.out.with_suffix('.report.json')
    svg_path.parent.mkdir(parents=True, exist_ok=True)
    svg_path.write_text('\n'.join(svg), encoding='utf-8')
    report = {
        'converter_version': '0.1.0',
        'inputs': [str(args.lap1), str(args.lap2)],
        'lap_metadata': [
            {'lap': d1.get('lap'), 'candidate_lap': d1.get('candidate_lap'), 'lap_time_s': d1.get('lap_time'), 'valid_gps_points': len(p1), 'skipped_samples': skip1},
            {'lap': d2.get('lap'), 'candidate_lap': d2.get('candidate_lap'), 'lap_time_s': d2.get('lap_time'), 'valid_gps_points': len(p2), 'skipped_samples': skip2},
        ],
        'projection': 'local equirectangular (meters)',
        'sample_count': n,
        'smoothing_window': args.smooth,
        'separation_m': {'min': min(sep), 'p10': percentile(sep,.1), 'median': statistics.median(sep), 'p90': percentile(sep,.9), 'max': max(sep)},
        'raw_progress_range': [
            {'min': min(p['pct'] for p in p1), 'max': max(p['pct'] for p in p1)},
            {'min': min(p['pct'] for p in p2), 'max': max(p['pct'] for p in p2)},
        ],
        'seam_chord_m': {'lap1_sorted_end_to_start': seam1, 'lap2_sorted_end_to_start': seam2},
        'warnings': [
            'El punto medio de dos trazadas no equivale necesariamente al centro físico de la pista.',
            'La interpolación cíclica puede atravesar un hueco de GPS en la línea de meta; revisar visualmente el cierre.',
            'No se ha inferido el borde real ni el ancho reglamentario de la pista.'
        ]
    }
    report_path.write_text(json.dumps(report, indent=2, ensure_ascii=False), encoding='utf-8')
    print(f'SVG: {svg_path.resolve()}')
    print(f'Informe: {report_path.resolve()}')
    print(f'Puntos válidos: vuelta 1={len(p1)} (omitidos={skip1}), vuelta 2={len(p2)} (omitidos={skip2})')
    print(f'Separación: mediana={statistics.median(sep):.2f} m; P10={percentile(sep,.1):.2f} m; P90={percentile(sep,.9):.2f} m')
    print(f'Cuerda entre extremos ordenados por progreso: vuelta 1={seam1:.2f} m; vuelta 2={seam2:.2f} m')

if __name__ == '__main__':
    try:
        main()
    except (OSError, ValueError, json.JSONDecodeError) as exc:
        print(f'ERROR: {exc}', file=sys.stderr)
        sys.exit(2)
