<?php
//////////////////////////////////////////////////////////////////////////////
// サービス割合処理 全体の割合(配賦率) 照会                                 //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/10/24 Created   service_percent_view_total.php                      //
// 2003/10/28 $per割合の計算結果を小数点以下４桁を５桁に変更100%の対策      //
//            number_formatを３桁から1桁へ変更                              //
// 2003/11/12 group by item_no,item,order_no order by order_no,item_no      //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2007/01/24 MenuHeaderクラス対応                                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(10,  5);                    // site_index=10(損益メニュー) site_id=5(サービス割合メニュー)
////////////// リターンアドレス設定(絶対指定する場合)
$menu->set_RetUrl($_SESSION['service_referer']);    // 分岐処理前に保存されている呼出元をセットする
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
$url_referer     = $_SESSION['service_referer'];    // 分岐処理前に保存されている呼出元をセットする

//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['service_ym'])) {
    $service_ym = $_SESSION['service_ym']; 
} else {
    $service_ym = date('Ym');        // セッションデータがない場合の初期値(前月)
    if (substr($service_ym,4,2) != 01) {
        $service_ym--;
    } else {
        $service_ym = $service_ym - 100;
        $service_ym = $service_ym + 11;   // 前年の12月にセット
    }
}

//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if (substr($service_ym,6,2) == '32') {
    $view_ym = substr($service_ym,0,6) . '決算';
} else {
    $view_ym = $service_ym;
}
$menu_title = "$view_ym サービス割合による配賦率 照会";
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title($menu_title);
//////////// 表題の設定
$menu->set_caption('製 造 経 費　間 接 費　の　配 賦 率　集 計');

///// 前半期末 年月の算出
$yyyy = substr($service_ym, 0,4);
$mm   = substr($service_ym, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
    $zenki_ym = $yyyy . '09';     // 期初年月
} elseif (($mm >= 10) && ($mm <= 12)) {
    $zenki_ym = $yyyy . '09';     // 期初年月
} else {
    $zenki_ym = $yyyy . '03';     // 期初年月
}

////////// データベースへの接続
if ( !($con = db_connect()) ) {
    $_SESSION['s_sysmsg'] = 'データベースに接続できません！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

//////////// 配賦率の全合計を抜出す
$query = "select sum(percent * 100)::int2 from service_percent_history where service_ym=$service_ym";
if (($rows = getUniResTrs($con, $query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '配賦率の全合計が取得できません！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
} else {
    $point_sum = $res;
}

//////////// item(直接部門)毎の合計を抜出す intext=1 工場間接費
$query = "select item_no, item, sum(percent * 100)::int2 from service_percent_history
          where service_ym=$service_ym and intext=1 group by item_no, item, order_no
          order by order_no, item_no";
if (($rows_fld1 = getResultTrs($con, $query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '直接部門毎の合計が取得できません！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
} else {
    $point1['小計'] = 0;                // 小計の初期化
    $per1['小計']   = 0;
    for ($i=0; $i<$rows_fld1; $i++) {
        $field1[$i] = $res[$i][1];      // フィールド名
        $point1[$i] = $res[$i][2];      // 合計配賦率
        $point1_f[$i] = number_format($point1[$i]);       // 合計配賦率表示用にフォーマット
        $point1['小計'] += $point1[$i];
        $point1_f['小計'] = number_format($point1['小計']);
        $per1[$i]   = Uround($point1[$i] / $point_sum, 5);  // 配賦率計算
        $per1_f[$i] = number_format($per1[$i] * 100, 1);    // % に変換して表示用にフォーマット
        $per1['小計'] += $per1[$i];
    }
    $per1_f['小計'] = number_format($per1['小計'] * 100, 1);    // % に変換して表示用にフォーマット
}
//////////// item(直接部門)毎の合計を抜出す intext=2 調達部門費 
$query = "select item_no, item, sum(percent * 100)::int2 from service_percent_history
          where service_ym=$service_ym and intext=2 group by item_no, item, order_no
          order by order_no, item_no";
if (($rows_fld2 = getResultTrs($con, $query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '直接部門毎の合計が取得できません！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
} else {
    $point2['小計'] = 0;                // 小計の初期化
    $per2['小計']   = 0;
    for ($i=0; $i<$rows_fld2; $i++) {
        $field2[$i] = $res[$i][1];      // フィールド名
        $point2[$i] = $res[$i][2];      // 合計配賦率
        $point2_f[$i] = number_format($point2[$i]);       // 合計配賦率表示用にフォーマット
        $point2['小計'] += $point2[$i];
        $point2_f['小計'] = number_format($point2['小計']);
        $per2[$i]   = Uround($point2[$i] / $point_sum, 5);  // 配賦率計算
        $per2_f[$i] = number_format($per2[$i] * 100, 1);    // % に変換して表示用にフォーマット
        $per2['小計'] += $per2[$i];
    }
    $per2_f['小計'] = number_format($per2['小計'] * 100, 1);    // % に変換して表示用にフォーマット
}
//////////// 合計パーセントの検証用の計算
$per_sum = $per1['小計'] + $per2['小計'];
$per_sum_f = number_format($per_sum * 100, 1);
$point_sum_f = number_format($point_sum);


//////////// 部門(act_id)及び item_no 毎の合計を抜出す(明細表示用)
$query = "select act_id, trim(s_name), item_no, item, sum(percent * 100)::int2
         from service_percent_history left outer join act_table using(act_id)
         where service_ym=$service_ym
         group by act_id, s_name, item_no, item, intext, order_no
         order by act_id, intext, order_no, item_no";
$res = array();
if (($rows = getResultTrs($con, $query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '間接部門明細が取得できません！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
} else {
    $_SESSION['s_sysmsg'] = '';     // 初期化
    ///// 直接部門のフィールド数毎にレコードを取得
    $res[-1][0] = '';   // スタート時の初期化
    $j = -1;            // インデックスの初期化
    for ($i=0; $i<$rows; $i++) {
        if ($res[$i-1][0] != $res[$i][0]) {
            $j++;
            $act_id[$j]   = $res[$i][0];
            $act_name[$j] = $res[$i][1];
            $k = 0;
            $act_poi[$j][$k]  = $res[$i][4];
        } else {
            $k++;
            $act_poi[$j][$k]  = $res[$i][4];
        }
    }
    $rows_mei = ($j + 1);
    ////////// 経理部門毎の合計ポイント数の計算
    $act_poisum1 = 0;
    $act_poisum2 = 0;
    $act_poisum  = 0;
    for ($r=0; $r<$rows_mei; $r++) {
        $act_poi[$r]['sum1'] = 0;
        for ($f=0; $f<$rows_fld1; $f++) {
            $act_poi[$r]['sum1'] += $act_poi[$r][$f];
        }
        $act_poi[$r]['sum2'] = 0;
        for ($f=$rows_fld1; $f<($rows_fld1+$rows_fld2); $f++) {
            $act_poi[$r]['sum2'] += $act_poi[$r][$f];
        }
        $act_poi_sum[$r] = $act_poi[$r]['sum1'] + $act_poi[$r]['sum2'];
        $act_poisum1 += $act_poi[$r]['sum1'];
        $act_poisum2 += $act_poi[$r]['sum2'];
        $act_poisum  += $act_poi_sum[$r];
    }
    ////////// 経理部門毎の配賦率の計算
    $act_persum1 = 0;   // 全体の工場間接費 合計 初期化
    $act_persum2 = 0;   // 全体の調達部門費 合計 初期化
    $act_persum  = 0;   // 全体の 合計 初期化
    for ($r=0; $r<$rows_mei; $r++) {
            ////////// 工場間接費
        $act_per[$r]['sum1'] = 0;
        for ($f=0; $f<$rows_fld1; $f++) {
            if ($act_poi[$r][$f] != 0) {
                $act_per[$r][$f] = Uround($act_poi[$r][$f] / $act_poi_sum[$r], 4);
            } else {
                $act_per[$r][$f] = 0;
            }
            $act_per[$r]['sum1'] += $act_per[$r][$f];
            $act_per_f[$r][$f] = number_format($act_per[$r][$f] * 100, 2);
        }
        $act_per_f[$r]['sum1'] = number_format($act_per[$r]['sum1'] * 100, 2);
            ////////// 調達部門費
        $act_per[$r]['sum2'] = 0;
        for ($f=$rows_fld1; $f<($rows_fld1+$rows_fld2); $f++) {
            if ($act_poi[$r][$f] != 0) {
                $act_per[$r][$f] = Uround($act_poi[$r][$f] / $act_poi_sum[$r], 4);
            } else {
                $act_per[$r][$f] = 0;
            }
            $act_per[$r]['sum2'] += $act_per[$r][$f];
            $act_per_f[$r][$f] = number_format($act_per[$r][$f] * 100, 2);
        }
        $act_per_f[$r]['sum2'] = number_format($act_per[$r]['sum2'] * 100, 2);
            ////////// act_id 毎の合計
        $act_per_sum[$r] = $act_per[$r]['sum1'] + $act_per[$r]['sum2'];
        $act_per_sum_f[$r] = number_format($act_per_sum[$r] * 100, 2);
            ////////// 全体の合計 計算
        $act_persum1 += $act_per[$r]['sum1'];
        $act_persum2 += $act_per[$r]['sum2'];
        $act_persum  += $act_per_sum[$r];
        $act_persum1_f = number_format($act_persum1 * 100, 2);
        $act_persum2_f = number_format($act_persum2 * 100, 2);
        $act_persum_f  = number_format($act_persum * 100, 2);
    }
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
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt10 {
    font:10pt;
}
.pt10b {
    font:bold 10pt;
}
.pt11bR {
    font:bold 11pt;
    color: red;
    font-family: monospace;
}
.pt11b {
    font:bold 9pt;
}
.ok_button {
    font:bold 11pt;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
th {
    font:bold 11pt;
}
.title-font {
    font:bold 13.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:none;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:1.0pt solid windowtext;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.explain_font {
    font-size: 8.5pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
}
-->
</style>
</head>
<body>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <form name='page_form' method='post' action='<?php echo $menu->out_retUrl() ?>'>
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <tr>
                <td align='center'>
                    <!-- <?php echo $menu->out_caption() . "　単位：％\n" ?> -->
                    <table align='center' border='3' cellspacing='0' cellpadding='0'>
                        <td align='right' class='ok_button'>
                            <input class='ok_button' type='submit' name='save' value=' ＯＫ '>　単位：％　
                        </td>
                    </table>
                </td>
            </tr>
        </table>
        
        <!--------------- ここから全体の配賦率を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr align='center' bgcolor='#beffbe'>
                    <td colspan='<?php echo $rows_fld1+$rows_fld2+7 ?>' class='pt11b'> <!-- colspanを過去20にしていた -->
                        <?php echo $menu->out_caption(), "\n" ?>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td rowspan='3' width='10' align='center' class='pt10' bgcolor='#ffcf9c'>工場間接費</td>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>直接</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ffcf9c' nowrap><?php echo $field1[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ffcf9c' nowrap>小　計</td>
                    <td rowspan='3' width='10' align='center' class='pt10' bgcolor='#ceceff'>調達部門費</td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>直接</td>
                    <?php for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ceceff' nowrap><?php echo $field2[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ceceff' nowrap>小　計</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>合　計</td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>率</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $per1_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $per1_f['小計'] ?></td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>率</td>
                    <?php for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $per2_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $per2_f['小計'] ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $per_sum_f ?></td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>集計</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $point1_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $point1_f['小計'] ?></td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>集計</td>
                    <?php for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $point2_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $point2_f['小計'] ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $point_sum_f ?></td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <br>
        
        <!--------------- ここから経理部門コード毎の明細を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr align='center' bgcolor='#beffbe'>
                    <td colspan='<?php echo $rows_fld1+$rows_fld2+7 ?>' class='pt11b'> <!-- colspanを過去20にしていた -->
                        製 造 経 費　間 接 費　の　配 賦 率 集 計　　明 細
                    </td>
                </tr>
                <tr>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>No</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>コード</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>部門名</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>　</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ffcf9c'><?php echo $field1[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ffcf9c'>小　計</td>
                    <?php for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ceceff'><?php echo $field2[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ceceff'>小　計</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>合　計</td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- フッターは合計を表示 -->
                <tr>
                    <td colspan='4' align='right' class='pt10' bgcolor='#ffffbe'>率　計</td>
                    <td colspan='<?php echo $rows_fld1+1 ?>' align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_persum1_f ?></td>
                    <td colspan='<?php echo $rows_fld2+1 ?>' align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_persum2_f ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $act_persum_f ?></td>
                </tr>
                <tr>
                    <td colspan='4' align='right' class='pt10' bgcolor='#ffffbe'>合　計</td>
                    <td colspan='<?php echo $rows_fld1+1 ?>' align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_poisum1 ?></td>
                    <td colspan='<?php echo $rows_fld2+1 ?>' align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_poisum2 ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $act_poisum ?></td>
                </tr>
            </TFOOT>
            <TBODY>
                <?php for ($r=0; $r<$rows_mei; $r++) { ?>
                <tr>
                    <td rowspan='2' nowrap align='center' class='pt10' bgcolor='#ffffbe'><?php echo ($r+1) ?></td>
                    <td rowspan='2' nowrap align='center' class='pt10' bgcolor='#ffffbe'><?php echo $act_id[$r] ?></td>
                    <td rowspan='2' nowrap align='center' class='pt10' bgcolor='#ffffbe'><?php echo $act_name[$r] ?></td>
                    <td width='10' align='center' class='pt10' bgcolor='#ffffbe'>率</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_per_f[$r][$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_per_f[$r]['sum1'] ?></td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_per_f[$r][$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_per_f[$r]['sum2'] ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $act_per_sum_f[$r] ?></td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffffbe'>計</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_poi[$r][$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_poi[$r]['sum1'] ?></td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_poi[$r][$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_poi[$r]['sum2'] ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $act_poi_sum[$r] ?></td>
                </tr>
                <?php } ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        </form>
    </center>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
