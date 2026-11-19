from datetime import datetime
import time
import mysql.connector
from pymodbus.client.sync import ModbusTcpClient
from pymodbus.exceptions import ModbusException

# --- 設定値 ---
PLC_IP = "127.0.0.1"
PLC_PORT = 5020
POLL_INTERVAL = 1.0  # 取得周期（秒）
TAG_RELOAD_INTERVAL = 60  # タグマスターを再読み込みする間隔（秒）

# MySQL接続設定（Laravel Sail環境）
DB_CONFIG = {
    "host": "localhost",
    "port": 3307,
    "database": "laravel",
    "user": "sail",
    "password": "password",
}


def get_db_connection():
    return mysql.connector.connect(**DB_CONFIG)


def fetch_active_tags():
    """MySQLの modbus_tags テーブルから有効なタグ一覧を取得する"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        # 実際のデータベース構造（register_address と name）に合わせる
        cursor.execute(
        "SELECT id, register_address AS address, tag_name AS name FROM modbus_tags WHERE is_active = 1"
         )
        tags = cursor.fetchall()
        cursor.close()
        conn.close()
        return tags
    except Exception as e:
        print(f"[{datetime.now()}] Failed to fetch tags from DB: {e}")
        return []

def log_communication_status(device_name, status, message):
    """通信断・復旧のイベントをDBに記録する"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        query = """
            INSERT INTO communication_logs (device_name, status, message, event_at, created_at, updated_at)
            VALUES (%s, %s, %s, %s, NOW(), NOW())
        """
        cursor.execute(query, (device_name, status, message, datetime.now()))
        conn.commit()
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"[{datetime.now()}] Failed to write communication log: {e}")


def main():
    client = ModbusTcpClient(PLC_IP, port=PLC_PORT, timeout=2.0)
    is_connected_prev = False

    print(f"[{datetime.now()}] Modbus Poller started. Target: {PLC_IP}:{PLC_PORT}")

    # 初回タグ情報の取得
    tags = fetch_active_tags()
    print(f"[{datetime.now()}] Loaded {len(tags)} tags from database.")
    last_tag_reload_time = time.time()

    try:
        # 最初に一度接続を試みる
        if client.connect():
            print(f"[{datetime.now()}] Connected to PLC.")
            log_communication_status(PLC_IP, "CONNECTED", "Connection established.")
            is_connected_prev = True
        else:
            print(f"[{datetime.now()}] Initial connection failed, will retry in loop...")

        while True:
            start_time = time.time()

            # 定期的にタグマスターの変更を再読み込みする
            if time.time() - last_tag_reload_time > TAG_RELOAD_INTERVAL:
                new_tags = fetch_active_tags()
                if new_tags:
                    tags = new_tags
                last_tag_reload_time = time.time()

            try:
                # 未接続の場合は接続を試みる
                if not client.connect():
                    time.sleep(POLL_INTERVAL)
                    continue

                if not is_connected_prev:
                    print(f"[{datetime.now()}] Connected to PLC.")
                    log_communication_status(PLC_IP, "CONNECTED", "Connection established.")
                    is_connected_prev = True

                if not tags:
                    print(f"[{datetime.now()}] Warning: No tags found in database. Retrying...")
                    time.sleep(POLL_INTERVAL)
                    continue

                recorded_at = datetime.now()
                conn = get_db_connection()
                cursor = conn.cursor()

                # 登録されているタグごとにModbusから値を読み込む
                for tag in tags:
                    tag_id = tag["id"]
                    address = tag["address"]

                    result = client.read_holding_registers(
                        address=address, count=1, slave=1
                    )

                    if result.isError():
                        print(f"[{datetime.now()}] Modbus Read Error at address {address}")
                        continue

                    val = result.registers[0]
                    float_val = float(val)

                    # modbus_logs テーブルへ保存
                    insert_query = """
                        INSERT INTO modbus_logs (modbus_tag_id, value, recorded_at, created_at, updated_at)
                        VALUES (%s, %s, %s, NOW(), NOW())
                    """
                    cursor.execute(insert_query, (tag_id, float_val, recorded_at))

                conn.commit()
                cursor.close()
                conn.close()

            except Exception as e:
                print(f"[{datetime.now()}] Communication Error: {e}")
                if is_connected_prev:
                    log_communication_status(PLC_IP, "DISCONNECTED", str(e))
                    is_connected_prev = False
                try:
                    client.close()
                except:
                    pass

            # ポーリング周期の調整
            elapsed = time.time() - start_time
            sleep_time = max(0, POLL_INTERVAL - elapsed)
            time.sleep(sleep_time)

    except KeyboardInterrupt:
        print(f"[{datetime.now()}] Poller stopped by user.")
        client.close()


if __name__ == "__main__":
    main()