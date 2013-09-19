<?php
###lit###

class RuMage_OAuth_Block_Notification_Window extends Mage_Adminhtml_Block_Notification_Window
{
    /**
     * Path setting active.
     */
    const XML_CONFIG_RUOAUTH_ACTIVE = 'ruoauth/active';

    /**
     * Path setting subscribe.
     */
    const XML_CONFIG_RUOAUTH_SUBSCRIBE= 'ruoauth/subscribe';

    /**
     * Initialize block window
     *
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setNoticeMessageText($this->escapeHtml(Mage::helper('ruoauth')->__('Update RuMage OAuth')));
        $this->setNoticeMessageYes($this->escapeUrl(
            Mage::helper("adminhtml")->getUrl("ruoauth_admin/adminhtml_answer/yes/")
        ));
        $this->setReadYesText($this->escapeHtml(Mage::helper('ruoauth')->__('Yes')));
        $this->setNoticeMessageNo($this->escapeUrl(
            Mage::helper("adminhtml")->getUrl("ruoauth_admin/adminhtml_answer/no/")
        ));
        $this->setReadNoText($this->escapeHtml(Mage::helper('ruoauth')->__('No')));
    }

    /**
     * Can we show notification window
     *
     * @return bool
     */
    public function canShow()
    {
        if (!Mage::getStoreConfig(self::XML_CONFIG_RUOAUTH_SUBSCRIBE) AND Mage::getStoreConfig(self::XML_CONFIG_RUOAUTH_ACTIVE)) {
                $this->_available = TRUE;
        }

        return $this->_available;
    }
}
