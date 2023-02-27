<?php
//////////////////////////////////////////////////////////////////////////////
// �����ץ���񡦺��������Խ� �ᥤ�� attention_point_add_Main.php       //
// Copyright (C) 2013-2013 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/01/31 Created  attention_point_add_Main.php                         //
// 2013/02/01 ��Ͽ�����μ�����ˡ��YYYY/MM/DD���ѹ�                          //
// 2013/02/06 ɽ��������Ĵ��                                                //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
session_start();                                 // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');             // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');             // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');           // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK ������ MVC Controller Class
access_log();                                    // Script Name �ϼ�ư����

/////////////// �����Ϥ��ѿ����ݴ�
if (isset($_POST['assy_no'])) {
    $_SESSION['assy_no'] = $_POST['assy_no'];                 // �����ֹ�򥻥å�������¸
}
if ( isset($_SESSION['assy_no']) ) {
    $assy_no = $_SESSION['assy_no'];
} else {
    $assy_no = '';
}

main();

function main()
{
    ///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    //////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
    $menu->set_title('�����ץ���񡦺����������Խ�');
    //////////// ����ؤ�GET�ǡ�������
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    $request->add('assy_no', $_SESSION['assy_no']);
    
    ////////////// �꥿���󥢥ɥ쥹����
    $menu->set_RetUrl('http:' . WEB_HOST . 'industry/custom_attention/attention_point_add_form.php');             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
    
    get_point_master($result, $request);        // �Ƽ�ǡ����μ���
    
    request_check($request, $result, $menu);    // ������ʬ�������å�
    
    outViewListHTML($request, $menu, $result);  // HTML����
    
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
    require_once ('attention_point_add_View.php');

    ob_end_flush(); 
}

////////////// ������ʬ����Ԥ�
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = attention_point_copy($request, $result);
    if ($request->get('del') != '') $ok = groupMaster_del($request);
    if ($request->get('entry') != '')  $ok = attention_point_entry($request, $result);
    if ($ok) {
        ////// �ǡ����ν����
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('assy_name', '');
        $request->add('point_name', '');
        $request->add('point_note', '');
        $request->add('file_name', '');
        $request->add('file_data', '');
        $request->add('last_date', '');
        get_point_master($result, $request);    // �Ƽ�ǡ����μ���
    }
}

////////////// �ɲá��ѹ����å� (��ץ쥳���ɿ��������˹Ԥ�)
function attention_point_entry($request, $result)
{
        $assy_no        = $request->get('assy_no');
        $point_name     = $request->get('point_name');
        $point_note     = $request->get('point_note');
        $file_name      = $_FILES["file_name"]["name"];
        $query = sprintf("SELECT * FROM attention_point_details WHERE assy_no='%s' AND point_name='%s'", $assy_no, $point_name);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� �����ϤǤ��ʤ��褦�ˤ���
            $_SESSION['s_sysmsg'] .= "Ʊ��������Ƥ����Ǥ���Ͽ����Ƥ��ޤ����ѹ�������ϰ��ٺ�����Ƥ��鿷����Ͽ���Ƥ���������";
            $msg_flg = 'alert';
            return false;
        } else {                                    // ��Ͽ�ʤ� INSERT ����
            //�ե������ʣ�����å�
            if (is_uploaded_file($_FILES["file_name"]["tmp_name"])) {
                $dir      = 'files/';
                $filelist = scandir($dir);
                foreach($filelist as $file){
                    if(!is_dir($file)){
                        if($file_name==$file){
                            $_SESSION['s_sysmsg'] .="�ե�����̾����ʣ���Ƥ���Τǥ��åץ��ɤǤ��ޤ���";
                            $msg_flg = 'alert';
                            return false;
                        }
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] .= "�ե����뤬���򤵤�Ƥ��ޤ���";
                $msg_flg = 'alert';
                return false;
            }
            $query = sprintf("INSERT INTO attention_point_details (assy_no, point_name, point_note, file_name, last_date, last_user)
                              VALUES ('%s', '%s', '%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                $assy_no, $point_name, $point_note, $file_name, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "���롼���ֹ桧{$group_no} ���롼��̾��{$group_name}���ɲä˼��ԡ�";      // .= �����
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "�����ֹ桧{$assy_no} ������ơ�{$point_name}���ɲä��ޤ�����";    // .= �����
                if (move_uploaded_file($_FILES['file_name']['tmp_name'], 'files/' . $_FILES['file_name']['name'])) {
                    chmod('files/' . $_FILES['file_name']['name'], 0644);
                    $_SESSION['s_sysmsg'] .= $_FILES['file_name']['name'] . "�򥢥åץ��ɤ��ޤ�����";
                } else {
                    $_SESSION['s_sysmsg'] .= "�ե�����Υ��åץ��ɤ˼��Ԥ��ޤ�����";
                }
                return true;
            }
        }
}

////////////// ������å� (��ץ쥳���ɿ��������˹Ԥ�)
function groupMaster_del($request)
{
        $assy_no     = $request->get('assy_no');
        $point_name  = $request->get('point_name');
        $deletefiles = $request->get('file_name_cp');
        $dir         = 'files/';
        if(file_exists($dir.$deletefiles)){
            unlink($dir.$deletefiles);
        }
        $query = sprintf("DELETE FROM attention_point_details WHERE assy_no = '%s' AND point_name = '%s'", $assy_no, $point_name);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "�����ֹ桧{$assy_no} ������ơ�{$point_name}�κ���˼��ԡ�";   // .= �����
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "�����ֹ桧{$assy_no} ������ơ�{$point_name}�������ޤ����� {$deletefiles}"; // .= �����
            return true;
        }
}
////////////// ɽ����(����ɽ)�Υ��롼�ץޥ������ǡ�����SQL�Ǽ���
function get_point_master ($result, $request)
{
    $assy_no = $request->get('assy_no');
    $query_g = "
        SELECT  to_char(last_date, 'YYYY/MM/DD')    AS ��Ͽ����          -- 0
            ,   point_name                          AS �������          -- 1
            ,   point_note                          AS ����              -- 2
            ,   file_name                           AS �ե�����̾        -- 3
        FROM
            attention_point_details
        WHERE assy_no = '{$assy_no}'
        ORDER BY
            point_name
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] .= "���������Ͽ������ޤ���";
        $field_g[0]   = "��Ͽ����";
        $field_g[1]   = "�������";
        $field_g[2]   = "����";
        $field_g[3]   = "�ե�����̾";
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', 4);
        $result->add('rows_g', '');
    } else {
        $num_g = count($field_g);
        $result->add_array2('res_g', $res_g);
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', 4);
        $result->add('rows_g', $rows_g);
    }
    $query = "
            SELECT  midsc          AS ����̾                 -- 0
            FROM
                miitem
            WHERE mipn = '{$assy_no}'
        ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $assy_name = '��';
    } else {
        $assy_name = $res[0][0];
    }
    $result->add('assy_name', $assy_name);
}

////////////// ���ԡ��Υ�󥯤������줿��
function attention_point_copy($request, $result)
{
    $res_g = $result->get_array2('res_g');
    $r = $request->get('number');
    $point_name = $res_g[$r][1];
    $point_note = $res_g[$r][2];
    $file_name  = $res_g[$r][3];
    $request->add('point_name', $point_name);
    $request->add('point_note', $point_note);
    $request->add('file_name_cp', $file_name);
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
    $listTable .= "    <form name='entry_form' action='attention_point_add_Main.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- �ơ��֥� �إå�����ɽ�� -->\n";
   $listTable .= "            <tr>\n";
    $listTable .= "                <td width='100%' bgcolor='#ffffc6' align='center' colspan='5' nowrap>\n";
    $listTable .= "                <B>�����ץ���񡦺�����������</B>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='100%' bgcolor='#ffffc6' align='center' colspan='5' nowrap>\n";
    $listTable .= "                <B>" . $request->get('assy_no') . "��" . $result->get('assy_name') . "</B>\n";
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
        $listTable .= "                <a href='../attention_point_add_Main.php?number=". $r ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>\n";
        $res_g = $result->get_array2('res_g');
        for ($i=0; $i<$result->get('num_g'); $i++) {    // �쥳���ɿ�ʬ���֤�
            switch ($i) {
                case 0:                                 // ��Ͽ����
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 1:                                 // �������
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 2:                                 // ����
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 3:                                 // �ե�����̾
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
    $file_name = "list/attention_point_add_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // file������rw�⡼�ɤˤ���
}
