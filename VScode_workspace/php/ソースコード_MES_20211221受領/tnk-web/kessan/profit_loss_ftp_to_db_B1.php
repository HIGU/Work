<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 自動FTP Download  科目別・部門経費 *** 明細 ***             //
// AS/400 ----> Web Server (PHP) TNKACT → 77 → 77 → 31 → 4              //
// 2003/01/31 Copyright(C) 2003-2004 K.Kobayashi tnksys@nitto-kohki.co.jp   //
// 変更経歴                                                                 //
// 2003/01/31 新規作成  profit_loss_ftp_to_db_B1.php                        //
//              データは B 表と同時期に作成される                           //
//              select sum(kin) from bm_km_summary where actcod=8103        //
//              and act_id<>900 and k_kubun='1' and                         //
//              (act_id=173 or act_id=174 or act_id=500)                    //
// 2003/02/28 データベースへの登録をトランザクションに変更                  //
// 2003/06/06 部門マスター等が変更された場合に既に登録されているか          //
//            登録されていないか１レコード毎にチェックするように変更        //
// 2004/02/05 成功時のメッセージ文字色を白へ変更  機種依存文字の�� → No へ //
//            現在は AS/400 の WCBMKMP に対象年月をチェックする項目はない   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name は自動取得
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
    header("Location: http:" . WEB_HOST . "kessan/kessan_menu.php");
    exit();
}

    ///// 対象年月の取得
$yyyymm = $_SESSION['pl_ym'];
    ///// 期の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);

    ///// AS/400 の ライブラリとファイル名設定
$as_lib_file = "UKFLIB/WCBMKMP";
    ///// Dounload File Name 設定
$file_orign = "WCBMKMP.TXT";
    ///// Dounload file 内容説明
$file_note  = "科目別部門経費B1";

    ///// ファイルの存在チェック
if (file_exists($file_orign)) {
    unlink($file_orign);    // ある場合は旧ファイルのため削除 FTP error 時に旧ファイルで更新しないため
}
// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect("10.1.1.252")) {
    if (ftp_login($ftp_stream,"FTPUSR","AS400FTP")) {
        if (ftp_get($ftp_stream, $file_orign, $as_lib_file, FTP_ASCII)) {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='white'>%d %sの DOWNLOAD 成功</font>", $yyyymm, $file_note);
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("%d %sの DOWNLOAD 失敗<br>ftp_get_error", $yyyymm, $file_note);
        }
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("%d %sの DOWNLOAD 失敗<br>ftp_login_error", $yyyymm, $file_note);
    }
    ftp_close($ftp_stream);
} else {
    $_SESSION['s_sysmsg'] .= sprintf("%d %sの DOWNLOAD 失敗<br>ftp_connect_error", $yyyymm, $file_note);
}

///// 月次損益 部門別・科目別 準備作業 FTP データを取得
if (file_exists($file_orign)) {           // ファイルの存在チェック
    $fp = fopen($file_orign,"r");
    $act_id   = array();    // 部門コード  3
    $actcod   = array();    // 科目コード  4
    $k_kubun  = array();    // 経費区分    1
    $div      = array();    // 事業部      1
    $kin      = array();    // 金額       11
    $rec      = 0;          // レコードNo
    while (!feof($fp)) {        // ファイルのEOFチェック
        $data = fgets($fp,100);   // 実際には21(LF含む) でOKだが余裕を持って
        $data = mb_convert_encoding($data, "EUC-JP", "auto");       // autoをEUC-JPへ変換
        $act_id[$rec]  = substr($data,0,3);         // 部門コード
        $actcod[$rec]  = substr($data,3,4);         // 科目コード
        $k_kubun[$rec] = substr($data,7,1);         // 経費区分(製造経費・販管費) '1'=製造経費 ' '=販管費
        $div[$rec]     = substr($data,8,1);         // 事業部 'C'=カプラ 'L'=リニア ' '=間接費 '9'=製造経費振替
        $kin[$rec]     = substr($data,9,11);        // 金額
        $rec++;
    }
    fclose($fp);
    $rec--;         // 最後のLF分を レコード削除

    /////////// begin トランザクション開始
    if ($con = db_connect()) {
        query_affected_trans($con, "begin");
    } else {
        $_SESSION["s_sysmsg"] .= "データベースに接続できません";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    ///////////////// データベースへの取り込み
    $ok_row = 0;        ///// 取り込み完了レコード数
    for ($i=0; $i < $rec; $i++) {       // 新規登録
        $query_chk = sprintf("select pl_bs_ym from bm_km_summary where pl_bs_ym=%d and act_id=%d and actcod=%d", $yyyymm, $act_id[$i], $actcod[$i]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = sprintf("insert into bm_km_summary (pl_bs_ym, ki, act_id, actcod, k_kubun, div, kin) 
                values(%d, %d, %d, %d, '%s', '%s', %d)",
                $yyyymm, $ki, $act_id[$i], $actcod[$i], $k_kubun[$i], $div[$i], $kin[$i]);
            if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
                $NG_row = ($i + 1);
                $_SESSION['s_sysmsg'] .= "<br>データベースの新規登録に失敗しました No$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else {
                $ok_row++;
            }
        } else {                                // UPDATE
            ///// 登録あり update 使用
            $query = sprintf("update bm_km_summary set pl_bs_ym=%d, ki=%d, act_id=%d, actcod=%d, k_kubun='%s', div='%s', kin=%d 
                where pl_bs_ym=%d and act_id=%d and actcod=%d", 
                $yyyymm, $ki, $act_id[$i], $actcod[$i], $k_kubun[$i], $div[$i], $kin[$i], 
                $yyyymm, $act_id[$i], $actcod[$i]);
            if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
                $NG_row = ($i + 1);
                $_SESSION['s_sysmsg'] .= "<br>データベースのUPDATEに失敗しました No$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else {
                $ok_row++;
            }
        }
    }
    $_SESSION['s_sysmsg'] .= sprintf("<br><font color='white'>%d %s %d 件 取り込み完了</font>", $yyyymm, $file_note, $ok_row);
    /////////// commit トランザクション終了
    query_affected_trans($con, "commit");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>月次損益 FTP Download </TITLE>
<style type="text/css">
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
</HEAD>
<BODY>
    <center>AS/400 と データリンク 完了</center>

    <script language="JavaScript">
    <!--
        location = 'http:<?php echo(WEB_HOST) . "kessan/profit_loss_select.php" ?>';
    // -->
    </script>
</BODY>
</HTML>
