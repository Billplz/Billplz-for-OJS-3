<?php

/**
 * @file plugins/payment/billplz/BillplzPaymentSettingsForm.inc.php
 *
 * @class BillplzPaymentSettingsForm
 * @ingroup plugins_payment_billplz
 *
 * @brief Form for managers to configure billplz payments.
 */

import('lib.pkp.classes.form.Form');

class BillplzPaymentSettingsForm extends Form
{

    /** @var int Associated context ID */
    private $_contextId;

    /** @var BillplzPaymentPlugin Billplz payment plugin */
    private $_plugin;

    /**
     * Constructor
     * @param $plugin BillplzPaymentPlugin Billplz payment plugin
     * @param $contextId int Context ID
     */
    public function __construct($plugin, $contextId)
    {
        $this->_contextId = $contextId;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    /**
     * Get the setting names for this form.
     * @return array
     */
    private function _getSettingNames()
    {
        return array('billplz_api_key', 'billplz_collection_id', 'billplz_x_signature', 'billplz_deliver');
    }

    public function fetch($request)
    {
        $templateMgr = TemplateManager::getManager($request);
        $billplz_delivers = array(
            '0' => 'No Notification',
            '1' => 'Email Only (FREE)',
            '2'  => 'SMS Only (RM0.30)',
            '3'  => 'Both (RM0.30)'
        );
				$templateMgr->assign('billplz_delivers', $billplz_delivers);
				return parent::fetch($request);
    }

    /**
     * Initialize form data.
     */
    public function initData()
    {
        $contextId = $this->_contextId;
        $plugin = $this->_plugin;

        foreach ($this->_getSettingNames() as $settingName) {
            $this->setData($settingName, $plugin->getSetting($contextId, $settingName));
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData()
    {
        $this->readUserVars($this->_getSettingNames());
    }

    /**
     * Save settings.
     */
    public function execute()
    {
        $plugin = $this->_plugin;
        $contextId = $this->_contextId;
        foreach ($this->_getSettingNames() as $settingName) {
            $plugin->updateSetting($contextId, $settingName, $this->getData($settingName));
        }
    }
}
