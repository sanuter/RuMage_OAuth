<?php

class RuMage_OAuth_Model_Services_Vk extends RuMage_OAuth_Model_Service
{
    public function _construct()
    {
        $this->setServiceName('vk');
        $this->setClientIdKey('app_id');
        $this->setClientSecretKey('app_secret');
    }
}