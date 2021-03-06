<?php
###lit###

abstract class RuMage_OAuth_Model_Base
    extends Varien_Object implements RuMage_OAuth_Interface
{
    /**
     * Returns service name(id).
     * @return string the service name(id).
     */
    public function getServiceName()
    {
        return $this->getName();
    }

    /**
     * Returns service title.
     * @return string the service title.
     */
    public function getServiceTitle()
    {
        return $this->getTitle();
    }

    /**
     * Returns service type (e.g. OpenID, OAuth).
     * @return string the service type (e.g. OpenID, OAuth).
     */
    public function getServiceType()
    {
        return $this->getType();
    }

    /**
     * Returns arguments for the jQuery.oauth() javascript function.
     * @return array the arguments for the jQuery.oauth() javascript function.
     */
    public function getJsArguments()
    {
        return $this->getJsArguments();
    }

    /**
     * Sets redirect url after successful authorization.
     * @param string url to redirect.
     */
    public function setRedirectUrl($url)
    {
        $this->setData('redirect_url', $url);
    }

    /**
     * Returns the redirect url after successful authorization.
     * @return string the redirect url after successful authorization.
     */
    public function getRedirectUrl()
    {
        return $this->getRedirectUrl();
    }

    /**
     * Sets redirect url after unsuccessful authorization (e.g. user canceled).
     * @param string url to redirect.
     */
    public function setCancelUrl($url)
    {
        $this->setData('cancel_url', $url);
    }

    /**
     * Returns the redirect url after unsuccessful authorization (e.g. user canceled).
     * @return string the redirect url after unsuccessful authorization (e.g. user canceled).
     */
    public function getCancelUrl()
    {
        return $this->getCancelUrl();
    }

    /**
     * Authenticate the user.
     * @return boolean whether user was successfuly authenticated.
     */
    public function authenticate()
    {
        return $this->getIsAuthenticated();
    }

    /**
     * Whether user was successfuly authenticated.
     * @return boolean whether user was successfuly authenticated.
     */
    public function getIsAuthenticated()
    {
        return $this->getAuthenticated();
    }

    /**
     * Redirect to the url. If url is NULL, {@link redirectUrl} will be used.
     * @param string $url url to redirect.
     */
    public function redirect($url = NULL)
    {
        Mage::app()->getResponse()->setRedirect(isset($url) ? $url : $this->getRedirectUrl(), TRUE);
    }

    /**
     * Redirect to the {@link cancelUrl} or simply close the popup window.
     */
    public function cancel($url = NULL)
    {
        Mage::app()->getResponse()->setRedirect(isset($url) ? $url : $this->getCancelUrl(), TRUE);
    }

    /**
     * Makes the curl request to the url.
     * @param string $url url to request.
     * @param array $options HTTP request options. Keys: query, data, referer.
     * @return string the response.
     */
    protected function makeRequest($url, $options = array(), $parseJson = TRUE)
    {
        $ch = $this->initRequest($url, $options);

        $result = curl_exec($ch);

        $headers = curl_getinfo($ch);

        if (curl_errno($ch) > 0) {
            throw new RuMage_OAuth_Exception(curl_error($ch), curl_errno($ch));
        }

        if ($headers['http_code'] != 200) {
            Mage::log(
                'Invalid response http code: ' . $headers['http_code'] . '.' . PHP_EOL .
                'URL: ' . $url . PHP_EOL .
                'Options: ' . var_export($options, TRUE) . PHP_EOL .
                'Result: ' . $result,
                Zend_Log::ERR, 'rumage.oauth.log'
            );
            throw new RuMage_OAuth_Exception(
                'Invalid response http code: ' . $headers['http_code'] . '.',
                $headers['http_code']
            );
        }

        curl_close($ch);

        if ($parseJson) {
            $result = $this->parseJson($result);
        }

        return $result;
    }

    /**
     * Initializes a new session and return a cURL handle.
     * @param string $url url to request.
     * @param array $options HTTP request options. Keys: query, data, referer.
     * @return cURL handle.
     */
    protected function initRequest($url, $options = array())
    {
        $ch = curl_init();
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // error with open_basedir or safe mode
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);

        if (isset($options['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $options['referer']);
        }

        if (isset($options['headers'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
        }

        if (isset($options['query'])) {
            $url_parts = parse_url($url);
            if (isset($url_parts['query'])) {
                $query = $url_parts['query'];
                if (strlen($query) > 0) {
                    $query .= '&';
                }

                $query .= http_build_query($options['query']);
                $url = str_replace($url_parts['query'], $query, $url);
            } else {
                $url_parts['query'] = $options['query'];
                $new_query = http_build_query($url_parts['query']);
                $url .= '?' . $new_query;
            }
        }

        if (isset($options['data'])) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['data']);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        return $ch;
    }

    /**
     * Parse response from {@link makeRequest} in json format and check OAuth errors.
     * @param string $response Json string.
     * @return object result.
     */
    protected function parseJson($response)
    {
        try {
            $result = json_decode($response);
            $error = $this->fetchJsonError($result);
            if (!isset($result)) {
                throw new RuMage_OAuth_Exception('Invalid response format.');
            } else if (isset($error)) {
                throw new RuMage_OAuth_Exception($error['message'], $error['code']);
            } else {
                return $result;
            }
        } catch (Exception $e) {
            throw new RuMage_OAuth_Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @return string a prefix for the name of the session variables storing oauth session data.
     */
    protected function getStateKeyPrefix() {
        return '__oauth_' . $this->getServiceName() . '__' ;
    }

    /**
     * Stores a variable in oauth session.
     * @param string $key variable name.
     * @param mixed $value variable value.
     * @param mixed $defaultValue default value. If $value===$defaultValue, the variable will be
     * removed from the session.
     * @see getState
     */
    protected function setState($key, $value, $defaultValue = NULL)
    {
        /* @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');

        $key = $this->getStateKeyPrefix() . $key;
        if ($value === $defaultValue) {
            $session->unsetData($key);
        } else {
            $session->setData($key, $value);
        }
    }

    /**
     * Returns a value indicating whether there is a state of the specified name.
     * @param string $key state name.
     * @return boolean whether there is a state of the specified name.
     */
    protected function hasState($key)
    {
        /* @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');

        $key = $this->getStateKeyPrefix() . $key;
        return $session->hasData($key);
    }

    /**
     * Returns the value of a variable that is stored in oauth session.
     * @param string $key variable name.
     * @param mixed $defaultValue default value.
     * @return mixed the value of the variable. If it doesn't exist in the session,
     * the provided default value will be returned.
     * @see setState
     */
    protected function getState($key, $defaultValue = NULL)
    {
        /* @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');

        $key = $this->getStateKeyPrefix() . $key;
        return $session->getData($key, $defaultValue);
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
                'code' => 500,
                'message' => 'Unknown error occurred.',
            );
        } else {
            return NULL;
        }
    }

    /**
     * Fetch attributes array.
     * @return boolean whether the attributes was successfully fetched.
     */
    protected function fetchAttributes()
    {
        return TRUE;
    }

    /**
     * Fetch attributes array.
     * This function is internally used to handle fetched state.
     */
    protected function _fetchAttributes()
    {
        $result = $this->fetchAttributes();
        if (isset($result)) {
            $this->setData($result);
        }
    }

    /**
     * Returns the user unique id.
     * @return mixed the user id.
     */
    public function getId()
    {
        $this->_fetchAttributes();
        return $this->getUid();
    }

    public function getAttributes()
    {
        $this->fetchAttributes();
    }

    public function getAttribute($key)
    {
        $this->_fetchAttributes();

        if ($this->hasData($key)) {
            return $this->getData($key);
        }
    }
}