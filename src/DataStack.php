<?php

namespace Lemmon;

class DataStack implements \Iterator, \ArrayAccess, \Countable
{
    private $_data;
    private $_keys;
    private $_i;
    private $_n;


    function __construct(array $data = [])
    {
        $this->_data = $data;
        $this->_keys = array_keys($data);
        $this->_i = 0;
        $this->_n = count($data);
    }


    function getFirst()
    {
        return $this->_data ? $this->offsetGet($this->_keys[0]) : NULL;
    }


    function getKeys()
    {
        return $this->_keys;
    }


    function getArray()
    {
        return $this->_data;
    }


    function getJson()
    {
        return json_encode($this->_data);
    }


    function shuffle()
    {
        $res = [];
        $keys = $this->_keys;
        shuffle($keys);
        foreach ($keys as $key) {
            $res[$key] = $this->_data[$key];
        }
        return new $this($res);
    }


    function sum($field = NULL)
    {
        return array_sum($field ? $this->getFields($field)->getArray() : $this->_data);
    }


    function getFields($field)
    {
        return new $this(array_map(function($item) use ($field) {
            return $item[$field];
        }, $this->_data));
    }


    function filter($filters)
    {
        return new $this(array_filter(array_map(function($item) use ($filters) {
            foreach ($filters as $filter => $value) {
                if (!($item = $this->_filter($item, explode('.', $filter), $value))) {
                    return FALSE;
                }
            }
            return $item;
        }, $this->_data)));
    }


    private function _filter($item, array $filter, $value) {
        if (!$this->_isArrayLike($item)) {
            return FALSE;
        }
        $current = array_shift($filter);
        if ('*' == $current) {
            return array_filter(array_map(function($item) use ($filter, $value) {
                return _filter($item, $filter, $value);
            }, $item));
        } elseif (isset($item[$current])) {
            if ($filter) {
                if ($this->_isArrayLike($item[$current]) and $res = _filter($item[$current], $filter, $value)) {
                    $item[$current] = $res;
                    return $item;
                } else {
                    return FALSE;
                }
            } elseif ($item[$current] == $value) {
                return $item;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }


    private function _isArrayLike($item)
    {
        return is_array($item) or (is_object($item) and $item instanceof \ArrayAccess);
    }


    function count()
    {
        return $this->_n;
    }


    function rewind()
    {
        $this->_i = 0;
    }


    function current()
    {
        return $this->_data[$this->_keys[$this->_i]];
    }


    function key()
    {
        return $this->_keys[$this->_i];
        return $this->_keys[$this->_i];
    }


    function next()
    {
        ++$this->_i;
    }


    function valid() {
        return isset($this->_keys[$this->_i]);
    }


    function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_data);
    }


    function offsetGet($offset)
    {
        return is_array($res = $this->_data[$offset]) ? new self($res) : $res;
    }


    function offsetSet($offset, $value) {}
    function offsetUnset($offset) {}


    /*
    public function getIterator()
    {
        return $this->_data;
    }
    */
}