<?php
header("Content-Type: application/json; charset=UTF-8");

$city = $_GET['city'] ?? '';
if (!$city) {
  echo json_encode(["error" => "缺少城市参数"]);
  exit;
}

$url = "https://wttr.in/" . urlencode($city) . "?format=j1";
$response = @file_get_contents($url);

if (!$response) {
  echo json_encode(["error" => "网络错误"]);
  exit;
}

$data = json_decode($response, true);
if (!$data || !isset($data['current_condition'][0])) {
  echo json_encode(["error" => "城市无效"]);
  exit;
}

$now = $data['current_condition'][0];
$result = [
  "weather" => $now['weatherDesc'][0]['value'] ?? '未知',
  "temp" => $now['temp_C'] ?? '',
  "feelslike" => $now['FeelsLikeC'] ?? ''
];
echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>
