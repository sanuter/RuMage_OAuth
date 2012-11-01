<?php

class RuMage_OAuth_Block_Services extends Mage_Core_Block_Template
{
    /**
     * Return all services.
     *
     * @return mixed
     */
    public function getServices()
    {
        return Mage::helper('ruoauth')->getServices();
    }

    /**
     * Check login user.
     *
     * @return mixed
     */
    public function isCustomerLogin()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Return url current provider.
     *
     * @param string $service
     * @return string
     */
    public function getProviderUrl($service = '')
    {
        return Mage::getUrl('ruoauth/provider', array('service' => $service));
    }

    /**
     * Return options current provider.
     *
     * @param $type
     * @return array
     */
    public function getProviderJsOptions($type)
    {
        return array(
            'width' => Mage::helper('ruoauth')->getWidth($type),
            'height' => Mage::helper('ruoauth')->getHeight($type),
        );
    }
}