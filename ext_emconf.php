<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Upgrade Analysis',
    'description' => 'extension that performs an upgrade analysis',
    'category' => 'plugin',
    'author' => 'Juliane Wundermann',
    'author_company' => 'familie redlich',
    'author_email' => 'j.wundermann@familie-redlich.de',
    'state' => 'alpha',
    'clearCacheOnLoad' => true,
    'version' => '0.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-8.9.99',
            'ns_ext_compatibility'=> '5.0.1-5.0.99'
        ],
    ],
];