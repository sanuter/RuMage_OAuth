<?php

abstract class RuMage_OAuth_Model_OpenID
    extends RuMage_OAuth_Model_Base
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
            if ($request->getParam('openid_mode') == 'id_res') {
                try {
                    if ($this->_auth->validate()) {
                        $this->setId($this->_auth->identity);

                        $attributes = $this->_auth->getAttributes();
                        foreach ($this->_requiredAttributes as $key => $attr) {
                            if (isset($attributes[$attr[1]])) {
                                $this->setData($key, $attributes[$attr[1]]);
                            } else {
                                Mage::helper('ruoauth')
                                ->getSession()
                                ->addError('Unable to complete the request because the user was not authenticated.');
                                $this->cancel();
                                return FALSE;
                            }
                        }

                        return TRUE;
                    } else {
                        Mage::helper('ruoauth')
                        ->getSession()
                        ->addError('Unable to complete the request because the user was not authenticated.');
                        $this->cancel();
                        return FALSE;;
                    }
                } catch (Exception $e) {
                    Mage::helper('ruoauth')
                        ->getSession()
                        ->addException($e, $e->getMessage());
                    $this->cancel();
                    return FALSE;
                }
            } else {
                Mage::helper('ruoauth')
                ->getSession()
                ->addError('Unable to complete the request because the user was not authenticated.');
                $this->cancel();
                return FALSE;
            }
        } else {
            $this->_auth->identity = $this->_url; //Setting identifier
            $this->_auth->required = array(); //Try to get info from openid provider

            foreach ($this->_requiredAttributes as $attribute) {
                $this->_auth->required[$attribute[0]] = $attribute[1];
            }

            $this->_auth->returnUrl = Mage::helper('ruoauth')->getReturnUrl(); //getting return URL

            try {
                $url = $this->_auth->authUrl();
                Mage::app()->getResponse()->setRedirect($url);
            } catch (Exception $e) {
                Mage::helper('ruoauth')->getSession()->addException(
                    $e, $e->getMessage()
                );
                $this->cancel();
            }
        }

        return FALSE;
    }
}