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
    private static function path() { return JPATH_ROOT . '/microdata'; }

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
        if (isset(self::$cache['settings'])) return isset(self::$cache['settings'][$name]) ? self::$cache['settings'][$name] : null;

        self::ReadSettings();
        
        return isset(self::$cache['settings'][$name]) ? self::$cache['settings'][$name] : null;
    }

    public static function ReadSettings()
    {
    	$data = self::ReadMicrodata(self::path(), 'settings');
    	
		if ($data === false)
    	{
    		$data = array();
    	}
    	if (!isset($data['sitename'])) $data['sitename'] = 'Amvidia';
    	if (!isset($data['sitelogo'])) $data['sitelogo'] = 'images/amvidia_logo.png';
    	if (!isset($data['siteurl'])) $data['siteurl'] = 'https://amvidia.com/';
    	if (!isset($data['homename'])) $data['homename'] = $data['sitename']; //'Amvidia main';
    	if (!isset($data['breadcrumbs_enabled'])) $data['breadcrumbs_enabled'] = '0';
    	if (!isset($data['articles_enabled'])) $data['articles_enabled'] = '0';
    	// нет значения по умолчанию
    	//if (!isset($data['articles_defaultauthor'])) $data['articles_defaultauthor'] = '';

    	self::$cache['settings'] = $data;
    }

	public static function ReadMicrodata($path, $name)
	{
		$filename = $path . '/'. $name . '.txt';
		if (JFile::exists($filename))
		{
			$lines = @file($filename);
			$data = array();
			foreach ($lines as $l)
			{
				$l = trim($l);
				if (substr($l, 0, 1) == '#') continue;
				$tmp = explode(':', $l, 2);
				if (sizeof($tmp) != 2) continue;
				$key = str_replace(array('\\', '"'), array('', '\\"'), trim($tmp[0]));
				$value = self::prepareVal($tmp[1]);//str_replace(array('\\', '"'), array('\\\\', '\\"'), trim($tmp[1])); // на всякий случай тоже будем чистить

				if (isset($data[$key])) // а вдруг дубль ключа? делаем массив
				{
                    if (!is_array($data[$key]))
                    {
                        $data[$key] = array($data[$key]);
                    }
                    array_push($data[$key], $value);
				}
                else
                {
                    $data[$key] = $value;
                }
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

        if (!(substr($image, 0, 8) == 'https://' || substr($image, 0, 7) == 'http://')) 
        {
            $imageSize = $image ? getimagesize(JPATH_ROOT . '/' . $image) : array(0, 0);
        }
        else
        {    
            $imageSize = $image ? getimagesize($image) : array(0, 0);
        }

        $info["width"]  = $imageSize[0];
        $info["height"] = $imageSize[1];

        return $info;
    }

    public static function proto()
    {
    	$proto = 'http://';
    	if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    	{
    		$proto = 'https://';
    	}
    	return $proto;
    }

    public static function imageURL($img)
    {
    	if (!$img) return $img;
		if (substr($img, 0, 8) == 'https://') return $img;
		if (substr($img, 0, 7) == 'http://') return $img;
    	return self::proto() . $_SERVER['SERVER_NAME'] . '/' . $img;
    }

    public static function prepareVal($val, $clearHtml = false)
    {
        if ($clearHtml)
        {
            /*echo '<pre>';
            var_dump($val);
            echo '<pre>';/**/
            if (stripos($val, '&lt;?php') !== false) // злоебучие вставки пхп кода
            {
                $val = str_replace(array('&lt;?php', '?&gt;'), array('<?php', '?>'), $val); // strip_tags вырежет html и php теги
            }
        }
    	$res = str_replace(array('\\', '"'), array('\\\\', '\\"'), trim($clearHtml ? strip_tags($val) : $val));
    	$res = preg_replace('/\{[^\}]*\}/', '', $res);
    	return $res;
    }

    public static function getSoftware(&$menu, &$params)
    {
        $overrides = self::ReadMicrodata(self::path() . '/software/', 'm.' . $menu->id);

        /*echo '<pre>';
		var_dump($overrides);
		echo '<pre>';/**/
        if (!$overrides) return;
        $data = array(
	        "contentType" => "Software",
            "name"        => self::prepareVal(isset($overrides['name']) ? $overrides['name'] : ''),
            "os"          => self::prepareVal(isset($overrides['os']) ? $overrides['os'] : ''),
            "category"    => self::prepareVal(isset($overrides['category']) ? $overrides['category'] : ''),
            "ratingvalue" => self::prepareVal(isset($overrides['ratingvalue']) ? $overrides['ratingvalue'] : ''),
            "ratingcount" => self::prepareVal(isset($overrides['ratingcount']) ? $overrides['ratingcount'] : ''),
            "price"       => self::prepareVal(isset($overrides['price']) ? $overrides['price'] : ''),
            "currency"    => self::prepareVal(isset($overrides['currency']) ? $overrides['currency'] : ''),
            "version"     => self::prepareVal(isset($overrides['version']) ? $overrides['version'] : ''),
            "screenshot"  => self::prepareVal(
                isset($overrides['screenshot']) ? $overrides['screenshot'] : ''
            ),
            "datePublished"  => self::prepareVal(isset($overrides['published']) ? $overrides['published'] : ''),
            
            "publisher"     => self::prepareVal(isset($overrides['publisher']) ? $overrides['publisher'] : 'Amvidia'),
            "publisherLogo" => self::prepareVal(isset($overrides['publisherLogo']) ? $overrides['publisherLogo'] : 'images/amvidia_logo.png'),
            
            "officialUrl"  => self::prepareVal(isset($overrides['officialUrl']) ? $overrides['officialUrl'] : ''),
            "downloadUrl"  => self::prepareVal(isset($overrides['downloadUrl']) ? $overrides['downloadUrl'] : ''),
            "reviewer"  => self::prepareVal(isset($overrides['reviewer']) ? $overrides['reviewer'] : ''),
            "reviewrating"  => self::prepareVal(isset($overrides['reviewrating']) ? $overrides['reviewrating'] : ''),
            "reviewdate"  => self::prepareVal(isset($overrides['reviewdate']) ? $overrides['reviewdate'] : ''),
        );

        if ($data['screenshot'])
		{
			$size = self::getImageSize($data['screenshot']);
			if ($size['width'] > 0 && $size['height'] > 0)
			{
				$data['screenshotWidth'] = $size['width'];
                $data['screenshotHeight'] = $size['height'];
                $data['screenshot'] = self::imageURL($data['screenshot']);
			}
			else
			{
				unset($data['screenshot']);
			}
        }/**/
        if ($data['publisherLogo'])
		{
			$size = self::getImageSize($data['publisherLogo']);
			if ($size['width'] > 0 && $size['height'] > 0)
			{
				$data['publisherLogoWidth'] = $size['width'];
                $data['publisherLogoHeight'] = $size['height'];
                $data['publisherLogo'] = self::imageURL($data['publisherLogo']);
			}
			else
			{
				unset($data['publisherLogo']);
			}
        }/**/
        return $data;
    }

    /**
     *  Get article's data
     *
     *  @return  array
     */
    public static function getArticle(&$item, &$menu, &$params)
    {
        try
        {
            //error_log("getArticle 0");
            $overrides = self::ReadMicrodata(self::path() . '/articles/', 'm.' . $menu->id);
            if (!$overrides) $overrides = self::ReadMicrodata(self::path() . '/articles/', 'a.' . $item->id);
            if (!$overrides) $overrides = array(); // чтоб не делать проверку на нул

            // аня просила если нет микродаты то не показывать.
            if (!$overrides) return;
            //error_log("getArticle 1");

            //echo 'intro='. $item->introtext;
            //echo '---->' . $item->metadesc;
            /*echo '<pre>';
            var_dump($menu->params['page_title']);
            echo '<pre>';/**/

            $image = new Registry($item->images);
            //error_log("getArticle 2");
            /*echo '<pre>';
            var_dump($image->get("image_intro"));
            echo '<pre>';/**/

            // Array data
            $data = array(
                "contentType" => "article",
                "url"         => self::prepareVal(
                    isset($overrides['url']) ? 
                        $overrides['url'] : 
                        //self::proto() . $_SERVER['SERVER_NAME'] . JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language))
                        AmvidiaUrlHelper::getCanonical($params)
                ),
                // titl надо брать с раздела, по просьбе ани
                "title"       => self::prepareVal(
                    isset($overrides['title']) ? 
                    $overrides['title'] : 
                    (
                        $menu->params['page_title'] ? $menu->params['page_title'] : $item->title
                    )
                ),
                "description" => self::prepareVal(
                    isset($overrides['description']) ? 
                        $overrides['description'] : 
                        (
                            isset($item->metadesc) && !empty($item->metadesc) ? 
                            $item->metadesc : 
                            (isset($item->introtext) && !empty($item->introtext) ? $item->introtext : $item->fulltext)
                        )
                    , true
                ),
                "image"       => self::prepareVal(
                    isset($overrides['image']) ? $overrides['image'] : ($image->get("image_intro") ?: $image->get("image_fulltext"))
                ),
                //"created_by"  => $item->created_by,
                "dateCreated"     => self::prepareVal(isset($overrides['created']) ? $overrides['created'] : $item->created),
                "dateModified"    => self::prepareVal(isset($overrides['modified']) ? $overrides['modified'] : $item->modified),
                "datePublished"  => self::prepareVal(isset($overrides['published']) ? $overrides['published'] : $item->publish_up),
                
                "authorName"     => self::prepareVal(
                    isset($overrides['author']) ? 
                        $overrides['author'] : 
                        ($item->created_by_alias ? $item->created_by_alias : $item->author)
                ),
                "authorLogo"     => self::prepareVal(
                        isset($overrides['authorlogo']) ? 
                        $overrides['authorlogo'] : 
                        "images/amvidia_logo.png"
                ),

                //"ratingValue" => $item->rating,
                //"reviewCount" => $item->rating_count
            );
            //error_log("getArticle 3");
            if ($data['image'])
            {
                $size = self::getImageSize($data['image']);
                if ($size['width'] > 0 && $size['height'] > 0)
                {
                    $data['imageWidth'] = $size['width'];
                    $data['imageHeight'] = $size['height'];
                    $data['image'] = self::imageURL($data['image']);
                }
                else
                {
                    unset($data['image']);
                }
            }/**/
            //error_log("getArticle 4");
            if ($data['authorLogo'])
            {
                $size = self::getImageSize($data['authorLogo']);
                if ($size['width'] > 0 && $size['height'] > 0)
                {
                    $data['authorLogoWidth'] = $size['width'];
                    $data['authorLogoHeight'] = $size['height'];
                    $data['authorLogo'] = self::imageURL($data['authorLogo']);
                }
                else
                {
                    unset($data['authorLogo']);
                }
            }/**/
            //error_log("getArticle 5");
            return $data;
        }
        catch(Exception $e)
        {
            //error_log("getArticle !!!!!" . $e->getMessage());
            return;
        }
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

    public static function getCurrentMenuItem()
    {
        //error_log("getCurrentMenuItem 0");
        $menu = JFactory::getApplication()->getMenu();
        //error_log("getCurrentMenuItem 1");
        $m = $menu->getActive();
        //error_log("getCurrentMenuItem 2");
        return $m;
    }

}