# ============================================
# IRACING LOGGER - STABLE RAW LAPTIMER VERSION
# ============================================

import irsdk
import yaml
import time
import os
import sys
import threading
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

def watch_ibt_folder(token):
    # Esta función correrá en un hilo separado
    # 1. Mira si hay archivos nuevos en Documents/iRacing/telemetry
    # 2. Si el archivo está cerrado (iRacing dejó de escribir), lo procesa.
    # 3. Envía el Setup y las vueltas precisas a la DB.
    pass

def restart_app(reason="Unknown"):
    print(f"\n🔄 Reiniciando logger → {reason}\n")
    python = sys.executable
    os.execv(python, [python] + sys.argv)

# =========================
# STATUS API
# =========================

def detect_status(ir):

    if not ir.is_connected:
        return "Esperando iRacing"

    is_on_track = ir_safe(ir, "IsOnTrack")

    if is_on_track:
        return "En pista"

    return "Online"


def send_status(token, status):

    global last_sent_status, last_sent_time

    now = time.time()

    if status != last_sent_status or now - last_sent_time > STATUS_HEARTBEAT:

        try:

            requests.post(
                f"{BASE_URL}/logger/status",
                headers={
                    "X-API-TOKEN": str(token),
                    "Content-Type": "application/json"
                },
                json={"current_status": status},
                timeout=5
            )

            last_sent_status = status
            last_sent_time = now

        except Exception as e:
            print("❌ Status error:", e)


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

    print("🔴 Logger cerrado manualmente")

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
# CONTEXT HELPERS
# =========================


def get_tyre_compound(ir):
    try:
        d = ir['DriverInfo']
        tires = d.get('DriverTires')

        if tires and isinstance(tires, list) and len(tires) > 0:
            return tires[0].get('TireCompoundType')

        return None
    except:
        return None


def wait_for_fastest_update(ir, timeout=2.0):

    try:
        start_time = time.time()
        initial = list(ir['CarIdxBestLapTime'])

        while time.time() - start_time < timeout:

            ir.freeze_var_buffer_latest()
            current = list(ir['CarIdxBestLapTime'])

            # 🔥 detectar cambio real
            if current != initial:
                return True

            time.sleep(0.05)

    except Exception as e:
        print("WAIT FASTEST ERROR:", e)

    return False

def update_live_fastest(ir, current_fastest, current_driver):

    new_fastest, new_driver = get_class_fastest_lap(ir)

    if not new_fastest:
        return current_fastest, current_driver

    # 🔥 primera vez
    if current_fastest is None:
        print(f"🏁 FASTEST INIT: {new_fastest:.3f} ({new_driver})")
        return new_fastest, new_driver

    # 🔥 mejora detectada
    if new_fastest < current_fastest:
        print(f"🚨 NEW FASTEST: {new_fastest:.3f} ({new_driver})")
        return new_fastest, new_driver

    return current_fastest, current_driver

def get_class_fastest_lap(ir):

    try:
        drivers = ir['DriverInfo']['Drivers']
        player_idx = ir['DriverInfo']['DriverCarIdx']
        car_best_laps = ir['CarIdxBestLapTime']

        if not drivers or player_idx is None:
            return None, None

        player_class = drivers[player_idx].get("CarClassShortName")

        best_time = None
        best_driver = None

        for i, d in enumerate(drivers):

            if i >= len(car_best_laps):
                continue

            lap = car_best_laps[i]

            if not lap or lap <= 0:
                continue

            # 🔥 MÁS FIABLE
            if d.get("CarClassShortName") != player_class:
                continue

            if best_time is None or lap < best_time:
                best_time = lap
                best_driver = d.get("UserName")

        return best_time, best_driver

    except Exception as e:
        print("FASTEST ERROR:", e)
        return None, None

def detect_session_change(ir):

    try:

        weekend = ir['WeekendInfo']
        driver = ir['DriverInfo']

        track_id = weekend.get("TrackID")
        subsession_id = weekend.get("SubSessionID")

        drivers = driver.get("Drivers")
        idx = driver.get("DriverCarIdx")

        car_path = None
        if drivers and idx is not None:
            car_path = drivers[idx].get("CarPath")

        return track_id, subsession_id, car_path

    except:
        return None, None, None

def process_stint_from_ibt(filepath, token):
    # 1. ibt = irsdk.IBT(); ibt.open(filepath)
    # 2. Extraer ibt.session_info_str -> Aquí está el SETUP (YAML)
    # 3. Recorrer los samples para calcular sectores exactos
    # 4. Enviar payload masivo a /telemetry/stint-batch

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
        restart_app("Restart token")
        return None

def get_driver_auth(ir):
    # 1. Obtiene DriverCarIdx y de ahí el UserID
    # 2. Llama a fetch_api_token(user_id)
    # 3. Si no hay token, el logger entra en modo "standby" (No registra nada)
    pass

# =========================
# MAIN LOGGER
# =========================

def run_logger():

    global tray_icon, current_user, current_status

    # Cada 60 segundos dentro del bucle principal:
    if time.time() - last_weather_send > 60:
        weather = get_weather(ir)
        send_live_weather(token, weather)
