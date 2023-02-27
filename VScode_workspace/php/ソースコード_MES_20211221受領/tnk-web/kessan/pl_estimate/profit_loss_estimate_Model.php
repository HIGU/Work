<?php
//////////////////////////////////////////////////////////////////////////////
// »��ͽ¬�ν��ס�ʬ�� ��� �Ȳ�(���پȲ�)                  MVC Model ��   //
// Copyright (C) 2011-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/07/13 Created   profit_loss_estimate_Model.php                      //
// 2011/07/14 ����Ū�˴��������پȲ��ǤʤΤǡ�������֤ˤ�äƿ������Ѥ�ä�//
//            ���ޤ���                                                      //
//            �����ͽ��ϡ����Υץ����η׻���ʬ�����Ѥ��ơ���ī�׻���  //
//            DB����Ͽ���롣���θ夳�Υץ�����Ȳ�Τߤ��ѹ����롣      //
//            �Ȳ�Τߤξ��ϡ��о�ǯ���ͽ¬��������(ǯ��Τߤʤ�ǿ�)    //
//            �ɲäǡ�Ĵ���ѤΥץ����������Ĵ����ˡ�ϡ��о�ǯ����ꡣ  //
//            ľ�ܿ����򤤤���櫓�ǤϤʤ���Ĵ����ۤ����Ϥ����            //
//            Ĵ�����̣�����ǤȲ�̣���ʤ��Ǥ��ڤ��ؤ����ǽ�ˤ�������������//
//            Ĵ���������ϡ������Ѥ���ʤɲ���ʬ����褦��                //
//            �ޥ�����������ǥ�����ɽ�����������󳰤�ɽ�������롣        //
//            ���Υץ����ϡ����ٷ׻��ƥ����ѤȤ��Ʊ����ǻĤ���          //
//            DB�ΤĤ���ϡ���ۡ�Ψ��note���о�ǯ���Ͽ����              //
//            Ĵ����DB��������ˡ���Ͽ���դǤϤʤ�Ĵ�����ա�Ĵ���Ԥ⡩    //
//            �������� ������Ф��Ȥ�-1���Ƥ��뤬¿ʬ����                   //
// 2011/07/19 ���پȲ��ǤȤ��ƥ������ɲ�                                  //
// 2011/07/20 ���ɡ���ϻ���Ū�˲�ǯ�֤�ʿ�Ѥ�ɽ��                    //
// 2014/08/25 ǯ����ѹ������ᤷ��(��̵̣���ä��Τ�)                        //
// 2018/04/17 ��˥���»�ץǡ�������˥�ɸ����ѹ��Ȥʤä��Τǽ���          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����

require_once ('../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class ProfitLossEstimate_Model extends daoInterfaceClass
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
        for ($i=1; $i<=12; $i++) {   // 36�������ޤ�
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
        $file_name = "list/profit_loss_estimate_ViewListHeader-{$_SESSION['User_ID']}.html";
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
        $file_name = "list/profit_loss_estimate_ViewList-{$_SESSION['User_ID']}.html";
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
        $file_name = "list/profit_loss_estimate_ViewListFooter-{$_SESSION['User_ID']}.html";
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
        // ����μ���
        $div   = 'C';
        $query = $this->getQueryStatement1($request, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_uri       = 0;                   // ���ץ�����
            $c_endinv    = 0;                   // ���ץ����ê���⣱
        } else {
            // �ƥǡ����ν����
            $c_uri       = 0;                   // ���ץ�����
            $c_endinv    = 0;                   // ���ץ����ê���⣱
            for ($r=0; $r<$rows_t; $r++) {
                $c_uri     += $res_t[$r][9];
                $c_endinv  -= $res_t[$r][7];
            }
        }
        $query = $this->getQueryStatement17($request, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_uri       += 0;
            $c_endinv    -= 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_uri     += $res_t[$r][9];
                $c_endinv  -= $res_t[$r][7];
            }
        }
        $query = $this->getQueryStatement15($request, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_uri       += 0;
            $c_endinv    -= 0;
        } else {
            $c_uri     += $res_t[0][0];
            $c_endinv  -= $res_t[0][3];
        }
        
        $div   = 'L';
        $query = $this->getQueryStatement1($request, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_uri       = 0;                   // ��˥�����
            $l_endinv    = 0;                   // ��˥�����ê���⣱
        } else {
            // �ƥǡ����ν����
            $l_uri       = 0;                   // ��˥�����
            $l_endinv    = 0;                   // ��˥�����ê���⣱
            for ($r=0; $r<$rows_t; $r++) {
                $l_uri     += $res_t[$r][9];
                $l_endinv  -= $res_t[$r][7];
            }
        }
        $query = $this->getQueryStatement17($request, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_uri       += 0;
            $l_endinv    -= 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_uri     += $res_t[$r][9];
                $l_endinv  -= $res_t[$r][7];
            }
        }
        $query = $this->getQueryStatement15($request, $div);
        $res_t   = array();
        $field_t = array();
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_uri       += 0;
            $l_endinv    -= 0;
        } else {
            $l_uri     += $res_t[0][0];
            $l_endinv  -= $res_t[0][3];
        }
        
        // ����ê����μ���
        $res_t   = array();
        $field_t = array();
        $div   = 'C';
        $query = $this->getQueryStatement2($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_invent = 0;
        } else {
            $c_invent = -$res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $div   = 'L';
        $query = $this->getQueryStatement2($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_invent = 0;
        } else {
            $l_invent = -$res_t[0][0];
        }
        // ������μ���
        $res_t   = array();
        $field_t = array();
        $div   = 'C';
        $query = $this->getQueryStatement3($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial = 0;
        } else {
            $c_metarial = $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement4($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            $c_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement5($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            $c_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement6($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_metarial += $res_t[$r][2];
            }
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement7($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            $c_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement8($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_metarial += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_metarial += $res_t[$r][2];
            }
        }
        
        $res_t   = array();
        $field_t = array();
        $div   = 'L';
        $query = $this->getQueryStatement3($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial = 0;
        } else {
            $l_metarial = $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement4($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            $l_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement5($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            $l_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement6($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_metarial += $res_t[$r][2];
            }
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement7($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            $l_metarial += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement8($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_metarial += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_metarial += $res_t[$r][2];
            }
        }
        // ����ê����μ���
        $res_t   = array();
        $field_t = array();
        $div   = 'C';
        $query = $this->getQueryStatement9($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            $c_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement10($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            $c_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement11($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            $c_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement12($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_endinv += $res_t[$r][2];
            }
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement13($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            $c_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement14($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $c_endinv += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $c_endinv += $res_t[$r][2];
            }
        }
        
        $res_t   = array();
        $field_t = array();
        $div   = 'L';
        $query = $this->getQueryStatement9($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            $l_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement10($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            $l_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement11($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            $l_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement12($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_endinv += $res_t[$r][2];
            }
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement13($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            $l_endinv += $res_t[0][0];
        }
        $res_t   = array();
        $field_t = array();
        $query = $this->getQueryStatement14($request, $div);
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $l_endinv += 0;
        } else {
            for ($r=0; $r<$rows_t; $r++) {
                $l_endinv += $res_t[$r][2];
            }
        }
        
        // �Ƽ����η׻�
        $note = array();
        $div = 'C';
        $note[0]  = '���ץ�ϫ̳��';
        $note[1]  = '���ץ���¤����';
        $note[2]  = '���ץ�ͷ���';
        $note[3]  = '���ץ����';
        $note[4]  = '���ץ��̳��������';
        $note[5]  = '���ץ�������';
        $note[6]  = '���ץ�Ķȳ����פ���¾';
        $note[7]  = '���ץ��ʧ��©';
        $note[8]  = '���ץ�Ķȳ����Ѥ���¾';
        $uri_note = '���ץ�����';
        $num = count($note);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement16($request, $note[$r]);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>����ͽ¬��ɬ�פʥǡ�������Ͽ����Ƥ��ޤ���</font>");
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
                exit();
            } else {
                $kei_tmp = $res_t[0][0];
            }
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement16($request, $uri_note);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>����ͽ¬��ɬ�פʥǡ�������Ͽ����Ƥ��ޤ���</font>");
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
                exit();
            } else {
                $uri_tmp = $res_t[0][0];
            }
            $kei_ritsu = round($kei_tmp / $uri_tmp, 4);
            $kei_kin   = round($c_uri * $kei_ritsu, 0);
            if ($r == 0) {
                $c_roumu    = $kei_kin;     // ��¤����-ϫ̳��
            } elseif ($r == 1) {
                $c_expense  = $kei_kin;     // ��¤����-����
            } elseif ($r == 2) {
                $c_han_jin  = $kei_kin;     // �δ���-�ͷ���
            } elseif ($r == 3) {
                $c_han_kei  = $kei_kin;     // �δ���-����
            } elseif ($r == 4) {
                $c_gyoumu   = $kei_kin;     // ��̳��������
            } elseif ($r == 5) {
                $c_swari    = $kei_kin;     // �������
            } elseif ($r == 6) {
                $c_pother   = $kei_kin;     // �Ķȳ����פ���¾
            } elseif ($r == 7) {
                $c_srisoku  = $kei_kin;     // ��ʧ��©
            } elseif ($r == 8) {
                $c_lother   = $kei_kin;     // �Ķȳ����Ѥ���¾
            }
        }
                
        $note = array();
        $div = 'L';
        $note[0]  = '��˥�ɸ��ϫ̳��';
        $note[1]  = '��˥�ɸ����¤����';
        $note[2]  = '��˥�ɸ��ͷ���';
        $note[3]  = '��˥�ɸ�����';
        $note[4]  = '��˥�ɸ���̳��������';
        $note[5]  = '��˥�ɸ��������';
        $note[6]  = '��˥�ɸ��Ķȳ����פ���¾';
        $note[7]  = '��˥�ɸ���ʧ��©';
        $note[8]  = '��˥�ɸ��Ķȳ����Ѥ���¾';
        $uri_note = '��˥�ɸ������';
        /*
        $note[0]  = '��˥�ϫ̳��';
        $note[1]  = '��˥���¤����';
        $note[2]  = '��˥��ͷ���';
        $note[3]  = '��˥�����';
        $note[4]  = '��˥���̳��������';
        $note[5]  = '��˥��������';
        $note[6]  = '��˥��Ķȳ����פ���¾';
        $note[7]  = '��˥���ʧ��©';
        $note[8]  = '��˥��Ķȳ����Ѥ���¾';
        $uri_note = '��˥�����';
        */
        $num = count($note);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement16($request, $note[$r]);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>����ͽ¬��ɬ�פʥǡ�������Ͽ����Ƥ��ޤ���</font>");
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
                exit();
            } else {
                $kei_tmp = $res_t[0][0];
            }
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement16($request, $uri_note);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>����ͽ¬��ɬ�פʥǡ�������Ͽ����Ƥ��ޤ���</font>");
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
                exit();
            } else {
                $uri_tmp = $res_t[0][0];
            }
            $kei_ritsu = round($kei_tmp / $uri_tmp, 4);
            $kei_kin   = round($l_uri * $kei_ritsu, 0);
            if ($r == 0) {
                $l_roumu    = $kei_kin;     // ��¤����-ϫ̳��
            } elseif ($r == 1) {
                $l_expense  = $kei_kin;     // ��¤����-����
            } elseif ($r == 2) {
                $l_han_jin  = $kei_kin;     // �δ���-�ͷ���
            } elseif ($r == 3) {
                $l_han_kei  = $kei_kin;     // �δ���-����
            } elseif ($r == 4) {
                $l_gyoumu   = $kei_kin;     // ��̳��������
            } elseif ($r == 5) {
                $l_swari    = $kei_kin;     // �������
            } elseif ($r == 6) {
                $l_pother   = $kei_kin;     // �Ķȳ����פ���¾
            } elseif ($r == 7) {
                $l_srisoku  = $kei_kin;     // ��ʧ��©
            } elseif ($r == 8) {
                $l_lother   = $kei_kin;     // �Ķȳ����Ѥ���¾
            }
        }
        // ���ʴ����ʲ�ǯ�֤�ʿ�ѡ�
        $item_b = array();
        $item_b[0]  = '���ʴ�������';
        $item_b[1]  = '���ʴ�����������ų���ê����';
        $item_b[2]  = '���ʴ���������(������)';
        $item_b[3]  = '���ʴ���ϫ̳��';
        $item_b[4]  = '���ʴ�����¤����';
        $item_b[5]  = '���ʴ������������ų���ê����';
        $item_b[6]  = '���ʴ�����帶��';
        $item_b[7]  = '���ʴ������������';
        $item_b[8]  = '���ʴ����ͷ���';
        $item_b[9]  = '���ʴ�������';
        $item_b[10] = '���ʴ����δ���ڤӰ��̴������';
        $item_b[11] = '���ʴ����Ķ�����';
        $item_b[12] = '���ʴ�����̳��������';
        $item_b[13] = '���ʴ����������';
        $item_b[14] = '���ʴ����Ķȳ����פ���¾';
        $item_b[15] = '���ʴ����Ķȳ����׷�';
        $item_b[16] = '���ʴ�����ʧ��©';
        $item_b[17] = '���ʴ����Ķȳ����Ѥ���¾';
        $item_b[18] = '���ʴ����Ķȳ����ѷ�';
        $item_b[19] = '���ʴ����о�����';
        $num = count($item_b);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement16($request, $item_b[$r]);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $b_uri = 0;
                } elseif ($r == 1) {
                    $b_invent = 0;
                } elseif ($r == 2) {
                    $b_metarial = 0;
                } elseif ($r == 3) {
                    $b_roumu = 0;
                } elseif ($r == 4) {
                    $b_expense = 0;
                } elseif ($r == 5) {
                    $b_endinv = 0;
                } elseif ($r == 6) {
                    $b_urigen = 0;
                } elseif ($r == 7) {
                    $b_gross_profit = 0;
                } elseif ($r == 8) {
                    $b_han_jin = 0;
                } elseif ($r == 9) {
                    $b_han_kei = 0;
                } elseif ($r == 10) {
                    $b_han_all = 0;
                } elseif ($r == 11) {
                    $b_ope_profit = 0;
                } elseif ($r == 12) {
                    $b_gyoumu = 0;
                } elseif ($r == 13) {
                    $b_swari = 0;
                } elseif ($r == 14) {
                    $b_pother = 0;
                } elseif ($r == 15) {
                    $b_nonope_profit_sum = 0;
                } elseif ($r == 16) {
                    $b_srisoku = 0;
                } elseif ($r == 17) {
                    $b_lother = 0;
                } elseif ($r == 18) {
                    $b_nonope_loss_sum = 0;
                } elseif ($r == 19) {
                    $b_current_profit = 0;
                }
            } else {
                if ($r == 0) {
                    $b_uri = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 1) {
                    $b_invent = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 2) {
                    $b_metarial = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 3) {
                    $b_roumu = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 4) {
                    $b_expense = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 5) {
                    $b_endinv = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 6) {
                    $b_urigen = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 7) {
                    $b_gross_profit = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 8) {
                    $b_han_jin = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 9) {
                    $b_han_kei = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 10) {
                    $b_han_all = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 11) {
                    $b_ope_profit = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 12) {
                    $b_gyoumu = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 13) {
                    $b_swari = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 14) {
                    $b_pother = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 15) {
                    $b_nonope_profit_sum = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 16) {
                    $b_srisoku = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 17) {
                    $b_lother = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 18) {
                    $b_nonope_loss_sum = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 19) {
                    $b_current_profit = round(($res_t[0][0] / 12), 0);
                }
            }
        }
        // �����
        $item_s = array();
        $item_s[0]  = '���������';
        $item_s[1]  = '�������������ų���ê����';
        $item_s[2]  = '�����������(������)';
        $item_s[3]  = '�����ϫ̳��';
        $item_s[4]  = '�������¤����';
        $item_s[5]  = '��������������ų���ê����';
        $item_s[6]  = '�������帶��';
        $item_s[7]  = '��������������';
        $item_s[8]  = '������ͷ���';
        $item_s[9]  = '���������';
        $item_s[10] = '������δ���ڤӰ��̴������';
        $item_s[11] = '������Ķ�����';
        $item_s[12] = '�������̳��������';
        $item_s[13] = '������������';
        $item_s[14] = '������Ķȳ����פ���¾';
        $item_s[15] = '������Ķȳ����׷�';
        $item_s[16] = '�������ʧ��©';
        $item_s[17] = '������Ķȳ����Ѥ���¾';
        $item_s[18] = '������Ķȳ����ѷ�';
        $item_s[19] = '������о�����';
        $num = count($item_s);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement16($request, $item_s[$r]);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $s_uri = 0;
                } elseif ($r == 1) {
                    $s_invent = 0;
                } elseif ($r == 2) {
                    $s_metarial = 0;
                } elseif ($r == 3) {
                    $s_roumu = 0;
                } elseif ($r == 4) {
                    $s_expense = 0;
                } elseif ($r == 5) {
                    $s_endinv = 0;
                } elseif ($r == 6) {
                    $s_urigen = 0;
                } elseif ($r == 7) {
                    $s_gross_profit = 0;
                } elseif ($r == 8) {
                    $s_han_jin = 0;
                } elseif ($r == 9) {
                    $s_han_kei = 0;
                } elseif ($r == 10) {
                    $s_han_all = 0;
                } elseif ($r == 11) {
                    $s_ope_profit = 0;
                } elseif ($r == 12) {
                    $s_gyoumu = 0;
                } elseif ($r == 13) {
                    $s_swari = 0;
                } elseif ($r == 14) {
                    $s_pother = 0;
                } elseif ($r == 15) {
                    $s_nonope_profit_sum = 0;
                } elseif ($r == 16) {
                    $s_srisoku = 0;
                } elseif ($r == 17) {
                    $s_lother = 0;
                } elseif ($r == 18) {
                    $s_nonope_loss_sum = 0;
                } elseif ($r == 19) {
                    $s_current_profit = 0;
                }
            } else {
                if ($r == 0) {
                    $s_uri = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 1) {
                    $s_invent = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 2) {
                    $s_metarial = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 3) {
                    $s_roumu = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 4) {
                    $s_expense = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 5) {
                    $s_endinv = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 6) {
                    $s_urigen = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 7) {
                    $s_gross_profit = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 8) {
                    $s_han_jin = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 9) {
                    $s_han_kei = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 10) {
                    $s_han_all = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 11) {
                    $s_ope_profit = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 12) {
                    $s_gyoumu = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 13) {
                    $s_swari = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 14) {
                    $s_pother = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 15) {
                    $s_nonope_profit_sum = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 16) {
                    $s_srisoku = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 17) {
                    $s_lother = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 18) {
                    $s_nonope_loss_sum = round(($res_t[0][0] / 12), 0);
                } elseif ($r == 19) {
                    $s_current_profit = round(($res_t[0][0] / 12), 0);
                }
            }
        }
        
        // ����ê����η׻�
        $c_endinv = -($c_invent + $c_endinv);
        $l_endinv = -($l_invent + $l_endinv);
        // ��帶���η׻�
        $c_urigen = $c_invent + $c_metarial + $c_roumu + $c_expense + $c_endinv;
        $l_urigen = $l_invent + $l_metarial + $l_roumu + $l_expense + $l_endinv;
        $s_urigen = $s_invent + $s_metarial + $s_roumu + $s_expense + $s_endinv;
        $b_urigen = $b_invent + $b_metarial + $b_roumu + $b_expense + $b_endinv;
        // ��������פη׻�
        $c_gross_profit = $c_uri - $c_urigen;
        $l_gross_profit = $l_uri - $l_urigen;
        $s_gross_profit = $s_uri - $s_urigen;
        $b_gross_profit = $b_uri - $b_urigen;
        // �δ����פη׻�
        $c_han_all = $c_han_jin + $c_han_kei;
        $l_han_all = $l_han_jin + $l_han_kei;
        $s_han_all = $s_han_jin + $s_han_kei;
        $b_han_all = $b_han_jin + $b_han_kei;
        // �Ķ����פη׻�
        $c_ope_profit = $c_gross_profit - $c_han_all;
        $l_ope_profit = $l_gross_profit - $l_han_all;
        $s_ope_profit = $s_gross_profit - $s_han_all;
        $b_ope_profit = $b_gross_profit - $b_han_all;
        // �Ķȳ����׷פη׻�
        $c_nonope_profit_sum = $c_gyoumu + $c_swari + $c_pother;
        $l_nonope_profit_sum = $l_gyoumu + $l_swari + $l_pother;
        $s_nonope_profit_sum = $s_gyoumu + $s_swari + $s_pother;
        $b_nonope_profit_sum = $b_gyoumu + $b_swari + $b_pother;
        // �Ķȳ����ѷפη׻�
        $c_nonope_loss_sum = $c_srisoku + $c_lother;
        $l_nonope_loss_sum = $l_srisoku + $l_lother;
        $s_nonope_loss_sum = $s_srisoku + $s_lother;
        $b_nonope_loss_sum = $b_srisoku + $b_lother;
        // �о����פη׻�
        $c_current_profit = $c_ope_profit + $c_nonope_profit_sum - $c_nonope_loss_sum;
        $l_current_profit = $l_ope_profit + $l_nonope_profit_sum - $l_nonope_loss_sum;
        $s_current_profit = $s_ope_profit + $s_nonope_profit_sum - $s_nonope_loss_sum;
        $b_current_profit = $b_ope_profit + $b_nonope_profit_sum - $b_nonope_loss_sum;
        
        // �ƹ�פη׻�
        $all_uri               = $c_uri + $l_uri + $s_uri + $b_uri;                         // ������
        $all_invent            = $c_invent + $l_invent + $s_invent + $b_invent;             // ����ê������
        $all_metarial          = $c_metarial + $l_metarial + $s_metarial + $b_metarial;     // ��������
        $all_roumu             = $c_roumu + $l_roumu + $s_roumu + $b_roumu;                 // ��¤����-ϫ̳����
        $all_expense           = $c_expense + $l_expense + $s_expense + $b_expense;         // ��¤����-������
        $all_endinv            = $c_endinv + $l_endinv + $s_endinv + $b_endinv;             // ����ê������
        $all_urigen            = $c_urigen + $l_urigen + $s_urigen + $b_urigen;             // ��帶�����
        $all_gross_profit      = $c_gross_profit + $l_gross_profit + $s_gross_profit + $b_gross_profit;                     // ��������׹��
        $all_han_jin           = $c_han_jin + $l_han_jin + $s_han_jin + $b_han_jin;         // �δ���-�ͷ�����
        $all_han_kei           = $c_han_kei + $l_han_kei + $s_han_kei + $b_han_kei;         // �δ���-������
        $all_han_all           = $c_han_all + $l_han_all + $s_han_all + $b_han_all;         // �δ���� ���
        $all_ope_profit        = $c_ope_profit + $l_ope_profit + $s_ope_profit + $b_ope_profit;                             // �Ķ����׹��
        $all_gyoumu            = $c_gyoumu + $l_gyoumu + $s_gyoumu + $b_gyoumu;             // �Ķȳ�����-��̳�����������
        $all_swari             = $c_swari + $l_swari + $s_swari + $b_swari;                 // �Ķȳ�����-����������
        $all_pother            = $c_pother + $l_pother + $s_pother + $b_pother;             // �Ķȳ�����-����¾���
        $all_nonope_profit_sum = $c_nonope_profit_sum + $l_nonope_profit_sum + $s_nonope_profit_sum + $b_nonope_profit_sum; // �Ķȳ����׷� ���
        $all_srisoku           = $c_srisoku + $l_srisoku + $s_srisoku + $b_srisoku;         // �Ķȳ�����-��ʧ��©���
        $all_lother            = $c_lother + $l_lother + $s_lother + $b_lother;             // �Ķȳ�����-����¾
        $all_nonope_loss_sum   = $c_nonope_loss_sum + $l_nonope_loss_sum + $s_nonope_loss_sum + $b_nonope_loss_sum;         // �Ķȳ����ѷ� ���
        $all_current_profit    = $c_current_profit + $l_current_profit + $s_current_profit + $b_current_profit;             // �о����� ���
        
        
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
            $listTable .= "<tr>\n";
            $listTable .= "        <td colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6' nowrap>�ࡡ������</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>�����ס���</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>�ꡡ�ˡ���</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>�������</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>���ʴ���</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>�硡������</td>\n";
            $listTable .= "        <td width='400' align='left' class='pt10b' bgcolor='#ffffc6' nowrap>�׻���ˡ(���ɡ���Ϥ��٤Ʋ�ǯ�֤�ʿ��)</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>�ġ��ȡ�»����</td>\n";
            $listTable .= "        <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�䡡�塡��</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($c_uri) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($l_uri) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($s_uri) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($b_uri) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($all_uri) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>��Ω�����ײ�</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>��帶��</td> <!-- ��帶�� -->\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>����������ų���ê����</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($c_invent) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($l_invent) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($s_invent) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($b_invent) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($all_invent) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>�º�ê����</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>��������(������)</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($c_metarial) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($l_metarial) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($s_metarial) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($b_metarial) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($all_metarial) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>Ǽ��ͽ����(���)</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>��ϫ����̳������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($c_roumu) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($l_roumu) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($s_roumu) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($b_roumu) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($all_roumu) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>ľ�ᣱǯ�֤�������</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���С�����������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($c_expense) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($l_expense) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($s_expense) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($b_expense) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($all_expense) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>ľ�ᣱǯ�֤�������</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>�����������ų���ê����</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($c_endinv) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($l_endinv) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($s_endinv) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($b_endinv) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($all_endinv) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>�ǿ����������׻�</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���䡡�塡������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($c_urigen) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($l_urigen) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($s_urigen) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($b_urigen) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($all_urigen) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�䡡�塡��������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($c_gross_profit) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($l_gross_profit) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($s_gross_profit) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($b_gross_profit) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($all_gross_profit) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>��</td>  <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- �δ��� -->\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���͡��������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($c_han_jin) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($l_han_jin) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($s_han_jin) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($b_han_jin) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($all_han_jin) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>ľ�ᣱǯ�֤�������</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>���С�����������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($c_han_kei) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($l_han_kei) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($s_han_kei) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($b_han_kei) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($all_han_kei) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>ľ�ᣱǯ�֤�������</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�δ���ڤӰ��̴������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($c_han_all) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($l_han_all) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($s_han_all) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($b_han_all) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($all_han_all) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�ġ����ȡ�����������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($c_ope_profit) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($l_ope_profit) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($s_ope_profit) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($b_ope_profit) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($all_ope_profit) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>�Ķȳ�»��</td>\n";
            $listTable .= "        <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- ;�� -->\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>����̳��������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($c_gyoumu) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($l_gyoumu) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($s_gyoumu) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($b_gyoumu) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($all_gyoumu) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>ľ�ᣱǯ�֤�������</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���š������䡡��</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($c_swari) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($l_swari) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($s_swari) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($b_swari) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($all_swari) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>ľ�ᣱǯ�֤�������</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>���������Ρ���¾</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($c_pother) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($l_pother) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($s_pother) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($b_pother) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($all_pother) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>ľ�ᣱǯ�֤�������</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���Ķȳ����� ��</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($c_nonope_profit_sum) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($l_nonope_profit_sum) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($s_nonope_profit_sum) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($b_nonope_profit_sum) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($all_nonope_profit_sum) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>��</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- ;�� -->\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���١�ʧ������©</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($c_srisoku) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($l_srisoku) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($s_srisoku) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($b_srisoku) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format($all_srisoku) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>ľ�ᣱǯ�֤�������</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>���������Ρ���¾</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($c_lother) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($l_lother) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($s_lother) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($b_lother) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format($all_lother) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>ľ�ᣱǯ�֤�������</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���Ķȳ����� ��</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($c_nonope_loss_sum) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($l_nonope_loss_sum) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($s_nonope_loss_sum) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($b_nonope_loss_sum) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format($all_nonope_loss_sum) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>��</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�С��������������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($c_current_profit) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($l_current_profit) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($s_current_profit) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($b_current_profit) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format($all_current_profit) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        $res = array();
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
    // ����ȴ���ê����ΰ��������(CL����)
    private function getQueryStatement1($request, $div)
    {
        //$str_date = $request->get('targetDateYM') . '01';
        // 2011/08/30 ͽ¬���ٸ���ΰ� ����μ�����ˡ���ѹ�
        // ����ޤǤϡ���Ω�����ײ�Τߤ�ͽ¬���Ƥ�����
        // �����ޤǤ������ӡ������������ޤǤ���Ω�����ײ�ι绻���ѹ�
        // �������Ϸ׻�������
        $str_date = date('Ymd');
        $end_date = $request->get('targetDateYM') . '31';
        /*if ($div == 'C') {
            if ($request->get('targetDateYM') < 200710) {
                $rate = 25.60;  // ���ץ�ɸ�� 2007/10/01���ʲ������
            } elseif ($request->get('targetDateYM') < 201104) {
                $rate = 57.00;  // ���ץ�ɸ�� 2007/10/01���ʲ���ʹ�
            } else {
                $rate = 45.00;  // ���ץ�ɸ�� 2011/04/01���ʲ���ʹ�
            }
        } elseif ($div == 'L') {
            if ($request->get('targetDateYM') < 200710) {
                $rate = 37.00;  // ��˥� 2008/10/01���ʲ������
            } elseif ($request->get('targetDateYM') < 201104) {
                $rate = 44.00;  // ��˥� 2008/10/01���ʲ���ʹ�
            } else {
                $rate = 53.00;  // ��˥� 2011/04/01���ʲ���ʹ�
            }
        } else {
            $rate = 65.00;
        }*/
        /*$query = "SELECT  
                    a.plan_no       AS �ײ��ֹ�,
                    a.parts_no      AS �����ֹ�,
                    a.kanryou       AS ��λͽ����,
                    a.plan          AS �ײ��,
                    a.cut_plan      AS ���ڿ�,
                    a.kansei        AS ������,
                    (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS �ǿ��������,
                    CASE
                        WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                        THEN 
                             Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * (a.plan-a.cut_plan), 0)
                        ELSE
                             Uround((SELECT sum_price FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (a.plan-a.cut_plan), 0) 
                    END             AS ��������,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL THEN 0
                        ELSE (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                    END
                                    AS �ǿ�����ñ��,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL THEN 0
                        ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) * (a.plan-a.cut_plan), 0)
                    END
                                    AS ����
                    FROM assembly_schedule AS a
                    WHERE a.kanryou<={$end_date} AND a.kanryou>={$str_date} AND a.dept='{$div}'
                    AND (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$end_date} AND assy_no = a.parts_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) > 0
        ";
        */
        // 2011/08/30 ����ñ����¸�ߤ��ʤ���硢��夬�׻�����ʤ��ä���
        // ���κݤϺǿ���������1.13�ܤǻ���ñ����׻���������׻�����褦���ѹ�
        // �ޤ��ǿ��������μ�������WHEN�����оݷ����ޤǤκǿ���ȴ���Ф��Ƥ��뤬plan_no = u.�ײ��ֹ���ѹ�
        // 2011/09/05 ������Ϻ߸ˤ�����Ȥ��˴������ɲä���뤿�ᡢ1.026��ݤ��Ʒ׻�����
        if ($div == 'C') {
            $zai_rate = 1.026;
        } else {
            $zai_rate = 1.026;
        }
        $query = "SELECT
                    a.plan_no       AS �ײ��ֹ�,
                    a.parts_no      AS �����ֹ�,
                    a.kanryou       AS ��λͽ����,
                    a.plan          AS �ײ��,
                    a.cut_plan      AS ���ڿ�,
                    a.kansei        AS ������,
                    CASE
                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                        THEN 
                             CASE
                                 WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                 THEN
                                     Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2)
                                 ELSE
                                     Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2)
                             END
                        ELSE
                             Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2)
                    END             AS �ǿ��������,
                    CASE
                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                        THEN 
                             CASE
                                 WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                 THEN
                                     Uround(Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2) * (a.plan-a.cut_plan), 0)
                                 ELSE
                                     Uround(Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2) * (a.plan-a.cut_plan), 0)
                             END
                        ELSE
                             Uround(Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2) * (a.plan-a.cut_plan), 0) 
                    END             AS ��������,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL
                        THEN
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL  
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)*1.13, 0)
                                        ELSE
                                            Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)*1.13, 0)
                                    END
                                ELSE
                                    Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)*1.13, 0)
                            END

                        ELSE (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                    END
                                    AS �ǿ�����ñ��,
                    CASE
                        WHEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) IS NULL
                        THEN 
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL  
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround(Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$end_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)*1.13, 0) * (a.plan-a.cut_plan), 0)
                                        ELSE
                                            Uround(Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)*1.13, 0) * (a.plan-a.cut_plan), 0)
                                    END
                                ELSE
                                    Uround(Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = a.plan_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)*1.13, 0)  * (a.plan-a.cut_plan), 0) 
                            END
                        ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) * (a.plan-a.cut_plan), 0)
                    END
                                    AS ����
                    FROM assembly_schedule AS a
                    WHERE a.kanryou<={$end_date} AND a.kanryou>={$str_date} AND a.dept='{$div}' 
                    AND assy_site='01111' AND a.nyuuko!=30 AND p_kubun='F'
        ";
        return $query;
    }
    
    // ����ê����μ���(����δ���ê���� CL����)
    private function getQueryStatement2($request, $div)
    {
        if ($div == 'C') {
            $div_note = '���ץ���������ų���ê����';
        } else {
            $div_note = '��˥�ɸ����������ų���ê����';
            //$div_note = '��˥����������ų���ê����';
        }
        if (substr($request->get('targetDateYM'),4,2)!=01) {
            $p1_ym = $request->get('targetDateYM') - 1;
        } else {
            $p1_ym = $request->get('targetDateYM') - 100;
            $p1_ym = $p1_ym + 11;
        }
        $query = "
            SELECT kin FROM profit_loss_pl_history
            WHERE pl_bs_ym={$p1_ym} AND note='{$div_note}'
        ";
        return $query;
    }
    
    // ������μ�����(CL����)
    private function getQueryStatement3($request, $div)
    {
        $str_date = $request->get('targetDateYM') . '01';
        $end_date = $request->get('targetDateYM') . '31';
        // ���ܣ��ʾ夬���äƤ������ᣵ�ޤ��ѹ�
        /*
        $query = "
            select 
            sum(Uround(order_price * siharai,0)) 
            FROM act_payable 
            WHERE act_date>=$str_date AND act_date<=$end_date AND div='{$div}' AND vendor !='01111' AND vendor !='00222'
        ";
        */
        $query = "
            select 
            sum(Uround(order_price * siharai,0)) 
            FROM act_payable 
            WHERE act_date>=$str_date AND act_date<=$end_date AND div='{$div}' AND vendor !='01111' AND vendor !='00222' AND kamoku<=5
        ";
        return $query;
    }
    
    // ������μ�����(CL����)
    private function getQueryStatement4($request, $div)
    {
        $query = "
            SELECT  sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data          AS data
                LEFT OUTER JOIN
                    acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                LEFT OUTER JOIN
                    order_plan          AS plan     USING (sei_no)
                WHERE
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                    AND
                    uke_no > '500000' AND data.parts_no LIKE '{$div}%' and vendor !='01111' and vendor !='00222' LIMIT 1
        ";
        return $query;
    }
    
    // ������μ�����(CL����)
    private function getQueryStatement5($request, $div)
    {
        $str_date = date('Ym') - 200;
        $str_date = $str_date . '01';
        $end_date = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT  sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
                OFFSET 0
                LIMIT 1
        ";
        return $query;
    }
    
    // ������μ�����(CL����)
    private function getQueryStatement6($request, $div)
    {
        $end_date = date('Ym');
        $end_date = $end_date . '31';
        $str_date = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(data.order_q * data.order_price,0)) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery > $str_date
                    AND
                    proc.delivery <= $end_date
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC

        ";
        return $query;
    }
    
    // ������μ�����(CL����)
    private function getQueryStatement7($request, $div)
    {
        $str_date = date('Ym') - 200;
        $str_date = $str_date . '01';
        $end_date = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT sum(Uround(plan.order_q * proc.order_price,0))
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
                OFFSET 0
                LIMIT 1

        ";
        return $query;
    }
    
    // ������μ�����(CL����)
    private function getQueryStatement8($request, $div)
    {
        $end_date = date('Ym') . '31';
        $str_date = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(plan.order_q * proc.order_price,0)) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery > {$str_date}
                    AND
                    proc.delivery <= {$end_date}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   ' and vendor !='01111' and vendor !='00222'
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC

        ";
        return $query;
    }
    
    // ����ê����μ�����(CL����)   // �����ޤǤ���ݶ��
    private function getQueryStatement9($request, $div)
    {
        $str_date = $request->get('targetDateYM') . '01';
        $end_date = $request->get('targetDateYM') . '31';
        // ���ܣ��ʾ夬���äƤ������ᣵ�ޤ��ѹ�
        /*
        $query = "
            select sum(Uround(order_price * siharai,0)) 
            from act_payable 
            where act_date>={$str_date} and act_date<={$end_date} and div='{$div}' 
        ";
        */
        $query = "
            select sum(Uround(order_price * siharai,0)) 
            from act_payable 
            where act_date>={$str_date} and act_date<={$end_date} and div='{$div}' and kamoku<=5
        ";
        return $query;
    }
    
    // ����ê����μ�����(CL����) �����ų�ʬ(̤�������)�ι�פ����
    private function getQueryStatement10($request, $div)
    {
        $query = "
            SELECT  sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data          AS data
                LEFT OUTER JOIN
                    acceptance_kensa    AS ken  on(data.order_seq=ken.order_seq)
                LEFT OUTER JOIN
                    order_plan          AS plan     USING (sei_no)
                WHERE
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    ( (ken.end_timestamp IS NULL) OR (ken.end_timestamp >= (CURRENT_TIMESTAMP - interval '10 minute')) )
                    AND
                    uke_no > '500000' AND data.parts_no LIKE '{$div}%'
                LIMIT 1
        ";
        return $query;
    }
    
    // ����ê����μ�����(CL����) Ǽ���٤�ʬ�ι�פ����
    private function getQueryStatement11($request, $div)
    {
        $str_date = date('Ym') - 200;
        $str_date = $str_date . '01';
        $end_date = date('Ymd') - 1;
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT sum(Uround(data.order_q * data.order_price,0))
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   '
                OFFSET 0
                LIMIT 1
        ";
        return $query;
    }
    
    // ����ê����μ�����(CL����) �����ʹߤΥ��ޥ꡼�����
    private function getQueryStatement12($request, $div)
    {
        $end_date = date('Ym') . '31';
        $str_date = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(data.order_q * data.order_price,0)) as kin
                FROM
                    order_data      AS data
                LEFT OUTER JOIN
                    order_process   AS proc
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan     USING (sei_no)
                WHERE
                    proc.delivery >= {$str_date}
                    AND
                    proc.delivery <= {$end_date}
                    AND
                    uke_date <= 0       -- ̤Ǽ��ʬ
                    AND
                    ken_date <= 0       -- ̤����ʬ
                    AND
                    data.sei_no > 0     -- ��¤�ѤǤ���
                    AND
                    (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                    AND
                    data.parts_no like '{$div}%' AND proc.locate != '52   '
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
        ";
        return $query;
    }
    // ����ê����μ�����(CL����) ��������(��ʸ��̤ȯ��) Ǽ���٤�ʬ�ι�פ����
    private function getQueryStatement13($request, $div)
    {
        $str_date = date('Ym') - 200;
        $str_date = $str_date . '01';
        $end_date = date('Ymd') - 1;
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT sum(Uround(plan.order_q * proc.order_price,0))
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery <= {$end_date}
                    AND
                    proc.delivery >= {$str_date}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   '
                OFFSET 0
                LIMIT 1
        ";
        return $query;
    }
    // ����ê����μ�����(CL����) ��������(��ʸ��̤ȯ��) �����ʹߤΥ��ޥ꡼�����
    private function getQueryStatement14($request, $div)
    {
        $end_date = date('Ym') . '31';
        $str_date = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '00';
            $str_date  = $request->get('targetDateYM') . '00';
        }
        $query = "
            SELECT  substr(to_char(proc.delivery, 'FM9999-99-99'), 3, 8) AS delivery
                    , count(proc.delivery) AS cnt
                    , sum(Uround(plan.order_q * proc.order_price,0)) as kin
                FROM
                    order_process   AS proc
                LEFT OUTER JOIN
                    order_data      AS data
                                            using(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                    order_plan      AS plan
                                            using(sei_no)
                WHERE
                    proc.delivery >= {$str_date}
                    AND
                    proc.delivery <= {$end_date}
                    AND
                    proc.sei_no > 0                 -- ��¤�ѤǤ���
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'       -- �鹩�������
                    AND
                    to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'  -- ������֤�ʪ�����
                    AND
                    proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
                    AND
                    data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
                    AND
                    (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                                    -- �鹩�������ڤ���Ƥ��ʤ�ʪ
                    AND
                    proc.parts_no like '{$div}%' AND proc.locate != '52   '
                GROUP BY
                    proc.delivery
                ORDER BY
                    proc.delivery ASC
        ";
        return $query;
    }
    ///// ���ʡ�����¾���⡢������μ���
    private function getQueryStatement15($request, $div)
    {
        $end_date = $request->get('targetDateYM');
        $str_date = $request->get('targetDateYM');
        if (substr($str_date,4,2)>=07) {
            $str_date = $str_date - 6;
            $str_date = $str_date . '01';
        } else {
            $str_date = $str_date - 100;
            $str_date = $str_date + 6;
            $str_date = $str_date . '01';
        }
        if (substr($end_date,4,2)!=01) {
            $end_date = $end_date - 1;
            $end_date = $end_date . '31';
        } else {
            $end_date = $end_date - 100;
            $end_date = $end_date + 11;
            $end_date = $end_date . '31';
        }
        $query = "
            SELECT
                Uround(sum(Uround(����*ñ��, 0)) / 6, 0)         AS ��������
                ,
                Uround(sum(Uround(����*ext_cost, 0)) / 6, 0)       AS ����������
                ,
                Uround(sum(Uround(����*int_cost, 0)) / 6, 0)      AS ���������
                ,
                Uround(sum(Uround(����*unit_cost, 0)) / 6, 0)      AS ���������
                ,
                count(*)                            AS ����
                ,
                count(*)-count(unit_cost)
                                                    AS ̤��Ͽ
            FROM
                hiuuri
            LEFT OUTER JOIN
                sales_parts_material_history ON (assyno=parts_no AND �׾���=sales_date)
            WHERE �׾��� >= {$str_date} AND �׾��� <= {$end_date}
             AND ������ = '{$div}' AND (assyno not like 'NKB%%') AND (assyno not like 'SS%%')
             AND datatype >= '3' 
        ";
        return $query;
    }
    ///// ϫ̳�񡦷����ۼ���
    private function getQueryStatement16($request, $note_name)
    {
        
            $end_date = $request->get('targetDateYM');
            $str_date = $request->get('targetDateYM');
            if (substr($str_date,4,2)==12) {
                $str_date = $str_date - 11;
            } else {
                $str_date = $str_date - 99;
            }
            if (substr($end_date,4,2)!=01) {
                $end_date = $end_date - 1;
            } else {
                $end_date = $end_date - 100;
                $end_date = $end_date + 11;
            }
            $query = "
                SELECT sum(kin) FROM profit_loss_pl_history
                    WHERE pl_bs_ym<={$end_date} AND pl_bs_ym>={$str_date} AND note='{$note_name}'
        ";
        return $query;
    }
    // ����ȴ���ê����ΰ��������(CL����) ������٤��
    private function getQueryStatement17($request, $div)
    {
        $str_date  = $request->get('targetDateYM') . '01';
        $end_date  = date('Ymd');
        if (substr($end_date,0,6)>$request->get('targetDateYM')) {
            $end_date  = $request->get('targetDateYM') . '31';
        } elseif (substr($end_date,6,2)!=01) {
            $end_date  = date('Ymd') - 1;
        }
        $cost_date = $request->get('targetDateYM') . '31';
        /*if ($div == 'C') {
            if ($request->get('targetDateYM') < 200710) {
                $rate = 25.60;  // ���ץ�ɸ�� 2007/10/01���ʲ������
            } elseif ($request->get('targetDateYM') < 201104) {
                $rate = 57.00;  // ���ץ�ɸ�� 2007/10/01���ʲ���ʹ�
            } else {
                $rate = 45.00;  // ���ץ�ɸ�� 2011/04/01���ʲ���ʹ�
            }
        } elseif ($div == 'L') {
            if ($request->get('targetDateYM') < 200710) {
                $rate = 37.00;  // ��˥� 2008/10/01���ʲ������
            } elseif ($request->get('targetDateYM') < 201104) {
                $rate = 44.00;  // ��˥� 2008/10/01���ʲ���ʹ�
            } else {
                $rate = 53.00;  // ��˥� 2011/04/01���ʲ���ʹ�
            }
        } else {
            $rate = 65.00;
        }*/
        if ($div == 'C') {
            $zai_rate = 1.026;
        } else {
            $zai_rate = 1.026;
        }
        $query = "select
                        u.�׾���        as �׾���,                  -- 0
                            CASE
                                WHEN u.datatype=1 THEN '����'
                                WHEN u.datatype=2 THEN '����'
                                WHEN u.datatype=3 THEN '����'
                                WHEN u.datatype=4 THEN 'Ĵ��'
                                WHEN u.datatype=5 THEN '��ư'
                                WHEN u.datatype=6 THEN 'ľǼ'
                                WHEN u.datatype=7 THEN '���'
                                WHEN u.datatype=8 THEN '����'
                                WHEN u.datatype=9 THEN '����'
                                ELSE u.datatype
                            END             as ��ʬ,                    -- 1
                            CASE
                                WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.�ײ��ֹ�
                            END                     as �ײ��ֹ�,        -- 2
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as �����ֹ�,        -- 3
                            CASE
                                WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.���˾��
                            END                     as ����,            -- 4
                            u.����          as ����,                    -- 5
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = u.�ײ��ֹ� ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$cost_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2)
                                        ELSE
                                            Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2)
                                    END
                                ELSE
                                    Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = u.�ײ��ֹ� ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2)
                            END             AS �ǿ��������,            -- 6
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = u.�ײ��ֹ� ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL
                                THEN
                                    CASE
                                        WHEN (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) IS NULL
                                        THEN
                                            Uround(Uround((SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<{$cost_date} AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1) * $zai_rate, 2) * u.����, 0)
                                        ELSE
                                            Uround(Uround((select sum_price from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1) * $zai_rate, 2) * u.����, 0)
                                    END
                                ELSE
                                    Uround(Uround((SELECT sum_price FROM material_cost_header WHERE plan_no = u.�ײ��ֹ� ORDER BY assy_no DESC, regdate DESC LIMIT 1) * $zai_rate, 2) * u.����, 0)
                            END             AS ��������,              -- 7
                            u.ñ��          as ����ñ��,                -- 8
                            Uround(u.���� * u.ñ��, 0) as ���          -- 9
                      from
                            hiuuri as u
                      left outer join
                            assembly_schedule as a
                      on u.�ײ��ֹ�=a.plan_no
                      left outer join
                            miitem as m
                      on u.assyno=m.mipn
                      left outer join
                            material_cost_header as mate
                      on u.�ײ��ֹ�=mate.plan_no
                      LEFT OUTER JOIN
                            sales_parts_material_history AS pmate
                      ON (u.assyno=pmate.parts_no AND u.�׾���=pmate.sales_date) 
                      where �׾���>={$str_date} and �׾���<={$end_date} and ������='{$div}' and datatype='1'
                      order by u.�׾���, assyno
        ";
        return $query;
    }
    ///// ���ɡ��»�׼���(6����)
    private function getQueryStatement18($request, $note_name)
    {
        
            $end_date = $request->get('targetDateYM');
            $str_date = $request->get('targetDateYM');
            if (substr($str_date,4,2)==06) {
                $str_date = $str_date - 11;
            } else {
                $str_date = $str_date - 99;
            }
            if (substr($end_date,4,2)!=01) {
                $end_date = $end_date - 1;
            } else {
                $end_date = $end_date - 100;
                $end_date = $end_date + 11;
            }
            $query = "
                SELECT sum(kin) FROM profit_loss_pl_history
                    WHERE pl_bs_ym<={$end_date} AND pl_bs_ym>={$str_date} AND note='{$note_name}'
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
<title>»��ͽ¬�Ȳ�</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../profit_loss_estimate.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../profit_loss_estimate.js'></script>
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
    
} // Class ProfitLossEstimate_Model End

?>
