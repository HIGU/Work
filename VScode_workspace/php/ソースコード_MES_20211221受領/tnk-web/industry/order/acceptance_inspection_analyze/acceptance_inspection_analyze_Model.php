<?php
//////////////////////////////////////////////////////////////////////////////
// ���������λ��֡�����ν��׎�ʬ�� ��� �Ȳ�                 MVC Model ��   //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/08/09 Created   acceptance_inspection_analyze_Model.php             //
// 2006/11/30 outListLeadTime()�������������ν��פǽ���꡼��             //
// 2006/12/22 getDetailsHTMLbody()�᥽�åɤθ������֤�number_format���ɲ�   //
// 2007/01/10 Web�Ǹ������AS������ڤꤵ��륱����������(�̾��̵���Ϥ���) //
//            AND uke_date > 0 ��3�ս��SELECTʸ���ɲ�                      //
// 2007/01/19 ��������(��α)��ǽ�ɲäˤ�꽸�פ�ȿ�� inspection_holding     //
// 2007/04/05 getDetailsHTMLfooter()�᥽�åɤγƹ�׻��֤򾮿�����������    //
// 2007/12/12 getViewHTMLconst()�᥽�åɤ�$menu�ѥ���ɲá����顼ɽ���б�   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class AcceptanceInspectionAnalyze_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $total_inspection;                  // ��׸������
    private $total_days;                        // ��׸�������
    private $total_time;                        // ��׸�������
    private $total_hold;                        // ������ǻ���
    private $total_actualTime;                  // ��׼»���
    private $total_average;                     // ���Τ�ʿ��(�������ϻ���)
    private $detail_user;                       // ����ɽ����ô����
    
    ///// public properties
    // public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        ///// �ץ�ѥƥ�(���С��ѿ�)�ν����
        $this->total_inspection = 0;
        $this->total_days       = 0;
        $this->total_average    = 0;
        $this->total_time       = 0;
        $this->detail_user      = '';
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
    
    ////////// MVC �� Model �� �Ƽ�ꥹ�ȵڤӥ��������
    ///// List��    ô������θ��������Υꥹ������
    public function outListLeadTime($request, $menu)
    {
                /***** �إå���������� *****/
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $headHTML .= $this->getLeadTimeHTMLheader($request);
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** ��ʸ����� *****/
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $listHTML .= $this->getLeadTimeHTMLbody($request, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** �եå���������� *****/
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $footHTML .= $this->getLeadTimeHTMLfooter();
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    ///// List��    ô������μ����������֥ꥹ������
    public function outListInspectionTime($request, $menu)
    {
                /***** �إå���������� *****/
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $headHTML .= $this->getInspectionTimeHTMLheader($request);
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** ��ʸ����� *****/
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $listHTML .= $this->getInspectionTimeHTMLbody($request, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** �եå���������� *****/
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $footHTML .= $this->getInspectionTimeHTMLfooter();
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    ///// List��    ô����������٥ꥹ������ (���̤ǥꥹ�Ȥ䥰��դ���2��Ū�˸ƤФ��)
    public function outListDetails($request, $menu)
    {
                /***** �إå���������� *****/
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $headHTML .= $this->getDetailsHTMLheader($request);
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** ��ʸ����� *****/
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $listHTML .= $this->getDetailsHTMLbody($request, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** �եå���������� *****/
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $footHTML .= $this->getDetailsHTMLfooter();
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    ///// �����Ȥ���¸
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
    
    ///// �����Ȥ����
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
    
    ///// ���顼��å������ѥꥹ�Ƚ���
    public function outListErrorMessage($request, $menu)
    {
                /***** �إå���������� *****/
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $headHTML .= $this->getLeadTimeHTMLheader($request);
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** ��ʸ����� *****/
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $listHTML .= $this->getErrorMessageHTMLbody($request, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** �եå���������� *****/
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $footHTML .= $this->getLeadTimeHTMLfooter();
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/acceptance_inspection_analyze_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
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
    ///// List��  �������� ����ɽ�� �إå����������
    private function getLeadTimeHTMLheader($request)
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 7%'>No</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>����</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>�Ұ��ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>�ᡡ̾</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>�������</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>��������</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>ʿ������</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��   �������� ����ɽ�� ��ʸ
    private function getLeadTimeHTMLbody($request, $menu)
    {
        $query = "
            SELECT uid          AS �Ұ��ֹ�
                , trim(name)    AS �Ұ�̾
                , count(uid)    AS �������
                , sum(
                        EXTRACT(DAY FROM (end_timestamp - CAST(to_char(uke_date, 'FM9999-99-99') AS TIMESTAMP)))
                    )           AS ��������
                , Uround(
                        CAST(sum(EXTRACT(DAY FROM (end_timestamp - CAST(to_char(uke_date, 'FM9999-99-99') AS TIMESTAMP)))) / count(uid) AS NUMERIC)
                    , 3)        AS ʿ������
            FROM acceptance_kensa LEFT OUTER JOIN user_detailes USING(uid) LEFT OUTER JOIN order_data USING(order_seq)
            WHERE end_timestamp >= '{$request->get('targetDateStr')} 00:00:00' AND end_timestamp < '{$request->get('targetDateEnd')} 24:00:00' AND uid IS NOT NULL AND uke_date > 0
            GROUP BY uid, name
            ORDER BY uid ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0) {
            $_SESSION['s_sysmsg'] = '�������������򤬤���ޤ���';
        }
        
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>�������������򤬤���ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                /*****
                if ($res[$i][10] != '') {   // �����Ȥ�����п����Ѥ���
                    $listTable .= "    <tr onDblClick='AcceptanceInspectionAnalyze.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='�����Ȥ���Ͽ����Ƥ��ޤ������֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='AcceptanceInspectionAnalyze.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='���ߥ����Ȥ���Ͽ����Ƥ��ޤ��󡣥��֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���'>\n";
                }
                *****/
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 7%' align='right' >" . ($i+1) . "</td>\n";    // ���ֹ�
                $listTable .= "        <td class='winbox' width=' 8%' align='right' ><a href='javascript:win_open(\"{$menu->out_self()}?Action=ListDetails&showMenu=ListWin&targetUid={$res[$i][0]}\");'>����</a></td>\n"; // ���٥���å���
                $listTable .= "        <td class='winbox' width='15%' align='center'>{$res[$i][0]}</td>\n";     // �Ұ��ֹ�
                if ($res[$i][0] == '00000A') {
                    $listTable .= "        <td class='winbox' width='20%' align='left'  >��ͭPC</td>\n";        // �ᡡ̾
                } else {
                    $listTable .= "        <td class='winbox' width='20%' align='left'  >{$res[$i][1]}</td>\n"; // �ᡡ̾
                }
                $listTable .= "        <td class='winbox' width='15%' align='right' >{$res[$i][2]}</td>\n";     // �������
                $listTable .= "        <td class='winbox' width='15%' align='right' >{$res[$i][3]}</td>\n";     // ��������
                $listTable .= "        <td class='winbox' width='20%' align='right' >{$res[$i][4]}</td>\n";     // ʿ������
                $listTable .= "    </tr>\n";
                ///// ��������ȸ���������ץ�ѥƥ�����¸
                $this->total_inspection += $res[$i][2];
                $this->total_days       += $res[$i][3];
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
            $this->total_average = Uround($this->total_days / $this->total_inspection, 3);
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��  �������� ����ɽ�� �եå����������
    private function getLeadTimeHTMLfooter()
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='50%' align='right' >���</td>\n";
        $listTable .= "        <td class='winbox' width='15%' align='right' >" . number_format($this->total_inspection) . "</td>\n";
        $listTable .= "        <td class='winbox' width='15%' align='right' >" . number_format($this->total_days) . "</td>\n";
        $listTable .= "        <td class='winbox' width='20%' align='right' >��avg.��" . number_format($this->total_average, 3) . "</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��  �������� ����ɽ�� �إå����������
    private function getInspectionTimeHTMLheader($request)
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 7%'>No</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>����</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>�Ұ��ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>�ᡡ̾</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>�������</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>��������(ʬ)</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>ʿ�ѻ���(ʬ)</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��   �������� ����ɽ�� ��ʸ
    private function getInspectionTimeHTMLbody($request, $menu)
    {
        $query = "
            SELECT uid                              AS �Ұ��ֹ�
                , trim(name)                        AS �Ұ�̾
                , to_char(count(uid), 'FM9,999')    AS �������
                , SUM(
                    CASE
                    WHEN hold.���ǻ��� IS NULL THEN
                        (EXTRACT(DAY FROM (kensa.end_timestamp - kensa.str_timestamp)) * 24 * 60) +
                        (EXTRACT(HOUR FROM (kensa.end_timestamp - kensa.str_timestamp)) * 60) +
                        EXTRACT(MINUTE FROM (kensa.end_timestamp - kensa.str_timestamp)) +
                        Uround(CAST(EXTRACT(SECOND FROM (kensa.end_timestamp - kensa.str_timestamp)) AS NUMERIC) / 60, 3)
                    ELSE
                        (EXTRACT(DAY FROM (kensa.end_timestamp - kensa.str_timestamp - hold.���ǻ���)) * 24 * 60) +
                        (EXTRACT(HOUR FROM (kensa.end_timestamp - kensa.str_timestamp - hold.���ǻ���)) * 60) +
                        EXTRACT(MINUTE FROM (kensa.end_timestamp - kensa.str_timestamp - hold.���ǻ���)) +
                        Uround(CAST(EXTRACT(SECOND FROM (kensa.end_timestamp - kensa.str_timestamp - hold.���ǻ���)) AS NUMERIC) / 60, 3)
                    END
                  )                                 AS �������֡�ʬ��
                , Uround(
                    CAST(
                        SUM(
                          CASE
                            WHEN hold.���ǻ��� IS NULL THEN
                            (EXTRACT(DAY FROM (kensa.end_timestamp - kensa.str_timestamp)) * 24 * 60) +
                            (EXTRACT(HOUR FROM (kensa.end_timestamp - kensa.str_timestamp)) * 60) +
                            EXTRACT(MINUTE FROM (kensa.end_timestamp - kensa.str_timestamp)) +
                            Uround(CAST(EXTRACT(SECOND FROM (kensa.end_timestamp - kensa.str_timestamp)) AS NUMERIC) / 60, 3)
                          ELSE
                            (EXTRACT(DAY FROM (kensa.end_timestamp - kensa.str_timestamp - hold.���ǻ���)) * 24 * 60) +
                            (EXTRACT(HOUR FROM (kensa.end_timestamp - kensa.str_timestamp - hold.���ǻ���)) * 60) +
                            EXTRACT(MINUTE FROM (kensa.end_timestamp - kensa.str_timestamp - hold.���ǻ���)) +
                            Uround(CAST(EXTRACT(SECOND FROM (kensa.end_timestamp - kensa.str_timestamp - hold.���ǻ���)) AS NUMERIC) / 60, 3)
                          END
                        ) / count(uid)
                    AS NUMERIC), 3
                  )                                AS ʿ�ѻ��֡�ʬ��
            FROM acceptance_kensa               AS kensa
                LEFT OUTER JOIN user_detailes   AS detail   USING(uid)
                LEFT OUTER JOIN order_data      AS data     USING(order_seq)
                LEFT OUTER JOIN (SELECT order_seq, sum(end_timestamp-str_timestamp) AS ���ǻ��� FROM inspection_holding
                 WHERE end_timestamp >= '{$request->get('targetDateStr')} 00:00:00' AND end_timestamp < '{$request->get('targetDateEnd')} 24:00:00'
                 GROUP BY order_seq)            AS hold     USING(order_seq)
            WHERE kensa.end_timestamp >= '{$request->get('targetDateStr')} 00:00:00' AND kensa.end_timestamp < '{$request->get('targetDateEnd')} 24:00:00' AND uid IS NOT NULL AND uke_date > 0
            GROUP BY uid, name
            ORDER BY uid ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0) {
            $_SESSION['s_sysmsg'] = '�������������򤬤���ޤ���';
        }
        
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>�������������򤬤���ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                /*****
                if ($res[$i][10] != '') {   // �����Ȥ�����п����Ѥ���
                    $listTable .= "    <tr onDblClick='AcceptanceInspectionAnalyze.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='�����Ȥ���Ͽ����Ƥ��ޤ������֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='AcceptanceInspectionAnalyze.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='���ߥ����Ȥ���Ͽ����Ƥ��ޤ��󡣥��֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���'>\n";
                }
                *****/
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 7%' align='right' >" . ($i+1) . "</td>\n";    // ���ֹ�
                $listTable .= "        <td class='winbox' width=' 8%' align='right' ><a href='javascript:win_open(\"{$menu->out_self()}?Action=ListDetails&showMenu=ListWin&targetUid={$res[$i][0]}\");'>����</a></td>\n"; // ���٥���å���
                $listTable .= "        <td class='winbox' width='15%' align='center'>{$res[$i][0]}</td>\n";     // �Ұ��ֹ�
                if ($res[$i][0] == '00000A') {
                    $listTable .= "        <td class='winbox' width='20%' align='left'  >��ͭPC</td>\n";        // �ᡡ̾
                } else {
                    $listTable .= "        <td class='winbox' width='20%' align='left'  >{$res[$i][1]}</td>\n"; // �ᡡ̾
                }
                $listTable .= "        <td class='winbox' width='15%' align='right' >{$res[$i][2]}</td>\n";     // �������
                $listTable .= "        <td class='winbox' width='15%' align='right' >" . number_format($res[$i][3], 3) . "</td>\n";     // ��������
                $listTable .= "        <td class='winbox' width='20%' align='right' >" . number_format($res[$i][4], 3) . "</td>\n";     // ʿ�ѻ���
                $listTable .= "    </tr>\n";
                ///// ��������ȸ������֤�ץ�ѥƥ�����¸
                $this->total_inspection += $res[$i][2];
                $this->total_time       += $res[$i][3];
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
            $this->total_average = Uround($this->total_time / $this->total_inspection, 3);
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   ����ɽ�� �եå����������
    private function getInspectionTimeHTMLfooter()
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='50%' align='right' >���</td>\n";
        $listTable .= "        <td class='winbox' width='15%' align='right' >" . number_format($this->total_inspection) . "</td>\n";
        $listTable .= "        <td class='winbox' width='15%' align='right' >" . number_format($this->total_time, 3) . "</td>\n";
        $listTable .= "        <td class='winbox' width='20%' align='right' >��avg.��" . number_format($this->total_average, 3) . "</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��  ����ô���Ԥ����� ����ɽ�� �إå����������
    private function getDetailsHTMLheader($request)
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>ȯ��No.</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>�����ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>����̾</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>������</th>\n";
        $listTable .= "        <th class='winbox' width='10%' style='font-size:11pt;'>����������</th>\n";
        $listTable .= "        <th class='winbox' width='10%' style='font-size:11pt;'>����λ����</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>����(ʬ)</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>����(ʬ)</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>�¸�(ʬ)</th>\n";
        $listTable .= "        <th class='winbox' width=' 6%' style='font-size:11pt;'>������</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��   ����ô���Ԥ����� ����ɽ�� ��ʸ
    private function getDetailsHTMLbody($request, $menu)
    {
        $query = "
            SELECT to_char(data.order_seq, 'FM999-9999')    AS ȯ��Ϣ��
                , data.parts_no                             AS �����ֹ�
                , trim(substr(midsc, 1, 12))                AS ����̾
                , to_char(uke_date, 'FM9999-99-99')         AS ������
                , kensa.str_timestamp                       AS ��������
                , kensa.end_timestamp                       AS ��λ����
                ,
                (EXTRACT(DAY FROM (kensa.end_timestamp - kensa.str_timestamp)) * 24 * 60) +
                (EXTRACT(HOUR FROM (kensa.end_timestamp - kensa.str_timestamp)) * 60) +
                EXTRACT(MINUTE FROM (kensa.end_timestamp - kensa.str_timestamp)) +
                Uround(CAST(EXTRACT(SECOND FROM (kensa.end_timestamp - kensa.str_timestamp)) AS NUMERIC) / 60, 3)
                                                            AS �������֡�ʬ��
                , EXTRACT(DAY FROM (kensa.end_timestamp - CAST(to_char(uke_date, 'FM9999-99-99') AS TIMESTAMP)))
                                                            AS ��������
                ---------------------------------------------------------- �ʲ��ϥꥹ�ȳ��Υǡ����ǻ���
                , trim(name)                                AS �Ұ�̾
                , uid                                       AS �Ұ��ֹ�
                ,
                (EXTRACT(DAY FROM (hold.���ǻ���)) * 24 * 60) +
                (EXTRACT(HOUR FROM (hold.���ǻ���)) * 60) +
                EXTRACT(MINUTE FROM (hold.���ǻ���)) +
                Uround(CAST(EXTRACT(SECOND FROM (hold.���ǻ���)) AS NUMERIC) / 60, 3)
                                                            AS ���ǻ��֡�ʬ��
            FROM acceptance_kensa               AS kensa
                LEFT OUTER JOIN user_detailes   AS detail USING(uid)
                LEFT OUTER JOIN order_data      AS data   USING(order_seq)
                LEFT OUTER JOIN order_plan      AS plan   USING(sei_no)
                LEFT OUTER JOIN miitem          AS item   ON(data.parts_no=mipn)
                LEFT OUTER JOIN (SELECT order_seq, sum(end_timestamp-str_timestamp) AS ���ǻ��� FROM inspection_holding
                 WHERE end_timestamp >= '{$request->get('targetDateStr')} 00:00:00' AND end_timestamp < '{$request->get('targetDateEnd')} 24:00:00'
                 GROUP BY order_seq)            AS hold     USING(order_seq)
            WHERE kensa.end_timestamp >= '{$request->get('targetDateStr')} 00:00:00' AND kensa.end_timestamp < '{$request->get('targetDateEnd')} 24:00:00'
                AND uid = '{$request->get('targetUid')}'  --'008044'  -- �Ŀͤ�����
                AND data.uke_date > 0
            ORDER BY kensa.end_timestamp ASC, kensa.str_timestamp ASC
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0) {
            $_SESSION['s_sysmsg'] = '�������������򤬤���ޤ���';
        }
        
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>�������������򤬤���ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                /*****
                if ($res[$i][10] != '') {   // �����Ȥ�����п����Ѥ���
                    $listTable .= "    <tr onDblClick='AcceptanceInspectionAnalyze.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='�����Ȥ���Ͽ����Ƥ��ޤ������֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='AcceptanceInspectionAnalyze.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='���ߥ����Ȥ���Ͽ����Ƥ��ޤ��󡣥��֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���'>\n";
                }
                *****/
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1) . "</td>\n";    // ���ֹ�
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>{$res[$i][0]}</td>\n";     // ȯ��No.(ȯ��Ϣ��)
                $listTable .= "        <td class='winbox' width=' 9%' align='center'>{$res[$i][1]}</td>\n";     // �����ֹ�
                $listTable .= "        <td class='winbox' width='15%' align='left'  >{$res[$i][2]}</td>\n";     // ����̾
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][3]}</td>\n";     // ������
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][4]}</td>\n";     // ������������
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][5]}</td>\n";     // ������λ����
                $listTable .= "        <td class='winbox' width=' 9%' align='right' >" . number_format($res[$i][6], 3) . "</td>\n";// ��������(ʬ)
                $listTable .= "        <td class='winbox' width=' 9%' align='right' >" . number_format($res[$i][10], 3) . "</td>\n";// ���ǻ���(ʬ)
                $listTable .= "        <td class='winbox' width=' 9%' align='right' >" . number_format($res[$i][6]-$res[$i][10], 3) . "</td>\n";// �¸�������(ʬ)
                $listTable .= "        <td class='winbox' width=' 6%' align='right'>{$res[$i][7]}</td>\n";     // ��������
                $listTable .= "    </tr>\n";
                ///// �������֤�������ץ�ѥƥ�����¸
                $this->total_time       += $res[$i][6];
                $this->total_hold       += $res[$i][10];
                $this->total_actualTime += ($res[$i][6]-$res[$i][10]);
                $this->total_days       += $res[$i][7];
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
            $this->total_inspection = $rows;
            $this->total_average = Uround($this->total_actualTime / $this->total_inspection, 3);
            if ($res[0][9] == '00000A') {
                $this->detail_user = '��ͭPC';
            } else {
                $this->detail_user = $res[0][8];
            }
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��  ����ô���Ԥ����� ����ɽ�� �եå����������
    private function getDetailsHTMLfooter()
    {
        $daysAverage = Uround($this->total_days / $this->total_inspection, 3);
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        ////////////// �嵭�� class='winbox_field list'�Ȥ��ƥե���Ȥ�Ĵ�����������ڡ����δط�����ǰ
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='67%' align='right' >��̾��{$this->detail_user}&nbsp;&nbsp;&nbsp;&nbsp;��׷����" . number_format($this->total_inspection) . "&nbsp;&nbsp;ʿ�ѻ��֡�" . number_format($this->total_average, 3) . "&nbsp;&nbsp;ʿ��������" . number_format($daysAverage, 3) . "</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right' >" . number_format($this->total_time, 0) . "</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right' >" . number_format($this->total_hold, 0) . "</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right' >" . number_format($this->total_actualTime, 0) . "</td>\n";
        $listTable .= "        <td class='winbox' width=' 6%' align='right' >" . number_format($this->total_days) . "</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// �����List��    HTML�ե��������
    private function getViewHTMLconst($status, $menu)
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
<title>���������ν��׷��</title>
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
<!-- <script type='text/javascript' src='../acceptance_inspection_analyze.js'></script> -->
<script type='text/javascript'>
    function win_open(url, w, h, winName)
    {
        if (!winName) winName = '';
        if (!w) w = 980;     // �����
        if (!h) h = 500;     // �����
        var left = (screen.availWidth  - w) / 2;
        var top  = (screen.availHeight - h) / 2;
        w -= 10; h -= 30;   // ��Ĵ����ɬ��
        window.open(url, winName, 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    }
</script>
</head>
<body style='background-color:#d6d3ce;'>  <!--  -->
<center>
";
        } elseif ($status == 'footer') {
            $listHTML = 
"
</center>
</body>
{$menu->out_alert_java(false)}
</html>
";
        } else {
            $listHTML = '';
        }
        return $listHTML;
    }
    
    ///// ���顼��å������� ����ɽ�� ��ʸ
    private function getErrorMessageHTMLbody($request, $menu)
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td width='100%' align='center' class='winbox'>�������Ͻ�λ���դ˥��顼������ޤ���</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
} // Class AcceptanceInspectionAnalyze_Model End

?>
