<?php
//////////////////////////////////////////////////////////////////////////////
// 売上分の日東工器 未検収分をダウンロードしてあるデータを照会する          //
// Copyright (C) 2004-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/04/30 Created  sales_miken_view.php                                 //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2005/02/08 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/08/20 set_focus()の機能は MenuHeader で実装しているので無効化した   //
// 2006/12/18 計画番号クリックで引当照会・登録クリックで登録画面への機能追加//
// 2006/12/21 標準・特注の識別のため項目追加                                //
// 2007/03/23 sales_miken_view.php → sales_miken_ViewBody.phpへ(フレーム版)//
// 2007/03/24 material/allo_conf_parts_view.php →                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
// 2007/03/27 製品名に予想以上の長い名称があるため nowrap を削除して２段へ  //
// 2007/06/19 登録画面を material/materialCost_entry.php →                 //
//                        material_entry/materialCost_entry_main.php 大谷   //
// 2007/09/04 総材料費の経歴照会を追加assy_noそれに伴いrecNoによる行マークへ//
// 2007/09/05 materialCost_view_assy.phpに引数plan_noを追加 小林            //
// 2009/08/19 白河AS開始に伴いW#TIUKSLに白河分が入ってしまった為            //
//            事業区分がC・L・U以外表示しないように変更                大谷 //
// 2011/07/06 仕切単価の計算と未検収金額の表示を追加した               大谷 //
// 2012/09/05 2012/08の計画No.C8385407が特殊な処理をしたためデータが        //
//            残ってしまうので、PGM的に除外した。                      大谷 //
// 2018/08/29 生産メニューと売上メニューで分離。訂正時は両方直すこと   大谷 //
// 2021/12/07 リニアでもSC～が入っていると特注になってしまうのを修正   大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');                // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');                // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');              // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK 全共通 MVC Controller Class

///// セッションのインスタンスを登録
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
// access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(INDEX_INDUST, 30);             // site_index=30(生産メニュー) site_id=30(NK未検収明細)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('日東工器 製品完成納入分 未検収 明細');
//////////// 表題の設定
$menu->set_caption('組立完成分 未検収明細表');
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須
//////////// 自分をフレーム定義に変える
$menu->set_self(INDUST . 'sales_miken/sales_miken_Main.php');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('引当構成表の表示',   INDUST . 'material/allo_conf_parts_view.php');
$menu->set_action('引当構成表の表示',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
$menu->set_action('総材料費用引当構成表の表示',   INDUST . 'parts/allocate_config_entry/allo_conf_parts_Main.php');
$menu->set_action('総材料費の登録',     INDUST . 'material/material_entry/materialCost_entry_main.php');
$menu->set_action('総材料費の履歴',     INDUST . 'material/materialCost_view_assy.php');
//////////// リターンアドレスへのGETデーターセット
$menu->set_retGET('page_keep', 'on');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = $menu->set_useNotCache('miken');

$_SESSION['miken_referer'] = H_WEB_HOST . $menu->out_self();     // 呼出もとのURLをセッションに保存

//////////// 一頁の行数
define('PAGE', '200');      // とりあえず

//////////// テキストファイルから明細の取得及び合計レコード数取得(対象テーブルの最大数をページ制御に使用)
$file_orign    = '../..' . SYS . 'backup/W#TIUKSL.TXT';
$res           = array();
$total_price   = 0;
$total_price_c = 0;
$total_price_l = 0;
$total_price_t = 0;
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp = fopen($file_orign, 'r');
    $rec = 0;       // レコード№
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 130, '_');     // 実レコードは103バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $num  = count($data);       // フィールド数の取得
        if ($num != 14) {   // AS側の削除レコードは php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $res[$rec][$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $res[$rec][$f] = addslashes($res[$rec][$f]);    // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);   // 半角カナを全角カナに変換
        }
        if($res[$rec][5] !='C8385407') {
            $query = sprintf("select midsc from miitem where mipn='%s' limit 1", $res[$rec][3]);
            getUniResult($query, $res[$rec][4]);       // 製品名の取得 (製品コードを上書きする)
            /******** 総材料費の登録済みの項目追加 *********/
            $sql = "
                SELECT plan_no FROM material_cost_header WHERE plan_no='{$res[$rec][5]}'
            ";
            if (getUniResult($sql, $temp) <= 0) {
                $res[$rec][13] = '登録';
                $sql_c = "
                    SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                ";
                if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                } else {
                }
            } else {
                $res[$rec][13] = '登録済';
                $sql_c = "
                    SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                ";
                if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                } else {
                }
            }
            /******** 特注・標準の項目追加 *********/
            if ($res[$rec][0] == 'C') {
                $sql2 = "
                    SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$res[$rec][5]}'
                ";
                $sc = '';
                getUniResult($sql2, $sc);
                if ($sc == 'SC') {
                    $res[$rec][15] = '特注';
                } else {
                    $res[$rec][15] = '標準';
                }
            } else {
                $res[$rec][15] = '標準';
            }
            /******** 仕切単価が元データにない場合の上書き処理 *********/
            if ($res[$rec][12] == 0) {                                  // 元データに仕切があるかどうか
                $res[$rec][14] = '1';
                $sql = "
                    SELECT price FROM sales_price_nk WHERE parts_no='{$res[$rec][3]}'
                ";
                if (getUniResult($sql, $sales_price) <= 0) {            // 最新仕切が登録されているか
                    $sql = "
                        SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                    ";
                    if (getUniResult($sql, $sales_price) <= 0) {        // 計画の総材料費が登録されているか
                        $sql_c = "
                            SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                        ";
                        if (getUniResult($sql, $sales_price) <= 0) {    // 製品の総材料費が登録されているか
                            $res[$rec][12] = 0;
                        } else {
                            if ($res[$rec][15] == '特注') {
                                $res[$rec][12] = round(($sales_price * 1.27), 2);   // 特注のときの倍率？
                            } else {
                                $res[$rec][12] = round(($sales_price * 1.13), 2);
                            }
                        }
                    } else {
                        if ($res[$rec][15] == '特注') {
                            $res[$rec][12] = round(($sales_price * 1.27), 2);       // 特注のときの倍率？
                        } else {
                            $res[$rec][12] = round(($sales_price * 1.13), 2);
                        }
                    }
                } else {
                    $res[$rec][12] = $sales_price;
                }
            } else {
                $res[$rec][14] = '0';
            }
            /******** 集計 計算 *********/
            $res[$rec][16] = round(($res[$rec][11] * $res[$rec][12]), 0);
            $total_price  += $res[$rec][16];
            if ($res[$rec][0] == 'C') {
                $total_price_c += $res[$rec][16];
            } elseif ($res[$rec][0] == 'L') {
                $total_price_l += $res[$rec][16];
            } else {
                $total_price_t += $res[$rec][16];
            }
            $rec++;
        }
    }
    $maxrows = $rec;
    $rec    -= 1;
    $rows    = $maxrows;    // 今回は合計レコード数と表示用レコード数は同じ
    $field   = array(0=>'事業部', 1=>'完成日', 3=>'製品番号', 4=>'製品名', 5=>'計画番号', 11=>'完成数', 12=>'仕切単価');
} else {
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    $_SESSION['s_sysmsg'] .= '未検収明細のファイルがありません！';  // .= メッセージを追加する
    exit();
}
//////////// ページオフセット設定(offsetは使用する時に名前を変更 例：sales_offset)
$offset = $session->get_local('offset');
if ($offset == '') $offset = 0;         // 初期化
if ( isset($_REQUEST['forward']) ) {                       // 次頁が押された
    $offset += PAGE;
    if ($offset >= $maxrows) {
        $offset -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // 次頁が押された
    $offset -= PAGE;
    if ($offset < 0) {
        $offset = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {               // 現在のページを維持する
    $offset = $offset;
} else {
    $offset = 0;                            // 初回の場合は０で初期化
    $session->add_local('recNo', '-1');     // 0レコードでマーカー表示してしまうための対応
}
$session->add_local('offset', $offset);

///////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<link rel='stylesheet' href='sales_miken.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<!-- <script type='text/javascript' src='sales_miken.js?<?php echo $uniq ?>'></script> -->

<script type='text/javascript'>
<!--
/* 入力文字が数字かどうかチェック(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* 入力文字がアルファベットかどうかチェック isDigit()の逆 */
function isABC(str) {
    // var str = str.toUpperCase();    // 必要に応じて大文字に変換
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // スペースはOK
            return false;
        }
    }
    return true;
}

/* 入力文字が数字かどうかチェック 小数点対応 */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1個目かチェック
                cnt_dot++;
            } else {
                return false;       // 2個目は false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
 -->

<style type="text/css">
<!--
body {
    background-image:   none;
    overflow-x:         hidden;
    overflow-y:         scroll;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <?php
            for ($r=0; $r<$rows; $r++) {
                if (($res[$r][0] == 'C') || ($res[$r][0] == 'L') || ($res[$r][0] == 'T') || ($res[$r][0] == 'U')) {
                    $recNo = ($offset + $r);
                    if ($session->get_local('recNo') == $recNo) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                        echo "    <td class='winbox' width=' 5%' nowrap align='right'><div class='pt10b'><a name='last' style='color:black;'>", ($r + $offset + 1), "</a></div></td>    <!-- 行ナンバーの表示 -->\n";
                    } else {
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                        echo "    <td class='winbox' width=' 5%' nowrap align='right'><div class='pt10b'>", ($r + $offset + 1), "</div></td>    <!-- 行ナンバーの表示 -->\n";
                    }
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                        switch ($i) {
                        case 0:     // 事業部
                            echo "<td class='winbox' width=' 7%' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 1:     // 完成日
                            echo "<td class='winbox' width=' 8%' nowrap align='center'><div class='pt9'>", format_date($res[$r][$i]), "</div></td>\n";
                            break;
                        case 3:     // 製品番号
                            echo "<td class='winbox' width=' 9%' nowrap align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"sales_miken_ViewBody.php?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費の履歴'), "?assy=", urlencode($res[$r][$i]), "&material=1&plan_no=", urlencode($res[$r][5]), "\")' target='_parent' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                            break;
                        case 4:     // 製品名
                            echo "<td class='winbox' width='33%' align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 5:     // 計画番号
/*
                            if( $_SESSION['User_ID'] == '300667' || $_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '970352' ) {
                                echo "<td class='winbox' width=' 9%' nowrap align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"sales_miken_ViewBody.php?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費用引当構成表の表示'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='_parent' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                            } else {
                                echo "<td class='winbox' width=' 9%' nowrap align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"sales_miken_ViewBody.php?recNo={$recNo}\");location.replace(\"", $menu->out_action('引当構成表の表示'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='_parent' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                            }
*/
                            echo "<td class='winbox' width=' 9%' nowrap align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"sales_miken_ViewBody.php?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費用引当構成表の表示'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='_parent' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                            break;
                        case 11:    // 完成数
                            echo "<td class='winbox' width=' 7%' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 12:    // 仕切単価
                            if ($res[$r][14] == '0') {
                                echo "<td class='winbox' width=' 9%' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            } else {
                                echo "<td class='winbox' width=' 9%' nowrap align='right' style='color:brown;'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            }
                            break;
                        default:
                            break;
                        }
                    // <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                        echo "<td class='winbox' width=' 7%' nowrap align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"sales_miken_ViewBody.php?recNo={$recNo}\");location.replace(\"", $menu->out_action('総材料費の登録'), "?plan_no=", urlencode($res[$r][5]), "&assy_no=", urlencode($res[$r][3]), "&miken_referer=", $_SESSION['miken_referer'], "\")' target='_parent' style='text-decoration:none;'>{$res[$r][13]}</a></td>\n";
                        echo "<td class='winbox pt9' width=' 6%' nowrap align='center'>{$res[$r][15]}</td>\n";
                    echo "</tr>\n";
                }
            }
            ?>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
