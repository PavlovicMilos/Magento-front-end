<?php

class MagicToolbox_MagicSlideshow_Model_Mysql4_Settings_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        $this->_init('magicslideshow/settings');
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->_items as $item) {
            //$website_id = $item->getData('website_id');
            //$group_id = $item->getData('group_id');
            //$store_id = $item->getData('store_id');
            //$package = $item->getData('package');
            //$theme = $item->getData('theme');
            //$custom_settings_title = $item->getData('custom_settings_title');
            //if ($package == 'all') {
            //    $item->setData('package_theme', 'Edit Magic Slideshow&#8482; default settings');
            //} else {
            //    $item->setData('package_theme', 'Settings for '.$package.'/'.$theme.' theme');
            //}
            $lastEditTime = $item->getData('last_edit_time');
            if (!$lastEditTime) {
                $item->setData('last_edit_time', 'not edited');
            }
        }
        return $this;
    }

}
