<?php

abstract class RuMage_OAuth_Model_Base
    extends Varien_Object
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
     * Redirect to the {@link cancelUrl} or simply close the popup window.
     */
    public function cancel($url = NULL)
    {
        Mage::app()->getResponse()->setRedirect(isset($url) ? $url : $this->getData('cancel_url'));
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
            Mage::helper('ruoauth')->getSession()->addError(
                Mage::helper('ruoauth')->__(curl_error($ch))
            );
            $this->cancel();
            return FALSE;
        }

        if ($headers['http_code'] != 200) {
            Mage::helper('ruoauth')->getSession()->addError(
                Mage::helper('ruoauth')->__('Invalid response http code: ' . $headers['http_code'])
            );
            $this->cancel();
            return FALSE;
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
                Mage::helper('ruoauth')->getSession()->addError(
                    Mage::helper('ruoauth')->__('Invalid response format.')
                );
                $this->cancel();
                return FALSE;
            } else if (isset($error)) {
                Mage::helper('ruoauth')->getSession()->addError(
                    Mage::helper('ruoauth')->__($error['message'] . '-' . $error['code'])
                );
                $this->cancel();
                return FALSE;
            } else {
                return $result;
            }
        } catch (Exception $e) {
            Mage::helper('ruoauth')->getSession()->addException(
                $e, $e->getMessage()
            );
            $this->cancel();
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
    protected function _fetchAttributes($answer = array())
    {
        foreach ($this->_attributesMapKeys as $param => $key) {
            if (array_key_exists($key, $answer)) {
                $this->setData($param, $answer[$key]);
            }
        }

        $this->setData('fullname', $this->getFirstname() . ' ' . $this->getLastname());
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

    /**
     * Return attributes current user.
     */
    public function getAttributes()
    {
        $this->fetchAttributes();
    }

    /**
     * Return value attribute.
     * @param $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $this->_fetchAttributes();

        if ($this->hasData($key)) {
            return $this->getData($key);
        }
    }
}