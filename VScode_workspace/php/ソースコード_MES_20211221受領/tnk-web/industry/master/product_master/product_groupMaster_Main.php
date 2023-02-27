<?php
//////////////////////////////////////////////////////////////////////////////
// ���ʥ��롼�ץ������Խ� �ᥤ�� product_groupMaster_Main.php               //
// ���ʥ��롼�סʾܺ١ˤθ����ѥ��롼������                                 //
// Copyright (C) 2009 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2009/11/24 Created  product_groupMaster_Main.php                         //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
session_start();                                 // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');             // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');             // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');           // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
access_log();                                    // Script Name �ϼ�ư����

main();

function main()
{
    ////////////// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                          // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    ////////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('���ʥ��롼�ץ������Խ�');
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// �꥿���󥢥ɥ쥹����
    $menu->set_RetUrl($_SESSION['product_master_referer']);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    get_serch_master($result, $request);                // ���롼�ץޥ������μ���
    get_product_master($result, $request);          // ��������ǡ����ޥ������μ���
    get_productUnreg_master ($result);
    
    request_check($request, $result, $menu);            // ������ʬ�������å�
    
    outViewListHTML($request, $menu, $result);          // HTML����
    
    display($menu, $request, $result);                  // ����ɽ��
}

////////////// ����ɽ��
function display($menu, $request, $result)
{       
    ////////// �֥饦�����Υ���å����к���
    $uniq = 'id=' . $menu->set_useNotCache('target');
    
    ////////// ��å��������ϥե饰
    $msg_flg = 'site';

    ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
    
    ////////// HTML Header ����Ϥ��ƥ���å��������
    $menu->out_html_header();
 
    ////////// View�ν���
    require_once ('product_groupMaster_View.php');

    ob_end_flush(); 
}

////////////// ������ʬ����Ԥ�
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = machineWork_copy($request, $result);
    if ($request->get('entry') != '')  $ok = machineWork_entry($request, $result);
    if ($ok) {
        ////// �ǡ����ν����
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('mhgcd', '');
        $request->add('mhgnm', '');
        $request->add('mhggp', '');
        get_serch_master($result, $request);       // ���롼�ץޥ������μ���
        get_product_master($result, $request); // ��������ǡ����ޥ������μ���
        get_productUnreg_master ($result);
    }
}

////////////// �ɲá��ѹ����å� (��ץ쥳���ɿ��������˹Ԥ�)
function machineWork_entry($request, $result)
{
    if (getCheckAuthority(22)) {                             // ǧ�ڥ����å�
        $mhgcd = $request->get('mhgcd');
        $mhgnm = $request->get('mhgnm');
        $mhggp = $request->get('mhggp');
        $query = sprintf("SELECT mhgcd FROM mshgnm WHERE mhgcd='%s'", $mhgcd);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {         // ��Ͽ���� UPDATE ����
            $query = sprintf("UPDATE mshgnm SET mhggp=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' 
                                WHERE mhgcd='%s'", $mhggp, $_SESSION['User_ID'], $mhgcd);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "���롼�ץ����ɡ�{$mhgcd} ���롼��̾��{$mhgnm}�Υǡ����ѹ����ԡ�";    // .= �����
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "���롼�ץ����ɡ�{$mhgcd} ���롼��̾��{$mhgnm}�Υǡ������ѹ����ޤ�����";    // .= �����
                return true;
            }
        }
    } else {                                                 // ǧ�ڤʤ����顼
        $_SESSION['s_sysmsg'] .= "�Խ����¤�����ޤ���ɬ�פʾ��ˤϡ�ô���Ԥ�Ϣ���Ʋ�������";
        return false;
    }
}

////////////// ɽ����(����ɽ)�Υ��롼�ץޥ������ǡ�����SQL�Ǽ���
function get_serch_master ($result, $request)
{
    $query_g = "
        SELECT  groupm.group_no                AS ���롼���ֹ�     -- 0
            ,   groupm.group_name              AS ���롼��̾       -- 1
        FROM
            product_serchGroup AS groupm
        ORDER BY
            group_name
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] = "���롼�פ���Ͽ������ޤ���";
        $field[0]   = "���롼���ֹ�";
        $field[1]   = "���롼��̾";
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', '');
        $result->add('num_g', 2);
        $result->add('rows_g', '');
    } else {
        $num_g = count($field_g);
        $result->add_array2('res_g', $res_g);
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', $num_g);
        $result->add('rows_g', $rows_g);
    }
}

////////////// ɽ����(����ɽ)�����ʥ��롼�ץ����ɥǡ�����SQL�Ǽ���
function get_product_master ($result, $request)
{
    $query = "
        SELECT  mshgn.mhgcd                 AS ���ʥ��롼�ץ�����  -- 0
            ,   mshgn.mhgnm                 AS ���ʥ��롼��̾      -- 1
            ,   mshgn.mhggp                 AS �����ѥ��롼��      -- 2
        FROM
            mshgnm AS mshgn
        ORDER BY
            mhgcd, mhggp
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
    } else {
        $num = count($field);
        $result->add_array2('res_m', $res);
        $result->add_array2('field_m', $field);
        $result->add('num_m', $num);
        $result->add('rows_m', $rows);
        $res_g = $result->get_array2('res_g');
        for ($r=0; $r<$rows; $r++) {    // ���롼���ֹ�ȥ��롼��̾���֤�����
            $group_name[$r] = "��";
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                if($res[$r][2] == $res_g[$i][0]) {
                    $group_name[$r] = $res_g[$i][1];
                }
            }
        }
        $result->add_array2('group_name', $group_name);
    }
}

////////////// ɽ����(����ɽ)�θ����ѥ��롼��̤��Ͽ������SQL�Ǽ���
function get_productUnreg_master ($result)
{
    $query_num = "
        SELECT  count(*) as num
        FROM
            mshgnm
        WHERE 
            mhggp IS NULL
    ";

    $res_num = array();
    if (getResult($query_num, $res_num) <= 0) {
        $unreg_num = 0;
        //$result->add('unreg_num', $unreg_num);
    } else {
        $unreg_num = $res_num[0]['num'];
        //$result->add('unreg_num', $unreg_num);
    }
    $query_num = "
        SELECT  count(*) as num
        FROM
            mshgnm
        WHERE 
            mhggp = 0
    ";

    $res_num = array();
    if (getResult($query_num, $res_num) <= 0) {
        $result->add('unreg_num', $unreg_num);
    } else {
        $unreg_num += $res_num[0]['num'];
        $result->add('unreg_num', $unreg_num);
    }
}

////////////// ���ԡ��Υ�󥯤������줿��
function machineWork_copy($request, $result)
{
    $r = $request->get('number');
    $res = $result->get_array2('res_m');
    $mhgcd = $res[$r][0];
    $mhgnm = $res[$r][1];
    $mhggp = $res[$r][2];
    
    $request->add('mhgcd', $mhgcd);
    $request->add('mhgnm', $mhgnm);
    $request->add('mhggp', $mhggp);
}

////////////// ���ʥ��롼�ץ������Խ����̤�HTML�κ���
function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<style type='text/css'>\n";
    $listTable .= "<!--\n";
    $listTable .= "th {\n";
    $listTable .= "    background-color:   blue;\n";
    $listTable .= "    color:              yellow;\n";
    $listTable .= "    font-size:          10pt;\n";
    $listTable .= "    font-weight:        bold;\n";
    $listTable .= "    font-family:        monospace;\n";
    $listTable .= "}\n";
    $listTable .= "a:hover {\n";
    $listTable .= "    background-color:   blue;\n";
    $listTable .= "    color:              white;\n";
    $listTable .= "}\n";
    $listTable .= "a:active {\n";
    $listTable .= "    background-color:   gold;\n";
    $listTable .= "    color:              black;\n";
    $listTable .= "}\n";
    $listTable .= "a {\n";
    $listTable .= "    color:   blue;\n";
    $listTable .= "}\n";
    $listTable .= "-->\n";
    $listTable .= "</style>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body>\n";
    $listTable .= "<center>\n";
    $listTable .= "    <form name='entry_form' action='product_groupMaster_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "               <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $listTable .= "                   ���ʥ��롼�ץ����ɰ���\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
    if ($result->get('num_m') > 0) {
        $field = $result->get_array2('field_m');
        for ($i=0; $i<$result->get('num_m'); $i++) {    // �ե�����ɿ�ʬ���֤�\n";
            $listTable .= "        <th class='winbox' nowrap>". $field[$i] ."</th>\n";
        }
    } else {
        $listTable .= "            <th class='winbox' nowrap>���ʥ��롼�ץ�����</th>\n";
        $listTable .= "            <th class='winbox' nowrap>���ʥ��롼��̾</th>\n";
        $listTable .= "            <th class='winbox' nowrap>�����ѥ��롼��</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' �������� -->\n";
    $listTable .= "            <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res = $result->get_array2('res_m');
    $group_name = $result->get_array2('group_name');
    for ($r=0; $r<$result->get('rows_m'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $listTable .= "                <a href='../product_groupMaster_Main.php?number=". $r ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>    <!-- �ԥʥ�С���ɽ�� -->\n";
        for ($i=0; $i<$result->get('num_m'); $i++) {    // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:                                 // ���롼�ץ�����
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 1:                                 // ���롼��̾
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 2:                                 // �Ȳ��ѥ��롼��̾
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $group_name[$r] ."</div></td>\n";
                    break;
                default:
                    break;
            }
        }
        $listTable .= "        </tr>\n";
    }
    $listTable .= "        </TBODY>\n";
    $listTable .= "        </table>\n";
    $listTable .= "            </td></tr>\n";
    $listTable .= "        </table> <!----------------- ���ߡ�End ------------------>\n";
    $listTable .= "    </form>\n";
    $listTable .= "</center>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

////////////// ���ʥ��롼�ץ����ɾȲ���̤�HTML�����
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '�Ȳ�');
    ////////// HTML�ե��������
    $file_name = "list/product_groupMaster_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
}
