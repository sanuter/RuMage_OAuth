<?php
/**
 * @package Magento OAuth.
 * @author: A.A.Treitjak
 * @copyright: 2012 - 2014 BelVG.com
 */


class RuMage_OAuth_Helper_Services_Facebook extends RuMage_OAuth_Helper_Service
{
    public function getConfigKey()
    {
        return array(
            'application_id' => 'app_id',
            'application_secret' => 'app_secret'
        );
    }
}