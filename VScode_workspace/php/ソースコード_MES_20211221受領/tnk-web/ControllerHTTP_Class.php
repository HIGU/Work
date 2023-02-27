<?php
//////////////////////////////////////////////////////////////////////////////
// MVC �� Controller ����ɬ�פʥ��饹 HTTP ��                               //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/06/28 Created   ControllerHTTP_Class.php                            //
//            ���Ǥ� Request Class ����� $request ���֥������Ȥǽ�������   //
// 2005/07/28 Session Class ���ɲ�                                          //
//   Ver 1.00                                                               //
// 2005/09/13 Request�� protected function add() �� public �᥽�åɤ��ѹ�   //
//   Ver 1.01                                                               //
// 2005/11/07 Request�Σ�����������б��Τ���add_array()���ɲ� ���å��ѹ� //
//   Ver 1.02                                                               //
// 2005/11/12 ini_set E_ALL �����ꤵ��Ƥ���Τǥ����ȥ�����              //
//   Ver 1.03                                                               //
// 2005/11/17 class Request �� del()�᥽�å��ɲ� add_array()�᥽�å�����    //
//   Ver 1.04   �����ѿ���������Τ˻��Ѥ��롣                            //
// 2005/11/20 class Result �� add() get() �᥽�å��ɲ� add_once() get_once()//
//   Ver 1.05 ��åѡ��᥽�åɤΤ褦�ʤ��¾�Υ��饹��Ʊ�����󥿡��ե�������//
// 2005/11/20 class Result �� get_once() get() �˻��ꤵ�줿̾���Υǡ�����   //
//   Ver 1.06 ̵�����Υ��å��ɲ� class Request �˹�碌�� Ver 1.02      //
// 2006/06/29 class Result ��data_array2 �ץ�ѥƥ������ɲä�ʣ��������б� //
//   Ver 1.07 class Result��Ver1.03�� add_array2(),get_array2()�᥽�å��ɲ� //
// 2007/09/11 Result Class �� add_array()/add_array2()�᥽�åɤ�&(����)��� //
//   Ver 1.08 class Result��Ver1.04�� �裲������ &$data �� $data ���ѹ�     //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// require_once ('function.php');

define('CHC_VERSION', '1.08');              // ���� ControllerHTTP_Class VERSION

if (class_exists('Request')) {
    return;
}
define('RQE_VERSION', '1.04');

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Common {} �ϸ��߻��Ѥ��ʤ� �����㡧Common::ComTableMnt �� $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class Request
{
    ///// Private properties
    private $params = array();                  // �ꥯ�����ȥѥ�᡼��(����)
    private $magic_quotes_gpc;                  // �����ƥ�����Υޥ��å���������(boolean)
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��)
    public function __construct($security = false)
    {
        $this->magic_quotes_gpc = ini_get('magic_quotes_gpc');
        if ($security) {
            // $_GET, $_POST, $_COOKIE, $_FILES �����Ƥ��Ǽ����Ϣ������ �� $_FILES��;�פ���
            // ���� MenuHeader Class �ǰʲ��ε�ǽ��������Ƥ���Τ� $security = false �ν���ͤȤ���
            foreach ($_REQUEST as $key => $value) {
                $_REQUEST[$key] = strip_tags(htmlspecialchars($value, ENT_QUOTES));
                // �ǡ�������Ѥ�CLI�ǥ�����ץ����Τ���˾嵭�εմؿ���ʲ��˼�����
                // function unhtmlentities ($string)
                // {
                //     $trans_tbl = get_html_translation_table (HTML_ENTITIES, ENT_QUOTES);
                //     $trans_tbl = array_flip ($trans_tbl);
                //     return strtr ($string, $trans_tbl);
                // }
            }
        }
        // �̾�Υꥯ�����ȥѥ�᡼����ץ�ѥƥ�����Ͽ
        if ( is_array($_REQUEST) ) {
            foreach ($_REQUEST as $name => $value) {
                if ($this->magic_quotes_gpc) {
                    if (is_array($value)) {
                        foreach ($value as $name2 => $value2) {
                            $this->add_array($name, $name2, stripslashes($value2));
                        }
                    } else {
                        $this->add($name, stripslashes($value));
                    }
                } else {
                    if (is_array($value)) {
                        foreach ($value as $name2 => $value2) {
                            $this->add_array($name, $name2, $value2);
                        }
                    } else {
                        $this->add($name, $value);
                    }
                }
            }
        }
    }
    /*************************** Get methods ************************/
    public function get($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        } else {
            return '';
        }
    }
    /*************************** Set methods ************************/
    public function add($name, $data)
    {
        $this->params[$name] = $data;
    }
    public function add_array($name, $name2, $data)
    {
        $this->params[$name][$name2] = $data;
    }
    ///// ���add_array()�ǥ��åȤ�������γ�����Ƥ������� ����add()�ǥ��åȤ�����Τˤ���ѤǤ���
    public function del($name)
    {
        unset($this->params[$name]);
    }
    
    /************************************************************************
    *                             Protected methods                         *
    ************************************************************************/
    
} // Class Request End


define('RES_VERSION', '1.04');
/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
class Result
{
    ///// Private properties
    private $data_once   = array();  // ���ԥǡ���
    private $data_array  = array();  // ����ǡ���(����)
    private $data_array2 = array();  // ����ǡ���(����������)
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    /*************************** Set methods ************************/
    public function add_once($name, $data)
    {
        $this->data_once[$name] = $data;
    }
    public function add($name, $data)
    {
        $this->data_once[$name] = $data;
    }
    public function add_array($data)
    {
        $this->data_array = $data;
    }
    public function add_array2($name, $data)
    {
        $this->data_array2[$name] = $data;
    }
    /*************************** Get methods ************************/
    public function get_once($name)
    {
        if (isset($this->data_once[$name])) {
            return $this->data_once[$name];
        } else {
            return '';
        }
    }
    public function get($name)
    {
        if (isset($this->data_once[$name])) {
            return $this->data_once[$name];
        } else {
            return '';
        }
    }
    public function get_array()
    {
        return $this->data_array;
    }
    public function get_array2($name)
    {
        if (isset($this->data_array2[$name])) {
            return $this->data_array2[$name];
        } else {
            return array();
        }
    }
} // Class Result End


define('SESSION_VERSION', '1.00');
/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
class Session
{
    ///// Private properties
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    /*************************** Set methods ************************/
    public function add($name, $data)
    {
        $_SESSION[$name] = $data;
    }
    public function add_local($name, $data)
    {
        $_SESSION[$_SERVER['PHP_SELF']][$name] = $data;
    }
    /*************************** Get methods ************************/
    public function get($name)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        } else {
            return '';
        }
    }
    public function get_local($name)
    {
        if (isset($_SESSION[$_SERVER['PHP_SELF']][$name])) {
            return $_SESSION[$_SERVER['PHP_SELF']][$name];
        } else {
            return '';
        }
    }
} // Class Session End

?>
