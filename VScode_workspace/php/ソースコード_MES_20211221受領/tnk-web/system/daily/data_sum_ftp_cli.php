#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// DATA SUM 組立作業日報 自動FTP Download cronで処理用 cgi版                //
// data_sum.tnk.co.jp ----> Web Server (PHP)                                //
// Copyright (C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/06/09 Created  data_sum_ftp_cgi.php → data_sum_ftp_cli.php         //
// 2004/06/16 sleep(3) → sleep(6) へ変更 ポーリングで2〜3秒遅延されるため  //
// 2004/06/18 data_sum_log テーブルを AS/400のフィールドに仕方なく合わせた  //
// 2004/12/13 #!/usr/local/bin/php-4.3.8-cgi -q→ php(内部は5.0.3RC2)に変更 //
// 2005/03/29 前回データの削除を最上部で行っていた(debug)のを最下部へ変更   //
//            データが文字化けしてINSERTに失敗した場合にメール報告を追加    //
// 2005/04/06 mailのsubjectをデータサム→DATA SUM へ変更 文字コードの違いに //
//            よりノーツのルール(フィルター)にヒットしないため EUC-JP→JIS  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'on');            // echo print で flush させる(遅くなるがメールロジックのため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
$fpa = fopen('/tmp/data_sum.log', 'a');     // 日報用ログファイルへの書込みでオープン

// FTPのターゲットファイル
define('LOG_DATA', 'log.dat');              // データサムのログファイル download file
// 保存先のディレクトリとファイル名
define('S_FILE', '/home/www/html/tnk-web/system/backup/data_sum.log');  // save file

/*****************************
if (file_exists(S_FILE)) {
    unlink(S_FILE);                         // 前のデータがある場合は削除
}
*****************************/

// FTP関係の定義
define('SUM_HOST', '10.1.3.173');           // ターゲットホスト
define('SUM_USER', 'data_sum');             // 接続ユーザー名
define('SUM_PASS', 'data_sum');             // パスワード
define('SUM_STOP', 'stop.dat');             // データサムのコントロールファイル
define('LOCAL_NAME', '/home/www/html/tnk-web/system/daily/data_sum_ctl.dat');   // コントロールファイルのローカル名

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(SUM_HOST)) {
    if (ftp_login($ftp_stream, SUM_USER, SUM_PASS)) {
        ///// データサムへコントロールファイル(通信中断指示)を送信
        if (ftp_put($ftp_stream, SUM_STOP, LOCAL_NAME, FTP_ASCII)) {
            // echo SUM_STOP, " upload OK \n";
            // fwrite($fpa, "$log_date " . SUM_STOP . " upload OK \n");
            ///// 通信中断が反映されるまで３秒間待つ ポーリングで２秒〜３秒遅延するので６秒に変更
            sleep(6);
            ///// データサムのログファイル存在チェック
            if (ftp_size($ftp_stream, LOG_DATA) != (-1) ) { // ファイルが存在していれば
                ///// データサムのログファイル取得
                if (ftp_get($ftp_stream, S_FILE, LOG_DATA, FTP_ASCII)) {
                    // echo 'ftp_get download OK ', LOG_DATA, ' → ', S_FILE, "\n";
                    fwrite($fpa,"$log_date ftp_get download OK " . LOG_DATA . ' → ' . S_FILE . "\n");
                    ///// データサムのログファイル削除
                    if (ftp_delete($ftp_stream, LOG_DATA)) {
                        // echo LOG_DATA, ":delete OK \n";
                        fwrite($fpa,"$log_date " . LOG_DATA . ":delete OK \n");
                    } else {
                        // echo LOG_DATA, ":delete Error \n";
                        fwrite($fpa,"$log_date " . LOG_DATA . ":delete Error \n");
                    }
                } else {
                    // echo 'ftp_get() Error ', LOG_DATA, "\n";
                    fwrite($fpa,"$log_date ftp_get() Error " . LOG_DATA . "\n");
                }
            }
            ///// コントロールファイル削除
            if (ftp_delete($ftp_stream, SUM_STOP)) {
                // echo SUM_STOP, ":delete OK \n";
                // fwrite($fpa,"$log_date " . SUM_STOP . ":delete OK \n");
            } else {
                // echo SUM_STOP, ":delete Error \n";
                fwrite($fpa,"$log_date " . SUM_STOP . ":delete Error \n");
            }
        } else {
            // echo SUM_STOP, " upload Error \n";
            fwrite($fpa, "$log_date " . SUM_STOP . " upload error \n");
        }
    } else {
        // echo "ftp_login() Error \n";
        fwrite($fpa,"$log_date ftp_login() Error \n");
    }
    ftp_close($ftp_stream);
} else {
    // echo "ftp_connect() error --> DATA SUM\n";
    fwrite($fpa,"$log_date ftp_connect() error --> DATA SUM\n");
}



// データサムのログファイル 日報処理 準備作業
$file_orign  = S_FILE;
$file_nippo  = '/home/www/html/tnk-web/system/backup/data_sum_nippo.log';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_nippo, 'a+');    // 日報用ファイルのオープン
    chmod($file_nippo, 0666);           // テスト用に追加
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    /////////// DB コネクション取得
    $con = db_connect();
    while (!(feof($fp))) {
        $data = fgets($fp, 300);     // 実レコードは240バイトなのでちょっと余裕を
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $data = substr($data, 0, 238);      // 余分なデータをカットする
        $data = $data . "\n";               // LF を付加する
        if (fwrite($fpw, $data, 300)) {
            $rec_ok++;
        } else {
            $rec_ng++;
        }
        /*****************************************/
        ///// FTP転送したログファイルから DBへ登録
        $term_no = substr($data, 0, 2);                 // バーコード端末番号
                        // 例 '20040611 163000' を生成     作業日時(タイムスタンプ)
        $dsum_stamp = ( '20' . substr($data, 2, 6) . ' ' . substr($data, 8, 4) . '00' );
        $work_dsc = substr($data, 12, 3);               // 作業区(作業内容)
        // 初回の書込みはデータがあるためチェック無しで書込む
        $emp_id1  = substr($data, 15, 6);               // 社員番号 又は 作業者コード
        $emp_id2  = substr($data, 21, 6);
        if ($emp_id2 == 0) $emp_id2 = '';
        $emp_id3  = substr($data, 27, 6);
        if ($emp_id3 == 0) $emp_id3 = '';
        $emp_id4  = substr($data, 33, 6);
        if ($emp_id4 == 0) $emp_id4 = '';
        $emp_id5  = substr($data, 39, 6);
        if ($emp_id5 == 0) $emp_id5 = '';
        $emp_id6  = substr($data, 45, 6);
        if ($emp_id6 == 0) $emp_id6 = '';
        $emp_id7  = substr($data, 51, 6);
        if ($emp_id7 == 0) $emp_id7 = '';
        $emp_id8  = substr($data, 57, 6);
        if ($emp_id8 == 0) $emp_id8 = '';
        $emp_id9  = substr($data, 63, 6);
        if ($emp_id9 == 0) $emp_id9 = '';
        $emp_id10 = substr($data, 69, 6);
        if ($emp_id10 == 0) $emp_id10 = '';
        $emp_id11 = substr($data, 75, 6);
        if ($emp_id11 == 0) $emp_id11 = '';
        $emp_id12 = substr($data, 81, 6);
        if ($emp_id12 == 0) $emp_id12 = '';
        $emp_id13 = substr($data, 87, 6);
        if ($emp_id13 == 0) $emp_id13 = '';
        $emp_id14 = substr($data, 93, 6);
        if ($emp_id14 == 0) $emp_id14 = '';
        $emp_id15 = substr($data, 99, 6);
        if ($emp_id15 == 0) $emp_id15 = '';
        $plan_no1 = substr($data, 105, 8);              // 計画番号
        $work_qt1 = substr($data, 113, 5);              // 作業数 分納等の場合
        $plan_no2 = substr($data, 118, 8);
        $plan_no2 = trim($plan_no2);
        $work_qt2 = substr($data, 126, 5);
        $plan_no3 = substr($data, 131, 8);
        $plan_no3 = trim($plan_no3);
        $work_qt3 = substr($data, 139, 5);
        $plan_no4 = substr($data, 144, 8);
        $plan_no4 = trim($plan_no4);
        $work_qt4 = substr($data, 152, 5);
        $plan_no5 = substr($data, 157, 8);
        $plan_no5 = trim($plan_no5);
        $work_qt5 = substr($data, 165, 5);
        $plan_no6 = substr($data, 170, 8);
        $plan_no6 = trim($plan_no6);
        $work_qt6 = substr($data, 178, 5);
        $plan_no7 = substr($data, 183, 8);
        $plan_no7 = trim($plan_no7);
        $work_qt7 = substr($data, 191, 5);
        $plan_no8 = substr($data, 196, 8);
        $plan_no8 = trim($plan_no8);
        $work_qt8 = substr($data, 204, 5);
        $plan_no9 = substr($data, 209, 8);
        $plan_no9 = trim($plan_no9);
        $work_qt9 = substr($data, 217, 5);
        $plan_no10= substr($data, 222, 8);
        $plan_no10= trim($plan_no10);
        $work_qt10= substr($data, 230, 5);
        $cut_time = substr($data, 235, 3);              // カット時間
        $query = "
            INSERT INTO data_sum_log
                (term_no, dsum_stamp, work_dsc, emp_id1, emp_id2, emp_id3, emp_id4, emp_id5
                , emp_id6, emp_id7, emp_id8, emp_id9, emp_id10, emp_id11, emp_id12, emp_id13
                , emp_id14, emp_id15, plan_no1, work_qt1, plan_no2, work_qt2, plan_no3, work_qt3
                , plan_no4, work_qt4, plan_no5, work_qt5, plan_no6, work_qt6, plan_no7, work_qt7
                , plan_no8, work_qt8, plan_no9, work_qt9, plan_no10, work_qt10, cut_time)
            VALUES ({$term_no}, '{$dsum_stamp}', {$work_dsc}, '{$emp_id1}', '{$emp_id2}', '{$emp_id3}'
                    , '{$emp_id4}', '{$emp_id5}', '{$emp_id6}', '{$emp_id7}', '{$emp_id8}', '{$emp_id9}'
                    , '{$emp_id10}', '{$emp_id11}', '{$emp_id12}', '{$emp_id13}', '{$emp_id14}', '{$emp_id15}'
                    , '{$plan_no1}', {$work_qt1}, '{$plan_no2}', {$work_qt2}, '{$plan_no3}', {$work_qt3}
                    , '{$plan_no4}', {$work_qt4}, '{$plan_no5}', {$work_qt5}, '{$plan_no6}', {$work_qt6}
                    , '{$plan_no7}', {$work_qt7}, '{$plan_no8}', {$work_qt8}, '{$plan_no9}', {$work_qt9}
                    , '{$plan_no10}', {$work_qt10}, {$cut_time})
        ";
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            fwrite($fpa,"$log_date DB INSERT Error \n");
            `echo "クエリー：\n $query" > /tmp/data_sum_error.txt`;
            `/bin/cat /tmp/data_sum_error.txt | /usr/bin/nkf -Ej | /bin/mail -s 'DATA SUM のデータエラー 端末=$term_no 日時=$dsum_stamp' tnksys@nitto-kohki.co.jp , usoumu@nitto-kohki.co.jp `;
            `/bin/rm -f /tmp/data_sum_error.txt`;
        }
        /*****************************************/
    }
    unlink($file_orign);    // FTP転送したデータサムオリジナルのS_FILE data_sum.log を削除
    fclose($fp);
    fclose($fpw);
}

fclose($fpa);      ////// 日報用ログ書込み終了
?>
