<?php
declare(strict_types=1);

function calendarDate(string $value): string
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d H:i:s', $value);
    $errors = DateTimeImmutable::getLastErrors();
    if (!$date || ($errors !== false && ($errors['warning_count'] || $errors['error_count']))
        || $date->format('Y-m-d H:i:s') !== $value) {
        throw new InvalidArgumentException('日付または時刻が不正です。');
    }
    return $value;
}

function calendarPayload(array $input, array $existing = []): array
{
    foreach ($input as $value) {
        if (!is_scalar($value) && $value !== null) {
            throw new InvalidArgumentException('入力形式が不正です。');
        }
    }
    $data = array_replace($existing, $input);
    $title = trim((string)($data['title'] ?? ''));
    $length = preg_match_all('/./us', $title);
    if ($title === '' || $length === false || $length > 255) {
        throw new InvalidArgumentException('タイトルは1〜255文字で入力してください。');
    }
    $start = calendarDate((string)($data['start'] ?? $data['start_datetime'] ?? ''));
    $type = (string)($data['event_type'] ?? 'other');
    $mode = (string)($data['time_mode'] ?? 'allday');
    if (!in_array($type, ['event', 'assignment', 'test', 'other'], true)
        || !in_array($mode, ['allday', 'clock', 'period'], true)) {
        throw new InvalidArgumentException('予定の種類または時間指定が不正です。');
    }
    $color = (string)($data['color'] ?? '#3788d8');
    if (!preg_match('/^#[0-9a-fA-F]{6}$/D', $color)) {
        throw new InvalidArgumentException('予定の色が不正です。');
    }
    $period = null;
    if ($mode === 'period') {
        $period = filter_var($data['period'] ?? null, FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 6]]);
        if ($period === false) {
            throw new InvalidArgumentException('時限は1〜6限で指定してください。');
        }
    }
    $rawEnd = array_key_exists('end', $data) ? $data['end'] : ($data['end_datetime'] ?? null);
    $end = $rawEnd !== null && $rawEnd !== '' ? calendarDate((string)$rawEnd) : null;
    if ($mode === 'clock') {
        if ($end !== null && $start >= $end) {
            throw new InvalidArgumentException('終了時刻は開始時刻より後に設定してください。');
        }
    } else {
        $start = substr($start, 0, 10) . ' 00:00:00';
        $end = $start;
    }
    $memo = (string)($data['memo'] ?? '');
    $memoLength = preg_match_all('/./us', $memo);
    if ($memoLength === false || $memoLength > 2000) {
        throw new InvalidArgumentException('備考は2000文字以内で入力してください。');
    }
    return ['title' => $title, 'start' => $start, 'end' => $end,
        'color' => $color, 'event_type' => $type, 'time_mode' => $mode,
        'period' => $period, 'memo' => $memo !== '' ? $memo : null];
}
