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
        driver = drivers[idx]

        return {
            "car_name": driver.get("CarScreenName"),
            "car_path": driver.get("CarPath"),
            "car_class_id": driver.get("CarClassID"),
            "car_class_short_name": driver.get("CarClassShortName")
        }
    except:
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


def current_track_id(ir):
    try:
        return ir['WeekendInfo'].get("TrackID")
    except:
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
        if drivers and idx is not None:
            car_path = drivers[idx].get("CarPath")

        return track_id, subsession_id, car_path

    except:
        return None, None, None


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
        return None


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

    last_track_id = None
    last_subsession_id = None
    last_car_path = None

    tray_icon = setup_tray()
    current_status = "Esperando iRacing"
    print("⏳ iRacing cerrado → esperando......")

    while not ir.startup():
        time.sleep(1)

    print("✅ SDK iniciado")

    while True:

        ir.freeze_var_buffer_latest()

        track_id, subsession_id, car_path = detect_session_change(ir)

        if last_track_id is None:
            last_track_id = track_id
            last_subsession_id = subsession_id
            last_car_path = car_path

        if track_id and track_id != last_track_id:
            restart_app("Cambio de circuito detectado")

        if subsession_id and subsession_id != last_subsession_id:
            restart_app("Cambio de SubSession detectado")

        if car_path and car_path != last_car_path:
            restart_app("Cambio de coche detectado")

        last_track_id = track_id
        last_subsession_id = subsession_id
        last_car_path = car_path

        if ir['DriverInfo'] and ir['WeekendInfo']:
            driver_data = current_driver_data(ir)
            if driver_data:
                break

        time.sleep(1)

    current_user = driver_data["name"]
    current_status = "Online"
    update_tooltip()

    print("👤 Driver:", driver_data["name"])
    print(f"🏁 Track: {current_track(ir)}")
    print(f"🆔 Track ID: {current_track_id(ir)}")

    token = fetch_api_token(driver_data["iracing_user_id"])
    if not token:
        return

    headers = {
        "X-API-TOKEN": token,
        "Content-Type": "application/json"
    }

    sector_splits = [0.33, 0.66]

    sector_index = 0
    sector_start_time = None
    last_lap_dist = 0
    current_lap_sector_times = []

    stint_active = False
    laps_buffer = []

    tyre_snapshots = []
    previous_fuel = None
    last_lap_completed = None

    print("🚀 Logger activo\n")

    while True:

        try:

            ir.freeze_var_buffer_latest()
            if not ir.is_connected:
                restart_app("iRacing cerrado")
            lap_dist = ir_safe(ir,'LapDistPct')
            session_time = ir_safe(ir,'SessionTime')

# =================================================================================================
# SECTORES (CONTINUO)
# =================================================================================================

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

# =================================================================================================
# fin SECTORES (CONTINUO) -----------------------------------------------------------------------
# =================================================================================================
            # detectar nueva vuelta (reset sectores)
            if isinstance(lap_dist,(int,float)) and isinstance(last_lap_dist,(int,float)):
                if lap_dist < last_lap_dist:

                    sector_index = 0
                    current_lap_sector_times = []
                    sector_start_time = session_time

            if isinstance(lap_dist, (int,float)) and isinstance(last_lap_dist,(int,float)):

                if sector_start_time is None:
                    sector_start_time = session_time

                if lap_dist < last_lap_dist:
                    sector_index = 0
                    current_lap_sector_times = []
                    sector_start_time = session_time

                if sector_index < len(sector_splits):

                    split = sector_splits[sector_index]

                    if isinstance(split,(int,float)):

                        if last_lap_dist < split <= lap_dist:

                            if session_time and sector_start_time:

                                sector_time = session_time - sector_start_time
                                print(f"Sector {sector_index+1} -> {sector_time:.3f}s")

                                sector_start_time = session_time

                            sector_index += 1

                last_lap_dist = lap_dist

            speed_raw = ir_safe(ir,'Speed')
            is_on_track = ir_safe(ir,'IsOnTrack')
            fuel = clean_number(ir_safe(ir,'FuelLevel'))
            lap_time = clean_number(ir_safe(ir,'LapLastLapTime'))
            lap_completed = ir_safe(ir,'LapCompleted')
            on_pit_road = ir_safe(ir,'OnPitRoad')

            if not isinstance(speed_raw,(int,float)):
                time.sleep(0.1)
                continue

            speed = speed_raw * 3.6

            if on_pit_road:
                lap_was_in_pit = True

            if not stint_active and is_on_track and speed > 10:

                print("🟢 Inicio de stint")

                stint_active = True
                laps_buffer = []
                previous_fuel = fuel
                last_lap_time_value = None
                lap_was_in_pit = False
                tyre_snapshots = []

            if stint_active and is_on_track and lap_time is not None:

                if last_lap_time_value is None:
                    last_lap_time_value = lap_time

                elif lap_time != last_lap_time_value:

                    weather = get_weather(ir)

                    fuel_used = None
                    if previous_fuel is not None and fuel is not None:
                        fuel_used = round(previous_fuel - fuel,3)

                    print(f"⏱ V{lap_completed} | {lap_time:.3f}s")

                    # cerrar último sector
                    if sector_start_time and session_time:

                        sector_sum = sum(current_lap_sector_times)

                        final_sector = round(lap_time - sector_sum, 3)

                        if 0.5 < final_sector < lap_time:

                            print(f"Sector {len(current_lap_sector_times)+1} -> {final_sector:.3f}s")

                            current_lap_sector_times.append(final_sector)

                    laps_buffer.append({

                        "lap": lap_completed,
                        "lap_time": lap_time,
                        "fuel": fuel,
                        "fuel_used": fuel_used,
                        "is_pit_lap": lap_was_in_pit,

                        "track_temp": weather["track_temp"],
                        "air_temp": weather["air_temp"],
                        "humidity": weather["humidity"],
                        "wind_speed": weather["wind_speed"],
                        "wind_dir": weather["wind_dir"],
                        "sky": weather["sky"],
                        "track_state": weather["track_state"],

                        "sectors": current_lap_sector_times.copy(),
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
                    tyre_snapshots = []
                    lap_was_in_pit = False
                    last_lap_time_value = lap_time

                    current_lap_sector_times = []
                    sector_index = 0
                    sector_start_time = session_time

            if stint_active and not is_on_track:

                print("🔴 Fin de stint")

                if len(laps_buffer) >= MIN_VALID_LAPS:

                    driver_info = ir_safe(ir, 'DriverInfo')
                    track_name = current_track(ir)
                    tank_capacity = None
                    if driver_info:
                        tank_capacity = clean_number(
                            driver_info.get('DriverCarFuelMaxLtr')
                        )

                    payload = {
                        "driver": driver_data,
                        "car": current_car(ir),
                        "tank_capacity": tank_capacity,
                        "track": track_name,
                        "track_id": current_track_id(ir),

                        "session_type": current_session(ir),

                        "laps": laps_buffer,
                        "tyre_snapshots": tyre_snapshots
                    }

                    send_telemetry(payload, headers)

                stint_active = False
                laps_buffer = []
                tyre_snapshots = []

            time.sleep(0.05)

        except Exception as e:
            print("💥 ERROR:", e)


if __name__ == "__main__":
    run_logger()
