<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Europe/Kiev');

$name  = trim($_POST['name']  ?? '');
$phone = trim($_POST['phone'] ?? '');
$items = trim($_POST['items'] ?? '');
$total = trim($_POST['total'] ?? '');

if (!$name || !$phone || !$items) {
    echo json_encode(['ok'=>false, 'msg'=>'Заповніть всі поля']);
    exit;
}

$file = __DIR__ . '/data/orders.csv';

// Якщо файл не існує або порожній — створюємо з BOM та заголовком
if (!file_exists($file) || filesize($file) === 0) {
    $h = fopen($file, 'w');
    fwrite($h, "\xEF\xBB\xBF"); // UTF-8 BOM для Excel
    fputcsv($h, ['Дата', "Ім'я клієнта", 'Телефон', 'Сума', 'Товари'], ';');
    fclose($h);
}

// Дописуємо новий рядок
$handle = fopen($file, 'a');
if ($handle) {
    fputcsv($handle, [
        date('d.m.Y H:i'),
        $name,
        $phone,
        $total . ' грн',
        $items
    ], ';');
    fclose($handle);
    echo json_encode(['ok'=>true]);
} else {
    
}
?>
