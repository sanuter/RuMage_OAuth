<?php
###lit###

class RuMage_OAuth_Model_Observer
{
    public function customer_login(Varien_Event_Observer $observer)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();

        if (Mage::helper('ruoauth')->checkEmail($customer)) {
            Mage::getSingleton('core/session')->addSuccess(
                Mage::helper('ruoauth')->__('Input valid email')
            );
        }
    }
}