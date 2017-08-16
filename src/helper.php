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

class AmvidiaGSDHelper
{

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
        return "Amvidia";//self::getParams()->get("sitename_name", JFactory::getConfig()->get('sitename'));
    }
    
    /**
     *  Returns the Site Logo URL
     *
     *  @return  string
     */
    public static function getSiteLogo()
    {
        return "https://amvidia.com/logo.png";
    }

    /**
     *  Get website URL
     *
     *  @return  string  Site URL
     */
    public static function getSiteURL()
    {
        return "https://amvidia.com/";//self::getParams()->get("sitename_url", JURI::root());
    }


}