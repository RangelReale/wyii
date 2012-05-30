<?php
/**
 * CDbFormatter class file.
 *
 * @author Rangel Reale <rreale@edgeit.com.br>
 * @copyright Copyright &copy; 2008-2010 EdgeIT Consultoria
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFormatter provides a set of commonly used data formatting methods.
 *
 *
 * @author Rangel Reale <rreale@edgeit.com.br>
 * @package system.utils
 * @since 1.3.0
 */
class CDbFormatter extends CComponent
{
	private $_schema;

	/**
	 * Calls the format method when its shortcut is invoked.
	 * This is a PHP magic method that we override to implement the shortcut format methods.
	 * @param string the method name
	 * @param array method parameters
	 * @return mixed the method return value
	 */
	public function __call($name,$parameters)
	{
		if(method_exists($this,'format'.$name))
			return call_user_func_array(array($this,'format'.$name),$parameters);
		elseif(method_exists($this,'parse'.$name))
			return call_user_func_array(array($this,'parse'.$name),$parameters);
		else
			return parent::__call($name,$parameters);
	}

	public function __construct($schema)
	{
		$this->_schema = $schema;
	}

	/**
	 * Formats a value based on the given type.
	 * @param mixed the value to be formatted
	 * @param string the data type. This must correspond to a format method available in CFormatter.
	 * For example, we can use 'text' here because there is method named {@link formatText}.
	 * @return string the formatted data
	 */
	public function format($value,$type)
	{
		$method='format'.$type;
		if(method_exists($this,$method))
			return $this->$method($value);
		else
			throw new CException(Yii::t('yii','Unknown type "{type}".',array('{type}'=>$type)));
	}

	/**
	 * Formats the value as a date.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see dateFormat
	 */
	public function formatDate($value)
	{
		return $value;
	}

	/**
	 * Formats the value as a time.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see timeFormat
	 */
	public function formatTime($value)
	{
		return $value;
	}

	/**
	 * Formats the value as a date and time.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see datetimeFormat
	 */
	public function formatDatetime($value)
	{
		return $value;
	}

	/**
	 * Formats the value as a number using PHP number_format() function.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see numberFormat
	 */
	public function formatNumber($value)
	{
		return $value;
	}


	/**
	 * Parse a value based on the given type.
	 * @param mixed the value to be parsed
	 * @param string the data type. This must correspond to a parse method available in CDbFormatter.
	 * @return string the parsed data
	 */
	public function parse($value,$type)
	{
		$method='parse'.$type;
		if(method_exists($this,$method))
			return $this->$method($value);
		else
			throw new CException(Yii::t('yii','Unknown type "{type}".',array('{type}'=>$type)));
	}

	/**
	 * Parse the value as a date.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see dateFormat
	 */
	public function parseDate($value)
	{
		return $value;
	}

	/**
	 * Parse the value as a time.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see timeFormat
	 */
	public function parseTime($value)
	{
		return $value;
	}

	/**
	 * Parse the value as a date and time.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see datetimeFormat
	 */
	public function parseDatetime($value)
	{
		return $value;
	}

	/**
	 * Parse the value as a number formatted by the PHP number_format() function.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see numberFormat
	 */
	public function parseNumber($value)
	{
		return $value;
	}
}
?>
