<?php
//////////////////////////////////////////////////////////////////////////////
// A伝状況の照会 ＆ チェック用  更新元 UKWLIB/W#MIADIMDE                    //
// Copyright (C) 2016-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/03/25 Created   aden_details_view.php                               //
// 2017/06/14 計画No.に引当部品構成表へのリンクを追加                       //
// 2017/08/10 計画完了済・未完了の条件を追加                                //
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
$menu->set_site(30, 99);                    // site_index=40(生産メニュー) site_id=10(買掛実績)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('Ａ 伝 状 況 の 照 会');
//////////// 表題の設定     下のロジックで処理するためここでは使用しない
// $menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('単価経歴表示',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('総材料費照会',   INDUST . 'material/materialCost_view_assy.php');
$menu->set_action('引当構成表の表示',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');

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
if (isset($_REQUEST['finish_del'])) {
    $finish_del = $_REQUEST['finish_del'];
    $_SESSION['payable_finishdel'] = $finish_del;
} else {
    if (isset($_SESSION['payable_finishdel'])) {
        $finish_del = $_SESSION['payable_finishdel'];
    } else {
        $finish_del = ' ';
    }
}
if (isset($_REQUEST['deli_com'])) {
    $deli_com = $_REQUEST['deli_com'];
    $_SESSION['payable_delicom'] = $deli_com;
} else {
    if (isset($_SESSION['payable_delicom'])) {
        $deli_com = $_SESSION['payable_delicom'];
    } else {
        $deli_com = ' ';
    }
}
if (isset($_REQUEST['answer'])) {
    $answer = $_REQUEST['answer'];
    $_SESSION['payable_answer'] = $answer;
} else {
    if (isset($_SESSION['payable_answer'])) {
        $answer = $_SESSION['payable_answer'];
    } else {
        $answer = ' ';
    }
}
if (isset($_REQUEST['finish'])) {
    $finish = $_REQUEST['finish'];
    $_SESSION['payable_finish'] = $finish;
} else {
    if (isset($_SESSION['payable_finish'])) {
        $finish = $_SESSION['payable_finish'];
    } else {
        $finish = ' ';
    }
}
if (isset($_REQUEST['kouji_no'])) {
    $kouji_no = $_REQUEST['kouji_no'];
    $_SESSION['payable_koujino'] = $kouji_no;
} else {
    if (isset($_SESSION['payable_koujino'])) {
        $kouji_no = $_SESSION['payable_koujino'];
    } else {
        $kouji_no = ' ';
    }
}
if (isset($_REQUEST['order'])) {
    $order = $_REQUEST['order'];
    $_SESSION['payable_order'] = $order;
} else {
    if (isset($_SESSION['payable_order'])) {
        $order = $_SESSION['payable_order'];
    } else {
        $order = ' ';
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
if (isset($_REQUEST['lt_str_date'])) {
    $lt_str_date = $_REQUEST['lt_str_date'];
    $_SESSION['paya_ltstrdate'] = $lt_str_date;
    $session->add('lt_str_date', $lt_str_date);
} elseif ($session->get('lt_str_date') != '') {
    $lt_str_date = $session->get('lt_str_date');
    $_SESSION['paya_ltstrdate'] = $lt_str_date;
} else {
    $lt_str_date = '';
}
if (isset($_REQUEST['lt_end_date'])) {
    $lt_end_date = $_REQUEST['lt_end_date'];
    $_SESSION['paya_ltenddate'] = $lt_end_date;
    $session->add('lt_end_date', $lt_end_date);
} elseif ($session->get('lt_end_date') != '') {
    $lt_end_date = $session->get('lt_end_date');
    $_SESSION['paya_ltenddate'] = $lt_end_date;
} else {
    $lt_end_date = '';
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    $_SESSION['paya_strdate'] = $str_date;
    $session->add('str_date', $str_date);
//} elseif (isset($_SESSION['paya_strdate'])) {
//    $str_date = $_SESSION['paya_strdate'];
//    $session->add('str_date', $str_date);
//    $str_date = '20150901';
} elseif ($session->get('str_date') != '') {
    $str_date = $session->get('str_date');
    $_SESSION['paya_strdate'] = $str_date;
} else {
    $year  = date('Y') - 5; // ５年前から
    $month = date('m');
    $str_date = $year . $month . '01';
}
if (isset($_REQUEST['end_date'])) {
    $end_date = $_REQUEST['end_date'];
    $_SESSION['paya_enddate'] = $end_date;
    $session->add('end_date', $end_date);
//} elseif (isset($_SESSION['paya_enddate'])) {
//    $end_date = $_SESSION['paya_enddate'];
//    $session->add('end_date', $end_date);
} elseif ($session->get('end_date') != '') {
    $end_date = $session->get('end_date');
    $_SESSION['paya_enddate'] = $end_date;
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
        $paya_page = 23;
    }
}

//////////// 一頁の行数
define('PAGE', $paya_page);

// デフォルトの検索条件とタイトルの設定
$search        = "where a.receive_day>={$str_date} and a.receive_day<={$end_date}";
$caption_title = '年月：' . format_date($str_date) . '～' . format_date($end_date);
$caption_flg   = 0;             // タイトルの改行タイミングを計る為のフラグ

// ASSY No.の指定がある場合
if ($parts_no != '') {
    $search .= " and a.parts_no='{$parts_no}'";
    $query = "select trim(substr(midsc,1,18)) from miitem where mipn='{$parts_no}' limit 1";
    if (getUniResult($query, $name) <= 0) {
        $name = 'マスター未登録';
    }
    $caption_title .= "　部品番号：{$parts_no}　<font color='blue'>部品名：{$name}</font>";
    $caption_flg    = 1;
}

// A伝回答状況の指定がある場合
if ($answer != ' ') {
    if ($answer == 'Y') {
        $search .= " and answer_day<>0";
        $caption_title .= "　回答状況：<font color='blue'>回答済</font>";
        if ($caption_flg == 1) {
            $caption_flg = 9;
        } else {
            $caption_flg = 2;
        }
    } else {
        $search .= " and answer_day=0";
        $caption_title .= "　回答状況：<font color='red'>未回答</font>";
        if ($caption_flg == 1) {
            $caption_flg = 9;
        } else {
            $caption_flg = 2;
        }
    }
}

// 計画完了状況の指定がある場合
if ($finish != ' ') {
    if ($finish == 'Y') {
        $search .= " and (finish_day IS NOT NULL or spare1='U')";
        $caption_title .= "　完了状況：<font color='blue'>完了済</font>";
        if ($caption_flg == 1) {
            $caption_flg = 9;
        } else {
            $caption_flg = 2;
        }
    } else {
        $search .= " and finish_day IS NULL and spare1<>'U'";
        $caption_title .= "　完了状況：<font color='red'>未完了</font>";
        if ($caption_flg == 1) {
            $caption_flg = 9;
        } else {
            $caption_flg = 2;
        }
    }
}

// 納期コメントの指定がある場合
if ($deli_com != ' ') {
    if ($deli_com == 'Y') {
        $search .= " and espoir_deli=delivery";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　納期コメ：希望通り";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　納期コメ：希望通り";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　納期コメ：希望通り";
        } else {
            $caption_flg = 10;
            $caption_title .= "　<BR>納期コメ：希望通り";
        }
    } elseif ($deli_com == 'N') {
        $search .= " and deli_com = 0 and espoir_deli<>delivery";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　納期コメ：未入力";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　納期コメ：未入力";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　納期コメ：未入力";
        } else {
            $caption_flg = 10;
            $caption_title .= "　<BR>納期コメ：未入力";
        }
    } elseif ($deli_com == '1') {
        $search .= " and deli_com = 1";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　納期コメ：部品遅れ";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　納期コメ：部品遅れ";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　納期コメ：部品遅れ";
        } else {
            $caption_flg = 10;
            $caption_title .= "　<BR>納期コメ：部品遅れ";
        }
    } elseif ($deli_com == '2') {
        $search .= " and deli_com = 2";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　納期コメ：設計変更";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　納期コメ：設計変更";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　納期コメ：設計変更";
        } else {
            $caption_flg = 10;
            $caption_title .= "　<BR>納期コメ：設計変更";
        }
    } elseif ($deli_com == '3') {
        $search .= " and deli_com = 3";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　納期コメ：L/T不足";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　納期コメ：L/T不足";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　納期コメ：L/T不足";
        } else {
            $caption_flg = 10;
            $caption_title .= "　<BR>納期コメ：L/T不足";
        }
    } elseif ($deli_com == '4') {
        $search .= " and deli_com = 4";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　納期コメ：伝送遅れ";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　納期コメ：伝送遅れ";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　納期コメ：伝送遅れ";
        } else {
            $caption_flg = 10;
            $caption_title .= "　<BR>納期コメ：伝送遅れ";
        }
    } elseif ($deli_com == '5') {
        $search .= " and deli_com = 5";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　納期コメ：その他";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　納期コメ：その他";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　納期コメ：その他";
        } else {
            $caption_flg = 10;
            $caption_title .= "　<BR>納期コメ：その他";
        }
    }
}

// 工番の指定がある場合
if ($kouji_no != ' ') {
    if ($kouji_no == 'S') {
        $search .= " and kouji_no LIKE 'SC%'";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　工事番号：<font color='blue'>SCのみ</font>";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　工事番号：<font color='blue'>SCのみ</font>";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　工事番号：<font color='blue'>SCのみ</font>";
        } elseif ($caption_flg == 3) {
            $caption_flg = 4;
            $caption_title .= "　工事番号：<font color='blue'>SCのみ</font>";
        } elseif ($caption_flg == 9) {
            $caption_flg = 10;
            $caption_title .= "<BR>工事番号：<font color='blue'>SCのみ</font>";
        } else {
            $caption_flg = 11;
            $caption_title .= "　工事番号：<font color='blue'>SCのみ</font>";
        }
    } elseif ($kouji_no == 'C') {
        $search .= " and kouji_no LIKE 'CQ%'";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　工事番号：<font color='blue'>CQのみ</font>";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　工事番号：<font color='blue'>CQのみ</font>";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　工事番号：<font color='blue'>CQのみ</font>";
        } elseif ($caption_flg == 3) {
            $caption_flg = 4;
            $caption_title .= "　工事番号：<font color='blue'>CQのみ</font>";
        } elseif ($caption_flg == 9) {
            $caption_flg = 10;
            $caption_title .= "<BR>工事番号：<font color='blue'>CQのみ</font>";
        } else {
            $caption_flg = 11;
            $caption_title .= "　工事番号：<font color='blue'>CQのみ</font>";
        }
    } elseif ($kouji_no == 'SCQ') {
        $search .= " and (kouji_no LIKE 'SC%' or kouji_no LIKE 'CQ%')";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　工事番号：<font color='blue'>SC+CQ</font>";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　工事番号：<font color='blue'>SC+CQ</font>";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　工事番号：<font color='blue'>SC+CQ</font>";
        } elseif ($caption_flg == 3) {
            $caption_flg = 4;
            $caption_title .= "　工事番号：<font color='blue'>SC+CQ</font>";
        } elseif ($caption_flg == 9) {
            $caption_flg = 10;
            $caption_title .= "<BR>工事番号：<font color='blue'>SC+CQ</font>";
        } else {
            $caption_flg = 11;
            $caption_title .= "　工事番号：<font color='blue'>SC+CQ</font>";
        }
    } elseif ($kouji_no == 'N') {
        $search .= " and kouji_no =''";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　工事番号：<font color='blue'>工番なし</font>";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　工事番号：<font color='blue'>工番なし</font>";
        } elseif ($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　工事番号：<font color='blue'>工番なし</font>";
        } elseif ($caption_flg == 3) {
            $caption_flg = 4;
            $caption_title .= "　工事番号：<font color='blue'>工番なし</font>";
        } elseif ($caption_flg == 9) {
            $caption_flg = 10;
            $caption_title .= "<BR>工事番号：<font color='blue'>工番なし</font>";
        } else {
            $caption_flg = 11;
            $caption_title .= "　工事番号：<font color='blue'>工番なし</font>";
        }
    }
}

// 納期L/T差の指定がある場合
if ($lt_str_date !='') {
    $search .= " and a.lt_diff>={$lt_str_date} and a.lt_diff<={$lt_end_date}";
    if ($caption_flg == 0) {
        $caption_flg = 2;
        $caption_title .= "　L/T差：" . $lt_str_date . '～' . $lt_end_date;
    } elseif ($caption_flg == 1) {
        $caption_flg = 9;
        $caption_title .= "　L/T差：" . $lt_str_date . '～' . $lt_end_date;
    } elseif($caption_flg == 2) {
        $caption_flg = 3;
        $caption_title .= "　L/T差：" . $lt_str_date . '～' . $lt_end_date;
    } elseif($caption_flg == 3) {
        $caption_flg = 4;
        $caption_title .= "　L/T差：" . $lt_str_date . '～' . $lt_end_date;
    } elseif($caption_flg == 4) {
        $caption_flg = 10;
        $caption_title .= "<BR>L/T差：" . $lt_str_date . '～' . $lt_end_date;
    } elseif($caption_flg == 9) {
        $caption_flg = 10;
        $caption_title .= "<BR>L/T差：" . $lt_str_date . '～' . $lt_end_date;
    } else {
        $caption_flg = 11;
        $caption_title .= "　L/T差：" . $lt_str_date . '～' . $lt_end_date;
    }
}

// 納期遅れの指定がある場合
if ($finish_del !='') {
    if ($finish_del == 'D') {
        $search .= " and (finish_del > 0 OR (a.delivery < to_char(current_date,'YYYYMMDD') and spare1 = 'B'))";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　納期遅れ：納期遅れ";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　納期遅れ：納期遅れ";
        } elseif($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　納期遅れ：納期遅れ";
        } elseif ($caption_flg == 3) {
            $caption_title .= "　納期遅れ：納期遅れ";
            $caption_flg = 4;
        } elseif ($caption_flg == 10) {
            $caption_title .= "　納期遅れ：納期遅れ";
            $caption_flg = 11;
        } elseif ($caption_flg == 11) {
            $caption_title .= "　納期遅れ：納期遅れ";
            $caption_flg = 12;
        } elseif ($caption_flg == 4) {
            $caption_title .= "<BR>納期遅れ：納期遅れ";
            $caption_flg = 10;
        } elseif ($caption_flg == 9) {
            $caption_title .= "<BR>納期遅れ：納期遅れ";
            $caption_flg = 10;
        } else {
            $caption_title .= "　納期遅れ：納期遅れ";
        }
    } elseif ($finish_del == 'Y') {
        $search .= " and finish_del = 0 and spare1 <> 'B'";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　納期遅れ：納期通り";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　納期遅れ：納期通り";
        } elseif($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　納期遅れ：納期通り";
        } elseif ($caption_flg == 3) {
            $caption_title .= "　納期遅れ：納期通り";
            $caption_flg = 4;
        } elseif ($caption_flg == 10) {
            $caption_title .= "　納期遅れ：納期通り";
            $caption_flg = 11;
        } elseif ($caption_flg == 11) {
            $caption_title .= "　納期遅れ：納期通り";
            $caption_flg = 12;
        } elseif ($caption_flg == 4) {
            $caption_title .= "<BR>納期遅れ：納期通り";
            $caption_flg = 10;
        } elseif ($caption_flg == 9) {
            $caption_title .= "<BR>納期遅れ：納期通り";
            $caption_flg = 10;
        } else {
            $caption_title .= "　納期遅れ：納期通り";
        }
    } elseif ($finish_del == 'A') {
        $search .= " and finish_del < 0 and spare1 <> 'B'";
        if ($caption_flg == 0) {
            $caption_flg = 2;
            $caption_title .= "　納期遅れ：納期前倒";
        } elseif ($caption_flg == 1) {
            $caption_flg = 9;
            $caption_title .= "　納期遅れ：納期前倒";
        } elseif($caption_flg == 2) {
            $caption_flg = 3;
            $caption_title .= "　納期遅れ：納期前倒";
        } elseif ($caption_flg == 3) {
            $caption_title .= "　納期遅れ：納期前倒";
            $caption_flg = 4;
        } elseif ($caption_flg == 10) {
            $caption_title .= "　納期遅れ：納期前倒";
            $caption_flg = 11;
        } elseif ($caption_flg == 11) {
            $caption_title .= "　納期遅れ：納期前倒";
            $caption_flg = 12;
        } elseif ($caption_flg == 4) {
            $caption_title .= "<BR>納期遅れ：納期前倒";
            $caption_flg = 10;
        } elseif ($caption_flg == 9) {
            $caption_title .= "<BR>納期遅れ：納期前倒";
            $caption_flg = 10;
        } else {
            $caption_title .= "　納期遅れ：納期前倒";
        }
    }
}

// 表示順番の設定

if ($order == ' ') {            // デフォルト A伝受注日(古) → ASSY No. → 希望納期(古)
    $order = "receive_day ASC, parts_no ASC, espoir_deli ASC";
} elseif ($order == '1') {      // 希望納期順 希望納期(古) → A伝受注日(古) → ASSY No.
    $order = "espoir_deli ASC, receive_day ASC, parts_no ASC";
} elseif ($order == '2') {      // L/T差順    L/T差(大) → A伝受注日(古) → ASSY No. → 希望納期(古)
    $order = "lt_diff DESC, receive_day ASC, parts_no ASC, espoir_deli ASC";
} elseif ($order == '3') {      // 完成遅れ順 完成遅れ(大) → A伝受注日(古) → ASSY No. → 希望納期(古)
    $order = "finish_del DESC, receive_day ASC, parts_no ASC, espoir_deli ASC";
}

//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
$query = sprintf('select count(*) from aden_details_master as a %s', $search);
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
    if ($caption_flg == 4) {
        $caption_title .= '<BR>合計件数：' . number_format($res_max[0][0]);   // 合計買掛件数をキャプションタイトルにセット
    } elseif ($caption_flg == 9) {
        $caption_title .= '<BR>合計件数：' . number_format($res_max[0][0]);   // 合計買掛件数をキャプションタイトルにセット
    } else {
        $caption_title .= '　合計件数：' . number_format($res_max[0][0]);   // 合計買掛件数をキャプションタイトルにセット
    }
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
            publish_day AS A伝発行日,       -- 00
            receive_day AS A伝受注日,       -- 01
            aden_no     AS A伝No,           -- 02
            parts_no    AS ASSYNo,          -- 03
            substr(sale_name, 1, 20)
                        AS 製品名,          -- 04
            plan_no     AS 計画No,          -- 05
            kouji_no    AS SC工番,          -- 06
            order_q     AS 数量,            -- 07
            espoir_deli AS 希望納期,        -- 08
            answer_day  AS A伝回答日,       -- 09
            ans_day_lt  AS A伝回答LT,       -- 10
            delivery    AS 回答納期,        -- 11
            espoir_lt   AS 希望LT,          -- 12
            ans_lt      AS 納回答LT,        -- 13
            lt_diff     AS LT差,            -- 14
            order_price AS 販売価格,        -- 15
            finish_day  AS 実完成日,        -- 16
            finish_del  AS 完成遅れ,        -- 17
            deli_com    AS 納期コメント,    -- 18
            comment     AS 備考,            -- 19
            spare1      AS 分納区分         -- 20
        FROM
            aden_details_master AS a
        %s 
        ORDER BY %s
        OFFSET %d LIMIT %d
    ", $search, $order, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'A伝データがありません。';
    if (isset($_REQUEST['material'])) {
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?material=' . $_REQUEST['material']);    // 直前の呼出元へ戻る
    } else {
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    }
    exit();
} else {
    $num = count($field);       // フィールド数取得
    $num = $num - 1;            // 分納区分は非表示のため
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
// 'YYYYMMDD'フォーマットの８桁の日付をYYYY/MM/DDの10桁にフォーマットして返す。
function format_date10($date8)
{
    if (0 == $date8) {
        $date8 = '----/--/--';    
    }
    if (8 == strlen($date8)) {
        $nen   = substr($date8,0,4);
        $tsuki = substr($date8,4,2);
        $hi    = substr($date8,6,2);
        return $nen . '/' . $tsuki . '/' . $hi;
    } else {
        return FALSE;
    }
}
// ここからCSV出力用の準備作業
// ファイル名に日本語をつけると受け渡しでエラーになるので一時英字に変更
if ($div == " ") $act_name = "ALL";
if ($div == "C") $act_name = "C-all";
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
$outputFile = $str_date . '-' . $end_date;

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();


$uid_sid   = $_SESSION['User_ID'];
$query_sid = "SELECT sid FROM user_detailes WHERE uid='$uid_sid'";
$res_sid   = array();
getResult($query_sid,$res_sid);
$sid_sid   = $res_sid[0][0];

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
.pt9bu {
    font-size:   9pt;
    font-weight: normal;
    font-family: monospace;
    font-color:  blue;
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
                        <a href='aden_details_csv.php?csvname=<?php echo $outputFile ?>&csvsearch=<?php echo $csv_search ?>&order=<?php echo $order ?>'>
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
                    if ($i==0) {
                ?>
                        <th class='winbox' nowrap>A伝<BR>発行日</th>
                <?php
                    } elseif ($i==1) {
                ?>
                        <th class='winbox' nowrap>A伝<BR>受注日</th>
                <?php
                    } elseif ($i==2) {
                ?>
                        <th class='winbox' nowrap>A伝No</th>
                <?php
                    } elseif ($i==3) {
                ?>
                        <th class='winbox' nowrap>ASSY No</th>
                <?php
                    } elseif ($i==5) {
                ?>
                        <th class='winbox' nowrap>計画No</th>
                <?php
                    } elseif ($i==6) {
                ?>
                        <th class='winbox' nowrap>SC工番</th>
                <?php
                    } elseif ($i==9) {
                ?>
                        <th class='winbox' nowrap>A伝<BR>回答日</th>
                <?php
                    } elseif ($i==10) {
                ?>
                        <th class='winbox' nowrap>A伝回答<BR>L/T</th>
                <?php
                    } elseif ($i==12) {
                ?>
                        <th class='winbox' nowrap>希望<BR>L/T</th>
                <?php
                    } elseif ($i==13) {
                ?>
                        <th class='winbox' nowrap>納回答<BR>L/T</th>
                <?php
                    } elseif ($i==14) {
                ?>
                        <th class='winbox' nowrap>L/T差</th>
                <?php
                    } elseif ($i==17) {
                ?>
                        <th class='winbox' nowrap>完成<BR>遅れ</th>
                <?php
                    } else {
                ?>
                        <th class='winbox' nowrap><?= $field[$i] ?></th>
                <?php
                    }
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
                    echo "    <td class='winbox' nowrap align='right'><span class='pt10b'>", ($r + $offset + 1), "</span></td>    <!-- 行ナンバーの表示 -->\n";
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case  0:        // A伝発行日
                            echo "<td class='winbox' nowrap align='center'><span class='pt9'>", format_date10($res[$r][$i]), "</span></td>\n";
                            break;
                        case  1:        // A伝受注日
                            echo "<td class='winbox' nowrap align='center'><span class='pt9'>", format_date10($res[$r][$i]), "</span></td>\n";
                            break;
                        case  3:        // 部品番号
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='center'>　</td>\n";
                            } else {
                                if ($sid_sid != '95') {
                                    echo "<td class='winbox' nowrap align='center'><span class='pt11b'><a href='", $menu->out_action('総材料費照会'), "?assy=", urlencode("{$res[$r][$i]}"), "&plan_no=", urlencode("{$res[$r][16]}"), "#mark'>{$res[$r][$i]}</a></span></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap align='center'><span class='pt11b'>{$res[$r][$i]}</span></td>\n";
                                }
                            }
                            break;
                        case  4:        // 部品名
                            $res[$r][$i] = mb_convert_kana($res[$r][$i], 'k');
                            $res[$r][$i] = mb_substr($res[$r][$i], 0, 20);
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case  5:        // 計画No.
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='center'>　</td>\n";
                            } else {
                                if ($sid_sid != '95') {
                                    if (trim($res[$r][6]) == '') {
                                        echo "<td class='winbox' nowrap align='center'><span class='pt11b'><a href='", $menu->out_action('引当構成表の表示'), "?plan_no=", urlencode("{$res[$r][$i]}"), "&aden_flg=1#mark'>{$res[$r][$i]}</a></span></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap align='center'><span class='pt11b'><a href='", $menu->out_action('引当構成表の表示'), "?plan_no=", urlencode("{$res[$r][$i]}"), "&sc_no=", urlencode("{$res[$r][6]}"), "&aden_flg=1#mark'>{$res[$r][$i]}</a></span></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap align='center'><span class='pt11b'>{$res[$r][$i]}</span></td>\n";
                                }
                            }
                            break;
                        //case  6:         // SC工番
                        case  7:         // 数量
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 0) . "</span></td>\n";
                            break;
                        case  8:        // 希望納期
                        case  9:        // A伝回答日
                            if (trim($res[$r][$i]) == 0) {
                                echo "<td class='winbox' nowrap align='center'>　</td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'>", format_date10($res[$r][$i]), "</span></td>\n";
                            }
                            break;
                        case 10:        // A伝回答L/T
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='right'>　</td>\n";
                            } elseif (trim($res[$r][$i]) == 0) {
                                echo "<td class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 0) . "</span></td>\n";
                            }
                            break;
                        case 11:        // 回答納期
                            if (trim($res[$r][$i]) == 0) {
                                echo "<td class='winbox' nowrap align='center'>　</td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'>", format_date10($res[$r][$i]), "</span></td>\n";
                            }
                            break;
                        case 12:        // 希望L/T
                        case 13:        // 回答L/T
                        case 14:        // L/T差
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='right'>　</td>\n";
                            } elseif (trim($res[$r][$i]) == 0) {
                                echo "<td class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 0) . "</span></td>\n";
                            }
                            break;
                        case 15:        // 販売価格
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 2) . "</span></td>\n";
                            break;
                        case 16:        // 実完成日
                            if ($res[$r][20] == 'BK' || $res[$r][20] == 'UK' || $res[$r][20] == 'BUK') {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'><font color='blue'>", format_date10($res[$r][$i]), "</font></span></td>\n";
                            } elseif ($res[$r][20] == 'U') {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'>打切</span></td>\n";
                            } elseif ($res[$r][20] == 'K') {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'>", format_date10($res[$r][$i]), "</span></td>\n";
                            } elseif ($res[$r][20] == 'B') {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'><font color='red'>", format_date10($res[$r][$i]), "</font></span></td>\n";
                            } elseif (trim($res[$r][$i]) == 0) {
                                echo "<td class='winbox' nowrap align='center'>　</td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><span class='pt9'>", format_date10($res[$r][$i]), "</span></td>\n";
                            }
                            break;
                        case 17:        // 完成遅れ
                            if (trim($res[$r][$i]) == '') {
                                echo "<td class='winbox' nowrap align='right'>　</td>\n";
                            } elseif (trim($res[$r][$i]) == 0) {
                                echo "<td class='winbox' nowrap align='right'><span class='pt9'>0</span></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 0) . "</span></td>\n";
                            }
                            break;
                        case 18:        // 納期コメント
                            if ($res[$r][8] == $res[$r][11]) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>希望通り</span></td>\n";
                            } elseif ($res[$r][$i] == 0) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>未入力</span></td>\n";
                            } elseif ($res[$r][$i] == 1) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>部品遅れ</span></td>\n";
                            } elseif ($res[$r][$i] == 2) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>設計変更</span></td>\n";
                            } elseif ($res[$r][$i] == 3) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>L/T不足</span></td>\n";
                            } elseif ($res[$r][$i] == 4) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>伝送遅れ</span></td>\n";
                            } elseif ($res[$r][$i] == 5) {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>その他</span></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            }
                            break;
                        default:
                            if (trim($res[$r][$i]) == '') $res[$r][$i] = '　';
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
