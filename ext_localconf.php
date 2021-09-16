<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied');
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\NITSAN\NsExtCompatibility\Controller\nsextcompatibilityController::class] = [
    'className' => \CPSIT\Typo3UpgradeAnalysis\Controller\UpgradeAnalysisController::class
];