<?php
###lit###

class RuMage_OAuth_Adminhtml_AnswerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Email site.
     */
    const XML_PATH_EMAIL_SITE = 'trans_email/ident_support/email';

    /**
     * Dev email.
     */
    const DEV_EMAIL = 'subscriber@gentoru.ru';

    /**
     * Action Yes.
     */
    public function yesAction()
    {
        $subscribe = new Mage_Core_Model_Config();
        $subscribe->saveConfig('ruoauth/subscribe', "1");

        $this->_sendSubcribe('yes');
    }

    /**
     * Action No.
     */
    public function noAction()
    {
        $this->_sendSubcribe('no');
    }

    /**
     * Send subscribe letter.
     * @param $answer
     */
    protected function _sendSubcribe($answer)
    {
        $mail = Mage::getModel('core/email');
        $mail->setToName('RuMage');
        $mail->setToEmail(self::DEV_EMAIL);
        $mail->setBody('Subcribe - ' . $answer . '.');
        $mail->setSubject('Subscriber');
        $mail->setFromEmail(Mage::getStoreConfig(self::XML_PATH_EMAIL_SITE));
        $mail->setFromName("Client");
        $mail->setType('html');

        try {
            $mail->send();
            Mage::getSingleton('core/session')->addSuccess(Mage::helper('ruoauth')->__('Thanks for subscriber'));
            $this->_redirect('adminhtml/system_config/edit/section/ruoauth');
        } catch (Exception $e) {
            $this->_redirect('adminhtml/system_config/edit/section/ruoauth');
        }
    }
}
