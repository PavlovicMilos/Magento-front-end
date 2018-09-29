<?php

    if(!function_exists('file_put_contents')) {
        function file_put_contents($filename, $data) {
            $fp = fopen($filename, 'w+');
            if ($fp) {
                fwrite($fp, $data);
                fclose($fp);
            }
        }
    }

    define('MAGICTOOLBOX_INSTALLER_PARENT_FOLDER', dirname(dirname(__FILE__)));

    class MagicToolboxCoreInstallerClass {

        var $errors = array();
        var $status = array('stopped', '');
        var $dir = './..';
        var $modDir = './module';
        var $backupSufix = '~backup~created~by~magictoolbox~team';
        var $backups = array();
        var $resDir = '';//NOTE: www path to resources (relative to the root folder)
        var $installMode = '';
        var $logEnabled = true;
        var $logFileDir = MAGICTOOLBOX_INSTALLER_PARENT_FOLDER;

        function __construct() {

        }

        function log($message) {

            if(!$this->logEnabled) return;

            //NOTE: make relative paths
            $message = str_replace($this->dir.'/', '', $message);

            $logFile = $this->logFileDir."/magicslideshow_install.log";

            $chmod = false;
            if(!file_exists($logFile)) {
                $chmod = true;
            }

            $fhandle = fopen($logFile, "a+b");
            fwrite($fhandle, $message."\n");
            fclose($fhandle);

            if($chmod) {
                @chmod($logFile, 0777);
            }

        }

        function setError($messages, $prefix = '') {
            if(!is_array($messages)) {
                $messages = array($messages);
            }
            foreach($messages as $message) {
                $this->errors[] = $prefix . $message;
            }
        }

        function getErrors($html = true) {
            return implode($html ? '<br />' : "\n\r", $this->errors);
        }

        function setStatus($status, $subStatus = '') {
            $this->status = array($status, $subStatus);
        }

        function getStatus($sub = false) {
            return $this->status[$sub?1:0];
        }

        function checkStatus() {
            $status = $this->getStatus();
            if($status == 'done') {
                return true;
            } else {
                return false;
            }
        }

        function setBackups() {
            if(empty($this->backups)) return;
            $this->setError('Installer has modified following Magento files:');
            $this->setError(array_keys($this->backups), '&nbsp;&nbsp;&nbsp;-&nbsp;');
            $this->setError('&nbsp;');
            $this->setError('&nbsp;');
            $this->setError('Installer has created backups for all modified files with \'' . $this->backupSufix . '\' suffix in the name:');
            $this->setError($this->backups, '&nbsp;&nbsp;&nbsp;-&nbsp;');
        }

        function run($uninstall = false, $upgrade = false) {
            sleep(2);
            $this->installMode = $uninstall?'uninstall':($upgrade?'upgrade':'install');
            if($this->init() && $this->check()) {
                if($uninstall || $this->backup()) {
                    if($uninstall && $this->uninstall() || $upgrade && $this->_upgrade() || !$uninstall && !$upgrade && $this->install()) {
                        $this->setStatus('done');
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        function init() {
            $this->setStatus('init');
            return true;
        }

        function check() {
            $this->setStatus('check');
            if($this->checkPlace() && $this->prepare()) {
                $canContinue = true;
                if($this->logEnabled && $this->installMode == 'install') {
                    if(!$this->isWriteable($this->logFileDir)) {/*is_writeable*/
                        $this->setError('This installer needs write access to create a log file of the installation process.');
                        $this->setError('Please check write access to the following folder:');
                        $this->setError($this->logFileDir, '&nbsp;&nbsp;&nbsp;-&nbsp;');
                        $canContinue = false;
                    } else if(file_exists($this->logFileDir."/magicslideshow_install.log")) {
                        $this->setError('Installer was detected the log file from a previous installation.');
                        $this->setError('Possible the module was not successfully uninstalled the last time.');
                        $this->setError('Make sure that the module was properly uninstalled.');
                        $this->setError('&nbsp;');
                        $canContinue = false;
                    }
                }
                return $this->checkPerm() && $canContinue;
            } else {
                return false;
            }
        }

        function checkPlace() {
            $this->setStatus('check', 'place');
            return true;
        }

        function prepare() {
            $this->setStatus('check', 'prepare');
            return true;
        }

        function isModuleInstalled() {
            $this->setStatus('check', 'module');
            return false;
        }

        function checkPerm() {
            $this->setStatus('check', 'perm');
            return true;
        }

        function backup() {
            $this->setStatus('backup');
            if($this->backupFiles()) {
                return $this->backupDB();
            } else {
                return false;
            }
        }

        function backupFiles() {
            $this->setStatus('backup', 'files');
            return true;
        }

        function restoreStep_backupFiles() {
            return true;
        }

        function backupDB() {
            $this->setStatus('backup', 'DB');
            return true;
        }

        function restoreStep_backupDB() {
            return true;
        }

        function install() {
            $this->setStatus('install');
            $this->sendStat('install');
            if($this->installFiles()) {
                return $this->installDB();
            } else {
                return false;
            }
        }

        function installFiles() {
            $this->setStatus('install', 'files');
            return true;
        }

        function restoreStep_installFiles() {
            return true;
        }

        function installDB() {
            $this->setStatus('install', 'DB');
            return true;
        }

        function restoreStep_installDB() {
            return true;
        }

        function uninstall() {
            if(!$this->isModuleInstalled()) {
                $this->setError('This installer can\'t uninstall module! It seems that the module is not installed.');
                return false;
            }
            $this->setStatus('install', 'DB');
            $this->sendStat('uninstall');
            $this->restore();
            $this->setError('Module was uninstalled!');
            return true;
        }

        function restore() {
            switch($this->getStatus()) {
                case 'install':
                    switch($this->getStatus(true)) {
                        case 'DB':
                            $this->restoreStep_installDB();
                        case 'files':
                            if($this->logEnabled && file_exists($this->logFileDir."/magicslideshow_install.log")) {
                                $this->uninstall_from_logFile();
                            } else {
                                $this->restoreStep_installFiles();
                            }
                        default: break;
                    }
                case 'backup':
                    switch($this->getStatus(true)) {
                        case 'DB':
                            $this->restoreStep_backupDB();
                        case 'files':
                            $this->restoreStep_backupFiles();
                        default: break;
                    }
                case 'check':
                case 'init':
                case 'stopped':
                default: break;
            }

            $this->setStatus('stopped');
            return true;
        }

        function _upgrade() {
            $this->setStatus('upgrade');
            // here we need to unzip file and upload it
            $zipFile = $_FILES['zipFile']['tmp_name'];

            //NOTE: old code not work with new zip files
            /*
            require_once('zip.class.php');
            $zipFileClass = new zipFile();
            $filesDataOrig = $zipFileClass->read_zip($zipFile);
            $filesData = array();
            foreach($filesDataOrig as $f) {
                $filesData[$f['name']] = $f['data'];
            }
            unset($filesDataOrig);
            */

            if(!extension_loaded('zip')) {
                @dl((strtolower(substr(PHP_OS, 0, 3)) == 'win') ? 'php_zip.dll' : 'zip.so');
            }
            if(extension_loaded('zip')) {
                $zip = zip_open($zipFile);
                $filesData = array();
                if($zip) {
                    while ($zip_entry = zip_read($zip)) {
                        $fileName = basename(zip_entry_name($zip_entry));
                        if (zip_entry_open($zip, $zip_entry, "r")) {
                            $filesData[$fileName] = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                            zip_entry_close($zip_entry);
                        }
                    }
                    zip_close($zip);
                }
            } else {
                return false;
            }

            $files = array();
            switch('MagicSlideshow') {
                //case 'MagicMagnify':
                //case 'MagicMagnifyPlus':
                //    $files['magicslideshow.swf'] = $filesData['magicslideshow.swf'];
                default:
                    $files['magicslideshow.js'] = $filesData['magicslideshow.js'];
                    break;
            }
            unset($filesData);
            if($this->upgrade($files)) {
                header('Location: congratulations.html');
            } else {
                return false;
            }
        }

        function upgrade($files) {
            return true;
        }

        /*function done() {
            // echo pix.gif image (we need to use ajax....)
            header("Content-type: image/gif");
            die(base64_decode('R0lGODlhAQABAIAAACqk1AAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='));
        }*/

        function checkFilesPerm($files, $perm = 'write') {
            if(!is_array($files)) {
                $files = array($files);
            }
            //$perm = intval($perm);
            $wrang = array();
            foreach($files as $file) {
                //if(intval(substr(decoct(fileperms($this->dir . $file)), -3)) < $perm) {
                    //$wrang[] = $file;
                //}
                if($perm == 'write' && !is_writeable($this->dir . $file) || $perm == 'read' && !is_readable($this->dir . $file)) {
                    $wrang[] = $file;
                }
            }
            return array(empty($wrang)?true:false, $wrang);
        }

        function removeFiles($files) {
            if(!is_array($files)) {
                $files = array($files);
            }
            foreach($files as $file) {
                $fileName = $this->dir . $file;
                @unlink($fileName);
            }
            return true;
        }

        function createBackups($files, $overwrite = false) {
            if(!is_array($files)) {
                $files = array($files);
            }
            $wrang = array();
            foreach($files as $file) {
                $file = $this->dir . $file;
                $info = pathinfo($file);
                if(intval(phpversion()) < 5 || !isset($info["filename"])) {
                    //$info["filename"] = basename($info["basename"], ".".$info["extension"]);
                    $info["filename"] = preg_replace("/\." . preg_quote($info["extension"]) . "$/is", "", $info["basename"]);
                }
                $backupFileName = $info['dirname'] . '/' . $info['filename'] . $this->backupSufix . '.' . $info['extension'];
                if(!file_exists($backupFileName) || $overwrite) {
                    if(!copy($file, $backupFileName)) {
                        $wrang[] = $file;
                    } else {
                        $this->backups[$file] = $backupFileName;
                        $this->log('CREATE BACKUP '.$backupFileName.' FOR '.$file);
                    }
                } else {
                    $this->backups[$file] = $backupFileName;
                }
            }
            return array(empty($wrang)?true:false, $wrang);
        }

        function removeBackups($files) {
            if(!is_array($files)) {
                $files = array($files);
            }
            foreach($files as $file) {
                $file = $this->dir . $file;
                $info = pathinfo($file);
                if(intval(phpversion()) < 5 || !isset($info["filename"])) {
                    //$info["filename"] = basename($info["basename"], ".".$info["extension"]);
                    $info["filename"] = preg_replace("/\." . preg_quote($info["extension"]) . "$/is", "", $info["basename"]);
                }
                $backupFileName = $info['dirname'] . '/' . $info['filename'] . $this->backupSufix . '.' . $info['extension'];
                @unlink($backupFileName);
            }
            return true;
        }

        function restoreFromBackups($files) {
            if(!is_array($files)) {
                $files = array($files);
            }
            foreach($files as $file) {
                $file = $this->dir . $file;
                $info = pathinfo($file);
                if(intval(phpversion()) < 5 || !isset($info["filename"])) {
                    //$info["filename"] = basename($info["basename"], ".".$info["extension"]);
                    $info["filename"] = preg_replace("/\." . preg_quote($info["extension"]) . "$/is", "", $info["basename"]);
                }
                $backupFileName = $info['dirname'] . '/' . $info['filename'] . $this->backupSufix . '.' . $info['extension'];
                if(file_exists($backupFileName)) {
                    @unlink($file);
                    @copy($backupFileName, $file);
                }
            }
            return true;
        }

        function copyDir($src, $dest, $perm = 0755, $overwrite = true) {
            if(!is_dir($dest)) {
                if(mkdir($dest)) {
                    $this->log('CREATE DIR '.$dest);
                    @chmod($dest, $perm);
                }
            }
            if($dir = @opendir($src)) {
                while (($file = readdir($dir))!==false) {
                    if($file == '.' || $file == '..') {
                        continue;
                    }
                    if(is_dir($src . '/' . $file)) {
                        $this->copyDir($src . '/' . $file, $dest . '/' . $file, $perm, $overwrite);
                    } else {
                        if($file == 'magicslideshow.settings.dat') {
                            $file = 'magicslideshow.settings.ini';
                            $fileExists = file_exists($dest.'/'.$file);
                            if($fileExists && !$overwrite) continue;
                            if(copy($src . '/magicslideshow.settings.dat', $dest . '/' . $file)) {
                                if(!$fileExists) $this->log('CREATE FILE '.$dest.'/'.$file);
                            }
                        } else if($this->resDir != '' && preg_match('#\.css$#i', $file)) {
                            $fileExists = file_exists($dest.'/'.$file);
                            if($fileExists && !$overwrite) continue;
                            if(copy($src.'/'.$file, $dest.'/'.$file)) {
                                //NOTE: fix url's in css files
                                $fileDir = str_replace('//', '/', str_replace('\\', '/', $dest));
                                $matches = array();
                                if(preg_match('#'.$this->resDir.'(.*+)$#i', $fileDir, $matches)) {
                                    $resDir = $this->resDir.$matches[1];
                                    $fileContents = file_get_contents($dest.'/'.$file);
                                    $pattern = '#url\(\s*(\'|")?(?!data:|mhtml:|http(?:s)?:|/)([^\)\s\'"]+?)(?(1)\1)\s*\)#is';
                                    $replace = 'url($1'.$resDir.'/$2$1)';
                                    $fixedFileContents = preg_replace($pattern, $replace, $fileContents);
                                    if($fixedFileContents != $fileContents) {
                                        file_put_contents($dest.'/'.$file, $fixedFileContents);
                                        /*
                                        $fp = fopen($dest.'/'.$file, 'w+');
                                        if($fp) {
                                            fwrite($fp, $fixedFileContents);
                                            fclose($fp);
                                        }
                                        /**/
                                    }
                                }
                                if(!$fileExists) $this->log('CREATE FILE '.$dest.'/'.$file);
                            }
                        } else {
                            $fileExists = file_exists($dest.'/'.$file);
                            if($fileExists && !$overwrite) continue;
                            if(copy($src . '/' . $file, $dest . '/' . $file)) {
                                if(!$fileExists) $this->log('CREATE FILE '.$dest.'/'.$file);
                            }
                        }
                        @chmod($dest . '/' . $file, $perm);
                        if(preg_match('/\.(settings\.ini)|(js)|(css)|(swf)$/is', $file)) {
                            @chmod($dest . '/' . $file, 0777);
                        }
                    }
                }
                closedir($dir);
            }
        }

        function copyFile($src, $dest, $perm = 0755, $overwrite = true) {
            $fileExists = file_exists($dest);
            if($fileExists && !$overwrite) {
                return true;
            }
            if(file_exists($src)) {
                $newDir = preg_replace('/^(.*?)\/[^\/]+\/?$/is', '$1', $dest);
                if(!is_dir($newDir)) {
                    if(!$this->createDirRecursive($newDir, $perm)) {
                        return false;
                    }
                }
                @copy($src, $dest);
                @chmod($dest, $perm);
                if(file_exists($dest)) {
                    if(!$fileExists) $this->log('CREATE FILE '.$dest);
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        function createDirRecursive($dir, $perm = 0755) {
            if(!is_dir($dir)) {
                $this->createDirRecursive(preg_replace('/^(.*?)\/[^\/]+\/?$/is', '$1', $dir), $perm);
                @mkdir($dir);
                if(is_dir($dir)) {
                    $this->log('CREATE DIR '.$dir);
                }
                @chmod($dir, $perm);
            }
            if(!is_dir($dir)) {
                return false;
            }
            return true;
        }

        function removeDir($src) {
            if($dir = @opendir($src)) {
                while (($file = readdir($dir))!==false) {
                    if($file == '.' || $file == '..') {
                        continue;
                    }
                    if(is_dir($src . '/' . $file)) {
                        $this->removeDir($src . '/' . $file);
                    } else {
                        unlink($src . '/' . $file);
                    }
                }
                closedir($dir);
            }
            rmdir($src);
        }

        function cleanUpDir($src, $remove = false) {
            if($dir = @opendir($src)) {
                while (($file = readdir($dir))!==false) {
                    if($file == '.' || $file == '..') {
                        continue;
                    }
                    if(is_dir($src . '/' . $file)) {
                        $this->cleanUpDir($src . '/' . $file, true);
                    } else {
                        unlink($src . '/' . $file);
                    }
                }
                closedir($dir);
            }
            if($remove) rmdir($src);
        }

        function isEmptyDir($dir) {
            if($dirH = @opendir($dir)) {
                while($file = readdir($dirH)) {
                    if($file != '.' && $file != '..') {
                        closedir($dirH);
                        return false;
                    }
                }
                closedir($dirH);
                return true;
            }
            else return false; // whatever the reason is : no such dir, not a dir, not readable
        }

        function isWriteable($path) {
            if(is_dir($path)) {
                $path = $path.($path[strlen($path)-1] == '/' ? '' : '/').uniqid(mt_rand()).'.tmp';
            }
            $alreadyExisted = file_exists($path);
            $resource = @fopen($path, 'a');
            if($resource === false) {
                return false;
            }
            fclose($resource);
            if(!$alreadyExisted) unlink($path);
            return true;
        }

        function uninstall_from_logFile($exclude = array()) {
            $errorLevel = 0;
            $lines = file($this->logFileDir."/magicslideshow_install.log", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if($lines == false) return false;
            $lines = array_reverse($lines);
            foreach($lines as $num => $line) {
                $matches = array();
                preg_match('/^([A-Z]+)\s+([A-Z]+)\s+(.+)$/', $line, $matches);
                switch($matches[1]) {
                    case 'CREATE':
                        switch($matches[2]) {
                            case 'FILE':
                                if(file_exists($this->dir.'/'.$matches[3]) && !in_array($matches[3], $exclude)) {
                                    if(!unlink($this->dir.'/'.$matches[3])) {
                                        $errorLevel = 2;
                                        $this->setError('Installer can\'t delete file: '.$this->dir.'/'.$matches[3]);
                                    }
                                }
                            break;
                            case 'DIR':
                                if(file_exists($this->dir.'/'.$matches[3]) && !in_array($matches[3], $exclude)) {
                                    if(!$this->isEmptyDir($this->dir.'/'.$matches[3])) {
                                        //if(preg_match('/^magic(?:toolbox|zoom(?:plus)?|thumb|360(?:plus)?|scroll|slideshow)$/i', basename($matches[3]))) {
                                        if(preg_match('/^magicslideshow$/i', basename($matches[3]))) {
                                            $this->removeDir($this->dir.'/'.$matches[3]);
                                        } else {
                                            $errorLevel = 1;
                                            $this->setError('Installer can\'t delete directory: '.$this->dir.'/'.$matches[3]);
                                            $this->setError('Possible contents of the folder has been changed since the module was installed.');
                                        }
                                    } else if(!rmdir($this->dir.'/'.$matches[3])) {
                                        $errorLevel = 2;
                                        $this->setError('Installer can\'t delete directory: '.$this->dir.'/'.$matches[3]);
                                    }
                                }
                            break;
                            case 'BACKUP':
                                $params = explode(' FOR ', $matches[3]/*, 2*/);
                                if(file_exists($this->dir.'/'.$params[0]) && !in_array($params[1], $exclude)) {
                                    if(!rename($this->dir.'/'.$params[0], $this->dir.'/'.$params[1])) {
                                        $errorLevel = 2;
                                        $this->setError('Installer can\'t restore file from backup: '.$this->dir.'/'.$params[1]);
                                    }
                                }
                            break;
                            default: //unknown command
                                $errorLevel = 2;
                                $this->setError('Wrong file format. The file magicslideshow_install.log maybe was corrupted.');
                        }
                    break;
                    case 'CACHE':
                        switch($matches[2]) {
                            case 'CLEANUP':
                                //$this->log('CACHE CLEANUP '.$this->dir.'/tmp/oxpec_menu_en_xml.txt');
                                if(file_exists($this->dir.'/'.$matches[3]) && !in_array($matches[3], $exclude)) {
                                    if(is_dir($this->dir.'/'.$matches[3])) {
                                        $this->cleanUpDir($this->dir.'/'.$matches[3]);
                                        if(!$this->isEmptyDir($this->dir.'/'.$matches[3])) {
                                            $this->setError('Installer can\'t cleanup cache folder: '.$this->dir.'/'.$matches[3]);
                                        }
                                    } else {
                                        if(!unlink($this->dir.'/'.$matches[3])) {
                                            $errorLevel = 2;
                                            $this->setError('Installer can\'t delete file in cache: '.$this->dir.'/'.$matches[3]);
                                        }
                                    }
                                }
                        }
                    break;
                    default: //unknown command
                        $errorLevel = 2;
                        $this->setError('Wrong file format. The file magicslideshow_install.log maybe was corrupted.');
                }
            }
            if($errorLevel < 2) unlink($this->logFileDir."/magicslideshow_install.log");
            return $errorLevel ? false : true;
        }

        function sendStat($mode = '') {

            //NOTE: don't send from working copy
            if('working' == 'v4.15.9' || 'working' == 'v3.1.15') {
                return;
            }

            $hostname = 'www.magictoolbox.com';
            $url = $_SERVER['HTTP_HOST'].preg_replace('/\/\magicslideshow\/installer\.php.*?$/i', '', $_SERVER['REQUEST_URI']);
            $url = urlencode(urldecode($url));
            $platformVersion = $this->getPlatformVersion();
            $path = "api/stat/?action={$mode}&tool_name=magicslideshow&license=trial&tool_version=v3.1.15&module_version=v4.15.9&platform_name=magento&platform_version={$platformVersion}&url={$url}";
            $handle = @fsockopen('ssl://' . $hostname, 443, $errno, $errstr, 30);
            if($handle) {
                $headers  = "GET /{$path} HTTP/1.1\r\n";
                $headers .= "Host: {$hostname}\r\n";
                $headers .= "Connection: Close\r\n\r\n";
                fwrite($handle, $headers);
                fclose($handle);
            }
        }

        function getPlatformVersion() {
            return '';
        }

    }
