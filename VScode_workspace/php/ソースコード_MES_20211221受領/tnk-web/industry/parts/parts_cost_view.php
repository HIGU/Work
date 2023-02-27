<?php
//////////////////////////////////////////////////////////////////////////////
// 単価経歴照会(最新単価を含む)                                             //
// Copyright (C) 2004-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/05/17 Created   parts_cost_view.php                                 //
// 2004/05/24 フォーム以外から呼ばれた場合の対処でcost_pageをセッション登録 //
// 2004/05/27 単価比較のロジックを有償支給による材料費との合算に対応        //
// 2004/06/01 GET & POST データの取得のチェックロジックに page_keep を追加  //
//            リターンアドレス設定にも page_keep のチェックを追加           //
// 2004/06/03 ORDER BY に lot_no ASC を追加 lot_cost→tmp1_cost,tmp2_costへ //
// 2004/12/03 登録番号又はロット毎にトータル単価を追加 デザインを統一を保留 //
// 2005/01/11 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/01/12 単価経歴がない時の戻り時に強制的に?material=1パラメータ追加   //
// 2005/01/13 登録日が受付日より以下の条件追加 cost_page=25→100へ変更      //
// 2005/01/14 発注先が買掛の発注先と同じか条件追加 同じチェックを合算に追加 //
//                                      合算の時の色も yellow → #ffffc6 へ //
// 2005/03/03 登録番号が違うかチェックし行番号を付加する $no追加            //
// 2006/02/02 売上明細から部品売上の場合の材料費(部品単価)照会を追加 確認用 //
//            に登録番号が指定されてロットが1の場合はマーカーを付ける       //
// 2006/06/22 $menu->set_retPOST('material', '1')が２重になっているのを修正 //
// 2007/06/09 noMenu 対応と phpのショートカットタグを推奨タグへ変更         //
// 2007/09/03 古い$_SESSION['offset']が他と競合するため$session->add_local  //
//            ついでに$_POST/$_GET → $_REQUESTへ。次頁のマーク対応&ジャンプ//
// 2013/04/09 協力工場毎の買掛集計に対応                               大谷 //
// 2016/01/29 買掛実績からの照会で、各項目が保持されなかったのを修正   大谷 //
//            検査日数集計からの照会に対応                             大谷 //
// 2016/08/08 mouseoverを追加                                          大谷 //
// 2020/07/29 $sei_no GetRegNo()を追加                                 waki //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
//////////// セッションのインスタンスを登録
$session = new Session();
// 以下はまだ使用していない
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 14);                    // site_index=30(生産メニュー) site_id=14(単価経歴照会)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('単 価 経 歴 の 照 会');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('単価経歴照会',   INDUST . 'parts/parts_cost_view.php');
//////////// リターン時のGETデータ設定
$menu->set_retGET('page_keep', '1');
//////////// リターン時のPOSTデータ設定
// if (isset($_REQUEST['material'])) {
//    $menu->set_retPOST('material', $_REQUEST['material']);
//}
//////////// 次頁・前頁等があるため無条件に付ける
$menu->set_retPOST('material', '1');

//////////// noMenu(Window表示)対応のため追加
if (isset($_REQUEST['noMenu'])) $noMenu = $_REQUEST['noMenu']; else $noMenu = '';

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// GET & POST データの取得
if ( !(isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {   // 次頁・前頁ボタンのチェック
    if (isset($_REQUEST['parts_no'])) {
        $parts_no = $_REQUEST['parts_no'];
        $_SESSION['cost_parts_no'] = $parts_no;
    } elseif (isset($_REQUEST['parts_no'])) {
        $parts_no = $_REQUEST['parts_no'];
        $_SESSION['cost_parts_no'] = $parts_no;
    } else {
        $_SESSION['s_sysmsg'] .= '部品が指定されていません！';
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }
    if (isset($_REQUEST['lot_cost'])) {
        $lot_cost = $_REQUEST['lot_cost'];
        $_SESSION['cost_lot_cost'] = $lot_cost;
    } elseif (isset($_REQUEST['lot_cost'])) {
        $lot_cost = $_REQUEST['lot_cost'];
        $_SESSION['cost_lot_cost'] = $lot_cost;
    } else {
        $lot_cost = 0;      // 指定されていない場合は 0で初期化
    }
    ///// 登録日のチェック用に追加
    if (isset($_REQUEST['uke_date'])) {
        $uke_date = $_REQUEST['uke_date'];
        $_SESSION['cost_uke_date'] = $uke_date;
    } else {
        $uke_date = 99999999;   // 指定されていない場合は最新単価にする
        $_SESSION['cost_uke_date'] = $uke_date;
    }
    ///// 発注先のチェック用に追加
    if (isset($_REQUEST['vendor'])) {
        $vendor = $_REQUEST['vendor'];
        $_SESSION['cost_vendor'] = $vendor;
    } else {
        $vendor = '';           // 指定されていない場合は最新単価にする
        $_SESSION['cost_vendor'] = $vendor;
    }
    ///// 製造番号のチェック用に追加    // 2020.07.29 add waki
    if (isset($_REQUEST['sei_no'])) {
        $sei_no = $_REQUEST['sei_no'];
        $_SESSION['cost_sei_no'] = $sei_no;
    } else {
        $sei_no = 0;   // 指定されていない場合は0
        $_SESSION['cost_sei_no'] = $sei_no;
    }
    if (isset($_REQUEST['cost_page'])) {
        $cost_page = $_REQUEST['cost_page'];
        $_SESSION['cost_page'] = $cost_page;
    } elseif (isset($_REQUEST['cost_page'])) {
        $cost_page = $_REQUEST['cost_page'];
        $_SESSION['cost_page'] = $cost_page;
    } else {
        $cost_page = 100;   // 指定されていない場合は 25→100(2004/01/13変更)で初期化
        $_SESSION['cost_page'] = $cost_page;    // フォーム以外から呼ばれた場合の対処
    }
    ///// 部品売上の単価登録照会用に追加
    if (isset($_REQUEST['reg_no'])) {
        $reg_no = $_REQUEST['reg_no'];
        $_SESSION['cost_reg_no'] = $reg_no;
    } else {
        $reg_no = (-1);     // 指定されていない場合は無効にする
        $_SESSION['cost_reg_no'] = $reg_no;
    }
} else {
    $parts_no  = $_SESSION['cost_parts_no'];
    $lot_cost  = $_SESSION['cost_lot_cost'];
    $cost_page = $_SESSION['cost_page'];
    $uke_date  = $_SESSION['cost_uke_date'];
    $vendor    = $_SESSION['cost_vendor'];
    $sei_no    = $_SESSION['cost_sei_no']; // 2020.07.29 add waki
    $reg_no    = $_SESSION['cost_reg_no'];
}
if(isset($_REQUEST['paya_code'])) {
    if (isset($_REQUEST['paya_code'])) {
        $paya_code = $_REQUEST['paya_code'];
        $_SESSION['paya_code'] = $paya_code;
    } elseif (isset($_REQUEST['paya_code'])) {
        $paya_code = $_REQUEST['paya_code'];
        $_SESSION['paya_code'] = $paya_code;
    }
    if (isset($_REQUEST['payable_code'])) {
        $payable_code = $_REQUEST['payable_code'];
        $_SESSION['payable_code'] = $payable_code;
    } elseif (isset($_REQUEST['payable_code'])) {
        $payable_code = $_REQUEST['payable_code'];
        $_SESSION['payable_code'] = $payable_code;
    }
    if (isset($_REQUEST['payable_s_ym'])) {
        $payable_s_ym = $_REQUEST['payable_s_ym'];
        $_SESSION['payable_s_ym'] = $payable_s_ym;
    } elseif (isset($_REQUEST['payable_s_ym'])) {
        $payable_s_ym = $_REQUEST['payable_s_ym'];
        $_SESSION['payable_s_ym'] = $payable_s_ym;
    }
    if (isset($_REQUEST['payable_e_ym'])) {
        $payable_e_ym = $_REQUEST['payable_e_ym'];
        $_SESSION['payable_e_ym'] = $payable_e_ym;
    } elseif (isset($_REQUEST['payable_e_ym'])) {
        $payable_e_ym = $_REQUEST['payable_e_ym'];
        $_SESSION['payable_e_ym'] = $payable_e_ym;
    }
    if (isset($_REQUEST['payable_div'])) {
        $payable_div = $_REQUEST['payable_div'];
        $_SESSION['payable_div'] = $payable_div;
    } elseif (isset($_REQUEST['payable_div'])) {
        $payable_div = $_REQUEST['payable_div'];
        $_SESSION['payable_div'] = $payable_div;
    }
    if (isset($_REQUEST['payable_vendor'])) {
        $payable_vendor = $_REQUEST['payable_vendor'];
        $_SESSION['payable_vendor'] = $payable_vendor;
    } elseif (isset($_REQUEST['payable_vendor'])) {
        $payable_vendor = $_REQUEST['payable_vendor'];
        $_SESSION['payable_vendor'] = $payable_vendor;
    }
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    $_SESSION['str_date'] = $str_date;
    $_SESSION['paya_strdate'] = $str_date;
    $session->add('str_date', $str_date);
} elseif (isset($_SESSION['paya_strdate'])) {
    $str_date = $_SESSION['paya_strdate'];
    $_SESSION['str_date'] = $str_date;
    $session->add('str_date', $str_date);
} else {
    $year  = date('Y') - 5; // ５年前から
    $month = date('m');
    $str_date = $year . $month . '01';
}
if (isset($_REQUEST['end_date'])) {
    $end_date = $_REQUEST['end_date'];
    $_SESSION['end_date'] = $end_date;
    $_SESSION['paya_enddate'] = $end_date;
    $session->add('end_date', $end_date);
} elseif (isset($_SESSION['paya_enddate'])) {
    $end_date = $_SESSION['paya_enddate'];
    $_SESSION['end_date'] = $end_date;
    $session->add('end_date', $end_date);
} else {
    $end_date = '99999999';
}
if (isset($_REQUEST['kamoku'])) {
    $kamoku = $_REQUEST['kamoku'];
    $_SESSION['paya_kamoku'] = $kamoku;
    $session->add('kamoku', $kamoku);
} else {
    if (isset($_SESSION['paya_kamoku'])) {
        $kamoku = $_SESSION['paya_kamoku'];
        $session->add('kamoku', $kamoku);
    } else {
        $kamoku = '';
    }
}
//////////// 表題の設定
$query = "select midsc from miitem where mipn='{$parts_no}'";
if (getUniResult($query, $name) <= 0) {
    $_SESSION['s_sysmsg'] .= 'マスター未登録';    // 後日、parts_cost_form.phpでマスターのチェックを行うように変更予定
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}
$menu->set_caption("部品番号：{$parts_no}　部品名：{$name}");

//////////// SQL 文の where 句を生成する
$search = sprintf("where parts_no='%s' and vendor!='88888'", $parts_no);
$search2 = sprintf("where p.parts_no='%s' and v.vendor!='88888'", $parts_no);

//////////// 一頁の行数
define('PAGE', $cost_page);

//////////// 合計レコード数取得     (対象テーブルの最大数をページ制御に使用)
$query = sprintf('select count(*) from parts_cost_history %s', $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= '合計レコード数の取得に失敗<br>DBの接続を確認！';  // .= メッセージを追加する
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
} elseif ( isset($_REQUEST['page_keep']) ) {                // 現在のページを維持する GETに注意
    $offset = $offset;
} else {
    $offset = 0;                            // 初回の場合は０で初期化
}
$session->add_local('offset', $offset);

//////////// 表形式のデータ表示用のサンプル Query & 初期化
$query = sprintf("
        select
            p.as_regdate            as 登録日,                  -- 0
            p.reg_no                as \"登録No\",              -- 1
            p.vendor                as コード,                  -- 2
            v.name                  as 発注先名,                -- 3
            CASE
                WHEN p.mtl_cond = '1' THEN '自給'
                WHEN p.mtl_cond = '2' THEN '有償'
                WHEN p.mtl_cond = '3' THEN '無償'
            END                     as 条件,                    -- 4
            CASE
                WHEN p.kubun = '1' THEN '継続'
                WHEN p.kubun = '2' THEN '暫定'
                WHEN p.kubun = '3' THEN '今回'
            END                     as 区分,                    -- 5
            p.pro_no                as 工程,                    -- 6
            p.pro_mark              as 記号,                    -- 7
            p.lot_cost              as 単価,                    -- 8
            p.lot_str               as \"LOT開始\",             -- 9
            p.lot_end               as \"LOT終了\",             --10
            p.lot_no                as \"LOT番号\"              --11
        from
            parts_cost_history as p
        left outer join
            vendor_master as v
        using (vendor)
        %s      -- ここに where句の and を挿入できる
        ORDER BY as_regdate DESC, reg_no DESC, lot_no ASC, pro_no ASC
        offset %d limit %d
    ", $search2, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>部品番号:%s <br>単価経歴がありません！</font>", $parts_no);
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?material=1');    // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
    $set_rows  = (-1);          // 初期化(-1=セットしない設定)
    $set_tanka = (-1);          // 有償による合算に対応
    if ($lot_cost > 0) {        // 現在は最初の頁にしか対応していない検討中→対応済み(&& $offset == 0を削除して対応)2007/09/03
        // 2020.07.29 add waki ----------------------------------------------->
        $register_no = GetRegNo($parts_no, $sei_no);
        for( $i=0; $i<$rows; $i++ ) {
            if( $res[$i][1] == $register_no ) {
                break;
            }
        }
        // <-------------------------------------------------------------------
        for (; $i<$rows; $i++) {
//        for ($i=0; $i<$rows; $i++) {
            ///// 単価が同じか？ $res[$i][8]に注意 (登録日が受付日より以下の条件追加 2005/01/13)
            if ( ($res[$i][8] == $lot_cost) && ($res[$i][0] <= $uke_date) ) {
                ///// 発注先が合っているか又は指定されていなければ無視する(2005/01/14)
                if ( ($res[$i][2] == $vendor) || ($vendor == '') ) {
                    $set_rows = $i;     // 合致したレコードをセットする
                    break;
                }
            } else {            // 有償支給による合算に対応
                if ($res[$i][7] == 'NK' || $res[$i][7] == 'MT') {
                    if ( (isset($res[$i+1][4])) && $res[$i+1][4] == '有償') {   // 有償チェック(材料)
                        ///// if文の型合わせのため以下を噛ます floatの場合は Uround()を使用する必要がある
                        $tmp1_cost = number_format($res[$i][8] + $res[$i+1][8], 2);
                        $tmp2_cost = number_format($lot_cost, 2);
                            ///// 以下は何度やってもうまくいかないためコメント(例：26.40 NG)
                            // $tmp1_cost = (double)($res[$i][8] + $res[$i+1][8]);
                            // $tmp2_cost = (double)($lot_cost);
                            // var_dump($tmp_cost, $lot_cost);
                        ///// 合算単価が同じか？ (登録日が受付日より以下の条件追加 2005/01/13)
                        if ( ($tmp1_cost == $tmp2_cost) && ($res[$i][0] <= $uke_date) ) {
                            ///// 発注先が合っているか又は指定されていなければ無視する(2005/01/14)
                            if ( ($res[$i+1][2] == $vendor) || ($vendor == '') ) {
                                $set_rows  = ($i+1);    // +1に注意
                                $set_tanka = $i;
                                break;
                            }
                        }
                    }
                }
            }
        }
    }
    $res[-1][0] = '';   // ダミー
    $res[-1][1] = '';   // ダミー
}

// 単価変更番号(登録番号) 取得
function GetRegNo( $parts_no, $sei_no )
{
    $query = $field = $res = array();

    $query = "
                 SELECT   *
                 FROM     order_plan
                 WHERE    parts_no = '$parts_no' AND sei_no = $sei_no
                 ORDER BY regdate ASC LIMIT 1
             ";
    $res  = array();
    $rows = getResultWithField2( $query, $field, $res );

    if( $rows < 1 ) {
        return "";
    }

    return $res[0][15];
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

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script language="JavaScript">
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
    // document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
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
    font-size:   8pt;
    font-weight: normal;
    font-family: monospace;
}
.pt9 {
    font-size:   9pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10 {
    font-size:   10pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10b {
    font-size:   10pt;
    font-weight: bold;
    font-family: monospace;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
    font-family: monospace;
}
.pt12b {
    font-size:   12pt;
    font-weight: bold;
    font-family: monospace;
}
th {
    background-color: blue;
    color:            yellow;
    font-size:        10pt;
    font-weight:      bold;
    font-family:      monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    /* background-color:#d6d3ce; */
}
.winboxr {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    color:                  red;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
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
-->
</style>
</head>
<body class='margin0' onLoad='set_focus()'>
    <center>
<?php if ($noMenu != 'yes') { ?>
<?php echo $menu->out_title_border() ?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self(), '#mark' ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td align='center' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='次頁'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
<?php } else { ?>
        <div>
            <span class='caption_font'><?php echo $menu->out_caption() ?></span>
            &nbsp;
            <input type='button' name='closeButton' value='閉じる' onclick='window.close();' class='pt11b'>
        </div>
<?php } ?>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                ?>
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                <?php
                $no = 1;    // 登録番号毎の行番号を初期化
                for ($r=0; $r<$rows; $r++) {
                    if ($set_rows == $r) {
                        echo "<tr style='background-color:#ffffc6'><a name='mark'></a>\n";
                    } else {
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    }
                    if ($res[$r][1] != $res[$r-1][1]) { // 登録番号が違うかチェックし行番号を付加する
                        echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>{$no}</div></td>    <!-- 行ナンバーの表示 -->\n";
                        $no += 1;
                    } else {
                        echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>&nbsp;</div></td>    <!-- 行ナンバーの表示 -->\n";
                    }
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        // <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                        switch ($i) {
                        case 0:     // 登録日
                            if ($res[$r][$i] != $res[$r-1][$i]) {
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>　</div></td>\n";
                            }
                            break;
                        case 1:     // 登録番号
                            if ($res[$r][$i] != $res[$r-1][$i]) {
                                echo "<td class='winbox' nowrap align='right'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='right'><div class='pt9'>　</div></td>\n";
                            }
                            break;
                        case 3:     // 発注先名
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                            break;
                        case 8:     // 単価
                            if ($set_tanka == $r) {
                                echo "<td class='winbox' bgcolor='#ffffc6' width='70' nowrap align='right'><div class='pt10b'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            } else {
                                echo "<td class='winbox' width='70' nowrap align='right'><div class='pt10b'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            }
                            break;
                        case  9:    // ロット開始
                        case 10:    // ロット終了
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i]) . "</div></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                        }
                        // <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                    echo "</tr>\n";
                    if (isset($res[$r+1][1])) {
                        // 登録番号かロット番号が変わった場合はトータル単価を取得する
                        if ( ($res[$r][1] != $res[$r+1][1]) || ($res[$r][11] != $res[$r+1][11]) ) {
                            $query = "select sum(lot_cost) from parts_cost_history where reg_no={$res[$r][1]} AND parts_no='{$parts_no}' and lot_no={$res[$r][11]} and vendor!='88888'";
                            $sum_cost = '';     // トータル単価の初期化
                            getUniResult($query, $sum_cost);
                            if ($res[$r][1] == $reg_no && $res[$r][11] == 1) {   // 登録番号が指定されてロットが1の場合はマーカーを付ける
                                echo "<tr style='background-color:#ffffc6'>\n";
                            } else {
                                echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                            }
                            $query = "select d.rate_sign from parts_rate_history as h left outer join rate_div_master as d ON h.rate_div=d.rate_div where h.reg_no={$res[$r][1]} AND h.parts_no='{$parts_no}'";
                            $rate_div = '';     // トータル単価の初期化
                            getUniResult($query, $rate_div);
                            if($rate_div == '') {
                                $rate_div = '\\';       // レート区分登録がなければ円
                            }
                            echo "    <td class='winbox' nowrap align='right' colspan='9'><div class='pt10b'>トータル単価</div></td>\n";
                            echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>", number_format($sum_cost, 2), "</div></td>\n";
                            if ($rate_div == '\\') {
                                echo "    <td class='winbox' nowrap align='center'><div class='pt10b'>", $rate_div, "</div></td>\n";
                            } else {
                                echo "    <td class='winboxr' nowrap align='center'><div class='pt10b'>", $rate_div, "</div></td>\n";
                            }
                            echo "    <td class='winbox' nowrap align='right' colspan='2'><div class='pt9'>&nbsp;</div></td>\n";
                            echo "</tr>\n";
                        }
                    } else {    // 最後のレコード
                        $query = "select sum(lot_cost) from parts_cost_history where reg_no={$res[$r][1]} AND parts_no='{$parts_no}' and lot_no={$res[$r][11]} and vendor!='88888'";
                        $sum_cost = '';     // トータル単価の初期化
                        getUniResult($query, $sum_cost);
                        if ($res[$r][1] == $reg_no && $res[$r][11] == 1) {   // 登録番号が指定されてロットが1の場合はマーカーを付ける
                            echo "<tr style='background-color:#ffffc6'>\n";
                        } else {
                            echo "<tr>\n";
                        }
                        $query = "select d.rate_sign from parts_rate_history as h left outer join rate_div_master as d ON h.rate_div=d.rate_div where h.reg_no={$res[$r][1]} AND h.parts_no='{$parts_no}'";
                        $rate_div = '';     // トータル単価の初期化
                        getUniResult($query, $rate_div);
                        if($rate_div == '') {
                            $rate_div = '\\';       // レート区分登録がなければ円
                        }
                        echo "    <td class='winbox' nowrap align='right' colspan='9'><div class='pt10b'>トータル単価</div></td>\n";
                        echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>", number_format($sum_cost, 2), "</div></td>\n";
                        if ($rate_div == '\\') {
                            echo "    <td class='winbox' nowrap align='center'><div class='pt10b'>", $rate_div, "</div></td>\n";
                        } else {
                            echo "    <td class='winboxr' nowrap align='center'><div class='pt10b'>", $rate_div, "</div></td>\n";
                        }
                        echo "    <td class='winbox' nowrap align='right' colspan='2'><div class='pt9'>&nbsp;</div></td>\n";
                        echo "</tr>\n";
                    }
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
