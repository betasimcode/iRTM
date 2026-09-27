import os
import glob
import re
import requests
import mysql.connector

# --- CONFIGURACIÓN ---
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'iracing_manager_mrt'
}
API_URL = "http://localhost:8000/api/tracks/sync"

def get_api_token():
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        cursor.execute("SELECT api_token FROM users LIMIT 1")
        row = cursor.fetchone()
        conn.close()
        return row[0] if row else None
    except Exception as e:
        print(f"❌ Error conectando a la DB para el token: {e}")
        return None

def safe_extract(pattern, text, default=""):
    """Extrae datos con regex sin romper el script si no encuentra nada"""
    match = re.search(pattern, text)
    return match.group(1).strip() if match else default

def scan_and_sync_tracks():
    token = get_api_token()
    if not token: return print("❌ No hay token en la DB.")

    path = os.path.expanduser("~/Documents/iRacing/telemetry/*.ibt")
    ibt_files = glob.glob(path)

    print(f"📂 Encontrados {len(ibt_files)} archivos para procesar...")

    headers = {"Authorization": f"Bearer {token}", "Content-Type": "application/json"}
    tracks_processed = set()

    for ibt_path in ibt_files:
        try:
            with open(ibt_path, 'rb') as f:
                # Leemos los primeros 250kb para asegurar que pillamos todo el YAML
                header_raw = f.read(250000).decode('latin-1', errors='ignore').replace('\x00', ' ')

            t_id = safe_extract(r'TrackID: (\d+)', header_raw)
            if not t_id or t_id in tracks_processed: continue

            track_name = safe_extract(r'TrackName: (.*)', header_raw)
            print(f"🔎 Procesando: {track_name} (ID: {t_id})...")

            # --- LONGITUD EN METROS ---
            # Buscamos algo como "5.79 km" o "5790 m"
            len_match = re.search(r'TrackLength: ([\d\.]+) (km|m)', header_raw)
            length_meters = 0
            if len_match:
                val = float(len_match.group(1))
                unit = len_match.group(2)
                length_meters = int(val * 1000) if unit == 'km' else int(val)

            # --- SECTORES ---
            block_start = header_raw.find("SplitTimeInfo:")
            block_end = header_raw.find("CarSetup:")
            if block_start != -1 and block_end != -1:
                block = header_raw[block_start:block_end]
                matches = re.findall(r'- SectorNum:\s+(\d+)[\s\S]*?SectorStartPct:\s+([\d\.]+)', block)
                sectors = sorted([{"sector_index": int(n)+1, "start_pct": float(p)} for n, p in matches], key=lambda x: x['start_pct'])
            else:
                sectors = []

            if not sectors:
                print(f"  ⚠️ No se encontraron sectores en {track_name}, saltando...")
                continue

            # 1. Buscamos la línea de TrackLength
            tl_match = re.search(r'TrackLength: ([\d\.]+) (km|m)', header_raw)
            if tl_match:
                val = float(tl_match.group(1))
                unit = tl_match.group(2)
                # Convertimos a metros (entero)
                meters = int(val * 1000) if unit == 'km' else int(val)
            else:
                meters = 0

            payload = {
                "track": {
                    "iracing_track_id": int(t_id),
                    "name": track_name,
                    "display_name": safe_extract(r'TrackDisplayName: (.*)', header_raw),
                    "variant": safe_extract(r'TrackConfigName: (.*)', header_raw),
                    "city": safe_extract(r'TrackCity: (.*)', header_raw),
                    "country": safe_extract(r'TrackCountry: (.*)', header_raw),
                    "region": safe_extract(r'TrackState: (.*)', header_raw),
                    "length_meters": meters # Enviamos el número limpio (ej: 5790)
                },
                "sectors": sectors
            }

            response = requests.post(API_URL, json=payload, headers=headers)

            if response.status_code in [200, 201]:
                print(f"   ✅ {track_name} ({length_meters}m) sincronizado.")
                tracks_processed.add(t_id)
            else:
                print(f"   ❌ Error API en {track_name}: Status {response.status_code}")

        except Exception as e:
            print(f"   ⚠️ Error crítico en {os.path.basename(ibt_path)}: {e}")

    print(f"\n✨ Sincronización terminada. {len(tracks_processed)} circuitos únicos añadidos.")

if __name__ == "__main__":
    scan_and_sync_tracks()
