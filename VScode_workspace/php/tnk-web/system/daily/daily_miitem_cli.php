#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.9-cli -c php4.ini                               //
// #!/usr/local/bin/php-5.0.4-cli --- 5.1.6-cli までは半角カナがNG          //
// アイテムマスター日報(daily)処理  (system_daily.phpから分離)              //
// Copyright (C) 2004-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history  (system_daily.phpの旧履歴を残す)                        //
// 2003/06/04 miitem の last_date last_user 分を \t \N にして書込み追加     //
// 2003/06/20 miitem の psql から insert update Transaction 処理に変更      //
// 2003/11/28 miitem の$str_flgを\r検出時リセットする前の行の不具合対策     //
//            miitem の書込み失敗時に break する様に変更。大量エラー対応    //
//            miitem を2002/02/ 以前のデータを全てコンバートしなおした。    //
// 2003/12/22 miitem の部品名を半角カナのまま使用するように変更 中止        //
// 2004/01/13 MLで fgetcsv()の仕様が変わった事を知りmiitemにも上記を摘要    //
//                                                                          //
// 2004/10/15 Created   daily_miitem_cli.php                                //
// 2004/10/15 AS/400からPCIXを使用したFTP転送に切替えたため trim()を使用    //
// 2004/12/13 daily_cli.phpからrequire()で呼出していたのを``へ変更          //
//                  半角カナが取込めない不具合対策 バルブ ASSY → ASSY      //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli → php (内部は5.0.3RC2)に変更  //
// 2004/12/17 上記のカナ文字問題で php → php-4.3.9-cliに変更               //
// 2005/03/29 バックアップデータを作成するように変更 file_name_bak          //
// 2005/04/20 カナ文字問題がphp-5.0.4で解消されたphp-4.3.9→php-5.0.4に変更 //
// 2005/05/18 ログの書式を変更（明細にもログ日時を追加)                     //
// 2005/05/31 カナ文字問題がSAPIモジュール(apache)では修正されたがCLI版はNG //
//            php → php-4.3.9-cli へ再度変更 (但し、cronで実行時のみNG)    //
//            直接コマンドラインで実行する場合は OK (なぜ？)                //
// 2006/08/29 simplate.so をDSO module で取込んだため php4は -c php4.ini追加//
// 2006/09/04 文字化けの原因はfgetcsv()のLANG環境変数の設定である事が分かり //
//            cronの定義ファイル(as400get_ftp)にLANG=ja_JP,eucJPを追加し対応//
// 2007/01/22 AS側からFTP転送されたCSVの処理を手動からfgetcsv()へ変更       //
//            予期しない2"(インチ)で使用されてしまったためfgetcsvにまかせる //
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるためcli版)
    // 現在はCLI版のdefault='1', SAPI版のdefault='0'になっている。CLI版のみスクリプトから変更出来る。
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/var/www/html/function.php');
/////// 処理報告用 変数 初期化
$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$msg   = '';        // メッセージ
$flag1 = '';        // 処理実行フラグ 売上
$flag2 = '';        // 処理実行フラグ アイテム
$flag3 = '';        // 処理実行フラグ 製品仕掛
$flag4 = '';        // 処理実行フラグ 労務費・経費
$b     = 0;         // テキストファイルのレコード数
$c     = 0;
$d     = 0;
$e     = 0;

// アイテムマスター 日報処理 準備作業
// $file_name = '/home/www/html/weekly/Q#MIITEM.CSV';
$file_name  = '/home/guest/daily/Q#MIITEM.CSV';
$file_temp  = '/home/guest/daily/Q#MIITEM.tmp';
$file_write = '/home/guest/daily/Q#MIITEM.txt';
///// 前回のデータを削除
if (file_exists($file_write)) {
    unlink($file_write);
}
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name, 'r');
    $fpw = fopen($file_temp, 'a');
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
        $data = mb_convert_kana($data, 'KV', 'UTF-8'); // 半角カナを全角カナに変換 (DB保存時は全角で照会時は必要に応じて半角変換する)
        fwrite($fpw,$data);
        $c++;
        if (feof($fp)) {
            $c--;
            break;
        }
    }
    fclose($fp);
    fclose($fpw);
    $fp = fopen($file_temp, 'r');
    $fpw = fopen($file_write, 'a');
    while (FALSE !== ($data = fgetcsv($fp, 300, ',')) ) {    // CSV file として読込み
        if ($data[0] == '') continue;   // 空行の処理
        $data[1] = str_replace('"', '', $data[1]);  // なぜか？"の入る位置がズレるのと￥まで書込まれるので削除する
                                                    // 上記は下のpg_escape_string()以前の問題である
        $data[1] = pg_escape_string($data[1]);      // 品名
        $data[2] = pg_escape_string($data[2]);      // 材質
        $data[3] = pg_escape_string($data[3]);      // 親機種
        ///// data[0]部品番号とdata[4]登録日は業務のルール上エスケープする必要が無い
        fwrite($fpw,"{$data[0]}\t{$data[1]}\t{$data[2]}\t{$data[3]}\t{$data[4]}\n");
        ///// 文字列内(品名等)に","があった場合は fgetcsv()にまかせる。
    }
    fclose($fp);
    fclose($fpw);
    // unlink($file_name);     // 一時ファイルを削除 CSV
    // unlink($file_temp);     // 一時ファイルを削除 tmp
    if (file_exists("{$file_name}.bak")) {
        unlink("{$file_name}.bak");         // 前回のデータを削除
    }
    if (file_exists("{$file_temp}.bak")) {
        unlink("{$file_temp}.bak");         // 前回のデータを削除
    }
    if (!rename($file_name, "{$file_name}.bak")) {
        echo "$log_date DownLoad File $file_name をBackupできません！\n";
    }
    if (!rename($file_temp, "{$file_temp}.bak")) {
        echo "$log_date DownLoad File $file_temp をBackupできません！\n";
    }
    // exit(); // debug用
}

// アイテムマスター 日報処理
$file_name = '/home/guest/daily/Q#MIITEM.txt';
$file_name_bak = '/home/guest/daily/backup/Q#MIITEM-bak.txt';
if (file_exists($file_name)) {            // ファイルの存在チェック
    $rowcsv = 0;        // read file record number
    $row_in = 0;        // insert record number 成功した場合にカウントアップ
    $row_up = 0;        // update record number   〃
    $miitem_ng_flg = FALSE;      // ＤＢ書込みＮＧフラグ
    if ( ($fp = fopen($file_name, 'r')) ) {
        /////////// begin トランザクション開始
        if ( !($con = db_connect()) ) {
            $msg .= "データベースに接続できません\n";
        } else {
            query_affected_trans($con, 'begin');
            while ($data = fgetcsv($fp, 200, "\t")) {
                // $num = count($data);     // CSV File の field 数
                $rowcsv++;
                $data[1] = addslashes($data[1]);    // "'"等がデータにある場合に\でエスケープする
                $data[1] = trim($data[1]);          // 部品名の前後のスペースを削除 AS/400のPCIXを使用したFTP転送のため
                $data[2] = trim($data[2]);          // 材質名の前後のスペースを削除
                $data[3] = trim($data[3]);          // 親機種の前後のスペースを削除
                ///////// 登録済みのチェック
                $query_chk = sprintf("select mipn from miitem where mipn='%s'", $data[0]);
                if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                    ///// 登録なし insert 使用
                    $query = sprintf("insert into miitem (mipn, midsc, mzist, mepnt, madat)
                            values('%s','%s','%s','%s',%d)", $data[0],$data[1],$data[2],$data[3],$data[4]);
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        $msg .= "miitem insert error rec No.=$rowcsv \n";
                        $miitem_ng_flg = TRUE;
                        break;          // NG のため抜ける
                    } else {
                        $row_in++;      // insert 成功
                    }
                } else {
                    ///// 登録あり update 使用
                    $query = sprintf("update miitem set mipn='%s', midsc='%s', mzist='%s', mepnt='%s', madat=%d
                            where mipn='%s'", $data[0], $data[1], $data[2], $data[3], $data[4], $data[0]);
                    if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                        $msg .= "miitem update error rec No.=$rowcsv \n";
                        $miitem_ng_flg = TRUE;
                        break;          // NG のため抜ける
                    } else {
                        $row_up++;      // update 成功
                    }
                }
            }
        }
        /////////// commit トランザクション終了
        if ($miitem_ng_flg) {
            query_affected_trans($con, 'rollback');     // transaction rollback
        } else {
            query_affected_trans($con, 'commit');       // 書込み完了
        }
    } else {
        $msg .= "Q#MIITEM.txtをオープン出来ません\n";
    }
    /**********
      ///////////////////////////////////////////////// stderr(2)→をstdout(1)へ 2>&1
    $result2 = `/usr/local/pgsql/bin/psql TnkSQL < /home/www/html/weekly/qmiitem 2>&1`;
    unlink($file_name);
    ***********/
    fclose($fp);
    if (file_exists($file_name_bak)) unlink($file_name_bak);    // 前回のバックアップを削除
    if (!rename($file_name, $file_name_bak)) {                  // 今回のデータをバックアップ
        echo "$log_date {$file_name} をBackupできません！\n";
    }
    $flag2 = 1;
}


// メッセージを返す
if ($flag2==1) {
    $msg .= "{$log_date} アイテムマスター更新\n";
    $msg .= "{$log_date} insert $row_in 件\n";
    $msg .= "{$log_date} update $row_up 件\n";
    $msg .= "{$log_date} CSV_file $rowcsv 件\n";
    $msg .= "{$log_date} Original $c 件\n";
} else {
    $msg .= "{$log_date}:アイテムマスターの更新データがありません。\n";
}
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "日報(daily)処理\n");
fwrite($fpb, "/var/www/html/system/daily/daily_cli.php\n");
fwrite($fpb, "------------------------------------------------------------------------\n");
fwrite($fpb, "アイテムマスターの更新\n");
fwrite($fpb, "/var/www/html/system/daily/daily_miitem_cli.php\n");

fwrite($fpa, $msg);
fwrite($fpb, $msg);
echo "$msg";
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
exit();
?>
