<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の ユーザー情報の変更 実行                                   //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/07/07 Created   chg_userinfo.php                                    //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
// 2003/12/05 view_userinfo_user.phpでgetObjectをコメントにしたため追加     //
//            getObjectAdd() として新規関数で追加                           //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2007/10/04 if($_POST['photo'])→if (isset($_POST['photo'])) へ変更       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // 共用 関数
require_once ('emp_function.php');          // 社員メニュー専用
access_log();                               // Script Name 自動設定

/* 画像データがある場合、そのオブジェクトIDを取得 */
$oid=0;
if ( isset($_POST['photo']) ) {
    if ( isset($_POST['photoid']) ) {
        delObject($_POST['photoid']);
    }
    $oid = addObject(INS_IMG . $_SESSION['User_ID']);
            /*** 2003/12/05 view_userinfo_user.php で getObject()をコメントにしたため 追加↓ ***/
    $file = IND . $_POST['userid'] . '.gif';
    getObjectAdd($oid, $file);
            /*** 2003/12/05 view_userinfo_user.php で getObject()をコメントにしたため 追加↑ ***/
    unlink(INS_IMG . $_SESSION['User_ID']);
}
/* トランザクションにて処理 */
$con = funcConnect();
if($con){
    execQuery("begin");
    /* USER_MASTER テーブルの更新 */
    $query="update user_master set";
    $query .=" aid=" . $_POST['authority'];
    $query .= " where uid='" . $_POST['userid'] . "'";
    if(!execQuery($query)){
        /* USER_DETAILES テーブルの更新 */
        $query = "update user_detailes set";
        $query .= " name='" . $_POST['name'] . "',kana='" . $_POST['kana'] . "',spell='" . $_POST['spell'] . "',pid=" . $_POST['position'] . ",";
        if($_POST['class'])
            $query .="class='" . $_POST['class'] . "',";
        else
            $query .="class=NULL,";
        $query .= "zipcode='" . $_POST['zipcode'] . "',address='" . $_POST['address'] . "',tel='" . $_POST['tel'] . "',birthday='" . $_POST['birthday'] . "',enterdate='" . $_POST['entrydate'] . "',";
        if($_POST['helthins_date'])
            $query .= "helthins_date='" . $_POST['helthins_date'] . "',";
        else
            $query .= "helthins_date=NULL,";
        if($_POST['helthins_no'])
            $query .= "helthins_no='" . $_POST['helthins_no'] . "',";
        else
            $query .= "helthins_no=NULL,";
        if($_POST['welperins_date'])
            $query .= "welperins_date='" . $_POST['welperins_date'] . "',";
        else
            $query .= "welperins_date=NULL,";
        if($_POST['welperins_no'])
            $query .= "welperins_no='" . $_POST['welperins_no'] . "',";
        else
            $query .="welperins_no=NULL,";
        if($_POST['unemploy_date'])
            $query .= "unemploy_date='" . $_POST['unemploy_date'] . "',";
        else
            $query .="unemploy_date=NULL,";
        if($_POST['unemploy_no'])
            $query .="unemploy_no='" . $_POST['unemploy_no'] . "',";
        else
            $query .="unemploy_no=NULL,";
        if($_POST['info'])
            $query .= "info='" . $_POST['info'] . "'";
        else
            $query .="info=NULL";
        if (isset($_POST['photo'])) // 2007/10/04 isset() ADD.
            $query .=",photo=$oid";
        $query .= " where uid='" . $_POST['userid'] . "'";

        if(!execQuery($query)){
            execQuery('commit');
            disConnectDB();
            // $lookupinfo="&lookupkind=$lookupkind&lookupkey=$lookupkey&lookupkeykind=$lookupkeykind&lookupsection=$lookupsection&lookupposition=$lookupposition&lookupentry=$lookupentry&lookupcapacity=$lookupcapacity&lookupreceive=$lookupreceive&retireflg=$retireflg";
            if($_SESSION['retireflg'] == 0){
                header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_LOOKUP);
                exit();
            }
            header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_RETIREINFO);
            exit();
        }
    }
    execQuery('rollback');
    disConnectDB();
}
// $sysmsg=urlencode("従業員の情報修正に失敗しました。管理者にお問い合わせください。");
// $lookupinfo="&lookupkind=$lookupkind&lookupkey=$lookupkey&lookupkeykind=$lookupkeykind&lookupsection=$lookupsection&lookupposition=$lookupposition&lookupentry=$lookupentry&lookupcapacity=$lookupcapacity&lookupreceive=$lookupreceive&retireflg=$retireflg";
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGUSERINFO . '&userid=' . $_POST['userid'] . '&inf=1');
?>
