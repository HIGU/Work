<?php
//////////////////////////////////////////////////////////////////////////////
// ����DB�ơ��֥�Υڡ�������ȥ���ӥ塼�����ƥʥ� ���饹            //
// Copyright (C) 2004-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/08/31 Created   CommonTableClass.php                                //
// 2005/06/27 file̾��  ComTableMntClass.php �� Class̾�� ComTableMnt ���ѹ�//
// 2005/07/09 out_pageRecOptions_HTM()��out_pageControl_HTML()�᥽�å��ɲ�  //
// 2005/07/10 out_pageCtlOpt_HTML() �᥽�å��ɲ� (�嵭���碌�����)       //
//                                                                          //
// ���С������Ǥϥơ��֥��Խ����Υ�������ǽ�������������ͽ��         //
//  ��ʻ��ͤ� �Խ�����User_ID, ����, �Խ����ǡ���, �Խ���Υǡ��� ��Ͽ   //
//  error log ��DB�ޥͥ��㡼�ˤޤ�����                                      //
//                                                                          //
// 2005/07/15 �嵭�Υ�������� Ver 1.00 ��������꡼��                    //
// 2005/07/17 �ѹ�������Υǡ�����¸log_sql_save���ư����implode()�Ǥ��ѹ� //
// 2005/07/20 DB���������� Data Access Object daoInterfaceClass���ѹ�       //
// 2005/08/18 �ꥯ�����ȥ��֥������Ȥ��ɲä��ڡ�������Υǡ����򥫥ץ��벽  //
// 2005/09/13 ɽ���ڡ��������� set_view_page()����500��10000���ѹ�        //
// 2005/09/17 <form name='pageControl'�򥳥��� �桼������form���������� //
//            if (!is_numeric($default)) $default = 5;��$default = 20;���ѹ�//
// 2005/10/04 page_rec�򥳥󥹥ȥ饯�������ꤹ��褦���ѹ�(����ͤ��ѹ�����)//
//  Ver1.06     ����ͤ�ƥ�����ץȤ��ѹ������褦�ˤ��뤿��              //
// 2005/10/07 �ڡ�������򤷤ʤ�Listɽ����SQLʸ�μ¹ԥ᥽�åɤ��ɲ�         //
//  Ver1.07     public function execute_ListNotPageControl($sql='', &$res)  //
// 2005/10/31 E_ALL �� E_STRICT ���ѹ� â�������Ȥˤ����ץ�����椹��     //
//  Ver1.08     �嵭�ϥ��ץ�¦�� ?????_Main.php �����椵�����������Ȥ���  //
// 2005/11/14 out_pageControll_HTML() �� out_pageControl_HTML() �ѹ�        //
//  Ver1.09     �����ץߥ������ˤ��᥽�å�̾���ѹ�                        //
// 2005/11/25 php5.1.0��UP��__construct()�᥽�åɤ�ifʸ��($request == '')�� //
//  Ver1.10     (!is_object($request)) ���ѹ� Notice �б�                   //
// 2006/07/03 ���ҥߥ�����                                                //
//  Ver1.11                $con=funcConnect() �� $con = $this->connectDB()  //
// 2017/11/06 out_pageCtlOpt_HTML�Υǥե���Ȥ�30���ѹ����ᤷ��20��    ��ë //
// 2019/12/18 out_pageCtlOpt_HTML�Υǥե���Ȥ�25���ѹ�                ��ë //
// 2020/02/25 out_pageCtlOpt_HTML�Υǥե���Ȥ�30���ѹ�                ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// require_once ('function.php');              // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('daoInterfaceClass.php');     // Data Access Object ���饹

if (class_exists('ComTableMnt')) {
    return;
}
define('CTM_VERSION', '1.11');

/****************************************************************************
*                        sub class ��ĥ���饹�����                         *
****************************************************************************/
///// namespace Common {} �ϸ��߻��Ѥ��ʤ� �����㡧Common::ComTableMnt �� $obj = new Common::ComTableMnt;
///// Common::$sum_page, Common::set_page_rec() (̾������::��ƥ��)
class ComTableMnt extends daoInterfaceClass
{
    ///// Private properties
        /*********** �ڡ��������� *************/
    private $sum_page;                  // ��ץڡ�����
    private $sum_rec;                   // ��ץ쥳���ɿ�
    private $page_rec;                  // ���ڡ����Υ쥳���ɿ�(���� 1��10000)
    private $view_page;                 // ɽ���ڡ����ֹ�(1��XX)(���� 1��500)
    private $offset;                    // ɽ���ڡ����Υ��ե��å���(SQLʸ��OFFSET)
    private $request;                   // �ꥯ�����ȥ��֥�������
        /*********** �ơ��֥���ƥʥ��� *************/
    private $sql_select_sum;            // ��ץ쥳���ɿ�������SQLʸ
    private $sql_select;                // Listɽ����SQLʸ      (�Ǹ�˼¹Ԥ���SQLʸ����¸)
    private $sql_insert;                // �ǡ����ɲä�SQLʸ            ��
    private $sql_delete;                // �ǡ��������SQLʸ            ��
    private $sql_update;                // �ǡ����ѹ���SQLʸ            ��
        /*********** ���� *************/
    private $log_file;                  // ���ե�����̾
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ///// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($sql_sum='', $request='', $log_file='', $pageRec=20)
    {
        if (!isset($_SESSION)) @session_start();    // ���å����γ��ϥ����å� Notice ���򤱤����Ƭ��@
        if ($log_file == '') {                      // ���ե�����Υ����å�
            $this->log_openCheck('/tmp/ComTableMnt.log');   // Default��
        } else {
            $this->log_openCheck($log_file);
        }
        if ($sql_sum == '') {
            $this->log_writer('�ѥ�᡼����$sql_sum�����ꤵ��Ƥ��ޤ���');
            exit;                                   // error exit
        } elseif (!is_object($request)) {
            $this->log_writer('�ѥ�᡼����$request�����ꤵ��Ƥ��ޤ���');
            exit;                                   // error exit
        }
        $this->set_page_rec($pageRec);              // �����(���֤�����)
        $this->set_PageRequest($request, $sql_sum); // �ڡ��������ѤΥꥯ�����Ȳ��� & ����
    }
    
    /*************************** Set & Check methods ************************/
    // ���ڡ����Υ쥳���ɿ������� (���� 1��10000)
    public function set_page_rec($page_rec=20)
    {
        $page_rec = (int)$page_rec;
        if ($page_rec < 1) {
            $this->page_rec = 1;
        } elseif ($page_rec > 10000) {
            $this->page_rec = 10000;
        } else {
            $this->page_rec = $page_rec;
        }
        return $this->page_rec;
    }
    // ɽ���ڡ��������� (���� 1��10000) 2005/09/13 500��10000���ѹ�
    public function set_view_page($view_page=1)
    {
        $view_page = (int)$view_page;
        if ($view_page < 1) {
            $this->view_page = 1;
        } elseif ($view_page > $this->sum_page) {
            $this->view_page = $this->sum_page;
        } elseif ($view_page > 10000) {
            $this->view_page = 10000;
        } else {
            $this->view_page = $view_page;
        }
        return $this->view_page;
    }
    
    /*************************** Execute methods ************************/
    // Listɽ����SQLʸ�μ¹�    (SQLʸ�ˤ�offset/limit�礬�ʤ����Ȥ�����)
    // $view_page(ɽ���ڡ����ֹ�)�ϥ��ץ����Ȥ��ƻ����ǽ
    public function execute_List($sql='', &$res, $view_page='')
    {
        if ($sql == '') {
            return false;
        } elseif (!preg_match('/\bSELECT\b/i', $sql)) {
            return false;
        } else {
            if ($view_page != '') $view_page = $this->set_view_page($view_page);
            if (preg_match('/\bOFFSET\b/i', $sql)) return false;
            if (preg_match('/\bLIMIT\b/i', $sql))  return false;
            $sql .= $this->out_offsetLimit();
            $this->sql_select = $sql;
            $res = array();
            if( ($rows = $this->getResult2($sql, $res)) < 0) {
                return false;   // SQLʸ�Υ��顼
            } else {
                return $rows;
            }
        }
    }
    // Listɽ����SQLʸ�μ¹� ���ֹ��դ��֤� (SQLʸ�ˤ�offset/limit�礬�ʤ����Ȥ�����)
    // $view_page(ɽ���ڡ����ֹ�)�ϥ��ץ����Ȥ��ƻ����ǽ
    public function execute_ListRec($sql='', &$res, $view_page='')
    {
        if ($sql == '') {
            return false;
        } elseif (!preg_match('/\bSELECT\b/i', $sql)) {
            return false;
        } else {
            if ($view_page != '') $view_page = $this->set_view_page($view_page);
            if (preg_match('/\bOFFSET\b/i', $sql)) return false;
            if (preg_match('/\bLIMIT\b/i', $sql))  return false;
            $sql .= $this->get_offsetLimit();
            $this->sql_select = $sql;
            $res = array();
            if( ($rows = $this->getResult2($sql, $res)) < 0) {
                return false;   // SQLʸ�Υ��顼
            } else {
                $offset = $this->get_offset();
                $tmp = array();
                $field = count($res[0]);
                for ($r=0; $r<$rows; $r++) {
                    $tmp[$r][0] = ($offset + $r + 1);
                    for ($f=0; $f<$field; $f++) {
                        $tmp[$r][$f+1] = $res[$r][$f];
                    }
                }
                $res = $tmp;    // ���ֹ��դ��ѹ�
                return $rows;
            }
        }
    }
    // Listɽ����SQLʸ�μ¹� (�ڡ�������򤷤ʤ�)
    public function execute_ListNotPageControl($sql='', &$res)
    {
        if ($sql == '') {
            return false;
        } elseif (!preg_match('/\bSELECT\b/i', $sql)) {
            return false;
        } else {
            $this->sql_select = $sql;
            $res = array();
            if( ($rows = $this->getResult2($sql, $res)) < 0) {
                return false;   // SQLʸ�Υ��顼
            } else {
                return $rows;
            }
        }
    }
    // �ǡ����ɲä�SQLʸ�μ¹�
    public function execute_Insert($sql='')
    {
        if ($sql == '') {
            return false;
        } else {
            $this->sql_insert = $sql;
            if ($con = $this->connectDB()) {
                $this->query_affected_trans($con, 'BEGIN');
                if ( ($rows = $this->query_affected_trans($con, $sql)) > 0 ) {
                    $this->sum_rec += $rows;
                    $this->set_sumPage();   // ��ץڡ������κ�����
                    $this->query_affected_trans($con, 'COMMIT');
                    $this->log_writer("Insert: OK SQL={$sql}");
                    return $rows;
                }
                $this->query_affected_trans($con, 'ROLLBACK');
                $this->log_writer("Insert: NG SQL={$sql}");
            }
        }
        return false;
    }
    // �ǡ��������SQLʸ�μ¹� ���ץ�����$save_sql����ꤹ��к�����Υǡ����������¸
    public function execute_Delete($sql='', $save_sql='')
    {
        if ($sql == '') {
            return false;
        } else {
            $this->sql_delete = $sql;
            if ($con = $this->connectDB()) {
                $this->query_affected_trans($con, 'BEGIN');
                if ($save_sql != '') {
                    if (!$this->log_sql_save('Delete: ', $save_sql)) {
                        $this->query_affected_trans($con, 'ROLLBACK');
                        return false;
                    }
                }
                if ( ($rows = $this->query_affected_trans($con, $sql)) > 0 ) {
                    $this->sum_rec -= $rows;
                    $this->set_sumPage();   // ��ץڡ������κ�����
                    $this->query_affected_trans($con, 'COMMIT');
                    $this->log_writer("Delete: OK SQL={$sql}");
                    return $rows;
                }
                $this->query_affected_trans($con, 'ROLLBACK');
                $this->log_writer("Delete: NG SQL={$sql}");
            }
        }
        return false;
    }
    // �ǡ����ѹ���SQLʸ�μ¹� ���ץ�����$save_sql����ꤹ����ѹ����Υǡ����������¸
    public function execute_Update($sql='', $save_sql='')
    {
        if ($sql == '') {
            return false;
        } else {
            $this->sql_update = $sql;
            if ($con = $this->connectDB()) {
                $this->query_affected_trans($con, 'BEGIN');
                if ($save_sql != '') {
                    if (!$this->log_sql_save('Update: ', $save_sql)) {
                        $this->query_affected_trans($con, 'ROLLBACK');
                        return false;
                    }
                }
                if ( ($rows = $this->query_affected_trans($con, $sql)) > 0 ) {
                    $this->query_affected_trans($con, 'COMMIT');
                    $this->log_writer("Update: OK SQL={$sql}");
                    return $rows;
                }
                $this->query_affected_trans($con, 'ROLLBACK');
                $this->log_writer("Update: NG SQL={$sql}");
            }
        }
        return false;
    }
    
    /******************************* Get methods ****************************/
    // ��ץڡ������μ���
    public function get_sumPage()
    {
        return $this->sum_page;
    }
    // ��ץ쥳���ɿ��μ���
    public function get_sumRec()
    {
        return $this->sum_rec;
    }
    // ���ڡ����Υ쥳���ɿ��μ���
    public function get_pageRec()
    {
        return $this->page_rec;
    }
    // ɽ���ڡ����ֹ�μ���
    public function get_viewPage()
    {
        return $this->view_page;
    }
    ///// ɽ���ڡ����ֹ椫��SQLʸ��offset�ͤ����
    public function get_offset()
    {
        // $offset = ($this->page_rec * $this->view_page - $this->page_rec);
        return $this->offset;
    }
    
    /******************************* Out methods ****************************/
    // �ڡ�������ȥ����� �Կ����ڡ��� ����View HTML�Ǥν���
    // ������ˡ
    // <select name='pageRec' onChange='submit()'><����= $obj->out_pageRecOptions_HTML() ����></select>
    public function out_pageRecOptions_HTML($default=30)
    {
        if (!is_numeric($default)) $default = 30;
        $Options = '';
        switch ($default) {
        case    5:
        case   10:
        case   15:
        case   20:
        case   25:
        case   30:
        case   50:
        case  100:
        case  500:
        case 1000:
            break;
        default:
            $Options .= "<option value='{$default}' selected>{$default}��</option>";
        }
        if ($default == 5) {
            $Options .= "<option value='5' selected>5��</option>";
        } else {
            $Options .= "<option value='5'>5��</option>";
        }
        if ($default == 10) {
            $Options .= "<option value='10' selected>10��</option>";
        } else {
            $Options .= "<option value='10'>10��</option>";
        }
        if ($default == 15) {
            $Options .= "<option value='15' selected>15��</option>";
        } else {
            $Options .= "<option value='15'>15��</option>";
        }
        if ($default == 20) {
            $Options .= "<option value='20' selected>20��</option>";
        } else {
            $Options .= "<option value='20'>20��</option>";
        }
        if ($default == 25) {
            $Options .= "<option value='25' selected>25��</option>";
        } else {
            $Options .= "<option value='25'>25��</option>";
        }
        if ($default == 30) {
            $Options .= "<option value='30' selected>30��</option>";
        } else {
            $Options .= "<option value='30'>30��</option>";
        }
        if ($default == 50) {
            $Options .= "<option value='50' selected>50��</option>";
        } else {
            $Options .= "<option value='50'>50��</option>";
        }
        if ($default == 100) {
            $Options .= "<option value='100' selected>100��</option>";
        } else {
            $Options .= "<option value='100'>100��</option>";
        }
        if ($default == 500) {
            $Options .= "<option value='500' selected>500��</option>";
        } else {
            $Options .= "<option value='500'>500��</option>";
        }
        if ($default == 1000) {
            $Options .= "<option value='1000' selected>1000��</option>";
        } else {
            $Options .= "<option value='1000'>1000��</option>";
        }
        return $Options;
    }
    // �ڡ�������ȥ���View HTML�Ǥν���
    // ������ˡ
    // MVC �� View(HTML��) ��Ǥ�դθĽ�� <����= $obj->out_pageControl_HTML($menu->out_self()."?id={$uniq}") ����> ��������
    // ����ͤ��ѿ�̾�� 'back', 'next', 'selectPage' �Ǥ���������
    public function out_pageControl_HTML($action='')
    {
        if ($action == '') return '';
        $controll = "\n";
        // $controll .= "<form name='pageControl' method='get' action='{$action}'>\n";
        $controll .= "<table border='0'>\n";
        $controll .= "    <tr>\n";
        $controll .= "        <td nowrap>\n";
        if ($this->view_page > 1) $disabled = ''; else $disabled = ' disabled';
        $controll .= "            <input name='CTM_back' type='submit' value='������'{$disabled}>\n";
        $controll .= "        </td>\n";
        $controll .= "        <td nowrap align='center'>\n";
        $controll .= "            <select name='CTM_selectPage' onChange='submit()' style='text-align:right;'>\n";
        for ($i=1; $i<=$this->sum_page; $i++) {
            if ($i == $this->view_page) $selected = ' selected '; else $selected = '';
            $controll .= "                <option value='$i'{$selected}>$i</option>\n";
        }
        $controll .= "            </select>\n";
        $controll .= "            ��\n"; //{$this->sum_page}\n";
        $controll .= "            <select name='dummy' disabled style='text-align:right;'>\n";
        $controll .= "                <option value='{$this->sum_page}'>{$this->sum_page}</option>\n";
        $controll .= "            </select>\n";
        $controll .= "        </td>\n";
        $controll .= "        <td nowrap>\n";
        if ($this->view_page < $this->sum_page) $disabled = ''; else $disabled = ' disabled';
        $controll .= "            <input name='CTM_next' type='submit' value='���آ�'{$disabled}>\n";
        $controll .= "        </td>\n";
        $controll .= "        <input type='hidden' name='CTM_prePage' value='{$this->view_page}'>\n";
        $controll .= "    </tr>\n";
        $controll .= "</table>\n";
        // $controll .= "</form>\n";
        return $controll;
    }
    // �ڡ�������ȥ��� 1�ڡ����Υ쥳���ɿ����ꥪ�ץ������ View HTML�Ǥν���
    // ������ˡ
    // MVC �� View(HTML��) ��Ǥ�դθĽ�� <����= $obj->out_pageCtlOpt_HTML($menu->out_self()) ����> ��������
    // ����ͤ��ѿ�̾�� 'back', 'next', 'selectPage' �Ǥ���������
    public function out_pageCtlOpt_HTML($action='')
    {
        if ($action == '') return '';
        $controll = "\n";
        // $controll .= "<form name='pageControl' method='get' action='{$action}'>\n";
        $controll .= "<table border='0'>\n";
        $controll .= "    <tr>\n";
        $controll .= "        <td nowrap>\n";
        if ($this->view_page > 1) $disabled = ''; else $disabled = ' disabled';
        $controll .= "            <input name='CTM_back' type='submit' value='������'{$disabled}>\n";
        $controll .= "        </td>\n";
        $controll .= "        <td nowrap align='center'>\n";
        $controll .= "            <select name='CTM_selectPage' onChange='submit()' style='text-align:right;'>\n";
        for ($i=1; $i<=$this->sum_page; $i++) {
            if ($i == $this->view_page) $selected = ' selected '; else $selected = '';
            $controll .= "                <option value='$i'{$selected}>$i</option>\n";
        }
        $controll .= "            </select>\n";
        $controll .= "            ��\n"; //{$this->sum_page}\n";
        $controll .= "            <select name='dummy' disabled style='text-align:right;'>\n";
        $controll .= "                <option value='{$this->sum_page}'>{$this->sum_page}</option>\n";
        $controll .= "            </select>\n";
        $controll .= "        </td>\n";
        $controll .= "        <td nowrap>\n";
        if ($this->view_page < $this->sum_page) $disabled = ''; else $disabled = ' disabled';
        $controll .= "            <input name='CTM_next' type='submit' value='���آ�'{$disabled}>\n";
        $controll .= "        </td>\n";
        $controll .= "        <input type='hidden' name='CTM_prePage' value='{$this->view_page}'>\n";
        $controll .= "        <td nowrap>\n";
        $controll .= "            <select name='CTM_pageRec' onChange='submit()' style='text-align:right;'>\n";
        $controll .= "                {$this->out_pageRecOptions_HTML($this->page_rec)}\n";
        $controll .= "            </select>\n";
        $controll .= "        </td>\n";
        $controll .= "    </tr>\n";
        $controll .= "</table>\n";
        // $controll .= "</form>\n";
        return $controll;
    }
    
    ///// �ڡ��������Ѥ�HTML GET�᥽�å��ѥѥ�᡼��������
    public function get_htmlGETparm()
    {
        return "CTM_viewPage={$this->view_page}&CTM_pageRec={$this->page_rec}";
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ///// �ꥯ�����ȤΥڡ��������ѥǡ�������������
    protected function set_PageRequest($request, $sql_sum)
    {
        $pageRec    = $request->get('CTM_pageRec');
        $back       = $request->get('CTM_back');
        $next       = $request->get('CTM_next');
        $selectPage = $request->get('CTM_selectPage');
        $prePage    = $request->get('CTM_prePage');
        $viewPage   = $request->get('CTM_viewPage');    // ��������򤷤ư����������Υڡ���������
        if ($pageRec != '') {
            if (!is_numeric($pageRec)) $pageRec = $this->page_rec;
        } else {
            $pageRec = $this->page_rec;  // ����ͤϥ��󥹥ȥ饯�������ꤵ���
        }
        if ($back != '') {
            $viewPage = ($prePage - 1);
        } elseif ($next != '') {
            $viewPage = ($prePage + 1);
        } elseif ($selectPage != '') {
            if (is_numeric($selectPage)) $viewPage = $selectPage; else $viewPage = 1;
        } elseif ($viewPage != '') {
            if (!is_numeric($viewPage)) $viewPage = 1;
        } else {
            $viewPage = 1;  // �����
        }
        $this->set_page_rec($pageRec);              // �����(���֤�����)
        $this->set_sumPageRec($sql_sum);            // ������ǹ�ץڡ��������쥳���ɿ�������
        $this->set_view_page($viewPage);            // �����(���֤�����)
        return;
    }
    ///// ��ץڡ��������쥳���ɿ��μ��������� (SQLʸ��count()����Ѥ��뤳�Ȥ�����)
    protected function set_sumPageRec($sql='')
    {
        if ($sql == '') {
            return false;
        } else {
            $this->sql_select_sum = $sql;
            $sum_rec = 0;
            $this->getUniResult($sql, $sum_rec);
            $this->sum_rec = $sum_rec;
            $this->set_sumPage();
            return $sum_rec;
            // $_SESSION['s_sysmsg'] = "�ǡ����١����Υ��顼�Ǥ��� ����ô���Ԥ�Ϣ���Ʋ�������";
            // $this->log_writer("DB sum error SQL={$sql_sum}");
            // header('location: ' . H_WEB_HOST . ERROR . 'ErrorComTableMntClass.php?status=2');
            // exit;   // SQLʸ��error����DB error
        }
    }
    ///// ��ץڡ�����������
    protected function set_sumPage()
    {
        if ($this->sum_rec > 0) {
            $this->sum_page  = (int)($this->sum_rec / $this->page_rec);
            if ( ($this->sum_rec % $this->page_rec) > 0 ) $this->sum_page += 1;
        } else {
            $this->sum_page = 1;    // �ơ��֥�˥쥳���ɤ��ʤ����϶���Ū�˹�ץڡ������򣱤ˤ���
        }
    }
    ///// ɽ���ڡ����ֹ椫��SQLʸ��offset/limit����֤�
    protected function out_offsetLimit()
    {
        $offset = ($this->page_rec * $this->view_page - $this->page_rec);
        $this->offset = $offset;    // �ץ�ѥƥ�����¸
        $limit  = $this->page_rec;
        return " OFFSET {$offset} LIMIT {$limit}";
    }
    
    ///// ���ե����륪���ץ�Υ����å�
    protected function log_openCheck($log_name)
    {
        if ( !($fp_log = fopen($log_name, 'a')) ) {
            if (isset($_SESSION)) {
                $_SESSION['s_sysmsg'] = "���ե����롧{$log_name} �򥪡��ץ�Ǥ��ޤ���";
                header('location: ' . H_WEB_HOST . ERROR . 'ErrorComTableMntClass.php?status=1');
                exit;
            } else {
                echo "���ե����롧{$log_name} �򥪡��ץ�Ǥ��ޤ���\n";
                exit;
            }
        } else {
            fclose($fp_log);
            $this->log_name = $log_name;
        }
        return;
    }
    ///// ���饹�ⶦ�ѥ�����ߥ᥽�å�
    protected function log_writer($msg)
    {
        $msg = date('Y-m-d H:i:s ') . "User={$_SESSION['User_ID']} Auth={$_SESSION['Auth']}\n    {$msg}\n";
        if ( ($fp_log = fopen($this->log_name, 'a')) ) {
            fwrite($fp_log, $msg);
        } else {
            ///// ���٤����ƻ�Ԥ���
            sleep(1);
            if ( ($fp_log = fopen($this->log_name, 'a')) ) {
                fwrite($fp_log, $msg);
            }
        }
        fclose($fp_log);
        return;
    }
    ///// �ơ��֥��ѹ������ ���Υǡ�������˽���
    protected function log_sql_save($prefix='', $save_sql='')
    {
        if (!preg_match('/\bSELECT\b/i', $save_sql)) return false;
        $res = array();
        if ( ($rows = $this->getResult2($save_sql, $res)) > 0) {
            /************
            $save_data = $res[0][0];    // �ǽ��1�ե������
            $field     = count($res[0]);
            for ($r=0; $r<$rows; $r++) {
                for ($f=1; $f<$field; $f++) {
                    $save_data .= ("\t" . $res[$r][$f]);    // TAB���ڤ�
                }
            }
            ************/
            for ($r=0; $r<$rows; $r++) {
                $save_data = implode("\t", $res[$r]);   // ��ư����implode()�Ǥ��ѹ�
                $this->log_writer("{$prefix}save data=\n{$save_data}");
            }
            return true;
        } else {
            $this->log_writer("{$prefix}save error={$save_sql}");
            return false;
        }
    }
    
} // Class ComTableMnt End

?>
