<?php
/**
 * Created by PhpStorm.
 * User: Victor
 * Date: 28/06/2018
 * Time: 7:32
 */

namespace VicHaunter\Nette\Api;

use App\Model\BaseModel;
use App\Model\Utils;
use Nette\Utils\FileSystem;
use Nuttilea\TableControl\EntityMapperConnector;

class BaseApiModel {
    
    protected $repositories = [];
    
    protected $debug = false;
    
    protected $data;
    protected $requestData;
    
    protected $error = ['code' => 0, 'message' => 'unknown'];
    
    protected $response;

//    public function __construct( $data ) {
//        $this->setData($data);
//    }
    public function setDebugMode( $bool = true ) {
        $this->debug = $bool ? true : false;
    }
    
    public function setError( $error ) {
        $this->error = $error;
    }
    
    public function setResponse( $response ) {
        $this->response = $response;
        $this->setError(false);
    }
    
    public function setRepository( $key, $repository ) {
        $this->repositories[ $key ] = $repository;
    }
    
    public function setData( $data ) {
        $this->requestData = $data;
        $this->storeData();
        //
        if ($missingFields = $this->arrayKeysNeeded($data, $this->getValidationFields(true))) {
            $this->error = ['error' => "Required fields ({$missingFields}) not set"];
            throw new ApiException($this->error['error']);
        }
        //
        $this->validateData($this->getValidationFields(true), true);
        $this->validateData($this->getValidationFields());
        //
        try {
            $hash = !empty($data['signature']) ? $data['signature'] : null;
            $response = json_decode(json_encode($this->removeNotAllowed($data, $this->getValidationFields())), true);
        } catch (\HttpResponseException $hre) {
            $this->error = ['error' => "Bad response"];
        }
        $data = $this->removeNotAllowed($data, array_merge($this->getValidationFields(true), $this->getValidationFields()));
        //
        $this->data = $data;
    }
    
    /**
     * Delete not allowed incoming attributes
     *
     * @param $data
     * @param $validation
     *
     * @return mixed
     */
    public function removeNotAllowed( $data, $validation ) {
        foreach ($data as $k => $v) {
            if (!array_key_exists($k, $validation)) {
                unset($data[ $k ]);
            }
        }
        return $data;
    }
    
    /**
     * Checks if multiple keys exist in an array and returns non existing ones in string or null
     *
     * @param array $data
     * @param array $requiredKeys
     *
     * @return bool
     */
    public function arrayKeysNeeded( array $data, $requiredKeys ) {
        $result = array_diff(array_keys($requiredKeys), array_keys($data));
        return $result ? implode(',', $result) : null;
    }
    
    /**
     * @return array [data,error]
     */
    public function result( $request ) {
        
        if ($this->error != false || $this->debug === true) {
            $result['status'] = 0;
            if (is_array($this->error) && (isset($this->error['message']) || isset($this->error['code']))) {
                $result['error']['message'] = isset($this->error['message']) ? $this->error['message'] : 'unknown';
                $result['error']['code'] = isset($this->error['code']) ? $this->error['code'] : 'unknown';
            } else {
                $result['error']['message'] = $this->error;
                $result['error']['code'] = 'unknown';
            }
        } else {
            $result['status'] = 1;
            $result['data'] = $this->response ? $this->response : [];
            if (is_subclass_of($result['data'], 'Nuttilea\\EntityMapper\\Entity')) {
                $result['data'] = $result['data']->toArray();
            }
            if (!is_array($result['data'])) {
                throw new ApiException('Please, give me an array in setResponse');
            }
        }
        
        //        $result['error'] = $this->error ? $this->error : null;
        return $result;
    }
    
    /**
     * Returns filtered array only with set values and key names modified as set in map
     * [
     *  'localKeyName' => 'responseKeyName',...
     * ]
     *
     * @param array || EntityMapper $data[]
     * @param array $map
     */
    public function filterResponse( array $rows, array $map ) {
        
        $result = [];
        foreach ($rows as $rowKey => $row) {
            $isInstance = $row instanceof EntityMapperConnector;
            if (is_array($row) || $isInstance) {
                $row = $isInstance ? $row->asArray() : $row;
            }
            foreach ($row as $col => $value) {
                if (array_key_exists($col, $map)) {
                    $result[ $rowKey ][ $map[ $col ] ] = $value;
                }
            }
        }
        
        return !empty($result) ? $result : null;
    }
    
    /**
     * Get required/not required fields in separated groups
     *
     * @param $validation
     *
     * @return mixed
     */
    public function getValidationFields( $required = false ) {
        $data['required'] = [];
        $data['noRequired'] = [];
        foreach ($this->getDataScheme() as $key => $value) {
            if (strpos($value, '!') !== false) {
                $data['required'][ $key ] = $value;
            } else {
                $data['noRequired'][ $key ] = $value;
            }
        }
        
        return $required ? $data['required'] : $data['noRequired'];
    }
    
    /**
     * Check if received data match with expected types
     *
     * @param      $data
     * @param      $validation
     * @param bool $required
     */
    public function validateData( $validation, $required = false ) {
        foreach ($this->requestData as $key => $value) {
            foreach ($validation as $validKey => $validValue) {
                if ($key == $validKey) {
                    $validValue = ltrim($validValue, '!');
                    if (($required && $value == '') ||
                        ($validValue == 'int' && !is_numeric($value)) ||
                        ($validValue == 'string' && !is_string($value)) ||
                        ($validValue == 'array' && !is_array($value)) ||
                        ($validValue == 'bool' && (!is_bool(Utils::isBool($value)))) ||
                        ($validValue == 'float' && !is_float($value))
                    ) {
                        $this->error = ['error' => "Wrong data set for {$key}"];
                        
                        return false;
                    }
                }
            }
        }
        
        return true;
    }
    
    public function storeData() {
        if ($GLOBALS['container']->parameters['debugMode']) {
            FileSystem::createDir(__ROOTDIR__."/temp/data/");
            FileSystem::write(__ROOTDIR__."/temp/data/".time().'.json', $this->requestData, null);
        }
    }
    
}