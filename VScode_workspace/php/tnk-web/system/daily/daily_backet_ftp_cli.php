#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// バケマスデータ 自動FTP Download cronで処理用 cli版                       //
// AS/400 ----> Web Server (daily保管) バケマス用Webでは使用しない          //
// Copyright (C) 2016-2016 Norihisa.Ohya nirihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/09/15 Created  daily_backet_ftp_cli.php                             //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "バケットマスター用部品・出庫指示更新\n");
fwrite($fpb, "/var/www/html/system/daily/daily_backet_ftp_cli.php\n");
echo "/var/www/html/system/daily/sales_get_ftp.php\n";

//出庫指示データ

// FTPのターゲットファイル
$target_file = 'FTPLIB/THMSSJP';        // download file
// 保存先のディレクトリとファイル名
$save_file = '/home/guest/daily/THMSSJP.TXT';     // save file

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            echo "$log_date バケマス出庫指示データ ftp_get download OK ", $target_file, "→", $save_file, "\n";
            fwrite($fpa,"$log_date バケマス出庫指示データ ftp_get download OK " . $target_file . '→' . $save_file . "\n");
            fwrite($fpb,"$log_date バケマス出庫指示データ ftp_get download OK " . $target_file . '→' . $save_file . "\n");
        } else {
            echo "$log_date バケマス出庫指示データ ftp_get() error ", $target_file, "\n";
            fwrite($fpa,"$log_date バケマス出庫指示データ ftp_get() error " . $target_file . "\n");
            fwrite($fpb,"$log_date バケマス出庫指示データ ftp_get() error " . $target_file . "\n");
        }
    } else {
        echo "$log_date バケマス出庫指示データ ftp_login() error \n";
        fwrite($fpa,"$log_date バケマス出庫指示データ ftp_login() error \n");
        fwrite($fpb,"$log_date バケマス出庫指示データ ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date バケマス出庫指示データ ftp_connect() error --> MICCC\n";
    fwrite($fpa,"$log_date バケマス出庫指示データ ftp_connect() error --> MICCC\n");
    fwrite($fpb,"$log_date バケマス出庫指示データ ftp_connect() error --> MICCC\n");
}

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

$first = 0;

// 出庫指示データ 日報処理 準備作業
$file_name  = '/home/guest/daily/THMSSJP.TXT';
$file_temp  = '/home/guest/daily/THMSSJP.tmp';
$file_write = '/home/guest/daily/YOTEI.TXT';
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
        $first += 1;                    // １行目判別用
        if ($first == 1) continue;      // １行目は無視
        $data[1] = substr($data[0], 21,9);      // 在庫コード
        $data[2] = 'O';      // 作業区分
        $data[3] = substr($data[0], 32,5) * 1;      // 出庫数
        $data[4] = substr($data[0], 13,8);      // 組立指示No
        ///// data[0]部品番号とdata[4]登録日は業務のルール上エスケープする必要が無い
        fwrite($fpw,"{$data[1]},{$data[2]},{$data[3]},{$data[4]}\r\n");
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
}

//部品マスターデータ

// 部品マスターデータ 日報処理 準備作業
$file_temp  = '/home/guest/daily/INP-BK.tmp';
$file_write = '/home/guest/daily/INP.CSV';

/////// 処理報告用 変数 初期化
$c     = 0;

// 前回のデータをバックアップ
if (file_exists($file_write)) {
    $fp  = fopen($file_write, 'r');
    $fpw = fopen($file_temp, 'a');
    while (1) {
        $data=fgets($fp,300);
        fwrite($fpw,$data);
        $c++;
        if (feof($fp)) {
            $c--;
            break;
        }
    }
}
///// 前回のデータを削除
if (file_exists($file_write)) {
    unlink($file_write);
    fclose($fp);
    fclose($fpw);
}
$fpw = fopen($file_write, 'a');
//////////// CSV出力用のデータ出力
$query_csv = sprintf("select
                            mipn                          as 在庫コード,    -- 0
                            to_char(last_date,'yyyymmdd') as 登録日,        -- 1
                            ''                            as 登録時間,      -- 2
                            trim(midsc)                   as 品名           -- 3
                        from
                            miitem
                        where
                            to_char(last_date,'yyyy-mm-dd')>current_date-3
                    ");
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s～%s</font>", format_date($d_start), format_date($d_end) );
    //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on');    // 直前の呼出元へ戻る
    exit();
} else {
    for ($r=0; $r<$rows_csv; $r++) {    // データをCSVに出力
        $res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'UTF-8');   // EUC-JPをSJISへ変換
        fwrite($fpw,"{$res_csv[$r][0]},{$res_csv[$r][1]},{$res_csv[$r][2]},{$res_csv[$r][3]}\r\n");
    }
    echo "$log_date バケマス部品マスターデータ 出力 OK Web miitem →", $file_write, "\n";
    fwrite($fpa,"$log_date バケマス部品マスターデータ 出力 OK Web miitem →" . $file_write . "\n");
    fwrite($fpb,"$log_date バケマス部品マスターデータ 出力 Web miitem →" . $file_write . "\n");
}
fclose($fpw);

fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
