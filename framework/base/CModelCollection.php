<?php
/**
 * CModelCollection class file.
 *
 * @author Rangel Reale <rangelreale@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CModelCollection is a container for CModel classes.
 *
 * CModel defines the basic framework for data models that need to be validated.
 *
 * @author Rangel Reale <rangelreale@gmail.com>
 * @version $Id$
 * @package system.base
 * @since 1.2
 */
class CModelCollection extends CModel implements Countable
{
	private $_models;
    
    public static function destroyModelList($list)
    {
        foreach ($list as $m)
            if ($m instanceof CModel)
                $m->destroy();
        unset($list);
    }
    
	public function __construct($parentModel = null, $parentAttribute = null, $data = null)
	{
		//parent::__construct();
		$this->_models=new CMap;
        if ($data !== null)
            $this->_models->copyFrom($data);
		$this->setParentModel($parentModel, $parentAttribute);
		$this->init();
	}

    public function destroy() 
    {
        foreach ($this->_models as $m)
            if ($m instanceof CModel)
                $m->destroy();
        $this->_models=array();
        parent::destroy();
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
		if(isset($this->models[$name]))
			return $this->models[$name];
		else
			return parent::__get($name);
	}

	/**
	 * Returns the list of attribute names of the model.
	 * @return array list of attribute names.
	 */
	public function attributeNames()
	{
		if ($this->models instanceof CMap)
			return $this->models->keys;
		return array_keys($this->models);
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
                if(isset($this->models[$name]))
                    $this->models[$name]->attributes=$value;
                else
                    $this->$name = $value;
            }
			else
				$this->onUnsafeAttribute($name,$value);
		}
	}

	/**
	 * Returns the attribute names that are safe to be massively assigned.
	 * A safe attribute is one that is associated with a validation rule in the current {@link scenario}.
	 * @return array safe attribute names
	 */
	public function getSafeAttributeNames()
	{
		return CMap::mergeArray(parent::getSafeAttributeNames(), $this->attributeNames());
	}

	public function validateExecute()
	{
		if (!parent::validateExecute())
            return false;
        
		$valid = true;
		foreach ($this->_models as $model)
        {
			if (!$model->validateExecute())
				$valid = false;
        }
        return $valid;
	}

	protected function doExecute()
	{
		foreach ($this->_models as $model)
			if (!$model->execute())
				return false;

		return parent::doExecute();
	}

	/**
	 * Returns an iterator for traversing the attributes in the model collection.
	 * This method is required by the interface IteratorAggregate.
	 * @return CMapIterator an iterator for traversing the items in the list.
	 */
	public function getIterator()
	{
		if ($this->models instanceof CMap)
			return $this->models->iterator;
		return new CMapIterator($this->models);
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->models[$offset]);
	}

	/**
	 * Returns the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return $this->models[$offset];
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
			$this->models[]=$item;
		else
			$this->models[$offset]=$item;
	}

	/**
	 * Unsets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		unset($this->models[$offset]);
	}	

    public function clear()
    {
        $this->_models->clear();
    }

	public function count()	
	{
		return count($this->_models);
	}

	protected function getModels()
	{
		return $this->_models;
	}
    
    public function getModelsIDList()
    {
        $ret = array();
        if ($this->parentModel !== null)
        {
            $modelRelation=$this->parentModel->metaData->relations[$this->parentAttribute];
            foreach ($this->getModels() as $m)
            {
                if (is_object($m) && $m instanceof CModel)
                {
					if ($modelRelation instanceof CManyManyRelation)
						$ret[]=$m->{$modelRelation->joinForeignKeys[1]};
					else
						$ret[]=$m->{$modelRelation->foreignKey};
                }
                else
                    $ret[]=$m;
            }
        }
        return $ret;
    }
    
    public function setModelsIDList($value)
    {
        throw new CException('Cannot set models id list');
    }
	
	public function executeConvertToNewRecord()
	{
		parent::executeConvertToNewRecord();
		
		// execute the dependent relations
		foreach ($this as $model)
		{
			$model->executeConvertToNewRecord();
		}
		
		$this->convertToNewRecord();
	}
}

?>
