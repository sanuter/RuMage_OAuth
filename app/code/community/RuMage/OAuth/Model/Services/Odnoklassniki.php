<?php
###lit###

class RuMage_OAuth_Model_Services_Odnoklassniki
    extends RuMage_OAuth_Model_Auth2
{
    const USER_URL = 'http://api.odnoklassniki.ru/fb.do';
    const AUTHORIZE_URL = 'http://www.odnoklassniki.ru/oauth/authorize';
    const ACCESS_TOKEN_URL = 'http://api.odnoklassniki.ru/oauth/token.do';

    const XML_PATH_CLIENT_ID = 'ruoauth/odnoklassniki/application_id';
    const XML_PATH_CLIENT_SECRET = 'ruoauth/odnoklassniki/application_secret';
    const XML_PATH_CLIENT_PUBLIC = 'ruoauth/odnoklassniki/application_public';
    const XML_PATH_POPUP_WITDTH = 'ruoauth/odnoklassniki/popup_width';
    const XML_PATH_POPUP_HEIGHT = 'ruoauth/odnoklassniki/popup_height';

    protected $_client_id = '';
    protected $_client_secret = '';
    protected $_client_public = '';
    protected $_scope = '';
    protected $_providerOptions = array(
        'authorize' => self::AUTHORIZE_URL ,
        'access_token' => self::ACCESS_TOKEN_URL,
    );

    public function _construct()
    {
        $this->_client_id = Mage::getStoreConfig(self::XML_PATH_CLIENT_ID);
        $this->_client_secret = Mage::getStoreConfig(self::XML_PATH_CLIENT_SECRET);
        $this->_client_public = Mage::getStoreConfig(self::XML_PATH_CLIENT_PUBLIC);

        $this->setData(array(
                            'name' => 'odnoklassniki',
                            'title' => 'Odnoklassniki.ru',
                            'type' => 'OAuth2',
                            'width' => Mage::getStoreConfig(self::XML_PATH_POPUP_WITDTH),
                            'height' => Mage::getStoreConfig(self::XML_PATH_POPUP_HEIGHT),
                       ));
    }

    protected function fetchAttributes()
    {
        if ($this->getData('_fetchattributes')) {
            return TRUE;
        }

        $this->restoreAccessToken();

        $info = $this->makeRequest(self::USER_URL, array(
                                                        'query' => array(
                                                            'method' => 'users.getCurrentUser',
                                                            'sig' => $this->_sig(),
                                                            'format' => 'JSON',
                                                            'application_key' => $this->_client_public,
                                                            'client_id' => $this->_client_id,
                                                            'access_token' => $this->_access_token,
                                                        ),
        ));

        $this->setData('uid', $info->uid);
        $this->setData('fullname', $info->first_name . ' ' . $info->last_name);
        $this->setData('firstname', $info->first_name);
        $this->setData('lastname', $info->last_name);
        $this->setData('email', NULL);
        $this->setData('_fetchattributes', TRUE);
    }

    protected function getTokenUrl($code)
    {
        return self::ACCESS_TOKEN_URL;
    }

    protected function getAccessToken($code)
    {
        $params = array(
            'client_id' => $this->_client_id,
            'client_secret' => $this->_client_secret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->getState('redirect_uri'),
        );
        $url = $this->getTokenUrl($code) .
            '?client_id=' . $this->_client_id .
            '&client_secret=' . $this->_client_secret .
            '&redirect_uri=' . urlencode($this->getState('redirect_uri')) .
            '&code=' . $code . '&grant_type=authorization_code';
        $result = $this->makeRequest($url, array('data' => $params));
        return $result->access_token;
    }

    protected function getCodeUrl($redirect_uri)
    {
        $this->setState('redirect_uri', $redirect_uri);
        $url = parent::getCodeUrl($redirect_uri);

        if (isset($_GET['js'])) {
            $url .= '&display=popup';
        }

        return $url;
    }

    /**
     * Returns the error info from json.
     * @param stdClass $json the json response.
     * @return array the error array with 2 keys: code and message. Should be NULL if no errors.
     */
    protected function fetchJsonError($json)
    {
        if (isset($json->error)) {
            return array(
                'code' => $json->error_code,
                'message' => $json->error_description,
            );
        } else {
            return NULL;
        }
    }

    protected function _sig()
    {
        return strtolower(
            md5(
                'application_key=' . $this->_client_public .
                'client_id=' . $this->_client_id .
                'format=JSONmethod=users.getCurrentUser' .
                md5($this->_access_token . $this->_client_secret)
            )
        );
    }
}