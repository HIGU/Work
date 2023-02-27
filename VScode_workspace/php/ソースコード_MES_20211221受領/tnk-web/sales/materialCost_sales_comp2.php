<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 総材料費と売上高の比率表２                                  //
// Copyright (C) 2004-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/01/27 Created  materialCost_sales_comp.php                          //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/11/02 カプラ・リニア共に全体・標準・特注(バイモル)を追加し明確化    //
// 2005/01/17 カプラ標準が → カラプ標準になっている誤記を訂正              //
//            industry/ → industry/materialへ  MenuHeaderクラスに変更      //
// 2005/06/14 文字サイズ等を大きくして画面を見やすくした(デザイン)          //
// 2005/08/22 総材料比率の追加 項目の組立費(外作) 外作合計・内作合計 を追加 //
//            最終日を求める関数 set_last_day() を追加                      //
// 2005/08/24 パスワード管理をコメントアウト                                //
// 2006/02/06 外注組立分をカプラ標準・特注 リニア標準・バイモルに比率分け   //
// 2007/09/28 Uround(assy_time * assy_rate, 2) →    自動機賃率を計算に追加 //
//    Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) //
// 2013/01/28 製品名の頭文字がDPEのものを液体ポンプ(バイモル)で集計するよう //
//            に変更                                                   大谷 //
//            バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま 大谷//
// 2013/01/31 リニアのみのDPE抜出SQLを訂正                             大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');
require_once ('../tnk_func.php');
require_once ('../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site( 1, 15);                    // 生産メニュー=30 売上メニュー=1 最後のメニュー= 99 システム管理用は９９番
                                            // 売上と総材料費=12 総材料費と売上=19  下位メニュー無し<=0 テンプレートファイルは６０番

////////////// リターンアドレス設定
// $menu->set_RetUrl(SALES_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('売上高と材料費の(比較)比率表');
//////////// 表題の設定
$menu->set_caption('検索条件を入力又は選択して下さい。');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

// 最終日を求めるfunction
function set_last_day($date) {
    if (strlen($date) == 8) {
        $year  = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day   = substr($date, 6, 2);
        if ($month == '02') {
            if ($day < 29) return $date;
        } else {
            if ($day < 31) return $date;
        }
    }
    return ($year . $month . last_day($year, $month) );
}

/////////// exec がsubmitされた時
while (isset($_POST['exec'])) {
    // $_SESSION['s_uri_passwd'] = $_POST['uri_passwd'];
    $_SESSION['s_div']        = $_POST['div'];
    $_SESSION['s_d_start']    = $_POST['d_start'];
    $_SESSION['s_d_end']      = $_POST['d_end'];
    $_SESSION['s_kubun']      = $_POST['kubun'];
    $_SESSION['uri_assy_no']  = $_POST['assy_no'];
    // $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['s_div'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
    $kubun      = $_SESSION['s_kubun'];
    $assy_no    = $_SESSION['uri_assy_no'];
    $d_end = set_last_day($d_end);
    $_SESSION['s_d_end'] = $d_end;
    
    ////////////// パスワードチェック
    /**********************************
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>パスワードが違います！</font>";
        unset($_POST['exec']);
        break;
    }
    **********************************/
    ///////////// 問合せのSQL文生成
    $query = "select
                    -- count(数量)                      AS 売上件数,
                    -- sum(数量)                        AS 売上数量,
                    sum(Uround(数量 * 単価, 0))         AS 売上高,
                    sum(Uround(数量 * ext_price, 0))    AS 外作部品,    -- 材料費(外作)
                    sum(Uround(数量 * int_price, 0) +
                    Uround(数量 * Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2), 0))
                                                        AS 内作合計,    -- 材料費(内作)+組立費=内作合計
                    sum(Uround(数量 * sum_price, 0) +
                    Uround(数量 * Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2), 0))
                                                        AS 総材料費,    -- 材料費(合計)+組立費=総材料費
                    sum(Uround(数量 * int_price, 0))    AS 内作製造,
                    sum(Uround(数量 * Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) ,0))
                                                        AS 内作組立,    -- 契約賃率による
                    sum(Uround(数量 * Uround(m_time*m_rate, 2), 0))
                                                        AS 内作手作業,  -- 標準賃率による
                    sum(Uround(数量 * Uround(a_time*a_rate, 2), 0))
                                                        AS 内作自動機,  -- 標準賃率による
                    sum(Uround(数量 * Uround(g_time*g_rate, 2), 0))
                                                        AS 内作外注,    -- 標準賃率による(内職)
                    count(*)                            AS 総件数,
                    count(*) - count(sum_price)         AS 未登録
              from
                    hiuuri as u
              left outer join
                    assembly_schedule as a
              on u.計画番号=a.plan_no
              left outer join
                    material_cost_header as mate
              on u.計画番号=mate.plan_no
              left outer join
                    miitem as m
              on assyno=m.mipn";
    //////////// SQL where 句を 共用する
    $search = "where 計上日>=$d_start and 計上日<=$d_end";
    if ($assy_no != '') {       // 製品番号が指定された場合
        $search .= " and assyno like '{$assy_no}%%'";
    } elseif ($div == 'H') {    // Ｃ標準なら
        $search .= " and 事業部='C' and note15 not like 'SC%%'";
    } elseif ($div == 'S') {    // Ｃ特注なら
        $search .= " and 事業部='C' and note15 like 'SC%%'";
    } elseif ($div == "M") {    // リニア標準の場合は事業部と assyno でチェック
        //$search .= " and 事業部='L' and (assyno NOT like 'LC%%' AND assyno NOT like 'LR%%')";
        $search .= " and 事業部='L' and (assyno NOT like 'LC%%' AND assyno NOT like 'LR%%') and CASE WHEN assyno = '' THEN 事業部='L' ELSE m.midsc not like 'DPE%%' END";
    } elseif ($div == "B") {    // バイモルの場合は assyno でチェック
        //$search .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
        $search .= " and (assyno like 'LC%%' or assyno like 'LR%%' or m.midsc like 'DPE%%')";
    } elseif ($div != " ") {
        $search .= " and 事業部='$div'";
    }
    if ($kubun != " ") {
        $search .= " and datatype='$kubun'";
    }
    $query = sprintf("$query %s", $search);     // SQL query 文の完成
    $res = array();
    if (getResult($query, $res) <= 0) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>データがありません！</font>";
        unset($_POST['exec']);
        break;
    }
    if ($res[0]['売上高'] <= 0) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>対象範囲で売上高がありません！</font>";
        unset($_POST['exec']);
        break;
    }
    ///////////// 組立費(外作)金額取得のSQL文生成
    $query =
    "
        select
            sum(Uround(siharai * order_price, 0))   AS 組立外作
            , Uround(sum(Uround(siharai * order_price, 0)) * 0.7, 0)
                                                    AS 標準外作
            , sum(Uround(siharai * order_price, 0)) - Uround(sum(Uround(siharai * order_price, 0)) * 0.7, 0)
                                                    AS 特注外作
            , Uround(sum(Uround(siharai * order_price, 0)) * 0.48, 0)
                                                    AS リニア外作
            , sum(Uround(siharai * order_price, 0)) - Uround(sum(Uround(siharai * order_price, 0)) * 0.48, 0)
                                                    AS バイモル外作
        from
            act_payable
        where
            act_date >= $d_start and act_date <= $d_end
            and
            type_no = 2
            and
            (kamoku = 2 OR kamoku = 3 OR kamoku = 4 OR kamoku = 5)
    ";
    ///// 事業部(グループ)はカプラ全体とリニア全体にしか分ける事が出来ない
    switch ($div) {
    case 'C':
    case 'H':
    case 'S':
        $query .= "and div = 'C'";
        break;
    case 'L':
    case 'M':
    case 'B':
        $query .= "and div = 'L'";
        break;
    default:
        // 全グループとなる
    }
    $res_kumi = array();
    if (getResult($query, $res_kumi) <= 0) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>データがありません！</font>";
        unset($_POST['exec']);
        break;
    } else {
        switch ($div) {
        case 'C':
            $res[0]['組立外作'] = $res_kumi[0]['組立外作'];
            break;
        case 'H':
            $res[0]['組立外作'] = $res_kumi[0]['標準外作'];
            break;
        case 'S':
            $res[0]['組立外作'] = $res_kumi[0]['特注外作'];
            break;
        case 'L':
            $res[0]['組立外作'] = $res_kumi[0]['組立外作'];
            break;
        case 'M':
            $res[0]['組立外作'] = $res_kumi[0]['リニア外作'];
            break;
        case 'B':
            $res[0]['組立外作'] = $res_kumi[0]['バイモル外作'];
            break;
        default:
            $res[0]['組立外作'] = $res_kumi[0]['組立外作'];
            // 全グループとなる
        }
    }
    
    ///// 売上高比の計算
    $res[0]['外作合計'] = $res[0]['外作部品'] + $res[0]['組立外作'];     // 組立費(外作)追加 2005/08/22
    $ext_bu_percent  = Uround($res[0]['外作部品'] / $res[0]['売上高'] * 100, 2);// 外作部品の売上高比
    $extkumi_percent = Uround($res[0]['組立外作'] / $res[0]['売上高'] * 100, 2);// 組立外作の売上高比
    $res[0]['総材料費'] = ($res[0]['外作合計'] + $res[0]['内作合計']);          // sum_priceと誤差が出るため追加
    $ext_percent = Uround($res[0]['外作合計'] / $res[0]['売上高'] * 100, 2);    // 外作合計の売上高比
    $int_percent = Uround($res[0]['内作合計'] / $res[0]['売上高'] * 100, 2);    // 内作合計の売上高比
    $sum_percent = Uround($res[0]['総材料費'] / $res[0]['売上高'] * 100, 2);    // 総材料費の売上高比
    $int_seizou  = Uround($res[0]['内作製造'] / $res[0]['売上高'] * 100, 2);    // 内作製造の売上高比
    $int_assy    = Uround($res[0]['内作組立'] / $res[0]['売上高'] * 100, 2);    // 内作組立の売上高比
    ///// 総材料比の計算
    $ext_bu_percent_zai  = Uround($res[0]['外作部品'] / $res[0]['総材料費'] * 100, 2);    // 外作部品の総材料比
    $extkumi_percent_zai = Uround($res[0]['組立外作'] / $res[0]['総材料費'] * 100, 2);    // 組立外作の総材料比
    $ext_percent_zai = Uround($res[0]['外作合計'] / $res[0]['総材料費'] * 100, 2);      // 外作合計の総材料比
    $int_percent_zai = Uround($res[0]['内作合計'] / $res[0]['総材料費'] * 100, 2);      // 内作合計の総材料比
    $sum_percent_zai = Uround($res[0]['総材料費'] / $res[0]['総材料費'] * 100, 2);      // 総材料費の総材料比
    $int_seizou_zai  = Uround($res[0]['内作製造'] / $res[0]['総材料費'] * 100, 2);      // 内作製造の総材料比
    $int_assy_zai    = Uround($res[0]['内作組立'] / $res[0]['総材料費'] * 100, 2);      // 内作組立の総材料比
    
    ///// 売上高比の計算(標準賃率) 違うのは内作組立・内作合計・総材料費の３個
    $res[0]['内作組立2'] = ($res[0]['内作手作業'] + $res[0]['内作自動機'] + $res[0]['内作外注']);  // 標準賃率による組立費
    $int_assy2           = Uround($res[0]['内作組立2'] / $res[0]['売上高'] * 100, 2);   // 内作組立の売上高比(標準賃率)
    $res[0]['内作合計2'] = ($res[0]['内作製造'] + $res[0]['内作組立2']);
    $res[0]['総材料費2'] = ($res[0]['外作合計'] + $res[0]['内作合計2']);
    $int_percent2        = Uround($res[0]['内作合計2'] / $res[0]['売上高'] * 100, 2);   // 内作合計の売上高比(標準賃率)
    $sum_percent2        = Uround($res[0]['総材料費2'] / $res[0]['売上高'] * 100, 2);   // 総材料費の売上高比(標準賃率)
    ///// 総材料比の計算(標準賃率) 違うのは内作組立・内作合計・総材料費の３個 と外作部品・組立外作・内作製造
    $int_assy2_zai    = Uround($res[0]['内作組立2'] / $res[0]['総材料費2'] * 100, 2);   // 内作組立の総材料比(標準賃率)
    $int_percent2_zai = Uround($res[0]['内作合計2'] / $res[0]['総材料費2'] * 100, 2);   // 内作合計の総材料比(標準賃率)
    $sum_percent2_zai = Uround($res[0]['総材料費2'] / $res[0]['総材料費2'] * 100, 2);   // 総材料費の総材料比(標準賃率)
    $ext_bu_percent2_zai  = Uround($res[0]['外作部品'] / $res[0]['総材料費2'] * 100, 2);    // 外作部品の総材料比
    $extkumi_percent2_zai = Uround($res[0]['組立外作'] / $res[0]['総材料費2'] * 100, 2);    // 組立外作の総材料比
    $int_seizou2_zai  = Uround($res[0]['内作製造'] / $res[0]['総材料費2'] * 100, 2);      // 内作製造の総材料比
    $ext_percent2_zai = Uround($res[0]['外作合計'] / $res[0]['総材料費2'] * 100, 2);      // 外作合計の総材料比
    break;
}

/////////////// 受け渡し変数の初期化
/*****************************************
if ( isset($_SESSION['s_uri_passwd']) ) {
    $uri_passwd = $_SESSION['s_uri_passwd'];
} else {
    $uri_passwd = '';
}
*****************************************/
if ( isset($_SESSION['s_div']) ) {
    $div = $_SESSION['s_div'];
} else {
    $div = '';
}
if ( isset($_SESSION['s_d_start']) ) {
    $d_start = $_SESSION['s_d_start'];
} else {
    if ( isset($_POST['d_start']) ) {
        $d_start = $_POST['d_start'];
    } else {
        $d_start = date_offset(1);
    }
}
if ( isset($_SESSION['s_d_end']) ) {
    $d_end = $_SESSION['s_d_end'];
} else {
    if ( isset($_POST['d_end']) ) {
        $d_end = $_POST['d_end'];
    } else {
        $d_end = date_offset(1);
    }
}
if ( isset($_SESSION['s_kubun']) ) {
    // $kubun = $_SESSION['s_kubun'];
    $kubun = '1';
} else {
    $kubun = '1';
}
if ( isset($_SESSION['uri_assy_no']) ) {
    $assy_no = $_SESSION['uri_assy_no'];
} else {
    $assy_no = '';      // 初期化
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
<?php if ($_SESSION['s_sysmsg'] == '') echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!--    ファイル指定の場合 -->
<script language='JavaScript' src='./materialCost_sales_comp.js?<?php echo $uniq ?>'>
</script>

<script language="JavaScript">
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
//    document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
//    document.form_name.element_name.select();
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
    font-weight:    normal;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
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
.pt12b-dred {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          darkred;
}
.caption_font {
    font-size:          11pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   blue;
    color:              white;
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
.caption2_font {
    font-size:          11pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   yellow;
    color:              blue;
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
.note1_font {
    font-size:          10pt;
    font-weight:        bold;
    background-color:   yellow;
    color:              blue;
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
.note2_font {
    font-size:          10pt;
    font-weight:        bold;
    background-color:   blue;
    color:              white;
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
.margin0 {
    margin:0%;
}
td {
    font-size: 10pt;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:#d6d3ce;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
-->
</style>
</head>
<body class='margin0' onLoad='document.uri_form.div.focus(); //document.uri_form.div.select()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <form name='uri_form' action='<?php echo $menu->out_self() ?>' method='post' onSubmit='return chk_sales_form(this)'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td colspan='2' align='center' class='caption_font'>
                        <?php echo $menu->out_caption() . "\n" ?>
                    </td>
                </tr>
                <!--------------------------------------------------------------
                <tr>
                    <td class='winbox' align='right'>
                        パスワードを入れて下さい
                    </td>
                    <td class='winbox' align='center'>
                        <input type='password' name='uri_passwd' size='16' value='$uri_passwd' maxlength='8'>
                    </td>
                </tr>
                --------------------------------------------------------------->
                <tr>
                    <td class='winbox' align='right'>
                        部門を選択して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <select name='div' class='pt12b'>
                            <option value=' '<?php if($div==' ') echo('selected'); ?>>全グループ</option>
                            <option value='C'<?php if($div=='C') echo('selected'); ?>>カプラ全体</option>
                            <option value='H'<?php if($div=='H') echo('selected'); ?>>カプラ標準</option>
                            <option value='S'<?php if($div=='S') echo('selected'); ?>>カプラ特注</option>
                            <option value='L'<?php if($div=='L') echo('selected'); ?>>リニア全体</option>
                            <option value='M'<?php if($div=='M') echo('selected'); ?>>リニア標準</option>
                            <option value='B'<?php if($div=='B') echo('selected'); ?>>液体ポンプ</option>
                            <option value='T'<?php if($div=='T') echo('selected'); ?>>ツール</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        日付を指定して下さい(必須)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='d_start' class='pt12b' size='8' value='<?php echo($d_start); ?>' maxlength='8'>
                        〜
                        <input type='text' name='d_end' class='pt12b' size='8' value='<?php echo($d_end); ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        製品番号の指定
                        (指定しない場合は空白)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='assy_no' size='9' class='pt12b' value='<?php echo $assy_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right' width='400'>
                        売上区分=
                        １：完成 ２：個別(通常) ３：手打 ４：調整 ５：移動 ６：直納 ７：売上 
                        ８：自動振替 ９：部品受注
                    </td>
                    <td class='winbox' align='center'>
                        <select name='kubun'>
                            <!-- <option value=' '<?php if($kubun==' ') echo('selected'); ?>>全て</option> -->
                            <option value='1'<?php if($kubun=='1') echo('selected'); ?>>1完成</option>
                            <!-- <option value='2'<?php if($kubun=='2') echo('selected'); ?>>2個別</option>
                            <option value='3'<?php if($kubun=='3') echo('selected'); ?>>3手打</option>
                            <option value='4'<?php if($kubun=='4') echo('selected'); ?>>4調整</option>
                            <option value='5'<?php if($kubun=='5') echo('selected'); ?>>5移動</option>
                            <option value='6'<?php if($kubun=='6') echo('selected'); ?>>6直納</option>
                            <option value='7'<?php if($kubun=='7') echo('selected'); ?>>7売上</option>
                            <option value='8'<?php if($kubun=='8') echo('selected'); ?>>8振替</option>
                            <option value='9'<?php if($kubun=='9') echo('selected'); ?>>9受注</option> -->
                        <select>
                    </td>
                </tr>
        <?php if (!isset($_POST['exec'])) { ?>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='exec' value='実行' >
                    </td>
                </tr>
        <?php } else { ?>
                <tr>
                    <td class='winbox' colspan='2' align='right'>
                        <input type='submit' name='exec' value='実行' >　　
                        <input type='submit' name='ret_ok' value='戻る'>　　
                        <span class='pt12b'>
                        総件数＝<?php echo number_format($res[0]['総件数']) ?>件　　
                        <?php if ($res[0]['未登録'] <= 0) { ?>
                        総材料費の未登録＝<?php echo number_format($res[0]['未登録']) ?>件
                        <?php } else { ?>
                        <span style='color:red;'>総材料費の未登録＝<?php echo number_format($res[0]['未登録']) ?>件</span>
                        <?php } ?>
                        </span>
                    </td>
                </tr>
        <?php } ?>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        <?php if (isset($_POST['exec'])) { ?>
        
        <br>
        
        <form name='query_form1' action='<?php echo $menu->out_self() ?>' method='post'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap align='center' class='note1_font'>契約賃率使用</td>
                    <td width='110' align='center' class='caption_font'>売上高</td>
                    <td width='110' align='center' class='caption_font'>材料費(外作)</td>
                    <td width='110' align='center' class='caption_font'>組立費(外作)</td>
                    <td width='110' align='center' class='caption_font'>(内作製造)</td>
                    <td width='110' align='center' class='caption_font'>(内作組立)</td>
                    <td width='110' align='center' class='caption_font'>総材料費</td>
                </tr>
                <tr>
                    <td align='center' class='caption_font'>金　額</td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['売上高'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['外作部品'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['組立外作'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'>（<?php echo number_format($res[0]['内作製造'], 0) ?>）</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>（<?php echo number_format($res[0]['内作組立'], 0) ?>）</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['総材料費'], 0) ?></span></td>
                </tr>
                <tr>
                    <td align='center' class='caption_font'>売上高比</td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($ext_bu_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($extkumi_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>（<?php echo number_format($int_seizou, 2) ?>%）</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>（<?php echo number_format($int_assy, 2) ?>%）</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent, 2) ?>%</span></td>
                </tr>
                <tr>
                    <td align='center' class='caption_font'>総材料比</td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($ext_bu_percent_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($extkumi_percent_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>（<?php echo number_format($int_seizou_zai, 2) ?>%）</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>（<?php echo number_format($int_assy_zai, 2) ?>%）</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent_zai, 2) ?>%</span></td>
                </tr>
                <tr>
                    <td align='center' class='caption_font'>外作内作別</td>
                    <td class='winbox' align='center'>---</td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b-dred'><?php echo number_format($res[0]['外作合計'], 0) ?></span></td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($res[0]['内作合計'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['総材料費'], 0) ?></span></td>
                </tr>
                <tr>
                    <td align='center' class='caption_font'>売上高比</td>
                    <td class='winbox' align='center'>---</td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($ext_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($int_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>---</span></td>
                </tr>
                <tr>
                    <td align='center' class='caption_font'>総材料比</td>
                    <td class='winbox' align='center'>---</td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($ext_percent_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($int_percent_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>---</span></td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        
        <br>
        
        <form name='query_form2' action='<?php echo $menu->out_self() ?>' method='post'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap align='center' class='note2_font'>標準賃率使用</td>
                    <td width='110' align='center' class='caption2_font'>売上高</td>
                    <td width='110' align='center' class='caption2_font'>材料費(外作)</td>
                    <td width='110' align='center' class='caption2_font'>組立費(外作)</td>
                    <td width='110' align='center' class='caption2_font'>(内作製造)</td>
                    <td width='110' align='center' class='caption2_font'>(内作組立)</td>
                    <td width='110' align='center' class='caption2_font'>総材料費</td>
                </tr>
                <tr>
                    <td align='center' class='caption2_font'>金　額</td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['売上高'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['外作部品'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['組立外作'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'>（<?php echo number_format($res[0]['内作製造'], 0) ?>）</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>（<?php echo number_format($res[0]['内作組立2'], 0) ?>）</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['総材料費2'], 0) ?></span></td>
                </tr>
                <tr>
                    <td align='center' class='caption2_font'>売上高比</td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent2_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($ext_bu_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($extkumi_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>（<?php echo number_format($int_seizou, 2) ?>%）</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>（<?php echo number_format($int_assy2, 2) ?>%）</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent2, 2) ?>%</span></td>
                </tr>
                <tr>
                    <td align='center' class='caption2_font'>総材料比</td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent2, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($ext_bu_percent2_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($extkumi_percent2_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>（<?php echo number_format($int_seizou2_zai, 2) ?>%）</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>（<?php echo number_format($int_assy2_zai, 2) ?>%）</span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($sum_percent2_zai, 2) ?>%</span></td>
                </tr>
                <tr>
                    <td align='center' class='caption2_font'>外作内作別</td>
                    <td class='winbox' align='center'>---</td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b-dred'><?php echo number_format($res[0]['外作合計'], 0) ?></span></td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($res[0]['内作合計2'], 0) ?></span></td>
                    <td class='winbox' align='right'><span class='pt12b'><?php echo number_format($res[0]['総材料費2'], 0) ?></span></td>
                </tr>
                <tr>
                    <td align='center' class='caption2_font'>売上高比</td>
                    <td class='winbox' align='center'>---</td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($ext_percent, 2) ?>%</span></td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($int_percent2, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>---</span></td>
                </tr>
                <tr>
                    <td align='center' class='caption2_font'>総材料比</td>
                    <td class='winbox' align='center'>---</td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($ext_percent2_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right' colspan='2'><span class='pt12b'><?php echo number_format($int_percent2_zai, 2) ?>%</span></td>
                    <td class='winbox' align='right'><span class='pt12b'>---</span></td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        <?php } ?>
    </center>
</body>
</html>
<?php
echo $menu->out_alert_java();
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
