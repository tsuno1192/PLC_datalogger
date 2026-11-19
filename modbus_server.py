import logging
from pymodbus.server.sync import StartTcpServer
from pymodbus.datastore import ModbusSequentialDataBlock, ModbusSlaveContext, ModbusServerContext

logging.basicConfig(level=logging.INFO)

def run_server():
    # レジスタごとに異なる初期値を設定（例: 1200, 1210, 1220... と変化させる）
    initial_values = [1200 + (i * 10) for i in range(100)]

    store = ModbusSlaveContext(
        hr=ModbusSequentialDataBlock(0, initial_values)
    )
    context = ModbusServerContext(slaves=store, single=True)

    print("Starting Modbus TCP Server on 127.0.0.1:5020 ...")
    StartTcpServer(context, address=("127.0.0.1", 5020))

if __name__ == "__main__":
    run_server()