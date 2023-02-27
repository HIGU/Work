<?php
//////////////////////////////////////////////////////////////////////////////
// プログラム開発依頼書 照会 条件選択                                       //
// Copyright(C) 2002-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2002/02/12 新規作成 dev_req_select.php                                   //
// 2002/08/09 register_globals = Off 対応                                   //
// 2002/08/27 フレーム対応                                                  //
// 2003/07/22 Opne Source Logo を非表示                                     //
// 2003/12/12 defineされた定数でディレクトリとメニュー名を使用する          //
// 2004/07/17 MenuHeader()クラスを新規作成しデザイン・認証等のロジック統一  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
// ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');       // TNK 全共通 function
require_once ('../MenuHeader.php');     // TNK 全共通 menu class
access_log();                           // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();               // 認証チェックも行っている

////////////// サイト設定
$menu->set_site(4, 1);                  // site_index=4(プログラム開発) site_id=1(依頼書の照会)
////////////// カレントスクリプトのアドレス設定
// $menu->set_self($_SERVER['PHP_SELF']);
////////////// リターンアドレス設定
// $menu->set_RetUrl(DEV_MENU);            // return addressを呼出元で設定へ変更
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('プログラム開発依頼書の照会');
//////////// 表題の設定
$menu->set_caption('開発依頼書（条件指定）');

$dev_req_No      = @$_SESSION['s_dev_req_No'];          // 受付番号
$dev_req_sdate   = @$_SESSION['s_dev_req_sdate'];       // 開始日
$dev_req_edate   = @$_SESSION['s_dev_req_edate'];       // 終了日
$dev_req_section = @$_SESSION['s_dev_req_section'];     // 依頼部署
$dev_req_client  = @$_SESSION['s_dev_req_client'];      // 依頼者
$dev_req_sort    = @$_SESSION['s_dev_req_sort'];        // ソート条件
$dev_req_kan     = @$_SESSION['s_dev_req_kan'];         // 完了条件

if ($dev_req_sort == '') {
    $dev_req_sort = '依頼日';               // 初期値の指定
}
if ($dev_req_kan == '') {
    $dev_req_kan = '全て';                  //初期値の指定
}

$_SESSION['s_rec_No'] = 0;  // 表示用レコードNoを0にする。

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<script language='JavaScript' src='./dev_req.js'>
</script>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table border='0'>
            <form action='edit_dev_req.php' method='post' onSubmit='return chk_dev_req_input(this)'>
                <table border='0'>
                    <tr><td><p><img src='<?php echo IMG ?>t_nitto_logo3.gif' width='348' height='83'></p></td></tr>
                </table>
                <table border='0' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td align='center' class='caption_font'>
                            <?= $menu->out_caption() , "\n" ?>
                        </td>
                    </tr>
                    <tr>
                        <td align='center'>
                            <img src='<?php echo IMG ?>tnk-turbine_small.gif'>
                        </td>
                    </tr>
                </table>
                <table cellspacing='0' cellpadding='3' border='1' bordercolor='#003e7c'>
                    <tr>
                        <th>1</th>
                        <td align='left' nowrap>
                            並び順を選択して下さい。<br>
                            <input type='radio' name='dev_req_sort' value='依頼日'
                                <?php if($dev_req_sort=='依頼日') echo('checked') ?>>依頼日順
                            <input type='radio' name='dev_req_sort' value='依頼部署'
                                <?php if($dev_req_sort=='依頼部署') echo('checked') ?>>依頼部署順
                            <input type='radio' name='dev_req_sort' value='依頼者'
                                <?php if($dev_req_sort=='依頼者') echo('checked') ?>>依頼者順
                            <input type='radio' name='dev_req_sort' value='完了日'
                                <?php if($dev_req_sort=='完了日') echo('checked') ?>>完了日順
                            <input type='radio' name='dev_req_sort' value='開発工数'
                                <?php if($dev_req_sort=='開発工数') echo('checked') ?>>開発工数順
                            <!--<br>-->
                            <input type='radio' name='dev_req_sort' value='番号'
                                <?php if($dev_req_sort=='番号') echo('checked') ?>>受付番号順
                        </td>
                    </tr>
                    <tr>
                        <th>2</th>
                        <td align='left'>
                            依頼者の社員No指定
                            <input type='text' name='dev_req_client' size='7' value='<?php echo($dev_req_client); ?>' maxlength='6'>
                            空白＝全て
                        </td>
                    </tr>
                    <tr>
                        <th>3</th>
                        <td align='left'>
                            依頼日の範囲指定
                            <input type='text' name='dev_req_sdate' size='9' value='<?php echo($dev_req_sdate); ?>' maxlength='8'>
                            〜
                            <input type='text' name='dev_req_edate' size='9' value='<?php echo($dev_req_edate); ?>' maxlength='8'>
                            空白＝全て
                        </td>
                    </tr>
                    <tr>
                        <th>4</th>
                        <td align='left'>
                            依頼No(受付No)指定
                            <input type='text' name='dev_req_No' size='5' value='<?php echo($dev_req_No); ?>' maxlength='5'>
                            空白＝全て
                        </td>
                    </tr>
                    <tr>
                        <th>5</th>
                        <td align='left' nowrap>
                            完了区分を選択して下さい。<br>
                            <input type='radio' name='dev_req_kan' value='全て'
                                <?php if($dev_req_kan=='全て') echo('checked') ?>>全て対象
                            <input type='radio' name='dev_req_kan' value='未完了'
                                <?php if($dev_req_kan=='未完了') echo('checked') ?>>未完了分
                            <input type='radio' name='dev_req_kan' value='完了'
                                <?php if($dev_req_kan=='完了') echo('checked') ?>>完了分
                            <input type='radio' name='dev_req_kan' value='保留他'
                                <?php if($dev_req_kan=='保留他') echo('checked') ?>>保留その他分
                        </td>
                    </tr>
                </table>
                <div align='center'><input type='submit' name='view_dev_req' value='実行' ></div>
            </form>
        </table>
    </center>
</body>
</html>
