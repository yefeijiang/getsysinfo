<?php
$id = isset($_GET['id']) ? $_GET['id'] : '';
$file = "specs_{$id}.json";

// 如果有 ID，显示纯英文配置单并准备打印
if (!empty($id) && file_exists($file)):
    $specs = json_decode(file_get_contents($file), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Specifications - YEStudio</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; padding: 40px; color: #333; }
        .card { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        h2 { text-align: center; color: #1a73e8; border-bottom: 2px solid #1a73e8; padding-bottom: 10px; margin-top: 0; }
        .item { display: flex; margin-bottom: 15px; border-bottom: 1px dashed #eee; padding-bottom: 10px; }
        .label { width: 160px; font-weight: bold; color: #555; }
        .value { flex: 1; color: #111; }
        .btn-zone { text-align: center; margin-top: 30px; }
        .btn { background: #1a73e8; color: #fff; border: none; padding: 10px 30px; font-size: 16px; border-radius: 4px; cursor: pointer; font-weight: 500; }
        .btn:hover { background: #1557b0; }
        @media print { 
            body { background: #fff; padding: 0; } 
            .card { box-shadow: none; max-width: 100%; padding: 0; } 
            .btn-zone { display: none; } 
        }
    </style>
</head>
<body>
<div class="card">
    <h2>System Specifications</h2>
    <div class="item"><div class="label">OS:</div><div class="value"><?= htmlspecialchars($specs['OS']) ?></div></div>
    <div class="item"><div class="label">Processor (CPU):</div><div class="value"><?= htmlspecialchars($specs['CPU']) ?></div></div>
    <div class="item"><div class="label">Memory (RAM):</div><div class="value"><?= htmlspecialchars($specs['RAM']) ?></div></div>
    <div class="item"><div class="label">Storage:</div><div class="value"><?= implode('<br>', array_map('htmlspecialchars', $specs['Disks'])) ?></div></div>
    <div class="item"><div class="label">Motherboard:</div><div class="value"><?= htmlspecialchars($specs['Motherboard']) ?></div></div>
    <div class="item"><div class="label">Graphics (GPU):</div><div class="value"><?= implode('<br>', array_map('htmlspecialchars', $specs['GPUs'])) ?></div></div>
    <div class="btn-zone"><button class="btn" onclick="window.print()">Print Specifications</button></div>
</div>
</body>
</html>

<?php 
else: 
// 自动获取 agent.ps1 地址
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$agent_url = "{$scheme}://{$host}{$dir}/agent.ps1";

// 精简版命令
$command = "powershell -ExecutionPolicy Bypass -WindowStyle Hidden -Command \"iex ((New-Object Net.WebClient).DownloadString('{$agent_url}'))\"";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YEStudio Specs Fetcher</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #eceff1; padding: 50px; text-align: center; }
        .box { max-width: 750px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        code { display: block; background: #263238; color: #80cbc4; padding: 15px; border-radius: 4px; text-align: left; font-family: monospace; word-break: break-all; margin: 20px 0; font-size: 14px; }
        .btn { background: #009688; color: #fff; border: none; padding: 12px 25px; font-size: 16px; border-radius: 4px; cursor: pointer; font-weight: 500; }
        .btn:hover { background: #00796b; }
    </style>
</head>
<body>
<div class="box">
    <h2>YEStudio Hardware Specs Fetcher</h2>
    <p style="color:#666;">Click the button below to copy the command, press <strong>Win + R</strong> on the target machine, paste, and press Enter.</p>
    <code id="cmdBlock"><?= htmlspecialchars($command) ?></code>
    <button class="btn" onclick="copyCmd()">Copy Command</button>
</div>
<script>
function copyCmd() {
    var text = document.getElementById('cmdBlock').innerText;
    navigator.clipboard.writeText(text);
    alert('Command copied to clipboard! Press Win+R to paste and run.');
}
</script>
</body>
</html>
<?php endif; ?>