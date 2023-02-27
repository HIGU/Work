<?php
//////////////////////////////////////////////////////////////////////////////
// 新JIS対象製品 生産実績照会 new_jis_sales_view.php                        //
// Copyright (C) 2014 - 2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2014/12/01 Created   new_jis_sales_view.php                              //
// 2014/12/02 デザインの微調整                                              //
// 2014/12/08 コピー・貼付した時に、行がずれるのを修正                      //
// 2014/12/22 形式→型式へ変更                                              //
// 2017/04/27 各メニューの表示より『新JIS』を削除                      大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');            // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');            // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
//////////// セッションのインスタンスを登録
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

$result  = new Result;

////////////// サイト設定
//$menu->set_site( 1, 11);                    // site_index=01(売上メニュー) site_id=11(売上実績明細)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('対象製品 生産実績照会');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費照会',   INDUST . 'material/materialCost_view.php');
$menu->set_action('単価登録照会',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('総材料費履歴',   INDUST . 'material/materialCost_view_assy.php');
$menu->set_action('売上明細',   SALES . 'details/sales_view.php');
$menu->set_action('買掛実績表示',     INDUST . 'payable/act_payable_view.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

/////////////// 受け渡し変数の初期化
if ( isset($_SESSION['s_uri_passwd']) ) {
    $_REQUEST['uri_passwd'] = $_SESSION['s_uri_passwd'];
} else {
    $uri_passwd = '';
}
if ( isset($_SESSION['s_d_start']) ) {
    if ( !isset($_REQUEST['d_start']) ) {
        $_REQUEST['d_start'] = $_SESSION['s_d_start'];
    }
} else {
    if ( isset($_POST['d_start']) ) {
        $d_start = $_POST['d_start'];
    } else {
        $d_start = date_offset(1);
    }
}
if ( isset($_SESSION['s_d_end']) ) {
    if ( !isset($_REQUEST['d_end']) ) {
        $_REQUEST['d_end'] = $_SESSION['s_d_end'];
    }
} else {
    if ( isset($_POST['d_end']) ) {
        $d_end = $_POST['d_end'];
    } else {
        $d_end = date_offset(1);
    }
}
if ( isset($_SESSION['s_kubun']) ) {
    $_REQUEST['kubun'] = $_SESSION['s_kubun'];
} else {
    $kubun = '';
}

//////////// 初回時のセッションデータ保存   次頁・前頁を軽くするため
//if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0レコードでマーカー表示してしまうための対応
    $_SESSION['s_uri_passwd'] = $_REQUEST['uri_passwd'];
    $_SESSION['s_d_start']    = $_REQUEST['d_start'];
    $_SESSION['s_d_end']      = $_REQUEST['d_end'];
    $_SESSION['s_kubun']      = $_REQUEST['kubun'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
    $kubun      = $_SESSION['s_kubun'];
        ///// day のチェック
        if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
        ///// 最終日をチェックしてセットする
        if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
            $d_start = ( substr($d_start, 0, 6) . last_day(substr($d_start, 0, 4), substr($d_start, 4, 2)) );
            if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
                $_SESSION['s_sysmsg'] = '日付の指定が不正ですz！';
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
                exit();
            }
        }
        ///// day のチェック
        if (substr($d_end, 6, 2) < 1) $d_end = substr($d_end, 0, 6) . '01';
        ///// 最終日をチェックしてセットする
        if (!checkdate(substr($d_end, 4, 2), substr($d_end, 6, 2), substr($d_end, 0, 4))) {
            $d_end = ( substr($d_end, 0, 6) . last_day(substr($d_end, 0, 4), substr($d_end, 4, 2)) );
            if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
                $_SESSION['s_sysmsg'] = '日付の指定が不正ですz！';
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
                exit();
            }
        }
    $_SESSION['s_d_start'] = $d_start;
    $_SESSION['s_d_end']   = $d_end  ;
    
    ////////////// パスワードチェック
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>パスワードが違います！</font>";
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }
    //////////// SQL where 句を 共用する
    $search = "where 計上日>=$d_start and 計上日<=$d_end";
    $search .= " and datatype='1'";
    $_SESSION['sales_search'] = $search;        // SQLのwhere句を保存
//}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = " ";
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$kubun      = '1';
$search     = $_SESSION['sales_search'];
$customer   = " ";

///////////// 型式と型式コードを取得
$query_g = sprintf("select
                        s.newjis_group_name         as 型式,            -- 0
                        s.newjis_apply_code         as 申請コード,      -- 1
                        s.newjis_kind_name          as 種類,            -- 2
                        s.newjis_certification_code as 形式認証番号,    -- 3
                        s.newjis_period_ym          as 有効期限,        -- 4
                        s.newjis_group_code         as 型式コード       -- 5
                  from
                        new_jis_select_master as s
                  order by newjis_group_code
                  ");
$res_g   = array();
$field_g = array();
if (($rows_g = getResultWithField3($query_g, $field_g, $res_g)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>型式の登録がありません。<br>%s～%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num_g = count($res_g);     // データ数取得
}

function get_assy_no($top_code, $search_middle, $result) {
    $query_ga = sprintf("select
                        assy_no as 製品番号       -- 0
                  from
                        new_jis_item_master
                  WHERE newjis_group_code=%d
                  order by assy_no
                  ", $top_code);   // 共用 $search で検索
    $field_ga = array();
    if (($rows_ga = getResultWithField3($query_ga, $field_ga, $res_ga)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>製品番号が登録されていません</font>");
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        //exit();
        $num_ga = count($res_ga);       // データ数取得
        $result->add_array2('data_assy', $res_ga);
        $result->add('num_ga', $num_ga);
        
        $group_assy = array();
        $data_middle_num = 0;
        $group_assy[0][0] = '　';
        $group_assy[0][1] = '　';
        $num_m = 1;       // データ数取得
        $result->add_array2('data_middle', $group_assy);
        $result->add('num_m', $num_m);
        $result->add('data_middle_num', $data_middle_num);
    } else {
        $num_ga = count($res_ga);       // データ数取得
        $assy_num = 0;
        $group_assy = array();
        for ($r=0; $r<$rows_ga; $r++) {
            $group_name = trim($res_ga[$r][0]);
            //$group_name = 'CB02189';
            $search_a = "where mipn like '{$group_name}%%'";
            $query_a = sprintf("
                    select
                            mipn  as 製品番号  -- 0
                    from
                            miitem
                    %s
                    ", $search_a);   // 共用 $search で検索
            $res_a   = array();
            $field_a = array();
            if (($rows_a = getResultWithField3($query_a, $field_a, $res_a)) <= 0) {
                //$group_assy[$assy_num][0]='CP22066-E';
                //$group_assy[$assy_num][1]=10;
            } else {
                $num_a = count($res_a);       // データ数取得
                for ($s=0; $s<$rows_a; $s++) {
                    //$search_middle .= " and assyno='$res_a[$s][0]'";
                    $query_m = sprintf("select
                            sum(数量) as 数量                       -- 0
                      from
                            hiuuri
                      left outer join
                            assembly_schedule as a
                      on 計画番号=plan_no
                      %s
                      and assyno='%s'
                      ", $search_middle, $res_a[$s][0]);   // 共用 $search で検索
                    $field_m = array();
                    if (($rows_m = getResultWithField3($query_m, $field_m, $res_m)) <= 0) {
                        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>中分類が登録されていません</font>");
                        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
                        //exit();
                        $group_assy[$assy_num][0] = $res_a[$s][0];
                        $group_assy[$assy_num][1] = 0;
                        $assy_num = $assy_num + 1;
                    } else {
                        $group_assy[$assy_num][0] = $res_a[$s][0];
                        $group_assy[$assy_num][1] = $res_m[0][0];
                        $assy_num = $assy_num + 1;
                    }
                }
            }
            
        }
        $data_middle_num = 0;
        for ($r=0; $r<$assy_num; $r++) {
            $data_middle_num += $group_assy[$r][1];
        }
        $num_m = count($group_assy);       // データ数取得
        $result->add_array2('data_middle', $group_assy);
        $result->add('num_m', $num_m);
        $result->add('data_middle_num', $data_middle_num);
    }
}
//////////// 表題の設定
//$ft_kingaku = number_format($data_top_t);                    // ３桁ごとのカンマを付加
//$ft_suryo   = number_format($data_top_nt);                   // ３桁ごとのカンマを付加
//$ft_ken     = number_format($t_ken);
//$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // 日付を / でフォーマット
$f_d_end    = format_date($d_end);
$menu->set_caption("対象年月 {$f_d_start}～{$f_d_end}");
//$menu->set_caption("対象年月 {$f_d_start}～{$f_d_end}：合計金額={$ft_kingaku}：合計数量={$ft_suryo}");
/////////// HTML Header を出力してキャッシュを制御
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
<?php echo $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.body.focus();                          // F2/F12キーで戻るための対応
    // document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-family:    monospace;
}
.pt9b {
    font-size:      9pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt10 {
    font-size:l     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winboxb {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:       #ccffff;
}
.winboxg {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:       #ccffcc;
}
.winboxy {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:       yellow;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<body>
    <center>
<?php echo $menu->out_title_border()?>
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#FFFFFF' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap><div class='pt11b'>申請コード</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>品名(種類)</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>形式<BR>認証番号</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>有効期限</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>型式</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>製品<BR>番号</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>生産<BR>数量</div></th>
                    
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <?php
                $data_middle_t  = 0;
                $data_middle_nt = 0;
                for ($r=0; $r<$rows_g; $r++) {
                    $flg_gu = ' ';
                    $check_gu = $r % 2;
                    if ($check_gu == 0) {
                        $flg_gu = '1';
                    }
                    get_assy_no($res_g[$r][5], $search, $result);
                    $num_m           = $result->get('num_m');
                    $data_middle     = $result->get_array2('data_middle');
                    $data_middle_num = $result->get('data_middle_num');
                    //if($num_m != 0) {
                        $num_m2      = $num_m + 1;
                        $assy_no = '';
                        echo "<tr>\n";
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='center'><div class='pt11b'>" . $res_g[$r][1] . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='center'><div class='pt11b'>" . $res_g[$r][1] . "</div></td>\n";
                        }
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' align='center'><div class='pt11b'>" . $res_g[$r][2] . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' align='center'><div class='pt11b'>" . $res_g[$r][2] . "</div></td>\n";
                        }
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='center'><div class='pt11b'>" . $res_g[$r][3] . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='center'><div class='pt11b'>" . $res_g[$r][3] . "</div></td>\n";
                        }
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='center'><div class='pt11b'>" . $res_g[$r][4] . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='center'><div class='pt11b'>" . $res_g[$r][4] . "</div></td>\n";
                        }
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='center'><div class='pt11b'>" . $res_g[$r][0] . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='center'><div class='pt11b'>" . $res_g[$r][0] . "</div></td>\n";
                        }
                        echo "  <td class='winbox' nowrap align='left'><div class='pt9'>" . $data_middle[0][0] . "</div></td>\n";
                        if ($data_middle[0][1] == 0) {
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>　</div></td>\n";
                        } else {
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>
                                    <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}\");location.replace(\"", $menu->out_action('売上明細'), "?uri_passwd={$uri_passwd}&d_start={$d_start}&d_end={$d_end}&kubun={$kubun}&div={$div}&customer=$customer&uri_ritu=52&sales_page=25&assy_no={$data_middle[0][0]}\")' target='application' style='text-decoration:none;'>"
                                    . number_format($data_middle[0][1], 0) . "</div></td>\n";
                        }
                        echo "</tr>\n";
                        for ($i=1; $i<$num_m; $i++) {
                            echo "<tr>\n";
                            echo "  <td class='winbox' nowrap align='left'><div class='pt9'>" . $data_middle[$i][0] . "</div></td>\n";
                            if ($data_middle[$i][1] == 0) {
                                echo "  <td class='winbox' nowrap align='right'><div class='pt9'>　</div></td>\n";
                            } else {
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>
                                    <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}\");location.replace(\"", $menu->out_action('売上明細'), "?uri_passwd={$uri_passwd}&d_start={$d_start}&d_end={$d_end}&kubun={$kubun}&div={$div}&customer=$customer&uri_ritu=52&sales_page=25&assy_no={$data_middle[$i][0]}\")' target='application' style='text-decoration:none;'>"
                                    . number_format($data_middle[$i][1], 0) . "</div></td>\n";
                            }
                            echo "</tr>\n";
                        }
                        echo "<tr>\n";
                        if ($flg_gu == '1') {
                            echo "  <td class='winboxb' nowrap align='center'><div class='pt9b'>計</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_num, 0) . "</div></td>\n";
                        } else {
                            echo "  <td class='winboxg' nowrap align='center'><div class='pt9b'>計</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_num, 0) . "</div></td>\n";
                        }
                        echo "</tr>\n";
                   // }
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
