<?php
//////////////////////////////////////////////////////////////////////////////
// ���饹�ο���                                              MVC Model ��   //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/09/21 Created   class_template_Model.php                            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class ClassTemplate_Model extends daoInterfaceClass
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
    
    ///// �о�ǯ���HTML <select> option �ν���
    public function getTargetDateYMvalues($request)
    {
        // �����
        $option = "\n";
        $yyyymm = date('Ym'); $yyyy = date('Y'); $mm = date('m');
        if ($request->get('targetDateYM') == $yyyymm) {
            $option .= "<option value='{$yyyymm}' selected>{$yyyy}ǯ{$mm}��</option>\n";
        } else {
            $option .= "<option value='{$yyyymm}'>{$yyyy}ǯ{$mm}��</option>\n";
        }
        for ($i=1; $i<=12; $i++) {   // 12�������ޤ�
            $mm--;
            if ($mm < 1) {
                $mm = 12; $yyyy -= 1;
            }
            $mm = sprintf('%02d', $mm);
            $yyyymm = $yyyy . $mm;
            if ($request->get('targetDateYM') == $yyyymm) {
                $option .= "<option value='{$yyyymm}' selected>{$yyyy}ǯ{$mm}��</option>\n";
            } else {
                $option .= "<option value='{$yyyymm}'>{$yyyy}ǯ{$mm}��</option>\n";
            }
        }
        return $option;
    }
    
    ///// ���������Υ���դ�����
    public function graphLeadTime($request, $result)
    {
        $query = "
            SELECT CAST(end_timestamp AS DATE) - CAST(to_char(uke_date, '9999-99-99') AS DATE) AS ��������, count(order_seq) AS ���
            FROM acceptance_kensa LEFT OUTER JOIN order_data USING(order_seq)
            WHERE CAST(end_timestamp AS DATE) >= '{$request->get('targetDateStr')}' AND CAST(end_timestamp AS DATE) <= '{$request->get('targetDateEnd')}'
            GROUP BY ��������
            ORDER BY �������� ASC ;
        ";
        if ($this->getResult2($query, $res) <= 0) {
            
        }
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    �ǡ��������� ����ɽ
    public function outViewListHTML($request, $menu)
    {
                /***** �إå���������� *****/
        /*****************
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $headHTML .= $this->getViewHTMLheader($request);
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        *****************/
        
                /***** ��ʸ����� *****/
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLbody($request, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** �եå���������� *****/
        /************************
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $footHTML .= $this->getViewHTMLfooter();
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        ************************/
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
        if ($this->getUniResult($query, $comment) < 1) {
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
        $temp = array();
        $query = $this->getQueryStatement1($request);
        $c_all_jisseki = $this->getResult2($query, $temp);      // ���ץ����ΥС������ɷ��
        $query = $this->getQueryStatement2($request);
        $c_toku_jisseki = $this->getResult2($query, $temp);     // ���������С������ɷ��
        $c_std_jisseki = $c_all_jisseki - $c_toku_jisseki;      // ���ץ�ɸ��С������ɷ��
        
        $query = $this->getQueryStatement3($request);
        $c_all_plan = $this->getResult2($query, $temp);         // ���ץ����� �ײ���
        $query = $this->getQueryStatement4($request);
        $c_toku_plan = $this->getResult2($query, $temp);        // ���ץ����� �ײ���
        $c_std_plan = $c_all_plan - $c_toku_plan;               // ���ץ�ɸ�� �ײ���
        
        $query = $this->getQueryStatement5($request);
        $c_all_time = 0;
        $this->getUniResult($query, $c_all_time);               // ���ץ����� �и˻���
        $query = $this->getQueryStatement6($request);
        $c_toku_time = 0;
        $this->getUniResult($query, $c_toku_time);              // ���ץ����� �и˻���
        $c_std_time = $c_all_time - $c_toku_time;               // ���ץ�ɸ�� �и˻���
        
        
        $query = $this->getQueryStatement11($request);
        $l_all_jisseki = $this->getResult2($query, $temp);      // ��˥����ΥС������ɷ��
        $query = $this->getQueryStatement12($request);
        $l_all_plan = $this->getResult2($query, $temp);         // ��˥����� �ײ���
        $query = $this->getQueryStatement13($request);
        $l_all_time = 0;
        $this->getUniResult($query, $l_all_time);               // ��˥����� �и˻���
        
        
        $query = $this->getQueryStatement7($request);
        $c_std_pcs = 0;
        $this->getUniResult($query, $c_std_pcs);                // ���ץ�ɸ�� �и˸Ŀ�
        
        $query = $this->getQueryStatement8($request);
        $c_toku_pcs = 0;
        $this->getUniResult($query, $c_toku_pcs);               // ���ץ����� �и˸Ŀ�
        
        $query = $this->getQueryStatement9($request);
        $rinear_pcs = 0;
        $this->getUniResult($query, $linear_pcs);               // ��˥� �и˸Ŀ�
        
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ($c_all_time == 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>�ǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $listTable .= "    <tr>\n";
            $listTable .= "        <th class='winbox'>&nbsp;</th>\n";
            $listTable .= "        <th class='winbox'>���ץ�����</th>\n";
            $listTable .= "        <th class='winbox'>���ץ�����</th>\n";
            $listTable .= "        <th class='winbox'>���ץ�ɸ��</th>\n";
            $listTable .= "        <th class='winbox'>��˥�����</th>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center'>�и˹��� �ײ��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_all_plan) . "��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_toku_plan) . "��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_std_plan) . "��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($l_all_plan) . "��</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center'>�С����������Ϸײ��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_all_jisseki) . "��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_toku_jisseki) . "��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_std_jisseki) . "��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($l_all_jisseki) . "��</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center'>�С�����������Ψ</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_all_jisseki / $c_all_plan * 100, 1) . "��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_toku_jisseki / $c_toku_plan * 100, 1) . "��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_std_jisseki / $c_std_plan * 100, 1) . "��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($l_all_jisseki / $l_all_plan * 100, 1) . "��</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center'>�С����������ϻ���</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_all_time) . "ʬ</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_toku_time) . "ʬ</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_std_time) . "ʬ</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($l_all_time) . "ʬ</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center'>���ײ褢�����ʿ�ѽи˻���</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_all_time / $c_all_jisseki, 1) . "ʬ</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_toku_time / $c_toku_jisseki, 1) . "ʬ</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_std_time / $c_std_jisseki, 1) . "ʬ</td>\n";
            if ($l_all_jisseki != 0) {
                $listTable .= "        <td class='winbox' align='right'>" . number_format($l_all_time / $l_all_jisseki, 1) . "ʬ</td>\n";
            } else {
                $listTable .= "        <td class='winbox' align='right'>---ʬ</td>\n";
            }
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        $listTable .= "<br>\n";
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <caption>�ʼ��ݾڲ��� �����</caption>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ($c_std_pcs == 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>�ǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $listTable .= "    <tr>\n";
            $listTable .= "        <th class='winbox'>&nbsp;</th>\n";
            $listTable .= "        <th class='winbox'>���ץ�ɸ��</th>\n";
            $listTable .= "        <th class='winbox'>���ץ�����</th>\n";
            $listTable .= "        <th class='winbox'>��˥�����</th>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' align='center'>��� �и� �Ŀ�</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_std_pcs) . "��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($c_toku_pcs) . "��</td>\n";
            $listTable .= "        <td class='winbox' align='right'>" . number_format($linear_pcs) . "��</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
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
        if (!$title) {  // �쥳���ɤ�̵������NULL�쥳���ɤ��֤뤿���ѿ������Ƥǥ����å�����
            $title = '�����ƥ�ޥ�����̤��Ͽ��';
        }
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' colspan='11'>{$title}</th>\n";
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
    private function getViewHTMLfooter()
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='79%' align='right'>�ǽ�ͭ���߸˿�</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right'>{$this->last_avail_pcs}</td>\n";
        $listTable .= "        <td class='winbox' width='12%' align='right'>&nbsp;</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��   ����ɽ��SQL���ơ��ȥ��ȼ���
    // �С��������������Τηײ��� ���ץ�����
    private function getQueryStatement1($request)
    {
        $query = "
            SELECT count(time.plan_no) FROM acceptance_inspection_time AS time
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE time.end_time >= '{$request->get('targetDateStr')} 070000' AND time.end_time <= '{$request->get('targetDateEnd')} 235959'
            AND sche.dept = 'C'
            GROUP BY time.plan_no
        ";
        return $query;
    }
    
    // �С���������������ηײ��� ���ץ�����
    private function getQueryStatement2($request)
    {
        $query = "
            SELECT count(time.plan_no) FROM acceptance_inspection_time AS time
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE time.end_time >= '{$request->get('targetDateStr')} 070000' AND time.end_time <= '{$request->get('targetDateEnd')} 235959'
            AND sche.dept = 'C' AND sche.note15 LIKE 'SC%' -- ����
            GROUP BY time.plan_no
        ";
        return $query;
    }
    
    // �и����Τηײ��� ���ץ�����
    private function getQueryStatement3($request)
    {
        $query = "
            SELECT count(hist.plan_no) FROM parts_stock_history AS hist
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE hist.upd_date >= {$request->get('targetDateStr')} AND hist.upd_date <= {$request->get('targetDateEnd')}
            AND hist.den_kubun = '3' AND hist.out_id = '2'
            AND sche.dept = 'C'
            GROUP BY hist.plan_no
        ";
        return $query;
    }
    
    // �и����Τηײ��� ���ץ�����
    private function getQueryStatement4($request)
    {
        $query = "
            SELECT count(hist.plan_no) FROM parts_stock_history AS hist
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE hist.upd_date >= {$request->get('targetDateStr')} AND hist.upd_date <= {$request->get('targetDateEnd')}
            AND hist.den_kubun = '3' AND hist.out_id = '2'
            AND sche.dept = 'C' AND sche.note15 LIKE 'SC%' -- ����
            GROUP BY hist.plan_no
        ";
        return $query;
    }
    
    // �С������ɤ����ϻ��� ���ץ�����
    private function getQueryStatement5($request)
    {
        $query = "
            SELECT sum(pick_time) FROM acceptance_inspection_time AS time
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE time.end_time >= '{$request->get('targetDateStr')} 080000' AND time.end_time <= '{$request->get('targetDateEnd')} 210000'
            AND sche.dept = 'C'
        ";
        return $query;
    }
    
    // �С������ɤ����ϻ��� ���ץ�����
    private function getQueryStatement6($request)
    {
        $query = "
            SELECT sum(pick_time) FROM acceptance_inspection_time AS time
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE time.end_time >= '{$request->get('targetDateStr')} 080000' AND time.end_time <= '{$request->get('targetDateEnd')} 210000'
            AND sche.dept = 'C' AND sche.note15 LIKE 'SC%' -- ����
        ";
        return $query;
    }
    
    // �С��������������Τηײ��� ��˥�����
    private function getQueryStatement11($request)
    {
        $query = "
            SELECT count(time.plan_no) FROM acceptance_inspection_linear AS time
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE time.end_time >= '{$request->get('targetDateStr')} 070000' AND time.end_time <= '{$request->get('targetDateEnd')} 235959'
            AND sche.dept = 'L'
            GROUP BY time.plan_no
        ";
        return $query;
    }
    
    // �и����Τηײ��� ��˥�����
    private function getQueryStatement12($request)
    {
        $query = "
            SELECT count(hist.plan_no) FROM parts_stock_history AS hist
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE hist.upd_date >= {$request->get('targetDateStr')} AND hist.upd_date <= {$request->get('targetDateEnd')}
            AND hist.den_kubun = '3' AND hist.out_id = '2'
            AND sche.dept = 'L'
            GROUP BY hist.plan_no
        ";
        return $query;
    }
    
    // �С������ɤ����ϻ��� ��˥�����
    private function getQueryStatement13($request)
    {
        $query = "
            SELECT sum(pick_time) FROM acceptance_inspection_linear AS time
            LEFT OUTER JOIN assembly_schedule AS sche USING (plan_no)
            WHERE time.end_time >= '{$request->get('targetDateStr')} 080000' AND time.end_time <= '{$request->get('targetDateEnd')} 210000'
            AND sche.dept = 'L'
        ";
        return $query;
    }
    
    ///// �ʼ��ݾڲݻ����� ��׽и˸Ŀ� ��� ���ץ�ɸ��
    private function getQueryStatement7($request)
    {
        $query = "
            SELECT
                sum(stock_mv)   AS ���ץ�ɸ��и˿�
            FROM
                parts_stock_history AS hist
            LEFT OUTER JOIN
                assembly_schedule USING(plan_no)
            WHERE
                upd_date>={$request->get('targetDateStr')} AND upd_date<={$request->get('targetDateEnd')} AND dept='C' AND note15 NOT LIKE 'SC%' AND den_kubun='3' AND out_id='2' AND note LIKE '���Ў��� �����ގ���%'
                AND NOT EXISTS (SELECT miccc FROM miccc WHERE mipn=hist.parts_no)
        ";
        return $query;
    }
    
    ///// �ʼ��ݾڲݻ����� ��׽и˸Ŀ� ��� ���ץ�����
    private function getQueryStatement8($request)
    {
        $query = "
            SELECT
                sum(stock_mv)   AS ���ץ�����и˿�
            FROM
                parts_stock_history AS hist
            LEFT OUTER JOIN
                assembly_schedule USING(plan_no)
            WHERE
                upd_date>={$request->get('targetDateStr')} AND upd_date<={$request->get('targetDateEnd')} AND dept='C' AND note15 LIKE 'SC%' AND den_kubun='3' AND out_id='2' AND note LIKE '���Ў��� �����ގ���%'
                AND NOT EXISTS (SELECT miccc FROM miccc WHERE mipn=hist.parts_no)
        ";
        return $query;
    }
    
    ///// �ʼ��ݾڲݻ����� ��׽и˸Ŀ� ��� ��˥�
    private function getQueryStatement9($request)
    {
        $query = "
            SELECT
                sum(stock_mv)   AS ��˥��и˿�
            FROM
                parts_stock_history AS hist
            LEFT OUTER JOIN
                assembly_schedule USING(plan_no)
            WHERE
                upd_date>={$request->get('targetDateStr')} AND upd_date<={$request->get('targetDateEnd')} AND dept='L' AND den_kubun='3' AND out_id='2' AND note LIKE '���Ў��� �����ގ���%'
                AND NOT EXISTS (SELECT miccc FROM miccc WHERE mipn=hist.parts_no)
        ";
        return $query;
    }
    
    ///// �����List��    HTML�ե��������
    private function getViewHTMLconst($status)
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
<title>�и˻��ֽ��׾Ȳ�</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../acceptance_inspection_analyze.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../acceptance_inspection_analyze.js'></script>
</head>
<body style='background-color:#d6d3ce;'>  <!--  -->
<center>
";
        } elseif ($status == 'footer') {
            $listHTML = 
"
</center>
</body>
</html>
";
        } else {
            $listHTML = '';
        }
        return $listHTML;
    }
    
} // Class AcceptanceInspectionAnalyze_Model End

?>
