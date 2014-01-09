<?php
###lit##

class RuMage_OAuth_Model_Customer
    extends Mage_Customer_Model_Customer
{
    public function validate()
    {
        foreach ($this->_attributes() as $attribute => $pattern) {
            if ($this->hasData($attribute)) {
                if (!Zend_Validate::is($this->getData($attribute), $pattern)) {
                    return TRUE;
                }
            }
        }

        return TRUE;
    }

    public function prepareData( RuMage_OAuth_Model_Base $provider )
    {
        $this->setData('service_uid', $provider->getId());
        $this->setData('service_name', $provider->getServiceName());
        $this->setData('firstname', $provider->getFirstname());
        $this->setData('lastname', $provider->getLastname());
        $this->setData('email', Mage::helper('ruoauth')->getServiceEmail($provider));
        $this->setData('password', $provider->getId());
    }

    public function isNewCustomer($provider)
    {
        $this->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
        $this->loadByEmail(Mage::helper('ruoauth')->getServiceEmail($provider));

        if ($this->getId()) {
            return FALSE;
        }

        return TRUE;
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
        $this->loadByEmail(Mage::helper('ruoauth')->getServiceEmail($provider));

        if (!$this->getId()) {
            throw Mage::exception('RuMage_Oauth', Mage::helper('ruoauth')->__('Invalid authenticate.'));
        }

        return TRUE;
    }

    protected function _attributes()
    {
        return array (
            'social_type' => 'Alpha',
            'social_uid' => 'Alnum',
            'first_name' => 'Alpha',
            'last_name' => 'Alpha',
            //'email' =>  'EmailAddress',
            //'password' => 'NotEmpty',
        );
    }


}