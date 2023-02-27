<?php
//////////////////////////////////////////////////////////////////////////////
// AS/400の組立工数をDBサーバーへ更新 ヘッダー・明細・マスターの３ファイル  //
//   登録工数の連携                                                         //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/07 Created  assembly_timeAllUpdate.php                           //
// 2007/05/16 ディレクトリ変更 daily/ → assembly_time/ ロジックでdir取得   //
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
$menu->set_title('AS/400→DBサーバー 組立 登録工数 更新処理実行');
//////////// リターンアドレス設定(絶対指定する場合)
$menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない

$Message  = "＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿\n\n";

/******** 組立工数 工程記号マスターの更新 *********/
$Message .= `{$currentFullPathName}/assembly_process_master.php`;
$Message .= "＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿\n\n";

/******** 組立工数 工程明細の更新 *********/
$Message .= `{$currentFullPathName}/assembly_standard_time.php`;
$Message .= "＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿\n\n";

/******** 組立工数 ヘッダーファイルの更新 *********/
$Message .= `{$currentFullPathName}/assembly_time_header.php`;
$Message .= "＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿\n";

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
    alert("<?php echo $Message ?>");
    location.replace("<?php echo SYS_MENU ?>");
}
</script>
<body   onLoad='
            resultMessage();
        '
</body>
<html>
