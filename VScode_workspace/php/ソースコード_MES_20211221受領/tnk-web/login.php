<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� ����������å�                                            //
// Copyright (C) 2001-2004 Kazuhiro.Kobayashi tnkyss@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   login.php                                           //
// 2002/08/07 ���å����������ɲ�                                          //
// 2002/08/26 �ե졼���Ȥäƥ����ȥ�˥塼���ɲ�                          //
// 2003/12/15 ��å�����������ѹ�(���ϥߥ������ɲ�, ���������ʤ�)          //
// 2004/02/13 index1.php �� authenticate.php ���ѹ�                         //
// 2004/06/10 ��ȯ�ѥƥ�ץ졼���ѤΥ꥿���󥢥ɥ쥹��ƥ����Ѥ��ɲ�        //
// 2005/11/24 ���å����ˤ�ѥ���ɤΰŹ沽���ɲ�                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('function.php');              // TNK���̥ե��󥯥å����
access_log();                               // Script Name �ϼ�ư����

// session_register('web_file','login_time','User_ID','Password','Auth');
$_SESSION['login_time'] = Date('m-d H:i');
$_SESSION['User_ID']    = $_POST['userid'];
$_SESSION['Password']   = md5($_POST['passwd']);
//  $_SESSION['Auth']       = $authority;
$_SESSION['web_file']   = $_SERVER['SCRIPT_NAME'];

if ( authorityUser($_POST['userid'], $_SESSION['Password'], $authority) ) {
    /*
    $uid   = $_POST['userid'];
    $query = "SELECT sid FROM user_detailes WHERE uid='$uid'";
    $res   = array();
    getResult($query,$res);
    if ($res[0][0] == '95') {          // ��°�����칩��ξ��
        //$_SESSION['s_sysmsg'] = $res[0][0];
        $_SESSION['Auth'] = $authority;
        header('Location: http:' . WEB_HOST . 'window_ctl_nk.php');
    } else {
    */
        //$_SESSION['s_sysmsg'] = $res[0][0];
        $_SESSION['Auth'] = $authority;
        // setcookie('ckUserid',$userid);       // ���ƥ��å����������ѹ���������ͽ��
        // setcookie('ckPasswd',$passwd);
        // setcookie('ckAuthority',$authority);
        // $_SESSION['template_ret'] = 'system_menu.php';  // ��ȯ�ѥƥ�ץ졼���Ѥ��ɲâ����ߤϻȤäƤ��ʤ�
        header('Location: http:' . WEB_HOST . 'window_ctl.php');
    /*
    }
    */
} else {
    // setcookie('ckUserid');               // ���ƥ��å����������ѹ���������ͽ��
    // setcookie('ckPasswd');
    // setcookie('ckAuthority');
    $_SESSION['s_sysmsg'] = 'ǧ�ڤ˼��Ԥ��ޤ��������ϥߥ��������ʤ��ξ�����Ͽ����Ƥ��ʤ���ǽ��������ޤ���' . 
            '���ξ��֤�³���褦�Ǥ��������ô���Ԥˤ��䤤��碌��������';
    header('Location: http:' . WEB_HOST . 'authenticate.php');
}
?>
