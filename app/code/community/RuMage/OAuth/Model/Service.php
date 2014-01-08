<?php
/**
 * @package Magento OAuth.
 * @author: A.A.Treitjak
 * @copyright: 2012 - 2014 BelVG.com
 */


class RuMage_OAuth_Model_Service extends Varien_Object
{
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
        }

        return new Opauth(array());
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