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

class plgSystemGSD extends JPlugin
{
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
		
		$config['params'] = GSDHelper::getParams();
		parent::__construct($subject, $config);
	}

	/**
	 *  onBeforeRender event (default) to add JSON markup to the document
	 *
	 *  Administrator can set the internal Joomla event on which the plugin should be initialised. 
	 *  This is very useful when the site is experiencing issues with the plugin not working.
	 *
	 *  @return void
	 */
	public function onBeforeRender()
	{
		//$this->init();
	}

	/**
	 *  onBeforeCompileHead event to add JSON markup to the document
	 *
	 *  @return void
	 */
	public function onBeforeCompileHead()
	{		
		$this->init();
	}

	/**
	 *  Adds Google Structured Markup to the document in JSON Format
	 *
	 *  @return void
	 */
	private function init()
	{
		// Load Helper
		if (!$this->getHelper())
		{
			return;
		}

		// Get JSON markup for each available type
		$data = array(
			$this->getJSONSiteName(),
			$this->getJSONSitelinksSearch(),
			$this->getJSONLogo(),
			
			$this->getCustomCode(),
			$this->getJSONBreadcrumbs()
		);

        // Load and trigger plugins
        GSDHelper::event()->trigger('onGSDBeforeRender', array(&$data));

		// Convert data array to string
		$markup = implode("\n", array_filter($data));

		// Return if markup is empty
		if (!$markup || empty($markup) || is_null($markup))
		{
			return;
		}

		// Minify output
		if ($this->params->get('minifyjson', false))
		{
			$markup = GSDHelper::minify($markup);
		}

		GSDHelper::log($markup, 'Final Output');

		// Output log messages if debug is enabled
    	if ($this->params->get("debug", false))
    	{
    		var_dump(GSDHelper::$log);
    	}

		// Add final markup to the document
		JFactory::getDocument()->addCustomTag('
            <!-- Start: ' . JText::_("GSD") . ' -->
            ' . $markup . '
            <!-- End: ' . JText::_("GSD") . ' -->
        ');
	}


	/**
	 *  Returns Breadcrumbs structured data markup
	 *  https://developers.google.com/structured-data/breadcrumbs
	 *
	 *  @return  string
	 */
	/*private function getJSONBreadcrumbs()
	{
		// Skip on homepage 
		if (!$this->params->get("breadcrumbs_enabled", true) || GSDHelper::isFrontPage())
		{
			return;
		}

		// Generate JSON
		return $this->json->setData(array(
			"contentType" => "breadcrumbs",
			"crumbs"      => GSDHelper::getCrumbs($this->params->get('breadcrumbs_home', JText::_('GSD_BREADCRUMBS_HOME')))
		))->generate();
	}*/

	/**
	 *  Returns Site Name strucuted data markup
	 *  https://developers.google.com/structured-data/site-name
	 *
	 *  @return  string on success, boolean on fail
	 */
	private function getJSONSiteName()
	{
		if (!$this->params->get("sitename_enabled", true) || !GSDHelper::isFrontPage())
		{
			return;
		}

		// Generate JSON
		return $this->json->setData(array(
			"contentType" => "sitename",
			"name"        => GSDHelper::getSiteName(),
			"url"         => GSDHelper::getSiteURL(),
			"alt"         => $this->params->get("sitename_name_alt")
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
		if (!$sitelinks = $this->params->get('sitelinks_enabled', false))
		{
			return;
		}

		// Setup the right Search URL
		switch ($sitelinks)
		{
			case "1": // com_search
				$searchURL = GSDHelper::route(JURI::base() . 'index.php?option=com_search&searchphrase=all&searchword={search_term}');
				break;
			case "2": // com_finder
				$searchURL = GSDHelper::route(JURI::base() . 'index.php?option=com_finder&q={search_term}');
				break;
			case "3": // custom URL
				$searchURL = trim($this->params->get('sitelinks_search_custom_url'));
				break;
		}

		// Generate JSON
		return $this->json->setData(array(
			"contentType" => "search",
			"siteurl"     => GSDHelper::getSiteURL(),
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
		if (!$logo = GSDHelper::getSiteLogo())
		{
			return;
		}

		// Generate JSON
		return $this->json->setData(array(
			"contentType" => "logo",
			"url"         => GSDHelper::getSiteURL(),
			"logo"        => $logo
		))->generate();
	}

	

	/**
	 *  Returns Custom Code
	 *
	 *  @return  string  The Custom Code
	 */
	private function getCustomCode()
	{
		return trim($this->params->get('customcode'));
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

		// Load Framework
		$framework = JPATH_PLUGINS . '/system/nrframework/helpers/functions.php';
		if (!JFile::exists($framework))
		{
			return;
		}

		require_once $framework;

		// Load component helpers
		$path = JPATH_ADMINISTRATOR . '/components/com_gsd/helpers/';
		if (!JFolder::exists($path))
		{
			return;
		}

		require_once $path . 'helper.php';
		require_once $path . 'json.php';

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
		if (NRFrameworkFunctions::isFeed() || $this->app->input->getInt('print', 0))
		{
			return false;
		}

		// Initialize JSON Generator Class
		$this->json = new GSDJSON();

		return ($this->init = true);
	}
}
