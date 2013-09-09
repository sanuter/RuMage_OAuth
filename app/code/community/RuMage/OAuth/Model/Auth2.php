<?php
###lit###

abstract class RuMage_OAuth_Model_Auth2
    extends RuMage_OAuth_Model_Base implements RuMage_OAuth_Interface
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
     * Authenticate the user.
     * @return boolean whether user was successfuly authenticated.
     */
    public function authenticate()
    {
        /* @var $request Mage_Core_Controller_Request_Http */
        $request = Mage::app()->getRequest();

        // user denied error
        if ($request->getParam('error', '') && $request->getParam('error', '') == 'access_denied') {
                $this->cancel();
                return FALSE;
        }

        // Get the access_token and save them to the session.
        if ($request->getParam('code', '')) {
            $code = $request->getParam('code');
            $token = $this->getAccessToken($code);
            if (isset($token)) {
                $this->saveAccessToken($token);
                $this->setAuthenticated(TRUE);
            }
        } else if (!$this->restoreAccessToken()) {
            // Use the URL of the current page as the callback URL.
            if ($request->getParam('redirect_uri', '')) {
                $redirect_uri = $request->getParam('redirect_uri');
            } else {
                $redirect_uri = Mage::app()->getHelper('ruoauth')->getReturnUrl(); //getting return URL
            }

            $url = $this->getCodeUrl($redirect_uri);

            Mage::app()->getResponse()->setRedirect($url);
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
        return $this->_providerOptions['authorize'] .
            '?client_id=' . $this->_client_id .
            '&redirect_uri=' . urlencode($redirect_uri) .
            '&scope=' . $this->_scope .
            '&response_type=code';
    }

    /**
     * Returns the url to request to get OAuth2 access token.
     * @return string url to request.
     */
    protected function getTokenUrl($code)
    {
        return $this->_providerOptions['access_token'] .
            '?client_id=' . $this->_client_id .
            '&client_secret=' . $this->_client_secret .
            '&code=' . $code;
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
     * Save access token to the session.
     * @param string $token access token.
     */
    protected function saveAccessToken($token)
    {
        $this->setState('auth_token', $token);
        $this->setState('expires', time() + 3600);
        $this->_access_token = $token;
    }

    /**
     * Restore access token from the session.
     * @return boolean whether the access token was successfuly restored.
     */
    protected function restoreAccessToken()
    {
        if ($this->hasState('auth_token') && $this->getState('expires', 0) > time()) {
            $this->_access_token = $this->getState('auth_token');
            $this->setAuthenticated(TRUE);
            return TRUE;
        } else {
            $this->_access_token = NULL;
            $this->setAuthenticated(FALSE);
            return FALSE;
        }
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
            throw new RuMage_OAuth_Exception(
                401,
                'Unable to complete the request because the user was not authenticated.'
            );
        }

        $options['query']['access_token'] = $this->_access_token;
        $result = $this->makeRequest($url, $options);
        return $result;

    }
}