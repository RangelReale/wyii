<?php
/**
 * CBaseArrayModifyRecordModelCollection class file
 *
 * @author Rangel Reale <rreale@edgeit.com.br>
 * @link http://www.edgeit.com.br/
 * @copyright Copyright &copy; 2010 EdgeIT Consultoria
 */

/**
 * CBaseArrayModifyRecordModelCollection is a model collection.
 *
 * @author Rangel Reale <rreale@edgeit.com.br>
 * @package system.collections.model
 */
class CBaseArrayModifyRecordModelCollection extends CModelCollection
{
    public $postIdList = false;

	public $idAttribute;
	public $copyAttributes=array();
	public $saveCopyAttributes=array();
	public $defaultAttributes=array();
	public $forceAttributes=array();
    public $modelClass;

	protected function doExecute()
	{
		if (count($this->saveCopyAttributes)>0)
			foreach ($this as $model)
			{
				foreach ($this->saveCopyAttributes as $attribute)
				{
					if (is_array($attribute))
						$model->{$attribute[0]}=$this->parentModel->{$attribute[1]};
					else
						$model->$attribute=$this->parentModel->$attribute;
				}
			}
		return parent::doExecute();
	}

	public function setAttributes($values,$safeOnly=true)
	{
		if (!is_array($values))
		{
			if ($values!='')
				throw new CException('Invalid attributes value - must be a value array');
			$values=array();
		}

		$r_exists=array();
		foreach ($this as $vid => $value)
		{
            $curValue = isset($this->idAttribute)?$value->{$this->idAttribute}:$vid;

			$r_exists[] = $curValue;
            if ($this->postIdList)
                $fvalue=in_array($curValue, $values)?$curValue:false;
            else
                $fvalue=isset($values[$curValue])?$values[$curValue]:false;

            if ($fvalue !== false)
			{
				$this->recordUpdate($curValue, $value, $fvalue);
			}
			else
			{
				$this->recordDelete($curValue, $value);
			}
		}

        if ($this->postIdList)
            $r_add=array_diff($values, $r_exists);
        else
            $r_add=array_diff(array_keys($values), $r_exists);

		foreach ($r_add as $r_new)
		{
			$this->recordInsert($r_new, $this->postIdList?null:$values[$_new]);
		}
	}

	public function recordCreateNew($id, $data = null)
	{
        if (isset($this->modelClass))
            $modelClassName=$this->modelClass;
        elseif ($this->parentModel instanceof CActiveRecord)
        {
            $relation=$this->parentModel->metaData->relations[$this->parentAttribute];
            $modelClassName=$relation->className;
        }
		$model=new $modelClassName;
        if (isset($this->idAttribute))
            $model->{$this->idAttribute}=$id;
        if (isset($data))
            $model->attributes=$data;
		return $model;
	}

	public function recordAddNew($model)
	{
        if ($this->parentModel instanceof CActiveRecord)
        {
            $relation=$this->parentModel->metaData->relations[$this->parentAttribute];
            if($relation->index!==null)
                $index=$model->{$relation->index};
            else
                $index=true;
            $this->parentModel->addRelatedRecord($this->parentAttribute,$model,$index);
        } elseif (isset($this->idAttribute)) {
            $this->parentModel->{$this->parentAttribute}[$model->{$this->idAttribute}]=$model;
        } else {
            $this->parentModel->{$this->parentAttribute}[]=$model;
        }
	}

	public function recordSetDefaults($model, $isNew = false)
	{
		foreach ($this->copyAttributes as $attribute)
		{
			if (is_array($attribute))
				$model->{$attribute[0]}=$this->parentModel->{$attribute[1]};
			else
				$model->$attribute=$this->parentModel->$attribute;
		}

		foreach ($this->forceAttributes as $aname => $avalue)
			$model->$aname=$avalue;

		if ($isNew)
			foreach ($this->defaultAttributes as $aname => $avalue)
				$model->$aname=$avalue;
	}

	/**
	 * This event is raised before the validation is performed.
	 * @param CModelEvent the event parameter
	 */
	public function onRecordInsert($event)
	{
		$this->raiseEvent('onRecordInsert',$event);
	}

	/**
	 * This event is raised before the validation is performed.
	 * @param CModelEvent the event parameter
	 */
	public function onRecordUpdate($event)
	{
		$this->raiseEvent('onRecordUpdate',$event);
	}

	/**
	 * This event is raised before the validation is performed.
	 * @param CModelEvent the event parameter
	 */
	public function onRecordDelete($event)
	{
		$this->raiseEvent('onRecordDelete',$event);
	}

	protected function recordInsert($id, $data = null)
	{
		$event=new CBaseArrayModifyRecordModelCollectionEvent($this, $id, null, $data);
		$this->onRecordInsert($event);
		return $event->handled;
	}

	protected function recordUpdate($id, $model, $data = null)
	{
		$event=new CBaseArrayModifyRecordModelCollectionEvent($this, $id, $model, $data);
		$this->onRecordUpdate($event);
		return $event->handled;
	}

	protected function recordDelete($id, $model)
	{
		$event=new CBaseArrayModifyRecordModelCollectionEvent($this, $id, $model);
		$this->onRecordDelete($event);
		return $event->handled;
	}

}

/**
 * CBaseArrayModifyRecordModelCollectionEvent.
 *
 * @author Rangel Reale <rreale@edgeit.com.br>
 * @package system.collections.model
 */
class CBaseArrayModifyRecordModelCollectionEvent extends CEvent
{
	public $id;
	public $model;
    public $data;

	/**
	 * Constructor.
	 * @param mixed sender of the event
	 */
	public function __construct($sender, $id, $model=null, $data=null)
	{
		parent::__construct($sender);
		$this->id=$id;
		$this->model=$model;
        $this->data=$data;
	}
}


?>
