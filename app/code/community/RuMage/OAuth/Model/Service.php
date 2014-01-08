<?php
/**
 * @package Magento OAuth.
 * @author: A.A.Treitjak
 * @copyright: 2012 - 2014 BelVG.com
 */


class RuMage_OAuth_Model_Service extends Varien_Object
{
    protected $config = array();

    /**
     * Init lib provider.
     */
    public function __construct()
    {
        require_once Mage::getBaseDir('lib') . DIRECTORY_SEPARATOR . 'Opauth' . DIRECTORY_SEPARATOR . 'Opauth.php';
        require_once Mage::getBaseDir('lib') . DIRECTORY_SEPARATOR . 'Opauth' . DIRECTORY_SEPARATOR . 'OpauthStrategy.php';

        parent::__construct();
    }

    public function getService($provider = '')
    {
        if (empty($provider)) {
            $this->_getSession()->addError(
                Mage::helper('ruoauth')->__('Unknown service.')
            );

            return NUll;
        }

        //Set Name current provider
        $this->setServiceName($provider);

        return new Opauth($this->configProvider());
    }

    public function configProvider()
    {
        if (!$this->getServiceName()) {
            $this->_getSession()->addError(
                Mage::helper('ruoauth')->__('Unknown service.')
            );

            return $this->config;
        }

        //Set Application Id
        $this->setConfigParam('app_id', $this->getClientId());

        //Set Application Secret
        $this->setConfigParam('app_secret', $this->getClientSecret());

        return $this->config;
    }

    /**
     * Return application ID.
     * @return mixed
     */
    public function getClientId()
    {
        return Mage::getStoreConfig('ruoauth/' . $this->getServiceName() . '/application_id');
    }

    /**
     * Return application secret key.
     * @return mixed
     */
    public function getClientSecret()
    {
        return Mage::getStoreConfig('ruoauth/' . $this->getServiceName() . '/application_secret');
    }

    protected function setConfigParam($key, $value)
    {
        return $this->config[$key] = $value;
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
} 