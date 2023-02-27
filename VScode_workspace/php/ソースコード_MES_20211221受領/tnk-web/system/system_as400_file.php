<?php
//////////////////////////////////////////////////////////////////////////////
// AS/400 OBJ/SRC/File �Ȳ���Ͽ���ѹ� ����                                //
// Copyright (C) 2002-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2002/12/10 Created  system_as400_file.php                                //
//                      Excel �Ǵ������Ƥ���ʪ�� Tnk Web System ��          //
// 2003/02/23 date("Y/m/d H:m:s") �� H:i:s �Υߥ�����                       //
// 2003/02/26 body �� onLoad ���ɲä�������ϸĽ�� focus() ������          //
//    �ե�����ɿ��μ�����ˡ�ѹ� getResult2() ����Ѥ� $num = count();      //
// 2003/03/05 $filename �� $_POST['file_name'] �Υߥ������� 176����         //
// 2003/05/01 ������ʬ��ե�����ɤ�center�� ʬ��=2 �ץ���� ���ɲ�       //
// 2003/07/11 ���ΤΥǥ������ Windows �����ѹ� ���뤬�⤤�Ƥ�褦��        //
// 2003/11/05 �����ܥ��󤬲����줿���� order by file_name ASC ���ɲ�        //
//            Ʊ��  (PAGE+20)���ɲä�������̤ΰ���ɽ��¿��ɽ��������       //
// 2003/12/03 ����ɽ(�߽�)�����Ͻ�(����)�λ��˼��ǡ����Ǥˤ�ȿ�Ǥ�����      //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/05/21 page_keep����as_offset�� offset�ˤʤäƤ����Τ���           //
// 2004/10/13 DB���Ӥ�<pre>��������¸�Υǡ������б����Ƥ��ʤ�������ǰ   //
// 2005/02/23 SQLʸ������� $file_name = stripslashes($file_name);���ɲ�    //
//            MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2007/02/07 overflow�������PAGE��Post�ǡ���3������ѹ�1�ǹԿ���12��20��  //
// 2007/02/23 addslashes(), stripslashes()�����Ƴ����ƽ�������ΰ��٤�����  //
// 2007/06/25 as400_file_view ���ɲ� SQL������ɤ���ʸ���˽���(����˽��)//
// 2007/10/19 ���硼�ȥ��åȤ�ɸ�ॿ���ء�E_ALL �� E_ALL | E_STRICT ��      //
//             onKeyUp='baseJS.keyInUpper(this);' ��ɬ�פʥե�������ɲ�    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('magic_quotes_gpc', '0');           // PHP_INI_PERDIR 2 php.ini, .htaccess �ޤ��� httpd.conf�������ǽ�ʥ���ȥ�
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(3);                  // ǧ�ڥ����å�0=���̰ʾ� 3=admini�ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(99, 31);                    // site_index=99(�����ƥ��˥塼) site_id=60(AS400 file)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('AS/400 Object Source File Reference');
//////////// ɽ�������
$menu->set_caption('AS/400 Object & Source & File ����');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

///// POST �ѿ��ν����
if (isset($_POST['as_sel'])) {
    $as_sel = $_POST['as_sel'];     // POST �ǡ����ǽ����
} else {
    $as_sel = '';                   // �����
}
    ///// �ʲ��� php.ini �� magic_quotes_gpc=on �ˤʤäƤ��뤿��ν���� 2007/02/23 �ѹ�
    ///// pg_escape_string()���ղä�����PostgreSQL 8.2.3��standard_conforming_strings = on�ˤ��Ƥ��뤿�ᳰ������
if (isset($_POST['file_name'])) {
    $file_name = stripslashes($_POST['file_name']); // POST �ǡ����ǽ����
} else {
    $file_name = '';                    // �����
}
if (isset($_POST['obj_lib'])) {
    $obj_lib = stripslashes($_POST['obj_lib']);     // POST �ǡ����ǽ����
} else {
    $obj_lib = '';                      // �����
}
if (isset($_POST['src_lib'])) {
    $src_lib = stripslashes($_POST['src_lib']);     // POST �ǡ����ǽ����
} else {
    $src_lib = '';                      // �����
}
if (isset($_POST['file_note'])) {
    $file_note = stripslashes($_POST['file_note']); // POST �ǡ����ǽ����
} else {
    $file_note = '';                    // �����
}

//////////// ���ǤιԿ�
define('PAGE', '20');

//////////// ����ɽ���¤ӽ�����
if (isset($_POST['search']) || ($as_sel != '')) {
    $_SESSION['as400_view'] = '�ɲý�';
    $_POST['view'] = 'view';                        // ɽ��������
} elseif ( isset($_POST['view']) ) {
    if ($_POST['view'] == '���Ͻ�') {
        $_SESSION['as400_view'] = '���Ͻ�';
        $_POST['view'] = 'view';                    // ɽ��������
    }
    if ($_POST['view'] == '����ɽ') {
        $_SESSION['as400_view'] = '�ɲý�';
        $_POST['view'] = 'view';                    // ɽ��������
    }
}
if ( !(isset($_SESSION['as400_view'])) ) {
    $_SESSION['as400_view'] = '�ɲý�';             // default ����
                                                    // ɽ�������ʤ�
}

//////////// ��Ͽ�쥳���ɿ� ����
$table_name = getTableName();
if ($_SESSION['as400_view'] == '���Ͻ�') {
    $query = "SELECT count(*) FROM {$table_name}";
} else {
    $query = "SELECT count(*) FROM {$table_name} WHERE last_date IS NOT NULL";
}
$res = array();
if(($rows=getResult($query,$res))>=1){
    $maxrows = $res[0][0];
}

//////////// �ڡ������ե��å�����
if ( isset($_POST['forward']) ) {
    $_SESSION['as_offset'] += PAGE;
    if ($_SESSION['as_offset'] >= $maxrows) {
        $_SESSION['as_offset'] = ($maxrows - 1);
    }
} elseif ( isset($_POST['backward']) ) {
    $_SESSION['as_offset'] -= PAGE;
    if ($_SESSION['as_offset'] < 0) {
        $_SESSION['as_offset'] = 0;
    }
} elseif ( isset($_GET['page_keep']) ) {                // ���ߤΥڡ�����ݻ����� GET�����
    $offset = $_SESSION['as_offset'];
} else {
    // if(!isset($_SESSION['as_offset']))     // ���ǡ����� �ʳ��Ͻ���ͤ��᤹����¾����ˡ��system_menu���ν����˥塼�� unset($_SESSION['as_offset'])���롣
    $_SESSION['as_offset'] = 0;
}
$offset = $_SESSION['as_offset'];

/////////// ���ƥʥ󥹤Υ쥳�����ɲ�(��Ͽ) as_sel=��Ͽ
if($as_sel == "��Ͽ"){
    $res_sel = array();
    $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM as400_file WHERE file_name='"
        . $file_name . "' and obj_lib='" . $obj_lib . "'";
    $rows_sel = getResult($query,$res_sel);
    if($rows_sel <= 0){
        if($_POST['category'] == ""){         ////// category = ʬ��� NULL �����
            $query = "insert into as400_file (file_name,obj_lib,src_lib,file_note) values ('"
                . $file_name . "','" . $obj_lib . "','" . $src_lib . "','" . $file_note . "')";
        }else{
            $query = "insert into as400_file (file_name,obj_lib,src_lib,file_note,category) values ('"
                . $file_name . "','" . $obj_lib . "','" . $src_lib . "','" . $file_note . "'," . $_POST['category'] . ")";
        }
        if(query_affected($query) >= 1)     /////// �������ѥ����꡼����Ͽ
            $_SESSION['s_sysmsg'] = "<font color='yellow'>[file_name = " . $file_name . "] [obj_lib = " . $obj_lib . "]����Ͽ���ޤ���!</font>";
        else
            $_SESSION['s_sysmsg'] = "[file_name = " . $file_name . "] [obj_lib = " . $obj_lib . "]����Ͽ ERROR";
    }else
        $_SESSION['s_sysmsg'] = "[file_name = " . $file_name . "] [obj_lib = " . $obj_lib . "]�� ������Ͽ����Ƥ��ޤ�!";
}
/////////// ���ƥʥ󥹤Υ쥳���ɺ�� as_sel=���
if($as_sel == "���"){
    $res_sel = array();
    $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM as400_file WHERE file_name='"
        . $_SESSION['as_file_name'] . "' and obj_lib='" . $_SESSION['as_obj_lib'] . "'";
    $rows_sel = getResult($query,$res_sel);
    if($rows_sel >= 1){
        $query = "delete FROM as400_file WHERE file_name='" . $_SESSION['as_file_name'] . "' and obj_lib='" . $_SESSION['as_obj_lib'] . "'";
        if(($del_rows = query_affected($query)) >= 1)     /////// �������ѥ����꡼����Ͽ
            $_SESSION['s_sysmsg'] = "<font color='yellow'>[file_name = " . $_SESSION['as_file_name'] . "] [obj_lib = " . $_SESSION['as_obj_lib'] . "] �Υ����� $del_rows:�쥳���ɺ�����ޤ���!</font>";
        else
            $_SESSION['s_sysmsg'] = "[file_name = " . $_SESSION['as_file_name'] . "] [obj_lib = " . $_SESSION['as_obj_lib'] . "] �Υ����ǥ�����:$del_rows:Error";
    }else
        $_SESSION['s_sysmsg'] = "[file_name = " . $_SESSION['as_file_name'] . "] [obj_lib = " . $_SESSION['as_obj_lib'] . "] �Υ����ǥ쥳���ɤ����Ĥ���ʤ�!";
    /* ���å�����ѿ��κ�� */
    unset($_SESSION['as_file_name']);
    unset($_SESSION['as_obj_lib']);
}
/////////// ���ƥʥ󥹤Υ쥳�����ѹ� as_sel=�ѹ�
if($as_sel == "�ѹ�"){
    $res_sel = array();
    $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM as400_file WHERE file_name='"
        . $_SESSION['as_file_name'] . "' and obj_lib='" . $_SESSION['as_obj_lib'] . "'";
    $rows_sel = getResult($query,$res_sel);
    if($rows_sel >= 1){
        if($_POST['category'] != ""){
            $query = "update as400_file set file_name='" . $file_name . "',obj_lib='" . $obj_lib
                . "',src_lib='" . $src_lib . "',file_note='" . $file_note . "',category='" . $_POST['category']
                . "' WHERE file_name='" . $_SESSION['as_file_name'] . "' and obj_lib='" . $_SESSION['as_obj_lib'] . "'";
        }else{
            $query = "update as400_file set file_name='" . $file_name . "',obj_lib='" . $obj_lib
                . "',src_lib='" . $src_lib . "',file_note='" . $file_note . "',category=NULL"
                . " WHERE file_name='" . $_SESSION['as_file_name'] . "' and obj_lib='" . $_SESSION['as_obj_lib'] . "'";
        }
        if(($chg_rows = query_affected($query)) >= 1)     /////// �������ѥ����꡼����Ͽ
            $_SESSION['s_sysmsg'] = "<font color='yellow'>[file_name = " . $_SESSION['as_file_name'] . "] [obj_lib = " . $_SESSION['as_obj_lib'] . "] �Υ����� $chg_rows:�쥳�����ѹ����ޤ���!</font>";
        else
            $_SESSION['s_sysmsg'] = "[file_name = " . $_SESSION['as_file_name'] . "] [obj_lib = " . $_SESSION['as_obj_lib'] . "] �Υ����ǥ�����:$chg_rows:Error";
    }else
        $_SESSION['s_sysmsg'] = "[file_name = " . $_SESSION['as_file_name'] . "] [obj_lib = " . $_SESSION['as_obj_lib'] . "] �Υ����ǥ쥳���ɤ����Ĥ���ʤ�!";
    /* ���å�����ѿ��κ�� */
    unset($_SESSION['as_file_name']);
    unset($_SESSION['as_obj_lib']);
}

//////////// ����ɽ�Υǡ�������
$res = array();
if ($_SESSION['as400_view'] == '�ɲý�') {
    if ($file_name != '') {
        ///// SQL��LIKEʸ��search���뤿��Ǹ夬\�ξ��ϥ��������פΰ�̣��\���ɲ�
        if (substr($file_name, -1, 1) == "\\") {
            $file_temp = $file_name . "\\";
        } else {
            $file_temp = $file_name;
        }
        if ($file_note != '') {         ////// name �� note ��ξ�����ꤵ��Ƥ�����
            $query = "
                SELECT file_name, obj_lib, src_lib, file_note, category FROM {$table_name}
                WHERE file_name LIKE '{$file_temp}%' and file_note LIKE '%{$file_note}%'
                ORDER BY file_name ASC LIMIT 
            ". (PAGE+20);
        } else {                        /////// name �������ꤷ�Ƥ�����
            $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM {$table_name} WHERE file_name like '"
                . $file_temp . "%' order by file_name ASC limit ". (PAGE+20);
        }
    } elseif ($file_note != "") {       /////// note �������ꤷ�Ƥ�����
        $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM {$table_name} WHERE file_note like '%"
            . $file_note . "%' order by file_name ASC limit ". (PAGE+20);
    } else {                            /////// name �� note ����ꤵ��Ƥ��ʤ����� ����ɽ��Ʊ���褦������郎�㤦
        $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM {$table_name} WHERE last_date IS NOT NULL order by last_date DESC offset $offset limit ".PAGE;
    }
    if (($rows = getResult2($query,$res)) > 0) {        // Ϣ�������Ȥ�ʤ� �ե�����ɿ������Τ��� getResultWithField($query, $field, $result) ��Ȥ���ˡ�⤢�� $num = count($field);
        $num = count($res[0]);      // �ե�����ɿ� ����
    } else {
        $num = 0;                       // 0 �ǽ����
        // phpinfo(INFO_CONFIGURATION | INFO_VARIABLES);
    }
    // $_POST['view'] = '����ɽ';   // �¤ӽ�����ذ�ư
} else {                                 //////// ����ɽ�������Ͻ��
    $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM {$table_name} offset $offset limit ".PAGE;
    if (($rows = getResult2($query,$res)) > 0) {        // Ϣ�������Ȥ�ʤ� �ե�����ɿ������Τ��� getResultWithField($query, $field, $result) ��Ȥ���ˡ�⤢�� $num = count($field);
        $num = count($res[0]);      // �ե�����ɿ� ����
    } else {
        $num = 0;                       // 0 �ǽ����
    }
}

/////////// ���ƥʥ󥹤Τ���Υ쥳�������� as_sel=select
if($as_sel == "select"){
    $res_sel = array();
    $query = "SELECT file_name,obj_lib,src_lib,file_note,category FROM {$table_name} WHERE file_name='"
        . $file_name . "' and obj_lib='" . $obj_lib . "'";
    if ( ($rows_sel=getResult2($query,$res_sel)) > 0) {
        $file_name = $res_sel[0][0];
        $obj_lib   = $res_sel[0][1];
        $src_lib   = $res_sel[0][2];
        $file_note = $res_sel[0][3];
        $category  = $res_sel[0][4];
    } else {
        $file_name = '';
        $obj_lib   = '';
        $src_lib   = '';
        $file_note = '';
        $category  = '';
        $_SESSION['s_sysmsg'] = '�ǡ��������ǥ��顼��ȯ�����ޤ�����';
        // phpinfo(INFO_CONFIGURATION | INFO_VARIABLES);
        phpinfo(INFO_VARIABLES);
    }
    /* ���ꥸ�ʥ� �����ե�����ɤ���¸ */
    $_SESSION['as_file_name'] = $file_name;
    $_SESSION['as_obj_lib']   = $obj_lib;
}

////////// �����С��ե�����
if (isset($rows)) {
    if(isset($_POST['view']) || isset($_POST['forward']) || isset($_POST['backward'])) {
        $overflow = "style='overflow-y:scroll;'";
    } else {
        $overflow = "style='overflow-y:hidden;'";
    }
}
function getTableName()
{
    if ($_SESSION['User_ID'] == '010561') {
        return 'as400_file';
    } else {
        return 'as400_file_view';
    }
}

///////////// HTML Header ����Ϥ��ƥ֥饦�����Υ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title()?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<script language='JavaScript'>
/* ����ʸ�����������ɤ��������å� */
function isDigit(str){
    var len=str.length;
    var c;
    for(i=0;i<len;i++){
        c=str.charAt(i);
        if("0">c||c>"9")
            return false;
        }
    return true;
}

/*  ����ե��٥åȤ���ʸ���Ѵ�  */
function file_name_up(obj){
    obj.file_name.value = obj.file_name.value.toUpperCase();
}

/*  ̤���ϥե�����ɤΥ����å�(ɬ�ܹ���)  */
function edit_chk(obj){
    if(!obj.file_name.value.length){
        alert("[File Name]�������󤬶���Ǥ���");
        obj.file_name.focus();
        return false;
    }
    obj.file_name.value = obj.file_name.value.toUpperCase();
    if(!obj.obj_lib.value.length){
        alert("[OBJ LIB]�������󤬶���Ǥ���");
        obj.obj_lib.focus();
        return false;
    }
    obj.obj_lib.value = obj.obj_lib.value.toUpperCase();
    if(!obj.src_lib.value.length){
        alert("[SRC LIB]�������󤬶���Ǥ���");
        obj.src_lib.focus();
        return false;
    }
    obj.src_lib.value = obj.src_lib.value.toUpperCase();
    if(!obj.file_note.value.length){
        alert("[DB ����]�������󤬶���Ǥ���");
        obj.file_note.focus();
        return false;
    }
    if(obj.category.value.length){
        if(!isDigit(obj.category.value)){
            alert("[ʬ��]��������˿����ʳ��Υǡ���������ޤ�!");
            obj.category.focus();
            obj.category.select();
            return false;
        }
    }
    return true;
}
// -->
</script>
<style type="text/css">
<!--
th {
    background-color:   yellow;
    color:              blue;
    font-size:              11pt;
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
td.gb {
    background-color:   #d6d3ce;
    color:              black;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
}
td {
    font-size: 11pt;
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
.white {
    color: white;
}
.y_b {
    background-color:   yellow;
    color:              blue;
}
.r_b {
    background-color:   red;
    color:              black;
}
.b_w {
    background-color:   blue;
    color:              white;
}
-->
</style>
</head>
<body onLoad='document.ini_form.file_name.focus()' <?php echo $overflow?>>
    <center>
<?php echo $menu->out_title_border()?>
        
        <table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <form name='ini_form' method='post' action='<?php echo $menu->out_self()?>' onSubmit='return file_name_up(this)'>
                    <td align='left' nowrap>
                        �����������Ϥ��Ʋ�������
                        Object or File
                        <input type='text' name='file_name' size='10' maxlength='8' value='<?php echo $file_name ?>' onKeyUp='baseJS.keyInUpper(this);'>
                        �ǡ����١�������
                        <input type='text' name='file_note' size='50' maxlength='40' value='<?php echo $file_note ?>'>
                        <input type='submit' name='search' value='����' >
                    </td>
                </form>
            </tr>
            <tr>
                <td>
                    <table class='winbox_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='2'>
                        <tr align='center'>
                            <form method='post' action='system_as400_file.php'>
                                <td>
                                    <input type='submit' name='view' value='����ɽ' >
                                </td>
                                <td>
                                    <input type='submit' name='view' value='���Ͻ�' >
                                </td>
                                <td>
                                    [Object or File] �� [�ǡ����١�������] ��ξ�����ꤷ������ and �����ˤʤ�ޤ���
                                </td>
                                <td>
                                    <input type='submit' name='as_sel' value='�ɲ�' >
                                </td>
                            </form>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    <?php
        if($as_sel == "select"){
            echo "<hr>\n";
            echo "<table width='100%' class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='1' cellpadding='3'>\n";
            echo "  <caption class='pt12b'>AS/400 Object & Source & File ���ƥʥ� \n";
            echo "  </caption>\n";
            echo("  <th nowrap class='b_w'>File Name</th><th nowrap class='b_w'>OBJ LIB</th><th nowrap class='b_w'>SRC LIB</th><th nowrap class='b_w'>DB ����</th><th nowrap class='b_w'>ʬ��</th><th nowrap class='b_w'>---</th><th nowrap class='b_w'>---</th>\n");
            echo("  </tr>\n");
            echo "  <form method='post' action='system_as400_file.php' onSubmit='return edit_chk(this)'>\n";
            echo("      <td><input type='text' name='file_name' size='11' maxlength='8' value='$file_name' onKeyUp='baseJS.keyInUpper(this);'></td>\n");
            echo("      <td><input type='text' name='obj_lib' size='12' maxlength='10' value='$obj_lib' onKeyUp='baseJS.keyInUpper(this);'></td>\n");
            echo("      <td><input type='text' name='src_lib' size='12' maxlength='10' value='$src_lib' onKeyUp='baseJS.keyInUpper(this);'></td>\n");
            echo("      <td><input type='text' name='file_note' size='80' maxlength='256' value='$file_note'></td>\n");
            echo("      <td><input type='text' name='category' size='5' maxlength='5' value='$category'></td>\n");
            echo("      <td align='center'><input type='submit' name='as_sel' value='�ѹ�'></td>\n");
            echo("      <td align='center'><input type='submit' name='as_sel' value='���'></td>\n");
            echo "  </form>\n";
            echo("  </tr>\n");
            echo("</table>\n");
        }
        if($as_sel == "�ɲ�"){
            echo "<hr>\n";
            echo "<table width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='1' cellpadding='3'>\n";
            echo "  <caption class='pt12b'>AS/400 Object & Source & File ���ƥʥ� \n";
            echo "  </caption>\n";
            echo("  <th nowrap class='b_w'>File Name</th><th nowrap class='b_w'>OBJ LIB</th><th nowrap class='b_w'>SRC LIB</th><th nowrap class='b_w'>DB ����</th><th nowrap class='b_w'>ʬ��</th><th nowrap class='b_w'>�ɲ�</th>\n");
            echo("  </tr>\n");
            echo "  <form method='post' action='system_as400_file.php' onSubmit='return edit_chk(this)'>\n";
            echo("      <td><input type='text' name='file_name' size='11' maxlength='8' value='' onKeyUp='baseJS.keyInUpper(this);'></td>\n");
            echo("      <td><input type='text' name='obj_lib' size='12' maxlength='10' value='' onKeyUp='baseJS.keyInUpper(this);'></td>\n");
            echo("      <td><input type='text' name='src_lib' size='12' maxlength='10' value='' onKeyUp='baseJS.keyInUpper(this);'></td>\n");
            echo("      <td><input type='text' name='file_note' size='80' maxlength='256' value=''></td>\n");
            echo("      <td><input type='text' name='category' size='5' maxlength='5' value=''></td>\n");
            echo("      <td align='center'><input type='submit' name='as_sel' value='��Ͽ'></td>\n");
            echo "  </form>\n";
            echo("  </tr>\n");
            echo("</table>\n");
        }
        if(isset($_POST['view']) || isset($_POST['forward']) || isset($_POST['backward'])){
            echo "<hr>\n";
            echo "<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>\n";
            echo "    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>\n";
            echo "<table width='100%' class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
            echo "  <form method='post' action='system_as400_file.php'>\n";
            echo "  <caption>\n";
            echo "      <font class='pt12b'>", $menu->out_caption(), "</font>\n";
            echo "      <input type='submit' name='backward' value='����'>\n";
            echo "      <input type='submit' name='forward' value='����'>\n";
            echo "      <font class='pt9'>  ʬ��=2 �ϥץ����</font>\n";
            echo "  </caption>\n";
            echo "  </form>\n";
            print(" <th nowrap class='y_b'>No</th><th nowrap class='y_b'>File Name</th><th nowrap class='y_b'>OBJ LIB</th><th nowrap class='y_b'>SRC LIB</th><th nowrap class='y_b'>DB ����</th><th nowrap class='y_b'>ʬ��</th>\n");
            for($r=0;$r<$rows;$r++){
                print("<tr>\n");
                echo "  <form method='post' action='system_as400_file.php'>\n";
                print(" <td align='center'><input type='submit' name='copy' value='" . ($r + $offset + 1) . "'></td>\n");
                echo "      <input type='hidden' name='as_sel' value='select'>\n";
                echo "      <input type='hidden' name='file_name' value='" . $res[$r][0] . "'>\n";
                echo "      <input type='hidden' name='obj_lib' value='" . $res[$r][1] . "'>\n";
                echo "  </form>\n";
                for($n=0;$n<$num;$n++){
                    if($res[$r][$n] == "")
                        echo("<td nowrap align='center'>---</td>\n");
                    else
                        if ($n == 3) {          // DB ����
                            echo("<td width='100%' align='left'>" . $res[$r][$n] . "</td>\n");
                        } elseif ($n == 4) {    // ʬ��
                            echo("<td width='40' align='center'>" . $res[$r][$n] . "</td>\n");
                        } else {
                            echo("<td nowrap width='60' align='left'>" . $res[$r][$n] . "</td>\n");
                        }
                }
                print("</tr>\n");
            }
            echo "</table>\n";
            echo "    </td></tr>\n";
            echo "</table> <!----------------- ���ߡ�End ------------------>\n";
        }
    ?>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();     // ���ϥХåե���gzip���� END
?>
