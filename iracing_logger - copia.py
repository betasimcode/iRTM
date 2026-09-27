import irsdk
import time
import requests
from datetime import datetime, UTC

# ============================================

# CONFIG

# ============================================

API_URL = "http://127.0.0.1:8000/api/telemetry/stint-batch"
API_TOKEN = "TU_TOKEN_AQUI"

HEADERS = {
"Content-Type": "application/json",
"X-API-TOKEN": API_TOKEN
}

MIN_VALID_LAPS = 4
STOP_TIMEOUT = 15  # segundos parado para cerrar stint

# ============================================

# HELPERS

# ============================================

def safe_float(v):
    try:
        v = float(v)
        if v != v or v == float("inf") or v == float("-inf"):
            return None
        return v
    except:
        return None

def get_track(ir):
    try:
        w = ir['WeekendInfo']
        return f"{w['TrackDisplayName']} - {w['TrackConfigName']}"
    except:
        return "Unknown"

def get_car(ir):
    try:
        d = ir['DriverInfo']
        idx = d['DriverCarIdx']
        return d['Drivers'][idx]['CarScreenName']
    except:
        return "Unknown"

def get_session(ir):
    try:
        return ir['SessionInfo']['Sessions'][ir['SessionNum']]['SessionType']
    except:
        return "Unknown"

def get_track_length(ir):
    try:
        txt = ir['WeekendInfo']['TrackLength']  # "1.623 km" or "0.375 mi"
        value, unit = txt.split()
        value = float(value)
        if unit.lower() == "mi":
            value *= 1.60934
        return round(value, 4)
    except:
        return None

# ============================================
# SEND STINT
# ============================================

def send_stint(ir, laps):
    print("Enviando stint...")

payload = {
    "car": get_car(ir),
    "track": get_track(ir),
    "session_type": get_session(ir),
    "track_length": get_track_length(ir),
    "laps": laps
}

try:
    r = requests.post(API_URL, json=payload, headers=HEADERS, timeout=20)
    print("STINT SENT:", r.status_code, r.text)
except Exception as e:
    print("SEND ERROR:", e)

# ============================================
# MAIN LOOP
# ============================================

ir = irsdk.IRSDK()

print("Esperando iRacing...")

while True:
    if ir.startup():
        print("Conectado a iRacing")
    break
    time.sleep(1)

last_lap = -1
laps_buffer = []
stint_active = False
last_move_time = time.time()

while True:
    ir.freeze_var_buffer_latest()
    if not ir['IsOnTrack']:
        if stint_active and len(laps_buffer) >= MIN_VALID_LAPS:
            send_stint(ir, laps_buffer)
            stint_active = False
            laps_buffer.clear()
            last_lap = -1
            time.sleep(1)
        continue

    lap = ir['Lap']
    lap_time = safe_float(ir['LapLastLapTime'])
    fuel = safe_float(ir['FuelLevel'])
    speed = safe_float(ir['Speed'])
    track_temp = safe_float(ir['WeekendInfo']['TrackSurfaceTemp'])
    air_temp = safe_float(ir['WeekendInfo']['TrackAirTemp'])

    moving = speed is not None and speed > 1

    if moving:
        last_move_time = time.time()

        # inicio stint
    if not stint_active and moving:
        stint_active = True
        print("▶ inicio stint")

        # nueva vuelta
    if stint_active and lap != last_lap and lap_time and lap_time > 5:
        last_lap = lap

        fuel_used = None
        if len(laps_buffer) > 0:
            prev_fuel = laps_buffer[-1]['fuel']
            if prev_fuel and fuel:
                    fuel_used = prev_fuel - fuel

            laps_buffer.append({
                "lap": lap,
                "lap_time": lap_time,
                "fuel": fuel,
                "fuel_used": fuel_used,
                "track_temp": track_temp,
                "air_temp": air_temp,
                "timestamp": datetime.now(UTC).isoformat()
            })

            print("lap", lap)

        # cierre por inactividad
    if stint_active and time.time() - last_move_time > STOP_TIMEOUT:
            if len(laps_buffer) >= MIN_VALID_LAPS:
                send_stint(ir, laps_buffer)

            stint_active = False
            laps_buffer.clear()
            last_lap = -1
            print("⏹ stint cerrado por inactividad")

    time.sleep(0.05)
