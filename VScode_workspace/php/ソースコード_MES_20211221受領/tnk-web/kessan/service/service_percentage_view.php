<?php
//////////////////////////////////////////////////////////////////////////////
// サービス割合 部門別 照会                                                 //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/10/24 Created   service_percentage_view.php                         //
//            JavaScriptで修正ボタン追加 locattion.replace(xx_input.php)    //
// 2004/04/19 前期の実績をグレーに変更                                      //
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

$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存

//////// 自分自身の呼出しの時はセッションに保存しない
if ( preg_match('/service_percentage_view.php/', $_SERVER['HTTP_REFERER']) ) {
    $url_referer = $_SESSION['service_view_referer'];
} else {
    $_SESSION['service_view_referer'] = $_SERVER['HTTP_REFERER'];       // 呼出もとのURLをセッションに保存
    $url_referer = $_SESSION['service_view_referer'];
}
////////////// リターンアドレス設定(絶対指定する場合)
$menu->set_RetUrl($url_referer);        // 上記の結果をセット
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

///////////// 対象部門の取得
if (isset($_POST['section_id'])) {
    $_SESSION['service_id']   = $_POST['section_id'];
    $_SESSION['section_name'] = $_POST['section_name'];
    $section_id   = $_POST['section_id'];
    $section_name = $_POST['section_name'];
} else {
    $section_id   = $_SESSION['service_id'];
    $section_name = $_SESSION['section_name'];
}

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
$menu_title = "$view_ym サービス割合 $section_name 部門 照会";
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title($menu_title);

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

//////////// 間接部門の明細(個人)を抜出し
$query = sprintf("select act.act_id as コード, s_name as 経理部門, cd.uid as 社員番号, d.name as 名前
        from cate_allocation left outer join act_table as act
            on dest_id=act.act_id
        left outer join cd_table as cd
            on act.act_id=cd.act_id
        left outer join user_detailes as d
            on cd.uid=d.uid
        where orign_id=0 and
            group_id=%d and
            act_flg='f'
        order by act.act_id",
        $section_id);
$res = array();
if (($rows = getResWithFieldTrs($con, $query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '間接部門明細が取得できません！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
    //////////// history 全体から使用されているフィールド名を抜出す group byの item_no,item ←がポイント
    $query = "select item, item_no, intext from service_percent_history
              where service_ym=$service_ym group by item_no, item, intext order by intext, item_no";
    if (($rows_item = getResultTrs($con, $query, $res_item)) <= 0) {
        $_SESSION['s_sysmsg'] = "サービス割合が入力されていません！<br>年月＝$service_ym";
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    } else {
        for ($i=$num; $i<($rows_item+$num); $i++) {
            $field[$i]        = $res_item[$i-$num][0];
            $item_no[$i-$num] = $res_item[$i-$num][1];
            $intext[$i]       = $res_item[$i-$num][2];
        }
        $field[$i] = '合　計';
        $num_p = count($field);     // フィールド数取得 num_p = num+の略
    }
    /********* 以下は service_percentage_input.php で使用していたロジック
    $query = "select item, item_no from service_item_master order by intext, item_no";
    if ( ($rows_item=getResultTrs($con, $query, $res_item)) <= 0) {
        $_SESSION['s_sysmsg'] = '直接部門のマスターが取得できません！';
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    } else {
        for ($i=$num; $i<($rows_item+$num); $i++) {
            $field[$i] = $res_item[$i-$num][0];
            // $item[$i-$num]    = $res_item[$i-$num][0];
            $item_no[$i-$num] = $res_item[$i-$num][1];
        }
        $field[$i] = '合　計';
        $num_p = count($field);     // フィールド数取得 num_p = num+の略
    }
    **********/
}

///////////// 前期実績を取得
for ($r=0; $r<$rows; $r++) {
    $zenki[$r]['合計'] = 0;
    for ($f=0; $f<$rows_item; $f++) {
        $query = sprintf("select percent from service_percent_history
                where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $zenki_ym . '32', $res[$r][0], $res[$r][2], $item_no[$f]);
        if (getUniResult($query, $res_user) > 0) {
            $zenki[$r][$f]      = ($res_user * 100);    // ％に変換
            $zenki[$r]['合計'] += $zenki[$r][$f];
        } else {
            $zenki[$r][$f] = 0;
        }
    }
}

/////////////////////// 照会用フォーム
for ($r=0; $r<$rows; $r++) {
    $percent[$r]['合計'] = 0;   // 初期化
    for ($f=0; $f<$rows_item; $f++) {
        ///// 登録済みのチェック
        $query = sprintf("select percent from service_percent_history
                where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $service_ym, $res[$r][0], $res[$r][2], $item_no[$f]);
        if (getUniResTrs($con, $query, $res_pert) > 0) {
            $percent[$r][$f]      = ($res_pert * 100);      // ％に変換
            $percent[$r]['合計'] += $percent[$r][$f];
        } else {
            $percent[$r][$f] = '';
        }
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
<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (("0" > c) || (c > "9")) {
            alert("数値以外は入力出来ません。");
            return false;
        }
    }
    return true;
}
// -->
</script>
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
    font-size:  10pt;
}
.pt10b {
    font:bold 10pt;
}
.pt10b {
    font-size:   10pt;
    font-weight: bold;
}
.pt11bR {
    font-size:   11pt;
    font-weight: bold;
    color: red;
    font-family: monospace;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
}
.pt12b {
    font-size:   12pt;
    font-weight: bold;
    font-family: monospace;
}
th {
    font-size:   9pt;
    font-weight: bold;
}
.title-font {
    font-size:   13.5pt;
    font-weight: bold;
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
.zenki {
    font-size:  10pt;
    color:      gray;
}
-->
</style>
</head>
<body>
    <center>
<?php echo $menu->out_title_border() ?>
        
            <form name='page_form' method='post' action='<?= $url_referer ?>'>
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <tr>
                <!--
                <td align='left'>
                    <table align='left' border='3' cellspacing='0' cellpadding='0'>
                        <td align='left'>
                            <input class='pt10b' type='button' name='backward' value='前頁'>
                        </td>
                    </table>
                </td>
                -->
                <td align='center'>
                    <!-- <?= $menu_title . "　単位：％\n" ?> -->
                    <table align='center' border='3' cellspacing='0' cellpadding='0'>
                        <td align='right' class='pt11b'>
                            <input class='pt11b' type='submit' name='save' value='ＯＫ'>　単位：％　
                            <input class='pt11b' type='button' name='repair' value='修正' 
                            onClick="JavaScript:location.replace('service_percentage_input.php?view=ret')">
                        </td>
                    </table>
                </td>
                <!--
                <td align='right'>
                    <table align='right' border='3' cellspacing='0' cellpadding='0'>
                        <td align='right'>
                            <input class='pt10b' type='button' name='forward' value='次頁'>
                        </td>
                    </table>
                </td>
                -->
            </tr>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th width='10' bgcolor='yellow'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num_p; $i++) {             // フィールド数分繰返し
                    if (isset($intext[$i])) {
                        if ($intext[$i] == 1) {            // 内作費(工場間接費)
                            echo "<th bgcolor='#ffcf9c' nowrap>{$field[$i]}</th>\n";
                        } elseif ($intext[$i] == 2) {      // 外作費(調達部門費)
                            echo "<th bgcolor='#ceceff' nowrap>{$field[$i]}</th>\n";
                        } else {                            // 部門や氏名等の見出し
                            echo "<th bgcolor='yellow' nowrap>{$field[$i]}</th>\n";
                        }
                    } else {
                        echo "<th bgcolor='yellow' nowrap>{$field[$i]}</th>\n";
                    }
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    echo "<tr>\n";
                        printf("<td class='pt10b' align='right'>%d</td>\n", $r + 1);    // 行番号の表示
                    for ($i=0; $i<$num_p; $i++) {       // レコード数分繰返し
                        if ($i == ($num_p - 1) ) {          // 合計なら
                            echo "    <td align='right' class='pt10b'>{$percent[$r]['合計']}</td>\n";
                        } elseif ( $i >= $num ) {           // 入力用フィールド
                            echo "<td align='right' class='pt10b'>{$percent[$r][$i-$num]}</td>\n";
                        } elseif ($res[$r][$i] != "") {     // 項目があれば
                            echo "<td align='center' class='pt10b'>{$res[$r][$i]}</td>\n";
                        } else {                            // 項目が無ければ
                            if ($i == 2) {
                                echo "<td align='center' class='pt10b'>経費のみ</td>\n";
                            } else {
                                echo "<td align='center' class='pt10b'>---</td>\n";
                            }
                        }
                    }
                    echo "</tr>\n";
                    echo "<tr>\n";
                    echo "    <td colspan='5' align='right' class='zenki'>\n";
                    echo "        前期実績({$zenki_ym}決算)\n";
                    echo "    </td>\n";
                    for ($j=0; $j<$rows_item; $j++) {
                        echo "    <td align='right' class='zenki'>{$zenki[$r][$j]}</td>\n";
                    }
                    echo "    <td align='right' class='zenki'>{$zenki[$r]['合計']}</td>\n";
                    echo "</tr>\n";
                }
                ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
            </form>
    </center>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
