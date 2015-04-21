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
*   inspired by 
*       JS Expression Evaluator by Prasad P. Khandekar
*
**/

// js closure paradise (or maybe hell?)
(function(window){
window.ToolsetParser=window.ToolsetParser || 

    (function(window){
        
        var keywords;
        
        var Functions=(function(){
            var _cookies=false;
            var _regexs={};
            var _params={
                user:{
                    ID:0,
                    role:'',
                    roles:[],
                    login:'',
                    display_name:''
                }
            };
            
            function _setParams(params)
            {
                for (var n in params)
                {
                    if (params.hasOwnProperty(n))
                        _params[n]=params[n];
                }
            }
            
            function _Cookie(name)
            {
                var i,c,C;
                
                if (!_cookies)
                {
                    c = document.cookie.split(/;\s*/);
                    _cookies = {};
                    
                    i=c.length;
                    while(--i>=0)
                    {
                       C = c[i].split('=');
                       _cookies[C[0]] = C[1];
                    }
                }
                return (_cookies[name])?_cookies[name]:'';
            }
            
            function _User(att)
            {
                att=att.toUpperCase();
                
                switch (att)
                {
                    case 'ID':
                        return _params['user']['ID']+'';
                    case 'NAME':
                        return _params['user']['display_name'];
                    case 'ROLE':
                        return _params['user']['role'];
                    case 'LOGIN':
                        return _params['user']['login'];
                    default:
                        return '';
                }
                return '';
            }
            
            function _Regex(rx, opts)
            {
                // cache regexes
                var _prefix='__REGEX_' /*,flags, pattern, inputRX*/;
                
                // replace flags not supported in JS
                opts=opts.replace(/[^gimy]/,'');
                
                // !opts && (opts='');
                if (!_regexs[_prefix+rx+opts])
                {
                    /*inputRX=rx;
                    flags = inputRX.replace(/.*\/([gimy]*)$/, '$1');
                    pattern = inputRX.replace(new RegExp('^/(.*?)/'+flags+'$'), '$1');*/
                    _regexs[_prefix+rx+opts]=new RegExp(rx /*pattern*/, /*flags+*/opts);
                    //console.log(_regexs[_prefix+rx+opts]);
                }
                return _regexs[_prefix+rx+opts];
            }
            
            function _Contains(a,v)
            {
                var found=false;
                var ii=a.length;
                while(--ii>=0)
                {
                    if (a[ii]==v)
                    {
                        found=true;
                        break;
                    }
                }
                return found;
            }
            
            return {
                setParams : _setParams,
                User : _User,
                Cookie : _Cookie,
                Regex : _Regex,
                Contains : _Contains
            };
        })();

        // private class
        var DateParser = (function(){
        
            // private members
            var 
            _ZONE_NAMES = {'AM' : 'AM','PM' : 'PM'},
            
            //_MONTH_NAMES = {'January':'January','February':'February','March':'March','April':'April','May':'May','June':'June','July':'July','August':'August','September':'September','October':'October','November':'November','December':'December'},
            
            //_DAY_NAMES = {'Sunday':'Sunday','Monday':'Monday','Tuesday':'Tuesday','Wednesday':'Wednesday','Thursday':'Thursday','Friday':'Friday','Saturday':'Saturday'},
            
            _MONTH_NAMES = ['January','February','March','April','May','June','July','August','September','October','November','December'],
            
            _DAY_NAMES = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
            
            _ENGLISH_MONTH_NAMES = new Array('January','February','March','April','May','June','July','August','September','October','November','December'),
            
            _ENGLISH_DAY_NAMES = new Array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'),
            
            _to_int = function(str) 
            {
                // return the integer representation of the string given as argument
                return parseInt(str , 10);
            },
            
            _escape_regexp = function(str) 
            {
                // return string with special characters escaped
                return str.replace(/([-.*+?^${}()|[\]\/\\])/g, '\\$1');
            },
            
            _str_pad = function(n, c) 
            {
                if ((n = n + '').length < c) 
                {
                    return new Array((++c) - n.length).join('0') + n;
                }
                return n;
            },
            
            _is_string = function(s)
            {
                if (typeof(s)=='string' ||
                    (typeof(s)=='object' && ((s instanceof String) || Object.prototype.toString.call(s) === '[object String]')))
                    return true;
                return false;
            };
            
            
            var _parseDate = function(date, supposed_format) 
            {
                // if already a date object
                if (typeof(date)!='undefined' && ((date instanceof Date) || Object.prototype.toString.call(date) === '[object Date]'))
                {
                    date.setHours(0,0,0,0); // normalize time part
                    return date;
                }
               
                if (
                    typeof(date)=='undefined' || 
                    date==null ||
                    !_is_string(date) || 
                    typeof(supposed_format)=='undefined' || 
                    supposed_format==null ||
                    !_is_string(supposed_format)
                    )
                return false;
                
                // treat argument as a string
                str_date = date + '';
                supposed_format = supposed_format+'';
                
                // if value is given
                if (str_date != '' && supposed_format != '') 
                {

                    var
                        // prepare the format by removing white space from it
                        // and also escape characters that could have special meaning in a regular expression
                        format = _escape_regexp(supposed_format.replace(/\s/g, '')),

                        // allowed characters in date's format
                        format_chars = ['d','D','j','l','N','S','w','F','m','M','n','Y','y'],

                        // "matches" will contain the characters defining the date's format
                        matches = new Array,

                        // "regexp" will contain the regular expression built for each of the characters used in the date's format
                        regexp = new Array;

                    // iterate through the allowed characters in date's format
                    for (var i = 0; i < format_chars.length; i++)
                    {
                        // if character is found in the date's format
                        if ((position = format.indexOf(format_chars[i])) > -1)

                            // save it, alongside the character's position
                            matches.push({character: format_chars[i], position: position});
                    }
                    
                    // sort characters defining the date's format based on their position, ascending
                    matches.sort(function(a, b){ return a.position - b.position });

                    // iterate through the characters defining the date's format
                    for (var index=0; index<matches.length; index++)
                    {
                        var match=matches[index];

                        // add to the array of regular expressions, based on the character
                        switch (match.character) 
                        {

                            case 'd': regexp.push('0[1-9]|[12][0-9]|3[01]'); break;
                            case 'D': regexp.push('[a-z]{3}'); break;
                            case 'j': regexp.push('[1-9]|[12][0-9]|3[01]'); break;
                            case 'l': regexp.push('[a-z]+'); break;
                            case 'N': regexp.push('[1-7]'); break;
                            case 'S': regexp.push('st|nd|rd|th'); break;
                            case 'w': regexp.push('[0-6]'); break;
                            case 'F': regexp.push('[a-z]+'); break;
                            case 'm': regexp.push('0[1-9]|1[012]+'); break;
                            case 'M': regexp.push('[a-z]{3}'); break;
                            case 'n': regexp.push('[1-9]|1[012]'); break;
                            case 'Y': regexp.push('[0-9]{4}'); break;
                            case 'y': regexp.push('[0-9]{2}'); break;

                        }
                    }

                    // if we have an array of regular expressions
                    if (regexp.length) 
                    {

                        // we will replace characters in the date's format in reversed order
                        matches.reverse();

                        // iterate through the characters in date's format
                        for (var index=0; index<matches.length; index++)
                        {
                            var match=matches[index];

                            // replace each character with the appropriate regular expression
                            format = format.replace(match.character, '(' + regexp[regexp.length - index - 1] + ')');
                        }

                        // the final regular expression
                        regexp = new RegExp('^' + format + '$', 'ig');

                        // if regular expression was matched
                        if ((segments = regexp.exec(str_date.replace(/\s/g, '')))) 
                        {

                            // check if date is a valid date (i.e. there's no February 31)
                            var original_day,
                                original_month,
                                original_year,
                                english_days   = _ENGLISH_DAY_NAMES, //['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
                                english_months = _ENGLISH_MONTH_NAMES,//['January','February','March','April','May','June','July','August','September','October','November','December'],
                                iterable,

                                // by default, we assume the date is valid
                                valid = true;

                            // reverse back the characters in the date's format
                            matches.reverse();

                            // iterate through the characters in the date's format
                            for (var index=0; index<matches.length; index++)
                            {
                                var match=matches[index];

                                // if the date is not valid, don't look further
                                if (!valid) break; //return true;

                                // based on the character
                                switch (match.character) 
                                {

                                    case 'm':
                                    case 'n':

                                        // extract the month from the value entered by the user
                                        original_month = _to_int(segments[index + 1]);

                                        break;

                                    case 'd':
                                    case 'j':

                                        // extract the day from the value entered by the user
                                        original_day = _to_int(segments[index + 1]);

                                        break;

                                    case 'D':
                                    case 'l':
                                    case 'F':
                                    case 'M':

                                        // if day is given as day name, we'll check against the names in the used language
                                        if (match.character == 'D' || match.character == 'l') iterable = _DAY_NAMES;

                                        // if month is given as month name, we'll check against the names in the used language
                                        else iterable = _MONTH_NAMES;

                                        // by default, we assume the day or month was not entered correctly
                                        valid = false;

                                        // iterate through the month/days in the used language
                                        for (var key=0; key<iterable.length; key++)
                                        {
                                            // if month/day was entered correctly, don't look further
                                            if (valid) break; //return true;
                                            
                                            var value=iterable[key];

                                            // if month/day was entered correctly
                                            if (segments[index + 1].toLowerCase() == value.substring(0, (match.character == 'D' || match.character == 'M' ? 3 : value.length)).toLowerCase()) 
                                            {

                                                // extract the day/month from the value entered by the user
                                                switch (match.character) 
                                                {

                                                    case 'D': segments[index + 1] = english_days[key].substring(0, 3); break;
                                                    case 'l': segments[index + 1] = english_days[key]; break;
                                                    case 'F': segments[index + 1] = english_months[key]; original_month = key + 1; break;
                                                    case 'M': segments[index + 1] = english_months[key].substring(0, 3); original_month = key + 1; break;

                                                }

                                                // day/month value is valid
                                                valid = true;

                                            }

                                        }

                                        break;

                                    case 'Y':

                                        // extract the year from the value entered by the user
                                        original_year = _to_int(segments[index + 1]);

                                        break;

                                    case 'y':

                                        // extract the year from the value entered by the user
                                        original_year = '19' + _to_int(segments[index + 1]);

                                        break;

                                }
                            }

                            // if everything is ok so far
                            if (valid) 
                            {
                                // generate a Date object using the values entered by the user
                                // (handle also the case when original_month and/or original_day are undefined - i.e date format is "Y-m" or "Y")
                                var date = new Date(original_year, (original_month || 1) - 1, original_day || 1);

                                // if, after that, the date is the same as the date entered by the user
                                if (date.getFullYear() == original_year && date.getDate() == (original_day || 1) && date.getMonth() == ((original_month || 1) - 1))
                                {
                                    // normalize time part, only date part checked
                                    date.setHours(0,0,0,0);
                                    // return the date as JavaScript date object
                                    return date;
                                }
                            }
                        }
                    }
                }
                // if script gets this far, return false as something must've went wrong
                return false;
            };
            
            var _formatDate = function(date, format) 
            {

                // if not a date object
                if (typeof(date)=='undefined' || date==null || !(date instanceof Date) || Object.prototype.toString.call(date) !== '[object Date]')
                {
                    return '';
                }
                
                date.setHours(0,0,0,0); // normalize time
                
                if (format=='')
                    return date.toString();
                    
                var result = '',

                    // extract parts of the date:
                    // day number, 1 - 31
                    j = date.getDate(),

                    // day of the week, 0 - 6, Sunday - Saturday
                    w = date.getDay(),

                    // the name of the day of the week Sunday - Saturday
                    l = _DAY_NAMES[w],

                    // the month number, 1 - 12
                    n = date.getMonth() + 1,

                    // the month name, January - December
                    f = _MONTH_NAMES[n - 1],

                    // the year (as a string)
                    y = date.getFullYear() + '';

                // iterate through the characters in the format
                for (var i = 0; i < format.length; i++) 
                {

                    // extract the current character
                    var chr = format.charAt(i);

                    // see what character it is
                    switch(chr) 
                    {
                        // year as two digits
                        case 'y': y = y.substr(2);

                        // year as four digits
                        case 'Y': result += y; break;

                        // month number, prefixed with 0
                        case 'm': n = _str_pad(n, 2);

                        // month number, not prefixed with 0
                        case 'n': result += n; break;

                        // month name, three letters
                        case 'M': f = f.substr(0, 3);

                        // full month name
                        case 'F': result += f; break;

                        // day number, prefixed with 0
                        case 'd': j = _str_pad(j, 2);

                        // day number not prefixed with 0
                        case 'j': result += j; break;

                        // day name, three letters
                        case 'D': l = l.substr(0, 3);

                        // full day name
                        case 'l': result += l; break;

                        // ISO-8601 numeric representation of the day of the week, 1 - 7
                        case 'N': w++;

                        // day of the week, 0 - 6
                        case 'w': result += w; break;

                        // English ordinal suffix for the day of the month, 2 characters
                        // (st, nd, rd or th (works well with j))
                        case 'S':

                            if (j % 10 == 1 && j != '11') result += 'st';

                            else if (j % 10 == 2 && j != '12') result += 'nd';

                            else if (j % 10 == 3 && j != '13') result += 'rd';

                            else result += 'th';

                            break;

                        // this is probably the separator
                        default: result += chr;

                    }

                }
                // return formated date
                return result;
            };
            
            // public (static) methods
            return {
            
            setDateLocaleStrings : function(dn, mn, zn)
            {
                if (typeof mn != 'undefined')
                {
                    _MONTH_NAMES = mn;
                }
                if (typeof dn != 'undefined')
                {
                    _DAY_NAMES = dn;
                }
                if (typeof zn != 'undefined')
                    _ZONE_NAMES = zn;
            },
            
            parseDate : _parseDate,
            formatDate : _formatDate,
            
            isDate : function (val, format, getDate) 
            {
                var date=_parseDate(val,format);
                if (date!==false)
                {
                    if (typeof(getDate)!='undefined' && typeof(getDate)=='object')
                    {
                        getDate.date=date;
                    }
                    return true;
                }
                return false;
            },
            
            currentDate : function()
            {
                var _now=new Date();
                _now.setHours(0,0,0,0); // normalize time part
                return _now;
            }
        };
        })();
        
        var Stack = (function(){
            // Converts stack contents into a comma separated string
            function _dumpStack()
            {
                var intCntr = 0;
                var strRet  =  "";
                if (this.intIndex == 0) return null;
                for (intCntr = 0; intCntr < this.intIndex; intCntr++)
                {
                    if (strRet.length == 0)
                        strRet += this.arrStack[intCntr].val;
                    else
                        strRet += "," + this.arrStack[intCntr].val;
                }
                return strRet;
            }

            // Returns size of stack
            function _getSize()
            {
                return this.intIndex;
            }

            // This method tells us if this Stack object is empty
            function _isStackEmpty()
            {
                if (this.intIndex == 0)
                    return true;
                else
                    return false;
            }

            // This method pushes a new element onto the top of the stack
            function _pushElement(newData)
            {
                // Assign our new element to the top
                //debugAssert ("Pushing " + newData);
                this.arrStack[this.intIndex++] = newData;
                //this.intIndex++;
            }

            // This method pops the top element off of the stack
            function _popElement()
            {
                var retVal;

                retVal = null;
                if (this.intIndex > 0)
                {
                   // Assign our new element to the top
                   //this.intIndex--;
                   retVal = this.arrStack[--this.intIndex];
                }
                return retVal;
            }

            // Gets an element at a particular offset from top of the stack
            function _getElement(intPos)
            {
                var retVal;

                //alert ("Size : " + this.intIndex + ", Index " + intPos);
                if (intPos >= 0 && intPos < this.intIndex)
                    retVal = this.arrStack[this.intIndex - intPos - 1];
                return retVal;
            }
             // Stack object constructor
            return  function()
            {
                this.arrStack = new Array();
                this.intIndex = 0;

                this.Size     = _getSize;
                this.IsEmpty  = _isStackEmpty;
                this.Push     = _pushElement;
                this.Pop      = _popElement;
                this.Get      = _getElement;
                this.toString = _dumpStack;
            };
        })();
        
        var Tokenizer = (function(DateParser){
 
            // private members
            var _tok_map_prefix = '__TOKEN_MAP_PREFIX__',
            _Alpha    = "abcdefghijklmnopqrstuvwxyz",
            _lstAlpha = _Alpha + _Alpha.toUpperCase(),
            _lstVariablePrefix = '_$' + _lstAlpha,
            _lstDigits   = "0123456789",
            _lstArithOps = ["^","*","/","%","+","-"],
            _lstLogicOps = ["NOT","!","OR","|","AND","&"],
            _lstCompaOps = ["<","<=",">",">=","<>","=","lt","lte","gt","gte","ne","eq"],
            _lstFuncOps  = ["AVG","ABS","ACOS","ARRAY","ASC","ASIN","ATAN","CHR","CONTAINS","COOKIE","COS","DATE","FIX","HEX","IIF","LCASE","LEN","LEFT","LOG","MAX","MID","MIN","NUM","RAND","REGEX","RIGHT","ROUND","SIN","SQRT","STR","TAN","TODAY","UCASE","USER", "EMPTY", "empty"],
            
            _UNARY_NEGATIVE = "-",
            _UNARY_NEGATION = "!",
            _ARG_TERMINAL = "?",
            
            _aritOpMap = {},
            _logiOpMap = {},
            _compOpMap = {},
            _funcMap = {},
            
            _TOKEN_TYPES = { __DEFAULT__:0, STRING_LITERAL:8, REGEX: 83, ARRAY:81, ARRAY_LITERAL:82, DATE:1, ARITHMETIC_OP:2, LOGICAL_OP:3, COMPARISON_OP:4, NUMBER:5, BOOLEAN:6, VARIABLE:7, FUNCTION:9, COMMA:10, LEFT_PAREN:11, LEFT_BRACKET:111, RIGHT_PAREN:12, RIGHT_BRACKET:122, ARG_TERMINAL:13, UNARY_NEGATIVE:14, UNARY_NEGATION:15, EMPTY_TOKEN:30, UNKNOWN:40 },
            
            _isDefined = function(s)
            {
                return ((typeof(s)=='undefined' || s==null)?false:true);
            },
            _isDigit = function(c)
            {
                if (!_isDefined(c))
                    return false;
                return ((c!='' && _lstDigits.indexOf(c) >= 0)?true:false);
            },
            _isAlpha = function(c)
            {
                if (!_isDefined(c))
                    return false;
                return ((c!='' && _lstAlpha.indexOf(c) >= 0)?true:false);
            },
            _isOperator = function(s)
            {
                if (!_isDefined(s))
                    return false;
                return ((_aritOpMap[_tok_map_prefix + s])?true:false);
            },
            _isLogicOperator = function(s)
            {
                if (!_isDefined(s))
                    return false;
                return ((_logiOpMap[_tok_map_prefix + s])?true:false);
            },
            _isCompOperator = function(s)
            {
                if (!_isDefined(s))
                    return false;
                return ((_compOpMap[_tok_map_prefix + s])?true:false);
            },
            _isFunction = function(s)
            {
                if (!_isDefined(s))
                    return false;
                return ((_funcMap[_tok_map_prefix + s])?true:false);
            },
            _isVariableName = function(s)
            {
                if (!_isDefined(s))
                    return false;
                c=(s=='')?'':s.charAt(0);
                return ((c!='' && _lstVariablePrefix.indexOf(c) >= 0)?true:false);
            },
            _isDateInstance = function(s)
            {
                if (!_isDefined(s))
                    return false;
                if (typeof(s)=='object' && ((s instanceof Date) || Object.prototype.toString.call(s) == '[object Date]'))
                    return true;
                return false;
            },
            _isArrayInstance = function(s)
            {
                if (!_isDefined(s))
                    return false;
                if (typeof(s)=='object' && ((s instanceof Array) || Object.prototype.toString.call(s) == '[object Array]'))
                    return true;
                return false;
            },
            _isRegExpInstance = function(s)
            {
                if (!_isDefined(s))
                    return false;
                if (typeof(s)=='object' && ((s instanceof RegExp) || Object.prototype.toString.call(s) == '[object RegExp]'))
                    return true;
                return false;
            },
            _isNumber = function(s)
            {
                var type_s=typeof(s);
                if (!_isDefined(s)) return false;
                if (_isDateInstance(s) || _isRegExpInstance(s) || _isArrayInstance(s)) return false;
                if ((type_s=='number' || (type_s=='object' && Object.prototype.toString.call(s) !== '[object Number]')) /*&& !isNaN(s)*/)
                    return true;
                var dblNo = Number.NaN;
                dblNo = new Number(s);
                return ((isNaN(dblNo))?false:true);
            },
            _isBoolean = function(s)
            {
                if (!_isDefined(s))
                    return false;
                
                var varType = typeof(s);
                var strTmp  = s;

                if (varType == "boolean") return true;
                if (varType == "number" || varType == "function" || varType == 'undefined') return false;
                if (_isNumber(s) || _isDateInstance(s) || _isRegExpInstance(s) || _isArrayInstance(s)) return false;
                if (varType == "object") strTmp = strTmp.toString();
                if (strTmp.toUpperCase && (strTmp.toUpperCase() == "TRUE" || strTmp.toUpperCase() == "FALSE"))  return true;
                return false;
            },
            _rtrim = function(s, ch)
            {
                var w_space;
                if (typeof ch == 'undefined')
                    w_space = String.fromCharCode(32);
                else
                    w_space = ch;
                var v_length = s.length;
                var strTemp = "";
                if(v_length < 0)
                {
                    return"";
                }
                var iTemp = v_length - 1;

                while(iTemp > -1)
                {
                    if(s.charAt(iTemp) == w_space)
                    {
                    }
                    else
                    {
                        strTemp = s.substring(0, iTemp + 1);
                        break;
                    }
                    iTemp = iTemp - 1;
                }
                return strTemp;
            },
            _ltrim = function (s, ch)
            {
                var w_space;
                if (typeof ch == 'undefined')
                    w_space = String.fromCharCode(32);
                else
                    w_space = ch;
                if(v_length < 1)
                {
                    return "";
                }
                var v_length = s.length;
                var strTemp = "";
                var iTemp = 0;

                while(iTemp < v_length)
                {
                    if(s.charAt(iTemp) == w_space)
                    {
                    }
                    else
                    {
                        strTemp = s.substring(iTemp, v_length);
                        break;
                    }
                    iTemp = iTemp + 1;
                }
                return strTemp;
            },
            _trim = function(s, ch)
            {
                if (s.length < 1) return "";

                s = _rtrim(_ltrim(s, ch), ch);
                if (s == "")
                    return "";
                else
                    return s;
            };
            
            // build maps for fast lookup
            var _buildMaps=function()
            {
                var i;
                
                for (i=0; i<_lstArithOps.length; i++)
                {
                    _aritOpMap[_tok_map_prefix + _lstArithOps[i]]=true;   
                }
                for (i=0; i<_lstLogicOps.length; i++)
                {
                    _logiOpMap[_tok_map_prefix + _lstLogicOps[i]]=true;   
                }
                for (i=0; i<_lstCompaOps.length; i++)
                {
                    _compOpMap[_tok_map_prefix + _lstCompaOps[i]]=true;   
                }
                for (i=0; i<_lstFuncOps.length; i++)
                {
                    _funcMap[_tok_map_prefix + _lstFuncOps[i]]=true;   
                }
            };
            
            var _makeToken = function(tok, force_type)
            {
                var token={val:tok, type:_TOKEN_TYPES.UNKNOWN};
                var typeoftok=typeof(tok);
                
                if (typeof(force_type) == 'undefined' || force_type==null || force_type=='')
                    force_type=_TOKEN_TYPES.__DEFAULT__;
                    
                switch (force_type)
                {
                    case _TOKEN_TYPES.EMPTY_TOKEN:
                            token.type=_TOKEN_TYPES.EMPTY_TOKEN;
                            token.isEmpty=true;
                            token.val='';
                            break;
                    case _TOKEN_TYPES.STRING_LITERAL:
                            token.type=_TOKEN_TYPES.STRING_LITERAL;
                            token.isStringLiteral=true;
                            token.val=tok.toString();
                            break;
                    case _TOKEN_TYPES.DATE:
                        if (_isDateInstance(tok))
                        {
                            token.type=_TOKEN_TYPES.DATE;
                            token.isDate=true;
                        }
                        break;
                    case _TOKEN_TYPES.ARRAY:
                        if (!_isArrayInstance(tok))
                        {
                            token.val=[tok];
                        }
                        token.type=_TOKEN_TYPES.ARRAY;
                        token.isArray=true;
                        break;
                    case _TOKEN_TYPES.REGEX:
                        if (!_isRegExpInstance(tok))
                        {
                            token.val=Functions.Regex(tok);
                        }
                        token.type=_TOKEN_TYPES.REGEX;
                        token.isRegex=true;
                        break;
                    case _TOKEN_TYPES.COMMA:
                        token.type=_TOKEN_TYPES.COMMA;
                        token.isComma=true;
                        token.val=',';
                        break;
                    case _TOKEN_TYPES.LEFT_PAREN:
                        token.type=_TOKEN_TYPES.LEFT_PAREN;
                        token.isLeftParen=true;
                        token.isParen=true;
                        token.val='(';
                        break;
                    case _TOKEN_TYPES.RIGHT_PAREN:
                        token.type=_TOKEN_TYPES.RIGHT_PAREN;
                        token.isRightParen=true;
                        token.isParen=true;
                        token.val=')';
                        break;
                    case _TOKEN_TYPES.ARG_TERMINAL:
                        token.type=_TOKEN_TYPES.ARG_TERMINAL;
                        token.isArgTerminal=true;
                        token.val=_ARG_TERMINAL;
                        break;
                    case _TOKEN_TYPES.UNARY_NEGATIVE:
                        token.type=_TOKEN_TYPES.UNARY_NEGATIVE;
                        token.isUnaryNegative=true;
                        token.val=_UNARY_NEGATIVE;
                        token.isArithmeticOp=true;
                        token.isOp=true;
                        break;
                    case _TOKEN_TYPES.UNARY_NEGATION:
                        token.type=_TOKEN_TYPES.UNARY_NEGATION;
                        token.isUnaryNegation=true;
                        token.val=_UNARY_NEGATION;
                        token.isLogicOp=true;
                        token.isOp=true;
                        break;
                    case _TOKEN_TYPES.NUMBER:
                        token.type=_TOKEN_TYPES.NUMBER;
                        token.isNumber=true;
                        if (typeoftok=='string')
                            token.val=new Number(tok);
                        else if (typeoftok=='number')
                            token.val=tok;
                        else if (typeoftok=='boolean')
                            token.val=(tok==true)?1:0;
                        else if (typeoftok=='object')
                            token.val=new Number(tok.toString());
                        break;
                    case _TOKEN_TYPES.BOOLEAN:
                        token.type=_TOKEN_TYPES.BOOLEAN;
                        token.isBoolean=true;
                        if (typeoftok=='boolean')
                            token.val=tok;
                        else if (typeoftok=='string')
                            token.val=(tok.toUpperCase()=='TRUE')?true:false;
                        else if (typeoftok=='number')
                            token.val=(tok != 0)?true:false;
                        else if (Object.prototype.toString.call(tok) === '[object Number]')
                            token.val=(tok.valueOf() != 0)?true:false;
                        else if (typeoftok=='object')
                            token.val=(tok.toString().toUpperCase()=='TRUE')?true:false;
                        break;
                    case _TOKEN_TYPES.VARIABLE:
                        token.type=_TOKEN_TYPES.VARIABLE;
                        token.isVariable=true;
                        break;
                    case _TOKEN_TYPES.__DEFAULT__:
                    default:
                        if (
                            (typeoftok=='object' && 
                            ((tok instanceof String) || Object.prototype.toString.call(tok) === '[object String]') &&
                            tok._isStringLiteral)
                            )
                        {
                            token.type=_TOKEN_TYPES.STRING_LITERAL;
                            token.isStringLiteral=true;
                            token.val=tok.toString();
                        }
                        // date token
                        else if (_isDateInstance(tok))
                        {
                            token.type=_TOKEN_TYPES.DATE;
                            token.isDate=true;
                        }
                        // array token
                        else if (_isArrayInstance(tok))
                        {
                            token.type=_TOKEN_TYPES.ARRAY;
                            token.isArray=true;
                        }
                        // regex token
                        else if (_isRegExpInstance(tok))
                        {
                            token.type=_TOKEN_TYPES.REGEX;
                            token.isRegex=true;
                        }
                        else if (tok==',')
                        {
                            token.type=_TOKEN_TYPES.COMMA;
                            token.isComma=true;
                            token.val=',';
                        }
                        else if (tok=='(')
                        {
                            token.type=_TOKEN_TYPES.LEFT_PAREN;
                            token.isLeftParen=true;
                            token.isParen=true;
                            token.val='(';
                            token.isOp=true;
                        }
                        else if (tok==')')
                        {
                            token.type=_TOKEN_TYPES.RIGHT_PAREN;
                            token.isRightParen=true;
                            token.isParen=true;
                            token.val=')';
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
                        else if (_isNumber(tok))
                        {
                            token.type=_TOKEN_TYPES.NUMBER;
                            token.isNumber=true;
                            if (typeoftok=='string')
                                token.val=new Number(tok).valueOf();
                            else if (typeoftok=='number')
                                token.val=tok;
                            else if (typeoftok=='boolean')
                                token.val=(tok==true)?1:0;
                            else if (typeoftok=='object')
                                token.val=new Number(tok.toString()).valueOf();
                        }
                        else if (_isBoolean(tok))
                        {
                            token.type=_TOKEN_TYPES.BOOLEAN;
                            token.isBoolean=true;
                            if (typeoftok=='boolean')
                                token.val=tok;
                            else if (typeoftok=='string')
                                token.val=(tok.toUpperCase()=='TRUE')?true:false;
                            else if (typeoftok=='number')
                                token.val=(tok != 0)?true:false;
                            else if (Object.prototype.toString.call(tok) === '[object Number]')
                                token.val=(tok.valueOf() != 0)?true:false;
                            else if (typeoftok=='object')
                                token.val=(tok.toString().toUpperCase()=='TRUE')?true:false;
                        }
                        else if (_isOperator(tok))
                        {
                            token.type=_TOKEN_TYPES.ARITHMETIC_OP;
                            token.isArithmeticOp=true;
                            token.isOp=true;

                        }
                        else if (_isLogicOperator(tok))
                        {
                            token.type=_TOKEN_TYPES.LOGICAL_OP;
                            token.isLogicOp=true;
                            token.isOp=true;
                        } 
                        else if (_isCompOperator(tok))
                        {
                            token.type=_TOKEN_TYPES.COMPARISON_OP;
                            token.isCompOp=true;
                            token.isOp=true;
                        } 
                        else if (_isFunction(tok))
                        {
                            token.type=_TOKEN_TYPES.FUNCTION;
                            token.isFunction=true;
                            token.val=tok;
                        }
                        else if (_isVariableName(tok))
                        {
                            token.type=_TOKEN_TYPES.VARIABLE;
                            token.isVariable=true;
                        }
                        break;
                }
                if (token.isOp || token.isFunction)
                {
                    var intRet = 0;

                    switch(token.val)
                    {
                        case "+" :
                        case "-" :
                            intRet = 50;
                            break;
                        case "*" :
                        case "/" :
                        case "%" :
                            intRet = 60;
                            break;
                        case "^" :
                            intRet = 70;
                            break;
                        case _UNARY_NEGATIVE:
                        case _UNARY_NEGATION:
                        case "!" :
                        case "NOT" :
                            intRet = 100;
                            break;
                        case "(" :
                            intRet = 1000;
                            break;
                        /*case "{" :
                            intRet = 500;  // as function
                            break;*/
                        case "AND" :
                        case "&" :
                            intRet = 35;
                            break;
                        case "OR" :
                        case "|" :
                            intRet = 30;
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
                            intRet = 40;
                            break;
                        default :
                            if (token.isFunction)
                                intRet = 500;
                            else
                                intRet = 0;
                            break;
                    }
                    token.precedence=intRet;
                }
                else
                    token.precedence=0;
                
                //console.log(token);
                return token;
            };
            
            var _EMPTY_TOKEN = _makeToken('', _TOKEN_TYPES.EMPTY_TOKEN);
            var _EMPTY_STRING = _makeToken('', _TOKEN_TYPES.STRING_LITERAL);
            
            // public (static) methods
            return {
            
            makeToken : _makeToken,
            
            cloneToken : function(tok)
            {
                var newtok={};
                
                for (var at in tok)
                {
                    if (tok.hasOwnProperty(at))
                    {
                        if (_isDateInstance(tok[at]))
                            newtok[at]=new Date(tok[at].getTime());
                        else
                            newtok[at]=tok[at];
                    }
                }
                return newtok;
            },
            
            TOKEN_TYPE : _TOKEN_TYPES,
            
            EMPTY_TOKEN : _EMPTY_TOKEN,
            
            EMPTY_STRING : _EMPTY_STRING,
            
            UNARY_NEGATIVE : _makeToken(_UNARY_NEGATIVE, _TOKEN_TYPES.UNARY_NEGATIVE),
            
            UNARY_NEGATION : _makeToken(_UNARY_NEGATION, _TOKEN_TYPES.UNARY_NEGATION),
            
            ARG_TERMINAL : _makeToken(_ARG_TERMINAL, _TOKEN_TYPES.ARG_TERMINAL),
            
            isBoolean : _isBoolean,
            
            isNumber : _isNumber,
            
            isString : function(a)
            {
                if (!_isDefined(a)) return false;
                var typeofa=typeof(a);
                if (_isNumber(a) || _isBoolean(a) || _isArrayInstance(a) || _isDateInstance(a) || _isRegExpInstance(a)) return false;
                
                if (
                        typeofa=='string' ||
                        (
                            typeofa=='object' && 
                            ((a instanceof String) || Object.prototype.toString.call(a) === '[object String]')
                        )
                    )
                    return true;
                return false;
            },
            
            isDateInstance : _isDateInstance,
            
            isArrayInstance : _isArrayInstance,
            
            isRegExpInstance : _isRegExpInstance,
            
            isDate : function(pstrVal, format, getDate)
            {
                if (!_isDefined(pstrVal))
                    return false;
                if (_isDateInstance(pstrVal))
                {
                    if (typeof(getDate)!='undefined')
                        getDate.date=pstrVal;
                    return true;
                }
                return DateParser.isDate(pstrVal, format, getDate);
            },
            
            toArray : function(v)
            {
                if (_isArrayInstance(v))
                    return v;
                else return [v];
            },
            
            toNumber : function(pobjVal)
            {
                var dblRet = Number.NaN;

                if (typeof(pobjVal) == "number")
                    return pobjVal;
                else
                {
                    dblRet = new Number(pobjVal);
                    return dblRet.valueOf();
                }
            },

            toBoolean : function(pobjVal)
            {
                var dblNo = Number.NaN;
                var strTmp = null;

                if (pobjVal == null || pobjVal == undefined)
                    throw "Boolean value is not defined!";
                else if (typeof(pobjVal) == "boolean")
                    return pobjVal;
                else if (typeof(pobjVal) == "number")
                    return (pobjVal != 0);
                else if (_isNumber(pobjVal))
                {
                    dblNo = Tokenizer.toNumber(pobjVal);
                    if (isNaN(dblNo)) 
                        return null;
                    else
                        return (dblNo != 0);
                }
                else if (typeof(pobjVal) == "object")
                {
                    strTmp = pobjVal.toString();
                    if (strTmp.toUpperCase() == "TRUE")
                        return true;
                    else if (strTmp.toUpperCase() == "FALSE")
                        return false;
                    else
                        return null;
                }
                else if (typeof(pobjVal) == "string")
                {
                    if (pobjVal.toUpperCase() == "TRUE")
                        return true;
                    else if (pobjVal.toUpperCase() == "FALSE")
                        return false;
                    else
                        return null;
                }
                else
                    return null;
            },
            
            Tokanize : function(pstrExpression)
            {

                var intCntr, intBraces;
                var arrTokens;
                var intIndex, intPos;
                var chrChar, chrNext;
                var strToken, prevToken;

                // build fast lookup maps
                _buildMaps();
                
                intCntr   = 0;
                intBraces = 0;
                intBrackets = 0;
                intIndex  = 0;
                strToken  = "";
                arrTokens = new Array();
                pstrExpression = _trim(pstrExpression);
                while (intCntr < pstrExpression.length)
                {
                    prevToken = _EMPTY_TOKEN;
                    chrChar = pstrExpression.substr(intCntr, 1);
                    switch (chrChar)
                    {
                        case " " :
                            if (strToken.length > 0)
                            {
                                arrTokens[intIndex] = _makeToken(strToken);
                                intIndex++;
                                strToken = "";
                            }
                            break;
                        //case "{":
                        case "(":
                            //(chrChar=='(')?intBraces++:intBrackets++;
                            intBraces++;
                            if (strToken.length > 0)
                            {
                                arrTokens[intIndex] = _makeToken(strToken);
                                intIndex++;
                                strToken = "";
                            }
                            arrTokens[intIndex] = _makeToken(chrChar);
                            intIndex++;
                            break;
                        //case "}" :
                        case ")" :
                            //(chrChar==')')?intBraces--:intBrackets--;
                            intBraces--;
                            if (strToken.length > 0)
                            {
                                arrTokens[intIndex] = _makeToken(strToken);
                                intIndex++;
                                strToken = "";
                            }
                            arrTokens[intIndex] = _makeToken(chrChar);
                            intIndex++;
                            break;
                        case "^" :
                        case "*" :
                        case "/" :
                        case "%" :
                        case "&" :
                        case "|" :
                        case "," :
                        case "!" :
                            if (strToken.length > 0)
                            {
                                arrTokens[intIndex] = _makeToken(strToken);
                                intIndex++;
                                strToken = "";
                            }
                            arrTokens[intIndex] = _makeToken(chrChar);
                            intIndex++;
                            break;
                        case "-" :
                            if (strToken.length > 0)
                            {
                                arrTokens[intIndex] = _makeToken(strToken);
                                intIndex++;
                                strToken = "";
                            }
                            chrNext = pstrExpression.substr(intCntr + 1, 1);
                            if (arrTokens.length > 0)
                                prevToken = arrTokens[intIndex - 1];
                            if (/*intCntr == 0 ||*/((prevToken.isArithmeticOp ||
                                prevToken.isLeftParen || prevToken.isComma) && 
                                (_isDigit(chrNext) || chrNext == "(")))
                            {
                                // Negative Number
                                strToken += chrChar;
                            }
                            else
                            {
                                arrTokens[intIndex] = _makeToken(chrChar);
                                intIndex++;
                                strToken = "";
                            }
                            break;
                        case "+" :
                            if (strToken.length > 0)
                            {
                                arrTokens[intIndex] = _makeToken(strToken);
                                intIndex++;
                                strToken = "";
                            }
                            chrNext = pstrExpression.substr(intCntr + 1, 1);
                            if (arrTokens.length > 0)
                                prevToken = arrTokens[intIndex - 1];
                            if (/*intCntr == 0 ||*/ ((prevToken.isArithmeticOp ||
                                prevToken.isLeftParen || prevToken.isComma) && 
                                (_isDigit(chrNext) || chrNext == "(")))
                            {
                                // positive Number
                                strToken += chrChar;
                            }
                            else
                            {
                                arrTokens[intIndex] = _makeToken(chrChar);
                                intIndex++;
                                strToken = "";
                            }
                            break;
                        case "<" :
                            chrNext = pstrExpression.substr(intCntr + 1, 1);
                            if (strToken.length > 0)
                            {
                                arrTokens[intIndex] = _makeToken(strToken);
                                intIndex++;
                                strToken = "";
                            }
                            if (chrNext == "=")
                            {
                                arrTokens[intIndex] = _makeToken(chrChar + "=");
                                intIndex++;
                                intCntr++;
                            }
                            else if (chrNext == ">")
                            {
                                arrTokens[intIndex] = _makeToken(chrChar + ">");
                                intIndex++;
                                intCntr++;
                            }
                            else
                            {
                                arrTokens[intIndex] = _makeToken(chrChar);
                                intIndex++;
                            }
                            break;
                        case ">" :
                            chrNext = pstrExpression.substr(intCntr + 1, 1);
                            if (strToken.length > 0)
                            {
                                arrTokens[intIndex] = _makeToken(strToken);
                                intIndex++;
                                strToken = "";
                            }
                            if (chrNext == "=")
                            {
                                arrTokens[intIndex] = _makeToken(chrChar + "=");
                                intIndex++;
                                intCntr++;
                            }
                            else
                            {
                                arrTokens[intIndex] = _makeToken(chrChar);
                                intIndex++;
                            }
                            break;
                        case "=" :
                            if (strToken.length > 0)
                            {
                                arrTokens[intIndex] = _makeToken(strToken);
                                intIndex++;
                                strToken = "";
                            }
                            arrTokens[intIndex] = _makeToken(chrChar);
                            intIndex++;
                            break;
                        case "'" :
                        case "\"" :
                            if (strToken.length > 0)
                            {
                                arrTokens[intIndex] = _makeToken(strToken);
                                intIndex++;
                                strToken = "";
                            }

                            intPos = pstrExpression.indexOf(chrChar, intCntr + 1);
                            if (intPos < 0) 
                                throw "Unterminated string constant";
                            else
                            {
                                strToken += pstrExpression.substring(intCntr + 1, intPos);
                                strToken=new String(strToken);
                                strToken._isStringLiteral=true;
                                arrTokens[intIndex] = _makeToken(strToken);
                                intIndex++;
                                strToken = "";
                                intCntr = intPos;
                            }
                            break;
                        default :
                            strToken += chrChar;
                            break;
                    }
                    intCntr++;
                }
                if (intBraces > 0)
                    throw "Unbalanced parenthesis!";

                if (strToken.length > 0)
                    arrTokens[intIndex] = _makeToken(strToken);
                return arrTokens;
            }
        }
        })(DateParser);
        
        /*------------------------------------------------------------------------------
         * NAME       : HandleFunctions
         * PURPOSE    : Execute built-in functions
         * PARAMETERS : pstrTok - The current function name
         *              pStack - Operand stack
         * RETURNS    : Nothing, the result is pushed back onto the stack.
         *----------------------------------------------------------------------------*/
        function _HandleFunctions(pstrTok, pStack, pdtFormat, parrVars)
        {
            var varTmp, varTerm, varTerm2, objTmp, varFormat;
            var objOp1, objOp2, objFormat;
            var arrArgs;
            var intCntr;



            if (!pstrTok.isFunction)
                throw "Unsupported function token [" + pstrTok.val + "]";

            varTmp = pstrTok.val;
            arrArgs = new Array();
            varTerm = Tokenizer.ARG_TERMINAL;
            while ( !pStack.IsEmpty() )
            {
                varTerm = pStack.Pop();
                if (!varTerm.isArgTerminal)
                    arrArgs[arrArgs.length] = varTerm;
                else
                    break;
            }

           // console.log( 'testing functions ', varTmp, arrArgs );

            switch (varTmp)
            {
                case "ARRAY" :
                   var arrArray=new Array();
                    
                    objTmp = 0;
                    intCntr = arrArgs.length;
                    while (--intCntr >= 0)
                    {
                        varTerm = arrArgs[intCntr];
                        if (varTerm.isVariable)
                        {
                            objTmp = parrVars[varTerm.val];
                            if (objTmp == undefined || objTmp == null)
                                throw "Variable [" + varTerm.val + "] not defined";
                            else
                                varTerm = objTmp;
                        }
                        arrArray=arrArray.concat(Tokenizer.toArray(varTerm.val));
                    }
                    pStack.Push(Tokenizer.makeToken(arrArray,Tokenizer.TOKEN_TYPE.ARRAY));
                    break;
                case "TODAY" :
                    pStack.Push(Tokenizer.makeToken(DateParser.currentDate(), Tokenizer.TOKEN_TYPE.DATE));
                    break;
                case "ACOS" :
                case "ASIN" :
                case "ATAN" :
                    throw "Function [" + varTmp + "] is not implemented!";
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

                    if (varTmp != "RAND")
                    {
                        if (arrArgs.length < 1)
                            throw varTmp + " requires at least one argument!";
                        else if (arrArgs.length > 1)
                            throw varTmp + " requires only one argument!";
                    }
                    else
                    {
                        if (arrArgs.length < 1)
                            throw varTmp + " requires at least one argument!";
                        else if (arrArgs.length > 2)
                            throw varTmp + " requires at most two arguments!";
                    }
                    varTerm = arrArgs[0];
                    if (varTerm.isVariable)
                    {
                        objTmp = parrVars[varTerm.val];
                        if (objTmp == undefined || objTmp == null)
                            throw "Variable [" + varTerm.val + "] not defined";
                        else
                            varTerm = objTmp;
                    }
                    
                    objTmp = varTerm.val;

                    if( varTerm.val !== 0 && !varTerm.val )
                        throw varTmp + " operates on numeric operands only!";
                    
                    else if ( isNaN( +varTerm.val ) )
                        throw varTmp + " operates on numeric operands only!";
                    else
                    {

                        objTmp = Tokenizer.toNumber(varTerm.val);
                        if (varTmp == "RAND")
                        {
                            rand_max=Math.floor(objTmp);
                            if (arrArgs.length == 2)
                            {
                                varTerm = arrArgs[1];
                                if (varTerm.isVariable)
                                {
                                    objTmp = parrVars[varTerm.val];
                                    if (objTmp == undefined || objTmp == null)
                                        throw "Variable [" + varTerm.val + "] not defined";
                                    else
                                        varTerm = objTmp;
                                }
                                
                                if (!varTerm.isNumber)
                                    throw varTmp + " operates on numeric operands only!";
                                
                                objTmp = Tokenizer.toNumber(varTerm.val);
                                
                                rand_min=Math.floor(objTmp);
                            }
                        }
                    }
                        
                    if (varTmp == "ABS")
                        pStack.Push(Tokenizer.makeToken(Math.abs(objTmp),Tokenizer.TOKEN_TYPE.NUMBER));
                    else if (varTmp == "CHR"){
                        // TODO check what happens when $objTmp is empty; what does fromCharCode() return?

                        pStack.Push(Tokenizer.makeToken(String.fromCharCode(objTmp),Tokenizer.TOKEN_TYPE.STRING_LITERAL));
                    }
                    else if (varTmp == "COS")
                        pStack.Push(Tokenizer.makeToken(Math.cos(objTmp),Tokenizer.TOKEN_TYPE.NUMBER));
                    else if (varTmp == "FIX")
                        pStack.Push(Tokenizer.makeToken(Math.floor(objTmp),Tokenizer.TOKEN_TYPE.NUMBER));
                    else if (varTmp == "HEX")
                        pStack.Push(Tokenizer.makeToken(objTmp.toString(16),Tokenizer.TOKEN_TYPE.STRING_LITERAL));
                    else if (varTmp == "LOG")
                        pStack.Push(Tokenizer.makeToken(Math.log(objTmp),Tokenizer.TOKEN_TYPE.NUMBER));
                    else if (varTmp == "RAND")
                        pStack.Push(Tokenizer.makeToken(Math.round(rand_min+(rand_max-rand_min)*Math.random()),Tokenizer.TOKEN_TYPE.NUMBER));
                    else if (varTmp == "ROUND")
                        pStack.Push(Tokenizer.makeToken(Math.round(objTmp),Tokenizer.TOKEN_TYPE.NUMBER));
                    else if (varTmp == "SIN")
                        pStack.Push(Tokenizer.makeToken(Math.sin(objTmp),Tokenizer.TOKEN_TYPE.NUMBER));
                    else if (varTmp == "SQRT")
                        pStack.Push(Tokenizer.makeToken(Math.sqrt(objTmp),Tokenizer.TOKEN_TYPE.NUMBER));
                    else if (varTmp == "TAN")
                        pStack.Push(Tokenizer.makeToken(Math.tan(objTmp),Tokenizer.TOKEN_TYPE.NUMBER));
                    break;
                case "STR" :
                    if (arrArgs.length < 1)
                        throw varTmp + " requires at least one argument!";
                    else if (arrArgs.length > 2)
                        throw varTmp + " requires at most two arguments!";
                    varTerm = arrArgs[arrArgs.length-1];
                    if (varTerm.isVariable)
                    {
                        objTmp = parrVars[varTerm.val];
                        if (objTmp == undefined || objTmp == null)
                            throw "Variable [" + varTerm.val + "] not defined";
                        else
                            varTerm = objTmp;
                    }
                    // if date, output formated date string
                    if (varTerm.isDate)
                    {
                        var format='';
                        if (arrArgs.length==2)
                        {
                            varFormat = arrArgs[0];
                            if (varFormat.isVariable)
                            {
                                objTmp = parrVars[varFormat.val];
                                if (objTmp == undefined || objTmp == null)
                                    throw "Variable [" + varFormat.val + "] not defined";
                                else
                                    varFormat = objTmp;
                            }
                            
                            if (!varFormat.isStringLiteral)
                                throw "format argument for " + varTmp + " must be a string!";
                            format=varFormat.val;
                        }
                        pStack.Push(Tokenizer.makeToken(DateParser.formatDate(varTerm.val, format),Tokenizer.TOKEN_TYPE.STRING_LITERAL));
                    }
                    else  // just convert to string
                        pStack.Push(Tokenizer.makeToken(varTerm.val.toString(),Tokenizer.TOKEN_TYPE.STRING_LITERAL));
                    break;
                case "ASC" :

                    if (arrArgs.length > 1)
                        throw varTmp + " requires only one argument!";
                    else if (arrArgs.length < 1)
                        throw varTmp + " requires at least one argument!";
                    varTerm = arrArgs[0];
                    if (varTerm.isVariable)
                    {
                        objTmp = parrVars[varTerm.val];
                        if (objTmp == undefined || objTmp == null)
                            throw "Variable [" + varTerm.val + "] not defined";
                        else
                            varTerm = objTmp;
                    }
                    if( varTerm.isNumber )
                    {
                        varTerm.val = varTerm.val.toString();
                        varTerm.isStringLiteral = true;
                    }

                    if (!varTerm.isStringLiteral)
                        throw varTmp + " requires a string type operand!";
                    else
                    {
						if ( varTerm.val ) {
							pStack.Push(Tokenizer.makeToken(varTerm.val.charCodeAt(0),Tokenizer.TOKEN_TYPE.NUMBER));
						} else {
							pStack.Push(Tokenizer.makeToken(0,Tokenizer.TOKEN_TYPE.NUMBER));
						}
                    }
                    break;
                case "REGEX" :
                    if (arrArgs.length < 1)
                        throw varTmp + " requires at least one argument!";
                    else if (arrArgs.length > 2)
                        throw varTmp + " requires at most two arguments!";
                    
                    varTerm = arrArgs[arrArgs.length-1];
                    if (varTerm.isVariable)
                    {
                        objTmp = parrVars[varTerm.val];
                        if (objTmp == undefined || objTmp == null)
                            throw "Variable [" + varTerm.val + "] not defined";
                        else
                            varTerm = objTmp;
                    }
                    
                    if (!varTerm.isStringLiteral)
                        throw varTmp + " operates on string type operands!";
                        
                    var opts=Tokenizer.EMPTY_STRING;
                    if (arrArgs.length==2)
                    {
                        opts = arrArgs[0];
                        if (opts.isVariable)
                        {
                            objTmp = parrVars[opts.val];
                            if (objTmp == undefined || objTmp == null)
                                throw "Variable [" + opts.val + "] not defined";
                            else
                                opts = objTmp;
                        }
                        
                        if (!opts.isStringLiteral)
                            throw varTmp + " operates on string type operands!";
                    }
                    pStack.Push(Tokenizer.makeToken(Functions.Regex(varTerm.val.toString(), opts.val.toString()),Tokenizer.TOKEN_TYPE.REGEX));
                    break;
                case "LCASE" :
                case "UCASE" :
                case "NUM" :

                    if (arrArgs.length < 1)
                        throw varTmp + " requires at least one argument!";
                    else if (arrArgs.length > 1)
                        throw varTmp + " requires only one argument!";

                    varTerm = arrArgs[0];
                    if (varTerm.isVariable)
                    {
                        objTmp = parrVars[varTerm.val];
                        if (objTmp == undefined || objTmp == null)
                            throw "Variable [" + varTerm.val + "] not defined";
                        else
                            varTerm = objTmp;
                    }

                    if( varTerm.isNumber )
                    {
                        varTerm.val = varTerm.val.toString();
                        varTerm.isStringLiteral = true;
                    }

                    if (!varTerm.isStringLiteral && varTmp != "NUM")
                        throw varTmp + " requires a string type operand!";
                    else
                    {
                        if (varTmp == "LCASE")
                        {
                            pStack.Push(Tokenizer.makeToken(varTerm.val.toLowerCase(),Tokenizer.TOKEN_TYPE.STRING_LITERAL));
                        }
                        else if (varTmp == "UCASE")
                        {
                            pStack.Push(Tokenizer.makeToken(varTerm.val.toUpperCase(),Tokenizer.TOKEN_TYPE.STRING_LITERAL));
                        }
                        else if (varTmp == "NUM")
                        {
                            objTmp=Tokenizer.toNumber(varTerm.val)+0.0;
                            if (isNaN(objTmp))
                                throw varTmp + " cannot convert [" + varTerm.val + "] to number!";
                            pStack.Push(Tokenizer.makeToken(objTmp,Tokenizer.TOKEN_TYPE.NUMBER));
                        }
                    }
                    break;
                case "LEN" :
                    if (arrArgs.length < 1)
                        throw varTmp + " requires at least one argument!";
                    else if (arrArgs.length > 1)
                        throw varTmp + " requires only one argument!";

                    varTerm = arrArgs[0];
                    if (varTerm.isVariable)
                    {
                        objTmp = parrVars[varTerm.val];
                        if (objTmp == undefined || objTmp == null)
                            throw "Variable [" + varTerm.val + "] not defined";
                        else
                            varTerm = objTmp;
                    }

                    if (!varTerm.isArray && !varTerm.isStringLiteral)
                        throw varTmp + " requires a string or array type operand!";
                    else
                    {
                        pStack.Push(Tokenizer.makeToken(varTerm.val.length,Tokenizer.TOKEN_TYPE.NUMBER));
                    }
                    break;
                case "USER" :
                    if (arrArgs.length < 1)
                        throw varTmp + " requires at least one argument!";
                    else if (arrArgs.length > 1)
                        throw varTmp + " requires only one argument!";

                    varTerm = arrArgs[0];
                    if (varTerm.isVariable)
                    {
                        objTmp = parrVars[varTerm.val];
                        if (objTmp == undefined || objTmp == null)
                            throw "Variable [" + varTerm.val + "] not defined";
                        else
                            varTerm = objTmp;
                    }

                    if (!varTerm.isStringLiteral)
                        throw varTmp + " requires a string type operand!";
                    else
                    {
                        pStack.Push(Tokenizer.makeToken(Functions.User(varTerm.val),Tokenizer.TOKEN_TYPE.STRING_LITERAL));
                    }
                    break;
                case "COOKIE" :
                    if (arrArgs.length < 1)
                        throw varTmp + " requires at least one argument!";
                    else if (arrArgs.length > 1)
                        throw varTmp + " requires only one argument!";

                    varTerm = arrArgs[0];
                    if (varTerm.isVariable)
                    {
                        objTmp = parrVars[varTerm.val];
                        if (objTmp == undefined || objTmp == null)
                            throw "Variable [" + varTerm.val + "] not defined";
                        else
                            varTerm = objTmp;
                    }

                    if (!varTerm.isStringLiteral)
                        throw varTmp + " requires a string type operand!";
                    else
                    {
                        //console.log(varTerm.val,varTerm.val.length);
                        pStack.Push(Tokenizer.makeToken(Functions.Cookie(varTerm.val),Tokenizer.TOKEN_TYPE.STRING_LITERAL));
                    }
                    break;
                case "CONTAINS" :
                 //   console.log( 'testing functions ', varTmp, arrArgs );
                    if (arrArgs.length < 2)
                        throw varTmp + " requires at least two arguments!";
                    else if (arrArgs.length > 2)
                        throw varTmp + " requires only two arguments!";

                    varTerm = arrArgs[1];
                    if (varTerm.isVariable)
                    {
                        objTmp = parrVars[varTerm.val];
                        if (objTmp == undefined || objTmp == null)
                            throw "Variable [" + varTerm.val + "] not defined";
                        else
                            varTerm = objTmp;
                    }
                    varTerm2 = arrArgs[0];
                    if (varTerm2.isVariable)
                    {
                        objTmp = parrVars[varTerm2.val];
                        if (objTmp == undefined || objTmp == null)
                            throw "Variable [" + varTerm2.val + "] not defined";
                        else
                            varTerm2 = objTmp;
                    }

                    if ( !varTerm.isArray )
                        throw varTmp + " requires an array as first argument!";
                    else
                    {
                        var found=false;
                        /*var ii=varTerm.val.length;
                        while(--ii>=0)
                        {
                            if (varTerm.val[ii]==varTerm2.val)
                            {
                                found=true;
                                break;
                            }
                        }*/
                        found=Functions.Contains(varTerm.val, varTerm2.val);
                        pStack.Push(Tokenizer.makeToken(found,Tokenizer.TOKEN_TYPE.BOOLEAN));
                    }
                    break;
                case "DATE" :
                    if (arrArgs.length < 2)
                        throw varTmp + " requires at least two arguments!";
                    else if (arrArgs.length > 2)
                        throw varTmp + " requires only two arguments!";

                    varTerm = arrArgs[1];
                    if (varTerm.isVariable)
                    {
                        objTmp = parrVars[varTerm.val];
                        if (objTmp == undefined || objTmp == null)
                            throw "Variable [" + varTerm.val + "] not defined";
                        else
                            varTerm = objTmp;
                    }
                    varFormat = arrArgs[0];
                    if (varFormat.isVariable)
                    {
                        objFormat = parrVars[varFormat.val];
                        if (objFormat == undefined || objFormat == null)
                            throw "Variable [" + varFormat.val + "] not defined";
                        else
                            varFormat = objFormat;
                    }

                    var dateobj={};
                    if (
                        (!varTerm.isStringLiteral) || 
                        (!varFormat.isStringLiteral)
                    )
                        throw varTmp + " requires string type operands!";
                    else if (!Tokenizer.isDate(varTerm.val, varFormat.val, dateobj))
                        throw varTmp + " can not convert [" + varTerm.val + "] to a valid date with format [" + varFormat.val + "]!";
                    else
                    {
                        if (dateobj.date)
                            pStack.Push(Tokenizer.makeToken(dateobj.date,Tokenizer.TOKEN_TYPE.DATE));
                        else
                            throw varTmp + " unknown error";
                    }
                    break;
                case "empty":
                case "EMPTY":
                    if (arrArgs.length < 1)
                        throw varTmp + " requires at least one arguments!";
                    else if (arrArgs.length > 1)
                        throw varTmp + " requires only one arguments!";

                    varFormat = arrArgs[0];


                    if( varFormat.isEmpty === true )
                    {
                        pStack.Push( Tokenizer.makeToken(true,Tokenizer.TOKEN_TYPE.BOOLEAN) );
                    }
                    else if( varFormat.isArray === true && varFormat.val.length === 0 )
                    {
                        pStack.Push( Tokenizer.makeToken(true,Tokenizer.TOKEN_TYPE.BOOLEAN) );
                    }
                    else if( varFormat.isStringLiteral === true && varFormat.val === "" )
                    {
                        pStack.Push( Tokenizer.makeToken(true,Tokenizer.TOKEN_TYPE.BOOLEAN) );
                    }
                    else if( varFormat.isDate && !varFormat.val )
                    {
                        pStack.Push( Tokenizer.makeToken(true,Tokenizer.TOKEN_TYPE.BOOLEAN) );
                    }
                    else
                    {
                        pStack.Push( Tokenizer.makeToken(false,Tokenizer.TOKEN_TYPE.BOOLEAN) );
                    }


                    break;
                case "LEFT" :
                case "RIGHT" :
                    if (arrArgs.length < 2)
                        throw varTmp + " requires at least two arguments!";
                    else if (arrArgs.length > 2)
                        throw varTmp + " requires only two arguments!";

                    for (intCntr = 0; intCntr < arrArgs.length; intCntr++)
                    {
                        varTerm = arrArgs[intCntr];
                        if (varTerm.isVariable)
                        {
                            objTmp = parrVars[varTerm.val];
                            if (objTmp == undefined || objTmp == null)
                                throw "Variable [" + varTerm.val + "] not defined";
                            else
                                varTerm = objTmp;
                        }

                        if( varTerm.isNumber )
                        {
                            arrArgs[1].val = arrArgs[1].val.toString()
                            varTerm.isStringLiteral = true;
                        }

                        if (intCntr == 0 && !varTerm.isNumber)
                            throw varTmp + " operator requires numeric length!";
                        else if (intCntr == 1 && !varTerm.isStringLiteral)
                            throw varTmp + " operator requires a string operand!";
                        arrArgs[intCntr] = varTerm;
                    }
                    varTerm = arrArgs[1].val.toString();
                    objTmp = Tokenizer.toNumber(arrArgs[0].val);
                    if (varTmp == "LEFT")
                    {
                        pStack.Push(Tokenizer.makeToken(varTerm.substring(0, objTmp),Tokenizer.TOKEN_TYPE.STRING_LITERAL));
                    }
                    else
                    {
                        pStack.Push(Tokenizer.makeToken(varTerm.substr((varTerm.length - objTmp), objTmp),Tokenizer.TOKEN_TYPE.STRING_LITERAL));
                    }
                    break;
                case "MID" :
                case "IIF" :

                    if (arrArgs.length < 3)
                        throw varTmp + " requires at least three arguments!";
                    else if (arrArgs.length > 3)
                        throw varTmp + " requires only three arguments!";



                    for (intCntr = 0; intCntr < arrArgs.length; intCntr++)
                    {
                        varTerm = arrArgs[intCntr];
                        if (varTerm.isVariable)
                        {
                            objTmp = parrVars[varTerm.val];
                            if (objTmp == undefined || objTmp == null)
                                throw "Variable [" + varTerm.val + "] not defined";
                            else
                                varTerm = objTmp;
                        }

                        if( varTerm.isNumber )
                        {
                            arrArgs[2].val = arrArgs[2].val.toString()
                            varTerm.isStringLiteral = true;
                        }

                        if (varTmp == "MID" && intCntr <= 1 && !varTerm.isNumber)
                            throw varTmp + " operator requires numeric lengths!";
                        else if (varTmp == "MID" && intCntr == 2 && !varTerm.isStringLiteral)
                            throw varTmp + " operator requires a string input!";
                        //else if (varTmp == "IIF" && intCntr == 2 && !varTerm.isBoolean && !varTerm.isNumber)
                            //throw varTmp + " operator requires boolean condition!";
                        arrArgs[intCntr] = varTerm;
                    }
                    if (varTmp == "MID")
                    {
                        varTerm = arrArgs[2].val.toString();
                        objOp1 = Tokenizer.toNumber(arrArgs[1].val);
                        objOp2 = Tokenizer.toNumber(arrArgs[0].val);
                        pStack.Push(Tokenizer.makeToken(varTerm.substring(objOp1, objOp2),Tokenizer.TOKEN_TYPE.STRING_LITERAL));
                    }
                    else
                    {

                        varTerm = Tokenizer.toBoolean(arrArgs[2].val);

                        if (varTerm)
                        {
                            objOp1 = arrArgs[1];
                        }

                        else
                        {
                            objOp1 = arrArgs[0];
                        }

                        pStack.Push(objOp1);
                    }
                    break;
                case "AVG" :
                case "MAX" :
                case "MIN" :
                    if (arrArgs.length < 1)
                        throw varTmp + " requires at least one operand!";

                    var _arr=[];
                    intCntr = arrArgs.length;
                    while (--intCntr>=0)
                    {
                        varTerm = arrArgs[intCntr];
                        if (varTerm.isVariable)
                        {
                            objTmp = parrVars[varTerm.val];
                            if (objTmp == undefined || objTmp == null)
                                throw "Variable [" + varTerm.val + "] not defined";
                            else
                                varTerm = objTmp;
                        }

                        if( jQuery.isArray( varTerm.val ) )
                        {
                            varTerm.isArray = true;
                        }
                        else if( varTerm.val !== '' || isNaN( +varTerm.val ) === false  )
                        {
                            varTerm.isNumber = true;
                        }

                        if (!varTerm.isNumber && !varTerm.isArray)
                            throw varTmp + " requires numeric or array operands only!";

                        if (!varTerm.isArray)
                            _arr=_arr.concat(Tokenizer.toArray(Tokenizer.toNumber(varTerm.val)));
                        else
                            _arr=_arr.concat(varTerm.val);
                    }
                    intCntr = -1;
                    objTmp = 0;
                    while (++intCntr < _arr.length)
                    {
                        varTerm = _arr[intCntr];
                        if (varTmp == "AVG")
                            objTmp +=  varTerm;
                        else if (varTmp == "MAX")
                        {
                            if (intCntr == 0) 
                                objTmp = varTerm;
                            else if (objTmp < varTerm)
                                objTmp = varTerm;
                        }
                        else if (varTmp == "MIN")
                        {
                            if (intCntr == 0) 
                                objTmp = varTerm;
                            else if (objTmp > varTerm)
                                objTmp = varTerm;
                        }
                    }
                    if (varTmp == "AVG" && _arr.length)
                        pStack.Push(Tokenizer.makeToken(objTmp/_arr.length,Tokenizer.TOKEN_TYPE.NUMBER));
                    else if (varTmp == "AVG")
                        pStack.Push(Tokenizer.makeToken(0,Tokenizer.TOKEN_TYPE.NUMBER));
                    else
                        pStack.Push(Tokenizer.makeToken(objTmp,Tokenizer.TOKEN_TYPE.NUMBER));
                    break;
            }
        };

        /*------------------------------------------------------------------------------
         * NAME       : InFixToPostFix
         * PURPOSE    : Convert an Infix expression into a postfix (RPN) equivalent
         * PARAMETERS : Infix expression element array
         * RETURNS    : array containing postfix expression element tokens
         *----------------------------------------------------------------------------*/
        function _InFixToPostFix(arrToks)
        {
            //console.log('post');
            var myStack;
            var intCntr, intIndex;
            var strTok, strTop, strNext, strPrev;
            var blnStart;

            blnStart = false;
            intIndex = 0;
            arrPFix  = new Array();
            myStack  = new Stack();

            // Infix to postfix converter
            for (intCntr = 0; intCntr < arrToks.length; intCntr++)
            {
                //console.log(arrPFix);
                //console.log(myStack.toString());
                strTok = arrToks[intCntr];
                switch (strTok.type)
                {
                    case Tokenizer.TOKEN_TYPE.LEFT_PAREN :
                        if (myStack.Size() > 0 && myStack.Get(0).isFunction)
                        {
                            arrPFix[intIndex] = Tokenizer.ARG_TERMINAL;
                            intIndex++;
                        }
                        myStack.Push(strTok);
                        break;
                    case Tokenizer.TOKEN_TYPE.RIGHT_PAREN :
                        blnStart = true;
                        while (!myStack.IsEmpty())
                        {
                            strTok = myStack.Pop();
                            if (!strTok.isLeftParen)
                            {
                                arrPFix[intIndex] = strTok;
                                intIndex++;
                            }
                            else
                            {
                                blnStart = false;
                                break;
                            }
                        }
                        if (myStack.IsEmpty() && blnStart)
                            throw "Unbalanced parenthesis!";
                        break;
                    case Tokenizer.TOKEN_TYPE.COMMA :
                        while (!myStack.IsEmpty())
                        {
                            strTok = myStack.Get(0);
                            if (strTok.isLeftParen) break;
                            arrPFix[intIndex] = myStack.Pop();
                            intIndex++;
                        }
                        break;
                    //case Tokenizer.TOKEN_TYPE.UNARY_NEGATIVE :
                    //case Tokenizer.TOKEN_TYPE.UNARY_NEGATION :
                    case Tokenizer.TOKEN_TYPE.ARITHMETIC_OP :
                    case Tokenizer.TOKEN_TYPE.LOGICAL_OP :
                    case Tokenizer.TOKEN_TYPE.COMPARISON_OP :
                        switch (strTok.val)
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
                                if (strTok.val=='-')
                                {
                                    // check for unary negative operator.
                                    strPrev = null;
                                    if (intCntr > 0)
                                        strPrev = arrToks[intCntr - 1];
                                    strNext = arrToks[intCntr + 1];
                                    if (strPrev == null || strPrev.isArithmeticOp || strPrev.isLeftParen || strPrev.isComma)
                                    {
                                        strTok = Tokenizer.UNARY_NEGATIVE;
                                    }
                                }
                                if (strTok.val=='+')
                                {
                                    // check for unary + addition operator, we need to ignore this.
                                    strPrev = null;
                                    if (intCntr > 0)
                                        strPrev = arrToks[intCntr - 1];
                                    strNext = arrToks[intCntr + 1];
                                    if (strPrev == null || strPrev.isArithmeticOp || strPrev.isLeftParen || strPrev.isComma)
                                    {
                                        break;
                                    }
                                }
                                strTop = Tokenizer.EMPTY_TOKEN;
                                if (!myStack.IsEmpty()) strTop = myStack.Get(0);
                                if (myStack.IsEmpty() || (!myStack.IsEmpty() && strTop.isLeftParen))
                                {
                                    myStack.Push(strTok);
                                }
                                else if (strTok.precedence >= strTop.precedence)
                                {
                                    myStack.Push(strTok);
                                }
                                else
                                {
                                    // Pop operators with precedence >= operator strTok
                                    while (!myStack.IsEmpty())
                                    {
                                        strTop = myStack.Get(0);
                                        if (strTop.isLeftParen || strTop.precedence < strTok.precedence)
                                        {
                                            break;
                                        }
                                        else
                                        {
                                            arrPFix[intIndex] = myStack.Pop();
                                            intIndex++;
                                        }
                                    }
                                    myStack.Push(strTok);
                                }
                                break;
                        }
                        break;
                    default :
                        if (strTok.type!=Tokenizer.TOKEN_TYPE.FUNCTION)
                        {
                            arrPFix[intIndex] = strTok;
                            intIndex++;
                        }
                        else
                        {
                            strTop = Tokenizer.EMPTY_TOKEN;
                            if (!myStack.IsEmpty()) strTop = myStack.Get(0);
                            if (myStack.IsEmpty() || (!myStack.IsEmpty() && strTop.isLeftParen))
                            {
                                myStack.Push(strTok);
                            }
                            else if (strTok.precedence >= strTop.precedence)
                            {
                                myStack.Push(strTok);
                            }
                            else
                            {
                                // Pop operators with precedence >= operator in strTok
                                while (!myStack.IsEmpty())
                                {
                                    strTop = myStack.Get(0);
                                    if (strTop.val == "(" || strTop.precedence < strTok.precedence)
                                    {
                                        break;
                                    }
                                    else
                                    {
                                        arrPFix[intIndex] = myStack.Pop();
                                        intIndex++;
                                    }
                                }
                                myStack.Push(strTok);
                            }
                        }
                    break;
                }
            }

            // Pop remaining operators from stack.
            while (!myStack.IsEmpty())
            {
                arrPFix[intIndex] = myStack.Pop();
                intIndex++;
            }
             //console.log(arrPFix);
           return arrPFix;
        };
        
        // public methods
        return (function(Tokenizer,DateParser){
            
            return {
            // delegate here
            setParams : function(params)
            {
                Functions.setParams(params);
            },
            
            Expression : function(pstrExp)
            {
                var strInFix = null;
                var arrVars = [];
                var arrTokens = null;
                var arrPostFix = null;
                var dtFormat = "d/m/Y";
                var thiss=this;
                
                if (typeof(pstrExp)!='undefined' && pstrExp)
                    strInFix=pstrExp;
                    
                // public methods
                this.dateLocales=function(a,b,c){DateParser.setDateLocaleStrings(a,b,c); return thiss;}
                this.dateFormat = function(df){dtFormat = df; return thiss;};
                this.expression = function(exp){strInFix = exp; arrTokens=arrPostFix=null; return thiss;};
                this.addVar = function(_var){_AddNewVariable(_var, arrVars); return thiss;};
                this.parse = function(){arrPostFix=_ParseExpression(); return _dumpPostFix(arrPostFix);};
                this.eval = function(){return _EvaluateExpression(arrPostFix, arrVars);};
                this.reset = function(){arrVars = [];strInFix=arrTokens=arrPostFix=null; return thiss;};
                this.dump = function(){return _dumpPostFix(arrPostFix);};
                this.preCompile = function(_exp){if (typeof(_exp)=='undefined') _exp=strInFix; return _getPrecompiledExpression(_exp);};
                
                function _AddNewVariable(varObj, varArr)
                {
                    if (typeof(varArr)=='undefined')
                        varArr = arrVars;
                        
                    if (varArr == null || varArr == undefined)
                        varArr = new Array();
                    
                    varName=varObj.name;
                    varToken=null;
                    if (varObj.withType)
                    {
                        switch(varObj.withType)
                        {
                            case 'boolean':
                                varToken=Tokenizer.makeToken(varObj.val,Tokenizer.TOKEN_TYPE.BOOLEAN); //Tokenizer.toBoolean(varObj.val);
                                break;
                            case 'number':
                                varToken=Tokenizer.makeToken(varObj.val,Tokenizer.TOKEN_TYPE.NUMBER); //Tokenizer.toNumber(varObj.val);
                                break;
                            case 'array':
                                varToken=Tokenizer.makeToken(varObj.val,Tokenizer.TOKEN_TYPE.ARRAY); //Tokenizer.toArray(varObj.val);
                                break;
                            case 'date':
                                var format;
                                if (varObj.format)
                                    format=varObj.format;
                                else
                                    format=dtFormat;
                                //varValue=DateParser.parseDate(varObj.val, format);
                                varToken=Tokenizer.makeToken(DateParser.parseDate(varObj.val, format),Tokenizer.TOKEN_TYPE.DATE); //
                                break;
                            case 'string':
                            default:
                                varToken=Tokenizer.makeToken(varObj.val,Tokenizer.TOKEN_TYPE.STRING_LITERAL); //
                                break;
                        }
                    }
                    else
                        varToken=Tokenizer.makeToken(varObj.val,Tokenizer.TOKEN_TYPE.STRING_LITERAL); //                    
                    
                    varArr[varName] = varToken;
                }

                function _dumpPostFix(pf)
                {
                    var out='';
                    for (var i=0; i<pf.length; i++)
                        out+=pf[i].val+',';
                    return out;
                }
                
                function _clonePostFix(pf)
                {
                    var newpf=new Array(pf.length);
                    for (var i=0; i<pf.length; i++)
                        newpf[i]=Tokenizer.cloneToken(pf[i]);
                    return newpf;
                }
                
                function _createFunction(myarrPostFix)
                {
                        return function(vars)
                        {
                            // init internal closure vars
                            var myvarsArray=[];
                            // add user vars at run-time
                            if (typeof(vars)!='undefined' && vars)
                            {
                                for (var i=0; i<vars.length; i++)
                                    _AddNewVariable(vars[i], myvarsArray);
                            }   
                            //console.log(_dumpPostFix(myarrPostFix));
                            // return evaluated result
                            return _EvaluateExpression(_clonePostFix(myarrPostFix), myvarsArray);
                        }
                }
                
                function _getPrecompiledExpression(exp)
                {
                    var myarrTokens = Tokenizer.Tokanize(exp);
                    if (myarrTokens == null || myarrTokens == undefined)
                        throw "Unable to tokanize the expression!";
                    if (myarrTokens.length <= 0)
                        throw "Unable to tokanize the expression!";

                    var myarrPostFix0 = _InFixToPostFix(myarrTokens);
                    if (myarrPostFix0 == null || myarrPostFix0 == undefined)
                        throw "Unable to convert the expression to postfix form!";
                    if (myarrPostFix0.length <= 0)
                        throw "Unable to convert the expression to postfix form!";
                    
                    // return precompiled dynamic function
                    return _createFunction(myarrPostFix0);
                }
                
                function _ParseExpression()
                {
                    arrTokens = Tokenizer.Tokanize(strInFix);
                    if (arrTokens == null || arrTokens == undefined)
                        throw "Unable to tokanize the expression!";
                    if (arrTokens.length <= 0)
                        throw "Unable to tokanize the expression!";

                    var myarrPostFix = _InFixToPostFix(arrTokens);
                    if (myarrPostFix == null || myarrPostFix == undefined)
                        throw "Unable to convert the expression to postfix form!";
                    if (myarrPostFix.length <= 0)
                        throw "Unable to convert the expression to postfix form!";
                    return myarrPostFix;
                }
                
                function _getVariable(strVarName, varArr)
                {
                    var retVal;
                    
                    if (typeof(varArr)=='undefined')
                        varArr=arrVars;
                    
                    if (varArr == null || varArr == undefined)
                        throw "Variable values are not supplied!";

                    retVal = varArr[strVarName];
                    if (typeof(varArr[strVarName])=='undefined' || retVal == undefined || retVal == null)
                        throw "Variable [" + strVarName + "] not defined";
                    return retVal;
                }

                // postfix function evaluator
                function _EvaluateExpression(myarrPostFix, myvarArr)
                {

                    var intIndex;
                    var myStack;
                    var strTok, strOp;
                    var objOp1, objOp2, objTmp1, objTmp2;
                    var dblNo, dblVal1, dblVal2;

                    if (myarrPostFix == null || myarrPostFix == undefined)
                        myarrPostFix=_ParseExpression();
                    if (myarrPostFix.length == 0)
                        throw "Unable to parse the expression!";
                    if (myarrPostFix == null || myarrPostFix == undefined || myarrPostFix.length == 0)
                    {
                        throw "Invalid postfix expression!";
                        return;
                    }

                    intIndex = 0;
                    myStack  =  new Stack();
                    //console.log(myarrPostFix);
                    while (intIndex < myarrPostFix.length)
                    {
                        //console.log(myStack.toString());
                        strTok = myarrPostFix[intIndex];
                        switch (strTok.type)
                        {
                            case Tokenizer.TOKEN_TYPE.ARG_TERMINAL :
                                myStack.Push(strTok);
                                break;
                            case Tokenizer.TOKEN_TYPE.UNARY_NEGATIVE :
                                if (myStack.IsEmpty())
                                    throw "No operand to negate!";

                                objOp1 = null;
                                objOp2 = null;
                                objOp1 = myStack.Pop();
                                if (objOp1.isVariable)
                                    objOp1 = _getVariable(objOp1.val, myvarArr);

                                dblNo = Tokenizer.toNumber(objOp1.val);
                                if (isNaN(dblNo))
                                    throw "Not a numeric value!";
                                else
                                {
                                    dblNo = (0 - dblNo);
                                    myStack.Push(Tokenizer.makeToken(dblNo,Tokenizer.TOKEN_TYPE.NUMBER));
                                }
                                break;
                            case Tokenizer.TOKEN_TYPE.UNARY_NEGATION :
                                if (myStack.IsEmpty())
                                    throw "No operand on stack!";

                                objOp1 = null;
                                objOp2 = null;
                                objOp1 = myStack.Pop();
                                if (objOp1.isVariable)
                                    objOp1 = _getVariable(objOp1.val, myvarArr);

                                objOp1 = Tokenizer.toBoolean(objOp1.val);
                                if (objOp1 == null)
                                    throw strTok.val + " applied not on a boolean value!";
                                else
                                    myStack.Push(Tokenizer.makeToken(!(objOp1),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                break;
                            case Tokenizer.TOKEN_TYPE.ARITHMETIC_OP :
                                switch(strTok.val)
                                {
                                    case "*" :
                                    case "/" :
                                    case "%" :
                                    case "^" :
                                        if (myStack.IsEmpty() || myStack.Size() < 2)
                                            throw "Stack is empty, can not perform [" + strTok.val + "]";
                                        objOp1 = null;
                                        objOp2 = null;
                                        objTmp = null;
                                        objOp2 = myStack.Pop();
                                        objOp1 = myStack.Pop();
                                        if (objOp1.isVariable)
                                            objOp1 = _getVariable(objOp1.val, myvarArr);
                                        if (objOp2.isVariable)
                                            objOp2 = _getVariable(objOp2.val, myvarArr);

                                        if (!objOp1.iNumber || !objOp2.isNumber)
                                            throw "Either one of the operand is not a number can not perform [" + strTok.val + "]";
                                            
                                        dblVal1 = Tokenizer.toNumber(objOp1.val);
                                        dblVal2 = Tokenizer.toNumber(objOp2.val);
                                        if (isNaN(dblVal1) || isNaN(dblVal2))
                                            throw "Either one of the operand is not a number can not perform [" + strTok.val + "]";
                                            
                                        if (strTok.val == "^")
                                            myStack.Push(Tokenizer.makeToken(Math.pow(dblVal1, dblVal2),Tokenizer.TOKEN_TYPE.NUMBER));
                                        else if (strTok.val == "*")
                                            myStack.Push(Tokenizer.makeToken((dblVal1 * dblVal2),Tokenizer.TOKEN_TYPE.NUMBER));
                                        else if (strTok.val == "/")
                                            myStack.Push(Tokenizer.makeToken((dblVal1 / dblVal2),Tokenizer.TOKEN_TYPE.NUMBER));
                                        else
                                            myStack.Push(Tokenizer.makeToken((dblVal1 % dblVal2),Tokenizer.TOKEN_TYPE.NUMBER));
                                        break;
                                    case "+" :
                                    case "-" :
                                        if (myStack.IsEmpty() || myStack.Size() < 2)
                                            throw "Stack is empty, can not perform [" + strTok.val + "]";
                                            
                                        objOp1 = null;
                                        objOp2 = null;
                                        objTmp1 = null;
                                        objTmp2 = null;
                                        strOp = ((strTok.val == "+") ? "Addition" : "Substraction");
                                        objOp2 = myStack.Pop();
                                        objOp1 = myStack.Pop();
                                        if (objOp1.isVariable)
                                            objOp1 = _getVariable(objOp1.val, myvarArr);
                                        if (objOp2.isVariable)
                                            objOp2 = _getVariable(objOp2.val, myvarArr);

                                        if (objOp1.isNumber && objOp2.isNumber)
                                        {
                                            // Number addition
                                            dblVal1 = Tokenizer.toNumber(objOp1.val);
                                            dblVal2 = Tokenizer.toNumber(objOp2.val);
                                            if (strTok.val == "+")
                                                myStack.Push(Tokenizer.makeToken((dblVal1 + dblVal2),Tokenizer.TOKEN_TYPE.NUMBER));
                                            else
                                                myStack.Push(Tokenizer.makeToken((dblVal1 - dblVal2),Tokenizer.TOKEN_TYPE.NUMBER));
                                        }
                                        else if (objOp1.isStringLiteral && objOp2.isStringLiteral)
                                        {
                                            if (strTok.val == "+")
                                                myStack.Push(Tokenizer.makeToken((objOp1.val + objOp2.val),Tokenizer.TOKEN_TYPE.STRING_LITERAL));
                                            else
                                                throw strOp + " not supported for strings!"
                                        }
                                        else
                                            throw strOp + " not supported for other types than numbers and strings!"
                                        break;
                                }
                                break;
                            case Tokenizer.TOKEN_TYPE.COMPARISON_OP :
                                switch(strTok.val)
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
                                        if (myStack.IsEmpty() || myStack.Size() < 2)
                                            throw "Stack is empty, can not perform [" + strTok.val + "]";
                                        objOp1  = null;
                                        objOp2  = null;
                                        objTmp1 = null;
                                        objTmp2 = null;
                                        objOp2  = myStack.Pop();
                                        objOp1  = myStack.Pop();
                                        
                                        if (objOp1.isVariable)
                                            objOp1 = _getVariable(objOp1.val, myvarArr);
                                        if (objOp2.isVariable)
                                            objOp2 = _getVariable(objOp2.val, myvarArr);

                                        if (objOp1.isStringLiteral && objOp2.isNumber)
                                        {                                            
                                            dblVal1 = objOp1.val.toString();
                                            dblVal2 = objOp2.val.toString();
                                        }
                                        else if (objOp1.isNumber && objOp2.isStringLiteral)
                                        {
                                            dblVal1 = objOp1.val.toString();
                                            dblVal2 = objOp2.val.toString();
                                        }
                                        else if (objOp1.isNumber && objOp2.isNumber)
                                        {                                            
                                            dblVal1 = Tokenizer.toNumber(objOp1.val);
                                            dblVal2 = Tokenizer.toNumber(objOp2.val);
                                        }
                                        else if (objOp1.isNumber && objOp2.isBoolean)
                                        {                                            
                                            dblVal1 = Tokenizer.toNumber(objOp1.val);
                                            dblVal2 = Tokenizer.toNumber(objOp2.val);
                                        }
                                        else if (objOp2.isNumber && objOp1.isBoolean)
                                        {
                                            dblVal1 = Tokenizer.toNumber(objOp1.val);
                                            dblVal2 = Tokenizer.toNumber(objOp2.val);
                                        }
                                        else if (objOp1.isDate && objOp2.isDate)
                                        {
                                            dblVal1 = objOp1.val.getTime();
                                            dblVal2 = objOp2.val.getTime();
                                        }
                                        else if (objOp1.isStringLiteral && objOp2.isStringLiteral)
                                        {
                                            dblVal1=objOp1.val.toString();
                                            dblVal2=objOp2.val.toString();
                                            /*
											if (!isNaN(dblVal1))
                                            {
                                             dblVal1=parseFloat(dblVal1);
                                            }
                                            if (!isNaN(dblVal2))
                                            {
                                             dblVal2=parseFloat(dblVal2);
                                            }
                                            dblVal1=parseFloat(objOp1.val.toString());
                                            dblVal2=parseFloat(objOp2.val.toString());
											*/
                                        }
                                        else if (objOp1.isBoolean && objOp2.isBoolean)
                                        {
                                            if (strTok.val == "=" || strTok.val == "<>" || strTok.val == "eq" || strTok.val == "ne")
                                            {
                                                dblVal1 = Tokenizer.toBoolean(objOp1.val);
                                                dblVal2 = Tokenizer.toBoolean(objOp2.val);
                                            }
                                            else
                                                throw strTok.val + " not supported for boolean values!";
                                        }
                                        else if (
                                            (strTok.val=='=' || strTok.val=='<>' || strTok.val=='eq' || strTok.val=='ne') &&
                                            (objOp1.isStringLiteral && objOp2.isRegex)
                                        )
                                        {
                                            if (strTok.val=='=' || strTok.val=='eq')
                                                myStack.Push(Tokenizer.makeToken((objOp2.val.test(objOp1.val.toString())),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                            else
                                                myStack.Push(Tokenizer.makeToken(!(objOp2.val.test(objOp1.val.toString())),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                            break;
                                        }
                                        else if (
                                            (strTok.val=='=' || strTok.val=='<>' || strTok.val=='eq' || strTok.val=='ne') &&
                                            (objOp2.isStringLiteral && objOp1.isRegex)
                                        )
                                        {
                                            if (strTok.val=='=' || strTok.val=='eq')
                                                myStack.Push(Tokenizer.makeToken((objOp1.val.test(objOp2.val.toString())),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                            else
                                                myStack.Push(Tokenizer.makeToken(!(objOp1.val.test(objOp2.val.toString())),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                            break;
                                        }
                                        else if (
                                            (strTok.val=='=' || strTok.val=='<>' || strTok.val=='eq' || strTok.val=='ne') &&
                                            (objOp1.isArray && (objOp2.isStringLiteral || objOp2.isNumber))
                                        )
                                        {
                                            if (strTok.val=='=' || strTok.val=='eq')
                                                myStack.Push(Tokenizer.makeToken(Functions.Contains(objOp1.val,objOp2.val),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                            else
                                                myStack.Push(Tokenizer.makeToken(!Functions.Contains(objOp1.val,objOp2.val),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                            break;
                                        }
                                        else if (
                                            (strTok.val=='=' || strTok.val=='<>' || strTok.val=='eq' || strTok.val=='ne') &&
                                            (objOp2.isArray && (objOp1.isStringLiteral || objOp1.isNumber))
                                        )
                                        {
                                            if (strTok.val=='=' || strTok.val=='eq')
                                                myStack.Push(Tokenizer.makeToken(Functions.Contains(objOp2.val,objOp1.val),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                            else
                                                myStack.Push(Tokenizer.makeToken(!Functions.Contains(objOp2.val,objOp1.val),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                            break;
                                        }
                                        else
                                            throw "For " + strTok.val + " operator LHS & RHS should be of same data type!";
                                        
                                        if (strTok.val=='=' || strTok.val=='eq')// TODO check here, might need to use === instead of ==
                                            myStack.Push(Tokenizer.makeToken((dblVal1 == dblVal2),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                        else if (strTok.val == "<>" || strTok.val == "ne")// TODO check here, might need to use !== instead of !=
                                            myStack.Push(Tokenizer.makeToken((dblVal1 != dblVal2),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                        else if (strTok.val == ">" || strTok.val == "gt")
                                            myStack.Push(Tokenizer.makeToken((dblVal1 > dblVal2),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                        else if (strTok.val == "<" || strTok.val == "lt")
                                            myStack.Push(Tokenizer.makeToken((dblVal1 < dblVal2),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                        else if (strTok.val == "<=" || strTok.val == "lte")
                                            myStack.Push(Tokenizer.makeToken((dblVal1 <= dblVal2),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                        else if (strTok.val == ">=" || strTok.val == "gte")
                                            myStack.Push(Tokenizer.makeToken((dblVal1 >= dblVal2),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                        break;
                                }
                                break;
                            case Tokenizer.TOKEN_TYPE.LOGICAL_OP :
                                switch(strTok.val)
                                {
                                    case 'NOT' :
                                    case '!' :
                                        if (myStack.IsEmpty())
                                            throw "No operand on stack!";

                                        objOp1 = null;
                                        objOp2 = null;
                                        objOp1 = myStack.Pop();
                                        if (objOp1.isVariable)
                                            objOp1 = _getVariable(objOp1.val, myvarArr);

                                        objOp1 = Tokenizer.toBoolean(objOp1.val);
                                        if (objOp1 == null)
                                            throw strTok.val + " applied not on a boolean value!";
                                        else
                                            myStack.Push(Tokenizer.makeToken(!(objOp1),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                        break;
                                    case "AND" :
                                    case "&" :
                                    case "OR" :
                                    case "|" :
                                        if (myStack.IsEmpty() || myStack.Size() < 2)
                                            throw "Stack is empty, can not perform [" + strTok.val + "]";
                                        objOp1  = null;
                                        objOp2  = null;
                                        objTmp1 = null;
                                        objTmp2 = null;
                                        objOp2  = myStack.Pop();
                                        objOp1  = myStack.Pop();
                                        if (objOp1.isVariable)
                                            objOp1 = _getVariable(objOp1.val, myvarArr);
                                        if (objOp2.isVariable)
                                            objOp2 = _getVariable(objOp2.val, myvarArr);

                                        if (
                                            (objOp1.isBoolean && objOp2.isBoolean) || 
                                            (objOp1.isNumber && objOp2.isNumber) || 
                                            (objOp1.isNumber && objOp2.isBoolean) || 
                                            (objOp1.isBoolean && objOp2.isNumber) 
                                            )
                                        {
                                            objTmp1 = Tokenizer.toBoolean(objOp1.val);
                                            objTmp2 = Tokenizer.toBoolean(objOp2.val);
                                            if (strTok.val == "AND"  || strTok.val == "&")
                                                myStack.Push(Tokenizer.makeToken((objTmp1 && objTmp2),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                            else if (strTok.val == "OR" || strTok.val == "|")
                                                myStack.Push(Tokenizer.makeToken((objTmp1 || objTmp2),Tokenizer.TOKEN_TYPE.BOOLEAN));
                                        }
                                        else
                                            throw "Logical operator requires LHS & RHS of boolean type!";
                                        break;
                                }
                                break;
                            case Tokenizer.TOKEN_TYPE.FUNCTION :
                                _HandleFunctions(strTok, myStack, dtFormat, myvarArr);
                                break;
                            default :
                                myStack.Push(strTok);
                                break;
                        }
                        intIndex++;
                    }
                    if (myStack.IsEmpty() || myStack.Size() > 1 || myStack.Get(0).isVariable)
                        throw "Unable to evaluate expression!";
                    else
                        return myStack.Pop().val;
                }
            }
        };})(Tokenizer,DateParser);
    })(window);


})(window);