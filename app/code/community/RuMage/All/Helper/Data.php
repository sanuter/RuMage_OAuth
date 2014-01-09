<?php


class RuMage_All_Helper_Data extends Mage_Core_Helper_Abstract{

    /**
     * Send email
     *
     * @param string Transactional Email Template's ID ()
     * @param array ( email => '***', name = > '***' , $vars => array )
     * @return Mage_Referralreward_Model_Friends_Collection
     */
    public function sendToEmail($templateId, $data){
        //Set recepient information
        $recepientEmail = $data['email'];
        $recepientName  = $data['name'];

        //Set sender information
        $customer_id    = (int)Mage::getSingleton('customer/session')->getId();
        $sender         = getCustomerInfo($customer_id);

        //array variables that can be used in email template
        $vars           = $data['vars'];

        //Send Transactional Email

        /* @var $translate Mage_Core_Model_Translate */
        $translate      = Mage::getSingleton('core/translate');
        $storeId        = Mage::app()->getStore()->getId();
        /* @var $translate Mage_Core_Model_Email_Template */
        $template       = Mage::getModel('core/email_template')
            ->sendTransactional($templateId, $sender, $recepientEmail, $recepientName, $vars, $storeId);
        $translate->setTranslateInline(TRUE);
    }

    /**
     * Get current customer full name and email
     *
     * @param int Customer id
     * @return array
     */
    public function getCustomerInfo($id){
        $customer = Mage::getModel('customer/customer')->load($id);
        $name     = $customer['firstname'] . ' ' . $customer['lastname'];
        $email    = $customer['email'];
        return array('name'  => $senderName,
                     'email' => $senderEmail);
    }

}