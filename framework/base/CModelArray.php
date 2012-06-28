<?php
/**
 * CModelArray class file.
 *
 * @author Rangel Reale <rangelreale@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CModelArray is model that stores properties in an associative array (CMap).
 *
 * @author Rangel Reale <rangelreale@gmail.com>
 * @version $Id$
 * @package system.base
 * @since 1.2
 */
class CModelArray extends CModel implements Countable
{
	protected $data;
    
	public function __construct($parentModel = null, $parentAttribute = null, $data = null)
	{
		//parent::__construct();
		$this->data=new CMap;
        if ($data !== null)
            $this->data->copyFrom($data);
		$this->setParentModel($parentModel, $parentAttribute);
		$this->init();
	}

	public function init()
	{
		
	}

	/**
	 * PHP getter magic method.
	 * This method is overridden so that AR attributes can be accessed like properties.
	 * @param string property name
	 * @return mixed property value
	 * @see getAttribute
	 */
	public function __get($name)
	{
		if(isset($this->data[$name]))
			return $this->data[$name];
		else
			return parent::__get($name);
	}

	/**
	 * Returns the list of attribute names of the model.
	 * @return array list of attribute names.
	 */
	public function attributeNames()
	{
		if ($this->data instanceof CMap)
			return $this->data->keys;
		return array_keys($this->data);
	}

	/**
	 * Sets the attribute values in a massive way.
	 * @param array attribute values (name=>value) to be set.
	 * @param boolean whether the assignments should only be done to the safe attributes.
	 * A safe attribute is one that is associated with a validation rule in the current {@link scenario}.
	 * @see getSafeAttributeNames
	 * @see attributeNames
	 */
	public function setAttributes($values,$safeOnly=true)
	{
		if(!is_array($values))
			return;
		$attributes=array_flip($safeOnly ? $this->getSafeAttributeNames() : $this->attributeNames());
		foreach($values as $name=>$value)
		{
			if(isset($attributes[$name]))
            {
                $this->data[$name] = $value;
            }
			else
				$this->onUnsafeAttribute($name,$value);
		}
	}

	/**
	 * Returns an iterator for traversing the attributes in the model collection.
	 * This method is required by the interface IteratorAggregate.
	 * @return CMapIterator an iterator for traversing the items in the list.
	 */
	public function getIterator()
	{
		if ($this->data instanceof CMap)
			return $this->data->iterator;
		return new CMapIterator($this->data);
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	/**
	 * Returns the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return $this->data[$offset];
	}

	/**
	 * Sets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to set element
	 * @param mixed the element value
	 */
	public function offsetSet($offset,$item)
	{
		if ($offset===null)
			$this->data[]=$item;
		else
			$this->data[$offset]=$item;
	}

	/**
	 * Unsets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}	

    public function clear()
    {
        $this->_models->clear();
    }

	public function count()	
	{
		return count($this->_models);
	}

	protected function getData()
	{
		return $this->data;
	}
}

?>
