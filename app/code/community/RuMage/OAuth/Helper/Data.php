<?php

class RuMage_OAuth_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Return all services.
     * @return array
     */
    public function getServices()
    {
        $services = array();
        $config = Mage::getStoreConfig('ruoauth');

        if (is_array($config) AND $config['active']) {
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

    /**
     * Get email or generate email for customer.
     * @param $provider
     * @return string
     */
    public function getServiceEmail($provider)
    {
        $provider->getAttributes();

        if ($provider->getEmail()) {
            return $provider->getEmail();
        }

        //TODO need this
        return $provider->getId() . '@' . strtolower($provider->getServiceName());
    }

    /**
     * Generate redirect link for current service.
     * @return string
     */
    public function getCallbackUrl()
    {
        $request = Mage::app()->getRequest();
        return Mage::getUrl('ruoauth/provider/callback', array('service' => $request->getParam('service')));
    }

    /**
     * Generate path for OAuth request.
     * @return string
     */
    public function getPathSite()
    {
        $path = Mage::app()->getStore()->getDefaultBasePath();
        return $path . 'ruoauth/provider/index/service/';
    }

    /**
     * Generate cancel link for current service.
     * @return string
     */
    public function getCancelUrl()
    {
        $request = Mage::app()->getRequest();
        return Mage::getUrl('ruoauth/provider/cancel', array('service' => $request->getParam('service')));
    }

    /**
     * Check valid email.
     * @param $customer
     * @return bool
     */
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
     * Return width for popup.
     * @param $type
     * @return mixed
     */
    public function getWidth($type)
    {
        $config = Mage::getStoreConfig('ruoauth');
        return $config[$type]['popup_width'];
    }

    /**
     * Return height for popup.
     * @param $type
     * @return mixed
     */
    public function getHeight($type)
    {
        $config = Mage::getStoreConfig('ruoauth');
        return $config[$type]['popup_height'];
    }

    /**
     * Current session.
     * @return Mage_Customer_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('customer/session');
    }
}
