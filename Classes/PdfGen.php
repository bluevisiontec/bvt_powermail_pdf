<?php
namespace Bvt\BvtPowermailPdf;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Error\Exception;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as ExtbaseAnnotation;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use In2code\Powermail\Domain\Model\Mail;

/**
 *  PdfGen class handles pdf generation and adds filename and link to powermail mail
 */
class PdfGen extends \In2code\Powermail\Controller\FormController {

    protected ?Logger $logger = null;
    protected array $typoscriptSettings = [];
    protected array $powermailSettings = [];

    public function __construct()
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );

        $this->typoscriptSettings = $extbaseFrameworkConfiguration['plugin.']['tx_bvtpowermailpdf.']['settings.'];
        $this->powermailSettings = $extbaseFrameworkConfiguration['plugin.']['tx_powermail.']['settings.'];
    }

    /**
     * Returns typo3 logger
     */
    protected function getLogger(): ?Logger
    {
        if (!$this->logger) {
            $this->logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }
        return $this->logger;
    }

    /**
     * Returns increment id
     */
    protected function getIncrementId(int $uid): string
    {
        $calc = $this->typoscriptSettings['incremendId.']['calc'];

        return $GLOBALS['TSFE']->cObj->calc($uid.$calc);
    }

    /**
     * Generate PDF from HTML Template
     *
     * @throws \FileNotFoundException
     */
    protected function generatePdf(Mail $mail): string
    {
        // Map fields
        $fieldMap = $this->typoscriptSettings['fieldMap.'];

        $answers = $mail->getAnswers();
        $answerData = [];

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

        if ($this->typoscriptSettings['incremendId.']['enable']) {
            $answerData['increment_id'] = $this->getIncrementId($mail->getUid());
        }

        // load html file here
        $htmlOriginal = GeneralUtility::getFileAbsFileName($this->typoscriptSettings['sourceFile']);

        if (!empty($htmlOriginal)) {
            $info = pathinfo($htmlOriginal);
            $fileName = basename($htmlOriginal, '.' . $info['extension']);

            // Name for the generated pdf
            $pdfFilename = $this->powermailSettings['setup.']['misc.']['file.']['folder'] . $fileName . '_' . md5(time()) . '.pdf';

            // Use mpdf

            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $mpdf = new \Mpdf\Mpdf([
                // $this->typoscriptSettings['mpdf.']['encoding'],
                // $this->typoscriptSettings['mpdf.']['defaultFontSize'],
                // $this->typoscriptSettings['mpdf.']['defaultFont'], alexbrush
                // $this->typoscriptSettings['mpdf.']['marginLeft'],
                // $this->typoscriptSettings['mpdf.']['marginRight'],
                // $this->typoscriptSettings['mpdf.']['marginTop'],
                // $this->typoscriptSettings['mpdf.']['marginBottom'],
                // $this->typoscriptSettings['mpdf.']['marginHeader'],
                // $this->typoscriptSettings['mpdf.']['marginFooter'],
                'displayDefaultOrientation' => $this->typoscriptSettings['mpdf.']['orientation'],
                'fontDir' => array_merge($fontDirs, [
                    $_SERVER['DOCUMENT_ROOT'] . '/typo3conf/ext/bvt_powermail_pdf/Resources/Public/Fonts/alex-brush/'
                ]),
                'fontdata' => $fontData + [ // lowercase letters only in font key
                    'alexbrush' => [
                        'R' => 'alex-brush-v21-latin-regular.ttf',
                    ]
                ],
                'default_font' => 'alexbrush'
            ]);

            $this->getLogger()->error($this->typoscriptSettings['mpdf.']['marginLeft']);

            // Fluid standalone view
            $htmlView = GeneralUtility::makeInstance('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
            $htmlView->setFormat('html');
            $htmlView->setTemplatePathAndFileName($htmlOriginal);

            $htmlView->assignMultiple($answerData);

            $html = $htmlView->render();

            // $mpdf->showImageErrors = true;

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
        $typolink = $this->typoscriptSettings['typolink.'];
        $typolink['parameter'] = $uri;

        $downloadLink = $GLOBALS['TSFE']->cObj->typolink($label, $typolink);

        return $downloadLink;
    }

    /**
     * @param Mail $mail
     * @param string $hash
     * @ExtbaseAnnotation\Validate("In2code\Powermail\Domain\Validator\UploadValidator", param="mail")
     * @ExtbaseAnnotation\Validate("In2code\Powermail\Domain\Validator\InputValidator", param="mail")
     * @ExtbaseAnnotation\Validate("In2code\Powermail\Domain\Validator\PasswordValidator", param="mail")
     * @ExtbaseAnnotation\Validate("In2code\Powermail\Domain\Validator\CaptchaValidator", param="mail")
     * @ExtbaseAnnotation\Validate("In2code\Powermail\Domain\Validator\SpamShieldValidator", param="mail")
     * @ExtbaseAnnotation\Validate("In2code\Powermail\Domain\Validator\UniqueValidator", param="mail")
     * @ExtbaseAnnotation\Validate("In2code\Powermail\Domain\Validator\ForeignValidator", param="mail")
     * @ExtbaseAnnotation\Validate("In2code\Powermail\Domain\Validator\CustomValidator", param="mail")
     * @return void
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidExtensionNameException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws UnknownObjectException
     * @throws InvalidControllerNameException
     * @throws \Exception
     * @noinspection PhpUnused
     */
    public function createCustomAction(Mail $mail, \In2code\Powermail\Controller\FormController $hash = NULL): void
    {
        $filePath = $this->typoscriptSettings['sourceFile'];
        $powermailFilePath = $this->powermailSettings['setup.']['misc.']['file.']['folder'] . basename($filePath);

        if ($this->typoscriptSettings['activate']) {
            if ($this->typoscriptSettings['sourceFile']) {
                if(!file_exists(GeneralUtility::getFileAbsFileName($this->typoscriptSettings['sourceFile']))) {
                    throw new Exception('The file does not exist: ' . $this->typoscriptSettings['sourceFile'] . ' Please set the correct path in plugin.tx_bvtpowermailpdf.settings.sourceFile', 1417520887);
                }
            }

            if ($this->typoscriptSettings['fillPdf']) {
                $powermailFilePath = $this->generatePdf($mail);
            } else {
                // Copy our pdf to powermail when is does not exist or has changed
                if (!file_exists(GeneralUtility::getFileAbsFileName($powermailFilePath))
                    || (md5_file(GeneralUtility::getFileAbsFileName($powermailFilePath)) != md5_file(GeneralUtility::getFileAbsFileName($filePath)))) {
                    copy(GeneralUtility::getFileAbsFileName($filePath), GeneralUtility::getFileAbsFileName($powermailFilePath));
                }
            }

            $formRepository = GeneralUtility::makeInstance(\In2code\Powermail\Domain\Repository\FieldRepository::class);

            // Display download link
            if ($this->typoscriptSettings['showDownloadLink']) {
                $label = LocalizationUtility::translate('download', 'bvt_powermail_pdf');

                $url = $GLOBALS['TSFE']->tmpl->setup['config.']['baseURL'] . '/' . $powermailFilePath;
                // if no baseURL is set (e. g. cause of use siteconfig)
                // TODO: better use siteconfig directly
                $query = '/';
                if(substr($url, 0, strlen($query)) === $query) {
                    $url = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . $url;
                }

                /* @var $answer In2code\Powermail\Domain\Model\Answer */
                $answer = GeneralUtility::makeInstance(\In2code\Powermail\Domain\Model\Answer::class);

                $field = $formRepository->findByMarkerAndForm($this->typoscriptSettings['marker.']['pdf_url'], 0);

                if (!$field) {
                    /* @var $field In2code\Powermail\Domain\Model\Field */
                    $field = GeneralUtility::makeInstance(\In2code\Powermail\Domain\Model\Field::class);
                    $field->setTitle(LocalizationUtility::translate('downloadUrl', 'bvt_powermail_pdf'));
                    $field->setType('input');
                    $field->setPid(0); // $mail->getPid()
                    $field->setMarker($this->typoscriptSettings['marker.']['pdf_url']);
                }

                $answer->setField($field);
                $answer->setValue($url);

                $mail->addAnswer($answer);

                // Add a field for the download link
                $link = $this->render($url, $label);

                /* @var $answer In2code\Powermail\Domain\Model\Answer */
                $answer = GeneralUtility::makeInstance(\In2code\Powermail\Domain\Model\Answer::class);

                $field = $formRepository->findByMarkerAndForm($this->typoscriptSettings['marker.']['pdf_link'], 0);

                if (!$field) {
                    /* @var $field In2code\Powermail\Domain\Model\Field */
                    $field = GeneralUtility::makeInstance('In2code\Powermail\Domain\Model\Field');
                    $field->setTitle(LocalizationUtility::translate('downloadLink', 'bvt_powermail_pdf'));
                    $field->setType('downloadLink');
                    $field->setPid(0);
                    $field->setMarker($this->typoscriptSettings['marker.']['pdf_link']);
                }

                $answer->setField($field);
                $answer->setValue($link);

                $mail->addAnswer($answer);
            }

            // Append download link to mail
            if ($this->typoscriptSettings['email.']['attachFile']) {
                /* @var $answer In2code\Powermail\Domain\Model\Answer */
                $answer = GeneralUtility::makeInstance('In2code\Powermail\Domain\Model\Answer');

                $field = $formRepository->findByMarkerAndForm('bvt_powermail_pdf_attachment', 0);

                if (!$field) {
                    /* @var $field In2code\Powermail\Domain\Model\Field */
                    $field = GeneralUtility::makeInstance('In2code\Powermail\Domain\Model\Field');
                    $field->setTitle(LocalizationUtility::translate('file', 'bvt_powermail_pdf'));
                    $field->setType('file');
                    $field->setMarker('bvt_powermail_pdf_attachment');
                    $field->setPid(0);
                }

                $answer->setField($field);
                $answer->setValue(json_encode(array(basename($powermailFilePath))));
                $mail->addAnswer($answer);
            }

            if ($this->typoscriptSettings['incremendId.']['enable']) {
                $incremendId = $this->getIncrementId($mail->getUid());

                /* @var $answer In2code\Powermail\Domain\Model\Answer */
                $answer = GeneralUtility::makeInstance('In2code\Powermail\Domain\Model\Answer');

                $field = $formRepository->findByMarkerAndForm('increment_id', 0);

                if (!$field) {
                    /* @var $field In2code\Powermail\Domain\Model\Field */
                    $field = GeneralUtility::makeInstance('In2code\Powermail\Domain\Model\Field');
                    $title = LocalizationUtility::translate('Request_Id', 'bvt_powermail_pdf');
                    if(empty($title)) { $title = 'Request Id'; }
                    $field->setTitle($title);
                    $field->setType('file');
                    $field->setMarker('increment_id');
                    $field->setPid(0);
                }

                $answer->setField($field);
                $answer->setValue('"'.$incremendId.'"');
                $mail->addAnswer($answer);
            }
        }
    }
}
