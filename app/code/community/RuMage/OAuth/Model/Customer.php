<?php

class RuMage_OAuth_Model_Customer
    extends Mage_Customer_Model_Customer
{
    /**
     * Validate data new customer.
     * @return bool
     */
    public function validate()
    {
        foreach ($this->_attributes() as $attribute => $pattern) {
            if ($this->hasData($attribute)) {
                if (!Zend_Validate::is($this->getData($attribute), $pattern)) {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /**
     * Set data customer.
     * @param RuMage_OAuth_Model_Base $provider
     */
    public function prepareData( RuMage_OAuth_Model_Service $provider )
    {
        //TODO change set data.
        $this->setData('service_uid', $provider->getId());
        $this->setData('service_name', $provider->getServiceName());
        $this->setData('firstname', $provider->getFirstname());
        $this->setData('lastname', $provider->getLastname());
        $this->setData('email', Mage::helper('ruoauth')->getServiceEmail($provider));
        $this->setData('password', $this->generatePassword());
    }

    /**
     * Source customer by your email.
     * @param RuMage_OAuth_Model_Base $provider
     * @return bool
     */
    public function isNewCustomer(RuMage_OAuth_Model_Service $provider)
    {
        $this->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
        $this->loadByEmail(Mage::helper('ruoauth')->getServiceEmail($provider));

        if ($this->getId()) {
            return FALSE;
        }

        return $this->_checkUidCustomer($provider);
    }

    /**
     * Authenticate customer
     *
     * @param  string $login
     * @param  string $password
     * @throws Mage_Core_Exception
     * @return TRUE
     *
     */
    public function socialAuthenticate($provider)
    {
        $this->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
        $this->loadByEmail(Mage::helper('ruoauth')->getServiceEmail($provider));

        if (!$this->getId()) {
            if ($this->_checkUidCustomer($provider)) {
                Mage::helper('ruoauth')->getSession()->addError(
                    Mage::helper('ruoauth')->__('Invalid authenticate.')
                );
            }
        }

        return TRUE;
    }

    /**
     * Source customer by your social UID.
     * @param RuMage_OAuth_Model_Base $provider
     * @return bool
     */
    protected  function _checkUidCustomer(RuMage_OAuth_Model_Service  $provider)
    {
        $this->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
        $customer = $this->getCollection()
            ->addAttributeToFilter('service_uid', array('like' => $provider->getId()))
            ->addAttributeToFilter('service_name', array('like' => $provider->getServiceName()))
            ->getFirstItem();

        if ($customer->getId()) {
            $this->load($customer->getId());
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Array attributes for validate.
     * @return array
     */
    protected function _attributes()
    {
        return array (
            'service_name' => 'NotEmpty',
            'service_uid' => 'NotEmpty',
            'first_name' => 'Alpha',
            'last_name' => 'Alpha',
            'email' =>  'NotEmpty',
            'password' => 'NotEmpty',
        );
    }


}