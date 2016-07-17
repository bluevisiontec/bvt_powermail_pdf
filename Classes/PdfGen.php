<?php

namespace Bvt\BvtPowermailPdf;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Error\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
/**
 *  PdfGen class handles pdf generation and adds filename and link to powermail mail
 */
class PdfGen extends \In2code\Powermail\Controller\FormController {


    /** @var \TYPO3\CMS\Core\Log\Logger|null $logger typo3 logger */
    protected $logger = null;

    /**
     * Returns typo3 logger
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        if (!$this->logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }
        return $this->logger;
    }
    
    /**
     * Returns increment id
     *
     * @return string
     */
    protected function getIncrementId($uid)
    {
        $calc = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_bvtpowermailpdf.']['settings.']['incremendId.']['calc'];

        return $GLOBALS['TSFE']->cObj->calc($uid.$calc);
    }

    /**
     * Generate PDF from HTML Template
     *
     * @param In2code\Powermail\Domain\Model\Mail $mail
     *
     * @throws \FileNotFoundException
     *
     * @return string
     */
    protected function generatePdf(\In2code\Powermail\Domain\Model\Mail $mail)
    {
        // Use mpdf
        require 'mpdf/mpdf.php';

        // Map fields
        $fieldMap = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_bvtpowermailpdf.']['settings.']['fieldMap.'];
        $powermailSettings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermail.']['settings.'];

        $answers = $mail->getAnswers();
        $answerData = array();

        foreach ($fieldMap as $key => $value) {
            foreach($answers as $answer){
                if($value == $answer->getField()->getMarker()){
                    $answerData[$key]  = $answer->getValue();
                    $answerData['powermail_all'] .= 
                        '<b>' . $answer->getField()->getTitle() . ':</b> '
                        . $answer->getValue() . '<br/>';
                }
            }
        }
        
        if ($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_bvtpowermailpdf.']['settings.']['incremendId.']['enable']) {
                $answerData['increment_id'] = $this->getIncrementId($mail->getUid());
        }

        // load html file here
        $htmlOriginal = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_bvtpowermailpdf.']['settings.']['sourceFile']);

        if (!empty($htmlOriginal)) {
            $info = pathinfo($htmlOriginal);
            $fileName = basename($htmlOriginal, '.'.$info['extension']);

            // Name for the generated pdf
            $pdfFilename = $powermailSettings['setup.']['misc.']['file.']['folder'].$fileName."_".md5(time()).'.pdf';
            $mpdf = new \mPDF();

            // Fluid standalone view
            $htmlView = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
            $htmlView->setFormat('html');
            $htmlView->setTemplatePathAndFileName($htmlOriginal);
            
            $htmlView->assignMultiple($answerData);

            $html = $htmlView->render();

            $mpdf->writeHTML($html);
            $mpdf->Output(GeneralUtility::getFileAbsFileName($pdfFilename), 'F');

        } else {
            $this->getLogger()->error("Unable to open HTML template for PDF generation: no sourceFile specified.");
            throw new Exception("No html file is set in Typoscript. Please set bvt_powermail_pdf.settings.sourceFile if you want to use the filling feature.",1417432239);
        }

        return $pdfFilename;
    }

    /**
     * @param string $uri
     * @param string $label
     *
     * @return string Rendered link
     */
    protected function render($uri, $label)
    {
        // Get filelink config
        $typolink = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_bvtpowermailpdf.']['settings.']['typolink.'];
        $typolink['parameter'] = $uri;

        $downloadLink = $GLOBALS['TSFE']->cObj->typolink($label, $typolink);

        return $downloadLink;
    }

    /**
     *
     * @param \In2code\Powermail\Domain\Model\Mail $mail
     * @param \string $hash
     */
    public function createAction(\In2code\Powermail\Domain\Model\Mail $mail, $hash = NULL)
    {$this->getLogger()->error(get_class($hash));
        // Plugin settings
        $settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_bvtpowermailpdf.']['settings.'];

        $powermailSettings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_powermail.']['settings.'];

        $filePath = $settings['sourceFile'];
        $powermailFilePath = $powermailSettings['setup.']['misc.']['file.']['folder'] . basename($filePath);

        if ($settings['activate']) {
            if ($settings['sourceFile']) {
                if(!file_exists(GeneralUtility::getFileAbsFileName($settings['sourceFile']))){
                    throw new Exception("The file does not exist: ". $settings['sourceFile']." Please set the correct path in plugin.tx_bvtpowermailpdf.settings.sourceFile", 1417520887);
                }
            }
            if ($settings['fillPdf']) {
                $powermailFilePath = $this->generatePdf($mail);
            } else {
                //Copy our pdf to powermail when is does not exist or has changed
                if (!file_exists(GeneralUtility::getFileAbsFileName($powermailFilePath))
                    || (md5_file(GeneralUtility::getFileAbsFileName($powermailFilePath)) != md5_file(GeneralUtility::getFileAbsFileName($filePath)))) {
                    copy(GeneralUtility::getFileAbsFileName($filePath),GeneralUtility::getFileAbsFileName($powermailFilePath));
                }
            }
            
            $formRepository = $this->objectManager->get('In2code\Powermail\Domain\Repository\FieldRepository');
            
            // Display download link
            if ($settings['showDownloadLink']) {
            
                $label = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate("download","bvt_powermail_pdf");
                $url = $GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'] . '/' . $powermailFilePath;
                
                /* @var $answer In2code\Powermail\Domain\Model\Answer */
                $answer = $this->objectManager->get('In2code\Powermail\Domain\Model\Answer');
                
                $field = $formRepository->findByMarkerAndForm($settings['marker.']['pdf_url'],0);
                
                if (!$field) {
                    /* @var $field In2code\Powermail\Domain\Model\Field */
                    $field = $this->objectManager->get('In2code\Powermail\Domain\Model\Field');
                    $field->setTitle(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('downloadUrl', 'bvt_powermail_pdf'));
                    $field->setType('input');
                    $field->setPid(0); // $mail->getPid()
                    $field->setMarker($settings['marker.']['pdf_url']);
                }
                $answer->setField($field);
                $answer->setValue($url);

                $mail->addAnswer($answer);
    
                // Add a field for the download link
                
                 $link = $this->render($url, $label);
                
                /* @var $answer In2code\Powermail\Domain\Model\Answer */
                $answer = $this->objectManager->get('In2code\Powermail\Domain\Model\Answer');
                
                $field = $formRepository->findByMarkerAndForm($settings['marker.']['pdf_link'],0);
                
                if (!$field) {
                    /* @var $field In2code\Powermail\Domain\Model\Field */
                    $field = $this->objectManager->get('In2code\Powermail\Domain\Model\Field');
                    $field->setTitle(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('downloadLink', 'bvt_powermail_pdf'));
                    $field->setType('downloadLink');
                    $field->setPid(0);
                    $field->setMarker($settings['marker.']['pdf_link']);
                }
                $answer->setField($field);
                $answer->setValue($link);

                $mail->addAnswer($answer);
            }
            // Append download link to mail
            if ($settings['email.']['attachFile']) {
                /* @var $answer In2code\Powermail\Domain\Model\Answer */
                $answer = $this->objectManager->get('In2code\Powermail\Domain\Model\Answer');

                $field = $formRepository->findByMarkerAndForm('bvt_powermail_pdf_attachment',0);
                
                 if (!$field) {
                    /* @var $field In2code\Powermail\Domain\Model\Field */
                    $field = $this->objectManager->get('In2code\Powermail\Domain\Model\Field');
                    $field->setTitle(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('file', 'bvt_powermail_pdf'));
                    $field->setType('file');
                    $field->setMarker('bvt_powermail_pdf_attachment');
                    $field->setPid(0);
                }
                $answer->setField($field);
                $answer->setValue(json_encode(array(basename($powermailFilePath))));
                $mail->addAnswer($answer);
            }

            if ($settings['incremendId.']['enable']) {
                
                $incremendId = $this->getIncrementId($mail->getUid());
            
                /* @var $answer In2code\Powermail\Domain\Model\Answer */
                $answer = $this->objectManager->get('In2code\Powermail\Domain\Model\Answer');

                $field = $formRepository->findByMarkerAndForm('increment_id',0);
                
                 if (!$field) {
                    /* @var $field In2code\Powermail\Domain\Model\Field */
                    $field = $this->objectManager->get('In2code\Powermail\Domain\Model\Field');
                    $field->setTitle(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('Request Id', 'bvt_powermail_pdf'));
                    $field->setType('file');
                    $field->setMarker('increment_id');
                    $field->setPid(0);
                }
                $answer->setField($field);
                $answer->setValue($incremendId);
                $mail->addAnswer($answer);
            }
        }
    }
}

