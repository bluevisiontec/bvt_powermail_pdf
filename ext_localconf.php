<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Bvt.' . $_EXTKEY,
    'PdfGen',
    array(
        'PDF' => 'main',
    ),
    array(
        'PDF' => 'main',
    )
);
\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')->connect(
        'In2code\\Powermail\\Controller\\FormController',
        'createActionAfterMailDbSaved',
        'Bvt\\BvtPowermailPdf\\PdfGen',
        'createAction'
);
