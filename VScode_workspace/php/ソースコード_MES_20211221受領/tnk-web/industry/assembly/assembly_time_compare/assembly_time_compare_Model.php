<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω�δ������������ӹ�������Ͽ���������                MVC Model ��   //
// Copyright (C) 2006-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/09 Created   assembly_time_compare_Model.php                     //
// 2006/03/13 SetInitWhere()�� targetDivision ���ɲá�̤��Ͽ�Ǥ�Ȳ��ǽ��  //
//            assy_time/sche.kansei��assy_time / (sche.plan - sche.cut_plan)//
// 2006/05/01 �����ȾȲ��Խ����å����ɲ�                              //
// 2006/05/02 �����Ȥ�������ϥХå������ɤο��ȥ�å��������Ѥ��롣  //
//            ���ӹ����λ�����ˡ����Ͽɸ���åȤ��鴰������ʬ����ѹ�      //
// 2006/05/08 �����ȤξȲ��Խ��ѥơ��֥�Υ����������ֹ梪�ײ��ֹ���ѹ�//
//            �嵭��ȼ��assembly_time_comment �� assembly_time_plan_comment //
// 2006/05/10 ���ȡ���ư������������ �̤˾Ȳ񥪥ץ������ɲ�           //
// 2006/05/12 ��Ͽ�����λ��Ф˻Ȥ����������߷פ��ѹ� comp_pcs �� kansei ��  //
// 2006/06/24 getUniResult() �� $this->getUniResult() ���ѹ�   83����       //
// 2006/08/31 �ꥹ�Ȥδ�λ�ֹ�������ƹ��ֹ�ȥ饤�󥰥롼�פ��ɲ� �ڤ�   //
//            ����å��������ܤ򥽡��Ȥ��뵡ǽ����� �����Τ���LIMIT2000 ADD//
// 2006/08/31 ���ܥ����ȵ�ǽ �ɲäˤ�� �ƥ᥽�åɤ��ѹ��ڤ��ɲ�            //
// 2007/06/11 �����Ȥ�����Х��åץإ��(title)��ɽ��������ɲ�           //
// 2007/06/12 ��������ϿWindow����ƥ�����ɥ��β��̹����б���commentSave //
//            ��getViewHTMLtable()�᥽�åɤ��ѹ�(�ѹ��ս�˥ޡ����ȥ�����)//
// 2007/09/03 �����ֹ�����Ǥ���褦���ɲ�(��������󤫤����)          //
// 2007/09/05 $this->where �ΥǥХå���å�������������Τ�˺��Ƥ������� //
// 2008/09/02 ���ӹ�����SQL��ȴ�Ф���(Cɸ��)sche.plan - sche.cut_plan��     //
//            0�ˤʤäƤ��ޤ���껻�ǥ��顼�ˤʤäƤ��ޤ��Τ���      ��ë //
// 2013/01/29 ����̾��Ƭʸ����DPE�Τ�Τ���Υݥ��(�Х����)�ǽ��פ���褦 //
//            ���ѹ�                                                   ��ë //
//            �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ� ��ë//
// 2013/01/31 ��˥��Τߤ�DPEȴ��SQL������                             ��ë //
// 2013/04/08 �ǲ����˼��Ӥ���Ͽ�ι�פ��ɲ�                           ��ë //
// 2015/06/30 �ƥ��Ȥΰٰ���ѹ��帵���᤹                             ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class AssemblyTimeCompare_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $order;                             // ���� SQL��ORDER��
    
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
            $this->where = $this->SetInitWhere($request);
            $this->order = $this->SetInitOrder($request);
            break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    ���ӹ������� �� ��Ͽ�����إå��� ����ɽ
    public function outViewListHTML($request, $menu, $session)
    {
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLtable($request, $menu, $session);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/assembly_time_compare_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    ///// ���ʤΥ����Ȥ���¸
    public function commentSave($request, $result, $session)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        // if ($request->get('comment') == '') return;  // �����Ԥ��Ⱥ���Ǥ��ʤ�
        if ($request->get('targetPlanNo') == '') return;
        if ($request->get('targetAssyNo') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $query = "SELECT comment FROM assembly_time_plan_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
        if ($this->getUniResult($query, $comment) < 1) {
            if ($request->get('comment') == '') {
                // �ǡ���̵��
                $result->add('AutoClose', 'G_reloadFlg=false; window.close();'); // ��Ͽ�� �ƤΥ���ɤϤ��ʤ���Window��λ
                return;
            }
            $sql = "
                INSERT INTO assembly_time_plan_comment (assy_no, plan_no, comment, last_date, last_host)
                values ('{$request->get('targetAssyNo')}', '{$request->get('targetPlanNo')}', '{$request->get('comment')}', '{$last_date}', '{$last_host}')
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$request->get('targetPlanNo')}\\n\\n�����Ȥ���Ͽ������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            } else {
                $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$request->get('targetPlanNo')}\\n\\n�����Ȥ���Ͽ���ޤ�����";
            }
        } else {
            if ($request->get('comment') == '') {
                // �����Ȥ����Ƥ��������ƹ����ξ��ϡ��¥쥳���ɤ���
                $sql = "DELETE FROM assembly_time_plan_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
                if ($this->query_affected($sql) <= 0) {
                    $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$request->get('targetPlanNo')}\\n\\n�����Ȥκ��������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
                } else {
                    $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$request->get('targetPlanNo')}\\n\\n�����Ȥ������ޤ�����";
                }
            } elseif ($comment == $request->get('comment')) {
                // �ѹ�̵��
                $result->add('AutoClose', 'G_reloadFlg=false; window.close();'); // ��Ͽ�� �ƤΥ���ɤϤ��ʤ���Window��λ
                return;
            } else {
                $sql = "
                    UPDATE assembly_time_plan_comment SET comment='{$request->get('comment')}',
                    last_date='{$last_date}', last_host='{$last_host}'
                    WHERE plan_no='{$request->get('targetPlanNo')}'
                ";
                if ($this->query_affected($sql) <= 0) {
                    $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$request->get('targetPlanNo')}\\n\\n�����Ȥ��ѹ�������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
                } else {
                    $_SESSION['s_sysmsg'] = "�ײ��ֹ桧{$request->get('targetPlanNo')}\\n\\n�����Ȥ��ѹ����ޤ�����";
                }
            }
        }
        $session->add('regPlan', $request->get('targetPlanNo'));  // �ޡ������ڤӥ������Ѥ���Ͽ
        $result->add('AutoClose', 'window.close();'); // ��Ͽ�� Window��λ
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
        $where = '';    // �����
        ///// �������Ƚ�λ���λ���
        $where .= "WHERE comp_date >= {$request->get('targetDateStr')} ";
        $where .= "AND comp_date <= {$request->get('targetDateEnd')} ";
        $where .= "AND comp.assy_no LIKE '%{$request->get('targetAssyNo')}%' ";
        switch ($request->get('targetDivision')) {
        case 'CA':
            $where .= "AND sche.dept = 'C' ";
            break;
        case 'CH':
            $where .= "AND sche.dept = 'C' AND note15 NOT LIKE 'SC%' ";
            break;
        case 'CS':
            $where .= "AND sche.dept = 'C' AND note15 LIKE 'SC%' ";
            break;
        case 'LA':
            $where .= "AND sche.dept = 'L' ";
            break;
        case 'LH':
            //$where .= "AND sche.dept = 'L' AND comp.assy_no NOT LIKE 'LC%' AND comp.assy_no NOT LIKE 'LR%' ";
            $where .= "AND sche.dept = 'L' AND comp.assy_no NOT LIKE 'LC%' AND comp.assy_no NOT LIKE 'LR%' AND CASE WHEN comp.assyno = '' THEN sche.dept='L' ELSE midsc not like 'DPE%%' END ";
            break;
        case 'LB':
            //$where .= "AND sche.dept = 'L' AND (comp.assy_no LIKE 'LC%' OR comp.assy_no LIKE 'LR%') ";
            $where .= "AND sche.dept = 'L' AND (comp.assy_no LIKE 'LC%' OR comp.assy_no LIKE 'LR%' OR midsc like 'DPE%%') ";
            break;
        }
        return $where;
    }
    
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���ORDER�������
    protected function SetInitOrder($request)
    {
        ///// targetSortItem������
        switch ($request->get('targetSortItem')) {
        case 'plan':
            $order = 'ORDER BY �ײ��ֹ� ASC';
            break;
        case 'assy':
            $order = 'ORDER BY �����ֹ� ASC';
            break;
        case 'name':
            $order = 'ORDER BY ����̾ ASC';
            break;
        case 'pcs':
            $order = 'ORDER BY ������ ASC';
            break;
        case 'date':
            $order = 'ORDER BY ������ ASC';
            break;
        case 'in_no':
            $order = 'ORDER BY ���� ASC';
            break;
        case 'res':
            $order = 'ORDER BY ���ӹ��� DESC';
            break;
        case 'reg':
            $order = 'ORDER BY ��Ͽ���� DESC';
            break;
        default:
            $order = 'ORDER BY line_group ASC';
        }
        return $order;
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
    ///// List��   ��Ω ���� ����ɽ & ���ӹ��� & ��Ͽ����
    private function getViewHTMLtable($request, $menu, $session)
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
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>�����ǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            if ($session->get('regPlan') != '') {
                $regPlan = $session->get('regPlan');
                $session->add('regPlan', '');
            } else {
                $regPlan = '';
            }
            $sum_results = 0;
            $sum_entry   = 0;
            for ($i=0; $i<$rows; $i++) {
                $sum_results += $res[$i][8];
                $sum_entry   += $res[$i][9];
                if ($regPlan == $res[$i][0]) {  // �����Ȥ���Ͽ����ľ��ϥޡ������դ��� 2007/06/12
                    $listTable .= "    <tr onDblClick='AssemblyTimeCompare.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='{$res[$i][10]}' style='background-color:#ffffc6;'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' ><a name='Mark' style='color:black;'>" . ($i+1) . "</a></td>\n";                    // ���ֹ�
                } elseif ($res[$i][10] == '') {   // �����Ȥ�����п����Ѥ��� 2007/06/11���åץإ��(title)��ɽ������
                    $listTable .= "    <tr onDblClick='AssemblyTimeCompare.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='���ߥ����Ȥ���Ͽ����Ƥ��ޤ���\n���֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1) . "</td>\n";                    // ���ֹ�
                } else {
                    $listTable .= "    <tr onDblClick='AssemblyTimeCompare.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='{$res[$i][10]}' style='background-color:#e6e6e6;'>\n";
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1) . "</td>\n";                    // ���ֹ�
                }
                // $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 3%' align='center'>{$res[$i][12]}</td>\n";                    // �饤�󥰥롼��
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][0]}</td>\n";                     // �ײ��ֹ�
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][1]}</td>\n";                     // �����ֹ�
                $listTable .= "        <td class='winbox' width='19%' align='left'>" . mb_convert_kana($res[$i][2], 'k') . "</td>\n";   // ����̾
                $listTable .= "        <td class='winbox' width=' 7%' align='right' >" . number_format($res[$i][3]) . "</td>\n";// ������
                $listTable .= "        <td class='winbox' width='12%' align='center' >{$res[$i][4]}</td>\n";                    // ������
                // $listTable .= "        <td class='winbox' width=' 8%' align='center'>{$res[$i][5]}</td>\n";                     // ��λ�ֹ�
                $listTable .= "        <td class='winbox' width=' 5%' align='center'>{$res[$i][6]}</td>\n";                     // ����
                $listTable .= "        <td class='winbox' width='11%' align='left'>{$res[$i][7]}</td>\n";                       // ����
                $listTable .= "        <td class='winbox' width=' 8%' align='right'\n";
                $listTable .= "        onClick='AssemblyTimeCompare.win_open(\"../../assembly_time_show/assembly_time_show_Main.php?targetPlanNo={$res[$i][0]}&noMenu=yes\", 900, 600)'\n";
                $listTable .= "        onMouseover='document.body.style.cursor=\"hand\"' onMouseout='document.body.style.cursor=\"auto\"'\n";
                if ($res[$i][8]) {
                    $listTable .= "        >{$res[$i][8]}</td>\n";                                                              // ���ӹ���
                } else {
                    $listTable .= "        >̤����</td>\n";                                                                     // ���ӹ����ʤ�
                }
                $listTable .= "        <td class='winbox' width=' 8%' align='right'\n";
                $listTable .= "        onClick='AssemblyTimeCompare.win_open(\"../../assembly_time_show/assembly_time_show_Main.php?targetPlanNo={$res[$i][0]}&noMenu=yes\", 900, 600)'\n";
                $listTable .= "        onMouseover='document.body.style.cursor=\"hand\"' onMouseout='document.body.style.cursor=\"auto\"'\n";
                if ($res[$i][9]) {
                    $listTable .= "        >{$res[$i][9]}</td>\n";                                                              // ��Ͽ����
                } else {
                    if ($res[$i][11]) { // ��Ͽ�ֹ椬�����
                        $listTable .= "        ><span style='color:gray;'>0.000</span></td>\n";
                    } else {
                        $listTable .= "        >̤��Ͽ</td>\n";                                                                 // ��Ͽ�����ʤ�
                    }
                }
                $listTable .= "    </tr>\n";
            }
            $listTable .= "<tr>\n";
            $listTable .= "<td class='winbox' colspan='5' align='right'>���ӹ��</td>\n";
            $listTable .= "<td class='winbox' colspan='2' align='right'>" . number_format($sum_results, 3) . "</td>\n";
            $listTable .= "<td class='winbox' colspan='2' align='right'>��Ͽ���</td>\n";
            $listTable .= "<td class='winbox' colspan='2' align='right'>" . number_format($sum_entry, 3) . "</td>\n";
            $listTable .= "</tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   ��Ω ���� ����ɽ & ���ӹ��� & ��Ͽ����
    private function getQueryStatement($request)
    {
        $query1 = "
            SELECT   comp.plan_no   AS �ײ��ֹ�         -- 00
                    ,comp.assy_no   AS �����ֹ�         -- 01
                    ,substr(midsc, 1, 16)
                                    AS ����̾           -- 02
                    ,comp_pcs       AS ������           -- 03
                    ,to_char(comp_date, '0000/00/00')
                                    AS ������           -- 04
                    ,to_char(comp_no, '00000')
                                    AS ��λ�ֹ�         -- 05
                    ,CASE
                        WHEN in_no='1' THEN '14'
                        WHEN in_no='2' THEN '52'
                        WHEN in_no='3' THEN '30'
                        WHEN in_no='4' THEN '39'
                        WHEN in_no='5' THEN '40'
                        WHEN in_no='6' THEN '91'
                        WHEN in_no='7' THEN '74'
                        WHEN in_no='8' THEN '60'
                        WHEN in_no='9' THEN '21'
                        ELSE in_no
                     END            AS ����             -- 06
                    ,CASE
                        WHEN trim(sche.note15) = '' THEN '&nbsp;'
                        ELSE substr(sche.note15, 1, 8)
                     END            AS ����             -- 07
        ";
        switch ($request->get('targetProcess')) {
        case 'M':       // ��ư������
            $query2 = "
                    ,0              AS ���ӹ���         -- 08
                    ,(   SELECT
                            CASE
                                WHEN kansei != 0 THEN   -- �߷״�������Ȥ����������ɲ�
                                    sum(assy_time) + Uround(sum(setup_time) / kansei, 3)    -- time_head_std_lot(comp.assy_no, comp_date)��kansei�����ؤ�
                                ELSE
                                    sum(assy_time) + Uround(sum(setup_time) / comp_pcs, 3)  -- time_head_std_lot(comp.assy_no, comp_date)��comp_pcs�����ؤ�
                            END
                         FROM assembly_standard_time AS mei LEFT OUTER JOIN assembly_process_master USING (pro_mark)
                         WHERE mei.assy_no = comp.assy_no AND mei.reg_no = time_head_reg_no(comp.assy_no, comp_date)
                         AND pro_seg = '2' -- ��ư��
                     )              AS ��Ͽ����         -- 09
            ";
            break;
        case 'G':       // ������
            $query2 = "
                    ,(   SELECT sum(assy_time) + Uround(sum(setup_time) / comp_pcs, 3)  -- comp_pcs �����ؤ� time_head_std_lot(comp.assy_no, comp_date)
                         FROM assembly_standard_time AS mei LEFT OUTER JOIN assembly_process_master USING (pro_mark)
                         WHERE mei.assy_no = comp.assy_no AND mei.reg_no = time_head_reg_no(comp.assy_no, comp_date)
                         AND pro_seg = '3' -- ����
                     )              AS ���ӹ���         -- 08
                    ,(   SELECT
                            CASE
                                WHEN kansei != 0 THEN   -- �߷״�������Ȥ����������ɲ�
                                    sum(assy_time) + Uround(sum(setup_time) / kansei, 3)    -- time_head_std_lot(comp.assy_no, comp_date)��kansei�����ؤ�
                                ELSE
                                    sum(assy_time) + Uround(sum(setup_time) / comp_pcs, 3)  -- time_head_std_lot(comp.assy_no, comp_date)��comp_pcs�����ؤ�
                            END
                         FROM assembly_standard_time AS mei LEFT OUTER JOIN assembly_process_master USING (pro_mark)
                         WHERE mei.assy_no = comp.assy_no AND mei.reg_no = time_head_reg_no(comp.assy_no, comp_date)
                         AND pro_seg = '3' -- ����
                     )              AS ��Ͽ����         -- 09
            ";
            break;
        case 'A':       // ������
            $query2 = "
                            -- �ʲ��η׻���ʬ������߷פǹԤ�����(SELECT sum(comp_pcs) FROM assembly_completion_history WHERE plan_no=comp.plan_no)
                    ,(   SELECT sum(Uround(assy_time / (sche.plan - sche.cut_plan), 3)) -- �ġ��ι�����׻����Ƥ����פ�Ф���
                         FROM assembly_process_time AS pro
                         WHERE sche.plan - sche.cut_plan <> 0 AND pro.plan_no = comp.plan_no
                     ) -- ���� ��̵������NULL�ͤ��֤뤿�� 0�Ȥʤ�
                     +
                     (   SELECT CASE
                                    WHEN sum(assy_time) IS NULL THEN 0
                                    ELSE sum(assy_time) + Uround(sum(setup_time) / comp_pcs, 3)  -- comp_pcs �����ؤ� time_head_std_lot(comp.assy_no, comp_date)
                                END
                         FROM assembly_standard_time AS mei LEFT OUTER JOIN assembly_process_master USING (pro_mark)
                         WHERE mei.assy_no = comp.assy_no AND mei.reg_no = time_head_reg_no(comp.assy_no, comp_date)
                         AND (pro_seg = '2' OR pro_seg = '3') -- ��ư���ȳ���
                     ) -- ��ư�� (���ߤ���Ͽ������ȤäƤ���)
                                    AS ���ӹ���         -- 08
                    ,(   SELECT
                            CASE
                                WHEN kansei != 0 THEN   -- �߷״�������Ȥ����������ɲ�
                                    sum(assy_time) + Uround(sum(setup_time) / kansei, 3)    -- time_head_std_lot(comp.assy_no, comp_date)��kansei�����ؤ�
                                ELSE
                                    sum(assy_time) + Uround(sum(setup_time) / comp_pcs, 3)  -- time_head_std_lot(comp.assy_no, comp_date)��comp_pcs�����ؤ�
                            END
                         FROM assembly_standard_time AS mei
                         WHERE mei.assy_no = comp.assy_no AND mei.reg_no = time_head_reg_no(comp.assy_no, comp_date)
                     )              AS ��Ͽ����         -- 09
            ";
            break;
        case 'H':       // ���ȹ���
        default:
            $query2 = "
                            -- �ʲ��η׻���ʬ������߷פǹԤ�����(SELECT sum(comp_pcs) FROM assembly_completion_history WHERE plan_no=comp.plan_no)
                    ,(   SELECT sum(Uround(assy_time / (sche.plan - sche.cut_plan), 3)) -- �ġ��ι�����׻����Ƥ����פ�Ф���
                         FROM assembly_process_time AS pro
                         WHERE sche.plan - sche.cut_plan <> 0 AND pro.plan_no = comp.plan_no
                     )              AS ���ӹ���         -- 08
                    ,(   SELECT
                            CASE
                                WHEN kansei != 0 THEN   -- �߷״�������Ȥ����������ɲ�
                                    sum(assy_time) + Uround(sum(setup_time) / kansei, 3)    -- time_head_std_lot(comp.assy_no, comp_date)��kansei�����ؤ�
                                ELSE
                                    sum(assy_time) + Uround(sum(setup_time) / comp_pcs, 3)  -- time_head_std_lot(comp.assy_no, comp_date)��comp_pcs�����ؤ�
                            END
                         FROM assembly_standard_time AS mei LEFT OUTER JOIN assembly_process_master USING (pro_mark)
                         WHERE mei.assy_no = comp.assy_no AND mei.reg_no = time_head_reg_no(comp.assy_no, comp_date)
                         AND pro_seg = '1' -- ����
                     )              AS ��Ͽ����         -- 09
            ";
            break;
        }
        $query3 = "
                    ,comment        AS ������         -- 10
                    ,time_head_reg_no(comp.assy_no, comp_date)
                                    AS ��Ͽ�ֹ�         -- 11
                    ,CASE
                        WHEN line_group = ' ' THEN '&nbsp;'
                        ELSE line_group
                     END            AS �饤�󥰥롼��   -- 12
                    FROM
                        assembly_completion_history AS comp
                    LEFT OUTER JOIN
                        assembly_schedule           AS sche
                        USING(plan_no)
                    LEFT OUTER JOIN
                        miitem ON (comp.assy_no=mipn)
                    LEFT OUTER JOIN
                        assembly_time_plan_comment USING (plan_no)
                    {$this->where}
                    {$this->order}
                    LIMIT 2000
        ";
                    // �ǥХå��� WHERE comp_date >= 20060309 AND comp_date <= 20060309
        $query = ($query1 . $query2 . $query3);
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
<title>��Ω�δ�������List��</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../assembly_time_compare.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../assembly_time_compare.js'></script>
</head>
<body>
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
    
} // Class AssemblyTimeCompare_Model End

?>
