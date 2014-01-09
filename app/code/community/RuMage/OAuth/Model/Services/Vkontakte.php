<?php
/**
 * @package Magento OAuth.
 * @author: A.A.Treitjak
 * @copyright: 2012 - 2014 BelVG.com
 */


class RuMage_OAuth_Model_Services_Vkontakte extends RuMage_OAuth_Model_Service
{
    public function _construct()
    {
        $this->setServiceName('vkontakte');
        $this->setClientIdKey('app_id');
        $this->setClientSecretKey('app_secret');
    }
}