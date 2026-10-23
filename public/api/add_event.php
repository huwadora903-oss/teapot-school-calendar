<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/src/bootstrap.php';
calendarRequest('POST');
try {
    $data = calendarPayload($_POST);
    $pdo = calendarPdo();
    $stmt = $pdo->prepare('INSERT INTO events
        (title, start_datetime, end_datetime, color, event_type, time_mode, period, memo, user_id)
        VALUES (:title, :start, :end, :color, :event_type, :time_mode, :period, :memo, NULL)');
    $stmt->execute($data);
    calendarJson(['success' => true, 'id' => $pdo->lastInsertId()]);
} catch (Throwable $error) {
    calendarFailure($error);
}
