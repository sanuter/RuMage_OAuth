<?php
###lit###

class RuMage_OAuth_Model_Services_Mailru
    extends RuMage_OAuth_Model_Auth2
{
    const AUTHORIZE_URL = 'https://connect.mail.ru/oauth/authorize';
    const ACCESS_TOKEN_URL = 'https://connect.mail.ru/oauth/token';

    const XML_PATH_CLIENT_ID = 'ruoauth/mailru/application_id';
    const XML_PATH_CLIENT_SECRET = 'ruoauth/mailru/application_secret';
    const XML_PATH_SCOPE = 'ruoauth/mailru/scope';
    const XML_PATH_POPUP_WITDTH = 'ruoauth/mailru/popup_width';
    const XML_PATH_POPUP_HEIGHT = 'ruoauth/mailru/popup_height';

    protected $_client_id = '';
    protected $_client_secret = '';
    protected $_scope = '';
    protected $_providerOptions = array(
        'authorize' => self::AUTHORIZE_URL,
        'access_token' => self::ACCESS_TOKEN_URL,
    );

    protected $_uid = NULL;

    public function _construct()
    {
        $this->_client_id = Mage::getStoreConfig(self::XML_PATH_CLIENT_ID);
        $this->_client_secret = Mage::getStoreConfig(self::XML_PATH_CLIENT_SECRET);
        $this->_scope = Mage::getStoreConfig(self::XML_PATH_SCOPE);

        $this->setData(array(
                            'name' => 'mailru',
                            'title' => 'Mail.ru',
                            'type' => 'OAuth2',
                            'width' => Mage::getStoreConfig(self::XML_PATH_POPUP_WITDTH),
                            'height' => Mage::getStoreConfig(self::XML_PATH_POPUP_HEIGHT),
                       ));
    }

    protected function fetchAttributes()
    {
        $info = (array) $this->makeSignedRequest('http://www.appsmail.ru/platform/api',
            array(
                'query' => array(
                    'uids' => $this->_uid,
                    'method' => 'users.getInfo',
                    'app_id' => $this->_client_id,
                    ),
                )
        );

        $this->setData('uid', $info[0]->uid);
        $this->setData('fullname', $info[0]->first_name . ' ' . $info[0]->last_name);
        $this->setData('firstname', $info[0]->first_name);
        $this->setData('lastname', $info[0]->last_name);
        $this->setData('email', NULL);
        $this->setData('link', $info[0]->link);
    }

    protected function getCodeUrl($redirect_uri)
    {
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
            'redirect_uri' => Mage::app()->getHelper('ruoauth')->getReturnUrl(),
        );
        return $this->makeRequest($this->getTokenUrl($code), array('data' => $params));
    }

    /**
     * Save access token to the session.
     * @param stdClass $token access token object.
     */
    protected function saveAccessToken($token)
    {
        $this->setState('auth_token', $token->access_token);
        $this->setState('uid', $token->x_mailru_vid);
        $this->setState('expires', time() + $token->expires_in - 60);
        $this->_uid = $token->x_mailru_vid;
        $this->_access_token = $token->access_token;
    }

    /**
     * Restore access token from the session.
     * @return boolean whether the access token was successfuly restored.
     */
    protected function restoreAccessToken()
    {
        if ($this->hasState('uid') && parent::restoreAccessToken()) {
            $this->_uid = $this->getState('uid');
            return TRUE;
        } else {
            $this->_uid = NULL;
            return FALSE;
        }
    }

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