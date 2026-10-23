<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/validation.php';
function check(bool $condition, string $label): void {
    if (!$condition) { throw new RuntimeException($label); }
}
function invalid(array $input): void {
    try { calendarPayload($input); }
    catch (InvalidArgumentException $error) { return; }
    throw new RuntimeException('invalid input was accepted');
}
$base = ['title' => '情報I 提出物', 'start' => '2026-10-26 09:00:00', 'time_mode' => 'clock'];
check(calendarPayload($base)['end'] === null, 'clock without end');
invalid($base + ['end' => '2026-10-26 08:00:00']);
invalid(array_replace($base, ['start' => '2026-02-30 09:00:00']));
invalid(array_replace($base, ['title' => ['array injection']]));
invalid(array_replace($base, ['title' => '  ']));
invalid(array_replace($base, ['time_mode' => 'period', 'period' => '7']));
invalid(array_replace($base, ['event_type' => 'invalid']));
$existing = calendarPayload($base);
$existing['start_datetime'] = $existing['start'];
$existing['end_datetime'] = null;
unset($existing['start'], $existing['end']);
$moved = calendarPayload(['start' => '2026-10-27 09:00:00', 'end' => ''], $existing);
check($moved['end'] === null, 'moving a clock event must keep no-end setting');
check($moved['title'] === $base['title'], 'moving an event must retain title');
check(calendarPayload(array_replace($base, ['title' => "'; DROP TABLE events; --"]))['title']
    === "'; DROP TABLE events; --", 'SQL-looking text stays a value');
echo "Validation tests passed\n";
