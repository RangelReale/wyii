<?php
class CFunctionHelper
{
    public static function is_array($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }
    
    public static function get_array($value)
    {
        if (is_array($value))
            return $value;
        elseif ($value instanceof CList || $value instanceof CMap)
            return $value->toArray();
        elseif ($value instanceof ArrayAccess)
        {
            $ret = array();
            foreach ($value as $vi => $vv)
                $ret[$vi] = $vv;
            return $ret;
        }
        else
            throw new CException('Could not get array from variable');
    }
    
    public static function implode($glue, $pieces)
    {
        return implode($glue, self::get_array($pieces));
    }
    
    public static function explode($delimiter, $string)
    {
        return explode($delimiter, $string);
    }
    
    public static function base64url_encode($data) 
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64url_decode($data) 
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }     
}
?>
