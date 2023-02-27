<?php
//////////////////////////////////////////////////////////////////////////////
// Ĺ����α���ʤξȲ� �ǽ�����������Ǹ��ߺ߸ˤ�����ʪ       MVC Model ��   //
// Copyright (C) 2006-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/03 Created   long_holding_parts_Model.php                        //
//            Ʊ���θ�������ʾ���б���AND long.in_pcs=act_payable.genpin  //
// 2006/04/04 private $order ���ɲä����ܥ���å����б����ܥ����ȵ�ǽ����   //
// 2006/04/05 ���֤�ǿ�ñ�����ѹ����߸˷����in_date��10�������ѹ�         //
//            SetInitWhere()�ǽ����������ϰ�������������б�              //
// ORDER BY((long.tnk_stock+long.nk_stock) * long.tanka)��ORDER BY ��� DESC//
// 2006/04/06 substr(long.parts_name, 1, 20)��substr(long.parts_name, 1, 16)//
//            long.in_pcs=act_payable.genpin��long.den_no=act_payable.uke_no//
//            ����иˤ��ϰϵڤӲ��(ʪ��ư��)�ξ�索�ץ��������        //
//            parts_no=" . urlencode($res[$i][1]) urlencode���ɲ� -#�ֹ��б�//
// 2006/06/24 getUniResult() �� $this->getUniResult() ���ѹ�  135����       //
// 2007/04/18 getViewHTMLbody()�᥽�åɤ�onMouseover��������              //
//                                  <a href='javascript:void(0);'>���ɲ�    //
// 2007/06/05 ��׷������׶�ۤ�ܥǥ�������եå������ذ�ư              //
//            ORDER BY long.tanka �� ORDER BY �ǿ�ñ��                      //
// 2008/03/11 ������Ĺ����Ǻǽ����������ϰϤ�1ǯ������11���������ѹ�  ��ë //
// 2011/07/28 ���������� �к그Ĺ����ˤ�ꡢ�Ƶ�����ɲ�              ��ë //
// 2012/01/17 ��۷׻��˻ͼθ������ɲá���׶�ۤ��ǿ�ñ���򽸷פ���        //
//            �����Τ�����                                             ��ë //
// 2013/06/13 CSV���Ϥ��ɲ�                                            ��ë //
// 2013/10/10 �и��ϰϤ�60�������ޤ���ФǤ���褦���ѹ�               ��ë //
// 2019/01/28 �ġ�����ɲá��Х���롦ɸ��򥳥��Ȳ�                 ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class LongHoldingParts_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $order;                             // ���� SQL��ORDER��
    private $totalMsg;                          // �եå������ι�׷������׶����
    
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
    
    ////////// MVC �� Model��  �ǽ��������Υǡ�������
    ///// Get��    <select>�� <option>�ꥹ�Ȥ����
    public function getTargetDateView($request)
    {
        $list = "\n"; //�����
        // �ϰϤϣ������������飷ǯ��
        for ($i=11; $i<=84; $i++) {
            if ($request->get('targetDate') == $i) {
                $list .= ("<option value='{$i}' selected>" . mb_convert_kana($i, 'N') . "������</option>\n");
            } else {
                $list .= ("<option value='{$i}'>" . mb_convert_kana($i, 'N') . "������</option>\n");
            }
        }
        return $list;
    }
    
    ///// �ǽ�������������ϰϥǡ�������
    public function getTargetDateSpanView($request)
    {
        $list = "\n"; //�����
        // �ϰϤϣ������12����
        for ($i=1; $i<=12; $i++) {
            if ($request->get('targetDateSpan') == $i) {
                $list .= ("<option value='{$i}' selected>" . mb_convert_kana($i, 'N') . "�����</option>\n");
            } else {
                $list .= ("<option value='{$i}'>" . mb_convert_kana($i, 'N') . "�����</option>\n");
            }
        }
        if ($request->get('targetDateSpan') == 120) {
            $list .= "<option value='120' selected>�Ǹ�ޤ�</option>\n";
        } else {
            $list .= "<option value='120'>�Ǹ�ޤ�</option>\n";
        }
        return $list;
    }
    
    ///// �иˤ����ߤ��鲿�����<option>�ǡ�������
    public function getTargetOutDateView($request)
    {
        $list = "\n"; //�����
        // �ϰϤϣ������36����
        for ($i=1; $i<=60; $i++) {
            if ($request->get('targetOutDate') == $i) {
                $list .= ("<option value='{$i}' selected>" . mb_convert_kana($i, 'N') . "������</option>\n");
            } else {
                $list .= ("<option value='{$i}'>" . mb_convert_kana($i, 'N') . "������</option>\n");
            }
        }
        return $list;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    Ĺ����α���ʤλ�����Ǥ� ����ɽ
    public function outViewListHTML($request, $menu)
    {
        /************************* �ܥǥ� ***************************/
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLbody($request, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/long_holding_parts_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        /************************* �եå��� ***************************/
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLfooter();
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/long_holding_parts_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    ///// ���ʤΥ����Ȥ���¸
    public function commentSave($request)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        // if ($request->get('comment') == '') return;  // �����Ԥ��Ⱥ���Ǥ��ʤ�
        if ($request->get('targetPartsNo') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_host = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        $query = "SELECT comment FROM long_holding_parts_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
        if ($this->getUniResult($query, $comment) < 1) {
            $sql = "
                INSERT INTO long_holding_parts_comment (parts_no, comment, last_date, last_host)
                values ('{$request->get('targetPartsNo')}', '{$request->get('comment')}', '{$last_date}', '{$last_host}')
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�����Ȥ���¸������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            }
        } else {
            $sql = "
                UPDATE long_holding_parts_comment SET comment='{$request->get('comment')}',
                last_date='{$last_date}', last_host='{$last_host}'
                WHERE parts_no='{$request->get('targetPartsNo')}'
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
        if ($request->get('targetPartsNo') == '') return '';
        $query = "
            SELECT  comment  ,
                    trim(substr(midsc, 1, 20))
            FROM miitem LEFT OUTER JOIN
            long_holding_parts_comment ON(mipn=parts_no)
            WHERE mipn='{$request->get('targetPartsNo')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) > 0) {
            $result->add('comment', $res[0][0]);
            $result->add('parts_name', $res[0][1]);
            $result->add('title', "{$request->get('targetPartsNo')}��{$res[0][1]}");
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
        ///// �ǽ��������μ���
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '{$request->get('targetDate')} month', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        ///// �����ޤǤμ���
        $query = "SELECT to_char((CAST(to_char({$date}, 'FM00000000') AS date) - interval '{$request->get('targetDateSpan')} month'), 'YYYYMMDD')";
        $this->getUniResult($query, $toDate);
        ///// �ǽ����������鲿����ޤǤ����ʥ��롼�פλ����WHERE�� ����
        $where .= "WHERE long.in_date <= {$date} ";
        $where .= "AND long.in_date >= {$toDate} ";
        ///// ����иˤξ���ղå��ץ��������å�
        if ($request->get('targetOutFlg') == 'on') {
            // ����иˤη������ǯ����������
            $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '{$request->get('targetOutDate')} month', 'YYYYMMDD')";
            $this->getUniResult($query, $outDate);
            switch ($request->get('targetOutCount')) {
            case '0':   // ����ޤ�(ư����̵�����)
                $where .= "AND long.out_date1 < {$outDate} ";
            case '1':   // ����ޤ�
                $where .= "AND long.out_date2 < {$outDate} ";
            case '2':   // ����ޤ�
                $where .= "AND long.out_date3 < {$outDate} ";
            }
        }
        switch ($request->get('targetDivision')) {
        case 'CA':
            $where .= "AND act_payable.div = 'C' AND long.parts_no LIKE 'C%' ";
            break;
        case 'CH':
            $where .= "AND act_payable.div = 'C' AND long.parts_no LIKE 'C%' AND (order_plan.kouji_no NOT LIKE 'SC%' OR order_plan.kouji_no IS NULL)";
            break;
        case 'CS':
            $where .= "AND act_payable.div = 'C' AND long.parts_no LIKE 'C%' AND order_plan.kouji_no LIKE 'SC%' ";
            break;
        case 'LA':
            $where .= "AND act_payable.div = 'L' AND long.parts_no LIKE 'L%' ";
            break;
        /*  �Х���롦ɸ��ζ��̤Ϥ⤦�ʤ��Τǥ����Ȳ�
        case 'LH':
            $where .= "AND act_payable.div = 'L' AND long.parts_no NOT LIKE 'LC%' AND long.parts_no NOT LIKE 'LR%' ";
            break;
        case 'LB':
            $where .= "AND act_payable.div = 'L' AND (long.parts_no LIKE 'LC%' OR long.parts_no LIKE 'LR%') ";
            break;
        */
        case 'TA':  // �ġ���
            $where .= "AND (long.parts_no not LIKE 'C%' AND long.parts_no not LIKE 'L%') ";
            break;
        case 'OT':  // OTHER ����¾ ��������ʬ
            $where .= "AND act_payable.div IS NULL ";
        }
        return $where;
    }
    
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���ORDER�������
    protected function SetInitOrder($request)
    {
        ///// targetSortItem������
        switch ($request->get('targetSortItem')) {
        case 'tana':
            $order = 'ORDER BY long.tnk_tana ASC, long.parts_no ASC';
            break;
        case 'parts':
            $order = 'ORDER BY long.parts_no ASC';
            break;
        case 'name':
            $order = 'ORDER BY long.parts_name ASC';
            break;
        case 'parent':
            $order = 'ORDER BY mepnt ASC';
            break;
        case 'date':
            $order = 'ORDER BY long.in_date ASC';
            break;
        case 'in_pcs':
            $order = 'ORDER BY long.in_pcs DESC';
            break;
        case 'stock':
            $order = 'ORDER BY long.tnk_stock DESC';
            break;
        case 'tanka':
            $order = 'ORDER BY �ǿ�ñ�� DESC';   // long.tanka DESC';�������tannka��NULL�λ��������ͤˤʤäƤ��ޤ�
            break;
        case 'price':
            $order = 'ORDER BY ��� DESC';  // ((long.tnk_stock+long.nk_stock) * long.tanka)�������tanka��NULL�λ��������ͤˤʤäƤ��ޤ�';
            break;
        default:
            $order = 'ORDER BY long.tnk_tana ASC, long.parts_no ASC';
        }
        return $order;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��   Ĺ����α���ʤλ�����Ǥΰ�������
    private function getViewHTMLbody($request, $menu)
    {
        $query = "
            SELECT   CASE
                        WHEN trim(long.tnk_tana) = '' THEN '&nbsp;'
                        ELSE long.tnk_tana
                     END            AS ê�ֹ�           -- 00
                    ,long.parts_no  AS �����ֹ�         -- 01
                    ,trim(substr(long.parts_name, 1, 16))
                                    AS ����̾           -- 02
                    ,CASE
                        WHEN mepnt='' THEN '&nbsp;'
                        WHEN mepnt IS NULL THEN '&nbsp;'
                        ELSE mepnt
                     END            AS �Ƶ���           -- 03
                    ,to_char(long.in_date, 'FM0000/00/00')
                                    AS ������           -- 04
                    ,in_pcs         AS ���˿�           -- 05
                    ,tnk_stock + nk_stock
                                    AS ���߸�           -- 06
                    ,CASE
                        WHEN tanka IS NULL THEN 0
                        ELSE tanka
                     END            AS �ǿ�ñ��         -- 07
                    ,CASE
                        WHEN tanka IS NULL THEN 0
                        ELSE UROUND((tnk_stock + nk_stock) * tanka, 0)
                     END            AS ���             -- 08
                    -------------------------------------------- �ʲ��ϥꥹ�ȳ�
                    , to_char((CAST(to_char(long.in_date, 'FM00000000') AS date) - interval '10 day'), 'YYYYMMDD') -- 10������(�߸˷����10�����򸫤�������)
                                    AS ������parameter  -- 09
                    ,CASE
                        WHEN trim(order_plan.kouji_no) = '' THEN '' -- ����Ϣ�'&nbsp;'
                        WHEN order_plan.kouji_no IS NULL THEN ''    -- ����Ϣ�'&nbsp;'
                        ELSE order_plan.kouji_no
                     END            AS ����             -- 10
                    FROM
                        long_holding_parts_work1 AS long
                    LEFT OUTER JOIN
                        act_payable
                        ON (long.parts_no=act_payable.parts_no AND long.in_date=act_payable.act_date AND long.den_no=act_payable.uke_no)
                        -- Ʊ�������ֹ��Ʊ���ˣ��󸡼����������б���(long.in_pcs=act_payable.genpin)���ɲâ�(long.den_no=act_payable.uke_no)���ѹ�
                    LEFT OUTER JOIN
                        order_plan USING(sei_no)
                    LEFT OUTER JOIN
                        miitem ON (long.parts_no=miitem.mipn)
                    {$this->where}
                    {$this->order}
        ";
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='11' width='100%' align='center' class='winbox'>�������ʤ�����ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
            $this->totalMsg = '&nbsp;';
            $this->csvFlg   = '0';
        } else {
            $this->csvFlg   = '1';
            $this->totalMsg = $this->getSumPrice($rows, $res);
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr onDblClick='LongHoldingParts.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPartsNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='���֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���'>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' ><div class='pt11b'>" . ($i+1) . "</div></td>\n";                    // ���ֹ�
                if ($request->get('targetSortItem') == 'tana') {
                    $listTable .= "        <td class='winbox' width=' 5%' align='center' style='background-color:#ffffc6;'><div class='pt11b'>{$res[$i][0]}</div></td>\n";  // ê��
                } else {
                    $listTable .= "        <td class='winbox' width=' 5%' align='center'><div class='pt11b'>{$res[$i][0]}</div></td>\n";                 // ê��
                }
                if ($request->get('targetSortItem') == 'parts') {
                    $listTable .= "        <td class='winbox' width='11%' align='center' style='background-color:#ffffc6;' title='�����ֹ�򥯥�å�����к߸˷����Ȳ�Ǥ��ޤ���'\n";
                } else {
                    $listTable .= "        <td class='winbox' width='11%' align='center' title='�����ֹ�򥯥�å�����к߸˷����Ȳ�Ǥ��ޤ���'\n";
                }
                $listTable .= "            onClick='LongHoldingParts.win_open(\"" . $menu->out_action('�߸˷���') . "?parts_no=" . urlencode($res[$i][1]) . "&date_low={$res[$i][8]}&view_rec=500&noMenu=yes\", 900, 680)'\n";
                // $listTable .= "            onMouseover='document.body.style.cursor=\"hand\"' onMouseout='document.body.style.cursor=\"auto\"'\n";
                // $listTable .= "        ><span style='color:blue;'>{$res[$i][1]}</span></td>\n";                                 // �����ֹ�
                $listTable .= "        ><a href='javascript:void(0);'><div class='pt11b'>{$res[$i][1]}</div></a></td>\n";                                 // �����ֹ�
                if ($request->get('targetSortItem') == 'name') {
                    $listTable .= "        <td class='winbox' width='16%' align='left' style='background-color:#ffffc6;'><div class='pt11b'>" . mb_convert_kana($res[$i][2], 'k') . "</div></td>\n";   // ����̾
                } else {
                    $listTable .= "        <td class='winbox' width='16%' align='left'><div class='pt11b'>" . mb_convert_kana($res[$i][2], 'k') . "</div></td>\n";   // ����̾
                }
                if ($request->get('targetSortItem') == 'parent') {
                    $listTable .= "        <td class='winbox' width='16%' align='left' style='background-color:#ffffc6;'><div class='pt11b'>" . mb_convert_kana($res[$i][3], 'k') . "</div></td>\n";   // �Ƶ���
                } else {
                    $listTable .= "        <td class='winbox' width='16%' align='left'><div class='pt11b'>" . mb_convert_kana($res[$i][3], 'k') . "</div></td>\n";   // �Ƶ���
                }
                if ($request->get('targetSortItem') == 'date') {
                    $listTable .= "        <td class='winbox' width='11%' align='center' style='background-color:#ffffc6;'><div class='pt11b'>{$res[$i][4]}</div></td>\n";  // ������
                } else {
                    $listTable .= "        <td class='winbox' width='11%' align='center'><div class='pt11b'>{$res[$i][4]}</div></td>\n";                 // ������
                }
                if ($request->get('targetSortItem') == 'in_pcs') {
                    $listTable .= "        <td class='winbox' width=' 8%' align='right' style='background-color:#ffffc6;'><div class='pt11b'>" . number_format($res[$i][5]) . "</div></td>\n"; // ���˿�
                } else {
                    $listTable .= "        <td class='winbox' width=' 8%' align='right'><div class='pt11b'>" . number_format($res[$i][5]) . "</div></td>\n"; // ���˿�
                }
                if ($request->get('targetSortItem') == 'stock') {
                    $listTable .= "        <td class='winbox' width=' 8%' align='right' style='background-color:#ffffc6;'><div class='pt11b'>" . number_format($res[$i][6]) . "</div></td>\n"; // TNK�߸�
                } else {
                    $listTable .= "        <td class='winbox' width=' 8%' align='right'><div class='pt11b'>" . number_format($res[$i][6]) . "</div></td>\n"; // ���߸�
                }
                if ($request->get('targetSortItem') == 'tanka') {
                    $listTable .= "        <td class='winbox' width='10%' align='right' style='background-color:#ffffc6;'><div class='pt11b'>" . number_format($res[$i][7], 2) . "</div></td>\n"; // �ǿ�ñ��
                } else {
                    $listTable .= "        <td class='winbox' width='10%' align='right'><div class='pt11b'>" . number_format($res[$i][7], 2) . "</div></td>\n";     // �ǿ�ñ��
                }
                if ($request->get('targetSortItem') == 'price') {
                    $listTable .= "        <td class='winbox' width='10%' align='right' style='background-color:#ffffc6;'><div class='pt11b'>" . number_format($res[$i][8]) . "</div></td>\n";  // ���
                } else {
                    $listTable .= "        <td class='winbox' width='10%' align='right'><div class='pt11b'>" . number_format($res[$i][8]) . "</div></td>\n";  // ���
                }
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
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
        //if ($_SESSION['User_ID'] == '300144') {
            if ($this->csvFlg == '1') {
                $csv_search = $this->where . $this->order;
                // SQL�Υ������������ܸ��ѻ����ѹ���'�⥨�顼�ˤʤ�Τ�/�˰���ѹ�
                $csv_search = str_replace('�ǿ�ñ��','saitanka',$csv_search);
                $csv_search = str_replace('���','kingaku',$csv_search);
                $csv_search = str_replace('\'','/',$csv_search);
                $listTable .= "<td class='winbox' align='right'><a href='../long_holding_parts_csv.php?csvsearch=$csv_search'>CSV����</a></td>\n";
            }
        //}
        $listTable .= "        <td class='winbox' align='right'>{$this->totalMsg}</td>\n";
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
<title>Ĺ����α����List��</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../long_holding_parts.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:   none;
    background-color:   #d6d3ce;
}
-->
</style>
<script type='text/javascript' src='../long_holding_parts.js'></script>
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
    
    ///// ��׶�ۡ������׻����ƥ�å��������֤���
    private function getSumPrice($rows, $array)
    {
        $sumPrice = 0;     // �����
        for ($i=0; $i<$rows; $i++) {
            $sumPrice += $array[$i][8];
        }
        $sumPrice = number_format($sumPrice);
        return "��׷�� �� {$rows} �� &nbsp;&nbsp;&nbsp&nbsp; ��׶�� �� {$sumPrice}";
    }
    
    
    ///// ��ץ�����Ȥꤢ�������ߡ��᥽�åɤǻĤ���getTargetDateView()��ǻ��Ѥ��Ƥ���
    private function getDummyView($rows, $array)
    {
        $list = "\n";
        ///// ��.0ǯ��
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '1 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>��ǯ��</option>\n";
        } else {
            $list .= "                <option value='{$date}'>��ǯ��</option>\n";
        }
        ///// 1.5ǯ��
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '1.5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>��ǯȾ��</option>\n";
        } else {
            $list .= "                <option value='{$date}'>��ǯȾ��</option>\n";
        }
        ///// 2.0ǯ��
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '2 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>��ǯ��</option>\n";
        } else {
            $list .= "                <option value='{$date}'>��ǯ��</option>\n";
        }
        ///// 2.5ǯ��
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '2.5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>��ǯȾ��</option>\n";
        } else {
            $list .= "                <option value='{$date}'>��ǯȾ��</option>\n";
        }
        ///// 3.0ǯ��
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '3 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>��ǯ��</option>\n";
        } else {
            $list .= "                <option value='{$date}'>��ǯ��</option>\n";
        }
        ///// 3.5ǯ��
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '3.5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>��ǯȾ��</option>\n";
        } else {
            $list .= "                <option value='{$date}'>��ǯȾ��</option>\n";
        }
        ///// 4.0ǯ��
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '4 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>��ǯ��</option>\n";
        } else {
            $list .= "                <option value='{$date}'>��ǯ��</option>\n";
        }
        ///// 4.5ǯ��
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '4.5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>��ǯȾ��</option>\n";
        } else {
            $list .= "                <option value='{$date}'>��ǯȾ��</option>\n";
        }
        ///// 5.0ǯ��
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>��ǯ��</option>\n";
        } else {
            $list .= "                <option value='{$date}'>��ǯ��</option>\n";
        }
        ///// 5.5ǯ��
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '5.5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>��ǯȾ��</option>\n";
        } else {
            $list .= "                <option value='{$date}'>��ǯȾ��</option>\n";
        }
        ///// 6.0ǯ��
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '6 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>��ǯ��</option>\n";
        } else {
            $list .= "                <option value='{$date}'>��ǯ��</option>\n";
        }
        ///// 6.5ǯ��
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '6.5 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>��ǯȾ��</option>\n";
        } else {
            $list .= "                <option value='{$date}'>��ǯȾ��</option>\n";
        }
        ///// 7.0ǯ��
        $query = "SELECT to_char(CURRENT_TIMESTAMP - interval '7 year', 'YYYYMMDD')";
        $this->getUniResult($query, $date);
        if ($date == $request->get('targetDate')) {
            $list .= "                <option value='{$date}' selected>��ǯ��</option>\n";
        } else {
            $list .= "                <option value='{$date}'>��ǯ��</option>\n";
        }
        return $list;
    }

} // Class LongHoldingParts_Model End

?>
