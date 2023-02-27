<?php
//////////////////////////////////////////////////////////////////////////////
// AS/400の組立完成経歴をDBサーバーへ更新  更新のタイミングは１日単位       //
//   同日なら何回でも出来るように仕組みを作ってある                         //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/09 Created  assembly_completion_history.php                      //
// 2007/05/15 ディレクトリを daily/ → assembly_completion/ へ変更          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 120);          // 最大実行時間 = 120秒(２分) 
$currentFullPathName = realpath(dirname(__FILE__));
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(3);                  // 認証チェック3=administrator以上 戻り先=TOP_MENU タイトル未設定

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('組立 完成 経歴 AS/400→DBサーバー 更新処理実行');
//////////// リターンアドレス設定(絶対指定する場合)
$menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない

$Message  = "＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿\n\n";

/******** 組立完成経歴の更新 *********/
$Message .= `{$currentFullPathName}/assembly_completion_history_cli.php`;
$Message .= "＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿＿\n";
// $Message .= "------------------------------------------------------------------------\n";

///// alert()出力用にメッセージを変換
$Message = str_replace("\n", '\\n', $Message);  // "\n"に注意

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
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
