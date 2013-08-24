<?php

/**
 * NF_PdoResult allows access to a result from a PDO query.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_PdoResult implements NF_IResult
{
    /**
     * @var PDO
     */
    protected $pdo = null;
    /**
     * @var PDOStatement
     */
    public $res = null;

    // Public functions

    /**
     *  Public constructor
     *
     *  @param resource $pdo Database handle
     *  @param string   $query Query to execute
     */
    public function __construct($pdo, $query, $params = null)
    {
        $this->pdo = $pdo;

        if ($params === null)
            $this->res = $this->pdo->query($query);
        else
        {
            if (!is_array($params))
                $params = array($params);
            $this->res = $this->pdo->prepare($query);
            $this->res->execute($params);
        }
    }

    /**
     *  Deallocate resources
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     *  Close the result set and free up resources
     *
     *  @return void
     */
    public function close()
    {
        if ($this->res != null)
        {
            $this->res->closeCursor();
            $this->res = null;
        }
    }

    /**
     *  Return the row count of the result
     *
     *  @return int
     */
    public function count()
    {
        return $this->res->columnCount();
    }

    /**
     *  Fetch the next row as an associative array
     *
     *  @return array
     */
    public function fetch()
    {
        return $this->res->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch the next row as a numeric array
     *
     * @return array
     */
    public function fetchValues()
    {
        return $this->res->fetch(PDO::FETCH_NUM);
    }

    /**
     * Get information about the fields in the result set. Returns an array with
     * "name", "len", "numeric", "type" and "null".
     *
     * @return array
     */
    public function getFieldInfo()
    {
        $result = array();
        for ($i = 0; $i < $this->res->columnCount(); $i++)
            $result[] = $this->res->getColumnMeta($i);

        return $result;
    }

    /**
     *  Fetch the whole result set as an array of numeric arrays
     *
     *  @return array An array containing numeric arrays, one per row
     */
    public function fetchAllAsValues()
    {
        return $this->res->fetchAll(PDO::FETCH_NUM);
    }

    /**
     *  Fetch the whole result set as an array of associative arrays
     *
     *  @return array An array containing associative arrays, one per row
     */
    public function fetchAllAsArray()
    {
        return $this->res->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     *  Fetch the whole result set as an array of stdClass objects
     *
     *  @return array An array of stdClass objects
     */
    public function fetchAllAsObjects($class = null)
    {
        if ($class)
            return $this->res->fetchAll(PDO::FETCH_CLASS, $class);
        else
            return $this->res->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     *  Fetch a result set consisting of only one field; the whole table will
     *  be returned as an array containing data values, one item per row
     *
     *  @return array A list of data values from the result set
     */
    public function fetchSingleValueArray()
    {
        return $this->res->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     *  Fetch a single value (first field of the first row)
     *
     *  @return mixed Scalar value
     */
    public function fetchScalar()
    {
        return $this->res->fetch(PDO::FETCH_COLUMN, 0);
    }

    /**
     *  Fetch a lookup data set - a data set consisting of several rows with two
     *  fields only: key => value.
     *
     *  The data is returned as an ordinary associate array containing the
     *  key/value pairs from the dataset.
     *
     *  @return array Associative array with lookup values
     */
    public function fetchLookup()
    {
        return $this->res->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
