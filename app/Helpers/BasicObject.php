<?php namespace App\Helpers;
/**
 * Created by PhpStorm.
 * User: molinski
 * Date: 16/03/15
 * Time: 10:16
 */


class BasicObject {

    protected $_infoArray = [];

    public function get($property) {
        return $this->__get($property);
    }

    public function set($property, $value) {
        return $this->__set($property, $value);
    }

    public function seed($infoArray = []) {
        foreach ($infoArray as $key => $value) {
            $this->_infoArray[strtolower($key)] = $value;
        }
    }

    /**
     * Magic getter looks up the property and if it exists in the infoArray,
     *   returns it
     **/
    public function __get($property)
    {
        if (isset($this->_infoArray[strtolower($property)])) {
            return $this->_infoArray[strtolower($property)];
        } else {
            return null;
        }
    }

    /**
     * Magic setter sets (or updates) a property into the the object
     **/
    public function __set($property, $value)
    {
        $this->_infoArray[strtolower($property)] = $value;
    }

    /**
     * Magic isset to check the _infoArray as well as the member properties
     */
    public function __isset($property)
    {
        if (isset($this->_infoArray[strtolower($property)])) {
            return true;
        } else if (isset($this->$property)) {
            return true;
        } else {
            return false;
        }
    }
}