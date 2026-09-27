# ============================================
# IRACING LOGGER - STABLE RAW LAPTIMER VERSION
# ============================================

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
current_status = "Esperando iRacing"
current_user = "No driver"
tray_icon = None
driver_iracing_id = None
last_sent_status = None
last_sent_time = 0
STATUS_HEARTBEAT = 10


# =========================
# RESOURCE PATH
# =========================

def resource_path(relative_path):
    try:
        base_path = sys._MEIPASS
    except Exception:
        base_path = os.path.abspath(".")
    return os.path.join(base_path, relative_path)


# =========================
# HELPERS
# =========================

def current_session(ir):
    try:
        return ir['SessionInfo']['Sessions'][ir['SessionNum']]['SessionType']
    except:
        return "Unknown"


def clean_number(value):
    try:
        v = float(value)
        if v != v or v in (float("inf"), float("-inf")):
            return None
        return v
    except:
        return None


def ir_safe(ir, key):
    try:
        return ir[key]
    except:
        return None


def restart_app(reason="Unknown"):
    print(f"\n🔄 Reiniciando logger → {reason}\n")
    python = sys.executable
    os.execv(python, [python] + sys.argv)


# =========================
# TRAY
# =========================

def setup_tray():
    image = Image.open(resource_path("icon.ico"))

    menu = pystray.Menu(
        pystray.MenuItem("Forzar Restart", lambda i, x: restart_app("Forzado desde menú")),
        pystray.MenuItem("Cerrar", lambda i, x: os._exit(0))
    )

    icon = pystray.Icon(APP_NAME, image, APP_NAME, menu)
    icon.run_detached()
    return icon


# =========================
# DRIVER DATA
# =========================

def current_driver_data(ir):
    try:
        d = ir['DriverInfo']
        idx = d['DriverCarIdx']
        drivers = d['Drivers']
        driver = drivers[idx]

        return {
            "iracing_user_id": driver.get("UserID"),
            "name": driver.get("UserName")
        }
    except:
        return None


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
    except:
        return None


def current_track(ir):
    try:
        w = ir['WeekendInfo']
        name = w.get('TrackDisplayName')
        config = w.get('TrackConfigName')
        return f"{name} - {config}" if config else name
    except:
        return "Unknown"


def current_track_id(ir):
    try:
        return ir['WeekendInfo'].get("TrackID")
    except:
        return None


# =========================
# WEATHER
# =========================

def get_weather(ir):

    return {
        "humidity": clean_number(ir_safe(ir, 'RelativeHumidity')),
        "wind_speed": clean_number(ir_safe(ir, 'WindVel')),
        "wind_dir": clean_number(ir_safe(ir, 'WindDir')),
        "sky": ir_safe(ir, 'Skies'),
        "track_state": ir_safe(ir, 'TrackWetness')
    }


# =========================
# API
# =========================

def fetch_api_token(iracing_user_id):
    try:

        response = requests.post(
            f"{BASE_URL}/logger/token",
            json={"iracing_user_id": iracing_user_id},
            timeout=5
        )

        if response.status_code == 200:
            data = response.json()
            token = data.get("api_token")
            print("TOKEN RECIBIDO:", token)
            return token

    except Exception as e:
        print("❌ Excepción token:", e)

    return None


def fetch_sector_splits(track_id, headers):

    try:

        r = requests.get(
            f"{BASE_URL}/track-sectors/{track_id}",
            headers=headers,
            timeout=5
        )

        if r.status_code == 200:

            splits = r.json().get("sector_splits")

            parsed = [float(s) for s in splits]

            parsed = sorted(parsed)

            print("📍 Splits cargados:", parsed)

            return parsed

    except Exception as e:
        print("❌ Error cargando splits:", e)

    return None


def send_telemetry(payload, headers):

    try:

        r = requests.post(API_URL, json=payload, headers=headers, timeout=15)

        if r.status_code == 200:
            print("📤 Stint enviado correctamente")

    except Exception as e:
        print("❌ Excepción enviando:", e)


# =========================
# MAIN LOGGER
# =========================

def run_logger():

    global tray_icon

    ir = irsdk.IRSDK()

    tray_icon = setup_tray()

    print("⏳ iRacing cerrado → esperando......")

    while not ir.startup():
        time.sleep(1)

    print("✅ SDK iniciado")

    while True:

        ir.freeze_var_buffer_latest()

        if ir['DriverInfo'] and ir['WeekendInfo']:
            driver_data = current_driver_data(ir)
            if driver_data:
                break

        time.sleep(1)

    print("👤 Driver:", driver_data["name"])

    token = fetch_api_token(driver_data["iracing_user_id"])

    if not token:
        return

    headers = {
        "X-API-TOKEN": token,
        "Content-Type": "application/json"
    }

    sector_splits = fetch_sector_splits(current_track_id(ir), headers)

    if not sector_splits:
        sector_splits = [1/3, 2/3]

    sector_index = 0
    sector_start_time = None
    last_lap_dist = 0
    current_lap_sector_times = []

    stint_active = False
    laps_buffer = []

    previous_fuel = None
    last_lap_completed = None

    print("🚀 Logger activo\n")

    while True:

        try:

            ir.freeze_var_buffer_latest()

            lap_dist = ir_safe(ir, 'LapDistPct')
            session_time = ir_safe(ir, 'SessionTime')

            speed_raw = ir_safe(ir,'Speed')
            is_on_track = ir_safe(ir,'IsOnTrack')
            on_pit_road = ir_safe(ir,'OnPitRoad')
            fuel = clean_number(ir_safe(ir,'FuelLevel'))
            lap_time = clean_number(ir_safe(ir,'LapLastLapTime'))
            lap_completed = ir_safe(ir,'LapCompleted')
            if (
                stint_active
                and is_on_track
                and not on_pit_road
                and isinstance(lap_dist,(int,float))
            ):

                if sector_start_time is None:
                    sector_start_time = session_time

                if sector_index < len(sector_splits):

                    split = sector_splits[sector_index]

                    if last_lap_dist <= split <= lap_dist:

                        sector_time = session_time - sector_start_time

                        if sector_time > 1.5:

                            print(f"Sector {sector_index+1} -> {sector_time:.3f}s")

                            current_lap_sector_times.append(round(sector_time,3))

                            sector_start_time = session_time

                        sector_index += 1

                last_lap_dist = lap_dist

            if not isinstance(speed_raw,(int,float)):
                continue

            speed = speed_raw * 3.6

            if not stint_active and is_on_track and speed > 10:

                print("🟢 Inicio de stint")

                stint_active = True
                previous_fuel = fuel
                last_lap_completed = lap_completed

                sector_index = 0
                current_lap_sector_times = []
                sector_start_time = session_time
                last_lap_dist = lap_dist

            if stint_active and lap_completed != last_lap_completed:

                weather = get_weather(ir)

                fuel_used = None
                if previous_fuel and fuel:
                    fuel_used = round(previous_fuel - fuel,3)

                sector_sum = sum(current_lap_sector_times)

                final_sector = round(lap_time - sector_sum,3)

                if 0.5 < final_sector < lap_time:

                    print(f"Sector {len(current_lap_sector_times)+1} -> {final_sector:.3f}s")

                    current_lap_sector_times.append(final_sector)

                print(f"⏱ V{lap_completed} | {lap_time:.3f}s")

                laps_buffer.append({

                    "lap": lap_completed,
                    "lap_time": lap_time,
                    "sectors": current_lap_sector_times.copy(),
                    "fuel": fuel,
                    "fuel_used": fuel_used,
                    "timestamp": datetime.now(UTC).isoformat()
                })

                previous_fuel = fuel
                last_lap_completed = lap_completed

                current_lap_sector_times = []
                sector_index = 0
                sector_start_time = session_time

            # =========================
            # FIN DE STINT
            # =========================

            if stint_active and not is_on_track:

                print("🔴 Fin de stint")

                if len(laps_buffer) >= MIN_VALID_LAPS:

                    payload = {

                        "driver": driver_data,
                        "car": current_car(ir),

                        "track": current_track(ir),
                        "track_id": current_track_id(ir),

                        "session_type": current_session(ir),

                        "laps": laps_buffer
                    }

                    send_telemetry(payload, headers)

                stint_active = False
                laps_buffer = []


            time.sleep(0.05)

        except Exception as e:
            print("💥 ERROR:", e)


if __name__ == "__main__":
    run_logger()
