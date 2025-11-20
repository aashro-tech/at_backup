<?php

declare(strict_types=1);

namespace AASHRO\AtBackup\Controller;

use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

class BackupController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected bool $isV12OrHigher;

    public function __construct(ModuleTemplateFactory $moduleTemplateFactory)
    {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $typo3Version = new Typo3Version();
        $this->isV12OrHigher = $typo3Version->getMajorVersion() > 11;
    }

    public function indexAction(): ResponseInterface
    {
        $belowV12 = !$this->isV12OrHigher;

        if ($this->isV12OrHigher) {
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->addJsFile('https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js', 'text/javascript');
            $pageRenderer->addJsFile('EXT:at_backup/Resources/Public/JavaScript/jquery.dataTables.min.js', 'text/javascript');
            $pageRenderer->loadJavaScriptModule('@aashro/at-backup/Script.js');
        }

        [, $backupPath] = $this->getPaths();
        $backups = [];

        if (is_dir($backupPath)) {
            foreach (scandir($backupPath) as $file) {
                $filePath = $backupPath . '/' . $file;
                if (is_file($filePath) && preg_match('/\.zip$/', $file)) {
                    $backups[] = [
                        'name' => $file,
                        'size' => filesize($filePath),
                        'time' => filemtime($filePath),
                    ];
                }
            }
            usort($backups, fn($a, $b) => $b['time'] <=> $a['time']);
        }

        $dateFormat = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'] . ' T (e';
        $dateFormat = date($dateFormat) . ', GMT ' . date('P') . ')';

        $activeTab = $this->request->hasArgument('activeTab') ? $this->request->getArgument('activeTab') : 'tab-backup';

        if ($this->isV12OrHigher) {
            $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
            $moduleTemplate->assignMultiple([
                'backups' => $backups,
                'belowV12' => $belowV12,
                'dateFormat' => $dateFormat,
                'activeTab' => $activeTab,
            ]);
            return $moduleTemplate->renderResponse('Backup/Index');
        }

        $this->view->assignMultiple([
            'backups' => $backups,
            'belowV12' => $belowV12,
            'dateFormat' => $dateFormat,
            'activeTab' => $activeTab,
        ]);

        return $this->htmlResponse();
    }

    public function downloadBackupAction(string $file): ResponseInterface
    {
        [, $backupPath] = $this->getPaths();
        $filePath = $backupPath . '/' . basename($file);

        if (!file_exists($filePath)) {
            $this->addFlashMessage('Backup file not found.', 'Error', $this->getFlashSeverity('ERROR'));
            return $this->redirect('index', null, null, ['activeTab' => 'tab-history']);
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function deleteBackupAction(string $file): ResponseInterface
    {
        [, $backupPath] = $this->getPaths();
        $filePath = $backupPath . '/' . basename($file);

        if (file_exists($filePath)) {
            unlink($filePath);
            $this->addFlashMessage('Backup deleted.', 'Success', $this->getFlashSeverity('OK'));
        } else {
            $this->addFlashMessage('Backup not found.', 'Error', $this->getFlashSeverity('ERROR'));
        }

        return $this->redirect('index', null, null, ['activeTab' => 'tab-history']);
    }

    public function runBackupAction(): ResponseInterface
    {
        $datetime = date('Y-m-d_H-i-s');
        [$typo3Path, $backupPath] = $this->getPaths();

        $databaseAuth = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default'];
        $dbUser = $databaseAuth['user'];
        $dbPassword = $databaseAuth['password'];
        $dbDbname = $databaseAuth['dbname'];

        try {
            if (!is_dir($typo3Path) || !is_dir($backupPath)) {
                $this->addFlashMessage('Invalid backup or TYPO3 path.', 'Error', $this->getFlashSeverity('ERROR'));
                return $this->redirect('index', null, null, ['activeTab' => 'tab-history']);
            }

            $cmsZipFile = $backupPath . '/' . $datetime . '__TYPO3-cms.zip';
            $sqlFile = $backupPath . '/' . $datetime . '__TYPO3-db.sql';
            $sqlZipFile = $backupPath . '/' . $datetime . '__TYPO3-db.zip';

            $zipCommand = sprintf(
                'zip -9 -r %s %s/* -x %s/at-backup/\*',
                escapeshellarg($cmsZipFile),
                escapeshellarg($typo3Path),
                escapeshellarg($typo3Path)
            );
            exec($zipCommand . ' 2>&1', $zipOutput, $zipResult);

            if ($zipResult !== 0) {
                $this->addFlashMessage('TYPO3 files backup failed. Output: ' . implode("\n", $zipOutput), 'Error', $this->getFlashSeverity('ERROR'));
                return $this->redirect('index', null, null, ['activeTab' => 'tab-history']);
            }

            $dumpCommand = sprintf(
                'mysqldump -u%s -p\'%s\' %s > %s',
                escapeshellarg($dbUser),
                str_replace("'", "'\\''", $dbPassword),
                escapeshellarg($dbDbname),
                escapeshellarg($sqlFile)
            );
            exec($dumpCommand . ' 2>&1', $sqlOutput, $sqlResult);

            if (!file_exists($sqlFile) || filesize($sqlFile) === 0) {
                $this->addFlashMessage("Database export failed: " . implode("\n", $sqlOutput), 'Error', $this->getFlashSeverity('ERROR'));
                return $this->redirect('index', null, null, ['activeTab' => 'tab-history']);
            }

            $sqlZipCommand = sprintf('zip -9 %s %s', escapeshellarg($sqlZipFile), escapeshellarg($sqlFile));
            exec($sqlZipCommand . ' 2>&1');
            @unlink($sqlFile);

            $this->addFlashMessage('Backup completed successfully.', 'Success', $this->getFlashSeverity('OK'));
        } catch (\Exception $e) {
            $this->addFlashMessage('Exception: ' . $e->getMessage(), 'Error', $this->getFlashSeverity('ERROR'));
        }

        return $this->redirect('index', null, null, ['activeTab' => 'tab-history']);
    }

    protected function getPaths(): array
    {
        $projectPath = Environment::getProjectPath();
        $backupPath = $projectPath . '/at-backup';

        if (!is_dir($backupPath)) {
            GeneralUtility::mkdir_deep($backupPath);
        }

        return [$projectPath, $backupPath];
    }

    protected function getFlashSeverity(string $level)
    {
        if ($this->isV12OrHigher) {
            switch ($level) {
                case 'OK':
                    return ContextualFeedbackSeverity::OK;
                case 'ERROR':
                    return ContextualFeedbackSeverity::ERROR;
                case 'NOTICE':
                    return ContextualFeedbackSeverity::NOTICE;
                case 'INFO':
                    return ContextualFeedbackSeverity::INFO;
                case 'WARNING':
                    return ContextualFeedbackSeverity::WARNING;
                default:
                    return ContextualFeedbackSeverity::INFO;
            }
        }

        switch ($level) {
            case 'OK':
                return FlashMessage::OK;
            case 'ERROR':
                return FlashMessage::ERROR;
            case 'NOTICE':
                return FlashMessage::NOTICE;
            case 'INFO':
                return FlashMessage::INFO;
            case 'WARNING':
                return FlashMessage::WARNING;
            default:
                return FlashMessage::INFO;
        }
    }
}
