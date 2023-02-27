<?php
//////////////////////////////////////////////////////////////////////////////
// ���롼�ץޥ������Խ� �ᥤ�� assemblyRate_groupMaster_Main.php            //
//                             (�� group_master_main.php)                   //
// Copyright (C) 2007-2008 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/07 Created  assemblyRate_groupMaster_Main.php                    //
// 2007/12/13 ;ʬ��font�����κ�� �����Ȥΰ���Ĵ��                       //
// 2007/12/14 �ץ����κǸ�˲��Ԥ��ɲ�                                  //
// 2007/12/21 ����񻺡��꡼���񻺡���������ǡ����ǥ��롼�פ�����        //
//            ����Ƥ�����祰�롼�פ����Ǥ��ʤ��褦���ѹ�                //
// 2007/12/29 ���եǡ���������ͤ�����                                      //
// 2008/01/09 ���ԡ����������եǡ������Ϥ��ʤ��ä��Τ���                  //
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
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('���롼�ץޥ��������Խ�');
    //////////// ����ؤ�GET�ǡ�������
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// �꥿���󥢥ɥ쥹����
    $menu->set_RetUrl($_SESSION['various_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    get_group_master($result, $request);                          // �Ƽ�ǡ����μ���
    
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
    require_once ('assemblyRate_groupMaster_View.php');

    ob_end_flush(); 
}

////////////// ������ʬ����Ԥ�
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = groupMaster_copy($request, $result);
    if ($request->get('del') != '') $ok = groupMaster_del($request);
    if ($request->get('entry') != '')  $ok = groupMaster_entry($request, $result);
    if ($ok) {
        ////// �ǡ����ν����
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('group_no', '');
        $request->add('group_name', '');
        get_group_master($result, $request);    // �Ƽ�ǡ����μ���
    }
}

////////////// �ɲá��ѹ����å� (��ץ쥳���ɿ��������˹Ԥ�)
function groupMaster_entry($request, $result)
{
    if (getCheckAuthority(22)) {                    // ǧ�ڥ����å�
        $group_no = $request->get('group_no');
        $group_name = $request->get('group_name');
        $query = sprintf("SELECT group_no FROM assembly_machine_group_master WHERE group_no=%d", $group_no);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
            $query = sprintf("UPDATE assembly_machine_group_master SET group_no=%d, group_name='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d", $group_no, $group_name, $_SESSION['User_ID'], $group_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "���롼���ֹ桧{$group_no} �Υ��롼��̾��{$group_name}���ѹ����ԡ�";      // .= �����
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "���롼���ֹ桧{$group_no}�� ���롼��̾��{$group_name}���ѹ����ޤ�����"; // .= �����
                return true;
            }
        } else {                                    // ��Ͽ�ʤ� INSERT ����   
            $query = sprintf("INSERT INTO assembly_machine_group_master (group_no, group_name, last_date, last_user)
                              VALUES (%d, '%s', CURRENT_TIMESTAMP, '%s')",
                                $group_no, $group_name, $_SESSION['User_ID']);
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
function groupMaster_del($request)
{
    if (getCheckAuthority(22)) {    // ǧ�ڥ����å�
        $group_no = $request->get('group_no');
        $group_name = $request->get('group_name');
        $query = sprintf("SELECT * FROM assembly_machine_group_capital_asset WHERE group_no=%d LIMIT 1", $group_no);
        $res_chk = array();
        if (getResult($query, $res_chk) > 0) {    // ��Ͽ����
            $_SESSION['s_sysmsg'] .= "���Υ��롼���ֹ�Ϥ��Ǥ�¾�Υޥ������ǻ��Ѥ���Ƥ��ޤ���";
            return false;
        }
        $query = sprintf("SELECT * FROM assembly_machine_group_leased_asset WHERE group_no=%d LIMIT 1", $group_no);
        $res_chk = array();
        if (getResult($query, $res_chk) > 0) {    // ��Ͽ����
            $_SESSION['s_sysmsg'] .= "���Υ��롼���ֹ�Ϥ��Ǥ�¾�Υޥ������ǻ��Ѥ���Ƥ��ޤ���";
            return false;
        }
        $query = sprintf("SELECT * FROM assembly_machine_group_work WHERE group_no=%d LIMIT 1", $group_no);
        $res_chk = array();
        if (getResult($query, $res_chk) > 0) {    // ��Ͽ����
            $_SESSION['s_sysmsg'] .= "���Υ��롼���ֹ�Ϥ��Ǥ�¾�Υޥ������ǻ��Ѥ���Ƥ��ޤ���";
            return false;
        }
        $query = sprintf("DELETE FROM assembly_machine_group_master WHERE group_no = %d", $group_no);
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

////////////// ���ԡ��Υ�󥯤������줿��
function groupMaster_copy($request, $result)
{
    $res_g = $result->get_array2('res_g');
    $r = $request->get('number');
    $group_no   = $res_g[$r][0];
    $group_name = $res_g[$r][1];
    $request->add('group_no', $group_no);
    $request->add('group_name', $group_name);
}

////////////// ���롼�ץޥ������Ȳ���̤�HTML�κ���
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
    $listTable .= "    <form name='entry_form' action='assemblyRate_groupMaster_Main.php' method='post' target='_parent'>\n";
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
    $field_g = $result->get_array2('field_g');
    for ($i=0; $i<$result->get('num_g'); $i++) {        // �ե�����ɿ�ʬ���֤�\n";
        $listTable .= "            <th class='winbox' nowrap>". $field_g[$i] ."</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    for ($r=0; $r<$result->get('rows_g'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>    <!-- ����ѹ��Ѥ�������˥��ԡ�  -->\n";
        $listTable .= "                <a href='../assemblyRate_groupMaster_Main.php?number=". $r ."&wage_ym=". $request->get('wage_ym') ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>\n";
        $res_g = $result->get_array2('res_g');
        for ($i=0; $i<$result->get('num_g'); $i++) {    // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:                                 // ���롼���ֹ�
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 1:                                 // ���롼��̾
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
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
    $file_name = "list/assemblyRate_groupMaster_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // file������rw�⡼�ɤˤ���
}
