<?php
###lit###

class RuMage_OAuth_Model_Services_Vk
    extends RuMage_OAuth_Model_Auth2
{
    /**
     * Alias service.
     */
    const PROVIDER_NAME = 'vk';

    /**
     * Authenticate link.
     */
    const AUTHORIZE_URL = 'https://api.vk.com/oauth/authorize';

    /**
     * Link for get token.
     */
    const ACCESS_TOKEN_URL = 'https://api.vk.com/oauth/access_token';

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
        $answer = (array) $this->makeSignedRequest('https://api.vk.com/method/getProfiles', array(
            'query' => array(
                'uids' => $this->_uid,
            ),
        ));

        if (!isset($answer['response'][0])) {
            Mage::helper('ruoauth')->getSession()->addError(
                Mage::helper('ruoauth')->__('Invalide data')
            );
            $this->cancel();
            return FALSE;
        }

        $this->_fetchAttributes((array) $answer['response'][0]);
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

        $this->_uid = $token->user_id;
        $this->_access_token = $token->access_token;
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