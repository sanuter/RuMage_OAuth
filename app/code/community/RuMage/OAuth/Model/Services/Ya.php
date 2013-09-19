<?php
###lit###

class RuMage_OAuth_Model_Services_Ya
        extends RuMage_OAuth_Model_OpenID
{
    /**
     * Alias service.
     */
    const PROVIDER_NAME = 'ya';

    /**
     * Authenticate link.
     * @var string
     */
    protected $_url = 'http://openid.yandex.ru/';

    protected $_requiredAttributes = array(
        'name' => array('fullname', 'namePerson'),
        'username' => array('nickname', 'namePerson/friendly'),
        'email' => array('email', 'contact/email'),
    );

    /**
     * Keys return attributes.
     * @var array
     */
    protected $_attributesMapKeys = array(
        'uid' => 'id',
        'firstname' => 'name',
        'lastname' => 'name',
        'email' => 'email',
    );

    /**
     * Return alias service.
     * @return string
     */
    public function getServiceName()
    {
        return self::PROVIDER_NAME;
    }

    /**
     * Get attributes current user.
     * @return bool|void
     */
    protected function fetchAttributes()
    {
        $this->_fetchAttributes($this->getData());
    }

    protected function _genid()
    {
        return crc32($this->getData('id'));
    }
}