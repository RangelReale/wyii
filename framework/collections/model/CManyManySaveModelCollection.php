<?php
/**
 * CManyManySaveModelCollection class file
 *
 * @author Rangel Reale <rreale@edgeit.com.br>
 * @link http://www.edgeit.com.br/
 * @copyright Copyright &copy; 2010 EdgeIT Consultoria
 */

/**
 * CManyManySaveModelCollection is a model collection for MANY_MANY relations.
 *
 * @author Rangel Reale <rreale@edgeit.com.br>
 * @package system.collections.model
 */
class CManyManySaveModelCollection extends CManyManyModelCollection
{
	protected function doExecute()
	{
		if ($this->changedValues!==null)
		{
			$this->writeManyManyTables();
		}
		return true;
	}

	/**
	 * At first, this function cycles through each MANY_MANY Relation. Then
	 * it checks if the attribute of the Object instance is an integer, an
	 * array or another ActiveRecord instance. It then builds up the SQL-Query
	 * to add up the needed Data to the MANY_MANY-Table given in the relation
	 * settings.
	 */
	public function writeManyManyTables() {
		Yii::trace('writing MANY_MANY data for '.get_class($this->parentModel),'system.db.ar.CActiveRecord');

		$modelRelation=$this->parentModel->metaData->relations[$this->parentAttribute];

		if (!$modelRelation instanceof CManyManyRelation)
			throw new CException(Yii::t('yii','{class} can only be used to save relations of type MANY_MANY.',
				array('{class}'=>get_class($this))));

/*
		if(isset($this->parentModel->{$this->relation}))
		{
*/			
			$this->executeManyManyEntry($this->makeManyManyDeleteCommand(
				$modelRelation->foreignKey,
				$this->parentModel->{$this->parentModel->tableSchema->primaryKey}));
			foreach($this->changedValues as $value)
			{
				$this->executeManyManyEntry ($this->makeManyManyInsertCommand(
					$modelRelation->foreignKey,
					$value));
			}
//		}
	}

	// We can't throw an Exception when this query fails, because it is possible
	// that there is not row available in the MANY_MANY table, thus execute()
	// returns 0 and the error gets thrown falsely.
	public function executeManyManyEntry($query) {
		Yii::app()->db->createCommand($query)->execute();
		//CVarDumper::dump($query, 1, true);
		//echo '<br>';
	}

	// It is important to use insert IGNORE so SQL doesn't throw an foreign key
	// integrity violation
	public function makeManyManyInsertCommand($model, $rel) {
		return sprintf("insert ignore into %s values ('%s', '%s')", $model,	$this->parentModel->{$this->parentModel->tableSchema->primaryKey}, $rel);
	}

	public function makeManyManyDeleteCommand($model, $rel) {
		return sprintf("delete ignore from %s where %s = '%s'", $this->getManyManyTable($model), $this->getRelationNameForDeletion($model), $rel);
	}

	public function getManyManyTable($model) {
		if (($ps=strpos($model, '('))!==FALSE)
		{
			return substr($model, 0, $ps);
		}
		else
			return $model;
	}

	public function getRelationNameForDeletion($model) {
		preg_match('/\((.*),/',$model, $matches) ;
		return substr($matches[0], 1, strlen($matches[0]) - 2);
	}
	
}

?>
