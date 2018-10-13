<?php
/**
 * Created by PhpStorm.
 * User: vicha
 * Date: 19/08/2018
 * Time: 21:37
 */

namespace App\ApiModule\Model;

interface IApiModelInterface {
    
//    function __construct( $data );
    
    /**
     * Filter prepared response to set only allowed data
     * and map needed columns
     *
     * @param $response
     *
     * @return mixed
     */
    function filterResponse( array $response, array $map );
    
    /**
     * Here happens almost all action about data.
     * Fill the data from the presenter request and perform checkings with mapper and allowed values.
     * Set finally data variable if everything is ok to be used for next methods.
     *
     * @param $requestData
     *
     * @return mixed
     */
    function setData( $data );
    
    function setError( $error );
    
    function setResponse( $response );
    
    /**
     * Send final result to caller.
     *
     * @param $result
     *
     * @return mixed
     */
    function result($request);
    
    /**
     * Return array with map of localColumnName => responseKeyName
     *
     * @return array
     */
    function getMapper();
    
    /**
     * Return multidimensional array with allowed attributes
     * [
     *      'name' => 'id',
     *      'required' => true,
     *      'type' => 'integer' //boolean, float, array, etc...
     * ]
     *
     * @return array
     */
    function getDataScheme();
    
    function validateData( $validation, $required = false );
    
    function getValidationFields( $required = false );
    
    function arrayKeysNeeded( array $data, $requiredKeys );
    
    function removeNotAllowed( $data, $validation );
}