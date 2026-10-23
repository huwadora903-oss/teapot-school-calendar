# 外部ソフトウェア・サービス

## FullCalendar 6.1.11

- カレンダー表示に使用するJavaScriptライブラリ
- 公式：https://fullcalendar.io/
- 使用版のライセンス：https://github.com/fullcalendar/fullcalendar/blob/v6.1.11/LICENSE.md
- MIT License / Copyright (c) 2021 Adam Shaw
- 現在の画面はCDN上の配布ファイルを参照します。ライブラリ本体を同梱する際は、著作権表示とライセンス文を保持します。

## 日本の祝日API

- 祝日の情報取得に使用する外部サービス
- 公開元：https://github.com/holidays-jp/api
- API：https://holidays-jp.github.io/api/v1/
- 公開元のライセンス：MIT

## 学校での運用環境

以下は学校での運用に使用しているソフトウェアであり、このフォルダーに本体を含めるものではありません。

- WordPress：https://wordpress.org/about/license/ （GPLv2以降）
- PHP / PDO：https://www.php.net/ （実行環境のバージョンを導入時に確認）
- MariaDB 10.11：https://github.com/MariaDB/server/tree/10.11 （GPLv2）
- phpMyAdmin：https://www.phpmyadmin.net/license/ （GPLv2）

提供されたPHPを確認し、このデモ版ではWordPress起動ファイルの読み込みとログイン関数への依存を外しています。WordPress本体のコードは同梱しません。
それぞれの名称を記載することと、再配布・改変時の利用条件に従うことは別の対応です。
