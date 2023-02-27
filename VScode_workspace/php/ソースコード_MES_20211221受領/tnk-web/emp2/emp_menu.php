<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理(人事関係) & ＩＳＯ教育/資格経歴                             //
// Copyright (C) 2001-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
//                                      2001/10/15 all rights reserved.     //
// Changed history                                                          //
// 2001/10/15 Created   emp_menu.php                                        //
// 2002/04/23 資格と教育のleft表示を全角24文字までに制限(substr使用)        //
//            capacity_master(資格)  receive_master(教育)                   //
// 2002/08/07 セッション管理を追加 & register_globals = Off 対応            //
// 2002/08/27 フレーム 対応                                                 //
// 2003/01/31 左側の検索メニューのデザイン変更                              //
// 2003/02/14 売上関係ニュー のフォントを style で指定に変更                //
//                              ブラウザーによる変更が出来ない様にした      //
// 2003/04/02 検索条件の所属に出向を除く全てを追加                          //
// 2003/04/23 統計情報ボタンを追加(従業員の平均年齢・直間比率等)            //
// 2003/12/05 mb_substr(trim($res[$i]['section_name']), -10)へ変更          //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加  /confirm.js→/emp/confirm.js //
// 2004/02/13 index1.php→index.phpへ変更(index1はauthenticateに変更のため) //
// 2004/06/10 上記を H_WEB_HOST . EMP_MENU へ変更                           //
// 2005/01/17 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
//            left menu の bgcolor='#003e7c'→'#7777bb'へ変更したが中止     //
// 2005/01/26 background-imageを追加してデザイン変更(AM/PMで切替式)         //
// 2005/02/21 <hr>→<hr style='border-color:white;'>NN7.1対応 検索ボタンに色//
// 2005/11/15 メールアドレス編集メニューを追加 FUNC_MAIL=22 mailAddress_Main//
// 2006/01/12 デフォルトの表示を社員統計情報へ変更                          //
// 2007/02/07 FUNC_RECIDREGISTCHK(一括登録確認画面)の追加 大谷              //
// 2007/02/09 FUNC_CAPIDREGISTCHK(一括登録確認画面)の追加 大谷              //
// 2007/07/24 データベース処理を削除 view_admindb.php FUNC_DBADMIN          //
// 2007/08/30 phpのショートカットタグを標準タグへ変更(推奨タグへ)           //
// 2007/09/11 ControllerHTTP_Class使用によりE_ALL対応(このスクリプトのみ)   //
// 2008/09/22 FUNC_WORKINGHOURS(就業週報照会)の追加                    大谷 //
// 2008/09/25 就業週報の照会をサイドメニューに移動                     大谷 //
// 2010/03/11 暫定的に大渕さん（970268）が登録できるように変更         大谷 //
// 2014/07/29 不在者照会ができるようメニューに追加(大谷、工場長限定)   大谷 //
//         ※アマノ側から受入出向者のデータが来ていない(要確認2014/07/29)   //
// 2015/01/30 退職者以降の設定項目をアドミンのみに変更                 大谷 //
// 2015/03/27 有給検索項目追加                                         大谷 //
// 2015/06/18 計画有給の登録を追加                                     大谷 //
// 2015/06/19 計画有給の登録画面の表示に保志さんと野澤さんを追加       大谷 //
// 2015/06/22 権限エラーを修正                                         大谷 //
// 2015/06/30 不在者の照会をgetCheckAuthority(54)で制御に変更          大谷 //
// 2015/07/30 検索方法に年齢順(高い順)職位に社員全て・パート全てを追加 大谷 //
// 2019/01/31 暫定的に平石さん（300551）が登録できるように変更         大谷 //
// 2019/09/13 有給管理台帳の追加                                       大谷 //
// 2019/09/17 有給管理台帳の年度入力チェックをjavaに                   大谷 //
// 2020/03/10 職位に課長代理以上を追加                                 大谷 //
//            有給管理台帳の所属に出向を追加（こっちで直の方がPGM的に）大谷 //
// 2020/04/01 人事異動による権限の変更                                 大谷 //
// 2020/05/22 有給検索の条件に製造部権限55が抜けていたので追加         大谷 //
//////////////////////////////////////////////////////////////////////////////
// E_STRICT=2048(php5), E_ALL=2047(php4まで), E_ALL | E_STRICT=8191(最高レベルログ出力)
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set('error_reporting', E_ALL & ~E_NOTICE | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
// ob_start();  //Warning: Cannot add header の対策のため追加。2002/01/21
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮

require_once ('../function.php');           // 共用 関数
require_once ('emp_function.php');          // 社員メニュー専用
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
require_once ('../ControllerHTTP_Class.php');   // TNK 全共通 MVC Controller Class
// access_log();                            // include fileで使用するため、ここでは指定しない

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site( 3, 99);                    // site_index=3(社員メニュー) site_id=99(サイトメニューを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(MENU);                 // 通常は指定する必要はない(MENU=トップメニュー)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('社 員 メニュー');
//////////// 表題の設定
$menu->set_caption('社員情報管理(ISO)システム');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   EMP . 'emp_menu.php');
//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('empTarget');

///// リクエストクラスのインスタンスを生成
$request = new Request();

/////////////// ユーザーデータのセッション変数登録
if ($request->get('lookupkind') != '') {
    $_SESSION['lookupkind']       = $request->get('lookupkind');
    $_SESSION['lookupkey']        = $request->get('lookupkey');
    $_SESSION['lookupkeykind']    = $request->get('lookupkeykind');
    $_SESSION['lookupsection']    = $request->get('lookupsection');
    $_SESSION['lookupposition']   = $request->get('lookupposition');
    $_SESSION['lookupentry']      = $request->get('lookupentry');
    $_SESSION['lookupcapacity']   = $request->get('lookupcapacity');
    $_SESSION['lookupreceive']    = $request->get('lookupreceive');
    $_SESSION['retireflg']        = $request->get('retireflg');
    $_SESSION['lookupyukyu']      = $request->get('lookupyukyu');
    $_SESSION['lookupyukyukind']  = $request->get('lookupyukyukind');
    $_SESSION['lookupyukyuf']     = $request->get('lookupyukyuf');
    $_SESSION['lookupyukyufive']  = $request->get('lookupyukyufive');
    $_SESSION['yukyulist']        = $request->get('yukyulist');
    $_SESSION['fivesection']      = $request->get('fivesection');
}

switch ($request->get('func')) {      // 2
case FUNC_NEWUSER;
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "権限のない処理です！<br>遊ばないで下さい！";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_DBADMIN;  // 4
    if ($_SESSION['Auth'] < AUTH_LEBEL3) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "権限のない処理です！<br>遊ばないで下さい！";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_CHGUSERINFO;  // 6
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "権限のない処理です！<br>遊ばないで下さい！";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_CONFUSERINFO; // 7
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "権限のない処理です！<br>遊ばないで下さい！";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_ADMINUSERINFO;    // 8
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "権限のない処理です！<br>遊ばないで下さい！";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_CHGRECEIVE;   // 10
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "権限のない処理です！<br>遊ばないで下さい！";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_CHGCAPACITY;  // 11
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "権限のない処理です！<br>遊ばないで下さい！";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_ADDPHOLYDAY;  // 15
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970227' && $_SESSION['User_ID'] != '015806') {
            $_SESSION["s_sysmsg"] = "権限のない処理です！<br>遊ばないで下さい！";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_HOLYDAYREGIST;  // 16
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970227' && $_SESSION['User_ID'] != '015806') {
            $_SESSION["s_sysmsg"] = "権限のない処理です！<br>遊ばないで下さい！";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
case FUNC_RECIDREGIST;  // 12
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '970227' && $_SESSION['User_ID'] != '015806' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "権限のない処理です！<br>遊ばないで下さい！";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_CAPIDREGIST;  // 13
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "権限のない処理です！<br>遊ばないで下さい！";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
case FUNC_CHGINDICATE;  // 14
    if ($_SESSION['Auth'] < AUTH_LEBEL2) {
        if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
            $_SESSION["s_sysmsg"] = "権限のない処理です！<br>遊ばないで下さい！";
            header('Location: ' . H_WEB_HOST . EMP_MENU);
            exit();
        }
    }
    break;
}

/*  $file = IND_IMG . $ckUserid . ".gif";
if(file_exists($file))
    unlink($file); */

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv='expires' content='0'>
<meta http-equiv='Pragma' content='no-cache'>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title()?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<style type='text/css'>
<!--
.listScroll{
    /*テーブルの横幅が250px以上の場合スクロール*/
    //width: 100%;
    /*テーブルの縦幅が100px以上の場合スクロール*/
    height: 100%;
    /*縦スクロール*/
    overflow: auto;
    /*横スクロール*/
    overflow-x: hidden;
}
.listScroll2{
    /*テーブルの横幅が250px以上の場合スクロール*/
    /*
    width: 90vw;
    max-width: 600px;
    */
    width: 100%;
    /*テーブルの縦幅が100px以上の場合スクロール*/
    height: 100%;
    /*縦スクロール*/
    overflow: auto;
    /*横スクロール*/
    overflow-x: auto;
    width: calc(100vw - 100px);
}
.vertical-scroll-table{
    color: #5e5e5e;
    max-height: 120px;
    overflow: auto;
    overflow-x: hidden;
}
.pt9 {
    font-size: 10pt;
    /*font-family: monospace;*/
    /*color: black;*/
    }
.left-font {
    font-size: 7.5pt;
    font-family: monospace;
    color: blue;
    }
.left-font-bla {
    font-size: 7.5pt;
    font-family: monospace;
    color: black;
    }
.left-font-m {
    font-size: 9pt;
    font-family: monospace;
    color: blue;
    }
.left-font-m-bla {
    font-size: 9pt;
    font-family: monospace;
    color: black;
    }
.nasiji {
    <?php if (date('Hi') < '1200') {    // 午前中なら ?>
    background-image: url(<?php echo IMG?>nasiji_apple.gif);
    <?php } else {  // 午後なら ?>
    background-image: url(<?php echo IMG?>nasiji_silver.gif);
    <?php } ?>
    background-repeat: repeat;
}
input.bg {
    background-image: url(<?php echo IMG?>border_silver_button.gif);
    background-repeat: repeat;
    color: blue;
}
select {
    background-color: #003e7c;
    color: white;
}}
-->
</style>
<script type='text/javascript' src='confirm.js'></script>
<script type='text/javascript' language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    document.stati_form.stati.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.stati_form.stati.select();
}
function win_open(url, w, h)
{
    if (!w) w = 256;
    if (!h) h = 382;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, '', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,resizable=yes,top='+top+',left='+left);
}
// -->
</script>
</head>
<body bgcolor='#ffffff' text='#000000' onLoad='set_focus()' style='overflow: hidden;'>

<!--
<body bgcolor='#ffffff' text='#000000' onLoad='set_focus()'>
-->
<?php echo $menu->out_title_border()?>
<table width='100%' height='90%' border='0' cellspacing='0' cellpadding='0' style='border-left:2.0pt solid #ffffff;'><tr>

<!-- left view  -->
<td valign='top' width='20%'>
<script type='text/javascript' language='Javascript'>
<!--
    str=navigator.appName.toUpperCase();
    if(str.indexOf("NETSCAPE")>=0) document.write("<table width='100%' height=1950 bgcolor='#003e7c' cellpadding='10' class='nasiji'>");
    if(str.indexOf("EXPLORER")>=0) document.write("<table width='100%' height='100%' bgcolor='#003e7c' cellpadding='10' class='nasiji'>");
//-->
</script>
<?php
    echo("<noscript><table width='100%' height='100%' bgcolor='#003e7c' cellpadding='10' class='nasiji'></noscript>");
    $_SESSION['lookupkey']=StripSlashes($_SESSION['lookupkey']);
?>
    
    <tr><td valign='top'>
    <div class='listScroll'>
    <p align='center'><img width=190 height=34 src='<?php echo IMG?>t_nitto_logo1.gif' border=0></p>

    <!-- function -->

    <center>
    <table width='100%'>
    <form method="post" action="emp_menu.php?func=<?php echo(FUNC_MINEINFO) ?>">
    <tr><td align='center'>
        <input type='hidden' name='func' value='<?php echo(FUNC_MINEINFO) ?>'>
<?php   
        if ($request->get('func') == FUNC_MINEINFO) {
            echo "    <input class='bg' type='submit' name='func_button' value='自己情報表示' style='color:red;'>\n";
        } else {
            echo "    <input class='bg' type='submit' name='func_button' value='自己情報表示'>\n";
        }
?>
    </td></tr>
    </form>

<?php   
    if ($_SESSION['Auth'] >= AUTH_LEBEL2 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
?>
        <form method="post" action="emp_menu.php?func=<?php echo(FUNC_NEWUSER) ?>">
        <tr><td align='center'>
            <input type='hidden' name='func' value='<?php echo(FUNC_NEWUSER) ?>'>
<?php
            if ($request->get('func') == FUNC_NEWUSER || $request->get('func') == FUNC_CONFNEWUSER) {
                echo "    <input class='bg' type='submit' value='社員新規登録' style='color:red;'>\n";
            } else {
                echo "    <input class='bg' type='submit' value='社員新規登録'>\n";
            }
?>
        </td></tr>
        </form>
<?php
    }
?>
    <form name='stati_form' method='post' action='emp_menu.php?func=<?php echo(FUNC_STATISTIC) ?>'>
        <tr>
        <td align='center'>
            <input type='hidden' name='func' value='<?php echo(FUNC_STATISTIC) ?>'>
<?php
            if ($request->get('func') == FUNC_STATISTIC) {
                echo "    <input class='bg' type='submit' name='stati' value='社員統計情報' style='color:red;'>\n";
            } else {
                echo "    <input class='bg' type='submit' name='stati' value='社員統計情報'>\n";
            }
?>
        </td>
        </tr>
    </form>
    </table>
        </center>
        <noscript><p><font size=-1 color="#ff7e00">JavaScriptが無効になっています。有効にしてから本システムをご利用ください。</font></p></noscript>
        <hr style='border-color:white;'>

    <!-- lookup func -->

        <table width="100%">
        <?php
        //if ($_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '300055' || $_SESSION['User_ID'] == '010472' || $_SESSION['User_ID'] == '015806') {
        if (getCheckAuthority(40) || getCheckAuthority(41) || getCheckAuthority(42) || getCheckAuthority(43) || getCheckAuthority(44) || getCheckAuthority(45) || getCheckAuthority(46) || getCheckAuthority(47) || getCheckAuthority(48) || getCheckAuthority(49) || getCheckAuthority(50) || getCheckAuthority(51) || getCheckAuthority(52) || getCheckAuthority(55)) {
        ?>
        <form method="post" action="emp_menu.php?func=<?php echo(FUNC_LOOKUP) ?>" onSubmit="return chkLookupTermsY(this)">
        <?php
        } else {
        ?>
        <form method="post" action="emp_menu.php?func=<?php echo(FUNC_LOOKUP) ?>" onSubmit="return chkLookupTerms(this)">
        <?php
        }
        ?>
        <input type='hidden' name='func' value='<?php echo(FUNC_LOOKUP) ?>'>
            <tr>
                <td colspan='2' align='center'>
                    <?php if ($request->get('func') == FUNC_LOOKUP) { ?>
                    <input class='bg' type='submit' name='lookup' value='検索の実行' style='color:red;'>
                    <?php } else { ?>
                    <input class='bg' type='submit' name='lookup' value='検索の実行'>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td colspan='2' align='center' class='left-font-m-bla'>基本情報</td>
            </tr>
            <tr><td align="left" nowrap class='left-font'>検索種別</td>
                <td align="right"><select name="lookupkind">
                    <option <?php if($_SESSION['lookupkind']==KIND_USER) echo("selected"); ?>
                         value=<?php echo(KIND_USER); ?>>従業員情報
<?php
    if($_SESSION['Auth'] >= AUTH_LEBEL1 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551'){
?>
                    <option <?php if($_SESSION['lookupkind']==KIND_TRAINING) echo("selected"); ?>
                         value=<?php echo(KIND_TRAINING); ?>>教育訓練記録
<?php
    }
?>
<?php
    if($_SESSION['Auth'] >= AUTH_LEBEL2 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551'){
?>
                    <option <?php if($_SESSION['lookupkind']==KIND_ADDRESS) echo("selected"); ?>
                         value=<?php echo(KIND_ADDRESS); ?>>住所情報
<?php
    }
?>
                    </select>
                </td>
            </tr>
            <tr><td align="left" nowrap class='left-font'>検索方法</td>
                <td align="right"><select name="lookupkeykind">
                <option <?php if($_SESSION['lookupkeykind'] == KIND_DISABLE) echo("selected"); ?> value=<?php echo(KIND_DISABLE); ?>>検索無し
                <option <?php if($_SESSION['lookupkeykind'] == KIND_USERID) echo("selected"); ?> value=<?php echo(KIND_USERID); ?>>社員No
                <option <?php if($_SESSION['lookupkeykind'] == KIND_FULLNAME) echo("selected"); ?> value=<?php echo(KIND_FULLNAME); ?>>フルネーム
                <option <?php if($_SESSION['lookupkeykind'] == KIND_LASTNAME) echo("selected"); ?> value=<?php echo(KIND_LASTNAME); ?>>姓
                <option <?php if($_SESSION['lookupkeykind'] == KIND_FASTNAME) echo("selected"); ?> value=<?php echo(KIND_FASTNAME); ?>>名
                <?php
                    if(getCheckAuthority(54)){
                ?>
                        <option <?php if($_SESSION['lookupkeykind'] == KIND_ABSENCE) echo("selected"); ?> value=<?php echo(KIND_ABSENCE); ?>>不在者
                <?php
                    }
                ?>
                <option <?php if($_SESSION['lookupkeykind'] == KIND_AGE) echo("selected"); ?> value=<?php echo(KIND_AGE); ?>>年齢順
                </select>
                </td>
            </tr>
            <tr>
                <td align="left" class='left-font'>検索キー</td>
                <td align="right">
                <input type="text" name="lookupkey" size=18 value='<?php echo($_SESSION['lookupkey']); ?>'>
                </td>
            </tr>
            <tr>
                <td align="left" class='left-font'>所属</td>
                <td align="right">
                <select name="lookupsection">
                <option <?php if ($_SESSION['lookupsection'] == '-2') echo 'selected '; ?>value='-2'>出向除く全て
                <option <?php if ($_SESSION['lookupsection'] == KIND_DISABLE) echo 'selected '; echo "value='" . KIND_DISABLE ."'"; ?>>すべて
<?php
    $query="select * from section_master where sflg=1 order by sid asc";
    $res=array();
    if($rows=getResult($query,$res)){
        for($i=0;$i<$rows;$i++){
            echo("<option ");
            if($_SESSION['lookupsection']==$res[$i]["sid"])
                echo("selected ");
            echo("value=" . $res[$i]["sid"] . ">" . mb_substr(trim($res[$i]['section_name']), -10) . "\n");
        }
    }
?>
                </select>
                </td>
            </tr>
            <tr>
                <td align="left" class='left-font'>職位</td>
                <td align="right"><select name="lookupposition">
                <option value=<?php echo(KIND_DISABLE); ?>>すべて
                <option <?php if($_SESSION['lookupposition'] == KIND_EMPLOYEE) echo("selected"); ?> value=<?php echo(KIND_EMPLOYEE); ?>>社員全て
                <option <?php if($_SESSION['lookupposition'] == KIND_PARTTIME) echo("selected"); ?> value=<?php echo(KIND_PARTTIME); ?>>パート全て
                <option <?php if($_SESSION['lookupposition'] == KIND_MANAGE) echo("selected"); ?> value=<?php echo(KIND_MANAGE); ?>>課長代理以上
<?php
    $query="select * from position_master where pflg=1 order by pid asc";
    $res=array();
    if($rows=getResult($query,$res)){
        for($i=0;$i<$rows;$i++){
            echo("<option ");
            if($_SESSION['lookupposition']==$res[$i]["pid"])
                echo("selected ");
            echo("value=" . $res[$i]["pid"] . ">" . $res[$i]["position_name"] . "\n");
        }
    }
?>
                </select></td>
            </tr>
            <tr>
                <td align="left" class='left-font'>入社年度</td>
                <td align="right"><select name="lookupentry">
                <option value=<?php echo(KIND_DISABLE); ?>>すべて
<?php
    $now=getdate(time());
    $thisyear=$now["year"];
    for($i=1960;$i<=$thisyear;$i++){
        echo("<option ");
        if($_SESSION['lookupentry'] == $i)
            echo("selected ");
        echo("value=" . $i . ">" . $i . "年度\n");
    }
?>
    </table>
    <table width='100%'>
    <hr style='border-color:white;'>
<?php
    //if ($_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '300055' || $_SESSION['User_ID'] == '010472' || $_SESSION['User_ID'] == '015806') {
    if (getCheckAuthority(40) || getCheckAuthority(41) || getCheckAuthority(42) || getCheckAuthority(43) || getCheckAuthority(44) || getCheckAuthority(45) || getCheckAuthority(46) || getCheckAuthority(47) || getCheckAuthority(48) || getCheckAuthority(49) || getCheckAuthority(50) || getCheckAuthority(51) || getCheckAuthority(52) || getCheckAuthority(55)) {
?>
            <tr>
                <td colspan='2' align='center' class='left-font-m-bla'>有給情報</td>
            </tr>
            <tr><td align="left" nowrap class='left-font'>検索方法</td>
                <td align="right"><select name="lookupyukyukind">
                <option <?php if($_SESSION['lookupyukyukind'] == KIND_DISABLE) echo("selected"); ?> value=<?php echo(KIND_DISABLE); ?>>検索無し
                <option <?php if($_SESSION['lookupyukyukind'] == KIND_DAYUP) echo("selected"); ?> value=<?php echo(KIND_DAYUP); ?>>指定日数以上取得
                <option <?php if($_SESSION['lookupyukyukind'] == KIND_DAYDOWN) echo("selected"); ?> value=<?php echo(KIND_DAYDOWN); ?>>指定日数未満取得
                <option <?php if($_SESSION['lookupyukyukind'] == KIND_PERUP) echo("selected"); ?> value=<?php echo(KIND_PERUP); ?>>指定％以上取得
                <option <?php if($_SESSION['lookupyukyukind'] == KIND_PERDOWN) echo("selected"); ?> value=<?php echo(KIND_PERDOWN); ?>>指定％以下取得
                </select>
                </td>
            </tr>
            <tr>
                <td align="left" class='left-font'>検索キー</td>
                <td align="right">
                <input type="text" name="lookupyukyu" size=18 value='<?php echo($_SESSION['lookupyukyu']); ?>'>
                </td>
            </tr>
<?php
    } else {
        $_SESSION['lookupyukyukind'] = KIND_DISABLE;
    }
?>
                </select></td>
            </tr>
            </table>
            <table width='100%'>
            <hr style='border-color:white;'>
<?php
    //if ($_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '300055' || $_SESSION['User_ID'] == '010472' || $_SESSION['User_ID'] == '015806') {
    if (getCheckAuthority(40) || getCheckAuthority(41) || getCheckAuthority(42) || getCheckAuthority(43) || getCheckAuthority(44) || getCheckAuthority(45) || getCheckAuthority(46) || getCheckAuthority(47) || getCheckAuthority(48) || getCheckAuthority(49) || getCheckAuthority(50) || getCheckAuthority(51) || getCheckAuthority(52) || getCheckAuthority(55)) {
?>
            <tr>
                <td colspan='2' align='center' class='left-font-m-bla'>有給5日情報<font color='red'>※優先</font></td>
            </tr>
            <tr><td align="left" nowrap class='left-font'>検索方法</td>
                <td align="right"><select name="lookupyukyufive">
                <option <?php if($_SESSION['lookupyukyufive'] == KIND_DISABLE) echo("selected"); ?> value=<?php echo(KIND_DISABLE); ?>>検索無し
                <option <?php if($_SESSION['lookupyukyufive'] == KIND_DAYUP) echo("selected"); ?> value=<?php echo(KIND_DAYUP); ?>>指定日数以上取得
                <option <?php if($_SESSION['lookupyukyufive'] == KIND_DAYDOWN) echo("selected"); ?> value=<?php echo(KIND_DAYDOWN); ?>>指定日数未満取得
                </select>
                </td>
            </tr>
            <tr>
                <td align="left" class='left-font'>検索キー</td>
                <td align="right">
                <input type="text" name="lookupyukyuf" size=18 value='<?php echo($_SESSION['lookupyukyuf']); ?>'>
                </td>
            </tr>
<?php
    } else {
        $_SESSION['lookupyukyufive'] = KIND_DISABLE;
    }
?>
                </select></td>
            </tr>
            </table>
            <table width='100%'>
            <hr style='border-color:white;'>
            <tr>
                <td colspan='2' align='center' class='left-font-m-bla'>資　格</td>
            </tr>
            <tr>
                <td colspan=2 align="right"><select name="lookupcapacity">
                <option value=<?php echo(KIND_DISABLE); ?>>検索条件無し
<?php
    $query="select * from capacity_master where cflg=1 order by cid asc";
    $res=array();
    if($rows=getResult($query,$res)){
        for($i=0;$i<$rows;$i++){
            echo("<option ");
            if($_SESSION['lookupcapacity'] == $res[$i]["cid"])
                echo("selected ");
            echo("value=" . $res[$i]["cid"] . ">" . substr($res[$i]["capacity_name"],0,24) . "\n");
        }
    }
?>
                </select>
                </td>
            </tr>
            <tr>
                <td colspan='2' align='center' class='left-font-m-bla'>教　育</td>
            </tr>
            <tr><td colspan=2 align="right"><select name="lookupreceive">
                <option value=<?php echo(KIND_DISABLE); ?>>検索条件無し
<?php
    $query="select * from receive_master where rflg=1 order by rid asc";
    $res=array();
    if($rows=getResult($query,$res)){
        for($i=0;$i<$rows;$i++){
            echo("<option ");
            if($_SESSION['lookupreceive']==$res[$i]["rid"])
                echo("selected ");
            echo("value=" . $res[$i]["rid"] . ">" . substr($res[$i]["receive_name"],0,24) . "\n");
        }
    }
?>
                </select>
                </td>
            </tr>
            <tr>
                <td colspan='2' align='center'>
                    <?php if ($request->get('func') == FUNC_LOOKUP) { ?>
                    <input class='bg' type='submit' name='lookup' value='検索の実行' style='color:red;'>
                    <?php } else { ?>
                    <input class='bg' type='submit' name='lookup' value='検索の実行'>
                    <?php } ?>
                </td>
            </tr>
        </form>
        </table>
        <hr style='border-color:white;'>

        <center>
        <table width='100%'>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_RETIREINFO) ?>'>
        <tr><td align='center'>
<?php
    if ($request->get('func') == FUNC_RETIREINFO) {
        echo "<input class='bg' type='submit' value='退職者一覧表' style='color:red;'>\n";
    } else {
        echo "<input class='bg' type='submit' value='退職者一覧表'>\n";
    }
?>
        </td></tr>
        </form>
<?php   
    if ($_SESSION['Auth'] >= AUTH_LEBEL3 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551'){
?>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_CHGRECEIVE) ?>'>
        <tr><td align='center'>
<?php   
        if ($request->get('func') == FUNC_CHGRECEIVE || $request->get('func') == FUNC_RECIDREGIST) {
            echo "<input class='bg' type='submit' value='教育一括登録' style='color:red;'>\n";
        } else {
            echo "<input class='bg' type='submit' value='教育一括登録'>\n";
        }
?>  
        </td></tr>
        </form>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_CHGCAPACITY) ?>'>
        <tr><td align='center'>
<?php
        if ($request->get('func') == FUNC_CHGCAPACITY || $request->get('func') == FUNC_CAPIDREGIST) {
            echo "<input class='bg' type='submit' value='資格一括登録' style='color:red;'>\n";
        } else {
            echo "<input class='bg' type='submit' value='資格一括登録'>\n";
        }
?>
        </td></tr>
        </form>
<?php   
    }
    if ($_SESSION['Auth'] >= AUTH_LEBEL3 || $_SESSION['User_ID'] == '970227' || $_SESSION['User_ID'] == '015806'){
?>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_ADDPHOLYDAY) ?>'>
        <tr><td align='center'>
<?php
        if ($request->get('func') == FUNC_ADDPHOLYDAY || $request->get('func') == FUNC_HOLYDAYREGIST) {
            echo "<input class='bg' type='submit' value='計画有給登録' style='color:red;'>\n";
        } else {
            echo "<input class='bg' type='submit' value='計画有給登録'>\n";
        }
?>
        </td></tr>
        </form>
<?php
    }
    if ($_SESSION['Auth'] >= AUTH_LEBEL2 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551') {
?>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_CHGINDICATE) ?>'>
        <tr><td align='center'>
<?php
        if ($request->get('func') == FUNC_CHGINDICATE) {
            echo "<input class='bg' type='submit' value='表示項目設定' style='color:red;'>\n";
        } else {
            echo "<input class='bg' type='submit' value='表示項目設定'>\n";
        }
?>
        </td></tr>
        </form>
<?php
    }
?>
        <form method='post' action='emp_menu.php?func=<?php echo(FUNC_MAIL) ?>'>
        <tr><td align='center'>
<?php
        if ($request->get('func') == FUNC_MAIL) {
            echo "<input class='bg' type='submit' value='メールアドレス' style='color:red;'>\n";
        } else {
            echo "<input class='bg' type='submit' value='メールアドレス'>\n";
        }
?>
        </td></tr>
        </form>
        </table>
        <table width='100%'>
        <hr style='border-color:white;'>
<?php
        if (getCheckAuthority(40) || getCheckAuthority(41) || getCheckAuthority(42) || getCheckAuthority(43) || getCheckAuthority(44) || getCheckAuthority(45) || getCheckAuthority(46) || getCheckAuthority(47) || getCheckAuthority(48) || getCheckAuthority(49) || getCheckAuthority(50) || getCheckAuthority(51) || getCheckAuthority(52) || getCheckAuthority(55)) {
?>
        <form method='post' target='_blank' action='print/print_emp_branch.php?emp_name=print_yukyu_five_list&yukyulist=<?php echo($_SESSION['yukyulist']) ?>&fivesection=<?php echo($_SESSION['fivesection']) ?>' onSubmit="return chkLookupFive(this)">
        <tr>
            <td colspan='2' align='center' class='left-font-m-bla'>有給管理台帳</td>
        </tr>
        <tr>
            <td align="center" class='left-font'>年度(例：2019)</td>
        </tr>
        <tr>
            <td align="center">
            <input type="text" name="yukyulist" size=18 value='<?php echo($_SESSION['yukyulist']); ?>'>
            </td>
        </tr>
        <tr>
            <td align="center" class='left-font-m'>所属<BR>
            <select name='fivesection'>
                    <?php 
                    if (getCheckAuthority(28)) {
                    ?>
                        <option <?php if ($fivesection == '-1') echo 'selected '; ?>value='-1'>全て
                        <option <?php if ($fivesection == '31') echo 'selected '; ?>value='31'>出向
                    <?php
                    }
                    if (getCheckAuthority(29)) {
                    ?>
                        <option <?php if ($fivesection == '-1') echo 'selected '; ?>value='-1'>全て
                        <option <?php if ($fivesection == '31') echo 'selected '; ?>value='31'>出向
                    <?php
                    }
                    ?>
                    <?php echo getTargetSectionvalues($fivesection) ?>
            </select>
            </td>
        </tr>
        <tr>
            <td align='center'>
            <?php
            if ($request->get('func') == FUNC_FIVE) {
                echo "<input class='bg' type='submit' value='有給管理台帳' style='color:red;'>\n";
            } else {
                echo "<input class='bg' type='submit' value='有給管理台帳'>\n";
            }
?>
            </td>
        </tr>
        </form>
<?php
        }
?>
        </table>
        </center>
        </td></tr>
    </table>
    </td>
</div>
<!-- right view -->

<td valign="top">
<div class='listScroll2'>
<?php
    if ($request->get('func') == '')
        // include("view_default.php");
        include("view_user_statistic.php");         // 21
    elseif ($request->get('func') == FUNC_MINEINFO)
        include("view_mineinfo.php");               // 1
    elseif ($request->get('func') == FUNC_NEWUSER)
        include("view_userinfo_get.php");           // 2
    elseif ($request->get('func')==FUNC_CONFNEWUSER)
        include("view_userinfo_conf.php");          // 3
    // elseif ($request->get('func')==FUNC_DBADMIN)
    //     include("view_admindb.php");                // 4
    elseif ($request->get('func')==FUNC_LOOKUP)
        include("view_userinfo.php");               // 5
    elseif ($request->get('func')==FUNC_CHGUSERINFO)
        include("view_userinfo_chg.php");           // 6
    elseif ($request->get('func')==FUNC_CONFUSERINFO)
        include("view_userinfo_chgconf.php");       // 7
    elseif ($request->get('func')==FUNC_ADMINUSERINFO)
        include("view_userinfo_chgadmin.php");      // 8
    elseif ($request->get('func')==FUNC_RETIREINFO)
        include("view_userinfo_retire.php");        // 9
    elseif ($request->get('func')==FUNC_CHGRECEIVE)
        include("view_userinfo_chgreceive.php");    // 10
    elseif ($request->get('func')==FUNC_CHGCAPACITY)
        include("view_userinfo_chgcapacity.php");   // 11
    elseif ($request->get('func')==FUNC_ADDPHOLYDAY)
        include("view_userinfo_addpholyday.php");   // 15
    elseif ($request->get('func')==FUNC_RECIDREGIST)
        include("view_recid_regist.php");           // 12
    elseif ($request->get('func')==FUNC_CAPIDREGIST)
        include("view_capid_regist.php");           // 13
    elseif ($request->get('func')==FUNC_HOLYDAYREGIST)
        include("view_holyday_regist.php");         // 16
    elseif ($request->get('func')==FUNC_CHGINDICATE)
        include("view_select_indicate.php");        // 14
    elseif ($request->get('func')==FUNC_STATISTIC)
        include("view_user_statistic.php");         // 21
    elseif ($request->get('func')==FUNC_MAIL)
        include("mail/mailAddress_Main.php");       // 22
    elseif ($request->get('func')==FUNC_RECIDREGISTCHK)
        include("view_recid_regist_check.php");     // 23
    elseif ($request->get('func')==FUNC_CAPIDREGISTCHK)
        include("view_capid_regist_check.php");     // 24
    elseif ($request->get('func')==FUNC_HOLYDAYREGISTCHK)
        include("view_holyday_regist_check.php");   // 25
    elseif ($request->get('func')==FUNC_FIVE)
        include("print/print_yukyu_five_list.php");   // 26
    else
        include("view_default.php");
?>
</div>
</td>
</tr></table>
</body>
<?php
if ($request->get('func')==FUNC_CHGRECEIVE || $request->get('func')==FUNC_CHGCAPACITY || $request->get('func')==FUNC_ADDPHOLYDAY) {
?>
    <?php echo $menu->out_alert_java(false)?>
<?php
} else {
?>
    <?php echo $menu->out_alert_java()?>
<?php
}
?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
<?php
////// 対象部門のHTML <select> option の出力
function getTargetSectionvalues($fivesection)
    {
        // 初期化
        $option = "\n";
        // 管理者用
        if (getCheckAuthority(28)) {
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $query="select * from section_master where sflg=1 and sid<>90 and sid<>95 and sid<>80 and sid<>31 order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($fivesection == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else if(getCheckAuthority(29)) {    // 工場長、副工場長は全てを閲覧できる
            $query="select * from section_master where sflg=1 and sid<>90 and sid<>95 and sid<>80 and sid<>31 order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($fivesection == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else if(getCheckAuthority(42)) {    // 技術部は技術部のみ閲覧できる
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $sid_where="(sid='38' or sid='18' or sid='4')";
            $query="select * from section_master where sflg=1 and {$sid_where} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($fivesection == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else if(getCheckAuthority(43)) {    // 生産部は生産部のみ閲覧できる
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $sid_where="(sid='8' or sid='32' or sid='2' or sid='3')";
            $query="select * from section_master where sflg=1 and {$sid_where} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($fivesection == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else if(getCheckAuthority(55)) {    // 製造部は製造部のみ閲覧できる
            //$query="select * from section_master where sflg=1 and sid<>99 and sid<>90 and sid<>80 and sid<>38 and sid<>31 and sid<>17 and sid<>9 and sid<>8 order by sid asc";
            $sid_where="(sid='17' or sid='34' or sid='35')";
            $query="select * from section_master where sflg=1 and {$sid_where} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                for($i=0;$i<$rows;$i++){
                    if($fivesection == $res[$i]["sid"]) {
                        $option .= "<option value='{$res[$i]['sid']}' selected>" . mb_substr(trim($res[$i]['section_name']), -10). "</option>\n";
                    } else {
                        $option .= "<option value='{$res[$i]['sid']}'>" . mb_substr(trim($res[$i]['section_name']), -10) . "</option>\n";
                    }
                }
            }
        } else {
        // 自部門のみ照会 各課の課長の社員番号を入れる
            if ($_SESSION['User_ID'] == '300349') {    // 商品管理課   村上課長代理
                $sid=19;    
            } else if ($_SESSION['User_ID'] == '012980') {    // 品証課   岩本課長
                $sid=18;
            } else if ($_SESSION['User_ID'] == '018040') {    // 製造１課 阿久津課長
                $sid=34;
            } else if ($_SESSION['User_ID'] == '015202') {    // 製造２課 高橋課長
                $sid=35;
            } else if ($_SESSION['User_ID'] == '016713' || $_SESSION['User_ID'] == '016080') {    // 生管課   中山部長代理 吉成課長
                $sid=32;
            } else if ($_SESSION['User_ID'] == '017850' || $_SESSION['User_ID'] == '300055') {    // 総務課   上野部長代理 川zｱ課長代理
                $sid=5;
            } else if ($_SESSION['User_ID'] == '017728') {    // リニア組立課  安田課長
                $sid=3;
            } else if ($_SESSION['User_ID'] == '017507') {    // カプラ組立課 小山課長
                $sid=2;
            } else if ($_SESSION['User_ID'] == '014524') {    // 技術課 萩野課長
                $sid=4;
            }
            $query="select * from section_master where sflg=1 and sid={$sid} order by sid asc";
            $res=array();
            if($rows=getResult($query,$res)){
                $option .= "<option value='{$res[0]['sid']}' selected>" . trim($res[0]['section_name']). "</option>\n";
            }
        }
        return $option;
    }
?>
