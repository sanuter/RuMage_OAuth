<?php

class RuMage_OAuth_Model_Services_Google
    extends RuMage_OAuth_Model_Auth2
{
    /**
     * Alias service.
     */
    const PROVIDER_NAME = 'google';

    /**
     * Link for get token.
     */
    const AUTHORIZE_URL = 'https://accounts.google.com/o/oauth2/auth';

    /**
     * Link for get token.
     */
    const ACCESS_TOKEN_URL = 'https://accounts.google.com/o/oauth2/token';

    /**
     * Data the user from service.
     * @var string
     */
    protected $_scope = 'https://www.googleapis.com/auth/userinfo.profile';

    protected $_providerOptions = array(
        'authorize' => self::AUTHORIZE_URL ,
        'access_token' => self::ACCESS_TOKEN_URL,
    );

    /**
     * Keys return attributes.
     * @var array
     */
    protected $_attributesMapKeys = array(
        'uid' => 'id',
        'fistname' => 'given_name',
        'lastname' => 'family_name',
        'email' => 'email',
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
     * Get attributes current user.
     * @return bool|void
     */
    protected function fetchAttributes()
    {
        $answer = (array) $this->makeSignedRequest('https://www.googleapis.com/oauth2/v1/userinfo');
        $this->_fetchAttributes($answer);
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
     * Makes the curl request to the url.
     * @param string $url url to request.
     * @param array $options HTTP request options. Keys: query, data, referer.
     * @param boolean $parseJson Whether to parse response in json format.
     * @return string the response.
     */
    protected function makeRequest($url, $options = array(), $parseJson = TRUE)
    {
        $options['query']['alt'] = 'json';
        return parent::makeRequest($url, $options, $parseJson);
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
                'code' => $json->error->code,
                'message' => $json->error->message,
            );
        } else {
            return NULL;
        }
    }
}