<?php
###lit##

$installer = $this;
/* @var $customer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

/*
$installer->run("

CREATE  TABLE IF NOT EXISTS {$this->getTable('rumage_oauth_service')} (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `service_type` VARCHAR(45) NULL ,
  `service_name` VARCHAR(45) NULL ,
  `service_uid` VARCHAR(128) NULL ,
  `customer_id` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `customer` (`service_name` ASC, `service_uid` ASC) )
ENGINE = InnoDB

");
*/

$installer->addAttribute('customer', 'service_name1', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Social Type',
    'visible' => FALSE,
    'required' => FALSE,
));

$installer->addAttribute('customer', 'service_uid1', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Social UID',
    'visible' => FALSE,
    'required' => FALSE,
));

$installer->endSetup();
