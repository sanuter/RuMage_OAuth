<?php
###lit###

class RuMage_OAuth_Block_Notification_Window extends Mage_Adminhtml_Block_Notification_Window
{
    const XML_CONFIG_RUOAUTH = 'ruoauth/subscribe';

    /**
     * Initialize block window
     *
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setHeaderText($this->escapeHtml($this->__('Incoming Message')));
        $this->setCloseText($this->escapeHtml($this->__('close')));
        $this->setNoticeText($this->escapeHtml($this->__('NOTICE')));
        $this->setMinorText($this->escapeHtml($this->__('MINOR')));
        $this->setMajorText($this->escapeHtml($this->__('MAJOR')));
        $this->setCriticalText($this->escapeHtml($this->__('CRITICAL')));


        $this->setNoticeMessageText($this->escapeHtml(Mage::helper('ruoauth')->__('Update RuMage OAuth')));
        $this->setNoticeMessageYes($this->escapeUrl(
            Mage::helper("adminhtml")->getUrl("ruoauth_admin/adminhtml_answer/yes/")
        ));
        $this->setReadYesText($this->escapeHtml(Mage::helper('ruoauth')->__('Yes')));
        $this->setNoticeMessageNo($this->escapeUrl(
            Mage::helper("adminhtml")->getUrl("ruoauth_admin/adminhtml_answer/no/")
        ));
        $this->setReadNoText($this->escapeHtml(Mage::helper('ruoauth')->__('No')));

        switch ($this->getLastNotice()->getSeverity()) {
            case Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE:
                $severity = 'SEVERITY_NOTICE';
                break;

            case Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR:
                $severity = 'SEVERITY_MINOR';
                break;

            case Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR:
                $severity = 'SEVERITY_MAJOR';
                break;

            case Mage_AdminNotification_Model_Inbox::SEVERITY_CRITICAL:
                $severity = 'SEVERITY_CRITICAL';
                break;

            default:
                break;
        }

        $this->setNoticeSeverity($severity);
    }

    /**
     * Can we show notification window
     *
     * @return bool
     */
    public function canShow()
    {
        if (!Mage::getStoreConfig(self::XML_CONFIG_RUOAUTH)) {
                $this->_available = TRUE;
        }

        return $this->_available;
    }
}
