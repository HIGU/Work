<?php
////////////////////////////////////////////////////////////////////////////////
// 機械稼動管理システムの 指示変更及びログ編集  ヘッダー部の定義              //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created  equip_edit_chartHeader.php                             //
//                                    -> monitaring_edit_chartHeader.php      //
// 2021/03/24 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(INDEX_EQUIP, 11);           // site_index=40(設備メニュー) site_id=11(計画変更)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

///// セッションよりデータ取得
$mac_no   = $_SESSION['mac_no'];
$plan_no1 = $_SESSION['plan_no'];
$koutei1  = $_SESSION['koutei'];
$_SESSION['plan_no1'] = $plan_no1;
$_SESSION['koutei1']  = $koutei1 ;

////////////// リターンアドレス設定
$menu->set_RetUrl(EQUIP2 . 'monitoring/monitoring_Main.php'); // 通常は指定する必要はない

////////////// 戻先はメンテナンスの計画変更画面 固定
$menu->set_retGET('selectMode', 'change');  // name value の順で設定
$menu->set_retGET('state', 'init');         // name value の順で設定

////////////// 戻先に渡すパラメーター設定
// $menu->set_retGET('page_keep', 'on');   // name value の順で設定
// $menu->set_retGET('mac_no', $mac_no);   // name value の順で設定
// $menu->set_retPOST('page_keep', 'on');   // name value の順で設定
// $menu->set_retPOST('mac_no', $mac_no);   // name value の順で設定

///// ローカル変数の初期化
$mac_name = '';
$parts_no1   = '　';
$parts_no2   = '　';
$parts_name1 = '　';
$parts_name2 = '　';
$parts_mate1 = '　';
$parts_mate2 = '　';
$plan_cnt1   = '　';
$plan_cnt2   = '　';
$jisseki1    = '　';
$jisseki2    = '　';
$plan_no2    = '　';
$koutei2     = '　';
$str_timestamp1 = '　';
$end_timestamp1 = '　';
$str_timestamp2 = '　';
$end_timestamp2 = '　';
$view = 'OK';

if (isset($_POST['sort'])) {
    $sort  = $_POST['sort'];
} else {
    $sort       = 'DESC';
}

while ($mac_no != '') {
    //////////////// 機械マスターから機械名を取得
    $query = "
        select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1
    ";
    if (getUniResult($query, $mac_name) <= 0) {
        $mac_name = '　';   // error時は機械名をブランク
    }
    //////////// ヘッダーより現在加工しているロットを取得
    $query = "
        select  to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_timestamp
            , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as end_timestamp
            , parts_no
            , plan_cnt
            , jisseki
        from
            equip_work_log2_header_moni
        where
            mac_no={$mac_no} and plan_no='{$plan_no1}' and koutei={$koutei1}
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$mac_no}：{$mac_name}では運転開始されていません！";
        $view = 'NG';
    } else {
        $str_timestamp1 = $res_head[0]['str_timestamp'];
        $end_timestamp1 = '　';
        // $end_timestamp1 = $res_head[0]['end_timestamp'];
        $parts_no1  = $res_head[0]['parts_no'];
        $plan_cnt1  = $res_head[0]['plan_cnt'];
        $jisseki1   = $res_head[0]['jisseki'];
        $query = "
            select substr(midsc, 1, 12) as midsc, mzist from miitem where mipn='{$parts_no1}'
        ";
        $res_mi = array();
        if ( getResult($query, $res_mi) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no1}で部品マスターの取得に失敗！";
            $view = 'NG';
        } else {
            $parts_name1 = $res_mi[0]['midsc'];
            $parts_mate1 = $res_mi[0]['mzist'];
        }
    }
    /////// ログデータより前のロットの計画番号と工程番号を取得 (完了品だけでなく中断品もある事に注意)
    $query = "
        SELECT plan_no, koutei
        FROM
            equip_work_log2_moni
        WHERE
            date_time < CAST('{$str_timestamp1}' AS TIMESTAMP) AND mac_no={$mac_no}
            AND
            date_time > (TIMESTAMP '{$str_timestamp1}' - INTERVAL '30 day') AND mac_no={$mac_no}
            AND
            (plan_no != '{$plan_no1}' or koutei != {$koutei1})
        ORDER BY date_time DESC, mac_no DESC, mac_state DESC
        LIMIT 1
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$mac_no}：{$mac_name}では実績データありません！";
        $view = 'NG';
        break;
    } else {
        $plan_no2   = $res_head[0]['plan_no'];
        $koutei2    = $res_head[0]['koutei'];
        $_SESSION['plan_no2'] = $plan_no2;           // equip_edit_chartList.phpに渡すために保存する
        $_SESSION['koutei2']  = $koutei2 ;           //  〃
    }
    //////////// ヘッダーより前のロットを取得 (完了品だけでなく中断品もある事に注意)
    $query = "
        select  to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_timestamp
            , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as end_timestamp
            , parts_no
            , plan_cnt
            , jisseki
        from
            equip_work_log2_header_moni
        where
            mac_no={$mac_no} and plan_no='{$plan_no2}' and koutei={$koutei2}
        limit 1
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$mac_no}：{$mac_name}では実績データありません！";
        $view = 'NG';
    } else {
        $str_timestamp2 = $res_head[0]['str_timestamp'];
        $end_timestamp2 = $res_head[0]['end_timestamp'];
        if ($end_timestamp2 == NULL) {      // 中断品
            $end_timestamp2 = '　';
        }
        $parts_no2  = $res_head[0]['parts_no'];
        $plan_cnt2  = $res_head[0]['plan_cnt'];
        $jisseki2   = $res_head[0]['jisseki'];
        $query = "
            select substr(midsc, 1, 12) as midsc, mzist from miitem where mipn='{$parts_no2}'
        ";
        $res_mi = array();
        if ( getResult($query, $res_mi) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no2}で部品マスターの取得に失敗！";
            $view = 'NG';
        } else {
            $parts_name2 = $res_mi[0]['midsc'];
            $parts_mate2 = $res_mi[0]['mzist'];
        }
    }
    break;
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("{$mac_no}　{$mac_name}　計画内容の編集");
//////////// 表題の設定
$menu->set_caption('機械番号の選択');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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
.cur_font {
    font-size:      11pt;
    font-family:    monospace;
    color:          blue;
}
.pre_font {
    font-size:      11pt;
    font-family:    monospace;
    color:          gray;
}
.pick_font {
    font-size:      9.5pt;
    font-weight:    bold;
    font-family: monospace;
}
th {
    background-color:yellow;
    color:          blue;
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.item {
    position: absolute;
    top:   120px;
    left:   40px;
}
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select'  value=''>
    <input type='hidden' name='mac_no'  value='<?= $mac_no ?>'>
    <input type='hidden' name='plan_no' value='<?= $plan_no2 ?>'>
    <input type='hidden' name='koutei'  value='<?= $koutei2 ?>'>
    <input type='hidden' name='sort'    value='<?= $sort ?>'>
</form>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        
        <!----------------- 見出しを表示 ------------------------>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
            <th nowrap>ソート</th><th nowrap>計画No</th><th nowrap>製品番号</th><th nowrap>製品名</th>
            <th nowrap>工程</th><th nowrap>計画数</th><th nowrap>実績数</th>
            <th nowrap>開始日時</th><th nowrap>終了日時</th>
            <!-- 現在加工中 -->
            <tr class='cur_font'>
                <td align='center' rowspan='2'>
                    <form name='mac_form' method='post' action='monitoring_edit_chartList.php' target='List'>
                        <select name='sort' class='ret_font' onChange='document.mac_form.submit()'>
                            <option value='ASC' <?php if ($sort == 'ASC') echo 'selected' ?>>  昇順</option>
                            <option value='DESC' <?php if ($sort == 'DESC') echo 'selected' ?>>降順</option>
                        </select>
                        <input type='hidden' name='mac_no'  value='<?= $mac_no ?>'>
                        <input type='hidden' name='plan_no' value='<?= $plan_no2 ?>'>
                        <input type='hidden' name='koutei'  value='<?= $koutei2 ?>'>
                        <input type='hidden' name='select'  value='<?= $view ?>'>
                    </form>
                </td>
                <td align='center' nowrap><?= $plan_no1 ?></td>
                <td align='center' nowrap><?= $parts_no1 ?></td>
                <td align='center' nowrap><?= $parts_name1 ?></td>
                <td align='center' nowrap><?= $koutei1 ?></td>
                <td align='right'  nowrap><?= number_format($plan_cnt1) ?></td>
                <td align='right'  nowrap><?= number_format($jisseki1) ?></td>
                <td align='center' nowrap><?= $str_timestamp1 ?></td>
                <td align='center' nowrap><?= $end_timestamp1 ?></td>
                <!--<td align='center' nowrap><?= $parts_mate1 ?></td>-->
            </tr>
            <!-- 前ロット(完了品) -->
            <tr class='pre_font'>
                <td align='center' nowrap><?= $plan_no2 ?></td>
                <td align='center' nowrap><?= $parts_no2 ?></td>
                <td align='center' nowrap><?= $parts_name2 ?></td>
                <td align='center' nowrap><?= $koutei2 ?></td>
                <td align='right'  nowrap><?= number_format($plan_cnt2) ?></td>
                <td align='right'  nowrap><?= number_format($jisseki2) ?></td>
                <td align='center' nowrap><?= $str_timestamp2 ?></td>
                <td align='center' nowrap><?= $end_timestamp2 ?></td>
                <!--<td align='center' nowrap><?= $parts_mate2 ?></td>-->
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        
        <!-- <hr color='797979'> -->
        
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table width=100% align='center' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='1'>
            <th nowrap width='50'>No</th>
            <th nowrap width='100'>年月日</th>
            <th nowrap width='100'>時分秒</th>
            <th nowrap width='100'>状態</th>
            <th nowrap width='80'>加工数</th>
            <th nowrap width='70'>計画No</th>
            <th nowrap width='150'>スタート位置変更</th>
            <th nowrap width='130'>数リセット位置</th>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
    </center>
</body>
</html>
<Script Language='JavaScript'>
document.MainForm.select.value = '<?=$view?>';
document.MainForm.target = 'List';
document.MainForm.action = 'monitoring_edit_chartList.php';
document.MainForm.submit();
</Script>
<?php ob_end_flush();   // 出力バッファをgzip圧縮 END ?>
