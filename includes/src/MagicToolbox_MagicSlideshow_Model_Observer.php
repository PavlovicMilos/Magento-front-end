<?php

class MagicToolbox_MagicSlideshow_Model_Observer
{

    /* NOTE: after get layout updates */
    public function fixLayoutUpdates($observer)
    {
        //NOTE: to prevent an override of our templates with other modules
        //NOTE: also to sort the modules layout for displaying headers in the right order

        global $isLayoutUpdatesAlreadyFixed;
        if (isset($isLayoutUpdatesAlreadyFixed)) return;
        $isLayoutUpdatesAlreadyFixed = true;

        //$xml = Mage::app()->getConfig()->getNode('frontend/layout/updates')->asNiceXml();
        //debug_log($xml);

        //NOTE: default order (without sorting)
        //Magic360
        //MagicScroll
        //MagicSlideshow
        //MagicThumb
        //MagicZoom
        //MagicZoomPlus

        //NOTE: sort order
        $modules = array(
            'magic360' => false,
            'magicthumb' => false,
            'magiczoom' => false,
            'magiczoomplus' => false,
            'magicscroll' => false,
            'magicslideshow' => false,
        );

        $pattern = '#^(?:'.implode('|', array_keys($modules)).')$#';
        foreach (Mage::app()->getConfig()->getNode('frontend/layout/updates')->children() as $key => $child) {
            if (preg_match($pattern, $key)) {
                //NOTE: remember detected modules 
                $modules[$key] = array(
                    'module' => $child->getAttribute('module'),
                    'file' => (string)$child->file,
                );
            }
        }

        //NOTE: remove node to prevent dublicate
        $path = implode(' | ', array_keys($modules));
        $elements = Mage::app()->getConfig()->getNode('frontend/layout/updates')->xpath($path);
        foreach ($elements as $element) {
            unset($element->{0});
        }

        //NOTE: add new nodes to the end
        foreach ($modules as $key => $data) {
            if (empty($data)) continue;
            $child = new Varien_Simplexml_Element("<{$key} module=\"{$data['module']}\"><file>{$data['file']}</file></{$key}>");
            Mage::app()->getConfig()->getNode('frontend/layout/updates')->appendChild($child);
        }

    }

    /* NOTE: before generate layout xml */
    public function addLayoutUpdate($observer)
    {

        global $isLayoutUpdateAlreadyAdded;
        if (isset($isLayoutUpdateAlreadyAdded)) return;
        $isLayoutUpdateAlreadyAdded = true;

        $layout = $observer->getEvent()->getLayout();
        //NOTE: modules are already sorted by order (fixLayoutUpdates)
        $pattern = '#^magic(?:thumb|360|zoom|zoomplus|scroll|slideshow)$#';
        foreach (Mage::app()->getConfig()->getNode('frontend/layout/updates')->children() as $key => $child) {
            if (preg_match($pattern, $key, $match)) {
                //NOTE: add layout update for detected module
                $xml = '
<reference name="product.info.media">
    <action method="setTemplate">
        <template helper="'.$match[0].'/settings/getBlockTemplate">
            <name>product.info.media</name>
            <template>'.$match[0].'/media.phtml</template>
        </template>
    </action>
</reference>';
                $layout->getUpdate()->addUpdate($xml);
            }
        }
    }
}
