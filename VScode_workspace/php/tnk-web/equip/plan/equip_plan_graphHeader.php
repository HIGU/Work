<?php
//////////////////////////////////////////////////////////////////////////////
// 機械稼動管理システムの スケジュール ガントグラフ メンテ  Headerフレーム  //
// Copyright (C) 2004-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/02 Created  equip_plan_graphHeader.php                           //
// 2005/06/24 F2/F12キーで戻るための対応で JavaScriptの set_focus()を追加   //
// 2018/05/18 ７工場を追加。コード４の登録を強制的に7に変更            大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');     // TNK 全共通 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 8);                     // site_index=40(設備メニュー) site_id=8(スケジューラー)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

///// GET/POSTのチェック&設定
$mac_no = @$_REQUEST['mac_no'];
if ($mac_no == '') {
    $reload = 'disabled';
} else {
    $reload = '';
    $_SESSION['mac_no'] = $mac_no;
}

/////////// グラフのX軸の時間範囲を取得
if (isset($_REQUEST['equip_xtime'])) {
    $_SESSION['equip_xtime'] = $_REQUEST['equip_xtime'];
} else {
    $_SESSION['equip_xtime'] = 'max';
}
$equip_xtime = $_SESSION['equip_xtime'];

if (isset($_REQUEST['reset_page'])) {
    @$_SESSION['equip_graph_page'] = 1;     // 初期化
}

///// ローカル変数の初期化
$view = 'NG';

//////////// 機械マスターから設備番号・設備名のリストを取得
if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    $factory = '';
}
///// リクエストが無ければセッションから工場区分を取得する。(通常はこのパターン)
if ($factory == '') {
    $factory = @$_SESSION['factory'];
}
if ($factory == '') {
    $query = "select mac_no                 AS mac_no
                    -- , substr(mac_name,1,7)  AS mac_name
                    , mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                order by mac_no ASC
    ";
} else {
    $query = "select mac_no                 AS mac_no
                    -- , substr(mac_name,1,7)  AS mac_name
                    , mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                    and
                    factory='{$factory}'
                order by mac_no ASC
    ";
}
$res_sel = array();
if (($rows_sel = getResult($query, $res_sel)) < 1) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>機械マスターに登録がありません！</font>";
} else {
    $mac_no_name = array();
    for ($i=0; $i<$rows_sel; $i++) {
        $mac_no_name[$i] = $res_sel[$i]['mac_no'] . " " . trim($res_sel[$i]['mac_name']);   // 機械番号と名称の間にスペース追加
    }
}

if ($mac_no != '') {
    //////////////// 機械マスターから工場区分・メーカー型式・メーカー名を取得
    $query = "select factory
                    ,maker_name
                    ,maker
                    ,mac_name
                from
                    equip_machine_master2
                where
                    mac_no={$mac_no}
                limit 1
    ";
    $res = array();
    if (getResult($query, $res) <= 0) {
        $factory = '　'; $maker_name = '　'; $maker = '　'; $mac_name = '　'; // error時はブランク
    } else {
        $factory = $res[0]['factory'];
        $maker_name = $res[0]['maker_name'];
        $maker   = $res[0]['maker'];
        $mac_name = $res[0]['mac_name'];
        switch ($factory) {
        case 1:
            $factory = '１工場';
            break;
        case 2:
            $factory = '２工場';
            break;
        case 3:
            $factory = '３工場';
            break;
        case 4:
            $factory = '４工場';
            break;
        case 5:
            $factory = '５工場';
            break;
        case 6:
            $factory = '６工場';
            break;
        case 7:
            $factory = '７工場';
            break;
        default:
            $factory = '未定義';
            break;
        }
        $view = 'OK';
    }
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($view == 'NG') {
    $menu->set_title('スケジューラーの照会及びメンテナンス');
} else {
    $menu->set_title("{$mac_no}　{$mac_name}　スケジューラーの照会及びメンテナンス");
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
<?= $menu->out_site_java() ?>
<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
.sub_font {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pick_font {
    font-size:      9.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    font-size:          10.5pt;
    font-weight:        bold;
    font-family:        monospace;
    color:              blue;
    /* background-color:   yellow; */
}
.item {
    position:       absolute;
    top:            90px;
    left:           90px;
}
.table_font {
    font-size:      11.5pt;
    font-family:    monospace;
}
.ext_font {
    /* background-color:   yellow; */
    color:              blue;
    font-size:          10.5pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color: #FFFFFF;
    border-left-color: #FFFFFF;
    border-right-color: #DFDFDF;
    border-bottom-color: #DFDFDF;
}
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
<script language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
    // document.mac_form.mac_no.focus();  // カーソルキーで機械を変更出来るようにする
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <!----------------- 見出しを表示 ------------------------>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <td align='center' width='100'>
                    <form name='mac_form' method='post' action='<?= $menu->out_self() ?>'>
                        <select name='mac_no' class='ret_font' onChange='document.mac_form.submit()'>
                        <?php if ($mac_no == '') echo "<option value=''>機械選択</option>\n" ?>
                        <?php
                        for ($j=0; $j<$rows_sel; $j++) {
                            if ($mac_no == $res_sel[$j]['mac_no']) {
                                printf("<option value='%s' selected>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            } else {
                                printf("<option value='%s'>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            }
                        }
                        ?>
                        </select>
                        <input type='hidden' name='reset_page' value=''>
                    </form>
                </td>
                <?php if ($view == 'OK') { ?>
                <td align='center' nowrap width='100'><?=$factory?></td>
                <td align='center' nowrap width='100'><?=$maker_name?></td>
                <td align='center' nowrap width='150'><?=$maker?></td>
                <?php } ?>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        
    </center>
</body>
</html>
<Script Language='JavaScript'>
    document.MainForm.select.value = '<?=$view?>';
    document.MainForm.target = 'List';
    document.MainForm.action = 'equip_plan_graphList.php';
    document.MainForm.submit();
</Script>
<?=$menu->out_alert_java()?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
