# ======================================================
# IRACING LOGGER - V29 (DEVELOPMENT FULL - NO SIMPLIFIED)
# ======================================================

import irsdk
import time
import os
import sys
import requests
from datetime import datetime, UTC
import pystray
from PIL import Image

BASE_URL = "http://localhost:8000/api"
API_URL = f"{BASE_URL}/telemetry/stint-batch"
MIN_VALID_LAPS = 2
APP_NAME = "iRacing Team Manager"
STATUS_HEARTBEAT = 10

current_status = "Esperando iRacing"
current_user = "No driver"
last_sent_status = None
last_sent_time = 0
tray_icon = None

# --- 1. HELPERS & TRAY SYSTEM ---
def resource_path(relative_path):
    try: base_path = sys._MEIPASS
    except Exception: base_path = os.path.abspath(".")
    return os.path.join(base_path, relative_path)

def setup_tray():
    try:
        image = Image.open(resource_path("icon.ico"))
        menu = pystray.Menu(
            pystray.MenuItem("Forzar Restart", lambda i, item: restart_app("Forzado")),
            pystray.MenuItem("Cerrar", lambda i, item: os._exit(0))
        )
        icon = pystray.Icon(APP_NAME, image, APP_NAME, menu)
        icon.run_detached()
        return icon
    except: return None

def clean_number(value):
    try:
        v = float(value)
        if v != v or v in (float("inf"), float("-inf")): return None
        return v
    except: return None

def ir_safe(ir, key):
    try: return ir[key]
    except: return None

def restart_app(reason="Unknown"):
    print(f"\n🔄 Reiniciando logger → {reason}\n")
    python = sys.executable
    os.execv(python, [python] + sys.argv)

# --- 2. STATUS & PING ---
def detect_status(ir):
    if not ir.is_connected: return "Esperando iRacing"
    return "En pista" if ir_safe(ir, "IsOnTrack") else "Online"

def send_status(token, status):
    global last_sent_status, last_sent_time
    now = time.time()
    if status != last_sent_status or now - last_sent_time > STATUS_HEARTBEAT:
        try:
            requests.post(f"{BASE_URL}/logger/status",
                          headers={"X-API-TOKEN": str(token), "Content-Type": "application/json"},
                          json={"current_status": status}, timeout=5)
            last_sent_status, last_sent_time = status, now
        except: pass

# --- 3. CONTEXT DATA FUNCTIONS ---
def current_driver_data(ir):
    try:
        d = ir['DriverInfo']
        idx = d['DriverCarIdx']
        driver = d['Drivers'][idx]
        return {"iracing_user_id": driver.get("UserID"), "name": driver.get("UserName")}
    except: return None

def current_car(ir):
    try:
        d = ir['DriverInfo']
        idx = d['DriverCarIdx']
        driver = d['Drivers'][idx]
        return {
            "car_name": driver.get("CarScreenName"),
            "car_path": driver.get("CarPath"),
            "car_class_id": driver.get("CarClassID"),
            "car_class_short_name": driver.get("CarClassShortName")
        }
    except: return None

def current_track(ir):
    try:
        w = ir['WeekendInfo']
        name, config = w.get('TrackDisplayName'), w.get('TrackConfigName')
        return f"{name} - {config}" if config else name
    except: return "Unknown"

def get_tyre_compound(ir):
    try:
        tires = ir['DriverInfo'].get('DriverTires')
        return tires[0].get('TireCompoundType') if tires else None
    except: return None

def get_weather(ir):
    return {
        "track_temp": clean_number(ir_safe(ir, 'TrackTemp')),
        "air_temp": clean_number(ir_safe(ir, 'AirTemp')),
        "humidity": clean_number(ir_safe(ir, 'RelativeHumidity')),
        "wind_speed": clean_number(ir_safe(ir, 'WindVel')),
        "wind_dir": clean_number(ir_safe(ir, 'WindDir')),
        "sky": ir_safe(ir, 'Skies'),
        "track_state": ir_safe(ir, 'TrackWetness')
    }

def get_class_fastest_lap(ir):
    try:
        drivers = ir['DriverInfo']['Drivers']
        player_idx = ir['DriverInfo']['DriverCarIdx']
        car_best_laps = ir['CarIdxBestLapTime']
        player_class = drivers[player_idx].get("CarClassShortName")
        best_time, best_driver = None, None
        for i, d in enumerate(drivers):
            if i < len(car_best_laps) and car_best_laps[i] > 0 and d.get("CarClassShortName") == player_class:
                if best_time is None or car_best_laps[i] < best_time:
                    best_time, best_driver = car_best_laps[i], d.get("UserName")
        return best_time, best_driver
    except: return None, None

# =========================
# MAIN EXECUTION
# =========================
def run_logger():
    global current_user, current_status, tray_icon
    ir = irsdk.IRSDK()

    tray_icon = setup_tray()

    while not ir.startup(): time.sleep(1)

    while True:
        ir.freeze_var_buffer_latest()
        if ir['DriverInfo'] and ir['WeekendInfo']:
            driver_data = current_driver_data(ir)
            if driver_data: break
        time.sleep(1)

    # Handshake & Token
    token_res = requests.post(f"{BASE_URL}/logger/token", json={"iracing_user_id": driver_data["iracing_user_id"]})
    token = token_res.json().get("api_token")
    headers = {"X-API-TOKEN": str(token), "Content-Type": "application/json"}

    # Track Splits
    track_id = ir['WeekendInfo'].get('TrackID')
    s_res = requests.get(f"{BASE_URL}/track-sectors/{track_id}", headers=headers)
    sector_splits = sorted([float(s) for s in s_res.json().get("sector_splits", [])]) if s_res.status_code == 200 else [0.33, 0.66]

    # Variables de control de tiempo (Precisión)
    last_session_time = ir['SessionTime']
    last_lap_dist = ir['LapDistPct']
    last_lap_time_value = ir['LapLastLapTime']

    stint_active = False
    laps_buffer, tyre_snapshots = [], []
    current_lap_sectors = []
    sector_index = 0
    sector_start_time = ir['SessionTime']
    prev_fuel = None
    lap_was_in_pit = False

    print(f"🚀 Logger V29 - {driver_data['name']} - Tray & Interpolación Activa")

    while True:
        try:
            ir.freeze_var_buffer_latest()
            if not ir.is_connected: restart_app("iRacing cerrado")

            session_time = ir['SessionTime']
            lap_dist = ir['LapDistPct']
            lap_time_sdk = clean_number(ir['LapLastLapTime'])
            is_on_track = ir_safe(ir, 'IsOnTrack')
            fuel = clean_number(ir['FuelLevel'])

            send_status(token, detect_status(ir))

            # Inicio de Stint
            if not stint_active and is_on_track and ir['Speed'] * 3.6 > 30:
                stint_active = True
                laps_buffer, tyre_snapshots = [], []
                prev_fuel = fuel
                stint_start_time = ir['SessionTimeOfDay']
                sector_start_time = session_time
                sector_index = 0
                current_lap_sectors = []

            if stint_active:
                if ir['OnPitRoad']: lap_was_in_pit = True

                # --- LÓGICA DE SECTORES (Interpolación) ---
                if lap_dist < last_lap_dist and last_lap_dist > 0.8:
                    ratio_meta = last_lap_dist / (last_lap_dist + (1.0 - lap_dist))
                    sector_start_time = last_session_time + ((session_time - last_session_time) * ratio_meta)
                    sector_index = 0
                    current_lap_sectors = []

                elif sector_index < len(sector_splits):
                    split_target = sector_splits[sector_index]
                    if last_lap_dist < split_target <= lap_dist:
                        ratio = (split_target - last_lap_dist) / (lap_dist - last_lap_dist) if (lap_dist - last_lap_dist) > 0 else 0
                        exact_time = last_session_time + ((session_time - last_session_time) * ratio)
                        s_time = exact_time - sector_start_time
                        current_lap_sectors.append(round(s_time, 3))
                        print(f"📍 S{sector_index+1}: {s_time:.3f}")
                        sector_start_time = exact_time
                        sector_index += 1

                # --- CIERRE DE VUELTA ---
                if lap_time_sdk is not None and lap_time_sdk != last_lap_time_value and lap_time_sdk > 0:
                    total_so_far = sum(current_lap_sectors)
                    current_lap_sectors.append(round(lap_time_sdk - total_so_far, 3))

                    laps_buffer.append({
                        "lap": ir['LapCompleted'],
                        "lap_time": lap_time_sdk,
                        "fuel": fuel,
                        "fuel_used": round(prev_fuel - fuel, 3) if prev_fuel else 0,
                        "is_pit_lap": lap_was_in_pit,
                        "sectors": current_lap_sectors.copy(),
                        "timestamp": datetime.now(UTC).isoformat(),
                        **get_weather(ir)
                    })

                    # 4 RUEDAS COMPLETAS
                    tyre_snapshots.append({
                        "lap_number": ir['LapCompleted'],
                        "tyre_compound": get_tyre_compound(ir),
                        "wear_fl": clean_number(ir['LFwearM']),
                        "wear_fr": clean_number(ir['RFwearM']),
                        "wear_rl": clean_number(ir['LRwearM']),
                        "wear_rr": clean_number(ir['RRwearM'])
                    })

                    print(f"🏁 V{ir['LapCompleted']}: {lap_time_sdk:.3f}")
                    last_lap_time_value, prev_fuel, lap_was_in_pit = lap_time_sdk, fuel, False

                # --- FIN DE STINT & ENVÍO ---
                if not is_on_track:
                    if len(laps_buffer) >= MIN_VALID_LAPS:
                        f_lap, f_driver = get_class_fastest_lap(ir)

                        payload = {
                            "driver": driver_data,
                            "car": current_car(ir),
                            "track": {"name": current_track(ir), "track_id": track_id},
                            "session": {
                                "subsession_id": ir['WeekendInfo'].get("SubSessionID"),
                                "session_id": ir['WeekendInfo'].get("SessionID"),
                                "sof": ir['WeekendInfo'].get("StrengthOfField"),
                                "session_type": ir['SessionInfo']['Sessions'][ir['SessionNum']]['SessionType'] if 'SessionInfo' in ir else "Unknown"
                            },
                            "stint_meta": {
                                "server_time_in": stint_start_time,
                                "server_time_out": ir['SessionTimeOfDay'],
                                "session_fastest_lap": f_lap,
                                "session_fastest_driver": f_driver
                            },
                            "laps": laps_buffer,
                            "tyre_snapshots": tyre_snapshots
                        }

                        try:
                            res = requests.post(API_URL, json=payload, headers=headers)
                            print(f"📤 Stint enviado exitosamente. Servidor: {res.status_code}")
                        except Exception as e:
                            print(f"❌ Error en envío: {e}")

                    stint_active = False
                    break

            last_lap_dist, last_session_time = lap_dist, session_time
            time.sleep(0.01)

        except Exception as e:
            print(f"💥 Error: {e}")
            time.sleep(1)

if __name__ == "__main__":
    run_logger()
