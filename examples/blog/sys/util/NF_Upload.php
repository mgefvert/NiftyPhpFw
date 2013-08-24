<?php

/**
 * The upload class simplifies handling of uploaded files.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Upload
{
    public $filename;
    public $filesize;
    public $extension;
    public $mimetype;
    public $localfile;
    public $error;

    /**
     *  Constructor for the upload class
     *
     *  @param string $formname Name of the form that submitted the file
     */
    public function __construct($formname)
    {
        if (!isset($_FILES[$formname]))
        {
            $this->error = UPLOAD_ERR_NO_FILE;
        }
        else
        {
            $data = $_FILES[$formname];

            $this->error     = $data['error'];
            $this->filename  = $data['name'];
            $this->mimetype  = $data['type'];
            $this->filesize  = (integer) $data['size'];
            $this->localfile = $data['tmp_name'];

            if ($this->filename)
            {
                $pathinfo        = pathinfo($this->filename);
                $this->filename  = $pathinfo['basename'];
                $this->extension = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
            }
        }
    }

    /**
     *
     */
    public function hasData()
    {
        return $this->error == UPLOAD_ERR_OK;
    }

    /**
     *  Save the uploaded file to something
     *
     *  @param string $filename Destination filename
     *
     *  @return int Result of move_uploaded_file
     */
    public function save($filename)
    {
        if ($this->error)
            throw new Exception($this->errorMessage());
        else
            return move_uploaded_file($this->localfile, $filename);
    }

    /**
     *  Load the contents of the uploaded file
     *
     *  @return string
     */
    public function load()
    {
        if ($this->error)
            throw new Exception($this->errorMessage());
        else if (!is_uploaded_file($this->localfile))
            throw new Exception('Not a valid uploaded file');
        else
            return file_get_contents($this->localfile);
    }

    /**
     *  Access to file upload error messages
     *
     *  @return string Error message
     */
    public function errorMessage()
    {
        switch($this->error)
        {
            case UPLOAD_ERR_OK:
                return "";

            case UPLOAD_ERR_INI_SIZE:
                return "File size exceeded system limits";

            case UPLOAD_ERR_FORM_SIZE:
                return "File size exceeded form limits";

            case UPLOAD_ERR_PARTIAL:
                return "The file was only partially uploaded";

            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded";

            case UPLOAD_ERR_NO_TMP_DIR:
                return "Temporary folder missing";

            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write temporary file to disk";

            default:
                return "Unrecognized file upload error " . $this->error;
        }
    }
}
