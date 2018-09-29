<?php

class MagicToolbox_MagicSlideshow_Block_Adminhtml_Settings_Edit_Tab_Form_Element_Radios extends Varien_Data_Form_Element_Radios
{

    protected function _optionToHtml($option, $selected)
    {
        $html = '<input type="radio" '.$this->serialize(array('name', 'class', 'style', 'disabled'));
        if (is_array($option)) {
            $html.= ' value="'.$this->_escape($option['value']).'" id="'.$this->getHtmlId().'-'.$option['value'].'"';
            if ($option['value'] == $selected) {
                $html.= ' checked="checked"';
            }
            $html.= ' />';
            $html.= '<label class="inline" for="'.$this->getHtmlId().'-'.$option['value'].'">'.$option['label'].'</label>';
        } elseif ($option instanceof Varien_Object) {
            $html.= ' id="'.$this->getHtmlId().'-'.$option->getValue().'" '.$option->serialize(array('label', 'title', 'value', 'class', 'style'));
            if (in_array($option->getValue(), $selected)) {
                $html.= ' checked="checked"';
            }
            $html.= ' />';
            $html.= '<label class="inline" for="'.$this->getHtmlId().'-'.$option->getValue().'">'.$option->getLabel().'</label>';
        }
        $html.= $this->getSeparator()."\n";
        return $html;
    }
}
