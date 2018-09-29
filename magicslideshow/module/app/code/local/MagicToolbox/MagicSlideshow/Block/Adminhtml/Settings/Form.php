<?php

class MagicToolbox_MagicSlideshow_Block_Adminhtml_Settings_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {

        $storeViews = array();
        $websites = Mage::app()->getWebsites();
        if ($websites) {
            foreach ($websites as $websiteId => $website) {
                $groups = array();
                foreach ($website->getGroups() as $group) {
                    $_stores = array();
                    if (!$group instanceof Mage_Core_Model_Store_Group) {
                        $group = Mage::app()->getGroup($group);
                    }
                    $stores = $group->getStores();
                    foreach ($stores as $store) {
                        $_stores[] = array(
                            'label' => $this->escapeHtml($store->getName()),
                            'value' => $websiteId.'/'.$group->getId().'/'.$store->getId(),
                            'style' => 'padding-left: 42px;',
                        );
                    }
                    if (!empty($_stores)) {
                        array_unshift($_stores, array(
                            'label' => $this->escapeHtml($group->getName()),
                            'value' => $websiteId.'/'.$group->getId().'/',
                            'style' => 'font-weight: bold; padding-left: 32px;',
                        ));
                        $groups = array_merge($groups, $_stores);
                    }
                }
                if (!empty($groups)) {
                    array_unshift($groups, array(
                        'label' => $this->escapeHtml($website->getName()),
                        'value' => $websiteId.'//',
                        'style' => 'font-weight: bold; padding-left: 16px; background-color: #DDDDDD;',
                    ));
                    $storeViews = array_merge($storeViews, $groups);
                }
            }
            if (!empty($storeViews)) {
                array_unshift($storeViews, array(
                    'label' => $this->__('All Store Views'),
                    'value' => '',
                    'style' => 'font-weight: bold; background-color: #CCCCCC;',
                ));
            }
        }

        $themeList = Mage::getModel('core/design_package')->getThemeList();
        $availableDesigns = array();
        foreach ($themeList as $package => $themes) {
            $availableDesigns[] = array(
                'label' => $package,
                'value' => $package.'/',
                'style' => 'font-weight: bold; padding-left: 16px;',
            );
            foreach ($themes as $theme) {
                $availableDesigns[] = array(
                    'label' => $theme,
                    'value' => $package.'/'.$theme,
                    'style' => 'padding-left: 32px;',
                );
            }
        }
        if (!empty($availableDesigns)) {
            array_unshift($availableDesigns, array(
                'label' => $this->__('All Designs'),
                'value' => '',
                'style' => 'font-weight: bold; background-color: #CCCCCC;',
            ));
        }

        $model = Mage::getModel('magicslideshow/settings');
        $collection = $model->getCollection();
        $designs = array();
        foreach ($collection as $item) {
            $designs[] = $item->getPackage()."/".$item->getTheme();
        }

        //$availableDesigns = Mage::getSingleton('core/design_source_design')->getAllOptions();
        /*foreach ($availableDesigns as $pKey => $package) {
            if (is_array($package['value'])) {
                foreach ($package['value'] as $tKey => $theme) {
                    if (in_array($theme['value'], $designs)) {
                        unset($availableDesigns[$pKey]['value'][$tKey]);
                    }
                }
                if (!count($availableDesigns[$pKey]['value'])) unset($availableDesigns[$pKey]);
            }
        }*/

        //if (count($availableDesigns) == 1) {
        //    Mage::register('magicslideshow_custom_design_settings_form', false);
        //    return parent::_prepareForm();
        //}

        $form = new Varien_Data_Form(array(
            'id' => 'add_form',
            'action' => $this->getUrl('*/*/add'),
            'method' => 'post',
        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('add_custom_set', array('legend'=>Mage::helper('magicslideshow')->__('Add custom settings')));

        $fieldset->addField('store_views', 'select', array(
            'label'     => Mage::helper('magicslideshow')->__('Store View'),
            'title'     => Mage::helper('magicslideshow')->__('Store View'),
            'values'    => $storeViews,
            'name'      => 'store_views',
            'required'  => false,
        ));

        $fieldset->addField('design', 'select', array(
            'label'     => Mage::helper('magicslideshow')->__('Custom Design'),
            'title'     => Mage::helper('magicslideshow')->__('Custom Design'),
            'values'    => $availableDesigns,
            'name'      => 'design',
            'required'  => false,
        ));

        $fieldset->addField('add_button', 'note', array(
            'text'      => $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'label'     => Mage::helper('magicslideshow')->__('Add Setting'),
                'onclick'   => "addForm.submit()",
                'class'     => 'add',
                'type'      => 'button'
            ))->toHtml(),
            'class' => 'a-right'
        ));

        Mage::register('magicslideshow_custom_design_settings_form', true);

        return parent::_prepareForm();
    }

    protected function _afterToHtml($html)
    {

        $html .= '<script type="text/javascript">addForm = new varienForm(\'add_form\', \'\');</script>';
        return parent::_afterToHtml($html);

    }

}
