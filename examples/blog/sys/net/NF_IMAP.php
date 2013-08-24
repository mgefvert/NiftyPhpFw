<?php

/**
 * Provides a basic wrapper around IMAP connectivity and simplifies operations
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_IMAP
{
    public $resource = null;

    public function __construct($connectString, $username, $password, $modifiers = 0)
    {
        if (($this->resource = imap_open($connectString, $username, $password, $modifiers)) == false)
        {
            throw new Exception(pr(imap_errors()));

        }
    }

    public function __destruct()
    {
        imap_close($this->resource);
    }

    /* Helper functions */

    protected function extractTextFromMultipart($msgId, $options, $nest, $item, &$text, &$encoding)
    {
        foreach ($item->parts as $id => $part)
        {
            if ($part->type == TYPETEXT)
            {
                $text     = $this->fetchBodyPart($msgId, $nest . ($id+1), $options);
                $encoding = $part->encoding;

                return true;
            }
            else if ($part->type == TYPEMULTIPART)
            {
                if ($this->extractTextFromMultipart($msgId, $options, $nest . ($id+1) . '.',
                        $part, $text, $encoding))
                    return true;
            }
        }

        return false;
    }


    public function deleteMessage($id)
    {
        imap_delete($this->resource, $id);
    }

    public function countAttachments($msgId)
    {
        $structure = $this->fetchStructure($msgId);
        $count     = 0;

        if ($structure->type == TYPEMULTIPART)
        {
            foreach ($structure->parts as $part)
                if ($part->type >= TYPEMESSAGE)
                    $count++;
        }

        return $count;
    }

    public static function decodeMimeHeader($text)
    {
        $data = imap_mime_header_decode($text);

        $result = '';
        foreach($data as $d)
            if ($d->charset == 'default')
                $result .= $d->text;
            else
                $result .= @iconv($d->charset, 'ISO-8859-1//TRANSLIT', $d->text);

        return $result;
    }

    public function fetchAttachments($msgId, $options = 0)
    {
        $structure = $this->fetchStructure($msgId);
        $result    = array();

        if ($structure->type == TYPEMULTIPART)
        {
            foreach ($structure->parts as $id => $part)
                if ($part->type >= TYPEMESSAGE)
                {
                    $data = $this->fetchBodyPart($msgId, $id+1, $options);

                    if ($part->encoding == ENCBASE64)
                        $data = imap_base64($data);
                    else if ($part->encoding == ENCQUOTEDPRINTABLE)
                        $data = imap_qprint($data);

                    $item = array();

                    if (isset($part->dparameters))
                        foreach($part->dparameters as $parameter)
                            $item[strtolower($parameter->attribute)] = $parameter->value;

                    if (isset($part->parameters))
                        foreach($part->parameters as $parameter)
                            $item[strtolower($parameter->attribute)] = $parameter->value;

                    $item['data'] = $data;
                    $item['size'] = strlen($data);
                    $item['type'] = $part->subtype;

                    $result[] = $item;
                }
        }

        return $result;
    }

    public function fetchBody($msgId, $options = 0)
    {
        return imap_body($this->resource, $msgId, $options);
    }

    public function fetchBodyPart($msgId, $partNo, $options = 0)
    {
        return imap_fetchbody($this->resource, $msgId, $partNo, $options);
    }

    public function fetchBodyPlainText($msgId, $options = 0)
    {
        $structure = $this->fetchStructure($msgId);
        $text      = '';
        $encoding  = null;

        if ($structure->type == TYPETEXT)
        {
            $text     = $this->fetchBody($msgId);
            $encoding = $structure->encoding;
        }
        else if ($structure->type == TYPEMULTIPART)
        {
            if (!$this->extractTextFromMultipart($msgId, $options, '', $structure, $text, $encoding))
                return false;
        }
        else
            return false;

        if ($text != '' && $encoding != null)
        {
            if ($encoding == ENCBASE64)
                $text = imap_base64($text);
            else if ($encoding == ENCQUOTEDPRINTABLE)
                $text = imap_qprint($text);
        }

        return $text;
    }

    public function fetchHeader($msgId)
    {
        return imap_header($this->resource, $msgId);
    }

    public function fetchMessageIds()
    {
        $result = array();

        foreach(imap_headers($this->resource) as $line)
            if (preg_match('|^(\w*)([\s\d]+)\)|', trim($line), $matches))
                $result[] = (int)$matches[2];

        return $result;
    }

    public function fetchOverview($sequence, $options = 0)
    {
        return imap_fetch_overview($this->resource, $sequence, $options);
    }

    public function fetchStructure($msgId)
    {
        return imap_fetchstructure($this->resource, $msgId);
    }

    public function moveMessage($destMailbox, $msglist)
    {
        return imap_mail_move($this->resource, $msglist, $destMailbox);
    }

    public function numMessages()
    {
        return imap_num_msgs($this->resource);
    }
}
