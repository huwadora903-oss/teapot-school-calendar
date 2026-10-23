<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/src/bootstrap.php';
calendarRequest('POST');
try {
    $id = calendarId($_POST);
    $pdo = calendarPdo();
    $existing = calendarFind($pdo, $id);
    if ($existing === null) {
        calendarJson(['success' => false, 'error' => '予定が見つかりません。'], 404);
    }
    $data = calendarPayload($_POST, $existing);
    $data['id'] = $id;
    $stmt = $pdo->prepare('UPDATE events SET title = :title, start_datetime = :start,
        end_datetime = :end, color = :color, event_type = :event_type,
        time_mode = :time_mode, period = :period, memo = :memo, updated_at = CURRENT_TIMESTAMP
        WHERE id = :id AND user_id IS NULL');
    $stmt->execute($data);
    calendarJson(['success' => true]);
} catch (Throwable $error) {
    calendarFailure($error);
}
