<?php
    /**
     *  MagicToolbox installer
    */

    @ini_set('display_errors', false);
    error_reporting(E_ALL & ~E_NOTICE);

    function magic_json_encode(&$array) {
        $tmpArray = array();
        $result = '{';
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $tmpArray[] = '"'.((string)$key).'":'.encodeArrayToJson($value);
            } else if(is_bool($value)) {
                $tmpArray[] = '"'.((string)$key).'":'.($value?'true':'false');
            } else if(is_string($value)) {
                $tmpArray[] = '"'.((string)$key).'":"'.$value.'"';
            } else if(is_numeric($value)) {
                $tmpArray[] = '"'.((string)$key).'":'.((string)$value);
            }
        }
        $result .= implode(',', $tmpArray);
        $result .= '}';
        return $result;
    }

    function magic_json_decode($json) {
        $json = str_replace(array('\\\\', '\"'), array('&#92;', '&#34;'), $json);
        $parts = preg_split('/("[^"]*")|([\[\]\{\},:])|\s+/is', $json, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $index => $part) {
            switch ($part) {
                case '[':
                case '{':
                    $parts[$index] = 'array(';
                break;
                case ']':
                case '}':
                    $parts[$index] = ')';
                break;
                case ':':
                    $parts[$index] = '=>';
                break;
                case ',':
                break;
                default:
                break;
            }
        }
        $json = str_replace(array('&#92;', '&#34;'), array('\\\\', '\"'), implode('', $parts));
        return eval("return $json;");
    }

    $json_encode = function_exists('json_encode')?'json_encode':'magic_json_encode';
    $json_decode = function_exists('json_decode')?'json_decode':'magic_json_decode';

    $mode = '';
    if(isset($_GET['mode'])) {
        $mode = trim($_GET['mode']);
    }

    if($mode == 'getConfig') {
        $config = array();
        $config['version'] = false;
        if(is_readable(dirname(__FILE__).'/VERSION')) {
            $versionString = file_get_contents(dirname(__FILE__).'/VERSION');
            $version = array();
            $v = 'v([0-9]+(?:\.[0-9]+)*)';
            if(preg_match('/'.$v.'\s*\['.$v.':'.$v.'\]/is', $versionString, $version)) {
                $config['version'] = array(
                    'module' => $version[1],
                    'core' => $version[2],
                    'tool' => $version[3]
                );
            }
        }
        $config['upgrade'] = false;
        $hostname = 'www.magictoolbox.com';
        $path = 'api/platform/magento/version/';
        $response = '';
        $handle = @fsockopen('ssl://' . $hostname, 443, $errno, $errstr, 30);
        if($handle) {
            $headers  = "GET /{$path} HTTP/1.1\r\n";
            $headers .= "Host: {$hostname}\r\n";
            $headers .= "Connection: Close\r\n\r\n";
            fwrite($handle, $headers);
            while(!feof($handle)) {
                $response .= fgets($handle);
            }
            fclose($handle);
            $response = substr($response, strpos($response, "\r\n\r\n") + 4);
            $responseObj = $json_decode($response);
            if(is_object($responseObj) && isset($responseObj->version)) {
                $version = array();
                if(preg_match('/v([0-9]+(?:\.[0-9]+)*)/is', $responseObj->version, $version)) {
                    if($config['version'] && version_compare($config['version']['module'], $version[1], '<')) {
                        $config['upgrade'] = $version[1];
                    }
                }
            }
        }
        echo $json_encode($config);
        return;
    }

    require_once(dirname(__FILE__) . '/magictoolbox.installer.core.class.php');
    require_once(dirname(__FILE__) . '/magictoolbox.installer.magento.class.php');

    $modInstaller = new MagicToolboxmagentoModuleInstallerClass();

    if($mode == 'check') {
        $response = array();
        if($modInstaller->isModuleInstalled()) {
            $response['isModuleInstalled'] = true;
        } else {
            $response['isModuleInstalled'] = false;
        }
        $response['baseURL'] = $modInstaller->getBaseURL();
        echo $json_encode($response);
        return;
    }

    $uninstall = false;
    $upgrade = false;
    if($mode == 'uninstall') {
        $uninstall = true;
    }
    if($mode == 'upgrade') {
        $upgrade = true;
    }

    if(!$modInstaller->run($uninstall, $upgrade)) {
        echo '[error]';
        echo $modInstaller->getErrors();
        $modInstaller->restore();
    } else {
        echo '[done]';
        $modInstaller->setBackups();
        echo $modInstaller->getErrors();
    }
