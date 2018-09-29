<?php

class MagicToolbox_MagicSlideshow_Block_Headers extends Mage_Core_Block_Abstract
{

    //NOTE: in reverse order
    protected $magictoolboxHeaders = array(
        'magicslideshow',
        'magicscroll',
        'magiczoomplus',
        'magiczoom',
        'magic360',
        'magicthumb'
    );

    public function _toHtml()
    {
        $html = '';
        $layout = $this->getLayout();
        if ($layout) {
            $doDisplayProductPageScript = true;
            $doDisplayAdditionalScroll = true;
            foreach ($this->magictoolboxHeaders as $name) {
                $block = $layout->getBlock($name.'_header');
                if ($block) {
                    $block->displayProductPageScript($doDisplayProductPageScript);
                    $block->displayAdditionalScroll($doDisplayAdditionalScroll);
                    $html = $block->toHtml().$html;
                    $doDisplayProductPageScript = $block->displayProductPageScript();
                    $doDisplayAdditionalScroll = $block->displayAdditionalScroll();
                    $layout->unsetBlock($name.'_header');//$block->getNameInLayout()
                }
            }
        }
        return $html;
    }
}
