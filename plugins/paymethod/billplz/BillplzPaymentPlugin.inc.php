<?php

/**
 * @file plugins/paymethod/billplz/BillplzPaymentPlugin.inc.php *
 * @class BillplzPaymentPlugin
 * @ingroup plugins_paymethod_billplz
 *
 * @brief Billplz payment plugin class
 */

import('lib.pkp.classes.plugins.PaymethodPlugin');
require_once(dirname(__FILE__) . '/vendor/autoload.php');
require 'billplz.php';

class BillplzPaymentPlugin extends PaymethodPlugin
{

    /**
     * @see Plugin::getName
     */
    public function getName()
    {
        return 'BillplzPayment';
    }

    /**
     * @see Plugin::getDisplayName
     */
    public function getDisplayName()
    {
        return __('plugins.paymethod.billplz.displayName');
    }

    /**
     * @see Plugin::getDescription
     */
    public function getDescription()
    {
        return __('plugins.paymethod.billplz.description');
    }

    /**
     * @see Plugin::register
     */
    public function register($category, $path)
    {
        if (parent::register($category, $path)) {
            $this->addLocaleData();
            return true;
        }
        return false;
    }

    /**
     * @copydoc PaymethodPlugin::getSettingsForm()
     */
    public function getSettingsForm($context)
    {
        $this->import('BillplzPaymentSettingsForm');
        return new BillplzPaymentSettingsForm($this, $context->getId());
    }

    /**
     * @copydoc PaymethodPlugin::getPaymentForm()
     */
    public function getPaymentForm($context, $queuedPayment)
    {
        $this->import('BillplzPaymentForm');
        return new BillplzPaymentForm($this, $queuedPayment);
    }

    /**
     * @see PaymethodPlugin::isConfigured
     */
    public function isConfigured()
    {
        $context = $this->getRequest()->getContext();
        if (!$context) {
            return false;
        }
        if ($this->getSetting($context->getId(), 'billplz_api_key') == '') {
            return false;
        }
        if ($this->getSetting($context->getId(), 'billplz_collection_id') == '') {
            return false;
        }
        if ($this->getSetting($context->getId(), 'billplz_x_signature') == '') {
            return false;
        }
        return true;
    }

    /**
     * Handle a handshake with the Billplz service
     */
    public function handle($args, $request)
    {
        $journal = $request->getJournal();
        $queuedPaymentDao = DAORegistry::getDAO('QueuedPaymentDAO');
        import('classes.payment.ojs.OJSPaymentManager'); // Class definition required for unserializing

        $api_key = $this->getSetting($journal->getId(), 'billplz_api_key');
        $x_signature = $this->getSetting($journal->getId(), 'billplz_x_signature');

        if (isset($_GET['billplz'])) {
            $data = Billplz::getRedirectData($x_signature);
        } else {
            $data = Billplz::getCallbackData($x_signature);
        }

        $billplz = new Billplz($api_key);
        $moreData = $billplz->check_bill($data['id']);

        $queuedPayment = $queuedPaymentDao->getById($moreData['reference_1']);

        if ($data['paid']) {
          ini_set('display_errors', 1);
          ini_set('display_startup_errors', 1);
          error_reporting(E_ALL);
            $paymentManager = Application::getPaymentManager($journal);
            $paymentManager->fulfillQueuedPayment($request, $queuedPayment, $this->getName());
            $request->redirectUrl($queuedPayment->getRequestUrl());
        } else if (isset($_GET['billplz'])) {
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign('message', 'plugins.paymethod.billplz.error');
            $templateMgr->display('frontend/pages/message.tpl');
        }
    }

    /**
     * @see Plugin::getInstallEmailTemplatesFile
     */
    public function getInstallEmailTemplatesFile()
    {
        return ($this->getPluginPath() . DIRECTORY_SEPARATOR . 'emailTemplates.xml');
    }

    /**
     * @see Plugin::getInstallEmailTemplateDataFile
     */
    public function getInstallEmailTemplateDataFile()
    {
        return ($this->getPluginPath() . '/locale/{$installedLocale}/emailTemplates.xml');
    }

    /**
     * @copydoc Plugin::getTemplatePath()
     */
    public function getTemplatePath($inCore = false)
    {
        return parent::getTemplatePath($inCore) . 'templates/';
    }
}
