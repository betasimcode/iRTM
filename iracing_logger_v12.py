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
STATUS_HEARTBEAT = 10  # segundos


# =========================
# RESOURCE PATH (IMPORTANTE PARA EXE)
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

def clean_number(value):
    try:
        v = float(value)
        if v != v or v in (float("inf"), float("-inf")):
            return None
        return v
    except:
        return None


def clean_temp(value):
    try:
        if isinstance(value, str):
            return float(value.replace(" C", "").strip())
        return float(value)
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
# STATUS API
# =========================

def detect_status(ir):

    # iRacing cerrado
    if not ir.is_connected:
        return "Esperando iRacing"

    is_on_track = ir_safe(ir, "IsOnTrack")

    if is_on_track:
        return "En pista"

    return "Online"

def send_status(token, status):

    try:
        response = requests.post(
            f"{BASE_URL}/logger/status",
            headers={
                "X-API-TOKEN": str(token),
                "Content-Type": "application/json"
            },
            json={
                "current_status": status
            },
            timeout=5
        )

    except Exception as e:
        print("❌ Excepción status:", e)

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
# CONTEXT HELPERS
# =========================

def current_driver_data(ir):
    try:
        d = ir['DriverInfo']
        idx = d['DriverCarIdx']
        drivers = d['Drivers']

        if idx is None or not drivers or idx >= len(drivers):
            return None

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
        drivers = d['Drivers']

        if idx is None or not drivers or idx >= len(drivers):
            return None

        driver = drivers[idx]

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
        if config:
            return f"{name} - {config}"
        return name
    except:
        return "Unknown"


def current_session(ir):
    try:
        return ir['SessionInfo']['Sessions'][ir['SessionNum']]['SessionType']
    except:
        return "Unknown"


def get_track_length_km(ir):
    try:
        raw = ir['WeekendInfo']['TrackLength']
        if not raw:
            return None

        raw = raw.strip().lower()

        if "km" in raw:
            return float(raw.replace("km", "").strip())

        if "mi" in raw:
            return float(raw.replace("mi", "").strip()) * 1.60934
    except:
        return None

    return None


def get_tyre_compound(ir):
    try:
        d = ir['DriverInfo']
        tires = d.get('DriverTires')

        if tires and isinstance(tires, list) and len(tires) > 0:
            return tires[0].get('TireCompoundType')

        return None
    except:
        return None


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
        return None

def get_weather(ir):

    return {
        "humidity": clean_number(ir_safe(ir, 'RelativeHumidity')),
        "wind_speed": clean_number(ir_safe(ir, 'WindVel')),
        "wind_dir": clean_number(ir_safe(ir, 'WindDir')),
        "sky": ir_safe(ir, 'Skies'),
        "track_state": ir_safe(ir, 'TrackWetness')
    }

def send_telemetry(payload, headers):
    try:
        response = requests.post(API_URL, json=payload, headers=headers, timeout=15)

        if response.status_code == 200:
            print("📤 Stint enviado correctamente")
            return True

        print(f"❌ HTTP {response.status_code}")
        return False

    except Exception as e:
        print("❌ Excepción enviando:", e)
        return False


# =========================
# MAIN LOGGER
# =========================

def run_logger():

    global tray_icon, current_user, current_status

    ir = irsdk.IRSDK()
    last_subsession_id = None

    tray_icon = setup_tray()
    current_status = "Esperando iRacing"
    print("⏳ iRacing cerrado → esperando......")

    while not ir.startup():

        time.sleep(1)

    print("✅ SDK iniciado")

    while True:
        ir.freeze_var_buffer_latest()

        if not ir.is_connected:

            # iRacing cerrado
            if not ir.is_connected:

                if current_status != "Esperando iRacing":
                    print("⏳ iRacing cerrado → esperando...")
                    current_user = driver_data["name"]
                    current_status = "Online"
                    update_tooltip()
                    current_status = "Esperando"
                    update_tooltip()


                time.sleep(1)
                continue

            # SDK glitch → reiniciar logger
            restart_app("SDK desconectado en loop principal")



        if ir['DriverInfo'] and ir['WeekendInfo']:
            driver_data = current_driver_data(ir)
            if driver_data:
                break

        time.sleep(1)

    current_user = driver_data["name"]
    current_status = "Online"
    update_tooltip()

    global driver_iracing_id
    driver_iracing_id = driver_data["iracing_user_id"]

    print("👤 Driver:", driver_data["name"])

    token = fetch_api_token(driver_data["iracing_user_id"])
    if not token:
        return

    headers = {
        "X-API-TOKEN": token,
        "Content-Type": "application/json"
    }



    stint_active = False
    laps_buffer = []
    tyre_snapshots = []
    previous_fuel = None
    last_lap_time_value = None
    lap_was_in_pit = False

    print("🚀 Logger activo\n")

    while True:

        try:
            ir.freeze_var_buffer_latest()

            new_status = detect_status(ir)

            if new_status != current_status:
                current_status = new_status
                print("STATUS →", current_status)

            if driver_data:
                send_status(token, current_status)

            if not ir.is_connected:
                restart_app("SDK desconectado en loop principal")

            weekend = ir_safe(ir, 'WeekendInfo')
            if not weekend:
                time.sleep(0.2)
                continue

            current_subsession = weekend.get('SubSessionID')
            if last_subsession_id is None:
                last_subsession_id = current_subsession
            elif current_subsession and current_subsession != last_subsession_id:
                restart_app("Cambio de SubSessionID")

            speed_raw = ir_safe(ir, 'Speed')
            is_on_track = ir_safe(ir, 'IsOnTrack')
            fuel = clean_number(ir_safe(ir, 'FuelLevel'))
            lap_time = clean_number(ir_safe(ir, 'LapLastLapTime'))
            lap_completed = ir_safe(ir, 'LapCompleted')
            on_pit_road = ir_safe(ir, 'OnPitRoad')

            if not isinstance(speed_raw, (int, float)):
                time.sleep(0.1)
                continue

            speed = speed_raw * 3.6

            if on_pit_road:
                lap_was_in_pit = True

            # INICIO STINT
            if not stint_active and is_on_track and speed > 10:
                print("🟢 Inicio de stint")
                current_status = "En pista"
                update_tooltip()

                stint_active = True
                laps_buffer = []
                tyre_snapshots = []
                previous_fuel = fuel
                last_lap_time_value = None
                lap_was_in_pit = False

            # NUEVA VUELTA RAW
            if stint_active and is_on_track and lap_time is not None:

                if last_lap_time_value is None:
                    last_lap_time_value = lap_time

                elif lap_time != last_lap_time_value:

                    weather = get_weather(ir)

                    fuel_used = None
                    if previous_fuel is not None and fuel is not None:
                        fuel_used = round(previous_fuel - fuel, 3)
                        if fuel_used < 0:
                            fuel_used = 0

                    print(f"⏱ V{lap_completed} | {lap_time:.3f}s")
                    print("WEATHER:", weather)
                    laps_buffer.append({
                        "lap": lap_completed,
                        "lap_time": lap_time,
                        "fuel": fuel,
                        "fuel_used": fuel_used,
                        "is_pit_lap": lap_was_in_pit,
                        "humidity": weather["humidity"],
                        "wind_speed": weather["wind_speed"],
                        "wind_dir": weather["wind_dir"],
                        "sky": weather["sky"],
                        "track_state": weather["track_state"],
                        "track_temp": clean_number(clean_temp(weekend.get('TrackSurfaceTemp'))),
                        "air_temp": clean_number(clean_temp(weekend.get('TrackAirTemp'))),
                        "timestamp": datetime.now(UTC).isoformat()
                    })

                    tyre_snapshots.append({
                        "lap_number": lap_completed,
                        "tyre_compound": get_tyre_compound(ir),
                        "wear_fl": clean_number(ir_safe(ir, 'LFwearM')),
                        "wear_fr": clean_number(ir_safe(ir, 'RFwearM')),
                        "wear_rl": clean_number(ir_safe(ir, 'LRwearM')),
                        "wear_rr": clean_number(ir_safe(ir, 'RRwearM'))
                    })

                    previous_fuel = fuel
                    lap_was_in_pit = False
                    last_lap_time_value = lap_time

            # FIN STINT
            if stint_active and not is_on_track:
                print("🔴 Fin de stint")
                current_status = "Online"
                update_tooltip()


                if len(laps_buffer) >= MIN_VALID_LAPS:

                    driver_info = ir_safe(ir, 'DriverInfo')
                    tank_capacity = None
                    if driver_info:
                        tank_capacity = clean_number(
                            driver_info.get('DriverCarFuelMaxLtr')
                        )

                    payload = {
                        "driver": driver_data,
                        "car": current_car(ir),
                        "track": current_track(ir),
                        "session_type": current_session(ir),
                        "track_length": get_track_length_km(ir),
                        "tank_capacity": tank_capacity,
                        "laps": laps_buffer,
                        "tyre_snapshots": tyre_snapshots
                    }

                    send_telemetry(payload, headers)

                stint_active = False
                laps_buffer = []
                tyre_snapshots = []
                previous_fuel = None
                last_lap_time_value = None
                lap_was_in_pit = False

            time.sleep(0.1)

        except Exception as e:
            print("💥 ERROR:", e)

            # STATUS UPDATE (NO INTERFIERE CON TELEMETRÍA)

        new_status = detect_status(ir)

        if new_status != current_status:
            current_status = new_status
            print("STATUS →", current_status)

        if driver_data:
            send_status(token, current_status)
            time.sleep(1)



if __name__ == "__main__":
    run_logger()
