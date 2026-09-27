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

def update_tooltip():
    global tray_icon
    if tray_icon:
        tray_icon.title = f"{APP_NAME}\n{current_user} → {current_status}"


def on_force_restart(icon, item):
    restart_app("Forzado desde menú")


def on_open_logs(icon, item):
    try:
        os.startfile("logger.log")
    except:
        pass


def on_exit(icon, item):
    icon.stop()
    os._exit(0)


def setup_tray():
    image = Image.open(resource_path("icon.ico"))

    menu = pystray.Menu(
        pystray.MenuItem("Forzar Restart", on_force_restart),
        pystray.MenuItem("Logs", on_open_logs),
        pystray.MenuItem("Cerrar", on_exit)
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
        "track_temp": clean_number(ir_safe(ir, 'TrackTemp')),
        "air_temp": clean_number(ir_safe(ir, 'AirTemp')),
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


        print("❌ Error token:", response.text)
        return None

    except Exception as e:
        print("❌ Excepción token:", e)
        restart_app("Token exception")

    return None

def detect_session_change(ir):

    try:

        weekend = ir['WeekendInfo']
        driver = ir['DriverInfo']

        track_id = weekend.get("TrackID")
        subsession_id = weekend.get("SubSessionID")

        drivers = driver.get("Drivers")
        idx = driver.get("DriverCarIdx")

        car_path = None

        if drivers and idx is not None and idx < len(drivers):
            car_path = drivers[idx].get("CarPath")

        return track_id, subsession_id, car_path

    except Exception as e:

        print("⚠️ detect_session_change error:", e)

        return None, None, None

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

        response = requests.post(
            API_URL,
            json=payload,
            headers=headers,
            timeout=15
        )

        if response.status_code == 200:
            print("📤 Stint enviado correctamente")
            return True

        print(f"❌ HTTP {response.status_code}")
        restart_app(f"API error {response.status_code}")

    except requests.exceptions.Timeout:
        restart_app("API timeout")

    except requests.exceptions.ConnectionError:
        restart_app("API connection error")

    except Exception as e:
        print("❌ Excepción enviando:", e)
        restart_app("Unknown telemetry error")

    return False

# =========================
# MAIN LOGGER
# =========================

def run_logger():

    global tray_icon

    ir = irsdk.IRSDK()
    tray_icon = setup_tray()

    print("⏳ Esperando iRacing...")

    while not ir.startup():
        time.sleep(1)

    print("✅ SDK iniciado")

    headers = {
        "Content-Type": "application/json"
    }

    sector_splits = [0.33, 0.66]

    sector_index = 0
    sector_start_time = None
    last_lap_dist = 0
    current_lap_sector_times = []

    stint_active = False
    laps_buffer = []

    previous_fuel = None
    last_lap_completed = None

    while True:

        try:

            ir.freeze_var_buffer_latest()

            if not ir.is_connected:
                restart_app("iRacing cerrado")

            lap_dist = ir_safe(ir, 'LapDistPct')
            session_time = ir_safe(ir, 'SessionTime')

            speed_raw = ir_safe(ir,'Speed')
            is_on_track = ir_safe(ir,'IsOnTrack')
            on_pit_road = ir_safe(ir,'OnPitRoad')

            fuel = clean_number(ir_safe(ir,'FuelLevel'))
            lap_time = clean_number(ir_safe(ir,'LapLastLapTime'))
            lap_completed = ir_safe(ir,'LapCompleted')

            # =========================
            # INICIO STINT
            # =========================

            if not stint_active and is_on_track and speed_raw:

                print("🟢 Inicio de stint")

                stint_active = True
                laps_buffer = []
                previous_fuel = fuel
                last_lap_completed = lap_completed

                sector_index = 0
                current_lap_sector_times = []
                sector_start_time = session_time
                last_lap_dist = lap_dist

            # =========================
            # SECTORES
            # =========================

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

            # =========================
            # VUELTA
            # =========================

            if (
                stint_active
                and is_on_track
                and lap_completed is not None
                and last_lap_completed is not None
                and lap_completed > last_lap_completed
                and lap_time is not None
                and lap_time > 0
            ):

                # evitar vuelta basura
                if not current_lap_sector_times:
                    last_lap_completed = lap_completed
                    continue

                weather = get_weather(ir)

                fuel_used = None
                if previous_fuel is not None and fuel is not None:
                    fuel_used = round(previous_fuel - fuel,3)

                sector_sum = sum(current_lap_sector_times)

                if sector_sum > lap_time:
                    current_lap_sector_times = []
                    sector_index = 0
                    sector_start_time = session_time
                    last_lap_completed = lap_completed
                    continue

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
                    "is_pit_lap": 0,

                    "track_temp": weather["track_temp"],
                    "air_temp": weather["air_temp"],
                    "humidity": weather["humidity"],

                    "wind_speed": weather["wind_speed"],
                    "wind_dir": weather["wind_dir"],

                    "sky": weather["sky"],
                    "track_state": weather["track_state"],

                    "timestamp": datetime.now(UTC).isoformat()
                })

                previous_fuel = fuel
                last_lap_completed = lap_completed

                current_lap_sector_times = []
                sector_index = 0
                sector_start_time = session_time

            # =========================
            # FIN STINT
            # =========================

            if stint_active and not is_on_track:

                print("🔴 Fin de stint")

                if len(laps_buffer) >= MIN_VALID_LAPS:

                    payload = {
                        "laps": laps_buffer
                    }

                    send_telemetry(payload, headers)

                stint_active = False
                laps_buffer = []

                sector_index = 0
                sector_start_time = None
                current_lap_sector_times = []

                last_lap_dist = 0
                last_lap_completed = None

            time.sleep(0.05)

        except Exception as e:
            print("💥 ERROR:", e)


if __name__ == "__main__":
    run_logger()
