#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 日報データ 自動FTP Download  cron で処理用       コマンドライン版        //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2002-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed histoy                                                           //
// 2002/03/11 Created  sales_get_ftp.php   (旧uriage_ftp.php)               //
// 2002/11/28 テスト版で debug 済 のため正式にリリース                      //
// 2003/05/30 組立日程計画データの日報処理を追加                            //
// 2003/06/06 AS/400のTIPPLNP等のトランザクションファイルはキー無しの物理   //
//             ファイルを頭から順番に読込書込しないといけない重複レコードが //
//             あるため最新に保てない。                                     //
// 2003/06/20 W#MIITEMをFTP_BINARYでDownloadしたが半角カナ(EBCDIC)の変換が要//
// 2003/11/14 php → php-4.3.4-cgi へ変更(明確にcgiを使うことが分かるように)//
// 2003/11/17 cgi → cli版へ変更出来るように requier_once を絶対指定へ      //
// 2004/04/21 FTPのターゲットとローカルファイルをdefine()で統一しbackup/ へ //
// 2004/04/30 FTP項目 売上未検収データを追加 FTP Download のみ              //
// 2004/06/07 php-4.3.6-cgi -q → php-4.3.7-cgi -q  バージョンアップに伴う  //
// 2004/11/18 php-5.0.2-cliへバージョンアップ *シェルスクリプトに対応に変更 //
//            MIITEMは別プログラムで処理しているためロジックを削除          //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli → php (内部は5.0.3RC2)に変更  //
// 2009/12/18 日報データ再取得用ログファイルへの書込みを追加           大谷 //
// 2010/01/19 メールを分かりやすくする為に、日付・リンク先等を追加     大谷 //
// 2010/01/20 $log_dateの前後は'では無く"なので修正                    大谷 //
// 2010/10/14 数量がマイナスの場合は、Webプログラムではなく                 //
//            ASの元データを直接修正しないと、データがうまく取れない   大谷 //
// 2018/09/07 20180901以降、事業部Lでdatatype=5(移動)で部品番号の先頭が     //
//            'T'の場合、事業部Tに変更                                      //
//            事業部Cも一部あるがとりあえずは事業部Lの移動のみ         大谷 //
// 2019/11/28 20191128以降、事業部Lでdatatype=7(売上)で部品番号の先頭が     //
//            'T'の場合、事業部Tに変更（ベルトン）対応                 大谷 //
// 2020/02/04 ベルトン対応で事業部Cも他のデータは無かったので               //
//            事業部を確認せず部品番号で事業部Tに変更する。                 //
//            合わせて、試験修理、商管に関してもそれぞれの事業部へ          //
//            強制的に変更                                             大谷 //
// 2021/05/31 2021/04からTに変更する部分をコメント化、また事業部Tで         //
//            来てしまったものは事業部Lに変更                          大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "売上データの更新(前日分)・売上未検収データ\n");
fwrite($fpb, "/var/www/html/system/daily/sales_get_ftp.php\n");
echo "/var/www/html/system/daily/sales_get_ftp.php\n";

// FTPのターゲットファイル
define('HIUURA', 'UKWLIB/W#HIUURA');        // 売上ファイル
///// define('MIITEM', 'UKWLIB/W#MIITEM');        // アイテムマスター
define('TIUKSL', 'UKWLIB/W#TIUKSL');        // 売上TRファイル
// 保存先のディレクトリとファイル名
define('W_HIUURI', '/var/www/html/system/backup/W#HIUURI.TXT');  // 売上
///// define('W_MIITEM', 'backup/W#MIITEM.TXT');  // アイテム
define('W_TIUKSL', '/var/www/html/system/backup/W#TIUKSL.TXT');  // 売上TRのDownloadファイル
// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        /*** 売上日報データ ***/
        if (ftp_get($ftp_stream, W_HIUURI, HIUURA, FTP_ASCII)) {
            echo "$log_date 売上データ ftp_get download OK ", HIUURA, "→", W_HIUURI, "\n";
            fwrite($fpa,"$log_date 売上データ ftp_get download OK " . HIUURA . '→' . W_HIUURI . "\n");
            fwrite($fpb,"$log_date 売上データ ftp_get download OK " . HIUURA . '→' . W_HIUURI . "\n");
        } else {
            echo "$log_date 売上データ ftp_get() error ", HIUURA, "\n";
            fwrite($fpa,"$log_date 売上データ ftp_get() error " . HIUURA . "\n");
            fwrite($fpb,"$log_date 売上データ ftp_get() error " . HIUURA . "\n");
        }
        /*** 部品・製品アイテムマスター ***/
        /*****************************************
        if (ftp_get($ftp_stream, W_MIITEM, MIITEM, FTP_ASCII)) {
            echo 'ftp_get download OK ', MIITEM, '→', W_MIITEM, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . MIITEM . "→" . W_MIITEM . "\n");
        } else {
            echo 'ftp_get() error ', MIITEM, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . MIITEM . "\n");
        }
        *****************************************/
        /*** 売上未検収データ ***/
        if (ftp_get($ftp_stream, W_TIUKSL, TIUKSL, FTP_ASCII)) {   // FTP_ASCII と FTP_BINARY のテスト
            echo "$log_date 売上未検収データ ftp_get download OK ", TIUKSL, "→", W_TIUKSL, "\n";
            fwrite($fpa,"$log_date 売上未検収データ ftp_get download OK " . TIUKSL . '→' . W_TIUKSL . "\n");
            fwrite($fpb,"$log_date 売上未検収データ ftp_get download OK " . TIUKSL . '→' . W_TIUKSL . "\n");
        } else {
            echo "$log_date 売上未検収データ ftp_get() error ", TIUKSL, "\n";
            fwrite($fpa,"$log_date 売上未検収データ ftp_get() error " . TIUKSL . "\n");
            fwrite($fpb,"$log_date 売上未検収データ ftp_get() error " . TIUKSL . "\n");
        }
    } else {
        echo "$log_date ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
        fwrite($fpb,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date ftp_connect() error --> 売上・アイテム\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 売上・アイテム\n");
    fwrite($fpb,"$log_date ftp_connect() error --> 売上・アイテム\n");
}

// 売上 日報処理 準備作業
$file_orign = W_HIUURI;
// $file_test  = "hiuuri.txt";
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign,"r");
    // $fpw = fopen($file_test,"w");        // TEST 用ファイルのオープン
    $div    = array();
    $date_s = array();
    $date_k = array();
    $assyno = array();
    $sei_no = array();
    $planno = array();
    $seizou = array();
    $tyumon = array();
    $hakkou = array();
    $nyuuko = array();
    $kan_no = array();
    $den_no = array();
    $suryou = array();
    $tanka1 = array();
    $tanka2 = array();
    $tokusa = array();
    $datatp = array();
    $tokuis = array();
    $bikou  = array();
    $kubun  = array();
    $rec = 0;       // レコード№
    while (1) {
        $data=fgets($fp,120);
        $data = mb_convert_encoding($data, "UTF-8", "auto");       // autoをEUC-JPへ変換
        // $data_KV = mb_convert_kana($data);           // 半角カナを全角カナに変換
        // fwrite($fpw,$data_KV);
        if (feof($fp)) {
            break;
        }
        $div[$rec]    = substr($data,0,1);      // 事業部
        $date_s[$rec] = substr($data,1,8);      // 処理日
        $date_k[$rec] = substr($data,9,8);      // 計上日
        $assyno[$rec] = substr($data,17,9);     // 部品・製品№
        $sei_no[$rec] = substr($data,26,9);     // 製品コード
        $planno[$rec] = substr($data,35,8);     // 計画№
        $seizou[$rec] = substr($data,43,7);     // 製造№
        $tyumon[$rec] = substr($data,50,7);     // 注文№
        $hakkou[$rec] = substr($data,57,7);     // 発行№
        $nyuuko[$rec] = substr($data,64,2);     // 入庫場所
        $kan_no[$rec] = substr($data,66,5);     // 組立完了№
        $den_no[$rec] = substr($data,71,6);     // 伝票№
        $suryou[$rec] = substr($data,77,6);     // 数量
        $tanka1[$rec]  = substr($data,83,7);    // 単価(整数部)
        $tanka2[$rec]  = substr($data,90,2);    // 単価(小数部)
        $tokusa[$rec] = substr($data,92,3);     // 特採率
        $datatp[$rec] = substr($data,95,1);     // datatype
        $tokuis[$rec] = substr($data,96,5);     // 得意先
        $bikou[$rec] = substr($data,101,15);    // 備考
        $kubun[$rec] = substr($data,116,1);     // 日報区分
        // 事業部がLでdatatypeが5で部品番号の先頭が'T'の時、事業部をTに変更
        /*
        if ($date_k[$rec]>=20180901) {
            if ($datatp[$rec]=='5') {
                if ($div[$rec]=='L') {
                    if (substr($assyno[$rec],0,1)=='T') {
                        $div[$rec] = 'T';
                    }
                }
            }
        }
        */
        // datatypeが7で部品番号の先頭が'T'の時、事業部をTに変更（ベルトン）
        /*
        if ($date_k[$rec]>=20191128) {
            if ($datatp[$rec]=='7') {
                //if ($div[$rec]=='L' || $div[$rec]=='C') {
                    if (substr($assyno[$rec],0,1)=='T') {
                        $div[$rec] = 'T';
                    }
                //}
            }
        }
        */
        // 機工対応（機工で来てしまったものはとりあえずLに移行）
        if ($date_k[$rec]>=20210401) {
            if ($div[$rec] == 'T') {
                $div[$rec] =  'L';
            }
        }
        // 試験修理対応 datatypeが7で部品番号の先頭が'SS'の時、事業部をLに変更
        if ($date_k[$rec]>=20191128) {
            if ($datatp[$rec]=='7') {
                if (substr($assyno[$rec],0,2)=='SS') {
                    $div[$rec] = 'L';
                }
            }
        }
        // 商管対応 datatypeが7で部品番号の先頭が'NKB'の時、事業部をCに変更
        if ($date_k[$rec]>=20191128) {
            if ($datatp[$rec]=='7') {
                if (substr($assyno[$rec],0,3)=='NKB') {
                    $div[$rec] = 'C';
                }
            }
        }
    /* テスト用にファイルに落とす
        fwrite($fpw,$div[$rec]    . "\n");
        fwrite($fpw,$date_s[$rec] . "\n");
        fwrite($fpw,$date_k[$rec] . "\n");
        fwrite($fpw,$assyno[$rec] . "\n");
        fwrite($fpw,$sei_no[$rec] . "\n");
        fwrite($fpw,$planno[$rec] . "\n");
        fwrite($fpw,$seizou[$rec] . "\n");
        fwrite($fpw,$tyumon[$rec] . "\n");
        fwrite($fpw,$hakkou[$rec] . "\n");
        fwrite($fpw,$nyuuko[$rec] . "\n");
        fwrite($fpw,$kan_no[$rec] . "\n");
        fwrite($fpw,$den_no[$rec] . "\n");
        fwrite($fpw,$suryou[$rec] . "\n");
        fwrite($fpw,$tanka1[$rec]  . ".");
        fwrite($fpw,$tanka2[$rec]  . "\n");
        fwrite($fpw,$tokusa[$rec] . "\n");
        fwrite($fpw,$datatp[$rec] . "\n");
        fwrite($fpw,$tokuis[$rec] . "\n");
        fwrite($fpw,$bikou[$rec]  . "\n");
        fwrite($fpw,$kubun[$rec]  . "\n");
            テスト用 END */
        $rec++;
    }
    fclose($fp);
    // fclose($fpw);
}

if ($rec >= 1) { // レコード数のチェック
    $res_chk = array();
    $query_chk = "select 計上日 from hiuuri where 計上日=" . $date_k[0];
    if (getResult($query_chk,$res_chk)<=0) {
        for ($i=0; $i<$rec; $i++) {
            $query = "insert into hiuuri values('";
            $query .= $div[$i] . "',";
            $query .= $date_s[$i] . ",";
            $query .= $date_k[$i] . ",'";
            $query .= $assyno[$i] . "','";
            $query .= $sei_no[$i] . "','";
            $query .= $planno[$i] . "',";
            $query .= $seizou[$i] . ",";
            $query .= $tyumon[$i] . ",";
            $query .= $hakkou[$i] . ",'";
            $query .= $nyuuko[$i] . "',";
            $query .= $kan_no[$i] . ",'";
            $query .= $den_no[$i] . "',";
            $query .= $suryou[$i] . ",";
            $query .= $tanka1[$i] . "."; // 小数点に注意
            $query .= $tanka2[$i] . ",";
            $query .= $tokusa[$i] . ",'";
            $query .= $datatp[$i] . "','";
            $query .= $tokuis[$i] . "','";
            $query .= $bikou[$i] . "','";
            $query .= $kubun[$i] . "')";
            if (query_affected($query) <= 0) {     // 更新用クエリーの実行
                fwrite($fpa,"$log_date 売上 計上日:".$date_k[$i].": ".($i+1).":レコード目の書込みに失敗しました!\n");
                fwrite($fpb,"$log_date 売上 計上日:".$date_k[$i].": ".($i+1).":レコード目の書込みに失敗しました!\n");
                echo "$log_date 売上 計上日:", ($i+1), ":レコード目の書込みに失敗しました!\n";
            }
//          $res_add = array();
//          $rows = getResult($query,$res_add);    // 旧タイプのクエリーfunction
        }
        fwrite($fpa,"$log_date 売上 計上日:" . $date_k[0] . ": " . $rec . " 件登録しました。\n");
        fwrite($fpb,"$log_date 売上 計上日:" . $date_k[0] . ": " . $rec . " 件登録しました。\n");
        echo "$log_date 売上 計上日:", $rec, " 件登録しました。\n";
    } else {
        fwrite($fpa,"$log_date 売上 計上日:" . $date_k[0] . " 既に登録されています!\n");
        fwrite($fpb,"$log_date 売上 計上日:" . $date_k[0] . " 既に登録されています!\n");
        echo "$log_date 売上 計上日:", $date_k[0], " 既に登録されています!\n";
    }
} else {
    fwrite($fpa,"$log_date 売上データ レコードがありません!\n");
    fwrite($fpb,"$log_date 売上データ レコードがありません!\n");
    echo "$log_date 売上データ レコードがありません!\n";
}
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
