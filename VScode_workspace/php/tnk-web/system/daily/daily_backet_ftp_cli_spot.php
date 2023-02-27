#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// バケマスデータ 自動FTP Download cronで処理用 cli版                       //
// AS/400 ----> Web Server (daily保管) バケマス用Webでは使用しない          //
// Copyright (C) 2016-2016 Norihisa.Ohya nirihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/09/15 Created  daily_backet_ftp_cli.php                              //
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
//部品マスターデータ

// 部品マスターデータ 日報処理 準備作業
$file_temp  = '/home/guest/daily/INP-C-BK.tmp';
$file_write = '/home/guest/daily/INP-C.CSV';

/////// 処理報告用 変数 初期化
$c     = 0;

// 前回のデータをバックアップ
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
///// 前回のデータを削除
if (file_exists($file_write)) {
    unlink($file_write);
}
fclose($fp);
fclose($fpw);
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
                            substr(mipn, 1, 1)='C' and substr(mipn, 1, 2)<>'CQ'
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
