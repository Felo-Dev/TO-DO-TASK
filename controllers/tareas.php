<?php
require __DIR__ . '/../db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = $_POST['nombre'];
    $sql = "INSERT INTO tasks (name, status_id 	) VALUES (?, 1)";
    $result = $conn->Execute($sql, [$name]);
    if ($result) {
        echo json_encode(['message' => 'Tarea creada exitosamente']);
    } else {
        echo json_encode(['error' => 'No se pudo crear la tarea.']);
    }
}

// Obtener todas las tareas
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT t.id, t.name, t.creation_date , e.name AS estado
            FROM tasks t
            JOIN statuses e ON t.status_id = e.id
            ORDER BY t.creation_date DESC";
    
    $result = $conn->Execute($sql);

    if ($result) {
        $task = $result->GetArray();
        
        echo json_encode($task);
    } else {
        echo json_encode(['error' => 'No se pudo ejecutar la consulta']);
    }
}

// Actualizar estado de tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = $_POST['id'];
    $status_id = $_POST['estado_id'];

    $sql = "UPDATE tasks SET status_id = ? WHERE id = ?";
    $stmt = $conn->Prepare($sql);

    $success = $conn->Execute($stmt, [$status_id, $id]);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Estado actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado']);
    }
}

// Eliminar tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'];
    
    $sql = "DELETE FROM task WHERE id = ?";
    $stmt = $conn->Prepare($sql);

    $success = $conn->Execute($stmt, [$id]);

    if ($success) {
        if ($conn->Affected_Rows() > 0) {
            echo json_encode(['success' => true, 'message' => 'Tarea eliminada exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró ninguna tarea con el ID proporcionado']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la tarea']);
    }
}