<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� ���ʾ���Ȳ񤫤�ѥ�����ѹ�����                        //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created chg_passwd.php                                        //
// 2002/08/07 register_globals = Off �б� & ���å�������                  //
//                         �����С��Υ�����桼�����ѹ������򥳥���     //
// 2004/04/19 table������ѹ� �ܤ����� kk_table_create �򻲾� user_master   //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2005/11/24 �ѥ���ɤ�Ź沽����Ͽ���� �ѥ�����ѹ���λ�Υ�å������� //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // ���� �ؿ�
require_once ('emp_function.php');          // �Ұ���˥塼����
// require("../define.php");                   // function.php �� require ����Ƥ���
// define("CMD_STR_PASSWD","/usr/bin/sudo /usr/sbin/newusers ");
access_log();                               // Script Name ��ư����

$_SESSION['userid'] = $_POST['userid'];
$_SESSION['kana']   = $_POST['kana'];
$_SESSION['name']   = $_POST['name'];
$_SESSION['section_name'] = $_POST['section_name'];
$_POST['passwd']    = md5($_POST['passwd']);

//  $userinfo = "&userid=" . $_POST['userid'];
//  $histnum = $_POST['histnum'] - 1;
//  $lookupinfo = "&lookupkind=" . $_POST['lookupkind'] . "&lookupkey=" . $_POST['lookupkey'] . "&lookupkeykind=" . $_POST['lookupkeykind'] . 
//      "&lookupsection=" . $_POST['lookupsection'] . "&lookupposition=" . $_POST['lookupposition'] . "&lookupentry=" . $_POST['lookupentry'] . 
//      "&lookupcapacity=" . $_POST['lookupcapacity'] . "&lookupreceive=" . $_POST['lookupreceive'] . "&histnum=" . $_POST['histnum'] . "&retireflg=" . $_POST['retireflg'];

if (funcConnect()) {
    execQuery("begin");

    $query="update user_master set passwd='" . $_POST['passwd'] . "' where uid='" . $_POST['userid'] . "'";
    if (!execQuery($query)) {
        execQuery("end");
        disConnectDB();
        
        /* add 09/27 begin */
//      $file=TEMP_DIR . "user";
//      $str=sprintf("%s:%s:::::\n",$acount,$passwd);
//      if($fp=fopen($file,"w")){
//          fwrite($fp,$str,strlen($str));
//          fclose($fp);
//          $cmd=escapeshellcmd(CMD_STR_PASSWD . $file);
//          exec($cmd);
//          unlink($file);
//      }
        /* end */
        
        $_SESSION['s_sysmsg'] = "�ѥ���ɤ��ѹ����ޤ�����";
        if ($_GET['func'] == FUNC_MINEINFO) {
            $_SESSION['Password'] = $_POST['passwd'];
            header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . $_GET['func']);
        } else {
            $_SESSION['Password'] = $_POST['passwd'];
            header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . $_POST['func'] . '&pwd=' . $_POST['pwd']);
        }
        exit();
    } else {
        execQuery('rollback');
        disConnectDB();
    }
}
$_SESSION['s_sysmsg'] = "�ѥ���ɤ��ѹ��˼��Ԥ��ޤ�����<br>�����Ԥ�Ϣ���Ʋ�������";
if ($_GET['func'] == FUNC_MINEINFO) {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_MINEINFO . '&pwd=1');
} else {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGUSERINFO . '&pwd=1');
}
?>
