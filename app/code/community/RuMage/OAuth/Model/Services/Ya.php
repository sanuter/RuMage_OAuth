<?php
###lit###

class RuMage_OAuth_Model_Services_Ya
        extends RuMage_OAuth_Model_OpenID
{
    const XML_PATH_POPUP_WITDTH = 'ruoauth/facebook/popup_width';
    const XML_PATH_POPUP_HEIGHT = 'ruoauth/facebook/popup_height';

    protected $_url = 'http://openid.yandex.ru/';

    public function _construct()
    {
        parent::_construct();

        $this->setData(array(
                            'name' => 'ya',
                            'title' => 'ya.ru',
                            'type' => 'OpenID',
                            'width' => Mage::getStoreConfig(self::XML_PATH_POPUP_WITDTH),
                            'height' => Mage::getStoreConfig(self::XML_PATH_POPUP_HEIGHT),
                       ));
    }

    protected $_requiredAttributes = array(
        'name' => array('fullname', 'namePerson'),
        'username' => array('nickname', 'namePerson/friendly'),
        'email' => array('email', 'contact/email'),
    );

    protected function fetchAttributes()
    {
        //TODO change place
        $this->setData('uid', $this->_genid());
        $this->setData('fullname', $this->getName());
        $this->setData('firstname', $this->getName());
        $this->setData('lastname', $this->getName());
        $this->setData('user_url', $this->getData('id'));
        $this->setData('_fetchattributes', TRUE);
    }

    protected function _genid()
    {
        return crc32($this->getData('id'));
    }
}