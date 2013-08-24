<?php

/**
 *  Class NF_IP provides a simple interface to TCP/IP connectivity.
 *
 *  PHP Version 5.3
 *
 *  @package    NiftyFramework
 *  @author     Mats Gefvert <mats@gefvert.se>
 *  @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_IP
{
    private $_handle = 0;
    private $_buffer = '';

    /**
     *  Sets the timeout for connect and read operations (seconds).
     */
    public $timeout = 30;

    /**
     *  Error message, if any.
     */
    public $error = '';

    /**
     *  Internal function: fetches all available data from the TCP stream and
     *  puts it in the internal buffer.
     *
     *  @return void
     */
    private function _fetchdata()
    {
        do {
            $data = stream_get_contents($this->_handle);
            $this->_buffer .= $data;
        } while ($data != "");
    }

    /**
     *  Connect to a remote host. Waits until success.
     *
     *  @param string $host Hostname
     *  @param int    $port Port
     *
     *  @return bool
     */
    public function connect($host, $port)
    {
        if (($this->_handle = fsockopen($host, $port, $errno, $this->error,
                                        $this->timeout)) == false)
            return false;

        stream_set_blocking($this->_handle, 0);
        stream_set_timeout($this->_handle, $this->timeout);
        return true;
    }

    /**
     *  Disconnect. Clears all buffers and handles.
     *
     *  @return void
     */
    public function disconnect()
    {
        $this->_buffer = '';

        if ($this->_handle != 0) {
            fclose($this->_handle);
            $this->_handle = 0;
        }
    }

    /**
     *  Returns the connected status
     *
     *  @return bool
     */
    public function connected()
    {
        return $this->_handle != 0;
    }

    /**
     *  Sends a string to the remote host.
     *
     *  @param string $str Data to send
     *
     *  @return void
     */
    public function send($str)
    {
        if ($this->_handle != 0) {
            fwrite($this->_handle, $str);
            fflush($this->_handle);  // For good measure
        }
    }

    /**
     *  Waits for data to become available or until timeout.
     *
     *  @return void
     */
    public function waitfor()
    {
        $t0 = time() + $this->timeout;

        do {
            $this->_fetchdata();
            if ($this->_buffer == '')
                usleep(10 * 1000);
        } while ($this->_buffer == '' && $t0 >= time());
    }

    /**
     *  Is the buffer empty?
     *
     *  @return bool
     */
    public function eof()
    {
        $this->_fetchdata();

        return $this->_buffer == '';
    }

    /**
     *  Reads data. If no data is available, returns immediately with an
     *  empty string.
     *
     *  @return string
     */
    public function read()
    {
        $this->_fetchdata();

        $data          = $this->_buffer;
        $this->_buffer = '';

        return $data;
    }

    /**
     *  Reads data up to and including a specific end-of-line. Waits for data to
     *  become available or until timeout.
     *
     *  @param string $eol End of line character to use for input
     *
     *  @return string
     */
    public function readeol($eol)
    {
        $t0 = time() + $this->timeout;
        while ($t0 >= time()) {
            $this->_fetchdata();

            $n = strpos($this->_buffer, $eol);
            if (is_int($n)) {
                $n += strlen($eol);

                $data          = substr($this->_buffer, 0, $n);
                $this->_buffer = substr($this->_buffer, $n);

                return $data;
            }

            usleep(10 * 1000);
        }

        return "";
    }
}
