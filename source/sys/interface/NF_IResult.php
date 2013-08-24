<?php

/**
 * NF_IResult describes common functionality for result objects.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
interface NF_IResult
{
    /**
     *  Close the result set and free up resources
     *
     *  @return void
     */
    public function close();

    /**
     *  Return the row count of the result
     *
     *  @return int
     */
    public function count();

    /**
     *  Fetch the next row as an associative array
     *
     *  @return array
     */
    public function fetch();

    /**
     *  Fetch the next row as a numeric array
     *
     *  @return array
     */
    public function fetchValues();

    /**
     *  Fetch the whole result set as an array of associative arrays
     *
     *  @return array An array containing associative arrays, one per row
     */
    public function fetchAllAsValues();

    /**
     *  Fetch the whole result set as an array of associative arrays
     *
     *  @return array An array containing associative arrays, one per row
     */
    public function fetchAllAsArray();

    /**
     *  Fetch the whole result set as an array of associative arrays
     *
     *  @return array An array containing associative arrays, one per row
     */
    public function fetchAllAsObjects($class = null);

    /**
     *  Fetch a result set consisting of only one field; the whole table will
     *  be returned as an array containing data values, one item per row
     *
     *  @return array A list of data values from the result set
     */
    public function fetchSingleValueArray();

    /**
     *  Fetch a single value (first field of the first row)
     *
     *  @return mixed Scalar value
     */
    public function fetchScalar();

    /**
     *  Fetch a lookup data set - a data set consisting of several rows with two
     *  fields only: key => value.
     *
     *  The data is returned as an ordinary associate array containing the
     *  key/value pairs from the dataset.
     *
     *  @return array Associative array with lookup values
     */
    public function fetchLookup();

    /**
     * Get information about the fields in the result set. Returns an array with
     * "name", "len", "numeric", "type" and "null".
     *
     * @return array
     */
    public function getFieldInfo();
}
