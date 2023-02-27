<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ŭ�����Ϣ���Ȳ� �ᥤ�� claim_disposal_Main.php                      //
// Copyright (C) 2013-2016 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/01/24 Created  claim_disposal_Main.php                              //
// 2013/01/30 ��Ŭ����֤���������˥塼��ʬ�䤷��                        //
// 2013/05/09 �������׸��������ؤΰ١��ѹ���Ԥä���                        //
// 2016/12/09 ¾��˥塼����Ω�ؼ���˥塼�ˤ���θƤӽФ����Ф���          //
//            �꥿���󤬤��ޤ������ʤ��Τǻųݤ�(various_referer)������ ��ë//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
session_start();                                 // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');             // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');             // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');           // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
access_log();                                    // Script Name �ϼ�ư����

/////////////// �����Ϥ��ѿ����ݴ�
if (isset($_POST['assy_no'])) {
    $_SESSION['assy_no'] = $_POST['assy_no'];                 // �����ֹ�򥻥å�������¸
} elseif (isset($_REQUEST['assy_no'])) {
    $_SESSION['assy_no'] = $_REQUEST['assy_no'];                 // �����ֹ�򥻥å�������¸
}
if ( isset($_SESSION['assy_no']) ) {
    $assy_no = $_SESSION['assy_no'];
} else {
    $assy_no = '';
}
if (isset($_POST['various_referer'])) {
    $_SESSION['various_referer'] = $_POST['various_referer']; // �꥿���󥢥ɥ쥹�Υե饰�򥻥å�������¸
} elseif (isset($_REQUEST['various_referer'])) {
    $_SESSION['various_referer'] = $_REQUEST['various_referer']; // �꥿���󥢥ɥ쥹�Υե饰�򥻥å�������¸
}

main();

function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('��Ŭ�����Ϣ���ξȲ�');
    //////////// ����ؤ�GET�ǡ�������
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    $request->add('assy_no', $_SESSION['assy_no']);
    
    ////////////// �꥿���󥢥ɥ쥹����
    //$menu->set_RetUrl($_SESSION['various_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    ////////////// �꥿���󥢥ɥ쥹���� ¾�Υץ���फ��θƤӽФ��ȶ��̤����
    if ($_SESSION['various_referer'] == 'form') {
        $menu->set_RetUrl('http:' . WEB_HOST . 'industry/custom_attention/claim_disposal_form.php');             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    }
    $_SESSION['various_referer'] == 'off';
    
    get_claim_master($result, $request);                          // �Ƽ�ǡ����μ���
    
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
    require_once ('claim_disposal_List.php');

    ob_end_flush(); 
}

////////////// ������ʬ����Ԥ�
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($ok) {
        ////// �ǡ����ν����
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('group_no', '');
        $request->add('group_name', '');
        get_claim_master($result, $request);    // �Ƽ�ǡ����μ���
    }
}

////////////// ɽ����(����ɽ)����Ŭ�����Ϣ���ǡ�����SQL�Ǽ���
function get_claim_master ($result, $request)
{
    $assy_no = $request->get('assy_no');
    $query_g = "
        SELECT  assy_no                 AS �����ֹ�     -- 0
            ,   midsc                   AS ����̾       -- 1
            ,   publish_date            AS ȯ����       -- 2
            ,   publish_no              AS ȯ���ֹ�     -- 3
            ,   claim_name              AS ��̾         -- 4
        FROM
            claim_disposal_details
        LEFT OUTER JOIN
            miitem
        ON assy_no = mipn
        WHERE assy_no LIKE '{$assy_no}%'
        ORDER BY
            mipn,publish_date
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] = "��Ŭ�����Ϣ������Ͽ������ޤ���";
        $field_g[0]   = "�����ֹ�";
        $field_g[1]   = "����̾";
        $field_g[2]   = "ȯ����";
        $field_g[3]   = "ȯ���ֹ�";
        $field_g[4]   = "��̾";
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', 5);
        $result->add('rows_g', '');
    } else {
        $num_g = count($field_g);
        $result->add_array2('res_g', $res_g);
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', $num_g);
        $result->add('rows_g', $rows_g);
    }
}

////////////// ��Ŭ�����Ϣ���Ȳ���̤�HTML�κ���
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
    $listTable .= "    <form name='entry_form' action='clame_disposal_Main.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='100%' bgcolor='#ffffc6' align='center' colspan='6' nowrap>\n";
    $listTable .= "                <B>��Ŭ�����Ϣ������</B>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='15'>No</th>        <!-- �ԥʥ�С���ɽ�� -->\n";
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
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "            </td>\n";
        $res_g = $result->get_array2('res_g');
        for ($i=0; $i<$result->get('num_g'); $i++) {    // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:                                 // �����ֹ�
                    if ($res_g[$r][$i] == '') {
                        $res_g[$r][$i] = '��';
                    }
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 1:                                 // ����̾
                    if ($res_g[$r][$i] == '') {
                        $res_g[$r][$i] = '��';
                    }
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 2:                                 // ȯ����
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". format_date($res_g[$r][$i]) ."</div></td>\n";
                break;
                case 3:                                 // ȯ���ֹ�
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>\n";
                    $listTable .= "    <a href='../claim_disposal_View.php?assy_no=". $request->get('assy_no') ."&c_assy_no=". $res_g[$r][0] ."&publish_no=". $res_g[$r][$i] ."' target='_parent' style='text-decoration:none;'>\n";
                    $listTable .= "     ". $res_g[$r][$i] ."\n"; 
                    $listTable .= "</div></td>\n";
                break;
                case 4:                                 // ��̾
                    if ($res_g[$r][$i] == '') {
                        $res_g[$r][$i] = '��';
                    }
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

////////////// ��Ŭ�����Ϣ���Ȳ���̤�HTML�����
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '�Ȳ�');
    ////////// HTML�ե��������
    $file_name = "list/claim_disposal_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // file������rw�⡼�ɤˤ���
}
