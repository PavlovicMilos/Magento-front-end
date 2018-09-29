<?php

class MagicToolbox_MagicSlideshow_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $isConfigurableSwatchesEnabled = false;

    public function __construct()
    {
        // Mage/Configurableswatches/Helper/Data.php
        $helperClass = Mage::getConfig()->getHelperClassName('configurableswatches/data');
        if (class_exists($helperClass, false)) {
            if (Mage::helper('configurableswatches')->isEnabled()) {
                $this->isConfigurableSwatchesEnabled = true;
            }
        }
    }

    /**
     * Retrieve list of option labels
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getProductOptionLabels(&$product)
    {
        static $labels = array();
        $id = $product->getId();
        if (!isset($labels[$id])) {
            $labels[$id] = array();
            if ($product->hasData('child_attribute_label_mapping')) {
                $childAttributeLabelMapping = $product->getChildAttributeLabelMapping();
                if (empty($childAttributeLabelMapping)) {
                    return array();
                }
                $mapping = call_user_func_array('array_merge_recursive', $childAttributeLabelMapping);
                $labels[$id] = array_unique($mapping['labels']);
            }
        }
        return $labels[$id];
    }

    /**
     * Determine whether to show an image in the product media gallery
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Varien_Object $image
     * @return bool
     */
    public function isGalleryImageVisible(&$product, &$image)
    {
        static $productImageFilters = array();
        if (!$this->isConfigurableSwatchesEnabled) {
            return true;
        }
        $id = $product->getId();
        if (!isset($productImageFilters[$id])) {
            $filters = $this->getProductOptionLabels($product);
            $filters = array_map(
                function ($label) {
                    return $label . Mage_ConfigurableSwatches_Helper_Productimg::SWATCH_LABEL_SUFFIX;
                },
                $filters
            );
            $productImageFilters[$id] = $filters;
        }
        return !in_array(Mage_ConfigurableSwatches_Helper_Data::normalizeKey($image->getLabel()), $productImageFilters[$id]);
    }

    /**
     * Retrieve list of all gallery images
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Varien_Data_Collection
     */
    public function getAllGalleryImages(&$product)
    {
        static $allImages = array();
        $id = $product->getId();
        if (!isset($allImages[$id])) {
            $allImages[$id] = new Varien_Data_Collection();
            $images = $product->getMediaGallery('images');
            if (is_array($images)) {
                foreach ($images as $image) {
                    $image['url'] = $product->getMediaConfig()->getMediaUrl($image['file']);
                    $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                    $image['path'] = $product->getMediaConfig()->getMediaPath($image['file']);
                    $allImages[$id]->addItem(new Varien_Object($image));
                }
            }
        }
        return $allImages[$id];
    }

    /**
     * Retrieve list of excluded gallery images
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Varien_Data_Collection
     */
    public function getExcludedGalleryImages(&$product)
    {
        static $excludedImages = array();
        $id = $product->getId();
        if (!isset($excludedImages[$id])) {
            $excludedImages[$id] = new Varien_Data_Collection();
            $images = $product->getMediaGallery('images');
            if (is_array($images)) {
                foreach ($images as $image) {
                    if ($image['disabled']) {
                        $image['url'] = $product->getMediaConfig()->getMediaUrl($image['file']);
                        $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                        $image['path'] = $product->getMediaConfig()->getMediaPath($image['file']);
                        $excludedImages[$id]->addItem(new Varien_Object($image));
                    }
                }
            }
        }
        return $excludedImages[$id];
    }

    /**
     * Retrieve list of used labeled images
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $excludedOnly
     * @return array
     */
    public function getUsedLabeledImages(&$product, $excludedOnly = false)
    {
        static $labeledImages = array();
        $id = $product->getId();
        if (!isset($labeledImages[$id])) {
            $labeledImages[$id] = array();
            $labels = $this->getProductOptionLabels($product);
            $images = $excludedOnly ? $this->getExcludedGalleryImages($product) : $this->getAllGalleryImages($product);
            foreach ($images as $image) {
                $label = trim(strtolower($image->getLabel()));
                if (in_array($label, $labels)) {
                    $labeledImages[$id][$label] = $image;
                }
            }
        }
        return $labeledImages[$id];
    }

    /**
     * Retrieve list of used labeled image's urls
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $excludedOnly
     * @return array
     */
    public function getUsedLabeledImageUrls(&$product, $excludedOnly = false)
    {
        static $data = array();
        $id = $product->getId();
        if (!isset($data[$id])) {
            $data[$id] = array();
            $images = $this->getUsedLabeledImages($product, $excludedOnly);

            $magicToolboxHelper = Mage::helper('magicslideshow/settings');
            $tool = $magicToolboxHelper->loadTool();
            $imageHelper = Mage::helper('catalog/image');
            $createSquareImages = $tool->params->checkValue('square-images', 'Yes');

            foreach ($images as $key => $image) {
                $imagePath = $image->getPath();
                if (file_exists($imagePath)) {
                    $imageSize = getimagesize($imagePath);
                    $bigImage = $imageHelper->init($product, 'image', $image->getFile())->__toString();
                    if ($createSquareImages) {
                        $bigImageSize = ($imageSize[0] > $imageSize[1]) ? $imageSize[0] : $imageSize[1];
                        $bigImage = $imageHelper->watermark(null, null)->resize($bigImageSize)->__toString();
                    }
                    list($w, $h) = $magicToolboxHelper->magicToolboxGetSizes('thumb', $imageSize);
                    $mediumImage = $imageHelper->watermark(null, null)->resize($w, $h)->__toString();
                    $data[$id][$key] = array(
                        'large-image-url' => $bigImage,
                        'small-image-url' => $mediumImage,
                    );
                }
            }
        }
        return $data[$id];
    }
}
