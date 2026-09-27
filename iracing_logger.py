# ============================================
# IRACING MANAGER - CLEAN CORE VERSION
# ============================================

import irsdk
import time
import requests
from datetime import datetime, UTC

# ============================================
# CONFIG
# ============================================

API_TOKEN = "4|u4zmUulGNwgI1q6Lxp3d0AU8YCa4rtX0sfs8gwe93a0a7423"
API_URL = "http://localhost:8000/api/telemetry/stint-batch"

HEADERS = {
    "X-API-TOKEN": API_TOKEN,
    "Content-Type": "application/json"
}

MIN_VALID_LAPS = 2


# ============================================
# HELPERS
# ============================================

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
            return float(value.replace(" C","").strip())
        return float(value)
    except:
        return None

def current_track(ir):
    try:
        w = ir['WeekendInfo']
        return f"{w.get('TrackDisplayName','Unknown')} - {w.get('TrackConfigName','Unknown')}"
    except:
        return "Unknown"

def current_car(ir):
    try:
        d = ir['DriverInfo']
        idx = d['DriverCarIdx']
        return d['Drivers'][idx]['CarScreenName']
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
            return float(raw.replace("km","").strip())
        if "mi" in raw:
            return float(raw.replace("mi","").strip()) * 1.60934
    except:
        return None
    return None


def send_stint(ir, laps):

    print("\n========== ENVIANDO STINT ==========")

    payload = {
        "car": current_car(ir),
        "track": current_track(ir),
        "session_type": current_session(ir),
        "track_length": get_track_length_km(ir),
        "laps": laps
    }

    try:
        r = requests.post(API_URL, json=payload, headers=HEADERS, timeout=15)
        print("Status:", r.status_code)
        print("Body:", r.text)
        if r.status_code == 200:
            print("STINT OK\n")
        else:
            print("STINT ERROR\n")
    except Exception as e:
        print("ERROR:", str(e))


# ============================================
# INIT IRACING
# ============================================

ir = irsdk.IRSDK()

print("Esperando iRacing...")
while not ir.startup():
    time.sleep(1)

print("Conectado a iRacing")


# ============================================
# STATE
# ============================================

stint_active = False
laps_buffer = []
lap_start_fuel = 0
last_lap_completed = None
last_recorded_lap_time = None
waiting_connection = False
current_context = None
last_recorded_lap_time = None

# ============================================
# MAIN LOOP
# ============================================

while True:
    try:

        # ------------------------------
        # RECONNECTION
        # ------------------------------

        if not ir.is_connected:
            if not waiting_connection:
                print("🔌 iRacing no conectado, esperando...")
                waiting_connection = True
            time.sleep(2)
            ir.startup()
            continue
        else:
            if waiting_connection:
                print("✅ iRacing reconectado")
                waiting_connection = False
                stint_active = False
                laps_buffer = []
                last_lap_completed = None
                last_recorded_lap_time = None

        ir.freeze_var_buffer_latest()

        # ------------------------------
        # CONTEXT CHECK
        # ------------------------------

        new_context = f"{current_track(ir)}|{current_session(ir)}"

        if current_context is None:
            current_context = new_context
        elif new_context != current_context:
            print("🔁 Cambio de contexto detectado")
            stint_active = False
            laps_buffer = []
            last_lap_completed = None
            last_recorded_lap_time = None
            current_context = new_context

        # ------------------------------
        # BASE DATA
        # ------------------------------

        speed = ir['Speed'] * 3.6
        fuel = ir['FuelLevel']
        is_on_track = ir['IsOnTrack']
        on_pit = ir['OnPitRoad']
        lap_completed = ir['LapCompleted']

        # ------------------------------
        # START STINT
        # ------------------------------

        if not stint_active and is_on_track and speed > 10:
            print("▶ inicio stint")
            stint_active = True
            laps_buffer = []
            lap_start_fuel = fuel
            last_lap_completed = lap_completed
            last_recorded_lap_time = None

        # ------------------------------
        # LAP DETECTION
        # ------------------------------

        if stint_active and is_on_track:

            lap_completed = ir['LapCompleted']
            lap_time_candidate = clean_number(ir['LapLastLapTime'])

            if lap_completed is None:
                continue

            # Detectar nueva vuelta
            if lap_completed > last_lap_completed:

                new_lap_time = None

                # Esperar hasta que LapLastLapTime CAMBIE
                for _ in range(50):  # ~2.5 segundos máximo
                    ir.freeze_var_buffer_latest()
                    candidate = clean_number(ir['LapLastLapTime'])

                    if (
                        candidate
                        and candidate > 0
                        and candidate != last_recorded_lap_time
                    ):
                        new_lap_time = candidate
                        break

                    time.sleep(0.05)

                if not new_lap_time or new_lap_time > 300:
                    last_lap_completed = lap_completed
                    continue

                fuel_used = lap_start_fuel - fuel

                # Si repostamos, solo resincronizamos
                if fuel > lap_start_fuel:
                    lap_start_fuel = fuel
                    fuel_used = 0

                if 0 <= fuel_used < 15:

                    laps_buffer.append({
                        "lap": len(laps_buffer) + 1,
                        "lap_time": new_lap_time,
                        "fuel": clean_number(fuel),
                        "fuel_used": clean_number(fuel_used),
                        "track_temp": clean_number(clean_temp(ir['WeekendInfo']['TrackSurfaceTemp'])),
                        "air_temp": clean_number(clean_temp(ir['WeekendInfo']['TrackAirTemp'])),
                        "timestamp": datetime.now(UTC).isoformat()
                    })

                    print("lap", len(laps_buffer), "|", new_lap_time)

                    lap_start_fuel = fuel
                    last_recorded_lap_time = new_lap_time

                last_lap_completed = lap_completed

        # ------------------------------
        # END STINT (ESC / GARAGE)
        # ------------------------------

        if stint_active and not is_on_track:

            print("⏹ salida de pista detectada")

            if len(laps_buffer) >= MIN_VALID_LAPS:
                send_stint(ir, laps_buffer)
            else:
                print("stint ignorado (<2 vueltas)")

            stint_active = False
            laps_buffer = []
            last_lap_completed = None
            last_recorded_lap_time = None

        time.sleep(0.05)

    except Exception as e:
        print("⚠ ERROR LOOP:", str(e))
        time.sleep(1)
