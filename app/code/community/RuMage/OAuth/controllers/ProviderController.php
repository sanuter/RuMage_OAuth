<?php

class RuMage_OAuth_ProviderController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * Current provider.
     * @var $_provider RuMage_OAuth_Model_Base
     */
    private $_provider;

    /**
     * Alias cusrrent provider.
     * @var string
     */
    private $_service = '';

    /**
     * Main action.
     */
    public function indexAction()
    {
        if ($this->getRequest()->getParam('service', '')) {
            if ($this->_setService()) {
                $this->_authenticate();
            }
        }

        $this->loadLayout('ruoauth_provider_index');
        $this->renderLayout();
    }

    public function callbackAction()
    {

    }

    /**
     * Close action.
     */
    public function cancelAction()
    {
        $this->loadLayout('ruoauth_provider_cancel');
        $this->renderLayout();
    }

    /**
     * Retrieve customer session model object
     *
     * @return RuMage_OAuth_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('ruoauth/session');
    }

    /**
     *
     * @throws RuMage_OAuth_Exception
     */
    protected function _authenticate()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
            //TODO login and confirmation
            try {
                $this->_provider->run();
                /* @var $customer RuMage_OAuth_Model_Customer */
                $customer = Mage::getModel('ruoauth/customer');

                if ($customer->isNewCustomer($this->_provider)) {
                    $this->_createCustomer($customer);
                }

                $this->_getSession()->sociaLogin($this->_provider);
                if ($this->_getSession()->getCustomer()->getIsJustConfirmed()) {
                    if (Mage::helper('ruoauth')->checkEmail($customer)) {
                        $this->_getSession()->addError(
                            Mage::helper('ruoauth')->__('Input valid email')
                        );
                    }
                }
            } catch (Mage_Core_Exception $e) {
                switch ($e->getCode()) {
                    case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                        $value = Mage::helper('customer')->getEmailConfirmationUrl($this->_provider->getEmail());
                        $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                        break;

                    default:
                        $message = $e->getMessage();
                        break;
                }

                $this->_getSession()->addError($message);
            }
    }

    protected function _createCustomer(RuMage_OAuth_Model_Customer $customer)
    {
        try {
            /* prepare customer */
            $customer->prepareData($this->_provider);

            if ($customer->validate()) {
                $customer->save();

                Mage::dispatchEvent('customer_register_success',
                    array('account_controller' => $this, 'customer' => $customer)
                );

                if (!Mage::helper('ruoauth')->checkEmail($customer)) {
                    if ($customer->isConfirmationRequired()) {
                        $customer->sendNewAccountEmail(
                            'confirmation',
                            $this->_getSession()->getBeforeAuthUrl(),
                            Mage::app()->getStore()->getId()
                        );
                        $this->_getSession()->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
                    } else {
                        $customer->sendNewAccountEmail();
                    }
                }
            } else {
                $this->_getSession()->addError(
                    Mage::helper('ruoauth')->__('No validate data.')
                );
            }
        } catch (RuMage_OAuth_Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('ruoauth')->__('Unable to complete the request because the user was not authenticated.')
            );
        }
    }

    /**
     * Set current provider.
     * @return bool
     */
    protected function _setService()
    {
        //Set service alias;
        $this->_service = $this->getRequest()->getParam('service', '');

        //Set provider
        try {
            $this->_provider = Mage::getSingleton('ruoauth/service')->getService($this->_service);
            return TRUE;
        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('ruoauth')->__('Unknown service.')
            );
            $this->_redirect('*/*/cancel');
            return FALSE;
        }
    }
}