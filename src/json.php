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

/**
 *  Google Structured Data JSON generator
 */
class AmvidiaGSDJSON
{
    /**
     *  Content Type Data
     *
     *  @var  object
     */
    private $data;

    /**
     *  List of available content types
     *
     *  @var  array
     */
    public $contentTypes = array(
        
        'article'
    );

    /**
     *  Class Constructor
     *
     *  @param  object  $data
     */
    public function __construct($data = null)
    {
        $this->setData($data);
    }

    /**
     *  Set Data
     *
     *  @param  array  $data
     */
    public function setData($data)
    {
        if (!is_array($data))
        {
            return;
        }

        $this->data = new JRegistry($data);
        return $this;
    }

    /**
     *  Get Content Type JSON
     *
     *  @return  string
     */
    public function generate()
    {
        $contentTypeMethod = "contentType" . $this->data->get("contentType");

        if (!method_exists($this, $contentTypeMethod))
        {
            return;
        }

        // Call Method
        if (!$result = $this->$contentTypeMethod())
        {
            return;
        }

        $json = '
            <script type="application/ld+json">
            {
                ' . preg_replace('/\s+/u', ' ', implode(",", $result)) . '
            }
            </script>'; //$this->prep(implode(",", $result)))

        // Add Custom Code
        /*$customCode = $this->data->get("custom", null);
        if (!empty($customCode) && strpos($customCode, '</script>') !== false)
        {
            $json .= "\n" . $customCode . "\n";
        }*/

        return $json;
    }

    /**
     *  Returns Breadcrumbs Content Type
     *
     *  @return  string
     */
    private function contentTypeBreadcrumbs()
    {
        $crumbsData = array();
        $crumbs = $this->data->get("crumbs");
        
        if (!is_array($crumbs))
        {
            return;
        }

        foreach ($crumbs as $key => $value)
        {
            $crumbsData[] = '{
                    "@type": "ListItem",
                    "position": ' . ($key + 1) . ',
                    "item": {
                        "@id":  "' . $value->link . '",
                        "name": "' . $value->name . '"
                    }
                }';
        }

        $json[] = '"@context": "https://schema.org",
                "@type": "BreadcrumbList",
                "itemListElement": [' . implode(",", $crumbsData) . ']';

        return $json;
    }

    /**
     *  Returns Site Name Content Type
     *  https://developers.google.com/structured-data/site-name
     *
     *  @return  string on success, boolean on fail
     */
    private function contentTypeSiteName()
    {
        $json[] = '"@context": "https://schema.org",
                "@type": "WebSite",
                "name": "' . $this->data->get("name") . '",
                "url": "' . $this->data->get("url") . '"';

        if ($this->data->get("alt"))
        {
            $json[] = '
                "alternateName": "' . $this->data->get("alt") . '"';
        }

        return $json;
    }

    /**
     *  Returns Sitelinks Searchbox Content Type
     *  https://developers.google.com/search/docs/data-types/sitelinks-searchbox
     *
     *  @return  string on success, boolean on fail
     */
    private function contentTypeSearch()
    {
        $json[] = '"@context": "https://schema.org",
                "@type": "WebSite",
                "url": "' . $this->data->get("siteurl") . '",
                "potentialAction": {
                    "@type": "SearchAction",
                    "target": "' . $this->data->get("searchurl") . '",
                    "query-input": "required name=search_term"
                }';

        return $json;
    }

    /**
     *  Returns Site Logo Content Type
     *  https://developers.google.com/search/docs/data-types/logo
     *
     *  @return  string on success, boolean on fail
     */
    private function contentTypeLogo()
    {
        $json[] = '"@context": "https://schema.org",
                "@type": "Organization",
                "name": "' . $this->data->get("name") . '",
                "url": "' . $this->data->get("url") . '",
                "logo": "' . $this->data->get("logo") . '"';

        $sameas = $this->data->get("sameas");
        if ($sameas)
        {
            if (!is_array($sameas)) $sameas = array($sameas);
            $sameas = array_map(function($item) { return '"' . $item . '"'; }, $sameas);
            /*echo '<pre>';
            var_dump($sameas);
            echo '<pre>';/**/
            
            $json[] = '
            "sameAs": [' . implode(',', $sameas) . ']';
        }
        return $json;
    }

    private function contentTypeSoftware()
    {
        $json[] = '"@context": "https://schema.org/",
        "@type": "SoftwareApplication",
        "name": "' . $this->data->get('name') . '",
        "operatingSystem": "' . $this->data->get('os') . '",
        "applicationCategory": "' . $this->data->get('category') . '"
        ';
        //softwareVersion
        if ($this->data->get("version"))
        {
            $json[] = '
            "softwareVersion": "' . $this->data->get('version') . '"';
        }
        //datePublished
        if ($this->data->get("datePublished"))
        {
            $json[] = '
            "datePublished": "' . $this->data->get('datePublished') . '"';
        }
        if ($this->data->get("screenshot"))
        {
            $json[] = '
                "screenshot": {
                    "@type": "ImageObject",
                    "url": "' . $this->data->get("screenshot") . '",
                    "height": ' . $this->data->get("screenshotHeight") . ',
                    "width":  ' . $this->data->get("screenshotWidth"). '
                }';
        }
        if ($this->data->get("ratingvalue"))
        {
            $json[] = '
                "aggregateRating": {
                    "@type": "AggregateRating",
                    "ratingValue": "' . $this->data->get("ratingvalue") . '",
                    "ratingCount":  "' . $this->data->get("ratingcount"). '"
                }';
        }
        if ($this->data->get("price"))
        {
            $json[] = '
                "offers": {
                    "@type": "Offer",
                    "price": "' . $this->data->get("price") . '",
                    "priceCurrency":  "' . $this->data->get("currency"). '"
                }';
        }

        if ($this->data->get("publisher"))
        {
            $plogo = '';
            $pl = $this->data->get("publisherLogo");
            if ($pl)
            {
                $plogo = ',"logo": {
                    "@type": "ImageObject",
                    "url": "' . $this->data->get("authorLogo") . '",
                    "height": ' . $this->data->get("authorLogoHeight") . ',
                    "width":  ' . $this->data->get("authorLogoWidth"). '
                }';
            }
            $json[] = '
                "publisher": {
                    "@type": "Organization",
                    "name": "' . $this->data->get("publisher") . '"
                    ' . $plogo . '
                }';
        }
        return $json;
    }

    private function prep($val)
    {
        return str_replace(chr(0xc2).chr(0xa0), ' ', $val); //"\xc2\xa0"
    }

    /**
     *  Generates Article Content Type
     *
     *  @return  string
     */
    private function contentTypeArticle()
    {
        $json[] = '"@context": "https://schema.org/",
                "@type": "Article",
                "mainEntityOfPage": {
                    "@type": "WebPage",
                    "@id": "' . $this->data->get("url") . '"
                },
                "headline": "' . $this->data->get("title") . '",
                "description": "' . $this->data->get("description") . '"
                ';

        if ($this->data->get("image"))
        {
            $json[] = '
                "image": {
                    "@type": "ImageObject",
                    "url": "' . $this->data->get("image") . '",
                    "height": ' . $this->data->get("imageHeight") . ',
                    "width":  ' . $this->data->get("imageWidth"). '
                }';
        }

        // Author
        if ($this->data->get("authorName"))
        {
            $plogo = '';
            $pl = $this->data->get("authorLogo");
            if ($pl)
            {
                $plogo = ',"logo": {
                    "@type": "ImageObject",
                    "url": "' . $this->data->get("authorLogo") . '",
                    "height": ' . $this->data->get("authorLogoHeight") . ',
                    "width":  ' . $this->data->get("authorLogoWidth"). '
                }';
            }
            $json[] = '
                "author": {
                    "@type": "Organization",
                    "name": "' . $this->data->get("authorName") . '"
                    ' . $plogo . '
                }';
            $json[] = '
                "publisher": {
                    "@type": "Organization",
                    "name": "' . $this->data->get("authorName") . '"
                    ' . $plogo . '
                }';
        }

        // Publisher
        /*if ($this->data->get("publisherName"))
        {
            $json[] = '
                "publisher": {
                    "@type": "Organization",
                    "name": "' . $this->data->get("publisherName") . '",
                    "logo": {
                        "@type": "ImageObject",
                        "url": "' . $this->data->get("publisherLogo") . '",
                        "width": 600,
                        "height": 60
                    }
                }';
        }*/

        $json[] = '
                "datePublished" : "' . $this->data->get("datePublished") . '",
                "dateCreated" : "' . $this->data->get("dateCreated") . '",
                "dateModified": "' . $this->data->get("dateModified") . '"';

        return $json;
    }

    
}

?>