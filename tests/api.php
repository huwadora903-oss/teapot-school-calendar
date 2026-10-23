<?php
declare(strict_types=1);
function request(string $path, ?array $body = null, ?string $origin = null): array {
    $headers = ['Content-Type: application/x-www-form-urlencoded'];
    if ($origin !== null) { $headers[] = 'Origin: ' . $origin; }
    $context = stream_context_create(['http' => [
        'method' => $body === null ? 'GET' : 'POST', 'ignore_errors' => true,
        'header' => implode("\r\n", $headers),
        'content' => $body === null ? '' : http_build_query($body), 'timeout' => 10]]);
    $raw = file_get_contents('http://127.0.0.1:8080/api/' . $path . '.php', false, $context);
    preg_match('/\s(\d{3})\s/', $http_response_header[0], $match);
    return [(int)$match[1], json_decode($raw, true, 512, JSON_THROW_ON_ERROR)];
}
function expect(bool $condition, string $message): void {
    if (!$condition) { throw new RuntimeException($message); }
}
[$status, $events] = request('get_events');
expect($status === 200 && count($events) === 3, 'sample events');
$event = ['title' => "提出物 '; DROP TABLE events; --", 'start' => '2026-10-26 09:00:00',
    'end' => '', 'time_mode' => 'clock', 'event_type' => 'assignment', 'memo' => '架空の提出先'];
[$status, $result] = request('add_event', $event);
expect($status === 200 && $result['success'], 'create event');
$id = (string)$result['id'];
[$status, $events] = request('get_events');
$found = array_values(array_filter($events, fn($row) => $row['id'] === $id))[0];
expect($found['title'] === $event['title'] && $found['end'] === null, 'bound text and no end');
[$status, $result] = request('update_event', ['id' => $id, 'start' => '2026-10-27 09:00:00', 'end' => '']);
expect($status === 200 && $result['success'], 'move event');
[$status, $events] = request('get_events');
$found = array_values(array_filter($events, fn($row) => $row['id'] === $id))[0];
expect($found['end'] === null && $found['start'] === '2026-10-27 09:00:00', 'move keeps null end');
[$status, $result] = request('update_event', ['id' => $id, 'title' => '情報I レポート',
    'start' => '2026-10-28 00:00:00', 'time_mode' => 'period', 'period' => '3']);
expect($status === 200 && $result['success'], 'edit event');
[$status, $result] = request('update_event', ['id' => $id, 'title' => '情報I レポート',
    'start' => '2026-10-28 00:00:00', 'time_mode' => 'period', 'period' => '3']);
expect($status === 200 && $result['success'], 'unchanged edit is a success');
[$status] = request('add_event', array_replace($event, ['end' => '2026-10-26 08:00:00']));
expect($status === 422, 'invalid end rejected');
[$status] = request('delete_event');
expect($status === 405, 'GET cannot delete');
[$status] = request('delete_event', ['id' => $id], 'https://unrelated.example');
expect($status === 403, 'cross-origin write rejected');
[$status, $result] = request('delete_event', ['id' => $id]);
expect($status === 200 && $result['success'], 'delete event');
[$status, $events] = request('get_events');
expect($status === 200 && count($events) === 3, 'database still present and samples unchanged');
echo "API integration tests passed\n";
