<?php

/**
 * NF_PdoDatabase provides connectivity to PDO databases
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_PdoDatabase implements NF_IDatabase
{
    /**
     * @var PDO
     */
    public $pdo = null;

    /**
     * @var NF_ConnectionInfo
     */
    public $connection = null;

    /**
     *  Log a query to the error log, if the configuration says so.
     *
     *  @param string $sql SQL Query
     */
    protected function logQuery($sql, $params = null)
    {
        if (NF::config()->debug->sql)
            error_log('SQL: ' . $sql . (!empty($params) ? print_r($params, true) : ''));
    }

    /**
     * Verify that the connection is active.
     *
     * @throws NF_Exception
     */
    protected function assertOpen()
    {
        if ($this->pdo === null)
            throw new NF_Exception("PDO is null");


        if (!$this->pdo || !($this->pdo instanceof PDO))
            throw new NF_Exception("Operation attempted on closed database connection");
    }

    /**
     *  Connect to the database with these parameters
     *
     *  @param NF_ConnectionInfo $connection Connection parameter object
     */
    public function __construct(NF_ConnectionInfo $connection)
    {
        $this->connection = $connection;

        $this->pdo = new PDO($connection->getDSN(), $connection->login, $connection->password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     *  Deallocate resources
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     *  Close the connection.
     *
     *  @return void
     */
    public function close()
    {
        $this->pdo = null;
    }

    /**
     *  Is the connection open or not?
     *
     *  @return bool
     */
    public function isOpen()
    {
        return $this->pdo != null;
    }

    // Query methods

    /**
     *  Run a query and return a result set
     *
     *  @param string $query SQL Query
     *  @return NF_PdoResult
     */
    public function query($query, $params = null)
    {
        $this->assertOpen();
        $this->logQuery($query, $params);
        return new NF_PdoResult($this->pdo, $query, $params);
    }

    /**
     *  Run a query and fetch the whole result set as an array of associative
     *  arrays
     *
     *  @param string $query SQL Query to run
     *  @return array An array containing associative arrays, one per row
     */
    public function queryAsArray($query, $params = null)
    {
        $q = $this->query($query, $params);

        $result = $q->fetchAllAsArray();
        $q->close();

        return $result;
    }

    /**
     *  Run a query and fetch the whole result set as an array of stdClass
     *  objects
     *
     *  @param string $query
     *  @return array An array containing stdClass objects, one per row
     */
    public function queryAsObjects($query, $params = null)
    {
        $q = $this->query($query, $params);

        $result = $q->fetchAllAsObjects();
        $q->close();

        return $result;
    }

    /**
     *  Run a query and fetch the result set consisting of only one field; the
     *  whole table will be returned as an array containing data values, one item
     *  per row
     *
     *  @param string $query SQL Query to run
     *  @return array A list of data values from the result set
     */
    public function querySingleValueArray($query, $params = null)
    {
        $q = $this->query($query, $params);

        $result = $q->fetchSingleValueArray();
        $q->close();

        return $result;
    }

    /**
     *  Run a query and fetch a single value (first field of the first row)
     *
     *  @param string $query SQL Query to run
     *  @return mixed Scalar value
     */
    public function queryScalar($query, $params = null)
    {
        $q = $this->query($query, $params);

        $result = $q->fetchScalar();
        $q->close();

        return $result;
    }

    /**
     *  Run a query and fetch lookup data from the result - a data set consisting
     *  of several rows with two fields only: key => value.
     *
     *  The data is returned as an ordinary associate array containing the
     *  key/value pairs from the dataset.
     *
     *  @param string $query SQL Query to run
     *  @return array Associative array with lookup values
     */
    public function queryLookup($query, $params = null)
    {
        $q = $this->query($query, $params);

        $result = $q->fetchLookup();
        $q->close();

        return $result;
    }

    /**
     *  Execute a query without returning data
     *
     *  @param string $query SQL Query
     *  @return void
     */
    public function execute($query, $params = null)
    {
        $this->assertOpen();
        $this->logQuery($query, $params);

        if ($params === null)
            return $this->pdo->exec($query);
        else
        {
            if (!is_array($params))
                $params = array($params);

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        }
    }

    // For convenience

    /**
     *  Convert a timestamp to a MySQL-specific string
     *
     *  @param int $timestamp Unix timestamp
     *  @return string
     */
    public function date($timestamp)
    {
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     *  Quotes a parameter according to MySQL conventions
     *
     *  @param string $str The string to be quoted
     *  @return string
     */
    public function quote($str)
    {
        $this->assertOpen();
        return $this->pdo->quote($str);
    }

    public function quoteField($field)
    {
        $fields = explode('.', $field);
        foreach($fields as &$ref_x)
            $ref_x = "`$ref_x`";

        return implode('.', $fields);
    }

    /**
     *  Returns the last inserted ID
     *
     *  @return int
     */
    public function lastInsertId()
    {
        $this->assertOpen();
        return $this->pdo->lastInsertId();
    }

    public function begin()
    {
        $this->assertOpen();
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->assertOpen();
        $this->pdo->commit();
    }

    public function rollback()
    {
        $this->assertOpen();
        $this->pdo->rollBack();
    }

    public function transaction($callback)
    {
        $this->begin();
        try
        {
            $callback();
            $this->commit();
        }
        catch(Exception $e)
        {
            $this->rollback();
            throw $e;
        }
    }
}
