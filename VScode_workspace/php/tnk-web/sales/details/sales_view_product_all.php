<?php
//////////////////////////////////////////////////////////////////////////////
// 売上 集計 照会 製品別  new version   sales_view_product.php              //
// Copyright (C) 2010 - 2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2010/12/14 Created   sales_view_product_all.php                          //
// 2011/01/20 日付の受け渡し等の不具合を修正→実装                          //
// 2011/05/16 表示がおかしいところを修正                                    //
// 2011/05/26 細かいエラーが発生していた為、修正                            //
// 2011/05/31 グループコード変更に伴いSQL文を変更                           //
// 2014/05/23 数量表示を追加(八木沢課長依頼)                           大谷 //
// 2015/03/06 セグメント別の照会に対応(製品グループ内で違いがある為)        //
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
$menu->set_title('製品グループ別 売上照会');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('総材料費照会',   INDUST . 'material/materialCost_view.php');
$menu->set_action('単価登録照会',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('総材料費履歴',   INDUST . 'material/materialCost_view_assy.php');
$menu->set_action('売上明細',   SALES . 'details/sales_view_product.php');

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
    if ( !isset($_REQUEST['kubun']) ) {
        $_REQUEST['kubun'] = $_SESSION['s_kubun'];
    }
} else {
    if ( isset($_POST['kubun']) ) {
        $kubun = $_POST['kubun'];
    } else {
        $kubun = '';
    }
}
if ( isset($_SESSION['s_div']) ) {
    if ( !isset($_REQUEST['div']) ) {
        $_REQUEST['div'] = $_SESSION['s_div'];
    }
} else {
    if ( isset($_POST['div']) ) {
        $div = $_POST['div'];
    } else {
        $div = '';
    }
}
if ( isset($_SESSION['s_divg']) ) {
    if ( !isset($_REQUEST['divg']) ) {
        $_REQUEST['divg'] = $_SESSION['s_divg'];
    }
} else {
    if ( isset($_POST['divg']) ) {
        $divg = $_POST['divg'];
    } else {
        $divg = '';
    }
}
//////////// 初回時のセッションデータ保存   次頁・前頁を軽くするため
//if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0レコードでマーカー表示してしまうための対応
    $_SESSION['s_uri_passwd'] = $_REQUEST['uri_passwd'];
    $_SESSION['s_div']        = $_REQUEST['div'];
    $_SESSION['s_d_start']    = $_REQUEST['d_start'];
    $_SESSION['s_d_end']      = $_REQUEST['d_end'];
    $_SESSION['s_kubun']      = $_REQUEST['kubun'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['s_div'];
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
    if ($kubun == '1') {
        $search .= " and datatype='1'";
    }
    if ($div == 'S') {    // Ｃ特注なら
        $search .= " and 事業部='C' and note15 like 'SC%%'";
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and (assyno not like 'SS%%')";
        //$search .= " and CASE WHEN 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='C' END";
        //$search .= " and groupm.support_group_code IS NULL";
    } elseif ($div == 'D') {    // Ｃ標準なら
        $search .= " and 事業部='C' and (note15 NOT like 'SC%%')";    // 部品売りを標準へする
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and (assyno not like 'SS%%')";
        //$search .= " and (CASE WHEN 計上日<20130501 THEN groupm.support_group_code IS NULL ELSE 事業部='C' END)";
        //$search .= " and groupm.support_group_code IS NULL";
    } elseif ($div == "_") {    // 事業部なし
        $search .= " and 事業部=' '";
    } elseif ($div == "C") {
        $search .= " and 事業部='$div'";
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and (assyno not like 'SS%%')";
    } elseif ($div == "L") {
        $search .= " and 事業部='$div'";
        $search .= " and (assyno not like 'SS%%')";
    } elseif ($div != " ") {
        $search .= " and 事業部='$div'";
    }
    $_SESSION['sales_search'] = $search;        // SQLのwhere句を保存
//}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$kubun      = $_SESSION['s_kubun'];
$search     = $_SESSION['sales_search'];

///////////// 大分類金額・件数等を取得
$query_k = sprintf("select
                        sum(Uround(数量*単価,0)) as 金額,       -- 0
                        pts.top_no as 大分類名,                 -- 1
                        sum(数量) as 数量                       -- 2
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on 計画番号=plan_no
                  left outer join
                        mshmas as p
                  on assyno=p.mipn
                  left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                  -- on p.mhjcd=gnm.mhgcd
                  on p.mhshc=gnm.mhgcd
                  left outer join
                        product_serchGroup as psc
                  on gnm.mhggp=psc.group_no
                  left outer join
                        product_top_serchgroup as pts
                  on psc.top_code=pts.top_no
                  %s
                  group by pts.top_no
                  order by pts.top_no
                  ", $search);   // 共用 $search で検索
$res_k   = array();
$field = array();
if (($rows_k = getResultWithField3($query_k, $field, $res_k)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>売上明細のデータがありません。<br>%s～%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
}
///////////// 照会順に並び替え
$query_o = sprintf("select
                        top_no as 大分類No,                  -- 0
                        top_name as 大分類名,                -- 1
                        s_order as 照合順                    -- 2
                  from
                        product_top_serchgroup
                  order by s_order
                  ");   
$res_o   = array();
$field_o = array();
if (($rows_o = getResultWithField3($query_o, $field_o, $res_o)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>照合大分類が登録されていません。");
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $num_o = count($field_o);       // フィールド数取得
    $data_top_t  = 0;
    $data_top_nt = 0;
    $view_data = array();
    for ($i=0; $i<$rows_o; $i++) {
        $data_top[$i][0] = '';
        $data_top[$i][1] =  0;
        $data_top[$i][2] = '';
        $data_top[$i][3] =  0;
        for ($r=0; $r<$rows_k; $r++) {
            if ($res_o[$i][0] == $res_k[$r][1]) {
                $data_top[$i][0] = $res_o[$i][1];
                $data_top[$i][1] = $res_k[$r][0];
                $data_top[$i][2] = $res_k[$r][1];
                $data_top[$i][3] = $res_k[$r][2];
                $data_top_t      += $res_k[$r][0];
                $data_top_nt     += $res_k[$r][2];
            }
        }
    }
}

function get_middle_data($top_code, $search_middle, $result, $data_middle_t, $data_middle_nt) {
    $search_middle .= " and psc.top_code='$top_code'";
    $query_m = sprintf("select
                        sum(Uround(数量*単価,0)) as 金額,       -- 0
                        psc.group_no as 中分類No,               -- 1
                        sum(数量) as 数量                       -- 2
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on 計画番号=plan_no
                  left outer join
                        mshmas as p
                  on assyno=p.mipn
                  left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                  -- on p.mhjcd=gnm.mhgcd
                  on p.mhshc=gnm.mhgcd
                  left outer join
                        product_serchGroup as psc
                  on gnm.mhggp=psc.group_no
                  %s
                  group by psc.group_no
                  order by psc.group_no
                  ", $search_middle);   // 共用 $search で検索
    $field_m = array();
    if (($rows_m = getResultWithField3($query_m, $field_m, $res_m)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>中分類が登録されていません</font>");
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        //exit();
    } else {
        $num_m = count($res_m);       // データ数取得
        for ($r=0; $r<$rows_m; $r++) {
            $group_no = $res_m[$r][1];
            $search_c = "where group_no='$group_no'";
            $query_c = sprintf("select
                            group_name as 中分類名                  -- 0
                    from
                            product_serchGroup
                    %s
                    LIMIT 1
                    ",  $search_c);   
            $res_c   = array();
            $field_c = array();
            if (($rows_c = getResultWithField3($query_c, $field_c, $res_c)) <= 0) {
                $group_name[$r] = '';
            } else {
                $group_name[$r] = $res_c[0][0];
            }
        }
        $data_middle_sum = 0;
        $data_middle_num = 0;
        for ($r=0; $r<$rows_m; $r++) {
            $res_m[$r][3]     = $group_name[$r];
            $data_middle_sum += $res_m[$r][0];
            $data_middle_num += $res_m[$r][2];
        }
        $data_middle_t  += $data_middle_sum;
        $data_middle_nt += $data_middle_num;
        $result->add_array2('data_middle', $res_m);
        $result->add('num_m', $num_m);
        $result->add('data_middle_sum', $data_middle_sum);
        $result->add('data_middle_t', $data_middle_t);
        $result->add('data_middle_num', $data_middle_num);
        $result->add('data_middle_nt', $data_middle_nt);
    }
}
//////////// 表題の設定
$ft_kingaku = number_format($data_top_t);                    // ３桁ごとのカンマを付加
$ft_suryo   = number_format($data_top_nt);                   // ３桁ごとのカンマを付加
//$ft_ken     = number_format($t_ken);
//$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // 日付を / でフォーマット
$f_d_end    = format_date($d_end);
$menu->set_caption("対象年月 {$f_d_start}～{$f_d_end}：合計金額={$ft_kingaku}：合計数量={$ft_suryo}");
//$menu->set_caption("対象年月 {$f_d_start}～{$f_d_end}：合計件数={$ft_ken}：合計金額={$ft_kingaku}：合計数量={$ft_kazu}<u>");
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
                    <!--
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->
                    <th class='winbox' nowrap><div class='pt11b'><?php echo $field[1] ?></div></th>
                    <th class='winbox' nowrap><div class='pt11b'><?php echo $field[2] ?></div></th>
                    <th class='winbox' nowrap><div class='pt11b'><?php echo $field[0] ?></div></th>
                    <th class='winbox' nowrap><div class='pt11b'>中分類名</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>数量</div></th>
                    <th class='winbox' nowrap><div class='pt11b'>金額</div></th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <?php
                $data_middle_t  = 0;
                $data_middle_nt = 0;
                for ($r=0; $r<$rows_o; $r++) {
                    $flg_gu = ' ';
                    $check_gu = $r % 2;
                    if ($check_gu == 0) {
                        $flg_gu = '1';
                    }
                    if($data_top[$r][1] != 0) {
                        get_middle_data($data_top[$r][2], $search, $result, $data_middle_t, $data_middle_nt);
                        $data_middle_t = $result->get('data_middle_t');
                        $data_middle_nt = $result->get('data_middle_nt');
                        $num_m           = $result->get('num_m');
                        $data_middle     = $result->get_array2('data_middle');
                        $data_middle_sum = $result->get('data_middle_sum');
                        $data_middle_num = $result->get('data_middle_num');
                        $num_m2      = $num_m + 1;
                        $assy_no = '';
                        echo "<tr>\n";
                        //echo "  <td rowspan = '" . $num_m2 . "' class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + 1) . "</div></td>    <!-- 行ナンバーの表示 -->\n";
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='left'><div class='pt11b'>" . $data_top[$r][0] . "</div></td>\n";
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='right'><div class='pt11b'>" . number_format($data_top[$r][3], 0) . "</div></td>\n";
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='right'><div class='pt11b'>" . number_format($data_top[$r][1], 0) . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='left'><div class='pt11b'>" . $data_top[$r][0] . "</div></td>\n";
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='right'><div class='pt11b'>" . number_format($data_top[$r][3], 0) . "</div></td>\n";
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='right'><div class='pt11b'>" . number_format($data_top[$r][1], 0) . "</div></td>\n";
                        }
                        echo "  <td class='winbox' nowrap align='left'><div class='pt9'>" . $data_middle[0][3] . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . number_format($data_middle[0][2], 0) . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>
                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}\");location.replace(\"", $menu->out_action('売上明細'), "?uri_passwd={$uri_passwd}&d_start={$d_start}&d_end={$d_end}&kubun={$kubun}&div={$div}&divg={$data_middle[0][1]}&uri_ritu=52&sales_page=9999&assy_no={$assy_no}\")' target='application' style='text-decoration:none;'>"
                                . number_format($data_middle[0][0], 0) . "</div></td>\n";
                        echo "</tr>\n";
                        for ($i=1; $i<$num_m; $i++) {
                            echo "<tr>\n";
                            echo "  <td class='winbox' nowrap align='left'><div class='pt9'>" . $data_middle[$i][3] . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . number_format($data_middle[$i][2], 0) . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>
                                    <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}\");location.replace(\"", $menu->out_action('売上明細'), "?uri_passwd={$uri_passwd}&d_start={$d_start}&d_end={$d_end}&kubun={$kubun}&div={$div}&divg={$data_middle[$i][1]}&uri_ritu=52&sales_page=9999&assy_no={$assy_no}\")' target='application' style='text-decoration:none;'>"
                                    . number_format($data_middle[$i][0], 0) . "</div></td>\n";
                            echo "</tr>\n";
                        }
                        echo "<tr>\n";
                        if ($flg_gu == '1') {
                            echo "  <td class='winboxb' nowrap align='left'><div class='pt9b'>小計</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_num, 0) . "</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_sum, 0) . "</div></td>\n";
                        } else {
                            echo "  <td class='winboxg' nowrap align='left'><div class='pt9b'>小計</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_num, 0) . "</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_sum, 0) . "</div></td>\n";
                        }
                        echo "</tr>\n";
                    }
                }
                ?>
            </tbody>
            <tr>
                <td class='winboxy' nowrap align='left'><div class='pt11b'>大分類計</div></td>
                <td class='winboxy' nowrap align='right'><div class='pt11b'><?php echo number_format($data_top_nt, 0) ?></div></td>
                <td class='winboxy' nowrap align='right'><div class='pt11b'><?php echo number_format($data_top_t, 0) ?></div></td>
                <td class='winboxy' nowrap align='left'><div class='pt11b'>中分類計</div></td>
                <td class='winboxy' nowrap align='right'><div class='pt11b'><?php echo number_format($data_middle_nt, 0) ?></div></td>
                <td class='winboxy' nowrap align='right'><div class='pt11b'><?php echo number_format($data_middle_t, 0) ?></div></td>
             </tr>
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
