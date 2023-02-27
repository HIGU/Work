<?php
//////////////////////////////////////////////////////////////////////////////
// ���� �߸ˡ�ͭ�����ѿ�(ͽ��߸˿�)�ޥ��ʥ��ꥹ�ȾȲ�         MVC Model �� //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/02 Created   parts_stock_avail_minus_Model.php                   //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../ComTableMntClass.php');     // TNK ������ �ơ��֥����&�ڡ�������Class
// require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*                             base class ���쥯�饹�����                                *
*****************************************************************************************/
class PartsStockAvailMinus_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $order;                             // ���� SQL��ORDER��
    private $totalMsg;                          // �եå�����������׷��
    
    ///// public properties
    // public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        ///// ���ߤν���ä˽���������Ϥʤ���
        $this->where = '';
        $this->order = '';
        $this->totalMsg = '';
    }
    
    ////////// SQLʸ�� WHERE�� ����
    public function setWhere($request)
    {
        $this->where = $this->SetInitWhere($request);
    }
    
    ////////// SQLʸ�� ORDER BY�� ����
    public function setOrder($request)
    {
        $this->order = $this->SetInitOrder($request);
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    ���߸����������оݤ���ͭ�����λ�����Ǥ� ����ɽ
    public function outViewListHTML($request, $menu, $session)
    {
        /************************* �إå��� ***************************/
        // �����HTML�إå��������������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�ܥǥ������������
        $listHTML .= $this->getViewHTMLheader($request, $menu);
        // �����HTML�եå��������������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/parts_stock_avail_minus_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        /************************* �ܥǥ� ***************************/
        // �����HTML�إå��������������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�ܥǥ������������
        $listHTML .= $this->getViewHTMLbody($request, $menu, $session);
        // �����HTML�եå��������������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/parts_stock_avail_minus_ViewListBody-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        /************************* �եå��� ***************************/
        // �����HTML�إå��������������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�ܥǥ������������
        $listHTML .= $this->getViewHTMLfooter();
        // �����HTML�եå��������������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/parts_stock_avail_minus_ViewListFooter-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    ///// ���ʤΥ����Ȥ����
    public function getComment($request, $result)
    {
        // ��̤Ͽ����ͤ��֤�
        return $this->get_comment($request, $result);
    }
    
    ///// ���ʤΥ����Ȥ���¸
    public function setComment($request, $result, $session)
    {
        // ��̤ϥ����ƥ��å����ؽ���
        $this->commentSave($request, $result, $session);
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���WHERE�������
    protected function SetInitWhere($request)
    {
        ///// ����WHERE�������
        if ($request->get('searchPartsNo') != '') {
            $where = "WHERE parts_no LIKE '%{$request->get('searchPartsNo')}%'";
        } else {
            $where = 'WHERE TRUE';
        }
        switch ($request->get('targetDivision')) {
        case 'AL':
            $where .= '';
            break;
        case 'CA':
            $where .= " AND division LIKE 'C%'";
            break;
        case 'CH':
            $where .= " AND division LIKE 'CH%'";
            break;
        case 'CS':
            $where .= " AND division LIKE 'CS%'";
            break;
        case 'LA':
            $where .= " AND division LIKE 'L%'";
            break;
        case 'LL':
            $where .= " AND division LIKE 'LL%'";
            break;
        case 'LB':
            $where .= " AND division LIKE 'LB%'";
            break;
        default:
            $where .= '';
        }
        switch ($request->get('targetMinusItem')) {
        case '1':   // ����
            $where .= '';
            break;
        case '2':   // ���ߺ߸ˤ��ޥ��ʥ�
            $where .= " AND stock < 0";
            break;
        case '3':   // ����κ߸ˤ��ޥ��ʥ�
            $where .= " AND mid_avail_pcs < 0";
            break;
        case '4':   // �ǽ��߸ˤ��ޥ��ʥ�
            $where .= " AND avail_pcs < 0";
            break;
        default:
            $where .= '';
        }
        return $where;
    }
    
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���ORDER�������
    protected function SetInitOrder($request)
    {
        ///// targetSortItem������
        switch ($request->get('targetSortItem')) {
        case 'parts':
            $order = 'ORDER BY �����ֹ� ASC';
            break;
        case 'name':
            $order = 'ORDER BY ����̾ ASC';
            break;
        case 'material':
            $order = 'ORDER BY ��� DESC';
            break;
        case 'parent':
            $order = 'ORDER BY �Ƶ��� DESC';
            break;
        case 'stock':
            $order = 'ORDER BY stock ASC';
            break;
        case 'avail_pcs':
            $order = 'ORDER BY avail_pcs ASC';
            break;
        case 'mid_plan_no':
            $order = 'ORDER BY mid_plan_no ASC';
            break;
        case 'mid_avail_date':
            $order = 'ORDER BY mid_avail_date ASC';
            break;
        case 'mid_avail_pcs':
            $order = 'ORDER BY mid_avail_pcs ASC';
            break;
        case 'TNKCC':
            $order = 'ORDER BY TNKCC DESC';
            break;
        default:
            $order = 'ORDER BY �����ֹ� ASC';
        }
        return $order;
    }
    
    ///// ���ʤΥ����Ȥ����
    protected function get_comment($request, $result)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        if ($request->get('targetPartsNo') == '') return '';
        $query = "
            SELECT  comment  ,
                    trim(substr(midsc, 1, 20))
            FROM miitem LEFT OUTER JOIN
            parts_stock_avail_minus_comment ON(mipn=parts_no)
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
    
    ///// ���ʤΥ����Ȥ���¸
    protected function commentSave($request, $result, $session)
    {
        // �����ȤΥѥ�᡼���������å�(���Ƥϥ����å��Ѥ�)
        // if ($request->get('comment') == '') return;  // �����Ԥ��Ⱥ���Ǥ��ʤ�
        if ($request->get('targetPartsNo') == '') return;
        $last_date = date('Y-m-d H:i:s');
        $last_user = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $_SESSION['User_ID'];
        // ̤��������������������å�
        if ($request->get('targetFactor') == '') {
            $reg_factor = 'NULL';
        } else {
            $reg_factor = $request->get('targetFactor');
        }
        // ��������Υ֥饦�������դ���CR����ʧ��
        $comment = str_replace("\r", '', $request->get('comment'));
        $query = "SELECT comment, factor FROM parts_stock_avail_minus_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
        if ($this->getResult($query, $res) < 1) {
            if ($comment == '' && $request->get('targetFactor') == '') {
                // �ǡ���̵��
                $result->add('AutoClose', 'G_reloadFlg=false; window.close();'); // ��Ͽ�� �ƤΥ���ɤϤ��ʤ���Window��λ
                return;
            }
            $sql = "
                INSERT INTO parts_stock_avail_minus_comment (parts_no, comment, factor, last_date, last_user)
                VALUES ('{$request->get('targetPartsNo')}', '{$comment}', {$reg_factor}, '{$last_date}', '{$last_user}')
            ";
            if ($this->execute_Insert($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�����ֹ桧{$request->get('targetPartsNo')}\\n\\n�װ��ڤӥ����Ȥ���Ͽ������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            } else {
                $_SESSION['s_sysmsg'] = "�����ֹ桧{$request->get('targetPartsNo')}\\n\\n�װ��ڤӥ����Ȥ���Ͽ���ޤ�����";
            }
        } else {
            $saveSQL = "SELECT * FROM parts_stock_avail_minus_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
            if ($comment == '' && $request->get('targetFactor') == '') {
                // �����Ȥ����Ƥ��������ƹ����ξ��ϡ��¥쥳���ɤ���
                $sql = "DELETE FROM parts_stock_avail_minus_comment WHERE parts_no='{$request->get('targetPartsNo')}'";
                if ($this->execute_Delete($sql, $saveSQL) <= 0) {
                    $_SESSION['s_sysmsg'] = "�����ֹ桧{$request->get('targetPartsNo')}\\n\\n�װ��ڤӥ����Ȥκ��������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
                } else {
                    $_SESSION['s_sysmsg'] = "�����ֹ桧{$request->get('targetPartsNo')}\\n\\n�װ��ڤӥ����Ȥ������ޤ�����";
                }
            } elseif ($res[0]['comment'] == $comment && $res[0]['factor'] == $request->get('targetFactor')) {
                // �ѹ�̵��
                $result->add('AutoClose', 'G_reloadFlg=false; window.close();'); // ��Ͽ�� �ƤΥ���ɤϤ��ʤ���Window��λ
                return;
            } else {
                $sql = "
                    UPDATE parts_stock_avail_minus_comment SET comment='{$comment}', factor={$reg_factor},
                    last_date='{$last_date}', last_user='{$last_user}'
                    WHERE parts_no='{$request->get('targetPartsNo')}'
                ";
                if ($this->execute_Update($sql, $saveSQL) <= 0) {
                    $_SESSION['s_sysmsg'] = "�����ֹ桧{$request->get('targetPartsNo')}\\n\\n�װ��ڤӥ����Ȥ��ѹ�������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
                } else {
                    $_SESSION['s_sysmsg'] = "�����ֹ桧{$request->get('targetPartsNo')}\\n\\n�װ��ڤӥ����Ȥ��ѹ����ޤ�����";
                }
            }
        }
        $session->add('regParts', $request->get('targetPartsNo'));  // �ޡ������ڤӥ������Ѥ���Ͽ
        $result->add('AutoClose', 'window.close();'); // ��Ͽ�� Window��λ
        return;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��   ����ɽ�� �إå����� ����
    private function getViewHTMLheader($request, $menu)
    {
        // �����ȹ��ܤ��������
        $item = $this->getSortItemArray($request);
        // HTML�ι�������
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='11%'{$item['parts']} title='�����ֹ�ǡ�����˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=parts' target='_parent' onMouseover=\"status='�����ֹ�ǡ�����˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">�����ֹ�</a></th>\n";
        $listTable .= "        <th class='winbox' width='12%'{$item['name']} title='����̾�ǡ�����˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=name' target='_parent' onMouseover=\"status='����̾�ǡ�����˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">�����ʡ�̾</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'{$item['material']} title='����ǡ��߽�˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=material' target='_parent' onMouseover=\"status='����ǡ��߽�˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">�ࡡ��</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'{$item['parent']} title='�Ƶ���ǡ��߽�˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=parent' target='_parent' onMouseover=\"status='�Ƶ���ǡ��߽�˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">�Ƶ���</a></th>\n";
        $listTable .= "        <th class='winbox' width='10%'{$item['stock']} title='���ߤκ߸˿��ǡ�����˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=stock' target='_parent' onMouseover=\"status='���ߤκ߸˿��ǡ�����˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">���ߺ߸�</a></th>\n";
        $listTable .= "        <th class='winbox' width='10%'{$item['avail_pcs']} title='�ǽ�ͭ����(ͽ��߸˿�)�ǡ��߽�˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=avail_pcs' target='_parent' onMouseover=\"status='�ǽ�ͭ����(ͽ��߸˿�)�ǡ��߽�˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">�ǽ��߸�</a></th>\n";
        $listTable .= "        <th class='winbox' width='10%'{$item['mid_plan_no']} title='����ޥ��ʥ��ηײ��ֹ�ǡ��߽�˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=mid_plan_no' target='_parent' onMouseover=\"status='����ޥ��ʥ��ηײ��ֹ�ǡ��߽�˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">����ײ�</a></th>\n";
        $listTable .= "        <th class='winbox' width='11%'{$item['mid_avail_date']} title='����ޥ��ʥ������դǡ��߽�˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=mid_avail_date' target='_parent' onMouseover=\"status='����ޥ��ʥ������դǡ��߽�˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">��������</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 9%'{$item['mid_avail_pcs']} title='����ޥ��ʥ��κ߸�ͽ����ǡ�����˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=mid_avail_pcs' target='_parent' onMouseover=\"status='����ޥ��ʥ��κ߸�ͽ����ǡ�����˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\">�����</a></th>\n";
        $listTable .= "        <th class='winbox' width=' 4%'{$item['TNKCC']} title='TNKCC�ǡ��߽�˥����Ȥ��ޤ���'>\n";
        $listTable .= "            <a href='{$menu->out_self()}?targetSortItem=TNKCC' target='_parent' onMouseover=\"status='TNKCC�ǡ��߽�˥����Ȥ��ޤ���'; return true;\" onMouseout=\"status='';\" class='factorFont'>CC</a></th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   SQLʸ����
    private function getQueryStatement()
    {
        $query = "
            SELECT   parts_no           AS �����ֹ�         -- 00
                    , CASE
                        WHEN midsc IS NULL THEN '̤��Ͽ'
                        ELSE trim(substr(midsc, 1, 9))
                      END               AS ����̾           -- 01
                    , CASE
                        WHEN mzist = '' THEN '&nbsp;'
                        WHEN mzist IS NULL THEN '&nbsp;'
                        ELSE trim(substr(mzist, 1, 6))
                      END               AS ���             -- 02
                    , CASE
                        WHEN mepnt = '' THEN '&nbsp;'
                        WHEN mepnt IS NULL THEN '&nbsp;'
                        ELSE trim(substr(mepnt, 1, 6))
                      END               AS �Ƶ���           -- 03
                    , to_char(stock, 'FM9,999,999')
                                        AS ���ߺ߸˿�       -- 04
                    , to_char(avail_pcs, 'FM9,999,999')
                                        AS �ǽ�ͭ����       -- 05
                    , CASE
                        WHEN mid_plan_no IS NULL THEN '&nbsp;'
                        WHEN mid_plan_no = '' THEN '&nbsp;'
                        ELSE mid_plan_no
                      END               AS ����ηײ��ֹ�   -- 06
                    , CASE
                        WHEN mid_avail_date IS NULL THEN '&nbsp;'
                        ELSE to_char(mid_avail_date, 'FM9999/99/99')
                      END               AS ���������       -- 07
                    , to_char(mid_avail_pcs, 'FM9,999,999')
                                        AS ����κ߸˿�     -- 08
                    , CASE
                        WHEN miccc IS NULL THEN '&nbsp;'
                        WHEN miccc = '' THEN '&nbsp;'
                        WHEN miccc = 'E' THEN 'TCC'
                        WHEN miccc = 'D' THEN 'CC'
                        ELSE miccc
                      END               AS TNKCC            -- 09
                    ----------------------- �ʲ��ϥꥹ�ȳ� ---------------------
                    , stock                                 -- 10
                    , avail_pcs                             -- 11
                    , mid_avail_pcs                         -- 12
                    FROM
                        parts_stock_avail_minus_table
                    LEFT OUTER JOIN
                        miitem ON (parts_no = mipn)
                    {$this->where}
                    {$this->order}
        ";
        return $query;
    }
    
    ///// List��   �������� �ܥǥ���
    private function getViewHTMLbody($request, $menu, $session)
    {
        // �����ȹ��ܤ��������
        $item = $this->getSortItemArray($request);
        $query = $this->getQueryStatement();
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>�������ʤ�����ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $this->totalMsg = $this->getSumPrice($rows, $res);
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' title='�ֹ�򥯥�å�����������ֹ�򥳥ԡ����ޤ���'>\n";
                $listTable .= "            <a href='javascript:void(0);' onClick='PartsStockAvailMinus.clipCopyValue(\"{$res[$i][0]}\");'>" . ($i+1) . "</a></td>\n";// ���ֹ�
                $listTable .= "        <td class='winbox' width='11%' align='center'{$item['parts']} title='�����ֹ�򥯥�å�����к߸�ͽ�������Ȳ�Ǥ��ޤ���'\n";
                $listTable .= "            onClick='PartsStockAvailMinus.win_open(\"" . $menu->out_action('�߸�ͽ��') . "?targetPartsNo=" . urlencode($res[$i][0]) . "&noMenu=yes\", 900, 680)'\n";
                $listTable .= "        ><a href='javascript:void(0);'>{$res[$i][0]}</a></td>\n";                                                        // �����ֹ�
                $listTable .= "        <td class='winbox' width='12%' align='left'{$item['name']}>" . mb_substr(mb_convert_kana($res[$i][1], 'ksa'), 0, 9) . "</td>\n";//����̾
                $listTable .= "        <td class='winbox' width=' 9%' align='left'{$item['material']}>" . mb_substr(mb_convert_kana($res[$i][2], 'ksa'), 0, 6) . "</td>\n";// ���
                $listTable .= "        <td class='winbox' width=' 9%' align='left'{$item['parent']}>" . mb_substr(mb_convert_kana($res[$i][3], 'ksa'), 0, 6) . "</td>\n";// �Ƶ���
                $listTable .= "        <td class='winbox' width='10%' align='right'{$item['stock']}>{$res[$i][4]}</td>\n";                              // ���ߺ߸˿�
                $listTable .= "        <td class='winbox' width='10%' align='right'{$item['avail_pcs']}>{$res[$i][5]}</td>\n";                          // �ǽ�ͽ��߸˿�
                $listTable .= "        <td class='winbox' width='10%' align='right'{$item['mid_plan_no']}>{$res[$i][6]}</td>\n";                        // ����ηײ��ֹ�
                $listTable .= "        <td class='winbox' width='11%' align='right'{$item['mid_avail_date']}>{$res[$i][7]}</td>\n";                     // ���������
                $listTable .= "        <td class='winbox' width=' 9%' align='right'{$item['mid_avail_pcs']}>{$res[$i][8]}</td>\n";                      // ����Υޥ��ʥ��߸˿�
                $listTable .= "        <td class='winbox factorFont' width=' 4%' align='left'{$item['TNKCC']}>{$res[$i][9]}</td>\n";                   // TNKCC
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   �������� �եå�����
    private function getViewHTMLfooter()
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' align='right'>{$this->totalMsg}</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// �����ȥ����ƥ��������֤�
    private function getSortItemArray($request)
    {
        // �����
        $itemArray = array('parts' => '', 'name' => '', 'material' => '', 'parent' => '', 'stock' => '',
            'avail_pcs' => '', 'mid_plan_no' => '', 'mid_avail_date' => '', 'mid_avail_pcs' => '', 'TNKCC' => '');
        // �ꥯ�����Ȥˤ�꥽���ȹ��ܤ˿��դ�
        switch ($request->get('targetSortItem')) {
        case 'parts':
            $itemArray['parts'] = " style='background-color:#ffffc6;'";
            break;
        case 'name':
            $itemArray['name'] = " style='background-color:#ffffc6;'";
            break;
        case 'material':
            $itemArray['material'] = " style='background-color:#ffffc6;'";
            break;
        case 'parent':
            $itemArray['parent'] = " style='background-color:#ffffc6;'";
            break;
        case 'stock':
            $itemArray['stock'] = " style='background-color:#ffffc6;'";
            break;
        case 'avail_pcs':
            $itemArray['avail_pcs'] = " style='background-color:#ffffc6;'";
            break;
        case 'mid_plan_no':
            $itemArray['mid_plan_no'] = " style='background-color:#ffffc6;'";
            break;
        case 'mid_avail_date':
            $itemArray['mid_avail_date'] = " style='background-color:#ffffc6;'";
            break;
        case 'mid_avail_pcs':
            $itemArray['mid_avail_pcs'] = " style='background-color:#ffffc6;'";
            break;
        case 'TNKCC':
            $itemArray['TNKCC'] = " style='background-color:#ffffc6;'";
            break;
        }
        return $itemArray;
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
<link rel='stylesheet' href='../parts_stock_avail_minus.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:   none;
    background-color:   #d6d3ce;
}
-->
</style>
<script type='text/javascript' src='../parts_stock_avail_minus.js'></script>
</head>
<body
    onLoad='if (parent.document.ConditionForm.searchPartsNo) parent.document.ConditionForm.searchPartsNo.focus();'
>
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
        $stock = 0;     // �����
        $avail = 0;
        $mid   = 0;
        for ($i=0; $i<$rows; $i++) {
            if ($array[$i][10] < 0) $stock++;
            if ($array[$i][11] < 0) $avail++;
            if ($array[$i][12] < 0) $mid++;
        }
        $stock = number_format($stock);
        $avail = number_format($avail);
        $mid   = number_format($mid);
        return "��׷����{$rows}�� &nbsp;&nbsp; ���ߺ߸˥ޥ��ʥ���{$stock}�� &nbsp;&nbsp; �ǽ�ͽ��߸˥ޥ��ʥ���{$avail}�� &nbsp;&nbsp; ����߸˥ޥ��ʥ���{$mid}��";
    }
    

} // Class PartsStockAvailMinus_Model End

?>
