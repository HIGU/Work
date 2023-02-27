<?php
//////////////////////////////////////////////////////////////////////////////
// タイムProの時間(出勤・退勤)DAYLY_MANU.TXTをデータベースへ手動更新CLI版   //
// Copyright (C) 2008      Norihisa.Ohya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2008/09/18 Created  timePro_update_cli.php(timePro_update_cli.php)       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 60);          // 最大実行時間 = 60秒 
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

$currentFullPathName = realpath(dirname(__FILE__));
///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(3);                  // 認証チェック3=administrator以上 戻り先=TOP_MENU タイトル未設定

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('タイムプロ→DBサーバー タイムプロデータ 更新処理実行');
//////////// リターンアドレス設定(絶対指定する場合)
$menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない

$log_date = date('Y-m-d H:i:s');        // 日報用ログの日時
$fpa = fopen('/tmp/timepro_manu.log', 'a');  // 日報用ログファイルへの書込みでオープン

/////////// begin トランザクション開始
if ($con = db_connect()) {
    // query_affected_trans($con, 'BEGIN');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    exit();
}
$file_orign  = '/home/guest/timepro/DAYLY_MANU.TXT';
$file_debug  = "{$currentFullPathName}/debug/debug-DAYLY-MANU.TXT";
$file_backup  = "{$currentFullPathName}/backup/backup-DAYLY-MANU.TXT";
///// 更新ファイルのタイムスタンプを取得
$save_file_time = "{$currentFullPathName}/timestamp_manu.txt";
if (file_exists($save_file_time)) {
    $fpt  = fopen($save_file_time, 'r');
    $timestamp = fgets($fpt, 50);
    fclose($fpt);
} else {
    $timestamp = '';
}
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $now = date('Ymd His', filemtime($file_orign));
    if ($now == $timestamp) {
        $log_date = date('Y-m-d H:i:s');
        $Message = "$log_date DAYLY_MANU.TXTが変更されていないため処理を中止します。\n";
        exit();
    } else {
        $fpt  = fopen($save_file_time, 'w');
        fwrite($fpt, $now);
        fclose($fpt);
    }
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $fpb = fopen($file_backup, 'w');     // backup 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $no_upd = 0;    // 未変更用カウンター
    while (!(feof($fp))) {
        $data = fgets($fp, 300);     // 実レコードは255バイトなのでちょっと余裕を
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $data = trim($data);       // 179～255のスペースを削除
        ///// バックアップへ書込み
        fwrite($fpb, "{$data}\n");
        if ($data == '') {
            $log_date = date('Y-m-d H:i:s');
            $Message = "$log_date 空行なので飛ばします。\n";
            continue;
        }
        ////////// データの存在チェック
        $query = "
            SELECT * FROM timepro_daily_data WHERE timepro_index(timepro) = timepro_index('{$data}')
        ";
        if (getUniResult($query, $res_chk) > 0) {
            if ($res_chk === $data) {   // ===に注意(型も合わせている)
                ///// データの変更が無い なにもしない
                $no_upd++;
            } else {
                ///// 変更あり update 使用
                $query = "
                    UPDATE timepro_daily_data SET timepro = '{$data}' WHERE timepro_index(timepro) = timepro_index('{$data}')
                ";
                if (query_affected($query) <= 0) {      // 更新用クエリーの実行
                    $log_date = date('Y-m-d H:i:s');
                    $Message = "$log_date {$rec}:レコード目のUPDATEに失敗しました!\n";
                    $rec_ng++;
                    ////////////////////////////////////////// Debug start
                    fwrite($fpw, "$query \n");              // debug
                    break;                                  // debug
                    ////////////////////////////////////////// Debug end
                } else {
                    $rec_ok++;
                    $upd_ok++;
                }
            }
        } else {    //////// 新規登録
            $query = "
                INSERT INTO timepro_daily_data VALUES ('{$data}')
            ";
            if (query_affected($query) <= 0) {
                $log_date = date('Y-m-d H:i:s');
                $Message = "$log_date {$rec}:レコード目のINSERTに失敗しました!\n";
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $ins_ok++;
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fclose($fpb);       // backup
    $log_date = date('Y-m-d H:i:s');
    $Message = "$log_date TimeProデータ更新 : {$rec_ok}/{$rec} 件登録しました。\n";
    $Message .= "$log_date TimeProデータ更新 : {$ins_ok}/{$rec} 件 追加 \n";
    $Message .= "$log_date TimeProデータ更新 : {$upd_ok}/{$rec} 件 変更 \n";
    $Message .= "$log_date TimeProデータ更新 : {$no_upd}/{$rec} 件 未変更 \n";
} else {
    $log_date = date('Y-m-d H:i:s');
    $Message = "$log_date ファイル$file_orign がありません!\n";
}
/////////// commit トランザクション終了
// query_affected_trans($con, 'COMMIT');
// echo $query . "\n";  // debug

///// alert()出力用にメッセージを変換
$Message = str_replace("\n", '\\n', $Message);  // "\n"に注意

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<script type='text/javascript'>
function resultMessage()
{
    
    location.replace("<?php echo SYS_MENU ?>");
    alert("<?php echo $Message ?>");
    
}
</script>
<body   onLoad='
            resultMessage();
        '
</body>
<html>
