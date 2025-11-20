<?php

use \AASHRO\AtBackup\Controller\BackupController;

return [
    'at_backup' => [
        'parent' => 'web',
        'position' => ['after' => 'web_info'],
        'access' => 'user',
        'path' => '/module/web/atbackup',
        'labels' => 'LLL:EXT:at_backup/Resources/Private/Language/locallang_backup.xlf',
        'extensionName' => 'AtBackup',
        'icon' => 'EXT:at_backup/Resources/Public/Icons/user_mod_backup.png',
        'controllerActions' => [
            BackupController::class => [
                'index', 'downloadBackup', 'deleteBackup', 'runBackup',
            ],
        ],
    ],
];