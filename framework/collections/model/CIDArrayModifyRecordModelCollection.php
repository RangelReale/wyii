<?php
/**
 * CIDArrayModifyRecordModelCollection class file
 *
 * @author Rangel Reale <rreale@edgeit.com.br>
 * @link http://www.edgeit.com.br/
 * @copyright Copyright &copy; 2010 EdgeIT Consultoria
 */
//require_once('CBaseIDArrayModifyRecordModelCollection.php');

/**
 * CIDArrayModifyRecordModelCollection is a model collection.
 *
 * @author Rangel Reale <rreale@edgeit.com.br>
 * @package system.collections.model
 */
class CIDArrayModifyRecordModelCollection extends CBaseIDArrayModifyRecordModelCollection
{
	protected function recordInsert($id, $data = null)
	{
		if (parent::recordInsert($id, $data))
			return true;

		$model=$this->recordCreateNew($id);
		$this->recordSetDefaults($model, true);
		$this->recordAddNew($model);
		return true;
	}
	
	protected function recordUpdate($id, $model, $data = null)
	{
		if (parent::recordUpdate($id, $model, $data))
			return true;

		$this->recordSetDefaults($model);
		return true;
	}

	protected function recordDelete($id, $model)
	{
		if (parent::recordDelete($id, $model))
			return true;

		$model->markDelete();	
		return true;
	}
}

?>
