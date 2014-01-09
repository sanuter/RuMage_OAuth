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

        //Set path
        $this->setPath();

        //Set callback
        $this->setCallbackUrl();

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

    public function getId()
    {
        return $this->getUid();
    }

    public function getFirstname()
    {
        return $this->getFirstName();
    }

    public function getLastname()
    {
        return $this->getLastName();
    }

    public function getEmail()
    {
        if (!$this->hasData('email')) {
            $socialEmail = Mage::helper('ruoauth')->getServiceEmail($this);
            $this->setData('email', $socialEmail);
        }

        return $this->getData('email');
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
     * Retrieve customer session model object.
     *
     * @return RuMage_OAuth_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('ruoauth/session');
    }

    /**
     * Set path.
     */
    protected function setPath()
    {
        $this->config['path'] = Mage::helper('ruoauth')->getPathSite();
    }

    /**
     * Set callback url.
     */
    protected function setCallbackUrl()
    {
        $this->config['callback_url'] = Mage::helper('ruoauth')->getCallbackUrl();
    }

    /**
     * Init customer info for request.
     */
    protected function initService()
    {
        if (Mage::getSingleton('core/session')->hasData('opauth')) {
            $response = Mage::getSingleton('core/session')->getOpauth();
            if (array_key_exists('error', $response)) {
                Mage::throwException(Mage::helper('ruoauth')->__('Authentication error: Opauth returns error auth response.'));
            } else {
                if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])) {
                    Mage::throwException(Mage::helper('ruoauth')->__('Invalid auth response: Missing key auth response components.'));
                } elseif (!$this->getService(strtolower($response['provider']))->validate(sha1(print_r($response['auth'], TRUE)), $response['timestamp'], $response['signature'], $reason)) {
                    Mage::throwException(Mage::helper('ruoauth')->__('Invalid auth response: %s.', $reason));
                } else {
                    $this->setData($response);
                    $this->getProvider()->initInfo();
                }
            }
        } else {
            Mage::throwException(Mage::helper('ruoauth')->__('Authentication error: Opauth returns error auth response.'));
        }
    }
} 