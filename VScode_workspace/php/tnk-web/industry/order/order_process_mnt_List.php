<?php
//////////////////////////////////////////////////////////////////////////////
// 発注工程メンテナンス(発注手順の保守)   Listフレーム                      //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/27 Created  order_process_mnt_List.php                           //
// 2004/11/30 ※ JavaScriptで delete キーワードは使えない！                 //
// 2005/02/10 $_REQUEST['sei_no']の適正チェックを追加                       //
// 2005/07/26 $menu->out_alert_java()が</html>の外にあったのを内側へ修正    //
//            注文番号頭6/7等の対応 order by に式を追加                     //
// 2006/10/06 各工程で注文書発行済みのものの打切りチェックをして表示        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function (define.phpを含む)
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
// $menu->set_site(30, 999);                // site_index=30(生産メニュー) site_id=999(未定)
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// 自分をフレーム定義に変える
// $menu->set_self(INDUST . 'order/order_process_mnt.php');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('運転グラフ', EQUIP2 . 'work/equip_work_graph.php');
// $menu->set_action('現在稼動表', EQUIP2 . 'work/equip_work_chart.php');
// $menu->set_action('スケジュール', EQUIP2 . 'plan/equip_plan_graph.php');
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('発注工程メンテナンスList');   // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり

///////// パラメーターチェック(基本的にセッションから取得)
if (isset($_REQUEST['sei_no'])) {
    $sei_no = $_REQUEST['sei_no'];
    if (is_numeric($sei_no)) {
        if (strlen($sei_no) == 7) {
            $view = 'OK';
        } else {
            $view = 'NG';
        }
    } else {
        $view = 'NG';
    }
} else {
    $sei_no = '';                           // 初期値(カプラ)あまり意味は無い
    $view = 'NG';
}
if (isset($_REQUEST['del_exec'])) {
    $order_no = $_REQUEST['order_no'];
    $vendor   = $_REQUEST['vendor'];
    $delete   = $_REQUEST['del_exec'];      // ※ JavaScriptで delete キーワードは使えない！
} else {
    $delete = '';
}

while ($delete) {
    $query = "select order_no from order_process where sei_no={$sei_no} and order_no={$order_no} and vendor='{$vendor}'";
    $res = array();
    if (($rows = getResult2($query, $res)) == 1) {
        $sql = "delete from order_process where sei_no={$sei_no} and order_no={$order_no} and vendor='{$vendor}'";
        if (query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '削除出来ませんでした！ 管理担当者へ連絡して下さい。';
        }
    } else {
        $_SESSION['s_sysmsg'] = "製造番号：{$sei_no} 注文番号：{$order_no} 発注先コード：{$vendor} で削除データが見つかりません！";
    }
    break;
}

while ($view == 'OK') {
    ////////// 共通SQL文を生成
    $query = "select proc.order_no          AS 注文番号
                  , proc.vendor             AS 発注先コード
                  , trim(substr(mast.name, 1, 8))
                                            AS 発注先名
                  , substr(to_char(proc.delivery, 'FM9999/99/99'), 3, 8)
                                            AS 納期
                  , proc.mtl_cond           AS 材料条件         -- 1=自給 2=有償 3=無償
                  , proc.pro_kubun          AS 工程単価区分     -- 1=継続 2=暫定 3=今回のみ 4=未定
                  , proc.pro_mark           AS 工程
                  , proc.order_price        AS 単価
                  , proc.locate             AS 納入場所
                  , proc.next_pro           AS 次工程
                  , proc.plan_cond          AS 注文書
                  , CASE
                        WHEN proc.plan_cond = 'O' AND (proc.order_q - proc.cut_siharai) <= 0 THEN '打切'
                        ELSE '&nbsp;'
                    END                     AS 打切
                  
                  , CASE
                          WHEN trim(plan.kouji_no) = '' THEN '&nbsp;'
                          ELSE trim(plan.kouji_no)
                    END                     AS 工事番号
                  , plan.order_q            AS 発注数
                  , proc.order_date         AS 発注日
                  , proc.kamoku             AS 科目             -- 科目(買掛科目1--9)
                  , proc.order_ku           AS 発注区分         -- (1=部品 2=追加工 3=製品/修正 4=素形材 5=治工具)
                  , proc.parts_no           AS 部品番号
                  , trim(substr(item.midsc, 1, 13))
                                            AS 部品名
                  , CASE
                          WHEN trim(item.mzist) = '' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                          ELSE substr(item.mzist, 1, 8)
                    END                     AS 材質
                  , CASE
                          WHEN trim(item.mepnt) = '' THEN '---'         --NULLでなくてスペースで埋まっている場合はこれ！
                          ELSE substr(item.mepnt, 1, 8)
                    END                     AS 親機種
            from
                order_process   AS proc
            left outer join
                order_plan      AS plan
                                        using(sei_no)
            left outer join
                vendor_master   AS mast
                                        on(proc.vendor = mast.vendor)
            left outer join
                miitem          AS item
                                        on(proc.parts_no = item.mipn)
            where
                proc.sei_no = {$sei_no}
            order by
                substr(to_char(proc.order_no, 'FM0000000'), 2, 7) ASC, proc.delivery ASC
            offset 0
            limit 20
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] = "製造番号：{$sei_no} ではデータがありません！";
        $view = 'NG';
    }
    break;
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
<style type='text/css'>
<!--
th {
    font-size:          11.5pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   #ffffc6;
    color:              blue;
}
table {
    font-size:      11pt;
    font-weight:    normal;
    /* font-family:    monospace; */
}
.item {
    position: absolute;
    /* top:   0px; */
    left:     0px;
}
.msg {
    position: absolute;
    top:  100px;
    left: 350px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
a {
    color: red;
}
a:hover {
    background-color: blue;
    color: white;
}
-->
</style>
<script language='JavaScript'>
<!--
function init() {
}
function winActiveChk() {
    if (document.all) {     // IEなら
        if (document.hasFocus() == false) {     // IE5.5以上で使える
            window.focus();
            return;
        }
        return;
    } else {                // NN ならとワリキッテ
        window.focus();
        return;
    }
    // 使用法 <body onLoad="setInterval('winActiveChk()',100)"> 又は <body onLoad='winActiveChk()'>
    // <input type='button' value='TEST' onClick="window.opener.location.reload()">
    // parent.Header.関数名() or オブジェクト;
}
function del_confirm(sei_no, order_no, vendor, vendor_name) {
    if (confirm('注文番号：' + order_no + '\n\n発注名：' + vendor_name + " の\n\n発注工程を削除します！\n\n宜しいですか？")) {
        // 実行します。
        document.del_form.sei_no.value   = sei_no;
        document.del_form.order_no.value = order_no;
        document.del_form.vendor.value   = vendor;
        document.del_form.del_exec.value = 'Execute';
        document.del_form.submit();
    } else {
        alert('取消しました。');
    }
}
// -->
</script>
<form name='del_form' method='post' action='<?=$menu->out_self()?>' target='_self'>
    <input type='hidden' name='sei_no'   value=''>
    <input type='hidden' name='order_no' value=''>
    <input type='hidden' name='vendor'   value=''>
    <input type='hidden' name='del_exec' value=''>
</form>
</head>
<body>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>製造番号を指定して下さい！</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- 詳細データ表示のための表を作成 -------------->
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='2'>
            <th class='winbox' nowrap width='30'>No</th>
            <th class='winbox' nowrap width='40'>削除</th>
            <th class='winbox' nowrap width='60' style='font-size:9.5pt;'>注文番号</th>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>コード</th>
            <th class='winbox' nowrap width='145'>発注先名</th>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>納 期</th>
            <th class='winbox' nowrap width='30' style='font-size:10.5pt;'>支給</th>
            <th class='winbox' nowrap width='30' style='font-size:10.5pt;'>区分</th>
            <th class='winbox' nowrap width='30' style='font-size:10.5pt;'>工程</th>
            <th class='winbox' nowrap width='60' style='font-size:10.5pt;'>単価</th>
            <th class='winbox' nowrap width='30' style='font-size:10.5pt;'>納入</th>
            <th class='winbox' nowrap width='70'>次工程</th>
            <th class='winbox' nowrap width='70'>注文書</th>
            <th class='winbox' nowrap width='60'>打切</th>
            <!--
            <th class='winbox' nowrap width='85'>材&nbsp;&nbsp;質</th>
            <th class='winbox' nowrap width='90'>親機種</th>
            <th class='winbox' nowrap width='20' style='font-size:10.5pt;'>工程</th>
            <th class='winbox' nowrap width='130'>発注先名</th>
            -->
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                if ($rec['打切'] != '打切') {
                    echo "<tr class='table_font'>\n";
                } else {
                    echo "<tr class='table_font' style='color:gray;'>\n";
                }
                echo "<td class='winbox' align='right'  width='30'  bgcolor='#d6d3ce'>{$i}</td>\n";
                echo "<td class='winbox' align='center' width='40'>\n";
                echo "  <input type='button' name='delete_chk' value='削除' style='color:red;' onClick='del_confirm(\"{$sei_no}\", \"{$rec['注文番号']}\", \"{$rec['発注先コード']}\", \"{$rec['発注先名']}\")'>\n";
                echo "</td>\n";
                echo "<td class='winbox' align='center' width='60'  bgcolor='#d6d3ce'>{$rec['注文番号']}</td>\n";
                echo "<td class='winbox' align='center' width='45'  bgcolor='#d6d3ce'>{$rec['発注先コード']}</td>\n";
                echo "<td class='winbox' align='center' width='145' bgcolor='#d6d3ce'>{$rec['発注先名']}</td>\n";
                echo "<td class='winbox' align='center' width='45'  bgcolor='#d6d3ce'>{$rec['納期']}</td>\n";
                echo "<td class='winbox' align='center' width='30'  bgcolor='#d6d3ce'>{$rec['材料条件']}</td>\n";
                echo "<td class='winbox' align='center' width='30'  bgcolor='#d6d3ce'>{$rec['工程単価区分']}</td>\n";
                echo "<td class='winbox' align='center' width='30'  bgcolor='#d6d3ce'>{$rec['工程']}</td>\n";
                echo "<td class='winbox' align='right'  width='60'  bgcolor='#d6d3ce'>", number_format($rec['単価'], 2), "</td>\n";
                echo "<td class='winbox' align='center' width='30'  bgcolor='#d6d3ce'>{$rec['納入場所']}</td>\n";
                echo "<td class='winbox' align='center' width='70'  bgcolor='#d6d3ce'>{$rec['次工程']}</td>\n";
                echo "<td class='winbox' align='center' width='70'  bgcolor='#d6d3ce'>{$rec['注文書']}</td>\n";
                echo "<td class='winbox' align='center' width='60'  bgcolor='#d6d3ce'>{$rec['打切']}</td>\n";
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
        
        <br>
        
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='2'>
            <tr>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'>部品番号</td>
                <td class='winbox' align='center' width='120'  bgcolor='#d6d3ce'><?=$res[0]['部品番号']?></td>
            </tr>
            <tr>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'>部品名</td>
                <td class='winbox' align='center' width='150'  bgcolor='#d6d3ce'><?=$res[0]['部品名']?></td>
            </tr>
            <tr>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'>材　質</td>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'><?=$res[0]['材質']?></td>
            </tr>
            <tr>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'>親機種</td>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'><?=$res[0]['親機種']?></td>
            </tr>
            <tr>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'>工事番号</td>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'><?=$res[0]['工事番号']?></td>
            </tr>
            <tr>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'>発注数</td>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'><?php echo number_format($res[0]['発注数']) ?></td>
            </tr>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush(); // 出力バッファをgzip圧縮 END
?>
