<?php

class RuMage_OAuth_Model_Services_Google extends RuMage_OAuth_Model_Service
{
    public function _construct()
    {
        $this->setServiceName('google');
        $this->setClientIdKey('client_id');
        $this->setClientSecretKey('client_secret');
    }
}