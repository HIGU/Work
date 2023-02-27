<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器掲示板用ライブラリクラス                                     //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/07/15 Created  applibs.php                                          //
// 2006/08/29 リッチテキストファイル対応のため class CGI_Request の post    //
//            メソッドをサニタイズしないに初期値を変更                      //
// 2007/09/18 Data::decData() と Data_Model::decData($line)と互換性確保の為 //
//            Data::decData($line) へ変更 (E_STRICT でログ出力)             //
//////////////////////////////////////////////////////////////////////////////
define("APP_ACTION_ARG","m");
define("APP_VIEW_DIR","view/");
define("APP_ACTION_DIR","action/");
define("APP_TEMPLATE_DIR","view/template/");
define("APP_TEMPLATE_DIR_E","view/template_e/");
define("APP_TEMPLATE_DIR_I","view/template_i/");
define("APP_TEMPLATE_DIR_J","view/template_j/");
define("APP_DEFAULT_VIEW", "default"."View");
define("APP_DEFAULT_ACTION","default"."Action");
define("APP_DEFAULT_VIEW_FILE", APP_VIEW_DIR.APP_DEFAULT_VIEW.".php");
define("APP_DEFAULT_ACTION_FILE",APP_ACTION_DIR.APP_DEFAULT_ACTION.".php");
define("APP_ERROR_VIEW", "error"."View");
define("APP_ERROR_ACTION","error"."Action");
define("APP_ERROR_VIEW_FILE", APP_VIEW_DIR.APP_ERROR_VIEW.".php");
define("APP_ERROR_ACTION_FILE",APP_ACTION_DIR.APP_ERROR_ACTION.".php");
if (isset($HTTP_SERVER_VARS["PHP_SELF"])) {
    define("APP_FILENAME", $HTTP_SERVER_VARS["PHP_SELF"]);
} elseif (isset($_SERVER["PHP_SELF"])) {
    define("APP_FILENAME", $_SERVER["PHP_SELF"]);
} else {
    define("APP_FILENAME", getenv("PHP_SELF"));
}
define("APP_DIR", dirname(APP_FILENAME)."/");

class Action
{
    function dispatch(&$context)
    {
    }
}
class Action_Manager
{
    function dispatch(&$context, $target="")
    {
        $cgi = $context->getCgi();
        if ($target =="") {
            $target = $cgi->param(APP_ACTION_ARG);
        }
        if ($target !="") {
            $actionFile = APP_ACTION_DIR.$target."Action.php";
            $actionClass = $target."Action";
        }
        if ($actionFile !="" && is_readable($actionFile)&& is_file($actionFile)) {
            include_once($actionFile);
        } elseif (is_readable(APP_DEFAULT_ACTION_FILE)&& is_file(APP_DEFAULT_ACTION_FILE)) {
            include_once(APP_DEFAULT_ACTION_FILE);
        }
        if (class_exists($actionClass)) {
            $o =new $actionClass;
        } elseif (class_exists(APP_DEFAULT_ACTION)) {
            $act =APP_DEFAULT_ACTION;
            $o = new $act;
        }
        if (method_exists($o,"dispatch")) {
            return $o->dispatch($context);
        }
    }
}
define("AUTH_NAME","ADMIN LOGIN");
class Auth_Basic
{
    var $_users = array();
    var $_cgi;
    
    function Auth_Basic($cgi, $arg)
    {
        if (is_array($arg)) {
            $this->_users = $arg;
        }
        $this->_cgi = $cgi;
    }
    function authHeader()
    {
        header('WWW-Authenticate: Basic realm="'.AUTH_NAME.'"');
        header('HTTP/1.0 401 Unauthorized');
    }
    function execute()
    {
        $cgi  =& $this->_cgi;
        $user = $cgi->env("PHP_AUTH_USER");
        $pass = $cgi->env("PHP_AUTH_PW");
        if ($user =="") {
            $this->authHeader();
            return FALSE;
        } else {
            if (array_key_exists($user,$this->_users)&& $this->_users[$user] ==$pass) {
                return TRUE;
            } else {
                $this->authHeader();
                return FALSE;
            }
        }
    }
}
class Auth_Param
{
    var $_users = array();
    var $_cgi;
    function Auth_Param($cgi, $arg)
    {
        if (is_array($arg)) {
            $this->_users = $arg;
        }
        $this->_cgi = $cgi;
    }
    function execute()
    {
        $cgi =& $this->_cgi;
        $user = $cgi->post("user");
        $pass = $cgi->post("pass");
        if ($user =="") {
            return FALSE;
        } else {
            if (array_key_exists($user,$this->_users)&& $this->_users[$user] ==$pass) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
    }
}
class CGI_Request
{
    var $p,$g,$e,$s,$c,$cookie;
    var $mq;
    function CGI_Request()
    {
        global $HTTP_POST_VARS, $HTTP_GET_VARS, $HTTP_ENV_VARS, $HTTP_SERVER_VARS;
        if (isset($HTTP_POST_VARS)) {
            $this->p =& $HTTP_POST_VARS;
        } elseif(isset($_POST)) {
            $this->p =& $_POST;
        }
        if (isset($HTTP_GET_VARS)) {
            $this->g =& $HTTP_GET_VARS;
        } elseif (isset($_GET)) {
            $this->g =& $_GET;
        }
        if (isset($HTTP_ENV_VARS)) {
        $this->e =& $HTTP_ENV_VARS;
        } elseif (isset($_ENV)) {
            $this->e =& $_ENV;
        } if (isset($HTTP_SERVER_VARS)) {
            $this->s =& $HTTP_SERVER_VARS;
        } elseif (isset($_SERVER)) {
            $this->s =& $_SERVER;
        }
        $this->prm = array_merge($this->g,$this->p);
        $this->env = array_merge($this->e,$this->s);
        $this->c = new CGI_Cookie;
        $this->cookie =& $this->c;
        $this->mq = get_magic_quotes_gpc();
    }
    function get($key, $sanitize=1)
    {
        $tmp = $this->g[$key];
        if ($this->mq) {
            $tmp = stripslashes($tmp);
        }
        if ($sanitize ==0) {
            return $tmp;
        } else {
            return htmlspecialchars($tmp);
        }
    }
    function post($key, $sanitize=0)    // 2006/08/29 $sanitize=1 → 0 へ
    {
        $tmp = $this->p[$key];
        if ($this->mq) {
            $tmp =stripslashes($tmp);
        }
        if ($sanitize == 0) {
            return $tmp;
        } else {
            return htmlspecialchars($tmp);
        }
    }
    function param($key, $sanitize=1)
    {
        $tmp = $this->prm[$key];
        if ($this->mq) {
            $tmp =stripslashes($tmp);
        }
        if ($sanitize == 0) {
            return $tmp;
        } else {
            return htmlspecialchars($tmp);
        }
    }
    function env($key, $sanitize=1)
    {
        if ($sanitize == 0){
            return $this->env[$key];
        } else {
            return htmlspecialchars($this->env[$key]);
        }
    }
    function file($key)
    {
        if (isset($HTTP_POST_FILES)) {
            return $HTTP_POST_FILES[$key];
        } elseif (isset($_FILES)) {
            return $_FILES[$key];
        }
    }
    function saveUpFile($name, $savepath)
    {
        if ($savepath == "")return;
        $tmp = $this->file($name);
        move_uploaded_file($tmp["tmp_name"],$savepath);
    }
    function randomId($len=16)
    {
        srand((double)microtime()* 1000000);
        $chars = array_merge(range(0,9),range('a','z'),range('A','Z'));
        for ($i=0;$i<$len;$i++) {
            $ret .= $chars[rand(0,count($chars))];
        }
        return $ret;
    }
    function uniqId()
    {
        return uniqid("",true);
    }
    function getAgent()
    {
        if (isset($HTTP_SERVER_VARS)) {
            $ag =$HTTP_SERVER_VARS['HTTP_USER_AGENT'];
        } elseif (isset($_SERVER)) {
            $ag = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $ag = getenv('HTTP_USER_AGENT');
        }
        if (preg_match("/DoCoMo/i",$ag)) {
            return "i";
        } elseif (preg_match("/J-PHONE/i",$ag)) {
            return "j";
        } elseif (preg_match("/UP\.Browser/i",$ag)) {
            return "e";
        } else {
            return "p";
        }
    }
}
class CGI_Cookie
{
    var $_expire;
    var $_hash = array();
    var $_cook = array();
    function CGI_Cookie()
    {
        global $_COOKIE,$HTTP_COOKIE_VARS;
        if (isset($HTTP_COOKIE_VARS)) {
            $this->_cook =$HTTP_COOKIE_VARS;
        } elseif (isset($_COOKIE)) {
            $this->_cook = $_COOKIE;
        }
    }
    function get($name)
    {
        if(isset($this->_cook[$name])) {
            return $this->_cook[$name];
        } elseif ($this->_hash[$name] !="") {
            return $this->_hash[$name];
        } else {
            return "";
        }
    }
    function set($name, $value)
    {
        $this->_hash[$name] = $value;
        $tmp ="_".$name;
        $this->$tmp = $value;
        if ($this->_expire !="") {
            $flg =setcookie($name,$value,$this->_expire);
        } else {
            $flg =setcookie($name,$value);
        }
        if ($flg ==FALSE) {
            trigger_error("クッキーが設定できません");
        }
    }
    function setExpire($value)
    {
        $this->_expire =$value;
    }
    function getExpire()
    {
        return $this->_expire;
    }
}
class CGI_Querystring
{
    var $args =array();
    function CGI_Querystring($flag=0)
    {
        if ($flag != 0)return;
        $qs = getenv("QUERY_STRING");
        $l = preg_split("&",$qs);
        for ($i=0; $i<count($l); $i++) {
            list($key, $value) = preg_split("=",$l[$i]);
            $this->add($key, $value);
        }
    }
    function add($name,$value)
    {
        $this->args[$name] = $value;
    }
    function toString()
    {
        $tmpar = array();
        foreach ($this->args as $key => $value) {
            array_push($tmpar,$key."=".$value);
        }
        if (count($tmpar) == 0)return ""; else return "?".join("&",$tmpar);
    }
    function get($key)
    {
        return $this->args[$name];
    }
    function delete($key)
    {
        $this->args[$name] ="";
    }
}
class Context
{
    var $_cgi;
    var $_hash;
    function Context()
    {
        $this->_cgi = new CGI_Request;
    }
    function &getCgi()
    {
        return $this->_cgi;
    }
    function set($name,$value)
    {
        $this->_hash[$name] = $value;
    }
    function get($name)
    {
        return $this->_hash[$name];
    }
}

class Ctrl_File_Data
{
    var $_datafile;
    var $_datatype;
    function Ctrl_File_Data($datatype, $file)
    {
        if ($datatype =="" && !class_exists($datatype)) {
            trigger_error("データタイプが指定されていません。または不正です");
        }
        $this->_datatype = $datatype;
        $this->_datafile = new File_Data($file);
    }
    function getFileData()
    {
        return $this->_datafile;
    }
    function find($query)
    {
        if (strtolower(get_class($query)) != "data_query") {
            trigger_error("wrong query");
        }
        $hash = $query->getHash();
        $in = $this->_datafile->get();
        for ($i=0; $i<count($in); $i++) {
            $obj = new $this->_datatype;
            $obj->decData($in[$i]);
            $flg = 0;
            foreach ($hash as $key=>$value) {
                $tmp = $obj->get($key);
                if ($tmp == $value) {
                    $flg =1;
                } else {
                    $flg =0;
                    break;
                }
            }
            if ($flg ==1) {
                return $obj;
            }
        }
        return "";
    }
    function find_r($query)
    {
        if (strtolower(get_class($query)) != "data_query") {
            trigger_error("wrong query");
        }
        $hash = $query->getHash();
        $in = $this->_datafile->get();
        for ($i=0; $i<count($in); $i++) {
            $obj = new $this->_datatype;
            $obj->decData($in[$i]);
            $flg =0;
            foreach ($hash as $key=>$value) {
                $tmp = $obj->get($key);
                if (preg_match("/$value/",$tmp)) {
                    $flg =1;
                } else {
                    $flg = 0;
                    break;
                }
            }
            if ($flg ==1) {
                return $obj;
            }
        }
        return "";
    }
    function findall($query)
    {
        if (strtolower(get_class($query)) != "data_query") {
            trigger_error("wrong query");
        }
        $hash = $query->getHash();
        $start = $query->getStart();
        $limit = $query->getLength();
        $ar = array();
        $in = $this->_datafile->get();
        for ($i=0; $i<count($in); $i++) {
            if ($i < $start)continue;
            $obj = new $this->_datatype;
            $obj->decData($in[$i]);
            if ($limit !="" && count($ar)> $limit)return $ar;
            $flg =0;
            foreach ($hash as $key=>$value) {
                $tmp =$obj->get($key);
                if ($tmp ==$value) {
                    $flg =1;
                } else {
                    $flg =0;
                    break;
                }
            }
            if ($flg ==1) {
                array_push($ar,$obj);
            }
        }
        return $ar;
    }
    function findall_r($query)
    {
        if (strtolower(get_class($query)) != "data_query") {
            trigger_error("wrong query");
        }
        $hash = $query->getHash();
        $start = $query->getStart();
        $limit = $query->getLength();
        $ar = array();
        $in = $this->_datafile->get();
        for ($i=0; $i<count($in); $i++) {
            if ($i < $start)continue;
            $obj = new $this->_datatype;
            $obj->decData($in[$i]);
            if ($limit !="" && count($ar) > $limit)return $ar;
            $flg =0;
            foreach ($hash as $key=>$value) {
                $tmp = $obj->get($key);
                if (preg_match("/$value/",$tmp)) {
                    $flg =1;
                } else {
                    $flg = 0;
                    break;
                }
            }
            if ($flg ==1) {
                array_push($ar,$obj);
            }
        }
        return $ar;
    }
    function get($query="")
    {
        if (strtolower(get_class($query)) == "data_query") {
            $start = $query->getStart();
            $limit = $query->getLength();
        }
        if ($start < 0) {
            $start =0;
        }
        if (isset($limit)) {
            $in = $this->_datafile->slice($start,$limit);
        } else {
            $in = $this->_datafile->get();
        }
        $obar = array();
        for ($i=0; $i<count($in); $i++) {
            $obj = new $this->_datatype;
            $obj->decData($in[$i]);
            array_push($obar,$obj);
        }
        return $obar;
    }
    function delete($tgtobj)
    {
        $in = $this->_datafile->get();
        $newar = array();
        $flg = 0;
        for ($i=0; $i<count($in); $i++) {
            $obj = new $this->_datatype;
            $obj->decData($in[$i]);
            $tmp = $obj->getPrimaryKeyName();
            if ( $obj->get($tmp) == $tgtobj->get($tmp)) {
                $flg =1;
            } else {
                array_push($newar,$in[$i]);
            }
        }
        $this->_datafile->overwrite($newar);
        return $flg;
    }
    function update($tgtobj)
    {
        $in = $this->_datafile->get();
        $newar = array();
        $flg = 0;
        for ($i=0; $i<count($in); $i++) {
            $obj = new $this->_datatype;
            $obj->decData($in[$i]);
            $tmp = $obj->getPrimaryKeyName();
            if ( $obj->get($tmp) == $tgtobj->get($tmp)) {
                array_push($newar,$tgtobj->encData());
                $flg = 1;
            } else {
                array_push($newar,$in[$i]);
            }
        }
        $this->_datafile->overwrite($newar);
        return $flg;
    }
    function insert($tgtobj,$index="last")
    {
        if ($index =="last") {
            $this->_datafile->writeAdd($tgtobj->encData());
        } elseif ($index =="first") {
            $this->_datafile->writeIndex($tgtobj->encData(),0);
        } else {
            $this->_datafile->writeIndex($tgtobj->encData(),$index);
        }
    }
}

class Data
{
    var $_hash = array();
    function Data(){}
    function get($name)
    {
        return $this->_hash[$name];
    }
    function set($name, $value)
    {
        $this->_hash[$name] = $value;
        $tmp = "_".$name;
        $this->$tmp = $value;
    }
    function encData(){}
    function decData($line){}
}

class Data_Model extends Data
{
    var $_format = array();
    function Data_Model($outdata="")
    {
        $this->set("delim","<>");
        $format = $this->_format;
        if (strtolower(get_class($outdata)) == "cgi_request") {
            foreach ($format as $key => $value) {
                switch ($value[5]) {
                case "post":
                    $tmp = $outdata->post($key);
                    break;
                case "get":
                    $tmp = $outdata->get($key);
                    break;
                case "file":
                    $tmp = $outdata->file($key);
                    break;
                case "env":
                    $tmp = $outdata->env($key);
                    break;
                default:
                    $tmp = $outdata->param($key);
                }
                $func_conv = "cnv_".$value[2];
                if (method_exists($this,$func_conv)) {
                    $tmp = call_user_method($func_conv,$this,$tmp);
                }
                if ($tmp == "" && $value[4] != "") {
                    $this->set($key,$value[4]);
                } else {
                    $this->set($key,$tmp);
                }
            }
        }
    }
    function getPrimaryKeyName()
    {
        $format = $this->_format;
        $tmp = key($format);
        return $tmp;
    }
    function encData()
    {
        $format = $this->_format;
        $ar = array();
        foreach ($format as $key => $value) { 
            array_push($ar,$this->get($key));
        }
        $tmp = join($this->get("delim"),$ar)."\n";
        return $tmp;
    }
    function encDataSQL($table)
    {
        $line = $this->encData();
        $line = preg_replace("\r|\n","",$line);
        $this->sqlQuote($line);
        $line = "'".str_replace($this->get("delim"),"','",$line)."'";
        $sql = "insert into $table values($line)";
        return $sql;
    }
    function decData($line)
    {
        $format = $this->_format;
        $line = preg_replace("\r|\n","",$line);
        $ar = preg_split($this->get("delim"),$line);
        $i = 0;
        foreach ($format as $key => $value) {
            $this->set($key,$ar[$i]);
            $i++;
        }
    }
    function decDataSQL($row)
    {
        $keys =array_keys($this->_format);
        for ($i=0; $i<count($row); $i++) {
            $data = $row[$i];
            $key = $keys[$i];
            $this->set($key,$data);
        }
    }
    function sqlSelect($table, $key="", $value="")
    {
        $where = $this->sqlWhere($key,$value);
        $sql = "select * from $table where $where";
        return $sql;
    }
    function sqlInsert($table)
    {
        return $this->encDataSQL($table);
    }
    function sqlUpdate($table, $array, $key="", $value="")
    {
        $ar = array();
        foreach ($array as $param) {
            if (!isset($this->_format[$param])) {
                continue;
            }
            $tmp = $this->get($param);
            $this->sqlQuote($tmp);
            array_push($ar,"$param = '".$tmp."'");
        }
        $where = $this->sqlWhere($key,$value);
        $sql = "update $table set ".join(",",$ar)." $where";
        return $sql;
    }
    function sqlDelete($table, $key="", $value="")
    {
        $where = $this->sqlWhere($key,$value);
        $sql = "delete from $table where $where";
        return $sql;
    }
    function sqlQuote(&$value)
    {
        $conv_rule =array( "'" => "\\'" );
        $value = strtr($value,$conv_rule);
    }
    function sqlWhere($key,$value="")
    {
        if($value !="")$this->sqlQuote($value);
        if($key != "" && $value != "") {
            $where = "where $key = '$value'";
        } elseif ($key != "" && $value == "") {
            $value = $this->get($key);
            $this->sql_quote($value);
            $where = "where $key = '$value'";
        } else {
            $tmp = $this->getPrimaryKeyName();
            $tmp2 = $this->get($tmp);
            $this->sqlQuote($tmp2);
            $where = "where $tmp = '$tmp2'";
        }
        return $where;
    }
    function intoString($str)
    {
        $format =$this->_format;
        foreach ($format as $key => $value) {
            $rep = $this->get($key);
            $str = str_replace("#".$key."#",$rep,$str);
        }
        return $str;
    }
    function intoFile($filename)
    {
        if (!file_exists($filename))die($filename." doesn't exists");
        $in = join("",file($filename));
        return $this->intoString($in);
    }
    function intoSmarty(&$smarty)
    {
        $format = $this->_format;
        foreach ($format as $key => $value) {
            $rep = $this->get($key);$smarty->assign($key,$rep);
        }
    }
    function convert($key)
    {
        $format = $this->_format;
        $value = $format[$key];
        $type = $value[2];
        $tmp = $this->get($key);
        $func_conv = "cnv_".$type;
        if (method_exists($this,$func_conv)) {
            $tmp = call_user_method($func_conv,$this,$tmp);
        }
        $this->set($key,$tmp);
    }
    function check()
    {
        $format = $this->_format;
        foreach ($format as $key => $value) {
            $tmp = $this->get($key);
            $jname = $value[0];
            $maxlength = $value[1];
            $type = $value[2];
            $null = $value[3];
            if ($maxlength !=0) {
                if (strlen($tmp) > $maxlength) {
                    return "${jname}は${maxlength}文字以内で入力してください";
                }
            }
            if ($null =="notnull") {
                if (strlen($tmp) == 0) {
                    return "${jname}が空白です";
                }
            }
            if ($type =="file") {
                if ($tmp["size"] > ($maxlength * 1024)) {
                    return "ファイルサイズが大きすぎます";
                }
            }
            if (strlen($tmp) != 0) {
                $func_check = "is_".$type;
                if (method_exists($this, $func_check)) {
                    $success = call_user_method($func_check, $this, $tmp);
                    if (!$success) {
                        return "${jname}が正しく入力されていません";
                    }
                }
            }
        }
        return "";
    }
    function saveFile($name,$path)
    {
        if ($path == "")return;
        $tmp = $this->get($name);
        preg_match("/\.([A-Za-z0-9]+)$/", $tmp["name"], $reg);
        $ext = $reg[0];
        move_uploaded_file($tmp["tmp_name"], $path. $ext);
        $this->set($name, $path.$ext);
    }
    function sampleForm()
    {
        $buff = "<form method=post action=>\n";
        $format = $this->_format;
        foreach ($format as $key => $value) {
            if ($value[5] != "env") {
                $buff .= $value[0]." : <input type=\"text\" size=\"$value[1]\" name=\"$key\"><br>\n";
            }
        }
        $buff .= "<input type=\"submit\" value=\"送信\">";
        $buff .= "</form>\n";
        return $buff;
    }
    function set($name, $value)
    {
        $this->_hash[$name] =$value;
        if (!is_array($value)) {
            $value = htmlspecialchars($value);
            $value = nl2br($value);
            $value = preg_replace("\r|\n|\r\n", "", $value);
        }
        $tmp = "_".$name;
        $this->$tmp = $value;
    }
    function is_url($text)
    {
        return preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $text);
    }
    function is_mail($text)
    {
        return preg_match('/^[a-zA-Z0-9_\.\-]+?@[A-Za-z0-9_\.\-]+$/',$text);
    }
    function is_digit($text)
    {
        return is_numeric($text);
    }
    function cnv_string($tmp)
    {
        $tmp = str_replace("\r\n","\n",$tmp);
        $tmp = str_replace("\r","\n",$tmp);
        $tmp = str_replace("\n","<br>",$tmp);
        return $tmp;
    }
    function cnv_random($tmp)
    {
        if ($tmp == "") return uniqid(""); else return $tmp;
    }
    function cnv_date($tmp)
    {
        if ($tmp == "") return time(); else return $tmp;
    }
    function cnv_pass($tmp)
    {
        if ($tmp != "") {
            return md5($tmp);
        } else {
            return "";
        }
    }
}

class Data_Query
{
    var $_query =array();
    var $_start = 0;
    var $_length;
    var $_order;
    var $_bool;
    function Data_Query()
    {
        $this->_start =0;
        $this->_bool = "and";
    }
    function get($name)
    {
        return $this->_query[$name];
    }
    function set($name, $value)
    {
        $this->_query[$name] = $value;
    }
    function clear()
    {
        $this->_query =array();
    }
    function getStart()
    {
        return $this->_start;
    }
    function setStart($value)
    {
        $this->_start =$value;
    }
    function getLength()
    {
        return $this->_length;
    }
    function setLength($value)
    {
        $this->_length = $value;
    }
    function getOrder()
    {
        return $this->_order;
    }
    function setOrder($value)
    {
        $this->_order = $value;
    }
    function getBool()
    {
        return $this->_bool;
    }
    function setBool($value)
    {
        $this->_bool = $value;
    }
    function toStringWhere($type="and",$like=0)
    {
        if (count($this->_query)==0) {
            return "1";
        }
        $tmp = array();
        foreach ($this->_query as $key => $value) {
            $this->sqlQuote($value);
            if ($like ==0) {
                array_push($tmp," $key = '$value' ");
            } else {
                array_push($tmp," $key like '%$value%' ");
            }
        }
        if ($type =="and") {
            return join(" and ",$tmp);
        } else {
            return join(" or ",$tmp);
        }
    }
    function getHash()
    {
        return $this->_query;
    }
    function sqlQuote(&$sql)
    {
        $conv_rule =array( "'" => "\\'", "%" => "\\%", "_" => "\\_" );
        $sql = strtr($sql,$conv_rule);
    }
}

define("LOCKDIR","data/lock");
class File_Data
{
    var $_filename;
    function File_Data($filename)
    {
        $this->_filename =$filename;
    }
    function size()
    {
        return filesize($this->_filename);
    }
    function exists()
    {
        return (is_readable($this->_filename)&& is_file($this->_filename));
    }
    function getAbsolutePath()
    {
        return realpath($this->_filename);
    }
    function get()
    {
        if (is_readable($this->_filename) && is_file($this->_filename)) {
            $lines =file($this->_filename);
            return $lines;
        } else {
            return array();
        }
    }
    function slice($offset, $length)
    {
        $in = $this->get();
        return array_slice($in, $offset, $length);
    }
    function getNoLf()
    {
        $ar = $this->get();
        for ($i=0; $i<count($ar); $i++) {
            $ar[$i] = preg_replace("\r|\n","",$ar[$i]);
        }
        return $ar;
    }
    function writeIndex($line, $index)
    {
        $in = $this->get();
        $this->lock();
        $fp = fopen($this->_filename,"w");
        $flg = 0;
        for ($i=0; $i<count($in); $i++) {
            $in[$i] = preg_replace("\r\n|\r","\n",$in[$i]);
            if ($i == $index) {
                fputs($fp,$line);
                $flg =1;
            }
            fputs($fp,$in[$i]);
        }
        if ($flg ==0) {
            fputs($fp,$line);
        }
        fclose($fp);
        $this->unlock();
    }
    function writeFirst($line)
    {
        $this->writeIndex($line, 0);
    }
    function writeAdd($line)
    {
        $fp = fopen($this->_filename,"a");
        fputs($fp,$line);
        fclose($fp);
    }
    function writeAddArray($array)
    {
        if(!is_array($array))return;
        $fp = fopen($this->_filename,"a");
        for ($i=0; $i<count($array); $i++) {
            $array[$i] = preg_replace("\r\n|\r","\n",$array[$i]);
            if (substr($array[$i],-1) != "\n") {
                $array[$i].="\n";
            }
            fputs($fp,$array[$i]);
        }
        fclose($fp);
    }
    function overwrite($array)
    {
        if (!is_array($array)) {
            $array = array($array);
        }
        $this->lock();$fp = fopen($this->_filename,"w");
        for ($i=0; $i<count($array); $i++) {
            $array[$i] = preg_replace("\r\n|\r","\n",$array[$i]);
            if (substr($array[$i],-1) != "\n") {
                $array[$i] .= "\n";
            }
            fputs($fp,$array[$i]);
        }
        fclose($fp);
        $this->unlock();
    }
    function writeBin($buff)
    {
        $fp = fopen($this->_filename,"wb");
        fputs($fp,$buff);
        fclose($fp);
    }
    function lock()
    {
        if (file_exists(LOCKDIR)) {
            $ar = stat(LOCKDIR);
            $last = $ar[10];
            if ((time()- $last) > LOCKTIME) $this->unlock();
        }
        $i =0;
        while (1) {
            if ($i > 10) {
                die("locked");
            }
            if (file_exists(LOCKDIR)) {
                sleep(1);
            } else {
                break;
            }
            $i++;
        }
        $ret = mkdir(LOCKDIR,0700);
        if ($ret == False) die("can't make lockdir");
    }
    function unlock()
    {
        $ret = rmdir(LOCKDIR);
        if ($ret == 0) die("can't remove lockdir");
    }
}

class View
{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        require( $this->getTemplateName($context));
    }
    function getTemplateName(&$context)
    {
        $cgi = $context->getCgi();
        $tmp = get_class($this);
        $tmp = preg_replace("view$","",$tmp);
        $agent = $cgi->getAgent();
        if ($agent =="p") {
            return APP_TEMPLATE_DIR.$tmp."defaultTemplate.php";
        } elseif ($agent =="i") {
            return APP_TEMPLATE_DIR_I.$tmp."oneTemplate.php";
        } elseif ($agent =="j") {
            return APP_TEMPLATE_DIR_J.$tmp."oneTemplate.php";
        } elseif ($agent =="e") {
            return APP_TEMPLATE_DIR_E.$tmp."oneTemplate.php";
        }
        $context->set("UserAgent",$agent);
    }
}

class View_Manager
{
    function dispatch(&$context, $target="")
    {
        $cgi =$context->getCgi();
        if ($target == "") {
            $target = $cgi->param(APP_ACTION_ARG);
        }
        if ($target != "") {
            $viewFile = APP_VIEW_DIR.$target."View.php";
            $viewClass = $target."View";
        }
        if ($viewFile != "" && is_readable($viewFile) && is_file($viewFile)) {
            include_once($viewFile);
        } elseif (is_readable(APP_DEFAULT_VIEW_FILE) && is_file(APP_DEFAULT_VIEW_FILE)) {
            include_once(APP_DEFAULT_VIEW_FILE);
        }
        if (class_exists($viewClass)) {
            $v = new $viewClass;
        } elseif (class_exists(APP_DEFAULT_VIEW)) {
            $view = APP_DEFAULT_VIEW;
            $v = new $view;
        }
        if (method_exists($v,"dispatch")) {
            $v->dispatch($context);
        }
    }
}

class Writer_Static
{
    var $_buff;
    function Writer_Static($context, $viewName)
    {
        ob_start();
        View_Manager::dispatch($context, $viewName);
        $buffer = ob_get_contents();
        ob_end_clean();
        $this->_buff = $buffer;
    }
    function output($filename)
    {
        $fp = fopen($filename,"w") or trigger_error("${filename}に書き込めません");
        fputs($fp,$this->_buff);
        fclose($fp);
    }
}

set_error_handler("APP_ERROR");
function APP_ERROR($errno, $errstr, $errfile, $errline)
{
    global $_APP_Context;
    if ($errno != E_USER_ERROR && $errno != E_USER_WARNING && $errno != E_USER_NOTICE) {
        return;
    }
    $_APP_Context->set("errno",$errno);
    $_APP_Context->set("errstr",$errstr);
    $_APP_Context->set("errfile",$errfile);
    $_APP_Context->set("errline",$errline);
    require_once( APP_ERROR_ACTION_FILE );
    require_once( APP_ERROR_VIEW_FILE );
    $tmp = APP_ERROR_ACTION;
    $action = new $tmp;
    $tmp = APP_ERROR_VIEW;
    $view = new $tmp;
    $action->dispatch($_APP_Context);
    $view->dispatch($_APP_Context);
    exit;
}

class Article_Res
{
    function getObjects($resfile)
    {
        if (!file_exists($resfile))return array();
        $ctrl = new Ctrl_File_Data("Data_Model_Res",$resfile);
        $objs = $ctrl->get();
        return $objs;
    }
}

class Auth_Page
{
    function execute(&$context)
    {
        global $_APP_AUTH_USER;
        $cgi = $context->getCgi();
        $auth = new Auth_Param($cgi, $_APP_AUTH_USER);
        if ($auth->execute() == FALSE) {
            $from = $cgi->param("from");
            header("Location:$from");
            exit;
        }
        return;
        $auth = new Auth_Basic($cgi, $_APP_AUTH_USER);
        if ($auth->execute() == FALSE) {
            trigger_error("認証できません");
        }
    }
}

include_once(APP_LIB_DIR.'class/Page_Index.php');

class Cache_Writer
{
    function execute(&$context,$module="default")
    {
        if ($module == "default") {
            $file = new File_Data(APP_DATA_FILE);
            $pagecount = APP_DATA_VIEW_COUNT;
            $allcount = count($file->get());
            for ($i=0; $i<$allcount; $i+=$pagecount) {
                $context->set("cachemode","yes");
                $context->set("c_offset",$i);
                $ws = new Writer_Static($context, $module);
                if ($i ==0) {
                    $ws->output("html/index.html");
                } else {
                    $ws->output("html/".APP_PAGE_PREFIX."${i}.html");
                }
                $context->set("cachemode","no");
            }
        } elseif ($module =="one") {
            $file = new File_Data(APP_DATA_FILE);
            $context->set("cachemode","yes");
            $one_id = $context->get("one_id");
            if ($one_id == "") {
                trigger_error("何らかのエラーが発生しました。");
            }
            $ws = new Writer_Static($context, $module);
            $ws->output("html/${one_id}.html");
            $context->set("cachemode","no");
        }
    }
}

class Data_Model_Res extends Data_Model
{
    var $_format = array( "id" => array("ＩＤ", 16, "random", notnull, "", "" ), "name" => array("名前", 60, "string", notnull, "", "post" ), "message" => array("メッセージ", 500, "mstring", notnull, "", "post" ), "date" => array("日付", 0, "date", notnull, "", "" ), "HTTP_USER_AGENT"=> array("エージェント", 0, "string", null, "", "env" ), "REMOTE_ADDR" => array("リモートIP", 0, "string", null, "", "env" ), );
    function Data_Model_User($outdata="")
    {
        $this->set("delim","<>");
        if($outdata != "")parent::Data_Model($outdata);
    }
    function is_date($text)
    {
        $text = date("Ymd",$text);
        $yyyy = substr($text,0,4);
        $mm = substr($text,4,2);
        $dd = substr($text,6,2);
        return checkdate($mm,$dd,$yyyy);
    }
    function cnv_mstring($text)
    {
        $text = $this->cnv_string($text);
        $text = preg_replace("(https?)(://[[:alnum:]\S\$\+\?\.\;\=\_\%\,\:\@\!\#\~\*\/\&\-]+)","<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",$text);
        return $text;
    }
}

class Data_Model_User extends Data_Model
{
    var $_format = array( "id" => array("ＩＤ", 16, "random", notnull, "", "" ), "name" => array("名前", 60, "string", notnull, "", "post" ), "mail" => array("メール", 60, "mail", null, "", "post" ), "title" => array("タイトル", 80, "string", null, "無題", "post" ), "message" => array("メッセージ",2000, "mstring", notnull, "", "post" ), "url" => array("ＵＲＬ", 80, "url", null, "", "post" ), "date" => array("日付", 0, "date", notnull, "", "" ), "HTTP_USER_AGENT"=> array("エージェント", 0, "string", null, "", "env" ), "REMOTE_ADDR" => array("リモートIP", 0, "string", null, "", "env" ), "file" => array("ファイル", 200, "file", null, "", "file" ), "file2" => array("ファイル2", 200, "file", null, "", "file" ), "delkey" => array("削除キー", 32, "delkey", null, "", "post" ), );
    function Data_Model_User($outdata="")
    {
        $this->set("delim","<>");
        if($outdata != "") parent::Data_Model($outdata);
    }
    function is_date($text)
    {
        $text = date("Ymd",$text);
        $yyyy = substr($text,0,4);
        $mm = substr($text,4,2);
        $dd = substr($text,6,2);
        return checkdate($mm,$dd,$yyyy);
    }
    function cnv_delkey($text)
    {
        if ($text =="") {
            return "";
        } else {
            return md5($text);
        }
    }
    function cnv_mstring($text)
    {
        if (substr($text,0,3) == "-m-") {
            $text = substr($text,3);
            $text = preg_replace("\r|\n","",$text);
            $text = preg_replace("<([^/].*) style([^=]*)=([^>]+)>","<\\1>",$text);
            $text = preg_replace("<([^/].*) on([^>]+)>","<\\1>",$text);
            $text = strip_tags($text,"<strong><font><big><small><hr><p><em><strike><u><a><blockquote><li><ol><ul><br>");
        } else {
            $text = $this->cnv_string($text);
            $text = preg_replace("(https?)(://[[:alnum:]\S\$\+\?\.\;\=\_\%\,\:\@\!\#\~\*\/\&\-]+)","<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",$text);
        }
        return $text;
    }
}
?>
