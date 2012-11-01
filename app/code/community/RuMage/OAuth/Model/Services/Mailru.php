<?php

class RuMage_OAuth_Model_Services_Mailru
    extends RuMage_OAuth_Model_Auth2
{
    /**
     * Alias service.
     */
    const PROVIDER_NAME = 'mailru';

    /**
     * Authenticate link.
     */
    const AUTHORIZE_URL = 'https://connect.mail.ru/oauth/authorize';

    /**
     * Link for get token.
     */
    const ACCESS_TOKEN_URL = 'https://connect.mail.ru/oauth/token';

    protected $_providerOptions = array(
        'authorize' => self::AUTHORIZE_URL,
        'access_token' => self::ACCESS_TOKEN_URL,
    );

    public function _construct()
    {
        $this->_client_id = Mage::helper('ruoauth')->getClientId($this);
        $this->_client_secret = Mage::helper('ruoauth')->getClientSecret($this);
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
     * Keys return attributes.
     * @var array
     */
    protected $_attributesMapKeys = array(
        'uid' => 'uid',
        'firstname' => 'first_name',
        'lastname' => 'last_name',
    );

    /**
     * Get attributes current user.
     * @return bool|void
     */
    protected function fetchAttributes()
    {
        $answer = (array) $this->makeSignedRequest('http://www.appsmail.ru/platform/api',
            array(
                'query' => array(
                    'uids' => $this->_uid,
                    'method' => 'users.getInfo',
                    'app_id' => $this->_client_id,
                    ),
                )
        );

        if (!isset($answer[0])) {
            Mage::helper('ruoauth')->getSession()->addError(
                Mage::helper('ruoauth')->__('Invalide data')
            );
            $this->cancel();
            return FALSE;
        }

        $this->_fetchAttributes((array) $answer[0]);
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
        return $this->makeRequest($this->getTokenUrl($code), array('data' => $params));
    }

    /**
     * Save access token.
     * @param stdClass $token access token object.
     */
    protected function saveAccessToken($token)
    {
        if (!isset($token->access_token)) {
            Mage::helper('ruoauth')->getSession()->addError(
                Mage::helper('ruoauth')->__('Invalide token')
            );
            $this->cancel();
            return FALSE;
        }

        $this->_uid = $token->x_mailru_vid;
        $this->_access_token = $token->access_token;
    }

    /**
     * Returns the protected resource.
     * @param string $url url to request.
     * @param array $options HTTP request options. Keys: query, data, referer.
     * @param boolean $parseJson Whether to parse response in json format.
     * @return string the response.
     * @see makeRequest
     */
    public function makeSignedRequest($url, $options = array(), $parseJson = TRUE)
    {
        if (!$this->getIsAuthenticated()) {
            throw new RuMage_OAuth_Exception('Unable to complete the authentication because the required data was not received.');
        }

        $options['query']['secure'] = 1;
        $options['query']['session_key'] = $this->_access_token;
        $_params = '';
        ksort($options['query']);
        foreach ($options['query'] as $k => $v) {
            $_params .= $k . '=' . $v;
        }

        $options['query']['sig'] = md5($_params . $this->_client_secret);

        $result = $this->makeRequest($url, $options);
        return $result;
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
}