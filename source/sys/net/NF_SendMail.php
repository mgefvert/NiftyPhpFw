<?php

/**
 * Wraps around the PHP sendmail function and composes text or HTML messages
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_SendMail
{
    protected $to = array();
    protected $cc = array();
    protected $bcc = array();
    protected $from;
    protected $subject;
    protected $headers = array();
    protected $text;
    protected $html;
    protected $files = array();

    protected $_emails = array();
    protected $_headers;
    protected $_body;
    protected $_subject;

    protected static $testEmailAddress = null;

    public static function setTestEmail($email)
    {
        self::$testEmailAddress = $email;
    }

    public static function isTestMode()
    {
        return self::$testEmailAddress != null;
    }

    public function addTo($to)
    {
        $this->to = array_merge($this->to, $this->parseEmails($to, true));
    }

    public function addCc($cc)
    {
        $this->cc = array_merge($this->cc, $this->parseEmails($cc, false));
    }

    public function addBcc($bcc)
    {
        $this->bcc = array_merge($this->bcc, $this->parseEmails($bcc, false));
    }

    public function setFrom($from)
    {
        $this->from = array_shift($this->parseEmails($from, false));
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function addHeader($header)
    {
        list($l1, $l2) = explode(':', $header, 2);

        $this->headers[trim($l1)] = trim($l2);
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function setHtml($html)
    {
        $this->html = $html;
    }

    public function addFile($filename, $filedata)
    {
        $this->files[$filename] = $filedata;
    }

    public function send()
    {
        $this->buildHeaders();
        $this->buildBody();

        $emails  = implode(', ', $this->_emails);
        $headers = implode("\r\n", $this->_headers);

        if (!mail($emails, null, $this->_body, $headers))
            throw new Exception('Sending mail failed');
    }

    // Protected methods

    protected function parseEmails($emails, $send_to)
    {
        $emails = array_filter(explode(',', $emails));
        $result = array();
        foreach($emails as $email)
        {
            if (preg_match('|(.*)<(.*)>|U', $email, $matches))
            {
                $name = mb_encode_mimeheader(trim($matches[1]), 'ISO-8859-1', 'Q');
                $email = self::$testEmailAddress ?: trim($matches[2]);
                $result[] = "$name <$email>";
                if ($send_to)
                    $this->_emails[] = $email;
            }
            else
            {
                $email = self::$testEmailAddress ?: trim($email);
                $result[] = $email;
                if ($send_to)
                    $this->_emails[] = $email;
            }
        }

        return $result;
    }

    protected function buildHeaders()
    {
        $this->_headers = array();
        $this->_headers[] = 'To: ' . implode(', ', $this->to);
        $this->_headers[] = 'From: ' . $this->from;
        $this->_headers[] = 'Subject: ' . mb_encode_mimeheader($this->subject, 'ISO-8859-1', 'Q');

        $cc = implode(', ', $this->cc);
        if ($cc)
            $this->_headers[] = 'Cc: ' . $cc;

        $bcc = implode(', ', $this->bcc);
        if ($bcc)
            $this->_headers[] = 'Bcc: ' . $bcc;
    }

    protected function buildBody()
    {
        if (!$this->html && empty($this->files))
        {
            $this->_headers[] = 'Content-Type: text/plain; charset="iso-8859-1"';
            $this->_headers[] = 'Content-Transfer-Encoding: quoted-printable';
            $this->_body = imap_8bit($this->text);
            return;
        }

        $boundary = md5(date('r'));
        $this->_headers[] = 'Content-Type: multipart/mixed; boundary="msg-mixed-' . $boundary . '"';

        $text = $this->text;
        $html = $this->html;

        if ($html && !$text)
            $text = strip_tags($html);

        $text = imap_8bit($text);
        $html = imap_8bit($html);

        if ($html)
        {
            $this->_body = <<<EOF
--msg-mixed-$boundary
Content-Type: multipart/alternative; boundary="msg-alt-$boundary"

--msg-alt-$boundary
Content-Type: text/plain; charset="iso-8859-1"
Content-Transfer-Encoding: quoted-printable

$text

--msg-alt-$boundary
Content-Type: text/html; charset="iso-8859-1"
Content-Transfer-Encoding: quoted-printable

$html

--msg-alt-$boundary--


EOF;
        }
        else
        {
            $this->_body = <<<EOF
--msg-mixed-$boundary
Content-Type: text/plain; charset="iso-8859-1"
Content-Transfer-Encoding: quoted-printable

$text


EOF;
        }

        foreach($this->files as $k => $v)
        {
            $v = chunk_split(base64_encode($v));
            $this->_body .= <<<EOF
--msg-mixed-$boundary
Content-Type: application/binary; name="$k"
Content-Transfer-Encoding: base64
Content-Disposition: attachment

$v

EOF;
        }

        $this->_body .= <<<EOF
--msg-mixed-$boundary--

EOF;
    }
}
