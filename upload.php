<?php
// 允许跨域请求并指定返回类型为 JSON
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_data = file_get_contents('php://input');
    
    if (!empty($raw_data)) {
        // 生成唯一 ID 并保存配置文件
        $id = time() . '_' . rand(1000, 9999);
        file_put_contents("specs_{$id}.json", $raw_data);
        
        // 动态构建精准的完整 URL 访问地址
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $print_url = "{$scheme}://{$host}{$dir}/index.php?id={$id}";
        
        echo json_encode([
            "status" => "success", 
            "url" => $print_url
        ]);
        exit;
    }
}

echo json_encode(["status" => "error", "message" => "No data received"]);