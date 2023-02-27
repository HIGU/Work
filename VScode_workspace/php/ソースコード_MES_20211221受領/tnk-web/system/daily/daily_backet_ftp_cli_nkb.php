#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// 商管バケマスデータ 自動FTP Download cronで処理用 cli版                   //
// AS/400 ----> Web Server (daily保管) バケマス用Webでは使用しない          //
// Copyright (C) 2017-2017 Norihisa.Ohya nirihisa_ooya@nitto-kohki.co.jp    //
// \FTPTNK    USER(AS400) ASFILE(W#MSTANA) LIB(UKWLIB)                      //
//            PCFILE(W#MSTANA.CSV) MODE(CSV)                                //
// Changed history                                                          //
// 2017/11/17 Created  daily_backet_ftp_cli_nkb.php                         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "商管バケットマスター用部品更新\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_backet_ftp_cli_nkb.php\n");
echo "/home/www/html/tnk-web/system/daily/sales_get_ftp_nkb.php\n";

/*
//出庫指示データ

// FTPのターゲットファイル
$target_file = 'UKWLIB/W#MSTANA';        // download file
// 保存先のディレクトリとファイル名
$save_file = '/home/guest/daily/W#MSTANA.TXT';        // save file
$back_file = '/home/guest/daily/W#MSTANA-BK.tmp';     // backup file

// 前回のデータをバックアップ
if (file_exists($save_file)) {
    $fp  = fopen($save_file, 'r');
    $fpw = fopen($back_file, 'a');
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
if (file_exists($save_file)) {
    unlink($save_file);
}

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            echo "$log_date 商管バケマス部品データ ftp_get download OK ", $target_file, "→", $save_file, "\n";
            fwrite($fpa,"$log_date 商管バケマス部品データ ftp_get download OK " . $target_file . '→' . $save_file . "\n");
            fwrite($fpb,"$log_date 商管バケマス部品データ ftp_get download OK " . $target_file . '→' . $save_file . "\n");
        } else {
            echo "$log_date 商管バケマス部品データ ftp_get() error ", $target_file, "\n";
            fwrite($fpa,"$log_date 商管バケマス部品データ ftp_get() error " . $target_file . "\n");
            fwrite($fpb,"$log_date 商管バケマス部品データ ftp_get() error " . $target_file . "\n");
        }
    } else {
        echo "$log_date 商管バケマス部品データ ftp_login() error \n";
        fwrite($fpa,"$log_date 商管バケマス部品データ ftp_login() error \n");
        fwrite($fpb,"$log_date 商管バケマス部品データ ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    echo "$log_date 商管バケマス部品データ ftp_connect() error --> MICCC\n";
    fwrite($fpa,"$log_date 商管バケマス部品データ ftp_connect() error --> MICCC\n");
    fwrite($fpb,"$log_date 商管バケマス部品データ ftp_connect() error --> MICCC\n");
}

*/
function calcJanCodeDigit($num) { 
    $arr = str_split($num); 
    $odd = 0; 
    $mod = 0; 
    for($i=0;$i<count($arr);$i++){ 
        if(($i+1) % 2 == 0) { 
            //偶数の総和 
            $mod += intval($arr[$i]); 
        } else { 
            //奇数の総和 
            $odd += intval($arr[$i]);                
        } 
    } 
    //偶数の和を3倍+奇数の総和を加算して、下1桁の数字を10から引く 
    $cd = 10 - intval(substr((string)($mod * 3) + $odd,-1)); 
    //10なら1の位は0なので、0を返す。 
    return $cd === 10 ? 0 : $cd; 
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
$e     = 0;

$file_name  = '/home/guest/daily/W#MSTANA.CSV';
$file_temp  = '/home/guest/daily/W#MSTANA.tmp';
$file_write = '/home/guest/daily/INPNKB.CSV';

///// 前回のデータを削除
if (file_exists($file_write)) {
    unlink($file_write);
}
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name, 'r');
    $fpw = fopen($file_temp, 'a');
    while (1) {
        $data=fgets($fp,200);
        $data = mb_convert_encoding($data, 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
        $data = mb_convert_kana($data, 'KV', 'EUC-JP'); // 半角カナを全角カナに変換 (DB保存時は全角で照会時は必要に応じて半角変換する)
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
        $data[2] = str_replace('"', '', $data[2]);  // なぜか？"の入る位置がズレるのと￥まで書込まれるので削除する
                                                    // 上記は下のpg_escape_string()以前の問題である
        $data[2] = str_replace(',', '', $data[2]);
        $data[2] = trim($data[2]);
        $data[2] = pg_escape_string($data[2]);      // 品名
        $data[2] = mb_convert_encoding($data[2], 'SJIS', 'EUC-JP');   // EUC-JPをSJISへ変換
        $jan_check = calcJanCodeDigit($data[1]);
        $data[1] = $data[1] . $jan_check;
        ///// data[0]部品番号とdata[4]登録日は業務のルール上エスケープする必要が無い
        //fwrite($fpw,"{$data[0]}\t{$data[1]}\t{$data[2]}\n");
        $blank = "";
        fwrite($fpw,"{$data[1]},{$blank},{$blank},{$data[2]},{$data[0]}\r\n");
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

/*
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
$file_name  = '/home/guest/daily/W#MSTANA.TXT';
$file_temp  = '/home/guest/daily/W#MSTANA.tmp';
$file_write = '/home/guest/daily/INPNKB.CSV';
$file_back  = '/home/guest/daily/INPNKB-BK.tmp';

// 前回のデータをバックアップ
if (file_exists($file_write)) {
    $fp  = fopen($file_write, 'r');
    $fpw = fopen($file_back, 'a');
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
}
if (file_exists($file_name)) {            // ファイルの存在チェック
    $fp = fopen($file_name, 'r');
    $fpw = fopen($file_temp, 'a');
    while (1) {
        $data=fgets($fp,300);
        $data = mb_convert_encoding($data, 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
        $data = mb_convert_kana($data, 'KV', 'EUC-JP'); // 半角カナを全角カナに変換 (DB保存時は全角で照会時は必要に応じて半角変換する)
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
        //$first += 1;                    // １行目判別用
        //if ($first == 1) continue;      // １行目は無視
        $data[1] = substr($data[0], 0,9);      // 部品番号
        $data[2] = substr($data[0], 9,12);     // JANコード
        $query_csv = sprintf("select
                            mipn                          as 在庫コード,    -- 0
                            trim(midsc)                   as 品名           -- 1
                        from
                            miitem
                        where
                            mipn='{$data[1]}'
                    ");
        $res       = array();
        $res_csv   = array();
        $field_csv = array();
        if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res)) <= 0) {
            //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s〜%s</font>", format_date($d_start), format_date($d_end) );
            //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on');    // 直前の呼出元へ戻る
            //exit();
        } else {
            $res_csv[0][0] = $data[2];
            $res_csv[0][1] = "";
            $res_csv[0][2] = "";
            $res_csv[0][3] = mb_convert_encoding($res[0][1], 'SJIS', 'EUC-JP');   // EUC-JPをSJISへ変換
            $res_csv[0][4] = $data[1];
            fwrite($fpw,"{$res_csv[0][0]},{$res_csv[0][1]},{$res_csv[0][2]},{$res_csv[0][3]},{$res_csv[0][4]}\r\n");
            echo "$log_date バケマス部品マスターデータ 出力 OK Web miitem →", $file_write, "\n";
            fwrite($fpa,"$log_date バケマス部品マスターデータ 出力 OK Web miitem →" . $file_write . "\n");
            fwrite($fpb,"$log_date バケマス部品マスターデータ 出力 Web miitem →" . $file_write . "\n");
        }
        ///// data[0]部品番号とdata[4]登録日は業務のルール上エスケープする必要が無い
        //fwrite($fpw,"{$data[1]},{$data[2]},{$data[3]},{$data[4]}\r\n");
        //fwrite($fpw,"{$data[1]},{$data[2]}\r\n");
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

*/
/*

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
    //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s〜%s</font>", format_date($d_start), format_date($d_end) );
    //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on');    // 直前の呼出元へ戻る
    exit();
} else {
    for ($r=0; $r<$rows_csv; $r++) {    // データをCSVに出力
        $res_csv[$r][3] = mb_convert_encoding($res_csv[$r][3], 'SJIS', 'EUC-JP');   // EUC-JPをSJISへ変換
        fwrite($fpw,"{$res_csv[$r][0]},{$res_csv[$r][1]},{$res_csv[$r][2]},{$res_csv[$r][3]}\r\n");
    }
    echo "$log_date バケマス部品マスターデータ 出力 OK Web miitem →", $file_write, "\n";
    fwrite($fpa,"$log_date バケマス部品マスターデータ 出力 OK Web miitem →" . $file_write . "\n");
    fwrite($fpb,"$log_date バケマス部品マスターデータ 出力 Web miitem →" . $file_write . "\n");
}
fclose($fpw);

*/
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
