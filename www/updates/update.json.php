<?php

header('Content-Type: application/json');

$shopwareVersion = $_GET['shopware_version'] ?? null;
$filename = '/var/www/cdn/update.zip';
$size = filesize($filename);
$sha1 = sha1_file($filename);

if (!$sha1 || !$size || !file_exists($filename) || !$shopwareVersion) {
    echo json_encode(['message' => 'No update file found.'], JSON_THROW_ON_ERROR);
    exit;
}

$updateVersion = explode('.', $shopwareVersion);
++$updateVersion[\count($updateVersion) - 1];
$updateVersion = implode('.', $updateVersion);

echo json_encode([
    'version' => $updateVersion,
    'release_date' => null,
    'uri' => 'http://cdn.example/' . $updateVersion . '_update_' . $sha1 . '_latest.zip',
    'size' => $size,
    'sha1' => $sha1,
    'checks' => [
        0 => [
            'type' => 'phpversion',
            'value' => '5.6.4',
            'level' => 20,
        ],
        1 => [
            'type' => 'mysqlversion',
            'value' => '5.5',
            'level' => 20,
        ],
        2 => [
            'type' => 'licensecheck',
            'value' => [
                'licenseKeys' => [
                    0 => 'SwagEnterprisePremium',
                    1 => 'SwagEnterpriseCluster',
                    2 => 'SwagEnterprise',
                    3 => 'SwagCommercial',
                    4 => 'SwagCore',
                ],
            ],
            'level' => 20,
        ],
        3 => [
            'type' => 'emotiontemplate',
            'value' => null,
            'level' => 20,
        ],
        4 => [
            'type' => 'regex',
            'value' => [
                'expression' => '#sGetArticleAccessories#i',
                'directories' => [
                    0 => 'engine/Shopware/Plugins/Local/',
                    1 => 'engine/Shopware/Plugins/Community/',
                ],
                'fileRegex' => '#.*\.php#i',
                'message' => [
                    'en' => 'sArticles::sGetArticleAccessories is deprecated<br/> %s',
                    'de' => 'sArticles::sGetArticleAccessories Zugriff ist veraltet<br/> %s',
                ],
            ],
            'level' => 10,
        ],
        5 => [
            'type' => 'regex',
            'value' => [
                'expression' => 'sCreateTranslationTable',
                'directories' => [
                    0 => 'engine/Shopware/Plugins/Local/',
                    1 => 'engine/Shopware/Plugins/Community/',
                ],
                'fileRegex' => '#.*\.php#i',
                'message' => [
                    'en' => 'sArticles::sCreateTranslationTable is removed<br/> %s',
                    'de' => 'sArticles::sCreateTranslationTable Zugriff ist veraltet<br/> %s',
                ],
            ],
            'level' => 10,
        ],
        6 => [
            'type' => 'writable',
            'value' => [
                0 => 'files',
                1 => 'recovery/',
            ],
            'level' => 20,
        ],
        7 => [
            'type' => 'writable',
            'value' => [
                0 => '/',
            ],
            'level' => 10,
        ],
    ],
    'changelog' => [
        'de' => [
            'id' => '143',
            'releaseId' => null,
            'language' => 'de',
            'changelog' => '',
            'release_id' => '77',
        ],
        'en' => [
            'id' => '144',
            'releaseId' => null,
            'language' => 'en',
            'changelog' => '',
            'release_id' => '77',
        ],
    ],
    'isNewer' => true,
], JSON_THROW_ON_ERROR);
