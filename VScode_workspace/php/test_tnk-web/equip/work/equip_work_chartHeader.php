<?php
//////////////////////////////////////////////////////////////////////////////
// 機械稼動管理システムの 運転状況表 表示  Headerフレーム                   //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/07/22 Created  equip_work_chartHeader.php                           //
// 2004/08/08 フレーム版の戻り先をapplication→_parentに変更(FRAME無し対応) //
// 2005/05/10 頁制御機能(簡易版)を追加 $_SESSION['equip_work_chart_offset'] //
// 2005/06/24 F2/F12キーで戻るための対応で JavaScriptの set_focus()を追加   //
// 2007/05/25 加工サイクル(秒)(分)を追加しデザイン変更                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 10);                    // site_index=40(設備メニュー) site_id=10(状況表)
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

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    $factory = '';
}
///// リクエストが無ければセッションから工場区分を取得する。(通常はこのパターン)
if ($factory == '') {
    $factory = @$_SESSION['factory'];
}

//////////// 機械マスターから設備番号・設備名のリストを取得
if ($factory == '') {
    $query = "select mac_no                 AS mac_no
                    , substr(mac_name,1,7)  AS mac_name
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
                    , substr(mac_name,1,7)  AS mac_name
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
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>機械マスターに登録がありません！</font>";
} else {
    $mac_no_name = array();
    for ($i=0; $i<$rows; $i++) {
        $mac_no_name[$i] = $res[$i]['mac_no'] . " " . trim($res[$i]['mac_name']);   // 機械番号と名称の間にスペース追加
    }
}

///// ローカル変数の初期化
$mac_name   = '';
$siji_no    = '　';
$koutei     = '　';
$parts_no   = '　';
$parts_name = '　';
$parts_mate = '　';
$plan_cnt   = '　';
$view       = 'NG';

if ($mac_no != '') {
    //////////////// 機械マスターから機械名を取得
    $query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1";
    if (getUniResult($query, $mac_name) <= 0) {
        $mac_name = '  ';   // error時は機械名をブランク
    }
    //////////// ヘッダーより開始日時と終了日時の取得
    $query = "select to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_timestamp
                , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as end_timestamp
                , siji_no
                , koutei
                , parts_no
                , plan_cnt
            from
                equip_work_log2_header
            where
                mac_no={$mac_no} and work_flg IS TRUE
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$mac_no}：{$mac_name} は運転開始されていません！</font>";
    } else {
        $str_timestamp = $res_head[0]['str_timestamp'];
        $end_timestamp = $res_head[0]['end_timestamp'];
        $siji_no   = $res_head[0]['siji_no'];
        $koutei    = $res_head[0]['koutei'];
        $parts_no  = $res_head[0]['parts_no'];
        $plan_cnt  = $res_head[0]['plan_cnt'];
        $query = "select substr(midsc, 1, 12) as midsc, mzist from miitem where mipn='{$parts_no}'";
        $res_mi = array();
        if ( getResult($query, $res_mi) <= 0) {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}で部品マスターが未登録！</font>";
        } else {
            $parts_name = $res_mi[0]['midsc'];
            $parts_mate = $res_mi[0]['mzist'];
            $_SESSION['work_mac_no']  = $mac_no;
            $_SESSION['work_siji_no'] = $siji_no;
            $_SESSION['work_koutei']  = $koutei;
            $view = 'OK';
        }
    }
}
// 上で設定しているが実際に表示OKになっているかチェック
if ($view != 'OK') {
    $reload = 'disabled';
} else {
    $reload = '';
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("{$mac_no}  {$mac_name}  運転 状況 表形式(明細)");
//////////// 表題の設定
$menu->set_caption('機械番号の選択');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_site_java() ?>
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
    font-family: monospace;
}
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
    color:              blue;
    background-color:   yellow;
}
.item {
    position: absolute;
    top:    90px;
    left:   90px;
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
    document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <!----------------- 見出しを表示 ------------------------>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table width=100% class='winbox_field' border='1' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <td class='winbox' align='center' width='13%'>
                    <form name='mac_form' method='post' action='<?php echo $menu->out_self() ?>'>
                    <select style='width:100%;' name='mac_no' class='ret_font' onChange='document.mac_form.submit()'>
                        <?php if ($mac_no == '') echo "<option value=''>選択指定</option>\n" ?>
                    <?php
                        for ($j=0; $j<$rows; $j++) {
                            if ($mac_no == $res[$j]['mac_no']) {
                                printf("<option value='%s' selected>%s</option>\n", $res[$j]['mac_no'], $mac_no_name[$j]);
                            } else {
                                printf("<option value='%s'>%s</option>\n", $res[$j]['mac_no'], $mac_no_name[$j]);
                            }
                        }
                    ?>
                    </select>
                    </form>
                </td>
                <td class='winbox' align='center' nowrap width='7%'>部品No</td>
                <td class='winbox' align='center' nowrap width='9%'><?php echo $parts_no ?></td>
                <td class='winbox' align='center' nowrap width='7%'>部品名</td>
                <td class='winbox pick_font' align='left' nowrap width='15%'><?php echo $parts_name ?></td>
                <td class='winbox' align='center' nowrap width='5%'>材質</td>
                <td class='winbox pick_font' align='center' nowrap width='9%'><?php echo $parts_mate ?></td>
                <td class='winbox' align='center' nowrap width='7%'>指示No</td>
                <td class='winbox' align='center' nowrap width='7%'><?php echo $siji_no ?></td>
                <td class='winbox' align='center' nowrap width='5%'>工程</td>
                <td class='winbox' align='center' nowrap width='2%'><?php echo $koutei ?></td>
                <td class='winbox' align='center' nowrap width='7%'>計画数</td>
                <td class='winbox' align='right'  nowrap width='7%'><?php echo number_format($plan_cnt) ?></td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        <!-- <hr color='797979'> -->
        <table align='left' border='2' cellspacing='0' cellpadding='0'>
            <form action='equip_work_chartList.php' method='post' target='List'>
                <tr>
                <td>
                    <input type='hidden' name='select' value='OK' >
                    <input style='font-size:10pt; color: red;' type='submit' name='reload' value='更新' <?php echo $reload?>>
                </td>
                <td>
                    <input style='font-size:10pt; color:blue;' type='submit' name='backward' value='前頁' <?php echo $reload?>>
                </td>
                </tr>
            </form>
        </table>
        <table class='item' width='78.6%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table width=100% class='winbox_field' border='1' cellspacing='1' cellpadding='1'>
            <th class='winbox' nowrap width='10%'>No</th>
            <th class='winbox' nowrap width='15%'>年月日</th>
            <th class='winbox' nowrap width='15%'>時分秒</th>
            <th class='winbox' nowrap width='15%'>状態</th>
            <th class='winbox' nowrap width='15%'>加工数</th>
            <th class='winbox' nowrap width='20%'>サイクル(秒)</th>
            <th class='winbox' nowrap width='10%'>分:秒</th>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
        <table align='right' border='2' cellspacing='0' cellpadding='0'>
            <form action='equip_work_chartList.php' method='post' target='List'>
                <tr><td>
                    <input type='hidden' name='select' value='OK' >
                    <input style='font-size:10pt; color:blue;' type='submit' name='forward' value='次頁' <?php echo $reload?>>
                </td></tr>
            </form>
        </table>
    </center>
</body>
</html>
<Script Language='JavaScript'>
    document.MainForm.select.value = '<?php echo $view?>';
    document.MainForm.target = 'List';
    document.MainForm.action = 'equip_work_chartList.php';
    document.MainForm.submit();
</Script>
<?php echo $menu->out_alert_java()?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
