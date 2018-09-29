<?php

class MagicToolbox_MagicSlideshow_Helper_Params extends Mage_Core_Helper_Abstract
{

    public function checkForOldModules()
    {
        static $oldModulesInstalled = null;
        if ($oldModulesInstalled === null) {
            $oldModulesInstalled = array();
            $modules = array(
                'magicthumb' => 'Magic Thumb',
                'magic360' => 'Magic 360',
                'magiczoom' => 'Magic Zoom',
                'magiczoomplus' => 'Magic Zoom Plus',
                'magicscroll' => 'Magic Scroll',
            );
            $inModules = "'".implode("_setup', '", array_keys($modules))."_setup'";
            $resource = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('core_read');
            $table = $resource->getTableName('core/resource');
            $result = $connection->query("SELECT * FROM `{$table}` WHERE `code` IN ({$inModules})");
            if ($result) {
                while ($module = $result->fetch(PDO::FETCH_ASSOC)) {
                    if (version_compare($module['version'], '4.12.0', '<')) {
                        $key = str_replace('_setup', '', $module['code']);
                        if ($this->isModuleEnabled('MagicToolbox_'.str_replace(' ', '', $modules[$key]))) {
                            $oldModulesInstalled[] = array('name' => $modules[$key], 'version' => $module['version']);
                        }
                    }
                }
            }
        }
        return $oldModulesInstalled;
    }

    public function getFixedDefaultValues()
    {
        $defaultValues = self::getDefaultValues();
        foreach ($defaultValues as $platform => $platformData) {
            foreach ($platformData as $profile => $profileData) {
                foreach ($profileData as $param => $value) {
                    if ($param == 'enable-effect' || $param == 'include-headers-on-all-pages') {
                        $defaultValues[$platform][$profile][$param] = 'No';
                    }
                }
            }
        }
        return $defaultValues;
    }

    public function getProfiles()
    {
        return array(
            'default' => 'Defaults',
            'customslideshowblock' => 'Homepage or custom block',
            'product' => 'Product page',
            'recentlyviewedproductsblock' => 'Recently Viewed Products block'
        );
    }

    public function getDefaultValues()
    {
        return array(
            'desktop' => array(
                'customslideshowblock' => array(
                    'enable-effect' => 'No',
                    'thumb-max-width' => '500',
                    'thumb-max-height' => '500',
                    'height' => '60%',
                    'arrows' => 'Yes',
                    'caption' => 'Yes',
                    'show-message' => 'No'
                ),
                'product' => array(
                    'enable-effect' => 'Yes',
                    'arrows' => 'Yes',
                    'caption' => 'Yes',
                    'fullscreen' => 'Yes'
                ),
                'recentlyviewedproductsblock' => array(
                    'enable-effect' => 'No',
                    'thumb-max-width' => '235',
                    'thumb-max-height' => '235',
                    'show-message' => 'No'
                )
            ),
            'mobile' => array(
            )
        );
    }

    public function getParamsMap($block)
    {
        $blocks = array(
            'default' => array(
                'General' => array(
                    'include-headers-on-all-pages'
                ),
                'Positioning and Geometry' => array(
                    'thumb-max-width',
                    'thumb-max-height',
                    'selector-max-width',
                    'selector-max-height',
                    'square-images'
                ),
                'Common settings' => array(
                    'width',
                    'height',
                    'orientation',
                    'arrows',
                    'loop',
                    'effect',
                    'effect-speed',
                    'effect-easing'
                ),
                'Autoplay' => array(
                    'autoplay',
                    'slide-duration',
                    'shuffle',
                    'kenburns',
                    'pause'
                ),
                'Selectors' => array(
                    'selectors-style',
                    'selectors',
                    'selectors-eye',
                    'bullets-preview',
                    'selectors-fill'
                ),
                'Caption' => array(
                    'caption'
                ),
                'Other settings' => array(
                    'fullscreen',
                    'preload',
                    'keyboard',
                    'show-loader',
                    'autostart'
                ),
                'Miscellaneous' => array(
                    'link-to-product-page',
                    'show-message',
                    'message'
                )
            ),
            'customslideshowblock' => array(
                'General' => array(
                    'enable-effect',
                    'block-title'
                ),
                'Positioning and Geometry' => array(
                    'thumb-max-width',
                    'thumb-max-height',
                    'selector-max-width',
                    'selector-max-height',
                    'square-images'
                ),
                'Common settings' => array(
                    'width',
                    'height',
                    'orientation',
                    'arrows',
                    'loop',
                    'effect',
                    'effect-speed',
                    'effect-easing'
                ),
                'Autoplay' => array(
                    'autoplay',
                    'slide-duration',
                    'shuffle',
                    'kenburns',
                    'pause'
                ),
                'Selectors' => array(
                    'selectors-style',
                    'selectors',
                    'selectors-eye',
                    'bullets-preview',
                    'selectors-fill'
                ),
                'Caption' => array(
                    'caption'
                ),
                'Other settings' => array(
                    'fullscreen',
                    'preload',
                    'keyboard',
                    'show-loader',
                    'autostart'
                ),
                'Miscellaneous' => array(
                    'show-message',
                    'message'
                )
            ),
            'product' => array(
                'General' => array(
                    'enable-effect'
                ),
                'Positioning and Geometry' => array(
                    'thumb-max-width',
                    'thumb-max-height',
                    'selector-max-width',
                    'selector-max-height',
                    'square-images'
                ),
                'Common settings' => array(
                    'width',
                    'height',
                    'orientation',
                    'arrows',
                    'loop',
                    'effect',
                    'effect-speed',
                    'effect-easing'
                ),
                'Autoplay' => array(
                    'autoplay',
                    'slide-duration',
                    'shuffle',
                    'kenburns',
                    'pause'
                ),
                'Selectors' => array(
                    'selectors-style',
                    'selectors',
                    'selectors-eye',
                    'bullets-preview',
                    'selectors-fill'
                ),
                'Caption' => array(
                    'caption'
                ),
                'Other settings' => array(
                    'fullscreen',
                    'preload',
                    'keyboard',
                    'show-loader',
                    'autostart'
                ),
                'Miscellaneous' => array(
                    'show-message',
                    'message'
                )
            ),
            'recentlyviewedproductsblock' => array(
                'General' => array(
                    'enable-effect'
                ),
                'Positioning and Geometry' => array(
                    'thumb-max-width',
                    'thumb-max-height',
                    'selector-max-width',
                    'selector-max-height',
                    'square-images'
                ),
                'Common settings' => array(
                    'width',
                    'height',
                    'orientation',
                    'arrows',
                    'loop',
                    'effect',
                    'effect-speed',
                    'effect-easing'
                ),
                'Autoplay' => array(
                    'autoplay',
                    'slide-duration',
                    'shuffle',
                    'kenburns',
                    'pause'
                ),
                'Selectors' => array(
                    'selectors-style',
                    'selectors',
                    'selectors-eye',
                    'bullets-preview',
                    'selectors-fill'
                ),
                'Caption' => array(
                    'caption'
                ),
                'Other settings' => array(
                    'fullscreen',
                    'preload',
                    'keyboard',
                    'show-loader',
                    'autostart'
                ),
                'Miscellaneous' => array(
                    'link-to-product-page',
                    'show-message',
                    'message'
                )
            )
        );
        return $blocks[$block];
    }
}
