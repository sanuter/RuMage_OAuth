<?php
###lit###

class RuMage_OAuth_Model_Services_Vk
    extends RuMage_OAuth_Model_Auth2
{
    const USER_URL = 'http://vk.com/id';
    const AUTHORIZE_URL = 'https://api.vk.com/oauth/authorize';
    const ACCESS_TOKEN_URL = 'https://api.vk.com/oauth/access_token';

    const XML_PATH_CLIENT_ID = 'ruoauth/vk/application_id';
    const XML_PATH_CLIENT_SECRET = 'ruoauth/vk/application_secret';
    const XML_PATH_SCOPE = 'ruoauth/vk/scope';
    const XML_PATH_POPUP_WITDTH = 'ruoauth/vk/popup_width';
    const XML_PATH_POPUP_HEIGHT = 'ruoauth/vk/popup_height';

    protected $_client_id = '';
    protected $_client_secret = '';
    protected $_scope = 'friends';
    protected $_providerOptions = array(
        'authorize' => self::AUTHORIZE_URL ,
        'access_token' => self::ACCESS_TOKEN_URL,
    );

    protected $_uid = NULL;

    public function _construct()
    {
        $this->_client_id = Mage::getStoreConfig(self::XML_PATH_CLIENT_ID);
        $this->_client_secret = Mage::getStoreConfig(self::XML_PATH_CLIENT_SECRET);
        $this->_scope = Mage::getStoreConfig(self::XML_PATH_SCOPE);

        $this->setData(array(
                            'name' => 'vk',
                            'title' => 'VK.com',
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

        $info = (array)$this->makeSignedRequest('https://api.vk.com/method/getProfiles', array(
            'query' => array(
                'uids' => $this->_uid,
                'fields' => 'nickname, sex, bdate, city, country, timezone',
            ),
        ));

        $info = $info['response'][0];

        $this->setData('uid', $info->uid);
        $this->setData('fullname', $info->first_name . ' ' . $info->last_name);
        $this->setData('firstname', $info->first_name);
        $this->setData('lastname', $info->last_name);
        $this->setData('email', NULL);
        $this->setData('link', self::USER_URL . $info->uid);
        $this->setData('_fetchattributes', TRUE);
    }

       /**
        * Returns the url to request to get OAuth2 code.
        * @param string $redirect_uri url to redirect after user confirmation.
        * @return string url to request.
        */
    protected function getCodeUrl($redirect_uri)
    {
        $url = parent::getCodeUrl($redirect_uri);

        if (isset($_GET['js'])) {
            $url .= '&display=popup';
        }

        return $url;
    }

    /**
     * Save access token to the session.
     * @param stdClass $token access token object.
     */
    protected function saveAccessToken($token)
    {
        $this->setState('auth_token', $token->access_token);
        $this->setState('uid', $token->user_id);
        $this->setState('expires', time() + $token->expires_in - 60);
        $this->_uid = $token->user_id;
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

    /**
     * Returns the error info from json.
     * @param stdClass $json the json response.
     * @return array the error array with 2 keys: code and message. Should be NULL if no errors.
     */
    protected function fetchJsonError($json)
    {
        if (isset($json->error) && is_object($json->error)) {
            return array(
                'code' => $json->error->error_code,
                'message' => $json->error->error_msg,
            );
        } else {
            return NULL;
        }
    }
}