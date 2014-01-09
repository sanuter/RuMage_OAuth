<?php
###lit###

class RuMage_OAuth_Model_Services_Facebook
    extends RuMage_OAuth_Model_Auth2
{
    const AUTHORIZE_URL = 'https://www.facebook.com/dialog/oauth';
    const ACCESS_TOKEN_URL = 'https://graph.facebook.com/oauth/access_token';

    const XML_PATH_CLIENT_ID = 'ruoauth/facebook/application_id';
    const XML_PATH_CLIENT_SECRET = 'ruoauth/facebook/application_secret';
    const XML_PATH_SCOPE = 'ruoauth/facebook/scope';
    const XML_PATH_POPUP_WITDTH = 'ruoauth/facebook/popup_width';
    const XML_PATH_POPUP_HEIGHT = 'ruoauth/facebook/popup_height';

    protected $_client_id = '';
    protected $_client_secret = '';
    protected $_scope = 'email';
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
                            'name' => 'facebook',
                            'title' => 'Facebook.com',
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

        $info = (object) $this->makeSignedRequest('https://graph.facebook.com/me',
            array(
                'query' => array(
                    'scope' => $this->_scope
                )
            )
        );

        $this->setData('uid', $info->id);
        $this->setData('fullname', $info->name);
        $full_name = explode(' ', $info->name);
        $this->setData('firstname', $full_name[0]);
        $this->setData('lastname', $full_name[1]);
        $this->setData('email', $info->email);
        $this->setData('link', $info->link);
        $this->setData('_fetchattributes', TRUE);

        unset($full_name, $info);
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

        $this->setState('redirect_uri', $redirect_uri);

        $url = parent::getCodeUrl($redirect_uri);

        if (isset($_GET['js'])) {
            $url .= '&display=popup';
        }

        return $url;
    }

    protected function getTokenUrl($code)
    {
        return parent::getTokenUrl($code) . '&redirect_uri=' . urlencode($this->getState('redirect_uri'));
    }

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
        $this->setState('auth_token', $token['access_token']);
        $this->setState('expires', isset($token['expires']) ? time() + (int)$token['expires'] - 60 : 0);
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