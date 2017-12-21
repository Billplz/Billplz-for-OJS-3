<?php
/**
 * @file BillplzPaymentForm.inc.php
 *
 *
 * @class BilllplzPaymentForm
 *
 * Form for Billplz-based payments.
 *
 */
require 'billplz.php';


import('lib.pkp.classes.form.Form');

class BillplzPaymentForm extends Form
{

    /** @var BillplzPaymentPlugin */
    public $_billplzPaymentPlugin;

    /** @var QueuedPayment */
    public $_queuedPayment;

    /**
     * @param $billplzPaymentPlugin BillplzPaymentPlugin
     * @param $queuedPayment QueuedPayment
     */
    public function __construct($billplzPaymentPlugin, $queuedPayment)
    {
        $this->_billplzPaymentPlugin = $billplzPaymentPlugin;
        $this->_queuedPayment = $queuedPayment;
        parent::__construct($this->_billplzPaymentPlugin->getTemplatePath() . '/paymentForm.tpl');
    }

    /**
     * @copydoc Form::display()
     */
    public function display($request = null, $template = null)
    {
        $journal = $request->getJournal();
        $paymentManager = Application::getPaymentManager($journal);

        $api_key = $this->_billplzPaymentPlugin->getSetting($journal->getId(), 'billplz_api_key');
        $collection_id = $this->_billplzPaymentPlugin->getSetting($journal->getId(), 'billplz_collection_id');
        $deliver = $this->_billplzPaymentPlugin->getSetting($journal->getId(), 'billplz_deliver');

        $paymentid = $this->_queuedPayment->getId();
        $pluginName = array($this->_billplzPaymentPlugin->getName(), 'return');

        $returnurl = $request->url(null, 'payment', 'plugin', $pluginName, '');
        $callbackurl = $request->url(null, 'payment', 'plugin', $pluginName, '');
        $description = $paymentManager->getPaymentName($this->_queuedPayment);

        $user = $request->getUser();
        $name = $user?$user->getFullName():('(' . __('common.none') . ')');
        $email = $user?$user->getEmail():('(' . __('common.none') . ')');

        $gateway = new Billplz($api_key);
        $gateway
            ->setCollection($collection_id)
            ->setDescription($description)
            ->setPassbackURL($callbackurl, $returnurl)
            ->setReference_1($paymentid)
            ->setReference_1_Label('ID')
            ->setName($name)
            ->setEmail($email)
            //->setReference_2('Lot 100, AAA, BB')
            //->setReference_2_Label('Address')
            ->setDeliver($deliver)
            ->setAmount(number_format($this->_queuedPayment->getAmount(), 2))
            ->create_bill(true);
        if (!empty($gateway->getErrorMessage())) {
            error_log('Billplz transaction exception: ' . $gateway->getErrorMessage());
            exit($gateway->getErrorMessage());
        }

        $request->redirectUrl($gateway->getURL());
    }
}
