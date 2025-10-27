<?php
header("Content-Type: application/json; charset=UTF-8");

$file = "records.json";

// 确保文件存在
if (!file_exists($file)) file_put_contents($file, json_encode([], JSON_UNESCAPED_UNICODE));

$data = json_decode(file_get_contents($file), true);
if (!is_array($data)) $data = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if ($_GET['action'] === 'list') {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents("php://input"), true);
  $action = $input['action'] ?? '';
  $record = $input['record'] ?? [];

  if ($action === 'add') {
    $data[] = $record;
  } elseif ($action === 'edit') {
    foreach ($data as &$r) {
      if ($r['id'] == $record['id']) {
        $r = $record;
        break;
      }
    }
  } elseif ($action === 'delete') {
    $id = $input['id'];
    $data = array_values(array_filter($data, fn($r) => $r['id'] != $id));
  }

  file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
  echo json_encode(["success" => true]);
  exit;
}
?>
