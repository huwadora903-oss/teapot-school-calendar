<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Tokyo');
require_once __DIR__ . '/validation.php';

function calendarConfig(): array
{
    $config = [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_NAME') ?: 'teapot_calendar',
        'username' => getenv('DB_USER') ?: 'calendar',
        'password' => getenv('DB_PASSWORD') ?: '',
        'read_only' => getenv('CALENDAR_READ_ONLY') === '1',
    ];
    $local = dirname(__DIR__) . '/config.php';
    return is_file($local) ? array_replace($config, require $local) : $config;
}

function calendarPdo(): PDO
{
    $config = calendarConfig();
    return new PDO(
        'mysql:host=' . $config['host'] . ';port=' . $config['port']
            . ';dbname=' . $config['database'] . ';charset=utf8mb4',
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
}

function calendarJson(array $body, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

function calendarRequest(string $method): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
        header('Allow: ' . $method);
        calendarJson(['success' => false, 'error' => 'この操作では ' . $method . ' を使用してください。'], 405);
    }
    if ($method === 'POST') {
        if (calendarConfig()['read_only']) {
            calendarJson(['success' => false, 'error' => 'このデモは閲覧専用です。'], 403);
        }
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin !== '') {
            $host = parse_url($origin, PHP_URL_HOST);
            $port = parse_url($origin, PHP_URL_PORT);
            $originHost = $host . ($port !== null ? ':' . $port : '');
            if ($originHost !== ($_SERVER['HTTP_HOST'] ?? '')) {
                calendarJson(['success' => false, 'error' => '別のサイトからの変更は受け付けません。'], 403);
            }
        }
        if (($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '') === 'cross-site') {
            calendarJson(['success' => false, 'error' => '別のサイトからの変更は受け付けません。'], 403);
        }
    }
}

function calendarId(array $input): int
{
    $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]);
    if ($id === false) {
        throw new InvalidArgumentException('予定のIDが不正です。');
    }
    return $id;
}

function calendarFind(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = :id AND user_id IS NULL');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function calendarFailure(Throwable $error, bool $list = false): never
{
    if ($error instanceof InvalidArgumentException) {
        calendarJson(['success' => false, 'error' => $error->getMessage()], 422);
    }
    error_log('Calendar API error: ' . $error->getMessage());
    calendarJson($list ? [] : ['success' => false,
        'error' => 'サーバーで処理できませんでした。時間をおいてもう一度お試しください。'], 500);
}
