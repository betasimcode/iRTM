import irsdk
import time

ir = irsdk.IRSDK()

print("Esperando startup...")

while not ir.startup():
    time.sleep(1)

print("SDK iniciado")

while True:
    ir.freeze_var_buffer_latest()

    print("ALL KEYS:", list(ir.var_headers_names))

    time.sleep(2)
