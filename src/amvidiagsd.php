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

defined('_JEXEC') or die('Restricted access');

class plgSystemAmvidiaGSD extends JPlugin
{
	private $CurrentMenuItem;

    /**
     *  Auto loads the plugin language file
     *
     *  @var  boolean
     */
    protected $autoloadLanguage = true;

    /**
     *  The loaded indicator of helper
     *
     *  @var  boolean
     */
    protected $init;

    /**
     *  Application Object
     *
     *  @var  object
     */
    protected $app;

    /**
     *  JSON Helper
     *
     *  @var  class
     */
    private $json;

    /**
     *  Class constructor
     *  We overriding in order to make the component's parameters available through all plugin events
     *
     *  @param  string  &$subject  
     *  @param  array   $config
     */
    public function __construct(&$subject, $config = array())
    {
        if (!$this->loadClasses())
        {
            return;
        }

        parent::__construct($subject, $config);
    }

    protected function getCurrentMenuItem()
    {
    	if (!$this->CurrentMenuItem) $this->CurrentMenuItem = AmvidiaGSDHelper::getCurrentMenuItem();

    	return $this->CurrentMenuItem;
    }

    /**
     *  onBeforeCompileHead event to add JSON markup to the document
     *
     *  @return void
     */
    public function onBeforeCompileHead()
    {
        $this->HeaderGSD();
    }

	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
		$app   = JFactory::getApplication();
		$view  = $app->input->get('view');
		$print = $app->input->getBool('print');

		if ($print)
		{
			return false;
		}

		if (($context == 'com_content.article') && ($view == 'article'))
		{
			//$menu = $this->getCurrentMenuItem();
			$this->ArticleGSD($row, $this->params);
		}
	}

    private function ArticleGSD(&$row, &$params)
    {
        // Load Helper
        if (!$this->getHelper())
        {
            return;
        }

        //$menu = $this->getCurrentMenuItem();

        $data = array(
            $this->getJSONArticle($row, $params)
        );

        // Convert data array to string
        $markup = implode("\n", array_filter($data));

        // Return if markup is empty
        if (!$markup || empty($markup) || is_null($markup))
        {
            return;
        }

        // Add final markup to the document
        JFactory::getDocument()->addCustomTag( ''
        //. '<!-- Start: ' . JText::_("AmvidiaGSD") . ' -->' 
        . $markup 
        //. '<!-- End: ' . JText::_("AmvidiaGSD") . ' -->'
        );
	}

    /**
     *  Adds Google Structured Markup to the document in JSON Format
     *
     *  @return void
     */
    private function HeaderGSD()
    {
        // Load Helper
        if (!$this->getHelper())
        {
            return;
        }

        //$menu = $this->getCurrentMenuItem();

        // Get JSON markup for each available type
        $data = array(
            $this->getJSONSiteName(),
            //$this->getJSONSitelinksSearch(),
            $this->getJSONLogo(),
            
            $this->getJSONBreadcrumbs(),
            $this->getJSONSoftware($this->params),
            //$this->getCustomCode(),
        );

        // Convert data array to string
        $markup = implode("\n", array_filter($data));

        // Return if markup is empty
        if (!$markup || empty($markup) || is_null($markup))
        {
            return;
        }

        // Add final markup to the document
        JFactory::getDocument()->addCustomTag( ''
        //. '<!-- Start: ' . JText::_("AmvidiaGSD") . ' -->' 
        . $markup 
        //. '<!-- End: ' . JText::_("AmvidiaGSD") . ' -->'
        );
    }


    /**
     *  Returns Site Name strucuted data markup
     *  https://developers.google.com/structured-data/site-name
     *
     *  @return  string on success, boolean on fail
     */
    private function getJSONSiteName()
    {
		if (!AmvidiaGSDHelper::isFrontPage())
		{
			return;
        }
        $url = AmvidiaGSDHelper::getSetting('siteurl');
        $searchURL = '';
        if (AmvidiaGSDHelper::getSetting("search_enabled"))
        {
            $searchURL = trim(AmvidiaGSDHelper::getSetting("search_url"));
            if (!$searchURL)
                $searchURL = AmvidiaGSDHelper::route($url . '/index.php?option=com_search&searchphrase=all&searchword={search_term}');
        }

        // Generate JSON
        return $this->json->setData(array(
            "contentType" => "sitename",
            "name"        => AmvidiaGSDHelper::getSetting('sitename'),
            "url"         => $url,
            "alt"         => AmvidiaGSDHelper::getSetting('sitealtname'),
            "searchurl"   => $searchURL
        ))->generate();
    }

	/**
	 *  Returns Sitelinks Searchbox structured data markup
	 *  https://developers.google.com/search/docs/data-types/sitelinks-searchbox
	 *
	 *  @return  string on success, boolean on fail
	 */
    /*private function getJSONSitelinksSearch()
    {
        if (!AmvidiaGSDHelper::getSetting("search_enabled"))
        {
             return;
        }
 
         // Setup the right Search URL
        //switch ($sitelinks)
        //{
        //    case "1": // com_search
        //         $searchURL = GSDHelper::route(JURI::base() . 'index.php?option=com_search&searchphrase=all&searchword={search_term}');
        //         break;
        //    case "2": // com_finder
        //         $searchURL = GSDHelper::route(JURI::base() . 'index.php?option=com_finder&q={search_term}');
        //         break;
        //    case "3": // custom URL
        //         $searchURL = trim($this->params->get('sitelinks_search_custom_url'));
        //         break;
        //}

        $searchURL = trim(AmvidiaGSDHelper::getSetting("search_url"));
        if (!$searchURL)
            $searchURL = AmvidiaGSDHelper::route(JURI::base() . 'index.php?option=com_search&searchphrase=all&searchword={search_term}');
 
         // Generate JSON
        return $this->json->setData(array(
             "contentType" => "search",
             "siteurl"     => AmvidiaGSDHelper::getSetting('siteurl'),
             "searchurl"   => $searchURL
        ))->generate();
    }*/
     
    /**
     *  Returns Site Logo structured data markup
     *  https://developers.google.com/search/docs/data-types/logo
     *
     *  @return  string on success, boolean on fail
     */
    private function getJSONLogo()
    {
        // не тока лого!!
        /*if (!$logo = AmvidiaGSDHelper::getSetting('sitelogo'))
        {
            return;
        }*/

        $logo = AmvidiaGSDHelper::getSetting('sitelogo');
        $sameas = AmvidiaGSDHelper::getSetting('sameas');

        /*echo '<pre>';
		var_dump($sameas);
        echo '<pre>';/**/
        
        // Generate JSON
        return $this->json->setData(array(
            "contentType" => "logo",
            "name"        => AmvidiaGSDHelper::getSetting('sitename'),
            "url"         => AmvidiaGSDHelper::getSetting('siteurl'),
            "logo"        => $logo,
            "sameas"      => $sameas
        ))->generate();
    }

    /**
     *  Returns Breadcrumbs structured data markup
     *  https://developers.google.com/structured-data/breadcrumbs
     *
     *  @return  string
     */
    private function getJSONBreadcrumbs()
    {
        // Skip on homepage 
        if (!AmvidiaGSDHelper::getSetting("breadcrumbs_enabled") || AmvidiaGSDHelper::isFrontPage())
        {
            return;
        }

        // Generate JSON
        return $this->json->setData(array(
            "contentType" => "breadcrumbs",
            "crumbs"      => AmvidiaGSDHelper::getCrumbs(AmvidiaGSDHelper::getSetting('homename'))
        ))->generate();
    }

    private function getJSONArticle(&$row, &$params)
    {
        if (!AmvidiaGSDHelper::getSetting("articles_enabled"))
        {
            return;
        }

        $menu = $this->getCurrentMenuItem();

        $art = AmvidiaGSDHelper::getArticle($row, $menu, $params);
        
        if (!$art) return;
        
        // Generate JSON
        $json = $this->json->setData(
            $art
        )->generate();

        return $json;
    }

    private function getJSONSoftware(&$params)
    {
		$menu = $this->getCurrentMenuItem();
        // Generate JSON
        $s = AmvidiaGSDHelper::getSoftware($menu, $params);
        if (!$s) return;
        $json = $this->json->setData($s)->generate();
        return $json;
    }

	/**
	 *  Returns Custom Code
	 *
	 *  @return  string  The Custom Code
	 */
	private function getCustomCode()
	{
		$menu = $this->getCurrentMenuItem();

		return '';
	}


    /**
     *  Load required classes
     *
     *  @return  mixed 
     */
    private function loadClasses()
    {
        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.folder');

        // Load component helpers
        $path = __DIR__;
        /*if (!JFolder::exists($path))
        {
            return;
        }*/

        require_once $path . '/helper.php';
        require_once $path . '/urlhelper.php';
        require_once $path . '/json.php';

        return true;
    }

    /**
     *  Loads Helper files
     *
     *  @return  boolean
     */
    private function getHelper()
    {
        // Return if is helper is already loaded
        if ($this->init)
        {
            return true;
        }

        // Return if we are not in frontend
        if (!$this->app->isSite())
        {
            return false;
        }

        // Only on HTML documents
        if (JFactory::getDocument()->getType() != 'html')
        {
            return false;
        }

        // Load required classes
        if (!$this->loadClasses())
        {
            return false;
        }

        // Return if current page is an XML page
        if (AmvidiaGSDHelper::isFeed() || $this->app->input->getInt('print', 0))
        {
            return false;
        }

        // Initialize JSON Generator Class
        $this->json = new AmvidiaGSDJSON();


        return ($this->init = true);
    }
}
