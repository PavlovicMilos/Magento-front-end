<?php

class MagicToolbox_MagicSlideshow_Block_Adminhtml_Settings_Edit_Tab_Form_Element_Gallery_Content extends Mage_Adminhtml_Block_Widget
{

    public $newUploader = false;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('magicslideshow/gallery.phtml');
    }

    protected function _prepareLayout()
    {

        //TODO: find the better way to determine class
        $this->newUploader = false;
        $block = 'adminhtml/media_uploader';
        if (class_exists('Mage_Uploader_Block_Multiple', false) || mageFindClassFile('Mage_Uploader_Block_Multiple')) {
            $this->newUploader = true;
            $block = 'uploader/multiple';
        }

        $this->setChild('uploader',
            $this->getLayout()->createBlock($block)
        );

        if ($this->newUploader) {
            $this->getUploader()->getUploaderConfig()
                ->setFileParameterName('image')
                ->setTarget(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/magicslideshow_gallery/upload'));

            $browseConfig = $this->getUploader()->getButtonConfig();
            $browseConfig
                ->setAttributes(array(
                    'accept' => $browseConfig->getMimeTypesByExtensions('gif, png, jpeg, jpg')
                ));
        } else {
            $this->getUploader()->getConfig()
                ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/magicslideshow_gallery/upload'))
                ->setFileField('image')
                ->setFilters(array(
                    'images' => array(
                        'label' => Mage::helper('adminhtml')->__('Images (.gif, .jpg, .png)'),
                        'files' => array('*.gif', '*.jpg','*.jpeg', '*.png')
                    )
                ));
        }

        return parent::_prepareLayout();
    }

    public function getUploader()
    {
        return $this->getChild('uploader');
    }

    public function getUploaderHtml()
    {
        $html = $this->getChildHtml('uploader');

        //NOTE: in case of Flow object
        $html = preg_replace('#\bvar\s++uploader\s++=\s++new\s++Uploader\b#', $this->getUploaderJsObjectName().' = new Uploader', $html);
        $html = preg_replace('#\buploader\.onContainerHideBefore\b#', $this->getUploaderJsObjectName().'.onContainerHideBefore', $html);

        return $html;
    }

    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    public function getUploaderJsObjectName()
    {
        $name = null;
        if ($name == null) {
            $name = $this->newUploader ? $this->getUploader()->getHtmlId() . 'JsObject' : $this->getUploader()->getJsObjectName();
        }
        return $name;
    }

    public function getImagesJson()
    {
        $model = Mage::registry('magicslideshow_model_data');
        if ($model) {
            $data = $model->getData();
            if (!empty($data['value'])) {
                $settings = unserialize($data['value']);
                if (isset($settings['desktop']['customslideshowblock']['gallery'])) {
                    $images = Mage::helper('core')->jsonDecode($settings['desktop']['customslideshowblock']['gallery']);
                    foreach ($images as &$image) {
                        $image['url'] = $this->getMagicslideshowMediaUrl($image['file']);
                    }
                    return Mage::helper('core')->jsonEncode($images);
                }
            }
        }
        return '[]';
    }

    public function getMagicslideshowMediaUrl($file)
    {
        $file = str_replace(DS, '/', $file);
        if (substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }
        return Mage::getBaseUrl('media').'magictoolbox/magicslideshow/'.$file;
    }
}
