<?php

class MagicToolbox_MagicSlideshow_Magicslideshow_GalleryController extends Mage_Adminhtml_Controller_Action
{

    public function uploadAction()
    {
        try {

            $pattern = "/([0-9]+\.[0-9]+\.[0-9]+)(?:\.[0-9]+)*/";
            $matches = array();
            preg_match($pattern, Mage::getVersion(), $matches);
            if (version_compare($matches[1], '1.5.1', '<')) {
                $uploader = new Varien_File_Uploader('image');
            } else {
                $uploader = new Mage_Core_Model_File_Uploader('image');
            }

            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->addValidateCallback('catalog_product_image', Mage::helper('catalog/image'), 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save($this->getMagicslideshowBaseMediaPath());

            /**
             * Workaround for prototype 1.7 methods "isJSON", "evalJSON" on Windows OS
             */
            $result['tmp_name'] = str_replace(DS, "/", $result['tmp_name']);
            $result['path'] = str_replace(DS, "/", $result['path']);

            $result['url'] = $this->getMagicslideshowMediaUrl($result['file']);
            $result['file'] = $result['file'];
            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );

        } catch (Exception $e) {
            $result = array(
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function getMagicslideshowMediaUrl($file)
    {
        $file = str_replace(DS, '/', $file);
        if (substr($file, 0, 1) == '/') {
            $file = substr($file, 1);
        }
        return Mage::getBaseUrl('media').'magictoolbox/magicslideshow/'.$file;
    }

    public function getMagicslideshowBaseMediaPath()
    {
        return Mage::getBaseDir('media').DS.'magictoolbox'.DS.'magicslideshow';
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/magictoolbox/magicslideshow');
    }

}
