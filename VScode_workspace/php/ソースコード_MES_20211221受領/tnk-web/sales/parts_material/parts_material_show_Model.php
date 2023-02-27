<?php
//////////////////////////////////////////////////////////////////////////////
// ������夲�κ�����(������)�� �Ȳ�         MVC Model ��                   //
// Copyright (C) 2006-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/02/15 Created   parts_material_show_Model.php                       //
// 2009/09/16 ������������ȴ�Ф��ʤ��褦���ѹ�(ñ����Ͽ�ʤ��ʤΤ�)      //
// 2009/10/01 ���ʴ��������ȴ�Ф��ʤ��褦���ѹ�(ñ����Ͽ�ʤ��ʤΤ�)        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��

require_once ('../../daoInterfaceClass.php');    // TNK ������ DAO���󥿡��ե��������饹


/*****************************************************************************************
*       MVC��Model�� ���饹��� daoInterfaceClass(base class) ���쥯�饹���ĥ           *
*****************************************************************************************/
class PartsMaterialShow_Model extends daoInterfaceClass
{
    ///// Private properties
    private $where;                             // ���� SQL��WHERE��
    
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
        case 'ListTable':
            $this->where = $this->SetInitWhere($request);
            break;
        case 'CondForm':
        case 'WaitMsg':
        default:
            $this->where = '';
        }
    }
    
    ////////// MVC �� Model ���η�� ɽ���ѤΥǡ�������
    ///// List��    ��Ω�����ײ� ����ɽ
    public function getViewListTable($request)
    {
        $query = "
            SELECT
                sum(Uround(����*ñ��, 0))           AS ��������
                ,
                sum(Uround(����*ext_cost, 0))       AS ����������
                ,
                sum(Uround(����*int_cost, 0))       AS ���������
                ,
                sum(Uround(����*unit_cost, 0))      AS ���������
                ,
                count(*)                            AS ����
                ,
                count(*)-count(unit_cost)
                                                    AS ̤��Ͽ
            FROM
                hiuuri
            LEFT OUTER JOIN
                sales_parts_material_history ON (assyno=parts_no AND �׾���=sales_date)
            {$this->where}
        ";
        $res = array();
        if ( ($rows=$this->getResult2($query, $res)) < 1 ) {
            // $_SESSION['s_sysmsg'] = '��夬����ޤ���';
            $listTable = '';
            $listTable .= "<table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>\n";
            $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
            $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td align='center' class='caption_font'>��夬����ޤ���</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        } else {
            $sales     = $res[0][0];    // ����
            $ext_cost  = $res[0][1];    // ������
            $int_cost  = $res[0][2];    // �����
            $unit_cost = $res[0][3];    // ������
            $soukensu  = $res[0][4];    // ����
            $mitouroku = $res[0][5];    // ̤��Ͽ
            if ($sales) {
                $sales_parcent     = Uround($sales / $sales * 100, 2);
                $ext_cost_parcent  = Uround($ext_cost / $sales * 100, 2);
                $int_cost_parcent  = Uround($int_cost / $sales * 100, 2);
                $unit_cost_parcent = Uround($unit_cost / $sales * 100, 2);
            } else {
                $sales_parcent     = 0;
                $ext_cost_parcent  = 0;
                $int_cost_parcent  = 0;
                $unit_cost_parcent = 0;
            }
            $listTable = '';
            $listTable .= "<table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>\n";
            $listTable .= "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
            $listTable .= "<table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td width='140' align='center' class='note1_font'>ñ�̡���</td>\n";
            $listTable .= "        <td width='110' align='center' class='caption_font'>��������</td>\n";
            $listTable .= "        <td width='110' align='center' class='caption_font'>���ʳ�����</td>\n";
            $listTable .= "        <td width='110' align='center' class='caption_font'>���������</td>\n";
            $listTable .= "        <td width='110' align='center' class='caption_font'>���������</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td align='center' class='caption_font'>�⡡��</td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($sales, 0) . "</span></td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($ext_cost, 0) . "</span></td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($int_cost, 0) . "</span></td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($unit_cost, 0) . "</span></td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td align='center' class='caption_font'>������</td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($sales_parcent, 2) . "%</span></td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($ext_cost_parcent, 2) . "%</span></td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($int_cost_parcent, 2) . "%</span></td>\n";
            $listTable .= "        <td class='winbox' align='right'><span class='pt12b'>" . number_format($unit_cost_parcent, 2) . "%</span></td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "    <tr>\n";
            $listTable .= "        <td colspan='5' align='right' class='caption_font'>������" . number_format($soukensu) . "����̤��Ͽ�����" . number_format($mitouroku) . "</td>\n";
            $listTable .= "    </tr>\n";
            $listTable .= "</table>\n";
            $listTable .= "    </td></tr>\n";
            $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
        return mb_convert_encoding($listTable, 'UTF-8');
    }
    
    /***************************************************************************
    *                              Protected methods                           *
    ***************************************************************************/
    ////////// �ꥯ�����Ȥˤ��SQLʸ�δ���WHERE�������
    protected function SetInitWhere($request)
    {
        $where = '';    // �����
        ///// ���������ϰ�
        $where .= "WHERE �׾��� >= {$request->get('targetDateStr')} AND �׾��� <= {$request->get('targetDateEnd')}";
        ///// ���ʻ������λ��� (�����ʬ�������ǽ������)
        if ($request->get('showDiv') == 'C') {
            $where .= " AND ������ = 'C'";
            $where .= " and (assyno not like 'NKB%%')";
        } elseif ($request->get('showDiv') == 'L') {
            $where .= " AND ������ = 'L'";
            $where .= " and (assyno not like 'SS%%')";
        } else {
            $where .= " and (assyno not like 'SS%%')";
            $where .= " and (assyno not like 'NKB%%')";
        }
        ///// ���ʡ������ֹ�λ���
        if ($request->get('targetItemNo') != '') {
            $where .= " AND assyno = '{$request->get('targetItemNo')}'";
        }
        ///// ����ʬ�λ��� (���ߤ����ʤΤߤ��оݤȤ���)
        switch ($request->get('targetSalesSegment')) {
        case '1':   // ����
            $where .= " AND datatype = '1'";
            break;
        case '2':   // ���ʹ��
            $where .= " AND datatype >= '5'";
            break;
        case '5':   // ����(��ư)
            $where .= " AND datatype = '5'";
            break;
        case '6':   // ����(ľǼNKT)
            $where .= " AND datatype = '6'";
            break;
        case '7':   // ����(���)
            $where .= " AND datatype = '7'";
            break;
        case '8':   // ����(����)
            $where .= " AND datatype = '8'";
            break;
        case '9':   // ����(����)
            $where .= " AND datatype = '9'";
            break;
        default :
        }
        return $where;
    }
    
    /***************************************************************************
    *                               Private methods                            *
    ***************************************************************************/
    
} // Class PartsMaterialShow_Model End

?>
