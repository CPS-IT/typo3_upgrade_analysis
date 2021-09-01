<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE === 'BE') {
    /**
     * Registers a Backend Module
     */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'CPSIT.upgrade_analysis',
        'tools',	 // Make module a submodule of 'tools'
        'UpgradeAnalysis',	// Submodule key
        '',						// Position
        [
            'UpgradeAnalysis' => 'list'
        ],
        [
            'access' => 'user,group',
            'icon'   => 'EXT:ns_ext_compatibility/Resources/Public/Icons/Extension.svg',
            'labels' => 'LLL:EXT:ns_ext_compatibility/Resources/Private/Language/locallang.xlf:module.title',
        ]
    );

    //unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['NsExtCompatibility']);
}


