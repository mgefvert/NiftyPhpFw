<?php

/**
 * NF_IDatabase is an interface that describes functionality for database objects.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
interface NF_IDatabase
{
    /**
     *  Forcibly close the connection
     *
     *  @return void
     */
    public function close();

    /**
     *  Is the connection open or not?
     *
     *  @return bool
     */
    public function isOpen();

    // Functions for running a query - returns an NF_IResult

    /**
     *  Run a query and return a result set
     *
     *  @param string $query SQL Query
     *
     *  @return NF_IResult
     */
    public function query($query, $params = null);

    /**
     *  Run a query and fetch the whole result set as an array of associative
     *  arrays
     *
     *  @param string $query SQL Query to run
     *
     *  @return array An array containing associative arrays, one per row
     */
    public function queryAsArray($query, $params = null);

    /**
     *  Run a query and fetch the whole result set as an array of stdClass
     *  objects
     *
     *  @param string $query
     *
     *  @return array An array containing stdClass objects, one per row
     */
    public function queryAsObjects($query, $params = null);

    /**
     *  Run a query and fetch the result set consisting of only one field; the
     *  whole table will be returned as an array containing data values, one item
     *  per row
     *
     *  @param string $query SQL Query to run
     *
     *  @return array A list of data values from the result set
     */
    public function querySingleValueArray($query, $params = null);

    /**
     *  Run a query and fetch a single value (first field of the first row)
     *
     *  @param string $query SQL Query to run
     *
     *  @return mixed Scalar value
     */
    public function queryScalar($query, $params = null);

    /**
     *  Run a query and fetch lookup data from the result - a data set consisting
     *  of several rows with two fields only: key => value.
     *
     *  The data is returned as an ordinary associate array containing the
     *  key/value pairs from the dataset.
     *
     *  @param string $query SQL Query to run
     *
     *  @return array Associative array with lookup values
     */
    public function queryLookup($query, $params = null);

    /**
     *  Execute a query without returning data
     *
     *  @param string $query SQL Query
     *
     *  @return void
     */
    public function execute($query, $params = null);

    // Tool functions

    /**
     *  Convert a timestamp to a database-specific string
     *
     *  @param int $timestamp Unix timestamp
     *
     *  @return string A string suitable for inserting into the database-specific
     *                 SQL query
     */
    public function date($timestamp);

    /**
     *  Quotes a parameter according to the default database settings
     *
     *  @param string $str The string to be quoted
     *
     *  @return string A string that can be inserted without risk into the
     *                 SQL query
     */
    public function quote($str);

    public function quoteField($field);

    /**
     *  Returns the last inserted ID
     *
     *  @return int
     */
    public function lastInsertId();

    /**
     *  Begin transaction
     *
     *  @return void
     */
    public function begin();

    /**
     *  Commit transaction
     *
     *  @return void
     */
    public function commit();

    /**
     *  Rollback transaction
     *
     *  @return void
     */
    public function rollback();

    public function transaction($callback);
}
