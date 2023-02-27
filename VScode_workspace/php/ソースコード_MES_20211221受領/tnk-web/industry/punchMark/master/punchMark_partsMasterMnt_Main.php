<?php
//////////////////////////////////////////////////////////////////////////////
// ������� �����ֹ�ޥ����� ���ƥʥ� �ᥤ����                          //
// Copyright (C) 2007-2008 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/07/30 Created   punchMark_partsMasterMnt_Main.php                   //
// 2007/09/25 header('Location: $menu->RetUrl()') ��                        //
//            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl())  ���� //
// 2007/09/26 site_index �� INDEX_INDUST ���ѹ�  ����                       //
// 2007/10/02 �ꥹ�Ȥ�����̾��ɽ������褦�ɲ�                              //
//            �����ֹ�ν�ʣ�����å��򳰤���Ʊ�������ֹ��ʣ���ι�������ɤ�//
//            ��Ͽ�Ǥ���褦���ѹ�                                          //
// 2007/10/20 getViewHTMLbody()<body>���ɲ� E_ALL �� E_ALL | E_STRICT��     //
//            request_check()�ǥ��顼�����å����ƥ��顼�ξ��ǡ�����Ĥ�   //
//            �ɲá��ѹ���������å������������ԤΥǡ������֤�            //
//            <a ��󥯤����ͤ�urlencode()�ؿ������ ������ǽ�ɲ�      ���� //
//            get_master()�ι���ޥ����������� LIMIT 1 ���ɲ�(�����å��Τ�) //
// 2007/10/21 getSearchCondition()where��note���� partsm.note ���ѹ� ���� //
// 2007/10/24 �ץ����κǸ�˲��Ԥ��ɲ�                                  //
// 2007/11/10 getPreDataRows()��setEditHistory()�Խ�������ɲá�        ����//
//            putErrorLogWrite()��Ȥ�����SQL���顼��debug��Ԥ�        ����//
//            Mark���ɲä��ԥޡ������ȥ����פ򤹤�                    ����//
// 2007/11/15 miitem��get_date()�Ǽ��������ʤ�Ʊ��ʪ��ɽ�����ʤ�        ����//
// 2008/09/03 �ߤ��Ф���ξ����������ɽ������褦���ѹ�                  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');     // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
require_once ('punchMark_MasterFunction.php');      // ������������ƥඦ�̥ޥ������ؿ�
access_log();                               // Script Name �ϼ�ư����

main();

function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    
    ////////////// ����������
    $menu->set_site(INDEX_INDUST, 999);         // site_index=������˥塼 site_id=999(�����ȥ�˥塼�򳫤�)
    
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('������� ���ʥޥ����� ���ƥʥ�');
      
    $request = new Request;
    $result  = new Result;
    
    get_master($request, $result, $menu);       // ����ޥ�������Ͽ�����å��ѥǡ�������
    
    request_check($request, $result);           // �����Υꥯ�����ȥ����å�
    
    get_data($request, $result);                // ���ʥޥ������ǡ�������
    
    outViewListHTML($request, $menu, $result);  // View��HTML�����
    
    display($menu, $request, $result);          // ����ɽ��
}

// ����ɽ��
function display($menu, $request, $result)
{       
    //////////// �֥饦�����Υ���å����к���
    $uniq = 'id=' . $menu->set_useNotCache('target');

    ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
    
    /////////// HTML Header ����Ϥ��ƥ���å��������
    $menu->out_html_header();
 
    /////////// View�ν���
    require_once ('punchMark_partsMasterMnt_View.php');

    ob_end_flush(); 
}

function request_check($request, $result)    // ������ʬ����Ԥ�
{
    $ok = true;
    if ($request->get('entry') != '')  $ok = punchMark_parts_entry($request, $result);
    if ($request->get('change') != '') $ok = punchMark_parts_change($request, $result);
    if ($request->get('del') != '')    $ok = punchMark_parts_del($request);
    if ($ok) {
        // �ޡ������Ѥ˥��å�
        $result->add('parts_no', $request->get('parts_no'));
        $result->add('punchMark_code', $request->get('punchMark_code'));
    }
    if ($request->get('search') != '') {
        $result->add('where', getSearchCondition($request));
    } elseif ($ok) {
        $request->add('parts_no', '');
        $request->add('punchMark_code', '');
        $request->add('note', '');
    }
    if ($request->get('number') != '') pre_copy($request, $result);
}

////////////// �ɲå��å�
function punchMark_parts_entry($request, $result)
{
    $parts_no = $request->get('parts_no');
    $punchMark_code = $request->get('punchMark_code');
    $note      = $request->get('note');
    $query = "SELECT midsc FROM miitem WHERE mipn='{$parts_no}'";
    if (getUniResult($query, $name) <= 0) {    //�����ֹ����Ͽ�����å�
        $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no}�ϥޥ���������Ͽ����Ƥ��ޤ��󡪡�";
        return false;
    }
    $query = sprintf("SELECT punchMark_code FROM punchMark_master WHERE punchMark_code='%s'", $punchMark_code);
    $res_chk = array();
    if (getResult($query, $res_chk) <= 0 ) {   //////// �����������Ͽ�ʤ� ���顼
        $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}����Ͽ����Ƥ��ޤ��󡪡�";
        return false;
    }
    $query = sprintf("SELECT parts_no FROM punchMark_parts_master WHERE parts_no='%s' and punchMark_code='%s'", $parts_no, $punchMark_code);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� ���顼
        $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no} ��������ɡ�{$punchMark_code}�Ϥ��Ǥ���Ͽ����Ƥ��ޤ���";
        return false;
    } else {                                    //////// ��Ͽ�ʤ� INSERT ����   
        if (!punchMark_entryBody($request)) {
            return false;
        }
    }
    return true;
}

////////////// ��Ͽ ���� ���å�
function punchMark_entryBody($request)
{
    $query  = "INSERT INTO punchMark_parts_master ";
    $query .= "(parts_no, punchMark_code, note, last_user) ";
    $query .= "VALUES ('{$request->get('parts_no')}', '{$request->get('punchMark_code')}', '{$request->get('note')}', '{$_SESSION['User_ID']}')";
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "�����ֹ桧{$request->get('parts_no')} ��������ɡ�{$request->get('punchMark_code')}���ɲä˼��ԡ�";
        putErrorLogWrite($query);
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "�����ֹ桧{$request->get('parts_no')} ��������ɡ�{$request->get('punchMark_code')}���ɲä��ޤ�����";
        // �Խ�������¸
        setEditHistory('punchMark_parts_master', 'I', $query);
    }
    return true;
}

///////////////// �ѹ����å�
function punchMark_parts_change($request, $result)
{
    $parts_no = $request->get('parts_no');
    $punchMark_code = $request->get('punchMark_code');
    $note      = $request->get('note');
    $query = "SELECT midsc FROM miitem WHERE mipn='{$parts_no}'";
    if (getUniResult($query, $name) <= 0) {    //�����ֹ����Ͽ�����å�
        $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no}�ϥޥ���������Ͽ����Ƥ��ޤ��󡪡�";
        return false;
    }
    $query = sprintf("SELECT punchMark_code FROM punchMark_master WHERE punchMark_code='%s'", $punchMark_code);
    $res_chk = array();
    if (getResult($query, $res_chk) <= 0 ) {   //////// �����������Ͽ�ʤ� ���顼
        $_SESSION['s_sysmsg'] .= "��������ɡ�{$punchMark_code}����Ͽ����Ƥ��ޤ��󡪡�";
        return false;
    }
    $query = sprintf("SELECT * FROM punchMark_parts_master WHERE parts_no='%s' and punchMark_code='%s' ", $parts_no, $punchMark_code);
    $old_data = getPreDataRows($query);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE ����
        $query = sprintf("UPDATE punchMark_parts_master SET parts_no='%s', punchMark_code='%s', note='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE parts_no='%s' and punchMark_code='%s'", $parts_no, $punchMark_code, $note, $_SESSION['User_ID'], $parts_no, $punchMark_code);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no} ��������ɡ�{$punchMark_code}���ѹ����ԡ�";
            putErrorLogWrite($query);
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no}��������ɡ�{$punchMark_code}���ѹ����ޤ�����";
            // �Խ�������¸
            setEditHistory('punchMark_parts_master', 'U', $query, $old_data);
            return true;
        }
    } else {                                    //////// ��Ͽ�ʤ� INSERT ����   
        if (!punchMark_entryBody($request)) {
            return false;
        }
    }
    return false;
}

//////////// ����ܥ��󤬲����줿��
function punchMark_parts_del($request)
{
    $parts_no = $request->get('parts_no');
    $punchMark_code = $request->get('punchMark_code');
    $query = "SELECT * FROM punchMark_parts_master WHERE parts_no = '{$parts_no}' and punchMark_code = '{$punchMark_code}'";
    if ( ($old_data=getPreDataRows($query)) === false ) {
        $_SESSION['s_sysmsg'] .= '�оݥǡ����μ����˼��ԡ� ����ô���Ԥ�Ϣ���Ʋ�������';
        return false;
    }
    $query = "DELETE FROM punchMark_parts_master WHERE parts_no = '{$parts_no}' and punchMark_code = '{$punchMark_code}'";
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no} ��������ɡ�{$punchMark_code}�κ���˼��ԡ�";
        putErrorLogWrite($query);
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no} ��������ɡ�{$punchMark_code}�������ޤ�����";
        // �Խ�������¸
        setEditHistory('punchMark_parts_master', 'D', $query, $old_data);
        return true;
    }
    return false;
}

////////////// ���ԡ��Υ�󥯤������줿��  &&���ɲ� Undefined index�б�
function pre_copy($request, $result)
{
    $res = array();
    $parts_no = $request->get('number');
    $punchMark_code = $request->get('punch');;
    $note      = $request->get('notes');;
    $request->add('parts_no', $parts_no);
    $request->add('punchMark_code', $punchMark_code);
    $request->add('note', $note);
}

////////////// ɽ����(����ɽ)�Υޥ������ǡ�����SQL�Ǽ���
function get_data($request, $result)
{
    $query = "
        SELECT  partsm.parts_no             AS �����ֹ�     -- 0
            ,   partsm.punchMark_code       AS ���������   -- 1
            ,   partsm.note                 AS ����         -- 2
            ,   shelf_no                    AS ê��         -- 3
            ,   mark                        AS �������     -- 4
            ,   shape_name                  AS ����̾       -- 5
            ,   size_name                   AS ������̾     -- 6
            ,   (SELECT substr(midsc, 1, 10) FROM miitem WHERE mipn=CAST(parts_no AS CHAR(9)) LIMIT 1)
                                            AS ����̾       -- 7
            ,
            CASE
                WHEN lend_flg IS TRUE THEN '�߽���'
                ELSE ''
            END                         AS �߽о���     -- 8
        FROM
            punchMark_parts_master AS partsm
        -- LEFT OUTER JOIN
        --     miitem ON (parts_no = mipn)
        LEFT OUTER JOIN
            punchMark_master USING (punchmark_code)
        LEFT OUTER JOIN
            punchMark_shape_master USING (shape_code)
        LEFT OUTER JOIN
            punchMark_size_master USING (size_code)
        {$result->get('where')}
        ORDER BY
            parts_no, shelf_no, punchmark_code
    ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $field[0]   = "�����ֹ�";
        $field[1]   = "���������";
        $field[2]   = "����";
        $num = 3;
        $result->add_array($res);
        $result->add_array2('field', $field);
        $request->add('num', $num);
        $request->add('rows', $rows);
    } else {
        $num = count($field);
        $result->add_array($res);
        $result->add_array2('field', $field);
        $request->add('num', $num);
        $request->add('rows', $rows);
    }
}

////////////// ����ޥ������Υǡ�����SQL�Ǽ���
function get_master($request, $result, $menu)
{
    $query = "
        SELECT  punchm.punchMark_code           AS ���������     -- 0
            ,   punchm.make_flg                 AS ������ե饰   -- 1
        FROM
            punchMark_master AS punchm
        ORDER BY
            punchMark_code
        LIMIT 1
    ";

    $res = array();
    $res_punchMark_code = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] .= "����ޥ��������������Ͽ����Ƥ��ޤ��� ��Ͽ���ǧ���Ƥ���������";    // .= �����
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    } else {
        // $result->add_array2('res_punch', $res);
        // $request->add('rows_punch', $rows);
    }
}

// View��HTML�κ���
function getViewHTMLbody($request, $menu, $result)
{
    $res        = $result->get_array();
    $field      = $result->get_array2('field');
    $num        = $request->get('num');
    $rows       = $request->get('rows');
    $parts_name = '';
    // �����
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>\n";
    $listTable .= "<link rel='stylesheet' href='../punchMark_MasterMnt.css' type='text/css' media='screen'>\n";
    $listTable .= "<script type='text/javascript' src='../punchMark_partsMasterMnt.js'></script>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body style='background-image:none;'>\n";
    $listTable .= "<center>\n";
    $listTable .= "    <table class='outside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>\n";
    $listTable .= "        <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "    <table class='inside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $res[-1][0] = '';   // ���ߡ������
    for ($r=0; $r<$rows; $r++) {
        if ($result->get('parts_no') == $res[$r][0] && $result->get('punchMark_code') == $res[$r][1]) {
            $listTable .= "<tr style='background-color:#ffffc6;'>\n";
            $Mark = "name='Mark' ";
        } elseif ($request->get('parts_no') == $res[$r][0] && $request->get('punchMark_code') == $res[$r][1]) {
            $listTable .= "<tr style='background-color:#ffffc6;'>\n";
            $Mark = "name='Mark' ";
        } else {
            $listTable .= "<tr>\n";
            $Mark = '';
        }
        $listTable .= "    <td class='winbox' width=' 6%' align='right'>    <!-- ����ѹ��Ѥ�������˥��ԡ�  -->\n";
        if ($res[$r][2] == '') {
            $listTable .= "        <a {$Mark}href='../punchMark_partsMasterMnt_Main.php?copy_flg=1&number=". urlencode($res[$r][0]) . "&punch=". urlencode($res[$r][1]) . "' target='_parent' style='text-decoration:none;'>\n";
        } else {
            $listTable .= "        <a {$Mark}href='../punchMark_partsMasterMnt_Main.php?copy_flg=1&number=". urlencode($res[$r][0]) . "&punch=". urlencode($res[$r][1]) . "&notes=" . urlencode($res[$r][2]) . "' target='_parent' style='text-decoration:none;'>\n";
        }
        $del_no = $r + 1;
        $listTable .= "        {$del_no}\n";
        $listTable .= "        </a>\n";
        $listTable .= "    </td>\n";
        if ($res[$r-1][0] == $res[$r][0]) {
            // �����ֹ�
            $listTable .= "<td class='winbox pt12b' width='11%' align='center'>&nbsp;</td>\n";
            // ����̾
            $listTable .= "<td class='winbox pt12b' width='18%' align='left' >&nbsp;</td>\n";
        } else {
            // �����ֹ�
            $listTable .= "<td class='winbox pt12b' width='11%' align='center'>{$res[$r][0]}</td>\n";
            // ����̾
            $listTable .= "<td class='winbox pt12b' width='18%' align='left' >{$res[$r][7]}</td>\n";
        }
        // ê��
        $listTable .= "<td class='winbox pt12b' width=' 8%' align='center'>{$res[$r][3]}</td>\n";
        // ���������
        $listTable .= "<td class='winbox pt12b' width='10%' align='center'>{$res[$r][1]}</td>\n";
        // �������
        $tmpView = str_replace("\r", '<br>', $res[$r][4]);
        $listTable .= "<td class='winbox pt12b' width='14%' align='center'>{$tmpView}</td>\n";
        // ����
        $listTable .= "<td class='winbox pt12b' width=' 6%' align='center'>{$res[$r][5]}</td>\n";
        // ������
        $listTable .= "<td class='winbox pt12b' width=' 6%' align='center'>{$res[$r][6]}</td>\n";
        // ����
        if ($res[$r][8] == '�߽���') {
            $addMsg = "<span style='color:red;'>{$res[$r][8]}</span>";
        } else {
            $addMsg = '';
        }
        if ($res[$r][2] == '') {
            $listTable .= "<td class='winbox pt12b' width='21%' align='left'>{$addMsg}&nbsp;</td>\n";
        } else {
            $listTable .= "<td class='winbox pt12b' width='21%' align='left'>{$addMsg}{$res[$r][2]}</td>\n";
        }
        $listTable .= "</tr>\n";
    }
    $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
    $listTable .= "</td></tr>\n";
    $listTable .= "</table>\n";
    $listTable .= "</center>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

// View��HTML�ν���
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    // HTML�ե��������
    $file_name = "list/punchMark_partsMasterMnt_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
}

////////// �������μ���
function getSearchCondition($request)
{
    if ($request->get('search') == '') return '';
    $where = '';
    if ($request->get('parts_no') != '') {
        $where .= "WHERE parts_no LIKE '%{$request->get('parts_no')}%'";
    }
    if ($request->get('punchMark_code') != '' && $where != '') {
        $where .= " AND punchMark_code LIKE '%{$request->get('punchMark_code')}%'";
    } elseif ($request->get('punchMark_code') != '') {
        $where .= "WHERE punchMark_code LIKE '%{$request->get('punchMark_code')}%'";
    }
    if ($request->get('note') != '' && $where != '') {
        $where .= " AND partsm.note LIKE '%{$request->get('note')}%'";
    } elseif ($request->get('note') != '') {
        $where .= "WHERE partsm.note LIKE '%{$request->get('note')}%'";
    }
    return $where;
}

?>
