<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file �Ұ�����Ȳ�ξ������                       //
// Copyright (C) 2001-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   view_userinfo.php                                   //
// 2002/08/07 register_globals = Off �б�                                   //
// 2003/11/11 �����ǥ�������  include �� include_once ���ѹ�              //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2015/11/17 ������ѹ��ʤɤ�����ä��ݤˡ��������ä����Զ��������ΰ�    //
//            �����ƥ��ȡ��ѹ��ʤ���                                   ��ë //
//////////////////////////////////////////////////////////////////////////////
// access_log("view_userinfo.php");        // Script Name ��ư����
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

/* ����̾�����줾��˥��ڡ������ղ� */
if ($_SESSION["lookupkeykind"]==KIND_FULLNAME) {
    $key = "'" . $_SESSION["lookupkey"] . "'";
} elseif ($_SESSION["lookupkeykind"]==KIND_LASTNAME) {
    if (substr($_SESSION["lookupkey"],0,1)>="a" && substr($_SESSION["lookupkey"],0,1)<="z") {
        $key = "'%" . $_SESSION["lookupkey"] . "%'";
    } else {
        $key = "'" . $_SESSION["lookupkey"] . "%'";
    }
} elseif ($_SESSION["lookupkeykind"]==KIND_FASTNAME) {
    if (substr($_SESSION["lookupkey"],0,1)>="a" && substr($_SESSION["lookupkey"],0,1)<="z") {
        $key = "'" . $_SESSION["lookupkey"] . "%'";
    } else {
        $key = "'%" . $_SESSION["lookupkey"] . "%'";
    }
} else {
    $key = $_SESSION["lookupkey"];
}

switch ($_SESSION["lookupkind"]) {
case KIND_USER:
    include_once ("view_userinfo_user.php");
    break;
case KIND_ADDRESS:
    include_once ("view_userinfo_address.php");
    break;
case KIND_TRAINING:
    include_once ("view_userinfo_training.php");
    break;
}
?>
