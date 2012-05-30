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
class CMysqlFormatter extends CDbFormatter
{
	/**
	 * Formats the value as a date.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see dateFormat
	 */
	public function formatDate($value)
	{
		return date('Y-m-d', $value);
	}

	/**
	 * Formats the value as a time.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see timeFormat
	 */
	public function formatTime($value)
	{
		return date('H:i:s', $value);
	}

	/**
	 * Formats the value as a date and time.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see datetimeFormat
	 */
	public function formatDatetime($value)
	{
		return date('Y-m-d H:i:s', $value);
	}

	/**
	 * Parse the value as a date.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see dateFormat
	 */
	public function parseDate($value)
	{
		return CDateTimeParser::parse($value, 'yyyy-MM-dd');
	}

	/**
	 * Parse the value as a time.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see timeFormat
	 */
	public function parseTime($value)
	{
		return CDateTimeParser::parse($value, 'hh:mm:ss');
	}

	/**
	 * Parse the value as a date and time.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see datetimeFormat
	 */
	public function parseDatetime($value)
	{
		return CDateTimeParser::parse($value, 'yyyy-MM-dd hh:mm:ss');
	}

}

?>