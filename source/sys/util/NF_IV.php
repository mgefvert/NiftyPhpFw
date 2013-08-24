<?php

/**
 * IV provides cryptographic functions to preserve data across GET/POST calls
 * and guards against CSRF attacks.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_IV extends NF_Elements
{
    private $iv = null;

    public function __construct($autoPostback = true)
    {
        if ($autoPostback && NF::request()->isPost())
            $this->restore();
    }

    public function __toString()
    {
        return $this->inject();
    }

    public function restore()
    {
        if (!NF::request()->isPost())
            throw new NF_EMethodNotAllowed('Form not submitted through POST');

        $b64 = explode('@', NF::request()->_iv);
        if (count($b64) != 2)
            throw new NF_ESecurityViolation('Form submission IV failure.');

        $data     = base64_decode($b64[0]);
        $this->iv = base64_decode($b64[1]);

        if (!$this->iv)
            throw new NF_ESecurityViolation('No IV value submitted.');

        $data = NF_Password::decrypt(NF::session()->key, $this->iv, $data);

        if (strlen($data) < 20)
            throw new NF_ESecurityViolation('Form submission hash failure.');

        $hash = substr($data, -20);
        $data = substr($data, 0, -20);

        if (sha1($data, true) != $hash)
            throw new NF_ESecurityViolation('Form submission hash failure.');

        $this->_elem = unserialize($data);
    }

    public function getValue()
    {
        if (!$this->iv)
            $this->iv = NF_Password::generateIV();

        $data = serialize($this->_elem);
        $data .= sha1($data, true);

        return base64_encode(NF_Password::encrypt(NF::session()->key, $this->iv, $data)) . '@' . base64_encode($this->iv);
    }

    public function inject($id = null)
    {
        if ($id !== null)
            $id = 'id="' . html($id) . '"';

        $value = $this->getValue();
        return "<input $id type=\"hidden\" class=\"_iv\" name=\"_iv\" value=\"$value\">";
    }
}
