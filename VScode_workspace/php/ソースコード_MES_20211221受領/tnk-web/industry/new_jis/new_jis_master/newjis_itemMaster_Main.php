<?php
//////////////////////////////////////////////////////////////////////////////
// ��JIS�о����ʥޥ���������Ͽ �ᥤ�� newjis_itemMaster_Main.php            //
// Copyright (C) 2014-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/11/17 Created  newjis_itemMaster_Main.php                           //
// 2014/12/02 �Խ����¤�39������                                            //
// 2014/12/08 ���ܢ��������ѹ�                                              //
// 2014/12/22 �������������ѹ�                                              //
// 2017/04/27 �ƥ�˥塼��ɽ�����ؿ�JIS�٤���                      ��ë //
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
    $menu->set_title('�о����ʥޥ���������Ͽ');
    //////////// ����ؤ�GET�ǡ�������
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// �꥿���󥢥ɥ쥹����
    $menu->set_RetUrl($_SESSION['newjis_master_referer']);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    get_serch_master($result, $request);                // ���롼�ץޥ������μ���
    get_product_master($result, $request);                          // �Ƽ�ǡ����μ���
    
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
    require_once ('newjis_itemMaster_View.php');

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
        $request->add('assy_no', '');
        $request->add('newjis_group_code', '');
        get_serch_master($result, $request);    // �Ƽ�ǡ����μ���
        get_product_master($result, $request); // ��������ǡ����ޥ������μ���
    }
}

////////////// �ɲá��ѹ����å� (��ץ쥳���ɿ��������˹Ԥ�)
function productMaster_entry($request, $result)
{
    if (getCheckAuthority(39)) {                    // ǧ�ڥ����å�
        $assy_no = $request->get('assy_no');
        $newjis_group_code = $request->get('newjis_group_code');
        $newjis_group_name = get_group_name($newjis_group_code);
        $query = sprintf("SELECT assy_no FROM new_jis_item_master WHERE assy_no='%s'", $assy_no);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
            $query = sprintf("UPDATE new_jis_item_master SET assy_no='%s', newjis_group_code=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE assy_no='%s'", $assy_no, $newjis_group_code, $_SESSION['User_ID'], $assy_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "�����ֹ桧{$assy_no} �򷿼���{$newjis_group_name} ���ѹ����ԡ�";      // .= �����
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "�����ֹ桧{$assy_no} �򷿼���{$newjis_group_name} ���ѹ����ޤ�����"; // .= �����
                return true;
            }
        } else {                                    // ��Ͽ�ʤ� INSERT ����   
            $query = sprintf("INSERT INTO new_jis_item_master (assy_no, newjis_group_code, last_date, last_user)
                              VALUES ('%s', %d, CURRENT_TIMESTAMP, '%s')",
                                $assy_no, $newjis_group_code, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "�����ֹ桧{$assy_no} ������{$newjis_group_name} ���ɲä˼��ԡ�";      // .= �����
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "�����ֹ桧{$assy_no} ������{$newjis_group_name} ���ɲä��ޤ�����";    // .= �����
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
    if (getCheckAuthority(39)) {    // ǧ�ڥ����å�
        $assy_no = $request->get('assy_no');
        $newjis_group_code = $request->get('newjis_group_code');
        $newjis_group_name = get_group_name($newjis_group_code);
        $query = sprintf("DELETE FROM new_jis_item_master WHERE assy_no = '%s'", $assy_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "�����ֹ桧{$assy_no} ������{$newjis_group_name} �κ���˼��ԡ�";   // .= �����
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "�����ֹ桧{$assy_no} ������{$newjis_group_name} �������ޤ�����"; // .= �����
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
        SELECT  selectm.newjis_group_code              AS �ٱ��襳����     -- 0
            ,   selectm.newjis_group_name              AS �ٱ���̾         -- 1
        FROM
            new_jis_select_master AS selectm
        ORDER BY
            newjis_group_code
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] = "��������Ͽ������ޤ���";
        $field_g[0]   = "����������";
        $field_g[1]   = "����";
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', $field_g);
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
////////////// ��å�����ɽ���Ѥλٱ���̾����
function get_group_name ($group_code)
{
    $query = sprintf("select newjis_group_name
                        FROM
                            new_jis_select_master
                        WHERE
                            newjis_group_code=%d
                        LIMIT 1
                        ", $group_code);
    getUniResult($query, $group_name);
    return $group_name;
}
////////////// ɽ����(����ɽ)�Υ��롼�ץޥ������ǡ�����SQL�Ǽ���
function get_product_master ($result, $request)
{
    $query = "
        SELECT  selectm.assy_no                AS �����ֹ�     -- 0
            ,   selectm.newjis_group_code      AS ����         -- 1
        FROM
            new_jis_item_master AS selectm
        ORDER BY
            selectm.newjis_group_code ASC, selectm.assy_no ASC
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "�����ֹ����Ͽ������ޤ���";
        $field[0]   = "�����ֹ�";
        $field[1]   = "����";
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
        $result->add_array2('res', '');
        $result->add_array2('field', $field);
        $result->add('num', 1);
        $result->add('rows', '');
    } else {
        $num = count($field);
        $result->add_array2('res', $res);
        $result->add_array2('field', $field);
        $result->add('num', $num);
        $result->add('rows', $rows);
        $res_g = $result->get_array2('res_g');
        for ($r=0; $r<$rows; $r++) {    // ���롼���ֹ�ȥ��롼��̾���֤�����
            $group_name[$r] = "��";
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                if($res[$r][1] == $res_g[$i][0]) {
                    $group_name[$r] = $res_g[$i][1];
                }
            }
        }
        $result->add_array2('group_name', $group_name);
    }
}

////////////// ���ԡ��Υ�󥯤������줿��
function productMaster_copy($request, $result)
{
    $res = $result->get_array2('res');
    $r = $request->get('number');
    $assy_no     = $res[$r][0];
    $newjis_group_code = $res[$r][1];
    $request->add('assy_no', $assy_no);
    $request->add('newjis_group_code', $newjis_group_code);
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
    $listTable .= ".pt11 {\n";
    $listTable .= "    font-size:          11pt;\n";
    $listTable .= "    font-weight:        normal;\n";
    $listTable .= "    font-family:        monospace;\n";
    $listTable .= "}\n";
    $listTable .= ".pt11b {\n";
    $listTable .= "    font-size:          11pt;\n";
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
    $listTable .= "    <form name='entry_form' action='newjis_itemMaster_Main.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td bgcolor='#ffffc6' align='center' colspan='4' nowrap>\n";
    $listTable .= "                �о����ʥޥ�����\n";
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
        $listTable .= "                <a href='../newjis_itemMaster_Main.php?number=". $r ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>\n";
        $res = $result->get_array2('res');
        $group_name = $result->get_array2('group_name');
        for ($i=0; $i<$result->get('num'); $i++) {    // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:                                 // �����ֹ�
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt11'>". $res[$r][$i] ."</div></td>\n";
                break;
                case 1:                                 // ����
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt11b'>". $group_name[$r] ."</div></td>\n";
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
    $file_name = "list/newjis_itemMaster_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // file������rw�⡼�ɤˤ���
}
