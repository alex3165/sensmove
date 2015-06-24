<?php
/**
*
*   Toolset Parser, advanced parser for arithmetic,logical and comparision expressions
*       by Nikos M. <nikos.m@icanlocalize.com>
*
*   Main Features:
*       + user variables
*       + support mathematical, and date functions
*       + limited date parsing
*
*   Additional Features:
*       + typed tokens
*       + typed user variables
*       + added string literals
*       + support advanced mathematical, string and date functions
*       + support advanced date operations (like (date + 3 days) or (3 days=date1-date2)) (not yet)
*       + support typecasting
*       + can parse and format every localized PHP date format
*       + precompilation of expressions into functions
*       + faster, optimized code
*       + use of closures for encapsulation and better access
*       + heavy refactoring, and various bug fixes
*
*   adapted to PHP form Toolset_Parser js version
*   inspired by JS Expression Evaluator by Prasad P. Khandekar
*
**/

class Toolset_Regex
{
    private $_regex='';

    public function Toolset_Regex($rx,$opts='')
    {
        // remove flags not supported by PHP
        $this->_regex='/'.$rx.'/'.str_replace('g','',$opts); // PHP does not support 'g' modifier
    }

    public function test($str)
    {
        return (preg_match($this->_regex,$str)==1);
    }
}

class Toolset_Functions
{
    private static $_cookies=null;
    private static $_regexs=array();
    private static $_params=array(
        'user'=>array(
            'ID'=>0,
            'role'=>'',
            'roles'=>array(),
            'login'=>'',
            'display_name'=>''
        )
    );

    public static function setParams($params)
    {
        self::$_params=array_merge(self::$_params,$params);
    }

    public static function Cookie($name)
    {
        if (!isset($_cookies)) $_cookies=&$_COOKIE;

        return (isset($_cookies[$name]))?$_cookies[$name]:'';
    }

    public static function User($att='')
    {
        $att=strtoupper($att);

        switch ($att)
        {
            case 'ID':
                return (string)(self::$_params['user']['ID'].'');
            case 'NAME':
                return self::$_params['user']['display_name'];
            case 'ROLE':
                return self::$_params['user']['role'];
            case 'LOGIN':
                return self::$_params['user']['login'];
            default:
                return '';
        }
        return '';
    }

    public static function Regex($rx, $opts='')  {return new Toolset_Regex($rx, $opts);}

    public static function Contains(&$a, $v)  {return in_array($v,$a);}
}

class Toolset_Date
{
    private $_timestamp;

 /*   getdate params
"seconds" 	Numeric representation of seconds 	0 to 59
"minutes" 	Numeric representation of minutes 	0 to 59
"hours" 	Numeric representation of hours 	0 to 23
"mday" 	Numeric representation of the day of the month 	1 to 31
"wday" 	Numeric representation of the day of the week 	0 (for Sunday) through 6 (for Saturday)
"mon" 	Numeric representation of a month 	1 through 12
"year" 	A full numeric representation of a year, 4 digits 	Examples: 1999 or 2003
"yday" 	Numeric representation of the day of the year 	0 through 365
"weekday" 	A full textual representation of the day of the week 	Sunday through Saturday
"month" A full textual representation of a month, such as January or March
*/
    private $_date=array(
        'hour'=>0,
        'min'=>0,
        'sec'=>0,
        'day_of_month'=>0,
        'day_of_week'=>0,
        'day_of_year'=>0,
        'day_of_week_string'=>'',
        'month_string'=>'',
        'month'=>0,
        'year'=>0
        );

    private static $_today=false;

    public static function setToday($date)
    {
        self::$_today=$date;
    }

    public static function getToday()
    {
        if (self::$_today)
            return new Toolset_Date(self::$_today);

        $today=new Toolset_Date();
        return  $today->setDateByTimestamp();
    }

    public function Toolset_Date($date=null)
    {
        if (isset($date))
            $this->setDate($date);
    }


    public function getDate($key=null)
    {
        if (!isset($key) || !in_array($key, array_keys($this->_date)))
            return $this->_date;
        else
            return $this->_date[$key];
    }

    public function setDate($date)
    {
        $hasYear=false;
        $hasMonth=false;
        $hasDay=false;

        foreach ($date as $k=>$v)
        {
            if ($k=='year')  $hasYear=true;
            if ($k=='month')  $hasMonth=true;
            if ($k=='day_of_year')  $hasDay=true;

            if (isset($this->_date[$k]))
                $this->_date[$k]=$v;
        }

        // fill all values
        if ($hasYear && $hasMonth && $hasDay)
            $this->setDateByTimestamp($this->getTimestamp());

        return $this;
    }

    public function getTimestamp()
    {
        if (class_exists("DateTime")) {
            $date = new DateTime("{$this->_date['year']}-{$this->_date['month']}-{$this->_date['day_of_month']} {$this->_date['hour']}:{$this->_date['min']}:{$this->_date['sec']}");
            return (method_exists('DateTime', 'getTimestamp')) ? $date->getTimestamp() : $date->format('U');
        } else
            return mktime ($this->_date['hour'], $this->_date['min'], $this->_date['sec'], $this->_date['month'], $this->_date['day_of_month'], $this->_date['year'] /*[, int $is_dst = -1 ]*/);
    }

    public function getNormalizedTimestamp()
    {
        if (class_exists("DateTime")) {
            $date = new DateTime("{$this->_date['year']}-{$this->_date['month']}-{$this->_date['day_of_month']}");
            return (method_exists('DateTime', 'getTimestamp')) ? $date->getTimestamp() : $date->format('U');
        } else
            return mktime (0, 0, 0, $this->_date['day_of_month'], $this->_date['month'], $this->_date['year'] /*[, int $is_dst = -1 ]*/);
    }

    public function setDateByTimestamp($time=null)
    {
        if (!isset($time))  $time=time();

        $dat=getdate($time);
        $date=array(
            'hour'=>0,
            'min'=>0,
            'sec'=>0,
            'month'=>$dat['mon'],
            'month_string'=>$dat['month'],
            'day_of_month'=>$dat['mday'],
            'day_of_week'=>$dat['wday'],
            'day_of_week_string'=>$dat['weekday'],
            'day_of_year'=>$dat['yday'],
            'year'=>$dat['year']
        );

        $this->_date=$date;

        return $this;
    }

    public function format($format)
    {
        //return date($format, $this->getTimestamp());

        // handle localized format
        return Toolset_DateParser::formatDate($this, $format);
    }
}

class Toolset_DateParser
{
    private static $_ZONE_NAMES = array('AM' => 'AM','PM' => 'PM');
    private static $_MONTH_NAMES = array('January','February','March','April','May','June','July','August','September','October','November','December');
    private static $_DAY_NAMES = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
    private static $_ENGLISH_MONTH_NAMES = array('January','February','March','April','May','June','July','August','September','October','November','December');
    private static $_ENGLISH_DAY_NAMES = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');

    private static function _to_int($str)
    {
        // return the integer representation of the string given as argument
        return intval($str);
    }

    private static function _escape_regexp($str)
    {
        // return string with special characters escaped
        //return preg_replace('#([\-\.\*\+\?\^\$\{\}\(\)\|\[\]\/\\])#', '\\$1', $str);
        return preg_quote($str, "/");
    }

    private static function _str_pad($n, $c)
    {
        if (strlen($n = $n . '') < $c)
        {
            return implode('0',array_fill((++$c) - strlen($n))). $n;
        }
        return $n;
    }

    private static function _is_string($s)
    {
        return is_string($s)?true:false;
    }

    public static function cmp($a, $b){ return $a['position'] - $b['position']; }

    public static function parseDate($date, $supposed_format)
    {
        // if already a date object
        if ($date instanceof Toolset_Date)
        {
            $date->setDate(array('hour'=>0,'min'=>0,'sec'=>0)); // normalize time part
            return $date;
        }

        if (
            !isset($date) ||
            !self::_is_string($date) ||
            !isset($supposed_format) ||
            !self::_is_string($supposed_format)
            )
        return false;

        // treat argument as a string
        $str_date = (string)$date . '';
        $supposed_format = (string)$supposed_format.'';

        // if value is given
        if ($str_date != '' && $supposed_format != '')
        {
                //echo '<br/>Date given<br />';
                // prepare the format by removing white space from it
                // and also escape characters that could have special meaning in a regular expression
                $format = self::_escape_regexp(preg_replace('/\s/','',$supposed_format));

                // allowed characters in date's format
                $format_chars = array('d','D','j','l','N','S','w','F','m','M','n','Y','y');

                // "matches" will contain the characters defining the date's format
                $matches = array();

                // "regexp" will contain the regular expression built for each of the characters used in the date's format
                $regexp = array();

            // iterate through the allowed characters in date's format
            for ($i = 0; $i < count($format_chars); $i++)
            {
                // if character is found in the date's format
                if (($position = strpos($format,$format_chars[$i])) > -1)

                    // save it, alongside the character's position
                    $matches[]=array('character'=> $format_chars[$i], 'position'=> $position);
            }

            // sort characters defining the date's format based on their position, ascending
            usort($matches,array('Toolset_DateParser','cmp'));

            // iterate through the characters defining the date's format
            for ($index=0; $index<count($matches); $index++)
            {
                $match=$matches[$index];

                // add to the array of regular expressions, based on the character
                switch ($match['character'])
                {

                    case 'd': $regexp[]='0[1-9]|[12][0-9]|3[01]'; break;
                    case 'D': $regexp[]='[a-z]{3}'; break;
                    case 'j': $regexp[]='[1-9]|[12][0-9]|3[01]'; break;
                    case 'l': $regexp[]='[a-z]+'; break;
                    case 'N': $regexp[]='[1-7]'; break;
                    case 'S': $regexp[]='st|nd|rd|th'; break;
                    case 'w': $regexp[]='[0-6]'; break;
                    case 'F': $regexp[]='[a-z]+'; break;
                    case 'm': $regexp[]='0[1-9]|1[012]+'; break;
                    case 'M': $regexp[]='[a-z]{3}'; break;
                    case 'n': $regexp[]='[1-9]|1[012]'; break;
                    case 'Y': $regexp[]='[0-9]{4}'; break;
                    case 'y': $regexp[]='[0-9]{2}'; break;

                }
            }

            // if we have an array of regular expressions
            if (!empty($regexp))
            {

                // we will replace characters in the date's format in reversed order
                $matches=array_reverse($matches);

                // iterate through the characters in date's format
                for ($index=0; $index<count($matches); $index++)
                {
                    $match=$matches[$index];

                    // replace each character with the appropriate regular expression
                    $format = str_replace($match['character'],'(' . $regexp[count($regexp) - $index - 1] . ')', $format);
                }

                // the final regular expression
                //$regexp = '/^' . $format . '$/ig';
                $regexp = '/^' . $format . '$/i';

                //echo '<br /><textarea>'.$regexp.'</textarea><br />';

                //preg_match_all('/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[012]+)\/([0-9]{2})$/i','13/10/12',$foo);
                //print_r($foo);

                // if regular expression was matched
                if (preg_match_all($regexp, preg_replace('/\s/', '', $str_date),$segments))
                {
                //echo '<br/>Regex matched<br />';
                    //print_r($segments);

                    // check if date is a valid date (i.e. there's no February 31)
                    $english_days   = self::$_ENGLISH_DAY_NAMES;
                    $english_months = self::$_ENGLISH_MONTH_NAMES;

                    // by default, we assume the date is valid
                    $valid = true;

                    // reverse back the characters in the date's format
                    $matches=array_reverse($matches);

                    // iterate through the characters in the date's format
                    for ($index=0; $index<count($matches); $index++)
                    {
                        $match=$matches[$index];

                        // if the date is not valid, don't look further
                        if (!$valid) break; //return true;

                        // based on the character
                        switch ($match['character'])
                        {

                            case 'm':
                            case 'n':

                                // extract the month from the value entered by the user
                                $original_month = self::_to_int($segments[$index+1][0]);

                                break;

                            case 'd':
                            case 'j':

                                // extract the day from the value entered by the user
                                $original_day = self::_to_int($segments[$index+1][0]);

                                break;

                            case 'D':
                            case 'l':
                            case 'F':
                            case 'M':

                                // if day is given as day name, we'll check against the names in the used language
                                if ($match['character'] == 'D' || $match['character'] == 'l') $iterable = self::$_DAY_NAMES;

                                // if month is given as month name, we'll check against the names in the used language
                                else $iterable = self::$_MONTH_NAMES;

                                // by default, we assume the day or month was not entered correctly
                                $valid = false;

                                // iterate through the month/days in the used language
                                for ($key=0; $key<count($iterable); $key++)
                                {
                                    // if month/day was entered correctly, don't look further
                                    if ($valid) break; //return true;

                                    $value=$iterable[$key];

                                    // if month/day was entered correctly
                                    if (strtolower($segments[$index+1][0]) == strtolower(substr($value, 0, ($match['character'] == 'D' || $match['character'] == 'M' ? 3 : strlen($value)))))
                                    {

                                        // extract the day/month from the value entered by the user
                                        switch ($match['character'])
                                        {

                                            case 'D': $segments[$index+1][0] = substr($english_days[$key],0, 3); break;
                                            case 'l': $segments[$index+1][0] = $english_days[$key]; break;
                                            case 'F': $segments[$index+1][0] = $english_months[$key]; $original_month = $key + 1; break;
                                            case 'M': $segments[$index+1][0] = substr($english_months[$key],0, 3); $original_month = $key + 1; break;

                                        }

                                        // day/month value is valid
                                        $valid = true;

                                    }

                                }

                                break;

                            case 'Y':

                                // extract the year from the value entered by the user
                                $original_year = self::_to_int($segments[$index+1][0]);

                                break;

                            case 'y':

                                // extract the year from the value entered by the user
                                $original_year = '19' + self::_to_int($segments[$index+1][0]);

                                break;

                        }
                    }

                    // if everything is ok so far
                    if ($valid)
                    {
                        //echo '<br/>Date valid 1<br />';

                        // generate a Date object using the values entered by the user
                        // (handle also the case when original_month and/or original_day are undefined - i.e date format is "Y-m" or "Y")
                       /* print_r(array(
                            'year'=>$original_year,
                            'month'=>$original_month,
                            'day'=>$original_day,
                            ));*/

                        // if, after that, the date is the same as the date entered by the user
                        //if (date.getFullYear() == original_year && date.getDate() == (original_day || 1) && date.getMonth() == ((original_month || 1) - 1))
                        //var_dump(checkdate(10, 12, 2012));
                        if (checkdate ((int)$original_month, (int)$original_day, (int)$original_year))
                        {
                            // normalize time part, only date part checked
                            $date = new Toolset_Date(array(
                                'year'=>$original_year,
                                'month'=>$original_month,
                                'day_of_month'=>$original_day,
                                ));
                            $date->setDate(array('hour'=>0,'min'=>0,'sec'=>0));
                            // return the date as our date object
                        //echo '<br/>Date valid 2<br />';
                            return $date;
                        }
                    }
                }
            }
        }
        // if script gets this far, return false as something must've went wrong
        return false;
    }

    public static function formatDate($date, $format, $isTimestamp=false)
    {

        // if not a date object
        /*if (!isset($date) || !($date instanceof Toolset_Date))
        {
            return '';
        }
        $date->setDate(array('hour'=>0,'min'=>0,'sec'=>0)); // normalize time
        return $date->format($format);*/

        if ($isTimestamp)
        {
            $dat=new Toolset_Date();
            $date=$dat->setDateByTimestamp($date);
        }

        // if not a date object
        if (!isset($date) || !($date instanceof Toolset_Date))
        {
            return '';
        }

        $date->setDate(array('hour'=>0,'min'=>0,'sec'=>0)); // normalize time part

        $result = '';

        // extract parts of the date:
        // day number, 1 - 31
        $j = $date->getDate('day_of_month');

        // day of the week, 0 - 6, Sunday - Saturday
        $w = $date->getDate('day_of_week');

        // the name of the day of the week Sunday - Saturday
        $l = self::$_DAY_NAMES[$w];

        // the month number, 1 - 12
        $n = $date->getDate('month');// + 1;

        // the month name, January - December
        $f = self::$_MONTH_NAMES[$n - 1];

        // the year (as a string)
        $y = (string)$date->getDate('year') . '';

        // iterate through the characters in the format
        for ($i = 0; $i < strlen($format); $i++)
        {

            // extract the current character
            $chr = $format[$i];

            // see what character it is
            switch($chr)
            {
                // year as two digits
                case 'y': $y = substr($y,2);

                // year as four digits
                case 'Y': $result .= $y; break;

                // month number, prefixed with 0
                case 'm': $n = self::_str_pad($n, 2);

                // month number, not prefixed with 0
                case 'n': $result .= $n; break;

                // month name, three letters
                case 'M': $f = substr($f,0,3);

                // full month name
                case 'F': $result .= $f; break;

                // day number, prefixed with 0
                case 'd': $j = self::_str_pad($j, 2);

                // day number not prefixed with 0
                case 'j': $result .= $j; break;

                // day name, three letters
                case 'D': $l = substr($l, 0, 3);

                // full day name
                case 'l': $result .= $l; break;

                // ISO-8601 numeric representation of the day of the week, 1 - 7
                case 'N': $w++;

                // day of the week, 0 - 6
                case 'w': $result .= $w; break;

                // English ordinal suffix for the day of the month, 2 characters
                // (st, nd, rd or th (works well with j))
                case 'S':

                    if ($j % 10 == 1 && $j != '11') $result .= 'st';

                    else if ($j % 10 == 2 && $j != '12') $result .= 'nd';

                    else if ($j % 10 == 3 && $j != '13') $result .= 'rd';

                    else $result .= 'th';

                    break;

                // this is probably the separator
                default: $result .= $chr;

            }

        }
        // return formated date
        return $result;
    }

    public static function setDateLocaleStrings($dn=null, $mn=null, $zn=null)
    {
        if (isset($mn))
        {
            self::$_MONTH_NAMES = $mn;
        }
        if (isset($dn))
        {
            self::$_DAY_NAMES = $dn;
        }
        if (isset($zn))
            self::$_ZONE_NAMES = $zn;
    }

    public static function isDate($val, $format, &$getDate=null)
    {
        $date=self::parseDate($val,$format);
        if ($date!==false)
        {
            //echo '<hr />Date correct<hr />';
            if (isset($getDate))
            {
                $getDate['date']=$date;
            }
            return true;
        }
        return false;
    }

    public static function currentDate()
    {
        return Toolset_Date::getToday();
    }
}

class Toolset_Stack
{
    // Stack object constructor
    private $arrStack=array();
    private $intIndex=0;

    // Converts stack contents into a comma separated string
    public function toString()
    {
        $intCntr = 0;
        $strRet  =  "";
        if ($this->intIndex == 0) return null;
        for ($intCntr = 0; $intCntr < $this->intIndex; $intCntr++)
        {
            if ($strRet == '')
                $strRet .= print_r($this->arrStack[$intCntr]->val,true);
            else
                $strRet .= "," . print_r($this->arrStack[$intCntr]->val,true);
        }
        return $strRet;
    }

    // Returns size of stack
    public function Size()
    {
        return $this->intIndex;
    }

    // This method tells us if this Stack object is empty
    public function IsEmpty()
    {
        return ($this->intIndex == 0)?true:false;
    }

    // This method pushes a new element onto the top of the stack
    public function Push($newData)
    {
        $this->arrStack[$this->intIndex++] = $newData;
    }

    // This method pops the top element off of the stack
    public function Pop()
    {
        $retVal = null;
        if ($this->intIndex > 0)
        {
           $retVal = $this->arrStack[--$this->intIndex];
        }
        return $retVal;
    }

    // Gets an element at a particular offset from top of the stack
    function Get($intPos)
    {
        if ($intPos >= 0 && $intPos < $this->intIndex)
            $retVal = $this->arrStack[$this->intIndex - $intPos - 1];
        return $retVal;
    }
}

class Toolset_Tokenizer
{

    // private members
    private static $_tok_map_prefix = '__TOKEN_MAP_PREFIX__';
    private static $_Alpha    = '';
    private static $_lstAlpha = '';
    private static $_lstVariablePrefix = '';
    private static $_lstDigits   = "0123456789";
    private static $_lstArithOps = array("^","*","/","%","+","-");
    private static $_lstLogicOps = array("NOT","!","OR","|","AND","&");
    private static $_lstCompaOps = array("<","<=",">",">=","<>","=","lt","lte","gt","gte","ne","eq");
    private static $_lstFuncOps  = array(
            "AVG","ABS","ACOS","ARRAY","ASC","ASIN","ATAN",
            "CHR","CONTAINS","COOKIE","COS",
            "DATE","FIX","HEX","IIF","LCASE","LEN","LEFT","LOG",
            "MAX","MID","MIN","NUM","RAND","REGEX","RIGHT","ROUND",
            "SIN","SQRT","STR","TAN","TODAY","UCASE","USER", "EMPTY", "empty"
    );

    private static $_UNARY_NEGATIVE = "-";
    private static $_UNARY_NEGATION = "!";
    private static $_ARG_TERMINAL = "?";
    private static $_EMPTY_TOKEN;
    private static $_EMPTY_STRING;
    private static $_TOKEN_TYPES = array(
        '__DEFAULT__'=>0,
        'STRING_LITERAL'=>8,
        'REGEX'=> 83,
        'ARRAY'=>81,
        'ARRAY_LITERAL'=>82,
        'DATE'=>1,
        'ARITHMETIC_OP'=>2,
        'LOGICAL_OP'=>3,
        'COMPARISON_OP'=>4,
        'NUMBER'=>5,
        'BOOLEAN'=>6,
        'VARIABLE'=>7,
        'FUNCTION'=>9,
        'COMMA'=>10,
        'LEFT_PAREN'=>11,
        'LEFT_BRACKET'=>111,
        'RIGHT_PAREN'=>12,
        'RIGHT_BRACKET'=>122,
        'ARG_TERMINAL'=>13,
        'UNARY_NEGATIVE'=>14,
        'UNARY_NEGATION'=>15,
        'EMPTY_TOKEN'=>30,
        'UNKNOWN'=>40
        );

    public static $TOKEN_TYPE;
    public static $EMPTY_TOKEN;
    public static $EMPTY_STRING;
    public static $UNARY_NEGATIVE;
    public static $UNARY_NEGATION;
    public static $ARG_TERMINAL;

    private static function _isDefined($s)
    {
        return (isset($s));
    }

    private static function _isDigit($c)
    {
        if (!self::_isDefined($c))
            return false;
        return (($c!='' && strpos(self::$_lstDigits,$c) >= 0)?true:false);
    }

    private static function _isAlpha($c)
    {
        if (!self::_isDefined($c))
            return false;
        return (($c!='' && strpos(self::$_lstAlpha,$c) >= 0)?true:false);
    }

    private static function _isOperator($s)
    {
        if (!self::_isDefined($s))
            return false;
        return (in_array($s,self::$_lstArithOps)?true:false);
    }

    private static function _isLogicOperator($s)
    {
        if (!self::_isDefined($s))
            return false;
        return (in_array($s,self::$_lstLogicOps)?true:false);
    }

    private static function _isCompOperator($s)
    {
        if (!self::_isDefined($s))
            return false;
        return (in_array($s,self::$_lstCompaOps)?true:false);
    }

    private static function _isFunction($s)
    {
        if (!self::_isDefined($s))
            return false;
        return (in_array($s,self::$_lstFuncOps)?true:false);
    }

    private static function _isVariableName($s)
    {
        if (!self::_isDefined($s))
            return false;
        $c=($s=='')?'':$s{0};
        return (($c!='' && strpos(self::$_lstVariablePrefix,$c) >= 0)?true:false);
    }

    public static function isDateInstance($s)
    {
        if (!self::_isDefined($s))
            return false;
        if ($s instanceof Toolset_Date)
            return true;
        return false;
    }

    public static function isArrayInstance($s)
    {
        if (!self::_isDefined($s))
            return false;
        if (is_array($s))
            return true;
        return false;
    }

    public static function isRegExpInstance($s)
    {
        if (!self::_isDefined($s))
            return false;
        if ($s instanceof Toolset_Regex)
            return true;
        return false;
    }

    public static function isNumber($s)
    {
        if (!self::_isDefined($s)) return false;
        //if (_isDateInstance(s) || _isRegExpInstance(s) || _isArrayInstance(s)) return false;
        if (is_numeric($s) && !is_nan((float)$s))
            return true;
        return false;
    }

    public static function isBoolean($s)
    {
        if (!self::_isDefined($s))
            return false;

        if (is_bool($s)) return true;

        if (strtoupper((string)$s)=='TRUE' || strtoupper((string)$s)=='FALSE')
            return true;

        return false;
    }

    private static function _ltrim($s, $ch=' ')
    {
        return ltrim($s,$ch);
    }

    private static function _rtrim($s, $ch=' ')
    {
        return rtrim($s,$ch);
    }

    private static function _trim($s, $ch=' ')
    {
        return trim($s,$ch);
    }

    // build maps for fast lookup
    private static function _buildMaps()
    {
        self::$_Alpha    = "abcdefghijklmnopqrstuvwxyz";
        self::$_lstAlpha = self::$_Alpha . strtoupper(self::$_Alpha);
        self::$_lstVariablePrefix = '_$' . self::$_lstAlpha;
    }

    public static function init()
    {
        static $isInited=false;

        if (!$isInited)
        {
            self::_buildMaps();
            self::$TOKEN_TYPE = self::$_TOKEN_TYPES;
            self::$_EMPTY_TOKEN = self::makeToken('', self::$_TOKEN_TYPES['EMPTY_TOKEN']);
            self::$_EMPTY_STRING = self::makeToken('', self::$_TOKEN_TYPES['STRING_LITERAL']);
            self::$EMPTY_TOKEN = self::$_EMPTY_TOKEN;
            self::$EMPTY_STRING = self::$_EMPTY_STRING;
            self::$UNARY_NEGATIVE = self::makeToken(self::$_UNARY_NEGATIVE, self::$_TOKEN_TYPES['UNARY_NEGATIVE']);
            self::$UNARY_NEGATION = self::makeToken(self::$_UNARY_NEGATION, self::$_TOKEN_TYPES['UNARY_NEGATION']);
            self::$ARG_TERMINAL = self::makeToken(self::$_ARG_TERMINAL, self::$_TOKEN_TYPES['ARG_TERMINAL']);
            $isInited=true;
        }
    }

    public static function makeToken($tok, $force_type=null/*, $debug=false*/)
    {
        /*if ($debug)
        {
            cred_log($force_type);
        }*/
        $token=(object)array(
            'val'=>$tok,
            'type'=>self::$_TOKEN_TYPES['UNKNOWN'],
            'isStringLiteral'=>false,
            'isArray'=>false,
            'isRegex'=>false,
            'isDate'=>false,
            'isFunction'=>false,
            'isNumber'=>false,
            'isBoolean'=>false,
            'isOp'=>false,
            'isArithmeticOp'=>false,
            'isLogicOp'=>false,
            'isCompOp'=>false,
            'isParen'=>false,
            'isLeftParen'=>false,
            'isRightParen'=>false,
            'isComma'=>false,
            'isVariable'=>false,
            'isArgTerminal'=>false,
            'isUnaryNegative'=>false,
            'isUnaryNegation'=>false,
            'isEmpty'=>false,
        );

        if (!isset($force_type))
            $force_type=self::$_TOKEN_TYPES['__DEFAULT__'];

        switch ($force_type)
        {
            case self::$_TOKEN_TYPES['EMPTY_TOKEN']:
                    $token->type=self::$_TOKEN_TYPES['EMPTY_TOKEN'];
                    $token->isEmpty=true;
                    $token->val='';
                    break;
            case self::$_TOKEN_TYPES['STRING_LITERAL']:
                    $token->type=self::$_TOKEN_TYPES['STRING_LITERAL'];
                    $token->isStringLiteral=true;
                    $token->val=(string)$tok;
                    /*if ($debug)
                    {
                    cred_log('---------');
                    cred_log($token);
                    cred_log('---------');
                    }*/
                    break;
            case self::$_TOKEN_TYPES['DATE']:
                    if (self::isDateInstance($tok))
                    {
                        $token->type=self::$_TOKEN_TYPES['DATE'];
                        $token->isDate=true;
                    }
                    break;
            case self::$_TOKEN_TYPES['ARRAY']:
                    if (!self::isArrayInstance($tok))
                    {
                        $token->val=array($tok);
                    }
                    $token->type=self::$_TOKEN_TYPES['ARRAY'];
                    $token->isArray=true;
                    break;
            case self::$_TOKEN_TYPES['REGEX']:
                    if (!self::isRegExpInstance($tok))
                    {
                        $token->val=new Toolset_Regex($tok);
                    }
                    $token->type=self::$_TOKEN_TYPES['REGEX'];
                    $token->isRegex=true;
                    break;
            case self::$_TOKEN_TYPES['COMMA']:
                    $token->type=self::$_TOKEN_TYPES['COMMA'];
                    $token->isComma=true;
                    $token->val=',';
                    break;
            case self::$_TOKEN_TYPES['LEFT_PAREN']:
                    $token->type=self::$_TOKEN_TYPES['LEFT_PAREN'];
                    $token->isLeftParen=true;
                    $token->isParen=true;
                    $token->val='(';
                    break;
            case self::$_TOKEN_TYPES['RIGHT_PAREN']:
                    $token->type=self::$_TOKEN_TYPES['RIGHT_PAREN'];
                    $token->isRightParen=true;
                    $token->isParen=true;
                    $token->val=')';
                    break;
            case self::$_TOKEN_TYPES['ARG_TERMINAL']:
                    $token->type=self::$_TOKEN_TYPES['ARG_TERMINAL'];
                    $token->isArgTerminal=true;
                    $token->val=self::$_ARG_TERMINAL;
                    break;
            case self::$_TOKEN_TYPES['UNARY_NEGATIVE']:
                    $token->type=self::$_TOKEN_TYPES['UNARY_NEGATIVE'];
                    $token->isUnaryNegative=true;
                    $token->val=self::$_UNARY_NEGATIVE;
                    $token->isArithmeticOp=true;
                    $token->isOp=true;
                    break;
            case self::$_TOKEN_TYPES['UNARY_NEGATION']:
                    $token->type=self::$_TOKEN_TYPES['UNARY_NEGATION'];
                    $token->isUnaryNegation=true;
                    $token->val=self::$_UNARY_NEGATION;
                    $token->isLogicOp=true;
                    $token->isOp=true;
                    break;
            case self::$_TOKEN_TYPES['NUMBER']:
                    $token->type=self::$_TOKEN_TYPES['NUMBER'];
                    $token->isNumber=true;
                    if (is_string($tok))
                        $token->val=(float)($tok);
                    elseif (is_bool($tok))
                        $token->val=((bool)$tok===true)?1:0;
                    else
                        $token->val=(float)$tok;
                    break;
            case self::$_TOKEN_TYPES['BOOLEAN']:
                    $token->type=self::$_TOKEN_TYPES['BOOLEAN'];
                    $token->isBoolean=true;
                    if (is_bool($tok))
                        $token->val=(bool)$tok;
                    elseif (is_string($tok))
                        $token->val=(strtoupper($tok)=='TRUE')?true:false;
                    elseif (is_numeric($tok))
                        $token->val=((float)$tok != 0)?true:false;
                    else
                        $token->val=(bool)$tok;
                    break;
            case self::$_TOKEN_TYPES['VARIABLE']:
                    $token->type=self::$_TOKEN_TYPES['VARIABLE'];
                    $token->isVariable=true;
                    break;
            case self::$_TOKEN_TYPES['__DEFAULT__']:
            default:
                    if (
                        (is_object($tok) && isset($tok->_isStringLiteral) && $tok->_isStringLiteral)
                    )
                    {
                        $token->type=self::$_TOKEN_TYPES['STRING_LITERAL'];
                        $token->isStringLiteral=true;
                        $token->val=(string)$tok->val;
                    }
                    // date token
                    elseif (self::isDateInstance($tok))
                    {
                        $token->type=self::$_TOKEN_TYPES['DATE'];
                        $token->isDate=true;
                    }
                    // array token
                    elseif (self::isArrayInstance($tok))
                    {
                        $token->type=self::$_TOKEN_TYPES['ARRAY'];
                        $token->isArray=true;
                    }
                    // regex token
                    elseif (self::isRegExpInstance($tok))
                    {
                        $token->type=self::$_TOKEN_TYPES['REGEX'];
                        $token->isRegex=true;
                    }
                    elseif ($tok==',')
                    {
                        $token->type=self::$_TOKEN_TYPES['COMMA'];
                        $token->isComma=true;
                        $token->val=',';
                    }
                    elseif ($tok=='(')
                    {
                        $token->type=self::$_TOKEN_TYPES['LEFT_PAREN'];
                        $token->isLeftParen=true;
                        $token->isParen=true;
                        $token->val='(';
                        $token->isOp=true;
                    }
                    elseif ($tok==')')
                    {
                        $token->type=self::$_TOKEN_TYPES['RIGHT_PAREN'];
                        $token->isRightParen=true;
                        $token->isParen=true;
                        $token->val=')';
                        //token.isOp=true;
                    }
                    /*else if (tok=='{')
                    {
                        token.type=_TOKEN_TYPES.LEFT_BRACKET;
                        token.isLeftBracket=true;
                        token.isBracket=true;
                        token.val='{';
                        token.isOp=true;
                    }
                    else if (tok=='}')
                    {
                        token.type=_TOKEN_TYPES.RIGHT_BRACKET;
                        token.isRightBracket=true;
                        token.isBracket=true;
                        token.val='}';
                        //token.isOp=true;
                    }*/
                    elseif (self::isNumber($tok))
                    {
                        $token->type=self::$_TOKEN_TYPES['NUMBER'];
                        $token->isNumber=true;
                        if (is_string($tok))
                            $token->val=(float)($tok);
                        else if (is_bool($tok))
                            $token->val=((bool)$tok===true)?1:0;
                        else
                            $token->val=(float)$tok;
                    }
                    elseif (self::isBoolean($tok))
                    {
                        $token->type=self::$_TOKEN_TYPES['BOOLEAN'];
                        $token->isBoolean=true;
                        if (is_bool($tok))
                            $token->val=(bool)$tok;
                        else if (is_string($tok))
                            $token->val=(strtoupper($tok)=='TRUE')?true:false;
                        else if (is_numeric($tok))
                            $token->val=((float)$tok != 0)?true:false;
                        else
                            $token->val=(bool)$tok;
                    }
                    elseif (self::_isOperator($tok))
                    {
                        $token->type=self::$_TOKEN_TYPES['ARITHMETIC_OP'];
                        $token->isArithmeticOp=true;
                        $token->isOp=true;

                    }
                    elseif (self::_isLogicOperator($tok))
                    {
                        $token->type=self::$_TOKEN_TYPES['LOGICAL_OP'];
                        $token->isLogicOp=true;
                        $token->isOp=true;
                    }
                    elseif (self::_isCompOperator($tok))
                    {
                        $token->type=self::$_TOKEN_TYPES['COMPARISON_OP'];
                        $token->isCompOp=true;
                        $token->isOp=true;
                    }
                    elseif (self::_isFunction($tok))
                    {
                        $token->type=self::$_TOKEN_TYPES['FUNCTION'];
                        $token->isFunction=true;
                        $token->val=(string)$tok;
                    }
                    elseif (self::_isVariableName($tok))
                    {
                        $token->type=self::$_TOKEN_TYPES['VARIABLE'];
                        $token->isVariable=true;
                    }
                    break;
        }
        if ($token->isOp || $token->isFunction)
        {
            $intRet = 0;

            switch($token->val)
            {
                case "+" :
                case "-" :
                    $intRet = 50;
                    break;
                case "*" :
                case "/" :
                case "%" :
                    $intRet = 60;
                    break;
                case "^" :
                    $intRet = 70;
                    break;
                case self::$_UNARY_NEGATIVE:
                case self::$_UNARY_NEGATION:
                case "!" :
                case "NOT" :
                    $intRet = 100;
                    break;
                case "(" :
                    $intRet = 1000;
                    break;
                /*case "{" :
                    intRet = 500;  // as function
                    break;*/
                case "AND" :
                case "&" :
                    $intRet = 35;
                    break;
                case "OR" :
                case "|" :
                    $intRet = 30;
                    break;
                case ">" :
                case ">=" :
                case "<" :
                case "<=" :
                case "=" :
                case "<>" :
                case "gt" :
                case "gte" :
                case "lt" :
                case "lte" :
                case "eq" :
                case "ne" :
                    $intRet = 40;
                    break;
                default :
                    if ($token->isFunction)
                        $intRet = 500;
                    else
                        $intRet = 0;
                    break;
            }
            $token->precedence=$intRet;
        }
        else
            $token->precedence=0;
        return $token;
    }

    public static function cloneToken($tok)
    {
        $newtok=(object)array();

        foreach ($tok as $at=>$val)
        {
            if (self::isDateInstance($tok->$at))
                $newtok->$at=new Toolset_Date($tok->$at->getDate());
            else
                $newtok->$at=$tok->$at;
        }
        return $newtok;
    }

    public static function isString($a)
    {
        if (!self::_isDefined($a)) return false;
        return (is_string($a))?true:false;
    }

    public static function isDate($pstrVal, $format, &$getDate)
    {
        if (!self::_isDefined($pstrVal))
            return false;
        if (self::isDateInstance($pstrVal))
        {
            if (isset($getDate))
                $getDate['date']=$pstrVal;
            return true;
        }
        return Toolset_DateParser::isDate($pstrVal, $format, $getDate);
    }

    public static function toArray($v)
    {
        if (self::isArrayInstance($v))
            return $v;
        else return array($v);
    }

    public static function toNumber($pobjVal)
    {
        if (is_numeric($pobjVal))
            return (float)$pobjVal;
        else
        {
            $dblRet = (float)$pobjVal;
            return $dblRet;
        }
    }

    public static function toBoolean($pobjVal)
    {
        //var_dump($pobjVal);
        if (!isset($pobjVal))
            throw new Exception("Boolean value is not defined!");
        else if (is_bool($pobjVal))
            return(bool)$pobjVal;
        else if (is_numeric($pobjVal))
            return (bool)(((float)$pobjVal) != 0.0);
        else if (strtoupper((string)$pobjVal)=='TRUE')
            return true;
        else if (strtoupper((string)$pobjVal)=='FALSE')
            return false;
        return null;
    }

    public static function Tokanize($pstrExpression)
    {
        // build fast lookup maps
        self::init();

        $intCntr   = 0;
        $intBraces = 0;
        $intBrackets = 0;
        $intIndex  = 0;
        $strToken  = "";
        $arrTokens = array();
        $pstrExpression = self::_trim($pstrExpression);
        while ($intCntr < strlen($pstrExpression))
        {
            $prevToken = self::$_EMPTY_TOKEN;
            $chrChar = substr($pstrExpression,$intCntr, 1);
            switch ($chrChar)
            {
                case " " :
                    if ($strToken!= '')
                    {
                        $arrTokens[$intIndex] = self::makeToken($strToken);
                        $intIndex++;
                        $strToken = "";
                    }
                    break;
                //case "{":
                case "(":
                    //(chrChar=='(')?intBraces++:intBrackets++;
                    $intBraces++;
                    if ($strToken!='')
                    {
                        $arrTokens[$intIndex] = self::makeToken($strToken);
                        $intIndex++;
                        $strToken = "";
                    }
                    $arrTokens[$intIndex] = self::makeToken($chrChar);
                    $intIndex++;
                    break;
                //case "}" :
                case ")" :
                    //(chrChar==')')?intBraces--:intBrackets--;
                    $intBraces--;
                    if ($strToken!='')
                    {
                        $arrTokens[$intIndex] = self::makeToken($strToken);
                        $intIndex++;
                        $strToken = "";
                    }
                    $arrTokens[$intIndex] = self::makeToken($chrChar);
                    $intIndex++;
                    break;
                case "^" :
                case "*" :
                case "/" :
                case "%" :
                case "&" :
                case "|" :
                case "," :
                case "!" :
                    if ($strToken!='')
                    {
                        $arrTokens[$intIndex] = self::makeToken($strToken);
                        $intIndex++;
                        $strToken = "";
                    }
                    $arrTokens[$intIndex] = self::makeToken($chrChar);
                    $intIndex++;
                    break;
                case "-" :
                    if ($strToken!='')
                    {
                        $arrTokens[$intIndex] = self::makeToken($strToken);
                        $intIndex++;
                        $strToken = "";
                    }
                    $chrNext = substr($pstrExpression,$intCntr + 1, 1);
                    if (count($arrTokens) > 0)
                        $prevToken = $arrTokens[$intIndex - 1];
                    if (/*intCntr == 0 ||*/(($prevToken->isArithmeticOp ||
                        $prevToken->isLeftParen || $prevToken->isComma) &&
                        (self::_isDigit($chrNext) || $chrNext == "(")))
                    {
                        // Negative Number
                        $strToken .= $chrChar;
                    }
                    else
                    {
                        $arrTokens[$intIndex] = self::makeToken($chrChar);
                        $intIndex++;
                        $strToken = "";
                    }
                    break;
                case "+" :
                    if ($strToken!='')
                    {
                        $arrTokens[$intIndex] = self::makeToken($strToken);
                        $intIndex++;
                        $strToken = "";
                    }
                    $chrNext = substr($pstrExpression,$intCntr + 1, 1);
                    if (count($arrTokens) > 0)
                        $prevToken = $arrTokens[$intIndex - 1];
                    if (/*intCntr == 0 ||*/ (($prevToken->isArithmeticOp ||
                        $prevToken->isLeftParen || $prevToken->isComma) &&
                        (self::_isDigit($chrNext) || $chrNext == "(")))
                    {
                        // positive Number
                        $strToken .= $chrChar;
                    }
                    else
                    {
                        $arrTokens[$intIndex] = self::makeToken($chrChar);
                        $intIndex++;
                        $strToken = "";
                    }
                    break;
                case "<" :
                    $chrNext = substr($pstrExpression,$intCntr + 1, 1);
                    if ($strToken!='')
                    {
                        $arrTokens[$intIndex] = self::makeToken($strToken);
                        $intIndex++;
                        $strToken = "";
                    }
                    if ($chrNext == "=")
                    {
                        $arrTokens[$intIndex] = self::makeToken($chrChar . "=");
                        $intIndex++;
                        $intCntr++;
                    }
                    else if ($chrNext == ">")
                    {
                        $arrTokens[$intIndex] = self::makeToken($chrChar . ">");
                        $intIndex++;
                        $intCntr++;
                    }
                    else
                    {
                        $arrTokens[$intIndex] = self::makeToken($chrChar);
                        $intIndex++;
                    }
                    break;
                case ">" :
                    $chrNext = substr($pstrExpression,$intCntr + 1, 1);
                    if ($strToken!='')
                    {
                        $arrTokens[$intIndex] = self::makeToken($strToken);
                        $intIndex++;
                        $strToken = "";
                    }
                    if ($chrNext == "=")
                    {
                        $arrTokens[$intIndex] = self::makeToken($chrChar . "=");
                        $intIndex++;
                        $intCntr++;
                    }
                    else
                    {
                        $arrTokens[$intIndex] = self::makeToken($chrChar);
                        $intIndex++;
                    }
                    break;
                case "=" :
                    if ($strToken!='')
                    {
                        $arrTokens[$intIndex] = self::makeToken($strToken);
                        $intIndex++;
                        $strToken = "";
                    }
                    $arrTokens[$intIndex] = self::makeToken($chrChar);
                    $intIndex++;
                    break;
                case "'" :
                case "\"" :
                    if ($strToken!='')
                    {
                        $arrTokens[$intIndex] = self::makeToken($strToken);
                        $intIndex++;
                        $strToken = "";
                    }
                    /*
                    var found=false;
                    var initCnt=intCntr;
                    while (!found)
                    {
                        intPos = pstrExpression.indexOf(chrChar, intCntr + 1);
                        if (intPos < 0) 
                            throw "Unterminated string constant";
                        else
                        {
                            if (pstrExpression.charAt(intPos-1)!='\\') // not escape quote
                            {
                                found=true;
                                strToken += pstrExpression.substring(initCnt + 1, intPos);
                                // replace all escaped quotes inside string
                                var strTok2=strToken.replace('\\'+chrChar,chrChar);
                                while (strToken!=strTok2) 
                                {
                                    strToken=strTok2
                                    strTok2=strToken.replace('\\'+chrChar,chrChar);
                                }
                                strToken=new String(strTok2);
                                strToken._isStringLiteral=true;
                                arrTokens[intIndex] = _makeToken(strToken);
                                intIndex++;
                                strToken = "";
                                intCntr = intPos;
                            }
                            else
                            {
                                intCntr=intPos;
                            }
                        }
                    }*/
                    $intPos = strpos($pstrExpression,$chrChar, $intCntr + 1);
                    if ($intPos < 0)
                        throw new Exception("Unterminated string constant");
                    else
                    {
                        $strToken .= substr($pstrExpression,$intCntr + 1, $intPos-$intCntr-1);
                        $strToken=(object)array('val'=>$strToken,'_isStringLiteral'=>true);
                        $arrTokens[$intIndex] = self::makeToken($strToken);
                        $intIndex++;
                        $strToken = "";
                        $intCntr = $intPos;
                    }
                    break;
                default :
                    $strToken .= $chrChar;
                    break;
            }
            $intCntr++;
        }
        if ($intBraces > 0)
            throw new Exception("Unbalanced parenthesis!");

        if ($strToken!='')
            $arrTokens[$intIndex] = self::makeToken($strToken);
        return $arrTokens;
    }
}



class Toolset_Parser
{

    private $strInFix = null;
    private $arrVars = array();
    private $arrTokens = null;
    private $arrPostFix = null;
    private $dtFormat = "d/m/Y";

    /*------------------------------------------------------------------------------
     * NAME       : HandleFunctions
     * PURPOSE    : Execute built-in functions
     * PARAMETERS : pstrTok - The current function name
     *              pStack - Operand stack
     * RETURNS    : Nothing, the result is pushed back onto the stack.
     *----------------------------------------------------------------------------*/
    private function _HandleFunctions($pstrTok, &$pStack, $pdtFormat, &$parrVars)
    {
        if (!$pstrTok->isFunction)
            throw new Exception("Unsupported function token [" . $pstrTok->val . "]");

        $varTmp = $pstrTok->val;
        $arrArgs = array();
        $varTerm = Toolset_Tokenizer::$ARG_TERMINAL;
        while (!$pStack->IsEmpty())
        {
            $varTerm = $pStack->Pop();
            if (!$varTerm->isArgTerminal)
                $arrArgs[] = $varTerm;
            else
                break;
        }

        switch ($varTmp)
        {
            case "ARRAY" :

                $arrArray=array();

                $objTmp = 0;
                $intCntr = count($arrArgs);
                while (--$intCntr >= 0)
                {
                    $varTerm = $arrArgs[$intCntr];
                    if ($varTerm->isVariable)
                    {
                        if (!isset($parrVars[$varTerm->val]))
                            throw new Exception("Variable [" . $varTerm->val . "] not defined");
                        else
                            $varTerm = $parrVars[$varTerm->val];
                    }
                    $arrArray=array_merge($arrArray,Toolset_Tokenizer::toArray($varTerm->val));
                }
                $pStack->Push(Toolset_Tokenizer::makeToken($arrArray,Toolset_Tokenizer::$TOKEN_TYPE['ARRAY']));
                break;
            case "TODAY" :
                $pStack->Push(Toolset_Tokenizer::makeToken(Toolset_DateParser::currentDate(), Toolset_Tokenizer::$TOKEN_TYPE['DATE']));
                break;
            case "ACOS" :
            case "ASIN" :
            case "ATAN" :
                throw new Exception("Function [" . $varTmp . "] is not implemented!");
                break;
            case "ABS" :
            case "CHR" :
            case "COS" :
            case "FIX" :
            case "HEX" :
            case "LOG" :
            case "RAND" :
            case "ROUND" :
            case "SIN" :
            case "SQRT" :
            case "TAN" :
                if ($varTmp != "RAND")
                {
                    if (count($arrArgs) < 1)
                        throw new Exception($varTmp . " requires at least one argument!");
                    else if (count($arrArgs) > 1)
                        throw new Exception($varTmp . " requires only one argument!");
                }
                else
                {
                    if (count($arrArgs) < 1)
                        throw new Exception($varTmp . " requires at least one argument!");
                    else if (count($arrArgs) > 2)
                        throw new Exception($varTmp . " requires at most two arguments!");
                }
                $varTerm = $arrArgs[0];
                if ($varTerm->isVariable)
                {
                    if (!isset($parrVars[$varTerm->val]))
                        throw new Exception("Variable [" . $varTerm->val . "] not defined");
                    else
                        $varTerm = $parrVars[$varTerm->val];
                }

                $objTmp=$varTerm->val;

                $rand_min=$rand_max=0;

                if ( is_numeric( $objTmp ) === false )
                    throw new Exception($varTmp . " operates on numeric operands only!");
                else
                {
                    $objTmp = Toolset_Tokenizer::toNumber($varTerm->val);
                    if ($varTmp == "RAND")
                    {
                        $rand_max=floor($objTmp);
                        if (count($arrArgs) == 2)
                        {
                            $varTerm = $arrArgs[1];
                            if ($varTerm->isVariable)
                            {
                                if (!isset($parrVars[$varTerm->val]))
                                    throw new Exception("Variable [" . $varTerm->val . "] not defined");
                                else
                                    $varTerm = $parrVars[$varTerm->val];
                            }

                            if (!$varTerm->isNumber)
                                throw new Exception($varTmp . " operates on numeric operands only!");

                            $objTmp = Toolset_Tokenizer::toNumber($varTerm->val);

                            $rand_min=floor($objTmp);
                        }
                    }
                }

                if ($varTmp == "ABS")
                    $pStack->Push(Toolset_Tokenizer::makeToken(abs($objTmp),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                else if ($varTmp == "CHR")// TODO check what happens when $objTmp is empty; what does chr() return?
                    $pStack->Push(Toolset_Tokenizer::makeToken(chr($objTmp),Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']));
                else if ($varTmp == "COS")
                    $pStack->Push(Toolset_Tokenizer::makeToken(cos($objTmp),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                else if ($varTmp == "FIX")
                    $pStack->Push(Toolset_Tokenizer::makeToken(floor($objTmp),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                else if ($varTmp == "HEX")
                    $pStack->Push(Toolset_Tokenizer::makeToken(dechex($objTmp),Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']));
                else if ($varTmp == "LOG")
                    $pStack->Push(Toolset_Tokenizer::makeToken(log($objTmp),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                else if ($varTmp == "RAND")
                    $pStack->Push(Toolset_Tokenizer::makeToken(mt_rand($rand_min,$rand_max),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                else if ($varTmp == "ROUND")
                    $pStack->Push(Toolset_Tokenizer::makeToken(round($objTmp),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                else if ($varTmp == "SIN")
                    $pStack->Push(Toolset_Tokenizer::makeToken(sin($objTmp),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                else if ($varTmp == "SQRT")
                    $pStack->Push(Toolset_Tokenizer::makeToken(sqrt($objTmp),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                else if ($varTmp == "TAN")
                    $pStack->Push(Toolset_Tokenizer::makeToken(tan($objTmp),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                break;
            case "STR" :
                if (count($arrArgs) < 1)
                    throw new Exception($varTmp . " requires at least one argument!");
                else if (count($arrArgs) > 2)
                    throw new Exception($varTmp . " requires at most two arguments!");
                $varTerm = $arrArgs[count($arrArgs)-1];
                if ($varTerm->isVariable)
                {
                    if (!isset($parrVars[$varTerm->val]))
                        throw new Exception("Variable [" . $varTerm->val . "] not defined");
                    else
                        $varTerm = $parrVars[$varTerm->val];
                }
                // if date, output formated date string
                if ($varTerm->isDate)
                {
                    $format='';
                    if (count($arrArgs)==2)
                    {
                        $varFormat = $arrArgs[0];
                        if ($varFormat->isVariable)
                        {
                            if (!isset($parrVars[$varFormat->val]))
                                throw new Exception("Variable [" . $varFormat->val . "] not defined");
                            else
                                $varFormat = $parrVars[$varFormat->val];
                        }

                        if (!$varFormat->isStringLiteral)
                            throw new Exception("format argument for " . $varTmp . " must be a string!");
                        $format=$varFormat->val;
                    }
                    $pStack->Push(Toolset_Tokenizer::makeToken(Toolset_DateParser::formatDate($varTerm->val, $format),Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']));
                }
                else  // just convert to string
                    $pStack->Push(Toolset_Tokenizer::makeToken((string)$varTerm->val.'',Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']));
                break;
            case "ASC" :
                if (count($arrArgs) > 1)
                    throw new Exception($varTmp . " requires only one argument!");
                else if (count($arrArgs) < 1)
                    throw new Exception($varTmp . " requires at least one argument!");
                $varTerm = $arrArgs[0];
                if ($varTerm->isVariable)
                {
                    if (!isset($parrVars[$varTerm->val]))
                        throw new Exception("Variable [" . $varTerm->val . "] not defined");
                    else
                        $varTerm = $parrVars[$varTerm]->val;
                }

                if( $varTerm->isNumber )
                {
                    $varTerm->val = (string) $varTerm->val;
                    $varTerm->isStringLiteral = true;
                }

                if (!$varTerm->isStringLiteral)
				{
                    throw new Exception($varTmp . " requires a string type operand!");
				}
                else
				{
                    if ( strlen( $varTerm->val ) > 0 )
					{
						$ascii_char = ord($varTerm->val{0});
					}
					else
					{
						$ascii_char = 0;
					}
					$pStack->Push(Toolset_Tokenizer::makeToken($ascii_char,Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
				}
                break;
            case "REGEX" :
                if (count($arrArgs) < 1)
                    throw new Exception($varTmp . " requires at least one argument!");
                else if (count($arrArgs) > 2)
                    throw new Exception($varTmp . " requires at most two arguments!");

                $varTerm = $arrArgs[count($arrArgs)-1];
                if ($varTerm->isVariable)
                {
                    if (!isset($parrVars[$varTerm->val]))
                        throw new Exception("Variable [" . $varTerm->val . "] not defined");
                    else
                        $varTerm = $parrVars[$varTerm->val];
                }

                if (!$varTerm->isStringLiteral)
                    throw new Exception($varTmp . " operates on string type operands!");

                $opts=Toolset_Tokenizer::$EMPTY_STRING;
                if (count($arrArgs)==2)
                {
                    $opts = $arrArgs[0];
                    if ($opts->isVariable)
                    {
                        if (!isset($parrVars[$opts->val]))
                            throw new Exception("Variable [" . $opts->val . "] not defined");
                        else
                            $opts = $parrVars[$opts->val];
                    }

                    if (!$opts->isStringLiteral)
                        throw new Exception($varTmp . " operates on string type operands!");
                }
                $pStack->Push(Toolset_Tokenizer::makeToken(Toolset_Functions::Regex($varTerm->val, $opts->val),Toolset_Tokenizer::$TOKEN_TYPE['REGEX']));
                break;
            case "LCASE" :
            case "UCASE" :
            case "NUM" :
                if (count($arrArgs) < 1)
                    throw new Exception($varTmp . " requires at least one argument!");
                else if (count($arrArgs) > 1)
                    throw new Exception($varTmp . " requires only one argument!");

                $varTerm = $arrArgs[0];
                if ($varTerm->isVariable)
                {
                    if (!isset($parrVars[$varTerm->val]))
                        throw new Exception("Variable [" . $varTerm->val . "] not defined");
                    else
                        $varTerm = $parrVars[$varTerm->val];
                }

                if( $varTerm->isNumber )
                {
                    $varTerm->val = (string) $varTerm->val;
                    $varTerm->isStringLiteral = true;
                }

                if (!$varTerm->isStringLiteral && $varTmp != "NUM")
                    throw new Exception($varTmp . " requires a string type operand!");
                else
                {
                    if ($varTmp == "LCASE")
                    {
                        $pStack->Push(Toolset_Tokenizer::makeToken(strtolower($varTerm->val),Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']));
                    }
                    else if ($varTmp == "UCASE")
                    {
                        $pStack->Push(Toolset_Tokenizer::makeToken(strtoupper($varTerm->val),Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']));
                    }
                    else if ($varTmp == "NUM")
                    {
                        $objTmp=Toolset_Tokenizer::toNumber($varTerm->val)+0.0;
                        if (is_nan($objTmp))
                            throw new Exception($varTmp . " cannot convert [" . $varTerm->val . "] to number!");
                        $pStack->Push(Toolset_Tokenizer::makeToken($objTmp,Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                    }
                }
                break;
            case "LEN" :
                if (count($arrArgs) < 1)
                    throw new Exception($varTmp . " requires at least one argument!");
                else if (count($arrArgs) > 1)
                    throw new Exception($varTmp . " requires only one argument!");

                $varTerm = $arrArgs[0];
                if ($varTerm->isVariable)
                {
                    if (!isset($parrVars[$varTerm->val]))
                        throw new Exception("Variable [" . $varTerm->val . "] not defined");
                    else
                        $varTerm = $parrVars[$varTerm->val];
                }

                if (!$varTerm->isArray && !$varTerm->isStringLiteral)
                    throw new Exception($varTmp . " requires a string or array type operand!");
                else
                {
                    if ($varTerm->isStringLiteral)
                        $pStack->Push(Toolset_Tokenizer::makeToken(strlen($varTerm->val),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                    else
                        $pStack->Push(Toolset_Tokenizer::makeToken(count($varTerm->val),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                }
                break;
            case "USER" :
                if (count($arrArgs) < 1)
                    throw new Exception($varTmp . " requires at least one argument!");
                else if (count($arrArgs) > 1)
                    throw new Exception($varTmp . " requires only one argument!");

                $varTerm = $arrArgs[0];
                if ($varTerm->isVariable)
                {
                    if (!isset($parrVars[$varTerm->val]))
                        throw new Exception("Variable [" . $varTerm->val . "] not defined");
                    else
                        $varTerm = $parrVars[$varTerm->val];
                }

                if (!$varTerm->isStringLiteral)
                    throw new Exception($varTmp . " requires a string type operand!");
                else
                {
                    $pStack->Push(Toolset_Tokenizer::makeToken(Toolset_Functions::User($varTerm->val),Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']));
                }
                break;
            case "COOKIE" :
                if (count($arrArgs) < 1)
                    throw new Exception($varTmp . " requires at least one argument!");
                else if (count($arrArgs) > 1)
                    throw new Exception($varTmp . " requires only one argument!");

                $varTerm = $arrArgs[0];
                if ($varTerm->isVariable)
                {
                    if (!isset($parrVars[$varTerm->val]))
                        throw new Exception("Variable [" . $varTerm->val . "] not defined");
                    else
                        $varTerm = $parrVars[$varTerm->val];
                }

                if (!$varTerm->isStringLiteral)
                    throw new Exception($varTmp . " requires a string type operand!");
                else
                {
                    $pStack->Push(Toolset_Tokenizer::makeToken(Toolset_Functions::Cookie($varTerm->val),Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']));
                }
                break;
            case "CONTAINS" :
                if (count($arrArgs) < 2)
                    throw new Exception($varTmp . " requires at least two arguments!");
                else if (count($arrArgs) > 2)
                    throw new Exception($varTmp . " requires only two arguments!");

                $varTerm = $arrArgs[1];
                if ($varTerm->isVariable)
                {
                    if (!isset($parrVars[$varTerm->val]))
                        throw new Exception("Variable [" . $varTerm->val . "] not defined");
                    else
                        $varTerm = $parrVars[$varTerm->val];
                }
                $varTerm2 = $arrArgs[0];
                if ($varTerm2->isVariable)
                {
                    if (!isset($parrVars[$varTerm2->val]))
                        throw new Exception("Variable [" . $varTerm2->val . "] not defined");
                    else
                        $varTerm2 = $parrVars[$varTerm2->val];
                }

                if ( !$varTerm->isArray )
                    throw new Exception($varTmp . " requires an array as first argument!");
                else
                {
                    $found=Toolset_Functions::Contains($varTerm->val, $varTerm2->val); //in_array($varTerm2->val,$varTerm->val);
                    $pStack->Push(Toolset_Tokenizer::makeToken($found,Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                }
                break;
            case "DATE" :
                if (count($arrArgs) < 2)
                    throw new Exception($varTmp . " requires at least two arguments!");
                else if (count($arrArgs) > 2)
                    throw new Exception($varTmp . " requires only two arguments!");

                $varTerm = $arrArgs[1];
                if ($varTerm->isVariable)
                {
                    if (!isset($parrVars[$varTerm->val]))
                        throw new Exception("Variable [" . $varTerm->val . "] not defined");
                    else
                        $varTerm = $parrVars[$varTerm->val];
                }
                $varFormat = $arrArgs[0];
                if ($varFormat->isVariable)
                {
                    if (!isset($parrVars[$varFormat->val]))
                        throw new Exception("Variable [" . $varFormat->val . "] not defined");
                    else
                        $varFormat = $parrVars[$varFormat->val];
                }

                $dateobj=array();
                if (
                    (!$varTerm->isStringLiteral) ||
                    (!$varFormat->isStringLiteral)
                )
                    throw new Exception($varTmp . " requires string type operands!");
                else if (!Toolset_Tokenizer::isDate($varTerm->val, $varFormat->val, $dateobj))
                    throw new Exception($varTmp . " can not convert [" . $varTerm->val . "] to a valid date with format [" . $varFormat->val . "]!");
                else
                {
                    if (isset($dateobj['date']))
                        $pStack->Push(Toolset_Tokenizer::makeToken($dateobj['date'],Toolset_Tokenizer::$TOKEN_TYPE['DATE']));
                    else
                        throw new Exception($varTmp . " unknown error");
                }
                break;
            case "empty" :
            case "EMPTY" :
                if (count($arrArgs) < 1)
                    throw new Exception($varTmp . " requires at least one argument!");
                else if (count($arrArgs) > 1)
                    throw new Exception($varTmp . " requires only one arguments!");

                $varFormat = $arrArgs[0];

                if( $varFormat->isEmpty )
                {
                    $pStack->Push(Toolset_Tokenizer::makeToken(true,Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                }
                elseif( $varFormat->isStringLiteral && $varFormat->val === '' )
                {
                    $pStack->Push(Toolset_Tokenizer::makeToken(true,Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                }
                elseif( $varFormat->isArray && count( $varFormat->val ) === 0 )
                {
                    $pStack->Push(Toolset_Tokenizer::makeToken(true,Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                }
                elseif(  $varFormat->isDate && !$varFormat->val )
                {
                    $pStack->Push(Toolset_Tokenizer::makeToken(true,Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                }
                else
                {
                    $pStack->Push(Toolset_Tokenizer::makeToken(false,Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                }

                break;
            case "LEFT" :
            case "RIGHT" :
                if (count($arrArgs) < 2)
                    throw new Exception($varTmp . " requires at least two arguments!");
                else if (count($arrArgs) > 2)
                    throw new Exception($varTmp . " requires only two arguments!");

                for ($intCntr = 0; $intCntr < count($arrArgs); $intCntr++)
                {
                    $varTerm = $arrArgs[$intCntr];
                    if ($varTerm->isVariable)
                    {
                        if (!isset($parrVars[$varTerm->val]))
                            throw new Exception("Variable [" . $varTerm->val . "] not defined");
                        else
                            $varTerm = $parrVars[$varTerm->val];
                    }

                    if( $varTerm->isNumber )
                    {
                        $arrArgs[1]->val = (string) $arrArgs[1]->val;
                        $varTerm->isStringLiteral = true;
                    }

                    if ($intCntr == 0 && !$varTerm->isNumber)
                        throw new Exception($varTmp . " operator requires numeric length!");
                    else if ($intCntr == 1 && !$varTerm->isStringLiteral)
                        throw new Exception($varTmp . " operator requires a string operand!");
                    $arrArgs[$intCntr] = $varTerm;
                }
                $varTerm = $arrArgs[1]->val;
                $objTmp = Toolset_Tokenizer::toNumber($arrArgs[0]->val);
                if ($varTmp == "LEFT")
                {
                    $pStack->Push(Toolset_Tokenizer::makeToken(substr($varTerm,0, $objTmp),Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']));
                }
                else
                {
                    $pStack->Push(Toolset_Tokenizer::makeToken(substr($varTerm,(strlen($varTerm) - $objTmp), $objTmp),Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']));
                }
                break;
            case "MID" :
            case "IIF" :
                if (count($arrArgs) < 3)
                    throw new Exception($varTmp . " requires at least three arguments!");
                else if (count($arrArgs) > 3)
                    throw new Exception($varTmp . " requires only three arguments!");

                for ($intCntr = 0; $intCntr < count($arrArgs); $intCntr++)
                {
                    $varTerm = $arrArgs[$intCntr];
                    if ($varTerm->isVariable)
                    {
                        if (!isset($parrVars[$varTerm->val]))
                            throw new Exception("Variable [" . $varTerm->val . "] not defined");
                        else
                            $varTerm = $parrVars[$varTerm->val];
                    }

                    if( $varTerm->isNumber )
                    {
                        $arrArgs[2]->val = (string) $arrArgs[2]->val;
                        $varTerm->isStringLiteral = true;
                    }

                    if ($varTmp == "MID" && $intCntr <= 1 && !$varTerm->isNumber)
                        throw new Exception($varTmp . " operator requires numeric lengths!");
                    else if ($varTmp == "MID" && $intCntr == 2 && !$varTerm->isStringLiteral)
                        throw new Exception($varTmp . " operator requires a string input!");
                  //  else if ($varTmp == "IIF" && $intCntr == 2 && !$varTerm->isBoolean && !$varTerm->isNumber)
                   //     throw new Exception($varTmp . " operator requires boolean condition!");
                    $arrArgs[$intCntr] = $varTerm;
                }
                if ($varTmp == "MID")
                {
                    $varTerm = $arrArgs[2]->val;
                    $objOp1 = Toolset_Tokenizer::toNumber($arrArgs[1]->val);
                    $objOp2 = Toolset_Tokenizer::toNumber($arrArgs[0]->val);
                    $pStack->Push(Toolset_Tokenizer::makeToken(substr($varTerm,$objOp1, $objOp2-$objOp1),Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']));
                }
                else
                {
                    $varTerm = Toolset_Tokenizer::toBoolean($arrArgs[2]->val);
                    if ($varTerm)
                        $objOp1 = $arrArgs[1];
                    else
                        $objOp1 = $arrArgs[0];
                    $pStack->Push($objOp1);
                }
                break;
            case "AVG" :
            case "MAX" :
            case "MIN" :
                if (count($arrArgs) < 1)
                    throw new Exception($varTmp . " requires at least one operand!");

                $_arr=array();
                $intCntr = count($arrArgs);
                while (--$intCntr>=0)
                {
                    $varTerm = $arrArgs[$intCntr];
                    if ($varTerm->isVariable)
                    {
                        if (!isset($parrVars[$varTerm->val]))
                            throw new Exception("Variable [" . $varTerm->val . "] not defined");
                        else
                            $varTerm = $parrVars[$varTerm->val];
                    }
                    if (!$varTerm->isNumber && !$varTerm->isArray)
                        throw new Exception($varTmp . " requires numeric or array operands only!");

                    if (!$varTerm->isArray)
                        $_arr=array_merge($_arr,Toolset_Tokenizer::toArray(Toolset_Tokenizer::toNumber($varTerm->val)));
                    else
                        $_arr=array_merge($_arr,$varTerm->val);
                }
                $intCntr = -1;
                $objTmp = 0;
                while (++$intCntr < count($_arr))
                {
                    $varTerm = $_arr[$intCntr];
                    if ($varTmp == "AVG")
                        $objTmp +=  $varTerm;
                    else if ($varTmp == "MAX")
                    {
                        if ($intCntr == 0)
                            $objTmp = $varTerm;
                        else if ($objTmp < $varTerm)
                            $objTmp = $varTerm;
                    }
                    else if ($varTmp == "MIN")
                    {
                        if ($intCntr == 0)
                            $objTmp = $varTerm;
                        else if ($objTmp > $varTerm)
                            $objTmp = $varTerm;
                    }
                }
                if ($varTmp == "AVG" && !empty($_arr))
                    $pStack->Push(Toolset_Tokenizer::makeToken($objTmp/count($_arr),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                else if ($varTmp == "AVG")
                    $pStack->Push(Toolset_Tokenizer::makeToken(0,Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                else
                    $pStack->Push(Toolset_Tokenizer::makeToken($objTmp,Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                unset($_arr);
                break;
        }
    }

    /*------------------------------------------------------------------------------
     * NAME       : InFixToPostFix
     * PURPOSE    : Convert an Infix expression into a postfix (RPN) equivalent
     * PARAMETERS : Infix expression element array
     * RETURNS    : array containing postfix expression element tokens
     *----------------------------------------------------------------------------*/
    private function _InFixToPostFix($arrToks)
    {
        $blnStart = false;
        $intIndex = 0;
        $arrPFix  = array();
        $myStack  = new Toolset_Stack();

        // Infix to postfix converter
        for ($intCntr = 0; $intCntr < count($arrToks); $intCntr++)
        {
            /*echo '<br />Tok: <br />';
            print_r($arrToks);
            echo '<br />PF: <br />';
            print_r($arrPFix);
            echo '<br />';*/

            $strTok = $arrToks[$intCntr];
            switch ($strTok->type)
            {
                case Toolset_Tokenizer::$TOKEN_TYPE['LEFT_PAREN'] :
                    if ($myStack->Size() > 0 && $myStack->Get(0)->isFunction)
                    {
                        $arrPFix[$intIndex] = Toolset_Tokenizer::$ARG_TERMINAL;
                        $intIndex++;
                    }
                    $myStack->Push($strTok);
                    break;
                case Toolset_Tokenizer::$TOKEN_TYPE['RIGHT_PAREN'] :
                    $blnStart = true;
                    while (!$myStack->IsEmpty())
                    {
                        $strTok = $myStack->Pop();
                        if (!$strTok->isLeftParen)
                        {
                            $arrPFix[$intIndex] = $strTok;
                            $intIndex++;
                        }
                        else
                        {
                            $blnStart = false;
                            break;
                        }
                    }
                    if ($myStack->IsEmpty() && $blnStart)
                        throw new Exception("Unbalanced parenthesis!");
                    break;
                case Toolset_Tokenizer::$TOKEN_TYPE['COMMA'] :
                    while (!$myStack->IsEmpty())
                    {
                        $strTok = $myStack->Get(0);
                        if ($strTok->isLeftParen) break;
                        $arrPFix[$intIndex] = $myStack->Pop();
                        $intIndex++;
                    }
                    break;
                //case Tokenizer.TOKEN_TYPE.UNARY_NEGATIVE :
                //case Tokenizer.TOKEN_TYPE.UNARY_NEGATION :
                case Toolset_Tokenizer::$TOKEN_TYPE['ARITHMETIC_OP'] :
                case Toolset_Tokenizer::$TOKEN_TYPE['LOGICAL_OP'] :
                case Toolset_Tokenizer::$TOKEN_TYPE['COMPARISON_OP'] :
                    switch ($strTok->val)
                    {
                        /*case "-" :
                        case "+" :
                        case "NOT" :
                        case "!" :
                        case "^" :
                        case "*" :
                        case "/" :
                        case "%" :
                        case "AND" :
                        case "&" :
                        case "OR" :
                        case "|" :
                        case ">" :
                        case "<" :
                        case "=" :
                        case ">=" :
                        case "<=" :
                        case "<>" :*/
                        default:
                            if ($strTok->val=='-')
                            {
                                // check for unary negative operator.
                                $strPrev = null;
                                if ($intCntr > 0)
                                    $strPrev = $arrToks[$intCntr - 1];
                                //$strNext = $arrToks[$intCntr + 1];
                                if ($strPrev === null || $strPrev->isArithmeticOp || $strPrev->isLeftParen || $strPrev->isComma)
                                {
                                    $strTok = Toolset_Tokenizer::$UNARY_NEGATIVE;
                                }
                            }
                            if ($strTok->val=='+')
                            {
                                // check for unary + addition operator, we need to ignore this.
                                $strPrev = null;
                                if ($intCntr > 0)
                                    $strPrev = $arrToks[$intCntr - 1];
                                //$strNext = $arrToks[$intCntr + 1];
                                if ($strPrev === null || $strPrev->isArithmeticOp || $strPrev->isLeftParen || $strPrev->isComma)
                                {
                                    break;
                                }
                            }
                            $strTop = Toolset_Tokenizer::$EMPTY_TOKEN;
                            if (!$myStack->IsEmpty()) $strTop = $myStack->Get(0);
                            if ($myStack->IsEmpty() || (!$myStack->IsEmpty() && $strTop->isLeftParen))
                            {
                                $myStack->Push($strTok);
                            }
                            else if ($strTok->precedence >= $strTop->precedence)
                            {
                                $myStack->Push($strTok);
                            }
                            else
                            {
                                // Pop operators with precedence >= operator strTok
                                while (!$myStack->IsEmpty())
                                {
                                    $strTop = $myStack->Get(0);
                                    if ($strTop->isLeftParen || $strTop->precedence < $strTok->precedence)
                                    {
                                        break;
                                    }
                                    else
                                    {
                                        $arrPFix[$intIndex] = $myStack->Pop();
                                        $intIndex++;
                                    }
                                }
                                $myStack->Push($strTok);
                            }
                            break;
                    }
                    break;
                default :
                    if ($strTok->type!=Toolset_Tokenizer::$TOKEN_TYPE['FUNCTION'])
                    {
                        $arrPFix[$intIndex] = $strTok;
                        $intIndex++;
                    }
                    else
                    {
                        $strTop = Toolset_Tokenizer::$EMPTY_TOKEN;
                        if (!$myStack->IsEmpty()) $strTop = $myStack->Get(0);
                        if ($myStack->IsEmpty() || (!$myStack->IsEmpty() && $strTop->isLeftParen))
                        {
                            $myStack->Push($strTok);
                        }
                        else if ($strTok->precedence >= $strTop->precedence)
                        {
                            $myStack->Push($strTok);
                        }
                        else
                        {
                            // Pop operators with precedence >= operator in strTok
                            while (!$myStack->IsEmpty())
                            {
                                $strTop = $myStack->Get(0);
                                if ($strTop->val == "(" || $strTop->precedence < $strTok->precedence)
                                {
                                    break;
                                }
                                else
                                {
                                    $arrPFix[$intIndex] = $myStack->Pop();
                                    $intIndex++;
                                }
                            }
                            $myStack->Push($strTok);
                        }
                    }
                break;
            }
        }

        // Pop remaining operators from stack.
        while (!$myStack->IsEmpty())
        {
            $arrPFix[$intIndex] = $myStack->Pop();
            $intIndex++;
        }
       return $arrPFix;
    }

    private function _AddNewVariable($varObj, &$varArr=null)
    {
        //cred_log($varObj);
        if (!isset($varArr))
            $varArr = &$this->arrVars;

        if (!isset($varArr))
            $varArr = array();

        $varName=$varObj['name'];
        $varToken=null;
        if (isset($varObj['withType']))
        {
            //cred_log($varObj['withType']);
            switch($varObj['withType'])
            {
                case 'boolean':
                    $varToken=Toolset_Tokenizer::makeToken($varObj['val'],Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']);
                    break;
                case 'number':
                    $varToken=Toolset_Tokenizer::makeToken($varObj['val'],Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']);
                    break;
                case 'array':
                    $varToken=Toolset_Tokenizer::makeToken($varObj['val'],Toolset_Tokenizer::$TOKEN_TYPE['ARRAY']);
                    break;
                case 'date':
                    if (isset($varObj['format']))
                        $format=$varObj['format'];
                    else
                        $format=$this->dtFormat;
                    $varToken=Toolset_Tokenizer::makeToken(Toolset_DateParser::parseDate($varObj['val'], $format),Toolset_Tokenizer::$TOKEN_TYPE['DATE']);
                    break;
                case 'string':
                default:
            //cred_log('STRING');
            //cred_log(Toolset_Tokenizer::$TOKEN_TYPE);
                    $varToken=Toolset_Tokenizer::makeToken($varObj['val'],Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']/*, true*/);
                    break;
            }
        }
        else
            $varToken=Toolset_Tokenizer::makeToken($varObj['val'],Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']);

        //cred_log($varToken);
        $varArr[$varName] = $varToken;
    }

    private function _dumpPostFix($pf)
    {
        $out='';
        for ($i=0; $i<count($pf); $i++)
            $out.=$pf[$i]->val.',';
        return $out;
    }

    private function _clonePostFix($pf)
    {
        $newpf=array();
        for ($i=0; $i<count($pf); $i++)
            $newpf[$i]=Toolset_Tokenizer::cloneToken($pf[$i]);
        return $newpf;
    }

    private function _ParseExpression()
    {
        $arrTokens = Toolset_Tokenizer::Tokanize($this->strInFix);
        if (!isset($arrTokens))
            throw new Exception("Unable to tokanize the expression!");
        if (count($arrTokens) <= 0)
            throw new Exception("Unable to tokanize the expression!");

        //print_r($arrTokens);

        $myarrPostFix = $this->_InFixToPostFix($arrTokens);
        if (!isset($myarrPostFix))
            throw new Exception("Unable to convert the expression to postfix form!");
        if (count($myarrPostFix) <= 0)
            throw new Exception("Unable to convert the expression to postfix form!");
        //print_r($myarrPostFix);
        return $myarrPostFix;
    }

    private function _getVariable($strVarName, &$varArr=null)
    {
        if (!isset($varArr))
            $varArr=$this->arrVars;

        if (!isset($varArr))
            throw new Exception("Variable values are not supplied!");

        if (!isset($varArr[$strVarName]))
            throw new Exception("Variable [" . $strVarName . "] not defined");

        //cred_log($varArr);
        return $varArr[$strVarName];
    }

    // postfix function evaluator
    private function _EvaluateExpression(&$myarrPostFix, &$myvarArr)
    {
        if (!isset($myarrPostFix))
            $myarrPostFix=$this->_ParseExpression();
        if (count($myarrPostFix) == 0)
            throw new Exception("Unable to parse the expression!");
        if (!isset($myarrPostFix) || count($myarrPostFix)==0)
        {
            throw new Exception("Invalid postfix expression!");
        }

        $intIndex = 0;
        $myStack  =  new Toolset_Stack();

        //echo $this->_dumpPostFix($myarrPostFix);

        while ($intIndex < count($myarrPostFix))
        {
            //echo $myStack->toString();

            $strTok = $myarrPostFix[$intIndex];
            switch ($strTok->type)
            {
                case Toolset_Tokenizer::$TOKEN_TYPE['ARG_TERMINAL'] :
                    $myStack->Push($strTok);
                    break;
                case Toolset_Tokenizer::$TOKEN_TYPE['UNARY_NEGATIVE'] :
                    if ($myStack->IsEmpty())
                        throw new Exception("No operand to negate!");

                    $objOp1 = null;
                    $objOp2 = null;
                    $objOp1 = $myStack->Pop();
                    if ($objOp1->isVariable)
                        $objOp1 = $this->_getVariable($objOp1->val, $myvarArr);

                    $dblNo = Toolset_Tokenizer::toNumber($objOp1->val);
                    if (is_nan($dblNo))
                        throw new Exception("Not a numeric value!");
                    else
                    {
                        $dblNo = (0 - $dblNo);
                        $myStack->Push(Toolset_Tokenizer::makeToken($dblNo,Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                    }
                    break;
                case Toolset_Tokenizer::$TOKEN_TYPE['UNARY_NEGATION'] :
                    if ($myStack->IsEmpty())
                        throw new Exception("No operand on stack!");

                    $objOp1 = null;
                    $objOp2 = null;
                    $objOp1 = $myStack->Pop();
                    if ($objOp1->isVariable)
                        $objOp1 = $this->_getVariable($objOp1->val, $myvarArr);

                    $objOp1 = Toolset_Tokenizer::toBoolean($objOp1->val);
                    if ($objOp1 === null)
                        throw new Exception($strTok->val . " applied not on a boolean value!");
                    else
                        $myStack->Push(Toolset_Tokenizer::makeToken(!($objOp1),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                    break;
                case Toolset_Tokenizer::$TOKEN_TYPE['ARITHMETIC_OP'] :
                    switch($strTok->val)
                    {
                        case "*" :
                        case "/" :
                        case "%" :
                        case "^" :
                            if ($myStack->IsEmpty() || $myStack->Size() < 2)
                                throw new Exception("Stack is empty, can not perform [" . $strTok->val . "]");
                            $objOp1 = null;
                            $objOp2 = null;
                            $objTmp = null;
                            $objOp2 = $myStack->Pop();
                            $objOp1 = $myStack->Pop();
                            if ($objOp1->isVariable)
                                $objOp1 = $this->_getVariable($objOp1->val, $myvarArr);
                            if ($objOp2->isVariable)
                                $objOp2 = $this->_getVariable($objOp2->val, $myvarArr);

                            if (!$objOp1->isNumber || !$objOp2->isNumber)
                                throw new Exception("Either one of the operand is not a number can not perform [" . $strTok->val . "]");

                            $dblVal1 = Toolset_Tokenizer::toNumber($objOp1->val);
                            $dblVal2 = Toolset_Tokenizer::toNumber($objOp2->val);
                            if (is_nan($dblVal1) || is_nan($dblVal2))
                                throw new Exception("Either one of the operand is not a number can not perform [" . $strTok->val . "]");

                            if ($strTok->val == "^")
                                $myStack->Push(Toolset_Tokenizer::makeToken(pow($dblVal1, $dblVal2),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                            else if ($strTok->val == "*")
                                $myStack->Push(Toolset_Tokenizer::makeToken(($dblVal1 * $dblVal2),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                            else if ($strTok->val == "/")
                                $myStack->Push(Toolset_Tokenizer::makeToken(($dblVal1 / $dblVal2),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                            else
                                $myStack->Push(Toolset_Tokenizer::makeToken(($dblVal1 % $dblVal2),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                            break;
                        case "+" :
                        case "-" :
                            if ($myStack->IsEmpty() || $myStack->Size() < 2)
                                throw new Exception("Stack is empty, can not perform [" . $strTok->val . "]");

                            $objOp1 = null;
                            $objOp2 = null;
                            $objTmp1 = null;
                            $objTmp2 = null;
                            $strOp = (($strTok->val == "+") ? "Addition" : "Substraction");
                            $objOp2 = $myStack->Pop();
                            $objOp1 = $myStack->Pop();
                            if ($objOp1->isVariable)
                                $objOp1 = $this->_getVariable($objOp1->val, $myvarArr);
                            if ($objOp2->isVariable)
                                $objOp2 = $this->_getVariable($objOp2->val, $myvarArr);

                            if ($objOp1->isNumber && $objOp2->isNumber)
                            {
                                // Number addition
                                $dblVal1 = Toolset_Tokenizer::toNumber($objOp1->val);
                                $dblVal2 = Toolset_Tokenizer::toNumber($objOp2->val);
                                if ($strTok->val == "+")
                                    $myStack->Push(Toolset_Tokenizer::makeToken(($dblVal1 + $dblVal2),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                                else
                                    $myStack->Push(Toolset_Tokenizer::makeToken(($dblVal1 - $dblVal2),Toolset_Tokenizer::$TOKEN_TYPE['NUMBER']));
                            }
                            else if ($objOp1->isStringLiteral && $objOp2->isStringLiteral)
                            {
                                if ($strTok->val == "+")
                                    $myStack->Push(Toolset_Tokenizer::makeToken(($objOp1->val . $objOp2->val),Toolset_Tokenizer::$TOKEN_TYPE['STRING_LITERAL']));
                                else
                                    throw new Exception($strOp . " not supported for strings!");
                            }
                            else
                                throw new Exception($strOp . " not supported for other types than numbers and strings!");
                            break;
                    }
                    break;
                case Toolset_Tokenizer::$TOKEN_TYPE['COMPARISON_OP'] :
                    switch($strTok->val)
                    {
                        case "=" :
                        case "<" :
                        case ">" :
                        case "<>" :
                        case "<=" :
                        case ">=" :
                        case "eq" :
                        case "lt" :
                        case "gt" :
                        case "ne" :
                        case "lte" :
                        case "gte" :

                            if ($myStack->IsEmpty() || $myStack->Size() < 2)
                                throw new Exception("Stack is empty, can not perform [" . $strTok->val . "]");
                            $objOp1  = null;
                            $objOp2  = null;
                            $objTmp1 = null;
                            $objTmp2 = null;
                            $objOp2  = $myStack->Pop();
                            $objOp1  = $myStack->Pop();
                            //cred_log(array($objOp1, $objOp2));
                            if ($objOp1->isVariable)
                                $objOp1 = $this->_getVariable($objOp1->val, $myvarArr);
                            if ($objOp2->isVariable)
                                $objOp2 = $this->_getVariable($objOp2->val, $myvarArr);

                            //cred_log(array($objOp1, $objOp2));
                            if ($objOp1->isStringLiteral && $objOp2->isNumber)
                            {
                                $dblVal1 = (string)$objOp1->val;
                                $dblVal2 = (string)$objOp2->val;
                            }
                            else if ($objOp1->isNumber && $objOp2->isStringLiteral)
                            {
                                $dblVal1 = (string)$objOp1->val;
                                $dblVal2 = (string)$objOp2->val;
                            }
                            else if ($objOp1->isNumber && $objOp2->isNumber)
                            {
                                $dblVal1 = Toolset_Tokenizer::toNumber($objOp1->val);
                                $dblVal2 = Toolset_Tokenizer::toNumber($objOp2->val);
                            }
                            else if ($objOp1->isNumber && $objOp2->isBoolean)
                            {
                                $dblVal1 = Toolset_Tokenizer::toNumber($objOp1->val);
                                $dblVal2 = Toolset_Tokenizer::toNumber($objOp2->val);
                            }
                            else if ($objOp2->isNumber && $objOp1->isBoolean)
                            {
                                $dblVal1 = Toolset_Tokenizer::toNumber($objOp1->val);
                                $dblVal2 = Toolset_Tokenizer::toNumber($objOp2->val);
                            }
                            else if ($objOp1->isDate && $objOp2->isDate)
                            {
                                $dblVal1 = $objOp1->val->getNormalizedTimestamp();
                                $dblVal2 = $objOp2->val->getNormalizedTimestamp();
                            }
                            else if ($objOp1->isStringLiteral && $objOp2->isStringLiteral)
                            {
                                $dblVal1=(string)$objOp1->val;
                                $dblVal2=(string)$objOp2->val;
                            }
                            else if ($objOp1->isBoolean && $objOp2->isBoolean)
                            {
                                if ($strTok->val == "=" || $strTok->val == "<>" || $strTok->val == "eq" || $strTok->val == "ne")
                                {
                                    $dblVal1 = Toolset_Tokenizer::toBoolean($objOp1->val);
                                    $dblVal2 = Toolset_Tokenizer::toBoolean($objOp2->val);
                                }
                                else
                                    throw new Exception($strTok->val + " not supported for boolean values!");
                            }
                            else if (
                                ($strTok->val=='=' || $strTok->val=='<>' || $strTok->val=='eq' || $strTok->val=='ne') &&
                                ($objOp1->isStringLiteral && $objOp2->isRegex)
                            )
                            {
                                if ($strTok->val=='=' || $strTok->val=='eq')
                                    $myStack->Push(Toolset_Tokenizer::makeToken(($objOp2->val->test((string)$objOp1->val)),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                                else
                                    $myStack->Push(Toolset_Tokenizer::makeToken(!($objOp2->val->test((string)$objOp1->val)),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                                break;
                            }
                            else if (
                                ($strTok->val=='=' || $strTok->val=='<>' || $strTok->val=='eq' || $strTok->val=='ne') &&
                                ($objOp2->isStringLiteral && $objOp1->isRegex)
                            )
                            {
                                if ($strTok->val=='=' || $strTok->val=='eq')
                                    $myStack->Push(Toolset_Tokenizer::makeToken(($objOp1->val->test((string)$objOp2->val)),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                                else
                                    $myStack->Push(Toolset_Tokenizer::makeToken(!($objOp1->val->test((string)$objOp2->val)),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                                break;
                            }
                            else if (
                                ($strTok->val=='=' || $strTok->val=='<>' || $strTok->val=='eq' || $strTok->val=='ne') &&
                                ($objOp1->isArray && ($objOp2->isStringLiteral || $objOp2->isNumber))
                            )
                            {
                                if ($strTok->val=='=' || $strTok->val=='eq')
                                    $myStack->Push(Toolset_Tokenizer::makeToken(Toolset_Functions::Contains($objOp1->val,$objOp2->val),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                                else
                                    $myStack->Push(Toolset_Tokenizer::makeToken(!Toolset_Functions::Contains($objOp1->val,$objOp2->val),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                                break;
                            }
                            else if (
                                ($strTok->val=='=' || $strTok->val=='<>' || $strTok->val=='eq' || $strTok->val=='ne') &&
                                ($objOp2->isArray && ($objOp1->isStringLiteral || $objOp1->isNumber))
                            )
                            {
                                if ($strTok->val=='=' || $strTok->val=='eq')
                                    $myStack->Push(Toolset_Tokenizer::makeToken(Toolset_Functions::Contains($objOp2->val,$objOp1->val),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                                else
                                    $myStack->Push(Toolset_Tokenizer::makeToken(!Toolset_Functions::Contains($objOp2->val,$objOp1->val),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                                break;
                            }
                            else
                                throw new Exception("For " . $strTok->val . " operator LHS & RHS should be of same data type!");

                            if ($strTok->val == "=" || $strTok->val == "eq")
                                $myStack->Push(Toolset_Tokenizer::makeToken(($dblVal1 == $dblVal2),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                            else if ($strTok->val == "<>" || $strTok->val == "ne")
                                $myStack->Push(Toolset_Tokenizer::makeToken(($dblVal1 != $dblVal2),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                            else if ($strTok->val == ">" || $strTok->val == "gt")
                                $myStack->Push(Toolset_Tokenizer::makeToken(($dblVal1 > $dblVal2),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                            else if ($strTok->val == "<" || $strTok->val == "lt")
                                $myStack->Push(Toolset_Tokenizer::makeToken(($dblVal1 < $dblVal2),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                            else if ($strTok->val == "<=" || $strTok->val == "lte")
                                $myStack->Push(Toolset_Tokenizer::makeToken(($dblVal1 <= $dblVal2),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                            else if ($strTok->val == ">=" || $strTok->val == "gte")
                                $myStack->Push(Toolset_Tokenizer::makeToken(($dblVal1 >= $dblVal2),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                            break;
                    }
                    break;
                case Toolset_Tokenizer::$TOKEN_TYPE['LOGICAL_OP'] :
                    switch($strTok->val)
                    {
                        case 'NOT' :
                        case '!' :
                            if ($myStack->IsEmpty())
                                throw new Exception("No operand on stack!");

                            $objOp1 = null;
                            $objOp2 = null;
                            $objOp1 = $myStack->Pop();
                            if ($objOp1->isVariable)
                                $objOp1 = $this->_getVariable($objOp1->val, $myvarArr);

                            $objOp1 = Toolset_Tokenizer::toBoolean($objOp1->val);
                            if ($objOp1 === null)
                                throw new Exception($strTok->val . " applied not on a boolean value!");
                            else
                                $myStack->Push(Toolset_Tokenizer::makeToken(!($objOp1),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                            break;
                        case "AND" :
                        case "&" :
                        case "OR" :
                        case "|" :
                            if ($myStack->IsEmpty() || $myStack->Size() < 2)
                                throw new Exception("Stack is empty, can not perform [" . $strTok->val . "]");
                            $objOp1  = null;
                            $objOp2  = null;
                            $objTmp1 = null;
                            $objTmp2 = null;
                            $objOp2  = $myStack->Pop();
                            $objOp1  = $myStack->Pop();
                            if ($objOp1->isVariable)
                                $objOp1 = $this->_getVariable($objOp1->val, $myvarArr);
                            if ($objOp2->isVariable)
                                $objOp2 = $this->_getVariable($objOp2->val, $myvarArr);

                            if (
                                ($objOp1->isBoolean && $objOp2->isBoolean) ||
                                ($objOp1->isNumber && $objOp2->isNumber) ||
                                ($objOp1->isNumber && $objOp2->isBoolean) ||
                                ($objOp1->isBoolean && $objOp2->isNumber)
                                )
                            {
                                $objTmp1 = Toolset_Tokenizer::toBoolean($objOp1->val);
                                $objTmp2 = Toolset_Tokenizer::toBoolean($objOp2->val);
                                if ($strTok->val == "AND"  || $strTok->val == "&")
                                    $myStack->Push(Toolset_Tokenizer::makeToken(($objTmp1 && $objTmp2),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                                else if ($strTok->val == "OR" || $strTok->val == "|")
                                    $myStack->Push(Toolset_Tokenizer::makeToken(($objTmp1 || $objTmp2),Toolset_Tokenizer::$TOKEN_TYPE['BOOLEAN']));
                            }
                            else
                                throw new Exception("Logical operator requires LHS & RHS of boolean type!");
                            break;
                    }
                    break;
                case Toolset_Tokenizer::$TOKEN_TYPE['FUNCTION'] :
                    $this->_HandleFunctions($strTok, $myStack, $this->dtFormat, $myvarArr);
                    break;
                default :
                    $myStack->Push($strTok);
                    break;
            }
            $intIndex++;

            //echo (string)$myStack->toString();
        }
        if ($myStack->IsEmpty() || $myStack->Size() > 1 || $myStack->Get(0)->isVariable)
            throw new Exception("Unable to evaluate expression!");
        else
            return $myStack->Pop()->val;
    }

    // delegate here
    public static function setParams($params)
    {
        Toolset_Functions::setParams((array)$params);
    }

    public function Toolset_Parser($exp=null)
    {
        if (isset($exp) && $exp!==null)
        {
            $this->strInFix = $exp;
            $this->arrTokens=$this->arrPostFix=null;
        }
        // init tokenizer here, else tokens become undefined on addVar
        Toolset_Tokenizer::init();
    }

    public function dateLocales($a=null,$b=null,$c=null)
    {
        Toolset_DateParser::setDateLocaleStrings($a,$b,$c);
        return $this;
    }

    public function dateFormat($df)
    {
        $this->dtFormat = $df;
        return $this;
    }

    public function expression($exp)
    {
        $this->strInFix = $exp;
        $this->arrTokens=$this->arrPostFix=null;
        return $this;
    }

    public function addVar($_var)
    {
        $this->_AddNewVariable($_var, $this->arrVars);
        return $this;
    }

    public function parse()
    {
        $this->arrPostFix=$this->_ParseExpression();
        return $this->_dumpPostFix($this->arrPostFix);
    }

    public function evaluate()
    {
        return $this->_EvaluateExpression($this->arrPostFix, $this->arrVars);
    }

    public function reset()
    {
        $this->arrVars = array();
        $this->strInFix=$this->arrTokens=$this->arrPostFix=null;
        return $this;
    }

    public function dump()
    {
        return $this->_dumpPostFix($this->arrPostFix);
    }
    //this.preCompile = function(_exp){if (typeof(_exp)=='undefined') _exp=strInFix; return _getPrecompiledExpression(_exp);};
}
