<?php
/**
 * Installer Requirements Check
 */

header('Content-Type: application/json');

$requirements = [
    [
        'name' => 'PHP Version >= 7.4',
        'passed' => version_compare(PHP_VERSION, '7.4.0', '>=')
    ],
    [
        'name' => 'MySQL Extension',
        'passed' => extension_loaded('mysqli') || extension_loaded('pdo_mysql')
    ],
    [
        'name' => 'cURL Extension',
        'passed' => extension_loaded('curl')
    ],
    [
        'name' => 'JSON Extension',
        'passed' => extension_loaded('json')
    ],
    [
        'name' => 'OpenSSL Extension',
        'passed' => extension_loaded('openssl')
    ],
    [
        'name' => 'mbstring Extension',
        'passed' => extension_loaded('mbstring')
    ],
    [
        'name' => 'Config Directory Writable',
        'passed' => is_writable(dirname(__DIR__) . '/config') || is_writable(dirname(__DIR__))
    ],
    [
        'name' => 'Logs Directory Writable',
        'passed' => is_writable(dirname(__DIR__) . '/logs') || is_writable(dirname(__DIR__))
    ]
];

echo json_encode($requirements);
