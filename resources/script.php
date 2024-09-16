<?php
require_once __DIR__ . '/../db/db.php';
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

function handleError($message) {
    http_response_code(500);
    echo json_encode(['error' => $message]);
    exit;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$encabezados = array_map('strtoupper', ['Nombre', 'Fecha de Creación', 'Estado']);
$sheet->fromArray($encabezados, null, 'A1');

$sheet->getStyle('A1:C1')->getFont()->setBold(true)->setSize(15);

$sql = "SELECT DISTINCT t.name, t.creation_date, e.name AS state
        FROM tasks t
        JOIN statuses e ON t.status_id = e.id
        WHERE e.name = 'Realizada'
        ORDER BY t.creation_date DESC";

try {
    $result = $conn->Execute($sql);
    if (!$result) {
        throw new Exception('No se pudo ejecutar la consulta');
    }

    $fila = 2;
    while ($row = $result->FetchRow()) {
        $row['creation_date'] = date('d/m/Y H:i', strtotime($row['creation_date']));
        $sheet->fromArray([$row['name'], $row['creation_date'], $row['state']], null, 'A' . $fila);
        $fila++;
    }

    $filename = 'tareas_realizadas.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    handleError($e->getMessage());
}

