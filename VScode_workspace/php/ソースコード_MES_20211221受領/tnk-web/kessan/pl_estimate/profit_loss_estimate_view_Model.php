<?php
//////////////////////////////////////////////////////////////////////////////
// »��ͽ¬�ν��ס�ʬ�� ��� �Ȳ�(�Ȳ�Τ�)                  MVC Model ��   //
// Copyright (C) 2011-2014 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/07/19 Created   profit_loss_estimate_view_Model.php                 //
// 2011/07/20 �ºݡ����ۤ�ɽ�����ɲá�������ɡ����Τϥ����Ȳ�          //
//            ������ɤϻ���Ū�˲�ǯʬ��ʿ�Ѥ�ɽ��(�׻��Ѥߤ����)    //
// 2011/07/25 ǯ���ɽ�����֤ʤɡ��쥤������Ĵ��                            //
// 2011/08/02 �����ܥ���Υƥ���                                            //
// 2011/08/04 ñ�̤ȷ�����ɲ�                                              //
// 2011/11/24 ���ۤη׻���ʬ����ˤ����ä��Τǡ�������դ��ѹ�              //
// 2014/08/25 ���٤Ƥ�ǯ���ɽ������褦���ѹ�                              //
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
        $query = "
            SELECT  target_ym
            FROM act_pl_estimate
            GROUP BY target_ym
            ORDER BY target_ym DESC
        ";
        $res = array();
        //if ($request->get('targetDateYM') == $yyyymm) {
        //    $option .= "<option value='{$yyyymm}' selected>{$yyyy}ǯ{$mm}��</option>\n";
        //} else {
        //    $option .= "<option value='{$yyyymm}'>{$yyyy}ǯ{$mm}��</option>\n";
        //}
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>»��ͽ¬�Υǡ���������ޤ���</font>");
            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
            exit();
        } else {
            for ($i=0; $i<$rows_t; $i++) {   // 12�������ޤ�
                $yyyymm = $res_t[$i][0];
                $yyyy   = substr($yyyymm,0,4);
                $mm     = substr($yyyymm,4,2);
                if ($request->get('targetDateYM') == $yyyymm) {
                    $option .= "<option value='{$yyyymm}' selected>{$yyyy}ǯ{$mm}��</option>\n";
                } else {
                    $option .= "<option value='{$yyyymm}'>{$yyyy}ǯ{$mm}��</option>\n";
                }
            }
        }
        
        return $option;
    }
    ///// ����ǯ���HTML <select> option �ν���
    public function getTargetDateYMDvalues($request)
    {
        // �����
        $target_ym = $request->get('targetDateYM');
        //$target_ym = 201107;
        if ($target_ym == "") {
            return false;
        }
        $option = "\n";
        $query = "
            SELECT cal_ymd
            FROM act_pl_estimate
            WHERE target_ym={$target_ym}
            GROUP BY cal_ymd
            ORDER BY cal_ymd DESC
            LIMIT 31
        ";
        $res = array();
        //if ($request->get('targetDateYM') == $yyyymm) {
        //    $option .= "<option value='{$yyyymm}' selected>{$yyyy}ǯ{$mm}��</option>\n";
        //} else {
        //    $option .= "<option value='{$yyyymm}'>{$yyyy}ǯ{$mm}��</option>\n";
        //}
        if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>»��ͽ¬�Υǡ���������ޤ���</font>");
            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
            exit();
        } else {
            for ($i=0; $i<$rows_t; $i++) {  // ������ʬ(�оݷ�ʬ���٤�)
                $ymd    = $res_t[$i][0];
                $yyyy   = substr($ymd,0,4);
                $mm     = substr($ymd,4,2);
                $dd     = substr($ymd,6,2);
                if ($request->get('targetDateYMD') == $ymd) {
                    $option .= "<option value='{$ymd}' selected>{$yyyy}ǯ{$mm}��{$dd}��</option>\n";
                } else {
                    $option .= "<option value='{$ymd}'>{$yyyy}ǯ{$mm}��{$dd}��</option>\n";
                }
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
        $file_name = "list/profit_loss_estimate_view_ViewListHeader-{$_SESSION['User_ID']}.html";
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
        $file_name = "list/profit_loss_estimate_view_ViewList-{$_SESSION['User_ID']}.html";
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
        $file_name = "list/profit_loss_estimat_viewe_ViewListFooter-{$_SESSION['User_ID']}.html";
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
        // »��ͽ¬�ǡ����μ���
        // ���ץ�
        $item_c = array();
        $item_c[0]  = '���ץ�����';
        $item_c[1]  = '���ץ��������ų���ê����';
        $item_c[2]  = '���ץ������(������)';
        $item_c[3]  = '���ץ�ϫ̳��';
        $item_c[4]  = '���ץ���¤����';
        $item_c[5]  = '���ץ���������ų���ê����';
        $item_c[6]  = '���ץ���帶��';
        $item_c[7]  = '���ץ����������';
        $item_c[8]  = '���ץ�ͷ���';
        $item_c[9]  = '���ץ����';
        $item_c[10] = '���ץ��δ���ڤӰ��̴������';
        $item_c[11] = '���ץ�Ķ�����';
        $item_c[12] = '���ץ��̳��������';
        $item_c[13] = '���ץ�������';
        $item_c[14] = '���ץ�Ķȳ����פ���¾';
        $item_c[15] = '���ץ�Ķȳ����׷�';
        $item_c[16] = '���ץ��ʧ��©';
        $item_c[17] = '���ץ�Ķȳ����Ѥ���¾';
        $item_c[18] = '���ץ�Ķȳ����ѷ�';
        $item_c[19] = '���ץ�о�����';
        $num = count($item_c);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement1($request, $item_c[$r]);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $c_uri = 0;
                } elseif ($r == 1) {
                    $c_invent = 0;
                } elseif ($r == 2) {
                    $c_metarial = 0;
                } elseif ($r == 3) {
                    $c_roumu = 0;
                } elseif ($r == 4) {
                    $c_expense = 0;
                } elseif ($r == 5) {
                    $c_endinv = 0;
                } elseif ($r == 6) {
                    $c_urigen = 0;
                } elseif ($r == 7) {
                    $c_gross_profit = 0;
                } elseif ($r == 8) {
                    $c_han_jin = 0;
                } elseif ($r == 9) {
                    $c_han_kei = 0;
                } elseif ($r == 10) {
                    $c_han_all = 0;
                } elseif ($r == 11) {
                    $c_ope_profit = 0;
                } elseif ($r == 12) {
                    $c_gyoumu = 0;
                } elseif ($r == 13) {
                    $c_swari = 0;
                } elseif ($r == 14) {
                    $c_pother = 0;
                } elseif ($r == 15) {
                    $c_nonope_profit_sum = 0;
                } elseif ($r == 16) {
                    $c_srisoku = 0;
                } elseif ($r == 17) {
                    $c_lother = 0;
                } elseif ($r == 18) {
                    $c_nonope_loss_sum = 0;
                } elseif ($r == 19) {
                    $c_current_profit = 0;
                }
            } else {
                if ($r == 0) {
                    $c_uri = $res_t[0][0];
                } elseif ($r == 1) {
                    $c_invent = $res_t[0][0];
                } elseif ($r == 2) {
                    $c_metarial = $res_t[0][0];
                } elseif ($r == 3) {
                    $c_roumu = $res_t[0][0];
                } elseif ($r == 4) {
                    $c_expense = $res_t[0][0];
                } elseif ($r == 5) {
                    $c_endinv = $res_t[0][0];
                } elseif ($r == 6) {
                    $c_urigen = $res_t[0][0];
                } elseif ($r == 7) {
                    $c_gross_profit = $res_t[0][0];
                } elseif ($r == 8) {
                    $c_han_jin = $res_t[0][0];
                } elseif ($r == 9) {
                    $c_han_kei = $res_t[0][0];
                } elseif ($r == 10) {
                    $c_han_all = $res_t[0][0];
                } elseif ($r == 11) {
                    $c_ope_profit = $res_t[0][0];
                } elseif ($r == 12) {
                    $c_gyoumu = $res_t[0][0];
                } elseif ($r == 13) {
                    $c_swari = $res_t[0][0];
                } elseif ($r == 14) {
                    $c_pother = $res_t[0][0];
                } elseif ($r == 15) {
                    $c_nonope_profit_sum = $res_t[0][0];
                } elseif ($r == 16) {
                    $c_srisoku = $res_t[0][0];
                } elseif ($r == 17) {
                    $c_lother = $res_t[0][0];
                } elseif ($r == 18) {
                    $c_nonope_loss_sum = $res_t[0][0];
                } elseif ($r == 19) {
                    $c_current_profit = $res_t[0][0];
                }
            }
        }
        // »�׼��ӥǡ����μ���
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement2($request, $item_c[$r]);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $c_uri_r = 0;
                    $c_uri_d = 0;
                } elseif ($r == 1) {
                    $c_invent_r = 0;
                    $c_invent_d = 0;
                } elseif ($r == 2) {
                    $c_metarial_r = 0;
                    $c_metarial_d = 0;
                } elseif ($r == 3) {
                    $c_roumu_r = 0;
                    $c_roumu_d = 0;
                } elseif ($r == 4) {
                    $c_expense_r = 0;
                    $c_expense_d = 0;
                } elseif ($r == 5) {
                    $c_endinv_r = 0;
                    $c_endinv_d = 0;
                } elseif ($r == 6) {
                    $c_urigen_r = 0;
                    $c_urigen_d = 0;
                } elseif ($r == 7) {
                    $c_gross_profit_r = 0;
                    $c_gross_profit_d = 0;
                } elseif ($r == 8) {
                    $c_han_jin_r = 0;
                    $c_han_jin_d = 0;
                } elseif ($r == 9) {
                    $c_han_kei_r = 0;
                    $c_han_kei_d = 0;
                } elseif ($r == 10) {
                    $c_han_all_r = 0;
                    $c_han_all_d = 0;
                } elseif ($r == 11) {
                    $c_ope_profit_r = 0;
                    $c_ope_profit_d = 0;
                } elseif ($r == 12) {
                    $c_gyoumu_r = 0;
                    $c_gyoumu_d = 0;
                } elseif ($r == 13) {
                    $c_swari_r = 0;
                    $c_swari_d = 0;
                } elseif ($r == 14) {
                    $c_pother_r = 0;
                    $c_pother_d = 0;
                } elseif ($r == 15) {
                    $c_nonope_profit_sum_r = 0;
                    $c_nonope_profit_sum_d = 0;
                } elseif ($r == 16) {
                    $c_srisoku_r = 0;
                    $c_srisoku_d = 0;
                } elseif ($r == 17) {
                    $c_lother_r = 0;
                    $c_lother_d = 0;
                } elseif ($r == 18) {
                    $c_nonope_loss_sum_r = 0;
                    $c_nonope_loss_sum_d = 0;
                } elseif ($r == 19) {
                    $c_current_profit_r = 0;
                    $c_current_profit_d = 0;
                }
            } else {
                if ($r == 0) {
                    $c_uri_r = $res_t[0][0];
                    $c_uri_d = $c_uri_r - $c_uri;
                } elseif ($r == 1) {
                    $c_invent_r = $res_t[0][0];
                    $c_invent_d = $c_invent_r - $c_invent;
                } elseif ($r == 2) {
                    $c_metarial_r = $res_t[0][0];
                    $c_metarial_d = $c_metarial_r - $c_metarial;
                } elseif ($r == 3) {
                    $c_roumu_r = $res_t[0][0];
                    $c_roumu_d = $c_roumu_r - $c_roumu;
                } elseif ($r == 4) {
                    $c_expense_r = $res_t[0][0];
                    $c_expense_d = $c_expense_r - $c_expense;
                } elseif ($r == 5) {
                    $c_endinv_r = $res_t[0][0];
                    $c_endinv_d = $c_endinv - $c_endinv_r;
                } elseif ($r == 6) {
                    $c_urigen_r = $res_t[0][0];
                    $c_urigen_d = $c_urigen_r - $c_urigen;
                } elseif ($r == 7) {
                    $c_gross_profit_r = $res_t[0][0];
                    $c_gross_profit_d = $c_gross_profit_r - $c_gross_profit;
                } elseif ($r == 8) {
                    $c_han_jin_r = $res_t[0][0];
                    $c_han_jin_d = $c_han_jin_r - $c_han_jin;
                } elseif ($r == 9) {
                    $c_han_kei_r = $res_t[0][0];
                    $c_han_kei_d = $c_han_kei_r - $c_han_kei;
                } elseif ($r == 10) {
                    $c_han_all_r = $res_t[0][0];
                    $c_han_all_d = $c_han_all_r - $c_han_all;
                } elseif ($r == 11) {
                    $c_ope_profit_r = $res_t[0][0];
                    $c_ope_profit_d = $c_ope_profit_r - $c_ope_profit;
                } elseif ($r == 12) {
                    $c_gyoumu_r = $res_t[0][0];
                    $c_gyoumu_d = $c_gyoumu_r - $c_gyoumu;
                } elseif ($r == 13) {
                    $c_swari_r = $res_t[0][0];
                    $c_swari_d = $c_swari_r - $c_swari;
                } elseif ($r == 14) {
                    $c_pother_r = $res_t[0][0];
                    $c_pother_d = $c_pother_r - $c_pother;
                } elseif ($r == 15) {
                    $c_nonope_profit_sum_r = $res_t[0][0];
                    $c_nonope_profit_sum_d = $c_nonope_profit_sum_r - $c_nonope_profit_sum;
                } elseif ($r == 16) {
                    $c_srisoku_r = $res_t[0][0];
                    $c_srisoku_d = $c_srisoku_r - $c_srisoku;
                } elseif ($r == 17) {
                    $c_lother_r = $res_t[0][0];
                    $c_lother_d = $c_lother_r - $c_lother;
                } elseif ($r == 18) {
                    $c_nonope_loss_sum_r = $res_t[0][0];
                    $c_nonope_loss_sum_d = $c_nonope_loss_sum_r - $c_nonope_loss_sum;
                } elseif ($r == 19) {
                    $c_current_profit_r = $res_t[0][0];
                    $c_current_profit_d = $c_current_profit_r - $c_current_profit;
                }
            }
        }
        // ��˥�
        $item_l = array();
        $item_l[0]  = '��˥�����';
        $item_l[1]  = '��˥���������ų���ê����';
        $item_l[2]  = '��˥�������(������)';
        $item_l[3]  = '��˥�ϫ̳��';
        $item_l[4]  = '��˥���¤����';
        $item_l[5]  = '��˥����������ų���ê����';
        $item_l[6]  = '��˥���帶��';
        $item_l[7]  = '��˥����������';
        $item_l[8]  = '��˥��ͷ���';
        $item_l[9]  = '��˥�����';
        $item_l[10] = '��˥��δ���ڤӰ��̴������';
        $item_l[11] = '��˥��Ķ�����';
        $item_l[12] = '��˥���̳��������';
        $item_l[13] = '��˥��������';
        $item_l[14] = '��˥��Ķȳ����פ���¾';
        $item_l[15] = '��˥��Ķȳ����׷�';
        $item_l[16] = '��˥���ʧ��©';
        $item_l[17] = '��˥��Ķȳ����Ѥ���¾';
        $item_l[18] = '��˥��Ķȳ����ѷ�';
        $item_l[19] = '��˥��о�����';
        $num = count($item_l);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement1($request, $item_l[$r]);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $l_uri = 0;
                } elseif ($r == 1) {
                    $l_invent = 0;
                } elseif ($r == 2) {
                    $l_metarial = 0;
                } elseif ($r == 3) {
                    $l_roumu = 0;
                } elseif ($r == 4) {
                    $l_expense = 0;
                } elseif ($r == 5) {
                    $l_endinv = 0;
                } elseif ($r == 6) {
                    $l_urigen = 0;
                } elseif ($r == 7) {
                    $l_gross_profit = 0;
                } elseif ($r == 8) {
                    $l_han_jin = 0;
                } elseif ($r == 9) {
                    $l_han_kei = 0;
                } elseif ($r == 10) {
                    $l_han_all = 0;
                } elseif ($r == 11) {
                    $l_ope_profit = 0;
                } elseif ($r == 12) {
                    $l_gyoumu = 0;
                } elseif ($r == 13) {
                    $l_swari = 0;
                } elseif ($r == 14) {
                    $l_pother = 0;
                } elseif ($r == 15) {
                    $l_nonope_profit_sum = 0;
                } elseif ($r == 16) {
                    $l_srisoku = 0;
                } elseif ($r == 17) {
                    $l_lother = 0;
                } elseif ($r == 18) {
                    $l_nonope_loss_sum = 0;
                } elseif ($r == 19) {
                    $l_current_profit = 0;
                }
            } else {
                if ($r == 0) {
                    $l_uri = $res_t[0][0];
                } elseif ($r == 1) {
                    $l_invent = $res_t[0][0];
                } elseif ($r == 2) {
                    $l_metarial = $res_t[0][0];
                } elseif ($r == 3) {
                    $l_roumu = $res_t[0][0];
                } elseif ($r == 4) {
                    $l_expense = $res_t[0][0];
                } elseif ($r == 5) {
                    $l_endinv = $res_t[0][0];
                } elseif ($r == 6) {
                    $l_urigen = $res_t[0][0];
                } elseif ($r == 7) {
                    $l_gross_profit = $res_t[0][0];
                } elseif ($r == 8) {
                    $l_han_jin = $res_t[0][0];
                } elseif ($r == 9) {
                    $l_han_kei = $res_t[0][0];
                } elseif ($r == 10) {
                    $l_han_all = $res_t[0][0];
                } elseif ($r == 11) {
                    $l_ope_profit = $res_t[0][0];
                } elseif ($r == 12) {
                    $l_gyoumu = $res_t[0][0];
                } elseif ($r == 13) {
                    $l_swari = $res_t[0][0];
                } elseif ($r == 14) {
                    $l_pother = $res_t[0][0];
                } elseif ($r == 15) {
                    $l_nonope_profit_sum = $res_t[0][0];
                } elseif ($r == 16) {
                    $l_srisoku = $res_t[0][0];
                } elseif ($r == 17) {
                    $l_lother = $res_t[0][0];
                } elseif ($r == 18) {
                    $l_nonope_loss_sum = $res_t[0][0];
                } elseif ($r == 19) {
                    $l_current_profit = $res_t[0][0];
                }
            }
        }
        // »�׼��ӥǡ����μ���
        $target_date = $request->get('targetDateYM');
        if ($target_date < 201607) {
            $item_l[0]  = '��˥�����';
            $item_l[1]  = '��˥���������ų���ê����';
            $item_l[2]  = '��˥�������(������)';
            $item_l[3]  = '��˥�ϫ̳��';
            $item_l[4]  = '��˥���¤����';
            $item_l[5]  = '��˥����������ų���ê����';
            $item_l[6]  = '��˥���帶��';
            $item_l[7]  = '��˥����������';
            $item_l[8]  = '��˥��ͷ���';
            $item_l[9]  = '��˥�����';
            $item_l[10] = '��˥��δ���ڤӰ��̴������';
            $item_l[11] = '��˥��Ķ�����';
            $item_l[12] = '��˥���̳��������';
            $item_l[13] = '��˥��������';
            $item_l[14] = '��˥��Ķȳ����פ���¾';
            $item_l[15] = '��˥��Ķȳ����׷�';
            $item_l[16] = '��˥���ʧ��©';
            $item_l[17] = '��˥��Ķȳ����Ѥ���¾';
            $item_l[18] = '��˥��Ķȳ����ѷ�';
            $item_l[19] = '��˥��о�����';
        } else {
            $item_l[0]  = '��˥�ɸ������';
            $item_l[1]  = '��˥�ɸ���������ų���ê����';
            $item_l[2]  = '��˥�ɸ�������(������)';
            $item_l[3]  = '��˥�ɸ��ϫ̳��';
            $item_l[4]  = '��˥�ɸ����¤����';
            $item_l[5]  = '��˥�ɸ����������ų���ê����';
            $item_l[6]  = '��˥�ɸ����帶��';
            $item_l[7]  = '��˥�ɸ�����������';
            $item_l[8]  = '��˥�ɸ��ͷ���';
            $item_l[9]  = '��˥�ɸ�����';
            $item_l[10] = '��˥�ɸ���δ���ڤӰ��̴������';
            $item_l[11] = '��˥�ɸ��Ķ�����';
            $item_l[12] = '��˥�ɸ���̳��������';
            $item_l[13] = '��˥�ɸ��������';
            $item_l[14] = '��˥�ɸ��Ķȳ����פ���¾';
            $item_l[15] = '��˥�ɸ��Ķȳ����׷�';
            $item_l[16] = '��˥�ɸ���ʧ��©';
            $item_l[17] = '��˥�ɸ��Ķȳ����Ѥ���¾';
            $item_l[18] = '��˥�ɸ��Ķȳ����ѷ�';
            $item_l[19] = '��˥�ɸ��о�����';
        }
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement2($request, $item_l[$r]);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $l_uri_r = 0;
                    $l_uri_d = 0;
                } elseif ($r == 1) {
                    $l_invent_r = 0;
                    $l_invent_d = 0;
                } elseif ($r == 2) {
                    $l_metarial_r = 0;
                    $l_metarial_d = 0;
                } elseif ($r == 3) {
                    $l_roumu_r = 0;
                    $l_roumu_d = 0;
                } elseif ($r == 4) {
                    $l_expense_r = 0;
                    $l_expense_d = 0;
                } elseif ($r == 5) {
                    $l_endinv_r = 0;
                    $l_endinv_d = 0;
                } elseif ($r == 6) {
                    $l_urigen_r = 0;
                    $l_urigen_d = 0;
                } elseif ($r == 7) {
                    $l_gross_profit_r = 0;
                    $l_gross_profit_d = 0;
                } elseif ($r == 8) {
                    $l_han_jin_r = 0;
                    $l_han_jin_d = 0;
                } elseif ($r == 9) {
                    $l_han_kei_r = 0;
                    $l_han_kei_d = 0;
                } elseif ($r == 10) {
                    $l_han_all_r = 0;
                    $l_han_all_d = 0;
                } elseif ($r == 11) {
                    $l_ope_profit_r = 0;
                    $l_ope_profit_d = 0;
                } elseif ($r == 12) {
                    $l_gyoumu_r = 0;
                    $l_gyoumu_d = 0;
                } elseif ($r == 13) {
                    $l_swari_r = 0;
                    $l_swari_d = 0;
                } elseif ($r == 14) {
                    $l_pother_r = 0;
                    $l_pother_d= 0;
                } elseif ($r == 15) {
                    $l_nonope_profit_sum_r = 0;
                    $l_nonope_profit_sum_d = 0;
                } elseif ($r == 16) {
                    $l_srisoku_r = 0;
                    $l_srisoku_d = 0;
                } elseif ($r == 17) {
                    $l_lother_r = 0;
                    $l_lother_d = 0;
                } elseif ($r == 18) {
                    $l_nonope_loss_sum_r = 0;
                    $l_nonope_loss_sum_d = 0;
                } elseif ($r == 19) {
                    $l_current_profit_r = 0;
                    $l_current_profit_d = 0;
                }
            } else {
                if ($r == 0) {
                    $l_uri_r = $res_t[0][0];
                    $l_uri_d = $l_uri_r - $l_uri;
                } elseif ($r == 1) {
                    $l_invent_r = $res_t[0][0];
                    $l_invent_d = $l_invent_r - $l_invent;
                } elseif ($r == 2) {
                    $l_metarial_r = $res_t[0][0];
                    $l_metarial_d = $l_metarial_r - $l_metarial;
                } elseif ($r == 3) {
                    $l_roumu_r = $res_t[0][0];
                    $l_roumu_d = $l_roumu_r - $l_roumu;
                } elseif ($r == 4) {
                    $l_expense_r = $res_t[0][0];
                    $l_expense_d = $l_expense_r - $l_expense;
                } elseif ($r == 5) {
                    $l_endinv_r = $res_t[0][0];
                    $l_endinv_d = $l_endinv - $l_endinv_r;
                } elseif ($r == 6) {
                    $l_urigen_r = $res_t[0][0];
                    $l_urigen_d = $l_urigen_r - $l_urigen;
                } elseif ($r == 7) {
                    $l_gross_profit_r = $res_t[0][0];
                    $l_gross_profit_d = $l_gross_profit_r - $l_gross_profit;
                } elseif ($r == 8) {
                    $l_han_jin_r = $res_t[0][0];
                    $l_han_jin_d = $l_han_jin_r - $l_han_jin;
                } elseif ($r == 9) {
                    $l_han_kei_r = $res_t[0][0];
                    $l_han_kei_d = $l_han_kei_r - $l_han_kei;
                } elseif ($r == 10) {
                    $l_han_all_r = $res_t[0][0];
                    $l_han_all_d = $l_han_all_r - $l_han_all;
                } elseif ($r == 11) {
                    $l_ope_profit_r = $res_t[0][0];
                    $l_ope_profit_d = $l_ope_profit_r - $l_ope_profit;
                } elseif ($r == 12) {
                    $l_gyoumu_r = $res_t[0][0];
                    $l_gyoumu_d = $l_gyoumu_r - $l_gyoumu;
                } elseif ($r == 13) {
                    $l_swari_r = $res_t[0][0];
                    $l_swari_d = $l_swari_r - $l_swari;
                } elseif ($r == 14) {
                    $l_pother_r = $res_t[0][0];
                    $l_pother_d = $l_pother_r - $l_pother;
                } elseif ($r == 15) {
                    $l_nonope_profit_sum_r = $res_t[0][0];
                    $l_nonope_profit_sum_d = $l_nonope_profit_sum_r - $l_nonope_profit_sum;
                } elseif ($r == 16) {
                    $l_srisoku_r = $res_t[0][0];
                    $l_srisoku_d = $l_srisoku_r - $l_srisoku;
                } elseif ($r == 17) {
                    $l_lother_r = $res_t[0][0];
                    $l_lother_d = $l_lother_r - $l_lother;
                } elseif ($r == 18) {
                    $l_nonope_loss_sum_r = $res_t[0][0];
                    $l_nonope_loss_sum_d = $l_nonope_loss_sum_r - $l_nonope_loss_sum;
                } elseif ($r == 19) {
                    $l_current_profit_r = $res_t[0][0];
                    $l_current_profit_d = $l_current_profit_r - $l_current_profit;
                }
            }
        }
        // ���ʴ���
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
            $query = $this->getQueryStatement1($request, $item_b[$r]);
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
                    $b_uri = $res_t[0][0];
                } elseif ($r == 1) {
                    $b_invent = $res_t[0][0];
                } elseif ($r == 2) {
                    $b_metarial = $res_t[0][0];
                } elseif ($r == 3) {
                    $b_roumu = $res_t[0][0];
                } elseif ($r == 4) {
                    $b_expense = $res_t[0][0];
                } elseif ($r == 5) {
                    $b_endinv = $res_t[0][0];
                } elseif ($r == 6) {
                    $b_urigen = $res_t[0][0];
                } elseif ($r == 7) {
                    $b_gross_profit = $res_t[0][0];
                } elseif ($r == 8) {
                    $b_han_jin = $res_t[0][0];
                } elseif ($r == 9) {
                    $b_han_kei = $res_t[0][0];
                } elseif ($r == 10) {
                    $b_han_all = $res_t[0][0];
                } elseif ($r == 11) {
                    $b_ope_profit = $res_t[0][0];
                } elseif ($r == 12) {
                    $b_gyoumu = $res_t[0][0];
                } elseif ($r == 13) {
                    $b_swari = $res_t[0][0];
                } elseif ($r == 14) {
                    $b_pother = $res_t[0][0];
                } elseif ($r == 15) {
                    $b_nonope_profit_sum = $res_t[0][0];
                } elseif ($r == 16) {
                    $b_srisoku = $res_t[0][0];
                } elseif ($r == 17) {
                    $b_lother = $res_t[0][0];
                } elseif ($r == 18) {
                    $b_nonope_loss_sum = $res_t[0][0];
                } elseif ($r == 19) {
                    $b_current_profit = $res_t[0][0];
                }
            }
        }
        // »�׼��ӥǡ����μ���
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement2($request, $item_b[$r]);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $b_uri_r = 0;
                    $b_uri_d = 0;
                } elseif ($r == 1) {
                    $b_invent_r = 0;
                    $b_invent_d = 0;
                } elseif ($r == 2) {
                    $b_metarial_r = 0;
                    $b_metarial_d = 0;
                } elseif ($r == 3) {
                    $b_roumu_r = 0;
                    $b_roumu_d = 0;
                } elseif ($r == 4) {
                    $b_expense_r = 0;
                    $b_expense_d = 0;
                } elseif ($r == 5) {
                    $b_endinv_r = 0;
                    $b_endinv_d = 0;
                } elseif ($r == 6) {
                    $b_urigen_r = 0;
                    $b_urigen_d = 0;
                } elseif ($r == 7) {
                    $b_gross_profit_r = 0;
                    $b_gross_profit_d = 0;
                } elseif ($r == 8) {
                    $b_han_jin_r = 0;
                    $b_han_jin_d = 0;
                } elseif ($r == 9) {
                    $b_han_kei_r = 0;
                    $b_han_kei_d = 0;
                } elseif ($r == 10) {
                    $b_han_all_r = 0;
                    $b_han_all_d = 0;
                } elseif ($r == 11) {
                    $b_ope_profit_r = 0;
                    $b_ope_profit_d = 0;
                } elseif ($r == 12) {
                    $b_gyoumu_r = 0;
                    $b_gyoumu_d = 0;
                } elseif ($r == 13) {
                    $b_swari_r = 0;
                    $b_swari_d = 0;
                } elseif ($r == 14) {
                    $b_pother_r = 0;
                    $b_pother_d = 0;
                } elseif ($r == 15) {
                    $b_nonope_profit_sum_r = 0;
                    $b_nonope_profit_sum_d = 0;
                } elseif ($r == 16) {
                    $b_srisoku_r = 0;
                    $b_srisoku_d = 0;
                } elseif ($r == 17) {
                    $b_lother_r = 0;
                    $b_lother_d = 0;
                } elseif ($r == 18) {
                    $b_nonope_loss_sum_r = 0;
                    $b_nonope_loss_sum_d = 0;
                } elseif ($r == 19) {
                    $b_current_profit_r = 0;
                    $b_current_profit_d = 0;
                }
            } else {
                if ($r == 0) {
                    $b_uri_r = $res_t[0][0];
                    $b_uri_d = $b_uri_r - $b_uri;
                } elseif ($r == 1) {
                    $b_invent_r = $res_t[0][0];
                    $b_invent_d = $b_invent_r - $b_invent;
                } elseif ($r == 2) {
                    $b_metarial_r = $res_t[0][0];
                    $b_metarial_d = $b_metarial_r - $b_metarial;
                } elseif ($r == 3) {
                    $b_roumu_r = $res_t[0][0];
                    $b_roumu_d = $b_roumu_r - $b_roumu;
                } elseif ($r == 4) {
                    $b_expense_r = $res_t[0][0];
                    $b_expense_d = $b_expense_r - $b_expense;
                } elseif ($r == 5) {
                    $b_endinv_r = $res_t[0][0];
                    $b_endinv_d = $b_endinv - $b_endinv_r;
                } elseif ($r == 6) {
                    $b_urigen_r = $res_t[0][0];
                    $b_urigen_d = $b_urigen_r - $b_urigen;
                } elseif ($r == 7) {
                    $b_gross_profit_r = $res_t[0][0];
                    $b_gross_profit_d = $b_gross_profit_r - $b_gross_profit;
                } elseif ($r == 8) {
                    $b_han_jin_r = $res_t[0][0];
                    $b_han_jin_d = $b_han_jin_r - $b_han_jin;
                } elseif ($r == 9) {
                    $b_han_kei_r = $res_t[0][0];
                    $b_han_kei_d = $b_han_kei_r - $b_han_kei;
                } elseif ($r == 10) {
                    $b_han_all_r = $res_t[0][0];
                    $b_han_all_d = $b_han_all_r - $b_han_all;
                } elseif ($r == 11) {
                    $b_ope_profit_r = $res_t[0][0];
                    $b_ope_profit_d = $b_ope_profit_r - $b_ope_profit;
                } elseif ($r == 12) {
                    $b_gyoumu_r = $res_t[0][0];
                    $b_gyoumu_d = $b_gyoumu_r - $b_gyoumu;
                } elseif ($r == 13) {
                    $b_swari_r = $res_t[0][0];
                    $b_swari_d = $b_swari_r - $b_swari;
                } elseif ($r == 14) {
                    $b_pother_r = $res_t[0][0];
                    $b_pother_d = $b_pother_r - $b_pother;
                } elseif ($r == 15) {
                    $b_nonope_profit_sum_r = $res_t[0][0];
                    $b_nonope_profit_sum_d = $b_nonope_profit_sum_r - $b_nonope_profit_sum;
                } elseif ($r == 16) {
                    $b_srisoku_r = $res_t[0][0];
                    $b_srisoku_d = $b_srisoku_r - $b_srisoku;
                } elseif ($r == 17) {
                    $b_lother_r = $res_t[0][0];
                    $b_lother_d = $b_lother_r - $b_lother;
                } elseif ($r == 18) {
                    $b_nonope_loss_sum_r = $res_t[0][0];
                    $b_nonope_loss_sum_d = $b_nonope_loss_sum_r - $b_nonope_loss_sum;
                } elseif ($r == 19) {
                    $b_current_profit_r = $res_t[0][0];
                    $b_current_profit_d = $b_current_profit_r - $b_current_profit;
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
            $query = $this->getQueryStatement1($request, $item_s[$r]);
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
                    $s_uri = $res_t[0][0];
                } elseif ($r == 1) {
                    $s_invent = $res_t[0][0];
                } elseif ($r == 2) {
                    $s_metarial = $res_t[0][0];
                } elseif ($r == 3) {
                    $s_roumu = $res_t[0][0];
                } elseif ($r == 4) {
                    $s_expense = $res_t[0][0];
                } elseif ($r == 5) {
                    $s_endinv = $res_t[0][0];
                } elseif ($r == 6) {
                    $s_urigen = $res_t[0][0];
                } elseif ($r == 7) {
                    $s_gross_profit = $res_t[0][0];
                } elseif ($r == 8) {
                    $s_han_jin = $res_t[0][0];
                } elseif ($r == 9) {
                    $s_han_kei = $res_t[0][0];
                } elseif ($r == 10) {
                    $s_han_all = $res_t[0][0];
                } elseif ($r == 11) {
                    $s_ope_profit = $res_t[0][0];
                } elseif ($r == 12) {
                    $s_gyoumu = $res_t[0][0];
                } elseif ($r == 13) {
                    $s_swari = $res_t[0][0];
                } elseif ($r == 14) {
                    $s_pother = $res_t[0][0];
                } elseif ($r == 15) {
                    $s_nonope_profit_sum = $res_t[0][0];
                } elseif ($r == 16) {
                    $s_srisoku = $res_t[0][0];
                } elseif ($r == 17) {
                    $s_lother = $res_t[0][0];
                } elseif ($r == 18) {
                    $s_nonope_loss_sum = $res_t[0][0];
                } elseif ($r == 19) {
                    $s_current_profit = $res_t[0][0];
                }
            }
        }
        // »�׼��ӥǡ����μ���
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement2($request, $item_s[$r]);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $s_uri_r = 0;
                    $s_uri_d = 0;
                } elseif ($r == 1) {
                    $s_invent_r = 0;
                    $s_invent_d = 0;
                } elseif ($r == 2) {
                    $s_metarial_r = 0;
                    $s_metarial_d = 0;
                } elseif ($r == 3) {
                    $s_roumu_r = 0;
                    $s_roumu_d = 0;
                } elseif ($r == 4) {
                    $s_expense_r = 0;
                    $s_expense_d = 0;
                } elseif ($r == 5) {
                    $s_endinv_r = 0;
                    $s_endinv_d = 0;
                } elseif ($r == 6) {
                    $s_urigen_r = 0;
                    $s_urigen_d = 0;
                } elseif ($r == 7) {
                    $s_gross_profit_r = 0;
                    $s_gross_profit_d = 0;
                } elseif ($r == 8) {
                    $s_han_jin_r = 0;
                    $s_han_jin_d = 0;
                } elseif ($r == 9) {
                    $s_han_kei_r = 0;
                    $s_han_kei_d = 0;
                } elseif ($r == 10) {
                    $s_han_all_r = 0;
                    $s_han_all_d = 0;
                } elseif ($r == 11) {
                    $s_ope_profit_r = 0;
                    $s_ope_profit_d = 0;
                } elseif ($r == 12) {
                    $s_gyoumu_r = 0;
                    $s_gyoumu_d = 0;
                } elseif ($r == 13) {
                    $s_swari_r = 0;
                    $s_swari_d = 0;
                } elseif ($r == 14) {
                    $s_pother_r = 0;
                    $s_pother_d = 0;
                } elseif ($r == 15) {
                    $s_nonope_profit_sum_r = 0;
                    $s_nonope_profit_sum_d = 0;
                } elseif ($r == 16) {
                    $s_srisoku_r = 0;
                    $s_srisoku_d = 0;
                } elseif ($r == 17) {
                    $s_lother_r = 0;
                    $s_lother_d = 0;
                } elseif ($r == 18) {
                    $s_nonope_loss_sum_r = 0;
                    $s_nonope_loss_sum_d = 0;
                } elseif ($r == 19) {
                    $s_current_profit_r = 0;
                    $s_current_profit_d = 0;
                }
            } else {
                if ($r == 0) {
                    $s_uri_r = $res_t[0][0];
                    $s_uri_d = $s_uri_r - $s_uri;
                } elseif ($r == 1) {
                    $s_invent_r = $res_t[0][0];
                    $s_invent_d = $s_invent_r - $s_invent;
                } elseif ($r == 2) {
                    $s_metarial_r = $res_t[0][0];
                    $s_metarial_d = $s_metarial_r - $s_metarial;
                } elseif ($r == 3) {
                    $s_roumu_r = $res_t[0][0];
                    $s_roumu_d = $s_roumu_r - $s_roumu;
                } elseif ($r == 4) {
                    $s_expense_r = $res_t[0][0];
                    $s_expense_d = $s_expense_r - $s_expense;
                } elseif ($r == 5) {
                    $s_endinv_r = $res_t[0][0];
                    $s_endinv_d = $s_endinv - $s_endinv_r;
                } elseif ($r == 6) {
                    $s_urigen_r = $res_t[0][0];
                    $s_urigen_d = $s_urigen_r - $s_urigen;
                } elseif ($r == 7) {
                    $s_gross_profit_r = $res_t[0][0];
                    $s_gross_profit_d = $s_gross_profit_r - $s_gross_profit;
                } elseif ($r == 8) {
                    $s_han_jin_r = $res_t[0][0];
                    $s_han_jin_d = $s_han_jin_r - $s_han_jin;
                } elseif ($r == 9) {
                    $s_han_kei_r = $res_t[0][0];
                    $s_han_kei_d = $s_han_kei_r - $s_han_kei;
                } elseif ($r == 10) {
                    $s_han_all_r = $res_t[0][0];
                    $s_han_all_d = $s_han_all_r - $s_han_all;
                } elseif ($r == 11) {
                    $s_ope_profit_r = $res_t[0][0];
                    $s_ope_profit_d = $s_ope_profit_r - $s_ope_profit;
                } elseif ($r == 12) {
                    $s_gyoumu_r = $res_t[0][0];
                    $s_gyoumu_d = $s_gyoumu_r - $s_gyoumu;
                } elseif ($r == 13) {
                    $s_swari_r = $res_t[0][0];
                    $s_swari_d = $s_swari_r - $s_swari;
                } elseif ($r == 14) {
                    $s_pother_r = $res_t[0][0];
                    $s_pother_d = $s_pother_r - $s_pother;
                } elseif ($r == 15) {
                    $s_nonope_profit_sum_r = $res_t[0][0];
                    $s_nonope_profit_sum_d = $s_nonope_profit_sum_r - $s_nonope_profit_sum;
                } elseif ($r == 16) {
                    $s_srisoku_r = $res_t[0][0];
                    $s_srisoku_d = $s_srisoku_r - $s_srisoku;
                } elseif ($r == 17) {
                    $s_lother_r = $res_t[0][0];
                    $s_lother_d = $s_lother_r - $s_lother;
                } elseif ($r == 18) {
                    $s_nonope_loss_sum_r = $res_t[0][0];
                    $s_nonope_loss_sum_d = $s_nonope_loss_sum_r - $s_nonope_loss_sum;
                } elseif ($r == 19) {
                    $s_current_profit_r = $res_t[0][0];
                    $s_current_profit_d = $s_current_profit_r - $s_current_profit;
                }
            }
        }
        // ����
        $item_a = array();
        $item_a[0]  = '��������';
        $item_a[1]  = '���δ�������ų���ê����';
        $item_a[2]  = '���κ�����(������)';
        $item_a[3]  = '����ϫ̳��';
        $item_a[4]  = '������¤����';
        $item_a[5]  = '���δ��������ų���ê����';
        $item_a[6]  = '������帶��';
        $item_a[7]  = '�������������';
        $item_a[8]  = '���οͷ���';
        $item_a[9]  = '���η���';
        $item_a[10] = '�����δ���ڤӰ��̴������';
        $item_a[11] = '���αĶ�����';
        $item_a[12] = '���ζ�̳��������';
        $item_a[13] = '���λ������';
        $item_a[14] = '���αĶȳ����פ���¾';
        $item_a[15] = '���αĶȳ����׷�';
        $item_a[16] = '���λ�ʧ��©';
        $item_a[17] = '���αĶȳ����Ѥ���¾';
        $item_a[18] = '���αĶȳ����ѷ�';
        $item_a[19] = '���ηо�����';
        $num = count($item_a);
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement1($request, $item_a[$r]);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $all_uri = 0;
                } elseif ($r == 1) {
                    $all_invent = 0;
                } elseif ($r == 2) {
                    $all_metarial = 0;
                } elseif ($r == 3) {
                    $all_roumu = 0;
                } elseif ($r == 4) {
                    $all_expense = 0;
                } elseif ($r == 5) {
                    $all_endinv = 0;
                } elseif ($r == 6) {
                    $all_urigen = 0;
                } elseif ($r == 7) {
                    $all_gross_profit = 0;
                } elseif ($r == 8) {
                    $all_han_jin = 0;
                } elseif ($r == 9) {
                    $all_han_kei = 0;
                } elseif ($r == 10) {
                    $all_han_all = 0;
                } elseif ($r == 11) {
                    $all_ope_profit = 0;
                } elseif ($r == 12) {
                    $all_gyoumu = 0;
                } elseif ($r == 13) {
                    $all_swari = 0;
                } elseif ($r == 14) {
                    $all_pother = 0;
                } elseif ($r == 15) {
                    $all_nonope_profit_sum = 0;
                } elseif ($r == 16) {
                    $all_srisoku = 0;
                } elseif ($r == 17) {
                    $all_lother = 0;
                } elseif ($r == 18) {
                    $all_nonope_loss_sum = 0;
                } elseif ($r == 19) {
                    $all_current_profit = 0;
                }
            } else {
                if ($r == 0) {
                    $all_uri = $res_t[0][0];
                } elseif ($r == 1) {
                    $all_invent = $res_t[0][0];
                } elseif ($r == 2) {
                    $all_metarial = $res_t[0][0];
                } elseif ($r == 3) {
                    $all_roumu = $res_t[0][0];
                } elseif ($r == 4) {
                    $all_expense = $res_t[0][0];
                } elseif ($r == 5) {
                    $all_endinv = $res_t[0][0];
                } elseif ($r == 6) {
                    $all_urigen = $res_t[0][0];
                } elseif ($r == 7) {
                    $all_gross_profit = $res_t[0][0];
                } elseif ($r == 8) {
                    $all_han_jin = $res_t[0][0];
                } elseif ($r == 9) {
                    $all_han_kei = $res_t[0][0];
                } elseif ($r == 10) {
                    $all_han_all = $res_t[0][0];
                } elseif ($r == 11) {
                    $all_ope_profit = $res_t[0][0];
                } elseif ($r == 12) {
                    $all_gyoumu = $res_t[0][0];
                } elseif ($r == 13) {
                    $all_swari = $res_t[0][0];
                } elseif ($r == 14) {
                    $all_pother = $res_t[0][0];
                } elseif ($r == 15) {
                    $all_nonope_profit_sum = $res_t[0][0];
                } elseif ($r == 16) {
                    $all_srisoku = $res_t[0][0];
                } elseif ($r == 17) {
                    $all_lother = $res_t[0][0];
                } elseif ($r == 18) {
                    $all_nonope_loss_sum = $res_t[0][0];
                } elseif ($r == 19) {
                    $all_current_profit = $res_t[0][0];
                }
            }
        }
        // »�׼��ӥǡ����μ���
        for ($r=0; $r<$num; $r++) {
            $res_t   = array();
            $field_t = array();
            $query = $this->getQueryStatement2($request, $item_a[$r]);
            if (($rows_t = $this->getResultWithField3($query, $field_t, $res_t)) <= 0) {
                if ($r == 0) {
                    $all_uri_r = 0;
                    $all_uri_d = 0;
                } elseif ($r == 1) {
                    $all_invent_r = 0;
                    $all_invent_d = 0;
                } elseif ($r == 2) {
                    $all_metarial_r = 0;
                    $all_metarial_d = 0;
                } elseif ($r == 3) {
                    $all_roumu_r = 0;
                    $all_roumu_d = 0;
                } elseif ($r == 4) {
                    $all_expense_r = 0;
                    $all_expense_d = 0;
                } elseif ($r == 5) {
                    $all_endinv_r = 0;
                    $all_endinv_d = 0;
                } elseif ($r == 6) {
                    $all_urigen_r = 0;
                    $all_urigen_d = 0;
                } elseif ($r == 7) {
                    $all_gross_profit_r = 0;
                    $all_gross_profit_d = 0;
                } elseif ($r == 8) {
                    $all_han_jin_r = 0;
                    $all_han_jin_d = 0;
                } elseif ($r == 9) {
                    $all_han_kei_r = 0;
                    $all_han_kei_d = 0;
                } elseif ($r == 10) {
                    $all_han_all_r = 0;
                    $all_han_all_d = 0;
                } elseif ($r == 11) {
                    $all_ope_profit_r = 0;
                    $all_ope_profit_d = 0;
                } elseif ($r == 12) {
                    $all_gyoumu_r = 0;
                    $all_gyoumu_d = 0;
                } elseif ($r == 13) {
                    $all_swari_r = 0;
                    $all_swari_d = 0;
                } elseif ($r == 14) {
                    $all_pother_r = 0;
                    $all_pother_d = 0;
                } elseif ($r == 15) {
                    $all_nonope_profit_sum_r = 0;
                    $all_nonope_profit_sum_d = 0;
                } elseif ($r == 16) {
                    $all_srisoku_r = 0;
                    $all_srisoku_d = 0;
                } elseif ($r == 17) {
                    $all_lother_r = 0;
                    $all_lother_d = 0;
                } elseif ($r == 18) {
                    $all_nonope_loss_sum_r = 0;
                    $all_nonope_loss_sum_d = 0;
                } elseif ($r == 19) {
                    $all_current_profit_r = 0;
                    $all_current_profit_d = 0;
                }
            } else {
                if ($r == 0) {
                    $all_uri_r = $res_t[0][0];
                    $all_uri_d = $all_uri - $all_uri_r;
                } elseif ($r == 1) {
                    $all_invent_r = $res_t[0][0];
                    $all_invent_d = $all_invent - $all_invent_r;
                } elseif ($r == 2) {
                    $all_metarial_r = $res_t[0][0];
                    $all_metarial_d = $all_metarial - $all_metarial_r;
                } elseif ($r == 3) {
                    $all_roumu_r = $res_t[0][0];
                    $all_roumu_d = $all_roumu - $all_roumu_r;
                } elseif ($r == 4) {
                    $all_expense_r = $res_t[0][0];
                    $all_expense_d = $all_expense - $all_expense_r;
                } elseif ($r == 5) {
                    $all_endinv_r = $res_t[0][0];
                    $all_endinv_d = $all_endinv_r - $all_endinv;
                } elseif ($r == 6) {
                    $all_urigen_r = $res_t[0][0];
                    $all_urigen_d = $all_urigen - $all_urigen_r;
                } elseif ($r == 7) {
                    $all_gross_profit_r = $res_t[0][0];
                    $all_gross_profit_d = $all_gross_profit - $all_gross_profit_r;
                } elseif ($r == 8) {
                    $all_han_jin_r = $res_t[0][0];
                    $all_han_jin_d = $all_han_jin - $all_han_jin_r;
                } elseif ($r == 9) {
                    $all_han_kei_r = $res_t[0][0];
                    $all_han_kei_d = $all_han_kei - $all_han_kei_r;
                } elseif ($r == 10) {
                    $all_han_all_r = $res_t[0][0];
                    $all_han_all_d = $all_han_all - $all_han_all_r;
                } elseif ($r == 11) {
                    $all_ope_profit_r = $res_t[0][0];
                    $all_ope_profit_d = $all_ope_profit - $all_ope_profit_r;
                } elseif ($r == 12) {
                    $all_gyoumu_r = $res_t[0][0];
                    $all_gyoumu_d = $all_gyoumu - $all_gyoumu_r;
                } elseif ($r == 13) {
                    $all_swari_r = $res_t[0][0];
                    $all_swari_d = $all_swari - $all_swari_r;
                } elseif ($r == 14) {
                    $all_pother_r = $res_t[0][0];
                    $all_pother_d = $all_pother - $all_pother_r;
                } elseif ($r == 15) {
                    $all_nonope_profit_sum_r = $res_t[0][0];
                    $all_nonope_profit_sum_d = $all_nonope_profit_sum - $all_nonope_profit_sum_r;
                } elseif ($r == 16) {
                    $all_srisoku_r = $res_t[0][0];
                    $all_srisoku_d = $all_srisoku - $all_srisoku_r;
                } elseif ($r == 17) {
                    $all_lother_r = $res_t[0][0];
                    $all_lother_d = $all_lother - $all_lother_r;
                } elseif ($r == 18) {
                    $all_nonope_loss_sum_r = $res_t[0][0];
                    $all_nonope_loss_sum_d = $all_nonope_loss_sum - $all_nonope_loss_sum_r;
                } elseif ($r == 19) {
                    $all_current_profit_r = $res_t[0][0];
                    $all_current_profit_d = $all_current_profit - $all_current_profit_r;
                }
            }
        }
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
            $yyyy   = substr($request->get('targetDateYMD'),0,4);
            $mm     = substr($request->get('targetDateYMD'),4,2);
            $dd     = substr($request->get('targetDateYMD'),6,2);
            if (!$request->get('keihi_tani')) {
                $tani = 1000;           // ����� ɽ��ñ�� ���
            } else {
                $tani = $request->get('keihi_tani');
            }
            if (!$request->get('keihi_keta')) {
                $keta = 0;              // ����� �������ʲ����
            } else {
                $keta = $request->get('keihi_keta');
            }
            $listTable .= "<tr>\n";
            $listTable .= "        <td colspan='3' rowspan='2' width='200' align='center' class='pt11b' bgcolor='#ffffc6' nowrap>" . $yyyy . "ǯ" . $mm . "��ͽ¬<BR>��" . $yyyy . "ǯ" . $mm . "��" . $dd . "��������</td>\n";
            $listTable .= "        <td align='center' colspan='3' class='pt10b' bgcolor='#ffffc6' nowrap>�����ס���</td>\n";
            $listTable .= "        <td align='center' colspan='3' class='pt10b' bgcolor='#ffffc6' nowrap>�ꡡ�ˡ���</td>\n";
            $listTable .= "        <td align='center' colspan='3' class='pt10b' bgcolor='#ffffc6' nowrap>�������</td>\n";
            $listTable .= "        <td align='center' colspan='3' class='pt10b' bgcolor='#ffffc6' nowrap>���ʴ���</td>\n";
            $listTable .= "        <td align='center' colspan='3' class='pt10b' bgcolor='#ffffc6' nowrap>�硡������</td>\n";
            $listTable .= "        <td rowspan='2' width='400' align='left' class='pt10b' bgcolor='#ffffc6'>�׻���ˡ(���ɡ���ϲ�ǯ�֤�ʿ��)</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "<tr>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>ͽ��¬</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>�¡���</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>������</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>ͽ��¬</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>�¡���</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>������</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>ͽ��¬</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>�¡���</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>������</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>ͽ��¬</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>�¡���</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>������</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>ͽ��¬</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>�¡���</td>\n";
            $listTable .= "        <td align='center' class='pt10b' bgcolor='#ffffc6' nowrap>������</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>�ġ��ȡ�»����</td>\n";
            $listTable .= "        <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�䡡�塡��</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_uri / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_uri_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_uri_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_uri / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_uri_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_uri_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_uri / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_uri_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_uri_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_uri / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_uri_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_uri_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($all_uri / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($all_uri_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($all_uri_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>��������������Ω�����ײ�</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>��帶��</td> <!-- ��帶�� -->\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>����������ų���ê����</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_invent / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_invent_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_invent_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_invent / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_invent_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_invent_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_invent / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_invent_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_invent_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_invent / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_invent_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_invent_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_invent / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_invent_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_invent_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>���������������º�ê����</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>��������(������)</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_metarial / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_metarial_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_metarial_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_metarial / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_metarial_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_metarial_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_metarial / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_metarial_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_metarial_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_metarial / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_metarial_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_metarial_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_metarial / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_metarial_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_metarial_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>������Ǽ��ͽ����(���)</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>��ϫ����̳������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_roumu / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_roumu_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_roumu_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_roumu / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_roumu_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_roumu_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_roumu / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_roumu_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_roumu_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_roumu / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_roumu_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_roumu_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_roumu / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_roumu_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_roumu_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>����ľ�ᣱǯ�֤�������</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���С�����������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_expense / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_expense_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_expense_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_expense / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_expense_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_expense_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_expense / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_expense_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_expense_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_expense / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_expense_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_expense_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_expense / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_expense_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_expense_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>����ľ�ᣱǯ�֤�������</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>�����������ų���ê����</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_endinv / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_endinv_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_endinv_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_endinv / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_endinv_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_endinv_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_endinv / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_endinv_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_endinv_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_endinv / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_endinv_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_endinv_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_endinv / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_endinv_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_endinv_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>�����ǿ����������׻�</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���䡡�塡������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($c_urigen / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($c_urigen_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($c_urigen_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($l_urigen / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($l_urigen_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($l_urigen_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($s_urigen / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($s_urigen_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($s_urigen_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($b_urigen / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($b_urigen_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($b_urigen_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($all_urigen / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($all_urigen_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($all_urigen_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�䡡�塡��������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_gross_profit / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_gross_profit_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_gross_profit_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_gross_profit / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_gross_profit_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_gross_profit_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_gross_profit / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_gross_profit_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_gross_profit_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_gross_profit / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_gross_profit_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_gross_profit_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($all_gross_profit / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($all_gross_profit_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($all_gross_profit_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>��</td>  <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- �δ��� -->\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���͡��������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_han_jin / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_han_jin_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_han_jin_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_han_jin / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_han_jin_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_han_jin_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_han_jin / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_han_jin_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_han_jin_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_han_jin / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_han_jin_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_han_jin_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_han_jin / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_han_jin_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_han_jin_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>����ľ�ᣱǯ�֤�������</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>���С�����������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_han_kei / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_han_kei_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_han_kei_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_han_kei / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_han_kei_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_han_kei_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_han_kei / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_han_kei_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_han_kei_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_han_kei / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_han_kei_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_han_kei_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_han_kei / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_han_kei_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_han_kei_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>����ľ�ᣱǯ�֤�������</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�δ���ڤӰ��̴������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($c_han_all / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($c_han_all_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($c_han_all_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($l_han_all / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($l_han_all_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($l_han_all_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($s_han_all / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($s_han_all_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($s_han_all_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($b_han_all / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($b_han_all_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($b_han_all_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($all_han_all / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($all_han_all_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($all_han_all_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�ġ����ȡ�����������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_ope_profit / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_ope_profit_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_ope_profit_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_ope_profit / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_ope_profit_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_ope_profit_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_ope_profit / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_ope_profit_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_ope_profit_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_ope_profit / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_ope_profit_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_ope_profit_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($all_ope_profit / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($all_ope_profit_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($all_ope_profit_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>��</td>  <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>�Ķȳ�»��</td>\n";
            $listTable .= "        <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- ;�� -->\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>����̳��������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_gyoumu / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_gyoumu_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_gyoumu_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_gyoumu / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_gyoumu_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_gyoumu_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_gyoumu / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_gyoumu_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_gyoumu_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_gyoumu / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_gyoumu_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_gyoumu_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_gyoumu / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_gyoumu_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_gyoumu_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>����ľ�ᣱǯ�֤�������</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���š������䡡��</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_swari / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_swari_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_swari_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_swari / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_swari_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_swari_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_swari / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_swari_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_swari_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_swari / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_swari_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_swari_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_swari / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_swari_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_swari_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>����ľ�ᣱǯ�֤�������</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>���������Ρ���¾</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_pother / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_pother_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_pother_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_pother / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_pother_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_pother_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_pother / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_pother_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_pother_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_pother / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_pother_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_pother_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_pother / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_pother_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_pother_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>����ľ�ᣱǯ�֤�������</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���Ķȳ����� ��</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($c_nonope_profit_sum / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($c_nonope_profit_sum_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($c_nonope_profit_sum_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($l_nonope_profit_sum / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($l_nonope_profit_sum_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($l_nonope_profit_sum_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($s_nonope_profit_sum / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($s_nonope_profit_sum_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($s_nonope_profit_sum_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($b_nonope_profit_sum / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($b_nonope_profit_sum_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($b_nonope_profit_sum_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($all_nonope_profit_sum / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($all_nonope_profit_sum_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($all_nonope_profit_sum_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>��</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- ;�� -->\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>���١�ʧ������©</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_srisoku / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_srisoku_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($c_srisoku_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_srisoku / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_srisoku_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($l_srisoku_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_srisoku / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_srisoku_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($s_srisoku_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_srisoku / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_srisoku_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($b_srisoku_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_srisoku / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_srisoku_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>" . number_format(($all_srisoku_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>����ľ�ᣱǯ�֤�������</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='white'>���������Ρ���¾</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_lother / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_lother_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($c_lother_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_lother / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_lother_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($l_lother_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_lother / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_lother_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($s_lother_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_lother / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_lother_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($b_lother_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_lother / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_lother_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='white'>" . number_format(($all_lother_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>����ľ�ᣱǯ�֤�������</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>���Ķȳ����� ��</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($c_nonope_loss_sum / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($c_nonope_loss_sum_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($c_nonope_loss_sum_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($l_nonope_loss_sum / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($l_nonope_loss_sum_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($l_nonope_loss_sum_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($s_nonope_loss_sum / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($s_nonope_loss_sum_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($s_nonope_loss_sum_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($b_nonope_loss_sum / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($b_nonope_loss_sum_r / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($b_nonope_loss_sum_d / $tani), $keta) .   "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($all_nonope_loss_sum / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($all_nonope_loss_sum_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10' bgcolor='#ffffc6'>" . number_format(($all_nonope_loss_sum_d / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='left'  class='pt10'>��</td> <!-- ;�� -->\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>�С��������������</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_current_profit / $tani), $keta) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_current_profit_r / $tani), $keta) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($c_current_profit_d / $tani), $keta) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_current_profit / $tani), $keta) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_current_profit_r / $tani), $keta) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($l_current_profit_d / $tani), $keta) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_current_profit / $tani), $keta) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_current_profit_r / $tani), $keta) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($s_current_profit_d / $tani), $keta) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_current_profit / $tani), $keta) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_current_profit_r / $tani), $keta) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($b_current_profit_d / $tani), $keta) .    "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($all_current_profit / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($all_current_profit_r / $tani), $keta) . "</td>\n";
            $listTable .= "        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'>" . number_format(($all_current_profit_d / $tani), $keta) . "</td>\n";
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
    // »��ͽ¬�ǡ����μ���(CL����)
    private function getQueryStatement1($request, $item)
    {
        $target_date = $request->get('targetDateYM');
        $create_date = $request->get('targetDateYMD');
        $query = "SELECT 
                            kin 
                    FROM    act_pl_estimate 
                    WHERE   target_ym={$target_date} AND cal_ymd={$create_date} AND note='{$item}'
        ";
        return $query;
    }
    // »�׼��ӥǡ����μ���(CL����)
    private function getQueryStatement2($request, $item)
    {
        $target_date = $request->get('targetDateYM');
        //$target_date = 201106;
        $query = "SELECT 
                            kin 
                    FROM    profit_loss_pl_history
                    WHERE   pl_bs_ym={$target_date} AND note='{$item}'
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
<style media=print>
<!--
/*�֥饦���Τ�ɽ��*/
.dspOnly {
    display:none;
}
.footer {
    display:none;
}
// -->
</style>
<script type='text/javascript' src='../profit_loss_estimate_view.js'></script>
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
