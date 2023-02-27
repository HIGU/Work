<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の 従業員新規登録 登録実行                                   //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   add_userinfo.php                                    //
// 2002/08/07 register_globals = Off 対応                                   //
// 2002/08/19 サーバーへのローカルユーザー登録をコメント                    //
// 2003/12/05 新規社員登録成功時のメッセージを yellow で表示させる。        //
// 2004/01/26 emp_function.phpのaddObject()でオブジェクトの登録はするがview //
//            _userinfo_user.phpでgetObject()をコメントにしたため、ここで   //
//            addObject()の後にgetObjectAdd()を追加しfileに保存する         //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2005/11/24 パスワードを暗号化し登録する user_master                      //
// 2008/04/28 異動履歴の初期登録を入力日から入社日に変更               大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // 共用 関数
require_once ('emp_function.php');          // 社員メニュー専用
access_log();                               // Script Name 自動設定

// define("CMD_STR_ADD","/usr/bin/sudo /usr/sbin/newusers ");

/* 画像データがある場合、そのオブジェクトIDを取得 */
$oid=0;
if ( isset($_POST['photo']) ) {
    $oid = addObject(INS_IMG . $_SESSION['User_ID']);
            /*** 2003/12/05 view_userinfo_user.php で getObject()をコメントにしたため 追加↓ ***/
    $file = IND . $_POST['userid'] . '.gif';
    getObjectAdd($oid, $file);
            /*** 2003/12/05 view_userinfo_user.php で getObject()をコメントにしたため 追加↑ ***/
    unlink(INS_IMG . $_SESSION['User_ID']);
}
/* トランザクションにて処理 */
$con = funcConnect();
if ($con) {
    execQuery('begin');
    /* USER_MASTER テーブルに登録 */
    $query="insert into user_master values(";
    $query .="'" . $_POST['userid'] . "','" . md5($_POST['passwd']) . "','" . $_POST['mailaddr'] . "'," . $_POST['authority'];
    $query .=")";
    if(!execQuery($query)){
        /* USER_TRANSFER テーブルに登録 */
        $tdate=date($_POST['entrydate']);
        $query="insert into user_transfer values(";
        $query .="'" . $_POST['userid'] . "','$tdate'," . $_POST['section'] . ",'" . $_POST['section_name'] . "'";
        $query .=")";
        if(!execQuery($query)){
            /* USER_DETAILES テーブルに登録 */
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

                    $_SESSION['s_sysmsg'] = "<font color='yellow'>従業員の新規登録完了しました。　<br>社員番号=" . $_POST['userid'] . 
                        "　<br>パスワード=" . $_POST['passwd'] . "　<br>氏名=" . $_POST['name'] . 
                        "　<br>アカウント=" . $_POST['acount'] . "@" . WEB_DOMAIN . '</font>';
                    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_NEWUSER);
                    exit();
                }
            }
        }
        execQuery('rollback');
        disConnectDB();
    }
    // $sysmsg=urlencode("従業員の新規登録に失敗しました。管理者にお問い合わせください。");
    $_SESSION['s_sysmsg'] = '従業員の新規登録に失敗しました。管理者にお問い合わせください。';
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_NEWUSER);
?>
