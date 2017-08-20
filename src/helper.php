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
use Joomla\Registry\Registry;

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
     *  Get settings param
     *
     *  @return  string  Site URL
     */
    public static function getSetting($name)
    {
        if (isset(self::$cache['settings'])) return self::$cache['settings'][$name];
        
        AmvidiaGSDHelper::ReadSettings();
        
        return self::$cache['settings'][$name];
    }

    /**
     *  Get website name
     *
     *  @return  string  Site URL
     */
    /*public static function getHomeName()
    {
        if (isset(self::$cache['settings'])) return self::$cache['settings']['homename'];
        
        AmvidiaGSDHelper::ReadSettings();
        
        return self::$cache['settings']['homename'];
    }*/

    /**
     *  Get website name
     *
     *  @return  string  Site URL
     */
    /*public static function getSiteName()
    {
        if (isset(self::$cache['settings'])) return self::$cache['settings']['sitename'];
        
        AmvidiaGSDHelper::ReadSettings();
        
        return self::$cache['settings']['sitename'];
    }*/
    
    /**
     *  Returns the Site Logo URL
     *
     *  @return  string
     */
    /*public static function getSiteLogo()
    {
        if (isset(self::$cache['settings'])) return self::$cache['settings']['sitelogo'];
        
        AmvidiaGSDHelper::ReadSettings();
        
        return self::$cache['settings']['sitelogo'];
    }*/

    /**
     *  Get website URL
     *
     *  @return  string  Site URL
     */
    /*public static function getSiteURL()
    {
        if (isset(self::$cache['settings'])) return self::$cache['settings']['siteurl'];
        
        AmvidiaGSDHelper::ReadSettings();
        
        return self::$cache['settings']['siteurl'];// . '.new';
    }*/

    public static function ReadSettings()
    {
    	$data = AmvidiaGSDHelper::ReadMicrodata(self::$path, 'settings');
    	
		if ($data === false)
    	{
    		$data = [];
    	}
    	if (!isset($data['sitename'])) $data['sitename'] = 'Amvidia';
    	if (!isset($data['sitelogo'])) $data['sitelogo'] = 'https://amvidia.com/images/amvidia_logo.png';
    	if (!isset($data['siteurl'])) $data['siteurl'] = 'https://amvidia.com/';
    	if (!isset($data['homename'])) $data['homename'] = $data['sitename']; //'Amvidia main';
    	if (!isset($data['breadcrumbs_enabled'])) $data['breadcrumbs_enabled'] = '1';
    	if (!isset($data['articles_enabled'])) $data['articles_enabled'] = '1';

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
				$l = trim($l);
				if (substr($l, 0, 1) == '#') continue;
				$tmp = explode(':', $l, 2);
				if (sizeof($tmp) != 2) continue;
				$key = str_replace(array('\\', '"'), array('', '\\"'), trim($tmp[0]));
				$value = str_replace(array('\\', '"'), array('\\\\', '\\"'), trim($tmp[1])); // на всякий случай тоже будем чистить

				if (isset($data[$key])) // а вдруг дубль ключа? но пока забиваем
				{
				}

				$data[$key] = $value;
			}
			return $data;
		}
		return false;
	}

    /**
     *  Returns image width and height
     *
     *  @param   string  $image  The URL of the image2wbmp(image)
     *
     *  @return  array
     */
    public static function getImageSize($image)
    {
        if (!ini_get('allow_url_fopen') || !function_exists('getimagesize'))
        {
            return array("width" => 0, "height" => 0);
        }

        $imageSize = $image ? getimagesize($image) : array(0, 0);

        $info["width"]  = $imageSize[0];
        $info["height"] = $imageSize[1];

        return $info;
    }

    /**
     *  Get article's data
     *
     *  @return  array
     */
    public function getArticle()
    {
        // Load current item via model
        $model = JModelLegacy::getInstance('Article', 'ContentModel');
        $item  = $model->getItem();

        // Image
        $image = new Registry($item->images);

        // Array data
        return array(
            "headline"    => $item->title,
            "description" => isset($item->introtext) && !empty($item->introtext) ? $item->introtext : $item->fulltext,
            "image"       => $image->get("image_intro") ?: $image->get("image_fulltext"),
            "created_by"  => $item->created_by,
            "created"     => $item->created,
            "modified"    => $item->modified,
            "publish_up"  => $item->publish_up,
            "ratingValue" => $item->rating,
            "reviewCount" => $item->rating_count
        );
    }

    /**
     *  Returns an array with crumbs
     *
     *  @return  array
     */
    public static function getCrumbs($hometext)
    {
        $pathway = JFactory::getApplication()->getPathway();
        //var_dump($pathway);
        $items   = $pathway->getPathWay();
        //var_dump($items);
        $menu    = JFactory::getApplication()->getMenu();
        $lang    = JFactory::getLanguage();
        $count   = count($items);

        // Look for the home menu
        if (JLanguageMultilang::isEnabled())
        {
            $home = $menu->getDefault($lang->getTag());
        }
        else
        {
            $home = $menu->getDefault();
        }

        if (!$count)
        {
            return false;
        }

        // We don't use $items here as it references JPathway properties directly
        $crumbs = array();

        for ($i = 0; $i < $count; $i++)
        {
            $crumbs[$i]       = new stdClass;
            $crumbs[$i]->name = stripslashes(htmlspecialchars($items[$i]->name, ENT_COMPAT, 'UTF-8'));
            $crumbs[$i]->link = self::route($items[$i]->link);
        }

        // Add Home item
        $item       = new stdClass;
        $item->name = htmlspecialchars($hometext);
        $item->link = self::route('index.php?Itemid=' . $home->id);
        array_unshift($crumbs, $item);

        // Fix last item's missing URL to make Google Markup Tool happy
        end($crumbs);
        if (empty($crumbs->link))
        {
            $crumbs[key($crumbs)]->link = JURI::current();
        }

        return $crumbs;
    }

	/**
     *  Returns URLs based on the Force SSL global configuration
     *
     *  @param   string   $route  The route for which we want a URL
     *  @param   boolean  $xhtml  If we want the output to be in XHTML
     *
     *  @return  string           The absolute url
     */
    public static function route($route, $xhtml = true)
    {
        $siteSSL = JFactory::getConfig()->get('force_ssl');
        $sslFlag = 2;

        // the force_ssl value in the global configuration needs
        // to be 2 for the frontend to also be under HTTPS
        if (($siteSSL == 2) || (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'))
        {
            $sslFlag = 1;
        }
        
        return JRoute::_($route, $xhtml, $sslFlag);
    }

    /**
     *  Determine if the user is viewing the front page
     *
     *  @return  boolean
     */
    public static function isFrontPage()
    {
        $menu = JFactory::getApplication()->getMenu();
        $lang = JFactory::getLanguage()->getTag();
        return ($menu->getActive() == $menu->getDefault($lang));
    }
}