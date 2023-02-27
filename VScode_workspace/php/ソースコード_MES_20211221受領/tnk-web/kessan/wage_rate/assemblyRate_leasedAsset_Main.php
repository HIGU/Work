<?php
//////////////////////////////////////////////////////////////////////////////
// �꡼�����Խ� �ᥤ�� assemblyRate_leasedAsset_Main.php                  //
//                       (�� leased_asset_master_main.php)                  //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/13 Created  assemblyRate_leasedAsset_Main.php                    //
//            ��ե�������ƽ�����ؿ��� �����Ȥΰ��֤�Ĵ��             //
//            ;ʬ��<font>�����κ��                                        //
// 2007/12/14 �ץ����κǸ�˲��Ԥ��ɲ�                                  //
// 2007/12/29 ���եǡ���������ͤ�����                                      //
// 2008/01/09 ���ԡ����������եǡ������Ϥ��ʤ��ä��Τ���                  //
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
    $menu->set_title('�꡼������Ģ���Խ�');
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// �꥿���󥢥ɥ쥹����
    $menu->set_RetUrl($_SESSION['various_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    get_group_master($result, $request);                // ���롼�ץޥ������μ���
    get_leased_master ($result, $request);              // �꡼���񻺥ޥ������μ���
    
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
    require_once ('assemblyRate_leasedAsset_View.php');

    ob_end_flush(); 
}

////////////// ������ʬ����Ԥ�
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = leasedMaster_copy($request, $result);
    if ($request->get('del') != '') $ok = leasedMaster_del($request);
    if ($request->get('entry') != '')  $ok = leasedMaster_entry($request, $result);
    if ($ok) {
        ////// �ǡ����ν����
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('group_no', '');
        $request->add('asset_no', '');
        $request->add('asset_name', '');
        $request->add('acquisition_money', '');
        $request->add('acquisition_date', '');
        $request->add('annual_lease_money', '');
        $request->add('end_date', '');
        get_group_master($result, $request);      // ���롼�ץޥ������μ���
        get_leased_master ($result, $request);    // �꡼���񻺥ޥ������μ���
    }
}

////////////// �ɲá��ѹ����å� (��ץ쥳���ɿ��������˹Ԥ�)
function leasedMaster_entry($request, $result)
{
    if (getCheckAuthority(22)) {                    // ǧ�ڥ����å�
        $group_no = $request->get('group_no');
        $asset_no = $request->get('asset_no');
        $asset_name = $request->get('asset_name');
        $acquisition_money = $request->get('acquisition_money');
        $acquisition_date = $request->get('acquisition_date');
        $annual_lease_money = $request->get('annual_lease_money');
        $end_date = $request->get('end_date');
        if ($end_date == 0) {
            $end_date = '';
        }
        $query = sprintf("SELECT asset_no FROM leased_asset_master WHERE asset_no='%s'", $asset_no);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
            $query = sprintf("UPDATE assembly_machine_group_leased_asset SET group_no=%d, asset_no='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE asset_no='%s'", $group_no, $asset_no, $_SESSION['User_ID'], $asset_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "�꡼����No.��{$asset_no} �꡼����̾��{$asset_name}���ѹ����ԡ�";    // .= �����
                $msg_flg = 'alert';
                return false;
            }                                       // ���롼�פȻ�No�Υǡ����١����ѹ���ޥ������ѹ�
            $query = sprintf("UPDATE leased_asset_master SET asset_no='%s', asset_name='%s', acquisition_money=%d, acquisition_date=%d, annual_lease_money=%d, end_date=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' 
                                WHERE asset_no='%s'", $asset_no, $asset_name, $acquisition_money, $acquisition_date, $annual_lease_money, $end_date, $_SESSION['User_ID'], $asset_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "�꡼����No.��{$asset_no} �꡼����̾��{$asset_name}���ѹ����ԡ�";    // .= �����
                $msg_flg = 'alert';
                return false;
            }
            $_SESSION['s_sysmsg'] .= "�꡼����No.��{$asset_no} �꡼����̾��{$asset_name}�����Ƥ��ѹ����ޤ�����";    // .= �����
            return true;
        } else {                                    // ��Ͽ�ʤ� INSERT ����   
            $query = sprintf("INSERT INTO leased_asset_master (asset_no, asset_name, acquisition_money, acquisition_date, annual_lease_money, end_date, last_date, last_user)
                              VALUES ('%s', '%s', %d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                              $asset_no, $asset_name, $acquisition_money, $acquisition_date, $annual_lease_money, $end_date, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "�꡼����No.��{$asset_no} �꡼����̾��{$asset_name}����Ͽ�˼��ԡ�";    // .= �����
                $msg_flg = 'alert';
                return false;
            } else {                                // �ޥ������ɲø奰�롼�פȻ�No��DB����
                $query = sprintf("INSERT INTO assembly_machine_group_leased_asset (group_no, asset_no, last_date, last_user)
                                  VALUES (%d, '%s', CURRENT_TIMESTAMP, '%s')",
                                  $group_no, $asset_no, $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {  // ���롼�פȻ�NoDB�������Ի��ϥޥ���������
                    $query = sprintf("DELETE FROM leased_asset_master WHERE asset_no = '%s'", $asset_no);
                    $_SESSION['s_sysmsg'] .= "�꡼����No.��{$asset_no} �꡼����̾��{$asset_name}����Ͽ�˼��ԡ�";    // .= �����
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "�꡼����No.��{$asset_no} �꡼����̾��{$asset_name}���ɲä��ޤ�����";    // .= �����
                    return true;
                }
            }
        }
    } else {                                        // ǧ�ڤʤ����顼
        $_SESSION['s_sysmsg'] .= "�Խ����¤�����ޤ���ɬ�פʾ��ˤϡ�ô���Ԥ�Ϣ���Ʋ�������";
        return false;
    }
}

////////////// ������å� (��ץ쥳���ɿ��������˹Ԥ�)
function leasedMaster_del($request)
{
    if (getCheckAuthority(22)) {    // ǧ�ڥ����å�
        $asset_no = $request->get('asset_no');
        $asset_name = $request->get('asset_name');
        $query = sprintf("DELETE FROM leased_asset_master WHERE asset_no = '%s'", $asset_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "�꡼����No.��{$asset_no} �꡼����̾��{$asset_no}�κ���˼��ԡ�";    // .= �����
            $msg_flg = 'alert';
            return false;
        } else {                    // �ޥ�������������奰�롼�פȻ�No��DB�������
            $query = sprintf("DELETE FROM assembly_machine_group_leased_asset WHERE asset_no = '%s'", $asset_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "�꡼����No.��{$asset_no} �꡼����̾��{$asset_name}�κ���˼��ԡ�";    // .= �����
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "�꡼����No.��{$asset_no} �꡼����̾��{$asset_name}�������ޤ�����";    // .= �����
                return true;
            }
        }
    } else {                        // ǧ�ڤʤ����顼
        $_SESSION['s_sysmsg'] .= "�Խ����¤�����ޤ���ɬ�פʾ��ˤϡ�ô���Ԥ�Ϣ���Ʋ�������";
        return false;
    }
}
////////////// ɽ����(����ɽ)�Υ��롼�ץޥ������ǡ�����SQL�Ǽ���
function get_group_master ($result, $request)
{
    $query_g = "
        SELECT  groupm.group_no                AS ���롼���ֹ�     -- 0
            ,   groupm.group_name              AS ���롼��̾       -- 1
        FROM
            assembly_machine_group_master AS groupm
        ORDER BY
            group_no
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

////////////// ɽ����(����ɽ)�Υ꡼���񻺥ǡ�����SQL�Ǽ���
function get_leased_master ($result, $request)
{
    $query = "
        SELECT  groupl.group_no                AS ���롼��̾       -- 0
            ,   groupl.asset_no                AS �����No       -- 1
            ,   lmaster.asset_name             AS ��̾��         -- 2
            ,   lmaster.acquisition_money      AS �������         -- 3
            ,   lmaster.acquisition_date       AS ����ǯ��         -- 4
            ,   lmaster.annual_lease_money     AS ǯ�֥꡼����     -- 5
            ,   lmaster.end_date               AS ��λǯ��         -- 6
        FROM
            assembly_machine_group_leased_asset AS groupl
        LEFT OUTER JOIN
            leased_asset_master AS lmaster
        ON (groupl.asset_no = lmaster.asset_no)
        ORDER BY
            group_no
        ";
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
    } else {
        $num = count($field);
        $result->add_array2('res_l', $res);
        $result->add_array2('field_l', $field);
        $result->add('num_l', $num);
        $result->add('rows_l', $rows);
    }
    $res_g = $result->get_array2('res_g');
    for ($r=0; $r<$rows; $r++) {    // ���롼���ֹ�ȥ��롼��̾���֤�����(����񻺡�
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res[$r][0] == $res_g[$i][0]) {
                $group_name[$r] = $res_g[$i][1];
            }
        }
    }
    $result->add_array2('group_name', $group_name);
}
////////////// ���ԡ��Υ�󥯤������줿��
function leasedMaster_copy($request, $result)
{
    $r = $request->get('number');
    $res = $result->get_array2('res_l');
    $group_no           = $res[$r][0];
    $asset_no           = $res[$r][1];
    $asset_name         = $res[$r][2];
    $acquisition_money  = $res[$r][3];
    $acquisition_date   = $res[$r][4];
    $annual_lease_money = $res[$r][5];
    if ($res[$r][6] == 0) {
        $end_date = '';
    } else {
        $end_date = $res[$r][6];
    }
    $request->add('group_no', $group_no);
    $request->add('asset_no', $asset_no);
    $request->add('asset_name', $asset_name);
    $request->add('acquisition_money', $acquisition_money);
    $request->add('acquisition_date', $acquisition_date);
    $request->add('annual_lease_money', $annual_lease_money);
    $request->add('end_date', $end_date);
}

////////////// �꡼���񻺾Ȳ���̤�HTML�κ���
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
    $listTable .= "    <form name='entry_form' action='assemblyRate_leasedAsset_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $listTable .= "                    �꡼������Ģ\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
    if ($result->get('num_l') > 0) {
        $field = $result->get_array2('field_l');
        for ($i=0; $i<$result->get('num_l'); $i++) {    // �ե�����ɿ�ʬ���֤�\n";
            $listTable .= "        <th class='winbox' nowrap>". $field[$i] ."</th>\n";
        }
    } else {
        $listTable .= "            <th class='winbox' nowrap>���롼��̾</th>\n";
        $listTable .= "            <th class='winbox' nowrap>�꡼����No.</th>\n";
        $listTable .= "            <th class='winbox' nowrap>��̾��</th>\n";
        $listTable .= "            <th class='winbox' nowrap>�������</th>\n";
        $listTable .= "            <th class='winbox' nowrap>����ǯ��</th>\n";
        $listTable .= "            <th class='winbox' nowrap>ǯ�֥꡼����</th>\n";
        $listTable .= "            <th class='winbox' nowrap>��λǯ��</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' �������� -->\n";
    $listTable .= "            <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res = $result->get_array2('res_l');
    $group_name = $result->get_array2('group_name');
    for ($r=0; $r<$result->get('rows_l'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $listTable .= "                <a href='../assemblyRate_leasedAsset_Main.php?number=". $r ."&wage_ym=". $request->get('wage_ym') ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>    <!-- �ԥʥ�С���ɽ�� -->\n";
        for ($i=0; $i<$result->get('num_l'); $i++) {    // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:                                 // ���롼��
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $group_name[$r] ."</div></td>\n";
                    break;
                case 1:                                 // ��No.
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 2:                                 // ̾��
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 3:                                 // �������
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:                                 // ����ǯ��
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date6($res[$r][$i]) ."</div></td>\n";
                    break;
                case 5:                                 // ǯ�֥꡼����
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 6:                                 // ��λǯ��
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date6($res[$r][$i]) ."</div></td>\n";
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

////////////// ��Ψ�Ȳ���̤�HTML�����
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '�Ȳ�');
    ////////// HTML�ե��������
    $file_name = "list/assemblyRate_leasedAsset_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
}
