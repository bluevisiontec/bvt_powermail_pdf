.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)

=================================
EXT: bvt_powermail_pdf
=================================

:Created:
      2010-12-18 19:57

:Changed:
      2019-10-17 13:00

:Classification:
      bvt_powermail_pdf

:Keywords:
      pdf, powermail, form, html2pdf, html, powermailpdf

:Author:
      Reinhard Schneidewind, BlueVisionTec UG (haftungsbeschränkt)

:Email:
      info@bluevisiontec.de

:Language:
      en

:Description:
      bvt_powermail_pdf is a fork of the extension `bw_powermail_mpdf <https://typo3.org/extensions/repository/view/bw_powermail_mpdf/>`_ which is a modification of the extension `powermailpdf <https://typo3.org/extensions/repository/view/powermailpdf/>`_ and can be used for automatic pdf generation with `mPDF <http://www.mpdf1.com/mpdf/index.php>`_ after a powermail form is submitted.


Introduction
-------------------------------------------------------------

This extension can insert powermail data to an html document and transform it to PDF.


Installation
-------------------------------------------------------------

- Install this extension
- Download version 6.1.0 `mPDF <https://github.com/mpdf/mpdf/releases>`_ and unzip the files to typo3conf/ext/bvt_powermail_pdf/Classes/mpdf
- Set config.baseUrl
- Add a valid TypoScript Setup Configuration to the page which uses the powermail form.
- Add static template bvt_powermail_pdf
- Create a html template which should be rendered to pdf and set the filepath in TypoScript Setup.

TypoScript Configuration
"""""""""""""""""""""""""
This is an example configuration.
   ::

      plugin.tx_bvtpowermailpdf {
        settings {
          # Activate powermail pdf
          activate = 1

          # Show download link on success page
          showDownloadLink = 1

          # Send PDF via Email?
          email{
            attachFile = 0
          }

          # Link settings
          typolink {
            extTarget = _blank
          }

          # PDF settings
          sourceFile = fileadmin/form.html
          fillPdf = 1
          fieldMap {
            # pdffield = powermailfield
            name = name
            email = email
            interests = interests
          }
        }
      }

You might want to configure PowerMail to add attachments to sender mails:

  ::

      plugin.tx_powermail.settings {
          sender.attachment = 1
      }


Configuration
-------------------------------------------------------------

See "Installation" for TypoScript configuration.

Powermail configuration
"""""""""""""""""""""""""

General markers:

- powermail_all : All filled fields
- pdf_url : URL to generated pdf
- pdf_linl : Link to generated pdf (typolink)
- increment_id : An incrementing "request id" based on mail uid

Add pdf url to sender/receiver mail:
    ::

    {pdf_url -> f:format.raw()}

Add pdf link to "thank you" page:
    ::

    {pdf_link -> f:format.raw()}

HTML Template for PDF generation
-------------------------------------------------------------

See `mPDF documentation <https://mpdf.github.io/>`_ for details.

Issues
-------------------------------------------------------------

Please create issue reports on Github: https://github.com/bluevisiontec/bvt_powermail_pdf

Credits
-------------------------------------------------------------

bvt_powermail_pdf is a fork of the extension `bw_powermail_mpdf <https://typo3.org/extensions/repository/view/bw_powermail_mpdf/>`_ in version 1.0.4 .

bw_powermail_mpdf is developed by Browserwerk. See https://docs.typo3.org/typo3cms/extensions/bw_powermail_mpdf/ for further details.

bw_powermail_mpdf itself is a modification of `powermailpdf <https://typo3.org/extensions/repository/view/powermailpdf/>`_ .

powermailpdf is developed by Eike Starkmann. See https://docs.typo3.org/typo3cms/extensions/powermailpdf/ for further deetails.
