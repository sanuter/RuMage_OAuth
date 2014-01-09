<?php
###lit###

abstract class RuMage_OAuth_Model_OpenID
    extends RuMage_OAuth_Model_Base implements RuMage_OAuth_Interface
{

    /**
     * @var OpenId_LightOpenID the openid library instance.
     */
    private $_auth;

    /**
     * @var string the OpenID authorization url.
     */
    protected $_url;

    /**
     * @var array the OpenID required attributes.
     */
    protected $_requiredAttributes = array();


    /**
     * Initialize the component.
     */
    public function _construct()
    {
        $this->_auth = new OpenId_LightOpenID();
    }

    /**
     * Authenticate the user.
     * @return boolean whether user was successfuly authenticated.
     */
    public function authenticate()
    {
        /* @var $request Mage_Core_Controller_Request_Http */
        $request = Mage::app()->getRequest();

        if ($request->getParam('openid_mode', '')) {
            switch ($request->getParam('openid_mode'))
            {
                case 'id_res':
                    try {
                        if ($this->_auth->validate()) {
                            $this->setId($this->_auth->identity);

                            $attributes = $this->_auth->getAttributes();
                            foreach ($this->_requiredAttributes as $key => $attr) {
                                if (isset($attributes[$attr[1]])) {
                                    $this->setData($key, $attributes[$attr[1]]);
                                } else {
                                    throw new RuMage_OAuth_Exception('Unable to complete the authentication because the required data was not received.');
                                    return FALSE;
                                }
                            }

                            $this->setAuthenticated(TRUE);
                            return TRUE;
                        } else {
                            throw new RuMage_OAuth_Exception('Unable to complete the authentication because the required data was not received.');
                            return FALSE;
                        }
                    } catch (Exception $e) {
                        throw new RuMage_OAuth_Exception($e->getMessage(), $e->getCode());
                    }
                    break;

                case 'cancel':
                    $this->cancel();
                    break;

                default:
                    throw new RuMage_OAuth_Exception('Your request is invalid.');
                    break;
            }
        } else {
            $this->_auth->identity = $this->_url; //Setting identifier
            $this->_auth->required = array(); //Try to get info from openid provider

            foreach ($this->_requiredAttributes as $attribute) {
                $this->_auth->required[$attribute[0]] = $attribute[1];
            }

            $this->_auth->returnUrl = Mage::app()->getHelper('ruoauth')->getReturnUrl(); //getting return URL

            try {
                $url = $this->_auth->authUrl();
                Mage::app()->getResponse()->setRedirect($url);
            } catch (Exception $e) {
                throw new RuMage_OAuth_Exception($e->getMessage(), $e->getCode());
            }
        }

        return FALSE;
    }
}