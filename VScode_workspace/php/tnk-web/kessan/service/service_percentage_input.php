<?php
//////////////////////////////////////////////////////////////////////////////
// サービス割合 部門別 入力 独自Templateを使用                              //
// Copyright(C) 2003-2012      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/10/17 Created   service_percentage_input.php                        //
// 2003/10/21 直接部門(グループ)をマスター化しロジック上から取込む          //
// 2003/10/22 HTML 関係をテンプレート風にし条件分岐で include_once する     //
//     JavaScript等から直接呼ばれた場合HTTP_REFERERにはmenu_frameが入る     //
// 2003/10/27 service_percent_historyに intextフィールドを追加し保存        //
// 2003/11/05 月次確定済みチェックのロジックを追加                          //
// 2003/11/12 内作・外作間接費の表示色変更 order_noによる表示順に変更       //
//            div(事業部)section(部門別)order_no(表示順)note(備考)を追加    //
// 2004/04/19 (部門毎の)一括入力方式だったのを個人毎にも入力出来るように変更//
//                          前期の実績表示をグレーに変更                    //
// 2007/01/24 MenuHeaderクラス対応                                          //
// 2012/10/06 決算時に前月コピーが効かないのを修正                     大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(10,  5);                    // site_index=10(損益メニュー) site_id=5(サービス割合メニュー)

$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存

//////// 自分自身の呼出しの時はセッションに保存しない
if ( preg_match('/service_percentage_input.php/', $_SERVER['HTTP_REFERER']) ) {
    $url_referer = $_SESSION['service_input_referer'];
// } elseif ( preg_match('/menu_frame.php/', $_SERVER['HTTP_REFERER']) ) { // これは将来の保証が無いのでコメント
} elseif (isset($_GET['view'])) {
    $_SESSION['service_input_referer'] = $_SESSION['service_view_referer'];   // viewから呼ばれたので
    $url_referer = $_SESSION['service_view_referer'];                   // viewから呼ばれたので
} else {
    $_SESSION['service_input_referer'] = $_SERVER['HTTP_REFERER'];  // 呼出もとのURLをセッションに保存
    $url_referer = $_SESSION['service_input_referer'];
}
////////////// リターンアドレス設定(絶対指定する場合)
$menu->set_RetUrl($url_referer);        // 上記の結果をセット
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

///////////// 対象部門の取得
if (isset($_POST['section_id'])) {
    $_SESSION['service_id']   = $_POST['section_id'];
    $_SESSION['section_name'] = $_POST['section_name'];
    $section_id   = $_POST['section_id'];
    $section_name = $_POST['section_name'];
} else {
    $section_id   = $_SESSION['service_id'];
    $section_name = $_SESSION['section_name'];
}

//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['service_ym'])) {
    $service_ym = $_SESSION['service_ym']; 
} else {
    $service_ym = date('Ym');        // セッションデータがない場合の初期値(前月)
    if (substr($service_ym,4,2) != 01) {
        $service_ym--;
    } else {
        $service_ym = $service_ym - 100;
        $service_ym = $service_ym + 11;   // 前年の12月にセット
    }
}

//////////// 前月データの取得用前月算出
if (substr($service_ym,4,2) != 01) {
    $before_ym = $service_ym - 1;
} else {
    $before_ym = $service_ym - 100;
    $before_ym = $before_ym + 11;   // 前年の12月にセット
}
if (substr($service_ym,6,2) == '32') {
    $before_ym = substr($service_ym,0,6);
}
////////////// 月次確定済みのチェック
if ( file_exists("final/$service_ym") ) {
    $_SESSION["s_sysmsg"] = "{$service_ym}：は既に確定処理されています。";
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if (substr($service_ym,6,2) == '32') {
    $view_ym = substr($service_ym,0,6) . '決算';
} else {
    $view_ym = $service_ym;
}
if (isset($_POST['check'])) {   // 登録の確認
    $menu_title = "$view_ym サービス割合 $section_name 部門 登録確認";
} else {                        // 初期入力フォーム
    $menu_title = "$view_ym サービス割合 $section_name 部門 入力";
}
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title($menu_title);

///// 前半期末 年月の算出
$yyyy = substr($service_ym, 0,4);
$mm   = substr($service_ym, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
    $zenki_ym = $yyyy . '09';     // 期初年月
} elseif (($mm >= 10) && ($mm <= 12)) {
    $zenki_ym = $yyyy . '09';     // 期初年月
} else {
    $zenki_ym = $yyyy . '03';     // 期初年月
}

////////// データベースへの接続
if ( !($con = db_connect()) ) {
    $_SESSION['s_sysmsg'] = 'データベースに接続できません！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

//////////// 間接部門の明細(個人)を抜出し
$query = sprintf("select act.act_id as コード, s_name as 経理部門, cd.uid as 社員番号, d.name as 名前
        from cate_allocation left outer join act_table as act
            on dest_id=act.act_id
        left outer join cd_table as cd
            on act.act_id=cd.act_id
        left outer join user_detailes as d
            on cd.uid=d.uid
        where orign_id=0 and
            group_id=%d and
            act_flg='f'
        order by act.act_id",
        $section_id);
$res = array();
if (($rows = getResWithFieldTrs($con, $query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '間接部門明細が取得できません！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
} else {
    $num = count($field);       // フィールド数取得
    $query = "select item, item_no, intext, div, section, order_no, note from service_item_master
              order by intext, order_no";
    $_SESSION['s_sysmsg'] = '';     // 初期化
    if ( ($rows_item=getResultTrs($con, $query, $res_item)) <= 0) {
        $_SESSION['s_sysmsg'] = '直接部門のマスターが取得できません！';
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    } else {
        for ($i=$num; $i<($rows_item+$num); $i++) {
            $field[$i]        = $res_item[$i-$num][0];      // 表示用フィールド
            $intext2[$i]      = $res_item[$i-$num][2];      // 2003/11/12 色分け表示のために追加
            
            $item[$i-$num]     = $res_item[$i-$num][0];      // 以下は登録用
            $item_no[$i-$num]  = $res_item[$i-$num][1];
            $intext[$i-$num]   = $res_item[$i-$num][2];
            $div[$i-$num]      = $res_item[$i-$num][3];
            $section[$i-$num]  = $res_item[$i-$num][4];
            $order_no[$i-$num] = $res_item[$i-$num][5];
            $note[$i-$num]     = $res_item[$i-$num][6];
        }
        $field[$i]   = '合　計';
        $num_p = count($field);     // フィールド数取得 num_p = num+の略
    }
}

///////////// 前期実績を取得
for ($r=0; $r<$rows; $r++) {
    $zenki[$r]['合計'] = 0;
    for ($f=0; $f<$rows_item; $f++) {
        $query = sprintf("select percent from service_percent_history
                where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $zenki_ym . '32', $res[$r][0], $res[$r][2], $item_no[$f]);
        if (getUniResult($query, $res_user) > 0) {
            $zenki[$r][$f]      = ($res_user * 100);    // ％に変換
            $zenki[$r]['合計'] += $zenki[$r][$f];
        } else {
            $zenki[$r][$f] = 0;
        }
    }
}
///////////// 前月実績を取得
for ($r=0; $r<$rows; $r++) {
    $zengetsu[$r]['合計'] = 0;
    for ($f=0; $f<$rows_item; $f++) {
        $query = sprintf("select percent from service_percent_history
                where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $before_ym, $res[$r][0], $res[$r][2], $item_no[$f]);
        if (getUniResult($query, $res_user) > 0) {
            $zengetsu[$r][$f]      = ($res_user * 100);    // ％に変換
            $zengetsu[$r]['合計'] += $zengetsu[$r][$f];
        } else {
            $zengetsu[$r][$f] = 0;
        }
    }
}

////////////// 確認用の実行ボタンが押された時
if (isset($_POST['check'])) {
    unset($_SESSION['percent']);
    $i = 0;     // 配列インデックス
    for ($r=0; $r<$rows; $r++) {
        $percent[$r]['合計'] = 0;
        $_SESSION['percent'][$r]['合計'] = 0;
        for ($f=0; $f<$rows_item; $f++) {
            if (isset($_POST['percent'][$i])) {
                if ($_POST['percent'][$i] == "") {
                    $percent[$r][$f] = '';      // 0を表示させないようにするため
                } else {
                    $percent[$r][$f] = $_POST['percent'][$i];
                }
                $_SESSION['percent'][$r][$f]      = $_POST['percent'][$i];
                $_SESSION['percent'][$r]['合計'] += $_POST['percent'][$i];
                $percent[$r]['合計'] += $_POST['percent'][$i];
            } else {
                $percent[$r][$f] = 'Not';
            }
            $i++;
        }
        if ($percent[$r]['合計'] == 100) {
            $_SESSION['percent'][$r]['登録'] = 'yes';   // 個別登録対応のため追加
        } elseif ($percent[$r]['合計'] == 0) {
            $_SESSION['percent'][$r]['登録'] = 'no';    // 0=入力していないと見なし除外する
        } else {
            $_SESSION['s_sysmsg'] .= "{$res[$r][3]}さんの合計が100でなく{$percent[$r]['合計']}です！<br>"; // 合計のエラー
            unset($_POST['check']);
            $_POST['repair'] = '修正';
        }
    }
//////////////////// 修正ボタンが押された時
} elseif (isset($_POST['repair'])) {
    if (isset($_SESSION['percent'])) {
        for ($r=0; $r<$rows; $r++) {
            for ($f=0; $f<$rows_item; $f++) {
                $percent[$r][$f] = $_SESSION['percent'][$r][$f];
            }
            $percent[$r]['合計'] = $_SESSION['percent'][$r]['合計'];
        }
    } else {
        for ($r=0; $r<$rows; $r++) {
            for ($f=0; $f<$rows_item; $f++) {
                $percent[$r][$f] = '';
            }
            $percent[$r]['合計'] = '　';
        }
    }
//////////////////// 前月データのコピーボタンが押された時
} elseif (isset($_POST['before'])) {
    for ($r=0; $r<$rows; $r++) {
        $percent[$r]['合計'] = 0;
        for ($f=0; $f<$rows_item; $f++) {
            $query = sprintf("select percent from service_percent_history
                    where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $before_ym, $res[$r][0], $res[$r][2], $item_no[$f]);
            if (getUniResult($query, $res_user) > 0) {
                $percent[$r][$f]      = ($res_user * 100);    // ％に変換
                $percent[$r]['合計'] += $percent[$r][$f];
            } else {
                $percent[$r][$f] = 0;
            }
        }
    }
    //for ($r=0; $r<$rows; $r++) {
    //    $percent[$r]['合計'] = 0;   // 初期化
    //    for ($f=0; $f<$rows_item; $f++) {
    //        ///// 登録済みのチェック
    //        $query = sprintf("select percent from service_percent_history
    //                where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $service_ym, $res[$r][0], $res[$r][2], $item_no[$f]);
    //        if (getUniResTrs($con, $query, $res_pert) > 0) {
    //            $percent[$r][$f]      = ($res_pert * 100);      // ％に変換
    //            $percent[$r]['合計'] += $percent[$r][$f];
    //        } else {
    //            $percent[$r][$f] = '';
    //        }
    //    }
    //}
//////////////////// 登録ボタンが押された時
} elseif (isset($_POST['save'])) {
    query_affected_trans($con, 'begin');    // トランザクションの開始
    for ($r=0; $r<$rows; $r++) {
        if ($_SESSION['percent'][$r]['登録'] == 'yes') {    // 個別登録対応のため追加
            for ($f=0; $f<$rows_item; $f++) {
                $percent[$r][$f] = ($_SESSION['percent'][$r][$f] / 100);    // ％のため変換
                ///// 登録済みのチェック
                $query = sprintf("select percent from service_percent_history
                        where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $service_ym, $res[$r][0] , $res[$r][2], $item_no[$f]);
                if (getUniResTrs($con, $query, $res_pert) <= 0) {
                    ///// 未登録 insert
                    $query = sprintf("INSERT INTO service_percent_history (service_ym, act_id, uid, item_no, intext, item, percent, div, section, order_no, note)
                             values (%d, %d, '%s', %d, %d, '%s', %1.2f, '%s', '%s', %d, '%s')",
                             $service_ym, $res[$r][0], $res[$r][2], $item_no[$f], $intext[$f], $item[$f], $percent[$r][$f],
                             $div[$f], $section[$f], $order_no[$f], $note[$f]);
                    if (query_affected_trans($con, $query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "{$res[$r][3]}さんの登録に失敗！";
                        query_affected_trans($con, 'rollback');         // Rollback
                        header("Location: $url_referer");               // 直前の呼出元へ戻る
                        exit();
                    }
                } else {
                    ///// 登録済 update
                    $query = "UPDATE service_percent_history SET percent={$percent[$r][$f]}, item='{$item[$f]}', intext={$intext[$f]},
                              div='{$div[$f]}', section='{$section[$f]}', order_no={$order_no[$f]}, note='{$note[$f]}'
                              where service_ym=$service_ym and act_id={$res[$r][0]} and uid='{$res[$r][2]}' and item_no={$item_no[$f]}";
                    if (query_affected_trans($con, $query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "{$res[$r][3]}さんの変更に失敗！";
                        query_affected_trans($con, 'rollback');         // Rollback
                        header("Location: $url_referer");               // 直前の呼出元へ戻る
                        exit();
                    }
                }
            }
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$res[$r][3]}さんを登録しました。<br></font>";
        }
    }
    query_affected_trans($con, 'commit');    // トランザクションの終了
    // $_SESSION['s_sysmsg'] .= "<font color='white'>全て完了！</font>";
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
/////////////////////// 初期入力フォーム
} else {
    for ($r=0; $r<$rows; $r++) {
        $percent[$r]['合計'] = 0;   // 初期化
        for ($f=0; $f<$rows_item; $f++) {
            ///// 登録済みのチェック
            $query = sprintf("select percent from service_percent_history
                    where service_ym=%d and act_id=%d and uid='%s' and item_no=%d", $service_ym, $res[$r][0], $res[$r][2], $item_no[$f]);
            if (getUniResTrs($con, $query, $res_pert) > 0) {
                $percent[$r][$f]      = ($res_pert * 100);      // ％に変換
                $percent[$r]['合計'] += $percent[$r][$f];
            } else {
                $percent[$r][$f] = '';
            }
        }
    }
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();

if (isset($_POST['check'])) {           // 確認用の実行ボタンが押された時
    include_once ('templates/service_percentage_check.templ.html');
} elseif (isset($_POST['repair'])) {    // 修正ボタンが押された時
    include_once ('templates/service_percentage_input.templ.html');
} else {                                // 初期入力フォーム
    include_once ('templates/service_percentage_input.templ.html');
}
?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
