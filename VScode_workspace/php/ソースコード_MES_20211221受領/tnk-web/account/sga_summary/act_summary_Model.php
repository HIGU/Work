<?php
//////////////////////////////////////////////////////////////////////////////
// ������ ��¤����ڤ��δ���ξȲ�                           MVC Model ��   //
// Copyright (C) 2007-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/10/13 Created   act_summary_Model.php                               //
// 2007/10/16 ����ʿ�ѤȺ���ʿ�ѤΣ����ܤ��ɲ�                              //
// 2007/11/08 �᥽�å�getLaborAuth()���ɲä���ϫ̳������پȲ��٥��ʬ����//
// 2008/05/14 ��˼��Ĺ����ˤ����ܤ��оݷ��¸�ߤ���ʪ����ɽ������ʤ��ä�//
//            �Τ��оݴ�or������¸�ߤ���ʪ������ɽ������褦���ѹ�     ��ë //
// 2008/05/21 Ⱦ���߷�ɽ�����ɲ�                                       ��ë //
// 2008/06/11 �����ɽ��������ѹ�(500�������ʤɤ�ɽ������褦��)      ��ë //
// 2008/09/12 ���ҹ�פ���¤����ξȲ���ɲ�                           ��ë //
// 2009/12/10 NK�����Ĺ����ˤ�꾮��ë��Ĺ��ϫ̳��������򸫤��褦      //
//            �ѹ�                                                     ��ë //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class ActSummary_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $order;                             // ���� SQL��ORDER��
    private $offset;                            // ���� SQL��OFFSET��
    private $limit;                             // ���� SQL��LIMIT��
    private $total_expense;                     // ���� ����ξ���
    private $total_laborCost;                   // ���� ϫ̳��ξ���
    private $total_cost;                        // ���� ���
    private $sum_expense;                       // �߷� ����ξ���
    private $sum_laborCost;                     // �߷� ϫ̳��ξ���
    private $sum_cost;                          // �߷� ���
    private $pre_expense;                       // ����ʿ�� ����ξ���
    private $pre_laborCost;                     // ����ʿ�� ϫ̳��ξ���
    private $pre_cost;                          // ����ʿ�� ���
    private $now_expense;                       // ����ʿ�� ����ξ���
    private $now_laborCost;                     // ����ʿ�� ϫ̳��ξ���
    private $now_cost;                          // ����ʿ�� ���
    private $hulf_expense;                      // Ⱦ���߷� ����ξ���
    private $hulf_laborCost;                    // Ⱦ���߷� ϫ̳��ξ���
    private $hulf_cost;                         // Ⱦ���߷� ���
    
    ///// public properties
    // public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct()
    {
        ///// Properties �ν����
        $this->where  = '';
        $this->order  = '';
        $this->offset = '';
        $this->limit  = '';
        $this->total_expense    = 0;
        $this->total_laborCost  = 0;
        $this->total_cost       = 0;
        $this->sum_expense      = 0;
        $this->sum_laborCost    = 0;
        $this->sum_cost         = 0;
        $this->pre_expense      = 0;
        $this->pre_laborCost    = 0;
        $this->pre_cost         = 0;
        $this->now_expense      = 0;
        $this->now_laborCost    = 0;
        $this->now_cost         = 0;
        $this->hulf_expense     = 0;
        $this->hulf_laborCost   = 0;
        $this->hulf_cost        = 0;
    }
    
    ///// SQL��WHERE�������
    public function setWhere($session)
    {
        $this->where = $this->setWhereBody($session);
    }
    
    ///// �о�ǯ���HTML <select> option �ν���
    public function getTargetDateYMvalues($session)
    {
        $str_ym = date('Ym') - 10000;   // ��ǯ������
        $query = "
            SELECT act_yymm + 200000
            FROM act_summary
            WHERE (act_yymm + 200000) >= {$str_ym}
            GROUP BY act_yymm ORDER BY act_yymm DESC
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        // �����
        $option = "\n";
        for ($i=0; $i<$rows; $i++) {
            $yyyy = substr($res[$i][0], 0, 4);
            $mm   = substr($res[$i][0], 4, 2);
            if ($session->get_local('targetDateYM') == $res[$i][0]) {
                $option .= "<option value='{$res[$i][0]}' selected>{$yyyy}ǯ{$mm}��</option>\n";
            } else {
                $option .= "<option value='{$res[$i][0]}'>{$yyyy}ǯ{$mm}��</option>\n";
            }
        }
        return $option;
    }
    
    ///// �о����祳���ɤ�HTML <select> option �ν���
    public function getTargetAct_idValues($session)
    {
        $query = "
            SELECT
                act_id      AS ���祳����
                ,
                act_name    AS ����̾
            FROM act_table
            WHERE act_id < 600 AND act_id NOT IN (395)
            ORDER BY act_name ASC
        ";
        $res = array();
        $rows = $this->getResult2($query, $res);
        // �����
        $option = "\n";
        $all_rows = $rows + 1;
        // ���ξȲ��� ��������
        array_unshift($res, array('000', '���ҹ�פ���¤����'));
        for ($i=0; $i<$rows+1; $i++) {
        // �����ޤ�
        //for ($i=0; $i<$rows; $i++) {
            if ($session->get_local('targetAct_id') == $res[$i][0]) {
                if ($res[$i][0] == '000') {
                    $option .= "<option value='{$res[$i][0]}' style='color:blue;' selected>{$res[$i][1]}</option>\n";
                } else {
                    $option .= "<option value='{$res[$i][0]}' selected>{$res[$i][1]}</option>\n";
                }
            } else {
                if ($res[$i][0] == '000') {
                    $option .= "<option value='{$res[$i][0]}' style='color:blue;'>{$res[$i][1]}</option>\n";
                } else {
                    $option .= "<option value='{$res[$i][0]}'>{$res[$i][1]}</option>\n";
                }
            }
        }
        return $option;
    }
    
    ///// Windowɽ���λ��Υ����ȥ�ν���
    public function getTitleDateValues($session)
    {
        $yyyymm = substr($session->get_local('targetDateYM'), 0, 4) . 'ǯ' . substr($session->get_local('targetDateYM'), 4, 2) . '��';
        $query = "
            SELECT
                act_name    AS ����̾
            FROM act_table
            WHERE act_id = {$session->get_local('targetAct_id')}
        ";
        $this->getUniResult($query, $name);
        return $yyyymm . '��' . $name;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    �ǡ��������� ����ɽ
    public function outViewListHTML($session, $menu)
    {
                /***** �إå���������� *****/
        /*****************
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $headHTML .= $this->getViewHTMLheader();
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/act_summary_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        *****************/
        
                /***** ��ʸ����� *****/
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLbody($session, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/act_summary_ViewList-{$_SESSION['User_ID']}.html";
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
        $file_name = "list/act_summary_ViewListFooter-{$_SESSION['User_ID']}.html";
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
        $query = "SELECT comment FROM act_summary_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
        if ($this->getUniResult($query, $comment) < 1) {
            $sql = "
                INSERT INTO act_summary_comment (assy_no, plan_no, comment, last_date, last_host)
                VALUES ('{$request->get('targetAssyNo')}', '{$request->get('targetPlanNo')}', '{$request->get('comment')}', '{$last_date}', '{$last_host}')
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�����Ȥ���¸������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            }
        } else {
            $sql = "
                UPDATE act_summary_comment SET comment='{$request->get('comment')}',
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
            act_summary_comment ON(mipn=assy_no)
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
    ////////// �ꥯ�����Ȥˤ��SQLʸ��WHERE�������
    protected function setWhereBody($session)
    {
        return "WHERE act_yymm = {$session->get_local('targetDateYM')} AND act_id = {$session->get_local('targetAct_id')}";
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��   ����ɽ��SQL���ơ��ȥ��ȼ���
    // �������祳���ɤǻ���ǯ��δ��Ȥ�������ʬ��ȯ���������ܤΰ�������� SQL
    private function getQueryActCode($session)
    {
        // ���������ǯ�����������(�㡧0604)
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $yyyy -= 1;
            $mm = '04';
        } else {
            $yyyy -= 2;
            $mm = '04';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                actcod              AS ����
                ,
                to_char(aucod, 'FM00')
                                    AS ����
                ,
                COALESCE(sub.s_name, par.s_name, NULL)  -- ��������֤�NULL�Ǥʤ��ǽ���ͤ��֤�
                                    AS ����̾
            FROM act_summary AS act
            LEFT OUTER JOIN mactukl AS sub USING(actcod, aucod)
            LEFT OUTER JOIN macuntl AS par USING(actcod)
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm AND act_id = {$session->get_local('targetAct_id')}
            GROUP BY actcod, aucod, sub.s_name, par.s_name
            ORDER BY actcod ASC, aucod ASC
        ";
        return $query;
    }
    // ���Ƥ�����λ���ǯ��δ��Ȥ�������ʬ��ȯ���������ܤΰ�������� SQL
    private function getQueryActCodeAll($session)
    {
        // ���������ǯ�����������(�㡧0604)
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $yyyy -= 1;
            $mm = '04';
        } else {
            $yyyy -= 2;
            $mm = '04';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                actcod              AS ����
                ,
                to_char(aucod, 'FM00')
                                    AS ����
                ,
                COALESCE(sub.s_name, par.s_name, NULL)  -- ��������֤�NULL�Ǥʤ��ǽ���ͤ��֤�
                                    AS ����̾
            FROM act_summary AS act
            LEFT OUTER JOIN mactukl AS sub USING(actcod, aucod)
            LEFT OUTER JOIN macuntl AS par USING(actcod)
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm
            GROUP BY actcod, aucod, sub.s_name, par.s_name
            ORDER BY actcod ASC, aucod ASC
        ";
        return $query;
    }
    // ����ǯ��λ������祳���ɤ���¤�����δ���ζ�ۤ���� SQL
    private function getQueryStatement($session, $actCode, $detailCode)
    {
        $ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                CASE
                    WHEN act_monthly IS NULL THEN 0
                    WHEN act_monthly = 0 THEN 0
                    ELSE act_monthly
                END                 AS ���
            FROM act_summary
            WHERE act_yymm = {$ym4} AND act_id = {$session->get_local('targetAct_id')}
                AND actcod = {$actCode} AND aucod = {$detailCode}
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    // ����ǯ������Ƥ����祳���ɤ���¤�����δ���ζ�ۤ���� SQL
    private function getQueryStatementAll($session, $actCode, $detailCode)
    {
        $ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                CASE
                    WHEN sum(act_monthly) IS NULL THEN 0
                    WHEN sum(act_monthly) = 0 THEN 0
                    ELSE sum(act_monthly)
                END                 AS ���
            FROM act_summary
            WHERE act_yymm = {$ym4}
                AND actcod = {$actCode} AND aucod = {$detailCode}
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    // ����ǯ��λ������祳���ɤ���¤�����δ�����߷פ���� SQL
    private function getQuerySum($session, $actCode, $detailCode)
    {
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $mm = '04';
        } else {
            $yyyy -= 1;
            $mm = '04';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                act_sum AS �߷�
                ,
                act_yymm AS ǯ��
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm AND act_id = {$session->get_local('targetAct_id')}
                AND actcod = {$actCode} AND aucod = {$detailCode}
            ORDER BY act_yymm DESC
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    // ����ǯ������Ƥ����祳���ɤ���¤�����δ�����߷פ���� SQL
    private function getQuerySumAll($session, $actCode, $detailCode)
    {
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $mm = '04';
        } else {
            $yyyy -= 1;
            $mm = '04';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                sum(act_monthly) AS �߷�
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm
                AND actcod = {$actCode} AND aucod = {$detailCode}
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    // ����ǯ��λ������祳���ɤ���¤�����δ����Ⱦ���߷פ���� SQL
    private function getQueryHalfSum($session, $actCode, $detailCode)
    {
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4 && $mm < 10) {
            $mm = '04';
        } else if ($mm >= 10 && $mm < 13) {
            $mm = '10';
        } else {
            $yyyy -= 1;
            $mm = '10';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                sum(act_monthly)
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm AND act_id = {$session->get_local('targetAct_id')}
                AND actcod = {$actCode} AND aucod = {$detailCode}
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    // ����ǯ������Ƥ����祳���ɤ���¤�����δ����Ⱦ���߷פ���� SQL
    private function getQueryHalfSumAll($session, $actCode, $detailCode)
    {
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4 && $mm < 10) {
            $mm = '04';
        } else if ($mm >= 10 && $mm < 13) {
            $mm = '10';
        } else {
            $yyyy -= 1;
            $mm = '10';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = substr($session->get_local('targetDateYM'), 2, 4);
        $query = "
            SELECT
                sum(act_monthly)
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm
                AND actcod = {$actCode} AND aucod = {$detailCode}
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    ///// List��   ��Ω�Υ饤���̹�������դ� ���٥ǡ�������
    private function getViewHTMLbody($session, $menu)
    {
        ///// �оݲ��ܤμ���
        if ($session->get_local('targetAct_id') == '000') {
            $query = $this->getQueryActCodeAll($session);
        } else {
            $query = $this->getQueryActCode($session);
        }
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) <= 0) {
            // $session->add('s_sysmsg', '��¤����Υǡ���������ޤ���');
        }
        ///// �оݷ�ζ�ۡ��߷פμ���
        for ($i=0; $i<$rows; $i++) {
            if ($session->get_local('targetAct_id') == '000') {
                $nowState[$i] = $this->getQueryStatementAll($session, $res[$i][0], $res[$i][1]);
                $nowSum[$i] = $this->getQuerySumAll($session, $res[$i][0], $res[$i][1]);
                $hulfSum[$i] = $this->getQueryHalfSumAll($session, $res[$i][0], $res[$i][1]);
            } else {
                $nowState[$i] = $this->getQueryStatement($session, $res[$i][0], $res[$i][1]);
                $nowSum[$i] = $this->getQuerySum($session, $res[$i][0], $res[$i][1]);
                $hulfSum[$i] = $this->getQueryHalfSum($session, $res[$i][0], $res[$i][1]);
            }
        }
        
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>��¤�����δ���Υǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $expenseFlg = 0;
            $laborFlg   = $this->getLaborAuth($session);
            $preData    = array();
            $nowData    = array();
            for ($i=0; $i<$rows; $i++) {
                if ($session->get_local('targetAct_id') == '000') {
                    ///// ���Ƥ�������оݲ��ܡ�����������ʿ�Ѥ����
                    $preData[$i] = $this->getPreAverageActCodeAll($session, $res[$i][0], $res[$i][1]);
                    ///// ���Ƥ�������оݲ��ܡ������κ���ʿ�Ѥ����
                    $nowData[$i] = $this->getNowAverageActCodeAll($session, $res[$i][0], $res[$i][1]);
                } else {
                    ///// �оݲ��ܡ�����������ʿ�Ѥ����
                    $preData[$i] = $this->getPreAverageActCode($session, $res[$i][0], $res[$i][1]);
                    ///// �оݲ��ܡ������κ���ʿ�Ѥ����
                    $nowData[$i] = $this->getNowAverageActCode($session, $res[$i][0], $res[$i][1]);
                }
                ///// ��¤����η����ϫ̳���ץ�ѥƥ�����¸
                if ($res[$i][0] <= 8000) {
                    $this->total_expense    += $nowState[$i];
                    $this->sum_expense      += $nowSum[$i];
                    $this->pre_expense      += $preData[$i];
                    $this->now_expense      += $nowData[$i];
                    $this->hulf_expense     += $hulfSum[$i];
                } else {
                    if ($expenseFlg == 0) {
                        $listTable .= "    <tr>\n";
                        $listTable .= "        <td class='winbox' width='16%' align='right' colspan='3'>&nbsp;</td>\n";
                        $listTable .= "        <td class='winbox total' width='20%' align='right' >�С��񡡷�</td>\n";
                        $listTable .= "        <td class='winbox total' width='12%' align='right' >" . number_format($this->pre_expense) . "</td>\n";   // ����ʿ�� ����ξ���
                        $listTable .= "        <td class='winbox total' width='12%' align='right' >" . number_format($this->now_expense) . "</td>\n";   // ����ʿ�� ����ξ���
                        $listTable .= "        <td class='winbox target' width='12%' align='right' >" . number_format($this->total_expense) . "</td>\n"; // ����ξ���
                        $listTable .= "        <td class='winbox total' width='14%' align='right' >" . number_format($this->sum_expense)   . "</td>\n"; // �߷� ����ξ���
                        $listTable .= "        <td class='winbox total' width='14%' align='right' >" . number_format($this->hulf_expense)   . "</td>\n"; // Ⱦ���߷� ����ξ���
                        $listTable .= "    </tr>\n";
                        $expenseFlg = 1;
                    }
                    $this->total_laborCost  += $nowState[$i];
                    $this->sum_laborCost    += $nowSum[$i];
                    $this->pre_laborCost    += $preData[$i];
                    $this->now_laborCost    += $nowData[$i];
                    $this->hulf_laborCost   += $hulfSum[$i];
                }
                /*****
                if ($res[$i][10] != '') {   // �����Ȥ�����п����Ѥ���
                    $listTable .= "    <tr onDblClick='ActSummary.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='�����Ȥ���Ͽ����Ƥ��ޤ������֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='ActSummary.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='���ߥ����Ȥ���Ͽ����Ƥ��ޤ��󡣥��֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���'>\n";
                }
                *****/
                // $listTable .= "    <tr style='visibility:hidden;'>\n";����ˡ�Ǳ��������ǽ
                if ($expenseFlg == 0 || $laborFlg) {
                    $listTable .= "    <tr>\n";
                    $listTable .= "        <td class='winbox' width=' 4%' align='right' >" . ($i+1) . "</td>\n";    // ���ֹ�
                    // $listTable .= "        <td class='winbox' width=' 8%' align='right' ><a href='javascript:win_open(\"{$menu->out_self()}?Action=ListDetails&showMenu=ListWin&targetUid={$res[$i][0]}\");'>����</a></td>\n"; // ���٥���å���
                    $listTable .= "        <td class='winbox' width=' 6%' align='right' >{$res[$i][0]}</td>\n";     // ����
                    $listTable .= "        <td class='winbox' width=' 6%' align='left'  >{$res[$i][1]}</td>\n";     // ��������
                    $listTable .= "        <td class='winbox' width='20%' align='left'  >{$res[$i][2]}</td>\n";     // ����̾
                    $listTable .= "        <td class='winbox' width='12%' align='right' >" . number_format($preData[$i]) . "</td>\n";// ����ʿ��
                    $listTable .= "        <td class='winbox' width='12%' align='right' >" . number_format($nowData[$i]) . "</td>\n";// ����ʿ��
                    $listTable .= "        <td class='winbox target' width='12%' align='right' >" . number_format($nowState[$i]) . "</td>\n";// ���
                    $listTable .= "        <td class='winbox' width='14%' align='right' >" . number_format($nowSum[$i]) . "</td>\n";// �߷�
                    $listTable .= "        <td class='winbox' width='14%' align='right' >" . number_format($hulfSum[$i]) . "</td>\n";// Ⱦ���߷�
                    $listTable .= "    </tr>\n";
                }
            }
            $this->total_cost = $this->total_expense + $this->total_laborCost;
            $this->sum_cost   = $this->sum_expense + $this->sum_laborCost;
            $this->pre_cost   = $this->pre_expense + $this->pre_laborCost;
            $this->now_cost   = $this->now_expense + $this->now_laborCost;
            $this->hulf_cost  = $this->hulf_expense + $this->hulf_laborCost;
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' width='16%' align='right' colspan='3'>&nbsp;</td>\n";
            $listTable .= "        <td class='winbox total' width='20%' align='right'>ϫ��̳���񡡷�</td>\n";
            $listTable .= "        <td class='winbox total' width='12%' align='right' >" . number_format($this->pre_laborCost) . "</td>\n";  // ����ʿ�� ϫ̳��ξ���
            $listTable .= "        <td class='winbox total' width='12%' align='right' >" . number_format($this->now_laborCost) . "</td>\n";  // ����ʿ�� ϫ̳��ξ���
            $listTable .= "        <td class='winbox target' width='12%' align='right' >" . number_format($this->total_laborCost) . "</td>\n";// ϫ̳��ξ���
            $listTable .= "        <td class='winbox total' width='14%' align='right' >" . number_format($this->sum_laborCost)   . "</td>\n";// �߷� ϫ̳��ξ���
            $listTable .= "        <td class='winbox total' width='14%' align='right' >" . number_format($this->hulf_laborCost)   . "</td>\n";// Ⱦ���߷� ϫ̳��ξ���
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox total' width='36%' align='right' colspan='4'>�����硡�硡��</td>\n";
            $listTable .= "        <td class='winbox total' width='12%' align='right' >" . number_format($this->pre_cost) . "</td>\n";  // ����ʿ�� ������
            $listTable .= "        <td class='winbox total' width='12%' align='right' >" . number_format($this->now_cost) . "</td>\n";  // ����ʿ�� ������
            $listTable .= "        <td class='winbox target' width='12%' align='right' >" . number_format($this->total_cost) . "</td>\n";// ������
            $listTable .= "        <td class='winbox total' width='14%' align='right' >" . number_format($this->sum_cost)   . "</td>\n";// �߷� ������
            $listTable .= "        <td class='winbox total' width='14%' align='right' >" . number_format($this->hulf_cost)   . "</td>\n";// Ⱦ���߷� ������
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   ����ɽ�� �إå����������
    private function getViewHTMLheader()
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' colspan='11'>{$title}</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 1%'>No</th>\n";
        $listTable .= "        <th class='winbox' width=' 6%'>����</th>\n";
        $listTable .= "        <th class='winbox' width=' 6%'>����</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>�������̾</th>\n";
        $listTable .= "        <th class='winbox' width='13%'>����ʿ��</th>\n";
        $listTable .= "        <th class='winbox' width='13%'>����ʿ��</th>\n";
        $listTable .= "        <th class='winbox' width='13%'>������</th>\n";
        $listTable .= "        <th class='winbox' width='14%'>�����߷�</th>\n";
        $listTable .= "        <th class='winbox' width='14%'>Ⱦ���߷�</th>\n";
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
<link rel='stylesheet' href='../act_summary.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../act_summary.js'></script>
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
    
    
    ////////// ������ܡ��������ܤ�������ʿ�Ѥ��֤�
    private function getPreAverageActCode($session, $actCode, $detailCode)
    {
        // ��������ǯ�����������(�㡧0603)
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $mm = '03';
        } else {
            $yyyy -= 1;
            $mm = '03';
        }
        $now_ym4 = substr($yyyy, 2, 2) . $mm;
        $pre_ym4 = $now_ym4 - 99;
        $query = "
            SELECT
                CASE
                    WHEN act_sum IS NULL THEN 0
                    WHEN act_sum = 0 THEN 0
                    ELSE Uround(act_sum / 12, 0)
                END                 AS ����ʿ��
                ,
                act_yymm AS ǯ��
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm AND act_id = {$session->get_local('targetAct_id')}
                AND actcod = {$actCode} AND aucod = {$detailCode}
            ORDER BY act_yymm DESC
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    ////////// ���Ƥ�����λ�����ܡ��������ܤ�������ʿ�Ѥ��֤�
    private function getPreAverageActCodeAll($session, $actCode, $detailCode)
    {
        // ��������ǯ�����������(�㡧0603)
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $mm = '03';
        } else {
            $yyyy -= 1;
            $mm = '03';
        }
        $now_ym4 = substr($yyyy, 2, 2) . $mm;
        $pre_ym4 = $now_ym4 - 99;
        $query = "
            SELECT
                CASE
                    WHEN sum(act_sum) IS NULL THEN 0
                    WHEN sum(act_sum) = 0 THEN 0
                    ELSE Uround(sum(act_sum) / 12, 0)
                END                 AS ����ʿ��
                ,
                act_yymm AS ǯ��
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm
                AND actcod = {$actCode} AND aucod = {$detailCode}
            GROUP BY act_yymm
            ORDER BY act_yymm DESC
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    ////////// ������ܡ��������ܤǺ�����ʿ�Ѥ��֤�
    private function getNowAverageActCode($session, $actCode, $detailCode)
    {
        // targetDateYM�Ǥκ�������ǯ�����������(�㡧0603)
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $yyyy += 1;
            $mm = '03';
        } else {
            $mm = '03';
        }
        $ym4 = substr($yyyy, 2, 2) . $mm;
        // �������ǤΥǡ���������Ǹ��ǯ����������
        $query = "SELECT act_yymm, act_ser FROM act_summary WHERE act_yymm <= {$ym4} ORDER BY act_yymm DESC LIMIT 1";
        $res = array();
        $this->getResult2($query, $res);
        $cnt = $res[0][1];
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $mm = '04';
        } else {
            $yyyy -= 1;
            $mm = '04';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = $ym4;
        $query = "
            SELECT
                CASE
                    WHEN act_sum IS NULL THEN 0
                    WHEN act_sum = 0 THEN 0
                    ELSE Uround(act_sum / {$cnt}, 0)
                END                 AS ����ʿ��
                ,
                act_yymm AS ǯ��
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm AND act_id = {$session->get_local('targetAct_id')}
                AND actcod = {$actCode} AND aucod = {$detailCode}
            ORDER BY act_yymm DESC
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    ////////// ���Ƥ�����λ�����ܡ��������ܤǺ�����ʿ�Ѥ��֤�
    private function getNowAverageActCodeAll($session, $actCode, $detailCode)
    {
        // targetDateYM�Ǥκ�������ǯ�����������(�㡧0603)
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $yyyy += 1;
            $mm = '03';
        } else {
            $mm = '03';
        }
        $ym4 = substr($yyyy, 2, 2) . $mm;
        // �������ǤΥǡ���������Ǹ��ǯ����������
        $query = "SELECT act_yymm, act_ser FROM act_summary WHERE act_yymm <= {$ym4} ORDER BY act_yymm DESC LIMIT 1";
        $res = array();
        $this->getResult2($query, $res);
        $cnt = $res[0][1];
        $yyyy = substr($session->get_local('targetDateYM'), 0, 4);
        $mm   = substr($session->get_local('targetDateYM'), 4, 2);
        if ($mm >= 4) {
            $mm = '04';
        } else {
            $yyyy -= 1;
            $mm = '04';
        }
        $pre_ym4 = substr($yyyy, 2, 2) . $mm;
        $now_ym4 = $ym4;
        $query = "
            SELECT
                CASE
                    WHEN sum(act_sum) IS NULL THEN 0
                    WHEN sum(act_sum) = 0 THEN 0
                    ELSE Uround(sum(act_sum) / {$cnt}, 0)
                END                 AS ����ʿ��
                ,
                act_yymm AS ǯ��
            FROM act_summary
            WHERE act_yymm <= {$now_ym4} AND {$pre_ym4} <= act_yymm
                AND actcod = {$actCode} AND aucod = {$detailCode}
            GROUP BY act_yymm
            ORDER BY act_yymm DESC
            LIMIT 1
        ";
        $data = 0;
        $this->getUniResult($query, $data);
        return $data;
    }
    ////////// ϫ̳������� �Ȳ񤬽���븢�¤μ��� true=OK false=NG
    private function getLaborAuth($session)
    {
        $query = "SELECT pid FROM user_detailes WHERE uid = '{$session->get('User_ID')}'";
        $pid = 0;               // �����
        $this->getUniResult($query, $pid);
        if ($pid >= 60 || $session->get('Auth') >= 3 || $session->get('User_ID')== '011061') {       // ����Ĺ�ʾ夫�����ƥ������
            return true;
        } else {
            return false;
        }
    }
    
} // Class ActSummary_Model End

?>
