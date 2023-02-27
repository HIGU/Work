<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 社員情報照会の条件選択                       //
// Copyright (C) 2001-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   view_userinfo.php                                   //
// 2002/08/07 register_globals = Off 対応                                   //
// 2003/11/11 コーディング統一  include → include_once へ変更              //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2015/11/17 情報の変更などから戻った際に、検索が消える不具合を訂正の為    //
//            色々テスト（変更なし）                                   大谷 //
//////////////////////////////////////////////////////////////////////////////
// access_log("view_userinfo.php");        // Script Name 手動設定
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

/* 姓と名、それぞれにスペースを付加 */
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
