<?php
/*
         M""""""""`M            dP                     
         Mmmmmm   .M            88                     
         MMMMP  .MMM  dP    dP  88  .dP   .d8888b.     
         MMP  .MMMMM  88    88  88888"    88'  `88     
         M' .MMMMMMM  88.  .88  88  `8b.  88.  .88     
         M         M  `88888P'  dP   `YP  `88888P'     
         MMMMMMMMMMM  [    -*- Magebay.com -*-   ]      
                                                       
         * * * * * * * * * * * * * * * * * * * * *     
         * -    - -    M.A.G.E.B.A.Y    - -    - *     
         * -  Copyright © 2010 - 2016 Magebay  - *     
         *    -  -  All Rights Reserved  -  -    *     
         * * * * * * * * * * * * * * * * * * * * *     
                                                     
*//**
 * --*--*--*--*--*--*--*--*--*--*--*--*--*--*--*--*-- *
 * @PROJECT    : Super Scan DIRECTORY
 * @AUTHOR     : Zuko
 * @COPYRIGHT  : © 2016 Magebay - Magento Ext Provider
 * @LINK       : https://www.magebay.com/
 * @FILE       : PathHelper.php
 * @CREATED    : 9:21 AM , 08/Nov/2016
 * @DETAIL     :
 * --*--*--*--*--*--*--*--*--*--*--*--*--*--*--*--*-- *
**/


namespace Zuko\Misc;


if(!defined('DS')) define('DS',DIRECTORY_SEPARATOR);
/**
 * Class PathHelper
 * @package Zuko\Misc
 */
class PathHelper {
    /** @var  string */
    private $_rootPath;

    /** @var  string */
    private $_path;

    /**
     * PathHelper constructor.Define applitication root path
     *
     * @param string|null $path
     */
    public function __construct($path = null)
    {
        if($path) $this->setPath($path);
        $this->defineRootPath();
    }
    /**
     * Define application root path by using getcwd() function
     */
    public function defineRootPath()
    {
        $cwd = getcwd();
        $this->_rootPath = $cwd;
    }

    /**
     * @return string
     */
    public function getRootPath()
    {
        if(!$this->_rootPath) $this->defineRootPath();
        return $this->_rootPath;
    }

    /**
     * Replace DIRECTORY SPERATOR string with " / " slash
     * @param string $path
     * @return string
     */
    public function toUnixDS($path)
    {
        return str_replace(DS,'/',$path);
    }
    /**
     * Replace " / " slash in given string with DIRECTORY SPERATOR
     * @param string $path
     * @return string
     */
    public function toOrginalDS($path)
    {
        return str_replace('/',DS,$path);
    }

    /**
     * Check the given path is able to write. If not exits try create first
     * @param string $dirPath
     * @return bool
     * @throws \Exception
     */
    public function isDirWriteAble($dirPath)
    {
        $dirPath = $this->buildFullPath($dirPath);
        $dirPath = $this->toOrginalDS($dirPath);
        if (!is_dir($dirPath))
            mkdir($dirPath, 0777, true);
        if (!is_dir($dirPath))
            throw new \Exception('Cant create directory : ' . $dirPath . ' .Please check then try again.');
        if (!is_writable($dirPath)) {
            if (!chmod($dirPath, 0775))
                throw new \Exception('Permission Denied.The Destination Path is not Writeable.');
        }
        return true;
    }
    /**
     * Remove root path and first splash from given string
     * @param string $path
     * @return string
     */
    public function buildRelativePath($path)
    {
        $root = $this->getRootPath();
        $path = $this->toOrginalDS($path);
        if($this->isFullPath($path))
            $path = str_replace($root, '', $path);
        if(substr($this->toUnixDS($path),0,1) === '/') $path = substr($path, 1);
        return $this->toUnixDS($path);
    }


    /**
     * Return true if filePath begin with $_rootPath property
     * @param string $filePath
     * @return bool
     */
    public function isFullPath($filePath)
    {
        error_reporting(null);
        $filePath = $this->toUnixDS($filePath);
        $rootPath = $this->toUnixDS($this->getRootPath());
        $tmpPath[] = $this->toUnixDS(realpath($_ENV['TMP']));
        $tmpPath[] = $this->toUnixDS(realpath($_ENV['TMPDIR']));
        $tmpPath[] = $this->toUnixDS(realpath($_ENV['TEMP']));
        $tmpPath[] = $this->toUnixDS(sys_get_temp_dir());
        $tmpPath[] = $this->toUnixDS(ini_get('upload_tmp_dir'));
        $isTmp = false;
        foreach ($tmpPath as $item) {
            if(stripos($filePath,$item) === 0)
            {
                $isTmp = true;
                break;
            }
        }
        if(stripos($filePath,$rootPath) === 0 || $isTmp == true)
            return true;
        return false;
    }

    /**
     * Return full path to file based on given Relative path
     * @param string $relativePath
     * @return string
     */
    public function buildFullPath($relativePath)
    {
        if($this->isFullPath($relativePath)) return $relativePath;
        $relativePath = $this->toUnixDS($relativePath);
        $rootPath = $this->toUnixDS($this->getRootPath());
        $endRoot = substr($rootPath,-1);
        $startRelative = substr($relativePath,0,1);
        if($startRelative !== '/')
        {
            if($endRoot === '/')
                $path = $rootPath . $relativePath;
            else
                $path = $rootPath . '/' . $relativePath;
        }
        else
        {
            if($endRoot === '/')
            {
                $rootPath = substr($rootPath,0,-1);
                $path = $rootPath.$relativePath;
            }
            else
                $path = $rootPath . $relativePath;
        }
        return $this->toOrginalDS($path);
    }
    /**
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->_path = $path;
        return $this;
    }

    /**
     * @param string $rootPath
     * @return PathHelper
     */
    public function setRootPath($rootPath)
    {
        $this->_rootPath = $rootPath;

        return $this;
    }

    /**
     * Used by self::generateUniqueName
     * @return string
     */
    static private function _nextChar()
    {
        return base_convert(mt_rand(0, 35), 10, 36);
    }

    /**
     * Generate Unique 32 chars length string . Useful for rename uploaded files
     * @return string
     */
    static public function generateUniqueName()
    {
        $parts = explode('.', uniqid('', true));

        $id = str_pad(base_convert($parts[0], 16, 2), 56, mt_rand(0, 1), STR_PAD_LEFT)
            . str_pad(base_convert($parts[1], 10, 2), 32, mt_rand(0, 1), STR_PAD_LEFT);
        $id = str_pad($id, strlen($id) + (8 - (strlen($id) % 8)), mt_rand(0, 1), STR_PAD_BOTH);

        $chunks = str_split($id, 8);

        $id = array();
        foreach ($chunks as $key => $chunk) {
            if ($key & 1) {  // odd
                array_unshift($id, $chunk);
            } else {         // even
                array_push($id, $chunk);
            }
        }

        // add random seeds
        $prefix = str_pad(base_convert(mt_rand(), 10, 36), 6, self::_nextChar(), STR_PAD_BOTH);
        $id = str_pad(base_convert(implode($id), 2, 36), 19, self::_nextChar(), STR_PAD_BOTH);
        $suffix = str_pad(base_convert(mt_rand(), 10, 36), 6, self::_nextChar(), STR_PAD_BOTH);

        return $prefix . self::_nextChar() . $id . $suffix;
    }
}