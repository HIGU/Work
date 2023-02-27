<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費の登録                                                           //
// Copyright (C) 2003-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/12/15 Created   metarialCost_entry.php                              //
// 2003/12/17 合計レコード数 取得後にレコード追加時の頁制御ロジック追加     //
//            対日東工器の契約賃率を define で定義 契約変更時に対応         //
//            組立費が入力されていないと完了を押してもエラーにする整合性    //
// 2003/12/18 新規登録の時に前回(最後)の登録データをコピーする機能を追加    //
//            １子部品が２以上の親を持つ工程があるのでファイル設計の変更    //
//                  key(plan_no,assy_no,parts_no,pro_no) →                 //
//                                      (plan_no,parts_no,pro_no,par_parts) //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/05/13 コピーが押された時のコピー元のソート順変更 regdate ASC        //
//            変更時と削除時に頁を維持する。マスター未登録の時にalert表示   //
// 2004/11/08 完了ボタンでヘッダーに登録されないと思われる不具合対策 $uniq  //
// 2005/02/08 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/02/25 メッセージをポップアップとサイトメッセージに内容によって分ける//
// 2005/03/02 set_focus()でカーソルが表示しない時があるのでselect();を追加  //
// 2005/05/23 コピーボタンの２回押しによるduplicate keyチェックのため       //
//            if ($rows == 0) → if ($rows == 0 && $offset == 0) linkの表示 //
// 2005/05/27 Query failed: ERROR:  duplicate key 対応のため debug文を埋込  //
// 2005/06/01 Query failed: ERROR:  duplicate key 対応のため debug文を埋込  //
// 上記はcopy時 order by plan_no DESC → order by assy_no DESC, regdate DESC//
//copy時の最新データを計画番号順から登録順へ変更(登録が順番にされているため)//
// 2005/06/02 last_user のトリガーをやめて'{$_SESSION['User_ID']}'の登録へ  //
// 2005/06/07 未登録照会からの呼出対応のためset_retGET('page_keep','On')設定//
//            一括 削除 機能 を 追加                                        //
// 2005/06/14 SQLエラー時のログ出力方法をシェル方式からファイルハンドルへ   //
// 2005/06/29 material_cost_historyのコピー時に既登録済のチェックを追加     //
//                     郡司さんの所で２重クリックでエラーになることが合った //
// 2005/09/09 $menu->out_RetUrl() . $menu->out_retGET()←これを追加(完了時) //
// 2006/02/23 コピーのリンクが押された時  &&を追加 Undefined index対応      //
// 2006/02/27 PostgreSQL8.1.3で内部のソート順が変わったためregdate順を →   //
//            部品番号・工程番号順に変更及び部品追加時に最後のページ→保持へ//
// 2006/02/28 上記のソートを更に照会画面と同じソートへ変更                  //
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
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 21);                    // site_index=30(生産メニュー) site_id=21(総材料費の登録)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('総 材 料 費 の 登 録 (工程明細)');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('総材料費登録',   INDUST . 'material/materialCost_entry.php');
//////////// 戻先へのGETデータ設定
$menu->set_retGET('page_keep', 'On');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// 一頁の行数
define('PAGE', '10');

//////////// メッセージ出力フラグ
$msg_flg = 'site';

//////////// エラーログの出力先
$error_log_name = '/tmp/materialCost_entry_error.log';

//////////// 計画番号・製品番号をセッションから取得
if (isset($_REQUEST['plan_no'])) {
    $plan_no = $_REQUEST['plan_no'];
    $_SESSION['material_plan_no'] = $plan_no;
    $_SESSION['plan_no']          = $plan_no;
} elseif (isset($_SESSION['plan_no'])) {
    $plan_no = $_SESSION['plan_no'];
} else {
    $_SESSION['s_sysmsg'] .= '計画番号が指定されてない！';      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}
if (isset($_REQUEST['assy_no'])) {
    $assy_no = $_REQUEST['assy_no'];
    $_SESSION['assy_no'] = $assy_no;
} elseif (isset($_SESSION['assy_no'])) {
    $assy_no = $_SESSION['assy_no'];
} else {
    $_SESSION['s_sysmsg'] .= '製品番号が指定されてない！';      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}

//////////// レートを計画番号から取得(後日マスター式に変更予定)
if (substr($plan_no, 0, 1) == 'C') {
    define('RATE', 25.60);  // カプラ
} else {
    define('RATE', 37.00);  // リニア(それ以外は現在ない)
}

//////////// 製品名の取得
$query = "select midsc from miitem where mipn='{$assy_no}'";
if ( getUniResult($query, $assy_name) <= 0) {           // 製品名の取得
    $_SESSION['s_sysmsg'] .= "製品名の取得に失敗";      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}

//////////// 表題の設定
// $menu->set_caption('現在の契約賃率：' . number_format(RATE, 2));
$menu->set_caption("計画番号：{$plan_no}&nbsp;&nbsp;製品番号：{$assy_no}&nbsp;&nbsp;製品名：{$assy_name}");


//////////// 前回データのコピーボタンが押された場合
if (isset($_GET['pre_copy'])) {
    $query = "select plan_no from material_cost_header where assy_no='{$assy_no}'
                order by assy_no DESC, regdate DESC limit 1
    ";
    $chk_sql = "SELECT plan_no FROM material_cost_history
                WHERE
                    plan_no='{$plan_no}' and assy_no='{$assy_no}'
                LIMIT 1
    ";
    if (getUniResult($query, $pre_plan_no) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$assy_name} は経歴がありません！";    // .= に注意
    } elseif (getUniResult($chk_sql, $tmp_plan) > 0) {
        $_SESSION['s_sysmsg'] .= "{$assy_name} は既に工程が登録されています！";    // .= に注意
        $msg_flg = 'alert';
    } else {
        $query = "insert into material_cost_history (
                        plan_no, assy_no, parts_no, pro_no, pro_mark,
                        par_parts, pro_price, pro_num, intext, last_date, last_user)
                  select
                        '{$plan_no}', '{$assy_no}', parts_no, pro_no, pro_mark,
                        par_parts, pro_price, pro_num, intext, CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}'
                  from material_cost_history
                  where plan_no='{$pre_plan_no}' and assy_no='{$assy_no}'
                  ORDER BY par_parts ASC, parts_no ASC, pro_no ASC
        ";
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$assy_name} のCOPYに失敗！ 担当者に連絡して下さい。<br>COPY元の計画番号：{$pre_plan_no}";    // .= に注意
            $msg_flg = 'alert';
            ///////////////////////////////////// debug ADD 2005/06/01
            $fp_error = fopen($error_log_name, 'a');   // エラーログへの書込みでオープン
            $log_msg  = date('Y-m-d H:i:s');
            $log_msg .= " エラーの時の SQL 文は以下 \n";
            fwrite($fp_error, $log_msg);
            fwrite($fp_error, $query);
            fclose($fp_error);
            ///////////////////////////////////// debug END
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$assy_name} をCOPYしました<br>COPY元の計画番号：{$pre_plan_no}</font>";    // .= に注意
        }
    }
}

////////////// 登録・変更ロジックの前処理
if (isset($_POST['entry'])) {
    $query = "select midsc from miitem where mipn='{$_POST['parts_no']}'";
    if (getResult2($query, $res_chk) <= 0) {
        $_SESSION['s_sysmsg'] .= "部品番号：{$_POST['parts_no']} はマスター未登録です！";    // .= に注意
        $msg_flg = 'alert';
        unset($_POST['entry']);
        // $unreg_msg = 1;     // JavaScriptのalert へ継ぐためセットする 2005/02/08 alert()を削除
    }
}

////////////// 登録・変更ロジック (合計レコード数取得前に行う)
if (isset($_POST['entry'])) {
    $parts_no = $_POST['parts_no'];
    $pro_no   = $_POST['pro_no'];
    $pro_mark = $_POST['pro_mark'];
    $par_parts = $_POST['par_parts'];
    $pro_price = $_POST['pro_price'];
    // if ($pro_price == '') $pro_price = 0;
    $pro_num   = $_POST['pro_num'];
    $intext    = $_POST['intext'];
    $query = sprintf("select parts_no from material_cost_history where plan_no='%s' and parts_no='%s' and pro_no=%d and par_parts='%s'",
                        $plan_no, $parts_no, $pro_no, $par_parts);
    $res_chk = array();
    if ( getResult2($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE 変更
        $query = sprintf("update material_cost_history set plan_no='%s', assy_no='%s', parts_no='%s',
                            pro_no=%d, pro_mark='%s', par_parts='%s', pro_price=%01.2f, pro_num=%01.4f,
                            intext=%01d, last_date=CURRENT_TIMESTAMP, last_user='%s'",
                          $plan_no, $assy_no, $parts_no, $pro_no, $pro_mark, $par_parts, $pro_price, $pro_num,
                          $intext, $_SESSION['User_ID']);
        $query .= sprintf(" where plan_no='%s' and parts_no='%s' and pro_no=%d and par_parts='%s'",
                        $plan_no, $parts_no, $pro_no, $par_parts);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no}：工程{$pro_no}の変更に失敗！";    // .= に注意
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}：工程{$pro_no}を変更しました！</font>";    // .= に注意
        }
        unset($_POST['entry']);   // UPDATEの場合はページを維持するため entry を削除して
        $_GET['page_keep'] = '1';   // ページを維持するため page_keepを使用
    } else {                                    //////// 登録なし INSERT 新規
        $query = sprintf("insert into material_cost_history (plan_no, assy_no, parts_no, pro_no, pro_mark,
                            par_parts, pro_price, pro_num, intext, last_date, last_user)
                          values ('%s', '%s', '%s', %d, '%s', '%s', %01.2f, %01.4f, %01d, CURRENT_TIMESTAMP, '%s')",
                            $plan_no, $assy_no, $parts_no, $pro_no, $pro_mark, $par_parts, $pro_price,
                            $pro_num, $intext, $_SESSION['User_ID']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no}：工程{$pro_no}の追加に失敗！";    // .= に注意
            ///////////////////////////////////// debug ADD 2005/05/27
            $fp_error = fopen($error_log_name, 'a');   // エラーログへの書込みでオープン
            $log_msg  = date('Y-m-d H:i:s');
            $log_msg .= " エラーの時の SQL 文は以下 \n";
            fwrite($fp_error, $log_msg);
            fwrite($fp_error, $query);
            fclose($fp_error);
            ///////////////////////////////////// debug END
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}：工程{$pro_no}を追加しました！</font>";    // .= に注意
        }
        unset($_POST['entry']);   // INSERTの場合も(2006/02/27)ページを維持するため entry を削除して
        $_GET['page_keep'] = '1';   // ページを維持するため page_keepを使用
    }
}

//////////// 削除ボタンが押された時
if (isset($_REQUEST['del'])) {
    $parts_no = $_POST['parts_no'];
    $pro_no   = $_POST['pro_no'];
    $par_parts = $_POST['par_parts'];
    $query = "select parts_no, pro_no from material_cost_history ";
    $search_del = " where plan_no='{$plan_no}' and parts_no='{$parts_no}' and pro_no={$pro_no} and par_parts='{$par_parts}'";
    $query .= $search_del;
    $res_chk = array();
    if ( getResult2($query, $res_chk) <= 0 ) {
        $_SESSION['s_sysmsg'] .= "{$parts_no}：工程 {$pro_no}：は登録されていません！";    // .= に注意
        $msg_flg = 'alert';
    } else {
        $query = "delete from material_cost_history ";
        $query .= $search_del;
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no}：工程 {$pro_no}：の削除に失敗！";    // .= に注意
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}：工程 {$pro_no}：を削除しました！</font>";
        }
        $_GET['page_keep'] = '1';   // 削除の場合はページを維持するため page_keepを使用
    }
}


//////////// SQL 文の where 句を 共用する
$search = sprintf("where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);
// $search = '';

//////////// 合計レコード数・総材料費の取得     (対象データの最大数をページ制御に使用)
$query = sprintf("select count(*), sum(Uround(pro_price * pro_num, 2)) from material_cost_history %s", $search);
$res_sum = array();
if ( getResult2($query, $res_sum) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
    $msg_flg = 'alert';
}
$maxrows = $res_sum[0][0];
$sum_kin = $res_sum[0][1];

$query = sprintf("select sum(Uround(pro_num * pro_price, 2)) from material_cost_history
                    %s and intext='0'", $search);
if ( getUniResult($query, $ext_kin) <= 0) {  // 内作の総材料費
    $_SESSION['s_sysmsg'] .= "外作総材料費の取得に失敗";      // .= メッセージを追加する
    $msg_flg = 'alert';
}
$query = sprintf("select sum(Uround(pro_num * pro_price, 2)) from material_cost_history
                    %s and intext='1'", $search);
if ( getUniResult($query, $int_kin) <= 0) {  // 外作の総材料費
    $_SESSION['s_sysmsg'] .= "内作総材料費の取得に失敗";      // .= メッセージを追加する
    $msg_flg = 'alert';
}


//////////// 完了ボタンが押された時
if (isset($_REQUEST['final'])) {
    $query = "select assy_time from material_cost_header where plan_no='{$plan_no}'";
    if ( getResult2($query, $res_chk) > 0 ) {
        ///// 登録済 UPDATE
        $query = sprintf("update material_cost_header set
                        plan_no='{$plan_no}', assy_no='{$assy_no}',
                        sum_price=%01.2f, ext_price=%01.2f, int_price=%01.2f,
                        last_date=CURRENT_TIMESTAMP, last_user='{$_SESSION['User_ID']}'
                        where plan_no='{$plan_no}'",
                    $sum_kin, $ext_kin, $int_kin
        );
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "合計サマリーの計画番号：{$plan_no} の変更に失敗！";   // .= に注意
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>計画番号：{$plan_no} => 総材料費の登録を完了しました。</font>";
            header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . $menu->out_retGET());  // 直前の呼出元へ帰る
            exit();
        }
    } else {
        $_SESSION['s_sysmsg'] .= "計画番号：{$plan_no} は組立費が未登録です。先に登録して下さい！";    // .= に注意
        $msg_flg = 'alert';
    }
}

//////////// 一括削除ボタンが押された時
if (isset($_REQUEST['all_del'])) {
    while (1) {
        if ( !($con = funcConnect()) ) {
            $_SESSION['s_sysmsg'] .= "データベースに接続できません！ 担当者へ連絡して下さい。";   // .= に注意
            $msg_flg = 'alert';
            break;
        }
        query_affected_trans($con, 'begin');    // トランザクションスタート
        /******** ヘッダー headerの削除 *********/
        $query = "DELETE FROM material_cost_header where plan_no='{$plan_no}'";
        if (query_affected_trans($con, $query) < 0) {   // 0件削除はOKにするに注意
            query_affected_trans($con, 'rollback');     // ロールバック
            $_SESSION['s_sysmsg'] .= "ヘッダーファイルの削除でエラーが発生しました！ 担当者へ連絡して下さい。";   // .= に注意
            $msg_flg = 'alert';
            break;
        }
        /******** 明細 historyの削除 *********/
        $query = "DELETE FROM material_cost_history WHERE plan_no='{$plan_no}'";
        if ( ($del_rec = query_affected_trans($con, $query)) < 0) {   // 0件削除はOKにするに注意
            query_affected_trans($con, 'rollback');     // ロールバック
            $_SESSION['s_sysmsg'] .= "明細ファイルの削除でエラーが発生しました！ 担当者へ連絡して下さい。";   // .= に注意
            $msg_flg = 'alert';
            break;
        }
        query_affected_trans($con, 'commit');     // コミット
        $_SESSION['s_sysmsg'] .= "{$del_rec}点の部品を一括削除しました。";   // .= に注意
        $msg_flg = 'alert';
        break;
    }
}

//////////// ページオフセット設定
if ( isset($_POST['forward']) ) {                       // 次頁が押された
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
        $msg_flg = 'alert';
    }
} elseif ( isset($_POST['backward']) ) {                // 次頁が押された
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
        $msg_flg = 'alert';
    }
} elseif ( isset($_GET['page_keep']) || isset($_GET['number']) ) {   // 現在のページを維持する
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['offset'];

//////////// 追加時の頁制御(最後の頁へ移動させる) 合計レコード数 取得後の処理
if (isset($_POST['entry'])) {
    while (1) {
        $_SESSION['offset'] += PAGE;
        if ($_SESSION['offset'] >= $maxrows) {
            $_SESSION['offset'] -= PAGE;
            break;
        }
    }
}
$offset = $_SESSION['offset'];

//////////// 計画番号単位の工程明細の作表
$query = sprintf("
        SELECT
            parts_no    as 部品番号,                    -- 0
            midsc       as 部品名,                      -- 1
            pro_num     as 使用数,                      -- 2
            pro_no      as 工程,                        -- 3
            pro_mark    as 工程名,                      -- 4
            pro_price   as 工程単価,                    -- 5
            Uround(pro_num * pro_price, 2)
                        as 工程金額,                    -- 6
            CASE
                WHEN intext = '0' THEN '外作'
                WHEN intext = '1' THEN '内作'
                ELSE intext
            END         as 内外作,                      -- 7
            par_parts   as 親番号                       -- 8
        FROM
            material_cost_history
        LEFT OUTER JOIN
             miitem ON parts_no=mipn
        %s 
        ORDER BY par_parts ASC, parts_no ASC, pro_no ASC OFFSET %d LIMIT %d
        
    ", $search, $offset, PAGE);       // 共用 $search で検索
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
}

////////////// コピーのリンクが押された時  &&を追加 Undefined index対応
if (isset($_GET['number']) && isset($res[$_GET['number']][0]) ) {
    $r = $_GET['number'];
    $parts_no  = $res[$r][0];
    $pro_num   = $res[$r][2];
    $pro_no    = $res[$r][3];
    $pro_mark  = $res[$r][4];
    $pro_price = $res[$r][5];
    $par_parts = $res[$r][8];
    if ($res[$r][7] == '外作') $intext = '0'; else $intext = '1';
} else {
    $parts_no  = '';
    $pro_num   = '';
    $pro_no    = '';
    $pro_mark  = '';
    $pro_price = '';
    $par_parts = '';
    $intext    = '0';
}

////////////// 組立費の登録・変更ロジック (ロジック位置の指定はない)
if (isset($_POST['assy_reg'])) {
    $m_time = $_POST['m_time'];
    $m_rate = $_POST['m_rate'];
    $a_time = $_POST['a_time'];
    $a_rate = $_POST['a_rate'];
    $g_time = $_POST['g_time'];
    $g_rate = $_POST['g_rate'];
    $assy_time = ($m_time + $a_time + $g_time);
    ////////// 登録済みのチェック
    $query = "select plan_no from material_cost_header where plan_no='{$plan_no}'";
    if ( getResult2($query, $res_chk) > 0 ) {      ///// 登録済 UPDATE
        $query = sprintf("update material_cost_header set
                            m_time=%01.3f, m_rate=%01.2f,
                            a_time=%01.3f, a_rate=%01.2f,
                            g_time=%01.3f, g_rate=%01.2f,
                            assy_time=%01.3f, assy_rate=%01.2f,
                            last_date=CURRENT_TIMESTAMP, last_user='{$_SESSION['User_ID']}'
                            where plan_no='{$plan_no}'",
                    $m_time, $m_rate, $a_time, $a_rate, $g_time, $g_rate, $assy_time, RATE);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "組立費→計画番号：{$plan_no} の変更に失敗！";    // .= に注意
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>組立費→計画番号：{$plan_no} を変更しました</font>";
        }
    } else {                                        ///// 未登録 INSERT
        $query = sprintf("insert into material_cost_header
                            (plan_no, m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate, last_date, last_user)
                            values ('{$plan_no}', %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, %01.3f, %01.2f, CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}')",
                    $m_time, $m_rate, $a_time, $a_rate, $g_time, $g_rate, $assy_time, RATE);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "計画番号：{$plan_no} で組立費の追加に失敗！";    // .= に注意
            $msg_flg = 'alert';
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>計画番号：{$plan_no} の組立費を追加しました</font>";
        }
    }
}

/////////// 組立費の取得
$query = "select m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate
            from material_cost_header where plan_no='{$plan_no}'";
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
    $assy_rate  = $res_time[0][7];
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
<title><?= $menu->out_title() ?></title>
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?= $uniq ?>'></script>
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
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    obj.par_parts.value = obj.par_parts.value.toUpperCase();
    if (obj.parts_no.value.length != 0) {
        if (obj.parts_no.value.length != 9) {
            alert('部品番号の桁数は９桁です。');
            obj.parts_no.focus();
            obj.parts_no.select();
            return false;
        }
    } else {
        alert('部品番号が入力されていません！');
        obj.parts_no.focus();
        obj.parts_no.select();
        return false;
    }
    
    if ( !(isDigitDot(obj.pro_num.value)) ) {
        alert('使用数は数字以外入力出来ません！');
        obj.pro_num.focus();
        obj.pro_num.select();
        return false;
    } else {
        if (obj.pro_num.value <= 0) {
            alert('使用数は０より大きい数字を入力して下さい！');
            obj.pro_num.focus();
            obj.pro_num.select();
            return false;
        }
        if (obj.pro_num.value > 999.9999) {
            alert('使用数は 0.0001～999.9999 までを入力して下さい！');
            obj.pro_num.focus();
            obj.pro_num.select();
            return false;
        }
    }
    
    if ( !(isDigit(obj.pro_no.value)) ) {
        alert('工程番号は数字以外入力出来ません！');
        obj.pro_no.focus();
        obj.pro_no.select();
        return false;
    } else {
        if (obj.pro_no.value <= 0) {
            alert('工程番号は１から始まります。');
            obj.pro_no.focus();
            obj.pro_no.select();
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
            return false;
        }
        *****/
    } else {
        alert('工程記号が入力されていません！');
        obj.pro_mark.focus();
        obj.pro_mark.select();
        return false;
    }
    
    if (!( (obj.intext.value == '0') || (obj.intext.value == '1') )) {
        alert('外作=0 内作=1 のどちらかを入力して下さい。');
        obj.intext.focus();
        obj.intext.select();
        return false;
    }
    
    return true;
}

function chk_assy_entry(obj) {
    /* 全体チェックのフラグ */
    var flg = false;
        /* 全ての項目の数字入力チェック */
    if ( !(isDigitDot(obj.m_time.value)) ) {
        alert('手作業 工数は数字以外入力出来ません！');
        obj.m_time.focus();
        obj.m_time.select();
        return false;
    }
    if ( !(isDigitDot(obj.m_rate.value)) ) {
        alert('手作業 賃率は数字以外入力出来ません！');
        obj.m_rate.focus();
        obj.m_rate.select();
        return false;
    }
    if ( !(isDigitDot(obj.a_time.value)) ) {
        alert('自動機 工数は数字以外入力出来ません！');
        obj.a_time.focus();
        obj.a_time.select();
        return false;
    }
    if ( !(isDigitDot(obj.a_rate.value)) ) {
        alert('自動機 賃率は数字以外入力出来ません！');
        obj.a_rate.focus();
        obj.a_rate.select();
        return false;
    }
    if ( !(isDigitDot(obj.g_time.value)) ) {
        alert('外注 工数は数字以外入力出来ません！');
        obj.g_time.focus();
        obj.g_time.select();
        return false;
    }
    if ( !(isDigitDot(obj.g_rate.value)) ) {
        alert('外注 賃率は数字以外入力出来ません！');
        obj.g_rate.focus();
        obj.g_rate.select();
        return false;
    }
        /* 手作業のペアー入力チェック */
    if (obj.m_time.value > 0) {
        if (obj.m_rate.value > 0) {
            if (obj.m_time.value > 999.999) {
                alert('手作業 工数は 0.001～999.999 までを入力して下さい！');
                obj.m_time.focus();
                obj.m_time.select();
                return false;
            }
            if (obj.m_rate.value > 999.99) {
                alert('手作業 賃率は 0.01～999.99 までを入力して下さい！');
                obj.m_rate.focus();
                obj.m_rate.select();
                return false;
            }
            flg = true;
        } else {
            alert("手作業 工数が入力されているのに\n手作業 賃率が入力されていません！");
            obj.m_rate.focus();
            obj.m_rate.select();
            return false;
        }
    } else {
        if (obj.m_rate.value > 0) {
            alert("手作業 賃率が入力されているのに\n手作業 工数が入力されていません！");
            obj.m_time.focus();
            obj.m_time.select();
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
                return false;
            }
            if (obj.a_rate.value > 999.99) {
                alert('自動機 賃率は 0.01～999.99 までを入力して下さい！');
                obj.a_rate.focus();
                obj.a_rate.select();
                return false;
            }
            flg = true;
        } else {
            alert("自動機 工数が入力されているのに\n手作業 賃率が入力されていません！");
            obj.a_rate.focus();
            obj.a_rate.select();
            return false;
        }
    } else {
        if (obj.a_rate.value > 0) {
            alert("自動機 賃率が入力されているのに\n手作業 工数が入力されていません！");
            obj.a_time.focus();
            obj.a_time.select();
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
                return false;
            }
            if (obj.g_rate.value > 999.99) {
                alert('外注 賃率は 0.01～999.99 までを入力して下さい！');
                obj.g_rate.focus();
                obj.g_rate.select();
                return false;
            }
            flg = true;
        } else {
            alert("外注 工数が入力されているのに\n手作業 賃率が入力されていません！");
            obj.g_rate.focus();
            obj.g_rate.select();
            return false;
        }
    } else {
        if (obj.g_rate.value > 0) {
            alert("外注 賃率が入力されているのに\n手作業 工数が入力されていません！");
            obj.g_time.focus();
            obj.g_time.select();
            return false;
        }
    }
        /* 全体のフラグで入力チェック */
    if (!flg) {
        alert('手作業・自動機・外注のどれか１セット以上、入力して下さい！');
        obj.m_time.focus();
        obj.m_time.select();
        return false;
    } else {
        return true;
    }
}

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    document.entry_form.parts_no.focus();      // 初期入力フォームがある場合はコメントを外す
    document.entry_form.parts_no.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
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
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?=$menu->out_title_border()?>
        
        <div class='pt10' style='color:gray;'>現在の契約賃率：<?= number_format(RATE, 2) ?></div>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <?= $menu->out_caption(), "\n" ?>
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
            <THEAD>
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
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                        <!--  bgcolor='#ffffc6' 薄い黄色 -->
                        <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                <?php
                for ($r=0; $r<$rows; $r++) {
                ?>
                    <tr>
                        <td class='winbox' nowrap align='right'>
                            <div class='pt10b'>
                            <a href='<?= $menu->out_self() .'?number='. ($r) ?>' target='application' style='text-decoration:none;'>
                                <?= ($r + $offset + 1) ?>
                            </a>
                            </div>
                        </td>    <!-- 行ナンバーの表示 -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case 1:
                            echo "<td class='winbox' nowrap width='300 align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case  2:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 4), "</div></td>\n";
                            break;
                        case  5:
                        case  6:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            break;
                        default:
                            if ($res[$r][$i] != '') {
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            } else {    // 親番号がない場合を想定 $i=8
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>&nbsp;</div></td>\n";
                            }
                        }
                    }
                    ?>
                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td class='winbox' nowrap colspan='<?= $num+1 ?>' align='right'>
                        <div class='pt10'>
                        内作材料費：<?= number_format($int_kin, 2) ."\n" ?>  
                        外作材料費：<?= number_format($ext_kin, 2) ."\n" ?>  
                        合計材料費：<?= number_format($sum_kin, 2) ."\n" ?>
                        <br>
                        合計工数：<?= number_format($assy_time, 3) ."\n" ?>
                        契約賃率：<?= number_format($assy_rate, 2) ."\n" ?>
                        　　組立費：<?= number_format($assy_price, 2) ."\n" ?>
                        　総材料費：<?= number_format($sum_kin + $assy_price, 2) ."\n" ?>
                        <br>
                        (参考：社内の実際賃率)
                        組立費：<?= number_format($assy_int_price, 2) ."\n" ?>
                        　総材料費：<?= number_format($sum_kin + $assy_int_price, 2) ."\n" ?>
                        </div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <br>
        
        <form name='entry_form' method='post' action='<?= $menu->out_self() ?>' onSubmit='return chk_cost_entry(this)'>
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
                            <input type='text' class='parts_font' name='parts_no' value='<?= $parts_no ?>' size='9' maxlength='9'>
                        </td>
                    </a>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='pro_num' value='<?= $pro_num ?>' size='7' maxlength='8'></td>
                    <td class='winbox' align='center'><input type='text' class='pro_num_font' name='pro_no' value='<?= $pro_no ?>' size='1' maxlength='1'></td>
                    <td class='winbox' align='center'><input type='text' class='pro_num_font' name='pro_mark' value='<?= $pro_mark ?>' size='2' maxlength='2'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='pro_price' value='<?= $pro_price ?>' size='11' maxlength='11'></td>
                    <td class='winbox' align='center'><input type='text' class='pro_num_font' name='intext' value='<?= $intext ?>' size='1' maxlength='1'></td>
                    <td class='winbox' align='center'><input type='text' class='parts_font' name='par_parts' value='<?= $par_parts ?>' size='9' maxlength='9'></td>
                </tr>
                <tr>
                    <td class='winbox' colspan='7' align='center'>
                        <input type='submit' class='entry_font' name='entry' value='追加変更'>
                        <input type='submit' class='entry_font' name='del' value='削除'>
                        <?php
                        if ($rows == 0 && $offset == 0) {
                            echo "<a href='". $menu->out_self() ."?pre_copy=1' target='application' style='text-decoration:none;'>
                                    前回のデータをコピー
                                  </a>";
                        }
                        ?>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        
        <br>
        
        <form name='assy_form' method='post' action='<?= $menu->out_self() ?>' onSubmit='return chk_assy_entry(this)'>
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
                    <td class='winbox' align='center'><input type='text' class='price_font' name='m_time' value='<?= $m_time ?>' size='6' maxlength='7'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='m_rate' value='<?= $m_rate ?>' size='5' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='a_time' value='<?= $a_time ?>' size='6' maxlength='7'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='a_rate' value='<?= $a_rate ?>' size='5' maxlength='6'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='g_time' value='<?= $g_time ?>' size='6' maxlength='7'></td>
                    <td class='winbox' align='center'><input type='text' class='price_font' name='g_rate' value='<?= $g_rate ?>' size='5' maxlength='6'></td>
                </tr>
                <tr>
                    <td class='winbox' colspan='6' align='center'>
                        <input type='submit' class='entry_font' name='assy_reg' value='追加変更'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        
        <br>
        
        <form name='final_form' method='post' action='<?= $menu->out_self(), "?id={$uniq}" ?>'>
            <?php if ($final_flg == 1) { ?>
            <input type='submit' class='entry_font' name='final' value='完了'>
            <input type='submit' class='entry_font' name='all_del' value='一括削除'
                onClick="return confirm('一括削除を実行します。\n\nこの処理は元には戻せません。\n\n実行しても宜しいでしょうか？')"
            >
            <?php } ?>
        </form>
    </center>
</body>
<?php if ($msg_flg == 'alert') echo $menu->out_alert_java(); ?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
