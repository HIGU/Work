<?php
//////////////////////////////////////////////////////////////////////////////
// �������ñ�� ���ڳ�Ψ����Ͽ �ᥤ����                                     //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/05/13 Created  materialPartsCredit_Main.php                         //
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
    $menu->set_title('���ڳ�Ψ����Ͽ');
    //////////// ����ؤ�GET�ǡ�������
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// �꥿���󥢥ɥ쥹����
    $menu->set_RetUrl($_SESSION['product_master_referer']);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    get_serch_master($result, $request);                          // �Ƽ�ǡ����μ���
    
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
    require_once ('materialPartsCredit_View.php');

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
        if ($request->get('assy_no') != '') {
            $request->add('parts_no', $request->get('assy_no'));
        } else {
            $request->add('parts_no', '');
        }
        $request->add('credit_per', '');
        get_serch_master($result, $request);    // �Ƽ�ǡ����μ���
    }
}

////////////// �ɲá��ѹ����å� (��ץ쥳���ɿ��������˹Ԥ�)
function productMaster_entry($request, $result)
{
    if (getCheckAuthority(22)) {                    // ǧ�ڥ����å�
        $parts_no = $request->get('parts_no');
        $credit_per = $request->get('credit_per');
        $query = sprintf("SELECT parts_no FROM parts_credit_per WHERE parts_no='%s'", $parts_no);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
            $query = sprintf("UPDATE parts_credit_per SET parts_no='%s', credit_per='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE parts_no='%s'", $parts_no, $credit_per, $_SESSION['User_ID'], $parts_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no} �γ�Ψ��{$credit_per}���ѹ����ԡ�";      // .= �����
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no}�� ��Ψ��{$credit_per}���ѹ����ޤ�����"; // .= �����
                return true;
            }
        } else {                                    // ��Ͽ�ʤ� INSERT ����   
            $query = sprintf("INSERT INTO parts_credit_per (parts_no, credit_per, last_date, last_user)
                              VALUES ('%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                $parts_no, $credit_per, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no} ��Ψ��{$credit_per}���ɲä˼��ԡ�";      // .= �����
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no} ��Ψ��{$credit_per}���ɲä��ޤ�����";    // .= �����
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
        $parts_no = $request->get('parts_no');
        $credit_per = $request->get('credit_per');
        $query = sprintf("DELETE FROM parts_credit_per WHERE parts_no = '%s'", $parts_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no} ��Ψ��{$credit_per}�κ���˼��ԡ�";   // .= �����
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "�����ֹ桧{$parts_no} ��Ψ��{$credit_per}�������ޤ�����"; // .= �����
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
        SELECT  p.parts_no                  AS �����ֹ�     -- 0
            ,   trim(substr(m.midsc,1,30))  AS ����̾       -- 1
            ,   p.credit_per                AS ��Ψ         -- 2
        FROM
            parts_credit_per AS p
        LEFT OUTER JOIN
            miitem AS m
        ON (p.parts_no = m.mipn)
        
        ORDER BY
            p.parts_no
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] = "��Ψ����Ͽ������ޤ���";
        $field[0]   = "�����ֹ�";
        $field[1]   = "����̾";
        $field[2]   = "��Ψ";
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
function productMaster_copy($request, $result)
{
    $res_g = $result->get_array2('res_g');
    $r = $request->get('number');
    $parts_no   = $res_g[$r][0];
    $credit_per = $res_g[$r][2];
    $request->add('parts_no', $parts_no);
    $request->add('credit_per', $credit_per);
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
    $listTable .= "    <form name='entry_form' action='materialPartsCredit_Main.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='700' bgcolor='#ffffc6' align='center' colspan='15'>\n";
    $listTable .= "                ��Ψ�ޥ�����\n";
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
        $listTable .= "                <a href='../materialPartsCredit_Main.php?number=". $r ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>\n";
        $res_g = $result->get_array2('res_g');
        for ($i=0; $i<$result->get('num_g'); $i++) {    // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:                                 // �����ֹ�
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 1:                                 // ��Ψ
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 2:                                 // ��Ψ
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

////////////// �Ȳ������ʥ��롼�װ���ɽ����HTML�����
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '�Ȳ�');
    ////////// HTML�ե��������
    $file_name = "list/materialPartsCredit_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // file������rw�⡼�ɤˤ���
}
