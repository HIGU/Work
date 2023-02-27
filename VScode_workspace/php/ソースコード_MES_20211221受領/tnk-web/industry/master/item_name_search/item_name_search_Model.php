<?php
//////////////////////////////////////////////////////////////////////////////
// �����ƥ�ޥ���������̾�ˤ��������������ʬ����            MVC Model ��   //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/10 Created   item_name_search_Model.php                          //
// 2006/04/11 ���ܥ����Ȼ��ϥ�å�����(��̾�κǽ�˰��פʤ�)��ɽ�����ʤ�    //
// 2006/05/22 ����ˤ��ޥ������������ɲ� targetItemMaterial  targetLimit  //
// 2006/05/23 �߸˥����å����ץ������ɲ� targetStockOption                //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class ItemNameSearch_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    private $order;                             // ���� SQL��ORDER��
    private $limit;                             // ���� SQL��LIMIT��
    private $option;                            // ��ͭ SQL�ؿ���option
    
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
            $this->limit = $this->SetInitLimit($request);
            $this->SetInitStockOption($request);
            break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    �����ƥ�ޥ������λ�����Ǥ� ����ɽ
    public function outViewListHTML($request, $menu)
    {
        // �����HTML�����������
        $listHTML  = $this->getViewHTMLconst('header');
        // ��������HTML�����������
        $listHTML .= $this->getViewHTMLtable($request, $menu);
        // �����HTML�����������
        $listHTML .= $this->getViewHTMLconst('footer');
        // HTML�ե��������
        $file_name = "list/item_name_search_ViewList-{$_SESSION['User_ID']}.html";
        $handle = fopen($file_name, 'w');
        fwrite($handle, $listHTML);
        fclose($handle);
        chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
        return ;
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���WHERE�������
    protected function SetInitWhere($request)
    {
        $where = '';    // �����
        switch ($request->get('targetDivision')) {
        case 'A':   // ���٤�
            $where = '';
            break;
        case 'C':
            $where = 'C';
            // $where .= "WHERE parts_no LIKE 'C%' OR parts_name LIKE '������%' OR parts_name LIKE '��������%'";
            break;
        case 'L':
            $where = 'L';
            // $where .= "WHERE parts_no LIKE 'L%' OR parts_name LIKE '������%' OR parts_name LIKE '��������%'";
            break;
        case 'T':
            $where = 'T';
            // $where .= "WHERE parts_no LIKE 'T%' OR parts_name LIKE '������%' OR parts_name LIKE '��������%'";
            break;
        case 'O':   // OTHER ����¾
            $where = 'F';
            // $where .= "WHERE (parts_no NOT LIKE 'C%' AND parts_no NOT LIKE 'L%' parts_no NOT LIKE 'T%') OR parts_name LIKE '������%' OR parts_name LIKE '��������%'";
        }
        return $where;
    }
    
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���ORDER�������
    protected function SetInitOrder($request)
    {
        ///// targetSortItem������
        switch ($request->get('targetSortItem')) {
        case 'parts':
            $order = 'ORDER BY parts_no ASC';
            break;
        case 'name':
            $order = 'ORDER BY parts_name ASC';
            break;
        case 'material':
            $order = 'ORDER BY material ASC';
            break;
        case 'parent':
            $order = 'ORDER BY parent ASC';
            break;
        case 'date':
            $order = 'ORDER BY as_date ASC';
            break;
        default:
            $order = '';
        }
        return $order;
    }
    
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���LIMIT�������
    protected function SetInitLimit($request)
    {
        ///// targetLimit������
        switch ($request->get('targetLimit')) {
        case 10000:
            $limit = 'LIMIT 10000 OFFSET 0';
            $limit = 10000;  // ���ߤϤ���������
            break;
        case 8000:
            $limit = 'LIMIT 8000 OFFSET 0';
            $limit = 8000;  // ���ߤϤ���������
            break;
        case 4000:
            $limit = 'LIMIT 4000 OFFSET 0';
            $limit = 4000;
            break;
        case 2000:
            $limit = 'LIMIT 2000 OFFSET 0';
            $limit = 2000;
            break;
        case 1000:
            $limit = 'LIMIT 1000 OFFSET 0';
            $limit = 1000;
            break;
        case 600:
            $limit = 'LIMIT 600 OFFSET 0';
            $limit = 600;
            break;
        case 300:
        default:
            $limit = 'LIMIT 300 OFFSET 0';
            $limit = 300;
        }
        return $limit;
    }
    
    ////////// �ꥯ�����Ȥˤ��SQL�ؿ��κ߸˥����å����ץ���������
    protected function SetInitStockOption($request)
    {
        $this->option = $request->get('targetStockOption');
        return $this->option;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    ///// List��   �����ƥ�ޥ������λ�����Ǥΰ�������
    private function getViewHTMLtable($request, $menu)
    {
        $query = "
            SELECT   CASE
                        WHEN parts_no = '' THEN '&nbsp;'
                        ELSE parts_no
                     END            AS �����ֹ�         -- 00
                    ,CASE
                        WHEN substr(parts_name, 1, 3) = '������' THEN '<span style=''color:teal;''>' || parts_name || '</span>'
                        WHEN substr(parts_name, 1, 3) = '������' THEN '<span style=''color:red;''>' || parts_name || '</span>'
                        ELSE parts_name
                     END            AS ����̾           -- 01
                    ,CASE
                        WHEN material = '' THEN '&nbsp;'
                        ELSE material
                     END            AS ���             -- 02
                    ,CASE
                        WHEN parent = '' THEN '&nbsp;'
                        ELSE parent
                     END        AS �Ƶ���̾             -- 03
                    ,CASE
                        WHEN as_date IS NULL THEN '&nbsp;'
                        WHEN as_date = 0     THEN '&nbsp;'
                        ELSE to_char(as_date, 'FM0000/00/00')
                     END            AS ������           -- 04
                    FROM
        ";
        if ($request->get('targetItemName')) {
            $query .= "                item_master_name_search_stock('{$request->get('targetItemName')}', '{$this->where}', '{$this->option}', {$this->limit})\n";
        } else {
            $query .= "                item_master_material_search_stock('{$request->get('targetItemMaterial')}', '{$this->where}', '{$this->option}', {$this->limit})\n";
        }
        $query .= "            {$this->order}\n";
        // �����
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
        } else {
            $decrement = 0;
            for ($i=0; $i<$rows; $i++) {
                $listTable .= "    <tr>\n";
                if (mb_ereg_match('<span', $res[$i][1])) {
                    $decrement++;
                    if ($request->get('targetSortItem') != '') {
                        continue;       // ���ܥ����Ȼ��ϥ�å�������ɽ�����ʤ�
                    }
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' >&nbsp;</td>\n";                        // ���ֹ�
                } else {
                    $listTable .= "        <td class='winbox' width=' 5%' align='right' >" . ($i+1-$decrement) . "</td>\n";     // ���ֹ�
                }
                if ($request->get('targetSortItem') == 'parts') {
                    $listTable .= "        <td class='winbox' width='12%' align='center' style='background-color:#ffffc6;' title='�����ֹ�򥯥�å�����к߸˷����Ȳ�Ǥ��ޤ���'\n";
                } else {
                    $listTable .= "        <td class='winbox' width='12%' align='center' title='�����ֹ�򥯥�å�����к߸˷����Ȳ�Ǥ��ޤ���'\n";
                }
                if ($res[$i][0] != '&nbsp;') {
                    $listTable .= "            onClick='ItemNameSearch.win_open(\"" . $menu->out_action('�߸˷���') . "?parts_no=" . urlencode($res[$i][0]) . "&&view_rec=500&noMenu=yes\", 900, 680)'\n";
                    $listTable .= "            onMouseover='document.body.style.cursor=\"hand\"' onMouseout='document.body.style.cursor=\"auto\"'\n";
                }
                $listTable .= "        ><span style='color:blue;'>{$res[$i][0]}</span></td>\n";                                 // �����ֹ�
                if ($request->get('targetSortItem') == 'name') {
                    $listTable .= "        <td class='winbox' width='41%' align='left' style='background-color:#ffffc6;'>" . mb_convert_kana($res[$i][1], 'k') . "</td>\n";   // ����̾
                } else {
                    $listTable .= "        <td class='winbox' width='41%' align='left'>" . mb_convert_kana($res[$i][1], 'k') . "</td>\n";   // ����̾
                }
                if ($request->get('targetSortItem') == 'material') {
                    $listTable .= "        <td class='winbox' width='12%' align='left' style='background-color:#ffffc6;'>{$res[$i][2]}</td>\n";  // ���
                } else {
                    $listTable .= "        <td class='winbox' width='12%' align='left'>{$res[$i][2]}</td>\n";                 // ���
                }
                if ($request->get('targetSortItem') == 'parent') {
                    $listTable .= "        <td class='winbox' width='18%' align='left' style='background-color:#ffffc6;'>{$res[$i][3]}</td>\n"; // �Ƶ���̾
                } else {
                    $listTable .= "        <td class='winbox' width='18%' align='left'>{$res[$i][3]}</td>\n"; // �Ƶ���̾
                }
                if ($request->get('targetSortItem') == 'date') {
                    $listTable .= "        <td class='winbox' width='12%' align='center' style='background-color:#ffffc6;'>{$res[$i][4]}</td>\n"; // ������
                } else {
                    $listTable .= "        <td class='winbox' width='12%' align='center'>{$res[$i][4]}</td>\n"; // ������
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
    
    ///// �����List��    HTML�ե��������  �ʲ��Υ������ϸ����餤�����Ϸ�̤򸫤䤹�����뤿��
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
<title>�����ƥ�ޥ������θ������List��</title>
<script type='text/javascript' src='/base_class.js'></script>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='../item_name_search.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    background-image:none;
}
-->
</style>
<script type='text/javascript' src='../item_name_search.js'></script>
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
    
    
} // Class ItemNameSearch_Model End

?>
