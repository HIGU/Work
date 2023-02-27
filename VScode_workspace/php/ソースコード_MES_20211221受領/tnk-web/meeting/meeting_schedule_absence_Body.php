<?php
////////////////////////////////////////////////////////////////////////////////////////////
// 会議帯 不在者をウィンドウ表示   Listフレーム                                           //
// Copyright (C) 2019-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                                        //
// 2019/03/15 Created  meeting_schedule_absence_Body.php                                  //
// 2020/08/07 千葉社長、増山担当部長を表示しないように追加（SQL内）                       //
////////////////////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('max_execution_time', 60);          // 最大実行時間=60秒 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');     // define.php と pgsql.php を require_once している
require_once ('../MenuHeader.php');   // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
// $menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(未定)
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// 自分をフレーム定義に変える
// $menu->set_self(INDUST . 'order/order_schedule.php');
//////////// 呼出先のaction名とアドレス設定
//$menu->set_action('在庫予定',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
//$menu->set_action('在庫経歴',   INDUST . 'parts/parts_stock_history/parts_stock_view.php');
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('不在者照会');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり

$date = date('Ymd');                    // 初期値(当日)例外発生の場合に対応

$view = 'OK';   // スタートはOKで行う

////////// 共通SQL文を生成 不在者（出勤打刻なし）
$query = "select 
                ud.name AS 氏名
                ,
                sm.section_name AS 所属
                ,
                substr(timepro, 33, 4) AS 出勤時間
                ,
                substr(timepro, 41, 4) AS 退勤時間
                ,
                ud.pid AS 職位コード
                ,
                substr(timepro, 173, 2) AS 不在理由
            from user_detailes ud
            LEFT OUTER JOIN     cd_table        AS ct    USING(uid)
            left outer join timepro_daily_data on uid=substr(timepro, 3, 6) and {$date}=substr(timepro, 17, 8),section_master sm,position_master pm
            where (substr(timepro, 33, 4)='0000' or substr(timepro, 33, 4) IS NULL or substr(timepro, 41, 4) !='0000') and ud.sid=sm.sid and ud.sid!=90 and ud.sid!=95 and ud.sid!=31 and ud.retire_date is null and ud.uid!='000000' and ud.uid!='002321' and ud.uid!='010367' and ud.uid!='012866' and ud.uid!='014699' and ud.uid!='023856' and ud.pid=pm.pid and ud.pid!=15 and ud.pid!=130
            ORDER BY ct.orga_id ASC, ud.pid DESC, ud.name ASC

";
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    
    $res[0]['氏名'] = 'なし';
    $res[0]['所属'] = 'なし';
    $res[0]['不在理由'] = '　';
    $num_res = count($res);
    
    //$_SESSION['s_sysmsg'] = '不在者がいません';
    //$view = 'NG';
} else {
    $num_res = count($res);
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<style type='text/css'>
<!--
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
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
a.link {
    color: blue;
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
    window.focus();
    return;
    /***** 以下の処理はsetInterval()を使用した場合に使う *****/
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
    // 使用法 <body onLoad="setInterval('winActiveChk()',100)">
    // <input type='button' value='TEST' onClick="window.opener.location.reload()">
    // parent.Header.関数名() or オブジェクト;
}
function inspection_recourse(order_seq, parts_no, parts_name) {
    if (confirm('部品番号：' + parts_no + '\n\n部品名称：' + parts_name + " の\n\n緊急部品 検査依頼の予約をします。\n\n宜しいですか？")) {
        // 実行します。
        document.inspection_form.order_seq.value = order_seq;
        document.inspection_form.submit();
    } else {
        alert('取消しました。');
    }
}
function vendor_code_view(vendor, vendor_name) {
    alert('発注先コード：' + vendor + '\n\n発注先名：' + vendor_name + '\n\n');
}
function input_details(comment) {
        alert('テスト' + comment + '\n\n');
}
function win_open(url) {
    var w = 900;
    var h = 680;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win2', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>
<form name='inspection_form' method='get' action='../inspection_recourse_regist.php' target='_self'>
    <input type='hidden' name='retUrl' value='<?php echo $menu->out_self(), '?' . $_SERVER['QUERY_STRING'] ?>'>
    <input type='hidden' name='order_seq' value=''>
</form>
</head>
<body onLoad='winActiveChk()'>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>データがありません！</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- 詳細データ表示のための表を作成 -------------->
        <table class='item' width=100% bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='3'>
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                //echo "<tr class='table_font' onDblClick='inspection_recourse(\"{$rec['発行連番']}\",\"{$rec['部品番号']}\",\"{$rec['部品名']}\")'>\n";
                /*
                if ($date == 'OLD') {
                */
                    // 製造No.と部品番号よりコメントを取得
                    echo "<tr>\n";
                    echo "<td class='winbox' align='right'  width=' 10%'  bgcolor='#d6d3ce'>{$i}</td>\n";
                    echo "<td class='winbox' align='center' width=' 30%'  bgcolor='#d6d3ce'>{$rec['氏名']}</td>\n";
                    echo "<td class='winbox' align='center' width=' 45%'  bgcolor='#d6d3ce'>{$rec['所属']}</td>\n";
                    //echo "<td class='winbox' align='center' width=' 45%'  bgcolor='#d6d3ce'>{$rec['不在理由']}</td>\n";
                    
                    if($rec['退勤時間'] != '0000') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>退勤</td>\n";
                    } elseif($rec['不在理由'] == '11') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>有給</td>\n";
                    } elseif($rec['不在理由'] == '12') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>欠勤</td>\n";
                    } elseif($rec['不在理由'] == '13') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>無欠</td>\n";
                    } elseif($rec['不在理由'] == '14') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>出張</td>\n";
                    } elseif($rec['不在理由'] == '15') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>振休</td>\n";
                    } elseif($rec['不在理由'] == '16') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>特休</td>\n";
                    } elseif($rec['不在理由'] == '17') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>慶事</td>\n";
                    } elseif($rec['不在理由'] == '18') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>弔事</td>\n";
                    } elseif($rec['不在理由'] == '19') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>産休</td>\n";
                    } elseif($rec['不在理由'] == '20') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>育休</td>\n";
                    } elseif($rec['不在理由'] == '21') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>生休</td>\n";
                    } elseif($rec['不在理由'] == '22') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>休職</td>\n";
                    } elseif($rec['不在理由'] == '23') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>労災</td>\n";
                    } elseif($rec['出勤時間'] == '0000') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>不在</td>\n";
                    } else {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>　</td>\n";
                    }
                    
                    echo "</tr>\n";
                /*
                } else {
                    echo "<td class='winbox' align='right'  width=' 5%'  bgcolor='#d6d3ce'>{$i}</td>\n";
                    echo "<td class='winbox' align='center' width=' 7%'  bgcolor='#d6d3ce'>{$rec['発行日']}</td>\n";
                    echo "<td class='winbox' align='center' width='10%'  bgcolor='#d6d3ce'>{$rec['製造番号']}</td>\n";
                    echo "<td class='winbox' align='center' width='13%'  bgcolor='#d6d3ce' onClick='win_open(\"{$menu->out_action('在庫予定')}?showMenu=CondForm&noMenu=yes&targetPartsNo=" . urlencode($rec['部品番号']) . "\");'>\n";
                    echo "    <a class='link' href='javascript:void(0);' target='_self' style='text-decoration:none;'>{$rec['部品番号']}</a></td>\n";
                    echo "<td class='winbox' align='left'   width='16%' bgcolor='#d6d3ce'>" . mb_convert_kana($rec['部品名'], 'k') . "</td>\n";
                    echo "<td class='winbox' align='left'   width='10%'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['材質'], 'k') . "</td>\n";
                    echo "<td class='winbox' align='left'   width='10%'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['親機種'], 'k') . "</td>\n";
                    echo "<td class='winbox' align='right'  width='10%'  bgcolor='#d6d3ce'>" . number_format($rec['注文数'], 0) . "</td>\n";
                    echo "<td class='winbox' align='center' width=' 4%'  bgcolor='#d6d3ce'>{$rec['工程']}</td>\n";
                    echo "<td class='winbox' align='left'   width='15%' bgcolor='#d6d3ce' onClick='vendor_code_view(\"{$rec['発注先コード']}\",\"{$rec['vendor_name']}\")'>{$rec['発注先名']}</td>\n";
                    echo "</tr>\n";
                }
                */
            }
        /*
        if ($date == 'OLD') {       // 納期遅れの場合コメント登録・修正ボタンを表示する
        */
        ?>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
</html>
<?php echo $menu->out_alert_java()?>
<?php $_SESSION['s_sysmsg'] = ''; ?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
