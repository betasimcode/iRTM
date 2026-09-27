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
#       RESTART APP (GLOBAL)
###################################################################################################

def restart_app(reason="Unknown"):
    """Cierra el proceso actual y lanza una instancia nueva del script."""
    print(f"\n🔄 REINICIANDO LOGGER: {reason}\n")
    try:
        ir.shutdown()
    except:
        pass

    python = sys.executable
    os.execv(python, [python] + sys.argv)

###################################################################################################
#       CLASE PRINCIPAL
###################################################################################################

class MRTLogger:
    def __init__(self):
        self.token = None
        self.last_sub_id = None
        self.db_session_id = None
        self.current_track_id = None # IMPORTANTE: Inicializado para evitar errores
        self.current_stint_id = None
        self.in_stint = False
        self.stop_event = threading.Event()
        self.icon = None

        self.fuel_at_start = 0      # Gasolina al salir de boxes
        self.fuel_at_lap_start = 0  # Gasolina al cruzar la meta

        # Lógica de Sectores
        self.sectors = []           # Lista de porcentajes de inicio de sector
        self.current_sector = 0     # Sector actual
        self.sector_start_time = 0  # Momento en que inició el sector actual
        self.current_lap_sectors = [] # Buffer para enviar a la DB al final de la vuelta

    def action_quit(self, icon, item):
        print("👋 Apagando servicios...")
        if icon:
            icon.stop()
        os._exit(0)

    def action_restart(self, icon, item):
        """Reinicia el tracking o la app completa"""
        restart_app("Reinicio manual desde Tray")

###################################################################################################
#       TOKEN & SESSION
###################################################################################################

    def fetch_token(self):
        """Paso 0: Obtener Token del Piloto"""
        print("🔍 Esperando conexión con iRacing para obtener Token...")
        while not self.stop_event.is_set():
            if ir.startup() and ir.is_connected:
                d_info = ir['DriverInfo']
                if d_info and d_info.get('DriverUserID'):
                    u_id = d_info['DriverUserID']
                    try:
                        r = requests.post(f"{BASE_URL}/logger/token", json={"iracing_user_id": u_id}, timeout=5)
                        if r.status_code == 200:
                            self.token = r.json().get("api_token")
                            print(f"✅ Token obtenido para User {u_id}")
                            return True
                    except Exception as e:
                        print(f"❌ Error API Token: {e}")
            time.sleep(3)
        return False

    def register_session_db(self):
        """Paso 1: Registrar sesión (Oficial o Test Drive)"""
        try:
            w_info = ir['WeekendInfo']
            sub_id = w_info.get('SubSessionID')

            # Identificación de sesión privada
            if sub_id is None or int(sub_id) <= 0:
                is_official = False
                now_str = datetime.now().strftime('%Y%m%d%H%M%S')
                final_sub_id = f"TEST-{now_str}"
            else:
                is_official = True
                final_sub_id = str(sub_id)

            try:
                s_info = ir['SessionInfo']
                s_num = ir['SessionNum']
                s_type = s_info['Sessions'][s_num]['SessionType']
            except:
                s_type = "Offline/Test"

            payload = {
                "session": {
                    "iracing_subsession_id": final_sub_id,
                    "session_type": str(s_type),
                    "sof": int(ir['SessionResultsSOF'] or 0),
                    "session_at": datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
                    "is_official": is_official
                },
                "track": { "iracing_track_id": int(w_info.get('TrackID', 0)) }
            }

            headers = {"Authorization": f"Bearer {self.token}", "Accept": "application/json"}
            r = requests.post(f"{BASE_URL}/init-session", json=payload, headers=headers, timeout=10)

            if r.status_code in [200, 201]:
                self.db_session_id = r.json().get('db_session_id')
                self.last_sub_id = sub_id
                self.current_track_id = w_info.get('TrackID')

                tipo_str = "🏁 OFICIAL" if is_official else "🧪 TEST/PRIVADA"
                print(f"✅ [DB] Sesión {tipo_str} registrada (ID: {final_sub_id})")

                self.load_track_sectors()
                return True
        except Exception as e:
            print(f"❌ Error crítico en register_session_db: {e}")
        return False

    def load_track_sectors(self):
        try:
            track_id = self.current_track_id
            headers = {"Authorization": f"Bearer {self.token}", "Accept": "application/json"}
            r = requests.get(f"{BASE_URL}/track-sectors/{track_id}", headers=headers, timeout=5)

            if r.status_code == 200:
                data = r.json()
                splits = data.get("sector_splits", [])
                self.sectors = sorted(list(set([0.0] + [float(s) for s in splits])))
                print(f"✅ Mapa de sectores cargado: {self.sectors}")
            else:
                print(f"⚠️ Usando sectores genéricos para track {track_id}")
                self.sectors = [0.0, 0.33, 0.66]
        except Exception as e:
            print(f"❌ Error al pedir mapa: {e}")

###################################################################################################
#       TELEMETRÍA Y STINTS
###################################################################################################

    def start_stint(self):
        try:
            d_info = ir['DriverInfo']
            my_idx = d_info.get('DriverCarIdx')
            my_driver_data = next((d for d in d_info['Drivers'] if d['CarIdx'] == my_idx), None)

            car_name = my_driver_data.get('CarScreenName', 'Unknown') if my_driver_data else "Unknown"
            car_id = my_driver_data.get('CarID', 0) if my_driver_data else 0
            fuel_initial = float(ir['FuelLevel'])

            payload = {
                "iracing_subsession_id": str(self.last_sub_id),
                "track_id": int(self.current_track_id),
                "car_name": car_name,
                "car_id": int(car_id),
                "fuel_setup": fuel_initial,
                "track_temp": float(ir['TrackTemp']),
                "air_temp": float(ir['AirTemp'])
            }

            headers = {"Authorization": f"Bearer {self.token}", "Accept": "application/json"}
            r = requests.post(f"{BASE_URL}/start-stint", json=payload, headers=headers)

            if r.status_code in [200, 201]:
                self.current_stint_id = r.json().get('stint_id')
                self.fuel_at_start = fuel_initial
                self.fuel_at_lap_start = fuel_initial
                print(f"⛽ Stint iniciado! ID:{self.current_stint_id}")
        except Exception as e:
            print(f"❌ Error start_stint: {e}")

    def registrar_vuelta(self, lap_num, lap_time):
        try:
            fuel_now = ir['FuelLevel']
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
        except Exception as e:
            print(f"❌ Error registrar_vuelta: {e}")

###################################################################################################
#       MAIN LOOP
###################################################################################################

    def main_loop(self):
        if not self.fetch_token(): return

        last_lap_time_seen = None
        print("🚀 Monitor activo...")

        while not self.stop_event.is_set():
            # 1. GESTIÓN DE CONEXIÓN
            if not ir.is_connected:
                ir.startup()
                if not ir.is_connected:
                    time.sleep(2)
                    continue
                else:
                    print("🏁 ¡Conectado a iRacing!")

            # 2. DETECCIÓN DE CAMBIO DE PISTA
            try:
                if ir['WeekendInfo']:
                    current_track = ir['WeekendInfo'].get('TrackID')
                    if self.current_track_id and current_track != self.current_track_id:
                        restart_app(f"Cambio de pista: {current_track}")
            except:
                pass

            # 3. GESTIÓN DE SESIÓN
            try:
                curr_sub_id = ir['WeekendInfo'].get('SubSessionID')
                if not self.db_session_id or (curr_sub_id != self.last_sub_id):
                    if not self.register_session_db():
                        time.sleep(5)
                        continue
            except:
                time.sleep(1)
                continue

            # 4. TELEMETRÍA
            if self.db_session_id:
                try:
                    on_track = ir['IsOnTrack']
                    # Usamos LapCompleted para saber si realmente hemos terminado una vuelta nueva
                    laps_completed = ir['LapCompleted']
                    current_last_lap_time = ir['LapLastLapTime']
                    rpm = ir['RPM']

                    if on_track and not self.in_stint and rpm > 1000:
                        print("🟢 Inicio de stint")
                        self.in_stint = True
                        self.sector_start_time = ir['SessionTime']
                        self.current_sector = 0
                        self.current_lap_sectors = []
                        # Inicializamos con la vuelta actual para no registrar basura al entrar
                        self.last_lap_completed = laps_completed
                        self.start_stint()

                    if self.in_stint and on_track:
                        # --- LÓGICA DE SECTORES ---
                        if self.sectors:
                            cp_pct = ir['LapDistPct']
                            session_time = ir['SessionTime']
                            detected_sector = 0
                            for i, start_pct in enumerate(self.sectors):
                                if cp_pct >= start_pct: detected_sector = i

                            if detected_sector > self.current_sector:
                                sector_duration = session_time - self.sector_start_time
                                self.current_lap_sectors.append({
                                    "sector_number": self.current_sector + 1,
                                    "sector_time": round(sector_duration, 4)
                                })
                                print(f"🚩 Sector {self.current_sector + 1}: {sector_duration:.3f}s")
                                self.current_sector = detected_sector
                                self.sector_start_time = session_time

                        # --- LÓGICA DE VUELTAS (CORREGIDA) ---
                        # Si el número de vueltas completadas ha aumentado
                        if laps_completed > self.last_lap_completed:
                            if current_last_lap_time > 0:
                                # Calculamos el tiempo del último sector (lo que falta para el total)
                                total_recorded_sectors = sum(s['sector_time'] for s in self.current_lap_sectors)
                                last_sector_time = max(0, current_last_lap_time - total_recorded_sectors)

                                self.current_lap_sectors.append({
                                    "sector_number": self.current_sector + 1,
                                    "sector_time": round(last_sector_time, 4)
                                })

                                # Registramos la vuelta
                                self.registrar_vuelta(laps_completed, current_last_lap_time)

                            # Reset para la siguiente vuelta
                            self.last_lap_completed = laps_completed
                            self.current_sector = 0
                            self.sector_start_time = ir['SessionTime']
                            self.current_lap_sectors = []

                    if not on_track and self.in_stint:
                        print("🔴 Fin de stint")
                        self.in_stint = False
                        self.last_lap_completed = -1 # Reset para el próximo stint

                except Exception as e:
                    print(f"❌ Error telemetría: {e}")

            time.sleep(0.1 if self.in_stint else 1.0)

###################################################################################################
#       TRAY & EXECUTION
###################################################################################################

    def create_tray_icon(self):
        image = Image.new('RGB', (64, 64), (30, 30, 30))
        draw = ImageDraw.Draw(image)
        draw.ellipse([10, 10, 54, 54], fill=(200, 0, 0))
        menu = Menu(
            MenuItem('MRT Logger Activo', lambda: None, enabled=False),
            MenuItem('Reiniciar App', self.action_restart),
            Menu.SEPARATOR,
            MenuItem('Cerrar Logger', self.action_quit)
        )
        self.icon = Icon("MRT_Logger", image, "iRacing MRT Logger", menu)
        threading.Thread(target=self.main_loop, daemon=True).start()
        self.icon.run()

if __name__ == "__main__":
    logger = MRTLogger()
    logger.create_tray_icon()
