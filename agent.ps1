# ====================================================
# YEStudio 硬件配置一键获取 Agent (稳健跳转版)
# ====================================================

# 1. 网络与 TLS 协议兼容设置
try {
    [System.Net.ServicePointManager]::ServerCertificateValidationCallback = {$true}
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.SecurityProtocolType]'Tls,Tls11,Tls12'
} catch {}

Write-Host "Collecting hardware information..." -ForegroundColor Green

# 2. 提取操作系统
$os = "Windows"
try {
    $os = (Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue).Caption.Trim()
} catch {}

# 3. 提取 CPU
$cpu = "Unknown CPU"
try {
    $cpu = (Get-CimInstance Win32_Processor -ErrorAction SilentlyContinue | Select-Object -First 1).Name.Trim()
} catch {}

# 4. 提取内存 (RAM)
$ramStr = "Unknown RAM"
try {
    $mems = Get-CimInstance Win32_PhysicalMemory -ErrorAction SilentlyContinue
    if ($mems) {
        $total = 0
        foreach ($m in $mems) { $total += [int64]$m.Capacity }
        $gb = [Math]::Round($total / 1GB, 2)
        $ramStr = "${gb} GB"
    }
} catch {}

# 5. 提取硬盘 (优化 NVMe/SSD 识别)
$disks = @()
try {
    $diskObjs = Get-CimInstance Win32_DiskDrive -ErrorAction SilentlyContinue
    foreach ($d in $diskObjs) {
        $sz = [Math]::Round([int64]$d.Size / 1GB, 2)
        $md = $d.Model.Trim()
        $isSSD = ($d.MediaType -like "*SSD*") -or 
                 ($md -like "*SSD*") -or 
                 ($md -like "*NVMe*") -or 
                 ($md -like "*Solid State*") -or 
                 ($md -like "*SN530*") -or 
                 ($md -like "*SN750*") -or 
                 ($md -like "*WDC PC*")
                 
        $type = if ($isSSD) { "SSD" } else { "HDD" }
        $disks += "${md}: ${sz} GB (${type})"
    }
} catch {}
if ($disks.Count -eq 0) { $disks += "Unknown Disk" }

# 6. 提取主板
$board = "Unknown Motherboard"
try {
    $b = Get-CimInstance Win32_BaseBoard -ErrorAction SilentlyContinue | Select-Object -First 1
    $board = "$($b.Manufacturer) $($b.Product)".Trim()
} catch {}

# 7. 提取显卡 (过滤 DisplayLink / Remote Display 等虚拟设备)
$gpus = @()
try {
    $gList = Get-CimInstance Win32_VideoController -ErrorAction SilentlyContinue
    foreach ($g in $gList) {
        if ($g.Name) {
            $gpuName = $g.Name.Trim()
            $isVirtual = ($gpuName -like "*DisplayLink*") -or 
                         ($gpuName -like "*Remote Display*") -or 
                         ($gpuName -like "*Basic Render*") -or 
                         ($gpuName -like "*Virtual*") -or 
                         ($gpuName -like "*Indirect*")
                         
            if (-not $isVirtual) {
                $gpus += $gpuName
            }
        }
    }
} catch {}
if ($gpus.Count -eq 0) { $gpus += "Integrated Graphics" }

# 8. 打包 JSON
$specData = @{
    OS = $os
    CPU = $cpu
    RAM = $ramStr
    Disks = $disks
    Motherboard = $board
    GPUs = $gpus
} | ConvertTo-Json -Compress

# 9. 回传服务端并调起网页
$targetUrl = "https://yestudio.co.nz/get_sys_info/upload.php"

Write-Host "Uploading data to server..." -ForegroundColor Green

try {
    $wc = New-Object System.Net.WebClient
    $wc.Encoding = [System.Text.Encoding]::UTF8
    $wc.Headers.Add("Content-Type", "application/json; charset=utf-8")
    
    $resRaw = $wc.UploadString($targetUrl, "POST", $specData)
    $res = $resRaw | ConvertFrom-Json

    if ($res.status -eq "success" -and $res.url) {
        $finalUrl = $res.url
        Write-Host "Opening report page: $finalUrl" -ForegroundColor Cyan
        
        # 第一重唤起机制：Start-Process
        try {
            Start-Process $finalUrl
        } catch {
            # 第二重降级机制：调用 system explorer 打开 URL
            & explorer.exe $finalUrl
        }
    } else {
        & explorer.exe "https://yestudio.co.nz/get_sys_info/"
    }
} catch {
    Write-Host "Upload error, opening main page..." -ForegroundColor Red
    & explorer.exe "https://yestudio.co.nz/get_sys_info/"
}