<?php
defined('TYPO3') || die();

use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

(static function() {

    $typo3Version = new Typo3Version();

    // Only execute this block for TYPO3 version 11
    if ($typo3Version->getMajorVersion() === 11) {
        // Register Module
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'AtBackup',
            'web',
            'backup',
            '',
            [
                \AASHRO\AtBackup\Controller\BackupController::class => 'index, runBackup, downloadBackup, deleteBackup',
                
            ],
            [
                'access' => 'user,group',
                'icon'   => 'EXT:at_backup/Resources/Public/Icons/user_mod_backup.png',
                'labels' => 'LLL:EXT:at_backup/Resources/Private/Language/locallang_backup.xlf',
            ]
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_atbackup_domain_model_backup', 'EXT:at_backup/Resources/Private/Language/locallang_csh_tx_atbackup_domain_model_backup.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_atbackup_domain_model_backup');
    }
})();
