<?php
###lit###

class RuMage_OAuth_ProviderController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * @var RuMage_OAuth_Model_Base
     */
    private $_provider;

    public function indexAction()
    {
        if ($this->getRequest()->getParam('service', '')) {
            if ($this->_setService()) {
                $this->_authenticate();
                //TODO bad code
                echo '<script>window.close(); window.opener.location.reload();</script>';
            }
        }
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

    protected function _authenticate()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        /* $session RuMage_OAuth_Model_Session */
        $session = $this->_getSession();

        if ($this->_provider->authenticate()) {
            /* @var $customer RuMage_OAuth_Model_Customer */
            $customer = Mage::getModel('ruoauth/customer');

            if ($customer->isNewCustomer($this->_provider)) {
                try {
                    /* prepare customer */
                    $customer->prepareData($this->_provider);

                    if ($customer->validate()) {
                        $customer->save();

                        if (!Mage::helper('ruoauth')->checkEmail($customer)) {
                            $customer->sendNewAccountEmail();
                        }
                    } else {
                        throw new RuMage_OAuth_Exception($this->__('No validate data.'));
                    }
                } catch (RuMage_OAuth_Exception $e) {
                    throw new RuMage_OAuth_Exception(
                        $this->__('Unable to complete the request because the user was not authenticated.')
                    );
                }
            }

            if (Mage::helper('ruoauth')->checkEmail($customer)) {
                Mage::getSingleton('core/session')->addSuccess(
                    Mage::helper('ruoauth')->__('Input valid email')
                );
            }

            $session->sociaLogin($this->_provider);
        }
    }

    protected function _setService()
    {
        //TODO validation
        $this->_provider = Mage::app()->getHelper('ruoauth')->getService($this);

        return $this->_provider;
    }
}