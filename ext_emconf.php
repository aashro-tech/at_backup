<?php

$EM_CONF[$_EXTKEY] = [
    'title' => '[AASHRO] Backup',
    'description' => 'Effortlessly create a complete backup of your TYPO3 project with a single click.',
    'category' => 'module',
    'author' => 'Team AASHRO',
    'author_email' => 'info@aashro.com',
    'author_company' => 'AASHRO Tech',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-13.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
