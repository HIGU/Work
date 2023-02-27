<?php
//////////////////////////////////////////////////////////////////////////////
// ������� �����ޥ����� ���ƥʥ� �ᥤ����                              //
// Copyright (C) 2007 Norihisa.Ooya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2007/07/24 Created   punchMark_shapeMasterMnt_Main.php                   //
// 2007/09/26 site_index �� INDEX_INDUST ���ѹ�  ����                       //
// 2007/10/20 getViewHTMLbody()<body>���ɲ� E_ALL �� E_ALL | E_STRICT�� ����//
//            �ꥹ�ȤΥإå�����<iframe>���ɲ� ����                         //
// 2007/10/24 �ץ����κǸ�˲��Ԥ��ɲ�                                  //
// 2007/11/09 getPreDataRows()��setEditHistory()�Խ�������ɲá�$menu��ȴ�� //
//              request_check($request, $result, $menu)�ʲ����ս�       ����//
// 2007/11/10 putErrorLogWrite()��Ȥ�����SQL���顼��debug��Ԥ�        ����//
//            ;ʬ��<font color='yellow'></font>����                  ����//
// 2008/04/18 �������ˤ������μ����'R'���ɲ�                           //
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
    $menu->set_title('������� �����ޥ����� ���ƥʥ�');
      
    $request = new Request;
    $result  = new Result;
    
    get_master($request, $result);              // ��������å��ѥޥ������ǡ����μ���
    
    branch_check($request, $result, $menu);     // ������ʬ�������å�
    
    get_data($request, $result);    // �����ޥ������ǡ�������
    
    outViewListHTML($request, $menu, $result);  // View��HTML�ե�����ν���
    
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
    require_once ('punchMark_shapeMasterMnt_View.php');

    ob_end_flush(); 
}

function branch_check($request, $result, $menu)    //������ʬ����Ԥ�
{
    if ($request->get('entry') != '') punchMark_shape_entry($request, $menu);
    if ($request->get('del') != '') punchMark_shape_del($request, $result, $menu);
    $request->add('shape_code', '');
    $request->add('shape_name', '');
    $request->add('note', '');
    if ($request->get('number') != '') pre_copy($request, $result);
}

////////////// ��Ͽ���ѹ����å�
function punchMark_shape_entry($request, $menu)
{
    $shape_code = $request->get('shape_code');
    $shape_name = $request->get('shape_name');
    $note      = $request->get('note');
    $query = sprintf("SELECT * FROM punchMark_shape_master WHERE shape_code=%d", $shape_code);
    $old_data=getPreDataRows($query);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {   //////// ��Ͽ���� UPDATE ����
        $query = sprintf("SELECT shape_code FROM punchMark_shape_master WHERE shape_code=%d AND shape_name='%s'", $shape_code, $shape_name);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {   //////// �����ɡ�����̾�ѹ��ʤ� UPDATE ���ͤ��ѹ�
            $query = sprintf("UPDATE punchMark_shape_master SET shape_code=%d, shape_name='%s', note='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE shape_code=%d", $shape_code, $shape_name, $note, $_SESSION['User_ID'], $shape_code);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "���������ɡ�{$shape_code} ����̾��{$shape_name}���ѹ����ԡ�";    // .= �����
                putErrorLogWrite($query);
                header('Location: ' . H_WEB_HOST . $menu->out_self());
                exit();
            } else {
                $_SESSION['s_sysmsg'] .= "���������ɡ�{$shape_code}����̾��{$shape_name}���ѹ����ޤ�����";
                // �Խ�������¸
                setEditHistory('punchMark_shape_master', 'U', $query, $old_data);
            }
        } else {    //����̾�ѹ����� ����̾�����Ǥ���Ͽ����Ƥ��뤫�����å�
            $query = sprintf("SELECT shape_code FROM punchMark_shape_master WHERE shape_name='%s'", $shape_name);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {   //////// ̾����Ͽ���� ���顼
                $_SESSION['s_sysmsg'] .= "����̾��{$shape_name}�Ϥ��Ǥ���Ͽ����Ƥ��ޤ�����";    // .= �����
                header('Location: ' . H_WEB_HOST . $menu->out_self());
                exit();
            } else {
                $query = sprintf("UPDATE punchMark_shape_master SET shape_code=%d, shape_name='%s', note='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE shape_code=%d", $shape_code, $shape_name, $note, $_SESSION['User_ID'], $shape_code);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "���������ɡ�{$shape_code} ����̾��{$shape_name}���ѹ����ԡ�";    // .= �����
                    putErrorLogWrite($query);
                    header('Location: ' . H_WEB_HOST . $menu->out_self());
                    exit();
                } else {
                    $_SESSION['s_sysmsg'] .= "���������ɡ�{$shape_code}����̾��{$shape_name}���ѹ����ޤ�����";
                    // �Խ�������¸
                    setEditHistory('punchMark_shape_master', 'U', $query, $old_data);
                }
            }
        }
    } else {                                    //////// ��Ͽ�ʤ� INSERT ����
        $query = sprintf("SELECT shape_code FROM punchMark_shape_master WHERE shape_name='%s'", $shape_name);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {   //////// ̾����Ͽ���� ���顼
            $_SESSION['s_sysmsg'] .= "����̾��{$shape_name}�Ϥ��Ǥ���Ͽ����Ƥ��ޤ�����";    // .= �����
            header('Location: ' . H_WEB_HOST . $menu->out_self());
            exit();
        } else {
            $query = sprintf("INSERT INTO punchMark_shape_master (shape_code, shape_name, note, last_date, last_user)
                              VALUES (%d, '%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                $shape_code, $shape_name, $note, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "���������ɡ�{$shape_code} ����̾��{$shape_name}���ɲä˼��ԡ�";    // .= �����
                putErrorLogWrite($query);
            } else {
                $_SESSION['s_sysmsg'] .= "���������ɡ�{$shape_code} ����̾��{$shape_name}���ɲä��ޤ�����";
                // �Խ�������¸
                setEditHistory('punchMark_shape_master', 'I', $query);
            }
        }
    }
}

//////////// ����ܥ��󤬲����줿��
function punchMark_shape_del($request, $result, $menu)
{
    $shape_code = $request->get('shape_code');
    $shape_name = $request->get('shape_name');
    $res_punch = $result->get_array2('res_punch');
    $rows_punch  = $request->get('rows_punch');
    for ($r=0; $r<$rows_punch; $r++) {
        if ( $res_punch[$r][3] == $shape_code) {    // ����ޥ���������Ͽ����Ƥ��뤫�����å�
            $_SESSION['s_sysmsg'] .= "���������ɡ�{$shape_code}�Ϲ���ޥ������ǻ��Ѥ���Ƥ��ޤ�����";
            header('Location: ' . H_WEB_HOST . $menu->out_self());
            exit();
        }
    }
    $query = "SELECT * FROM punchMark_shape_master WHERE shape_code = {$shape_code}";
    if ( ($old_data=getPreDataRows($query)) === false ) {
        $_SESSION['s_sysmsg'] .= '�оݥǡ����μ����˼��ԡ� ����ô���Ԥ�Ϣ���Ʋ�������';
        header('Location: ' . H_WEB_HOST . $menu->out_self());
        exit();
    }
    $query = sprintf("DELETE FROM punchMark_shape_master WHERE shape_code = %d", $shape_code);
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "���������ɡ�{$shape_code} ����̾��{$shape_name}�κ���˼��ԡ�";    // .= �����
        putErrorLogWrite($query);
    } else {
        $_SESSION['s_sysmsg'] .= "���������ɡ�{$shape_code} ����̾��{$shape_name}�������ޤ�����";
        // �Խ�������¸
        setEditHistory('punchMark_shape_master', 'D', $query, $old_data);
    }
}

////////////// ���ԡ��Υ�󥯤������줿��  &&���ɲ� Undefined index�б�
function pre_copy($request, $result)
{
    $res = array();
    $shape_code = $request->get('number');
    $request->add('shape_code', $shape_code);
    $query = "SELECT * FROM punchMark_shape_master WHERE shape_code=$shape_code";
    if (getResult($query, $res) <= 0) putErrorLogWrite($query);
    $shape_name = $res[0]['shape_name'];
    $note      = $res[0]['note'];
    $request->add('shape_name', $shape_name);
    $request->add('note', $note);
}

////////////// ɽ����(����ɽ)�Υޥ������ǡ�����SQL�Ǽ���
function get_data($request, $result)
{
    $query = "
        SELECT  shapem.shape_code               AS ����������     -- 0
            ,   shapem.shape_name               AS ����̾         -- 1
            ,   shapem.note                     AS ����           -- 2
        FROM
            punchMark_shape_master AS shapem
        ORDER BY
            shape_code
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $field[0]   = "����������";
        $field[1]   = "����̾";
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

////////////// ��������å��ѤΥޥ������ǡ�����SQL�Ǽ���
function get_master($request, $result)
{
    /////////�������
    if ($_SESSION['Auth'] >= 3) {
        $shape_name_master = array('��', '�ͳ�', '��', '���顼', 'R', '�ƥ���'); //��Ͽ�ѷ���̾
        $request->add('rows_name', 6); //����̾�θĿ� ������̾���ɲä���Ȥ��ϰʾ売�Ĥ��ѹ����롣
    } else {
        $shape_name_master = array('��', '�ͳ�', '��', '���顼', 'R'); //��Ͽ�ѷ���̾
        $request->add('rows_name', 5); //����̾�θĿ� ������̾���ɲä���Ȥ��ϰʾ売�Ĥ��ѹ����롣
    }
    $result->add_array2('shape_name_master', $shape_name_master);
    
    $query = "
        SELECT  punchm.punchMark_code           AS ���������     -- 0
            ,   punchm.shelf_no                 AS ê��           -- 1
            ,   punchm.mark                     AS �������       -- 2
            ,   punchm.shape_code               AS ����������     -- 3
            ,   punchm.size_code                AS ������������   -- 4
            ,   punchm.user_code                AS ���襳����     -- 5
            ,   punchm.note                     AS ����           -- 6
            ,   punchm.make_flg                 AS ������ե饰   -- 7
        FROM
            punchMark_master as punchm
        ORDER BY
            punchMark_code
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $result->add_array2('res_punch', '');
        $request->add('rows_punch', '');
    } else {
        $result->add_array2('res_punch', $res);
        $request->add('rows_punch', $rows);
    }
}

// View��HTML�κ���
function getViewHTMLbody($request, $menu, $result)
{
    $res   = $result->get_array();
    $field = $result->get_array2('field');
    $num   = $request->get('num');
    $rows  = $request->get('rows');
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
    $listTable .= "<script type='text/javascript' src='../punchMark_shapeMasterMnt.js'></script>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body style='background-image:none; background-color:#d6d3ce;'>\n";
    $listTable .= "<center>\n";
    // $listTable .= "    <form name='entry_form' action='../punchMark_shapeMasterMnt_Main.php' method='post'>\n";
    $listTable .= "        <table class='outside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='inside_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    /*****
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td class='winbox_title' align='center' colspan='4'>\n";
    $listTable .= "                �����ޥ�����\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox pt11b' nowrap >No</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
        for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
            $listTable .= "        <th class='winbox pt11b' nowrap>{$field[$i]}</th>\n";
        }
    $listTable .= "            </tr>\n";
    *****/
    for ($r=0; $r<$rows; $r++) {
        $listTable .= "<tr>\n";
        $listTable .= "    <td class='winbox' width='10%' align='right'>    <!-- ����ѹ��Ѥ�������˥��ԡ�  -->\n";
        $listTable .= "        <a href='../punchMark_shapeMasterMnt_Main.php?copy_flg=1&number={$res[$r][0]}' target='_parent' style='text-decoration:none;'>\n";
        $del_no = $r + 1;
        $listTable .= "        {$del_no}\n";
        $listTable .= "        </a>\n";
        $listTable .= "    </td>\n";
        for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
            // <!--  bgcolor='#ffffc6' �������� --> 
            switch ($i) {
            case 0:     // ������������
                $listTable .= "<td class='winbox pt12b' width='20%' align='center'>{$res[$r][$i]}</td>\n";
                break;
            case 1:     // ������̾
                $listTable .= "<td class='winbox pt12b' width='20%' align='left'>{$res[$r][$i]}</td>\n";
                break;
            case 2:     // ����
                if ($res[$r][$i] == '') {
                    $listTable .= "<td class='winbox pt12b' width='50%' align='left'>&nbsp;</td>\n";
                } else {
                    $listTable .= "<td class='winbox pt12b' width='50%' align='left'>{$res[$r][$i]}</td>\n";
                }
                break;
            default:
                break;
            }
        }
        $listTable .= "</tr>\n";
    }
    $listTable .= "</table>\n";
    $listTable .= "</td></tr>\n";
    $listTable .= "</table> <!----------------- ���ߡ�End ------------------>\n";
    // $listTable .= "</form>\n";
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
    $file_name = "list/punchMark_shapeMasterMnt_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // file������rw�⡼�ɤˤ���
}
?>
