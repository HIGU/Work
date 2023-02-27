<?php
//////////////////////////////////////////////////////////////////////////////
// 買掛ヒストリの照会 ＆ チェック用  更新元 UKWLIB/W#HIBCTR                 //
// Copyright (C) 2003-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/18 Created   act_payable_view.php                                //
// 2003/11/19 自動仕訳確認リストと突合せが出来る様に以下のロジックを追加    //
//            原材料(1)と部品仕掛Ｃ(2-5) 科目(6)- の合計金額 諸口を除外     //
//            リニアの原材料1 を除外                                        //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/05/13 生産用に条件選択が出来る様に変更 (act_payable_form.phpから)   //
// 2004/05/17 start日付の初期化を前月の１日〜に変更 買掛金の合計を表示追加  //
//            発注先(協力工場)の指定を追加                                  //
// 2004/06/01 部品番号にCQ12357-#の様に'#'があるため urlencode()を付加した。//
// 2004/06/02 div='%s' and vendor='%s' → vendor='%s' and div='%s' に変更   //
// 2004/12/07 ディレクトリを階層下の industry/payable に変更                //
// 2004/12/29 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/01/07 $menu->set_retGET('page_keep', $_REQUEST['material']);で統一  //
// 2005/01/12 フォーム(単体から)呼出時の対応$_REQUEST['uke_no']の追加       //
// 2005/01/13 GETパラメータにuke_dateを追加 単価経歴で登録日のチェックに使用//
// 2005/01/14 GETパラメータにvendorを追加 単価経歴で発注先のチェックに使用  //
// 2005/04/25 単体照会時に頁移動で日付範囲が変更される不具合を修正          //
//                〃    買掛 科目番号のAND検索を追加                        //
// 2006/01/16 if ($kamoku != '') → if (trim($kamoku) != '') へ変更         //
// 2006/01/24 リストに部品名を追加 生管吉成さんの依頼                       //
// 2007/02/23 リストに親機種を追加 生管長谷川さん依頼。伴うレイアウト変更   //
// 2007/05/14 日東工器購入品への色付け(kei_ym)     大谷                     //
// 2007/05/17 セッションに年月日が無い場合の対応追加(在庫経歴から買掛照会)  //
// 2007/05/22 日東工器購入品への色付け(kei_ym)の処理を別ロジックへ移動(小林)//
// 2007/09/03 単価経歴照会に(#mark)を追加 (小林)                            //
// 2007/10/01 買掛データが無い場合の戻り先にgetメソッド追加 E_ALL | E_STRICT//
// 2008/06/24 品証依頼により発注件数の表示を追加                       大谷 //
// 2011/12/27 NKCT及びNKT対応の為、条件を追加                               //
//            抽出条件は棚番の先頭が'8'と受付番号の先頭が'Z'                //
//            (受付番号の先頭が'H'のものはNK伝票の為区別できなかった)  大谷 //
// 2013/04/09 協力工場毎の合計金額照会の下部追加の為微調整             大谷 //
// 2013/10/12 csv出力を追加(生管依頼)                                  大谷 //
// 2015/05/21 機工生産に対応                                           大谷 //
// 2015/08/26 caption_titleに現品数計と支払数計を追加（生管小松依頼）       //
//            追加にあたりcaption_title2を追加し２行に分割             大谷 //
// 2016/01/29 各項目が保持されなかったため修正                         大谷 //
// 2016/08/08 mouseoverを追加                                          大谷 //
// 2017/06/30 エラー防止の為、$act_nameの初期化を追加                  大谷 //
// 2018/01/29 カプラ特注・標準を追加                                   大谷 //
// 2018/06/29 多部門のT部品購入に対応                                  大谷 //
// 2019/05/10 部品番号で検索した場合、取引先等の条件が無視されていたので    //
//            すべての条件を加味するよう変更。（小森谷工場長依頼）     大谷 //
// 2019/05/20 テストで終了日を99999999にしていたのを解除               大谷 //
// 2019/06/25 開始日の初期値を7年前に変更。総材料費でないことがある為  大谷 //
// 2020/07/29 部品番号に引数 &sei_no 追加                              和氣 //
// 2020/12/21 機工のL事業部分の抜き出しを中止                          大谷 //
// 2021/01/20 機工のL事業部分の抜き出しをSQL修正で再開                 大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 10);                    // site_index=40(生産メニュー) site_id=10(買掛実績)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('買 掛 実 績 の 照 会');
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
    $session->add('paya_parts_no', $parts_no);
} else {
    $parts_no = $_SESSION['paya_parts_no'];
    $session->add('paya_parts_no', $parts_no);
    ///// 部品番号は必須
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
    $session->add('paya_kamoku', $kamoku);
} elseif (isset($_SESSION['paya_kamoku'])) {
    $kamoku = $_SESSION['paya_kamoku'];
    $session->add('paya_kamoku', $kamoku);
} elseif ($session->get('kamoku') != '') {
    $kamoku = $session->get('kamoku');
    $_SESSION['paya_kamoku'] = $kamoku;
} else {
    $kamoku = '';
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    $_SESSION['paya_strdate'] = $str_date;
    $session->add('str_date', $str_date);
    $session->add('paya_strdate', $str_date);
} elseif ($session->get('str_date') != '') {
    $str_date = $session->get('str_date');
    $_SESSION['paya_strdate'] = $str_date;
    $session->add('str_date', $str_date);
    $session->add('paya_strdate', $str_date);
} else {
    //$year  = date('Y') - 5; // ５年前から
    $year  = date('Y') - 7;  // ７年前から
    $year  = date('Y') - 10; // １０年前から
    $month = date('m');
    $str_date = $year . $month . '01';
}
if (isset($_REQUEST['end_date'])) {
    $end_date = $_REQUEST['end_date'];
    $_SESSION['paya_enddate'] = $end_date;
    $session->add('end_date', $end_date);
    $session->add('paya_enddate', $end_date);
} elseif ($_SESSION['paya_enddate'] != '') {
    $end_date = $_SESSION['paya_enddate'];
    $session->add('end_date', $end_date);
    $session->add('paya_enddate', $end_date);
} elseif ($session->get('end_date') != '') {
    $end_date = $session->get('end_date');
    $_SESSION['paya_enddate'] = $end_date;
    $session->add('end_date', $end_date);
    $session->add('paya_enddate', $end_date);
} else {
    $end_date = '99999999';
}
//$end_date = '99999999';
if (isset($_REQUEST['paya_page'])) {
    $paya_page = $_REQUEST['paya_page'];
    $_SESSION['payable_page'] = $paya_page;
} else {
    if (isset($_SESSION['payable_page'])) {
        $paya_page = $_SESSION['payable_page'];
    } else {
        $paya_page = 23;
    }
}

//////////// 一頁の行数
define('PAGE', $paya_page);

//////////// SQL 文の where 句を 共用する
if ($parts_no != '') {
    
    if ($div != ' ') {
        if ($vendor != '') {
            if($div == 'NKCT') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and vendor='%s' and a.div='C' AND (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $parts_no, $str_date, $end_date, $vendor, '8%', 'Z%', 'H%');
            } elseif($div == 'NKT') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and vendor='%s' and a.div='L' AND (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $parts_no, $str_date, $end_date, $vendor, '8%', 'Z%', 'H%');
            } elseif($div == 'D') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and vendor='%s' and a.div='C' and ((kouji_no NOT like 'SC%%') or (kouji_no IS NULL))", $parts_no, $str_date, $end_date, $vendor, $div);
            } elseif($div == 'S') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and vendor='%s' and a.div='C' and kouji_no like 'SC%%'", $parts_no, $str_date, $end_date, $vendor, $div);
            } else {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and vendor='%s' and a.div='%s'", $parts_no, $str_date, $end_date, $vendor, $div);
            }
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = 'マスター未登録';
            }
            $caption_title = "部品番号：{$parts_no}　事業部：{$div}　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($str_date) . '〜' . format_date($end_date);
        } else {
            if($div == 'NKCT') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='C' and (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $parts_no, $str_date, $end_date, '8%', 'Z%', 'H%');
                $caption_title = "部品番号：{$parts_no}　事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date);
            } elseif($div == 'NKT') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='L' and (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $parts_no, $str_date, $end_date, '8%', 'Z%', 'H%');
                $caption_title = "部品番号：{$parts_no}　事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date);
            /* CC部品除外用だがCC部品も抜き出すよう 大元のprofit_loss_pl_act_save.phpも変更
            } elseif($div == 'T') {
                $search = sprintf("where a.parts_no='%s' and  act_date>=%d and act_date<=%d and (a.div='%s' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s' and (c.miccc = 'E' or c.miccc IS NULL)))", $parts_no, $str_date, $end_date, $div, 'T%');
                $caption_title = "事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date);
            */

            } elseif($div == 'T') {
                //$search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and (a.div='%s' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s'))", $parts_no, $str_date, $end_date, $div, 'T%');
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='%s'", $parts_no, $str_date, $end_date, $div);
                $caption_title = "部品番号：{$parts_no}　事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date); 
            } elseif($div == 'L') {
                //$search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='%s' and a.parts_no not like '%s'", $parts_no, $str_date, $end_date, $div, 'T%');
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='%s'", $parts_no, $str_date, $end_date, $div);
                $caption_title = "部品番号：{$parts_no}　事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date);
            } elseif($div == 'D') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='C' and ((kouji_no NOT like 'SC%%') or (kouji_no IS NULL))", $parts_no, $str_date, $end_date, $div);
                $caption_title = "部品番号：{$parts_no}　事業部：C標準　年月：" . format_date($str_date) . '〜' . format_date($end_date);
            } elseif($div == 'S') {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='C' and kouji_no like 'SC%%'", $parts_no, $str_date, $end_date, $div);
                $caption_title = "部品番号：{$parts_no}　事業部：C特注　年月：" . format_date($str_date) . '〜' . format_date($end_date);
            } else {
                $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and a.div='%s'", $parts_no, $str_date, $end_date, $div);
                $caption_title = "部品番号：{$parts_no}　事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date);
            }
        }
    } else {
        if ($vendor != '') {
            $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d and vendor='%s'", $parts_no, $str_date, $end_date, $vendor);
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = 'マスター未登録';
            }
            $caption_title = "部品番号：{$parts_no}　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($str_date) . '〜' . format_date($end_date);
        } else {
            $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d", $parts_no, $str_date, $end_date);
            $query = "select trim(substr(midsc,1,18)) from miitem where mipn='{$parts_no}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = 'マスター未登録';
            }
            $caption_title = "部品番号：{$parts_no}　<font color='blue'>部品名：{$name}</font>　年月：" . format_date($str_date) . '〜' . format_date($end_date);
        }
    }

    /*
    $search = sprintf("where a.parts_no='%s' and act_date>=%d and act_date<=%d", $parts_no, $str_date, $end_date);
    $query = "select trim(substr(midsc,1,18)) from miitem where mipn='{$parts_no}' limit 1";
    if (getUniResult($query, $name) <= 0) {
        $name = 'マスター未登録';
    }
    $caption_title = "部品番号：{$parts_no}　<font color='blue'>部品名：{$name}</font>　年月：" . format_date($str_date) . '〜' . format_date($end_date);
    */
} elseif ($div != ' ') {
    if ($vendor != '') {
        if($div == 'NKCT') {
            $search = sprintf("where act_date>=%d and act_date<=%d and vendor='%s' and a.div='C' AND (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $str_date, $end_date, $vendor, '8%', 'Z%', 'H%');
        } elseif($div == 'NKT') {
            $search = sprintf("where act_date>=%d and act_date<=%d and vendor='%s' and a.div='L' AND (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $str_date, $end_date, $vendor, '8%', 'Z%', 'H%');
        } elseif($div == 'D') {
            $search = sprintf("where act_date>=%d and act_date<=%d and vendor='%s' and a.div='C' and ((kouji_no NOT like 'SC%%') or (kouji_no IS NULL))", $str_date, $end_date, $vendor, $div);
        } elseif($div == 'S') {
            $search = sprintf("where act_date>=%d and act_date<=%d and vendor='%s' and a.div='C' and kouji_no like 'SC%%'", $str_date, $end_date, $vendor, $div);
        } else {
            $search = sprintf("where act_date>=%d and act_date<=%d and vendor='%s' and a.div='%s'", $str_date, $end_date, $vendor, $div);
        }
        $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
        if (getUniResult($query, $name) <= 0) {
            $name = 'マスター未登録';
        }
        $caption_title = "事業部：{$div}　<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($str_date) . '〜' . format_date($end_date);
    } else {
        if($div == 'NKCT') {
            $search = sprintf("where act_date>=%d and act_date<=%d and a.div='C' and (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $str_date, $end_date, '8%', 'Z%', 'H%');
            $caption_title = "事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date);
        } elseif($div == 'NKT') {
            $search = sprintf("where act_date>=%d and act_date<=%d and a.div='L' and (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $str_date, $end_date, '8%', 'Z%', 'H%');
            $caption_title = "事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date);
        /* CC部品除外用だがCC部品も抜き出すよう 大元のprofit_loss_pl_act_save.phpも変更
        } elseif($div == 'T') {
            $search = sprintf("where act_date>=%d and act_date<=%d and (a.div='%s' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s' and (c.miccc = 'E' or c.miccc IS NULL)))", $str_date, $end_date, $div, 'T%');
            $caption_title = "事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date);
        */
        } elseif($div == 'T') {
            // 旧ツール用
            //$search = sprintf("where act_date>=%d and act_date<=%d and (a.div='%s' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s'))", $str_date, $end_date, $div, 'T%');
            // ツールなし用
            //$search = sprintf("where act_date>=%d and act_date<=%d and a.div='%s'", $str_date, $end_date, $div);
            // 一部ツール用
            $search = sprintf("where act_date>=%d and act_date<=%d and (a.div='%s' or (a.div<>'T' and a.div<>'C' and a.parts_no like '%s' and ( mepnt like 'ADR%%' or mepnt like 'L-25%%' )))", $str_date, $end_date, $div, 'T%');
            $caption_title = "事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date); 
        } elseif($div == 'L') {
            //$search = sprintf("where act_date>=%d and act_date<=%d and a.div='%s' and a.parts_no not like '%s'", $str_date, $end_date, $div, 'T%');
            //$search = sprintf("where act_date>=%d and act_date<=%d and a.div='%s' and (mepnt not like 'ADR%%' or mepnt not like 'L-25%%')", $str_date, $end_date, $div);
            $search = sprintf("where act_date>=%d and act_date<=%d and a.div='%s' and (mepnt is NULL or mepnt not like 'ADR%%' and mepnt not like 'L-25%%')", $str_date, $end_date, $div);
            //$search = sprintf("where act_date>=%d and act_date<=%d and a.div='%s'", $str_date, $end_date, $div);
            $caption_title = "事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date);
        } elseif($div == 'D') {
            $search = sprintf("where act_date>=%d and act_date<=%d and a.div='C' and ((kouji_no NOT like 'SC%%') or (kouji_no IS NULL))", $str_date, $end_date, $div);
            $caption_title = "事業部：C標準　年月：" . format_date($str_date) . '〜' . format_date($end_date);
        } elseif($div == 'S') {
            $search = sprintf("where act_date>=%d and act_date<=%d and a.div='C' and kouji_no like 'SC%%'", $str_date, $end_date, $div);
            $caption_title = "事業部：C特注　年月：" . format_date($str_date) . '〜' . format_date($end_date);
        } else {
            $search = sprintf("where act_date>=%d and act_date<=%d and a.div='%s'", $str_date, $end_date, $div);
            $caption_title = "事業部：{$div}　年月：" . format_date($str_date) . '〜' . format_date($end_date);
        }
    }
} else {
    if ($vendor != '') {
        $search = sprintf("where act_date>=%d and act_date<=%d and vendor='%s'", $str_date, $end_date, $vendor);
        $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
        if (getUniResult($query, $name) <= 0) {
            $name = 'マスター未登録';
        }
        $caption_title = "<font color='blue'>協力工場：{$name}</font>　年月：" . format_date($str_date) . '〜' . format_date($end_date);
    } else {
        $search = sprintf("where act_date>=%d and act_date<=%d", $str_date, $end_date);
        $caption_title = '年月：' . format_date($str_date) . '〜' . format_date($end_date);
    }
}
///// 買掛 科目 指定を追加
if (trim($kamoku) != '') {
    $search .= " and kamoku = {$kamoku}";
}

//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
//$query = sprintf('select count(*), sum(Uround(order_price * siharai,0)), sum(genpin), sum(siharai) from act_payable as a LEFT OUTER JOIN parts_stock_master AS m ON (m.parts_no=a.parts_no) LEFT OUTER JOIN order_plan AS o USING(sei_no) LEFT OUTER JOIN miccc AS c ON (c.mipn=a.parts_no) %s', $search);
$query = sprintf('select count(*), sum(Uround(order_price * siharai,0)), sum(genpin), sum(siharai) from act_payable as a LEFT OUTER JOIN parts_stock_master AS m ON (m.parts_no=a.parts_no) LEFT OUTER JOIN order_plan AS o USING(sei_no) LEFT OUTER JOIN miitem ON (mipn = a.parts_no) %s', $search);
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
            substr(trim(name), 1, 8)
                        as 発注先名,        -- 03
            a.parts_no    as 部品番号,        -- 04
            substr(midsc, 1, 12)
                        AS 部品名,          -- 05
            substr(mepnt, 1, 10)
                        AS 親機種,          -- 06
            koutei      as 工程,            -- 07
            mtl_cond    as 条,      -- 条件    08
            order_price as 発注単価,        -- 09
            genpin      as 現品数,          -- 10
            siharai     as 支払数,          -- 11
            Uround(order_price * siharai,0)
                        as 買掛金額,        -- 12
            sei_no      as 製造番号,        -- 13
            a.div       as 事,              -- 14
            kamoku      as 科,              -- 15
            order_no    as 注文番号,        -- 16
            vendor      as 発注先,          -- 17
            o.kouji_no  as 工事番号         -- 18
        FROM
            act_payable AS a
        LEFT OUTER JOIN
            vendor_master USING(vendor)
        LEFT OUTER JOIN
            miitem ON (parts_no = mipn)
        LEFT OUTER JOIN
            parts_stock_master AS m ON (m.parts_no=a.parts_no)
        LEFT OUTER JOIN
            order_plan AS o USING(sei_no)
        LEFT OUTER JOIN 
            miccc AS c ON (c.mipn=a.parts_no)
        %s 
        ORDER BY act_date DESC
        OFFSET %d LIMIT %d
    ", $search, $offset, PAGE);       // 共用 $search で検索
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
$act_name = "";                             // 初期化
if ($div == " ") $act_name = "ALL";
if ($div == "") $act_name = "ALL";
if ($div == "C") $act_name = "C-all";
if ($div == "D") $act_name = "C-hyo";
if ($div == "S") $act_name = "C-toku";
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
                        <a href='act_payable_csv.php?csvname=<?php echo $outputFile ?>&csvsearch=<?php echo $csv_search ?>&csvvendor=<?php echo $vendor ?>'>
                            CSV出力
                        </a>
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
                    } else if ($res[$r][17] == '91111' && $kei_ym == $res[$r][2]){  //日東工器購入品への色付け
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><span class='pt10b'>", ($r + $offset + 1), "</span></td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case  5:        // 部品名
                            $res[$r][$i] = mb_convert_kana($res[$r][$i], 'k');
                            $res[$r][$i] = mb_substr($res[$r][$i], 0, 12);
                        case  3:        // 発注先名
                            if ($res[$r][$i] == '') $res[$r][$i] = '&nbsp;';
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case  4:        // 部品番号
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='center'>&nbsp;</td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><span class='pt11b'><a href='", $menu->out_action('単価経歴表示'), "?parts_no=", urlencode("{$res[$r][$i]}"), "&lot_cost=", urlencode("{$res[$r][9]}"), "&uke_date={$res[$r][1]}&vendor={$res[$r][17]}&sei_no={$res[$r][13]}&material=1&str_date={$str_date}&end_date={$end_date}#mark'>{$res[$r][$i]}</a></span></td>\n";
                            }
                            break;
                        case  6:        // 親機種
                            if ($res[$r][$i] == '') $res[$r][$i] = '&nbsp;';
                            $res[$r][$i] = mb_convert_kana($res[$r][$i], 'k');
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case  9:        // 発注単価
                        case 10:        // 現品数
                        case 11:        // 支払数
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 2) . "</span></td>\n";
                            break;
                        case 12:
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i]) . "</span></td>\n";
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
