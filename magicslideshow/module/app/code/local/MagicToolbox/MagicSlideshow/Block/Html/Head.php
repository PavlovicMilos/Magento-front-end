<?php

class MagicToolbox_MagicSlideshow_Block_Html_Head extends Mage_Page_Block_Html_Head
{

    public function getCssJsHtml()
    {

        $helper = Mage::helper('magicslideshow/settings');
        if ($helper->isModuleOutputEnabled()) {
            //check Magento version
            $mageVersion = Mage::getVersion();
            $pattern = "/([0-9]+\.[0-9]+\.[0-9]+)(?:\.(?:[0-9]+))*/";
            $matches = array();
            if (preg_match($pattern, $mageVersion, $matches)) {
                if (version_compare($matches[1], '1.4.1', '<')) {
                    if (isset($this->_data['items']['js/varien/menu.js'])) {
                        $this->_data['items']['js/varien/menu.js']['name'] = 'magicslideshow/menu.js';
                    }
                    if (isset($this->_data['items']['js/varien/iehover-fix.js'])) {
                        $this->_data['items']['js/varien/iehover-fix.js']['name'] = 'magicslideshow/iehover-fix.js';
                    }
                }
            }
        }
        return parent::getCssJsHtml();

    }

}
