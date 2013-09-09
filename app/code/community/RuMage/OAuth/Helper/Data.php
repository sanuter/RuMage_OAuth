<?php
###lit###

class RuMage_OAuth_Helper_Data extends Mage_Payment_Helper_Data
{
    public function getService(RuMage_OAuth_ProviderController $controller)
    {
        $_service = (string) $controller->getRequest()->getParam('service');

        if (array_key_exists($_service, $this->getServices())) {
            return $this->_setProvider($_service);
        }

        return FALSE;
    }

    public function getServices()
    {
        $services = array();
        $config = Mage::getStoreConfig('ruoauth');

        if (is_array($config)) {
            foreach ($config as $service=>$serviceConfig) {
                if (is_array($serviceConfig)) {
                    if ($config[$service]['active']) {
                        $services[$service] = $serviceConfig;
                    }
                }
            }
        }

        return $services;
    }

    public function setProvider($type)
    {
        return $this->_setProvider($type);
    }

    public function getProvider($type = '')
    {
        if (empty($type)) {
            $type = $this->_getSession()->getData('service');
        }

        if (empty($type)) {
            return NULL;
        }

        $_provider = Mage::getModel('ruoauth/services_' . $type);
        $_provider->setRedirectUrl($this->_url_addition());
        $_provider->setCancelUrl($this->_url_providet());

        return $_provider;
    }

    public function requiredInfo($provider)
    {
        $provider->getAttributes();
        return $this->_provider->getFirstName();
    }

    public function getServiceEmail($provider)
    {
        $provider->getAttributes();

        if ($provider->getEmail()) {
            return $provider->getEmail();
        }

        return $provider->getId() . '@' . strtolower($provider->getServiceName());
    }

    public function getReturnUrl()
    {
        $request = Mage::app()->getRequest();
        return Mage::getUrl('ruoauth/provider', array('service' => $request->getParam('service')));
    }

    public function checkEmail($customer)
    {
        $services = $this->getServices();
        foreach ($services as $alias => $config) {
            if (substr_count($customer->getEmail(), $alias)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    protected function _setProvider($type)
    {
        $provider = Mage::getModel('ruoauth/services_' . $type);
        $provider->setRedirectUrl($this->_url_addition());
        $provider->setCancelUrl($this->_url_providet());

        $this->_getSession()->setData('service', $type);

        return $provider;
    }

    protected function _url_addition()
    {
        return Mage::getUrl('ruoauth/provider');
    }

    protected function _url_providet()
    {
        return Mage::getUrl('ruoauth/provider/addition');
    }
}
