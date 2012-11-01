<?php

class RuMage_OAuth_Model_Services_Facebook
    extends RuMage_OAuth_Model_Auth2
{
    /**
     * Alias service.
     */
    const PROVIDER_NAME = 'facebook';

    /**
     * Link for get token.
     */
    const AUTHORIZE_URL = 'https://www.facebook.com/dialog/oauth';

    /**
     * Link for get token.
     */
    const ACCESS_TOKEN_URL = 'https://graph.facebook.com/oauth/access_token';

    /**
     * Addition info from service.
     * @var string
     */
    protected $_scope = 'email';

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
        'firstname' => 'first_name',
        'lastname' => 'last_name',
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
        $answer = $this->makeSignedRequest('https://graph.facebook.com/me',
            array(
                'query' => array(
                    'scope' => $this->_scope
                )
            )
        );

        $this->_fetchAttributes((array) $answer);
    }

    /**
     * Returns the url to request to get OAuth2 code.
     * @param string $redirect_uri url to redirect after user confirmation.
     * @return string url to request.
     */
    protected function getCodeUrl($redirect_uri)
    {
        if (strpos($redirect_uri, '?') !== FALSE) {
            $url = explode('?', $redirect_uri);
            $url[1] = preg_replace('#[/]#', '%2F', $url[1]);
            $redirect_uri = implode('?', $url);
        }

        $url = parent::getCodeUrl($redirect_uri);

        return $url;
    }

    protected function getTokenUrl($code)
    {
        return parent::getTokenUrl($code) . '&redirect_uri=' . urlencode(Mage::helper('ruoauth')->getReturnUrl());
    }

    /**
     * Returns the OAuth2 access token.
     * @param stdClass $token access token object.
     */
    protected function getAccessToken($code)
    {
        $response = $this->makeRequest($this->getTokenUrl($code), array(), FALSE);
        parse_str($response, $result);
        return $result;
    }

    /**
     * Save access token to the session.
     * @param array $token access token array.
     */
    protected function saveAccessToken($token)
    {
        if (!isset($token['access_token'])) {
            Mage::helper('ruoauth')->getSession()->addError(
                Mage::helper('ruoauth')->__('Invalide token')
            );
            $this->cancel();
            return FALSE;
        }

        $this->_access_token = $token['access_token'];
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