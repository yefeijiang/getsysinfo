import platform
import psutil
import wmi
import sys
# ---- Add this block 👇 ----
class DualOutput:
    def __init__(self, file_path):
        self.terminal = sys.stdout
        self.log = open(file_path, "w", encoding="utf-8")

    def write(self, message):
        self.terminal.write(message)
        self.log.write(message)

    def flush(self):
        self.terminal.flush()
        self.log.flush()

sys.stdout = DualOutput("result.txt")
# ---- End of insert ----
memory_type_map = {
    0: "Unknown",
    1: "Other",
    2: "DRAM",
    3: "Synchronous DRAM",
    4: "Cache DRAM",
    5: "EDO",
    6: "EDRAM",
    7: "VRAM",
    8: "SRAM",
    9: "RAM",
    10: "ROM",
    11: "Flash",
    12: "EEPROM",
    13: "FEPROM",
    14: "EPROM",
    15: "CDRAM",
    16: "3DRAM",
    17: "SDRAM",
    18: "SGRAM",
    19: "RDRAM",
    20: "DDR",
    21: "DDR2",
    22: "DDR2 FB-DIMM",
    24: "DDR3",
    25: "FBD2",
    26: "DDR4",
    27: "LPDDR",
    28: "LPDDR2",
    29: "LPDDR3",
    30: "LPDDR4",
    31: "Logical non-volatile device",
    32: "HBM",
    33: "HBM2",
    34: "DDR5"
}

def get_cpu_info():
    c = wmi.WMI()
    cpu_name = c.Win32_Processor()[0].Name.strip()
    print(f"CPU: {cpu_name}")

def get_memory_info():
    c = wmi.WMI()
    try:
        modules = c.Win32_PhysicalMemory()

        total_capacity = 0
        mem_types = set()

        for module in modules:
            total_capacity += int(module.Capacity)
            type_code = int(module.MemoryType)
            mem_types.add(memory_type_map.get(type_code, f"Unknown({type_code})"))

        total_gb = round(total_capacity / (1024 ** 3), 2)
        mem_type_str = ", ".join(mem_types)

        print(f"RAM: {total_gb} GB {mem_type_str}")
    except:
        print("RAM: Unknown")
        
def get_system_info():
    print(f"OS: {platform.system()} {platform.release()} ({platform.version()})")

def get_motherboard_info():
    c = wmi.WMI()
    try:
        board = c.Win32_BaseBoard()[0]
        print(f"Motherboard Manufacturer: {board.Manufacturer}")
        print(f"Motherboard Model: {board.Product}")
    except:
        print("Unable to retrieve motherboard info.")

def get_gpu_info():
    c = wmi.WMI()
    try:
        gpus = c.Win32_VideoController()
        for i, gpu in enumerate(gpus):
            print(f"GPU {i+1}: {gpu.Name}")
    except:
        print("Unable to retrieve GPU info.")

def get_disk_drive_info():
    print("Disk Drives:")
    c = wmi.WMI()
    try:
        for disk in c.Win32_DiskDrive():
            size_gb = round(int(disk.Size) / (1024**3), 2) if disk.Size else "Unknown"
            model = disk.Model.strip()
            media_type = "SSD" if ("ssd" in disk.MediaType.lower() if disk.MediaType else False) or "ssd" in model.lower() or "solid" in model.lower() else "HDD"
            print(f"  - {model}: {size_gb} GB ({media_type})")
    except:
        print("Unable to retrieve disk drive info.")

def main():
    print("System Hardware Overview:\n" + "-" * 30)
    get_system_info()
    get_cpu_info()
    get_memory_info()
    get_disk_drive_info()
    get_motherboard_info()
    get_gpu_info()

if __name__ == "__main__":
    main()