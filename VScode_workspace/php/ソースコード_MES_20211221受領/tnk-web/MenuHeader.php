<?php
//////////////////////////////////////////////////////////////////////////////
// TNK ���̥�˥塼�إå������饹                                           //
// Copyright (C) 2004-2011 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/16 Ver 1.00 Created   MenuHeader.php                             //
// 2004/07/20 Ver 1.01 �����Υ��å����롼����å����ɲ�               //
//             ���ꤵ��Ƥ��ʤ�����default=$_SERVER['HTTP_REFERER']����� //
//             �ƽи������ꤷ�Ƥ��ʤ����� ErrorPage�����Ф���             //
// 2004/07/22 Ver 1.02 form��name/target°����ץ�ѥƥ��ڤӥ᥽�åɤ��ɲ�  //
// 2004/07/23 Ver 1.03 set_frame()�᥽�åɤ��ɲ�(set_action()�Υե졼����)  //
// 2004/07/24 Ver 1.04 set_retGET()/set_retPOST()�᥽�åɤ��ɲ�             //
// 2004/07/27 Ver 1.05 view_user()�᥽�åɤ�SERVER_NAME/SERVER_ADDR/REMOTE_ //
//              ADDR���ɲ�  ���ܥ����̾����RetIndex��backwardStack���ѹ� //
// 2004/07/31 Ver 1.06 menu_site��submit()���ѹ� out_site_java()��ƽФ��Τ�//
//              </html>�θ� NN7.1- �б� IE������ʤ� ��Ǥ��뤫�⡩       //
//              �����ѡ������Х��ѿ��Υ������ƥ������å� method ���ɲ�  //
//              global_chk() Protected methods �ǥ��󥹥ȥ饯������θƽ�   //
// 2004/08/08 Ver 1.07 �嵭��submit()��out_site_java()��out_site_javaEnd()��//
//              �ѹ����ƽ����out_site_java()�򥪥֥������ȤΥ����å��򤷤� //
//              menu_site̾�� Window ������� JavaScript��¹Ԥ���褦���ѹ�//
// 2004/08/10 Ver 1.08 out_frame()���ɲ�(out_action��ƽФ�)out_action���ѹ�//
// 2004/09/19 Ver 1.09 �ꥢ�륿���९��å�ɽ����ǽ�ɲ�(Default)���̸ߴ���  //
//                     set_notRealTime() �ץ�ѥƥ������ꤹ�롣             //
// 2004/09/28 Ver 1.10 $_SESSION['s_sysmsg]��out_alert_java()�᥽�åɤǽ��� //
// 2004/10/04 Ver 1.11 out_title_only_border()�����ȥ�����ν��ϥ᥽�å��ɲ�//
// 2004/12/22 Ver 1.12 view_user()��script_name���ɲ� title��nowrap�����   //
// 2004/12/23          script_name�� center ���� right ���ѹ�               //
//                   menu_OnOff()��font-weight:normal;���ɲ� style td�δ��� //
// 2005/01/13 ���ܥ���˶��̥����γ�����JavaScript���ɲ� F12=123, F2=113  //
//   Ver 1.13 Protected methods �� common_backward_key() method���ɲä�     //
//            out_title_border()����ƽФ��ƻ��� �����ȼ��formName����     //
//            name='������title_time��<input size=19��18���ѹ�NN7.1�к�   //
//            ���ξ����б�@�ǲ��򤻤�����Ͽ����$_SESSION['s_sysmsg']='' //
// 2005/01/21 out_title_border()�Υǥ������ѹ� (bg_gra style �������ɲ�)    //
//   Ver 1.14 �ɲò�����border_silver.gif, border_silver_text.gif,          //
//            border_silver_button.gif  out_css()��ľ�ܵ��Ҥ���MENU_FORM��  //
// 2005/01/28 set_retGET($name, $value)�� $value = urlencode($value);���ɲ� //
//   Ver 1.15 $value������ʸ������Ȥ���褦�ˤ��뤿����б�                //
// 2005/01/31 $retGETanchor�ץ�ѥƥ����ɲ� set_retGETanchor($name='')������//
//   Ver 1.16 out_css()�᥽�åɤ� out_css($file=MENU_FORM)��file̾�ѹ���ǽ��//
// 2005/02/10 out_title_only_border()��class='bg_gra'�ε���ȴ������       //
//   Ver 1.17 MENU_FORM��.bg_gra��height:31px;���ɲ� �ܥ����̵ͭ�˴ط��ʤ� //
// 2005/02/21 view_user()�᥽�åɤ� border-width:0px;���ɲ�                 //
//   Ver 1.18 �桼��������Υ������륷���Ȥ� td{} �˱ƶ�����ʤ��褦�ˤ���  //
//   ����     �����ȴ֤Υ��ץ궦ͭ�ξ���RetUrl�����ꤵ��ʤ��Τ� TOP������//
// 2005/06/15 ��������Υ��ߤ���� if (!isset($_SESSION))�� else ʸ����   //
//            out_site_javaEnd()��top.menu_site�Υ����å�ȴ������         //
//              �С��������ѹ��Ϥʤ�                                      //
// 2005/06/26 F2/F12������ͭ���ˤ��뤿��backwardName�ץ�ѥƥ����ɲä�      //
//   Ver 1.19 common_backward_key()�᥽�åɤ˥ե���������ǽ���ɲ�           //
// 2005/07/07 global_chk() ENT_QUOTES ���ɲ� "'" ���󥰥륯�����Ȥ��Ѵ����� //
//   Ver 1.20 htmlspecialchars()�˾嵭�Υ��ץ������ɲä��������ƥ��ζ���//
//            �ڡ������� ��ǽ�� ComTableMntClass.php �Ǽ���                 //
// 2005/07/14 ����Υ����С�̾�����ɥ쥹��ƥ����ѤΥ����С��λ����ֿ���ɽ��//
//   Ver 1.21 ʣ���Υ����С��Ǻ�Ȥ��Ƥ��������դ�¥������                //
// 2005/08/03 HTML4.01�˹�碌��JavaScript�ε��Ҥ� type='text/javascript'�� //
//   Ver 1.22 ����ε��Ҥ� language='JavaScript' Content-Script ��Ʊ�����ѹ�//
//            menu_OnOff()�᥽�åɤ�site_view�Υ��å����¸�ߥ����å����ɲ� //
// 2005/08/20 �֥饦�����Υ���å����к��Ѥ� $uniq = uniqid('�������')�� //
//   Ver 1.23 �ġ��Υ�����ץȤǽ������Ƥ����᥽�å�set_useNotCache()����� //
// 2005/08/20 PHP5 �ذܹ� ������ private public protected   __construct()   //
//   Ver 1.24    ����name=����ƥ����ä��Τ� $this->backwardName �ؽ��� //
//               ���褬���ꤵ��Ƥ��ʤ��������ܥ����Disabled����       //
// 2005/08/28 JavaScript �� base_class.js ������å����ɲ�                //
//   Ver 1.25    JavaScript �򥪥֥������Ȼظ���ɸ�ಽ�����裱���ƥå�      //
// 2005/09/05 menu_OnOff()�᥽�åɤ�base_class��menuOnOff()����Ѥ�����ѹ� //
//   Ver 1.26    multi window������window_ctl.js/menu_frame.js�˼�����������//
// 2005/09/07 �嵭�� Client side scripting�ξ��˳ƥ�����ɥ��֤ǰ㤤���Ф�//
//   Ver 1.27 �����˥����С��Ȥ�Status������å�����menuStatusCheck()�ɲ� //
// 2005/09/09 �᥽�å� out_retGET() out_retGETanchor() ���ɲ�  Properties�� //
//   Ver 1.28 �����ǻ��Ѥ��Ƥ������������Ȥ�����ʤ�����᥽�åɤ��ɲ�      //
// 2005/11/01 out_html_header()�᥽�åɤ� static �᥽�åɤ��ѹ� ǧ�ڤ�ɬ�פ�//
//   Ver 1.29 ���ʤ��ѥ�᡼���� __construct($auth=-1) ���ɲ�               //
// 2005/11/05 global_chk()�᥽�åɤ�_GET _POST _REQUEST �Σ������ܤ������  //
//   Ver 1.30 �����å�������Ǥ���Х������ƥ������å���¹Ԥ���褦���ѹ�//
// 2005/11/12 set_auth_chk(-1)ǧ�ڤ�ɬ�פȤ��ʤ����Υ��å������        //
//   Ver 1.31 ����¾�Υ�˥塼��ǧ�ںѤߤ� Auth �� User_ID �ǥ����å�����   //
// 2005/11/17 view_user()�᥽�åɤθ���ɽ������ºݤθ��¥�٥��ɽ�������� //
//   Ver 1.32 $auth = (int)$u_id �� (int)$_SESSION['Auth'] ���ѹ�           //
// 2006/04/12 view_user()�᥽�åɤ�HTML��font-family:�ͣ� �Х����å�;���ɲ� //
//   Ver 1.33 CSS�ե���������body {font-family}�������л��ꤵ�줿�����б� //
// 2006/07/06 out_alert_java() �� out_alert_java($addSlashes=true)          //
//   Ver 1.34 �����̤Υ�å���������\n������Ϥ��������λ���(false)     //
// 2006/08/01 �嵭�򹹤�out_alert_java($addSlashes=true, $strip_tags=true)��//
//   Ver 1.35 addSlashes�Ϥ��ʤ���strip_tags�Ϥ������ν�����б����뤿��    //
// 2007/01/22 out_title_border()��out_title_border($switchReload=0) ���ѹ���//
//   Ver 1.36 menu_OnOff()�ǤΥ�������ؤ�ƽФ�¦�ǽ�����ѹ��Ǥ���褦��//
// 2007/06/21 public�᥽�å�out_retF2Script()���ɲä�iframe��ǥ����ȥ�̵�� //
//   Ver 1.37 �˥ե��������������äƤ��Ƥ�F2/F12�����뵡ǽ���ɲ�          //
//            2007/06/22 �嵭�Υ᥽�åɤ˥������åȥѥ�᡼�������ɲ�       //
// 2011/06/11 ɽ�ꣲ��������ɲ�$caption2                              ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
if (class_exists('MenuHeader')) {
    return;
}
require_once ('define.php');
define('MH_VERSION', '1.37');

/****************************************************************************
*                       base class ���쥯�饹�����                         *
****************************************************************************/
///// namespace Controller {} �ϸ��߻��Ѥ��ʤ� �����㡧Controller::equipController �� $obj = new Controller::equipController;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class MenuHeader
{
    ///// Private properties
    private $title;                     // �����ȥ�̾
    private $caption;                   // ɽ��̾
    private $caption2;                  // ɽ��̾2
    private $RetUrl = '';               // �����URLdocument_root����http://���ɥ쥹
    private $auth;                      // �׵Ḣ�¥�٥�
    private $today;                     // ���������� date('Y/m/d H:i:s')
    private $site_index;                // �����ȥޥåפ� Site_Index
    private $site_id;                   // �����ȥޥåפ� Site_ID
    private $self;                      // ��ʬ���Ȥ�document_root���ɥ쥹+������ץ�̾
    private $user_id;                   // �Ұ��ֹ�
    private $action;                    // �ƽ������ݲ�(action)̾�ȥ��ɥ쥹�Ѥ�Ϣ������
    private $formName;                  // ���form��name°���������̾��(name=��ޤޤʤ�) JavaScript����������
    private $backwardName;              // ���form��<input type='submit'��name°���������̾��(name=��ޤޤʤ�) JavaScript����������
    private $target;                    // form��JavaScript�ǥ���������Window��̾��(target=��ޤ�)�ե졼���б�
    private $retGET;                    // �������Ϥ�GET�ѥ�᡼����
    private $retPOST;                   // �������Ϥ�POST�ѥ�᡼����
    private $retGETanchor;              // �������Ϥ�GET anchor̾ (link������)
    private $_parent;                   // �ե졼���Ǥλ��οƥե졼��Υ��ɥ쥹 ����ʳ��ϼ�ʬ����($self)
    private $real_time;                 // ��˥塼�λ���ɽ����1����˹������뤫�Υե饰
    private $uniq = '';                 // �֥饦�����Υ���å����к���
    private $jsBaseFlag = false;        // JavaScript �� base_class set flag
    private $evtKeyFlag = false;        // JavaScript �� base_class.evt_key_chk()�᥽�åɤ�document.onkeydown�����ꤵ��Ƥ��뤫�����å�2007/06/21ADD
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��)
    public function __construct($auth=0, $RetUrl='', $title='Title is not set')
    {
        if (!isset($_SESSION)) {                    // ���å����γ��ϥ����å�
            session_start();                        // Notice ���򤱤����Ƭ��@
        }
        if ($RetUrl == '') {
            $RetName = $_SERVER['PHP_SELF'] . '_ret';       // �����Υ��å�����ѿ�̾�������롼��ˤ��
            if (isset($_SESSION["$RetName"])) {             // �ƽи��ǥ��åȤ���Ƥ��뤫�����å�
                $this->set_RetUrl($_SESSION["$RetName"]);
            } else {
                // $this->set_RetUrl(ERROR . 'ErrorReturnPage.php');   // ���ꤵ��Ƥ��ʤ�����Error Page�����Ф�
                ///// �����ȴ֤Υ��ץ궦ͭ�ξ���RetUrl�����ꤵ��ʤ��Τǡ����礨���ȥåץ�˥塼�����Ф�
                // $this->set_RetUrl(TOP_MENU);         // ���ߤϥܥ����Disabled������ˤ���б����Ƥ���
            }
        } else {
            $this->set_RetUrl($RetUrl);                 // ���ꤵ�줿 Return URL ����
        }
        $this->set_auth_chk($auth);                     // �׵Ḣ�¥�٥������ڤӥ����å�
        $this->set_title($title);                       // �����ȥ������
        $this->set_self($_SERVER['PHP_SELF']);          // ��ʬ�Υ��ɥ쥹������
        $this->action = array();                        // Ϣ������ν����
        $this->set_formName('mhForm');                  // �����(default=MenuHeader��mh+Form)
        $this->set_backwardName('backwardStack');       // �����(default=MenuHeader�����ܥ���̾)
        $this->target = '';                             // �����(����ʤ�)
        $this->retGET  = '';                            // �����(�ѥ�᡼�����ʤ�)
        $this->retPOST = '';                            // �����(�ѥ�᡼�����ʤ�)
        $this->retGETanchor = '';                       // �����(�ѥ�᡼�����ʤ�)
        $this->set_parent();                            // �ƥե졼��Υ��å�
        $this->global_chk();                            // �����ѡ������Х��ѿ��Υ������ƥ������å�
        $this->real_time = true;                        // �ꥢ�륿���९��å�
    }
    
    /*************************** Set & Check methods ************************/
    // �׵Ḣ�¤�����ȥ����å�
    public function set_auth_chk($auth)
    {
        $this->auth = $auth;
        if ($auth < 0) {                    // ǧ�ڤ�ɬ�פȤ��ʤ���(-1)
            // ����¾�Υ�˥塼��ǧ�ںѤʤ�
            if (isset($_SESSION['Auth'])) {
                $this->auth = $_SESSION['Auth'];
            } else {
                $this->auth = 0;            // ���㸢�¤�ǧ��
                $_SESSION['Auth'] = $this->auth;
            }
            // ����¾�Υ�˥塼�ǥ桼������Ͽ����Ƥ���ʤ�
            if (isset($_SESSION['User_ID'])) {
                $this->user_id = $_SESSION['User_ID'];
            } else {                    // ������user_id=00000A�� int��cast����000000�桼������Ʊ���ˤʤ�
                $this->user_id = '000000';  // ǧ�ڤ�ɬ�פȤ��ʤ�����user_id
                $_SESSION['User_ID'] = $this->user_id;
            }
            return true;
        }
        if (isset($_SESSION['Auth'])) {     // ���ѼԤθ��¤����ꤵ��Ƥ��뤫
            if ($_SESSION['Auth'] >= $this->auth) {   // ���ѼԤθ��¥�٥뤬�׵Ḣ�¥�٥�ʾ夢�뤫
                $this->user_id = $_SESSION['User_ID'];
                return true;
            }
            $_SESSION['s_sysmsg'] = '���Ѥ��븢�¤�����ޤ���';
            if (substr($this->RetUrl, 0, 4) == 'http') {
                header("Location: {$this->RetUrl}");
            } else {
                header('Location: ' . H_WEB_HOST . $this->RetUrl);
            }
            exit();     // �׵Ḣ�¤��������Ƥ��ʤ�
        } else {
            $_SESSION['s_sysmsg'] = 'ǧ�ڴ��¤��ڤ줿��ǧ�ڤ��Ƥ��ޤ���';
            header('Location: http:' . WEB_HOST);
            exit();     // ǧ�ڥ��顼
        }
    }
    /******************************* Set methods ****************************/
    // Return URL ������
    public function set_RetUrl($RetUrl)
    {
        $this->RetUrl = $RetUrl;
    }
    // �����ȥ�̾������
    public function set_title($title)
    {
        $this->title = $title;
    }
    // ɽ��̾������
    public function set_caption($caption)
    {
        $this->caption = $caption;
    }
    // ɽ��̾2������
    public function set_caption2($caption2)
    {
        $this->caption2 = $caption2;
    }
    // Site Index �� Site ID ������
    public function set_site($site_index, $site_id)
    {
        $this->site_index = $site_index;
        $this->site_id    = $site_id;
        $_SESSION['site_index'] = $this->site_index;
        $_SESSION['site_id']    = $this->site_id;
    }
    // Self url ��ʬ���ȤΥ��ɥ쥹������
    public function set_self($self)
    {
        $this->self = $self;
    }
    // �ƽ���Υ��ɥ쥹�������(��������������)
    public function set_action($name, $addr)
    {
        // $name=��ݲ�(action)̾ ���ܸ�Ǥ�OK
        // $addr=(document_root�����)���ɥ쥹
        if ($name != '') {
            $this->action[$name] = $addr;
        } else {
            $this->action[] = $addr;
        }
        $addr_ret = $addr . '_ret';                 // �����Υ��å�����ѿ�̾�������롼��ˤ��
        $_SESSION["$addr_ret"] = $this->self;       // �꥿���󥢥ɥ쥹�򥻥å�
    }
    // �ƽ���Υ��ɥ쥹�������(��������������) �ե졼����
    public function set_frame($name, $addr)
    {
        // $name=��ݲ�(action)̾ ���ܸ�Ǥ�OK
        // $addr=(document_root�����)���ɥ쥹
        if ($name != '') {
            $this->action[$name] = $addr;
        } else {
            $this->action[] = $addr;
        }
        $addr_ret = $addr . '_ret';                 // �����Υ��å�����ѿ�̾�������롼��ˤ��
        $_SESSION["$addr_ret"] = $this->RetUrl;     // �꥿���󥢥ɥ쥹�Ͽƥե졼��������
        $addr_parent = $addr . '_parent';           // �ƻҴط��Υ��å�����ѿ�̾ �����롼��ˤ��
        $_SESSION["$addr_parent"] = $this->self;    // �ҥե졼��Υ��å�����ѿ��˿ƥե졼��Υ��ɥ쥹����Ͽ
    }
    // form name ������
    public function set_formName($formName)
    {
        if ($formName != '') {
            $this->formName = $formName;
        }
    }
    // ���ܥ��� <input type='submit' name ������
    public function set_backwardName($backwardName)
    {
        if ($backwardName != '') {
            $this->backwardName = $backwardName;
        }
    }
    // Target Window Name ������
    public function set_target($target)
    {
        if ($target != '') {
            $this->target = "target='{$target}'";
        }
    }
    // Return GET parameter ������
    public function set_retGET($name, $value='')
    {
        if ($name != '') {
            $value = urlencode($value);
            if ($this->retGET == '') {
                $this->retGET = "?{$name}={$value}";
            } else {
                $this->retGET .= "&{$name}={$value}";
            }
        }
    }
    // Return POST parameter ������
    public function set_retPOST($name, $value='')
    {
        if ($name != '') {
            if ($this->retPOST == '') {
                $this->retPOST = "                    <input type='hidden' name='{$name}' value='{$value}'>\n";
            } else {
                $this->retPOST .= "                    <input type='hidden' name='{$name}' value='{$value}'>\n";
            }
        }
    }
    // Return GET �� link(���󥫡�̾) ������
    public function set_retGETanchor($name='')
    {
        if ($name != '') {
            $this->retGETanchor = "#{$name}";
        }
    }
    // Not real time clock view ������
    public function set_notRealTime()
    {
        $this->real_time = false;
    }
    // �֥饦�����Υ���å����к��� $uniq ������
    public function set_useNotCache($prefix='ID')
    {
        if ($this->uniq == '') $this->uniq = uniqid($prefix);
        return $this->uniq;
    }
    /******************************* Out methods ****************************/
    // HTML Header ����
    public static function out_html_header()
    {
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');               // ���դ����
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');  // ��˽�������Ƥ���
        header('Cache-Control: no-store, no-cache, must-revalidate');   // HTTP/1.1
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');                                     // HTTP/1.0
    }
    // �����ȥ�̾�Τ߽���
    public function out_title()
    {
        return $this->title;
    }
    // ����ץ����̾�Τ߽���
    public function out_caption()
    {
        return $this->caption;
    }
    // ����ץ����̾2�Τ߽���
    public function out_caption2()
    {
        return $this->caption2;
    }
    // �����ȥޥå���JavaScript����
    public function out_site_java()
    {
        $site_java  = $this->out_jsBaseClass();
        $site_java .= "<script type='text/javascript'>\n";
        $site_java .= "<!--\n";
        // �ҥե졼���б� $site_java .= "parent.menu_site.location = '" . H_WEB_HOST . SITE_MENU . "';\n";
        $site_java .= "if (top.menu_site) {\n";
        $site_java .= "    top.menu_site.location = '" . H_WEB_HOST . SITE_MENU . "';\n";
        $site_java .= "}\n";
        $site_java .= "// -->\n";
        $site_java .= "</script>\n";
        return $site_java;
    }
    // �����ȥޥå���JavaScript���� (</body>�θ�˽���)
    public function out_site_javaEnd()
    {
        $site_java  = "<form name='siteForm' method='post' target='menu_site' action='" . SITE_MENU . "'>\n";
        $site_java .= "</form>\n";  // �ե�����ˤ�����ˤ�ä� out_site_java()��ƽФ��Τ�</html>�θ� NN7.1- �б� IE������ʤ�
        $site_java .= "<script type='text/javascript'>\n";
        $site_java .= "<!--\n";
        // �ҥե졼���б� $site_java .= "parent.menu_site.location = '" . H_WEB_HOST . SITE_MENU . "';\n";
        // submit()���ѹ� $site_java .= "top.menu_site.location = '" . H_WEB_HOST . SITE_MENU . "';\n";
        $site_java .= "if (top.menu_site) {\n";
        $site_java .= "    document.siteForm.submit();\n";
        $site_java .= "}\n";
        $site_java .= "// -->\n";
        $site_java .= "</script>\n";
        return $site_java;
    }
    // Menu Header�� CSS ���ѥե�������� ����
    public function out_css($file=MENU_FORM)
    {
        $css  = $this->out_jsBaseClass();
        $css .= "<link rel='stylesheet' href='{$file}?" . date('YmdHis') . "' type='text/css' media='screen'>\n";
        return $css;
    }
    // Menu Header�� JavaScript ���ѥե�������� ����
    public function out_javaFile($file)
    {
        $jf  = $this->out_jsBaseClass();
        $jf .= "<script type='text/javascript' src='{$file}?id=" . date('YmdHis') . "'></script>\n";
        return $jf;
    }
    // Menu Header�� JavaScript ���ѥե�������� ����
    public function out_jsBaseClass()
    {
        if (!$this->jsBaseFlag) {
            $this->jsBaseFlag = true;
            return "<script type='text/javascript' src='". JS_BASE_CLASS. "?id=" . date('YmdHis') . "'></script>\n";
        }
        return '';
    }
    // �����ȥ�ܡ������ν���
    public function out_title_border($switchReload=0)
    {
        if ($this->RetUrl != '') $disabled = ''; else $disabled = ' disabled';   // ���褬���ꤵ��Ƥ��ʤ�
        $this->today = date('Y/m/d H:i:s');     // �ǿ������������
        $title_border  = '';    // ���ߡ�
        if (!$this->jsBaseFlag)
        $title_border .= "        ". $this->out_jsBaseClass();
        $title_border .= "        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>\n";
        $title_border .= "            <tr><td> <!-- ���ߡ�(�ǥ�������) -->\n";
        $title_border .= "        <table width='100%' border='1' cellspacing='0' cellpadding='0' class='bg_gra'>\n";
        $title_border .= "            <tr>\n";
        $title_border .= "                <form method='post' action='{$this->RetUrl}{$this->retGET}{$this->retGETanchor}' name='{$this->formName}' {$this->target}>\n";
        $title_border .= $this->retPOST;
        $title_border .= "                    <td width='60' bgcolor='blue' align='center' valign='center' class='ret_border'>\n";
        $title_border .= "                        <input class='ret_font' type='submit' name='{$this->backwardName}' value='���'{$disabled}>\n";
        $title_border .= "                    </td>\n";
        $title_border .= "                </form>\n";
        $title_border .= $this->menu_OnOff($this->_parent . '?page_keep=1', $switchReload);
        $title_border .= "                <td nowrap align='center' class='title_font'>\n";
        $title_border .= "                    {$this->title}\n";
        $title_border .= "                </td>\n";
        $title_border .= "                <td align='center' width='140' class='today_font'>\n";
        if ($this->real_time == false) {
        $title_border .= "                    {$this->today}\n";
        } else {
        $title_border .= "                    <form name='clock_ctl'>\n";               // ���Υ���������������19����NN7.1�к���18���ѹ�
        $title_border .= "                        <input type='text' name='text_date' size='18' class='title_time' value='{$this->today}'>\n";
        $title_border .= "                    </form>\n";
        }
        $title_border .= "                </td>\n";
        $title_border .= "            </tr>\n";
        $title_border .= "        </table>\n";
        $title_border .= "            </td></tr>\n";
        $title_border .= "        </table>\n";
        $title_border .= $this->view_user($this->user_id);
        if ($this->real_time == true) {
            $title_border .= '        ' . $this->out_clock_java('document.clock_ctl.text_date');
        }
        if (!$this->evtKeyFlag) {
            $title_border .= '        ' . $this->common_backward_key();
            $this->evtKeyFlag = true;
        }
        return $title_border;
    }
    // �����ȥ�ܡ����������ν���
    public function out_title_only_border()
    {
        $this->today = date('Y/m/d H:i:s');     // �ǿ������������
        $title_border  = '';    // ���ߡ�
        if (!$this->jsBaseFlag)
        $title_border .= "        ". $this->out_jsBaseClass();
        $title_border .= "        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>\n";
        $title_border .= "            <tr><td> <!-- ���ߡ�(�ǥ�������) -->\n";
        $title_border .= "        <table width='100%' border='1' cellspacing='0' cellpadding='0' class='bg_gra'>\n";
        $title_border .= "            <tr>\n";
        $title_border .= "                <td nowrap align='center' class='title_font'>\n";
        $title_border .= "                    {$this->title}\n";
        $title_border .= "                </td>\n";
        $title_border .= "                <td align='center' width='140' class='today_font'>\n";
        if ($this->real_time == false) {
        $title_border .= "                    {$this->today}\n";
        } else {
        $title_border .= "                    <form name='clock_ctl'>\n";               // ���Υ���������������19����NN7.1�к���18���ѹ�
        $title_border .= "                        <input type='text' name='text_date' size='18' class='title_time' value='{$this->today}'>\n";
        $title_border .= "                    </form>\n";
        }
        $title_border .= "                </td>\n";
        $title_border .= "            </tr>\n";
        $title_border .= "        </table>\n";
        $title_border .= "            </td></tr>\n";
        $title_border .= "        </table>\n";
        $title_border .= $this->view_user($this->user_id);
        if ($this->real_time == true) {
            $title_border .= '        ' . $this->out_clock_java('document.clock_ctl.text_date');
        }
        return $title_border;
    }
    // ��ʬ���ȤΥ��ɥ쥹�Τ߽���
    public function out_self()
    {
        return $this->self;
    }
    // �ƤΥ��ɥ쥹�Τ߽���
    public function out_parent()
    {
        return $this->_parent;
    }
    // �꥿���󥢥ɥ쥹�Τ߽���
    public function out_RetUrl()
    {
        return $this->RetUrl;
    }
    // GET�ǡ����ν���
    public function out_retGET()
    {
        return $this->retGET;
    }
    // GET anchor �ǡ����ν���
    public function out_retGETanchor()
    {
        return $this->retGETanchor;
    }
    // �ƽ���Υ��ɥ쥹����
    public function out_action($name)
    {
        // $name=��ݲ�(action)̾
        if (isset($this->action[$name])) {
            return $this->action[$name];
        } else {
            return (ERROR . 'ErrorActionPage.php');   // ���ꤵ��Ƥ��ʤ�����Error Page�����Ф�
        }
    }
    // �ƽ���Υ��ɥ쥹����(�ե졼����)
    public function out_frame($name)
    {
        // $name=��ݲ�(action)̾
        return $this->out_action($name);
    }
    // �ٹ��å�������JavaScript�ǽ���
    public function out_alert_java($addSlashes=true, $strip_tags=true)
    {
        if (!isset($_SESSION['s_sysmsg'])) $_SESSION['s_sysmsg'] = '';  // ���ξ����б�@�ǲ��򤻤�����Ͽ����
        if ($_SESSION['s_sysmsg'] != '') {
            if ($strip_tags) $_SESSION['s_sysmsg'] = strip_tags($_SESSION['s_sysmsg']);
            if ($addSlashes) $_SESSION['s_sysmsg'] = addslashes($_SESSION['s_sysmsg']);
            $alert_java  = "<script type='text/javascript'>\n";
            $alert_java .= "<!--\n";
            $alert_java .= "alert('{$_SESSION['s_sysmsg']}');\n";
            $alert_java .= "// -->\n";
            $alert_java .= "</script>\n";
            $_SESSION['s_sysmsg'] = '';     // ���ѺѤߤΥ�å������Ϻ��
            return $alert_java;
        } else {
            return '';
        }
    }
    // �֥饦�����Υ���å����к��� $uniq �ν���
    public function out_useNotCache($prefix='ID')
    {
        if ($this->uniq == '') $this->set_useNotCache($prefix);
        return $this->uniq;
    }
    // F2/F12������������ ñ���� (�֥饦������ˤ�ɽ���ϸ����ʤ�)
    // �裱�ѥ�᡼�����ϥ֥饦�����Υ������å���, �裲�ѥ�᡼�����ϣ���Τ�=Y ���֤�����=N
    public function out_retF2Script($F2target='', $once='Y')
    {
        if ($this->evtKeyFlag && $once == 'Y') return "\n";
        if ($F2target == '') $F2target = $this->target; else $F2target = "target='{$F2target}'";
        $ret_form = '';
        $ret_form .= "\n<span style='visibility:hidden;'>\n";
        $ret_form .= "    <form method='post' action='{$this->RetUrl}{$this->retGET}{$this->retGETanchor}' name='_HIDDEN_RETURN_FORM' {$F2target}>\n";
        $ret_form .= $this->retPOST;
        $ret_form .= "    </form>\n";
        $ret_form .= "</span>\n";
        
        $ret_java  = $ret_form;
        if (!$this->jsBaseFlag) $ret_java .= $this->out_jsBaseClass();
        $ret_java .= "<script type='text/javascript'>\n";
        $ret_java .= "    var backward_obj = document._HIDDEN_RETURN_FORM;\n";
        $ret_java .= "    document.onkeydown = baseJS.evt_key_chk;\n";
        $ret_java .= "</script>\n";
        
        $this->evtKeyFlag = true;
        return $ret_java;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////////////////////////////////////////////////////////////////////////
    // �ƥե졼�ब���å�����ѿ�����Ͽ����Ƥ���Х��å� ̵����м�ʬ�򥻥å�//
    ////////////////////////////////////////////////////////////////////////////
    protected function set_parent()
    {
        if (isset($_SESSION["{$this->self}_parent"])) {     // �ƥե졼��Υ��å�����ѿ�����Ͽ����Ƥ����
            $this->_parent = $_SESSION["{$this->self}_parent"];     // �ƥե졼��Υ��ɥ쥹�򥻥å�
        } else {
            $this->_parent = $this->self;                           // ����ʳ��ϼ�ʬ���Ȥ򥻥å�
        }
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // �ե졼���˥塼�� On/Off(ɽ������ɽ��)�ؿ�                            //
    ////////////////////////////////////////////////////////////////////////////
    protected function menu_OnOff($script, $switchReload)
    {
        /***** �����ȥ�˥塼 On / Off *****/
        if (!isset($_SESSION['site_view'])) $_SESSION['site_view'] = 'off';
        if ($_SESSION['site_view'] == 'on') {
            $site_view = 'MenuOFF';         // $frame_cols= '0%,*';
        } else {
            $site_view = 'MenuON';          // $frame_cols= '10%,*';
        }
        // out_title_border($switchReload)�ƽФ����˥ѥ�᡼�����ǻؼ������褦���ѹ�
        $reload = $switchReload;
        // if (preg_match('/order_schedule_Header.php/', $_SERVER['PHP_SELF'])) $reload = 1;
        // if (isset($_REQUEST['graph'])) $reload = 0; // ����դξ��ϲ������
        // if (preg_match('/inspection_recourse_Header.php/', $_SERVER['PHP_SELF'])) $reload = 1;
        // �嵭�Τ�Τ϶���Ū�˥�����Ǥˤ���
                                                             // ret_border �ϳƥ�˥塼�ǻ��Ѥ��Ƥ���
        return "
                <td width='40' align='center' valign='center' class='ret_border'>
                    <input style='font-size:8.5pt; font-weight:normal; font-family:monospace;'
                        type='submit' name='site' value='{$site_view}' id='switch_name' class='menu_onoff'
                        onClick='baseJS.menuOnOff(\"switch_name\", \"/menu_frame.php?name={$script}\", $reload)'
                    >
                </td>
                <script type='text/javascript'>baseJS.menuStatusCheck(\"switch_name\", \"/menu_frame.php?name={$script}\", $reload)</script>
        ";
        // �ҥե졼���б��Τ��� parent.location.href �� top.location.href ���ѹ�
        // onClick=\"top.location.href='/menu_frame_OnOff.php?name={$script}';\"
        // ���Ǥ�OK���������ͤˤ���Τ������ܾ她�ޡ��ȷ�����NN���б����Ƥ��ʤ���
        // onClick=\"top.topFrame.cols='0%,*'\" ���ѹ� �� base_class.js(menuOnOff())�� 2005/09/05
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // �إå����Υ�˥塼�С��β��˥桼�����ɣġ��桼����̾��ɽ��             //
    ////////////////////////////////////////////////////////////////////////////
    protected function view_user($u_id)
    {
        switch ($u_id) {
        case 0:
        case 1:
        case 2:
        case 3:
        case 4:
        case 5:
            $auth = (int)$_SESSION['Auth'];     // ���������Ѵ� 2005/11/17 (int)$u_id ��ºݤθ��¤��ѹ�
            $name = "Auth{$auth}";  // ʸ�����Ϣ��
            break;
        default:
            $query = "SELECT trim(name) FROM user_detailes WHERE uid='{$u_id}'";
            if (getUniResult($query, $name) <= 0) {
                $name = 'check'; // ̤��Ͽ���ϥ��顼�ʤ�
            }
        }
        // ���߼¹���ο��¤Υ�����ץ�̾(include/require��ޤ�)
        // $script_name = substr(basename(__FILE__), 0, -4);
        if ($_SESSION['Auth'] <= 2) {
            // �֥饦�������ɽ�����Ƥ��륹����ץ�̾�Τ�
            $script_name = basename($_SERVER['PHP_SELF'], '.php');
        } else {
            // �ե륢�ɥ쥹��ɽ��
            $script_name = substr($_SERVER['PHP_SELF'], 0, -4);
        }
        // �ƥ����ѤΥ����С��λ����ֻ���ɽ�����������դ�¥����caseʸ��ʣ������Ǥ���褦�ˤ��Ƥ���
        switch ($_SERVER['SERVER_ADDR']) {
            case '10.1.3.252':
                $color = '';
                break;
            default:
                $color = ' color:red;';
        }
        return "
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <td align='left' width='30%' nowrap style='font-size:10pt; font-weight:normal; font-family:�ͣ� �Х����å�; border-width:0px;{$color}'>
                {$_SERVER['SERVER_NAME']} [{$_SERVER['SERVER_ADDR']}] [{$_SERVER['REMOTE_ADDR']}]
            </td>
            <td align='right' width='40%' nowrap style='font-size:10pt; font-weight:normal; font-family:�ͣ� �Х����å�; border-width:0px;'>
            </td>
            <td align='right' width='30%' nowrap style='font-size:10pt; font-weight:normal; font-family:�ͣ� �Х����å�; border-width:0px;'>
                {$script_name}&nbsp;&nbsp;{$u_id}&nbsp;{$name}
            </td>
        </table>\n";
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // GET/POST/REQUEST �Υ����ѡ������Х��ѿ��Υ������ƥ������å�        //
    ////////////////////////////////////////////////////////////////////////////
    protected function global_chk()
    {
        // SQL���󥸥���������к��Τ����addslashes($value)�� magic_quotes_gpc = On �����ꤷ�Ƥ��뤿���ά
        // �ʲ���strip_tags()��̵��̣���� �����ƻĤ� htmlspecialchars()�����礬���뤿��
        // �ƥ����ȥե�����ɤ䥨�ꥢ��� "<" �Υ�����ɬ�פʻ��� ���饹�Υ��󥹥��󥹤�����������˥������ѿ�����¸�����
        // 2005/07/07 ENT_QUOTES ���ɲ� "'" ���󥰥륯�����Ȥ��Ѵ����뤿��
        // htmlspecialchars($value, ENT_QUOTES) ���оݤ� &=&amp; "=&quot; '=&#039; <=&lt; >=&gt; ��5�ĤǤ���
        // htmlentities($value, ENT_QUOTES) �����Ƥ�HTMLʸ������ƥ��ƥ����Ѵ����롣
        foreach ($_GET as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    $_GET[$key][$key2] = strip_tags(htmlspecialchars($value2, ENT_QUOTES));
                }
            } else {
                $_GET[$key] = strip_tags(htmlspecialchars($value, ENT_QUOTES));
            }
        }
        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    $_POST[$key][$key2] = strip_tags(htmlspecialchars($value2, ENT_QUOTES));
                }
            } else {
                $_POST[$key] = strip_tags(htmlspecialchars($value, ENT_QUOTES));
            }
        }
        // $_GET, $_POST, $_COOKIE, $_FILES �����Ƥ��Ǽ����Ϣ������ �� $_FILES��;�פ���
        foreach ($_REQUEST as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    $_REQUEST[$key][$key2] = strip_tags(htmlspecialchars($value2, ENT_QUOTES));
                }
            } else {
                $_REQUEST[$key] = strip_tags(htmlspecialchars($value, ENT_QUOTES));
            }
        }
        // �ǡ�������Ѥ�CLI�ǥ�����ץ����Τ���˾嵭�εմؿ���ʲ��˼�����
        // function unhtmlentities ($string)
        // {
        //     $trans_tbl = get_html_translation_table (HTML_ENTITIES, ENT_QUOTES);
        //     $trans_tbl = array_flip ($trans_tbl);
        //     return strtr ($string, $trans_tbl);
        // }
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // �ꥢ�륿���९��å�ɽ����JavaScript����                               //
    ////////////////////////////////////////////////////////////////////////////
    protected function out_clock_java($controll_obj)
    {
        // $controll_obj = JavaScript�λ��֤ν񤭹����襳��ȥ��륪�֥�������
        $server_time = date('M d, Y H:i:s');    // 'Dec 31, 1999 23:59:59'�η���  Y,m,d�Ϸ��0��11�ʤΤǻȤ�ʤ�
        $clock_java  = "<script type='text/javascript'>";
        $clock_java .= "var DateTime = new Date('{$server_time}'); ";
        $clock_java .= "setInterval('baseJS.disp_clock(1000, {$controll_obj})', 1000);";
        $clock_java .= "</script>\n";
        return $clock_java;
    }
    
    ////////////////////////////////////////////////////////////////////////////
    // ���̥����������� JavaScript����                                        //
    // 1.���ܥ����� F12=123, F2=113  �ɤ���Ǥ�Ȥ���褦��                 //
    ////////////////////////////////////////////////////////////////////////////
    protected function common_backward_key()
    {
        $ret_java  = '';
        $ret_java .= "<script type='text/javascript'>";
        $ret_java .= "document.onkeydown = baseJS.evt_key_chk; ";
        $ret_java .= "var backward_obj = document.{$this->formName}; ";
        $ret_java .= "try {";
        $ret_java .= "    document.{$this->formName}.{$this->backwardName}.focus();";
        $ret_java .= "} catch (error) {";
        $ret_java .= "}";
        $ret_java .= "</script>\n";
        return $ret_java;
    }

} // class MenuHeader End
?>
