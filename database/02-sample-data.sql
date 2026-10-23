-- 架空の説明用データのみ。本番の学校予定は含みません。
INSERT INTO events
(title,start_datetime,end_datetime,color,event_type,time_mode,period,memo,user_id)
VALUES
('情報I レポート提出','2026-10-26 00:00:00','2026-10-26 00:00:00','#f39c12','assignment','period',3,'A4用紙2枚・情報の先生へ（説明用データ）',NULL),
('数学I テスト','2026-10-27 00:00:00','2026-10-27 00:00:00','#e74c3c','test','period',1,'教科書 pp.10〜30・定規（説明用データ）',NULL),
('学校行事','2026-10-30 00:00:00','2026-10-30 00:00:00','#3788d8','event','allday',NULL,NULL,NULL);
