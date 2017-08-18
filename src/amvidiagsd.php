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
            //$this->getJSONSitelinksSearch(),
            $this->getJSONLogo(),
            
            //$this->getCustomCode(),
            $this->getJSONBreadcrumbs()
        );

        // Convert data array to string
        $markup = implode("\n", array_filter($data));

        // Return if markup is empty
        if (!$markup || empty($markup) || is_null($markup))
        {
            return;
        }

        // Add final markup to the document
        JFactory::getDocument()->addCustomTag('
            <!-- Start: ' . JText::_("GSD") . ' -->
            ' . $markup . '
            <!-- End: ' . JText::_("GSD") . ' -->
        ');
    }


    /**
     *  Returns Site Name strucuted data markup
     *  https://developers.google.com/structured-data/site-name
     *
     *  @return  string on success, boolean on fail
     */
    private function getJSONSiteName()
    {
        // Generate JSON
        return $this->json->setData(array(
            "contentType" => "sitename",
            "name"        => AmvidiaGSDHelper::getSetting('sitename'),
            "url"         => AmvidiaGSDHelper::getSetting('siteurl'),
            //"alt"         => $this->params->get("sitename_name_alt")
        ))->generate();
    }

    /**
     *  Returns Site Logo structured data markup
     *  https://developers.google.com/search/docs/data-types/logo
     *
     *  @return  string on success, boolean on fail
     */
    private function getJSONLogo()
    {
        if (!$logo = AmvidiaGSDHelper::getSetting('sitelogo'))
        {
            return;
        }

        // Generate JSON
        return $this->json->setData(array(
            "contentType" => "logo",
            "url"         => AmvidiaGSDHelper::getSetting('siteurl'),
            "logo"        => $logo
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
