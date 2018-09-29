<?php

class MagicToolbox_MagicSlideshow_Model_Mysql4_Settings extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {

        $this->_init('magicslideshow/settings', 'setting_id');

    }

}
