<?php
//////////////////////////////////////////////////////////////////////////////
// 組立機械稼動管理システムの 運転 実績 状況表 表示  Headerフレーム         //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                          //
// 2021/03/26 Created  equip_chart_moniHeader.php                           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 6);                     // site_index=40(設備メニュー) site_id=6(実績照会)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

///// GET/POSTのチェック&設定
$mac_no  = @$_SESSION['mac_no'];
$plan_no = @$_SESSION['plan_no'];
$koutei  = @$_SESSION['koutei'];

////////////// 戻先に渡すパラメーター設定
// $menu->set_retGET('page_keep', 'on');   // name value の順で設定
// $menu->set_retGET('mac_no', $mac_no);   // name value の順で設定
$menu->set_retPOST('page_keep', 'on');   // name value の順で設定
$menu->set_retPOST('mac_no', $mac_no);   // name value の順で設定

///// ローカル変数の初期化
$mac_name   = '';
$parts_no   = '　';
$parts_name = '　';
$parts_mate = '　';
$plan_cnt   = '　';
$view       = 'NG';

if (isset($_POST['sort'])) {
    $sort  = $_POST['sort'];
} else {
    $sort       = 'ASC';
}

if ($mac_no != '') {
    //////////////// 機械マスターから機械名を取得
    $query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1";
    if (getUniResult($query, $mac_name) <= 0) {
        $mac_name = '　';   // error時は機械名をブランク
    }
    //////////// ヘッダーより開始日時と終了日時の取得
    $query = "select to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_timestamp
                , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as end_timestamp
                , plan_no
                , koutei
                , parts_no
                , plan_cnt
            from
                equip_work_log2_header_moni
            where
                mac_no={$mac_no} and plan_no='{$plan_no}' and koutei={$koutei}
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$mac_no}：{$mac_name}では運転開始されていません！";
    } else {
        $str_timestamp = $res_head[0]['str_timestamp'];
        $end_timestamp = $res_head[0]['end_timestamp'];
        $plan_no   = $res_head[0]['plan_no'];
        $koutei    = $res_head[0]['koutei'];
        $parts_no  = $res_head[0]['parts_no'];
        $plan_cnt  = $res_head[0]['plan_cnt'];
        $query = "select substr(midsc, 1, 12) as midsc, mzist from miitem where mipn='{$parts_no}'";
        $res_mi = array();
        if ( getResult($query, $res_mi) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no}で部品マスターの取得に失敗！";
        } else {
            $parts_name = $res_mi[0]['midsc'];
            $parts_mate = $res_mi[0]['mzist'];
            $_SESSION['work_mac_no']  = $mac_no;
            $_SESSION['work_plan_no'] = $plan_no;
            $_SESSION['work_koutei']  = $koutei;
            $view = 'OK';
        }
    }
}
// ページ移動の可否チェック
if ($view != 'OK') {
    $reload = 'disabled';
} else {
    $reload = '';
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("{$mac_no}　{$mac_name}　運転 実績表 照会");
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
    <input type='hidden' name='select'  value=''>
    <input type='hidden' name='mac_no'  value='<?php echo $mac_no ?>'>
    <input type='hidden' name='plan_no' value='<?php echo $plan_no ?>'>
    <input type='hidden' name='koutei'  value='<?php echo $koutei ?>'>
    <input type='hidden' name='sort'    value='<?php echo $sort ?>'>
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
                    <form name='mac_form' method='post' action='equip_chart_moniList.php' target='List'>
                        <select style='width:100%;' name='sort' class='ret_font' onChange='document.mac_form.submit()'>
                            <option value='ASC' <?php if ($sort == 'ASC') echo 'selected' ?>>  昇順ソート</option>
                            <option value='DESC' <?php if ($sort == 'DESC') echo 'selected' ?>>降順ソート</option>
                        </select>
                        <input type='hidden' name='mac_no'  value='<?php echo $mac_no ?>'>
                        <input type='hidden' name='plan_no' value='<?php echo $plan_no ?>'>
                        <input type='hidden' name='koutei'  value='<?php echo $koutei ?>'>
                        <input type='hidden' name='select'  value='<?php echo $view ?>'>
                    </form>
                </td>
                <td class='winbox' align='center' nowrap width='7%'>製品No</td>
                <td class='winbox' align='center' nowrap width='9%'><?php echo $parts_no ?></td>
                <td class='winbox' align='center' nowrap width='7%'>製品名</td>
                <td class='winbox pick_font' align='left' nowrap width='15%'><?php echo $parts_name ?></td>
                <td class='winbox' align='center' nowrap width='5%'>材質</td>
                <td class='winbox pick_font' align='center' nowrap width='9%'><?php echo $parts_mate ?></td>
                <td class='winbox' align='center' nowrap width='7%'>計画No</td>
                <td class='winbox' align='center' nowrap width='7%'><?php echo $plan_no ?></td>
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
            <form action='equip_chart_moniList.php' method='post' target='List'>
                <tr>
                <td>
                    <input style='font-size:10pt; color:blue;' type='submit' name='backward' value='前頁' <?php echo $reload?>>
                    <input type='hidden' name='select' value='OK' >
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
            <form action='equip_chart_moniList.php' method='post' target='List'>
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
document.MainForm.action = 'equip_chart_moniList.php';
document.MainForm.submit();
</Script>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
