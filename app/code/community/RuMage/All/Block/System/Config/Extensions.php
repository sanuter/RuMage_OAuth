<?php

class RuMage_All_Block_System_Config_Extensions
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

    /**
     * Object of form field
     *
     * @var Mage_Adminhtml_Block_System_Config_Form_Field
     */
    protected $_fieldRenderer;

    /**
     * Render html "Info fieldset" in menu: System -> Configuration -> RuMage -> Info -> Extensions
     *
     * @param Varien_Data_Form_Element_Abstract
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html      = '';
        $modules   = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        sort($modules);

        foreach ($modules as $moduleName) {
            if (!strstr($moduleName, 'RuMage_')) {
                continue;
            }

            $html .= $this->getFieldHtml($element, $moduleName);
        }

        return $html;
    }

    /**
     * Get field html with the modul info
     *
     * @param Varien_Data_Form_Element_Abstract
     * @param string Modul name
     * @return string
     */
    protected function getFieldHtml($element, $moduleName)
    {
        $id     = $moduleName;
        $data   = array(
            'name'  => '0000',
            'label' => $moduleName . $this->getModuleGuideLink($moduleName),
            'value' => $this->getModuleVersion($moduleName),
        );

        /* @var $_option Mage_Catalog_Model_Product_Option */
        $field  = $element->addField($id, 'label', $data)
            ->setRenderer($this->getFieldRenderer());
        return $field->toHtml();
    }

    /**
     * Get render class for fields
     *
     * @return Mage_Adminhtml_Block_System_Config_Form_Field
     */
    protected function getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }

        return $this->_fieldRenderer;
    }

    /**
     * Get modul version
     *
     * @param string Modul name
     * @return string
     */
    protected function getModuleVersion($moduleName)
    {
        return Mage::getConfig()->getModuleConfig($moduleName)->version;
    }

    /**
     * Get html link to modul guide ( system.xml )
     *
     * @param string Modul name
     * @return string
     */
    protected function getModuleGuideLink($moduleName)
    {
        $link = Mage::getConfig()->getModuleConfig($moduleName)->guidelink;
        return '<a class="rumage_guide_link_ico" href="' . $link . '" target="_blank" title="' . $this->__('User Guide link') . '"></a>';
    }

}