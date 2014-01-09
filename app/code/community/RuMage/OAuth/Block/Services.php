<?php
###lit###

class RuMage_OAuth_Block_Services extends Mage_Core_Block_Template
{
    public function getServices()
    {
        return $this->_getService();
    }

    /**
     * @return RuMage_OAuth_Model_Base
     */
    public function getAuthenticate()
    {
        $_provider = Mage::app()->getHelper('ruoauth')->getProvider();

        if (empty($_provider)) {
             return FALSE;
        }

        return $_provider->getIsAuthenticated();
    }

    public function isCustomerLogin()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function getProviderUrl($service = '')
    {
        return Mage::getUrl('ruoauth/provider', array('service' => $service));
    }

    public function getProviderJsOptions($type)
    {
        $_provider = Mage::app()->getHelper('ruoauth')->getProvider($type);
        return array(
            'width' => $_provider->getWidth(),
            'heigth' => $_provider->getHeight(),
        );
    }

    protected function _getService()
    {
        return Mage::app()->getHelper('ruoauth')->getServices();
    }
}