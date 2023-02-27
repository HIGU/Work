<?php
//////////////////////////////////////////////////////////////////////////////
// 引当部品構成表の照会  計画番号の表示 view                                //
//                              Allocated Configuration Parts 引当構成部品  //
// Copyright (C) 2004-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/05/28 Created  allo_conf_parts_view.php                             //
// 2004/06/07 リターンアドレスの設定を呼出元で先セッションに保存しておく    //
// 2004/12/08 CC部品とTNKCCを表示追加                                       //
// 2004/12/28 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
//    ディレクトリをindustry→industry/materialへ変更unregistからの呼出対応 //
// 2005/01/07 $menu->set_retGET('page_keep', $_REQUEST['material']);で統一  //
// 2005/01/12 部品名をtrim(substr(midsc,1,25))→trim(substr(midsc,1,21))変更//
// 2005/01/31 部品番号から行番号へマーク変更 &row={$r} の追加で対応         //
// 2005/02/07 $search = sprintf("where plan_no='%s'", $plan_no); を↓に変更 //
//            where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);    //
// 2005/05/20 db_connect() → funcConnect() へ変更 pgsql.phpで統一のため    //
// 2006/04/13 <a name='mark'によりフォーカス移動対応で、setTimeout()を追加  //
// 2006/08/01 合計レコード数 取得時に引当が無ければ終了を追加               //
// 2006/12/01 ダブルクリックで不要な引当を削除する機能を追加delParts権限必要//
// 2006/12/18 上記の機能を使った場合もリターン情報を維持するため$param追加  //
// 2007/02/20 parts/からparts/parts_stock_history/parts_stock_view.phpへ変更//
// 2007/02/22 set_caption()に工事番号追加。部品番号10pt→11pt,支給条件→条件//
// 2007/03/22 parts_stock_view.php → parts_stock_history_Main.php へ変更   //
// 2007/03/24 ディレクトリmaterial/→parts/allocate_config/ フレーム版へ変更//
// 2007/09/03 古い$_SESSION['offset']が他と競合するため$session->add_local  //
//            ついでに$_POST/$_GET → $_REQUEST へ変更                      //
// 2016/08/08 mouseOverを追加                                          大谷 //
// 2017/06/28 A伝情報の照会に対応                                      大谷 //
// 2019/05/16 markがうまく反応していなかったので修正(trでは無くNoに)   大谷 //
// 2019/10/17 部品を自動的に引当、登録時と同じ並びで表示させる         和氣 //
// 2020/06/01 照会データを登録画面へコピーする                         和氣 //
// 2020/08/01 振替品は、IsAlternative() or IsNoSubstitute() に随時追加 和氣 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');     // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');     // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class
//////////// セッションのインスタンスを登録
$session = new Session();
// 以下はまだ使用していない
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
// access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(INDEX_INDUST, 26);          // site_index=30(生産メニュー) site_id=26(引当部品構成表の照会)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);          // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('引当 部品 構成表 の 照会');
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須
//////////// 自分をフレーム定義に変える
//$menu->set_self(INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
$menu->set_self(INDUST . 'parts/allocate_config_entry/allo_conf_parts_Main.php');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('在庫経歴',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');
$menu->set_action('総材料費登録',   INDUST . 'material/material_entry/materialCost_entry_main.php');
//////////// リターン時の情報復元
if (isset($_REQUEST['plan_cond'])) {    // 計画番号の入力状態をチェック(フォームからの呼出対応)
    $menu->set_retGET('plan', $_REQUEST['plan_cond']);
}
if (isset($_REQUEST['material'])) {     // 総材料費の未登録からの呼出対応
    $menu->set_retGET('page_keep', $_REQUEST['material']);
    $parts_no = @$_SESSION['stock_parts'];
    if (isset($_REQUEST['row'])) {
        $row_no = $_REQUEST['row'];   // 前回呼出した行番号
        $param  = "&material={$_REQUEST['material']}&row={$_REQUEST['row']}";
    } else {
        $row_no = -1;       // 未登録リストから呼ばれた時
        $param  = "&material={$_REQUEST['material']}";
        $inquiries_only = false; // 登録エリアも表示
        $_SESSION['inquiries_only'] = $inquiries_only; // セッションにセット
    }
} else {
    $parts_no = '';
    $row_no   = '-1';       // 単体で照会された時
    $param    = '';
    $inquiries_only = true; // 照会のみ表示
    $_SESSION['inquiries_only'] = $inquiries_only; // セッションにセット
}

// 強制登録フラグ
if (isset($_REQUEST['comp_regi'])) {
    define('COMPREGI', true);
} else {
    define('COMPREGI', false);
}

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// 一頁の行数
define('PAGE', '300');      // 現在は300を越える引当構成はない

//////////// 計画番号・製品番号をリクエストから取得(主に総材料費の登録で使用)
if (isset($_REQUEST['plan_no'])) {
    $plan_no = $_REQUEST['plan_no'];
    $_SESSION['material_plan_no'] = $plan_no;   // セッションに保存
    $_SESSION['plan_no'] = $plan_no;            // フォーム用のデータにも保存
    //////////// 計画番号・製品番号をセッションから取得(フォームからの照会で使用)
} elseif (isset($_SESSION['plan_no'])) {
    $plan_no = $_SESSION['plan_no'];
} else {
    $_SESSION['s_sysmsg'] .= '計画番号が指定されてない！';      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}
//////////// SC工番をリクエストから取得(主にA伝情報の照会で使用）)
if (isset($_REQUEST['sc_no'])) {
    $sc_no = $_REQUEST['sc_no'];
    $_SESSION['material_sc_no'] = $sc_no;   // セッションに保存
    $_SESSION['sc_no'] = $sc_no;            // フォーム用のデータにも保存
    //////////// 計画番号・製品番号をセッションから取得(フォームからの照会で使用)
} elseif (isset($_SESSION['sc_no'])) {
    $sc_no = $_SESSION['sc_no'];
} else {
    $sc_no = '';
    $_SESSION['material_sc_no'] = '';   // セッションに保存
    $_SESSION['sc_no'] = '';            // フォーム用のデータにも保存
}
///// 製品番号・工事番号の取得
$query = "SELECT parts_no, note15 from assembly_schedule where plan_no='{$plan_no}'";
if (getResult2($query, $assy_res) <= 0) {
    // .= メッセージを追加する
    $_SESSION['s_sysmsg'] .= "計画番号：{$plan_no} 計画データがないため Assy番号を取得出来ません！";
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
} else {
    $assy_no = $assy_res[0][0];
    $kouji_no = $assy_res[0][1];
    if (substr($assy_no, 0, 1) == 'C') {    // assy_noの頭１桁で事業部を判定
        define('RATE', 25.60);  // カプラ
    } else {
        define('RATE', 37.00);  // リニア(それ以外は現在ない)
    }
}

//////////// 製品名の取得
$query = "select midsc from miitem where mipn='{$assy_no}'";
if ( getUniResult($query, $assy_name) <= 0) {           // 製品名の取得
    $_SESSION['s_sysmsg'] .= "製品名の取得に失敗";      // .= メッセージを追加する
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}

//////////// 表題の設定
$menu->set_caption("計画番号：{$plan_no}　製品番号：{$assy_no}　製品名：{$assy_name}　<span style='color:red;'>工事：{$kouji_no}</span>");

//////////// SQL 文の where 句を 共用する
$search = sprintf("where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);
// $search = '';

//////////// 合計レコード数＝引当部品数の取得     (対象データの最大数をページ制御に使用)
$query = sprintf("select count(*) from allocated_parts %s", $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
} else {
    if ($maxrows <= 0) {
        $_SESSION['s_sysmsg'] .= "引当がありません！";      // .= メッセージを追加する
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }
}


//////////// ページオフセット設定
$offset = $session->get_local('offset');
if ($offset == '') $offset = 0;         // 初期化
if ( isset($_REQUEST['forward']) ) {                       // 次頁が押された
    $offset += PAGE;
    if ($offset >= $maxrows) {
        $offset -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // 次頁が押された
    $offset -= PAGE;
    if ($offset < 0) {
        $offset = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) || isset($_REQUEST['number']) ) {   // 現在のページを維持する
    $offset = $offset;
} else {
    $offset = 0;                            // 初回の場合は０で初期化
}
$session->add_local('offset', $offset);


//////////// 計画番号単位の工程明細の作表

$query_basic = "
        SELECT  parts_no    as 部品番号                 -- 0
                ,trim(substr(midsc,1,21))
                            as 部品名                   -- 1
                ,unit_qt    as 使用数                   -- 2
                ,allo_qt    as 引当数                   -- 3
                ,sum_qt     as 出庫累計                 -- 4
                ,allo_qt - sum_qt
                            as 出庫残                   -- 5
                ,CASE
                    WHEN cond = '2' THEN '有償'
                    WHEN cond = '3' THEN '無償'
                    ELSE cond
                END         as 条件                     -- 6 旧は支給条件
                ,price      as 有償単価                 -- 7
                ,Uround(allo_qt * price, 2)
                            as 有償金額                 -- 8
        FROM
            allocated_parts
        LEFT OUTER JOIN
            miitem ON parts_no=mipn
        ";

$query = sprintf("{$query_basic}
        %s 
        ORDER BY parts_no ASC OFFSET %d LIMIT %d
    ", $search, $offset, PAGE);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'><br>引当データがありません！</font>";
    $rows_view = $rows;
    // header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    // exit();
} else {
    $num = count($field);       // フィールド数取得
    /////////////// 表示用の配列データを生成 view_data (子部品を親部品の最後に並び替えする)
    $res_view   = array();
    $field_view = array();
    $rows_view  = 0;
    $num_view   = 0;
    $rec        = 0;
    $col        = 0;
    $query_basic = "SELECT parts_no
                        , trim(substr(midsc,1,21))
                        , unit_qt
                        , '-'
                        , '-'
                        , '-'
                        ,CASE
                            WHEN mtl_cond = '1' THEN '自給'
                            WHEN mtl_cond = '2' THEN '有償'
                            WHEN mtl_cond = '3' THEN '無償'
                            ELSE mtl_cond
                        END
                        , '-'
                        , '-'
                    FROM
                        parts_configuration
                    LEFT OUTER JOIN
                        miitem
                    ON parts_no=mipn
                    WHERE p_parts_no='%s' AND mtl_cond!='1' ORDER BY parts_no ASC";
    //////// Level1 Start
    for ($r=0; $r<$rows; $r++) {
        for ($c=0; $c<$num; $c++) {
            if ($c == 0) {
                $res_view[$rec][$col] = '.1';   // L1=レベル１
                $col++;
                $res_view[$rec][$col] = $res[$r][$c];
                $col++;
            } else {
                $res_view[$rec][$col] = $res[$r][$c];
                $col++;
            }
        }
        $col = 0;
        $rec++;
        ////////// Level2 子部品データチェック
        $query = sprintf($query_basic, $res[$r][0]);
        $res2 = array();
        if ( ($rows2=getResult2($query, $res2)) > 0) {         // 子部品 の取得
            ////////// Level2 Start 子部品データあり
            for ($r2=0; $r2<$rows2; $r2++) {
                for ($c2=0; $c2<$num; $c2++) {
                    if ($c2 == 0) {
                        $res_view[$rec][$col] = '..2';   // L2=レベル２
                        $col++;
                        $res_view[$rec][$col] = $res2[$r2][$c2];
                        $col++;
                    } else {
                        $res_view[$rec][$col] = $res2[$r2][$c2];
                        $col++;
                    }
                }
                $col = 0;
                $rec++;
                ////////// Level3 子部品データチェック
                $query = sprintf($query_basic, $res2[$r2][0]);
                $res3 = array();
                if ( ($rows3=getResult2($query, $res3)) > 0) {         // 子部品 の取得
                    ////////// Level3 Start 子部品データあり
                    for ($r3=0; $r3<$rows3; $r3++) {
                        for ($c3=0; $c3<$num; $c3++) {
                            if ($c3 == 0) {
                                $res_view[$rec][$col] = '...3';   // L3=レベル３
                                $col++;
                                $res_view[$rec][$col] = $res3[$r3][$c3];
                                $col++;
                            } else {
                                $res_view[$rec][$col] = $res3[$r3][$c3];
                                $col++;
                            }
                        }
                        $col = 0;
                        $rec++;
                        ////////// Level4 子部品データチェック
                        $query = sprintf($query_basic, $res3[$r3][0]);
                        $res4 = array();
                        if ( ($rows4=getResult2($query, $res4)) > 0) {         // 子部品 の取得
                            ////////// Level4 Start 子部品データあり
                            for ($r4=0; $r4<$rows4; $r4++) {
                                for ($c4=0; $c4<$num; $c4++) {
                                    if ($c4 == 0) {
                                        $res_view[$rec][$col] = '....4';   // L4=レベル４
                                        $col++;
                                        $res_view[$rec][$col] = $res4[$r4][$c4];
                                        $col++;
                                    } else {
                                        $res_view[$rec][$col] = $res4[$r4][$c4];
                                        $col++;
                                    }
                                }
                                $col = 0;
                                $rec++;
                                ////////// Level5 子部品データチェック
                                $query = sprintf($query_basic, $res4[$r4][0]);
                                $res5 = array();
                                if ( ($rows5=getResult2($query, $res5)) > 0) {         // 子部品 の取得
                                    ////////// Level5 Start 子部品データあり
                                    for ($r5=0; $r5<$rows5; $r5++) {
                                        for ($c5=0; $c5<$num; $c5++) {
                                            if ($c5 == 0) {
                                                $res_view[$rec][$col] = '.....5';   // L5=レベル５
                                                $col++;
                                                $res_view[$rec][$col] = $res5[$r5][$c5];
                                                $col++;
                                            } else {
                                                $res_view[$rec][$col] = $res5[$r5][$c5];
                                                $col++;
                                            }
                                        }
                                        $col = 0;
                                        $rec++;
                                    }
                                }
                                ////////// Level5 End
                            }
                        }
                        ////////// Level4 End
                    }
                }
                ////////// Level3 End
            }
        }
        /////////// Level2 End
    }
    ///////// Level1 End
    
    ////// レコード数の設定
    $rows_view = $rec;
    ////// フィールド名の追加
    for ($i=0; $i<$num; $i++) {
        if ($i == 0) {
            $field_view[0] = 'レベル';
            $field_view[$i+1] = $field[0];
        } else {
            $field_view[$i+1] = $field[$i];
        }
    }
    ////// フィールド数の設定
    $num_view = count($field_view);       // フィールド数取得
    
    /**************** TNKCC CC部品 表示追加 *********************/
    /////////// begin トランザクション開始
    if ($con = funcConnect()) {
        // query_affected_trans($con, 'begin');
    } else {
        $_SESSION['s_sysmsg'] = 'データベースと接続できません！';
    }
    ////// TNKCC部品の取得と階層レベル(レベル２以下)のCC部品の表示
    for ($r=0; $r<$rows_view; $r++) {
        $query_tnkcc = "SELECT
                            CASE
                                WHEN miccc='E' THEN 'TNKCC'
                                WHEN miccc='D' THEN 'CC部品'
                                ELSE '&nbsp;'
                            END
                        FROM miccc WHERE mipn='{$res_view[$r][1]}'
        ";
        if (getUniResTrs($con, $query_tnkcc, $res_tnkcc) > 0) {
            // データあり
            $res_view[$r][$num_view] = $res_tnkcc;
        } else {
            // データなし
            $res_view[$r][$num_view] = '&nbsp;';
        }
    }
    $field_view[$num_view] = 'CC部品';
    ////// フィールド数の設定
    $num_view = count($field_view);       // フィールド数取得
    ////// CC部品の取得
    $query_cc = "
        SELECT  '.1'        as レベル                   -- 0
                ,parts_no   as 部品番号                 -- 1
                ,trim(substr(midsc,1,21))
                            as 部品名                   -- 2
                ,unit_qt    as 使用数                   -- 3
                ,'-'        as 引当数                   -- 4
                ,'-'        as 出庫累計                 -- 5
                ,'-'        as 出庫残                   -- 6
                ,CASE
                    WHEN mtl_cond = '1' THEN '自給'     -- ありえないが？
                    WHEN mtl_cond = '2' THEN '有償'
                    WHEN mtl_cond = '3' THEN '無償'
                    ELSE mtl_cond
                END         as 条件                     -- 7 旧は支給条件
                ,'-'        as 有償単価                 -- 8
                ,'-'        as 有償金額                 -- 9
                ,'CC部品'   as CC部品                   -- 10
        FROM
            parts_configuration
        LEFT OUTER JOIN
            miccc
        ON parts_no=miccc.mipn
        LEFT OUTER JOIN
             miitem
        ON parts_no=miitem.mipn
        WHERE
            p_parts_no='{$assy_no}'
            and
            miccc.miccc='D'
        ORDER BY parts_no ASC
    ";
/*
    if ( ($rows_cc=getResultTrs($con, $query_cc, $res_cc)) > 0) {
        // CC部品あり
        $num_cc = count($res_cc[0]);
        for ($r=0; $r<$rows_cc; $r++) {
            for ($i=0; $i<$num_cc; $i++) {
                $res_view[$rows_view+$r][$i] = $res_cc[$r][$i];
            }
        }
        // レコード数のセット
        $rows_view = ($rows_view + $rows_cc);
    }
/**/

    // 登録画面と同じになるようCC部品を配置する
    if( ($rows_cc=getResultTrs($con, $query_cc, $res_cc)) > 0 ) {
        $num_cc = count($res_cc[0]);
        $sort_view = array();
        $r=$c=0;
        for ($s=0; $s<($rows_view + $rows_cc); $s++) {
            if( $c >= $rows_cc || $r < $rows_view && ($res_view[$r][0] != '.1' || strcmp($res_view[$r][1], $res_cc[$c][1]) < 0)) {
                for ($i=0; $i<$num_cc; $i++) {
                        $sort_view[$s][$i] = $res_view[$r][$i];
                }
                $r++;
            } else {
                /* 個別対応：単価間違い？、特別に１つ前の枝番号に変更 -----> */
                if( strcmp($res_cc[$c][1], "CP00873-2") == 0 ) {
//                    $_SESSION['s_sysmsg'] .= "※CP00873-2 単価間違いの可能性あり、CP00873-1 へ変更してます。";
                    $res_cc[$c][1] = "CP00873-1";
                }
                /* <-------------------------------------------------------- */
                for ($i=0; $i<$num_cc; $i++) {
                        $sort_view[$s][$i] = $res_cc[$c][$i];
                }
                $c++;
            }
        }
        // レコード数のセット
        $rows_view = ($rows_view + $rows_cc);
        $res_view = array();
        $res_view = $sort_view;
    }

    $num_view = $num_view + 2; // 引当数と、出庫数分を追加

    // 既存の表示用配列から登録と同様の表示配列に変換する。
    $res_view2 = array(); /* [0]レベル[1]部品番号[2]部品名[3]使用数[4]引当数[5]出庫数[6]工程
                             [7]工程名[8]工程単価[9]工程金額[10]内外作[11]親番号[12]CC部品 */

    $d_data = array(); //  [0]工程[1]工程名[2]工程単価[3]内外作[4]親番号
    $sei_no = $parts = array();
    $out_no = -1;
    $parts_idx = array();
    $level_one = -1;
    $reserveflg = -1;

    SetCompleteInfo( $plan_no, $assy_no );

    /* 特殊：片方だけ表示する（フラグ）------------------------------------> */
    $rabelflg = false;   // ラベル用フラグ
    $konpoflg = false;   // コンポウバコ用フラグ
    $poriflg = false;    // ポリブクロ 80X160 用フラグ

    /* 特殊：片方だけ表示する（以下は、表示する部品のチェック）------------> */
    for( $c=0; $c<$rows_view; $c++ ) {
        if( !$rabelflg ) {
            if( strncmp($res_view[$c][1], 'CP25730-', 8) == 0 ) {
                $rabelflg = true; // ラベル 表示
            }
        }
        if( !$konpoflg ) {
            if( strncmp($res_view[$c][1], 'CP08807-', 8) == 0 ) {
                $konpoflg = true; // コンポウバコ チユウ 2ピ-ス 表示
            }
        }
        if( !$poriflg ) {
            if( strncmp($res_view[$c][1], 'TP08441-', 8) == 0 ) {
                $poriflg = true; // ポリブクロ 80X160 表示
            }
        }
    }
    /* <-------------------------------------------------------------------- */

    // 行数分繰り返す
    for( $r=0,$a=0; $r<$rows_view; $r++,$a++ ) {

        /* 特殊：片方だけ表示する（表示する部品があった場合表示させない）--> */
        if( $rabelflg ) {
            if( strncmp($res_view[$r][1], 'CQ30241-', 8) == 0 ) {
                $a--;
                continue; // コンポウヨウラベル シヨウ 表示させない
            }
        }
        if( $konpoflg ) {
            if( strncmp($res_view[$r][1], 'CQ20823-', 8) == 0 ) {
                $a--;
                continue; // コンポウバコ チユウとコンポウバコ チユウ 1ピ-ス 表示させない
            }
        }
        if( $poriflg ) {
            if( strncmp($res_view[$r][1], 'CP20447-', 8) == 0 ) {
                $a--;
                continue; // ポリエチレンブクロ 80X160 表示させない
            }
        }
        /* <---------------------------------------------------------------- */

        IniDetailData( $d_data ); // 初期データセット

        $d_drow = -1;

        $idx = strlen($res_view[$r][0]) - 2;

        $sei_no[$idx] = "";
        if( $idx == 0 ) { // レベル.1の部品
            $reserveflg = 0;
            if( $res_view[$r][10] == "CC部品" ) {
                $d_drow = GetCCDetail( $res_view, $rows_view, $d_data, $r );
                $res_view[$r][4] = 0; // まれに、値が入っているがCC部品なので０にしておく
                $res_view[$r][5] = 0; // まれに、値が入っているがCC部品なので０にしておく
            } else {
                $parts_idx[$idx] = $a;
                $d_drow = GetLevelDetail( $res_view, $rows_view, $plan_no, $d_data, $sei_no[$idx], $r );
                $level_one = $d_drow;
                $count = $res_view[$r][3];       // レベル..2以下の数量にかける為、数量をセット
                $parts[$idx] = $res_view[$r][1]; // 子部品に親番号をセットする為、部品番号をセット
                $reserveflg = $res_view[$r][4] - $res_view[$r][5]; // 引当数 - 出庫数をセット
                if( $reserveflg <= 0 || $res_view[$r][5] > 0 ) {
                    $reserveflg = 0;
                } else {
//                  $_SESSION['s_sysmsg'] .= "{$res_view[$r][1]} : 出庫されていません！※時間を空け、再度お試し下さい。";
                    $reserveflg = -1;
                    if( $res_view[$r][6] > 0 && COMPLETE ) {
                        $_SESSION['s_sysmsg'] .= "{$res_view[$r][1]} : {$res_view[$r][6]} 個の出庫残あり。※時間を空け再実行して下さい。";
                        $reserveflg = 1;          // 出庫残あり
                        $d_drow = 0;              // エラーで表示する
                        IniDetailData( $d_data ); // 初期データセット
                    }
                }
            }
        } else { // レベル.1以外の部品
            // レベル.1のデータが取得できて、１工程しかない場合はレベル..2以下は表示しない。
            if( $res_view2[$a-1][0] == ".1" && $res_view2[$a-1][6] == 1 && $res_view2[$a-1][7] != "--" ) {
                $a--;
                continue; // 不要な為、次の行へ
            }

            // 親部品とレベルが、2以上離れている場合表示しない
            if( COMPLETE && (strlen($res_view2[$a-1][0])) < ($idx+1) ) {
                $a--;
                continue; // 不要な為、次の行へ
            }

            $d_data[0][4] = $parts[$idx-1]; // 子部品なので、親番号セット

            if( $res_view[$r][10] == "CC部品" ) {
                $d_drow = GetCCDetail( $res_view, $rows_view, $d_data, $r );
            } else {
                $parts_idx[$idx] = $a;
                $d_drow = GetLevelDetail( $res_view, $rows_view, $sei_no[$idx-1], $d_data, $sei_no[$idx], $r );

                /* 個別対応： CQ16739-3 → CQ19736-0 ----------------------> */
                if( $d_drow == 0 && strncmp($res_view[$r][1], 'CQ16739-', 8) == 0 ) {
                    $res_view[$r][1] = 'CQ19736-0';
                    $d_drow = GetLevelDetail( $res_view, $rows_view, $sei_no[$idx-1], $d_data, $sei_no[$idx], $r );
                }
                /* <-------------------------------------------------------- */

                // GetLevelDetail()内、最新の枝番でデータ取得不可の場合、古い枝番も検索している為、
                // そのデータが、既に表示用配列に格納されている場合があるのでチェックを行う
                if( $d_drow > 0 ) {
                    for( $i=$a-1; 0<=$i; $i-- ) {
                        if( 1 > strlen($res_view2[$i][0])-2 ) break; // レベル.1になったら抜ける

                        if( $res_view[$r][0] != $res_view2[$i][0] ) continue; // レベルが違うなら次の行へ

                        if( $res_view[$r][1] == $res_view2[$i][1] ) {
                            $d_drow = -1; // 同一部品を見つけたらエラーにする
                            break;
                        }
                    }
                }

                $parts[$idx] = $res_view[$r][1]; // 子部品に親番号をセットする為、部品番号をセット

                if( empty($sei_no[$idx]) == true && $level_one != 0 ) {
                    // 部品番号の製造番号が取得できないかつ、レベル.1の情報は取得できてるとき
                    if( IsSerial($res_view[$r][1], GetPlanNo($res_view[$r][1], $sei_no[$idx-1])) == true ) {
                        $d_drow = 0;  // シリアル番号取得可能なら表示
                    } else {
                        if( COMPLETE ) {
                            $d_drow = -1; // 計上済計画の為、使用してないと判断し非表示
                        } else {
//                          $_SESSION['s_sysmsg'] .= "*** {$res_view[$r][1]} : 未計上の為、使用の有無が確定していません。 ***";
                            $d_drow = 0;  // 未計上計画の為、表示させる
                        }
                    }
                }
            }

            if( $reserveflg != 0 ) {  // 出庫数が引当数に到達していない
                $d_data[0][1] = "--"; // エラー表示にする為
                $d_drow = 1;          // エラー表示用１工程分だけ表示させる
            }
        }

        if( $d_drow != -1 && $reserveflg == 0 ) {
            // 同一レベルに、枝番号が複数あるとき
            // 使用していない方、又はCC部品の方を表示させない為の処理
            for( $i=$a-1; $i>=0; $i-- ) {
                if( $res_view[$r][0] != $res_view2[$i][0] ) {
                    break; // レベルが変わったら抜ける
                }
                if( strncmp($res_view[$r][1], $res_view2[$i][1], 8) != 0 ) {
                    continue; // 枝番号以前が違う場合は、次の行へ
                }
                if( $res_view2[$i][7] != "--" && $res_view2[$i][12] != "CC部品" ) {
                    if( $d_data[0][1] == "--" ) {
                        $d_drow = -1; // すでに違う枝番号で登録されている為、表示させない
                        break;
                    } else {
                        $res_view2[$i][3] = sprintf( "%0.04f", $res_view2[$i][3] );
                        $res_view[$r][3]  = sprintf( "%0.04f", $res_view[$r][3] );
                        if( $res_view2[$i][3] == $res_view[$r][3] && $res_view2[$i][3] > 1 && $res_view[$r][3] > 1 ) {
                            $a= $i;      // 使用数が同じ場合、両方１以上なら最新の枝番号で登録する
                            $d_drow = 1; // 再登録する為
                            break;
                        }
                    }
                }
                if( $res_view2[$i][12] == "CC部品" ) {
                    $a= $i;      // CC部品として登録されていたら在庫品に置き換える
                    $d_drow = 1; // 再登録する為
                    break;
                }
                if( strcmp($res_view[$r][1], $res_view2[$i][1]) == 0 ) {
                    $d_drow = -1; // 同一部品の為、表示させない
                    break;
                }
            }
        }

        if( $d_drow < 0 || ($reserveflg == -1 && COMPLETE) ) { // 計上済みで、出庫数０も表示しない
            $a--;
            continue; // 不要な為、次の行へ
        } else if( $d_drow < 1 ) {
            $d_drow = 1; // データ取得に失敗時、エラー表示するため
        }

        if( $idx > 0 ) {
            if( $idx == 1 ) { // レベル.2
                $count2 = $res_view[$r][3];
                $count3 = $count4 = $count5 = 1;
            } else if( $idx == 2 ) { // レベル.3
                $count3 = $res_view[$r][3];
                $count4 = $count5 = 1;
            } else if( $idx == 3 ) { // レベル.4
                $count4 = $res_view[$r][3];
                $count5 = 1;
            } else if( $idx == 4 ) { // レベル.5
                $count5 = $res_view[$r][3];
            }
//            $res_view[$r][3] *= $count; // レベル.1以外なら、レベル.1の使用数をかける
            $res_view[$r][3] = $count * $count2 * $count3 * $count4 * $count5 ; // レベル.1以外なら、使用数をかける
            if( $d_data[0][1] != "--" ) {
                if( $res_view[$r][7] != "有償" ) {
                    $res_view2[$parts_idx[$idx-1]][8] = 0; // 工程単価をゼロにする
                    $res_view2[$parts_idx[$idx-1]][9] = 0; // 工程金額をゼロにする
                }
                $res_view2[$parts_idx[$idx-1]][12] = $res_view[$r][7]; // 有償or無償をセット
            }
        } else {
            $count2 = $count3 = $count4 = $count5 = 1; // レベル.1 の時 使用数初期化
        }

        if( !empty($d_data[0][5]) ) {
            $res_view[$r][10] = $d_data[0][5];
            if( $res_view[$r][7] != "有償" ) {
                $d_data[0][1] = "--";
                $d_data[0][2] = 0;
                if( $idx > 0 ) {
                    $res_view2[$parts_idx[$idx-1]][8] = 0; // 工程単価をゼロにする
                    $res_view2[$parts_idx[$idx-1]][9] = 0; // 工程金額をゼロにする
                    $res_view2[$parts_idx[$idx-1]][12] = $res_view[$r][7]; // 有償or無償をセット
                }
            }
        }

        // 表示用レコードへ取得した表示データを一行単位でセット
        for( $i=0; $i<$d_drow; $i++ ) {
            if( $i > 0 ) {
                $a++;    // 工程が複数あるときに通る
            }
            for( $c=0; $c<$num_view; $c++ ) {
                switch ($c) {
                    case 0:    // レベル
                    case 1:    // 部品番号
                    case 2:    // 部品名
                    case 3:    // 使用数
                    case 4:    // 引当数
                    case 5:    // 出庫数
                        $res_view2[$a][$c] = $res_view[$r][$c];
                        break;
                    case 6:    // 工程
                        $res_view2[$a][$c] = $d_data[$i][0];
                        break;
                    case 7:    // 工程名
                        $res_view2[$a][$c] = $d_data[$i][1];
                        break;
                    case 8:    // 工程単価
                        $res_view2[$a][$c] = $d_data[$i][2];
                        break;
                    case 9:    // 工程金額
                        $res_view2[$a][$c] = Uround($res_view[$r][3] * $d_data[$i][2], 2);
                        break;
                    case 10:    // 内外作
                        $res_view2[$a][$c] = $d_data[$i][3];
                        break;
                    case 11:    // 親番号
                        $res_view2[$a][$c] = $d_data[0][4];
                        break;
                    default:   // 12 CC部品
                        $res_view2[$a][$c] = $res_view[$r][10];
                }
            }
            if( !empty($d_data[0][6]) ) {
                $res_view2[$a][$c] = $d_data[0][6]; // 表示データではないが、13に振替フラグ
            }
        }
    }
    $rows_view = $a;

    /////////// commit トランザクション終了
    // query_affected_trans($con, 'commit');
    // pg_close($con); は必要ない
}

// 組立完成経歴をセット
function SetCompleteInfo( $plan_no, $parts_no )
{
    $query = "
                SELECT   comp_date
                FROM     assembly_completion_history
                WHERE    plan_no = '$plan_no' AND assy_no = '$parts_no'
             ";
    if( getResultWithField2($query, $field, $res) <= 0 ) {
        if( COMPREGI ) {
            define('COMPLETE', true);
        } else {
            define('COMPLETE', false);
        }
        define('COMPDATE', date('Ymd'));
    } else {
        define('COMPLETE', true);
        define('COMPDATE', $res[0][0]);
    }
}

// デフォルト値セット
function IniDetailData( &$d_data )
{
    $d_data = array();
    $d_data[0][0] = '1';         // 工程
    $d_data[0][1] = "--";        // 工程記号
    $d_data[0][2] = 0;           // 単価
    $d_data[0][3] = "外作";      // 内外作
    $d_data[0][4] = "---------"; // 親番号
}

// 部品在庫経歴照会にデータがあるかチェック
function IsSerial( $parts_no, $plan_no )
{
    $query = $field = $res = array();

    // 部品在庫経歴照会：部品番号と計画番号よりシリアル番号取得
    $query = "
                SELECT   *
                FROM     parts_stock_history
                WHERE    parts_no = '$parts_no' AND plan_no = '$plan_no'
                ORDER BY stock_mv DESC, upd_date ASC LIMIT 3
             ";
    if( getResultWithField2($query, $field, $res) <= 0 ) {
//        $_SESSION['s_sysmsg'] .= "$parts_no : $plan_no : シリアル番号取得失敗！";
        return false;
    }

    return true;
}

function GetPlanNo( $level, $sei_no )
{
    $plan_no = $sei_no;

    // レベル.1以外は＠を先頭に付けた製造番号を計画名にする
    if( $level != ".1" ) {
        $plan_no = "@";
        $plan_no .= $sei_no;
    }

    return $plan_no;
}

// 一つ後の枝番号を取得
function GetNewNo( $parts_no )
{
    $old_no = hexdec( substr($parts_no, -1, 1) );

    if( $old_no >= 15 ) {
        return $parts_no;
    }

    $new_no = dechex( $old_no + 1 );
    if( $old_no >= 9 ) {
        $new_no = strtoupper( $new_no );
    }

    return str_replace(('-'.+$old_no), ('-'.+$new_no), $parts_no);
}

// 一つ前の枝番号を取得
function GetOldNo( $parts_no )
{
    $new_no = hexdec( substr($parts_no, -1, 1) );

    if( $new_no <= 0 ) {
        return $parts_no;
    }

    $old_no = dechex( $new_no - 1 );
    if( $new_no >= 11 ) {
        $old_no = strtoupper( $old_no );
    }

    return str_replace(('-'.+$new_no), ('-'.+$old_no), $parts_no);
}

// 支給品かチェック (月次処理にて支給品かセットしている)
function IsProvide( $parts_no, $reg_ymd )
{
    $query = $field = $res = array();

    $kei_ym = substr(COMPDATE, 0, 6);
    $now_ym = date('Ym');
    $reg_ym = substr($reg_ymd, 0, 6);

    // 計上月と照会月、登録月が同じ場合、まだ支給品の登録がされてないので前月の状態をみる
    if( $kei_ym == $now_ym && $kei_ym == $reg_ym ) {
        $year = substr($reg_ymd, 0, 4);
        $month = substr($reg_ymd, 4, 2);
        if( $month == '01' ) {
            $year -= 1;
            $month = '12';
        } else {
            $month -= 1;
        }
        $reg_ym = sprintf( "%04s%02s", $year, $month );
    }

    $query = "
                SELECT   *
                FROM     provide_item
                WHERE    parts_no = '$parts_no' AND reg_ym = $reg_ym
                ORDER BY reg_ym DESC LIMIT 1
             ";
    if( getResultWithField2($query, $field, $res) <= 0 ) {
//        $_SESSION['s_sysmsg'] .= "$parts_no : 支給品ではありません！<br>";
          return false;
    }

    return true;
}

// CC部品のデータ取得
function GetCCDetail( &$res_view, $rows_view, &$d_data, &$r )
{
    $query = $field = $res = array();
    $cc_count = 0;

    // 枝番号が複数あるとき、最新の行を選択（昇順で入っているはず）
    for( ; $r < $rows_view; $r++ ) {
        $cc_count++;
        if( $r+1 == $rows_view || $res_view[$r+1][10] != "CC部品" || strncmp($res_view[$r][1],$res_view[$r+1][1], 7) ) {
            break;  // 最後の行、または次の行がCC部品以外、別部品に変わったら抜ける
        }
    }
    $parts_no = $res_view[$r][1]; // 検索する部品名をセット

    $compdate = COMPDATE;

    // 最新の枝番号から単価を取得する、ダメなら前の枝番号で検索
    for( ; ; ) {
        // 支給品かのチェックをする
        if( IsProvide($parts_no, $compdate) ) {
            $d_data[0][5] = "支給品";
        } else {
            $d_data[0][5] = "";
        }

        $query = "
                    SELECT   *
                    FROM     act_payable
                    WHERE    parts_no = '$parts_no' AND act_date <= '$compdate' AND koutei != ''
                    ORDER BY ken_date DESC LIMIT 1
                 ";
        $res   = array();
        if( getResultWithField2($query, $field, $res) > 0 ) {
            // 買掛実績あり、買掛実績の単価をセット
            $d_data[0][1] = $res[0][10]; // 工程記号
            $d_data[0][2] = $res[0][12]; // 発注単価
        } else {
            $query = "
                        SELECT   *
                        FROM     parts_cost_history
                        WHERE    parts_no = '$parts_no' AND vendor != 88888 AND as_regdate <= '$compdate'
                        ORDER BY reg_no DESC, pro_no ASC LIMIT 1
                     ";
            $res   = array();
            if( getResultWithField2($query, $field, $res) > 0 ) {
                // 買掛実績なし、単価経歴の単価をセット
                $d_data[0][1] = $res[0][2];  // 工程記号
                $d_data[0][2] = $res[0][11]; // 単価
            }
        }

        if( $d_data[0][1] == "--" ) {
            $old_no = GetOldNo( $parts_no );
            $cc_count--;
            if( $parts_no == $old_no || $d_data[0][5] == "支給品" || $cc_count == 0 ) {
                return 0;
            }
            $parts_no = $old_no;
            continue;
        }

        $res_view[$r][1] = $parts_no;
        break;
    }

    return 1;
}

function GetLevelDetail( &$res_view, $rows_view, $parent_sei_no, &$d_data, &$child_sei_no, &$r )
{
    $plan_no = array();
    $d_drow  = $cnt = 0;
    $plan_no = $parent_sei_no;

    // レベル.1以外は＠を先頭に付けた製造番号を計画名にする
    if( $res_view[$r][0] != ".1" ) {
        $plan_no = "@";
        $plan_no .= $parent_sei_no;
    }

    // 枝番号が複数あるとき、使用した行のみを表示する為の処理
    for( $i=$r; $i<$rows_view; $i++ ) {
        if( strncmp($res_view[$r][0], $res_view[$i][0], strlen($res_view[$r][0])) !=0 ) {
            break; // レベルが変わったら抜ける
        }
        if( strncmp($res_view[$r][1], $res_view[$i][1], 8) !=0 ) {
            continue; // 部品名が一致しなければ次に行へ
        }
        if( $res_view[$i][10] == "CC部品" ) {
            continue; // 一致した部品名がCC部品なら次に行へ
        }
        $d_drow = GetDetailData( $res_view[$i][1], $plan_no, $d_data, $child_sei_no );

        $cnt++; // 古い枝番検索時、スルーていい枝数をカウント
        $r = $i;

        if( $d_drow > 0 ) {
//            $r = $i;
            break;
        }
    }

    // 古い枝番号を使用してないか検索してみる
    if( $d_drow == 0 ) {
        $parts_no = $res_view[$r][1];
        $org_no   = $res_view[$r][1];
        for( $i=$cnt; $i>1; $i-- ) { // 既に検索した枝番はスルーする。
            $parts_no = GetOldNo( $parts_no );
        }
        for( ; ; ) {
            $old_no = GetOldNo( $parts_no );
            if( $parts_no == $old_no || $res_view[$r][0] == ".1" || strcmp($res_view[$r-1][1], $old_no) == 0 ) {
                $res_view[$r][1] = $org_no;
                break;
            }
            $res_view[$r][1] = $old_no;
            $d_drow = GetDetailData( $res_view[$r][1], $plan_no, $d_data, $child_sei_no );
            if( $d_drow == 0 ) {
                $parts_no = $old_no;
                continue;
            }
            break;
        }
    }

    if( $res_view[$r][7] == '有償' ) {
        $d_data[0][2] = 0;
    }

    return $d_drow;
}

// 計上日を求める計算
function AccrualDateCalc( $res2, $rows2, $stock, $out_stock, $flag )
{
    $max = -1;
    if( $flag == "after" ) {
        $stock -= $out_stock;
        for( $r=0; $r<$rows2; $r++ ) {
            if( ctype_space($res2[$r][3])==true || $res2[$r][3]!=2  || $res2[$r][2] < 0 ) {
                continue; // マイナス行は飛ばす
            }
            $stock += $res2[$r][2]; // 在庫に次の移動数を足す
            if( $max == -1 ) {
                $max = $r;
            }
            if( $res2[$max][2] < $res2[$r][2] ) {
                $max = $r;
            }
            if( $stock >= 0 ) {
                $r = $max;
                break;
            }
        }
    } else if( $flag == "before" ) {
        if( $stock - $out_stock < 0 ) {
            $out_stock -= $stock;
        }
        $max = -1;
        for( $r=0; $r<$rows2; $r++ ) {
            if( ctype_space($res2[$r][3])==true || $res2[$r][3]!=2 || $res2[$r][2] < 0 ) {
                continue; // マイナス行は飛ばす
            }
            $stock -= $res2[$r][2]; // 在庫より移動数を引く
            if( $out_stock < $stock ) {
                continue; // 出庫量よりまだ在庫量が多い場合次の移動数へ
            }
            if( $max == -1 ) {
                $max = $r; // 初回のみ
                $res2[$max][2] = $out_stock - $stock;
            }
            if( $res2[$max][2] <= $res2[$r][2] ) {
                $max = $r;
            }
            if( $stock <= 0 || ($stock < ($out_stock/2) && $stock < $res2[$max][2]) ) {
                if( ($stock+$res2[$r][2]) >= $res2[$max][2] ) {
                    if( $res2[$r][2] >= $res2[$max][2] ) {
                        $max = $r;
                    }
                }
                $r = $max;
                break;
            }
        }
    } else { // other
        for( $r=0; $r<$rows2; $r++ ) {
            if( $res2[$r][3]!=2 || $res2[$r][2] < 0 ) {
                continue; // マイナス行は飛ばす
            }
            if( ($res2[$r][2]+$res2[$r][9]) >= $out_stock ) {
                if( $res2[$r][2] > ($out_stock/2) ) {
                    break;
                } else {
                    $out_stock -= $res2[$r][2];
                }
            }
        }
    }
    return $r;
}

// 部品が検査中かチェック
function IsInspection( $parts_no )
{
    $query = $field = $res = array();

    $query = "
            SELECT
                ''                                     AS 行番号 -- 0
                ,
                to_char(data.uke_date, 'FM0000/00/00') AS 計上日 -- 1 (受付日)
                ,
                '検査中'                               AS 摘要   -- 2
            FROM
                order_plan      AS plan
                LEFT OUTER JOIN
                order_data      AS data
                    USING(sei_no)
                LEFT OUTER JOIN
                order_process   AS proc
                    USING(sei_no, order_no)
            WHERE
                plan.parts_no = '$parts_no'
                AND plan.zan_q > 0
                AND data.uke_q > 0
                AND data.ken_date = 0
                AND proc.next_pro = 'END..'
             ";
    if( getResultWithField2($query, $field, $res) <= 0 ) {
//        $_SESSION['s_sysmsg'] .= "$parts_no : 検査中ではありません。";
          return false;
    }

    return true;
}

// 部品在庫経歴照会：対象の計上日レコードを取得
function GetPartsStokHistory( $parts_no, $plan_no, $den_no, &$res, &$r, &$fstock_mv )
{
    $query = $field = $res = array();
    $rows = 0;

    // 部品在庫経歴照会：部品番号と計画番号よりシリアル番号取得
    if( !$den_no ) {
        $query = "
                    SELECT   *
                    FROM     parts_stock_history
                    WHERE    parts_no = '$parts_no' AND plan_no = '$plan_no'
                    ORDER BY stock_mv DESC, upd_date ASC, serial_no ASC LIMIT 5
                 ";
        if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
//        $_SESSION['s_sysmsg'] .= "$parts_no : $plan_no : シリアル番号取得失敗！";
            return 0;
        }
        $fstock_mv = $res[0][2]; // 在庫移動数
    } else {
        $query = "
                    SELECT   *
                    FROM     parts_stock_history
                    WHERE    parts_no = '$parts_no' AND den_no = '$den_no'
                    ORDER BY stock_mv DESC, upd_date ASC LIMIT 5
                 ";
        if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
//        $_SESSION['s_sysmsg'] .= "$parts_no : $den_no : シリアル番号取得失敗！";
            return 0;
        }
    }

    $serial = $res[0][13];   // 自動連番
    $stock = $res[0][9];     // TNKの在庫数
    $out_stock = $fstock_mv; // 在庫移動数

    // 部品在庫経歴照会：最大と最小を足したときに０になった場合、次の行をセット
    if( $rows > 2 && (($res[0][2] + $res[$rows-1][2]) == 0) ) {
        $serial = $res[1][13];   // 自動連番
        $stock = $res[1][9];     // TNKの在庫数
        if( $fstock_mv == 0 ) {
            $fstock_mv = $res[1][2]; // 在庫移動数
        }
        $out_stock = $fstock_mv; // 在庫移動数
    }

    // 部品在庫経歴照会：出庫在庫がいつ入庫したか判断し計上日取得
    if( $stock <= 0 || $stock < ($out_stock/2) ) {
        // 在庫が０以下、移動数が在庫の半分より多い
        $query = "
                    SELECT   *
                    FROM     parts_stock_history
                    WHERE    parts_no = '$parts_no' AND serial_no >= $serial
                    ORDER BY serial_no ASC LIMIT 20
                 ";
        $res   = array();
        $rows2 = getResultWithField2($query, $field, $res);

        for( $i=1; $i<$rows2; $i++ ) {
            if( ctype_space($res[$i][3]) && !ctype_space($res[$i][4]) ) {
                if( abs($res[$i][2]) >= 0 ) {
                    $c = (int)($res[$i][9] - $res[$i][2]);
                } else {
                    $c = (int)($res[$i][9] + $res[$i][2]);
                }
            } else {
                $c = (int)($res[$i][2] + $res[$i][9]);
            }
            if( $c < 0 ) {
                continue; // 次の行へ
            }
            if( !ctype_space($res[$i][3]) || $res[$i][6] == "" ) {
                // 通常の入庫
                $r = AccrualDateCalc( $res, $rows2, $stock, $out_stock, "after" );
                if( $r == $rows2 ) {
                    if( COMPLETE ) {
                        $_SESSION['s_sysmsg'] .= "$parts_no : 買掛データが、登録されていない可能性があります。";
                    }
                    return 0;
                }
                break;
            }
            if( $res[$i][2] < 0 ) {
                // 再入庫や在庫品の移動
                $den_no = $res[$i][6]; // 再入庫時の伝票番号をキーに検索する為セット
                $query = "
                            SELECT   *
                            FROM     parts_stock_history
                            WHERE    parts_no = '$parts_no' AND den_no = '$den_no'
                            ORDER BY serial_no ASC LIMIT 1
                         ";
                $res   = array();
                if( getResultWithField2($query, $field, $res) <= 0 ) {
                    $_SESSION['s_sysmsg'] .= "$parts_no : $plan_no 買掛データ取得不可！（再入庫や在庫品）";
                    return 0;
                }

                $serial = $res[0][13]; // 再入庫の出庫時の自動連番をキーに検索する為セット

                $query = "
                           SELECT   *
                           FROM     parts_stock_history
                           WHERE    parts_no = '$parts_no' AND serial_no <= $serial
                           ORDER BY serial_no DESC LIMIT 100
                         ";
                $res   = array();
                if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
                    $_SESSION['s_sysmsg'] .= "$parts_no : $plan_no 買掛データ取得不可！（再入庫や在庫品:100）";
                    return 0;
                }

                $r = AccrualDateCalc( $res, $rows, $stock, $out_stock, "before" );
                if( $r == $rows ) {
                    $_SESSION['s_sysmsg'] .= "$parts_no : $plan_no 買掛データ取得不可！（再入庫・在庫品の計算）";
                    return 0;
                }
            }
            break;
        }
        if( $i == $rows2 ) {
            if( COMPLETE ) {
                if( IsInspection($parts_no) ) {
                    $_SESSION['s_sysmsg'] .= "$parts_no : ただいま検査中です。※時間を空け、再度お試し下さい。";
                } else {
                    /* 2021.08.24 Add. ------------------------------------> */
                    if( IsProvide($parts_no, date('Ym-d')) > 0 ) {
                        // 部品在庫経歴照会でエラーになるが、支給品の為、強制的に値をセット
                        $res[0][5]  = '6';          // 伝票区分
                        $res[0][11] = date('Ym-d'); // データの日
                        return 1;
                    }
                    /* <---------------------------------------------------- */
                    $_SESSION['s_sysmsg'] .= "$parts_no : 入庫データ不明です。※時間を空け、再度お試し下さい。";
                }
            }
            return 0;
        }
    } else {
        // 在庫が１以上、移動数より在庫が多い
        $query = "
                    SELECT   *
                    FROM     parts_stock_history
                    WHERE    parts_no = '$parts_no' AND serial_no <= $serial AND upd_date <= '{$res[0][12]}'
                    ORDER BY serial_no DESC LIMIT 200
                 ";
        $res   = array();
        if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "$parts_no : 買掛データ見つかりません。(200)";
            return 0;
        }

        $r = AccrualDateCalc( $res, $rows, $stock, $out_stock, "before" );

        // シリアルの降順でヒットしない場合、更新日の降順でも探してみる。
        if( $r == $rows ) {
            $query = "
                        SELECT   *
                        FROM     parts_stock_history
                        WHERE    parts_no = '$parts_no' AND upd_date <= '{$res[0][12]}'
                        ORDER BY upd_date DESC LIMIT 200
                     ";
            $res   = array();
            if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "$parts_no : 買掛データ見つかりません。(200:upd)";
                return 0;
            }
    
            $r = AccrualDateCalc( $res, $rows, $stock, $out_stock, "before" );
        }

        // 取得データで、計上日が分からなかった場合リミットを増やし再度検索
        if( $r == $rows ) {
            $query = "
                        SELECT   *
                        FROM     parts_stock_history
                        WHERE    parts_no = '$parts_no' AND serial_no <= $serial
                             AND in_id != '' AND out_id = ''
                        ORDER BY serial_no DESC LIMIT 100
                     ";
            $res   = array();
            if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "$parts_no : 買掛データ見つかりません。(100)\t\t2000/04/01 以前の場合は、ACSを使用し確認して下さい。";
                return 0;
            }
            $r = AccrualDateCalc( $res, $rows, $stock, $out_stock, "before" );
            if( $r == $rows ) {
                $_SESSION['s_sysmsg'] .= "$parts_no : 一致する買掛データがありません。(before)";
                return 0;
            }
        }
    }

    // 在庫調整は単価取得できないので、1つ前の入庫単価へ変更する。
    if( $res[$r][5] == '9' ) {
        $serial = $res[$r][13];
        $query = "
                    SELECT   *
                    FROM     parts_stock_history
                    WHERE    parts_no = '$parts_no' AND serial_no < $serial
                    ORDER BY serial_no DESC LIMIT 100
                 ";
        $res2   = array();
        if( ($rows2 = getResultWithField2($query, $field, $res2)) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "$parts_no : 買掛データ見つかりません。(在庫調整)";
            return 0;
        }

        for( $i=0; $i< $rows2; $i++ ){
            if( $res2[$i][3] == 2 ) {
                $r = $r + $i + 1;
                break;
            }
        }
    }

    return $rows;
}

// ACS画面：Top → 02 → [F10] で、表示される代替品あり
function IsAlternative( &$parts_no, &$tomodori )
{
    // 共取り品 LQ06578-0 LQ06578-1 (11/11/21〜)、LQ06588-0 LQ06588-1 (11/11/21〜)
    if( strncmp($parts_no, 'LQ06578-', 8) == 0 || strncmp($parts_no, 'LQ06588-', 8) == 0 ) {
        $parts_no = 'LB08177-0';
        $tomodori = true;

    // 代替部品を確認できた一覧
    } else if( strcmp($parts_no, 'CQ15722-2') == 0 ) { // CQ15722-2 (16/08/02〜)
        $parts_no = 'CQ41839-1';
    } else if( strcmp($parts_no, 'CQ39443-1') == 0 ) { // CQ39443-1 (20/07/28〜)
        $parts_no = 'CQ44424-1';
    } else if( strcmp($parts_no, 'CQ39563-1') == 0 ) { // CQ39563-1 (17/10/02〜)
        $parts_no = 'CQ35214-1';
    } else if( strcmp($parts_no, 'CQ39888-0') == 0 ) { // CQ39888-0 (10/06/21〜)
        $parts_no = 'CP00950-0';
    } else if( strcmp($parts_no, 'CQ40154-0') == 0 ) { // CQ40154-0 (10/11/09〜)
        $parts_no = 'CP00999-0';
    } else if( strcmp($parts_no, 'CQ43263-0') == 0 ) { // CQ43263-0 (14/12/23〜)
        $parts_no = 'CQ41598-0';
    } else if( strcmp($parts_no, 'CQ43264-0') == 0 ) { // CQ43264-0 (14/12/25〜)
        $parts_no = 'CQ35226-1';
    } else if( strcmp($parts_no, 'CQ43820-0') == 0 ) { // CQ43820-0 (20/05/27〜)
        $parts_no = 'CP04899-3';
    } else if( strcmp($parts_no, 'CQ45636-0') == 0 ) { // CQ45636-0 (20/04/22〜)
        $parts_no = 'CQ35225-1';
    } else if( strcmp($parts_no, 'CQ45711-1') == 0 ) { // CQ45711-1 (21/01/15〜)
        $parts_no = 'CQ35247-3';
    } else if( strcmp($parts_no, 'CQ45713-1') == 0 ) { // CQ45713-1 (21/01/15〜)
        $parts_no = 'CQ35257-3';
    } else if( strcmp($parts_no, 'CQ46057-0') == 0 ) { // CQ46057-0 (20/04/07〜)
        $parts_no = 'CP00999-0';
    } else if( strcmp($parts_no, 'CQ46058-1') == 0 ) { // CQ46058-1 (21/01/13〜)
        $parts_no = 'CP01048-0';
    } else if( strcmp($parts_no, 'CQ46218-0') == 0 ) { // CQ46218-0 (15/11/09〜)
        $parts_no = 'CQ35200-2';
    } else if( strcmp($parts_no, 'CQ46218-1') == 0 ) { // CQ46218-1 (20/04/10〜)
        $parts_no = 'CQ35200-3';
    } else if( strcmp($parts_no, 'CQ46219-0') == 0 ) { // CQ46219-0 (15/11/09〜)
        $parts_no = 'CQ35203-1';
    } else if( strcmp($parts_no, 'CQ46220-0') == 0 ) { // CQ46220-0 (15/11/10)
        $parts_no = 'CQ35208-2';
    } else if( strcmp($parts_no, 'CQ46220-1') == 0 ) { // CQ46220-1 (20/04/06〜)
        $parts_no = 'CQ35208-3';
    } else if( strcmp($parts_no, 'CQ46977-0') == 0 ) { // CQ46977-0 (16/11/24〜)
        $parts_no = 'CP00955-1';
    } else if( strcmp($parts_no, 'CQ48288-0') == 0 ) { // CQ48288-0 (21/03/10〜)
        $parts_no = 'CQ35203-1';
    } else if( strcmp($parts_no, 'CQ48697-0') == 0 ) { // CQ48697-0 (19/11/11〜)
        $parts_no = 'CQ35222-2';
    } else if( strcmp($parts_no, 'CQ48698-0') == 0 ) { // CQ48698-0 (19/11/11〜)
        $parts_no = 'CQ35226-1';
    } else if( strcmp($parts_no, 'CQ48713-0') == 0 ) { // CQ48713-0 (19/11/20〜19/11/20)
        $parts_no = 'CQ35211-4';
    } else if( strcmp($parts_no, 'CQ48714-0') == 0 ) { // CQ48714-0 (19/11/20〜)
        $parts_no = 'CQ35214-1';
    } else if( strcmp($parts_no, 'CQ48715-0') == 0 ) { // CQ48715-0 (19/11/11〜20/01/08)
        $parts_no = 'CQ35219-3';
    } else if( strcmp($parts_no, 'CQ48812-0') == 0 ) { // CQ48812-0 (20/09/15〜)
        $parts_no = 'CQ03291-1';
    } else if( strcmp($parts_no, 'CQ48969-0') == 0 ) { // CQ48969-0 (20/05/18〜)
        $parts_no = 'CQ42507-0';
    } else if( strcmp($parts_no, 'CQ49040-0') == 0 ) { // CQ48969-0 (20/09/10〜)
        $parts_no = 'CQ42495-0';
    } else if( strcmp($parts_no, 'CQ49059-0') == 0 ) { // CQ49059-0 (20/09/10〜)
        $parts_no = 'CQ42489-0';
    } else if( strcmp($parts_no, 'CQ49102-0') == 0 ) { // CQ49102-0 (20/12/17〜)
        $parts_no = 'CP05420-0';
    } else if( strcmp($parts_no, 'CQ49103-0') == 0 ) { // CQ49103-0 (20/12/17〜)
        $parts_no = 'CP21944-0';

    } else if( strcmp($parts_no, 'LB02450-1') == 0 ) { // LB02450-1 (21/06/30〜)
        $parts_no = 'LB02450-0';
    } else if( strcmp($parts_no, 'LB02527-2') == 0 ) { // LB09324-2 (21/06/18〜)
        $parts_no = 'LB02527-1';
    } else if( strcmp($parts_no, 'LB09324-3') == 0 ) { // LB09324-3 (20/06/22〜)
        $parts_no = 'LB09324-0';

    } else if( strcmp($parts_no, 'LP10359-3') == 0 ) { // LP10359-3 (19/10/18〜)
        $parts_no = 'LP10359-1';
    } else if( strcmp($parts_no, 'LP13866-6') == 0 ) { // LP13866-6 (21/06/18〜)
        $parts_no = 'LP13866-5';
    } else if( strcmp($parts_no, 'LP13867-5') == 0 ) { // LP13867-5 (21/06/18〜)
        $parts_no = 'LP13867-4';
    } else if( strcmp($parts_no, 'LP30920-3') == 0 ) { // LP30920-3 (20/06/22〜)
        $parts_no = 'LP30920-2';

    } else if( strcmp($parts_no, 'LQ03998-3') == 0 ) { // LQ03998-3 (20/03/09〜)
        $parts_no = 'LQ03998-2';
    } else if( strcmp($parts_no, 'LQ04189-4') == 0 ) { // LQ04189-4 (20/04/13〜)
        $parts_no = 'LQ04189-3';
    } else if( strcmp($parts_no, 'LQ06004-1') == 0 ) { // LQ06004-1 (17/04/04〜)
        $parts_no = 'LQ07266-0';
    } else if( strcmp($parts_no, 'LQ08075-1') == 0 ) { // LQ08075-1 (21/06/12〜)
        $parts_no = 'LQ08075-0';
    } else {
        return false;
    }

    return true;
}

// ACS画面：Top → 02 → [F10] で、表示される代替部品なし
function IsNoSubstitute( $parts_no )
{
    if( // 確認したが、代替部品がなかった一覧
        strcmp($parts_no, 'CA91348-1') == 0

     || strcmp($parts_no, 'CB64153-3') == 0
     || strcmp($parts_no, 'CB66523-0') == 0

     || strcmp($parts_no, 'CP00950-0') == 0
     || strcmp($parts_no, 'CP00996-1') == 0
     || strcmp($parts_no, 'CP02057-7') == 0
     || strcmp($parts_no, 'CP03269-0') == 0
     || strcmp($parts_no, 'CP11554-1') == 0
     || strcmp($parts_no, 'CP11557-0') == 0
     || strcmp($parts_no, 'CP20083-4') == 0
     || strcmp($parts_no, 'CP22066-E') == 0
     || strcmp($parts_no, 'CP22854-0') == 0
     || strcmp($parts_no, 'CP24459-2') == 0

     || strcmp($parts_no, 'CQ01259-0') == 0
     || strcmp($parts_no, 'CQ10337-1') == 0
     || strcmp($parts_no, 'CQ12177-1') == 0
     || strcmp($parts_no, 'CQ15087-5') == 0
     || strcmp($parts_no, 'CQ15403-0') == 0
     || strcmp($parts_no, 'CQ18883-2') == 0
     || strcmp($parts_no, 'CQ18936-1') == 0
     || strcmp($parts_no, 'CQ19809-0') == 0
     || strcmp($parts_no, 'CQ20711-1') == 0
     || strcmp($parts_no, 'CQ21458-1') == 0
     || strcmp($parts_no, 'CQ23525-1') == 0
     || strcmp($parts_no, 'CQ23781-1') == 0
     || strcmp($parts_no, 'CQ23813-1') == 0
     || strcmp($parts_no, 'CQ24758-1') == 0
     || strcmp($parts_no, 'CQ29437-1') == 0
     || strcmp($parts_no, 'CQ30923-1') == 0
     || strcmp($parts_no, 'CQ31278-0') == 0
     || strcmp($parts_no, 'CQ31279-0') == 0
     || strcmp($parts_no, 'CQ31280-0') == 0
     || strcmp($parts_no, 'CQ32206-0') == 0
     || strcmp($parts_no, 'CQ33066-1') == 0
     || strcmp($parts_no, 'CQ33072-1') == 0
     || strcmp($parts_no, 'CQ35226-1') == 0
     || strcmp($parts_no, 'CQ40653-0') == 0
     || strcmp($parts_no, 'CQ41583-0') == 0
     || strcmp($parts_no, 'CQ44406-2') == 0
     || strcmp($parts_no, 'CQ46802-0') == 0
     || strcmp($parts_no, 'CQ46803-0') == 0
     || strcmp($parts_no, 'CQ46804-0') == 0
     || strcmp($parts_no, 'CQ47700-0') == 0
     || strcmp($parts_no, 'CQ47901-0') == 0
     || strcmp($parts_no, 'CQ48239-0') == 0
     || strcmp($parts_no, 'CQ48264-0') == 0
     || strcmp($parts_no, 'CQ48276-0') == 0
     || strcmp($parts_no, 'CQ48344-0') == 0
     || strcmp($parts_no, 'CQ48719-0') == 0
     || strcmp($parts_no, 'CQ48723-0') == 0
     || strcmp($parts_no, 'CQ48724-0') == 0
     || strcmp($parts_no, 'CQ48950-0') == 0
     || strcmp($parts_no, 'CQ49106-0') == 0
     || strcmp($parts_no, 'CQ53406-0') == 0

     || strcmp($parts_no, 'LB07397-2') == 0
     || strcmp($parts_no, 'LB07403-2') == 0
     || strcmp($parts_no, 'LB09324-1') == 0
     || strcmp($parts_no, 'LB09324-2') == 0
     || strcmp($parts_no, 'LB09333-1') == 0
     || strcmp($parts_no, 'LB09603-0') == 0

     || strcmp($parts_no, 'LP14069-B') == 0
     || strcmp($parts_no, 'LP30939-8') == 0
     || strcmp($parts_no, 'LP31351-1') == 0

     || strcmp($parts_no, 'LQ01456-0') == 0
     || strcmp($parts_no, 'LQ01457-0') == 0
     || strcmp($parts_no, 'LQ01478-0') == 0
     || strcmp($parts_no, 'LQ01979-2') == 0
     || strcmp($parts_no, 'LQ01982-1') == 0
     || strcmp($parts_no, 'LQ01983-2') == 0
     || strcmp($parts_no, 'LQ01986-1') == 0
     || strcmp($parts_no, 'LQ02097-0') == 0
     || strcmp($parts_no, 'LQ02098-0') == 0
     || strcmp($parts_no, 'LQ02259-7') == 0
     || strcmp($parts_no, 'LQ02882-0') == 0
     || strcmp($parts_no, 'LQ02883-0') == 0
     || strcmp($parts_no, 'LQ03501-1') == 0
     || strcmp($parts_no, 'LQ03846-0') == 0
     || strcmp($parts_no, 'LQ04994-0') == 0
     || strcmp($parts_no, 'LQ05085-5') == 0
     || strcmp($parts_no, 'LQ05130-5') == 0
     || strcmp($parts_no, 'LQ05329-0') == 0
     || strcmp($parts_no, 'LQ05687-4') == 0
     || strcmp($parts_no, 'LQ05787-3') == 0
     || strcmp($parts_no, 'LQ06165-0') == 0
     || strcmp($parts_no, 'LQ07112-1') == 0
     || strcmp($parts_no, 'LQ07529-0') == 0
     || strcmp($parts_no, 'LQ07785-0') == 0
     || strcmp($parts_no, 'LQ07786-0') == 0
     || strcmp($parts_no, 'LQ07990-0') == 0
     || strcmp($parts_no, 'LQ07994-0') == 0
     || strcmp($parts_no, 'LQ07996-0') == 0
     || strcmp($parts_no, 'LQ08159-0') == 0
     || strcmp($parts_no, 'LQ08188-0') == 0
     || strcmp($parts_no, 'LQ08190-0') == 0
    ) {
        return true;
    } else {
        return false;
    }
}

// 買掛実績の照会：対象のレコードを取得
function GetActPayable( $parts_no, $kei_date, $flag, $uke_no, $genpin, &$res )
{
    $query = $field = $res = array();
    $rows = 0;

    // 買掛実績の照会：計上日と受付番号より発注単価取得
    $query = "
                 SELECT   *
                 FROM     act_payable
                 WHERE    act_date = $kei_date AND uke_no = '$uke_no'
                 ORDER BY regdate DESC LIMIT 3
             ";
    $res  = array();
    $rows = getResultWithField2( $query, $field, $res );

    if( $rows > 0 ) {
        return $rows;
    }

    // 受付番号が違うときの対応
    if( $flag == "after" ) {
        $query = "
                    SELECT   *
                    FROM     act_payable
                    WHERE    act_date >= $kei_date AND parts_no = '$parts_no' AND genpin = $genpin
                    ORDER BY act_date ASC LIMIT 3
                 ";
    } else {
        if( $genpin != 0 ) {
            $query = "
                        SELECT   *
                        FROM     act_payable
                        WHERE    act_date < $kei_date AND parts_no = '$parts_no' AND genpin = $genpin
                        ORDER BY act_date DESC LIMIT 3
                     ";
        } else {
            $query = "
                        SELECT   *
                        FROM     act_payable
                        WHERE    act_date < $kei_date AND parts_no = '$parts_no'
                        ORDER BY act_date DESC LIMIT 3
                     ";
        }
    }
    $res  = array();
    $rows = getResultWithField2( $query, $field, $res );

    if( $rows <= 0 ) {
        if( $flag == "after" ) {
            $query = "
                        SELECT   *
                        FROM     act_payable
                        WHERE    act_date >= $kei_date AND parts_no = '$parts_no'
                        ORDER BY act_date ASC LIMIT 3
                     ";
        } else {
            $query = "
                        SELECT   *
                        FROM     act_payable
                        WHERE    act_date < $kei_date AND parts_no = '$parts_no'
                        ORDER BY act_date DESC LIMIT 3
                     ";
        }
        $res  = array();
        $rows = getResultWithField2( $query, $field, $res );
    }

    if( $rows > 0 && ctype_space($res[0][10]) ) {
        $res[0][10] = '--';
        $res[0][12] = 0;
        return 0;
    }

    return $rows;
}

// 単価経歴の照会より単価を取得する
// （$sei_noが空なら買掛実績の照会のデータがないこともチェック）
function GetPartsCost( &$parts_no, &$sei_no, $kei_date, &$res )
{
    $query = $field = array();

    if( empty($sei_no) ) {
        // 買掛実績の照会：部品番号の買掛データがあるかチェック
        $query = "
                     SELECT   *
                     FROM     act_payable
                     WHERE    parts_no = '$parts_no'
                     ORDER BY regdate DESC LIMIT 1
                 ";
        $res  = array();
        if( getResultWithField2($query, $field, $res) > 0 ) {
            return 0; // 買掛実績がある場合は、登録されるまで待つ必要があるためエラーにする。
        }
    }

    $parts_org = $parts_no;
    // 枝番をさかのぼって単価が登録されているところを探す。
    for( ; ; ) {
        // 単価経歴の照会：部品番号と計上日、発注単価より登録番号・ロット番号取得
        $query = "
                    SELECT   *
                    FROM     parts_cost_history
                    WHERE    parts_no = '$parts_no' AND vendor != 88888 AND as_regdate < $kei_date
                    ORDER BY reg_no DESC, lot_no ASC, pro_no ASC LIMIT 5
                 ";
        $res   = array();
        if( getResultWithField2($query, $field, $res) > 0 ) {
            break;
        }

        $parts_no2 = GetOldNo( $parts_no );
        if( $parts_no2 != $parts_no ) {
            $parts_no = $parts_no2;
            continue;
        }

        $parts_no = $parts_org;
        // 計上日以後にデータがないかも確認してみる
        $query = "
                    SELECT   *
                    FROM     parts_cost_history
                    WHERE    parts_no = '$parts_no' AND vendor != 88888 AND as_regdate >= $kei_date
                    ORDER BY reg_no ASC, lot_no ASC, pro_no ASC LIMIT 5
                 ";
        $res   = array();
        if( getResultWithField2($query, $field, $res) > 0 ) {
            break;
        }

        return 0;
    }

    if( $sei_no == "cost_only" ) {
        $sei_no = "";
        $query = "
                     SELECT   *
                     FROM     act_payable
                     WHERE    parts_no = '$parts_no' AND act_date < $kei_date
                     ORDER BY regdate DESC LIMIT 1
                 ";
        $res2  = array();
        if( getResultWithField2($query, $field, $res2) > 0 ) {
            $sei_no = $res2[0][17];
        }
    }

    return 1;
}

// 部品の単価変更番号(登録No) 取得
function GetTanNo( $parts_no, $sei_no )
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

// 部品の登録No取得（単価変更番号をキーにする）
function GetRegNo( $parts_no, $tan_no, &$res )
{
    $query = $field = $res = array();

    $query = "
                SELECT *
                FROM     parts_cost_history
                WHERE    parts_no = '$parts_no' AND vendor != 88888 AND reg_no = $tan_no
                ORDER BY lot_no ASC, pro_no ASC LIMIT 10
             ";
    $res   = array();
    if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
        return 0;
    }

    return $rows;
}

// 単価経歴の照会：対象のレコードを取得
function GetPartsCostHistory( $pro_con, $parts_no, $vendor, $kei_date, $sei_no, $price, &$res )
{
    $query = $field = $res = array();
    $rows = $reg_no = $total = 0;

    $price = sprintf( "%0.02f", $price );

    // 単価経歴の照会：最初に、登録Noでチェックする。(※無償のみ)
    if( !empty($sei_no) ) {
        $tan_no = GetTanNo( $parts_no, $sei_no );
        if( !empty($tan_no) ) {
            $rows = GetRegNo( $parts_no, $tan_no, $res );
            if( $rows > 0 && $pro_con != '2' ) {
                for( $r = 0; $r < $rows; $r++ ) {
                    // 発注単価と同じ単価があるまで繰り返す
                    $res[$r][11] = sprintf( "%0.02f", $res[$r][11] );
                    if( $price == $res[$r][11] ) {
                        break;
                    }
                }
                if( $r == $rows ) {
                    $rows = 0;
                }
            }
        }
    }

    // 単価経歴の照会：部品番号と計上日、発注単価より登録番号・ロット番号取得
    if( $pro_con != '2' ) {
        // 無償、継続
        if( $rows <= 0 ) {
            $query = "
                        SELECT   *
                        FROM     parts_cost_history
                        WHERE    parts_no = '$parts_no' AND vendor != 88888 AND vendor = '$vendor'
                             AND as_regdate <= $kei_date AND lot_cost = $price
                        ORDER BY reg_no DESC, lot_no ASC, pro_no ASC LIMIT 10
                     ";
            $res   = array();
            $rows = getResultWithField2( $query, $field, $res );
        }

        // 複数ロット番号があるとき
        for( $r = 0; $r < $rows; $r++ ) {
            // 発注単価と同じ単価があるまで繰り返す
            $res[$r][11] = sprintf( "%0.02f", $res[$r][11] );
            if( $price == $res[$r][11] ) {
                $reg_no = $res[$r][1]; // 登録番号
                $lot_no = $res[$r][8]; // ロット番号
                break;
            }
        }

        if( $r == $rows ) {
            if( $pro_con != '1' && $vendor != 91111 ) {
                if( COMPLETE ) {
                    $_SESSION['s_sysmsg'] .= "$parts_no : 単価経歴照会(無償)失敗！AS/400端末で要確認。";
                }
                return 0; // 複数工程ある為、NGと判断
            }
            return -1; // 発注単価までは取得できているのでOKと判断
        }
    } else {
        // 有償
        if( $rows <= 0 ) {
            $query = "
                        SELECT   *
                        FROM     parts_cost_history
                        WHERE    parts_no = '$parts_no' AND vendor != 88888
                             AND as_regdate <= $kei_date
                        ORDER BY reg_no DESC, lot_no ASC, pro_no ASC LIMIT 30
                     ";
            $res   = array();
            $rows = getResultWithField2( $query, $field, $res );
        }

        // 発注単価とトータル単価が同じになる登録番号を探す
        for( $r = 0; $r < $rows; $r++ ) {
            $total = $total + $res[$r][11];
            if( ($r+1==$rows) || ($res[$r][1] != $res[$r+1][1]) || ($res[$r][8] != $res[$r+1][8]) ) {
                $total = sprintf( "%0.02f", $total );
                if( $total == $price ) {
                    $reg_no = $res[$r][1]; // 登録番号
                    $lot_no = $res[$r][8]; // ロット番号
                    break;
                }
                $total = 0.00;
            }
        }

        if( $r == $rows ) {
            if( COMPLETE ) {
                $_SESSION['s_sysmsg'] .= "$parts_no : 単価経歴照会(1-1)失敗！ ※AS/400端末で要確認。";
                return 0;
            }
        }
    }

    // 単価経歴の照会：部品番号と計上日、登録番号より工程、工程名、単価、内外作取得
    $query = "
                SELECT *
                FROM     parts_cost_history
                WHERE    parts_no = '$parts_no' AND vendor != 88888
                     AND reg_no = $reg_no AND lot_no = $lot_no
                ORDER BY reg_no DESC, pro_no ASC, lot_no ASC LIMIT 10
             ";
    $res   = array();
    if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
        $_SESSION['s_sysmsg'] .= "$parts_no : 単価経歴の照会(2)失敗！";
        return 0;
    }

    return $rows;
}

// 対象部品の工程、工程名、単価、内外作データを取得
function GetDetailData( $parts_no2, $plan_no2, &$d_data, &$sei_no )
{
    $query2 = $field2 = $res2 = array();
    $rows2 = $r = $fstock_mv = 0;

    $rows2 = GetPartsStokHistory( $parts_no2, $plan_no2, false, $res2, $r, $fstock_mv );
    if( $rows2 <= 0 ) {
        return 0;
    } else if( $res2[$r][10] == "PCｻﾞｲｺ ﾌﾞﾝ" ){
        $_SESSION['s_sysmsg'] .= "$parts_no2 : {$res2[$r][10]}の為、別紙 Excel 参照!!";
        return 0;
    }

    $den_kubun = $res2[$r][5]; // 伝票区分
    $ent_date = $res2[$r][11]; // データの日

    if( IsProvide($parts_no2, $ent_date) ) {
        $d_data[0][5] = "支給品";
    }

    /* 特殊：代替品使用 + 共取り ------------------------------------------> */
    $tomodori = false;
    if( $den_kubun == '6' ) {
        if( !empty($d_data[0][5]) ) {
            return 1;
        }
        $ent_date = date( 'Y/m/d', strtotime($ent_date) );
        $d_data[0][6] = "振替";
        if( IsAlternative($parts_no2, $tomodori) ) {
            $den_no = $res2[$r][6];
            $len = strlen( $den_no );
            $work = $den_no - 1;
            $den_no = str_pad( $work, $len, 0, STR_PAD_LEFT );
            $rows2 = GetPartsStokHistory( $parts_no2, false, $den_no, $res2, $r, $fstock_mv );
            if( $rows2 <= 0 ) {
                $work = $den_no + 2;
                $den_no = str_pad( $work, $len, 0, STR_PAD_LEFT );
                $rows2 = GetPartsStokHistory( $parts_no2, false, $den_no, $res2, $r, $fstock_mv );
                if( $rows2 <= 0 ) {
                    return 0;
                }
            }
            $den_kubun = $res2[$r][5]; // 伝票区分
        } else {
            if( IsNoSubstitute($parts_no2) ) {
                ; // メッセージ出さない。
            } else {
                $_SESSION['s_sysmsg'] .= "------------------------------------------------------------------------------- ";
                $_SESSION['s_sysmsg'] .= " $parts_no2 : $ent_date 振替品の為、移動票などで確認して下さい。";
                $_SESSION['s_sysmsg'] .= "移動数 = {$res2[$r][2]} ：伝票番号 = {$res2[$r][6]} : 想定部品番号 = ※未確認。";
                $_SESSION['s_sysmsg'] .= "※部品番号を担当者へ連絡して、プログラム上に追加してもらいましょう。";
                $_SESSION['s_sysmsg'] .= " --------------------------------------------------------------------------------";
            }
        }
    }
    /* <-------------------------------------------------------------------- */

    if( $den_kubun == '6' ) {
        $genpin = 0;            // 移動数 0 にする。
    } else {
        $genpin = $res2[$r][2]; // 移動数
    }
    $uke_no = $res2[$r][6];     // 受付番号
    $plan_no = $res2[$r][7];    // 摘要
    $kei_date = $res2[$r][12];  // 計上日
/**
if( $parts_no2 == 'CB24772-0' ) {
$_SESSION['s_sysmsg'] .= "$parts_no2 : kei_date=$kei_date : uke_no=$uke_no : plan_no = $plan_no";
}
/**/
    if( $den_kubun != '6' ) {
        $rows2 = GetActPayable( $parts_no2, $kei_date, "after", $uke_no, $genpin, $res2 );
    } else {
        $rows2 = GetActPayable( $parts_no2, $kei_date, "before", $uke_no, $genpin, $res2 );
    }

    if( $rows2 > 0 && substr($plan_no, 0 ,1) != '@' ) {
        $d_data[0][1] = $res2[0][10]; // 工程記号
        $d_data[0][2] = $res2[0][12]; // 発注単価

        $vendor = $res2[0][6];   // 発注先番号
        $pro_con = $res2[0][11]; // 材料条件
        $price = $res2[0][12];   // 発注単価
        $sei_no = $res2[0][17];  // 製造番号
    } else {
        if( substr($plan_no, 0 ,1) == '@' ) {
            // 摘要の＠マークを取り除き、子部品へ製造番号を渡す
            $sei_no = substr($plan_no, 1);
        }

        if( $den_kubun == '6' ) {
            $sei_no = "cost_only";
            if( GetPartsCost($parts_no2, $sei_no, $kei_date, $res2) <= 0 ) {
                if( $sei_no == "cost_only" ) {
                    $sei_no = "";
                }
                return 0;
            }
        } else {
            if( GetPartsCost($parts_no2, $sei_no, $kei_date, $res2) <= 0 ) {
                if( COMPLETE && $den_kubun != '6' ) {
                    $_SESSION['s_sysmsg'] .= "$parts_no2 : 単価経歴が見つかりません！※手動で検索して下さい。";
                }
                return 0;
            }
        }

        $d_data[0][1] = $res2[0][2];  // 工程記号
        $d_data[0][2] = $res2[0][11]; // 単価

        $vendor = $res2[0][3];    // 発注先番号
        $pro_con = $res2[0][5];   // 材料条件
        $price = $res2[0][11];    // ロット単価
        $kei_date = $res2[0][12]; // AS400登録日
    }

/**
if( $parts_no2 == 'CB63941-0' )
$_SESSION['s_sysmsg'] .= "$parts_no2 : price=$price : {$res2[0][11]} : kei_date = $kei_date : pro_con=$pro_con";
/**/

    $rows2 = GetPartsCostHistory( $pro_con, $parts_no2, $vendor, $kei_date, $sei_no, $price, $res2 );

    if( $rows2 < 0 ) {
        return 1; // 発注単価までは取得できているのでOKと判断
    } else if( $rows2 == 0 ) {
        IniDetailData( $d_data ); // 部品情報が入っている場合があるので初期化しておく
        return 0;
    }

    // 表示用データセット、[0]工程[1]工程名[2]単価[3]内外作
    for( $r=0; $r < $rows2; $r++ ) {
        $d_data[$r][0] = $res2[$r][4];  // 工程
        $d_data[$r][1] = $res2[$r][2];  // 工程名
        $d_data[$r][2] = $res2[$r][11]; // 単価

        if( $res2[$r][3] == "01111" || $res2[$r][3] == "00222" ) {
            $d_data[$r][3] = "内作"; // ﾄﾁｷﾞﾆｯﾄｳｺｳｷ、ｾｲｿﾞｳｶ ﾄｸﾁｭｳ
        } else {
            $d_data[$r][3] = "外作"; // それ以外
        }
    }

    /* 特殊：代替品使用 + 共取り ------------------------------------------> */
    if( $tomodori ) {
        $d_data[0][2] = $d_data[0][2] / 2 ; // 共取りの為、単価を半分にする
    }
    /* <-------------------------------------------------------------------- */

    return $r;
}

// 総材料費の登録データがあるかチェック
function IsMaterial( $plan_no, $assy_no )
{
    $query = $res_chk = array();

    $query = "
                SELECT   *
                FROM     material_cost_history
                WHERE    plan_no = '$plan_no' AND assy_no = '$assy_no'
             ";
    if( getResult2($query, $res_chk) <= 0 ) {
        return false;
    }

    return true;
}

// 社員名取得
function GetName( $uid )
{
    $query = $res_chk = array();

    $query = "
                SELECT   name
                FROM     user_detailes 
                WHERE    uid = '$uid'
             ";
    if( getResult2($query, $res_chk) <= 0 ) {
        return $uid;
    }

    return $res_chk[0][0];
}

// 登録日を取得
function GteRegDate( $plan_no, $assy_no )
{
    $query = $res = array();

    $query = "
                SELECT TO_CHAR(last_date, 'YYYY/MM/DD')
                FROM material_cost_header
                WHERE plan_no = '$plan_no' AND assy_no= '$assy_no'
             ";
    if( getResult2($query, $res) <= 0 ) {
        return "----/--/--";
    }

    return $res[0][0];
}

// 登録者名を取得
function GteRegUser( $plan_no, $assy_no )
{
    $query = $res = array();

    $query = "
                SELECT last_user
                FROM material_cost_header
                WHERE plan_no = '$plan_no' AND assy_no= '$assy_no'
             ";
    if( getResult2($query, $res) <= 0 ) {
        return "---- ----";
    }

    return GetName($res[0][0]);
}





$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!--    ファイル指定の場合
<script type='text/javascript' language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script type='text/javascript' language='JavaScript'>
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

function checkDelete(url, delParts, sumQT)
{
    if (sumQT == 0) {
        if (confirm(delParts + "の部品を削除します。\n\n宜しいですか？")) {
            parent.location.replace(url<?php echo "+\"{$param}\""?>);
        }
    } else {
        if (confirm(delParts + "は既に出庫済みです。それでも削除しますか？\n\n削除した場合は元に戻せません！\n\n宜しいですか？")) {
            parent.location.replace(url<?php echo "+\"{$param}\""?>);
        }
    }
}

/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // <a name='mark' でフォーカスが移るため0.1秒ずらしてフォーカスをセットする。
    // フレームを切っていないためフォーカスを変えるとmarkへいかないためコメント
    // setTimeout("document.mhForm.backwardStack.focus()", 100);  //こちらに変更しNN対応
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
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
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family: monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-wieght:        bold;
    font-family:        monospace;
}
a:hover {
    background-color:   gold;
    color:              darkblue;
}
a {
    font-size:          11pt;
    font-weight:        bold;
    color:              blue;
    text-decoration:    none;
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
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <?php
            $ok_color = "#d6d3ce"; // 照会成功時のカラーコード
            $ng_color = "#F5A9F2"; // 照会失敗時のカラーコード
            $cc_color = "#e6e6e6"; // CC部品表示時のカラーコード
            $color = $entry_data = array();
            $entry_ok = true; // 照会結果

            /* 下部に、内作、外作、合計金額を表示する為 -------------------> */
            $int_kin = 0;        // 内作材料費
            $ext_kin = 0;        // 外作材料費
            $assy_int_price = 0; // 組立費
            $m_time = 0;         // 手作業工数
            $m_rate = 0;         // 手作業賃率
            $a_time = 0;         // 自動機工数
            $a_rate = 0;         // 自動機賃率
            $g_time = 0;         // 外注工数
            $g_rate = 0;         // 外注賃率
            $last_date = "データ無し";
            $last_user = "------";
            $query = "
                        SELECT m_time, m_rate, a_time, a_rate, g_time, g_rate,
                               assy_time, assy_rate, TO_CHAR(last_date, 'YYYY/MM/DD'), last_user
                        FROM material_cost_header
                        WHERE assy_no='$assy_no' AND LENGTH(last_user) = 6
                        ORDER BY last_date DESC LIMIT 1
                     ";
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
                $last_date = $res_time[0][8];
                $last_user = $res_time[0][9];
            }
            /* <------------------------------------------------------------ */

            for( $r=0, $e=0; $r<$rows_view; $r++ ) {
                if( $row_no == $r ) {
                    if(  $res_view2[$r][12] != "CC部品" ) { // 引当数のフィールドをチェックして引当部品なら
                        echo "<tr style='background-color:#ffffc6;' onDblClick='checkDelete(\"", $menu->out_self(), '?delParts=', urlencode($res_view2[$r][1]), "\", \"{$res_view2[$r][1]}\", \"{$res_view2[$r][7]}\")'>\n";
                    } else {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    }
                    echo "    <td class='winbox' width=' 4%' nowrap style='font-size:10pt; font-weight:bold; font-family:monospace;' align='right'><a name='mark'>\n";
                    echo "            ", ($r + $offset + 1), "\n";
                    echo "    </a></td>    <!-- 行ナンバーの表示 -->\n";
                } else {
                    if(  $res_view2[$r][12] != "CC部品" ) { // 引当数のフィールドをチェックして引当部品なら
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\" onDblClick='checkDelete(\"", $menu->out_self(), '?delParts=', urlencode($res_view2[$r][1]), "\", \"{$res_view2[$r][1]}\", \"{$res_view2[$r][7]}\")'>\n";
                    } else {
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    }
                    echo "    <td class='winbox' width=' 4%' nowrap style='font-size:10pt; font-weight:bold; font-family:monospace;' align='right'>\n";
                    echo "            ", ($r + $offset + 1), "\n";
                    echo "    </td>    <!-- 行ナンバーの表示 -->\n";
                }

                if( $res_view2[$r][12] == "CC部品" || $res_view2[$r][12] == "支給品" ) {
                    $color = $cc_color; // CC部品時のカラーコードセット
                } else if( $res_view2[$r][7] != "--" && !ctype_space($res_view2[$r][7]) ) {
                    $color = $ok_color; // 照会成功時のカラーコードセット
                } else if( $res_view2[$r][7] == "--" || ctype_space($res_view2[$r][7]) ) {
                    $color = $ng_color; // 照会失敗時のカラーコードセット（ピンク）
                }

                if( $r != 0 && $res_view2[$r][1] == $res_view2[$r-1][1] && $res_view2[$r][0] == $res_view2[$r-1][0]) {
                    $part_no_view = "　";
                    $part_na_view = "　";
                } else {
                    $part_no_view = $res_view2[$r][1];
                    $part_na_view = $res_view2[$r][2];
                }

                /* 引当数と出庫数が一致しない -----------------------------> */
                if( $res_view2[$r][4] != $res_view2[$r][5] ) {
                    if( $res_view2[$r][5] > $res_view2[$r][4]/2 ) {
                        $color = "#A9F5F2"; // （水色）
                    } else {
                        $color = $ng_color; // 照会失敗時のカラーコードセット（ピンク）
                    }
                }
                /* <-------------------------------------------------------- */

                /* 振替部品 -----------------------------------------------> */
                if( !empty($res_view2[$r][13]) && $res_view2[$r][13] == "振替" ) {
                    $color = "#00FF00"; // （緑色）
                }
                /* <-------------------------------------------------------- */

                /* NG除外部品(CC部品で、NGとなった行) ---------------------> */
                if( $color == $ng_color && $res_view2[$r][12] == "CC部品" ) {
                    $color = $cc_color; // CC部品時のカラーコードセット
                }
                /* <-------------------------------------------------------- */

                /* 下部に、内作、外作、合計金額を表示する為 ---------------> */
                if( $res_view2[$r][10] == "内作" ) {
                    $int_kin += $res_view2[$r][9];
                } else {
                    $ext_kin += $res_view2[$r][9];
                }
                /* <-------------------------------------------------------- */

                if( $color == $ng_color ) {
                    $entry_ok = false; // 照会が正常にできていない場合
                }

                /* 特殊：CB09209- CB09212- 専用 （登録できるよう編集）-----> */
                $sp_conver = false;
                if( strncmp($assy_no, 'CB09209-', 8)==0 || strncmp($assy_no, 'CB09212-', 8)==0 ) {
                    $sp_conver = true;
                }
                /* <-------------------------------------------------------- */

                for( $i=0; $i<$num_view; $i++ ) { // フィールド数分繰返し
                    /* 登録時に、必要な材料データをまとめる ---------------> */
                    // entry_data[0]部品番号[1]工程[2]工程名[3]親番号[4]工程単価[5]使用数[6]内外作
                    if( $entry_ok || (!$entry_ok && COMPLETE) ) {
                        switch ($i) {
                            case  1: // 部品番号
                                $entry_data[$r][0] = $res_view2[$r][$i];
                                break;
                            case  6: // 工程
                                $entry_data[$r][1] = $res_view2[$r][$i];
                                break;
                            case  7: // 工程名
                                // 先頭に空白があると登録へコピー時にエラーになる為、trim()追加
                                $entry_data[$r][2] = trim($res_view2[$r][$i]);
                                break;
                            case 11: // 親番号
                                if( strcmp($res_view2[$r][$i], "---------") == 0 ) {
                                    $entry_data[$r][3] = '';
                                } else {
                                    $entry_data[$r][3] = $res_view2[$r][$i];
                                }
                                break;
                            case  8: // 工程単価
                                $entry_data[$r][4] = $res_view2[$r][$i];
                                break;
                            case  3: // 使用数
                                $entry_data[$r][5] = $res_view2[$r][$i];
                                break;
                            case 10: // 内外作
                                if( strcmp($res_view2[$r][$i], "外作") == 0 ) {
                                    $entry_data[$r][6] = '0';
                                } else {
                                    $entry_data[$r][6] = '1';
                                }
                                break;
                            default: // その他データ必要なし
                                break;
                        }
                    }
                    /* <---------------------------------------------------- */

                    if( $color == $ok_color ) {
                        switch ($i) {
                            case  0: // レベル
                                echo "<td class='winbox' width=' 7%' nowrap align='left' style='font-size:10pt; font-weight:bold; font-family:monospace;'>" . $res_view2[$r][$i] . "</td>\n";
                                break;
                            case  1: // 部品番号
                                echo "<td class='winbox' width='10%' nowrap align='center' style='font-size:9pt; font-family:monospace;'><a href='", $menu->out_action('在庫経歴'), "?parts_no=", urlencode($part_no_view), "&plan_no=", urlencode($plan_no), "&material=1&row={$r}' target='_parent' style='text-decoration:none;'>{$part_no_view}</a></td>\n";
                                break;
                            case  2: // 部品名
                                echo "<td class='winbox' width='25%' nowrap align='left' style='font-size:9pt; font-family:monospace;'>{$part_na_view}</td>\n";
                                break;
                            case  3: // 使用数
                                echo "<td class='winbox' width=' 6%' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 4) . "</td>\n";
                                break;
                            case  4: // 引当数
                                echo "<td class='winbox' width=' 5%' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 0) . "</td>\n";
                                break;
                            case  5: // 出庫数
                                echo "<td class='winbox' width=' 5%' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 0) . "</td>\n";
                                break;
                            case  6: // 工程
                                echo "<td class='winbox' width=' 3%' nowrap align='center' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 0) . "</td>\n";
                                break;
                            case  7: // 工程名
                                echo "<td class='winbox' width=' 4%' nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                                break;
                            case  8: // 工程単価
                                echo "<td class='winbox' width=' 7%' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 2) . "</td>\n";
                                break;
                            case  9: // 工程金額
                                echo "<td class='winbox' width=' 7%' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 2) . "</td>\n";
                                break;
                            case 10: // 内外作
                                echo "<td class='winbox' width=' 5%' nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                                break;
                            case 11: // 親番号
                                echo "<td class='winbox' width=' 7%' nowrap align='right' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                                break;
                            default: // 12 CC部品
                                echo "<td class='winbox' width=' 5%' nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                        }
                    } else {
                        switch ($i) {
                            case  0: // レベル
                                echo "<td class='winbox' width=' 7%' nowrap align='left' style='font-size:10pt; font-weight:bold; font-family:monospace;'>" . $res_view2[$r][$i] . "</td>\n";
                                break;
                            case  1: // 部品番号
                                echo "<td class='winbox' width=' 10%' bgcolor=$color nowrap align='center' style='font-size:9pt; font-family:monospace;'><a href='", $menu->out_action('在庫経歴'), "?parts_no=", urlencode($part_no_view), "&plan_no=", urlencode($plan_no), "&material=1&row={$r}' target='_parent' style='text-decoration:none;'>{$part_no_view}</a></td>\n";
                                break;
                            case  2: // 部品名
                                echo "<td class='winbox' width='25%' bgcolor=$color nowrap align='left' style='font-size:9pt; font-family:monospace;'>{$part_na_view}</td>\n";
                                break;
                            case  3: // 使用数
                                echo "<td class='winbox' width=' 6%' bgcolor=$color nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 4) . "</td>\n";
                                break;
                            case  4: // 引当数
                                echo "<td class='winbox' width=' 5%' bgcolor=$color nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 0) . "</td>\n";
                                break;
                            case  5: // 出庫数
                                echo "<td class='winbox' width=' 5%' bgcolor=$color nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 0) . "</td>\n";
                                break;
                            case  6: // 工程
                                echo "<td class='winbox' width=' 3%' bgcolor=$color nowrap align='center' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 0) . "</td>\n";
                                break;
                            case  7: // 工程名
                                echo "<td class='winbox' width=' 4%' bgcolor=$color nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                                break;
                            case  8: // 工程単価
                                echo "<td class='winbox' width=' 7%' bgcolor=$color nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 2) . "</td>\n";
                                break;
                            case  9: // 工程金額
                                echo "<td class='winbox' width=' 7%' bgcolor=$color nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 2) . "</td>\n";
                                break;
                            case 10: // 内外作
                                echo "<td class='winbox' width=' 5%' bgcolor=$color nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                                break;
                            case 11: // 親番号
                                echo "<td class='winbox' width=' 7%' bgcolor=$color nowrap align='right' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                                break;
                            default: // 12 CC部品
                                echo "<td class='winbox' width=' 5%' bgcolor=$color nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                        }
                    }
                }
                echo "</tr>\n";

                /* 特殊：CB09209- CB09212- 専用 （登録できるよう編集）-----> */
                if( $entry_ok || (!$entry_ok && COMPLETE) ) {
                    if( $sp_conver ) {
                        $add_on = false;
                        if( $res_view2[$r][0] != ".1" && $r+1<$rows_view ) { // レベル.1以外かつ、最終行ではない

                            for( $w=$r+1; $w<$rows_view; $w++ ) {
                                if( $res_view2[$r][0] == $res_view2[$w][0] ) {
                                    continue; // レベルが同じ間は次の行へ
                                }
                                if( $res_view2[$r][1] == $res_view2[$w][11]) {
                                    $add_on = true; // 登録行が親番号なら追加フラグON
                                }
                                break;
                            }

                            for( $x=$r-strlen($res_view2[$r][6]); $x>=0; $x-- ) { // 登録行の前を検索
                                if( $res_view2[$r][0] != $res_view2[$x][0]     // レベル
                                 || $res_view2[$r][1] != $res_view2[$x][1]     // 部品番号
                                 || $res_view2[$r][6] != $res_view2[$x][6] ) { // 工程
                                    continue;
                                }
                                // レベル、部品番号、工程が一致した行を発見した
                                if( $add_on ) {
                                    $add_on = false; // フラグリセット
                                    for( $w=$x+1; $w<$rows_view; $w++ ) {
                                        if( $res_view2[$r][0] == $res_view2[$w][0] ) {
                                            continue; // レベルが同じ間は次の行へ
                                        }
                                        if( $res_view2[$r][1] == $res_view2[$w][11] ) {
                                            $add_on = true; // 登録行が親番号なら追加フラグON
                                        }
                                        break;
                                    }
                                } else {
                                    if( $res_view2[$r][0] != '..2' ) {
                                        $add_on = true; // レベル...3以降で子部品がないものは追加
                                    }
                                }
                                break;
                            }
                            if( $x == -1 ) $add_on = false;
                        }
    
                        if( $add_on ) {
                            $entry_data[$x][5] += $res_view2[$r][3]; // 同一子部品の為、数量を足す
                        } else {
                            $entry_data[$e][0] = $res_view2[$r][1]; // 部品番号
                            $entry_data[$e][1] = $res_view2[$r][6]; // 工程
                            $entry_data[$e][2] = $res_view2[$r][7]; // 工程名
                            if( strcmp($res_view2[$r][11], "---------") == 0 ) {
                                $entry_data[$e][3] = '';
                            } else {
                                $entry_data[$e][3] = $res_view2[$r][11]; // 親番号
                            }
                            $entry_data[$e][4] = $res_view2[$r][8]; // 工程単価
                            $entry_data[$e][5] = $res_view2[$r][3]; // 使用数
                            if( strcmp($res_view2[$r][10], "外作") == 0 ) {
                                $entry_data[$e][6] = '0'; // 外作
                            } else {
                                $entry_data[$e][6] = '1'; // 内作
                            }
                            $e++;
                            $entry_data[$e] = array();
                        }
                    }
                }
                /* <-------------------------------------------------------- */
            }
            ?>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <?php
        $inquiries_only = $_SESSION['inquiries_only'];
        if( !$inquiries_only && $sp_conver ) {
            echo "<p class='pt10b' rel='nofollow'> 当製品の【総材料費の登録（工程明細）】画面は、専用の変換がかかっていますが合計材料費に問題はありません!!</p>";
        }
        ?>

        <!------- 下部に、内作、外作、合計金額を表示する為 ------>
        <table width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox' align='right'>
                    <div class='pt10'>
                    内作材料費：<?php echo number_format($int_kin, 2) ."\n" ?>
                    外作材料費：<?php echo number_format($ext_kin, 2) ."\n" ?>
                    <?php $total = number_format($int_kin + $ext_kin, 2); ?>
                    合計材料費：<?php echo $total ."\n" ?>
                    <?php if( !$inquiries_only ) { // 照会じゃない時 ?>
                        <input type="button" class='pt9' value="コピー" onMouseout="document.body.style.cursor='auto';" title='合計材料費をコピーします。' onclick='if(window.clipboardData){window.clipboardData.setData("text","<?php echo $total ?>");}else if(navigator.clipboard){navigator.clipboard.writeText("<?php echo $total ?>");}else{alert("このブラウザでは、コピー機能使用不可！\n\n合計材料費を自分でコピーして下さい。");}'>
                    <?php } ?>
                    <?php
                    $reg_date = GteRegDate( $plan_no, $assy_no );
                    if( $reg_date == "----/--/--" && $inquiries_only && !COMPREGI && ($_SESSION['User_ID'] == '300667' || $_SESSION['User_ID'] == '970352') ) {
                        echo "<a class='pt10' href='", $menu->out_self(), "?plan_no=", urlencode($plan_no), "&comp_regi=true", "&material=1\")' target='application' style='text-decoration:none;'>.</a>";
                    } else{
                        echo "<br>";
                    }

                    if( $inquiries_only ) { // 照会のみ
                        unset( $_SESSION['entry_data'] );
                        unset( $_SESSION['assy_reg_data'] );

                        if( COMPLETE ) {    // 完成済
//                            echo "<br>";
                            $compdate = date( 'Y/m/d', strtotime(COMPDATE) );
                            $reg_date = GteRegDate( $plan_no, $assy_no );
                            if( $reg_date == "----/--/--" ) { // 未登録
//                                echo "(完成日：$compdate / 未登録)";
                            } else {
                                echo "<br>";
//                                if( $_SESSION['User_ID'] == '300667' ) {
                                    $user = trim(GteRegUser($plan_no, $assy_no));
                                    echo "(完成日：$compdate / 登録日：$reg_date [$user])";
//                                } else {
//                                    echo "(完成日：$compdate / 登録日：$reg_date)";
//                                }
                            }
//                        } else {
//                            echo "(未完成)";
                        }
                    } else { // 登録エリアも表示
                    ?>
                        ※あくまで参考に、以下は前回(<?php echo $last_date?>)登録時の工数と賃率データ
                        <br>
                        <table class='pt10' border="1" cellspacing="0">
                            <tr>
                                <td>手作業工数</td>
                                <td>手作業賃率</td>
                                <td>自動機工数</td>
                                <td>自動機賃率</td>
                                <td>外注工数</td>
                                <td>外注賃率</td>
                            </tr>
                            <tr align='right'>
                                <td><?php echo number_format($m_time, 3) ."\n" ?></td>
                                <td><?php echo number_format($m_rate, 2) ."\n" ?></td>
                                <td><?php echo number_format($a_time, 3) ."\n" ?></td>
                                <!-- 自動機賃率がある場合、一時的にACSより調べること -->
                                <?php if( $a_rate != 0 ) { ?>
                                <td style='background-color:yellow; color:red;' >
                                <?php } else { ?>
                                <td>
                                <?php } ?>
                                <!----------------------------------------------------->
                                    <?php echo number_format($a_rate, 2) ."\n" ?></td>
                                <td><?php echo number_format($g_time, 3) ."\n" ?></td>
                                <td><?php echo number_format($g_rate, 2) ."\n" ?></td>
                            </tr>
                        </table>
                        登録者：<?php echo GetName($last_user) ."\n" ?>
                        　組立費：<?php echo number_format($assy_int_price, 2) ."\n" ?>
                        　総材料費：<?php echo number_format($int_kin + $ext_kin + $assy_int_price, 2) ."\n" ?>
                        <br>
                        ※注）総材料費の登録を完了するには、最新の工数と賃率を確認して下さい。
                        <br>
                        <?php
                        echo "<td class='winbox' nowrap align='center'>";
                        if( IsMaterial($plan_no, $assy_no) == true ) { // 登録画面に、登録データあり
                            unset( $_SESSION['entry_data'] );
                            unset( $_SESSION['assy_reg_data'] );
                            if( COMPDATE < date('Ymd', strtotime("today -30 day")) ) {
                                $reg_date = GteRegDate( $plan_no, $assy_no );
                                echo "<p class='pt10' rel='nofollow'> $reg_date<br>総材料費の<br>登録は完了<br>しています</p>";
                            } else {
                                echo "<p class='pt10' rel='nofollow'> 総材料費の<br>登録画面に<br>データあり<br>コピー不可</p>";
                                if( COMPLETE ) {
                                    echo "<a class='pt10' href='", $menu->out_action('総材料費登録'), "?plan_no=", urlencode($plan_no), "&assy_no=", urlencode($assy_no), "&data_copy=on ' target='_parent' style='text-decoration:none;'>登録画面へ移動</a>";
                                }
                            }
                        } else { // 登録画面に、登録データなし
                            // 登録時に、必要な組立費データをまとめる
                            $assy_reg_data[0] = $m_time;
                            $assy_reg_data[1] = $m_rate;
                            $assy_reg_data[2] = $a_time;
                            $assy_reg_data[3] = $a_rate;
                            $assy_reg_data[4] = $g_time;
                            $assy_reg_data[5] = $g_rate;
                            if( strcmp($last_date, "データ無し") == 0 ) {
                                unset( $_SESSION['assy_reg_data'] );
                            } else {
                                $_SESSION['assy_reg_data'] = $assy_reg_data; // 組立費データをセッションにセット
                            }
                            $_SESSION['entry_data'] = $entry_data;           // 材料データをセッションにセット

                            if( $entry_ok ) { // 照会ＯＫ
                                echo "<p class='pt10b' rel='nofollow'> 照会ＯＫ<br></p>";
                                if( COMPLETE ) { // 完成済
                                    echo "<a class='pt10' href='", $menu->out_action('総材料費登録'), "?plan_no=", urlencode($plan_no), "&assy_no=", urlencode($assy_no), "&data_copy=on ' target='_parent' style='text-decoration:none;'>登録画面へ<br>コピー</a>";
                                } else {
                                    unset( $_SESSION['entry_data'] );
                                    unset( $_SESSION['assy_reg_data'] );
                                }
                            } else { // 照会ＮＧ
                                echo "<p class='pt10b' rel='nofollow'> 照会ＮＧ<br></p>";
                                if( COMPLETE ) { // 完成済
                                    echo "<a class='pt10' href='", $menu->out_action('総材料費登録'), "?plan_no=", urlencode($plan_no), "&assy_no=", urlencode($assy_no), "&data_copy=on ' target='_parent' style='text-decoration:none;'>ＮＧを含め<br>登録画面へ<br>コピー</a>";
                                } else {
                                    unset( $_SESSION['entry_data'] );
                                    unset( $_SESSION['assy_reg_data'] );
                                }
                            }
                        }
                        echo "</td>";
                    } // $inquiries_only
                    ?>
                    </div>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd --------------------->
        <!------------------------------------------------------------------>

    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
