-- 公開デモ用に、提供PHPが参照する列から再構成したテーブルです。
-- 本番データベースの構造エクスポートやバックアップではありません。
CREATE TABLE IF NOT EXISTS events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NULL,
    color CHAR(7) NOT NULL DEFAULT '#3788d8',
    event_type ENUM('event','assignment','test','other') NOT NULL DEFAULT 'other',
    time_mode ENUM('allday','clock','period') NOT NULL DEFAULT 'allday',
    period TINYINT UNSIGNED NULL,
    memo TEXT NULL,
    user_id BIGINT UNSIGNED NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY event_start (start_datetime),
    CONSTRAINT period_range CHECK (period IS NULL OR period BETWEEN 1 AND 6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
