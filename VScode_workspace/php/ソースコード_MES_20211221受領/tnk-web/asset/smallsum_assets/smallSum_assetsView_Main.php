<?php
//////////////////////////////////////////////////////////////////////////////
// ���ۻ񻺴�����Ģ�ξȲ� �ᥤ�� smallSum_assetsView_Main.php               //
// Copyright (C) 2010 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/10/05 Created  smallSum_assetsView_Main.php                         //
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
    ////////// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                          // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    ////////////// ����������
    $menu->set_site(80, 82);                // site_index=4(�ץ���೫ȯ) site_id=999(�ҥ�˥塼����)
    ////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('���ۻ񻺴�����Ģ�ξȲ�');
    
    $request = new Request;
    $result  = new Result;
    
    ////////////// �꥿���󥢥ɥ쥹����
    $menu->set_RetUrl(ASSET_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    //get_group_master($result, $request);                // ���롼�ץޥ������ǡ����μ���
    
    outViewListHTML($request, $menu, $result);          // HTML����
    
    display($menu, $request, $result);                  // ����ɽ��
    
    request_check($request, $result, $menu);            // ������ʬ�������å�.
    
    outViewListHTML($request, $menu, $result);          // HTML����
    
    display($menu, $request, $result);                  // ����ɽ��
    //get_capital_master ($result, $request);             // ����񻺥ޥ������μ���
    
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
    require_once ('smallSum_assetsView_View.php');

    ob_end_flush(); 
}

////////////// ������ʬ����Ԥ�
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('search') != '') get_capital_master ($result, $request);    // ����񻺥ޥ������μ���
    if ($ok) {
        ////// �ǡ����ν����
        //$request->add('del', '');
        //$request->add('entry', '');
        //$request->add('change', '');
        //$request->add('number', '');
        //$request->add('act_name', '');
        //$request->add('set_place', '');
        //$request->add('assets_name', '');
        //$request->add('assets_model', '');
        //$request->add('buy_ym', '');
        //$request->add('buy_price', '');
        //$request->add('delete_ym', '');
        //$request->add('note', '');
    }
}

////////////// ɽ����(����ɽ)�ξ��ۻ񻺴�����Ģ�ǡ�����SQL�Ǽ���
function get_capital_master ($result, $request)
{
    $act_name     = $request->get('act_name');
    $set_place    = $request->get('set_place');
    $assets_name  = $request->get('assets_name');
    $assets_model = $request->get('assets_model');
    $delete_in    = $request->get('delete_in');
    // ����WHERE��
    $search  = '';
    if ($act_name != '' && $set_place != '' && $assets_name != '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name != '' && $set_place != '' && $assets_name != '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%'";
        }
    } elseif ($act_name != '' && $set_place != '' && $assets_name == '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name != '' && $set_place == '' && $assets_name != '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name != '' && $set_place != '' && $assets_name == '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%'";
        }
    } elseif ($act_name != '' && $set_place == '' && $assets_name == '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name != '' && $set_place == '' && $assets_name != '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND assets_name LIKE '%{$assets_name}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND assets_name LIKE '%{$assets_name}%'";
        }
    } elseif ($act_name != '' && $set_place == '' && $assets_name == '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%'";
        }
    }
    if ($act_name == '' && $set_place != '' && $assets_name != '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name == '' && $set_place != '' && $assets_name == '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name == '' && $set_place != '' && $assets_name != '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%'";
        }
    } elseif ($act_name == '' && $set_place != '' && $assets_name == '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE set_place LIKE '%{$set_place}%'";
        }
    }
    if ($act_name == '' && $set_place == '' && $assets_name != '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name == '' && $set_place == '' && $assets_name != '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE assets_name LIKE '%{$assets_name}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE assets_name LIKE '%{$assets_name}%'";
        }
    }
    if ($act_name == '' && $set_place == '' && $assets_name == '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE assets_model LIKE '%{$assets_model}%'";
        }
    }
    if ($act_name == '' && $set_place == '' && $assets_name == '' && $assets_model == '' && $delete_in == 'IN') {
        $query = "
            SELECT  act_name        AS ��������         -- 0
                ,   set_place       AS ���־��         -- 1
                ,   assets_name     AS ����             -- 2
                ,   assets_model    AS �᡼����̾������ -- 3
                ,   buy_ym          AS ����ǯ����       -- 4
                ,   buy_price       AS ��������         -- 5
                ,   delete_ym       AS ����ǯ����       -- 6
                ,   note            AS ����             -- 7
            FROM
                smallsum_assets_master
            ORDER BY
                act_name ASC, assets_name ASC
        ";
    } elseif ($act_name == '' && $set_place == '' && $assets_name == '' && $assets_model == '' && $delete_in != 'IN') {
        $query = "
            SELECT  act_name        AS ��������         -- 0
                ,   set_place       AS ���־��         -- 1
                ,   assets_name     AS ����             -- 2
                ,   assets_model    AS �᡼����̾������ -- 3
                ,   buy_ym          AS ����ǯ����       -- 4
                ,   buy_price       AS ��������         -- 5
                ,   delete_ym       AS ����ǯ����       -- 6
                ,   note            AS ����             -- 7
            FROM
                smallsum_assets_master
            WHERE
                delete_ym = 0
            ORDER BY
                act_name ASC, assets_name ASC
        ";
    } else {
        $query = "
            SELECT  act_name        AS ��������         -- 0
                ,   set_place       AS ���־��         -- 1
                ,   assets_name     AS ����             -- 2
                ,   assets_model    AS �᡼����̾������ -- 3
                ,   buy_ym          AS ����ǯ����       -- 4
                ,   buy_price       AS ��������         -- 5
                ,   delete_ym       AS ����ǯ����       -- 6
                ,   note            AS ����             -- 7
            FROM
                smallsum_assets_master
            {$search}
            ORDER BY
                act_name ASC, assets_name ASC
        ";
    }
    

    $res_c = array();
    if (($rows_c = getResultWithField2($query, $field_c, $res_c)) <= 0) {
        //$_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
        $result->add_array2('res_c', '');
        $result->add_array2('field_c', '');
        $result->add('num_c', 0);
        $result->add('rows_c', 0);
    } else {
        $code_actname   = array();
        $code_placename = array();
        for ($i=0; $i<$rows_c; $i++) {    // �ե�����ɿ�ʬ���֤�\n";
            $code_actname[$i] = getActNameCode($res_c[$i][0]);
            $code_placename[$i] = getPlaceNameCode($res_c[$i][1]);
        }
        $num_c = count($field_c);
        $result->add_array2('res_c', $res_c);
        $result->add_array2('code_actname', $code_actname);
        $result->add_array2('code_placename', $code_placename);
        $result->add_array2('field_c', $field_c);
        $result->add('num_c', $num_c);
        $result->add('rows_c', $rows_c);
    }
    //$res_g = $result->get_array2('res_g');
    //for ($r=0; $r<$rows_c; $r++) {    // ���롼���ֹ�ȥ��롼��̾���֤�����(����񻺡�
    //    for ($i=0; $i<$result->get('rows_g'); $i++) {
    //        if($res_c[$r][0] == $res_g[$i][0]) {
    //            $group_name[$r] = $res_g[$i][1];
    //        }
    //    }
    //}
    //$result->add_array2('group_name', $group_name);
}

////////////// �����Ǥ�դ����դ�'/'�ե����ޥåȤ����֤���
function format_date8($date8)
{
    if (0 == $date8) {
        $date8 = '--------';    
    }
    if (8 == strlen($date8)) {
        $nen   = substr($date8, 0, 4);
        $tsuki = substr($date8, 4, 2);
        $hi    = substr($date8, 6, 2);
        return $nen . "/" . $tsuki . "/" . $hi;
    } else {
        return FALSE;
    }
}

////////////// �����Ǥ�դ����դ�'ǯ��'�ե����ޥåȤ����֤���
function format_date_kan($date6)
{
    if (0 == $date8) {
        $date8 = '--------';    
    }
    if (8 == strlen($date6)) {
        $nen   = substr($date8, 0, 4);
        $tsuki = substr($date8, 4, 2);
        $hi    = substr($date8, 6, 2);
        return $nen . "ǯ" . $tsuki . "��" . $hi . "��";
    } else {
        return FALSE;
    }
}

////////////// ����񻺾Ȳ���̤�HTML�κ���
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
    $listTable .= "    <form name='entry_form' action='smallSum_assetsView_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $listTable .= "                    ���ۻ񻺴�����Ģ\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
    if ($result->get('num_c') > 0) {
        $field_c = $result->get_array2('field_c');
        for ($i=0; $i<$result->get('num_c'); $i++) {    // �ե�����ɿ�ʬ���֤�\n";
            $listTable .= "        <th class='winbox' nowrap>". $field_c[$i] ."</th>\n";
        }
    } else {
        $listTable .= "            <th class='winbox' nowrap>��������</th>\n";
        $listTable .= "            <th class='winbox' nowrap>���־��</th>\n";
        $listTable .= "            <th class='winbox' nowrap>����</th>\n";
        $listTable .= "            <th class='winbox' nowrap>�᡼����������̾</th>\n";
        $listTable .= "            <th class='winbox' nowrap>����ǯ����</th>\n";
        $listTable .= "            <th class='winbox' nowrap>��������</th>\n";
        $listTable .= "            <th class='winbox' nowrap>����ǯ����</th>\n";
        $listTable .= "            <th class='winbox' nowrap>����</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' �������� -->\n";
    $listTable .= "            <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res_c = $result->get_array2('res_c');
    $group_name = $result->get_array2('group_name');
    $code_actname = $result->get_array2('code_actname');
    $code_placename = $result->get_array2('code_placename');
    for ($r=0; $r<$result->get('rows_c'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "            </td>    <!-- �ԥʥ�С���ɽ�� -->\n";
        for ($i=0; $i<$result->get('num_c'); $i++) {    // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:                                 // ��������
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $code_actname[$r] ."</div></td>\n";
                break;
                case 1:                                 // ���־��
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $code_placename[$r] ."</div></td>\n";
                break;
                case 2:                                 // ����
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_c[$r][$i] ."</div></td>\n";
                break;
                case 3:                                 // �᡼����̾������
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_c[$r][$i] ."</div></td>\n";
                break;
                case 4:                                 // ����ǯ����
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date8($res_c[$r][$i]) ."</div></td>\n";
                break;
                case 5:                                 // ��������
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res_c[$r][$i], 0) ."</div></td>\n";
                break;
                case 6:                                 // ���ѡ���ưǯ����
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date8($res_c[$r][$i]) ."</div></td>\n";
                break;
                case 7:                                 // ����
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_c[$r][$i] ."</div></td>\n";
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
///// ���������HTML <select> option �ν���
function getActOptionsBody($request)
{
    $query = "SELECT * FROM smallsum_assets_actname_master ORDER BY code_act ASC";
    $res = array();
    if (($rows=getResult2($query, $res)) <= 0) return '';
    $options = "\n";
    $options .= "<option value='' style='color:red;'>̤����</option>\n";
    for ($i=0; $i<$rows; $i++) {
        if ($request->get('act_name') == $res[$i][0]) {
            $options .= "<option value='{$res[$i][0]}' selected>{$res[$i][1]}</option>\n";
        } else {
            $options .= "<option value='{$res[$i][0]}'>{$res[$i][1]}</option>\n";
        }
    }
    return $options;
}
///// �������祳���ɡ�̾���Ѵ�
function getActNameCode($act_code)
{
    $query_chk = sprintf("SELECT name_act FROM smallsum_assets_actname_master WHERE code_act=%d", $act_code);
    $res_chk = array();
    $code_actname = '';
    //$code_actname = array();
    if ( getResult($query_chk, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
        $code_actname = $res_chk[0][0];
    } else {
        $code_actname = "---";
    }
    return $code_actname;
}

///// ���־���HTML <select> option �ν���
function getPlaceOptionsBody($request)
{
    $query = "SELECT * FROM smallsum_assets_placename_master ORDER BY code_place ASC";
    $res = array();
    if (($rows=getResult2($query, $res)) <= 0) return '';
    $options = "\n";
    $options .= "<option value='' style='color:red;'>̤����</option>\n";
    for ($i=0; $i<$rows; $i++) {
        if ($request->get('set_place') == $res[$i][0]) {
            $options .= "<option value='{$res[$i][0]}' selected>{$res[$i][1]}</option>\n";
        } else {
            $options .= "<option value='{$res[$i][0]}'>{$res[$i][1]}</option>\n";
        }
    }
    return $options;
}

///// ���־�ꥳ���ɡ�̾���Ѵ�
function getPlaceNameCode($place_code)
{
    $query_chk = sprintf("SELECT name_place FROM smallsum_assets_placename_master WHERE code_place=%d", $place_code);
    $res_chk = array();
    $code_placename = '';
    //$code_placename = array();
    if ( getResult($query_chk, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
        $code_placename = $res_chk[0][0];
    } else {
        $code_placename = "---";
    }
    return $code_placename;
}

////////////// ��Ψ�Ȳ���̤�HTML�����
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '�Ȳ�');
    ////////// HTML�ե��������
    $file_name = "list/smallSum_assetsView_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
}
