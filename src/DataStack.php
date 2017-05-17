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


    function getLast()
    {
        return $this->_data ? $this->offsetGet($this->_keys[$this->_n - 1]) : NULL;
    }


    function getEq(int $i)
    {
        return $this->_data ? $this->offsetGet($this->_keys[$i]) : NULL;
    }


    function getKeys()
    {
        return $this->_keys;
    }


    function getData()
    {
        return $this->_data;
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


    function unique()
    {
      return new $this(array_unique($this->_data));
    }


    function slice(int $a, int $b = NULL)
    {
        if (isset($b)) {
            $offset = $a;
            $length = $b;
        } else {
            $offset = 0;
            $length = $a;
        }
        return new $this(array_slice($this->_data, $offset, $length, TRUE));
    }


    function sum($field = NULL)
    {
        return array_sum($field ? $this->getFields($field)->getArray() : $this->_data);
    }


    function _getField($res, $field)
    {
        $field = explode('.', $field);
        while ($field and $this->_isArrayLike($res) and $_field = array_shift($field) and isset($res[$_field])) {
            $res = $res[$_field];
        }
        return !$field ? $res : NULL;
    }


    function getFields($field)
    {
        return new $this(array_map(function($item) use ($field) {
            return $this->_getField($item, $field);
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


    private function _filter($item, array $filter, $value, $op = 'eq')
    {
        if (!$this->_isArrayLike($item)) {
            return FALSE;
        }
        // current value
        $current = array_shift($filter);
        // operator
        if ('!' == $current{0}) {
            $current = substr($current, 1);
            $op = 'neq';
        }
        // filter
        if ('*' == $current) {
            return array_filter(array_map(function($item) use ($filter, $value, $op) {
                return $this->_filter($item, $filter, $value, $op);
            }, $item));
        } elseif (isset($item[$current])) {
            if ($filter) {
                if ($this->_isArrayLike($item[$current]) and $res = $this->_filter($item[$current], $filter, $value, $op)) {
                    $item[$current] = $res;
                    return $item;
                } else {
                    return FALSE;
                }
            } elseif ('eq' === $op && $item[$current] == $value) {
                return $item;
            } elseif ('neq' === $op && $item[$current] != $value) {
                return $item;
            } else {
                return FALSE;
            }
        } elseif ('neq' === $op and NULL !== $value) {
            return $item;
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


    function current($yo = TRUE)
    {
        return $yo ? $this->offsetGet($this->_keys[$this->_i]) : $this->_data[$this->_keys[$this->_i]];
    }


    function key()
    {
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
        return is_array($_ = $this->_data[$offset]) ? new self($_) : $_;
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