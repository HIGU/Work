<?php
//////////////////////////////////////////////////////////////////////////////
// ������������ƥ� �Խ������˥塼                         MVC Model ��   //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/11/15 Created   punchMark_editHistory_Model.php                     //
//////////////////////////////////////////////////////////////////////////////
require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class PunchMarkEditHistory_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $order;                             // ���� SQL��ORDER��
    private $offset;                            // ���� SQL��OFFSET��
    private $limit;                             // ���� SQL��LIMIT��
    private $sql;                               // ���� SQLʸ
    private $masterOptions;                     // targetMaster��<select><option>�ǡ���
    private $historyOptions;                    // targetHistory��<select><option>�ǡ���
    
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
        $this->order  = 'ORDER BY edit_date DESC';
        $this->offset = 'OFFSET 0';
        $this->limit  = 'LIMIT 500';
        $this->sql    = '';
        $this->masterOptions  = '';
        $this->historyOptions = '';
    }
    
    ///// SQL��WHERE�������
    public function setWhere($session)
    {
        $this->where = $this->setWhereBody($session);
    }
    
    ///// SQLʸ������
    public function setSQL($session)
    {
        $this->sql = $this->setSQLbody($session);
    }
    
    ///// �ޥ����������HTML <select> option �ν���
    public function getMasterOptions($session)
    {
        if ($this->masterOptions == '') {
            $this->masterOptions = $this->getMasterOptionsBody($session);
        }
        return $this->masterOptions;
    }
    
    ///// �ޥ������������Ƥ����� HTML <select> option �ν���
    public function getHistoryOptions($session)
    {
        if ($this->historyOptions == '') {
            $this->historyOptions = $this->getHistoryOptionsBody($session);
        }
        return $this->historyOptions;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    �ǡ��������� ����ɽ
    public function outViewListHTML($session, $menu)
    {
                /***** �إå���������� *****/
        /*****************
        $this->outViewHTMLheader($session);
        *****************/
        
                /***** ��ʸ����� *****/
        $this->outViewHTMLbody($session, $menu);
        
                /***** �եå���������� *****/
        /************************
        $this->outViewHTMLfooter($session);
        ************************/
        return ;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����Ȥˤ��SQLʸ��WHERE�������
    protected function setWhereBody($session)
    {
        $where = '';
        if ($session->get_local('targetMaster') == 'parts') {
            $where .= "WHERE table_name = 'punchMark_parts_master'";
        } elseif ($session->get_local('targetMaster') == 'mark') {
            $where .= "WHERE table_name = 'punchMark_master'";
        } elseif ($session->get_local('targetMaster') == 'shape') {
            $where .= "WHERE table_name = 'punchMark_shape_master'";
        } elseif ($session->get_local('targetMaster') == 'size') {
            $where .= "WHERE table_name = 'punchMark_size_master'";
        }
        if ($session->get_local('targetHistory') != '' && $where != '') {
            $where .= " AND edit_code = '{$session->get_local('targetHistory')}'";
        } elseif ($session->get_local('punchMark_code') != '') {
            $where .= "WHERE edit_code = '{$session->get_local('targetHistory')}'";
        }
        return $where;
    }
    
    ///// �ޥ����������HTML <select> option �ν���
    protected function getMasterOptionsBody($session)
    {
        $options = "\n";
        if ($session->get_local('targetMaster') == 'parts') {
            $options .= "<option value='parts' selected>���ʥޥ�����</option>\n";
        } else {
            $options .= "<option value='parts'>���ʥޥ�����</option>\n";
        }
        if ($session->get_local('targetMaster') == 'mark') {
            $options .= "<option value='mark' selected>����ޥ�����</option>\n";
        } else {
            $options .= "<option value='mark'>����ޥ�����</option>\n";
        }
        if ($session->get_local('targetMaster') == 'shape') {
            $options .= "<option value='shape' selected>�����ޥ�����</option>\n";
        } else {
            $options .= "<option value='shape'>�����ޥ�����</option>\n";
        }
        if ($session->get_local('targetMaster') == 'size') {
            $options .= "<option value='size' selected>�������ޥ�����</option>\n";
        } else {
            $options .= "<option value='size'>�������ޥ�����</option>\n";
        }
        return $options;
    }
    
    ///// �ޥ������������Ƥ����� HTML <select> option �ν���
    protected function getHistoryOptionsBody($session)
    {
        $options = "\n";
        if ($session->get_local('targetHistory') == 'U') {
            $options .= "<option value='U' selected>�ѹ�����</option>\n";
        } else {
            $options .= "<option value='U'>�ѹ�����</option>\n";
        }
        if ($session->get_local('targetHistory') == 'D') {
            $options .= "<option value='D' selected>�������</option>\n";
        } else {
            $options .= "<option value='D'>�������</option>\n";
        }
        if ($session->get_local('targetHistory') == 'I') {
            $options .= "<option value='I' selected>�ɲ�����</option>\n";
        } else {
            $options .= "<option value='I'>�ɲ�����</option>\n";
        }
        return $options;
    }
    
    ///// ���٤ν��� �إå�����
    protected function outViewHTMLheader($session)
    {
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $headHTML .= $this->getViewHTMLheader();
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/punchMark_editHistory_ViewListHeader-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    ///// ���٤ν��� �ܥǥ�����
    protected function outViewHTMLbody($session, $menu)
    {
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLbody($session, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/punchMark_editHistory_ViewList-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    ///// ���٤ν��� �եå�����
    protected function outViewHTMLfooter($session)
    {
        // �����HTML�����������
        $footHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $footHTML .= $this->getViewHTMLfooter();
        // �����HTML�����������
        $footHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/punchMark_editHistory_ViewListFooter-{$session->get('User_ID')}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $footHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��   ����ɽ��SQL���ơ��ȥ�������
    // ���ʥޥ�����������ޥ������������ޥ��������������ޥ������� SQL
    private function setSQLbody($session)
    {
        $query = "
            SELECT
                -- COALESCE(pre_data, '&nbsp;')
                CASE
                    WHEN pre_data = '' THEN '&nbsp;'
                    ELSE pre_data
                END                                 AS �������ǡ��� -- 0
                ,
                to_char(edit_date, 'YYYY/MM/DD HH24:MI')
                                                    AS �ѹ�����     -- 1
                ,
                (SELECT name FROM user_detailes WHERE uid = substr(edit_user, 1, 6))
                                                    AS ������       -- 2
                ,
                substr(edit_user, 8)                AS IP�ۥ���̾   -- 3
                ---------------- �ʲ��ϥꥹ�ȳ� ------------------
                ,
                edit_sql                            AS ����SQL      -- 4
            FROM
                punchMark_edit_history  AS edit
            {$this->where}
            {$this->order}
            {$this->offset}
            {$this->limit}
        ";
        return $query;
    }
    
    ///// List��   ������̤� ���٥ǡ�������
    private function getViewHTMLbody($session, $menu)
    {
        if ($this->sql == '') exit();
        $res = array();
        if ( ($rows=$this->getResult2($this->sql, $res)) <= 0) {
            // $session->add('s_sysmsg', '�������򤬤���ޤ���');
        }
        
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        if ($rows <= 0) {
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='100%' align='center' class='winbox'>�������򤬤���ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                $viewSQL = str_replace("\n", '\n', $res[$i][4]);    // LF���ƥ��ʸ��������Ѵ�
                $viewSQL = str_replace("\r", '', $viewSQL);         // CR����
                $viewSQL = str_replace("'", '', $viewSQL);          // ���󥰥륯�����Ȥ���
                /*****
                if ($res[$i][10] != '') {   // �����Ȥ�����п����Ѥ���
                    $listTable .= "    <tr onDblClick='PunchMarkEditHistory.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='�����Ȥ���Ͽ����Ƥ��ޤ������֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���' style='background-color:#e6e6e6;'>\n";
                } else {
                    $listTable .= "    <tr onDblClick='PunchMarkEditHistory.win_open(\"" . $menu->out_self() . "?showMenu=Comment&targetPlanNo=" . urlencode($res[$i][0]) . "&targetAssyNo=" . urlencode($res[$i][1]) . "\", 600, 235)' title='���ߥ����Ȥ���Ͽ����Ƥ��ޤ��󡣥��֥륯��å��ǥ����ȤξȲ��Խ�������ޤ���'>\n";
                }
                *****/
                $listTable .= "    <tr ondblClick='alert(\"{$viewSQL}\");'>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1) . "</td>\n";    // ���ֹ�
                // $listTable .= "        <td class='winbox' width=' 8%' align='right' >\n";
                // $listTable .= "            <a href='javascript:PunchMarkEditHistory.win_open(\"{$menu->out_self()}?Action=ListDetails&showMenu=ListWin&targetUid={$res[$i][0]}\");'>����</a>\n";
                // $listTable .= "        </td>\n"; // ���٥���å���
                $listTable .= "        <td class='winbox' width='40%' align='left'  >{$res[$i][0]}</td>\n";     // �������ǡ���
                $listTable .= "        <td class='winbox' width='20%' align='center'>{$res[$i][1]}</td>\n";     // ��������
                $listTable .= "        <td class='winbox' width='15%' align='center'>{$res[$i][2]}</td>\n";     // ������
                $listTable .= "        <td class='winbox' width='20%' align='left'  >{$res[$i][3]}</td>\n";     // IP�ۥ���̾
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
        $listTable .= "        <th class='winbox' colspan='11'>��������</th>\n";
        $listTable .= "    </tr>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='40%'>�������ǡ���</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>��������</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>������</th>\n";
        $listTable .= "        <th class='winbox' width='20%'>IP�ۥ���̾</th>\n";
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
<title>������������ƥ��Խ�����</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../punchMark_editHistory.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../punchMark_editHistory.js'></script>
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
    
    
} // Class PunchMarkEditHistory_Model End

?>
