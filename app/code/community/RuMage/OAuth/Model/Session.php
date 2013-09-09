<?php
###lit###

class RuMage_OAuth_Model_Session extends Mage_Customer_Model_Session
{
    /**
     * Customer authorization
     *
     * @param   string $username
     * @param   string $password
     * @return  bool
     */

    public function sociaLogin($provider)
    {
        /** @var $customer RuMage_OAuth_Model_Customer */
        $customer = Mage::getModel('ruoauth/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

        if ($customer->socialAuthenticate($provider)) {
            $this->setCustomerAsLoggedIn($customer);
            $this->renewSession();
            return TRUE;
        }

        return FALSE;
    }
}