<?php
//////////////////////////////////////////////////////////////////////////////
// ���� �߸� ͽ�� �Ȳ� (������ȯ������Ȳ�)                   MVC Model ��   //
// Copyright (C) 2006-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/05/25 Created   parts_stock_plan_Model.php                          //
// 2006/05/28 �᥽�åɤ� getViewHTMLtable() �� getViewHTMLbody()��̾���ѹ�  //
// 2007/02/08 �����ȥ�ԤΥ�å������ $title��nowrap�ɲä�getViewHTMLconst //
//            ('header')�� overflow-x:hidden; overflow-y:scroll; ���ɲ�     //
//            ���ͤΥ֥���б���if ($res[$i][9] == '') {���ɲ�            //
// 2007/02/09 �嵭�����ͤΥ֥�󥯥����å�����ߢ�parts_stock_plan()PL/pgsql//
// 2007/02/22 ���Ѥΰ����ξ��˥�åפ��Ƥ��ޤ�����ײ��ֹ�� nowrap �ɲ�  //
// 2007/02/26 ����̾��15ʸ�����ѹ� mb_substr($res[$i][4], 0, 15)            //
//            Ⱦ�ѥ��ʤξ���Ѵ���ʸ�����������С�����Τ��Ѵ���15ʸ���ˤ���//
//            �嵭��ȼ������̾��nowrap �����ֹ椬SC���֤λ� yellow �ɲ�     //
// 2007/03/02 ������ͽ��ײ���ѹ����뤿��getPlanStatus()�᥽�åɤ��ɲ�   //
// 2007/03/24 header �� $title = mb_convert_kana($title, 'k') ���ɲ�        //
// 2007/04/27 getViewHTMLbody()�᥽�åɤ˹������Ǽ�����ɲ� (����darkred)   //
// 2007/05/17 getViewHTMLfooter()�᥽�åɤ˷�ʿ�ѽи˿�����ͭ����ɲ�       //
// 2007/05/21 ɬ�����Υ����å��Ѥ�getQueryStatement()�᥽�åɤ˥��å��ɲ� //
// 2007/06/22 getViewHTMLconst()��MenuHeader���饹��out_retF2Script()���ɲ� //
//                �� �θƽФ����� noMenu �Υѥ�᡼�������å���Ԥ�         //
// 2016/08/08 mouseOver���ɲ�                                          ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class PartsStockPlan_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $last_avail_pcs;                    // �ǽ�ͭ����(�ǽ�ͽ��߸˿�)
    
    ///// public properties
    // public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        ///// ����WHERE�������
        switch ($request->get('showMenu')) {
        case 'List':
        case 'ListWin':
            // $this->where = $this->SetInitWhere($request);
            // break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    ����եǡ��������� ����ɽ
    public function outViewListHTML($request, $menu)
    {
                /***** �إå���������� *****/
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $headHTML .= $this->getViewHTMLheader($request);
        // �����HTML�����������
        if ($request->get('noMenu') == '') {
            $headHTML .= $this->getViewHTMLconst('footer', $menu);
        } else {
            $headHTML .= $this->getViewHTMLconst('footer');
        }
        // HTML�ե��������
        $file_name = "list/parts_stock_plan_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** ��ʸ����� *****/
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLbody($request, $menu);
        // �����HTML�����������
        if ($request->get('noMenu') == '') {
            $listHTML .= $this->getViewHTMLconst('footer', $menu);
        } else {
            $listHTML .= $this->getViewHTMLconst('footer');
        }
        // HTML�ե��������
        $file_name = "list/parts_stock_plan_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** �եå���������� *****/
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $footHTML .= $this->getViewHTMLfooter($request);
        // �����HTML�����������
        if ($request->get('noMenu') == '') {
            $footHTML .= $this->getViewHTMLconst('footer', $menu);
        } else {
            $footHTML .= $this->getViewHTMLconst('footer');
        }
        // HTML�ե��������
        $file_name = "list/parts_stock_plan_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    ///// ���ʤΥ����Ȥ���¸
    public function commentSave($request)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        // if ($request->get('comment') == '') return;  // �����Ԥ��Ⱥ���Ǥ��ʤ�
        if ($request->get('targetPlanNo') == '') return;
        if ($request->get('targetAssyNo') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $query = "SELECT comment FROM assembly_time_plan_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
        if (getUniResult($query, $comment) < 1) {
            $sql = "
                INSERT INTO assembly_time_plan_comment (assy_no, plan_no, comment, last_date, last_host)
                values ('{$request->get('targetAssyNo')}', '{$request->get('targetPlanNo')}', '{$request->get('comment')}', '{$last_date}', '{$last_host}')
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�����Ȥ���¸������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            }
        } else {
            $sql = "
                UPDATE assembly_time_plan_comment SET comment='{$request->get('comment')}',
                last_date='{$last_date}', last_host='{$last_host}'
                WHERE plan_no='{$request->get('targetPlanNo')}'
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�����Ȥ���¸������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            }
        }
        return ;
    }
    
    ///// ���ʤΥ����Ȥ����
    public function getComment($request, $result)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        if ($request->get('targetAssyNo') == '') return '';
        $query = "
            SELECT  comment  ,
                    trim(substr(midsc, 1, 20))
            FROM miitem LEFT OUTER JOIN
            assembly_time_plan_comment ON(mipn=assy_no)
            WHERE mipn='{$request->get('targetAssyNo')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $result->add('comment', $res[0][0]);
            $result->add('assy_name', $res[0][1]);
            $result->add('title', "{$request->get('targetPlanNo')}��{$request->get('targetAssyNo')}��{$res[0][1]}");
            return true;
        } else {
            return false;
        }
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���WHERE�������
    protected function SetInitWhere($request)
    {
        // ���ȥ����ɥץ������㡼�η���
        // SELECT * FROM assembly_schedule_time_line($request->get('targetDateStr'), $request->get('targetDateEnd'), '$request->get('targetLine')')
        if ($request->get('showMenu') == 'Graph') {
            $where = "{$request->get('targetDateStr')}, {$request->get('targetDateEnd')}, '{$request->get('targetLine')}'";
        } else {
            $where = "{$request->get('targetDateList')}, {$request->get('targetDateList')}, '{$request->get('targetLine')}'";
        }
        return $where;
    }
    
    ///// �ײ��ֹ椫�������ֹ桦����̾���ײ���������������
    protected function getPlanData($request, &$res)
    {
        // �ײ��ֹ椫�������ֹ�μ���(���ӥǡ�����̵�������б�)
        $query = "SELECT parts_no       AS �����ֹ�     -- 00
                        ,substr(midsc, 1, 20)
                                        AS ����̾       -- 01
                        ,plan-cut_plan  AS �ײ��       -- 02
                        ,kansei         AS ������       -- 03
                    FROM assembly_schedule
                    LEFT OUTER JOIN
                        miitem ON (parts_no=mipn)
                    WHERE plan_no='{$request->get('targetPlanNo')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $res['assy_no']   = $res[0][0];
            $res['assy_name'] = $res[0][1];
            $res['keikaku']   = $res[0][2];
            $res['kansei']    = $res[0][3];
            return true;
        } else {
            $res['assy_no']   = '';
            $res['assy_name'] = '';
            $res['keikaku']   = '';
            $res['kansei']    = '';
            return false;
        }
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��   ��Ω�Υ饤���̹�������դ� ���٥ǡ�������
    private function getViewHTMLbody($request, $menu)
    {
        $query = $this->getQueryStatement($request);
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>�ǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $listTable .= "    <tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
            $listTable .= "        <td class='winbox' width='79%' colspan='8' align='right'>{$res[0][4]}</td>\n";
            $listTable .= "        <td class='winbox' width=' 9%' colspan='1' align='right'>{$res[0][7]}</td>\n";
            $listTable .= "        <td class='winbox' width='12%' colspan='2' align='right'>&nbsp;</td>\n";
            $listTable .= "    </tr>\n";
            for ($i=1; $i<$rows; $i++) {
                $res[$i][4] = mb_convert_kana($res[$i][4], 'k');    // ����ä�Ⱦ�ѥ��ʤ��Ѵ�
                $res[$i][4] = mb_substr($res[$i][4], 0, 15);        // Ⱦ�ѥ��ʤξ�祪���С�����Τ��Ѵ���15ʸ���ˤ���
                if (mb_substr($res[$i][3], 0, 3) == '������') {
                    $colorKen = " style=' color:blue;'";
                } elseif (substr($res[$i][3], 0, 2) == 'SC') {
                    $colorKen = " style=' color:yellow;'";
                } elseif (mb_substr($res[$i][4], 0, 2) == '����') {
                    $colorKen = " style=' color:darkred;'";
                } else {
                    $colorKen = '';
                }
                if ($request->get('aden_key') != '') {
                    if ($res[$i][2] == $request->get('aden_plan')) $colorPlan=" style=' background-color:#ffffc6;'"; else $colorPlan = '';
                } else {
                    if ($this->getPlanStatus($res[$i][2]) == 'P') $colorPlan=" style=' background-color:#ffffc6;'"; else $colorPlan = '';
                }
                $listTable .= "    <tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i) . "</td>\n";      // ���ֹ�
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>{$res[$i][0]}</td>\n";     // ������
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>{$res[$i][1]}</td>\n";     // �»���
                $listTable .= "        <td class='winbox' width='10%' align='right' nowrap{$colorPlan}>{$res[$i][2]}</td>\n";   // �ײ��ֹ�
                $listTable .= "        <td class='winbox' width='12%' align='right'{$colorKen}>{$res[$i][3]}</td>\n";           // �����ֹ�
                $listTable .= "        <td class='winbox' width='18%' align='left'  nowrap{$colorKen}>{$res[$i][4]}</td>\n";    // ����̾
                $listTable .= "        <td class='winbox' width=' 9%' align='right' >{$res[$i][5]}</td>\n";     // ������
                $listTable .= "        <td class='winbox' width=' 9%' align='right' >{$res[$i][6]}</td>\n";     // ȯ���
                $listTable .= "        <td class='winbox' width=' 9%' align='right' >{$res[$i][7]}</td>\n";     // ͭ����
                $listTable .= "        <td class='winbox' width=' 4%' align='center'>{$res[$i][8]}</td>\n";     // �����å�
                $listTable .= "        <td class='winbox' width=' 8%' align='left'>{$res[$i][9]}</td>\n";       // ����
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
            $this->last_avail_pcs = $res[$rows-1][7];
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   ����ɽ�� �إå����������
    private function getViewHTMLheader($request)
    {
        // �����ȥ��SQL�Υ��ȥ����ɥץ������㡼�������
        $query = "SELECT parts_stock_title('{$request->get('targetPartsNo')}')";
        $title = '';
        $this->getUniResult($query, $title);
        $title = mb_convert_kana($title, 'k');  // ���ѥ��ʤǤϲ��������꤭��ʤ���礬���뤿��Ⱦ�Ѥ�
        if (!$title) {  // �쥳���ɤ�̵������NULL�쥳���ɤ��֤뤿���ѿ������Ƥǥ����å�����
            $title = '�����ƥ�ޥ�����̤��Ͽ��';
        }
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' colspan='11' nowrap>{$title}</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>������</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>�»���</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>�ײ��ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>�����ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='18%'>�����ʡ�̾</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>������</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>ȯ���</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>ͭ����</th>\n";
        $listTable .= "        <th class='winbox' width=' 4%'>CK</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>����</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��   ����ɽ�� �եå����������
    private function getViewHTMLfooter($request)
    {
        // �׻����߸˿�����ʿ�ѽи˿�����ͭ����ɲ�
        $query = "
            SELECT invent_pcs, month_pickup_avr, hold_monthly_avr FROM inventory_average_summary
            WHERE parts_no = '{$request->get('targetPartsNo')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $invent = number_format($res[0][0], 0);
            $pickup = number_format($res[0][1], 0);
            $month  = number_format($res[0][2], 1);
            $footer_title = "�׻����߸ˡ�{$invent}����ʿ�ѽиˡ�{$pickup}��<span style='color:teal;'>��ͭ�{$month}</span>";
        } else {
            $footer_title = '&nbsp;';
        }
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='60%' align='center'>{$footer_title}</td>\n";
        $listTable .= "        <td class='winbox' width='19%' align='right'>�ǽ�ͭ���߸˿�</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right'>{$this->last_avail_pcs}</td>\n";
        $listTable .= "        <td class='winbox' width='12%' align='right'>&nbsp;</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��   ����ɽ��SQL���ơ��ȥ��ȼ���
    private function getQueryStatement($request)
    {
        $query = "
            SELECT   CASE
                        WHEN syuka = 0 THEN '̤��'
                        ELSE substr(to_char(syuka, 'FM9999/99/99'), 6, 5)
                     END            AS ������                   -- 00
                    ,CASE
                        WHEN chaku = 0 THEN '̤��'
                        ELSE substr(to_char(chaku, 'FM9999/99/99'), 6, 5)
                     END            AS �»���                   -- 01
                    ,plan_no        AS �ײ��ֹ�                 -- 02
                    ,CASE
                        WHEN assy_no = '' THEN '&nbsp;'
                        ELSE assy_no
                     END            AS �����ֹ�                 -- 03
                    ,CASE
                        WHEN assy_name = '' THEN '&nbsp;'
                        ELSE substr(assy_name, 1, 15)
                     END            AS ����̾                   -- 04
                    ,CASE
                        WHEN allocate = 0 THEN '&nbsp;'
                        ELSE to_char(allocate, 'FM9,999,999')
                     END            AS ������                   -- 05
                    ,CASE
                        WHEN order_pcs = 0 THEN '&nbsp;'
                        ELSE to_char(order_pcs, 'FM9,999,999')
                     END            AS ȯ���                   -- 06
                    ,CASE
                        WHEN avail_pcs IS NULL THEN '&nbsp;'
                        ELSE to_char(avail_pcs, 'FM9,999,999')
                     END            AS ͭ����                   -- 07
                    ,CASE
                        WHEN avail_msg = '' THEN '&nbsp;'
                        ELSE avail_msg
                     END            AS �����å�                 -- 08
                    ,CASE
                        WHEN note = '' THEN '&nbsp;'
                        ELSE note
                     END            AS ����                     -- 09
            FROM
        ";
        if ($request->get('requireDate')) {
            $query .= "
                parts_stock_plan('{$request->get('targetPartsNo')}', 'ɬ����')
            ";
        } else {
            $query .= "
                parts_stock_plan('{$request->get('targetPartsNo')}')
            ";
        }
        return $query;
    }
    
    ///// �����List��    HTML�ե��������
    private function getViewHTMLconst($status, $menu='')
    {
        if ($status == 'header') {
            $listHTML = 
"
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title>���ʺ߸�ͽ��Ȳ�</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../parts_stock_plan.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:   none;
    overflow-x:         hidden;
    overflow-y:         scroll;
}
-->
</style>
<script type='text/javascript' src='../parts_stock_plan.js'></script>
</head>
<body style='background-color:#d6d3ce;'>
<center>
";
        } elseif ($status == 'footer') {
            if (is_object($menu)) {
                $listHTML = $menu->out_retF2Script('_parent', 'N');
            } else {
                $listHTML = '';
            }
            $listHTML .= "</center>\n";
            $listHTML .= "</body>\n";
            $listHTML .= "</html>\n";
        } else {
            $listHTML = '';
        }
        return $listHTML;
    }
    
    ///// �ײ��ֹ��Ŭ��������å����ƥơ��֥뤫�� ����=F, ͽ��=P �����ײ�̵��=''���֤�
    private function getPlanStatus($plan_no)
    {
        $p_kubun = '';
        if (strlen($plan_no) == 8) {
            $query = "
                SELECT p_kubun FROM assembly_schedule WHERE plan_no='{$plan_no}'
            ";
            $this->getUniResult($query, $p_kubun);
            return $p_kubun;
        }
        return $p_kubun;
    }
    
} // Class PartsStockPlan_Model End

?>
