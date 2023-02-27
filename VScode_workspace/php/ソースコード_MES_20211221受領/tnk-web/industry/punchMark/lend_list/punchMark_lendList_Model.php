<?php
//////////////////////////////////////////////////////////////////////////////
// ������������ƥ� �߽���Ģ��˥塼                         MVC Model ��   //
// Copyright (C) 2007-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/16 Created   punchMark_lendList_Model.php                        //
// 2007/11/20 ���������ɽ����ˡ�ѹ�Ʊ�����ʤ�ɽ����֥��                //
// 2007/11/26 �߽Хե�����Υǡ���������getLend()�᥽�åɤ��ɲ�             //
// 2007/11/30 win_open()�˥�����ɥ�̾���ɲ� LendRegist setLendBody()���ɲ� //
// 2007/12/03 setReturn(), setReturnCancel(), setLendCancel() ���ɲ�        //
// 2007/12/04 �߽Ф��ǡ�������Ͽ��ä� U �� D �إߥ�����                    //
// 2007/12/05 �߽�ɼ�ΰ��� lendPrint()�᥽�åɤ��ɲ�                        //
// 2007/12/20 targetPartsNo��urlencode()��ȴ���Ƥ����Τ��ɲ�                //
// 2008/09/03 Ʊ�������ֹ�Ǥ�㤦���Ƥι����¸�ߤ����                    //
//            ��������ɰʲ���ɽ������褦���ѹ�                       ��ë //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹
require_once ('../master/punchMark_MasterFunction.php');// ������������ƥඦ�̥ޥ������ؿ�


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class PunchMarkLendList_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $order;                             // ���� SQL��ORDER��
    private $offset;                            // ���� SQL��OFFSET��
    private $limit;                             // ���� SQL��LIMIT��
    private $sql;                               // ���� SQLʸ
    
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
        $this->offset = 'OFFSET 0';
        $this->limit  = 'LIMIT 500';
        $this->sql    = '';
    }
    
    ///// SQL��WHERE�������
    public function setLendWhere($session)
    {
        $this->where = $this->setLendWhereBody($session);
    }
    
    ///// SQL��ORDER�������
    public function setLendOrder($session)
    {
        $this->order = $this->setLendOrderBody($session);
    }
    
    ///// SQLʸ������
    public function setLendSQL($session)
    {
        $this->sql = $this->setLendSQLbody($session);
    }
    
    ///// SQL��WHERE�������
    public function setMarkWhere($session)
    {
        $this->where = $this->setMarkWhereBody($session);
    }
    
    ///// SQL��ORDER�������
    public function setMarkOrder($session)
    {
        $this->order = $this->setMarkOrderBody($session);
    }
    
    ///// SQLʸ������
    public function setMarkSQL($session)
    {
        $this->sql = $this->setMarkSQLbody($session);
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    �߽���Ģ������ ����ɽ
    public function outViewLendListHTML($session, $menu)
    {
                /***** �إå���������� *****/
        /*****************
        $this->outViewHTMLheader($session, $menu);
        *****************/
        
                /***** ��ʸ����� *****/
        $this->outViewLendHTMLbody($session, $menu);
        
                /***** �եå���������� *****/
        /************************
        $this->outViewHTMLfooter($session, $menu);
        ************************/
        return ;
    }
    
    ///// List��    ���������̤����� ����ɽ
    public function outViewMarkListHTML($session, $menu)
    {
                /***** �إå���������� *****/
        /*****************
        $this->outViewHTMLheader($session, $menu);
        *****************/
        
                /***** ��ʸ����� *****/
        $this->outViewMarkHTMLbody($session, $menu);
        
                /***** �եå���������� *****/
        /************************
        $this->outViewHTMLfooter($session, $menu);
        ************************/
        return ;
    }
    
    ///// �߽Хե�����Υǡ�������
    public function getLend($session, $result)
    {
        $this->getLendBody($session, $result);
    }
    
    ///// �߽Хǡ�������Ͽ�¹�
    public function setLend($session)
    {
        $this->setLendBody($session);
    }
    
    ///// �߽Хǡ�������Ͽ ���
    public function setLendCancel($session)
    {
        $this->setLendCancelBody($session);
    }
    
    ///// �ֵѥǡ�������Ͽ�¹�
    public function setReturn($session)
    {
        $this->setReturnBody($session);
    }
    
    ///// �ֵѥǡ�������Ͽ ���
    public function setReturnCancel($session)
    {
        $this->setReturnCancelBody($session);
    }
    
    ///// ����߽�ɼ�ΰ���
    public function lendPrint($menu, $session)
    {
        $this->lendPrintBody($menu, $session);
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����Ȥˤ��SQLʸ��WHERE�������
    protected function setLendWhereBody($session)
    {
        $where = '';
        if ($session->get_local('targetPartsNo') != '') {
            $where .= "WHERE parts_no LIKE '%{$session->get_local('targetPartsNo')}%'";
        }
        if ($session->get_local('targetMarkCode') != '' && $where != '') {
            $where .= " AND punchmark_code LIKE '%{$session->get_local('targetMarkCode')}%'";
        } elseif ($session->get_local('targetMarkCode') != '') {
            $where .= "WHERE punchmark_code LIKE '%{$session->get_local('targetMarkCode')}%'";
        }
        if ($session->get_local('targetShelfNo') != '' && $where != '') {
            $where .= " AND shelf_no LIKE '%{$session->get_local('targetShelfNo')}%'";
        } elseif ($session->get_local('targetShelfNo') != '') {
            $where .= "WHERE shelf_no LIKE '%{$session->get_local('targetShelfNo')}%'";
        }
        if ($session->get_local('targetNote') != '' && $where != '') {
            $where .= " AND note LIKE '%{$session->get_local('targetNote')}%'";
        } elseif ($session->get_local('targetNote') != '') {
            $where .= "WHERE note LIKE '%{$session->get_local('targetNote')}%'";
        }
        return $where;
    }
    
    protected function setMarkWhereBody($session)
    {
        $where = '';
        if ($session->get_local('targetPartsNo') != '') {
            $where .= "WHERE parts_no LIKE '%{$session->get_local('targetPartsNo')}%'";
        }
        if ($session->get_local('targetMarkCode') != '' && $where != '') {
            $where .= " AND punchmark_code LIKE '%{$session->get_local('targetMarkCode')}%'";
        } elseif ($session->get_local('targetMarkCode') != '') {
            $where .= "WHERE punchmark_code LIKE '%{$session->get_local('targetMarkCode')}%'";
        }
        if ($session->get_local('targetShelfNo') != '' && $where != '') {
            $where .= " AND shelf_no LIKE '%{$session->get_local('targetShelfNo')}%'";
        } elseif ($session->get_local('targetShelfNo') != '') {
            $where .= "WHERE shelf_no LIKE '%{$session->get_local('targetShelfNo')}%'";
        }
        if ($session->get_local('targetNote') != '' && $where != '') {
            $where .= " AND note LIKE '%{$session->get_local('targetNote')}%'";
        } elseif ($session->get_local('targetNote') != '') {
            $where .= "WHERE note LIKE '%{$session->get_local('targetNote')}%'";
        }
        return $where;
    }
    
    protected function setLendOrderBody($session)
    {
        return 'ORDER BY lend_date DESC';
    }
    
    protected function setMarkOrderBody($session)
    {
        return 'ORDER BY parts_no ASC';
    }
    
    ///// ���٤ν��� �إå�����
    protected function outViewHTMLheader($session, $menu)
    {
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $headHTML .= $this->getViewHTMLheader();
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/punchMark_lendList_ViewListHeader-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    ///// �߽���Ģ ���٤ν��� �ܥǥ�����
    protected function outViewLendHTMLbody($session, $menu)
    {
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $listHTML .= $this->getViewLendHTMLbody($session, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/punchMark_lendList_ViewList-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    ///// ���������� ���٤ν��� �ܥǥ�����
    protected function outViewMarkHTMLbody($session, $menu)
    {
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $listHTML .= $this->getViewMarkHTMLbody($session, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/punchMark_markList_ViewList-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    ///// ���٤ν��� �եå�����
    protected function outViewHTMLfooter($session, $menu)
    {
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $footHTML .= $this->getViewHTMLfooter();
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/punchMark_lendList_ViewListFooter-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��   ����ɽ��SQL���ơ��ȥ�������
    // �߽���Ģ�� SQL
    private function setLendSQLbody($session)
    {
        $query = "
            SELECT
                -- COALESCE(pre_data, '&nbsp;')
                punchmark_code                      AS ���������   -- 0
                ,
                shelf_no                            AS ê��         -- 1
                ,
                to_char(lend_date, 'YY/MM/DD HH24:MI:SS')
                                                    AS �߽�����     -- 2
                ,
                COALESCE(
                    to_char(return_date, 'YY/MM/DD HH24:MI:SS'), '�߽���'
                )                                   AS �ֵ�����     -- 3
                ,
                (SELECT substr(name, 1, 6) FROM vendor_master WHERE vendor = lend_vendor)
                                                    AS �߽���       -- 4
                ,
                (SELECT name FROM user_detailes WHERE uid = substr(lend_user, 1, 6))
                                                    AS �߽м�       -- 5
                ,
                parts_no                            AS �����о����� -- 6
                ,
                CASE
                    WHEN lend.note = '' THEN '&nbsp;'
                    ELSE lend.note
                END                                 AS ����         -- 7
                ---------------- �ʲ��ϥꥹ�ȳ� ------------------
                ,
                to_char(regdate, 'YYYY/MM/DD HH24:MI')
                                                    AS ��������     -- 8
                ,
                to_char(last_date, 'YYYY/MM/DD HH24:MI')
                                                    AS �ѹ�����     -- 9
                ,
                last_user                           AS �ѹ�����     -- 10
            FROM
                punchMark_lend_list  AS lend
            {$this->where}
            {$this->order}
            {$this->offset}
            {$this->limit}
        ";
        return $query;
    }
    
    // ���ʥޥ�����������ޥ������� SQL
    private function setMarkSQLbody($session)
    {
        $query = "
            SELECT
                mark.parts_no               AS �����ֹ�     -- 0
                ,
                (SELECT substr(midsc, 1, 10) FROM miitem WHERE mipn=CAST(parts_no AS CHAR(9)) LIMIT 1)
                                            AS ����̾       -- 1
                ,
                shelf_no                    AS ê��         -- 2
                ,
                mark.punchMark_code         AS ���������   -- 3
                ,
                mark                        AS �������     -- 4
                ,
                shape_name                  AS ����̾       -- 5
                ,
                size_name                   AS ������̾     -- 6
                ,
                CASE
                    WHEN mark.note = '' THEN '&nbsp;'
                    ELSE mark.note
                END                         AS ����         -- 7
                ,
                CASE
                    WHEN lend_flg IS TRUE THEN '�߽���'
                    ELSE ''
                END                         AS �߽о���     -- 8
            FROM
                punchMark_parts_master AS mark
            LEFT OUTER JOIN
                punchMark_master USING (punchmark_code)
            LEFT OUTER JOIN
                punchMark_shape_master USING (shape_code)
            LEFT OUTER JOIN
                punchMark_size_master USING (size_code)
            {$this->where}
            {$this->order}
            {$this->offset}
            {$this->limit}
        ";
        return $query;
    }
    
    ///// List��   �߽���Ģ�� ���٥ǡ�������
    private function getViewLendHTMLbody($session, $menu)
    {
        if ($this->sql == '') exit();
        $res = array();
        if ( ($rows=$this->getResult2($this->sql, $res)) <= 0) {
            // $session->add('s_sysmsg', '�߽���Ģ�Υǡ���������ޤ���');
        }
        
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>�߽���Ģ�Υǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' >\n";                       // ���ֹ�
                if ($res[$i][3] == '�߽���' && substr($res[$i][2], 0, 8) == date('y/m/d')) {
                    $lend_url = "{$menu->out_self()}?Action=LendCancel&showMenu=CondForm&targetMarkCode=" . urlencode($res[$i][0]) . "&targetShelfNo=" . urlencode($res[$i][1]) . "&targetLendDate=20{$res[$i][2]}&AutoStart=MarkList";
                    $listTable .= "        <a href='{$lend_url}' target='_parent'>���</a>\n";
                } else {
                    $listTable .= "        " . ($i+1) . "\n";
                }
                $listTable .= "        </td>\n";
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][0]}</td>\n";     // ���������
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][1]}</td>\n";     // ê��
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][2]}</td>\n";     // �߽�����
                $listTable .= "        <td class='winbox' width='10%' align='center'>\n";                       // �ֵ�����
                if ($res[$i][3] == '�߽���') {
                    $lend_url = "{$menu->out_self()}?Action=Return&showMenu=CondForm&targetMarkCode=" . urlencode($res[$i][0]) . "&targetShelfNo=" . urlencode($res[$i][1]) . "&targetLendDate=20{$res[$i][2]}&AutoStart=LendList";
                    $listTable .= "        <a href='{$lend_url}' target='_parent'>{$res[$i][3]}</a>\n";
                    $lend_url = "{$menu->out_self()}?Action=noAction&showMenu=LendPrint&targetMarkCode=" . urlencode($res[$i][0]) . "&targetShelfNo=" . urlencode($res[$i][1]) . "&targetLendDate=20{$res[$i][2]}&AutoStart=LendList";
                    $listTable .= "        <a href='{$lend_url}' target='_parent'>������</a>\n";
                } elseif (substr($res[$i][3], 0, 8) == date('y/m/d')) {
                    $lend_url = "{$menu->out_self()}?Action=ReturnCancel&showMenu=CondForm&targetMarkCode=" . urlencode($res[$i][0]) . "&targetShelfNo=" . urlencode($res[$i][1]) . "&targetLendDate=20{$res[$i][2]}&AutoStart=LendList";
                    $listTable .= "        <a href='{$lend_url}' target='_parent'>�衡��</a>" . substr($res[$i][3], 8) . "\n";
                } else {
                    $listTable .= "        {$res[$i][3]}\n";
                }
                $listTable .= "        </td>\n";
                $listTable .= "        <td class='winbox' width='13%' align='left'  >{$res[$i][4]}</td>\n";     // �߽���
                $listTable .= "        <td class='winbox' width='10%' align='left'  >{$res[$i][5]}</td>\n";     // �߽м�
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][6]}</td>\n";     // ��������
                $listTable .= "        <td class='winbox' width='20%' align='left'  >{$res[$i][7]}</td>\n";     // ����
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��   �߽���Ģ�� ���٥ǡ�������
    private function getViewMarkHTMLbody($session, $menu)
    {
        if ($this->sql == '') exit();
        $res = array();
        if ( ($rows=$this->getResult2($this->sql, $res)) <= 0) {
            // $session->add('s_sysmsg', '�ǡ��������Ĥ���ޤ���');
        }
        
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>�ǡ��������Ĥ���ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $res[-1][0] = '';   // ���ߡ������
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right'>\n";
                if ($res[$i][8] == '�߽���') {
                    $listTable .= "            " . ($i+1) . "\n";
                    $addMsg = "<span style='color:red;'>{$res[$i][8]}</span>";
                } else {
                    $listTable .= "            <a href='javascript:PunchMarkLendList.win_open(\"{$menu->out_self()}?Action=LendRegist&showMenu=LendRegistForm&targetPartsNo=" . urlencode($res[$i][0]) . "&targetMarkCode={$res[$i][3]}&targetShelfNo={$res[$i][2]}\", 500, 600, \"LendRegist\");'>" . ($i+1) . "</a>\n";
                    $addMsg = '';
                }
                $listTable .= "         </td>\n";    // ���ֹ�
                if ($res[$i-1][0] == $res[$i][0]) {
                    $listTable .= "        <td class='winbox' width='11%' align='center'>&nbsp;</td>\n";        // �����ֹ�
                    $listTable .= "        <td class='winbox' width='18%' align='left'  >&nbsp;</td>\n";        // ����̾
                } else {
                    $listTable .= "        <td class='winbox' width='11%' align='center'>{$res[$i][0]}</td>\n"; // �����ֹ�
                    $listTable .= "        <td class='winbox' width='18%' align='left'  >{$res[$i][1]}</td>\n"; // ����̾
                }
                $listTable .= "        <td class='winbox' width=' 8%' align='center'>{$res[$i][2]}</td>\n";     // ê��
                //// Ʊ�������ֹ�Ǥ�㤦���Ƥι����¸�ߤ���١���������ɰʲ���ɽ������褦���ѹ�
                //if ($res[$i-1][0] == $res[$i][0]) {
                //    $listTable .= "        <td class='winbox' width='10%' align='center'>&nbsp;</td>\n";        // ���������
                //    $listTable .= "        <td class='winbox' width='10%' align='center'>&nbsp;</td>\n";        // �������
                //    $listTable .= "        <td class='winbox' width=' 6%' align='center'>&nbsp;</td>\n";        // ����̾
                //    $listTable .= "        <td class='winbox' width=' 6%' align='center'>&nbsp;</td>\n";        // ������
                //    $listTable .= "        <td class='winbox' width='26%' align='left'  >{$addMsg}&nbsp;</td>\n";// ����
                //} else {
                    $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][3]}</td>\n"; // ���������
                    $tmpView = str_replace("\r", '<br>', $res[$i][4]);
                    $listTable .= "        <td class='winbox' width='14%' align='center'>{$tmpView}</td>\n";    // �������
                    $listTable .= "        <td class='winbox' width=' 6%' align='center'>{$res[$i][5]}</td>\n"; // ����̾
                    $listTable .= "        <td class='winbox' width=' 6%' align='center'>{$res[$i][6]}</td>\n"; // ������
                    $listTable .= "        <td class='winbox' width='22%' align='left'  >{$addMsg}{$res[$i][7]}</td>\n";// ����
                //}
                $listTable .= "    </tr>\n";
            }
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
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>���������</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>ê����</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>�߽�����</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>�ֵ�����</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>�߽���</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>�߽м�</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>��������</th>\n";
        $listTable .= "        <th class='winbox' width='25%'>������</th>\n";
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
        $listTable .= "        <td class='winbox' width='79%' align='right'>��׷��</td>\n";
        $listTable .= "        <td class='winbox' width=' 9%' align='right'>{$this->sumCount}</td>\n";
        $listTable .= "        <td class='winbox' width='12%' align='right'>&nbsp;</td>\n";
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
<title>������������ƥ��߽���Ģ</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../punchMark_lendList.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../punchMark_lendList.js'></script>
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
    
    
    ///// �߽Хե�����Υǡ�������
    private function getLendBody($session, $result)
    {
        $execFlg = '';
        // �߽����μ���
        $result->add('LendDate', date('Y/m/d H:i:s'));
        // �߽���μ���
        if ($session->get_local('targetVendor') != '') {
            $query = "SELECT name FROM vendor_master WHERE vendor = '{$session->get_local('targetVendor')}'";
            $vendorName = '�ޥ�����̤��Ͽ';
            if ($this->getUniResult($query, $vendorName) < 1) {
                $execFlg = ' disabled';
            }
            $result->add('vendorName', $vendorName);
        } else {
            $result->add('vendorName', '&nbsp;');
            $execFlg = ' disabled';
        }
        // ô���Ԥμ���
        if ($session->get_local('targetLendUser') != '') {
            $query = "SELECT name FROM user_detailes WHERE uid = '{$session->get_local('targetLendUser')}'";
            $result->add('LendUser', $session->get_local('targetLendUser'));
        } else {
            $query = "SELECT name FROM user_detailes WHERE uid = '{$session->get('User_ID')}'";
            $result->add('LendUser', $session->get('User_ID'));
        }
        $userName = '�ޥ�����̤��Ͽ';
        if ($this->getUniResult($query, $userName) < 1) {
            $execFlg = ' disabled';
        }
        $result->add('userName', $userName);
        // ��������̾�μ���
        $query = "SELECT midsc FROM miitem WHERE mipn = '{$session->get_local('targetPartsNo')}'";
        $partsName = '�ޥ�����̤��Ͽ';
        if ($this->getUniResult($query, $partsName) < 1) {
            $execFlg = ' disabled';
        }
        $result->add('partsName', $partsName);
        // ������ơ��������������μ���
        $query = "
            SELECT mark, shape_name, size_name
            FROM punchmark_master
            LEFT OUTER JOIN punchmark_shape_master USING (shape_code)
            LEFT OUTER JOIN punchmark_size_master  USING (size_code)
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}'
            AND shelf_no = '{$session->get_local('targetShelfNo')}' LIMIT 1
        ";
        $mark  = '�ޥ�����̤��Ͽ';
        $shape = '�ޥ�����̤��Ͽ';
        $size  = '�ޥ�����̤��Ͽ';
        if ($this->getResult2($query, $res) > 0) {
            $mark  = $res[0][0];
            $shape = $res[0][1];
            $size  = $res[0][2];
        } else {
            $execFlg = ' disabled';
        }
        $result->add('Mark',  $mark);
        $result->add('Shape', $shape);
        $result->add('Size',  $size);
        $result->add('execFlg', $execFlg);
    }
    
    ///// �߽Хǡ�������Ͽ�¹�
    private function setLendBody($session)
    {
        $user = $session->get('User_ID') . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $query = "
            INSERT INTO punchmark_lend_list
            (punchmark_code, shelf_no, lend_date, lend_vendor, lend_user, parts_no, note, last_user)
            VALUES ('{$session->get_local('targetMarkCode')}', '{$session->get_local('targetShelfNo')}'
                , now(), '{$session->get_local('targetVendor')}', '{$session->get_local('targetLendUser')}'
                , '{$session->get_local('targetPartsNo')}', '{$session->get_local('targetNote')}', '{$user}'
            )
            ;
            UPDATE punchmark_master SET lend_flg = TRUE
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}'
        ";
        if ($this->query_affected($query) > 0) {
            $session->add('s_sysmsg', "��������ɡ�{$session->get_local('targetMarkCode')} ���߽Ф��ޤ�����");
            // ��Ģ�������¸
            setEditHistory('punchMark_lend_list', 'I', $query);
        } else {
            $session->add('s_sysmsg', "��������ɡ�{$session->get_local('targetMarkCode')} ���߽���Ͽ�˼��Ԥ��ޤ�����");
        }
    }
    
    ///// �߽Хǡ�������Ͽ ���
    private function setLendCancelBody($session)
    {
        $user = $session->get('User_ID') . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $query = "
            SELECT * FROM punchmark_lend_list
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
        ";
        $old_data = getPreDataRows($query);
        $query = "
            DELETE FROM punchmark_lend_list
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
            ;
            UPDATE punchmark_master SET lend_flg = FALSE
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}'
        ";
        if ($this->query_affected($query) > 0) {
            $session->add('s_sysmsg', "��������ɡ�{$session->get_local('targetMarkCode')} ���߽Ф��äޤ�����");
            // ��Ģ�������¸
            setEditHistory('punchMark_lend_list', 'D', $query, $old_data);
        } else {
            $session->add('s_sysmsg', "��������ɡ�{$session->get_local('targetMarkCode')} ���߽м�ä˼��Ԥ��ޤ�����");
        }
    }
    
    ///// �ֵѥǡ�������Ͽ�¹�
    private function setReturnBody($session)
    {
        $user = $session->get('User_ID') . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $query = "
            SELECT * FROM punchmark_lend_list
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
        ";
        $old_data = getPreDataRows($query);
        $query = "
            UPDATE punchmark_lend_list SET return_date = now(), last_date = now(), last_user = '{$user}'
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
            ;
            UPDATE punchmark_master SET lend_flg = FALSE
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}'
        ";
        if ($this->query_affected($query) > 0) {
            $session->add('s_sysmsg', "��������ɡ�{$session->get_local('targetMarkCode')} ���ֵѤ��ޤ�����");
            // ��Ģ�������¸
            setEditHistory('punchMark_lend_list', 'U', $query, $old_data);
        } else {
            $session->add('s_sysmsg', "��������ɡ�{$session->get_local('targetMarkCode')} ���ֵ���Ͽ�˼��Ԥ��ޤ�����");
        }
    }
    
    ///// �ֵѥǡ�������Ͽ ���
    private function setReturnCancelBody($session)
    {
        $user = $session->get('User_ID') . ' ' . $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']);
        $query = "
            SELECT * FROM punchmark_lend_list
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
        ";
        $old_data = getPreDataRows($query);
        $query = "
            UPDATE punchmark_lend_list SET return_date = NULL, last_date = now(), last_user = '{$user}'
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
            ;
            UPDATE punchmark_master SET lend_flg = TRUE
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}'
        ";
        if ($this->query_affected($query) > 0) {
            $session->add('s_sysmsg', "��������ɡ�{$session->get_local('targetMarkCode')} ���ֵѤ��ä��ޤ�����");
            // ��Ģ�������¸
            setEditHistory('punchMark_lend_list', 'U', $query, $old_data);
        } else {
            $session->add('s_sysmsg', "��������ɡ�{$session->get_local('targetMarkCode')} ���ֵѼ�ä˼��Ԥ��ޤ�����");
        }
    }
    
    ///// ����߽�ɼ�ΰ��� ����
    private function lendPrintBody($menu, $session)
    {
        $baseName = basename($_SERVER['SCRIPT_NAME'], '.php');
        // if(!extension_loaded('simplate')) { dl('simplate.so'); }
        $smarty = new simplate();
        $this->getLendPrintData($session, $smarty);
        $output  = '<?xml version="1.0" encoding="EUC-JP"?>' . "\n";
        $output .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">' . "\n";
            // ��ư�����򤹤���� �ץ�ӥ塼 �� ��ư���� ���ѹ�
        $output .= "<pxd name='�ץ�ӥ塼' title='{$menu->out_title()} ����߽�ɼ' paper-type='B6' paper-name='B6-���åȻ�' orientation='portrait' delete='yes' save='no' print='yes' tool-fullscreen='no'>\n";
        $output .= "<page>\n";
        $output .= "<chapter name='���ڡ���' id='1' parent='' />\n";
        $output .= $smarty->fetch('����߽�ɼ.tpl');
        $output .= "</page>\n";
        $output .= "</pxd>\n";
        header('Content-type: application/pxd;');
        header("Content-Disposition:inline;filename=\"{$baseName}.pxd\"");
        echo $output;
    }
    
    ///// ����߽�ɼ�ΰ����ѥǡ�������
    private function getLendPrintData($session, $smarty)
    {
        $query = "
            SELECT
                lend_date       -- 0
                ,
                lend_vendor     -- 1
                ,
                (SELECT substr(name, 1, 10) FROM vendor_master WHERE vendor = lend_vendor LIMIT 1) -- 2
                ,
                lend_user       -- 3
                ,
                (SELECT name FROM user_detailes WHERE uid = substr(lend_user, 1, 6) LIMIT 1) -- 4
                ,
                parts_no        -- 5
                ,
                (SELECT substr(midsc, 1, 12) FROM miitem WHERE mipn = parts_no LIMIT 1) -- 6
                ,
                shelf_no        -- 7
                ,
                punchmark_code  -- 8
                ,
                mark            -- 9
                ,
                shape_name      -- 10
                ,
                size_name       -- 11
                ,
                substr(lend.note, 1, 15) -- 12
            FROM
                punchMark_lend_list AS lend
            LEFT OUTER JOIN
                punchMark_master    AS master USING (punchmark_code, shelf_no)
            LEFT OUTER JOIN
                punchMark_shape_master  AS shape USING (shape_code)
            LEFT OUTER JOIN
                punchMark_size_master   AS size USING (size_code)
            WHERE punchmark_code = '{$session->get_local('targetMarkCode')}' AND shelf_no = '{$session->get_local('targetShelfNo')}' AND lend_date = '{$session->get_local('targetLendDate')}'
        ";
        $res = array();
        if ($this->getResult2($query, $res) < 1) {
            $session->add('s_sysmsg', '����߽�ɼ�Υǡ��������˼��Ԥ��ޤ�������ô���Ԥ�Ϣ���Ʋ�������');
        } else {
            $smarty->assign('date', $res[0][0]);
            $smarty->assign('vendor', $res[0][1]);
            $smarty->assign('vendorName', $res[0][2]);
            $smarty->assign('user', $res[0][4]);
            $smarty->assign('partsNo', $res[0][5]);
            $smarty->assign('partsName', $res[0][6]);
            $smarty->assign('shelfNo', $res[0][7]);
            $smarty->assign('punchMarkCode', $res[0][8]);
            $smarty->assign('mark', $res[0][9]);
            $smarty->assign('shape', $res[0][10]);
            $smarty->assign('size', $res[0][11]);
            $smarty->assign('note', $res[0][12]);
            // $session->add('s_sysmsg', '�ǡ����μ����ϣ� '.$res[0][6]);
        }
    }
    
} // Class PunchMarkLendList_Model End

?>
