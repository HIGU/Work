<?php
//////////////////////////////////////////////////////////////////////////////
// ��ԡ�������ȯ��ν��� ��� �Ȳ�                          MVC Model ��   //
// Copyright (C) 2007-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/12/19 Created   total_repeat_order_Model.php                        //
// 2007/12/20 setWhereBody()����ʸ��������ʪ���оݤˤ�������ɲ�          //
//            ���٥���å��ǳƹ������٤ξȲ���ɲ� Details���������      //
// 2008/07/30 �ʾڰ����Ĺ����ˤ��BODY���˿Ƶ�����ɲ�               ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
if (_TNK_DEBUG) access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class TotalRepeatOrder_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $order;                             // ���� SQL��ORDER��
    private $offset;                            // ���� SQL��OFFSET��
    private $limit;                             // ���� SQL��LIMIT��
    private $sql;                               // ���� SQLʸ
    private $dateYMvalues;                      // targetDateYMvalues��<select><option>�ǡ���
    private $total;                             // ��׷��
    private $viewRec;                           // ɽ�����
    private $detailsPartsName;                  // ����������̾
    private $detailsVendor;                     // ������ȯ����̾
    private $detailsPartsNo;                    // �����������ֹ�
    private $detailsProMark;                    // �����ѹ�������
    
    ///// public properties
    // public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�ذܹԻ��� __construct() ���ѹ�ͽ��) (�ǥ��ȥ饯��__destruct())
    public function __construct($request)
    {
        ///// �ץ�ѥƥ�(���С��ѿ�)�ν����
        $this->where  = '';
        $this->order  = '';
        $this->offset = '';
        $this->limit  = '';
        $this->sql    = '';
        $this->dateYMvalues = '';
        $this->total   = 0;
        $this->viewRec = 0;
        $this->detailsPartsName = '';
        $this->detailsVendor    = '';
        $this->detailsPartsNo   = '';
        $this->detailsProMark   = '';
    }
    
    ///// SQL��WHERE�������
    public function setWhere($session)
    {
        $this->where = $this->setWhereBody($session);
    }
    
    ///// �ƹ������٤�SQL��WHERE�������
    public function setDetailsWhere($session)
    {
        $this->where = $this->setDetailsWhereBody($session);
    }
    
    ///// SQL��WHERE�������
    public function setLimit($session)
    {
        $this->limit = $this->setLimitBody($session);
    }
    
    ///// SQLʸ������
    public function setSQL($session)
    {
        $this->sql = $this->setSQLbody($session);
    }
    
    ///// �ƹ������٤�SQLʸ������
    public function setDetailsSQL($session)
    {
        $this->sql = $this->setDetailsSQLbody($session);
    }
    
    ///// ��׷��������
    public function setTotal()
    {
        $this->total = $this->setTotalBody();
    }
    
    ///// �ƹ������٤�����̾��ȯ����̾������
    public function setDetailsItem($session)
    {
        $this->setDetailsItemBody($session);
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
        $this->dateYMvalues = $option;
        return $option;
    }
    
    ////////// MVC �� Model �� �Ƽ�ꥹ�ȵڤӥ��������
    ///// List��    �ꥹ������
    public function outListViewHTML($session, $menu)
    {
                /***** �إå���������� *****/
        $this->outViewHTMLheader($session, $menu);
        
                /***** ��ʸ����� *****/
        $this->outViewHTMLbody($session, $menu);
        
                /***** �եå���������� *****/
        $this->outViewHTMLfooter($session, $menu);
        
        return ;
    }
    
    ///// ���顼��å������ѥꥹ�Ƚ���
    public function outListErrorMessage($session, $menu)
    {
                /***** �إå���������� *****/
        $this->outViewHTMLheader($session, $menu);
        
                /***** ��ʸ����� *****/
        $this->outErrorMessageHTMLbody($session, $menu);
        
                /***** �եå���������� *****/
        $this->outViewHTMLfooter($session, $menu);
        
        return ;
    }
    
    ///// �ƹ������٥ꥹ������
    public function outDetailsViewHTML($session, $menu)
    {
                /***** �إå���������� *****/
        $this->outDetailsHTMLheader($session, $menu);
        
                /***** ��ʸ����� *****/
        $this->outDetailsHTMLbody($session, $menu);
        
                /***** �եå���������� *****/
        $this->outDetailsHTMLfooter($session, $menu);
        
        return ;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����Ȥˤ��SQLʸ��WHERE�������
    protected function setWhereBody($session)
    {
        $where = "
            WHERE delivery >= {$session->get_local('targetDateStr')} AND delivery <= {$session->get_local('targetDateEnd')}
            AND (order_q - cut_siharai) > 0
        ";
        // �嵭����ʸ��������ʪ���оݤˤ��Ƥ��롣�ޤ������칩�狼���ͭ���ٵ�����ڤϥޥ��ʥ���ʸ�������äƤ��������ա�
        return $where;
    }
    
    ////////// �ƹ��������٥ꥹ����SQLʸ��WHERE�������
    protected function setDetailsWhereBody($session)
    {
        $where = "
            WHERE delivery >= {$session->get_local('targetDateStr')} AND delivery <= {$session->get_local('targetDateEnd')}
            AND order_process.vendor = '{$session->get_local('targetVendor')}' AND parts_no = '{$session->get_local('targetPartsNo')}'
            AND pro_mark = '{$session->get_local('targetProMark')}'
            AND (order_q - cut_siharai) > 0
        ";
        // �嵭����ʸ��������ʪ���оݤˤ��Ƥ��롣�ޤ������칩�狼���ͭ���ٵ�����ڤϥޥ��ʥ���ʸ�������äƤ��������ա�
        return $where;
    }
    
    protected function setLimitBody($session)
    {
        $limit = "LIMIT {$session->get_local('targetLimit')}";
        return $limit;
    }
    
    protected function setTotalBody()
    {
        $query = "
            SELECT count(*)
            FROM order_process
            {$this->where}
            GROUP BY parts_no, pro_mark, vendor
        ";
        $rows = $this->getResult2($query, $res);
        return $rows;
    }
    
    protected function setSQLbody($session)
    {
        $query = "
            SELECT
                parts_no AS �����ֹ�
                ,
                substr(midsc, 1, 16) AS ����̾
                ,
                substr(to_char(order_no, 'FM9999999'), 7, 1) AS �����ֹ�
                ,
                pro_mark AS ��������
                ,
                substr(name, 1, 14) AS ȯ����̾
                ,
                count(*) AS ���
                ,
                sum(order_q - cut_siharai) AS ��׿���
                ,   -- �ʲ��ϥꥹ�ȳ�
                order_process.vendor AS ȯ���襳����
                ,
                mepnt AS �Ƶ���
            FROM order_process
            LEFT OUTER JOIN miitem ON (parts_no = mipn)
            LEFT OUTER JOIN vendor_master USING (vendor)
            {$this->where}
            GROUP BY �����ֹ�, �Ƶ���, ����̾, �����ֹ�, ��������, order_process.vendor, vendor_master.name
            ORDER BY ��� DESC, ��׿��� DESC, parts_no ASC, �����ֹ� ASC, �������� ASC, vendor ASC
            {$this->limit}
        ";
        return $query;
    }
    
    ///// �ƹ��������٥ꥹ�Ȥ�SQLʸ
    protected function setDetailsSQLbody($session)
    {
        $query = "
            SELECT
                to_char(order_no, 'FM999999-9') AS ��ʸ�ֹ�
                ,
                to_char(sei_no, 'FM0000000')    AS ��¤�ֹ�
                ,
                substr(to_char(order_date, 'FM9999/99/99'), 3, 8)
                                                AS ȯ����
                ,
                substr(to_char(delivery, 'FM9999/99/99'), 3, 8)
                                                AS Ǽ��
                ,
                CASE
                    WHEN mtl_cond = '1' THEN '����'
                    WHEN mtl_cond = '2' THEN 'ͭ��'
                    WHEN mtl_cond = '3' THEN '̵��'
                    ELSE                     '̤����'
                END                             AS �������
                ,
                order_price                     AS ñ��
                ,
                CASE
                    WHEN pro_kubun = '0' THEN '����'
                    WHEN pro_kubun = '1' THEN '��³'
                    WHEN pro_kubun = '2' THEN '����'
                    WHEN pro_kubun = '3' THEN '����'
                    WHEN pro_kubun = '4' THEN '̤��'
                    ELSE                      '̤����'
                END                             AS ñ����ʬ
                ,
                order_q - cut_siharai           AS ȯ���
                ,
                siharai                         AS ������
            FROM order_process
            {$this->where}
            ORDER BY delivery ASC
            {$this->limit}
        ";
        return $query;
    }
    
    protected function outViewHTMLheader($session, $menu)
    {
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $headHTML .= $this->getViewHTMLheader($session);
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/total_repeat_order_ViewListHeader-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    protected function outViewHTMLbody($session, $menu)
    {
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLbody($session, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/total_repeat_order_ViewList-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    protected function outViewHTMLfooter($session, $menu)
    {
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $footHTML .= $this->getViewHTMLfooter();
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/total_repeat_order_ViewListFooter-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    protected function outErrorMessageHTMLbody($session, $menu)
    {
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $listHTML .= $this->getErrorMessageHTMLbody($session, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/total_repeat_order_ViewList-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    ///// �ƹ��������٥ꥹ�Ƚ��� �إå�����
    protected function outDetailsHTMLheader($session, $menu)
    {
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $headHTML .= $this->getDetailsHTMLheader($session);
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/total_repeat_order_ViewListHeader-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    ///// �ƹ��������٥ꥹ�Ƚ��� �ܥǥ���
    protected function outDetailsHTMLbody($session, $menu)
    {
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $listHTML .= $this->getDetailsHTMLbody($session, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/total_repeat_order_ViewList-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    ///// �ƹ��������٥ꥹ�Ƚ��� �եå�����
    protected function outDetailsHTMLfooter($session, $menu)
    {
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header', $menu);
        // ��������HTML�����������
        $footHTML .= $this->getDetailsHTMLfooter();
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer', $menu);
        // HTML�ե��������
        $file_name = "list/total_repeat_order_ViewListFooter-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��  ����ɽ�� �إå����������
    private function getViewHTMLheader($session)
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>�Ƶ���</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>�����ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='23%'>����̾</th>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>����</th>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>����</th>\n";
        $listTable .= "        <th class='winbox' width='22%'>ȯ����̾</th>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>���</th>\n";
        $listTable .= "        <th class='winbox' width=' 8%'>����</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// List��   ����ɽ�� ��ʸ
    private function getViewHTMLbody($session, $menu)
    {
        $res = array();
        $rows = $this->getResult2($this->sql, $res);
        $this->viewRec = $rows;
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>ȯ��ǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $res[-1][0] = '';   // ���ߡ������
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 8%' align='right'>\n";
                $listTable .= "            <a class='button' href='javascript:win_open(\"{$menu->out_self()}?Action=Details&showMenu=ListWin&targetVendor={$res[$i][7]}&targetPartsNo=" . urlencode($res[$i][0]) . "&targetProMark={$res[$i][3]}\", 900, 600, \"\");'>\n";
                $listTable .= "        " . ($i+1) . "</a></td>\n";    // ���ֹ�
                if ($res[$i-1][0] != $res[$i][0]) {
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][8]}</td>\n";     // �Ƶ���
                $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][0]}</td>\n";     // �����ֹ�
                $listTable .= "        <td class='winbox' width='23%' align='left'  >{$res[$i][1]}</td>\n";     // ����̾
                } else {
                $listTable .= "        <td class='winbox' width='12%' align='center'>&nbsp;</td>\n";     // �Ƶ���
                $listTable .= "        <td class='winbox' width='12%' align='center'>&nbsp;</td>\n";     // �����ֹ�
                $listTable .= "        <td class='winbox' width='23%' align='left'  >&nbsp;</td>\n";     // ����̾
                }
                $listTable .= "        <td class='winbox' width=' 5%' align='center'>{$res[$i][2]}</td>\n";     // ����
                $listTable .= "        <td class='winbox' width=' 5%' align='center'>{$res[$i][3]}</td>\n";     // ����
                $listTable .= "        <td class='winbox' width='22%' align='left'  >{$res[$i][4]}</td>\n";     // ȯ����̾
                $listTable .= "        <td class='winbox' width=' 5%' align='right' >{$res[$i][5]}</td>\n";     // ���
                $listTable .= "        <td class='winbox' width=' 8%' align='right' >" . number_format($res[$i][6]) . "</td>\n";// ��׿���
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// List��  ����ɽ�� �եå����������
    private function getViewHTMLfooter()
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='100%' align='right' >ɽ�������" . number_format($this->viewRec) . "���ס�" . number_format($this->total) . "��</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// �ƹ��������� ����ɽ�� �إå����������
    private function getDetailsHTMLheader($session)
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>��ʸ�ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>��¤�ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>ȯ����</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>Ǽ����</th>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>����</th>\n";
        $listTable .= "        <th class='winbox' width='11%'>ñ��</th>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>��ʬ</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>ȯ���</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>������</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// �ƹ��������� ����ɽ�� �ܥǥ��������
    private function getDetailsHTMLbody($session, $menu)
    {
        $res = array();
        $rows = $this->getResult2($this->sql, $res);
        $this->viewRec = $rows;
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>ȯ��ǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $res[-1][0] = '';   // ���ߡ������
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1) . "</td>\n";    // ���ֹ�
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][0]}</td>\n";     // ��ʸ�ֹ�
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][1]}</td>\n";     // ��¤�ֹ�
                $listTable .= "        <td class='winbox' width='15%' align='center'>{$res[$i][2]}</td>\n";     // ȯ����
                $listTable .= "        <td class='winbox' width='15%' align='center'>{$res[$i][3]}</td>\n";     // Ǽ��
                $listTable .= "        <td class='winbox' width=' 5%' align='center'>{$res[$i][4]}</td>\n";     // �������
                $listTable .= "        <td class='winbox' width='11%' align='right' >" . number_format($res[$i][5], 2) . "</td>\n";// ñ��
                $listTable .= "        <td class='winbox' width=' 5%' align='center'>{$res[$i][6]}</td>\n";     // ñ����ʬ
                $listTable .= "        <td class='winbox' width='12%' align='right' >" . number_format($res[$i][7]) . "</td>\n";// ȯ���
                $listTable .= "        <td class='winbox' width='12%' align='right' >" . number_format($res[$i][8]) . "</td>\n";// ������
                $listTable .= "    </tr>\n";
            }
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
    ///// �ƹ��������� ����ɽ�� �եå����������
    private function getDetailsHTMLfooter()
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' width='100%' align='right'>�����ֹ桧{$this->detailsPartsNo}������̾��{$this->detailsPartsName}��������{$this->detailsProMark}��ȯ���衧{$this->detailsVendor}����׷����" . number_format($this->viewRec) . "��</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        return $listTable;
    }
    
    ///// ���������� ����̾��ȯ����̾������
    private function setDetailsItemBody($session)
    {
        $query = "
            SELECT substr(midsc, 1, 20) FROM miitem WHERE mipn = '{$session->get_local('targetPartsNo')}'
        ";
        $this->getUniResult($query, $partsName);
        $query = "
            SELECT substr(name, 1, 20) FROM vendor_master WHERE vendor = '{$session->get_local('targetVendor')}'
        ";
        $this->getUniResult($query, $vendorName);
        $this->detailsPartsName = trim($partsName);
        $this->detailsVendor    = mb_substr(str_replace('��', '', trim($vendorName)), 0, 10);
        $this->detailsPartsNo   = $session->get_local('targetPartsNo');
        $this->detailsProMark   = $session->get_local('targetProMark');
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
<title>���֤�ȯ��ν��׷��</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../total_repeat_order.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<!-- <script type='text/javascript' src='../total_repeat_order.js'></script> -->
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
    private function getErrorMessageHTMLbody($session, $menu)
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <td width='100%' align='center' class='winbox'>�������Ͻ�λ���դ˥��顼���Ϥ���¾���顼�ǽ�������ߤ��ޤ�����</td>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "</table>\n";
        $listTable .= "    </td></tr>\n";
        $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        // return mb_convert_encoding($listTable, 'UTF-8');
        return $listTable;
    }
    
} // Class TotalRepeatOrder_Model End

?>
