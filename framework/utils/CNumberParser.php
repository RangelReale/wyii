<?php
/**
 * CNumberParser class file.
 *
 * @author Rangel Reale <rangelreale[at]gmail[dot]com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CNumberParser provides number parsing functionalities.
 *
 * CNumberParser parses a string and outputs a string (integer or double) 
 * based on the specified format. A CNumberParser instance is associated with a locale,
 * and thus parses the string representation of the number in a locale-dependent fashion.
 *
 * CNumberParser currently supports currency format, percentage format, decimal format,
 * and custom format. The first three formats are specified in the locale data, while the custom
 * format allows you to enter an arbitrary format string.
 *
 * A format string may consist of the following special characters:
 * <ul>
 * <li>dot (.): the decimal point. It will be replaced with the localized decimal point.</li>
 * <li>comma (,): the grouping separator. It will be replaced with the localized grouping separator.</li>
 * <li>zero (0): required digit. This specifies the places where a digit must appear (will pad 0 if not).</li>
 * <li>hash (#): optional digit. This is mainly used to specify the location of decimal point and grouping separators.</li>
 * <li>currency (?): the currency placeholder. It will be replaced with the localized currency symbol.</li>
 * <li>percentage (%): the percetage mark. If appearing, the number will be multiplied by 100 before being formatted.</li>
 * <li>permillage (?): the permillage mark. If appearing, the number will be multiplied by 1000 before being formatted.</li>
 * <li>semicolon (;): the character separating positive and negative number sub-patterns.</li>
 * </ul>
 *
 * Anything surrounding the pattern (or sub-patterns) will be kept.
 *
 * The followings are some examples:
 * <pre>
 * Pattern "#,##0.00" will format 12345.678 as "12,345.68".
 * Pattern "#,#,#0.00" will format 12345.6 as "1,2,3,45.60".
 * </pre>
 * Note, in the first example, the number is rounded first before applying the formatting.
 * And in the second example, the pattern specifies two grouping sizes.
 *
 * CNumberParser attempts to parse number formatting according to
 * the {@link http://www.unicode.org/reports/tr35/ Unicode Technical Standard #35}.
 * The following features are NOT implemented:
 * <ul>
 * <li>significant digit</li>
 * <li>scientific format</li>
 * <li>arbitrary literal characters</li>
 * <li>arbitrary padding</li>
 * </ul>
 *
 * @author Rangel Reale <rangelreale[at]gmail[dot]com>
 * @version $Id$
 * @package system.i18n
 * @since 1.0
 */
class CNumberParser extends CComponent
{
	private $_locale;
	private $_formats=array();

	/**
	 * Constructor.
	 * @param mixed locale ID (string) or CLocale instance
	 */
	public function __construct($locale)
	{
		if(is_string($locale))
			$this->_locale=CLocale::getInstance($locale);
		else
			$this->_locale=$locale;
	}

	/**
	 * Parses a number based on the specified pattern.
	 * Note, if the format contains '%', the number will be divided by 100 first.
	 * If the format contains '?', the number will be divided by 1000.
	 * If the format contains currency placeholder, it will removed.
	 * @param string format pattern
	 * @param mixed the number to be parsed
	 * @param string 3-letter ISO 4217 code. For example, the code "USD" represents the US Dollar and "EUR" represents the Euro currency.
	 * The currency placeholder in the pattern will be removed.
	 * If null, no parsing will be done.
	 * @return string the parsed result.
	 */
	public function parse($pattern,$value,$currency=null)
	{
		$format=$this->parseFormat($pattern);
		$result=$value;
		if($currency!==null)
		{
			if(($symbol=$this->_locale->getCurrencySymbol($currency))===null)
				$symbol=$currency;
			$result=str_replace($symbol,'',$result);
		}
		return $this->parseNumber($format,$result);
	}

	/**
	 * Parses a number using the currency format defined in the locale.
	 * @param mixed the number to be parsed
	 * @param string 3-letter ISO 4217 code. For example, the code "USD" represents the US Dollar and "EUR" represents the Euro currency.
	 * The currency placeholder in the pattern will be erased.
	 * @return string the parsing result.
	 */
	public function parseCurrency($value,$currency)
	{
		return $this->parse($this->_locale->getCurrencyFormat(),$value,$currency);
	}

	/**
	 * Parses a number using the percentage format defined in the locale.
	 * Note, if the percentage format contains '%', the number will be divided by 100 first.
	 * If the percentage format contains '?', the number will be divided by 1000.
	 * @param mixed the number to be parsed
	 * @return string the parsing result.
	 */
	public function parsePercentage($value)
	{
		return $this->parse($this->_locale->getPercentFormat(),$value);
	}

	/**
	 * Parses a number using the decimal format defined in the locale.
	 * @param mixed the number to be parsed
	 * @return string the parsing result.
	 */
	public function parseDecimal($value)
	{
		return $this->parse($this->_locale->getDecimalFormat(),$value);
	}

	/**
	 * Parses a number using the decimal format defined in the locale.
	 * @param mixed the number to be parsed
	 * @return string the parsing result.
	 */
	public function parseStatistical($value)
	{
		return $this->parse($this->_locale->getStatisticalFormat(),$value);
	}

	/**
	 * Parses a number using the decimal format defined in the locale.
	 * @param mixed the number to be parsed
	 * @return string the parsing result.
	 */
	public function parseMonetary($value)
	{
		return $this->parse($this->_locale->getMonetaryFormat(),$value);
	}
    
	/**
	 * Parses a number based on a format.
	 * This is the method that does actual number parsing.
	 * @param array format with the following structure:
	 * <pre>
	 * array(
	 * 	'decimalDigits'=>2,     // number of required digits after decimal point; 0s will be padded if not enough digits; if -1, it means we should drop decimal point
	 *  'maxDecimalDigits'=>3,  // maximum number of digits after decimal point. Additional digits will be truncated.
	 * 	'integerDigits'=>1,     // number of required digits before decimal point; 0s will be padded if not enough digits
	 * 	'groupSize1'=>3,        // the primary grouping size; if 0, it means no grouping
	 * 	'groupSize2'=>0,        // the secondary grouping size; if 0, it means no secondary grouping
	 * 	'positivePrefix'=>'+',  // prefix to positive number
	 * 	'positiveSuffix'=>'',   // suffix to positive number
	 * 	'negativePrefix'=>'(',  // prefix to negative number
	 * 	'negativeSuffix'=>')',  // suffix to negative number
	 * 	'multiplier'=>1,        // 100 for percent, 1000 for per mille
	 * );
	 * </pre>
	 * @param mixed the number to be parsed
	 * @return string the parsing result
	 */
	protected function parseNumber($format,$value)
	{
		$number=strtr($value,array($this->_locale->getNumberSymbol('percentSign')=>'',$this->_locale->getNumberSymbol('perMille')=>''));
		$negative=false;
		if ($format['negativePrefix']!='' && substr($number, 0, 1)==$format['negativePrefix'])
		{
			$negative=true;
			$number=substr($number, strlen($format['negativePrefix']));
		}
		if ($format['negativeSuffix']!='' && substr($number, -1, 1)==$format['negativeSuffix'])
		{
			$negative=true;
			$number=substr($number, 0, strlen($number)-strlen($format['negativeSuffix']));
		}

		$number=strtr($number,array($this->_locale->getNumberSymbol('group')=>'', $this->_locale->getNumberSymbol('decimal')=>'.'));

		if (!is_numeric($number))
			return false;

		$number=$number/$format['multiplier'];

		if ($negative)
			$number=-1*$number;

		return $number;
	}

	/**
	 * Parses a given string pattern.
	 * @param string the pattern to be parsed
	 * @return array the parsed pattern
	 * @see parseNumber
	 */
	protected function parseFormat($pattern)
	{
		if(isset($this->_formats[$pattern]))
			return $this->_formats[$pattern];

		$format=array();

		// find out prefix and suffix for positive and negative patterns
		$pattern=strtr($pattern, array('?'=>''));
		
		$patterns=explode(';',$pattern);
		$format['positivePrefix']=$format['positiveSuffix']=$format['negativePrefix']=$format['negativeSuffix']='';
		if(preg_match('/^(.*?)[#,\.0]+(.*?)$/',$patterns[0],$matches))
		{
			$format['positivePrefix']=$matches[1];
			$format['positiveSuffix']=$matches[2];
		}

		if(isset($patterns[1]) && preg_match('/^(.*?)[#,\.0]+(.*?)$/',$patterns[1],$matches))  // with a negative pattern
		{
			$format['negativePrefix']=$matches[1];
			$format['negativeSuffix']=$matches[2];
		}
		else
		{
			$format['negativePrefix']=$this->_locale->getNumberSymbol('minusSign').$format['positivePrefix'];
			$format['negativeSuffix']=$format['positiveSuffix'];
		}
		$pat=$patterns[0];

		// find out multiplier
		if(strpos($pat,'%')!==false)
			$format['multiplier']=100;
		else if(strpos($pat,'?')!==false)
			$format['multiplier']=1000;
		else
			$format['multiplier']=1;

		// find out things about decimal part
		if(($pos=strpos($pat,'.'))!==false)
		{
			if(($pos2=strrpos($pat,'0'))>$pos)
				$format['decimalDigits']=$pos2-$pos;
			else
				$format['decimalDigits']=0;
			if(($pos3=strrpos($pat,'#'))>=$pos2)
				$format['maxDecimalDigits']=$pos3-$pos;
			else
				$format['maxDecimalDigits']=$format['decimalDigits'];
			$pat=substr($pat,0,$pos);
		}
		else   // no decimal part
		{
			$format['decimalDigits']=0;
			$format['maxDecimalDigits']=0;
		}

		// find out things about integer part
		$p=str_replace(',','',$pat);
		if(($pos=strpos($p,'0'))!==false)
			$format['integerDigits']=strrpos($p,'0')-$pos+1;
		else
			$format['integerDigits']=0;
		// find out group sizes. some patterns may have two different group sizes
		$p=str_replace('#','0',$pat);
		if(($pos=strrpos($pat,','))!==false)
		{
			$format['groupSize1']=strrpos($p,'0')-$pos;
			if(($pos2=strrpos(substr($p,0,$pos),','))!==false)
				$format['groupSize2']=$pos-$pos2-1;
			else
				$format['groupSize2']=0;
		}
		else
			$format['groupSize1']=$format['groupSize2']=0;

		return $this->_formats[$pattern]=$format;
	}
}
