<?php
/**
 * @package Magento OAuth.
 * @author: A.A.Treitjak
 * @copyright: 2012 - 2014 BelVG.com
 */

class RuMage_OAuth_Helper_Service extends RuMage_OAuth_Helper_Data
{
    protected $config = array();
    protected $provider = NULL;
    protected $configKey = array(
        'application_id' => '',
        'application_secret' => ''
    );

    public function configProvider(RuMage_OAuth_Model_Service $provider)
    {
        if (!$provider->getServiceName()) {
            $this->getSession()->addError(
                Mage::helper('ruoauth')->__('Unknown service.')
            );

            return $this->config;
        }

        $this->setProvider($provider);

        try {
            $this->initConfigKey();
        } catch (Exception $e) {

            $this->getSession()->addError(
                Mage::helper('ruoauth')->__('Unknown service.')
            );

            return $this->config;
        }

        //Set Application Id
        $this->setClientId();

        //Set Application Secret
        $this->setClientSecret();

        return $this->config;
    }

    /**
     * Return application ID.
     * @return mixed
     */
    public function setClientId()
    {
        $this->setConfigParam($this->getClientIdKey(), $this->getClientId());
    }

    /**
     * Return application secret key.
     * @return mixed
     */
    public function setClientSecret()
    {
        $this->setConfigParam($this->getClientSecretKey(), $this->getClientSecret());
    }

    protected function initConfigKey()
    {
        $this->configKey = Mage::helper('ruoauth/services_' . $this->getProvider()->getServiceName())->getConfigKey();
    }

    protected function getClientId()
    {
        return Mage::getStoreConfig('ruoauth/' . $this->getProvider()->getServiceName() . '/application_id');
    }

    protected function getClientSecret()
    {
        return Mage::getStoreConfig('ruoauth/' . $this->getProvider()->getServiceName() . '/application_secret');
    }

    protected function getClientIdKey()
    {
        if (!$this->configKey['application_id']) {
            $this->getSession()->addError(
                Mage::helper('ruoauth')->__('Empty application id.')
            );

            return '';
        }

        return $this->configKey['application_id'];
    }

    protected function getClientSecretKey()
    {
        if (!$this->configKey['application_secret']) {
            $this->getSession()->addError(
                Mage::helper('ruoauth')->__('Empty application secret.')
            );

            return '';
        }

        return $this->configKey['application_secret'];
    }

    protected function setConfigParam($key, $value)
    {
        return $this->config[$key] = $value;
    }

    protected function getProvider()
    {
        return $this->provider;
    }

    protected function setProvider(RuMage_OAuth_Model_Service $provider)
    {
        return $this->provider = $provider;
    }
} 