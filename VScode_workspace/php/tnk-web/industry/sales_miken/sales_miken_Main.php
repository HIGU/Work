<?php
//////////////////////////////////////////////////////////////////////////////
// 売上分の日東工器 未検収分をダウンロードしてあるデータを照会する          //
// Copyright (C) 2004-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/04/30 Created  sales_miken_view.php                                 //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2005/02/08 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/08/20 set_focus()の機能は MenuHeader で実装しているので無効化した   //
// 2006/12/18 計画番号クリックで引当照会・登録クリックで登録画面への機能追加//
// 2006/12/21 標準・特注の識別のため項目追加                                //
// 2007/03/23 sales_miken_view.php → sales_miken_Main.phpへ(フレーム版)    //
// 2011/07/06 仕切単価の計算と未検収金額の表示を追加した               大谷 //
// 2012/09/05 2012/08の計画No.C8385407が特殊な処理をしたためデータが        //
//            残ってしまうので、PGM的に除外した。                      大谷 //
// 2015/05/21 機工生産に対応                                           大谷 //
// 2018/08/29 生産メニューと売上メニューで分離。訂正時は両方直すこと   大谷 //
// 2020/12/11 達成率の表示から来た時、リターンアドレスをセット         和氣 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');                // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');                // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');              // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(INDEX_INDUST, 30);          // site_index=30(生産メニュー) site_id=30(NK未検収明細)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
if( isset($_REQUEST['tassei']) ) {
    $menu->set_RetUrl(SALES . "sales_plan/sales_plan_view.php");
}
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('日東工器 製品完成納入分 未検収 明細');
//////////// 表題の設定
$menu->set_caption('組立完成分 未検収明細表');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('引当構成表の表示',   INDUST . 'material/allo_conf_parts_view.php');
// $menu->set_action('総材料費の登録',     INDUST . 'material/materialCost_entry.php');
//////////// リターンアドレスへのGETデーターセット
$menu->set_retGET('page_keep', 'on');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = $menu->set_useNotCache('miken');
//////////// テキストファイルから明細の取得及び合計レコード数取得(対象テーブルの最大数をページ制御に使用)
$file_orign    = '../..' . SYS . 'backup/W#TIUKSL.TXT';
$res           = array();
$total_price   = 0;
$total_price_c = 0;
$total_price_l = 0;
$total_price_t = 0;
$total_num     = 0;
$total_num_c   = 0;
$total_num_l   = 0;
$total_num_t   = 0;
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
            $total_num    += 1;
            if ($res[$rec][0] == 'C') {
                $total_price_c += $res[$rec][16];
                $total_num_c   += 1;
            } elseif ($res[$rec][0] == 'L') {
                $total_price_l += $res[$rec][16];
                $total_num_l   += 1;
            } else {
                $total_price_t += $res[$rec][16];
                $total_num_t   += 1;
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
$f_total_price   = number_format($total_price, 0);
$f_total_price_c = number_format($total_price_c, 0);
$f_total_price_l = number_format($total_price_l, 0);
$f_total_price_t = number_format($total_price_t, 0);
// 件数カウント
$f_total_num   = number_format($total_num, 0);
$f_total_num_c = number_format($total_num_c, 0);
$f_total_num_l = number_format($total_num_l, 0);
$f_total_num_t = number_format($total_num_t, 0);
//$menu->set_caption2("<u>カプラ未検収金額={$f_total_price_c}：リニア未検収金額={$f_total_price_l}：ツール未検収金額={$f_total_price_t}：合計未検収金額={$f_total_price}<u>");
$menu->set_caption2("<u>カプラ未検収件数={$f_total_num_c}件、金額={$f_total_price_c}円：リニア未検収件数={$f_total_num_l}件、金額={$f_total_price_l}円：合計未検収件数={$f_total_num}件、金額={$f_total_price}円<u>");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='sales_miken.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<!-- <script type='text/javascript' src='sales_miken.js?<?php echo $uniq ?>'></script> -->
</head>
<!-- background-color:#d6d3ce; -->
<body style='overflow-y:hidden;'>
<center>
<?=$menu->out_title_border()?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center' class='caption_font'>
                    <?= $menu->out_caption(), "\n" ?>
                    　
                    
                </td>
            </tr>
        </table>
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td nowrap align='center' class='caption_font'>
                    <?php echo $menu->out_caption2(), "\n" ?>
                </td>
            </tr>
        </table>
<?php
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='sales_miken_ViewHeader.html?{$uniq}' name='header' align='center' width='98%' height='32' title='項目'>\n";
echo "    項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='sales_miken_ViewBody.php?", $_SERVER['QUERY_STRING'], "&{$uniq}#last' name='list' align='center' width='98%' height='81%' title='一覧'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
?>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
