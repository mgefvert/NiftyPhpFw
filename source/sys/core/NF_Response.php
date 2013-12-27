<?php

/**
 * Encapsulates responses.
 *
 * PHP Version 5.3
 *
 * @package  NiftyFramework
 * @author   Mats Gefvert <mats@gefvert.se>
 * @license  http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Response extends NF_Elements
{
    public $template        = '';
    public $content         = '';
    public $title           = '';
    public $css             = array();
    public $js              = array();
    public $headers         = array();
    public $headTags        = array();
    public $cacheTime       = null;
    public $contentType     = null;
    public $contentEncoding = null;

    public $sent            = false;
    public $direct          = false;

    /**
     *  Constructor
     */
    public function __construct()
    {
        $cfg = NF::config();

        $this->template    = $cfg->main->template;
        $this->title       = $cfg->main->title;
        $this->contentType = $cfg->main->content_type ?: 'text/html';

        if (($css = $cfg->main->css) != '')
            if (is_array($css))
                $this->css = $css;
            else
                $this->css[] = $css;

        if (($js = $cfg->main->js) != '')
            if (is_array($js))
                $this->js = $js;
            else
                $this->js[] = $js;
    }

    public function requireCss($url)
    {
        if (!is_array($url))
            $url = array($url);

        foreach($url as $_url)
            if (!in_array($_url, $this->css))
                $this->css[] = $_url;
    }

    public function requireJs($url)
    {
        if (!is_array($url))
            $url = array($url);

        foreach($url as $_url)
            if (!in_array($_url, $this->js))
                $this->js[] = $_url;
    }

    public function addHttpHeader($header)
    {
        $this->headers[] = $header;
    }

    public function addHeadTag($html)
    {
        $this->headTags[] = $html;
    }

    /**
     *  Return a well-formatted text from the css[] array
     *
     *  @return string
     */
    protected function getCss()
    {
        $result = '';
        foreach($this->css as $css)
            $result .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css\">\r\n";

        return $result;
    }

    /**
     *  Return a well-formatted text from the js[] array
     *
     *  @return string
     */
    protected function getJs()
    {
        $result = '';
        foreach($this->js as $js)
            $result .= "<script language=\"JavaScript\" src=\"$js\"></script>\r\n";

        return $result;
    }

    /**
     *  Return a text containing all the tags from the headTags[] array
     *
     *  @return string
     */
    protected function getHeadTags()
    {
        return implode("\r\n", $this->headTags);
    }

    /**
     *  Send the response to the client. If storeCacheId is set, will also store
     *  the response in a cache ID for later requests.
     *
     *  @return void
     */
    public function send()
    {
        if ($this->sent)
            return;

        $this->sent = true;

        if ($this->contentType)
            $this->headers[] = 'Content-Type: ' . $this->contentType;

        if ($this->contentEncoding)
            $this->headers[] = 'Content-Encoding: ' . $this->contentEncoding;

        if ($this->cacheTime !== null)
        {
            $dt = gmdate('r', time() + $this->cacheTime);
            $this->headers[] = "Cache-control: max-age=" . $this->cacheTime;
            $this->headers[] = "Pragma: cache";
            $this->headers[] = "Expires: $dt";
        }

        foreach($this->headers as $header)
            header($header);

        if ($this->template != '') {
            $tmpl = NF_Template::load($this->template);

            $tmpl->css          = $this->getCss();
            $tmpl->js           = $this->getJs();
            $tmpl->head         = $this->getHeadTags();
            $tmpl->title        = $this->title;
            $tmpl->content      = $this->content;
            $tmpl->content_type = $this->contentType;
            $tmpl->minify       = false;  // Don't minify main template ... check later

            foreach($this->elements() as $k => $v)
                $tmpl->$k = $v;

            $result = $tmpl->parse();
        }
        else
            $result = $this->content;

        if (NF::config()->main->minify)
        {
            // Examine the output to see if we dare to minify it... If there are any <pre> tags
            // or CSS white-space definitions - don't touch it. We have already minified the main
            // portion anyway.
            if (stripos($result, '<pre') === false && stripos($result, 'white-space') === false)
                $result = NF_Text::minify($result);
        }

        header('Content-Length: ' . strlen($result));
        echo $result;

        if (rand(1, 50) == 1)
            NF::cache()->process();
    }

    /**
     *  Send a HTTP redirect now to the given url.
     *
     *  @param string $url URL to redirect to.
     */
    public function redirect($url)
    {
        header("Location: $url");
    }

    /**
     *  Send a HTML response that redirects to the given url.
     *  Used when you submit a form, for instance, and don't want the user to
     *  resubmit the form when you hit Refresh
     *
     *  @param string $url URL to redirect to.
     */
    public function slowRedirect($url)
    {
        $url = str_replace('"', '\\"', $url);

        $this->template = null;
        $this->content = <<<EOF
<html>
    <head>
        <meta http-equiv="refresh" content="0; $url">
    </head>
    <body>
        <script>
            location.href = "$url";
        </script>
    </body>
</html>
EOF;
    }

    /**
     *  Reset the Response object. This will clear out all predetermined values
     *  set from the configuration files.
     */
    public function reset()
    {
        $this->sent = false;

        foreach(get_object_vars($this) as $k => $v)
            if (is_array($this->$k))
                $this->$k = array();
            else
                $this->$k = null;

        parent::clear();

        $this->contentType = NF::config()->main->content_type;
    }

    /**
     *  Add data to the output buffer.
     *
     *  @param string $data
     */
    public function output($data)
    {
        $this->content .= $data;
    }

    /**
     *  Add data to the output buffer with a terminating <br>.
     *
     *  @param string $data
     */
    public function outputBr($data)
    {
        $this->content .= $data . "<br>\r\n";
    }

    /**
     * Streams a physical file to the end user.
     *
     * @param string $physicalFile Physical filename on the disk
     * @param string $mimetype
     * @param string $filename     Logical name of the file
     */
    public function streamFile($physicalFile, $mimetype = '', $filename = '')
    {
        if (($fp = fopen($physicalFile, 'rb')) === false)
            throw new Exception('Invalid file.');

        // This takes over the normal response
        $this->directOutput();

        $length = filesize($physicalFile);

        if (!$mimetype)
            $mimetype = 'application/octet-string';

        if ($filename)
            header("Content-Disposition: filename=\"{$filename}\"");
        header("Content-Type: $mimetype");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: $length");

        while( (!feof($fp)) && (connection_status()==0) )
        {
            set_time_limit(120);
            print(fread($fp, 1024*80));
            flush();
        }
        fclose($fp);
    }

    /**
     * Streams data to the end user as a file.
     *
     * @param string $physicalFile Physical filename on the disk
     * @param string $mimetype
     * @param string $filename     Logical name of the file
     */
    public function sendFile($data, $mimetype = '', $filename = '')
    {
        // This takes over the normal response
        $this->directOutput();

        if (!$mimetype)
            $mimetype = 'application/octet-string';

        if ($filename)
        {
            // Only a small subset of punctuation is allowed.
            $filename = preg_replace('/[^a-zA-Z0-9\.\-\_\@\$\(\)]/', '', $filename);
            header("Content-Disposition: attachment; filename={$filename}");
        }
        header("Content-Type: $mimetype");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . strlen($data));

        print($data);
        flush();
    }

    /**
     * Close all output buffering and cancel all response output. Suitable for
     * direct echoing to screen. Tries to fix PHP default buffering too.
     */
    public function directOutput()
    {
        $this->sent = true;
        $this->direct = true;
        while (ob_get_level() > 0)
            ob_end_clean();
    }
}
