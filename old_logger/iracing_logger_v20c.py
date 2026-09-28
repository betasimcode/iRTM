# ======================================================
# IRACING LOGGER - V25 PRECISION SECTORS & STABLE RAW
# ======================================================

import irsdk
import yaml
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
current_status = "Esperando iRacing"
current_user = "No driver"
session_fastest = None
tray_icon = None
driver_iracing_id = None
last_sent_status = None
last_sent_time = 0
STATUS_HEARTBEAT = 10

# =========================
# RESOURCE PATH & HELPERS
# =========================

def resource_path(relative_path):
    try: base_path = sys._MEIPASS
    except Exception: base_path = os.path.abspath(".")
    return os.path.join(base_path, relative_path)

def current_session(ir):
    try: return ir['SessionInfo']['Sessions'][ir['SessionNum']]['SessionType']
    except: return "Unknown"

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

# =========================
# STATUS & TRAY (Tus funciones originales)
# =========================

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

# =========================
# CONTEXT HELPERS
# =========================

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

def get_tyre_compound(ir):
    try:
        tires = ir['DriverInfo'].get('DriverTires')
        return tires[0].get('TireCompoundType') if tires else None
    except: return None

def current_track(ir):
    try:
        w = ir['WeekendInfo']
        name, config = w.get('TrackDisplayName'), w.get('TrackConfigName')
        return f"{name} - {config}" if config else name
    except: return "Unknown"

def current_track_id(ir):
    try: return ir['WeekendInfo'].get("TrackID")
    except: return None

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
# WEATHER
# =========================

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


# =========================
# MAIN LOGGER (IMPLEMENTACIÓN SOLICITADA)
# =========================

def run_logger():
    global tray_icon, current_user, current_status
    ir = irsdk.IRSDK()
    tray_icon = setup_tray()

    while not ir.startup(): time.sleep(1)

    # Handshake inicial de sesión
    while True:
        ir.freeze_var_buffer_latest()
        if ir['DriverInfo'] and ir['WeekendInfo']:
            driver_data = current_driver_data(ir)
            if driver_data: break
        time.sleep(1)

    current_user, current_status = driver_data["name"], "Online"
    token = requests.post(f"{BASE_URL}/logger/token", json={"iracing_user_id": driver_data["iracing_user_id"]}).json().get("api_token")
    headers = {"X-API-TOKEN": token, "Content-Type": "application/json"}

    # Obtener Splits
    s_res = requests.get(f"{BASE_URL}/track-sectors/{current_track_id(ir)}", headers=headers)
    sector_splits = sorted([float(s) for s in s_res.json().get("sector_splits", [])]) if s_res.status_code == 200 else [0.33, 0.66]

    # Variables de Stint
    stint_active = False
    laps_buffer, tyre_snapshots = [], []
    sector_index = 0
    sector_start_time = None
    last_lap_dist = 0
    current_lap_sectors = []
    last_lap_time_value = None
    prev_fuel = None
    lap_was_in_pit = False

    print(f"🚀 Logger V25 Activo en {current_track(ir)}")

    while True:
        try:
            ir.freeze_var_buffer_latest()
            if not ir.is_connected: restart_app("iRacing cerrado")

            is_on_track = ir_safe(ir, 'IsOnTrack')
            lap_dist = ir_safe(ir, 'LapDistPct')
            session_time = ir_safe(ir, 'SessionTime')
            lap_time = clean_number(ir_safe(ir, 'LapLastLapTime'))
            lap_completed = ir_safe(ir, 'LapCompleted')
            fuel = clean_number(ir_safe(ir, 'FuelLevel'))

            # DETECCIÓN DE INICIO
            if not stint_active and is_on_track and ir_safe(ir, 'Speed') * 3.6 > 30:
                print("🟢 Stint Iniciado")
                stint_active = True
                laps_buffer, tyre_snapshots = [], []
                prev_fuel = fuel
                stint_start_time = ir_safe(ir, "SessionTimeOfDay")
                sector_start_time = session_time
                last_lap_time_value = lap_time # Sincronizar con el valor actual de iRacing

            if stint_active:
                if ir_safe(ir, 'OnPitRoad'): lap_was_in_pit = True

                # --- LÓGICA DE SECTORES ---
                if is_on_track and not ir_safe(ir, 'OnPitRoad'):
                    if sector_index < len(sector_splits):
                        split_pct = sector_splits[sector_index]
                        if last_lap_dist < split_pct <= lap_dist:
                            s_time = session_time - sector_start_time
                            if s_time > 1.0: # Evitar falsos positivos
                                current_lap_sectors.append(round(s_time, 3))
                                print(f"📍 Sector {sector_index+1}: {s_time:.3f}")
                                sector_index += 1
                                sector_start_time = session_time

                # --- LÓGICA DE META (TRIGGER POR CAMBIO DE TIEMPO) ---
                if lap_time is not None and lap_time != last_lap_time_value and lap_time > 0:
                    # Cerrar el último sector por diferencia exacta
                    total_before = sum(current_lap_sectors)
                    final_sector = round(lap_time - total_before, 3)
                    current_lap_sectors.append(final_sector)

                    # Registrar Vuelta
                    laps_buffer.append({
                        "lap": lap_completed, # iRacing ya lo incrementó al cruzar
                        "lap_time": lap_time,
                        "fuel": fuel,
                        "fuel_used": round(prev_fuel - fuel, 3) if prev_fuel else 0,
                        "is_pit_lap": lap_was_in_pit,
                        "sectors": current_lap_sectors.copy(),
                        "timestamp": datetime.now(UTC).isoformat(),
                        **get_weather(ir)
                    })

                    tyre_snapshots.append({
                        "lap_number": lap_completed,
                        "tyre_compound": get_tyre_compound(ir),
                        "wear_fl": clean_number(ir_safe(ir, 'LFwearM')),
                        "wear_fr": clean_number(ir_safe(ir, 'RFwearM')),
                        "wear_rl": clean_number(ir_safe(ir, 'LRwearM')),
                        "wear_rr": clean_number(ir_safe(ir, 'RRwearM'))
                    })

                    print(f"🏁 V{lap_completed}: {lap_time:.3f} | Fuel Used: {round(prev_fuel-fuel, 2)}")

                    # Reset para la nueva vuelta
                    last_lap_time_value = lap_time
                    current_lap_sectors = []
                    sector_index = 0
                    sector_start_time = session_time
                    prev_fuel = fuel
                    lap_was_in_pit = False

                last_lap_dist = lap_dist

                # --- FIN DE STINT ---
                if not is_on_track:
                    print("🔴 Fin de stint")
                    if len(laps_buffer) >= MIN_VALID_LAPS:
                        f_lap, f_driver = get_class_fastest_lap(ir)
                        payload = {
                            "driver": driver_data,
                            "car": current_car(ir),
                            "track": current_track(ir),
                            "track_id": current_track_id(ir),
                            "session": {
                                "subsession_id": ir['WeekendInfo'].get("SubSessionID"),
                                "sof": ir['WeekendInfo'].get("StrengthOfField")
                            },
                            "stint_meta": {
                                "server_time_in": stint_start_time,
                                "server_time_out": ir_safe(ir, "SessionTimeOfDay"),
                                "session_fastest_lap": f_lap,
                                "session_fastest_driver": f_driver
                            },
                            "laps": laps_buffer,
                            "tyre_snapshots": tyre_snapshots
                        }
                        requests.post(API_URL, json=payload, headers=headers, timeout=15)
                    stint_active = False

            time.sleep(0.02) # 50Hz es suficiente y estable

        except Exception as e:
            print("💥 ERROR:", e); time.sleep(1)

if __name__ == "__main__":
    run_logger()
