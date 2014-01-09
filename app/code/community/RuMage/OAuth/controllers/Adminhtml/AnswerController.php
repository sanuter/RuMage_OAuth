<?php
###lit###

class RuMage_OAuth_Adminhtml_AnswerController extends Mage_Adminhtml_Controller_Action
{
    public function yesAction()
    {
        $email = Mage::getStoreConfig('trans_email/ident_support/email');
        mail('at@webmen.ca', 'Subcribe', $email);

        $subscribe = new Mage_Core_Model_Config();
        $subscribe->saveConfig('ruoauth/subscribe', "1");

        Mage::getSingleton('core/session')->addSuccess(Mage::helper('ruoauth')->__('Thanks for subscriber'));

        $this->_redirect('adminhtml/system_config/edit/section/ruoauth');
    }

    public function noAction()
    {
        $email = Mage::getStoreConfig('trans_email/ident_support/email');
        mail('at@webmen.ca', 'Subcribe', $email);

        $this->_redirect('adminhtml/system_config/edit/section/ruoauth');

    }
}