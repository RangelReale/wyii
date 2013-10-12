<?php
/**
 * CFormatter class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFormatter provides a set of commonly used data formatting methods.
 *
 * The formatting methods provided by CFormatter are all named in the form of <code>formatXyz</code>.
 * The behavior of some of them may be configured via the properties of CFormatter. For example,
 * by configuring {@link dateFormat}, one may control how {@link formatDate} formats the value into a date string.
 *
 * For convenience, CFormatter also implements the mechanism of calling formatting methods with their shortcuts (called types).
 * In particular, if a formatting method is named <code>formatXyz</code>, then its shortcut method is <code>xyz</code>
 * (case-insensitive). For example, calling <code>$formatter->date($value)</code> is equivalent to calling
 * <code>$formatter->formatDate($value)</code>.
 *
 * Currently, the following types are recognizable:
 * <ul>
 * <li>raw: the attribute value will not be changed at all.</li>
 * <li>text: the attribute value will be HTML-encoded when rendering.</li>
 * <li>ntext: the {@link formatNtext} method will be called to format the attribute value as a HTML-encoded plain text with newlines converted as the HTML &lt;br /&gt; tags.</li>
 * <li>html: the attribute value will be purified and then returned.</li>
 * <li>date: the {@link formatDate} method will be called to format the attribute value as a date.</li>
 * <li>time: the {@link formatTime} method will be called to format the attribute value as a time.</li>
 * <li>datetime: the {@link formatDatetime} method will be called to format the attribute value as a date with time.</li>
 * <li>boolean: the {@link formatBoolean} method will be called to format the attribute value as a boolean display.</li>
 * <li>number: the {@link formatNumber} method will be called to format the attribute value as a number display.</li>
 * <li>email: the {@link formatEmail} method will be called to format the attribute value as a mailto link.</li>
 * <li>image: the {@link formatImage} method will be called to format the attribute value as an image tag where the attribute value is the image URL.</li>
 * <li>url: the {@link formatUrl} method will be called to format the attribute value as a hyperlink where the attribute value is the URL.</li>
 * </ul>
 *
 * By default, {@link CApplication} registers {@link CFormatter} as an application component whose ID is 'format'.
 * Therefore, one may call <code>Yii::app()->format->boolean(1)</code>.
 *
 * @property CHtmlPurifier $htmlPurifier The HTML purifier instance.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.utils
 * @since 1.1.0
 */
class CFormatter extends CApplicationComponent
{
	private $_htmlPurifier;
    private $_customTypes = array();
    private $_customTypesConfig = array();

	/**
	 * @var string the format string to be used to format a date using PHP date() function. Defaults to 'Y/m/d'.
	 */
	public $dateFormat='Y/m/d';
	/**
	 * @var string the format string to be used to format a time using PHP date() function. Defaults to 'h:i:s A'.
	 */
	public $timeFormat='h:i:s A';
	/**
	 * @var string the format string to be used to format a date and time using PHP date() function. Defaults to 'Y/m/d h:i:s A'.
	 */
	public $datetimeFormat='Y/m/d h:i:s A';
	/**
	 * @var array the format used to format a number with PHP number_format() function.
	 * Three elements may be specified: "decimals", "decimalSeparator" and "thousandSeparator". They
	 * correspond to the number of digits after the decimal point, the character displayed as the decimal point,
	 * and the thousands separator character.
	 */
	public $numberFormat=array('decimals'=>null, 'decimalSeparator'=>null, 'thousandSeparator'=>null);
	/**
	 * @var array the text to be displayed when formatting a boolean value. The first element corresponds
	 * to the text display for false, the second element for true. Defaults to <code>array('No', 'Yes')</code>.
	 */
	public $booleanFormat=array('No','Yes');

	/**
	 * @var array the format used to format size (bytes). Two elements may be specified: "base" and "decimals".
	 * They correspond to the base at which KiloByte is calculated (1000 or 1024) bytes per KiloByte and
	 * the number of digits after decimal point.
	 */
	public $sizeFormat=array(
		'base'=>1024,
		'decimals'=>2,
	);

	/**
	 * Calls the format method when its shortcut is invoked.
	 * This is a PHP magic method that we override to implement the shortcut format methods.
	 * @param string $name the method name
	 * @param array $parameters method parameters
	 * @return mixed the method return value
	 */
	public function __call($name,$parameters)
	{
		if(method_exists($this,'format'.$name))
			return call_user_func_array(array($this,'format'.$name),$parameters);
		else
			return parent::__call($name,$parameters);
	}

    public function setCustomTypes($value)
    {
        $this->_customTypesConfig = $value;
    }

    public function hasCustomType($type)
    {
        return isset($this->_customTypesConfig[$type]);
    }

    public function getCustomType($type)
    {
        if (!isset($this->_customTypes[$type]))
            $this->_customTypes[$type]=Yii::createComponent($this->_customTypesConfig[$type]);

        return $this->_customTypes[$type];
    }

    public function formatCustomType($value,$type)
    {
        return $this->getCustomType($type)->format($value);
    }

    public function parseCustomType($value,$type)
    {
        return $this->getCustomType($type)->parse($value);
    }

	/**
	 * Formats a value based on the given type.
	 * @param mixed $value the value to be formatted
	 * @param string $type the data type. This must correspond to a format method available in CFormatter.
	 * For example, we can use 'text' here because there is method named {@link formatText}.
	 * @return string the formatted data
	 */
	public function format($value,$type)
	{
		$method='format'.$type;
		$method2='parse'.$type;
		if(method_exists($this,$method))
			return $this->$method($value);
		elseif(method_exists($this,$method2))
			return $this->$method2($value);
        elseif($this->hasCustomType($type))
            return $this->formatCustomType($value, $type);
		else
			throw new CException(Yii::t('yii','Unknown type "{type}".',array('{type}'=>$type)));
	}

	/**
	 * Formats the value as is without any formatting.
	 * This method simply returns back the parameter without any format.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatRaw($value)
	{
		return $value;
	}

	/**
	 * Formats the value as a HTML-encoded plain text.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatText($value)
	{
		return CHtml::encode($value);
	}

	/**
	 * Formats the value as a HTML-encoded plain text and converts newlines with HTML br tags.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatNtext($value)
	{
		return nl2br(CHtml::encode($value));
	}

	/**
	 * Formats the value as text suitable for flash.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 */
	public function formatFlashText($value)
	{
		return str_replace(array("\r\n", "\r"), "\n", $value);
	}

	/**
	 * Formats the value as HTML text without any encoding.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatHtml($value)
	{
		return $this->getHtmlPurifier()->purify($value);
	}

	/**
	 * Formats the value as a date.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 * @see dateFormat
	 */
	public function formatDate($value)
	{
		return date($this->dateFormat,$value);
	}

	/**
	 * Formats the value as a time.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 * @see timeFormat
	 */
	public function formatTime($value)
	{
		return date($this->timeFormat,$value);
	}

	/**
	 * Formats the value as a date and time.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 * @see datetimeFormat
	 */
	public function formatDatetime($value)
	{
		return date($this->datetimeFormat,$value);
	}

	/**
	 * Formats the value as a boolean.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 * @see booleanFormat
	 */
	public function formatBoolean($value)
	{
		return $value ? $this->booleanFormat[1] : $this->booleanFormat[0];
	}

	/**
	 * Formats the value as a mailto link.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatEmail($value)
	{
		return CHtml::mailto($value);
	}

	/**
	 * Formats the value as an image tag.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatImage($value)
	{
		return CHtml::image($value);
	}

	/**
	 * Formats the value as a hyperlink.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function formatUrl($value)
	{
		$url=$value;
		if(strpos($url,'http://')!==0 && strpos($url,'https://')!==0)
			$url='http://'.$url;
		return CHtml::link(CHtml::encode($value),$url);
	}

	/**
	 * Formats the value as a number using PHP number_format() function.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 * @see numberFormat
	 */
	public function formatNumber($value)
	{
		return number_format($value,$this->numberFormat['decimals'],$this->numberFormat['decimalSeparator'],$this->numberFormat['thousandSeparator']);
	}

	/**
	 * Formats the value as a currency using PHP number_format() function.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see numberFormat
	 */
	public function formatCurrency($value, $currency='')
	{
		return $this->formatNumber($value);
	}

	/**
	 * Formats the value as a percentage using PHP number_format() function.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see numberFormat
	 */
	public function formatPercentage($value)
	{
		return $this->formatNumber($value);
	}

	/**
	 * Formats the value as a percentage using PHP number_format() function.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see numberFormat
	 */
	public function formatStatistical($value)
	{
		return $this->formatNumber($value);
	}

	/**
	 * Formats the value as a percentage using PHP number_format() function.
	 * @param mixed the value to be formatted
	 * @return string the formatted result
	 * @see numberFormat
	 */
	public function formatMonetary($value)
	{
		return $this->formatNumber($value);
	}
    
	/**
	 * Formats the value as a byte value.
	 * @param mixed the value to be formatted
     * @param string the minimum byte measure to return
	 * @return string the formatted result
	 */
    function formatBytes($value, $min = '')
	{
		$ext = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$unitCount = 0;
		for(; $value > 1024; $unitCount++)
		{
			$value /= 1024;
			if ($ext[$unitCount] == $min) break;
		}
		return $this->formatCurrency($value).' '.$ext[$unitCount];
	}

    /**
     * Formats the value as a type period.
     * @param integer the value to be formatted
     * @return string the formatted result
     */
    function formatTimeperiod($value)
    {
        $padHours = true;

        // start with a blank string
        $hms = "";

        // do the hours first: there are 3600 seconds in an hour, so if we divide
        // the total number of seconds by 3600 and throw away the remainder, we're
        // left with the number of hours in those seconds
        $hours = intval(intval($value) / 3600);

        // add hours to $hms (with a leading 0 if asked for)
        $hms .= ($padHours)
              ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
              : $hours. ":";

        // dividing the total seconds by 60 will give us the number of minutes
        // in total, but we're interested in *minutes past the hour* and to get
        // this, we have to divide by 60 again and then use the remainder
        $minutes = intval(($value / 60) % 60);

        // add minutes to $hms (with a leading 0 if needed)
        $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

        // seconds past the minute are found by dividing the total number of seconds
        // by 60 and using the remainder
        $seconds = intval($value % 60);

        $dt = mktime($hours, $minutes, $seconds, null, null, null);
        return $this->formatTime($dt);

        // add seconds to $hms (with a leading 0 if needed)
        $hms .= str_pad($value, 2, "0", STR_PAD_LEFT);

        // done!
        return $hms;
    }

    /**
     * Formats the value as a bit mask.
     * @param integer the value to be formatted
     * @return array a list of the selected bits
     */
    function formatBitmask($value)
    {
        $value = (int)$value;

        $ret = array();
        for ($i=0; $i<32; $i++)
        {
            $p = pow(2, $i);
            if (($value & $p)==$p)
                $ret[]=$i;
        }
        return $ret;
    }

	/**
	 * Parse a value based on the given type.
	 * @param mixed the value to be parsed
	 * @param string the data type. This must correspond to a parse method available in CFormatter.
	 * For example, we can use 'text' here because there is method named {@link parseText}.
	 * @return string the parsed data
	 */
	public function parse($value,$type)
	{
		$method='parse'.$type;
		if(method_exists($this,$method))
			return $this->$method($value);
        elseif($this->hasCustomType($type))
            return $this->parseCustomType($value, $type);
		else
			throw new CException(Yii::t('yii','Unknown type "{type}".',array('{type}'=>$type)));
	}

	/**
	 * Parse the value as is without any formatting.
	 * This method simply returns back the parameter without any parsing.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 */
	public function parseRaw($value)
	{
		return $value;
	}

	/**
	 * Parse the value as a HTML-encoded plain text.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 */
	public function parseText($value)
	{
		return $value;
	}

	/**
	 * Parse the value as a HTML-encoded plain text.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 */
	public function parseNtext($value)
	{
		return $value;
	}

	/**
	 * Parse the value as HTML text without any encoding.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 */
	public function parseHtml($value)
	{
		return $value;
	}

	/**
	 * Parse the value as a date.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see dateFormat
	 */
	public function parseDate($value)
	{
		if ($value!==null && $value!='')
			return strptime($value, $this->dateFormat);
		return null;
	}

	/**
	 * Parse the value as a time.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see timeFormat
	 */
	public function parseTime($value)
	{
		if ($value!==null && $value!='')
			return strptime($value, $this->timeFormat);
		return null;
	}

	/**
	 * Parse the value as a date and time.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see datetimeFormat
	 */
	public function parseDatetime($value)
	{
		if ($value!==null && $value!='')
			return strptime($value, $this->datetimeFormat);
		return null;
	}

	/**
	 * Parse the value as a boolean.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see trueText
	 * @see falseText
	 */
	public function parseBoolean($value)
	{
		return $value==$this->booleanFormat[1]? 1 : 0;
	}

	/**
	 * Parse the value as a mailto link.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 */
	public function parseEmail($value)
	{
		return $value;
	}

	/**
	 * Parse the value as an image tag.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 */
	public function parseImage($value)
	{
		return $value;
	}

	/**
	 * Prase the value as a hyperlink.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 */
	public function parseUrl($value)
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
	
	/**
	 * Parses the value as a currency using PHP number_format() function.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see numberFormat
	 */
	public function parseCurrency($value, $currency='')
	{
		return $this->parseNumber($value);
	}

	/**
	 * Parses the value as a percentage using PHP number_format() function.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see numberFormat
	 */
	public function parsePercentage($value)
	{
		return $this->parseNumber($value);
	}

	/**
	 * Parses the value as a percentage using PHP number_format() function.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see numberFormat
	 */
	public function parseStatistical($value)
	{
		return $this->parseNumber($value);
	}

	/**
	 * Parses the value as a percentage using PHP number_format() function.
	 * @param mixed the value to be parsed
	 * @return string the parsed result
	 * @see numberFormat
	 */
	public function parseMonetary($value)
	{
		return $this->parseNumber($value);
	}
    
	/**
	 * Parses the value as a time period.
	 * @param mixed the value to be parsed
	 * @return integer the parsed result
	 */
	public function parseTimeperiod($value)
	{
		$time = $this->parseTime($value);
        return $this->calcTimePeriod($time);
	}

	/**
	 * Calculates the time period from a time integer.
	 * @param integer time value
	 * @return integer the calculated value
	 */
	public function calcTimeperiod($value)
	{
        if ($value !== false && $value !== null)
        {
            $dt=getdate($value);
            return $dt['seconds'] + ($dt['minutes'] * 60) + ($dt['hours'] * 60 * 60);
        }
        return $value;
    }

	/**
	 * Parses the value as a bit mask.
	 * @param mixed the list of items as an array or comma-delimited string
	 * @return integer the bit mask
	 */
    function parseBitmask($value)
    {
        if (!is_array($value))
            $value = explode(',', $value);

        $ret = 0;
        foreach ($value as $item)
        {
            $ret |= pow(2, $item);
        }
        return $ret;
    }

	/**
	 * @return CHtmlPurifier the HTML purifier instance
	 */
	public function getHtmlPurifier()
	{
		if($this->_htmlPurifier===null)
			$this->_htmlPurifier=new CHtmlPurifier;
		return $this->_htmlPurifier;
	}

	/**
	 * Formats the value in bytes as a size in human readable form.
	 * @param integer $value value in bytes to be formatted
	 * @param boolean $verbose if full names should be used (e.g. Bytes, KiloBytes, ...).
	 * Defaults to false meaning that short names will be used (e.g. B, KB, ...).
	 * @return string the formatted result
	 */
	public function formatSize($value,$verbose=false)
	{
		$base=$this->sizeFormat['base'];
		for($i=0; $base<=$value && $i<5; $i++)
			$value=$value/$base;

		$value=round($value, $this->sizeFormat['decimals']);

		switch($i)
		{
			case 0:
				return $verbose ? Yii::t('size_units', '{n} Bytes', $value) : Yii::t('size_units', '{n} B', $value);
			case 1:
				return $verbose ? Yii::t('size_units', '{n} KiloBytes', $value) : Yii::t('size_units', '{n} KB', $value);
			case 2:
				return $verbose ? Yii::t('size_units', '{n} MegaBytes', $value) : Yii::t('size_units', '{n} MB', $value);
			case 3:
				return $verbose ? Yii::t('size_units', '{n} GigaBytes', $value) : Yii::t('size_units', '{n} GB', $value);
			default:
				return $verbose ? Yii::t('size_units', '{n} TeraBytes', $value) : Yii::t('size_units', '{n} TB', $value);
		}
	}
}
