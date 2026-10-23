<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/src/bootstrap.php';
calendarRequest('POST');
try {
    $id = calendarId($_POST);
    $stmt = calendarPdo()->prepare('DELETE FROM events WHERE id = :id AND user_id IS NULL');
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() === 0) {
        calendarJson(['success' => false, 'error' => '予定が見つかりません。'], 404);
    }
    calendarJson(['success' => true]);
} catch (Throwable $error) {
    calendarFailure($error);
}
