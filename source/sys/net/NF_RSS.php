<?php

/**
 * Class that allows you to dynamically create RSS feeds, or consume existing RSS feeds over
 * the internet
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_RSS
{
    // Required fields
    public $title;
    public $link;
    public $description;

    // If the content is utf8 already - meaning no internal conversion
    public $utf8 = false;

    // Optional fields
    public $language;
    public $copyright;
    public $managingEditor;
    public $webMaster;
    public $pubDate;
    public $lastBuildDate;
    public $category;
    public $category_domain;
    public $generator = 'Nifty Framework RSS';
    public $docs = 'http://blogs.law.harvard.edu/tech/rss';
    public $ttl;
    public $image_url;
    public $image_title;
    public $image_link;
    public $image_width;
    public $image_height;
    public $image_description;

    // Atom "self" address
    public $thisUrl;

    public $items = array();

    // --- Constructor ---

    public function __construct($title, $link, $description, $thisUrl = '')
    {
        $this->title       = $title;
        $this->link        = $link;
        $this->description = $description;
        $this->thisUrl     = $thisUrl;
    }

    // --- Static methods ---

    public static function fromUrl($url, $maxItems = 0, $stripTags = false)
    {
        if (($data = file_get_contents($url)) === false)
            return false;

        return self::fromString($data, $maxItems, $stripTags);
    }

    public static function fromString($data, $maxItems = 0, $stripTags = false)
    {
        $rss = new NF_RSS('', '', '');
        $rss->parseXmlData($data, $maxItems);

        if ($stripTags)
            $rss->stripTags();

        return $rss;
    }

    // Helper methods

    public function xmlGetChild($element, $node)
    {
        $children = $element->childNodes;
        for($i=0; $i<$children->length; $i++)
            if ($children->item($i)->nodeName == $node)
                return $children->item($i);

        return false;
    }

    public function xmlGetChildData($element, $node)
    {
        if (($node = $this->xmlGetChild($element, $node)) == false)
            return '';

        return $this->utf8 ? $node->nodeValue : NF_Text::fromUnicode($node->nodeValue);
    }

    public function xmlGetChildAttribute($element, $node, $attribute)
    {
        $node = $this->xmlGetChild($element, $node);
        if ($node == false)
            return false;

        $value = $node->getAttribute($attribute);
        return $this->utf8 ? $value : NF_Text::fromUnicode($value);
    }

    public function xmlCreateChild($doc, $node, $tag, $value = '', array $attributes = null)
    {
        $newNode = $doc->createElement($tag);
        $node->appendChild($newNode);

        if ($value)
            $newNode->appendChild(
                $doc->createTextNode($this->utf8 ? $value : NF_Text::toUnicode($value))
            );

        if ($attributes != null)
            foreach($attributes as $k => $v)
                if ($v != '')
                    $newNode->setAttribute($k, $this->utf8 ? $v : NF_Text::toUnicode($v));

        return $newNode;
    }

    public function xmlCreateChildCDATA($doc, $node, $tag, $value)
    {
        $newNode = $this->xmlCreateChild($doc, $node, $tag);
        $newNode->appendChild(
            $doc->createCDATASection($this->utf8 ? $value : NF_Text::toUnicode($value))
        );

        return $newNode;
    }

    // --- Generating and parsing XML data

    public function getXml()
    {
        $doc = new DOMDocument('1.0', 'utf-8');

        if ($this->title == '' || $this->link == '' || $this->description == '')
            throw new Exception('RSS: title, link and description must contain a value');

        $rss = $this->xmlCreateChild($doc, $doc, 'rss');
        $rss->setAttribute('version', '2.0');

        if ($this->thisUrl != '')
            $rss->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');

        $channel = $this->xmlCreateChild($doc, $rss, 'channel');

        $this->xmlCreateChild($doc, $channel, 'title', $this->utf8 ? $this->title : NF_Text($this->title));
        $this->xmlCreateChild($doc, $channel, 'link', $this->link);

        $description = $this->xmlCreateChild($doc, $channel, 'description');
        $data = $doc->createCDATASection($this->utf8 ? $this->description : NF_Text::toUnicode($this->description));
        $description->appendChild($data);

        if ($this->language != '')
            $this->xmlCreateChild($doc, $channel, 'language', $this->language);
        if ($this->copyright != '')
            $this->xmlCreateChild($doc, $channel, 'copyright', $this->copyright);
        if ($this->managingEditor != '')
            $this->xmlCreateChild($doc, $channel, 'managingEditor', $this->managingEditor);
        if ($this->webMaster != '')
            $this->xmlCreateChild($doc, $channel, 'webMaster', $this->webMaster);
        if ($this->pubDate != '')
            $this->xmlCreateChild($doc, $channel, 'pubDate', $this->pubDate->format('r'));
        if ($this->lastBuildDate != '')
            $this->xmlCreateChild($doc, $channel, 'lastBuildDate', $this->lastBuildDate->format('r'));
        if ($this->category != '')
            $this->xmlCreateChild($doc, $channel, 'category', $this->category, array(
                'domain' => $this->category_domain
            ));
        if ($this->generator != '')
            $this->xmlCreateChild($doc, $channel, 'generator', $this->generator);
        if ($this->docs != '')
            $this->xmlCreateChild($doc, $channel, 'docs', $this->docs);
        if ($this->ttl != '')
            $this->xmlCreateChild($doc, $channel, 'ttl', $this->ttl);

        if ($this->thisUrl != '')
            $this->xmlCreateChild($doc, $channel, 'atom:link', '', array(
                'href' => $this->thisUrl,
                'rel'  => 'self',
                'type' => 'application/rss+xml'
            ));

        foreach($this->items as $item)
            $item->buildXml($this, $doc, $channel);

        return $doc->saveXML();
    }

    protected function parseXmlData($xml, $maxItems)
    {
        if (($doc = DOMDocument::loadXML($xml)) == false)
            return false;

        if (($rss = $this->xmlGetChild($doc, 'rss')) == false)
            return false;

        if (($channel = $this->xmlGetChild($rss, 'channel')) == false)
            return false;

        $this->title             = $this->xmlGetChildData     ($channel, 'title');
        $this->link              = $this->xmlGetChildData     ($channel, 'link');
        $this->description       = $this->xmlGetChildData     ($channel, 'description');
        $this->language          = $this->xmlGetChildData     ($channel, 'language');
        $this->copyright         = $this->xmlGetChildData     ($channel, 'copyright');
        $this->managingEditor    = $this->xmlGetChildData     ($channel, 'managingEditor');
        $this->webMaster         = $this->xmlGetChildData     ($channel, 'webMaster');
        $this->pubDate           = $this->xmlGetChildData     ($channel, 'pubDate');
        $this->lastBuildDate     = $this->xmlGetChildData     ($channel, 'lastBuildDate');
        $this->category          = $this->xmlGetChildData     ($channel, 'category');
        $this->category_domain   = $this->xmlGetChildAttribute($channel, 'category', 'domain');
        $this->generator         = $this->xmlGetChildData     ($channel, 'generator');
        $this->docs              = $this->xmlGetChildData     ($channel, 'docs');
        $this->ttl               = $this->xmlGetChildData     ($channel, 'ttl');
        $this->image_url         = $this->xmlGetChildAttribute($channel, 'image', 'url');
        $this->image_title       = $this->xmlGetChildAttribute($channel, 'image', 'title');
        $this->image_link        = $this->xmlGetChildAttribute($channel, 'image', 'link');
        $this->image_width       = $this->xmlGetChildAttribute($channel, 'image', 'width');
        $this->image_height      = $this->xmlGetChildAttribute($channel, 'image', 'height');
        $this->image_description = $this->xmlGetChildAttribute($channel, 'image', 'description');

        $atomLinks = $channel->getElementsByTagName('atom:link');
        for ($i=0; $i<$atomLinks->length; $i++)
            if ($atomLinks[$i]->getAttribute('rel') == 'self')
            {
                $this->thisUrl = $atomLinks[$i]->getAttribute('href');
                break;
            }

        $nodes = $channel->childNodes;
        for($i=0; $i<$nodes->length; $i++)
            if ($nodes->item($i)->nodeName == 'item')
            {
                $this->addItem(NF_RSS_Item::fromDomItem($this, $nodes->item($i)));
                if ($maxItems > 0 && count($this->items) >= $maxItems)
                    break;
            }
    }

    // --- Public methods ---

    public function addItem(NF_RSS_Item $item)
    {
        $this->items[] = $item;
    }

    public function setCategory($value, $domain = '')
    {
        $this->category       = $value;
        $this->categoryDomain = $domain;
    }

    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
    }

    public function setDocs($docs)
    {
        $this->docs = $docs;
    }

    public function setGenerator($generator)
    {
        $this->generator = $generator;
    }

    public function setImage($url, $title = '', $link = '', $width = '', $height = '', $description = '')
    {
        $this->image_url         = $url;
        $this->image_title       = $title;
        $this->image_link        = $link;
        $this->image_width       = $width;
        $this->image_height      = $height;
        $this->image_description = $description;
    }

    public function setLanguage($langCode)
    {
        $this->language = $langCode;
    }

    public function setLastBuildDate($date)
    {
        if (is_numeric($date))
            $this->lastBuildDate = NF_DateTime::fromTimestamp($date);
        else if (is_object($date) && $date instanceof NF_DateTime)
            $this->lastBuildDate = clone $date;
        else
            $this->lastBuildDate = new NF_DateTime($date);
    }

    public function setManagingEditor($email, $name)
    {
        $this->managingEditor = $email .
            ($name != '' ? " ($name)" : '');
    }

    public function setPubDate($date)
    {
        if (is_numeric($date))
            $this->pubDate = NF_DateTime::fromTimestamp($date);
        else if (is_object($date) && $date instanceof NF_DateTime)
            $this->pubDate = clone $date;
        else
            $this->pubDate = new NF_DateTime($date);
    }

    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    public function setWebMaster($email, $name = '')
    {
        $this->webMaster = $email .
            ($name != '' ? " ($name)" : '');
    }

    public function stripTags()
    {
        $vars = get_class_vars('NF_RSS');
        foreach(array_keys($vars) as $k)
        {
            $value = $this->$k;
            if (is_string($value))
                $this->$k = strip_tags($value);
        }

        $vars = get_class_vars('NF_RSS_Item');
        foreach($this->items as &$ref_item)
            foreach(array_keys($vars) as $k)
            {
                $value = $ref_item->$k;
                if (is_string($value))
                    $ref_item->$k = strip_tags($value);
            }
    }
}
