<?php
/**
 * @package Magento OAuth.
 * @author: A.A.Treitjak
 * @copyright: 2012 - 2014 BelVG.com
 */


class RuMage_OAuth_Model_Service extends Mage_Core_Model_Abstract
{
    /**
     * Enity config.
     * @var array
     */
    protected $config = array();

    /**
     * Init lib provider.
     */
    public function _construct()
    {
        require_once Mage::getBaseDir('lib') . DIRECTORY_SEPARATOR . 'Opauth' . DIRECTORY_SEPARATOR . 'Opauth.php';
        require_once Mage::getBaseDir('lib') . DIRECTORY_SEPARATOR . 'Opauth' . DIRECTORY_SEPARATOR . 'OpauthStrategy.php';
    }

    /**
     * Return provider for current service.
     * @param string $provider
     *
     * @return null|Opauth
     */
    public function getService($provider = '')
    {
        if (empty($provider)) {
            $this->_getSession()->addError(
                Mage::helper('ruoauth')->__('Unknown service.')
            );

            return NULL;
        }

        //Set current provider
        $this->setProvider($provider);

        return new Opauth($this->configProvider(), FALSE);
    }

    /**
     * Return config for current provider.
     * @return array
     */
    public function configProvider()
    {
        if (!$this->getProvider()->getServiceName()) {
            $this->getSession()->addError(
                Mage::helper('ruoauth')->__('Unknown service.')
            );

            return $this->config;
        }

        //Set Application Id
        $this->setClientId();

        //Set Application Secret
        $this->setClientSecret();

        $this->config['path'] = '/oauth/ruoauth/provider/index/service/';

        return $this->config;
    }

    /**
     * Return application ID.
     * @return mixed
     */
    public function setClientId()
    {
        $this->setConfigParam($this->getProvider()->getClientIdKey(), $this->getClientIdValue());
    }

    /**
     * Return application secret key.
     * @return mixed
     */
    public function setClientSecret()
    {
        $this->setConfigParam($this->getProvider()->getClientSecretKey(), $this->getClientSecretValue());
    }

    /**
     * Return application id value.
     * @return mixed
     */
    protected function getClientIdValue()
    {
        return Mage::getStoreConfig('ruoauth/' . $this->getProvider()->getServiceName() . '/application_id');
    }

    /**
     * Return application secret value.
     * @return mixed
     */
    protected function getClientSecretValue()
    {
        return Mage::getStoreConfig('ruoauth/' . $this->getProvider()->getServiceName() . '/application_secret');
    }

    /**
     * Set param in config.
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    protected function setConfigParam($key, $value)
    {
        return $this->config['Strategy'][$this->getProvider()->getServiceName()][$key] = $value;
    }

    /**
     * Set cuurent provider.
     * @param $provider
     *
     * @return Varien_Object
     */
    protected function setProvider($provider)
    {
        $providerModel = Mage::getModel('ruoauth/services_' . $provider);
        return $this->setData('provider', $providerModel);
    }

    /**
     * Retrieve customer session model object
     *
     * @return RuMage_OAuth_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('ruoauth/session');
    }
} 