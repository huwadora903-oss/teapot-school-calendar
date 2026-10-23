<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/src/bootstrap.php';
calendarRequest('GET');
try {
    $rows = calendarPdo()->query('SELECT id, title, start_datetime, end_datetime,
        color, event_type, time_mode, period, memo FROM events
        WHERE user_id IS NULL ORDER BY start_datetime, id')->fetchAll();
    $events = [];
    foreach ($rows as $row) {
        $clock = $row['time_mode'] === 'clock';
        $events[] = ['id' => (string)$row['id'], 'title' => $row['title'],
            'start' => $clock ? $row['start_datetime'] : substr($row['start_datetime'], 0, 10),
            'end' => $clock ? $row['end_datetime'] : null, 'allDay' => !$clock,
            'color' => $row['color'], 'extendedProps' => [
                'event_type' => $row['event_type'], 'time_mode' => $row['time_mode'],
                'period' => $row['period'], 'memo' => $row['memo']]];
    }
    calendarJson($events);
} catch (Throwable $error) {
    calendarFailure($error, true);
}
