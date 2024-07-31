<?php
if (!defined('TYPO3')) {
    die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Bvt.BvtPowermailPdf',
    'PdfGen',
    [
        'PDF' => 'main',
    ],
    [
        'PDF' => 'main',
    ]
);

\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')->connect(
    'In2code\\Powermail\\Controller\\FormController',
    'createActionAfterMailDbSaved',
    'Bvt\\BvtPowermailPdf\\PdfGen',
    'createCustomAction'
);
