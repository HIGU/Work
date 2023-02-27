<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ƚ���ν��� �������Ƥ�����                                   Main ��  //
// Copyright (C) 2008 - 2017 Norihisa.Ohya usoumu@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2008/11/21 Created   working_hours_report_CorrectMain.php                //
// 2017/06/02 ����Ĺ���� �ܳʲ�ư                                           //
// 2017/06/29 ���顼�ս���������                                            //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);

require_once ('../../MenuHeader.php');               // TNK ������ menu class
require_once ('../../tnk_func.php');                 // day_off(), date_offset() �ǻ���
require_once ('../../ControllerHTTP_Class.php');     // TNK ������ MVC Controller Class
require_once ('../../function.php');                 // access_log()���ǻ���

Correctmain();

function Correctmain()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('�������Ƥ�����');
    //////////// ����ؤ�GET�ǡ�������
    $menu->set_retGET('page_keep', 'On');    
    
    $request = new Request;
    $result  = new Result;
    
    getCorrectData($result, $request);                          // �Ƽ�ǡ����μ���
    
    requestCheck($request, $result, $menu);           // ������ʬ�������å�
    
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
    require_once ('working_hours_report_CorrectView.php');

    ob_end_flush(); 
}

////////////// ������ʬ����Ԥ�
function requestCheck($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = correctCopy($request, $result);
    if ($request->get('del') != '') $ok = correctDel($request);
    if ($request->get('entry') != '')  $ok = correctEntry($request, $result);
    if ($ok) {
        ////// �ǡ����ν����
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('group_no', '');
        $request->add('group_name', '');
        getCorrectData($result, $request);    // �Ƽ�ǡ����μ���
    }
}

////////////// �ɲá��ѹ����å� (��ץ쥳���ɿ��������˹Ԥ�)
function correctEntry($request, $result)
{
    $uid = $request->get('uid');
    $uid = sprintf('%06d', $uid);
    $working_date = $request->get('working_date');
    $correct_contents = $request->get('correct_contents');
    $query = sprintf("SELECT * FROM working_hours_report_correct WHERE uid='%s' AND working_date=%d", $uid, $working_date);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
        $query = sprintf("UPDATE working_hours_report_correct SET correct_contents='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE uid='%s' AND working_date='%s'", $correct_contents, $_SESSION['User_ID'], $uid, $working_date);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "�Ұ��ֹ桧{$uid}  ����ǯ������{$working_date}�����������ѹ����ԡ�";      // .= �����
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "�Ұ��ֹ桧{$uid}  ����ǯ������{$working_date}���������Ƥ��ѹ����ޤ�����"; // .= �����
            return true;
        }
    } else {                                    // ��Ͽ�ʤ� INSERT ����   
        $query = sprintf("INSERT INTO working_hours_report_correct (uid, working_date, correct_contents, correct, last_date, last_user)
                          VALUES ('%s', '%s', '%s', FALSE, CURRENT_TIMESTAMP, '%s')",
                            $uid, $working_date, $correct_contents, $_SESSION['User_ID']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "�Ұ��ֹ桧{$uid}  ����ǯ������{$working_date}���������ɲä˼��ԡ�";      // .= �����
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "�Ұ��ֹ桧{$uid}  ����ǯ������{$working_date}���������ɲä��ޤ�����";    // .= �����
            return true;
        }
    }
}

////////////// ������å� (��ץ쥳���ɿ��������˹Ԥ�)
function correctDel($request)
{
    
    $uid = $request->get('uid');
    $working_date = $request->get('working_date');
    $query = sprintf("DELETE FROM working_hours_report_correct WHERE uid='%s' AND working_date='%s'", $uid, $working_date);
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "�Ұ��ֹ桧{$uid} ����ǯ������{$working_date}���������Ƥκ���˼��ԡ�";   // .= �����
        $msg_flg = 'alert';
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "�Ұ��ֹ桧{$uid} ����ǯ������{$working_date}���������Ƥ������ޤ�����"; // .= �����
        return true;
    }
}
////////////// ɽ����(����ɽ)�ν��Ƚ��������ǡ�����SQL�Ǽ���
function getCorrectData ($result, $request)
{
    $query_g = "
        SELECT  uid                AS �Ұ��ֹ�     -- 0
            ,   working_date       AS ����ǯ����   -- 1
            ,   correct_contents   AS ��������     -- 2
        FROM
            working_hours_report_correct
        WHERE last_user = {$_SESSION['User_ID']} AND correct = 'f'
        ORDER BY
            uid
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $field_g[0]   = "�Ұ��ֹ�";
        $field_g[1]   = "�Ұ�̾";
        $field_g[2]   = "����ǯ����";
        $field_g[3]   = "��������";
        $num_g = count($field_g);
        $num_g = $num_g + 1;
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', $num_g);
        $result->add('rows_g', '');
        $result->add('get_flg', 't');
    } else {
        $num_g = count($field_g);
        for ($i=0; $i<$rows_g; $i++) {
            $user_name[$i] = getViewUserName($res_g[$i][0]);
        }
        $result->add_array2('user_name', $user_name);
        $result->add_array2('res_g', $res_g);
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', $num_g);
        $result->add('rows_g', $rows_g);
        $result->add('get_flg', '');
    }
}

function getViewUserName($uid)
{
    $query_n = "
        SELECT trim(name) AS ��̾
        FROM
            user_detailes
        WHERE
            uid = '{$uid}'
        
    ";
    $res_n = array();
    if ( ($rows_n=getResult2($query_n, $res_n)) < 1 ) {
        $user_name = '̤��Ͽ';
    } else {
        $user_name = $res_n[0][0];
    }
    
    return $user_name;
    
}

////////////// ���ԡ��Υ�󥯤������줿��
function correctCopy($request, $result)
{
    $res_g = $result->get_array2('res_g');
    $r = $request->get('number');
    $uid   = $res_g[$r][0];
    $working_date = $res_g[$r][1];
    $correct_contents = $res_g[$r][2];
    $request->add('uid', $uid);
    $request->add('working_date', $working_date);
    $request->add('correct_contents', $correct_contents);
}

////////////// ���Ƚ����������ϲ��̤�HTML�κ���
function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<script type='text/javascript' src='/base_class.js'></script>\n";
    $listTable .= "<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>\n";
    $listTable .= "<link rel='stylesheet' href='../working_hours_report.css' type='text/css' media='screen'>\n";
    $listTable .= "<style type='text/css'>\n";
    $listTable .= "<!--\n";
    $listTable .= "body {\n";
    $listTable .= "    background-image:none;\n";
    $listTable .= "}\n";
    $listTable .= "-->\n";
    $listTable .= "</style>\n";
    $listTable .= "<script type='text/javascript' src='../working_hours_report.js'></script>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body>\n";
    $listTable .= "<center>\n";
    $listTable .= "    <form name='entry_form' action='working_hours_report_CorrectMain.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
    $field_g = $result->get_array2('field_g');
    $listTable .= "                <th class='winbox' nowrap>�Ұ��ֹ�</th>\n";
    $listTable .= "                <th class='winbox' nowrap>�Ұ�̾</th>\n";
    $listTable .= "                <th class='winbox' nowrap>����ǯ����</th>\n";
    $listTable .= "                <th class='winbox' nowrap>��������</th>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- ���ߤϥեå����ϲ���ʤ� -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    if ($result->get('get_flg') == 't') {
        $listTable .= "    <tr>\n";
        $listTable .= "        <td class='winbox' colspan='5' nowrap align='center'><div class='pt9'>�������Ƥ���Ͽ������ޤ���</div></td>\n";
        $listTable .= "    </tr>\n";
    } else {
        for ($r=0; $r<$result->get('rows_g'); $r++) {
            $listTable .= "        <tr>\n";
            $listTable .= "            <td class='winbox' nowrap align='right'>    <!-- ����ѹ��Ѥ�������˥��ԡ�  -->\n";
            $listTable .= "                <a href='../working_hours_report_CorrectMain.php?number=". $r ."' target='_parent' style='text-decoration:none;'>\n";
            $cnum = $r + 1;
            $listTable .= "                ". $cnum ."\n";
            $listTable .= "                </a>\n";
            $listTable .= "            </td>\n";
            $res_g     = $result->get_array2('res_g');
            $user_name = $result->get_array2('user_name');
            for ($i=0; $i<$result->get('num_g'); $i++) {    // �쥳���ɿ�ʬ���֤�
                switch ($i) {
                    case 0:                                 // �Ұ��ֹ�
                        $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                        $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $user_name[$r] ."</div></td>\n";
                    break;
                    case 1:                                 // ����ǯ����
                        $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                    break;
                    case 2:                                 // ��������
                        $listTable .= "<td class='winbox' nowrap align='left' width='700'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                    break;
                    default:
                    break;
                }
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

////////////// ���Ƚ����������ϲ��̤�HTML�����
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    ////////// HTML�ե��������
    $file_name = "list/working_hours_report_Correct_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // file������rw�⡼�ɤˤ���
}
