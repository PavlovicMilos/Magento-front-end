<?php

if (!defined('MagicSlideshowModuleCoreClassLoaded')) {

define('MagicSlideshowModuleCoreClassLoaded', true);

require_once(dirname(__FILE__).'/magictoolbox.params.class.php');

/**
 * MagicSlideshowModuleCoreClass
 *
 */
class MagicSlideshowModuleCoreClass
{

    /**
     * MagicToolboxParamsClass class
     *
     * @var   MagicToolboxParamsClass
     *
     */
    public $params;

    /**
     * Tool type
     *
     * @var   string
     *
     */
    public $type = 'category';

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->params = new MagicToolboxParamsClass();
        $this->params->setScope('magicslideshow');
        $this->params->setMapping(array(
            'arrows' => array('Yes' => 'true', 'No' => 'false'),
            'loop' => array('Yes' => 'true', 'No' => 'false'),
            'autoplay' => array('Yes' => 'true', 'No' => 'false'),
            'shuffle' => array('Yes' => 'true', 'No' => 'false'),
            'kenburns' => array('Yes' => 'true', 'No' => 'false'),
            'pause' => array('Yes' => 'true', 'No' => 'false'),
            'selectors-eye' => array('Yes' => 'true', 'No' => 'false'),
            'selectors-fill' => array('Yes' => 'true', 'No' => 'false'),
            'caption' => array('Yes' => 'true', 'No' => 'false'),
            'fullscreen' => array('Yes' => 'true', 'No' => 'false'),
            'preload' => array('Yes' => 'true', 'No' => 'false'),
            'keyboard' => array('Yes' => 'true', 'No' => 'false'),
            'show-loader' => array('Yes' => 'true', 'No' => 'false'),
            'autostart' => array('Yes' => 'true', 'No' => 'false'),
        ));
        $this->loadDefaults();
    }

    /**
     * Method to get headers string
     *
     * @param string $jsPath  Path to JS file
     * @param string $cssPath Path to CSS file
     *
     * @return string
     */
    public function getHeadersTemplate($jsPath = '', $cssPath = null)
    {
        //to prevent multiple displaying of headers
        if (!defined('MAGICSLIDESHOW_MODULE_HEADERS')) {
            define('MAGICSLIDESHOW_MODULE_HEADERS', true);
        } else {
            return '';
        }
        if ($cssPath == null) {
            $cssPath = $jsPath;
        }
        $headers = array();
        $headers[] = '<!-- Magic Slideshow Magento module version v4.15.9 [v1.6.75:v3.1.15] -->';
        $headers[] = '<script type="text/javascript">window["mgctlbx$Pltm"] = "Magento";</script>';
        $headers[] = '<link type="text/css" href="'.$cssPath.'/magicslideshow.css" rel="stylesheet" media="screen" />';
        $headers[] = '<link type="text/css" href="'.$cssPath.'/magicslideshow.module.css" rel="stylesheet" media="screen" />';
        $headers[] = '<script type="text/javascript" src="'.$jsPath.'/magicslideshow.js"></script>';
        $headers[] = $this->getOptionsTemplate();
        return "\r\n".implode("\r\n", $headers)."\r\n";
    }

    /**
     * Method to get options string
     *
     * @return string
     */
    public function getOptionsTemplate()
    {
        $addition = '';
        if ($selectorsSize = $this->params->getParam('selectors-size')) {
            if (!isset($selectorsSize['scope']) || $selectorsSize['scope'] != 'magicslideshow') {
                $selectorsSize = $this->params->getValue('selectors-size');
                $addition = "\n\t\t'selectors-size':'{$selectorsSize}',";
            }
        } else {
            if ($this->params->checkValue('selectors', array('bottom', 'top'))) {
                $selectorsSize = $this->params->getValue('selector-max-height');
                if (empty($selectorsSize)) {
                    $selectorsSize = 70;
                }
            } elseif ($this->params->checkValue('selectors', array('right', 'left'))) {
                $selectorsSize = $this->params->getValue('selector-max-width');
                if (empty($selectorsSize)) {
                    $selectorsSize = 70;
                }
            } else {
                $selectorsSize = 70;
            }
            $addition = "\n\t\t'selectors-size':'{$selectorsSize}',";
        }
        return "<script type=\"text/javascript\">\n\tMagicSlideshowOptions = {{$addition}\n\t\t".$this->params->serialize(true, ",\n\t\t")."\n\t}\n</script>";
    }

    /**
     * Method to get MagicSlideshow HTML
     *
     * @param array $data   MagicSlideshow items data
     * @param array $params Additional params
     *
     * @return string
     */
    public function getMainTemplate($data, $params = array())
    {
        $id = '';
        $width = '';
        $height = '';

        $html = array();

        extract($params);

        if (empty($width)) {
            $width = '';
        } else {
            $width = " width=\"{$width}\"";
        }
        if (empty($height)) {
            $height = '';
        } else {
            $height = " height=\"{$height}\"";
        }

        if (empty($id)) {
            $id = '';
        } else {
            $id = ' id="'.addslashes($id).'"';
        }

        $options = '';
        if ($selectorsSize = $this->params->getParam('selectors-size'/*, '', true*/)) {
            if (!isset($selectorsSize['scope']) || $selectorsSize['scope'] != 'magicslideshow') {
                $selectorsSize = $this->params->getValue('selectors-size');
                $options = "selectors-size:{$selectorsSize};";
            }
        } else {
            if ($this->params->checkValue('selectors', array('bottom', 'top'))) {
                $selectorsSize = $this->params->getValue('selector-max-height');
                if (empty($selectorsSize)) {
                    $selectorsSize = 70;
                }
            } elseif ($this->params->checkValue('selectors', array('right', 'left'))) {
                $selectorsSize = $this->params->getValue('selector-max-width');
                if (empty($selectorsSize)) {
                    $selectorsSize = 70;
                }
            } else {
                $selectorsSize = 70;
            }
            $options = "selectors-size:{$selectorsSize};";
        }

        //NOTE: get personal options
        $options .= $this->params->serialize();
        if (empty($options)) {
            $options = '';
        } else {
            $options = ' data-options="'.$options.'"';
        }

        $html[] = '<div'.$id.' class="MagicSlideshow"'.$width.$height.$options.'>';

        foreach ($data as $item) {

            $img = '';//main image
            $img2x = '';//main 2x image
            $thumb = '';//thumbnail image
            $fullscreen = '';//image shown in Full Screen
            $link = '';
            $target = '';
            $alt = '';
            $title = '';
            $description = '';
            $width = '';
            $height = '';
            $content = '';

            extract($item);

            if (empty($link)) {
                $link = '';
            } else {
                if (empty($target)) {
                    $target = '';
                } else {
                    $target = ' target="'.$target.'"';
                }
                $link = $target.' href="'.addslashes($link).'"';
            }

            if (empty($alt)) {
                $alt = '';
            } else {
                $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
            }

            if (empty($title)) {
                $caption = $title = '';
            } else {
                $caption = $title;
                $title = htmlspecialchars(htmlspecialchars_decode($title, ENT_QUOTES));
                if (empty($alt)) {
                    $alt = $title;
                }
                $title = " title=\"{$title}\"";
            }

            if (empty($description)) {
                $description = '';
            } else {
                $description = preg_replace('#<(/?)a([^>]*+)>#is', '[$1a$2]', $description);
                $description = str_replace('"', '&quot;', $description);
            }

            if (empty($width)) {
                $width = '';
            } else {
                $width = " width=\"{$width}\"";
            }
            if (empty($height)) {
                $height = '';
            } else {
                $height = " height=\"{$height}\"";
            }

            if (!empty($content)) {
                $mssCaption = '';//NOTE: caption is displayed under title
                if (empty($thumb)) {
                    $thumb = '';
                    $mssThumbnail = "<div data-mss-thumbnail>{$alt}</div>";
                } else {
                    $thumb = ' data-thumb-image="'.$thumb.'"';
                    $mssThumbnail = '';
                }
                $html[] = "<div class=\"mss-content-container\"{$title}{$thumb}>{$mssThumbnail}{$mssCaption}{$content}</div>";
            } elseif (empty($img)) {
                if (empty($caption)) {
                    $html[] = "<div>{$description}</div>";
                } else {
                    //data-out-move=\"fade\"
                    $html[] = "<div><div data-mss-caption>{$caption}</div><div data-mss-thumbnail>{$description}</div></div>";
                }
            } else {
                if (empty($thumb)) {
                    $thumb = $img;
                }
                if (empty($fullscreen)) {
                    $fullscreen = $img;
                }
                $img = $this->params->checkValue('preload', 'Yes') ? ' src="'.$img.'"' : ' data-image="'.$img.'"';
                if (!empty($img2x)) {
                    //$img .= ' srcset="'.$img2x.' 2x" ';
                    //$img .= ' srcset="'.$img.' 1x, '.$img2x.' 2x" ';
                    $img .= ' srcset="'.str_replace(' ', '%20', $img).' 1x, '.str_replace(' ', '%20', $img2x).' 2x"';
                }
                $thumb = ' data-thumb-image="'.$thumb.'"';
                $fullscreen = ' data-fullscreen-image="'.$fullscreen.'"';
                if (!empty($description)) {
                    $description = " data-caption=\"{$description}\"";
                }
                $html[] = "<a{$link}><img{$width}{$height}{$img}{$thumb}{$fullscreen}{$title}{$description} alt=\"{$alt}\" /></a>";
            }

        }

        $html[] = '</div>';

        if ($this->params->checkValue('show-message', 'Yes')) {
            $html[] = '<div class="MagicToolboxMessage">'.$this->params->getValue('message').'</div>';
        }

        return implode('', $html);
    }

    /**
     * Method to load defaults options
     *
     * @return void
     */
    public function loadDefaults()
    {
        $params = array(
            "enable-effect"=>array("id"=>"enable-effect","group"=>"General","order"=>"10","default"=>"Yes","label"=>"Enable Magic Slideshow™","type"=>"array","subType"=>"select","values"=>array("Yes","No"),"scope"=>"module"),
            "block-title"=>array("id"=>"block-title","group"=>"General","order"=>"20","default"=>"","label"=>"Block title","type"=>"text","scope"=>"module"),
            "include-headers-on-all-pages"=>array("id"=>"include-headers-on-all-pages","group"=>"General","order"=>"21","default"=>"No","label"=>"Include headers on all pages","description"=>"To be able to apply an effect on any page","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module"),
            "thumb-max-width"=>array("id"=>"thumb-max-width","group"=>"Positioning and Geometry","order"=>"10","default"=>"450","label"=>"Maximum width of slideshow images (in pixels)","type"=>"num","scope"=>"module"),
            "thumb-max-height"=>array("id"=>"thumb-max-height","group"=>"Positioning and Geometry","order"=>"11","default"=>"450","label"=>"Maximum height of slideshow images (in pixels)","type"=>"num","scope"=>"module"),
            "selector-max-width"=>array("id"=>"selector-max-width","group"=>"Positioning and Geometry","order"=>"12","default"=>"56","label"=>"Maximum width of thumbnails (in pixels)","type"=>"num","scope"=>"module"),
            "selector-max-height"=>array("id"=>"selector-max-height","group"=>"Positioning and Geometry","order"=>"13","default"=>"56","label"=>"Maximum height of thumbnails (in pixels)","type"=>"num","scope"=>"module"),
            "square-images"=>array("id"=>"square-images","group"=>"Positioning and Geometry","order"=>"40","default"=>"No","label"=>"Always create square images","description"=>"","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module"),
            "width"=>array("id"=>"width","group"=>"Common settings","order"=>"10","default"=>"auto","label"=>"Slideshow width","description"=>"auto | pixels | percentage","type"=>"text","scope"=>"magicslideshow"),
            "height"=>array("id"=>"height","group"=>"Common settings","order"=>"20","default"=>"auto","label"=>"Slideshow height","description"=>"auto | responsive | pixels | percentage","type"=>"text","scope"=>"magicslideshow"),
            "orientation"=>array("id"=>"orientation","group"=>"Common settings","order"=>"30","default"=>"horizontal","label"=>"Slideshow direction","description"=>"vertical (up/down) / horizontal (right/left)","type"=>"array","subType"=>"radio","values"=>array("horizontal","vertical"),"scope"=>"magicslideshow"),
            "arrows"=>array("id"=>"arrows","group"=>"Common settings","order"=>"40","default"=>"No","label"=>"Show navigation arrows","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "loop"=>array("id"=>"loop","group"=>"Common settings","order"=>"45","default"=>"Yes","label"=>"Repeat slideshow after last slide","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "effect"=>array("id"=>"effect","group"=>"Common settings","order"=>"50","default"=>"slide","label"=>"Slide change effect","type"=>"array","subType"=>"select","values"=>array("slide","fade","fade-up","fade-down","dissolve","scroll","cube","bars3d","slide-in","slide-out","flip","blinds3d","slide-change","diffusion","blocks","random"),"scope"=>"magicslideshow"),
            "effect-speed"=>array("id"=>"effect-speed","group"=>"Common settings","order"=>"60","default"=>"600","label"=>"Slide-in duration (milliseconds)","description"=>"e.g. 0 = instant; 600 = 0.6 seconds","type"=>"num","scope"=>"magicslideshow"),
            "effect-easing"=>array("id"=>"effect-easing","group"=>"Common settings","order"=>"70","advanced"=>"1","default"=>"ease","label"=>"CSS3 Animation Easing","description"=>"ease | ease-in | ease-out | ease-in-out | linear | step-start | step-end | steps(n, start | end) | cubic-bezier(n, n, n, n)","type"=>"text","scope"=>"magicslideshow"),
            "autoplay"=>array("id"=>"autoplay","group"=>"Autoplay","order"=>"10","default"=>"Yes","label"=>"Autoplay slideshow","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "slide-duration"=>array("id"=>"slide-duration","group"=>"Autoplay","order"=>"20","default"=>"6000","label"=>"Display duration (milliseconds)","description"=>"e.g. 3000 = 3 seconds","type"=>"num","scope"=>"magicslideshow"),
            "shuffle"=>array("id"=>"shuffle","group"=>"Autoplay","order"=>"30","default"=>"No","label"=>"Shuffle order of slides","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "kenburns"=>array("id"=>"kenburns","group"=>"Autoplay","order"=>"40","default"=>"No","label"=>"Use Ken Burns effect","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "pause"=>array("id"=>"pause","group"=>"Autoplay","order"=>"50","default"=>"Yes","label"=>"Pause autoplay after click or hover","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "selectors-style"=>array("id"=>"selectors-style","group"=>"Selectors","order"=>"10","default"=>"bullets","label"=>"Selectors style","type"=>"array","subType"=>"radio","values"=>array("bullets","thumbnails"),"scope"=>"magicslideshow"),
            "selectors"=>array("id"=>"selectors","group"=>"Selectors","order"=>"20","default"=>"none","label"=>"Selectors position","type"=>"array","subType"=>"radio","values"=>array("bottom","top","right","left","none"),"scope"=>"magicslideshow"),
            "selectors-eye"=>array("id"=>"selectors-eye","group"=>"Selectors","order"=>"40","default"=>"Yes","label"=>"Highlight thumbnail when selected","description"=>"only available when 'selectors style' is set to thumbnails","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "bullets-preview"=>array("id"=>"bullets-preview","group"=>"Selectors","order"=>"45","default"=>"top","label"=>"Show tooltip on bullets","description"=>"","type"=>"array","subType"=>"radio","values"=>array("top","bottom","none"),"scope"=>"magicslideshow"),
            "selectors-fill"=>array("id"=>"selectors-fill","group"=>"Selectors","order"=>"50","default"=>"No","label"=>"Fit thumbnails","description"=>"","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "caption"=>array("id"=>"caption","group"=>"Caption","order"=>"10","default"=>"No","label"=>"Add caption under each image","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "fullscreen"=>array("id"=>"fullscreen","group"=>"Other settings","order"=>"10","default"=>"No","label"=>"Enable full-screen version of slideshow","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "preload"=>array("id"=>"preload","group"=>"Other settings","order"=>"20","default"=>"Yes","label"=>"Load images","description"=>"on page load / on demand","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "keyboard"=>array("id"=>"keyboard","advanced"=>"1","group"=>"Other settings","order"=>"30","default"=>"Yes","label"=>"Use keyboard arrows to move between slides","description"=>"always enabled in Full Screen mode","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "show-loader"=>array("id"=>"show-loader","group"=>"Other settings","order"=>"40","advanced"=>"1","default"=>"Yes","label"=>"Show loading progress bar","description"=>"","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "autostart"=>array("id"=>"autostart","advanced"=>"1","group"=>"Other settings","order"=>"50","default"=>"Yes","label"=>"Start Initialization on page load","description"=>"","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"magicslideshow"),
            "link-to-product-page"=>array("id"=>"link-to-product-page","group"=>"Miscellaneous","order"=>"30","default"=>"Yes","label"=>"Link enlarged image to the product page","type"=>"array","subType"=>"select","values"=>array("Yes","No"),"scope"=>"module"),
            "show-message"=>array("id"=>"show-message","group"=>"Miscellaneous","order"=>"200","default"=>"No","label"=>"Show message under slideshow","type"=>"array","subType"=>"radio","values"=>array("Yes","No"),"scope"=>"module"),
            "message"=>array("id"=>"message","group"=>"Miscellaneous","order"=>"210","default"=>"","label"=>"Enter message to appear under slideshow","type"=>"text","scope"=>"module")
        );
        $this->params->appendParams($params);
    }
}
}
