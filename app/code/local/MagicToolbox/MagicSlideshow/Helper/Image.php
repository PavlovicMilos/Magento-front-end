<?php

class MagicToolbox_MagicSlideshow_Helper_Image extends Mage_Core_Helper_Abstract
{

    protected $_imageFile;
    protected $_baseDir;
    protected $_baseFile;
    protected $_cacheBaseDir;
    protected $_newFile;

    protected $_processor = null;//Varien_Image

    protected $_width = null;
    protected $_height = null;
    protected $_scheduleResize = false;

    protected $_quality = 90;//values in percentage from 0 to 100
    protected $_keepAspectRatio  = true;
    protected $_keepFrame        = true;
    protected $_keepTransparency = true;
    protected $_constrainOnly    = false;
    protected $_backgroundColor  = array(255, 255, 255);

    protected $_watermarkFile = null;
    protected $_watermarkPosition = null;
    protected $_watermarkSize = null;//param size in format 100x200
    protected $_watermarkWidth;
    protected $_watermarkHeight;
    protected $_watermarkImageOpacity = 70;

    public function init($imageFile=null)
    {
        $this->_processor = null;
        $this->_scheduleResize = false;
        $this->_width = null;
        $this->_height = null;
        $this->_watermarkFile = null;
        $this->_watermarkPosition = null;
        $this->_watermarkSize = null;
        $this->_watermarkImageOpacity = 70;
        $this->_imageFile = null;
        $this->_cacheBaseDir = $this->_baseDir = Mage::getBaseDir('media').DS.'magictoolbox'.DS.'magicslideshow';
        $this->setWatermarkFile(Mage::getStoreConfig("design/watermark/image_image"));
        $this->setWatermarkImageOpacity(Mage::getStoreConfig("design/watermark/image_imageOpacity"));
        $this->setWatermarkPosition(Mage::getStoreConfig("design/watermark/image_position"));
        $this->setWatermarkSize(Mage::getStoreConfig("design/watermark/image_size"));
        if ($imageFile) {
            $this->setImageFile($imageFile);
        }
        return $this;
    }

    public function resize($width, $height = null)
    {
        $this->setWidth($width)->setHeight($height);
        $this->_scheduleResize = true;
        return $this;
    }

    public function __toString()
    {
        try {
            if ($this->getImageFile()) {
                $this->setBaseFile($this->getImageFile());
            }
            if ($this->isCached()) {
                return $this->getUrl();
            } else {
                if ($this->_scheduleResize) {
                    $this->_resize();
                }
                if ($this->getWatermarkFile()) {
                    $filePath = $this->_getWatermarkFilePath();
                    if ($filePath) {
                        $this->getImageProcessor()
                            ->setWatermarkPosition($this->getWatermarkPosition())
                            ->setWatermarkImageOpacity($this->getWatermarkImageOpacity())
                            ->setWatermarkWidth($this->getWatermarkWidth())
                            //NOTE: method name is "setWatermarkHeigth" (not "setWatermarkHeight")
                            ->setWatermarkHeigth($this->getWatermarkHeight())
                            ->watermark($filePath);
                    }
                }
                $url = $this->saveFile()->getUrl();
            }
        } catch (Exception $e) {
            $url = '';
        }
        return $url;
    }

    protected function setImageFile($file)
    {
        $this->_imageFile = $file;
        return $this;
    }

    protected function getImageFile()
    {
        return $this->_imageFile;
    }

    public function getOriginalWidth()
    {
        return $this->getImageProcessor()->getOriginalWidth();
    }

    public function getOriginalHeight()
    {
        return $this->getImageProcessor()->getOriginalHeight();
    }

    public function getOriginalSizeArray()
    {
        return array(
            $this->getOriginalWidth(),
            $this->getOriginalHeight()
        );
    }

    public function setWidth($width)
    {
        $this->_width = $width;
        return $this;
    }

    public function getWidth()
    {
        return $this->_width;
    }

    public function setHeight($height)
    {
        $this->_height = $height;
        return $this;
    }

    public function getHeight()
    {
        return $this->_height;
    }

    public function setQuality($quality)
    {
        $this->_quality = $quality;
        return $this;
    }

    public function getQuality()
    {
        return $this->_quality;
    }

    public function setKeepAspectRatio($keep)
    {
        $this->_keepAspectRatio = (bool)$keep;
        return $this;
    }

    public function setKeepFrame($keep)
    {
        $this->_keepFrame = (bool)$keep;
        return $this;
    }

    public function setKeepTransparency($keep)
    {
        $this->_keepTransparency = (bool)$keep;
        return $this;
    }

    public function setConstrainOnly($flag)
    {
        $this->_constrainOnly = (bool)$flag;
        return $this;
    }

    public function setBaseDir($path)
    {
        $this->_baseDir = $path;
        return $this;
    }

    public function setBaseFile($file)
    {
        if ($file && (0 !== strpos($file, '/', 0))) {
            $file = '/'.$file;
        }
        $baseFile = $this->_baseDir.$file;
        if (!$file || !file_exists($baseFile)) {
            throw new Exception(Mage::helper('magicslideshow')->__('Image file was not found.'));
        }
        $this->_baseFile = $baseFile;

        $path = array(
            $this->_cacheBaseDir,
            'cache'
        );
        if ((!empty($this->_width)) || (!empty($this->_height))) {
            $path[] = "{$this->_width}x{$this->_height}";
        } else {
            $path[] = 'original_size';
        }
        $paramsForHash = array(
            $this->_keepAspectRatio  ? 'true' : 'false',
            $this->_keepFrame        ? 'true' : 'false',
            $this->_keepTransparency ? 'true' : 'false',
            $this->_constrainOnly    ? 'true' : 'false',
            'quality'.$this->_quality
        );
        if ($this->getWatermarkFile()) {
            $paramsForHash[] = $this->getWatermarkFile();
            $paramsForHash[] = $this->getWatermarkImageOpacity();
            $paramsForHash[] = $this->getWatermarkPosition();
            $paramsForHash[] = $this->getWatermarkWidth();
            $paramsForHash[] = $this->getWatermarkHeight();
        }
        $path[] = md5(implode('_', $paramsForHash));

        $this->_newFile = implode('/', $path).$file;

        return $this;
    }

    public function getBaseFile()
    {
        return $this->_baseFile;
    }

    public function getNewFile()
    {
        return $this->_newFile;
    }

    public function setImageProcessor($processor)
    {
        $this->_processor = $processor;
        return $this;
    }

    public function getImageProcessor()
    {
        if (!$this->_processor) {
            $this->_processor = new Varien_Image($this->getBaseFile());
        }
        $this->_processor->keepAspectRatio($this->_keepAspectRatio);
        $this->_processor->keepFrame($this->_keepFrame);
        $this->_processor->keepTransparency($this->_keepTransparency);
        $this->_processor->constrainOnly($this->_constrainOnly);
        $this->_processor->backgroundColor($this->_backgroundColor);
        $this->_processor->quality($this->_quality);
        return $this->_processor;
    }

    public function _resize()
    {
        if (!is_null($this->_width) || !is_null($this->_height)) {
            $this->getImageProcessor()->resize($this->_width, $this->_height);
        }
        return $this;
    }

    public function saveFile()
    {
        $this->getImageProcessor()->save($this->getNewFile());
        return $this;
    }

    public function getUrl()
    {
        $baseDir = Mage::getBaseDir('media');
        $path = str_replace($baseDir.DS, '', $this->_newFile);
        return Mage::getBaseUrl('media').str_replace(DS, '/', $path);
    }

    public function isCached()
    {
        return file_exists($this->_newFile);
    }

    public function setWatermarkFile($file)
    {
        $this->_watermarkFile = $file;
        return $this;
    }

    public function getWatermarkFile()
    {
        return $this->_watermarkFile;
    }

    protected function _getWatermarkFilePath()
    {
        $filePath = false;
        $file = $this->getWatermarkFile();
        if ($file) {
            if (file_exists(BP.DS.$file)) {
                return BP.DS.$file;
            }
            $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
            $storeId = Mage::app()->getStore()->getId();
            if ($this->_fileExists($baseDir.'/watermark/stores/'.$storeId.$file)) {
                $filePath = $baseDir.'/watermark/stores/'.$storeId.$file;
            } elseif ($this->_fileExists($baseDir.'/watermark/websites/'.$storeId.$file)) {
                $filePath = $baseDir.'/watermark/websites/'.$storeId.$file;
            } elseif ($this->_fileExists($baseDir.'/watermark/default/'.$file)) {
                $filePath = $baseDir.'/watermark/default/'.$file;
            } elseif ($this->_fileExists($baseDir.'/watermark/'.$file)) {
                $filePath = $baseDir.'/watermark/'.$file;
            } else {
                $baseDir = Mage::getDesign()->getSkinBaseDir();
                if ($this->_fileExists($baseDir.$file)) {
                    $filePath = $baseDir.$file;
                }
            }
        }
        return $filePath;
    }

    protected function _fileExists($filename)
    {
        if (file_exists($filename)) {
            return true;
        } else {
            return Mage::helper('core/file_storage_database')->saveFileToFilesystem($filename);
        }
    }

    public function setWatermarkPosition($position)
    {
        $this->_watermarkPosition = $position;
        return $this;
    }

    public function getWatermarkPosition()
    {
        return $this->_watermarkPosition;
    }

    public function setWatermarkImageOpacity($imageOpacity)
    {
        $this->_watermarkImageOpacity = $imageOpacity;
        return $this;
    }

    public function getWatermarkImageOpacity()
    {
        return $this->_watermarkImageOpacity;
    }

    public function setWatermarkSize($size)
    {
        if (is_string($size)) {
            $this->_watermarkSize = $size;
            $this->setWatermarkSize($this->parseSize($size));
        } elseif (is_array($size)) {
            $this->setWatermarkWidth($size['width'])
                ->setWatermarkHeight($size['height']);
        }
        return $this;
    }

    protected function parseSize($string)
    {
        $size = explode('x', strtolower($string));
        if (sizeof($size) == 2) {
            return array(
                'width' => ($size[0] > 0) ? $size[0] : null,
                'height' => ($size[1] > 0) ? $size[1] : null,
            );
        }
        return false;
    }

    protected function getWatermarkSize()
    {
        return $this->_watermarkSize;
    }

    public function setWatermarkWidth($width)
    {
        $this->_watermarkWidth = $width;
        return $this;
    }

    public function getWatermarkWidth()
    {
        return $this->_watermarkWidth;
    }

    public function setWatermarkHeight($height)
    {
        $this->_watermarkHeight = $height;
        return $this;
    }

    public function getWatermarkHeight()
    {
        return $this->_watermarkHeight;
    }

    public function clearCache()
    {
        $directory = Mage::getBaseDir('media').DS.'magictoolbox'.DS.'magicslideshow'.DS.'cache'.DS;
        $io = new Varien_Io_File();
        $io->rmdir($directory, true);
    }

}
