<?php
defined('TYPO3') || die();

(static function() {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'AtBackup',
        'backup',
        [
            \AASHRO\AtBackup\Controller\BackupController::class => 'index, runBackup, downloadBackup, deleteBackup',
        ]
    );
})();
