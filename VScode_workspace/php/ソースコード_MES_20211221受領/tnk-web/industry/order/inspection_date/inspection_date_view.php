<?php
//////////////////////////////////////////////////////////////////////////////
// 検査日数の照会（明細）更新元 UKWLIB/W#HIBCTR                             //
// Copyright (C) 2016-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/01/29 Created   inspection_date_view.php                            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php');    // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 99);                    // site_index=40(生産メニュー) site_id=10(買掛実績)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('検 査 日 数 の 照 会 （ 明 細 ）');
//////////// 表題の設定     下のロジックで処理するためここでは使用しない
// $menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('単価経歴表示',   INDUST . 'parts/parts_cost_view.php');

//////////// セッションのインスタンスを生成
$session = new Session();

if (isset($_REQUEST['material'])) {
    $menu->set_retGET('material', $_REQUEST['material']);
    if (isset($_REQUEST['uke_no'])) {
        $uke_no = $_REQUEST['uke_no'];
        $_SESSION['uke_no'] = $uke_no;
    } else {
        $uke_no = @$_SESSION['uke_no'];     // @在庫経歴の表題等から指定された場合の対応(uke_noなし)
    }
    $current_script = $menu->out_self() . '?material=1';
    if (isset($_SESSION['paya_kamoku'])) {
        unset($_SESSION['paya_kamoku']);        // 単体での科目指定が既にされていればクリアー
    }
    $_SESSION['paya_strdate'] = '20001001';     // 分社化時点
    $_SESSION['paya_enddate'] = '99999999';     // 最新まで
} elseif (isset($_REQUEST['uke_no'])) {     // 在庫経歴(単体から)呼出時の対応
    $uke_no = $_REQUEST['uke_no'];
    $current_script = $menu->out_self();
    if (isset($_SESSION['paya_kamoku'])) {
        unset($_SESSION['paya_kamoku']);        // 単体での科目指定が既にされていればクリアー
    }
    $_SESSION['paya_strdate'] = '20001001';     // 分社化時点
    $_SESSION['paya_enddate'] = '99999999';     // 最新まで
} else {                                    // フォーム(単体から)呼出時の対応
    $uke_no = '';
    $current_script = $menu->out_self();
}

//////////// 日東工器支給品の対応
if (isset($_REQUEST['kei_ym'])) {
    $kei_ym = $_REQUEST['kei_ym'];
    $kei_ym = format_date8($kei_ym);
    $_SESSION['kei_ym'] = $kei_ym;
} else {
    $kei_ym = @$_SESSION['kei_ym'];     // @単価経歴の戻り時の対応(逆の場合は無視する)
}

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// 条件選択フォームからのPOSTデータ取得
if (isset($_REQUEST['parts_no'])) {
    $parts_no = $_REQUEST['parts_no'];
    $_SESSION['paya_parts_no'] = $parts_no;
} else {
    if (isset($_SESSION['paya_parts_no'])) {
        $parts_no = $_SESSION['paya_parts_no'];
    } else {
        $parts_no = '';
    }
}
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];
    $_SESSION['payable_div'] = $div;
} else {
    if (isset($_SESSION['payable_div'])) {
        $div = $_SESSION['payable_div'];
    } else {
        $div = ' ';
    }
}
if (isset($_REQUEST['vendor'])) {
    $vendor = $_REQUEST['vendor'];
    $_SESSION['paya_vendor'] = $vendor;
} else {
    if (isset($_SESSION['paya_vendor'])) {
        $vendor = $_SESSION['paya_vendor'];
    } else {
        $vendor = '';
    }
}
if (isset($_REQUEST['kamoku'])) {
    $kamoku = $_REQUEST['kamoku'];
    $_SESSION['paya_kamoku'] = $kamoku;
} else {
    if (isset($_SESSION['paya_kamoku'])) {
        $kamoku = $_SESSION['paya_kamoku'];
    } else {
        $kamoku = '';
    }
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    $_SESSION['paya_strdate'] = $str_date;
} elseif (isset($_SESSION['paya_strdate'])) {
    $str_date = $_SESSION['paya_strdate'];
} else {
    $year  = date('Y') - 5; // ５年前から
    $month = date('m');
    $str_date = $year . $month . '01';
}
if (isset($_REQUEST['end_date'])) {
    $end_date = $_REQUEST['end_date'];
    $_SESSION['paya_enddate'] = $end_date;
} elseif (isset($_SESSION['paya_enddate'])) {
    $end_date = $_SESSION['paya_enddate'];
} else {
    $end_date = '99999999';
}
if (isset($_REQUEST['paya_page'])) {
    $paya_page = $_REQUEST['paya_page'];
    $_SESSION['payable_page'] = $paya_page;
} else {
    if (isset($_SESSION['payable_page'])) {
        $paya_page = $_SESSION['payable_page'];
    } else {
        $paya_page = 25;
    }
}
if (isset($_REQUEST['ken_num'])) {
    $ken_num = $_REQUEST['ken_num'];
    $_SESSION['paya_ken_num'] = $ken_num;
} else {
    if (isset($_SESSION['paya_ken_num'])) {
        $ken_num = $_SESSION['paya_ken_num'];
    } else {
        $ken_num = '';
    }
}
if ($session->get('str_date') != '') {
    $str_date = $session->get('str_date');
    $_SESSION['str_date'] = $str_date;
}
if ($session->get('end_date') != '') {
    $end_date = $session->get('end_date');
    $_SESSION['end_date'] = $end_date;
}
//////////// 一頁の行数
define('PAGE', $paya_page);

//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口
$search = sprintf("where act_date>=%d and act_date<=%d", $str_date, $end_date);

//////////// SQL 文の where 句を 共用する
if ($parts_no != '') {
    if ($ken_num != '') {
        $search_kin = sprintf("%s and paya.parts_no='%s' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $parts_no, $ken_num);
        $query = "select trim(substr(midsc,1,18)) from miitem where mipn='{$parts_no}' limit 1";
        if (getUniResult($query, $name) <= 0) {
            $name = 'マスター未登録';
        }
        $caption_title = "部品番号：{$parts_no}　<font color='blue'>部品名：{$name}</font>　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "　検査日数：{$ken_num}日";
    } else {
        $search_kin = sprintf("%s and paya.parts_no='%s'", $search, $parts_no);
        $query = "select trim(substr(midsc,1,18)) from miitem where mipn='{$parts_no}' limit 1";
        if (getUniResult($query, $name) <= 0) {
            $name = 'マスター未登録';
        }
        $caption_title = "部品番号：{$parts_no}　<font color='blue'>部品名：{$name}</font>　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "　検査日数：すべて";
    }
} elseif ($div != ' ') {
    if ($vendor != '') {
        if ($ken_num != '') {
            if($div == 'D') {
                $search_kin = sprintf("%s and vendor='%s' and paya.div='C' and kouji_no NOT like 'SC%%' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $vendor, $ken_num);
                $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
                if (getUniResult($query, $name) <= 0) {
                    $name = 'マスター未登録';
                }
                $caption_title = "事業部：カプラ標準　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "発注先：" . $name . "　検査日数：{$ken_num}日";
            } elseif($div == 'S') {
                $search_kin = sprintf("%s and vendor='%s' and paya.div='C' and kouji_no like 'SC%%' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $vendor, $ken_num);
                $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
                if (getUniResult($query, $name) <= 0) {
                    $name = 'マスター未登録';
                }
                $caption_title = "事業部：カプラ特注　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "発注先：" . $name . "　検査日数：{$ken_num}日";
            } else {
                $search_kin = sprintf("%s and vendor='%s' and paya.div='%s' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $vendor, $div, $ken_num);
                $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
                if (getUniResult($query, $name) <= 0) {
                    $name = 'マスター未登録';
                }
                $caption_title = "事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "発注先：" . $name . "　検査日数：{$ken_num}日";
            }
        } else {
            if($div == 'D') {
                $search_kin = sprintf("%s and vendor='%s' and paya.div='C' and kouji_no NOT like 'SC%%'", $search, $vendor);
                $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
                if (getUniResult($query, $name) <= 0) {
                    $name = 'マスター未登録';
                }
                $caption_title = "事業部：カプラ標準　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "発注先：" . $name . "　検査日数：すべて";
            } elseif($div == 'S') {
                $search_kin = sprintf("%s and vendor='%s' and paya.div='C' and kouji_no like 'SC%%'", $search, $vendor);
                $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
                if (getUniResult($query, $name) <= 0) {
                    $name = 'マスター未登録';
                }
                $caption_title = "事業部：カプラ特注　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "発注先：" . $name . "　検査日数：すべて";
            } else {
                $search_kin = sprintf("%s and vendor='%s' and paya.div='%s'", $search, $vendor, $div);
                $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
                if (getUniResult($query, $name) <= 0) {
                    $name = 'マスター未登録';
                }
                $caption_title = "事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "発注先：" . $name . "　検査日数：すべて";
            }
        }
    } else {
        if ($ken_num != '') {
            if($div == 'D') {
                $search_kin = sprintf("%s and paya.div='C' and kouji_no NOT like 'SC%%' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $ken_num);
                $caption_title = "事業部：カプラ標準　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "　検査日数：{$ken_num}日";
            } elseif($div == 'S') {
                $search_kin = sprintf("%s and paya.div='C' and kouji_no like 'SC%%' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $ken_num);
                $caption_title = "事業部：カプラ特注　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "　検査日数：{$ken_num}日";
            } else {
                $search_kin = sprintf("%s and paya.div='%s' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $div, $ken_num);
                $caption_title = "事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "　検査日数：{$ken_num}日";
            }
        } else {
            if($div == 'D') {
                $search_kin = sprintf("%s and paya.div='C' and kouji_no NOT like 'SC%%'", $search);
                $caption_title = "事業部：カプラ標準　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "　検査日数：すべて";
            } elseif($div == 'S') {
                $search_kin = sprintf("%s and paya.div='C' and kouji_no like 'SC%%'", $search);
                $caption_title = "事業部：カプラ特注　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "　検査日数：すべて";
            } else {
                $search_kin = sprintf("%s and paya.div='%s'", $search, $div);
                $caption_title = "事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "　検査日数：すべて";
            }
        }
    }
} else {
    if ($vendor != '') {
        if ($ken_num != '') {
            $search_kin = sprintf("%s and vendor='%s' and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $vendor, $ken_num);
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = 'マスター未登録';
            }
            $caption_title = "事業部：全部門　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "発注先：" . $name . "　検査日数：{$ken_num}日";
        } else {
            $search_kin = sprintf("%s and vendor='%s'", $search, $vendor);
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = 'マスター未登録';
            }
            $caption_title = "事業部：全部門　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "発注先：" . $name . "　検査日数：すべて";
        }
    } else {
        if ($ken_num != '') {
            $search_kin = sprintf("%s and ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))='%d'", $search, $ken_num);
            $caption_title = "事業部：全部門　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "　検査日数：{$ken_num}日";
        } else {
            $search_kin = $search;
            $caption_title = "事業部：全部門　年月：" . format_date($str_date) . '〜' . format_date($end_date) . "　検査日数：すべて";
        }
    }
}

//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
$query = sprintf('select count(*), sum(Uround(order_price * siharai,0)), sum(genpin), sum(siharai) from act_payable as paya left outer join order_plan using(sei_no) LEFT OUTER JOIN parts_stock_master AS m ON (m.parts_no=paya.parts_no) %s', $search_kin);
$res_max = array();
if ( getResult2($query, $res_max) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $maxrows = $res_max[0][0];                  // 合計レコード数の取得
    // $sum_kin = $res_max[0][1];                  // 合計買掛金額の取得
    //$caption_title  .= '　合計金額：' . number_format($res_max[0][1]);   // 合計買掛金額をキャプションタイトルにセット
    //$caption_title  .= '　合計件数：' . number_format($res_max[0][0]);   // 合計買掛件数をキャプションタイトルにセット
    $caption_title2  = '合計金額：' . number_format($res_max[0][1]);   // 合計買掛金額をキャプションタイトルにセット
    $caption_title2 .= '　合計件数：' . number_format($res_max[0][0]);   // 合計買掛件数をキャプションタイトルにセット
    $caption_title2 .= '　現品数計：' . number_format($res_max[0][2], 2);   // 合計現品数をキャプションタイトルにセット
    $caption_title2 .= '　支払数計：' . number_format($res_max[0][3], 2);   // 合計支払数をキャプションタイトルにセット
}

//////////// ページオフセット設定
if ( isset($_REQUEST['forward']) ) {                       // 次頁が押された
    $_SESSION['paya_offset'] += PAGE;
    if ($_SESSION['paya_offset'] >= $maxrows) {
        $_SESSION['paya_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // 次頁が押された
    $_SESSION['paya_offset'] -= PAGE;
    if ($_SESSION['paya_offset'] < 0) {
        $_SESSION['paya_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {               // 現在のページを維持する
    $offset = $_SESSION['paya_offset'];
} else {
    $_SESSION['paya_offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['paya_offset'];

//////////// 表形式のデータ表示用のサンプル Query & 初期化
$query = sprintf("
        SELECT
            -- act_date    as 処理日,
            -- type_no     as \"T\",
            uke_no      as 受付番,          -- 00
            uke_date    as 受付日,          -- 01
            ken_date    as 検収日,          -- 02
             (to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f') as 検査日数,          -- 03
            substr(trim(name), 1, 8)
                        as 発注先名,        -- 04
            paya.parts_no    as 部品番号,        -- 05
            substr(midsc, 1, 12)
                        AS 部品名,          -- 06
            substr(mepnt, 1, 10)
                        AS 親機種,          -- 07
            koutei      as 工程,            -- 08
            mtl_cond    as 条,      -- 条件    09
            order_price as 発注単価,        -- 10
            genpin      as 現品数,          -- 11
            siharai     as 支払数,          -- 12
            Uround(order_price * siharai,0)
                        as 買掛金額,        -- 13
            sei_no      as 製造番号,        -- 14
            paya.div    as 事,              -- 15
            kamoku      as 科,              -- 16
            order_no    as 注文番号,        -- 17
            vendor      as 発注先           -- 18
        FROM
            act_payable AS paya
        LEFT OUTER JOIN
            vendor_master USING(vendor)
        left outer join
            order_plan using(sei_no)
        LEFT OUTER JOIN
            miitem ON (paya.parts_no = mipn)
        LEFT OUTER JOIN
            parts_stock_master AS m ON (m.parts_no=paya.parts_no)
        %s 
        ORDER BY 検査日数 DESC, act_date DESC
        OFFSET %d LIMIT %d
    ", $search_kin, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= '買掛データがありません。';
    if (isset($_REQUEST['material'])) {
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?material=' . $_REQUEST['material']);    // 直前の呼出元へ戻る
    } else {
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    }
    exit();
} else {
    $num = count($field);       // フィールド数取得
}

// 'YY/MM/DD'フォーマットの８桁の日付をYYYYMMDDの８桁にフォーマットして返す。
function format_date8($date8)
{
    if (0 == $date8) {
        $date8 = '--------';    
    }
    if (8 == strlen($date8)) {
        $nen   = substr($date8,0,2);
        $tsuki = substr($date8,3,2);
        $hi    = substr($date8,6,2);
        return '20' . $nen . $tsuki . $hi;
    } else {
        return FALSE;
    }
}

// ここからCSV出力用の準備作業
// ファイル名に日本語をつけると受け渡しでエラーになるので一時英字に変更
if ($div == " ") $act_name = "ALL";
if ($div == "C") $act_name = "C-all";
if ($div == "D") $act_name = "C-toku";
if ($div == "S") $act_name = "C-hyou";
if ($div == "L") $act_name = "L-all";
if ($div == "T") $act_name = "T-all";
if ($div == "NKCT") $act_name = "NKCT";
if ($div == "NKT") $act_name = "NKT";
///// 得意先名のCSV出力用
/*
if ($customer == " ") $c_name = "T-ALL";
if ($customer == "00001") $c_name = "T-NK";
if ($customer == "00002") $c_name = "T-MEDO";
if ($customer == "00003") $c_name = "T-NKT";
if ($customer == "00004") $c_name = "T-MEDOTEC";
if ($customer == "00005") $c_name = "T-SNK";
if ($customer == "00101") $c_name = "T-NKCT";
if ($customer == "00102") $c_name = "T-BRECO";
if ($customer == "99999") $c_name = "T-SHO";
*/
// SQLのサーチ部も日本語を英字に変更。'もエラーになるので/に一時変更
//$csv_search = str_replace('計上日','keidate',$search);
//$csv_search = str_replace('事業部','jigyou',$csv_search);
//$csv_search = str_replace('伝票番号','denban',$csv_search);
//$csv_search = str_replace('得意先','tokui',$csv_search);
$csv_search = str_replace('\'','/',$search);

// CSVファイル名を作成（開始年月-終了年月-事業部）
$outputFile = $str_date . '-' . $end_date . '-' . $act_name;

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
</script>
-->

<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.body.focus();   // F2/F12キーを有効化する対応
    document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
    // document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.form_name.element_name.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
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
    background-color: yellow;
    color:            blue;
    font-size:        10pt;
    font-weight:      bold;
    font-family:      monospace;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:              blue;
    text-decoration:    none;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?=$menu->out_title_border()?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?= $current_script ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <?= $caption_title . "\n" ?>
                        <br>
                        <?= $caption_title2 . "\n" ?>
                        <!--
                        <a href='act_payable_csv.php?csvname=<?php echo $outputFile ?>&csvsearch=<?php echo $csv_search ?>&csvvendor=<?php echo $vendor ?>'>
                            CSV出力
                        </a>
                        -->
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
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                ?>
                    <th class='winbox' nowrap><?= $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
                        <!--  bgcolor='#ffffc6' 薄い黄色 -->
                        <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                <?php
                for ($r=0; $r<$rows; $r++) {
                    if ($uke_no == $res[$r][0]) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else if ($res[$r][18] == '91111' && $kei_ym == $res[$r][2]){  //日東工器購入品への色付け
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><span class='pt10b'>", ($r + $offset + 1), "</span></td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case  6:        // 部品名
                            $res[$r][$i] = mb_convert_kana($res[$r][$i], 'k');
                            $res[$r][$i] = mb_substr($res[$r][$i], 0, 12);
                        case  4:        // 発注先名
                            if ($res[$r][$i] == '') $res[$r][$i] = '&nbsp;';
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case  5:        // 部品番号
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'>&nbsp;</span></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><span class='pt11b'><a href='", $menu->out_action('単価経歴表示'), "?parts_no=", urlencode("{$res[$r][$i]}"), "&lot_cost=", urlencode("{$res[$r][10]}"), "&uke_date={$res[$r][1]}&vendor={$res[$r][18]}&material=1&str_date={$str_date}&end_date={$end_date}#mark'>{$res[$r][$i]}</a></span></td>\n";
                            }
                            break;
                        case  7:        // 親機種
                            if ($res[$r][$i] == '') $res[$r][$i] = '&nbsp;';
                            $res[$r][$i] = mb_convert_kana($res[$r][$i], 'k');
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case 10:        // 発注単価
                        case 11:        // 現品数
                        case 12:        // 支払数
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 2) . "</span></td>\n";
                            break;
                        case 13:
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i]) . "</span></td>\n";
                            break;
                        case 3:
                            echo "<td class='winbox' nowrap align='right'><span class='pt10b'>" . number_format($res[$r][$i]) . "</span></td>\n";
                            break;
                        default:
                            if (trim($res[$r][$i]) == '') $res[$r][$i] = '&nbsp;';
                            echo "<td class='winbox' nowrap align='center'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                        }
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
