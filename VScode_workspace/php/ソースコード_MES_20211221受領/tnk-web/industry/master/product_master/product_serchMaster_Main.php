<?php
//////////////////////////////////////////////////////////////////////////////
// �Ȳ��ѥ��롼�פ���Ͽ �ᥤ�� product_serchMaster_Main.php                 //
// Copyright (C) 2009-2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/11/24 Created  product_serchMaster_Main.php                         //
// 2009/11/26 �����Ȥ�����                                                //
// 2010/12/11 ��ʬ�॰�롼�פ���Ͽ���ɲ�                                    //
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
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('�Ȳ��ѥ��롼�פ���Ͽ');
    //////////// ����ؤ�GET�ǡ�������
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// �꥿���󥢥ɥ쥹����
    $menu->set_RetUrl($_SESSION['product_master_referer']);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    get_serch_master($result, $request);                // ���롼�ץޥ������μ���
    get_product_master($result, $request);                          // �Ƽ�ǡ����μ���
    get_productUnreg_master ($result);
    
    request_check($request, $result, $menu);           // ������ʬ�������å�
    
    outViewListHTML($request, $menu, $result);    // HTML����
    
    display($menu, $request, $result);          // ����ɽ��
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
    require_once ('product_serchMaster_View.php');

    ob_end_flush(); 
}

////////////// ������ʬ����Ԥ�
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = productMaster_copy($request, $result);
    if ($request->get('del') != '') $ok = productMaster_del($request);
    if ($request->get('entry') != '')  $ok = productMaster_entry($request, $result);
    if ($ok) {
        ////// �ǡ����ν����
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('group_no', '');
        $request->add('group_name', '');
        get_serch_master($result, $request);    // �Ƽ�ǡ����μ���
        get_product_master($result, $request); // ��������ǡ����ޥ������μ���
        get_productUnreg_master ($result);
    }
}

////////////// �ɲá��ѹ����å� (��ץ쥳���ɿ��������˹Ԥ�)
function productMaster_entry($request, $result)
{
    if (getCheckAuthority(22)) {                    // ǧ�ڥ����å�
        $group_no = $request->get('group_no');
        $group_name = $request->get('group_name');
        $top_code = $request->get('top_code');
        $query = sprintf("SELECT group_no FROM product_serchGroup WHERE group_no=%d", $group_no);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
            $query = sprintf("UPDATE product_serchGroup SET group_no=%d, group_name='%s', top_code=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d", $group_no, $group_name, $top_code, $_SESSION['User_ID'], $group_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "���롼���ֹ桧{$group_no} �Υ��롼��̾��{$group_name}���ѹ����ԡ�";      // .= �����
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "���롼���ֹ桧{$group_no}�� ���롼��̾��{$group_name}���ѹ����ޤ�����"; // .= �����
                return true;
            }
        } else {                                    // ��Ͽ�ʤ� INSERT ����   
            $query = sprintf("INSERT INTO product_serchGroup (group_no, group_name, top_code, last_date, last_user)
                              VALUES (%d, '%s', %d, CURRENT_TIMESTAMP, '%s')",
                                $group_no, $group_name, $top_code, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "���롼���ֹ桧{$group_no} ���롼��̾��{$group_name}���ɲä˼��ԡ�";      // .= �����
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "���롼���ֹ桧{$group_no} ���롼��̾��{$group_name}���ɲä��ޤ�����";    // .= �����
                return true;
            }
        }
    } else {                                        // ���¤ʤ����顼
        $_SESSION['s_sysmsg'] .= "�Խ����¤�����ޤ���ɬ�פʾ��ˤϡ�ô���Ԥ�Ϣ���Ʋ�������";
        return false;
    }
}

////////////// ������å� (��ץ쥳���ɿ��������˹Ԥ�)
function productMaster_del($request)
{
    if (getCheckAuthority(22)) {    // ǧ�ڥ����å�
        $group_no = $request->get('group_no');
        $group_name = $request->get('group_name');
        $query = sprintf("SELECT * FROM mshgnm WHERE mhggp=%d LIMIT 1", $group_no);
        $res_chk = array();
        if (getResult($query, $res_chk) > 0) {    // ��Ͽ����
            $_SESSION['s_sysmsg'] .= "���Υ��롼���ֹ�Ϥ��Ǥ�¾�Υޥ������ǻ��Ѥ���Ƥ��ޤ���";
            return false;
        }
        $query = sprintf("DELETE FROM product_serchGroup WHERE group_no = %d", $group_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "���롼���ֹ桧{$group_no} ���롼��̾��{$group_name}�κ���˼��ԡ�";   // .= �����
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "���롼���ֹ桧{$group_no} ���롼��̾��{$group_name}�������ޤ�����"; // .= �����
            return true;
        }
    } else {                        // ���¤ʤ����顼
        $_SESSION['s_sysmsg'] .= "�Խ����¤�����ޤ���ɬ�פʾ��ˤϡ�ô���Ԥ�Ϣ���Ʋ�������";
        return false;
    }
}
////////////// ɽ����(����ɽ)�Υ��롼�ץޥ������ǡ�����SQL�Ǽ���
function get_serch_master ($result, $request)
{
    $query_g = "
        SELECT  groupm.top_no                AS ���롼���ֹ�     -- 0
            ,   groupm.top_name              AS ���롼��̾       -- 1
        FROM
            product_top_serchGroup AS groupm
        ORDER BY
            top_name
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] = "���롼�פ���Ͽ������ޤ���";
        $field_g[0]   = "���롼���ֹ�";
        $field_g[1]   = "���롼��̾";
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
////////////// ɽ����(����ɽ)�Υ��롼�ץޥ������ǡ�����SQL�Ǽ���
function get_product_master ($result, $request)
{
    $query = "
        SELECT  groupm.group_no                AS ���롼���ֹ�     -- 0
            ,   groupm.group_name              AS ���롼��̾       -- 1
            ,   groupm.top_code                AS ��ʬ�॰�롼��   -- 2
        FROM
            product_serchGroup AS groupm
        ORDER BY
            group_no
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "���롼�פ���Ͽ������ޤ���";
        $field[0]   = "���롼���ֹ�";
        $field[1]   = "���롼��̾";
        $field[2]   = "��ʬ�॰�롼��";
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
        $result->add_array2('res', '');
        $result->add_array2('field', '');
        $result->add('num', 3);
        $result->add('rows', '');
    } else {
        $num = count($field);
        $result->add_array2('res', $res);
        $result->add_array2('field', $field);
        $result->add('num', $num);
        $result->add('rows', $rows);
        $res_g = $result->get_array2('res_g');
        for ($r=0; $r<$rows; $r++) {    // ���롼���ֹ�ȥ��롼��̾���֤�����
            $top_name[$r] = "��";
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                if($res[$r][2] == $res_g[$i][0]) {
                    $top_name[$r] = $res_g[$i][1];
                }
            }
        }
        $result->add_array2('top_name', $top_name);
    }
}
////////////// ɽ����(����ɽ)�θ����ѥ��롼��̤��Ͽ������SQL�Ǽ���
function get_productUnreg_master ($result)
{
    $query_num = "
        SELECT  count(*) as num
        FROM
            product_serchGroup
        WHERE 
            top_code = 0
    ";

    $res_num = array();
    if (getResult($query_num, $res_num) <= 0) {
        $unreg_num = 0;
        $result->add('unreg_num', $unreg_num);
    } else {
        $unreg_num = $res_num[0]['num'];
        $result->add('unreg_num', $unreg_num);
    }
}

////////////// ���ԡ��Υ�󥯤������줿��
function productMaster_copy($request, $result)
{
    $res = $result->get_array2('res');
    $r = $request->get('number');
    $group_no   = $res[$r][0];
    $group_name = $res[$r][1];
    $top_code   = $res[$r][2];
    $request->add('group_no', $group_no);
    $request->add('group_name', $group_name);
    $request->add('top_code', $top_code);
}

////////////// �Ȳ��ѥ��롼�ץ����ɾȲ���̤�HTML�κ���
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
    $listTable .= "    a:active {\n";
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
    $listTable .= "    <form name='entry_form' action='product_serchMaster_Main.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='700' bgcolor='#ffffc6' align='center' colspan='15'>\n";
    $listTable .= "                ���롼�ץޥ�����\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
    $field = $result->get_array2('field');
    for ($i=0; $i<$result->get('num'); $i++) {        // �ե�����ɿ�ʬ���֤�\n";
        $listTable .= "            <th class='winbox' nowrap>". $field[$i] ."</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    for ($r=0; $r<$result->get('rows'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>    <!-- ����ѹ��Ѥ�������˥��ԡ�  -->\n";
        $listTable .= "                <a href='../product_serchMaster_Main.php?number=". $r ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>\n";
        $res = $result->get_array2('res');
        $top_name = $result->get_array2('top_name');
        for ($i=0; $i<$result->get('num'); $i++) {    // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:                                 // ���롼���ֹ�
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                break;
                case 1:                                 // ���롼��̾
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                break;
                case 2:                                 // ��ʬ�॰�롼��
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $top_name[$r] ."</div></td>\n";
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

////////////// �Ȳ������ʥ��롼�װ���ɽ����HTML�����
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '�Ȳ�');
    ////////// HTML�ե��������
    $file_name = "list/product_serchMaster_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // file������rw�⡼�ɤˤ���
}
