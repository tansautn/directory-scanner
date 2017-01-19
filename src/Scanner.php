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
 * @FILE       : Scanner.php
 * @CREATED    : 8:26 AM , 08/Nov/2016
 * @DETAIL     :
 * --*--*--*--*--*--*--*--*--*--*--*--*--*--*--*--*-- *
**/


namespace Zuko\Misc;

/**
 * Class Scanner
 * @package Zuko\Misc
 */
class Scanner {
    /**
     * @var string
     */
    private $_currentPath;
    /**
     * @var \Directory
     */
    private $_dirHandle;
    /**
     * @var \Zuko\Misc\PathHelper
     */
    private $_pathHelper;
    /**
     * @var array
     */
    private $_scanResult;
    /**
     * Callback function , take param as realpath of file
     *
     * @var callable
     */
    private $_fileEntryCallback;

    /**
     * Scanner constructor.
     *
     * @param string $path
     * @param null|array $scanResult
     */
    public function __construct($path,$scanResult = null)
    {
        $this->_currentPath = $path;
        $this->_dirHandle = dir($this->_currentPath);
        $this->_pathHelper = new PathHelper($this->_currentPath);
        if(!$scanResult) $this->_scanResult = [];
        else $this->_scanResult = $scanResult;
    }

    /**
     * @param callable $fileEntryCallback
     * @return Scanner
     */
    public function setFileEntryCallback($fileEntryCallback)
    {
        $this->_fileEntryCallback = $fileEntryCallback;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentPath()
    {
        return $this->_currentPath;
    }

    /**
     * @return \Zuko\Misc\PathHelper
     */
    public function getPathHelper()
    {
        return $this->_pathHelper;
    }

    /**
     * @return array
     */
    public function getScanResult()
    {
        return $this->_scanResult;
    }

    /**
     * Recursive scan dir & file in self::$_currentPath , apply callback if entry is file , return scan result in array
     *
     * @return array
     */
    public function getDirEntries()
    {
        set_time_limit(0);
        while (false !== ($entry = $this->_dirHandle->read())) {
            if($entry != '.' && $entry != '..')
            {
                if(is_dir($this->_currentPath.DIRECTORY_SEPARATOR.$entry))
                {
                    $dirEntry = new self($this->_currentPath.DIRECTORY_SEPARATOR.$entry);
                    if(is_callable($this->_fileEntryCallback)) $dirEntry->setFileEntryCallback($this->_fileEntryCallback);
                    $this->_scanResult[$entry] = $dirEntry->getDirEntries();
                }
                else{
                    if(is_callable($this->_fileEntryCallback))
                    {
                        call_user_func($this->_fileEntryCallback,realpath($this->_currentPath.DIRECTORY_SEPARATOR.$entry));
                    }
                    $this->_scanResult[] = $this->getPathHelper()->buildRelativePath(realpath($this->_currentPath.DIRECTORY_SEPARATOR.$entry));
                }
            }
        }
        $this->_dirHandle->close();
        return $this->_scanResult;
    }
}