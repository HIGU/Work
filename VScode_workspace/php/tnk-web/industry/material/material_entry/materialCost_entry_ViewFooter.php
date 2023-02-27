<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費の登録 materialCost_entry_ViewFooter.php                         //
// Copyright (C) 2008-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2007/05/23 Created   materialCost_entry_ViewFooter.php                   //
// 2007/06/19 フォーカスのタイミングを遅延させて郡司さんのPCに対応          //
// 2007/06/21 JavaScriptのbackgroundColor初期化変更,ショートタグ→標準タグへ//
//            onLoad=set_focus() → onLoad='set_focus();' HTML一部削除 小林 //
//            $menu->out_retF2Script() 追加 小林                            //
// 2007/06/22 $uniqの２重設定を修正。phpタグ無しの{$uniq} を修正 小林       //
// 2007/07/04 契約賃率が登録されない不具合 契約賃率s_rateのnameを修正 大谷  //
// 2007/09/18 E_ALL | E_STRICT へ変更 小林                                  //
// 2007/09/19 elseif (substr($plan_no, 0, 2) == 'ZZ') 25.60 を追加 小林     //
// 2008/02/14 if (substr($assy_no, 0, 1) == 'C') 25.6 else 37.0 を追加      //
// 2008/11/11 賃率変更による仕切価格変更の為賃率を                          //
//            カプラ=57.00 リニア=44.00 に変更                              //
// 2008/11/14 賃率変更以前を登録しようとした時、旧仕切になるように訂正      //
// 2011/03/04 11/04/01以降は、カプラ57→45、リニア44→53に変更              //
// 2015/05/21 機工製品の総材料費登録に対応                                  //
// 2020/02/21 自動登録時に、契約賃率が入らない為の対応 和氣                 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT)
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../../ControllerHTTP_Class.php');     // TNK 全共通 MVC Controller Class
require_once ('../../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 21);                    // site_index=30(生産メニュー) site_id=21(総材料費の登録)

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('総 材 料 費 の 登 録 (工程明細)');
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須
//////////// 自分をフレーム定義に変える
$menu->set_self(INDUST . 'material/material_entry/materialCost_entry_main.php');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('旧総材料費登録',   INDUST . 'material/materialCost_entry_old.php');
//////////// 戻先へのGETデータ設定
$menu->set_retGET('page_keep', 'On');

$request = new Request;
$session = new Session;
//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// 一頁の行数
define('PAGE', '300');

//////////// メッセージ出力フラグ
$msg_flg = 'site';

//////////// エラーログの出力先
$error_log_name = '/tmp/materialCost_entry_error.log';

//////////// 計画番号・製品番号を取得
$plan_no = $session->get('plan_no');
$assy_no = $session->get('assy_no');

//////////// 総材料費の最新登録の戻り先製品番号指定
if (substr($plan_no, 0, 2) == 'ZZ') $menu->set_retGET('assy', $assy_no);

/******** 特注・標準の項目追加 *********/
$sql2 = "
    SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$plan_no}'
";
$sc = '';
getUniResult($sql2, $sc);
if ($sc == 'SC') {
    $plan = '特注';
} else {
    $plan = '標準';
}
//////////// レートを計画番号から取得(後日マスター式に変更予定)
if (substr($plan_no, 0, 1) == 'C') {
    /******** 特注・標準の項目追加 *********/
    $sql2 = "
        SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$plan_no}'
    ";
    $sc = '';
    getUniResult($sql2, $sc);
    if ($sc == 'SC') {
        define('RATE', 25.60);  // カプラ特注
    } else {
        $sql2 = "
            SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        $kan = '';
        getUniResult($sql2, $kan);
        if ($kan < 20071001) {
            define('RATE', 25.60);  // カプラ標準 2007/10/01価格改定以前
        } elseif ($kan < 20110401) {
            define('RATE', 57.00);  // カプラ標準 2007/10/01価格改定以降
        } else {
            define('RATE', 45.00);  // カプラ標準 2011/04/01価格改定以降
        }
    }
} elseif (substr($plan_no, 0, 2) == 'ZZ') {
    if (substr($assy_no, 0, 1) == 'C') {
        if ($kan < 20110401) {
            define('RATE', 57.00);  // カプラ標準 2007/10/01価格改定以降
        } else {
            define('RATE', 45.00);  // カプラ標準 2011/04/01価格改定以降
        }
    } elseif (substr($assy_no, 0, 1) == 'L') {
        if ($kan < 20110401) {
            define('RATE', 44.00);  // リニア 2007/10/01価格改定以降
        } else {
            define('RATE', 53.00);  // リニア 2011/04/01価格改定以降
        }
    } else {
        define('RATE', 50.00);  // ツール
    }
} elseif (substr($plan_no, 0, 1) == 'L') {
    $sql2 = "
        SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
    ";
    $kan = '';
    getUniResult($sql2, $kan);
    if ($kan < 20081001) {
        define('RATE', 37.00);  // リニア 2008/10/01価格改定以前
    } elseif ($kan < 20110401) {
        define('RATE', 44.00);  // リニア 2008/10/01価格改定以降
    } else {
        define('RATE', 53.00);  // リニア 2011/04/01価格改定以降
    }
} else {
    $sql2 = "
        SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
    ";
    $kan = '';
    getUniResult($sql2, $kan);
    define('RATE', 50.00);  // ツール
}

//////////// SQL 文の WHERE 句を 共用する
$search = sprintf("WHERE plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);
// $search = '';

//////////// 合計レコード数・総材料費の取得     (対象データの最大数をページ制御に使用)
$query = sprintf("SELECT count(*), sum(Uround(pro_price * pro_num, 2)) FROM material_cost_history %s", $search);
$res_sum = array();
if ( getResult2($query, $res_sum) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
    $msg_flg = 'alert';
}
$maxrows = $res_sum[0][0];
$sum_kin = $res_sum[0][1];

$query = sprintf("SELECT sum(Uround(pro_num * pro_price, 2)) FROM material_cost_history
                    %s and intext='0'", $search);
if ( getUniResult($query, $ext_kin) <= 0) {  // 内作の総材料費
    $_SESSION['s_sysmsg'] .= "外作総材料費の取得に失敗";      // .= メッセージを追加する
    $msg_flg = 'alert';
}
$query = sprintf("SELECT sum(Uround(pro_num * pro_price, 2)) FROM material_cost_history
                    %s and intext='1'", $search);
if ( getUniResult($query, $int_kin) <= 0) {  // 外作の総材料費
    $_SESSION['s_sysmsg'] .= "内作総材料費の取得に失敗";      // .= メッセージを追加する
    $msg_flg = 'alert';
}

//////////// 計画番号単位の工程明細の作表
$query = sprintf("
        SELECT
            mate.last_user  AS \"Level\",                   -- 0
            parts_no        as 部品番号,                    -- 1
            midsc           as 部品名,                      -- 2
            pro_num         as 使用数,                      -- 3
            pro_no          as 工程,                        -- 4
            pro_mark        as 工程名,                      -- 5
            pro_price       as 工程単価,                    -- 6
            Uround(pro_num * pro_price, 2)
                            as 工程金額,                    -- 7
            CASE
                WHEN intext = '0' THEN '外作'
                WHEN intext = '1' THEN '内作'
                ELSE intext
            END             as 内外作,                      -- 8
            par_parts       as 親番号                       -- 9
        FROM
            -- material_cost_history
            material_cost_level_as('{$plan_no}') AS mate
        LEFT OUTER JOIN
             miitem ON parts_no=mipn
        -- %s 
        -- ORDER BY par_parts ASC, parts_no ASC, pro_no ASC
        
    ", $search);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'><br>現在未登録です！</font>";
    // header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    // exit();
    $num = count($field);       // フィールド数取得
    $final_flg = 0;             // 完了フラグ 0=NG
} else {
    $num = count($field);       // フィールド数取得
    $final_flg = 1;             // 完了フラグ 1=OK
    $query = "SELECT parts_no FROM material_cost_level_as('{$plan_no}')";
    $chk_rows = getResult2($query, $res_chk);
    if ($chk_rows != $maxrows) {
        $_SESSION['s_sysmsg'] .= "レベル表示：{$chk_rows} と実データ：{$maxrows} のレコード数が一致していません！　直接入力メニューを使用して下さい。";    // .= に注意
        $msg_flg = 'alert';
        $old_menu = 'on';
        $_GET['page_keep'] = '1';   // エラーの場合はページを維持するため page_keepを使用
    }
}

////////////// コピーのリンクが押された時  &&を追加 Undefined index対応
if ($request->get('number') != '' && $res[$request->get('number')][0] != '') {
    $c_number = $request->get('number');
    $parts_no  = $res[$c_number][1];
    $pro_num   = $res[$c_number][3];
    $pro_no    = $res[$c_number][4];
    $pro_mark  = $res[$c_number][5];
    $pro_price = $res[$c_number][6];
    $par_parts = $res[$c_number][9];
    if ($res[$c_number][8] == '外作') $intext = '0'; else $intext = '1';
} else {
    $c_number  = '';
    $parts_no  = '';
    $pro_num   = '';
    $pro_no    = '';
    $pro_mark  = '';
    $pro_price = '';
    $par_parts = '';
    $intext    = '0';
}

/////////// 組立費の取得
$query = "SELECT m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate
            FROM material_cost_header WHERE plan_no='{$plan_no}'";
$res_time = array();
if ( getResult2($query, $res_time) > 0 ) {
    $m_time = $res_time[0][0];
    $m_rate = $res_time[0][1];
    $a_time = $res_time[0][2];
    $a_rate = $res_time[0][3];
    $g_time = $res_time[0][4];    
    $g_rate = $res_time[0][5];
    ///// 合計 組立費(社内用)
    $assy_int_price = ( (Uround($m_time * $m_rate, 2)) + 
                        (Uround($a_time * $a_rate, 2)) + 
                        (Uround($g_time * $g_rate, 2)) );
    ///// 対日東工器 契約賃率の組立費
    $assy_time  = $res_time[0][6];
//    $assy_rate  = $res_time[0][7];
    /* 自動登録時に、契約賃率が入らない為の対応 -----------------> */
    // 取得した$assy_rateの値が０の場合、RATEの値に変更する
    if( $res_time[0][7] == 0 ) {
        $assy_rate  = RATE;
        $query = sprintf("UPDATE material_cost_header SET
                        plan_no='{$plan_no}', assy_rate=%01.2f
                        WHERE plan_no='{$plan_no}'", $assy_rate );
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "計画番号：{$plan_no} の契約賃率変更に失敗！";   // .= に注意
            $msg_flg = 'alert';
        }
    } else {
        $assy_rate  = $res_time[0][7];
    }
    /* <---------------------------------------------------------- */
    $assy_price = Uround($assy_time * $assy_rate, 2);
} else {
    $m_time = 0;
    $m_rate = 0;
    $a_time = 0;
    $a_rate = 0;
    $g_time = 0;
    $g_rate = 0;
    $assy_int_price = 0;
    $assy_time  = 0;
    $assy_rate  = RATE;
    $assy_price = 0;
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
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

function chk_cost_entry(obj) {
    obj.parts_no.style.backgroundColor  = '';
    obj.pro_num.style.backgroundColor   = '';
    obj.pro_no.style.backgroundColor    = '';
    obj.pro_mark.style.backgroundColor  = '';
    obj.pro_price.style.backgroundColor = '';
    obj.intext.style.backgroundColor    = '';
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    obj.par_parts.value = obj.par_parts.value.toUpperCase();
    if (obj.parts_no.value.length != 0) {
        if (obj.parts_no.value.length != 9) {
            alert('部品番号の桁数は９桁です。');
            obj.parts_no.focus();
            // obj.parts_no.select();
            obj.parts_no.style.backgroundColor='#ff99cc';
            return false;
        }
    } else {
        alert('部品番号が入力されていません！');
        obj.parts_no.focus();
        obj.parts_no.select();
        obj.parts_no.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.pro_num.value)) ) {
        alert('使用数は数字以外入力出来ません！');
        obj.pro_num.focus();
        obj.pro_num.select();
        obj.pro_num.style.backgroundColor='#ff99cc';
        return false;
    } else {
        if (obj.pro_num.value <= 0) {
            alert('使用数は０より大きい数字を入力して下さい！');
            obj.pro_num.focus();
            obj.pro_num.select();
            obj.pro_num.style.backgroundColor='#ff99cc';
            return false;
        }
        if (obj.pro_num.value > 999.9999) {
            alert('使用数は 0.0001～999.9999 までを入力して下さい！');
            obj.pro_num.focus();
            obj.pro_num.select();
            obj.pro_num.style.backgroundColor='#ff99cc';
            return false;
        }
    }
    if ( !(isDigit(obj.pro_no.value)) ) {
        alert('工程番号は数字以外入力出来ません！');
        obj.pro_no.focus();
        obj.pro_no.select();
        obj.pro_no.style.backgroundColor='#ff99cc';
        return false;
    } else {
        if (obj.pro_no.value <= 0) {
            alert('工程番号は１から始まります。');
            obj.pro_no.focus();
            obj.pro_no.select();
            obj.pro_no.style.backgroundColor='#ff99cc';
            return false;
        }
    }
    obj.pro_mark.value = obj.pro_mark.value.toUpperCase();
    if (obj.pro_mark.value.length != 0) {
        /*****      ///// 工程記号に数字があるためコメント
        if ( !(isABC(obj.pro_mark.value)) ) {
            alert('工程記号はアルファベットです。');
            obj.pro_mark.focus();
            obj.pro_mark.select();
            obj.pro_mark.style.backgroundColor='#ff99cc';
            return false;
        }
        *****/
    } else {
        alert('工程記号が入力されていません！');
        obj.pro_mark.focus();
        obj.pro_mark.select();
        obj.pro_mark.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.pro_price.value)) ) {
        alert('工程単価は数字以外入力出来ません！');
        obj.pro_price.focus();
        obj.pro_price.select();
        obj.pro_price.style.backgroundColor='#ff99cc';
        return false;
    } else if (obj.pro_price.value > 9999999.99 || obj.pro_price.value < 0) {
        alert('工程単価は 0～9999999.99 までを入力して下さい！');
        obj.pro_price.focus();
        obj.pro_price.select();
        obj.pro_price.style.backgroundColor='#ff99cc';
        return false;
    }
    if (!( (obj.intext.value == '0') || (obj.intext.value == '1') )) {
        alert('外作=0 内作=1 のどちらかを入力して下さい。');
        obj.intext.focus();
        obj.intext.select();
        obj.intext.style.backgroundColor='#ff99cc';
        return false;
    }
    return true;
}

function chk_assy_entry(obj) {
    obj.m_time.style.backgroundColor = '';
    obj.m_rate.style.backgroundColor = '';
    obj.a_time.style.backgroundColor = '';
    obj.a_rate.style.backgroundColor = '';
    obj.g_time.style.backgroundColor = '';
    obj.g_rate.style.backgroundColor = '';
    /* 全体チェックのフラグ */
    var flg = false;
        /* 全ての項目の数字入力チェック */
    if ( !(isDigitDot(obj.m_time.value)) ) {
        alert('手作業 工数は数字以外入力出来ません！');
        obj.m_time.focus();
        obj.m_time.select();
        obj.m_time.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.m_rate.value)) ) {
        alert('手作業 賃率は数字以外入力出来ません！');
        obj.m_rate.focus();
        obj.m_rate.select();
        obj.m_rate.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.a_time.value)) ) {
        alert('自動機 工数は数字以外入力出来ません！');
        obj.a_time.focus();
        obj.a_time.select();
        obj.a_time.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.a_rate.value)) ) {
        alert('自動機 賃率は数字以外入力出来ません！');
        obj.a_rate.focus();
        obj.a_rate.select();
        obj.a_rate.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.g_time.value)) ) {
        alert('外注 工数は数字以外入力出来ません！');
        obj.g_time.focus();
        obj.g_time.select();
        obj.g_time.style.backgroundColor='#ff99cc';
        return false;
    }
    if ( !(isDigitDot(obj.g_rate.value)) ) {
        alert('外注 賃率は数字以外入力出来ません！');
        obj.g_rate.focus();
        obj.g_rate.select();
        obj.g_rate.style.backgroundColor='#ff99cc';
        return false;
    }
        /* 手作業のペアー入力チェック */
    if (obj.m_time.value > 0) {
        if (obj.m_rate.value > 0) {
            if (obj.m_time.value > 9999.999) {  // 2006/11/24 999.999→9999.999へ変更
                alert('手作業 工数は 0.001～9999.999 までを入力して下さい！');
                obj.m_time.focus();
                obj.m_time.select();
                obj.m_time.style.backgroundColor='#ff99cc';
                return false;
            }
            if (obj.m_rate.value > 999.99) {
                alert('手作業 賃率は 0.01～999.99 までを入力して下さい！');
                obj.m_rate.focus();
                obj.m_rate.select();
                obj.m_rate.style.backgroundColor='#ff99cc';
                return false;
            }
            flg = true;
        } else {
            alert("手作業 工数が入力されているのに\n手作業 賃率が入力されていません！");
            obj.m_rate.focus();
            obj.m_rate.select();
            obj.m_rate.style.backgroundColor='#ff99cc';
            return false;
        }
    } else {
        if (obj.m_rate.value > 0) {
            alert("手作業 賃率が入力されているのに\n手作業 工数が入力されていません！");
            obj.m_time.focus();
            obj.m_time.select();
            obj.m_time.style.backgroundColor='#ff99cc';
            return false;
        }
    }
        /* 自動機のペアー入力チェック */
    if (obj.a_time.value > 0) {
        if (obj.a_rate.value > 0) {
            if (obj.a_time.value > 999.999) {
                alert('自動機 工数は 0.001～999.999 までを入力して下さい！');
                obj.a_time.focus();
                obj.a_time.select();
                obj.a_time.style.backgroundColor='#ff99cc';
                return false;
            }
            if (obj.a_rate.value > 999.99) {
                alert('自動機 賃率は 0.01～999.99 までを入力して下さい！');
                obj.a_rate.focus();
                obj.a_rate.select();
                obj.a_rate.style.backgroundColor='#ff99cc';
                return false;
            }
            flg = true;
        } else {
            alert("自動機 工数が入力されているのに\n手作業 賃率が入力されていません！");
            obj.a_rate.focus();
            obj.a_rate.select();
            obj.a_rate.style.backgroundColor='#ff99cc';
            return false;
        }
    } else {
        if (obj.a_rate.value > 0) {
            alert("自動機 賃率が入力されているのに\n手作業 工数が入力されていません！");
            obj.a_time.focus();
            obj.a_time.select();
            obj.a_time.style.backgroundColor='#ff99cc';
            return false;
        }
    }
        /* 外注のペアー入力チェック */
    if (obj.g_time.value > 0) {
        if (obj.g_rate.value > 0) {
            if (obj.g_time.value > 999.999) {
                alert('外注 工数は 0.001～999.999 までを入力して下さい！');
                obj.g_time.focus();
                obj.g_time.select();
                obj.g_time.style.backgroundColor='#ff99cc';
                return false;
            }
            if (obj.g_rate.value > 999.99) {
                alert('外注 賃率は 0.01～999.99 までを入力して下さい！');
                obj.g_rate.focus();
                obj.g_rate.select();
                obj.g_rate.style.backgroundColor='#ff99cc';
                return false;
            }
            flg = true;
        } else {
            alert("外注 工数が入力されているのに\n手作業 賃率が入力されていません！");
            obj.g_rate.focus();
            obj.g_rate.select();
            obj.g_rate.style.backgroundColor='#ff99cc';
            return false;
        }
    } else {
        if (obj.g_rate.value > 0) {
            alert("外注 賃率が入力されているのに\n手作業 工数が入力されていません！");
            obj.g_time.focus();
            obj.g_time.select();
            obj.g_time.style.backgroundColor='#ff99cc';
            return false;
        }
    }
        /* 全体のフラグで入力チェック */
    if (!flg) {
        alert('手作業・自動機・外注のどれか１セット以上、入力して下さい！');
        obj.m_time.focus();
        obj.m_time.select();
        obj.m_time.style.backgroundColor='#ff99cc';
        return false;
    } else {
        return true;
    }
}

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    window.setTimeout("document.entry_form.parts_no.focus()", 300);    // 初期入力フォームがある場合はコメントを外す
    window.setTimeout("document.entry_form.parts_no.select()", 300);
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
body {
    background-image:   none;
    overflow-x:         hidden;
    overflow-y:         hidden;
}
.pt9 {
    font:normal     9pt;
    font-family:    monospace;
}
.pt10 {
    font:normal     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.caption_font {
    font-size:      11pt;
    color:          blue;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-wieght:        bold;
    font-family:        monospace;
}
.parts_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     left;
}
.pro_num_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     center;
}
.price_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     right;
}
.entry_font {
    font-size:      11pt;
    font-weight:    normal;
    color:          red;
}
a:hover {
    background-color: gold;
}
a:active {
    background-color: yellow;
}
a {
    font-size:   10pt;
    font-weight: bold;
    color:       blue;
}
-->
</style>
</head>
<body onLoad='set_focus();'>
    <center>
       <!--------------- ここから本文の表を表示する -------------------->
        <table width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                    <!--  bgcolor='#ffffc6' 薄い黄色 -->
                    <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
            <tr>
                <td class='winbox' nowrap colspan='<?php echo $num+1 ?>' align='right'>
                    <div class='pt10'>
                    内作材料費：<?php echo number_format($int_kin, 2) ."\n" ?>
                    外作材料費：<?php echo number_format($ext_kin, 2) ."\n" ?>  
                    合計材料費：<?php echo number_format($sum_kin, 2) ."\n" ?>
                    <br>
                    合計工数：<?php echo number_format($assy_time, 3) ."\n" ?>
                    契約賃率：<?php echo number_format($assy_rate, 2) ."\n" ?>
                    　　組立費：<?php echo number_format($assy_price, 2) ."\n" ?>
                    　総材料費：<?php echo number_format($sum_kin + $assy_price, 2) ."\n" ?>
                    <br>
                    (参考：社内の実際賃率)
                    組立費：<?php echo number_format($assy_int_price, 2) ."\n" ?>
                    　総材料費：<?php echo number_format($sum_kin + $assy_int_price, 2) ."\n" ?>
                    </div>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <form name='entry_form' method='post' action='materialCost_entry_main.php' target='application' onSubmit='return chk_cost_entry(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' nowrap>部品番号</th>
                    <th class='winbox' nowrap>使用数</th>
                    <th class='winbox' nowrap>工程番号</th>
                    <th class='winbox' nowrap>工程名</th>
                    <th class='winbox' nowrap>工程単価</th>
                    <th class='winbox' nowrap>0外作/1内作</th>
                    <th class='winbox' nowrap>親部品番号</th>
                </tr>
                <tr>
                    <a name='entry_point'>
                        <td class='winbox' align='center'>
                            <input type='text' tabindex='1' class='parts_font' name='parts_no' value='<?php echo $parts_no ?>' size='9' maxlength='9' style='ime-mode: disabled;' onKeyUp='baseJS.keyInUpper(this);'>
                        </td>
                    </a>
                    <td class='winbox' align='center'><input type='text' tabindex='2' class='price_font' name='pro_num' value='<?php echo $pro_num ?>' size='7' maxlength='8' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='3' class='pro_num_font' name='pro_no' value='<?php echo $pro_no ?>' size='1' maxlength='1' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='4' class='pro_num_font' name='pro_mark' value='<?php echo $pro_mark ?>' size='2' maxlength='2' style='ime-mode: disabled;' onKeyUp='baseJS.keyInUpper(this);'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='5' class='price_font' name='pro_price' value='<?php echo $pro_price ?>' size='10' maxlength='10' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='6' class='pro_num_font' name='intext' value='<?php echo $intext ?>' size='1' maxlength='1' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='7' class='parts_font' name='par_parts' value='<?php echo $par_parts ?>' size='9' maxlength='9' style='ime-mode: disabled;' onKeyUp='baseJS.keyInUpper(this);'></td>
                </tr>
                <tr>
                    <td class='winbox' colspan='7' align='center'>
                        <input type='submit' tabindex='8' class='entry_font' name='entry' value='追加変更'>
                        <input type='submit' tabindex='9' class='entry_font' name='del' value='削除'>
                        <input type='hidden' name='c_number' value='<?php echo $c_number ?>'>
                        <?php 
                        if ($rows == 0) {
                            echo "<a href='". H_WEB_HOST . $menu->out_self() ."?pre_copy=1' target='application' style='text-decoration:none;' tabindex='10'>
                                    前回のデータをコピー
                                  </a>";
                        }
                        if (isset($old_menu)) {
                            echo "<a href='". $menu->out_action('旧総材料費登録') ."' target='_parent' style='text-decoration:none;'>
                                    直接入力メニューへ
                                  </a>";
                        }
                        ?>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        
        <form name='assy_form' method='post' action='materialCost_entry_main.php' target='_parent' onSubmit='return chk_assy_entry(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' nowrap>手作業工数</th>
                    <th class='winbox' nowrap>手作業賃率</th>
                    <th class='winbox' nowrap>自動機工数</th>
                    <th class='winbox' nowrap>自動機賃率</th>
                    <th class='winbox' nowrap>外注工数</th>
                    <th class='winbox' nowrap>外注賃率</th>
                </tr>
                <tr>
                    <td class='winbox' align='center'><input type='text' tabindex='10' class='price_font' name='m_time' value='<?php echo $m_time ?>' size='7' maxlength='8' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='11' class='price_font' name='m_rate' value='<?php echo $m_rate ?>' size='5' maxlength='6' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='12' class='price_font' name='a_time' value='<?php echo $a_time ?>' size='6' maxlength='7' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='13' class='price_font' name='a_rate' value='<?php echo $a_rate ?>' size='5' maxlength='6' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='14' class='price_font' name='g_time' value='<?php echo $g_time ?>' size='6' maxlength='7' style='ime-mode: disabled;'></td>
                    <td class='winbox' align='center'><input type='text' tabindex='15' class='price_font' name='g_rate' value='<?php echo $g_rate ?>' size='5' maxlength='6' style='ime-mode: disabled;'></td>
                </tr>
                <tr>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit'  tabindex='16'class='entry_font' name='assy_reg' value='追加変更'>
                        <input type='hidden' name='s_rate' value='<?php echo RATE ?>'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        
        <form name='final_form' method='post' action='materialCost_entry_main.php?id=<?php echo $uniq ?>' target='_parent'>
            <?php if ($final_flg == 1) { ?>
            <input type='submit' tabindex='17' class='entry_font' name='final' value='完了'>
            <input type='submit' tabindex='18' class='entry_font' name='all_del' value='一括削除'
                onClick="return confirm('一括削除を実行します。\n\nこの処理は元には戻せません。\n\n実行しても宜しいでしょうか？')"
            >
            <?php } ?>
        </form>
        <?php echo $menu->out_retF2Script() ?>
    </center>
</body>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
