<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Europe/Kiev');

$name    = trim($_POST['name']    ?? '');
$contact = trim($_POST['contact'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name || !$contact || !$message) {
    echo json_encode(['ok'=>false, 'msg'=>'Заповніть всі поля']);
    exit;
}

$file = __DIR__ . '/data/messages.csv';

if (!file_exists($file) || filesize($file) === 0) {
    $h = fopen($file, 'w');
    fwrite($h, "\xEF\xBB\xBF");
    fputcsv($h, ['Дата', "Ім'я клієнта", 'Контакт', 'Повідомлення'], ';');
    fclose($h);
}

$handle = fopen($file, 'a');
if ($handle) {
    fputcsv($handle, [
        date('d.m.Y H:i'),
        $name,
        $contact,
        $message
    ], ';');
    fclose($handle);
    echo json_encode(['ok'=>true]);
} else {
    echo json_encode(['ok'=>false, 'msg'=>'Не вдалось відкрити messages.csv']);
}
?>
