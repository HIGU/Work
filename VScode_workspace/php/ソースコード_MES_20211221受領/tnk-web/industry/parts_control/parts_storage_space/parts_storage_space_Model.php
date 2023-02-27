<?php
//////////////////////////////////////////////////////////////////////////////
// ���긡�����ǻ����ݴɾ��ΰ���(NKB������)�Ȳ�               MVC Model �� //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/06/21 Created   parts_storage_space_Model.php                       //
// 2006/06/24 getUniResult() �� $this->getUniResult() ���ѹ�  122����       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����

require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class PartsStorageSpace_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $last_avail_pcs;                    // �ǽ�ͭ����(�ǽ�ͽ��߸˿�)
    
    ///// public properties
    // public  $graph;                             // GanttChart�Υ��󥹥���
    
    /************************************************************************
    *                               Public methods                          *
    ************************************************************************/
    ////////// Constructer ����� (php5�� __construct() ) (�ǥ��ȥ饯��__destruct())
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
    public function getTargetDateValues($request)
    {
        // �����
        $option = "\n";
        $pre_ymd = '';
        for ($i=0; $i<39; $i++) {   // 31��+8��
            $yyyymmdd = workingDayOffset("-{$i}");
            if ($yyyymmdd == $pre_ymd) continue;
            $pre_ymd = $yyyymmdd;
            $yyyy = substr($yyyymmdd, 0, 4); $mm = substr($yyyymmdd, 4, 2); $dd = substr($yyyymmdd, 6, 2);
            $option .= "<option value='{$yyyymmdd}'>{$yyyy}ǯ{$mm}��{$dd}</option>\n";
        }
        return $option;
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    �ǡ��������� ����ɽ
    public function outViewListHTML($request, $menu)
    {
                /***** �إå���������� *****/
        // �����HTML�����������
        $headHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $headHTML .= $this->getViewHTMLheader($request);
        // �����HTML�����������
        $headHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/parts_storage_space_ViewListHeader-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $headHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        
                /***** ��ʸ����� *****/
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLbody($request, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/parts_storage_space_ViewList-{$_SESSION['User_ID']}.html";
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
        $file_name = "list/parts_storage_space_ViewListFooter-{$_SESSION['User_ID']}.html";
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
        $query = "SELECT comment FROM parts_storage_space_comment WHERE plan_no='{$request->get('targetPlanNo')}'";
        if ($this->getUniResult($query, $comment) < 1) {
            $sql = "
                INSERT INTO parts_storage_space_comment (assy_no, plan_no, comment, last_date, last_host)
                values ('{$request->get('targetAssyNo')}', '{$request->get('targetPlanNo')}', '{$request->get('comment')}', '{$last_date}', '{$last_host}')
            ";
            if ($this->query_affected($sql) <= 0) {
                $_SESSION['s_sysmsg'] = "�����Ȥ���¸������ޤ���Ǥ�����������ô���Ԥ�Ϣ���Ʋ�������";
            }
        } else {
            $sql = "
                UPDATE parts_storage_space_comment SET comment='{$request->get('comment')}',
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
            parts_storage_space_comment ON(mipn=assy_no)
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
        $where = "
            data.ken_date >= {$request->get('targetDateStr')} AND data.ken_date <= {$request->get('targetDateEnd')}
            AND pro.next_pro = 'END..' AND plan.locate = '{$request->get('targetLocate')}'
        ";
        return $where;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��   ����ɽ ����
    private function getViewHTMLbody($request, $menu)
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
            $listTable .= "        <td width='100%' align='center' class='winbox'>�ǡ���������ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                $listTable .= "        <td class='winbox' width=' 5%' align='right'>" . ($i+1) . "</td>\n";                     // ���ֹ�
                $listTable .= "        <td class='winbox' width='12%' align='center'>\n";
                if ($request->get('showMenu') == 'List') {
                    $listTable .= "            <a href='{$menu->out_action('�߸˷���')}?parts_no=" . urlencode($res[$i][0]) . "' target='application' style='text-decoration:none;'>\n";
                }
                $listTable .= "        {$res[$i][0]}</a></td>\n";                                                               // �����ֹ�
                $listTable .= "        <td class='winbox' width='15%' align='left'>".mb_convert_kana($res[$i][1], 'k')."</td>\n";// ����̾
                $listTable .= "        <td class='winbox' width='17%' align='center'\n";
                $listTable .= "            onClick='alert(\"��¤�ֹ桧{$res[$i][8]}\\n\\n��ʸ�ֹ桧{$res[$i][9]}\");' title='��󥯥�å�����¤�ֹ����ʸ�ֹ��ɽ�����ޤ���'\n";
                $listTable .= "            onMouseover=\"this.style.backgroundColor='#ceffce'; this.style.color='black'; this.style.cursor='hand'; \"\n";
                $listTable .= "            onMouseout =\"this.style.backgroundColor=''; this.style.color=''; this.style.cursor='auto'; \"\n";
                $listTable .= "        >{$res[$i][2]}��{$res[$i][3]}</td>\n";                                                   // ���բ�������
                $listTable .= "        <td class='winbox' width='10%' align='center'>{$res[$i][4]}</td>\n";                     // �����ֹ�
                $listTable .= "        <td class='winbox' width='10%' align='right'>".number_format($res[$i][5], 0)."</td>\n";  // ���տ�(�������ʲ�3�夢�뤬�������Τ�ɽ��)
                $listTable .= "        <td class='winbox' width='10%' align='right'>".number_format($res[$i][6], 0)."</td>\n";  // ������(�������ʲ�3�夢�뤬�������Τ�ɽ��)
                $listTable .= "        <td class='winbox' width='21%' align='left'\n";
                $listTable .= "            onClick='alert(\"ȯ���襳���ɡ�{$res[$i][10]}\");' title='��󥯥�å���ȯ���襳���ɤ�ɽ�����ޤ���'\n";
                $listTable .= "            onMouseover=\"this.style.backgroundColor='#ceffce'; this.style.color='black'; this.style.cursor='hand'; \"\n";
                $listTable .= "            onMouseout =\"this.style.backgroundColor=''; this.style.color=''; this.style.cursor='auto'; \"\n";
                $listTable .= "        >{$res[$i][7]}</td>\n";                                                                  // ȯ����
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
    private function getViewHTMLheader($request)
    {
        // �����
        $listTable = '';
        $listTable .= "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
        $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
        $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
        $listTable .= "    <tr>\n";
        $listTable .= "        <th class='winbox' width=' 5%'>No</th>\n";
        $listTable .= "        <th class='winbox' width='12%'>�����ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='15%'>�����ʡ�̾</th>\n";
        $listTable .= "        <th class='winbox' width='17%'>���բ�������</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>�����ֹ�</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>���տ�</th>\n";
        $listTable .= "        <th class='winbox' width='10%'>������</th>\n";
        $listTable .= "        <th class='winbox' width='21%'>ȯ����̾</th>\n";
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
    private function getQueryStatement($request)
    {
        $query = "
            SELECT
                  data.parts_no             AS �����ֹ�     -- 00
                , trim(substr(miitem.midsc, 1, 10))
                                            AS ����̾       -- 01
                , substr(to_char(data.uke_date, 'FM0000/00/00'), 3)
                                            AS ������       -- 02
                , substr(to_char(data.ken_date, 'FM0000/00/00'), 6)
                                            AS ������       -- 03
                , data.uke_no               AS �����ֹ�     -- 04
                , data.uke_q                AS ���տ�       -- 05
                , data.genpin               AS ������       -- 06
                , trim(substr(ven.name, 1, 10))
                                            AS ȯ����       -- 07
                --------------------------------------------�ʲ��ϥꥹ�ȳ�
                , data.sei_no               AS ��¤�ֹ�     -- 08
                , data.order_no             AS ��ʸ�ֹ�     -- 09
                , data.vendor               AS ȯ�����ֹ�   -- 10
            FROM
                order_data AS data
            LEFT OUTER JOIN
                order_process AS pro USING (sei_no, order_no, vendor)
            LEFT OUTER JOIN
                order_plan AS plan USING (sei_no)
            LEFT OUTER JOIN
                miitem ON (plan.parts_no=mipn)
            LEFT OUTER JOIN
                vendor_master AS ven USING (vendor)
            WHERE
                data.ken_date >= {$request->get('targetDateStr')} AND data.ken_date <= {$request->get('targetDateEnd')}
                AND pro.next_pro = 'END..' AND plan.locate = '{$request->get('targetLocate')}'
            ORDER BY
                data.uke_no ASC
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
<title>�и˻��ֽ��׾Ȳ�</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../parts_storage_space.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../parts_storage_space.js'></script>
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
    
} // Class PartsStorageSpace_Model End

?>
