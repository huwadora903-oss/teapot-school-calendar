<?php
// PHPを直接起動する場合だけconfig.phpへコピーして編集します。
// config.phpはGit管理対象外です。実際の接続情報をGitHubへ置かないでください。
return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'teapot_calendar',
    'username' => 'calendar',
    'password' => 'SET_YOUR_LOCAL_PASSWORD',
    'read_only' => false,
];
