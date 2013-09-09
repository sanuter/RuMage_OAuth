<?php
###lit##

$installer = $this;
/* @var $customer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$customer = Mage::getModel('customer/customer');
$attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();

$this->addAttributeToSet('customer', $attrSetId, 'General', 'service_name');
$this->addAttributeToSet('customer', $attrSetId, 'General', 'service_uid');

Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'service_name')
    ->setData('used_in_forms', array('adminhtml_customer'))
    ->save();
Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'service_uid')
    ->setData('used_in_forms', array('adminhtml_customer'))
    ->save();

$this->updateAttribute('customer', 'service_name', 'frontend_label', 'Social Type');
$this->updateAttribute('customer', 'service_uid', 'frontend_label', 'Social UID');

$installer->endSetup();
