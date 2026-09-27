import irsdk
import requests
import time
import threading
import sys
import os
from datetime import datetime
import yaml
from pystray import Icon, Menu, MenuItem
from PIL import Image, ImageDraw

# --- CONFIGURACIÓN ---
BASE_URL = "http://localhost:8000/api"
ir = irsdk.IRSDK()

###################################################################################################
#       RESTART APP (MODIFICADA: Ahora es un método de clase para consistencia)
###################################################################################################

def restart_app(self, reason="Unknown"):
        """Cierra el proceso actual y lanza una instancia nueva del script."""
        print(f"\n🔄 REINICIANDO LOGGER: {reason}\n")
        try:
            ir.shutdown()
        except:
            pass
        python = sys.executable
        os.execv(python, [python] + sys.argv)

class MRTLogger:
    def __init__(self):
        self.token = None
        self.last_sub_id = None
        self.db_session_id = None
        self.current_stint_id = None
        self.current_track_id = None  # Agregado para evitar error en primera comparación
        self.in_stint = False
        self.stop_event = threading.Event()
        self.icon = None
        self.last_lap_completed = -1
        self.fuel_at_start = 0      # Gasolina al salir de boxes
        self.fuel_at_lap_start = 0  # Gasolina al cruzar la meta

        # Lógica de Sectores
        self.last_pct = 0  # <--- AÑADE ESTA LÍNEA
        self.sector_start_time = 0
        self.sectors = []           # Lista de porcentajes de inicio de sector
        self.current_sector = 0     # Sector actual # Momento en que inició el sector actual
        self.current_lap_sectors = [] # Buffer para enviar a la DB al final de la vuelta
        self.telemetry_path = r"C:\Users\Monte\Documents\iRacing\telemetry"

    def action_quit(self, icon, item):
        print("👋 Apagando servicios...")
        icon.stop()
        os._exit(0)

###################################################################################################
#       TOKEN
###################################################################################################

    def fetch_token(self):
        """Paso 0: Obtener Token del Piloto (Espera infinita)"""
        print("🔍 Buscando iRacing...")
        while not self.stop_event.is_set():
            # Intentamos arrancar el SDK en cada ciclo
            if ir.startup() and ir.is_connected:
                d_info = ir['DriverInfo']

                # Verificamos que iRacing ya haya cargado los datos del piloto
                if d_info and isinstance(d_info, dict) and d_info.get('DriverUserID'):
                    u_id = d_info['DriverUserID']
                    try:
                        r = requests.post(f"{BASE_URL}/logger/token", json={"iracing_user_id": u_id}, timeout=5)
                        if r.status_code == 200:
                            self.token = r.json().get("api_token")
                            print(f"✅ Token obtenido para User {u_id}. Entrando en monitor...")
                            return True
                    except Exception as e:
                        print(f"❌ Error API Token (reintentando...): {e}")

            # Si no hay conexión o no hay UserID, simplemente esperamos.
            # No imprimimos en cada ciclo para no inundar la consola,
            # solo mantenemos el proceso vivo.
            time.sleep(3)

        return False

###################################################################################################
#       REGISTER SESSION
###################################################################################################

    def register_session_db(self):
        """Paso 1: Registrar sesión (Soporta Sesiones Oficiales y Test Drive)"""
        try:
            from datetime import datetime

            w_info = ir['WeekendInfo']
            # MODIFICACIÓN: Si WeekendInfo es None o está vacío, salimos para no congelar
            if not w_info or not isinstance(w_info, dict):
                return False

            sub_id = w_info.get('SubSessionID')

            # --- LÓGICA DE IDENTIFICACIÓN DE SESIÓN ---
            if sub_id is None or int(sub_id) <= 0:
                is_official = False
                now_str = datetime.now().strftime('%Y%m%d%H%M%S')
                final_sub_id = f"TEST-{now_str}"
            else:
                is_official = True
                final_sub_id = str(sub_id)

            # --- OBTENCIÓN DE DATOS DE SESIÓN ---
            try:
                s_info = ir['SessionInfo']
                s_num = ir['SessionNum']
                # Verificación extra de estructura de SessionInfo
                if s_info and 'Sessions' in s_info and len(s_info['Sessions']) > s_num:
                    s_type = s_info['Sessions'][s_num].get('SessionType', "Unknown")
                else:
                    s_type = "Offline/Test"
            except:
                s_type = "Offline/Test"

            # --- PAYLOAD ---
            payload = {
                "session": {
                    "iracing_subsession_id": final_sub_id,
                    "session_type": str(s_type),
                    "sof": int(ir['SessionResultsSOF'] or 0),
                    "session_at": datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
                    "is_official": is_official
                },
                "track": {
                    "iracing_track_id": int(w_info.get('TrackID', 0))
                }
            }

            headers = {"Authorization": f"Bearer {self.token}", "Accept": "application/json"}
            r = requests.post(f"{BASE_URL}/init-session", json=payload, headers=headers, timeout=12)

            if r.status_code in [200, 201]:
                response_data = r.json()
                self.db_session_id = response_data.get('db_session_id')
                self.last_sub_id = sub_id
                tipo_str = "🏁 OFICIAL" if is_official else "🧪 TEST/PRIVADA"
                print(f"✅ [DB] Sesión {tipo_str} registrada (ID: {final_sub_id} | DB ID: {self.db_session_id})")
                self.load_track_sectors()
                return True
            else:
                print(f"❌ Error API en Registro: {r.status_code} - {r.text}")

        except Exception as e:
            print(f"❌ Error crítico en register_session_db: {e}")

        return False

###################################################################################################
#       LOAD SECTORS
###################################################################################################

    def load_track_sectors(self):
        try:
            w_info = ir['WeekendInfo']
            if not w_info: return
            track_id = w_info.get('TrackID')
            headers = {"Authorization": f"Bearer {self.token}", "Accept": "application/json"}
            r = requests.get(f"{BASE_URL}/track-sectors/{track_id}", headers=headers, timeout=5)

            if r.status_code == 200:
                data = r.json()
                splits = data.get("sector_splits", [])
                self.sectors = sorted(list(set([0.0] + [float(s) for s in splits])))
                print(f"✅ Mapa de sectores cargado (Manual): {self.sectors}")
            else:
                print(f"⚠️ Track {track_id} sin mapa manual. Usando sectores genéricos [0.0, 0.33, 0.66]")
                self.sectors = [0.0, 0.33, 0.66]
        except Exception as e:
            print(f"❌ Error al pedir mapa: {e}")
            self.sectors = [0.0, 0.33, 0.66]

###################################################################################################
#       START STINT
###################################################################################################

    def start_stint(self):
        """Capa 2: Registro de Stint"""
        try:
            d_info = ir['DriverInfo']
            if not d_info: return

            my_idx = d_info.get('DriverCarIdx')
            my_driver_data = next((d for d in d_info.get('Drivers', []) if d['CarIdx'] == my_idx), None)

            car_name = my_driver_data.get('CarScreenName', 'Unknown') if my_driver_data else "Unknown"
            car_id = my_driver_data.get('CarID', 0) if my_driver_data else 0

            # Verificación de combustible para evitar Crash si devuelve None
            fuel_val = ir['FuelLevel']
            fuel_initial = float(fuel_val) if fuel_val is not None else 0.0

            payload = {
                "iracing_subsession_id": str(self.last_sub_id), # Pasado a str para compatibilidad con TEST-ID
                "track_id": int(ir['WeekendInfo'].get('TrackID', 0)),
                "car_name": car_name,
                "car_id": int(car_id),
                "sim_time_day": float(ir['SessionTimeOfDay'] or 0),
                "fuel_setup": fuel_initial,
                "track_temp": float(ir['TrackTemp'] or 0),
                "air_temp": float(ir['AirTemp'] or 0),
                "track_usage_pct": float(ir['TrackCleanup'] or 0)
            }

            headers = {"Authorization": f"Bearer {self.token}", "Accept": "application/json"}
            r = requests.post(f"{BASE_URL}/start-stint", json=payload, headers=headers)

            if r.status_code in [200, 201]:
                self.current_stint_id = r.json().get('stint_id')
                self.fuel_at_start = fuel_initial
                self.fuel_at_lap_start = fuel_initial
                print(f"⛽ Stint iniciado! ID:{self.current_stint_id}")
            else:
                print(f"❌ Error API Stint: {r.text}")
        except Exception as e:
            print(f"❌ Error capturando datos del coche: {e}")

###################################################################################################
#       MAIN LOOP
###################################################################################################

    def main_loop(self):
        if not self.fetch_token(): return

        # MODIFICACIÓN: last_lap_time_seen se inicializa en -1 para evitar fallos de comparación
        last_lap_time_seen = -1
        print("🚀 Monitor activo...")

        while not self.stop_event.is_set():
            # 1. GESTIÓN DE CONEXIÓN
            if not ir.is_connected:
                ir.startup()
                if not ir.is_connected:
                    restart_app("iRacing cerrado")
                    time.sleep(2)
                    continue
                else:
                    print("🏁 ¡Conectado a iRacing!")

            # 2. DETECCIÓN DE CAMBIO DE PISTA
            try:
                w_info = ir['WeekendInfo']
                if w_info and isinstance(w_info, dict):
                    current_track = w_info.get('TrackID')
                    if self.current_track_id is not None and current_track != self.current_track_id:
                        self.restart_app(f"Cambio de pista: {self.current_track_id} -> {current_track}")
            except:
                time.sleep(0.5)
                continue

            # 3. GESTIÓN DE SESIÓN
            try:
                w_info = ir['WeekendInfo']
                if not w_info:
                    time.sleep(1)
                    continue

                curr_sub_id = w_info.get('SubSessionID')

                if not self.db_session_id or (curr_sub_id is not None and curr_sub_id != self.last_sub_id):
                    if self.register_session_db():
                        self.current_track_id = w_info.get('TrackID')
                    else:
                        time.sleep(5)
                        continue
            except Exception as e:
                time.sleep(1)
                continue

            # 4. LÓGICA DE TELEMETRÍA
            if self.db_session_id:
                try:
                    on_track = ir['IsOnTrack']
                    current_last_lap_time = ir['LapLastLapTime']
                    rpm = ir['RPM'] or 0

                    # --- INICIO DE STINT ---
                    if on_track and not self.in_stint and rpm > 1000:
                        print("🟢 Inicio de stint")
                        self.in_stint = True
                        self.fuel_at_start = ir['FuelLevel'] or 0
                        self.fuel_at_lap_start = self.fuel_at_start
                        self.sector_start_time = ir['SessionTime'] or 0
                        self.current_sector = 0
                        self.current_lap_sectors = []
                        last_lap_time_seen = current_last_lap_time
                        self.start_stint()

                    # --- PROCESAMIENTO EN PISTA ---
                    if self.in_stint and on_track:
                        cp_pct = ir['LapDistPct'] or 0
                        session_time = ir['SessionTime'] or 0

                        # 1. DETECTAR CRUCE DE META (Fin de vuelta / Fin del último sector)
                        if cp_pct < self.last_pct and self.last_pct > 0.8:
                            # Cerramos el último sector de la vuelta anterior
                            last_sector_duration = session_time - self.sector_start_time
                            self.current_lap_sectors.append({
                                "sector_number": self.current_sector + 1,
                                "sector_time": round(last_sector_duration, 4)
                            })
                            print(f"🏁 Último Sector: {last_sector_duration:.3f}s (Vuelta Completa)")

                            # RESET PARA NUEVA VUELTA
                            self.current_sector = 0
                            self.sector_start_time = session_time

                        # 2. LÓGICA DE SECTORES INTERMEDIOS
                        if self.sectors:
                            # Buscamos si hemos pasado el umbral del SIGUIENTE sector
                            # self.sectors suele ser [0.0, 0.33, 0.66]
                            next_sector_index = self.current_sector + 1

                            if next_sector_index < len(self.sectors):
                                target_pct = self.sectors[next_sector_index]

                                if cp_pct >= target_pct:
                                    sector_duration = session_time - self.sector_start_time
                                    self.current_lap_sectors.append({
                                        "sector_number": self.current_sector + 1,
                                        "sector_time": round(sector_duration, 4)
                                    })
                                    print(f"🚩 Sector {self.current_sector + 1}: {sector_duration:.3f}s")

                                    # Avanzamos al siguiente
                                    self.current_sector = next_sector_index
                                    self.sector_start_time = session_time

                        self.last_pct = cp_pct # Guardamos para detectar el cruce de meta en el siguiente frame

                        # Lógica de Vueltas
                        if current_last_lap_time != last_lap_time_seen and current_last_lap_time > 0:
                            if current_last_lap_time > 5:
                                total_recorded = sum(s['sector_time'] for s in self.current_lap_sectors)
                                last_sector_time = current_last_lap_time - total_recorded
                                self.current_lap_sectors.append({
                                    "sector_number": self.current_sector + 1,
                                    "sector_time": round(last_sector_time, 4)
                                })
                                self.registrar_vuelta(ir['LapCompleted'], current_last_lap_time)

                            self.current_sector = 0
                            self.sector_start_time = ir['SessionTime'] or 0
                            self.current_lap_sectors = []
                            last_lap_time_seen = current_last_lap_time

                    # --- FIN DE STINT ---
                    if not on_track and self.in_stint:
                        print("🔴 Fin de stint")
                        self.in_stint = False
                        self.current_lap_sectors = []
                        last_lap_time_seen = -1

                except Exception as e:
                    print(f"❌ Error leyendo telemetría: {e}")

            time.sleep(0.1 if self.in_stint else 1.0)

    def registrar_vuelta(self, lap_num, lap_time):
        try:
            fuel_now = ir['FuelLevel'] or 0
            fuel_start = self.fuel_at_lap_start if self.fuel_at_lap_start > 0 else self.fuel_at_start
            consumed = round(max(0, fuel_start - fuel_now), 3)

            payload = {
                "stint_id": self.current_stint_id,
                "lap_number": int(lap_num),
                "lap_time": float(lap_time),
                "fuel_lap_start": float(fuel_start),
                "fuel_consumed": float(consumed),
                "sectors": self.current_lap_sectors
            }

            self.fuel_at_lap_start = fuel_now
            headers = {"Authorization": f"Bearer {self.token}", "Accept": "application/json"}
            r = requests.post(f"{BASE_URL}/add-lap", json=payload, headers=headers, timeout=5)

            if r.status_code in [200, 201]:
                print(f"⏱️ Vuelta {lap_num}: {lap_time:.3f} | Consumo: {consumed}L")
            else:
                print(f"❌ Error API Vuelta: {r.text}")
        except Exception as e:
            print(f"❌ Error registrar_vuelta: {e}")

    def create_tray_icon(self):
        image = Image.new('RGB', (64, 64), (30, 30, 30))
        draw = ImageDraw.Draw(image)
        draw.ellipse([10, 10, 54, 54], fill=(200, 0, 0))
        menu = Menu(
            MenuItem('MRT Logger Activo', lambda: None, enabled=False),
            MenuItem('Reiniciar Conexión', self.action_restart),
            Menu.SEPARATOR,
            MenuItem('Cerrar Logger', self.action_quit)
        )
        self.icon = Icon("MRT_Logger", image, "iRacing MRT Logger", menu)
        threading.Thread(target=self.main_loop, daemon=True).start()
        self.icon.run()

    def action_restart(self, icon, item):
        self.last_sub_id = None
        self.db_session_id = None
        self.in_stint = False
        print("🔄 Reiniciando tracking...")

if __name__ == "__main__":
    logger = MRTLogger()
    logger.create_tray_icon()
