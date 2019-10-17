<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "bvt_powermail_pdf".
 *
 * Auto generated 17-10-2016 15:52
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF['bvt_powermail_pdf'] = [
    'title' => 'Powermail PDF generator',
    'description' => 'Adds the possibility to download a generated pdf with input data from powermail. This extension is a fork of "bw_powermail_mpdf" which is based on "powermailpdf"',
    'category' => 'fe',
    'author' => 'Reinhard Schneidewind',
    'author_email' => 'info@bluevisiontec.de',
    'author_company' => 'BlueVisionTec UG (haftungsbeschränkt)',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.1',
    'constraints' => [
        'depends' => [
            'php' => '5.5.0-0.0.0',
            'typo3' => '8.7.0-9.9.99',
            'extbase' => '8.7.0-9.99.99',
            'fluid' => '8.7.0-9.99.99',
            'powermail' => '2.1.8-7.4.99'
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'clearcacheonload' => false,
];
