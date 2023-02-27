<?php
//////////////////////////////////////////////////////////////////////////////
// ���� �߸� ͽ�� �Ȳ� (������ȯ������Ȳ�)                   MVC Model ��   //
// Copyright (C) 2004-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/12/20 Created   parts_stock_history_Model.php                       //
// 2007/03/09 ���ꥸ�ʥ��parts_stock_view.php��parts_stock_plan_Model.php  //
//            �˹�碌�ƴ����ʣ֣ͣå�ǥ�ǥ����ǥ��󥰤�����              //
//            �ѹ������ backup/parts_stock_view.php �򻲾Ȥ��뤳�ȡ�       //
// 2007/03/24 last_stock_day ����ɽ����κǽ��׾������С��ѿ����ɲ�       //
// 2007/05/14 �׾����Υ������˷׾���kei_ym���Ϥ��褦���ɲ� ��ë         //
// 2007/05/17 getViewHTMLfooter()�᥽�åɤ˷�ʿ�ѽи˿�����ͭ����ɲ�       //
// 2007/06/09 �եå�����message��2�ʤˤʤ��礬���뤿��׻����߸ˤ�0.8em�� //
// 2007/06/22 getViewHTMLconst()��MenuHeader���饹��out_retF2Script()���ɲ� //
//                �� �θƽФ����� noMenu �Υѥ�᡼�������å���Ԥ�         //
// 2007/08/02 Window�Ǥ�ͽ��ȷ��������ɽ������ɽ���ϰϤ�����뤿���б���  //
//            $session->get('stock_date_low')���������褦���ѹ�           //
// 2007/12/18 getQueryStatement()��SQLʸ���������(���ˤȽиˤ�Ʊ���ξ��)  //
// 2011/07/27 �߸˷����ɽ���ϰϤ򣵣����Ԥ��ѹ�                       ��ë //
// 2016/08/08 mouseOver���ɲ�                                          ��ë //
// 2019/05/10 ���PGM�ѹ���ȼ���ѹ�                                    ��ë //
// 2019/06/25 ɽ�������1000��ˡ���������7ǯ�����ѹ���                     //
//            �������Ǥʤ����Ȥ������                               ��ë //
// 2019/07/24 ��������10ǯ�����ѹ����������Ǥʤ����Ȥ�����١�       ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class PartsStockHistory_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $last_stock_day;                    // ����ɽ����κǽ��׾���
    private $last_stock_pcs;                    // ���ߺ߸�
    
    ///// public properties
    // public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request, $result, $session)
    {
        $this->last_stock_day = '';             // �����
        $this->last_stock_pcs = '0';            // �����
        
        ///// ����WHERE�������
        switch ($request->get('showMenu')) {
        case 'List':
        case 'ListWin':
            $this->where = $this->SetInitWhere($request, $result, $session);
            break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    ����եǡ��������� ����ɽ
    public function outViewListHTML($request, $menu, $result)
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
        $file_name = "list/parts_stock_history_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** ��ʸ����� *****/
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLbody($request, $menu, $result);
        // �����HTML�����������
        if ($request->get('noMenu') == '') {
            $listHTML .= $this->getViewHTMLconst('footer', $menu);
        } else {
            $listHTML .= $this->getViewHTMLconst('footer');
        }
        // HTML�ե��������
        $file_name = "list/parts_stock_history_ViewList-{$_SESSION['User_ID']}.html";
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
        $file_name = "list/parts_stock_history_ViewListFooter-{$_SESSION['User_ID']}.html";
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
    protected function SetInitWhere($request, $result, $session)
    {
        // �����ϰϤγ���
        if ($request->get('date_low') != '') {
            $date_low = $request->get('date_low');
        } elseif ($session->get('stock_date_low') != '') {
            $date_low = $session->get('stock_date_low');    // 2007/08/02 Window�Ǥ�ͽ��ȷ��������ɽ���б�
        } else {
            //$date_low = (date('Ymd') - 50000);      // ��ǯ������
            $date_low = (date('Ymd') - 70000);      // ��ǯ������
            $date_low = (date('Ymd') - 100000);      // ����ǯ������
        }
        // �����ϰϤν�λ
        if ($request->get('date_upp') != '') {
            $date_upp = $request->get('date_upp');
        } else {
            $date_upp = date('Ymd');                // �����ޤ�
        }
        // ɽ���Կ���������
        if ($request->get('view_rec') != '') {
            $view_rec = $request->get('view_rec');
        } else {
            $sql = "SELECT tnk_stock FROM parts_stock_master WHERE parts_no='{$request->get('targetPartsNo')}'";
            $this->getUniResult($sql, $tnk_stock);
            if ($tnk_stock >= 100000) {
                //$view_rec = '500';      // ���ꤵ��Ƥ��ʤ����(��������̤��Ͽ����θƽ���) 500��400
                $view_rec = '1000';      // ���ꤵ��Ƥ��ʤ����(��������̤��Ͽ����θƽ���) 500��400
            } else {
                // 2006/10/13 200��300 ���ѹ� (��ͳ:�ײ��ֹ�CA234885 �����ֹ�CQ17202-0�������������ˤ�����200�Ǥ���Ū�����ˤ�ɽ��������ʤ�����)
                // 2019/06/28 500��1000 ���ѹ� (��Ū�����ˤ�ɽ��������ʤ�����)
                //$view_rec = '500';      // ���ꤵ��Ƥ��ʤ����(��������̤��Ͽ����θƽ���) 500��200
                $view_rec = '1000';      // ���ꤵ��Ƥ��ʤ����(��������̤��Ͽ����θƽ���) 500��200
            }
            /***** 2006/10/12 ����������������å�������η��򥪥ե��åȥ��å��ɲ� *****/
            if ($result->get('plan_no') != '��' && $result->get('plan_no') != '') {
                $sql = "
                    SELECT upd_date FROM parts_stock_history
                    WHERE parts_no='{$request->get('targetPartsNo')}' AND plan_no='{$result->get('plan_no')}'
                    ORDER BY upd_date ASC LIMIT 1
                ";
                ;
                if ($this->getUniResult($sql, $upd_date) > 0) {
                    ///// �оݤΣ�ǯ������  // ��ǯ������ // ����ǯ������
                    $date_low = ((substr($upd_date, 0, 4) - 10) . substr($upd_date, 4, 4));
                    ///// �оݤΣ������ޤ�
                    if (substr($upd_date, 4, 2) >= 12) {
                        $date_upp = ((substr($upd_date, 0, 4) + 1) . '01' . substr($upd_date, 6, 2));
                    } else {
                        $month = sprintf('%02d', substr($upd_date, 4, 2) + 1);
                        $date_upp = (substr($upd_date, 0, 4) . $month . substr($upd_date, 6, 2));
                    }
                }
            }
        }
        $where = "
            WHERE parts_no = '{$request->get('targetPartsNo')}' AND upd_date >= {$date_low} AND upd_date <= {$date_upp}
            ORDER BY parts_no DESC, upd_date DESC, serial_no DESC
            LIMIT {$view_rec}
        ";
        return $where;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
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
        $listTable .= "        <th class='winbox' width='10%'>�׾���</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>Ŧ����</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>�и˿�</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>���˿�</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>��׺߸�</th>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>��ʬ</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>��ɼ�ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>���ں߸�</th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'>�Σ˺߸�</th>\n";
        $listTable .= "        <th class='winbox' width='16%'>������</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��   ����ɽ�Υܥǥ��������١ˤ����
    private function getViewHTMLbody($request, $menu, $result)
    {
        $query = $this->getQueryStatement($request);
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) > 0 ) {
            $last_anchor_flg = TRUE;    // �Ǹ�ιԤ˥��󥫡���ɬ�פν����
            for ($i=($rows-1); $i>=0; $i--) {
                // $res[$i][9] = mb_convert_kana($res[$i][9], 'k');    // ����ä�Ⱦ�ѥ��ʤ��Ѵ�
                // $res[$i][9] = mb_substr($res[$i][9], 0, 12);        // Ⱦ�ѥ��ʤξ�祪���С�����Τ��Ѵ���15ʸ���ˤ���
                ///// �ԥޡ�����������
                if ($result->get('plan_no') == $res[$i][1]) {
                    $listTable .= "    <tr style='background-color:#ffffc6;'>\n";
                } else {
                    $listTable .= "    <tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                }
                ///// ���󥫡�������(<td>�����ˤ��Ƥ���Τ�NN7.1���б�)
                if ($result->get('plan_no') == $res[$i][1]) {  // �ײ��ֹ椬���פ��Ƥ���Х��󥫡���Ω�Ƥ�
                    $listTable .= "        <td class='winbox' width=' 5%' align='right'  nowrap><a name='last' style='color:black;'>" . ($i + 1) . "</a></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    $last_anchor_flg = FALSE;   // �Ǹ�ιԤΥ��󥫡���ɬ�פǤʤ�
                } else if ( ($i == 0) && ($last_anchor_flg) ) {    // �Ǹ�ιԤ˥��󥫡���Ω�Ƥ�
                    $listTable .= "        <td class='winbox' width=' 5%' align='right'  nowrap><a name='last' style='color:black;'>" . ($i + 1) . "</a></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                } else {
                    $listTable .= "        <td class='winbox' width=' 5%' align='right'  nowrap>" . ($i + 1) . "</td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                }
                ///// �׾����Υ��(���)����
                if ($res[$i][5] == '1' && (!$request->get('noMenu')) ) {  // �׾���
                    $listTable .= "        <td class='winbox' width='10%' align='center' nowrap><a href='{$menu->out_action('��ݼ��ӾȲ�')}?parts_no=" . urlencode($request->get('targetPartsNo')) . "&uke_no={$res[$i][6]}{$result->get('material')}&kei_ym={$res[$i][0]}&div=" . ' ' . "&vendor=". '' ."' target='_parent' style='text-decoration:none;'>{$res[$i][0]}</a></td>\n";
                } else {
                    $listTable .= "        <td class='winbox' width='10%' align='center' nowrap>{$res[$i][0]}</td>\n";
                }
                $listTable .= "        <td class='winbox' width='10%' align='center' nowrap>{$res[$i][1]}</td>\n";  // Ŧ����
                $listTable .= "        <td class='winbox' width=' 9%' align='right'  nowrap>" . number_format($res[$i][2]) . "</td>\n";  // �и˿�
                $listTable .= "        <td class='winbox' width=' 9%' align='right'  nowrap>" . number_format($res[$i][3]) . "</td>\n";  // ���˿�
                $listTable .= "        <td class='winbox' width=' 9%' align='right'  nowrap>" . number_format($res[$i][4]) . "</td>\n";  // ��׺߸�
                $listTable .= "        <td class='winbox' width=' 5%' align='center' nowrap>{$res[$i][5]}</td>\n";  // ��ʬ
                $listTable .= "        <td class='winbox' width=' 9%' align='center' nowrap>{$res[$i][6]}</td>\n";  // ��ɼ�ֹ�
                $listTable .= "        <td class='winbox' width=' 9%' align='right'  nowrap>" . number_format($res[$i][7]) . "</td>\n";  // ���ں߸�
                $listTable .= "        <td class='winbox' width=' 9%' align='right'  nowrap>" . number_format($res[$i][8]) . "</td>\n";  // �Σ˺߸�
                $listTable .= "        <td class='winbox' width='16%' align='left'   nowrap>{$res[$i][9]}</td>\n";  // ����
                $listTable .= "    </tr>\n";
            }
            $this->last_stock_day = $res[0][0];     // ����ɽ����κǽ��� �ݴ�
            $this->last_stock_pcs = $res[0][4];     // ��׺߸��ݴ�
        }
        if ($rows > 0) {
            $all_stock = $res[$i+1][4];     // �Ǹ�ι�׺߸ˤ��ݴ�
            $tnk_stock = $res[$i+1][7];     // �Ǹ�����ں߸ˤ��ݴ�
            $nk_stock  = $res[$i+1][8];     // �Ǹ�ΣΣ˺߸ˤ��ݴ�
        } else {
            $all_stock = 0;                 // �����б�
            $tnk_stock = 0;
            $nk_stock  = 0;
        }
        $queryKen = $this->getQueryStatement2($request);
        $resKen   = array();
        $rowsKen  = $this->getResult2($queryKen, $resKen);
        for ($s=0; $s<$rowsKen; $s++) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td class='winbox' width=' 5%' align='right'  style='color:yellow;' nowrap>{$resKen[$s][0]}</td>\n";  // ���ֹ�
            $listTable .= "        <td class='winbox' width='10%' align='center' style='color:yellow;' nowrap>{$resKen[$s][1]}</td>\n";  // �׾���
            $listTable .= "        <td class='winbox' width='10%' align='center' style='color:yellow;' nowrap>{$resKen[$s][2]}</td>\n";  // Ŧ����
            $listTable .= "        <td class='winbox' width=' 9%' align='right'  style='color:yellow;' nowrap>{$resKen[$s][3]}</td>\n";  // �и˿�
            $listTable .= "        <td class='winbox' width=' 9%' align='right'  style='color:yellow;' nowrap>" . number_format($resKen[$s][4]) . "</td>\n";  // ���˿�
            $all_stock += $resKen[$s][4];
            $listTable .= "        <td class='winbox' width=' 9%' align='right'  style='color:gray;' nowrap>" . number_format($all_stock) . "</td>\n";  // ��׺߸�
            $listTable .= "        <td class='winbox' width=' 5%' align='center' style='color:gray;' nowrap>{$resKen[$s][6]}</td>\n";  // ��ʬ
            $listTable .= "        <td class='winbox' width=' 9%' align='center' style='color:gray;' nowrap>{$resKen[$s][7]}</td>\n";  // ��ɼ�ֹ�
            $tnk_stock += $resKen[$s][4];
            $listTable .= "        <td class='winbox' width=' 9%' align='right'  style='color:gray;' nowrap>" . number_format($tnk_stock) . "</td>\n";  // ���ں߸�
            $nk_stock += $resKen[$s][9];
            $listTable .= "        <td class='winbox' width=' 9%' align='right'  style='color:gray;' nowrap>" . number_format($nk_stock) . "</td>\n";   // �Σ˺߸�
            $listTable .= "        <td class='winbox' width='16%' align='left'   style='color:gray;' nowrap>{$resKen[$s][10]}</td>\n"; // ����
            $listTable .= "    </tr>\n";
        }
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
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
            $footer_title = "<span style='font-size:0.8em;'>�׻����߸ˡ�{$invent}��</span>��ʿ�ѽиˡ�{$pickup}��<span style='color:teal;'>��ͭ�{$month}</span>";
        } else {
            $footer_title = '&nbsp;';
        }
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='43%' align='right'>'{$this->last_stock_day}���ߤκ߸�</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right'>" . number_format($this->last_stock_pcs) . "</td>\n";
        $listTable .= "        <td class='winbox' width='48%' align='center'>{$footer_title}</td>\n";
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
            SELECT substr(to_char(ent_date, 'FM9999/99/99'), 3, 8)
                                                            as �׾���       -- 0
                , CASE
                    WHEN plan_no = '' THEN '&nbsp;'
                    ELSE plan_no
                  END                                       as Ŧ����       -- 1
                , CASE
                    WHEN out_id = '1' THEN CAST(stock_mv AS TEXT)
                    WHEN out_id = '2' THEN CAST(stock_mv AS TEXT)
                    ELSE '&nbsp;'
                  END                                       as �и˿�       -- 2
                , CASE
                    WHEN in_id = '1' THEN CAST(stock_mv AS TEXT)
                    WHEN in_id = '2' THEN CAST(stock_mv AS TEXT)
                    ELSE '&nbsp;'
                  END                                       as ���˿�       -- 3
                , CASE
                    WHEN out_id = '1' AND in_id  = '2' THEN nk_stock  - stock_mv + stock_mv + tnk_stock -- 2007/12/18 ADD ��Ĺ�ˤʤ뤬���å��ΰ�̣�����Τˤ��뤿��
                    WHEN out_id = '1' THEN nk_stock  - stock_mv + tnk_stock
                    WHEN out_id = '2' AND in_id  = '1' THEN tnk_stock  - stock_mv + stock_mv + nk_stock -- 2007/12/18 ADD ��Ĺ�ˤʤ뤬���å��ΰ�̣�����Τˤ��뤿��
                    WHEN out_id = '2' THEN tnk_stock - stock_mv + nk_stock
                    WHEN in_id  = '1' THEN nk_stock  + stock_mv + tnk_stock
                    WHEN in_id  = '2' THEN tnk_stock + stock_mv + nk_stock
                    ELSE nk_stock + tnk_stock
                  END                                       as ��׺߸�     -- 4
                , den_kubun                                 as ��ʬ         -- 5
                , CASE
                    WHEN den_no = '' THEN '&nbsp;'
                    ELSE den_no
                  END                                       as ��ɼ�ֹ�     -- 6
                , CASE
                    WHEN out_id = '2' THEN tnk_stock - stock_mv
                    WHEN in_id  = '2' THEN tnk_stock + stock_mv
                    ELSE tnk_stock
                  END                                       as ���ں߸�     -- 7
                , CASE
                    WHEN out_id = '1' THEN nk_stock  - stock_mv
                    WHEN in_id  = '1' THEN nk_stock  + stock_mv
                    ELSE nk_stock
                  END                                       as �Σ˺߸�     -- 8
                , CASE
                    WHEN note = '' THEN '&nbsp;'
                    ELSE note
                  END                                       as ������       -- 9
            FROM
                parts_stock_history
        ";
        $query .= $this->where;
        return $query;
    }
    
    ///// List��   ����ɽ��SQL���ơ��ȥ��ȼ���
    private function getQueryStatement2($request)
    {
        $query = "
            SELECT
                '&nbsp;'                                                AS ���ֹ�               -- 0
                ,
                substr(to_char(data.uke_date, 'FM0000/00/00'), 3, 8)    AS �׾���               -- 1 (������)
                ,
                '������'                                                AS Ŧ��                 -- 2
                ,
                0                                                       AS �и˿�               -- 3
                ,
                -- to_char(data.uke_q, 'FM9,999,999')                      AS ���˿�               -- 4 (���տ�)
                round(data.uke_q, 0)                                    AS ���˿�               -- 4 (���տ�)
                ,
                0                                                       AS ��׺߸�             -- 5
                ,
                '��'                                                    AS ��ʬ                 -- 6
                ,
                data.uke_no                                             AS ��ɼ�ֹ�             -- 7 (�����ֹ�)
                ,
                0                                                       AS ���ں߸�             -- 8
                ,
                0                                                       AS �Σ˺߸�             -- 9
                ,
                substr(vendor.name, 1, 6)                               AS ����                 --10 (Ǽ����)
            FROM
                order_plan      AS plan
                LEFT OUTER JOIN
                order_data      AS data
                    USING(sei_no)
                LEFT OUTER JOIN
                order_process   AS proc
                    USING(sei_no, order_no, vendor)
                LEFT OUTER JOIN
                vendor_master   AS vendor
                    USING(vendor)
            WHERE
                plan.parts_no = '{$request->get('targetPartsNo')}' AND plan.zan_q > 0 AND data.uke_q > 0 AND data.ken_date = 0 AND proc.next_pro = 'END..'
            ORDER BY
                �׾��� ASC, ��ɼ�ֹ� ASC
        ";
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
<title>���ʺ߸˷���Ȳ�</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../parts_stock_history.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:   none;
    overflow-x:         hidden;
    overflow-y:         scroll;
}
-->
</style>
<script type='text/javascript' src='../parts_stock_history.js'></script>
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
    
} // Class PartsStockHistory_Model End

?>
