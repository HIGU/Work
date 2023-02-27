<?php
//////////////////////////////////////////////////////////////////////////////
// ����Ψ�׻��ǡ����Խ� �ᥤ�� assemblyRate_costAllocation_Main.php         //
//                             (�� cost_allocation_rate_main.php)           //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/14 Created  assemblyRate_costAllocation_Main.php                 //
//            ��ե�������ƽ�����ؿ��� �����Ȥΰ��֤�Ĵ��             //
//            ;ʬ��<font>�����κ��                                        //
// 2007/12/29 ���եǡ���������ͤ�����                                      //
// 2011/06/22 format_date�Ϥ�tnk_func�˰�ư�Τ��ᤳ�������               //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
session_start();                                 // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');             // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');             // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');           // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
access_log();                                    // Script Name �ϼ�ư����

main();

function main()
{
    ////////////// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                          // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    ////////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('����Ψ�׻��ǡ����Խ�');
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// �꥿���󥢥ɥ쥹����
    $menu->set_RetUrl($_SESSION['various_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    before_date($request, $result);                     // ��������ɽ���ΰ٤�����׻�
    get_costAllocation_master($result, $request);       // ����Ψ�׻��ǡ����ޥ������μ���
    get_costAllocationBefore_master($result, $request); // ����ʬ����Ψ�׻��ǡ����ޥ������μ���
    
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
    require_once ('assemblyRate_costAllocation_View.php');

    ob_end_flush(); 
}

////////////// ��������ɽ���ΰ٤�����׻�
function before_date($request, $result)
{
    $wage_ym = $request->get('wage_ym');
    $nen   = substr($wage_ym, 0, 4);
    $tsuki = substr($wage_ym, 4, 2);
    if (1 == $tsuki) {
        $nen   = $nen - 1;
        $tsuki = 12;
    } else {
        $tsuki = $tsuki - 1;
        if ($tsuki < 10) {
            $tsuki = 0 . $tsuki;
        }
    }
    $wage_ym_b = $nen . $tsuki;
    $result->add('wage_ym_b', $wage_ym_b);
}

////////////// ������ʬ����Ԥ�
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = costAllocation_copy($request, $result);
    if ($request->get('del') != '') $ok = costAllocation_del($request);
    if ($request->get('entry') != '')  $ok = costAllocation_entry($request, $result);
    if ($ok) {
        ////// �ǡ����ν����
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('total_date', '');
        $request->add('item', '');
        $request->add('external_price', '');
        $request->add('external_assy_price', '');
        $request->add('direct_expense', '');
        get_costAllocation_master($result, $request); // ����Ψ�׻��ǡ����ޥ������μ���
    }
}

////////////// �ɲá��ѹ����å� (��ץ쥳���ɿ��������˹Ԥ�)
function costAllocation_entry($request, $result)
{
    if (getCheckAuthority(22)) {                            // ǧ�ڥ����å�
        $total_date = $request->get('wage_ym');
        $query = sprintf("SELECT indirect_cost FROM indirect_cost_allocate WHERE total_date=%d", $total_date);
        $res_check = array();
        $rows_check = getResult($query,$res_check);
        if ($rows_check <= 0) {                             // ����Ψ����Ͽ�Ѥߤ������å�
            $item = $request->get('item');
            $external_price = $request->get('external_price');
            $external_assy_price = $request->get('external_assy_price');
            $direct_expense = $request->get('direct_expense');
            $query = sprintf("SELECT total_date, item FROM indirect_cost_allocate WHERE total_date=%d AND item='%s'", $total_date, $item);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {        // ��Ͽ���� UPDATE ����
                $query = sprintf("UPDATE indirect_cost_allocate SET total_date=%d, item='%s', external_price=%d, external_assy_price=%d, direct_expense=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' 
                                    WHERE total_date=%d AND item='%s'", $total_date, $item, $external_price, $external_assy_price, $direct_expense, $_SESSION['User_ID'], $total_date, $item);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} �оݥ��롼�ס�{$item}�Υǡ����ѹ����ԡ�";               // .= �����
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} �оݥ��롼�ס�{$item}�Υǡ������ѹ����ޤ�����";         // .= �����
                    return true;
                }
            } else {                                        // ��Ͽ�ʤ� INSERT ����   
                $query = sprintf("INSERT INTO indirect_cost_allocate (total_date, item, external_price, external_assy_price, direct_expense, last_date, last_user)
                             VALUES (%d, '%s', %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                             $total_date, $item, $external_price, $external_assy_price, $direct_expense, $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} �оݥ��롼�ס�{$item}�Υǡ�����Ͽ�˼��ԡ�";             // .= �����
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} �оݥ��롼�ס�{$item}�Υǡ�����Ͽ���ɲä��ޤ�����";     // .= �����
                    return true;
                }
            }
        } else {
            if ($res_check[0]['indirect_cost'] == '') {     // ����Ψ����Ͽ�Ѥߤ������å�
                $item = $request->get('item');
                $external_price = $request->get('external_price');
                $external_assy_price = $request->get('external_assy_price');
                $direct_expense = $request->get('direct_expense');
                $query = sprintf("SELECT total_date, item FROM indirect_cost_allocate WHERE total_date=%d AND item='%s'", $total_date, $item);
                $res_chk = array();
                if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
                    $query = sprintf("UPDATE indirect_cost_allocate SET total_date=%d, item='%s', external_price=%d, external_assy_price=%d, direct_expense=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' 
                                        WHERE total_date=%d AND item='%s'", $total_date, $item, $external_price, $external_assy_price, $direct_expense, $_SESSION['User_ID'], $total_date, $item);
                    if (query_affected($query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} �оݥ��롼�ס�{$item}�Υǡ����ѹ����ԡ�";           // .= �����
                        $msg_flg = 'alert';
                        return false;
                    } else {
                        $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} �оݥ��롼�ס�{$item}�Υǡ������ѹ����ޤ�����";     // .= �����
                        return true;
                    }
                } else {                                    // ��Ͽ�ʤ� INSERT ����   
                    $query = sprintf("INSERT INTO indirect_cost_allocate (total_date, item, external_price, external_assy_price, direct_expense, last_date, last_user)
                                 VALUES (%d, '%s', %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                 $total_date, $item, $external_price, $external_assy_price, $direct_expense, $_SESSION['User_ID']);
                    if (query_affected($query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} �оݥ��롼�ס�{$item}�Υǡ�����Ͽ�˼��ԡ�";         // .= �����
                        $msg_flg = 'alert';
                        return false;
                    } else {
                        $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} �оݥ��롼�ס�{$item}�Υǡ�����Ͽ���ɲä��ޤ�����"; // .= �����
                        return true;
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] .= "����Ψ�����Ǥ˳��ꤵ��Ƥ��ޤ���";
                return false;
            }
        }
    } else {                                                // ǧ�ڤʤ����顼
        $_SESSION['s_sysmsg'] .= "�Խ����¤�����ޤ���ɬ�פʾ��ˤϡ�ô���Ԥ�Ϣ���Ʋ�������";
        return false;
    }
}

////////////// ������å� (��ץ쥳���ɿ��������˹Ԥ�)
function costAllocation_del($request)
{
    if (getCheckAuthority(22)) {                           // ǧ�ڥ����å�
        $total_date = $request->get('wage_ym');
        $query = sprintf("SELECT indirect_cost FROM indirect_cost_allocate WHERE total_date=%d", $total_date);
        $res_check = array();
        $rows_check = getResult($query,$res_check);
        if ($rows_check <= 0) {                            // ����Ψ����Ͽ�Ѥߤ������å�
            $item = $request->get('item');
            $query = sprintf("DELETE FROM indirect_cost_allocate WHERE total_date=%d AND item='%s'", $total_date, $item);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} �оݥ��롼�ס�{$item}�κ���˼��ԡ�";       // .= �����
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} �оݥ��롼�ס�{$item}�������ޤ�����";     // .= �����
                return true;
            }
        } else {
            if ($res_check[0]['indirect_cost'] == '') {    // ����Ψ����Ͽ�Ѥߤ������å�
                $item = $request->get('item');
                $query = sprintf("DELETE FROM indirect_cost_allocate WHERE total_date=%d AND item='%s'", $total_date, $item);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} �оݥ��롼�ס�{$item}�κ���˼��ԡ�";   // .= �����
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "�оݷ�{$total_date} �оݥ��롼�ס�{$item}�������ޤ�����"; // .= �����
                    return true;
                }
            } else {
                $_SESSION['s_sysmsg'] .= "����Ψ�����Ǥ˳��ꤵ��Ƥ��ޤ���";
                return false;
            }
        }
    } else {                                               // ǧ�ڤʤ����顼
        $_SESSION['s_sysmsg'] .= "�Խ����¤�����ޤ���ɬ�פʾ��ˤϡ�ô���Ԥ�Ϣ���Ʋ�������";
        return false;
    }
}

////////////// ɽ����(����ɽ)������Ψ�׻��ǡ�����SQL�Ǽ���
function get_costAllocation_master ($result, $request)
{
    $wage_ym = $request->get('wage_ym');
    $query = "
        SELECT  incost.total_date            AS ����ǯ��         -- 0
            ,   incost.item                  AS �оݥ��롼��     -- 1
            ,   incost.external_price        AS ������           -- 2
            ,   incost.external_assy_price   AS ����Assy��       -- 3
            ,   incost.direct_expense        AS ľ����           -- 4
        FROM
            indirect_cost_allocate AS incost
        WHERE
            incost.total_date = $wage_ym
        ORDER BY
            item
    ";

    $res = array();
    $num = 0;
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
    } else {
        $num = count($field);
        $result->add_array2('res_c', $res);
        $result->add_array2('field_c', $field);
        $result->add('num_c', $num);
        $result->add('rows_c', $rows);
    }
}

////////////// ɽ����(����ʬ)������Ψ�׻��ǡ�����SQL�Ǽ���
function get_costAllocationBefore_master($result, $request)
{
    $wage_ym_b = $result->get('wage_ym_b');
    $query = "
        SELECT  incostb.total_date            AS ����ǯ��         -- 0
            ,   incostb.item                  AS �оݥ��롼��     -- 1
            ,   incostb.external_price        AS ������           -- 2
            ,   incostb.external_assy_price   AS ����Assy��       -- 3
            ,   incostb.direct_expense        AS ľ����           -- 4
        FROM
            indirect_cost_allocate AS incostb
        WHERE
            incostb.total_date = $wage_ym_b
        ORDER BY
            item
    ";

    $res = array();
    $num = 0;
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
    } else {
        $num = count($field);
        $result->add_array2('res_b', $res);
        $result->add_array2('field_b', $field);
        $result->add('num_b', $num);
        $result->add('rows_b', $rows);
    }
}

////////////// ���ԡ��Υ�󥯤������줿��
function costAllocation_copy($request, $result)
{
    $r = $request->get('number');
    $res = $result->get_array2('res_c');
    $total_date            = $res[$r][0];
    $item                  = $res[$r][1];
    $external_price        = $res[$r][2];
    $external_assy_price   = $res[$r][3];
    $direct_expense        = $res[$r][4];
    
    $request->add('total_date', $total_date);
    $request->add('item', $item);
    $request->add('external_price', $external_price);
    $request->add('external_assy_price', $external_assy_price);
    $request->add('direct_expense', $direct_expense);
}

////////////// ����Ψ�׻��ǡ����Ȳ���̤�HTML�κ���
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
    $listTable .= "    <form name='entry_form' action='assemblyRate_costAllocation_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "               <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $wage_ym = $request->get('wage_ym');
    $listTable .= "                   ". format_date6_kan($wage_ym) ."\n";
    $listTable .= "                   ����Ψ�׻��ǡ���\n";
    $listTable .= "                   <font size=2>\n";
    $listTable .= "                   (ñ��:��)\n";
    $listTable .= "                   </font>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
    if ($result->get('num_c') > 0) {
        $field = $result->get_array2('field_c');
        for ($i=0; $i<$result->get('num_c'); $i++) {    // �ե�����ɿ�ʬ���֤�\n";
            if ($i == 1) {
            } else {
                $listTable .= "        <th class='winbox' nowrap>". $field[$i] ."</th>\n";
            }
        }
    } else {
        $listTable .= "            <th class='winbox' nowrap>�оݥ��롼��</th>\n";
        $listTable .= "            <th class='winbox' nowrap>������</th>\n";
        $listTable .= "            <th class='winbox' nowrap>����ASSY��</th>\n";
        $listTable .= "            <th class='winbox' nowrap>ľ����</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' �������� -->\n";
    $listTable .= "            <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res = $result->get_array2('res_c');
    for ($r=0; $r<$result->get('rows_c'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $listTable .= "                <a href='../assemblyRate_costAllocation_Main.php?number=". $r ."&wage_ym=". $request->get('wage_ym') ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>    <!-- �ԥʥ�С���ɽ�� -->\n";
        for ($i=0; $i<$result->get('num_c'); $i++) {    // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:                                 // ����ǯ��
                    break;
                case 1:                                 // �оݥ��롼��
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 2:                                 // ������
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 3:                                 // ����ASSY��
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:                                 // ľ����
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
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
    $listTable .= "    <form name='entry_form' action='assemblyRate_costAllocation_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "               <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $wage_ym_b = $result->get('wage_ym_b');
    $listTable .= "                   ������\n";
    $listTable .= "                   ". format_date6_kan($wage_ym_b) ."\n";
    $listTable .= "                   ����Ψ�׻��ǡ���\n";
    $listTable .= "                   <font size=2>\n";
    $listTable .= "                   (ñ��:��)\n";
    $listTable .= "                   </font>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
    if ($result->get('num_b') > 0) {
        $field = $result->get_array2('field_b');
        for ($i=0; $i<$result->get('num_b'); $i++) {    // �ե�����ɿ�ʬ���֤�\n";
            if ($i == 1) {
            } else {
                $listTable .= "        <th class='winbox' nowrap>". $field[$i] ."</th>\n";
            }
        }
    } else {
        $listTable .= "            <th class='winbox' nowrap>�оݥ��롼��</th>\n";
        $listTable .= "            <th class='winbox' nowrap>������</th>\n";
        $listTable .= "            <th class='winbox' nowrap>����ASSY��</th>\n";
        $listTable .= "            <th class='winbox' nowrap>ľ����</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' �������� -->\n";
    $listTable .= "            <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res = $result->get_array2('res_b');
    $group_name = $result->get_array2('group_name_b');
    for ($r=0; $r<$result->get('rows_b'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $cnum = $r + 1;
        $listTable .= "            ". $cnum ."\n";
        $listTable .= "            </td>    <!-- �ԥʥ�С���ɽ�� -->\n";
        for ($i=0; $i<$result->get('num_b'); $i++) {    // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:                                 // ����ǯ��
                    break;
                case 1:                                 // �оݥ��롼��
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 2:                                 // ������
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 3:                                 // ����ASSY��
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:                                 // ľ����
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
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

////////////// ����Ψ�׻��ǡ����Ȳ���̤�HTML�����
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '�Ȳ�');
    ////////// HTML�ե��������
    $file_name = "list/assemblyRate_costAllocation_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
}
