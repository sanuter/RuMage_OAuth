<?php
###lit###

class RuMage_OAuth_Model_Services_Odnoklassniki
    extends RuMage_OAuth_Model_Auth2
{
    /**
     * Alias service.
     */
    const PROVIDER_NAME = 'odnoklassniki';

    /**
     * Authenticate link.
     */
    const AUTHORIZE_URL = 'http://www.odnoklassniki.ru/oauth/authorize';

    /**
     * Link for get token.
     */
    const ACCESS_TOKEN_URL = 'http://api.odnoklassniki.ru/oauth/token.do';

    /**
     * OAuth2 client secret key.
     * @var string
     */
    protected $_client_public = '';

    protected $_providerOptions = array(
        'authorize' => self::AUTHORIZE_URL ,
        'access_token' => self::ACCESS_TOKEN_URL,
    );

    /**
     * Keys return attributes.
     * @var array
     */
    protected $_attributesMapKeys = array(
        'uid' => 'uid',
        'firstname' => 'first_name',
        'lastname' => 'last_name',
    );

    public function _construct()
    {
        $this->_client_id = Mage::helper('ruoauth')->getClientId($this);
        $this->_client_secret = Mage::helper('ruoauth')->getClientSecret($this);
        $this->_client_public = Mage::helper('ruoauth')->getClientPublic($this);
    }

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
        $answer = $this->makeRequest('http://api.odnoklassniki.ru/fb.do', array(
                                                        'query' => array(
                                                            'method' => 'users.getCurrentUser',
                                                            'sig' => $this->_sig(),
                                                            'format' => 'JSON',
                                                            'application_key' => $this->_client_public,
                                                            'client_id' => $this->_client_id,
                                                            'access_token' => $this->_access_token,
                                                        ),
        ));

        $this->_fetchAttributes((array) $answer);
    }

    //TODO change
    protected function getTokenUrl($code)
    {
        return self::ACCESS_TOKEN_URL;
    }

    /**
     * Returns the OAuth2 access token.
     * @param stdClass $token access token object.
     */
    protected function getAccessToken($code)
    {
        $params = array(
            'client_id' => $this->_client_id,
            'client_secret' => $this->_client_secret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => Mage::helper('ruoauth')->getReturnUrl(),
        );
        $url = $this->getTokenUrl($code) .
            '?client_id=' . $this->_client_id .
            '&client_secret=' . $this->_client_secret .
            '&redirect_uri=' . Mage::helper('ruoauth')->getReturnUrl() .
            '&code=' . $code . '&grant_type=authorization_code';

        return $this->makeRequest($url, array('data' => $params));
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
                'code' => $json->error,
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