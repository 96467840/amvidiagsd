<?php

/**
 * @package         Google Structured Data
 * @version         3.6.4 Free
 *
 * @author          Andrey Murin <96467840@mail.ru>
 * @link            https://www.amvidia.com
 * @copyright       Copyright (c) Amvidia
 * @license         The MIT License (MIT)
 */

defined('_JEXEC') or die('Restricted Access');

// docs
// https://docs.joomla.org/How_to_use_the_filesystem_package
// https://docs.joomla.org/Using_caching_to_speed_up_your_code
// https://docs.joomla.org/Constants

class AmvidiaGSDHelper
{
    /**
     *  Cache for data
     *
     *  @var  dictionary
     */
    private static $cache = [];

    /**
     *  Path to data
     *
     *  @var string
     */
    private static $path = JPATH_ROOT . '/microdata';

    /**
     *  Checks if document is a feed document (xml, rss, atom)
     *
     *  @return  boolean
     */
    public static function isFeed()
    {
        return (
            JFactory::getDocument()->getType() == 'feed'
            || JFactory::getDocument()->getType() == 'xml'
            || JFactory::getApplication()->input->getWord('format') == 'feed'
            || JFactory::getApplication()->input->getWord('type') == 'rss'
            || JFactory::getApplication()->input->getWord('type') == 'atom'
        );
    }

    /**
     *  Get website name
     *
     *  @return  string  Site URL
     */
    public static function getSiteName()
    {
        if (isset(self::$cache['settings'])) return self::$cache['settings']['sitename'];
        
        AmvidiaGSDHelper::ReadSettings();
        
        return self::$cache['settings']['sitename'];
    }
    
    /**
     *  Returns the Site Logo URL
     *
     *  @return  string
     */
    public static function getSiteLogo()
    {
        if (isset(self::$cache['settings'])) return self::$cache['settings']['sitelogo'];
        
        AmvidiaGSDHelper::ReadSettings();
        
        return self::$cache['settings']['sitelogo'];
    }

    /**
     *  Get website URL
     *
     *  @return  string  Site URL
     */
    public static function getSiteURL()
    {
        if (isset(self::$cache['settings'])) return self::$cache['settings']['siteurl'];
        
        AmvidiaGSDHelper::ReadSettings();
        
        return self::$cache['settings']['siteurl'];// . '.new';
    }

    public static function ReadSettings()
    {
    	$data = AmvidiaGSDHelper::ReadMicrodata(self::$path, 'settings');
    	
		if ($data === false)
    	{
    		$data = [];
    	}
    	if (!isset($data['sitename'])) $data['sitename'] = 'Amvidia';
    	if (!isset($data['sitelogo'])) $data['sitelogo'] = 'https://amvidia.com/logo.png';
    	if (!isset($data['siteurl'])) $data['siteurl'] = 'https://amvidia.com/';

    	self::$cache['settings'] = $data;
    }

	public static function ReadMicrodata($path, $name)
	{
		$filename = $path . '/'. $name . '.txt';
		if (JFile::exists($filename))
		{
			$lines = @file($filename);
			$data = [];
			foreach ($lines as $l)
			{
				$tmp = explode(':', $l, 2);
				if (sizeof($tmp) != 2) continue;
				$key = trim($tmp[0]);
				$value = trim($tmp[1]); // на всякий случай тоже будем чистить
				
				if (isset($data[$key])) // а вдруг дубль ключа? но пока забиваем
				{
				}

				$data[$key] = $value;
			}
			return $data;
		}
		return false;
	}


}