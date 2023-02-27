<?php
//////////////////////////////////////////////////////////////////////////////
// 月次 比較棚卸表のデータ取込み ロジック部 include file                    //
// Copyright(C) 2003-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/09/29 Created  invent_comp_get.php (Inventory comparable)           //
//            使用テーブル：act_comp_invent_history  miitemを雛型にした     //
// 2004/02/05 ディレクトリを/home/www/html/monthly→/home/guest/monthly へ  //
// 2005/03/10 CSVファイルをSJIS〜EUC-JPへ変換すると文字化けする対策 Jプラ   //
//            VerUPまでの暫定処置とする   ついでにaccess_log()を変更        //
// 2007/10/10 システムメッセージに改行を挿入                                //
//////////////////////////////////////////////////////////////////////////////
/***** include file のため以下をコメントアウト *****/
// ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
// ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分 CLI CGI版
// ob_start("ob_gzhandler");               // 出力バッファをgzip圧縮
// session_start();                        // ini_set()の次に指定すること Script 最上行

access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
// access_log('invent_comp_get.php');      // Script Name を手動設定
// $_SESSION["site_index"] = 99;        // 最後のメニューにするため 99 を使用
// $_SESSION["site_id"] = 10;           // 下位メニュー無し (0 < であり)

//////////////// 認証チェック
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION["Auth"] <= 2) {
    $_SESSION['s_sysmsg'] = "認証されていないか認証期限が切れました。ログインからお願いします。";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}

/////// inventoryYYYYMM.csv ファイルから比較棚卸データの取込み
// $file_name = "/home/www/html/monthly/inventory" . $yyyymm . ".csv";    // 読込ファイルSJIS
$file_name = "/home/guest/monthly/inventory" . $yyyymm . ".csv";    // 読込ファイルSJIS
if (file_exists($file_name)) {            // ファイルの存在チェック
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number 成功した場合にカウントアップ
    $row_up = 0;        // update record number   〃
    if ( ($fp = fopen($file_name, "r")) ) {
        /////////// begin トランザクション開始
        if ( !($con = db_connect()) ) {
            $_SESSION['s_sysmsg'] = "データベースに接続できません";
            return FALSE;
        } else {
            query_affected_trans($con, "begin");
            while ($data = fgetcsv($fp, 100, "\t")) {
                if ( ($num=count($data)) != 2) {        // CSV File の field 数チェック
                    $_SESSION['s_sysmsg'] = "CSVファイルのfield数が2個でない！:$num";
                    return FALSE;
                }
                $data[0] = mb_convert_encoding($data[0], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
                $data[1] = mb_convert_encoding($data[1], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
                // 暫定処置
                $data[0] = str_replace('J', 'カ',   $data[0]);
                $data[0] = str_replace('j', 'リニ', $data[0]);
                $rowcsv++;
                ///////// 登録済みのチェック
                $query_chk = sprintf("select item from act_comp_invent_history where invent_ym=%d and item='%s'", $yyyymm, $data[0]);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                    ///// 登録なし insert 使用
                    $query = "insert into act_comp_invent_history (invent_ym, item, kin)
                            values({$yyyymm}, '{$data[0]}', {$data[1]})
                    ";
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        query_affected_trans($con, "rollback");         // transaction rollback
                        $_SESSION['s_sysmsg'] = "比較棚卸表 insert error rec No.=$rowcsv";
                        return FALSE;
                    } else {
                        $row_in++;      // insert 成功
                    }
                } else {
                    ///// 登録あり update 使用
                    $query = sprintf("update act_comp_invent_history set invent_ym=%d, item='%s', kin=%d
                            where invent_ym=%d and item='%s'", $yyyymm, $data[0], $data[1], $yyyymm, $data[0]);
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        query_affected_trans($con, "rollback");         // transaction rollback
                        $_SESSION['s_sysmsg'] = "比較棚卸表 update error rec No.=$rowcsv";
                        return FALSE;
                    } else {
                        $row_up++;      // update 成功
                    }
                }
            }
        }
        query_affected_trans($con, "commit");       // 書込み完了
    } else {
        $_SESSION['s_sysmsg'] = "指定年月:$yyyymm のファイルがオープン出来ません";
        return FALSE;
    }
    fclose($fp);
    unlink($file_name);     // 一時ファイルを削除 txt
} else {
    $_SESSION['s_sysmsg'] = "指定年月:$yyyymm のファイルがありません";
    return FALSE;
}
$_SESSION['s_sysmsg'] = "<font color='yellow'>CSV file = $rowcsv 件<br>\\n\\nInsert file = $row_in 件<br>\\n\\nUpdate file = $row_up 件<br></font>\\n\\n";
return TRUE;    // 大文字小文字に依存しないが JavaScriptに合わせる。

?>

