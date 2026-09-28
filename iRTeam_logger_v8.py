import irsdk
import requests
import time
import threading
import sys
import os
import tempfile
import shutil

from pathlib import Path
import subprocess
import logging

from flask import Flask, request, jsonify
from flask_cors import CORS

from datetime import datetime
from pystray import Icon, Menu, MenuItem
from PIL import Image, ImageDraw

logging.basicConfig(

    filename='mrt_logger.log',

    level=logging.INFO,

    format='%(asctime)s - %(levelname)s - %(message)s'
)


def get_base_dir():

    if getattr(
        sys,
        'frozen',
        False
    ):

        return os.path.dirname(
            sys.executable
        )

    return os.path.dirname(
        os.path.abspath(__file__)
    )

def get_telemetry_folder():

    return str(

        Path.home()

        / "Documents"

        / "iRacing"

        / "telemetry"
    )

BASE_DIR = get_base_dir()
# --- CONFIGURACIÓN ---
BASE_URL = "http://localhost:8000/api"
STINT_URL = "http://localhost:8000/api/v1/stints"

# Ruta absoluta al ejecutable
# PATH_CRONOMETRO_CS = r"C:\Users\Monte\source\repos\iRTeam_manager_logger\iRTeam_manager_logger\bin\x64\Debug\net8.0\iRTeam_manager_logger.exe"

PATH_CRONOMETRO_CS = os.path.join(

    BASE_DIR,

    "crono",
    "iRTeam_manager_logger.exe"
)

current_user = "No driver"

ir = irsdk.IRSDK()

def resource_path(relative_path):

    try:

        base_path = sys._MEIPASS

    except Exception:

        base_path = os.path.abspath(".")

    return os.path.join(
        base_path,
        relative_path
    )

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


import os
import time


# ===============================
# IBT - DETECTAR ARCHIVO NUEVO
# ===============================
def wait_for_new_ibt(path, timeout=10):

    print("⏳ Esperando IBT...")

    start_time = time.time()
    initial_files = set(os.listdir(path))

    while time.time() - start_time < timeout:

        current_files = set(os.listdir(path))
        new_files = current_files - initial_files

        ibts = [f for f in new_files if f.endswith('.ibt')]

        if ibts:
            latest = max(
                [os.path.join(path, f) for f in ibts],
                key=os.path.getmtime
            )

            print("📂 IBT nuevo detectado:", latest)
            return latest

        time.sleep(0.5)

    print("❌ Timeout esperando IBT")
    return None

# ===============================
# IBT - DETECTAR IBT DE SESION
# ===============================
def get_latest_ibt(path, not_before=None):

    try:

        files = [
            os.path.join(path, f)
            for f in os.listdir(path)
            if f.lower().endswith(".ibt")
        ]

        if not files:
            print("❌ No hay archivos IBT")
            return None

        # Solo IBT modificados durante la sesión actual
        if not_before is not None:

            files = [
                f for f in files
                if os.path.getmtime(f) >= not_before
            ]

        if not files:
            print(
                "⚠️ No hay IBT generado durante "
                "la sesión actual. Se omite el envío."
            )
            return None

        latest = max(
            files,
            key=os.path.getmtime
        )

        print("📂 IBT de sesión actual:", latest)

        return latest

    except Exception as e:

        print(f"❌ Error buscando IBT: {e}")

        return None

def get_ibt_filename(file_path):
    if not file_path:
        return None

    return os.path.basename(file_path)

# ======================================
# CURRENT SETUP FILE
# ======================================

def get_current_setup_file(car_folder):

    try:

        setup_file = os.path.join(
            Path.home(),
            "Documents",
            "iRacing",
            "setups",
            car_folder,
            "-Current-"
        )

        print(
            f"BUSCANDO SETUP EN: {car_folder}"
        )

        print(
            f"RUTA COMPLETA: {setup_file}"
        )

        if os.path.exists(setup_file):

            print(
                f"📂 Current setup found: {setup_file}"
            )

            return setup_file

        print(
            f"❌ Current setup not found: {setup_file}"
        )

        return None

    except Exception as e:

        print(
            f"❌ get_current_setup_file: {e}"
        )

        return None

def copy_current_setup(
    car_folder,
    stint_id
):

    try:

        source_file = get_current_setup_file(
            car_folder
        )

        if not source_file:

            return None

        destination = os.path.join(

            tempfile.gettempdir(),

            f"stint_{stint_id}.sto"
        )

        shutil.copy2(
            source_file,
            destination
        )

        print(
            f"✅ Setup copied: {destination}"
        )

        return destination

    except Exception as e:

        print(
            f"❌ copy_current_setup: {e}"
        )

        return None


# ===============================
# IBT - EXTRAER TEXTO
# ===============================
def extract_carsetup_from_ibt(file_path):

    try:
        with open(file_path, 'rb') as f:
            content = f.read()

        text = content.decode('utf-8', errors='ignore')

        start = text.find("CarSetup:")

        if start == -1:
            print("❌ No CarSetup encontrado")
            return None

        # 🔥 buscar siguiente bloque raíz (sin indentación)
        lines = text[start:].splitlines()

        setup_lines = []
        first = True

        for line in lines:

            # parar cuando aparece otra sección sin indentación
            if not first and not line.startswith(" ") and ":" in line:
                break

            setup_lines.append(line)
            first = False

        setup_text = "\n".join(setup_lines)

        # 🔥 seguridad: límite tamaño
        setup_text = setup_text[:10000]

        print("📏 Setup chars:", len(setup_text))

        return setup_text

    except Exception as e:
        print(f"❌ Error leyendo IBT: {e}")
        return None


# ===============================
# PARSEAR TEXTO → DICT
# ===============================
def parse_setup_text(setup_text):

    data = {}
    stack = []

    for line in setup_text.splitlines():

        if not line.strip():
            continue

        indent = len(line) - len(line.lstrip())
        key_value = line.strip().split(":", 1)

        key = key_value[0].strip()
        value = key_value[1].strip() if len(key_value) > 1 else None

        while stack and stack[-1][0] >= indent:
            stack.pop()

        if stack:
            parent = stack[-1][1]
        else:
            parent = data

        if value:
            parent[key] = value
        else:
            parent[key] = {}
            stack.append((indent, parent[key]))

    return data

def restart_app(reason="Unknown"):

    print(f"\n🔄 REINICIANDO LOGGER: {reason}\n")

    exe_path = sys.argv[0]

    print("RESTART PATH =", exe_path)

    subprocess.Popen([exe_path])

    os._exit(0)

def extract_session_results_from_ibt(file_path):

    try:

        with open(file_path, 'rb') as f:
            content = f.read()

        text = content.decode('utf-8', errors='ignore')

        lines = text.splitlines()

        results = []

        # ============================================
        # DRIVERS MAP
        # ============================================

        drivers_map = {}

        in_drivers = False
        current_driver = None

        for line in lines:

            stripped = line.strip()

            # ----------------------------------------
            # ENTRAR BLOQUE DRIVERS
            # ----------------------------------------

            if stripped.startswith('Drivers:'):

                in_drivers = True
                continue

            if not in_drivers:
                continue

            # ----------------------------------------
            # NUEVO DRIVER
            # ----------------------------------------

            if stripped.startswith('- CarIdx:'):

                try:

                    current_driver = {
                        'car_idx': int(
                            stripped.split(':', 1)[1].strip()
                        )
                    }

                except:

                    current_driver = None

                continue

            # ----------------------------------------
            # USERNAME
            # ----------------------------------------

            if current_driver and stripped.startswith('UserName:'):

                current_driver['user_name'] = (
                    stripped.split(':', 1)[1].strip()
                )

            # ----------------------------------------
            # CAR NAME
            # ----------------------------------------

            elif current_driver and stripped.startswith('CarScreenNameShort:'):

                current_driver['car_name'] = (
                    stripped.split(':', 1)[1].strip()
                )

            # ----------------------------------------
            # IRATING
            # ----------------------------------------

            elif current_driver and stripped.startswith('IRating:'):

                try:

                    current_driver['irating'] = int(
                        stripped.split(':', 1)[1].strip()
                    )

                except:

                    current_driver['irating'] = None
            # ----------------------------------------
            # USER ID
            # ----------------------------------------

            elif current_driver and stripped.startswith('UserID:'):

                try:

                    current_driver['iracing_user_id'] = int(
                        stripped.split(':', 1)[1].strip()
                    )

                except:

                    current_driver['iracing_user_id'] = None
            # ----------------------------------------
            # CAR NUMBER
            # ----------------------------------------

            elif current_driver and stripped.startswith('CarNumber:'):

                current_driver['car_number'] = (
                    stripped.split(':', 1)[1]
                    .strip()
                    .replace('"', '')
                )
            # ----------------------------------------
            # LICENSE
            # ----------------------------------------

            elif current_driver and stripped.startswith('LicString:'):

                current_driver['license_class'] = (
                    stripped.split(':', 1)[1].strip()
                )
            # ----------------------------------------
            # COUNTRY
            # ----------------------------------------

            elif current_driver and stripped.startswith('FlairName:'):

                current_driver['country'] = (
                    stripped.split(':', 1)[1].strip()
                )
            # ----------------------------------------
            # COUNTRY ID
            # ----------------------------------------

            elif current_driver and stripped.startswith('FlairID:'):

                try:

                    current_driver['country_id'] = int(
                        stripped.split(':', 1)[1].strip()
                    )

                except:

                    current_driver['country_id'] = None
            # ----------------------------------------
            # DIVISION NAME
            # ----------------------------------------

            elif current_driver and stripped.startswith('DivisionName:'):

                current_driver['division_name'] = (
                    stripped.split(':', 1)[1].strip()
                )
            # ----------------------------------------
            # DIVISION ID
            # ----------------------------------------

            elif current_driver and stripped.startswith('DivisionID:'):

                try:

                    current_driver['division_id'] = int(
                        stripped.split(':', 1)[1].strip()
                    )

                except:

                    current_driver['division_id'] = None
            # ----------------------------------------
            # TEAM INCIDENT COUNT
            # FINAL DRIVER BLOCK
            # ----------------------------------------

            elif current_driver and stripped.startswith('TeamIncidentCount:'):

                car_idx = current_driver.get('car_idx')

                if car_idx is not None:

                    drivers_map[car_idx] = current_driver

                    print(current_driver)

                current_driver = None
        # ============================================
        # RESULTS POSITIONS
        # ============================================

        in_results = False

        current_result = {}

        for line in lines:

            stripped = line.strip()

            if stripped.startswith('ResultsPositions:'):
                in_results = True
                continue

            if not in_results:
                continue

            if stripped.startswith('- Position:'):

                if current_result:
                    results.append(current_result.copy())

                try:

                    position = int(
                        stripped.split(':', 1)[1].strip()
                    )

                    current_result = {
                        'position': position
                    }

                except:
                    current_result = {}

            elif stripped.startswith('CarIdx:'):

                try:

                    car_idx = int(
                        stripped.split(':', 1)[1].strip()
                    )

                    # ignorar pace/spectator/invalid

                    if car_idx < 0:
                        current_result = {}
                        continue

                    current_result['car_idx'] = car_idx

                    driver = drivers_map.get(car_idx, {})

                    current_result['user_name'] = (
                        driver.get('user_name')
                    )

                    current_result['car_name'] = (
                        driver.get('car_name')
                    )

                    current_result['irating'] = (
                    driver.get('irating')
                    )

                    current_result['car_number'] = (
                        driver.get('car_number')
                    )

                    current_result['iracing_user_id'] = (
                    driver.get('iracing_user_id')
                    )

                    current_result['license_class'] = (
                        driver.get('license_class')
                    )

                    current_result['country'] = (
                        driver.get('country')
                    )

                    current_result['country_id'] = (
                        driver.get('country_id')
                    )

                    current_result['division_name'] = (
                        driver.get('division_name')
                    )

                    current_result['division_id'] = (
                        driver.get('division_id')
                    )
                except:
                    pass

            # ----------------------------------------
            # CLASS POSITION
            # ----------------------------------------

            elif stripped.startswith('ClassPosition:'):

                try:

                    current_result['class_position'] = int(
                        stripped.split(':', 1)[1].strip()
                    )

                except:

                    current_result['class_position'] = None

            elif 'FastestTime:' in stripped:

                try:

                    current_result['fastest_time'] = float(
                        stripped.split(':', 1)[1].strip()
                    )

                except:
                    current_result['fastest_time'] = None

        # último resultado
        if current_result:
            results.append(current_result.copy())

        print(
            f"🏁 Session results parsed: "
            f"{len(results)}"
        )

        return results

    except Exception as e:

        print(
            f"❌ extract_session_results_from_ibt: "
            f"{e}"
        )

        return []

#////////////////////////////////////////////////////////////////////////////////////////////////////
#////////////////////////////////////////////////////////////////////////////////////////////////////
#///////////////////////////////// MRT LOGGER ///////////////////////////////////////////////////////
#////////////////////////////////////////////////////////////////////////////////////////////////////
#////////////////////////////////////////////////////////////////////////////////////////////////////

class MRTLogger:
    def __init__(self):
        self.token = None
        self.was_connected = False
        self.db_user_id = None
        self.is_restarting = False
        self.current_driver_name = "No driver"
        self.current_car_name = "No car"
        self.current_car_path = None
        self.current_track_name = "No track"
        self.current_status = "Starting"

        self.current_stint_id = None
        self.last_track_id = None
        self.current_track = None
        self.current_track_id = None
        self.stint_attempted = False
        self.current_sectors = "0.33,0.66,0.99"
        self.in_stint = False

        # =====================================
        # IBT - CONTROL DE SESION ACTUAL
        # =====================================
        self.last_subsession_id = None
        self.last_ibt_session_key = None
        self.ibt_session_started_at = None

        self.tank_capacity = None
        self.last_tank_log_time = 0
        self.sync_attempts = 0
        self.car_synced = False

        self.last_lap_completed = None

        self.was_on_pitroad = False
        self.pit_in_this_lap = False
        self.pit_out_this_lap = False

        # 🔥 NUEVO: control de incidentes / offtrack
        self.incident_lap = 0
        self.offtrack_lap = 0

        self.last_incident_total = 0
        self.was_offtrack = False

        self.tyres_start_sent = False

        self.last_fuel = None

        self.stop_event = threading.Event()
        self.icon = None
        self.csharp_process = None

        self.local_api = Flask(__name__)
        CORS(self.local_api)
        self.register_local_routes()


    def check_cronometer(self):

        return os.path.exists(
            PATH_CRONOMETRO_CS
        )

    def upload_setup_file(
        self,
        setup_file
    ):
        print(
        "🚀 INICIANDO UPLOAD SETUP"
        )

        print(
            f"📂 Archivo: {setup_file}"
        )

        print(
            f"🆔 Stint: {self.current_stint_id}"
        )
        try:

            with open(
                setup_file,
                "rb"
            ) as f:

                files = {
                    "setup": f
                }

                data = {
                    "stint_id":
                        self.current_stint_id
                }
                print(
                    f"🌐 URL: {BASE_URL}/telemetry/setup"
                )
                r = requests.post(

                    f"{BASE_URL}/telemetry/setup",

                    files=files,

                    data=data,

                    headers=self.get_headers(),

                    timeout=150
                )

                print(
                    f"📡 Setup response: {r.status_code}"
                )

                print(
                    "📦 RESPONSE BODY:"
                )

                print(
                    r.text
                )

        except Exception as e:

            print(
                f"❌ Setup upload error: {e}"
            )
    # ======================================
    # iRACING TELEMETRY FOLDER
    # ======================================

    def get_headers(self):
        return {
            "X-API-TOKEN": self.token,
            "Accept": "application/json"
        }

    def calculate_tank_capacity(self):
        try:
            fuel = float(ir['FuelLevel'] or 0.0)
            pct  = float(ir['FuelLevelPct'] or 0.0)

            if pct > 0:
                capacity = round(fuel / pct, 2)

                if capacity > 5:
                    return capacity

            return None

        except Exception as e:
            print(f"⚠️ Tank calc error: {e}")
            return None


    def sync_car_once(self, car_id, car_name):

        if self.car_synced:
            return

        try:
            r = requests.post(
                f"{BASE_URL}/logger/car",
                json={
                    "iracing_car_id": int(car_id),
                    "name": car_name,
                    "iracing_setup_folder": self.current_car_path,
                    "tank_capacity": self.calculate_tank_capacity()
                },
                headers=self.get_headers(),
                timeout=3
            )

            if r.status_code in [200, 201]:

                data = r.json()

                # 🔥 CASO 1 → ya existe en DB
                if data.get('status') == 'exists':

                    self.car_synced = True

                    self.tank_capacity = data.get(
                        'tank_capacity'
                    )

                    print(

                        f"✅ Car ya existe en DB → "

                        f"Tank:{self.tank_capacity} | "

                        f"Folder:{self.current_car_path}"
                    )

                    return

                # 🔥 CASO 2 → recién creado
                if data.get('tank_capacity'):

                    self.car_synced = True

                    self.tank_capacity = data.get(
                        'tank_capacity'
                    )

                    print(

                        f"✅ Car synced → "

                        f"Tank:{self.tank_capacity} | "

                        f"Folder:{self.current_car_path}"
                    )

                    return

            # 🔥 CONTROL DE LOG (solo si no hay respuesta válida)
            now = time.time()
            if now - self.last_tank_log_time > 5:
                print("⏳ Esperando tank_capacity válido...")
                self.last_tank_log_time = now

        except Exception as e:
            print(f"⚠️ Error sync car: {e}")

    def send_with_retry(self, payload):

        for i in range(5):

            try:
                r = requests.post(
                    f"{BASE_URL}/logger/setup-snapshot",
                    json=payload,
                    headers=self.get_headers(),
                    timeout=10
                )

                if r.status_code == 200:
                    print("✅ Setup guardado")
                    return r  # 👈 ESTO ES CLAVE

                print(f"⚠️ intento {i+1} fallo:", r.text)

            except Exception as e:
                print("❌ error:", e)

            time.sleep(1)

        print("💀 No se pudo guardar setup")
        return None

    def handle_stint_end(self):

        print("📦 Capturando setup desde IBT...")

        ibt_path = get_telemetry_folder()

        time.sleep(2)

        ibt_file = get_latest_ibt(
        ibt_path,
        self.ibt_session_started_at
    )




        # ===============================
        # 🔥 ENVÍO IBT (NUEVO PIPELINE)
        # ===============================
        if ibt_file:
            ibt_filename = get_ibt_filename(ibt_file)

            print(f"📂 IBT original: {ibt_filename}")
            print("🚀 Enviando IBT al servidor...")

            try:
                with open(ibt_file, 'rb') as f:
                    files = {'ibt': f}

                    data = {
                        "stint_id": self.current_stint_id,
                        "ibt_filename": ibt_filename
                    }

                    r = requests.post(
                        f"{BASE_URL}/telemetry/ibt",
                        files=files,
                        data=data,
                        headers=self.get_headers(),
                        timeout=30
                    )

                    print(
                        f"CAR PATH = {self.current_car_path}"
                    )

                    print(
                        f"CURRENT STINT = {self.current_stint_id}"
                    )

                    print("📡 IBT response:", r.status_code)

                    # ===============================
                    # SETUP FILE
                    # ===============================

                    if self.current_car_path:

                        setup_file = copy_current_setup(

                            self.current_car_path,

                            self.current_stint_id
                        )

                        if setup_file:
                            print(
                                "📤 ENVIANDO SETUP AL SERVIDOR"
                            )
                            print(
                                "⏳ Esperando creación del Setup..."
                            )

                            self.upload_setup_file(
                                setup_file
                            )

            except Exception as e:
                print(f"❌ Error enviando IBT: {e}")


        # ============================================
        # NO IBT ACTUAL: OMITIR TELEMETRIA AUXILIAR
        # ============================================

        if not ibt_file:

            print(
                "⚠️ No hay IBT de la sesión actual. "
                "Se conservan vueltas y tiempos, "
                "pero se omite IBT y setup."
            )

            return

        # ============================================
        # SESSION RESULTS
        # ============================================

        try:

            session_results = extract_session_results_from_ibt(ibt_file)

            if session_results:

                results_payload = {

                    "iracing_subsession_id":
                        ir['WeekendInfo']['SubSessionID'],

                    "results":
                        session_results
                }

                r = requests.post(

                    f"{BASE_URL}/sessions/results",

                    json=results_payload,

                    headers=self.get_headers(),

                    timeout=10
                )

                print(
                    f"🏁 Session results sent: "
                    f"{r.status_code}"
                )

            else:

                print("⚠️ No session results found")

        except Exception as e:

            print(f"❌ Session results error: {e}")

        text = extract_carsetup_from_ibt(ibt_file)

        if not text:
            print("❌ No se pudo extraer CarSetup")
            return

        data = parse_setup_text(text)

        if not data:
            print("⛔ Setup vacío")
            return

        payload = {
            "stint_id": self.current_stint_id,
            "values": data
        }

        print("📦 SETUP SNAPSHOT OK")
        print("SIZE:", len(str(data)))
        print("STINT ID (logger):", self.current_stint_id)

        try:
            r = self.send_with_retry(payload)  # 👈 CLAVE

            if r:
                print("STATUS:", r.status_code)
                print("RESPONSE:", r.text)
            else:
                print("❌ No se recibió respuesta del servidor")

        except Exception as e:
            print("❌ Error enviando setup:", e)


    def ir_safe(self, key):
        try:
            val = self.ir[key]

            # 🔥 si es lista/tuple → coger primer valor
            if isinstance(val, (list, tuple)):
                return val[0] if len(val) > 0 else None

            return val

        except Exception as e:
            print(f"⚠️ ir_safe fallo en {key}: {e}")
            return None

    def clean_number(self, v):
        try:
            if v is None:
                return 0
            return float(v)
        except:
            return 0


    def send_enrich(self, lap_id):

        try:
            r = requests.post(
                f"{BASE_URL}/laps/enrich",
                json={
                    "lap_id": lap_id,
                    "incident_count": self.incident_lap,
                    "offtrack_count": self.offtrack_lap
                },
                headers=self.get_headers(),
                timeout=2
            )

            if r.status_code == 200:
                print("🧠 Lap enriquecida")

        except Exception as e:
            print("❌ enrich error:", e)

    def get_tyre_wear(self):
        try:
            wear = ir['CarIdxTireWear']
            idx = ir['DriverInfo']['DriverCarIdx']

            if wear and idx is not None and idx < len(wear):
                return wear[idx]  # [LF, RF, LR, RR]

        except:
            pass

        return None


    def send_tyre_snapshot(self, snapshot_type, lap):
        try:
            global ir

            payload = {

                "stint_id": self.current_stint_id,
                "lap_number": lap,
                "snapshot_type": snapshot_type,

                # ===== TEMPS BASE (centro) =====
                "temp_fl": float(ir['LFtempCM']),
                "temp_fr": float(ir['RFtempCM']),
                "temp_rl": float(ir['LRtempCM']),
                "temp_rr": float(ir['RRtempCM']),

                # ===== FRONT LEFT =====
                "temp_fl_o": float(ir['LFtempCL']),
                "temp_fl_m": float(ir['LFtempCM']),
                "temp_fl_i": float(ir['LFtempCR']),

                # ===== FRONT RIGHT (invertido) =====
                "temp_fr_o": float(ir['RFtempCR']),
                "temp_fr_m": float(ir['RFtempCM']),
                "temp_fr_i": float(ir['RFtempCL']),

                # ===== REAR LEFT =====
                "temp_rl_o": float(ir['LRtempCL']),
                "temp_rl_m": float(ir['LRtempCM']),
                "temp_rl_i": float(ir['LRtempCR']),

                # ===== REAR RIGHT (invertido) =====
                "temp_rr_o": float(ir['RRtempCR']),
                "temp_rr_m": float(ir['RRtempCM']),
                "temp_rr_i": float(ir['RRtempCL']),

                # ===== WEAR (MIDDLE estable) =====
                "wear_fl": float(ir['LFwearM']),
                "wear_fr": float(ir['RFwearM']),
                "wear_rl": float(ir['LRwearM']),
                "wear_rr": float(ir['RRwearM']),
            }

            # print("📦 PAYLOAD TYRES:", payload)  # 👈 DEBUG CLAVE

            r = requests.post(
                f"{BASE_URL}/stint-tyres",
                json=payload,
                headers=self.get_headers(),
                timeout=2
            )

            print("STATUS:", r.status_code)
            print("RESPONSE:", r.text)

        except Exception as e:
            print(f"❌ Error enviando tyres: {e}")


    # --- NUEVA FUNCIÓN PARA CARGAR SECTORES ---
    def load_track_maps(self, track_id):
        """Busca los sectores en la API/DB. Si falla, devuelve genéricos."""
        print(f"🚨 load_track_maps() CALLED → track_id={track_id}")
        try:
            print("🌐 Intentando cargar sectores desde API...")
            headers = self.get_headers()
            r = requests.get(f"{BASE_URL}/tracks/{track_id}/sectors", headers=headers, timeout=5)
            if r.status_code == 200:
                data = r.json()
            print(f"📡 STATUS: {r.status_code}")
            print(f"📦 RESPONSE: {r.text[:200]}")  # corta para no spamear

            if isinstance(data, list) and len(data) > 0:
                pts = [str(item['pct']) for item in data]
                return ",".join(pts)
                if pts: return str(pts)
                print(f"🌐 GET {BASE_URL}/tracks/{track_id}/sectors")
                print("STATUS:", r.status_code)
                print("RESPONSE:", r.text)
        except Exception as e:
            print(f"⚠️ No se pudo conectar para sectores: {e}")

        print("ℹ️ Usando sectores genéricos (0.33, 0.66, 0.99)")
        return "0.33,0.66,0.99"


    # --- FUNCIÓN DE LANZAMIENTO UNIFICADA ---
    def launch_csharp_timer(self):
        if not os.path.exists(PATH_CRONOMETRO_CS):

            self.current_status = (
                "Cronometer Missing"
            )

            print(
                f"❌ ERROR: No existe el EXE en {PATH_CRONOMETRO_CS}"
            )

            return

        working_dir = os.path.dirname(PATH_CRONOMETRO_CS)
        stint_id = str(self.current_stint_id)
        token = str(self.token)
        pts = self.current_sectors if self.current_sectors else "0.33,0.66,0.99"
        args_list = [PATH_CRONOMETRO_CS, stint_id, token, pts]

        print("--- 🔍 LANZANDO CRONÓMETRO C# ---")
        print(f"🚀 Stint ID: {stint_id} | 📍 Sectores: {pts}")
        self.current_status = "Recording Stint"
        try:
            self.csharp_process = subprocess.Popen(
                args_list,
                cwd=working_dir
            )
            print("✅ Proceso C# vinculado y ejecutándose.")
        except Exception as e:
            print(f"❌ Error crítico al lanzar C#: {e}")


    def stop_csharp_timer(self):

        if not self.csharp_process:

            print("⚠️ stop_csharp_timer() llamado sin proceso")

            return

        try:

            print("🛑 Deteniendo Cronómetro C#...")

            print(
                f"PID = {self.csharp_process.pid}"
            )

            self.csharp_process.terminate()

            print(
                "📨 terminate() enviado"
            )

            self.csharp_process.wait(
                timeout=2
            )

            print(
                "✅ Proceso finalizado correctamente"
            )

        except Exception as e:

            print(
                f"❌ EXCEPCIÓN EN WAIT(): {e}"
            )

            try:

                print(
                    "💀 Ejecutando kill()"
                )

                self.csharp_process.kill()

                print(
                    "✅ kill() completado"
                )

            except Exception as e2:

                print(
                    f"❌ ERROR EN KILL(): {e2}"
                )

        finally:

            print(
                "🧹 Liberando referencia C#"
            )

            self.csharp_process = None

            print(
                "✅ self.csharp_process = None"
            )

    def fetch_token(self):
        # `print("URL:", STINT_URL)`
        print("🔍 Buscando iRacing...")
        self.current_status = "Waiting iRacing"
        while not self.stop_event.is_set():
            if ir.startup() and ir.is_connected:
                d_info = ir['DriverInfo']
                u_id = d_info.get('DriverUserID')

                if u_id:
                    try:
                        r = requests.post(f"{BASE_URL}/logger/token", json={"iracing_user_id": u_id}, timeout=5)
                        if r.status_code == 200:
                            self.current_status = "Connected"
                            data = r.json()
                            self.token = data.get("api_token")
                            self.db_user_id = data.get("id")
                            self.iracing_user_id = u_id
                            print(f"✅ Token validated - User {u_id} (ID DB: {self.db_user_id}).")
                            return True
                    except Exception as e:
                        print(f"❌ Error API Token: {e}")
            time.sleep(3)
        return False

    def detect_session_change(self):
        try:
            w = ir['WeekendInfo']
            d = ir['DriverInfo']

            track_id = w.get('TrackID') if w else None
            subsession_id = str(w.get('SubSessionID')) if w and w.get('SubSessionID') else None

            car_path = None
            if d and 'Drivers' in d:
                idx = d.get('DriverCarIdx')
                if idx is not None and idx < len(d['Drivers']):
                    car_path = d['Drivers'][idx].get('CarPath')

            return track_id, subsession_id, car_path

        except:
            return None, None, None

    def get_session_type(self):
        try:
            s_info = ir['SessionInfo']
            s_num = ir.get('SessionNum', 0)

            if s_info and 'Sessions' in s_info and len(s_info['Sessions']) > s_num:
                return s_info['Sessions'][s_num].get('SessionType', "Unknown")

        except:
            pass

        return "Unknown"


    def start_stint(self):
        try:
            d_info = ir['DriverInfo']
            if not d_info: return

            my_idx = d_info.get('DriverCarIdx')
            my_driver_data = next((d for d in d_info.get('Drivers', []) if d['CarIdx'] == my_idx), None)

            car_name = my_driver_data.get('CarScreenName', 'Unknown') if my_driver_data else "Unknown"
            car_id = my_driver_data.get('CarID', 0) if my_driver_data else 0
            fuel_initial = float(ir['FuelLevel'] or 0.0)
            self.last_fuel = fuel_initial  # 👈 CLAVE
            # cuando detectas inicio de stint

            # print("DEBUG SessionTimeOfDay:", ir['SessionTimeOfDay'])
            payload = {
                "driver": {
                    "iracing_user_id": self.iracing_user_id
                },
                "track_id": self.current_track_id,
                "car_name": car_name,
                "car_id": int(car_id),
                "fuel_setup": fuel_initial,
                "sim_time_day": float(ir['SessionTimeOfDay'] or 0),
                "track_temp": float(ir['TrackTemp'] or 0),
                "air_temp": float(ir['AirTemp'] or 0),
                "humidity": float(ir['RelativeHumidity'] or 0),
                "pressure": float(ir['AirPressure'] or 0),
                "air_density": float(ir['AirDensity'] or 0),
                "fuel_per_lap": 0,
                "fuel_consumed": 0,
                "laps": []  # 👈 importante luego
            }
            # 👇 AÑADE ESTO ANTES del POST del stint
            try:
                requests.post(
                    f"{BASE_URL}/logger/car",
                    json={
                        "iracing_car_id": int(car_id),
                        "name": car_name
                    },
                    headers=self.get_headers(),
                    timeout=3
                )
            except Exception as e:
                print(f"⚠️ Error sync car: {e}")
            headers = self.get_headers()
            r = requests.post(STINT_URL, json=payload, headers=headers)

            if r.status_code in [200, 201]:
                # 👇 NUEVO



                self.current_stint_id = r.json().get('stint_id')
                self.send_tyre_snapshot("start", -1)
                self.tyres_start_sent = True
                print(f"{BASE_URL}/logger/car")
                print(f"⛽ Stint iniciado! ID:{self.current_stint_id}")
                print("🟢 Tyres START capturados al iniciar stint")
                self.launch_csharp_timer()

                self.last_lap_completed = None
                self.pit_in_this_lap = False
                self.pit_out_this_lap = False
                # print("DEBUG PAYLOAD:", payload)
            else:
                print(f"❌ Error al crear Stint ({r.status_code}): {r.text}")
                # NO resetees, evita loop infinito
                print("⚠️ Stint no creado, pero seguimos en pista")
        except Exception as e:
            print(f"❌ Error start_stint: {e}")
            self.in_stint = False

    def action_quit(self, icon, item):
        self.stop_csharp_timer()
        icon.stop()
        os._exit(0)

    def action_restart(self, icon, item):
        restart_app("Reinicio manual")

    def main_loop(self):

        print(PATH_CRONOMETRO_CS)

        print(os.path.exists(
            PATH_CRONOMETRO_CS
        ))

        if not self.fetch_token(): return
        last_ir_init = None
        while not self.stop_event.is_set():

            if self.is_restarting:
                print("🛑 Main loop detenido por reinicio")
                return

            if not ir.is_connected:
                if self.in_stint:
                    self.stop_csharp_timer()
                    self.in_stint = False

                print("🔌 iRacing desconectado")
                restart_app("sim exit")
                # 🔥 RESET TOTAL (CLAVE)
                self.last_track_id = None
                self.last_sub_id = None
                self.last_car_path = None

                ir.startup()
                time.sleep(2)
                continue

            if ir.is_initialized != last_ir_init:

                print(
                    f"IR INIT = {ir.is_initialized}"
                )

                last_ir_init = ir.is_initialized

            if not ir.is_initialized:

                print(
                    "⚠️ IRSDK no inicializado"
                )

                time.sleep(1)

                continue

            # 1. Comprobar cambios de sesión/pista
            ir.freeze_var_buffer_latest()

            # =========================
            # 🧠 TELEMETRÍA POR VUELTA
            # =========================
            if self.in_stint and self.current_stint_id:
                try:
                    current_lap = ir['LapCompleted'] or 0
                    is_on_pitroad = ir['OnPitRoad']

                    # Inicializar estado pit
                    if self.last_lap_completed is None:
                        self.last_lap_completed = current_lap
                        self.was_on_pitroad = is_on_pitroad

                    # Detectar PIT IN
                    if not self.was_on_pitroad and is_on_pitroad and (ir['Speed'] or 0) > 7:
                        self.pit_in_this_lap = True
                        print("🟥 PIT IN detectado (Python)")

                    # Detectar PIT OUT
                    if self.was_on_pitroad and not is_on_pitroad:
                        self.pit_out_this_lap = True
                        print("🟩 PIT OUT detectado (Python)")

                    self.was_on_pitroad = is_on_pitroad

                # 🔥 OFFTRACK DETECTION
                    try:
                        surface = ir['PlayerTrackSurface'] or 0

                        # 0 = pista válida, resto = offtrack
                        is_offtrack = surface != 0

                        if is_offtrack and not self.was_offtrack:
                            self.offtrack_lap += 1
                            print(f"🚧 Offtrack detectado ({self.offtrack_lap})")

                        self.was_offtrack = is_offtrack

                    except Exception as e:
                        pass

                    # 🔥 INCIDENT COUNT
                    try:
                        incident_total = ir['PlayerCarDriverIncidentCount'] or 0

                        if incident_total > self.last_incident_total:
                            delta = incident_total - self.last_incident_total
                            self.incident_lap += delta

                            print(f"💥 Incident +{delta} (total lap {self.incident_lap})")

                        self.last_incident_total = incident_total

                    except Exception as e:
                        pass

                    print(f"📊 Lap Stats → OFF:{self.offtrack_lap} INC:{self.incident_lap}")
                    # Detectar nueva vuelta
                    if current_lap != self.last_lap_completed:

                    # 🔥 ENRICH DE VUELTA ANTERIOR (CLAVE)
                        lap_to_update = current_lap - 1

                        if lap_to_update >= 0:

                            try:
                                payload = {
                                    "ir_stint_id": self.current_stint_id,
                                    "lap_number": lap_to_update,

                                    "incident_count": self.incident_lap,
                                    "offtrack_count": self.offtrack_lap,
                                    "is_clean": self.incident_lap == 0 and self.offtrack_lap == 0
                                }

                                requests.post(
                                    f"{BASE_URL}/laps/enrich",
                                    json=payload,
                                    headers=self.get_headers(),
                                    timeout=2
                                )



                                print(f"🧠 Enrich lap {lap_to_update} → inc:{self.incident_lap} off:{self.offtrack_lap}")

                            except Exception as e:
                                print(f"⚠️ Error enrich: {e}")

                        # Reset flags
                        self.pit_in_this_lap = False
                        self.pit_out_this_lap = False

                        self.last_lap_completed = current_lap
                        # 🔥 RESET POR VUELTA
                        self.offtrack_lap = 0
                        self.incident_lap = 0

                    # print(f"DEBUG TELEMETRIA → stint:{self.in_stint} id:{self.current_stint_id}")
                except Exception as e:
                    print(f"⚠️ Error telemetría: {e}")


            track_id, subsession_id, car_path = self.detect_session_change()

            # =====================================
            # IBT - MARCADOR DE SESION
            # =====================================

            session_key = (
                track_id,
                subsession_id,
                car_path
            )

            if session_key != self.last_ibt_session_key:

                self.last_ibt_session_key = session_key

                self.ibt_session_started_at = time.time()

                print(
                    "🕒 Nueva sesión detectada. "
                    "Se registrarán solo IBT posteriores a: "
                    f"{self.ibt_session_started_at}"
                )


            driver = current_driver_data(ir)

            if driver:

                self.current_driver_name = (
                    driver.get("name")
                    or "Unknown Driver"
                )
            # 🔥 FIX CRÍTICO: asegurar track_id en memoria (SIN sesiones)
            if track_id:
                self.current_track_id = int(track_id)


            # 🔥 PRIMERA VEZ
            if self.last_track_id is None:
                self.last_track_id = track_id
                self.last_car_path = car_path
                self.session_registered = False

                print("🟢 Estado inicial capturado")
                print(f"📍 Track en inicio de stint: {track_id}")
                self.current_status = "Session Active"
                # 🔥 CARGAR SECTORES AQUÍ (CLAVE)
                if track_id:
                    print("🧠 Cargando sectores desde API...")
                    self.current_sectors = self.load_track_maps(track_id)

                print(f"📍 Sectores actuales: {self.current_sectors}")


            # 2. Lógica de Stint
            on_track = ir['IsOnTrack']
            speed = ir['Speed'] or 0
            lap_pct = ir['LapDistPct'] or 0

            # 🚗 SYNC CAR (solo una vez y con datos válidos)
            try:
                d_info = ir['DriverInfo']

                try:

                    weekend = ir['WeekendInfo']

                    self.current_track_name = (

                        weekend.get('TrackDisplayName')

                        or

                        weekend.get('TrackName')

                        or

                        'Unknown Track'
                    )

                except:

                    pass

                my_idx = d_info.get('DriverCarIdx')
                my_driver_data = next((d for d in d_info.get('Drivers', []) if d['CarIdx'] == my_idx), None)

                if my_driver_data:
                    car_name = (

                        my_driver_data.get(
                            'CarScreenNameShort'
                        )

                        or

                        my_driver_data.get(
                            'CarScreenName'
                        )

                        or

                        'Unknown'

                    )
                    car_id = my_driver_data.get('CarID', 0)

                    self.current_car_name = car_name
                    self.current_car_path = my_driver_data.get(
                        "CarPath",
                        None
                    )

                    self.sync_car_once(car_id, car_name)

            except Exception as e:
                print(f"⚠️ Error preparando sync car: {e}")

            if (
                on_track
                and not self.in_stint
                and speed > 5
                and lap_pct > 0.01
                and self.car_synced  # 🔥 CLAVE
            ):
                print("🏁 Pista detectada + RPM OK. Iniciando...")
                self.in_stint = True
                self.stint_attempted = True
                self.start_stint()

                # self.send_setup_snapshot()
            elif not on_track and self.in_stint:

                print("🔴 Fin de stint detectado (Fuera de pista)")

                session_time_end = float(ir['SessionTime'] or 0.0)

                # ==========================================
                # TYRES END
                # ==========================================

                try:

                    self.send_tyre_snapshot(
                        "end",
                        ir['LapCompleted'] or 0
                    )

                    print(
                        f"🔴 Tyres END enviados | "
                        f"PitRoad={ir['OnPitRoad']} | "
                        f"Speed={ir['Speed']} | "
                        f"Garage={ir['IsInGarage']}"
                    )

                except Exception as e:

                    print(f"⚠️ Error Tyres END: {e}")

                # ==========================================
                # CERRAR STINT
                # ==========================================

                try:
                    r = requests.post(
                        f"{BASE_URL}/v1/stints/{self.current_stint_id}/end",
                        json={
                            "session_time_end": session_time_end
                        },
                        headers=self.get_headers(),
                        timeout=2
                    )

                    print("📡 STINT END response:", r.status_code)
                    print("📡 STINT END data:", r.text)

                    response = r.json()

                    if response.get("valid"):
                        print("⏱️ Stint válido. Continuando procesamiento...")
                        time.sleep(1)
                        self.handle_stint_end()
                    else:
                        print("🗑️ Stint descartado: no contiene vueltas cronometradas válidas.")

                except Exception as e:
                    print(f"❌ Error cerrando stint: {e}")

                self.in_stint = False
                self.stint_attempted = False
                self.stop_csharp_timer()

            time.sleep(1)

    def register_local_routes(self):

        @self.local_api.route(
            '/ping',
            methods=['POST']
        )

        @self.local_api.route(
            '/repair-setup-file',
            methods=['POST']
        )
        def repair_setup_file():

            data = request.get_json()

            self.current_stint_id = data.get(
                "stint_id"
            )

            self.current_car_path = data.get(
                "car_folder"
            )

            print(
                f"🔧 Repair setup | "
                f"Stint={self.current_stint_id} | "
                f"Car={self.current_car_path}"
            )

            setup_file = copy_current_setup(

                self.current_car_path,

                self.current_stint_id

            )

            if not setup_file:

                return jsonify({

                    "success": False,

                    "message": "Current setup not found"

                }), 404

            self.upload_setup_file(
                setup_file
            )

            return jsonify({

                "success": True

            })

        def ping():

            print(
                "🚀 ORDEN RECIBIDA DESDE IRTEAM"
            )

            return jsonify({
                "status": "ok"
            })

        @self.local_api.route(
            '/install-setup',
            methods=['POST']
        )
        def install_setup():

            data = request.get_json()

            setup_id = data.get(
                'setup_id'
            )

            setup_name = data.get(
                'setup_name'
            )

            car_folder = data.get(
                'car_folder'
            )

            track_name = data.get(
                'track_name'
            )

            print(
                "📦 INSTALL SETUP REQUEST"
            )

            print(
                f"SETUP ID: {setup_id}"
            )

            print(
                f"SETUP NAME: {setup_name}"
            )

            print(
                f"TARGET FOLDER: {car_folder}"
            )
            target_dir = os.path.join(

                os.path.expanduser(
                    "~/Documents/iRacing/setups"
                ),

                car_folder,

                "IRTM",

                track_name
            )

            os.makedirs(
                target_dir,
                exist_ok=True
            )

            url = (

                f"{BASE_URL}"

                f"/setups/{setup_id}/file"
            )

            print(
                f"⬇️ DOWNLOADING: {url}"
            )

            response = requests.get(url)

            print(f"STATUS: {response.status_code}")

            print(f"CONTENT TYPE: {response.headers.get('Content-Type')}")

            target_file = os.path.join(

                target_dir,

                f"{setup_name}.sto"
            )

            if response.status_code != 200:

                print("❌ DOWNLOAD FAILED")

                print(response.text[:500])

                return jsonify({
                    "error": "download failed"
                }), 500

            with open(
                target_file,
                "wb"
            ) as f:

                f.write(
                    response.content
                )

            print(
                f"✅ INSTALLED: {target_file}"
            )

            return jsonify({

                "status": "LOGGER_OK",

                "setup_id": setup_id,

                "setup_name": setup_name,

                "car_folder": car_folder
            })

    def create_tray_icon(self):
        image = Image.open(
            resource_path("icon.ico")
        )
        menu = Menu(

    MenuItem(
        lambda item:
        f"{self.current_driver_name}",
        None,
        enabled=False
    ),

    MenuItem(
        lambda item:
        f"{self.current_car_name}",
        None,
        enabled=False
    ),

    MenuItem(
        lambda item:
        f"{self.current_track_name}",
        None,
        enabled=False
    ),

    MenuItem(
        lambda item:
        f"Status: {self.current_status}",
        None,
        enabled=False
    ),

    Menu.SEPARATOR,

    MenuItem(
        'Reiniciar Todo',
        self.action_restart
    ),

    Menu.SEPARATOR,

    MenuItem(
        'Cerrar Logger',
        self.action_quit
    )
)

        self.icon = Icon("MRT_Logger", image, "iRacing MRT Logger", menu)

        threading.Thread(

            target=lambda: self.local_api.run(

                host='127.0.0.1',

                port=53999,

                debug=False,

                use_reloader=False

            ),

            daemon=True

        ).start()

        threading.Thread(target=self.main_loop, daemon=True).start()
        self.icon.run()

if __name__ == "__main__":
    logger = MRTLogger()
    logger.create_tray_icon()
