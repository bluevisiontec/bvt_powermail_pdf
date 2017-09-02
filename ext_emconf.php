<?php
/***************************************************************
 * Extension Manager/Repository config file for ext "bvt_powermail_pdf".
 *
 * Auto generated 09-07-2016 13:35
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
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
    'version' => '1.0.2',
    'constraints' => 
    array (
        'depends' => 
            array (
                'typo3' => '6.2.7-8.99.99',
                'powermail' => '2.1.8-3.99.99',
                'extbase' => '6.2.0-8.99.99',
                'fluid' => '6.2.0-8.99.99',
                'php' => '5.5.0-0.0.0',
            ),
        'conflicts' => 
            array (
            ),
        'suggests' => 
            array (
            ),
    ),
    'clearcacheonload' => true,
);

