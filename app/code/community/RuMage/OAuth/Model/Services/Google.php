<?php
###lit###

class RuMage_OAuth_Model_Services_Google
    extends RuMage_OAuth_Model_Auth2
{
    const AUTHORIZE_URL = 'https://accounts.google.com/o/oauth2/auth';
    const ACCESS_TOKEN_URL = 'https://accounts.google.com/o/oauth2/token';

    const XML_PATH_CLIENT_ID = 'ruoauth/google/application_id';
    const XML_PATH_CLIENT_SECRET = 'ruoauth/google/application_secret';
    const XML_PATH_SCOPE = 'ruoauth/google/scope';
    const XML_PATH_POPUP_WITDTH = 'ruoauth/google/popup_width';
    const XML_PATH_POPUP_HEIGHT = 'ruoauth/google/popup_height';

    protected $_client_id = '';
    protected $_client_secret = '';
    protected $_scope = 'https://www.googleapis.com/auth/userinfo.profile';
    protected $_providerOptions = array(
        'authorize' => self::AUTHORIZE_URL ,
        'access_token' => self::ACCESS_TOKEN_URL,
    );

    protected $_uid = NULL;

    public function _construct()
    {
        $this->_client_id = Mage::getStoreConfig(self::XML_PATH_CLIENT_ID);
        $this->_client_secret = Mage::getStoreConfig(self::XML_PATH_CLIENT_SECRET);
        //$this->_scope = Mage::getStoreConfig(self::XML_PATH_SCOPE);

        $this->setData(array(
                            'name' => 'google',
                            'title' => 'Google.com',
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

        $info = (array)$this->makeSignedRequest('https://www.googleapis.com/oauth2/v1/userinfo');

        $this->setData('uid', $info['id']);
        $this->setData('fullname', $info['given_name'] . ' ' . $info['family_name']);
        $this->setData('firstname', $info['given_name']);
        $this->setData('lastname', $info['family_name']);
        $this->setData('email', NULL);

        if (!empty($info['link'])) {
            $this->setData('user_url', $info['link']);
        }

        $this->setData('_fetchattributes', TRUE);
    }

    /**
     * Returns the url to request to get OAuth2 code.
     * @param string $redirect_uri url to redirect after user confirmation.
     * @return string url to request.
     */
    protected function getCodeUrl($redirect_uri)
    {
        $this->setState('redirect_uri', $redirect_uri);
        $url = parent::getCodeUrl($redirect_uri);
        if (isset($_GET['js'])) {
            $url .= '&display=popup';
        }

        return $url;
    }

    protected function getTokenUrl($code)
    {
        return $this->_providerOptions['access_token'];
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

        return $this->makeRequest($this->getTokenUrl($code), array('data' => $params));
    }

    /**
     * Save access token to the session.
     * @param stdClass $token access token array.
     */
    protected function saveAccessToken($token)
    {
        $this->setState('auth_token', $token->access_token);
        $this->setState('expires', time() + $token->expires_in - 60);
        $this->_access_token = $token->access_token;
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