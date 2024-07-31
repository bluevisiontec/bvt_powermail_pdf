<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "bvt_powermail_pdf".
 *
 * Auto generated 17-10-2016 15:52
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 *
 * WARNING:
 * Modified by www.hauer-heinrich.de
 ***************************************************************/

$EM_CONF['bvt_powermail_pdf'] = [
    'title' => 'Powermail PDF generator',
    'description' => 'Adds the possibility to download a generated pdf with input data from powermail. This extension is a fork of "bw_powermail_mpdf" which is based on "powermailpdf"',
    'category' => 'fe',
    'author' => 'Reinhard Schneidewind, Christian Hackl',
    'author_email' => 'info@bluevisiontec.de, web@hauer-heinrich.de',
    'author_company' => 'BlueVisionTec UG (haftungsbeschränkt)',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '2.1.0',
    'constraints' => [
        'depends' => [
            'php' => '8.1.0-8.3.99',
            'typo3' => '11.5.0-11.5.99',
            'extbase' => '11.5.0-11.5.99',
            'fluid' => '11.5.0-11.5.99',
            'powermail' => '11.5.0-11.5.99'
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Bvt\\BvtPowermailPdf\\' => 'Classes',
            'DeepCopy\\' => 'vendor/myclabs/deep_copy/src',
            'paragonie\\random_compat\\' => 'vendor/paragonie',
            'php-http\\message-factory\\' => 'vendor/message-factory',
            'setasign\\Fpdi\\' => 'vendor/setasign/fpdi/src',
            'Mpdf\\PsrHttpMessageShim\\' => 'vendor/mpdf/psr-http-message-shim/src',
            'Mpdf\\PsrLogAwareTrait\\' => 'vendor/mpdf/psr-log-aware-trait/src',
            'Mpdf\\' => 'vendor/mpdf/mpdf/src'
        ]
    ],
    'clearcacheonload' => false,
];
