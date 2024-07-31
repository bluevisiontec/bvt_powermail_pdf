<?php
if (!defined('TYPO3')) {
    die ('Access denied.');
}

$extensionKey = 'bvt_powermail_pdf';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    $extensionKey,
    'Pi1',
    [
        'Pdf' => 'main',
    ],
    [
        'Pdf' => 'main'
    ]
);

$pluginSignature = str_replace('_', '', $extensionKey) . '_pdf';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $extensionKey . '/Configuration/FlexForms/flexform_pdf.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extensionKey, 'Configuration/TypoScript', 'Powermail PDF Form');
