<?php

/**
 * RSS Item encapsulates an individual Content Item with an RSS feed
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_RSS_Item
{
    // Optional fields
    public $title;
    public $link;
    public $description;
    public $author;
    public $category;
    public $category_domain;
    public $comments;
    public $enclosure_url;
    public $enclosure_length;
    public $enclosure_type;
    public $guid;
    public $guid_isPermaLink = true;
    public $pubDate;
    public $source;
    public $source_url;

    // RDF 1.0 Module: Content
    public $content;

    // --- Generate and parse content items ---

    public static function fromDomItem(NF_RSS $rss, $item)
    {
        $result = new NF_RSS_Item();

        $result->title            = $rss->xmlGetChildData($item, 'title');
        $result->link             = $rss->xmlGetChildData($item, 'link');
        $result->description      = html_entity_decode($rss->xmlGetChildData($item, 'description'), ENT_QUOTES);
        $result->author           = $rss->xmlGetChildData($item, 'author');
        $result->category         = $rss->xmlGetChildData($item, 'category');
        $result->category_domain  = $rss->xmlGetChildAttribute($item, 'category', 'domain');
        $result->comments         = $rss->xmlGetChildData($item, 'comments');
        $result->enclosure_url    = $rss->xmlGetChildAttribute($item, 'enclosure', 'url');
        $result->enclosure_length = $rss->xmlGetChildAttribute($item, 'enclosure', 'length');
        $result->enclosure_type   = $rss->xmlGetChildAttribute($item, 'enclosure', 'type');
        $result->guid             = $rss->xmlGetChildData($item, 'guid');
        $result->guid_isPermaLink = $rss->xmlGetChildAttribute($item, 'enclosure', 'isPermaLink');
        $result->source           = $rss->xmlGetChildData($item, 'source');
        $result->source_url       = $rss->xmlGetChildAttribute($item, 'source', 'url');

        $result->content          = $rss->xmlGetChildData($item, 'content:encoded');

        if (($data = $rss->xmlGetChildData($item, 'pubDate')) != '')
            $result->setPubDate($data);

        return $result;
    }

    public function buildXml($rss, $doc, $channel)
    {
        if ($this->title == '' && $this->description == '')
            throw new Exception('RSS: Either title or description must contain a value');

        $item = $rss->xmlCreateChild($doc, $channel, 'item');

        if ($this->title != '')
            $rss->xmlCreateChild($doc, $item, 'title', $this->title);
        if ($this->link != '')
            $rss->xmlCreateChild($doc, $item, 'link', $this->link);
        if ($this->description != '')
            $rss->xmlCreateChildCDATA($doc, $item, 'description', $this->description);
        if ($this->author != '')
            $rss->xmlCreateChild($doc, $item, 'author', $this->author);
        if ($this->category != '')
            $rss->xmlCreateChild($doc, $item, 'category', $this->category, array(
                'domain' => $this->category_domain
            ));
        if ($this->comments != '')
            $rss->xmlCreateChild($doc, $item, 'comments', $this->comments);
        if ($this->enclosure_url != '')
            $rss->xmlCreateChild($doc, $item, 'enclosure', '', array(
                'url'    => $this->enclosure_url,
                'length' => $this->enclosure_length,
                'type'   => $this->enclosure_type
            ));
        if ($this->guid != '')
            $rss->xmlCreateChild($doc, $item, 'guid', $this->guid, array(
                'isPermaLink' => $this->guid_isPermaLink ? 'true' : 'false'
            ));
        if ($this->pubDate != '')
            $rss->xmlCreateChild($doc, $item, 'pubDate', $this->pubDate->format('r'));
        if ($this->source != '')
            $rss->xmlCreateChild($doc, $item, 'source', $this->source, array(
                'url' => $this->source_url
            ));

        if ($this->content != '')
        {
            if ($channel->parentNode->hasAttribute('xmlns:content') == false)
                $channel->parentNode->setAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');

            $rss->xmlCreateChild($doc, $item, 'content:encoded', $this->content);
        }
    }

    // --- Some rapid accessor functions ---

    public function getEffectiveContent()
    {
        return $this->content != '' ? $this->content : $this->description;
    }

    public function getEffectiveLink()
    {
        if ($this->link != '')
            return $this->link;

        if ($this->guid_isPermaLink)
            return $this->guid;

        return '';
    }

    // --- Public setters, to ensure data validity ---

    public function setAuthor($email, $name = '')
    {
        $this->author = $email .
            ($name != '' ? " ($name)" : '');
    }

    public function setCategory($value, $domain = '')
    {
        $this->category       = $value;
        $this->categoryDomain = $domain;
    }

    public function setComments($url)
    {
        $this->comments = $url;
    }

    public function setContent($text)
    {
        $this->content = $text;
    }

    public function setDescription($text)
    {
        $this->description = $text;
    }

    public function setEnclosure($url, $length, $mimeType)
    {
        $this->enclosure_url    = $url;
        $this->enclosure_length = $length;
        $this->enclosure_type   = $mimeType;
    }

    public function setGuid($guid, $isPermaLink = true)
    {
        $this->guid        = $guid;
        $this->isPermaLink = $isPermaLink;
    }

    public function setLink($url)
    {
        $this->link = $url;
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

    public function setSource($url, $title)
    {
        $this->source     = $title;
        $this->source_url = $url;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
}
