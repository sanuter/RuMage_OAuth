<?php
###lit###

abstract class RuMage_OAuth_Model_Auth2
    extends RuMage_OAuth_Model_Base
{
    /**
     * @var string OAuth2 client id.
     */
    protected $_client_id;

    /**
     * @var string OAuth2 client secret key.
     */
    protected $_client_secret;

    /**
     * @var string OAuth2 scopes.
     */
    protected $_scope = '';

    /**
     * @var array Provider options. Must contain the keys: authorize, access_token.
     */
    protected $_providerOptions = array(
        'authorize' => '',
        'access_token' => '',
    );

    /**
     * @var string current OAuth2 access token.
     */
    protected $_access_token = '';

    /**
     * @var string ID current social user.
     */
    protected $_uid = NULL;

    /**
     * Set type provider.
     */
    public function _construct()
    {
        $this->setType('OAuth2');
    }

    /**
     * Authenticate the user.
     * @return boolean whether user was successfuly authenticated.
     */
    public function authenticate()
    {
        /* @var $request Mage_Core_Controller_Request_Http */
        $request = Mage::app()->getRequest();

        //TODO fix this (Odnklassniki)
        if ($request->getParam('service', 'odnoklassniki') && isset($_GET['error'])) {
            Mage::helper('ruoauth')->getSession()->addError(
                Mage::helper('ruoauth')->__($_GET['error'])
            );
            $this->cancel();
            return FALSE;
        }

        // user denied error
        if ($request->getParam('error', '') && $request->getParam('error', '') == 'access_denied') {
                Mage::helper('ruoauth')->getSession()->addError(
                    Mage::helper('ruoauth')->__($request->getParam('error'))
                );
                $this->cancel();
                return FALSE;
        }

        // Get the access_token and save them to the session.
        if ($request->getParam('code', '')) {
            $code = $request->getParam('code');
            $token = $this->getAccessToken($code);
            $this->saveAccessToken($token);
            $this->setAuthenticated(TRUE);
        } else {
            // Use the URL of the current page as the callback URL.
            if ($request->getParam('redirect_uri', '')) {
                $redirect_uri = $request->getParam('redirect_uri');
            } else {
                $redirect_uri = Mage::helper('ruoauth')->getReturnUrl(); //getting return URL
            }

            Mage::app()->getResponse()->setRedirect($this->getCodeUrl($redirect_uri));
        }

        return $this->getIsAuthenticated();
    }

    /**
     * Returns the url to request to get OAuth2 code.
     * @param string $redirect_uri url to redirect after user confirmation.
     * @return string url to request.
     */
    protected function getCodeUrl($redirect_uri)
    {
        $params = array(
           'client_id' => $this->_client_id,
           'redirect_uri' => $redirect_uri,
           'scope' => $this->_scope,
           'response_type' => 'code',
        );

        return $this->_providerOptions['authorize'] . '?' . http_build_query($params);
    }

    /**
     * Returns the url to request to get OAuth2 access token.
     * @return string url to request.
     */
    protected function getTokenUrl($code)
    {
        $params = array(
            'client_id' => $this->_client_id,
            'client_secret' => $this->_client_secret,
            'code' => $code,
        );

        return $this->_providerOptions['access_token'] . '?' . http_build_query($params);
    }

    /**
     * Returns the OAuth2 access token.
     * @param string $code the OAuth2 code. See {@link getCodeUrl}.
     * @return string the token.
     */
    protected function getAccessToken($code)
    {
        return $this->makeRequest($this->getTokenUrl($code));
    }

    /**
     * Save access token.
     * @param string $token access token.
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
    public function makeSignedRequest($url, $options = array())
    {
        if (!$this->getIsAuthenticated()) {
            Mage::helper('ruoauth')->getSession()
                ->addError('Unable to complete the request because the user was not authenticated.');
        }

        $options['query']['access_token'] = $this->_access_token;
        $result = $this->makeRequest($url, $options);
        return $result;
    }
}