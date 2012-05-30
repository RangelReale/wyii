<?php

/**
 * CLocaleFormatter class file.
 *
 * @author Rangel Reale <rangelreale@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CLocateFormatter formats using the current locale.
 *
 * @author Rangel Reale <rangelreale@gmail.com>
 * @version $Id$
 * @package system.utils
 * @since 1.1.1
 */

class CLocaleFormatter extends CFormatter
{
	/**
	 * @var string the format width to be used to format a date using CDateFormatter formatDateTime function. Defaults to 'medium'.
	 */
	public $dateFormatWidth='medium';
	/**
	 * @var string the format width to be used to format a time using CDateFormatter  formatDateTime function. Defaults to 'medium'.
	 */
	public $timeFormatWidth='medium';

	/**
	 * Formats the value as a date.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see dateFormatWidth
	 */
	public function formatDate($value)
	{
		return Yii::app()->dateFormatter->formatDateTime($value, $this->dateFormatWidth, null);
	}

	/**
	 * Formats the value as a time.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see timeFormatWidth
	 */
	public function formatTime($value)
	{
		return Yii::app()->dateFormatter->formatDateTime($value, null, $this->timeFormatWidth);
	}

	/**
	 * Formats the value as a date and time.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see dateFormatWidth
	 * @see timeFormatWidth
	 */
	public function formatDatetime($value)
	{
		return Yii::app()->dateFormatter->formatDateTime($value, $this->dateFormatWidth, $this->timeFormatWidth);
	}
	
	/**
	 * Formats the value as a number using the locale.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 */
	public function formatNumber($value)
	{
		return Yii::app()->locale->numberFormatter->formatDecimal($value, '');
	}
	
	/**
	 * Formats the value as a currency using the locale.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see numberFormat
	 */
	public function formatCurrency($value, $currency='')
	{
		return Yii::app()->locale->numberFormatter->formatCurrency($value, '');
	}

	/**
	 * Formats the value as a percentage using  the locale.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see numberFormat
	 */
	public function formatPercentage($value)
	{
		return Yii::app()->locale->numberFormatter->formatPercentage($value, '');
	}

	/**
	 * Formats the value as a percentage using  the locale.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see numberFormat
	 */
	public function formatStatistical($value)
	{
		return Yii::app()->locale->numberFormatter->formatStatistical($value, '');
	}
	
	/**
	 * Parses the value as a date.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see dateFormatWidth
	 */
	public function parseDate($value)
	{
		if ($value!==null && $value!='')
			return CDateTimeParser::parse($value, Yii::app()->locale->getDateFormat($this->dateFormatWidth));
		return null;
	}

	/**
	 * Parses the value as a time.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see timeFormatWidth
	 */
	public function parseTime($value)
	{
		if ($value!==null && $value!='')
			return CDateTimeParser::parse($value, Yii::app()->locale->getTimeFormat($this->timeFormatWidth));
		return null;
	}

	/**
	 * Parses the value as a date and time.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see dateFormatWidth
	 * @see timeFormatWidth
	 */
	public function parseDatetime($value)
	{
		if ($value!==null && $value!='')
		{
			$dtformat=strtr(Yii::app()->locale->dateTimeFormat,array('{0}'=>Yii::app()->locale->getTimeFormat($this->timeFormatWidth),'{1}'=>Yii::app()->locale->getDateFormat($this->dateFormatWidth)));
			return CDateTimeParser::parse($value, $dtformat);
		}
		return null;
	}
	
	/**
	 * Parse the value as a number formatted by the PHP number_format() function.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see numberFormat
	 */
	public function parseNumber($value)
	{
		$nparse=new CNumberParser(Yii::app()->locale);
		return $nparse->parseDecimal($value);
	}
	
	/**
	 * Parses the value as a currency using PHP number_format() function.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see numberFormat
	 */
	public function parseCurrency($value, $currency='')
	{
		$nparse=new CNumberParser(Yii::app()->locale);
		return $nparse->parseCurrency($value, $currency);
	}

	/**
	 * Parses the value as a percentage using PHP number_format() function.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see numberFormat
	 */
	public function parsePercentage($value)
	{
		$nparse=new CNumberParser(Yii::app()->locale);
		return $nparse->parsePercentage($value);
	}

	/**
	 * Parses the value as a percentage using PHP number_format() function.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see numberFormat
	 */
	public function parseStatistical($value)
	{
		$nparse=new CNumberParser(Yii::app()->locale);
		return $nparse->parseStatistical($value);
	}
	
}

?>
