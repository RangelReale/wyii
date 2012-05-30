<?php
/**
 * CFormatParseValidator class file.
 *
 * @author Rangel Reale <rangelreale@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFormatParseValidator validates the attribute by checking if CFormatter::parse returns false.
 *
 * @author Rangel Reale <rangelreale@gmail.com>
 * @version $Id: CNumberValidator.php 1678 2010-01-07 21:02:00Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CFormatParseValidator extends CValidator
{
	/**
	 * @var string CFormatter format.
	 */
	public $format='';
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel the object being validated
	 * @param string the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		if ($this->format!='')
		{
			if (Yii::app()->format->parse($value, $this->format)===false)
			{
				$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} format is invalid.');
				$this->addError($object,$attribute,$message);
			}
		}
		else
		{
			if ($value===false)
			{
				$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} format is invalid.');
				$this->addError($object,$attribute,$message);
			}
		}
	}
}
