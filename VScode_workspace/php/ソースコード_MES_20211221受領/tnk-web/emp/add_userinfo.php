<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� ���Ȱ�������Ͽ ��Ͽ�¹�                                   //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   add_userinfo.php                                    //
// 2002/08/07 register_globals = Off �б�                                   //
// 2002/08/19 �����С��ؤΥ�����桼������Ͽ�򥳥���                    //
// 2003/12/05 �����Ұ���Ͽ�������Υ�å������� yellow ��ɽ�������롣        //
// 2004/01/26 emp_function.php��addObject()�ǥ��֥������Ȥ���Ͽ�Ϥ��뤬view //
//            _userinfo_user.php��getObject()�򥳥��Ȥˤ������ᡢ������   //
//            addObject()�θ��getObjectAdd()���ɲä�file����¸����         //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2005/11/24 �ѥ���ɤ�Ź沽����Ͽ���� user_master                      //
// 2008/04/28 ��ư����ν����Ͽ���������������������ѹ�               ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // ���� �ؿ�
require_once ('emp_function.php');          // �Ұ���˥塼����
access_log();                               // Script Name ��ư����

// define("CMD_STR_ADD","/usr/bin/sudo /usr/sbin/newusers ");

/* �����ǡ����������硢���Υ��֥�������ID����� */
$oid=0;
if ( isset($_POST['photo']) ) {
    $oid = addObject(INS_IMG . $_SESSION['User_ID']);
            /*** 2003/12/05 view_userinfo_user.php �� getObject()�򥳥��Ȥˤ������� �ɲâ� ***/
    $file = IND . $_POST['userid'] . '.gif';
    getObjectAdd($oid, $file);
            /*** 2003/12/05 view_userinfo_user.php �� getObject()�򥳥��Ȥˤ������� �ɲâ� ***/
    unlink(INS_IMG . $_SESSION['User_ID']);
}
/* �ȥ�󥶥������ˤƽ��� */
$con = funcConnect();
if ($con) {
    execQuery('begin');
    /* USER_MASTER �ơ��֥����Ͽ */
    $query="insert into user_master values(";
    $query .="'" . $_POST['userid'] . "','" . md5($_POST['passwd']) . "','" . $_POST['mailaddr'] . "'," . $_POST['authority'];
    $query .=")";
    if(!execQuery($query)){
        /* USER_TRANSFER �ơ��֥����Ͽ */
        $tdate=date($_POST['entrydate']);
        $query="insert into user_transfer values(";
        $query .="'" . $_POST['userid'] . "','$tdate'," . $_POST['section'] . ",'" . $_POST['section_name'] . "'";
        $query .=")";
        if(!execQuery($query)){
            /* USER_DETAILES �ơ��֥����Ͽ */
            $query="insert into user_detailes values(";
            $query .="'" . $_POST['userid'] . "','" . $_POST['name'] . "','" . $_POST['kana'] . "','" . $_POST['spell'] . "'," . $_POST['section'] . "," . $_POST['position'] . ",";
            if($_POST['class'])
                $query .="'" . $_POST['class'] . "',";
            else
                $query .="NULL,";
            $query .="'" . $_POST['zipcode'] . "','" . $_POST['address'] . "','" . $_POST['tel'] . "','" . $_POST['birthday'] . "','" . $_POST['entrydate'] . "',NULL,NULL,";
            if($_POST['helthins_date'])
                $query .="'" . $_POST['helthins_date'] . "',";
            else
                $query .="NULL,";
            if($_POST['helthins_no'])
                $query .="'" . $_POST['helthins_no'] . "',";
            else
                $query .="NULL,";
            if($_POST['welperins_date'])
                $query .="'" . $_POST['welperins_date'] . "',";
            else
                $query .="NULL,";
            if($_POST['welperins_no'])
                $query .="'" . $_POST['welperins_no'] . "',";
            else
                $query .="NULL,";
            if($_POST['unemploy_date'])
                $query .="'" . $_POST['unemploy_date'] . "',";
            else
                $query .="NULL,";
            if($_POST['unemploy_no'])
                $query .="'" . $_POST['unemploy_no'] . "',";
            else
                $query .="NULL,";
            if($_POST['info'])
                $query .="'" . $_POST['info'] . "',";
            else
                $query .="NULL,";
            if($oid)
                $query .="$oid";
            else
                $query .="NULL";
            $query .=")";
            if(!execQuery($query)){
                execQuery('commit');
                disConnectDB();

/* add 09/27 begin */

//                  $file=TEMP_DIR . "user";
//                  $str=sprintf("%s:%s:::::\n",$_POST['acount'],$_POST['passwd']);
//                  if($fp=fopen($file,"w")){
//                          fwrite($fp,$str,strlen($str));
//                          fclose($fp);
//                          $cmd=escapeshellcmd(CMD_STR_ADD . $file);
//                          exec($cmd);
//                          unlink($file);
//                  }

/* end */

                    $_SESSION['s_sysmsg'] = "<font color='yellow'>���Ȱ��ο�����Ͽ��λ���ޤ�������<br>�Ұ��ֹ�=" . $_POST['userid'] . 
                        "��<br>�ѥ����=" . $_POST['passwd'] . "��<br>��̾=" . $_POST['name'] . 
                        "��<br>���������=" . $_POST['acount'] . "@" . WEB_DOMAIN . '</font>';
                    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_NEWUSER);
                    exit();
                }
            }
        }
        execQuery('rollback');
        disConnectDB();
    }
    // $sysmsg=urlencode("���Ȱ��ο�����Ͽ�˼��Ԥ��ޤ����������Ԥˤ��䤤��碌����������");
    $_SESSION['s_sysmsg'] = '���Ȱ��ο�����Ͽ�˼��Ԥ��ޤ����������Ԥˤ��䤤��碌����������';
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_NEWUSER);
?>
