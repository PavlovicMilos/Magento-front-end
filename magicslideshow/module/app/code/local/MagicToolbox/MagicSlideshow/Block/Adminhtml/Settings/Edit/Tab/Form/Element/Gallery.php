<?php

class MagicToolbox_MagicSlideshow_Block_Adminhtml_Settings_Edit_Tab_Form_Element_Gallery extends Varien_Data_Form_Element_Abstract
{

    public function getElementHtml()
    {
        $content = Mage::getSingleton('core/layout')->createBlock('magicslideshow/adminhtml_settings_edit_tab_form_element_gallery_content');
        $content->setId($this->getHtmlId() . '_content')->setElement($this);
        $html = $content->toHtml();
        return $html;
    }

    public function toHtml()
    {
        return '<tr><td class="label" colspan="3">'.
                '<label for="customslideshowblock_gallery_content_save">Choose files from your computer</label>'.
                '</td></tr>'.
                '<tr><td class="value" colspan="3">'.$this->getElementHtml().'</td></tr>';
    }

}
