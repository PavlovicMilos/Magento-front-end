<?php

class MagicToolbox_MagicSlideshow_Block_Adminhtml_Settings_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {

        parent::__construct();

        $this->setId('magicslideshow_config_tabs');
        $this->setDestElementId('edit_form');//this should be same as the form id
        $this->setTitle('<span style="visibility: hidden">'.Mage::helper('magicslideshow')->__('Supported blocks:').'</span>');

    }

    protected function _beforeToHtml()
    {

        $blocks = Mage::helper('magicslideshow/params')->getProfiles();
        $activeTab = $this->getRequest()->getParam('tab', 'product');

        foreach ($blocks as $id => $label) {
            $this->addTab($id, array(
                'label'     => Mage::helper('magicslideshow')->__($label),
                'title'     => Mage::helper('magicslideshow')->__($label.' settings'),
                'content'   => $this->getLayout()->createBlock('magicslideshow/adminhtml_settings_edit_tab_form', 'magicslideshow_'.$id.'_settings_block')->toHtml(),
                'active'    => ($id == $activeTab) ? true : false
            ));
        }

        return parent::_beforeToHtml();
    }
}
