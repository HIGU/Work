<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係のデータ 自動FTP Download  要素別買掛金明細表のデータ        //
// AS/400 ----> Web Server (PHP)  TNKACT → 02 → 26 → 37                  //
// 2003/01/17 Copyright(C) 2003-2004 K.Kobayashi tnksys@nitto-kohki.co.jp   //
// 変更経歴                                                                 //
// 2003/01/17 新規作成  profit_loss_ftp_to_db_E.php                         //
// 2003/01/24 データベースへの取り込みロジックを追加                        //
// 2003/01/27 データベースへの取り込みを小分けするためファイル名変更        //
//            ＣＬ要素別買掛用のデータ取り込み 表ID=E                       //
// 2003/01/28 データベースのフィールド追加 対象期(ki=3など)                 //
// 2003/02/28 データベースへの登録をトランザクションに変更                  //
// 2004/02/05 AS/400 の対象年月のチェック機能追加 kin9 != $yyyymm           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name は自動取得
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "あなたは許可されていません!<br>管理者に連絡して下さい!";
    header("Location: http:" . WEB_HOST . "kessan/kessan_menu.php");
    exit();
}

    ///// 対象年月の取得
$yyyymm = 202211
    ///// 期の取得
$ki = 22

    ///// AS/400 の ライブラリとファイル名設定
$as_lib_file = "UKFLIB/WCPLBSP";
    ///// Dounload File Name 設定
$file_orign = "WCPLBSP.TXT";
    ///// Dounload file 内容説明
$file_note  = "要素別買掛金 E";

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

///// 月次損益データ 準備作業 FTP データを取得
if(file_exists($file_orign)){           // ファイルの存在チェック
    $fp = fopen($file_orign,"r");
    $t_id     = array();   // 表ID   アルファベット 1
    $t_row    = array();   // 行№                  2
    $actcod = array();   // 科目コード            4
    $wplkn1 = array();   // 金額1                11
    $wplkn2 = array();   // 金額2                11
    $wplkn3 = array();   // 金額3                11
    $wplkn4 = array();   // 金額4                11
    $wplkn5 = array();   // 金額5                11
    $wplkn6 = array();   // 金額6                11
    $wplkn7 = array();   // 金額7                11
    $wplkn8 = array();   // 金額8                11
    $wplkn9 = array();   // 金額9                11
    $rec = 0;       // レコード№
    while(!feof($fp)){          // ファイルのEOFチェック
        $data=fgets($fp,200);   // 実際には120 でOKだが余裕を持って
        $data = mb_convert_encoding($data, "UTF-8", "auto");       // autoをEUC-JPへ変換
        $t_id[$rec]     = substr($data,0,1);        // 表ID
        if ($t_id[$rec] != 'E')     // 要素買掛データでなければ再読込
            continue;
        $t_row[$rec]  = substr($data,1,2);          // 行№
        $actcod[$rec] = substr($data,3,4);          // 科目コード
        $wplkn1[$rec] = substr($data,7,11)  ;       // 金額1
        $wplkn2[$rec] = substr($data,18,11) ;       // 金額2
        $wplkn3[$rec] = substr($data,29,11) ;       // 金額3
        $wplkn4[$rec] = substr($data,40,11) ;       // 金額4
        $wplkn5[$rec] = substr($data,51,11) ;       // 金額5
        $wplkn6[$rec] = substr($data,62,11) ;       // 金額6
        $wplkn7[$rec] = substr($data,73,11) ;       // 金額7
        $wplkn8[$rec] = substr($data,84,11) ;       // 金額8
        $wplkn9[$rec] = substr($data,95,11) ;       // 金額9
        $rec++;
    }
    fclose($fp);
    //////////// 対象年月のチェック
    if ($wplkn9[0] != $yyyymm) {
        $_SESSION['s_sysmsg'] .= "AS/400の年月が違います<br>{$t_id[0]}{$t_row[0]}：{$wplkn9[0]}";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    
    /////////// begin トランザクション開始
    if ($con = db_connect()) {
        query_affected_trans($con, "begin");
    } else {
        $_SESSION["s_sysmsg"] .= "データベースに接続できません";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    ///// データベースへの取り込み
    $ok_row = 0;        ///// 取り込み完了レコード数
    $res_chk = array();
    $query_chk = sprintf("select pl_bs_ym from pl_bs_summary where pl_bs_ym=%d and t_id='E'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {     ///// 既登録済みのチェック
        for($i=0;$i<$rec;$i++){
            $query = sprintf("insert into pl_bs_summary (pl_bs_ym,ki,t_id,t_row,actcod,kin1,kin2,kin3,kin4,kin5,kin6,kin7,kin8,kin9) 
                values(%d,%d,'%s',%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d)",
                $yyyymm, $ki, $t_id[$i], $t_row[$i], $actcod[$i], $wplkn1[$i], $wplkn2[$i], $wplkn3[$i], 
                $wplkn4[$i], $wplkn5[$i], $wplkn6[$i], $wplkn7[$i], $wplkn8[$i], $wplkn9[$i]);
            if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
                $NG_row = ($i + 1);
                $_SESSION['s_sysmsg'] .= "<br>データベースの新規登録に失敗しました №$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else
                $ok_row++;
        }
        /******** debug start
        $i = 85;
            //$query = sprintf("insert into pl_bs_summary (pl_bs_ym,t_id,t_row,actcod,kin1,kin2,kin3,kin4,kin5,kin6,kin7,kin8,kin9) 
                values(%d,'%s',%d,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d)",
                $yyyymm, $t_id[$i], $t_row[$i], $actcod[$i], $wplkn1[$i], $wplkn2[$i], $wplkn3[$i], 
                $wplkn4[$i], $wplkn5[$i], $wplkn6[$i], $wplkn7[$i], $wplkn8[$i], $wplkn9[$i]);
        $_SESSION['s_sysmsg'] .= $query;
        *********//// debug end
    } else {                  // UPDATE
        for($i=0;$i<$rec;$i++){
            $query = sprintf("update pl_bs_summary set pl_bs_ym=%d, ki=%d, t_id='%s', t_row=%d, actcod=%d, 
                kin1=%d, kin2=%d, kin3=%d, kin4=%d, kin5=%d, kin6=%d, kin7=%d, kin8=%d, kin9=%d 
                where pl_bs_ym=%d and t_id='%s' and t_row=%d", 
                $yyyymm, $ki, $t_id[$i], $t_row[$i], $actcod[$i], $wplkn1[$i], $wplkn2[$i], $wplkn3[$i], 
                $wplkn4[$i], $wplkn5[$i], $wplkn6[$i], $wplkn7[$i], $wplkn8[$i], $wplkn9[$i], 
                $yyyymm, $t_id[$i], $t_row[$i]);
            if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
                $NG_row = ($i + 1);
                $_SESSION['s_sysmsg'] .= "<br>データベースのUPDATEに失敗しました №$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            } else 
                $ok_row++;
        }
        /******* debug start
        $i = 1;
            //$query = sprintf("update pl_bs_summary set pl_bs_ym=%d, t_id='%s', t_row=%d, actcod=%d, 
                kin1=%d, kin2=%d, kin3=%d, kin4=%d, kin5=%d, kin6=%d, kin7=%d, kin8=%d, kin9=%d 
                where pl_bs_ym=%d and t_id='%s' and t_row=%d", 
                $yyyymm, $t_id[$i], $t_row[$i], $actcod[$i], $wplkn1[$i], $wplkn2[$i], $wplkn3[$i], 
                $wplkn4[$i], $wplkn5[$i], $wplkn6[$i], $wplkn7[$i], $wplkn8[$i], $wplkn9[$i], 
                $yyyymm, $t_id[$i], $t_row[$i]);
        $_SESSION['s_sysmsg'] .= $query;
        ********////// debug end
    }
    $_SESSION['s_sysmsg'] .= sprintf("<br>%d %s %d 件 取り込み完了", $yyyymm, $file_note, $ok_row);
    /////////// commit トランザクション終了
    query_affected_trans($con, "commit");
}

?>
<!DOCTYPE html>
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
