<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 ＣＬ損益計算書 計算結果 保存                                //
// Copyright (C) 2003-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/02/14 Created   profit_loss_pl_act_save.php                         //
// 2003/02/20 期末棚卸高のマイナス登録を止めた照会時にマイナス表示          //
// 2003/02/23 期首棚卸高調整と期末棚卸高調整と仕入高の調整ロジック追加      //
//              sprintf で like 文を使う場合 '%期末'→'%%期末'にする        //
// 2003/03/06 新規の業務委託収入科目がある時にその他が抜けるバグ修正        //
// 2003/03/10 売上高の調整項目追加による調整ロジック追加                    //
// 2004/01/08 売上高比のリテラルの数字を変更予定 (来期から？) 以下の様に    //
// 2004/06/08 第５期から カプラ=77.87% リニア=22.13% へ変更 半期毎に見直す  //
// 2004/11/05 第５期下期から カプラ=76.73% リニア=23.27% へ変更             //
// 2005/05/30 第６期上期から カプラ=80.71% リニア=19.29% へ変更             //
// 2005/11/08 第６期下期から カプラ=80.27% リニア=19.73% へ変更             //
// 2005/11/11 11月の月次以降リニア固有の業務委託収入に対応 $tmp_gyoumu_l    //
// 2005/12/06 C/L配賦終了後、全体の業務委託収入を元に戻すを追加。上記の修正 //
// 2006/05/09 第７期上期から カプラ=78.87% リニア=24.63% へ変更             //
// 2006/11/06 第７期下期から カプラ=81.27% リニア=18.73% へ変更             //
// 2007/11/05 第８期下期から カプラ=82.14% リニア=17.86% へ変更        大谷 //
// 2008/05/01 第９期上期から カプラ=82.42% リニア=17.58% へ変更        大谷 //
// 2008/10/10 第９期下期から カプラ=83.65% リニア=16.35% へ変更        大谷 //
// 2009/07/02 2009年6月分のみ業務委託収入を調整するように変更               //
//            2009年7月よりリニアの業務委託収入の固有値を加味しないよう     //
//            変更                                                     大谷 //
// 2009/07/07 おかしなスペースが入っていたので修正                     大谷 //
// 2009/08/18 試験・修理の売上高登録のロジックを追加                   大谷 //
// 2009/08/21 特注・バイモルの売上高登録を追加                         大谷 //
// 2009/08/26 試験・修理の売上比率を全体売上との比率に変更             大谷 //
// 2009/10/06 200909より商管の売上高がASにカプラとして入力されているので    //
//            カプラ全体から商管を引いてDBに登録する                   大谷 //
// 2009/12/09 サービス割合の登録チェックを行うように変更                    //
//            賃率計算が不要なのでサービス割合を入力しなかったら            //
//            セグメント別の損益が正しく計算されなかったため           大谷 //
// 2009/12/10 コメントの整理                                           大谷 //
// 2010/01/13 サービス割合の登録チェックが逆だったのを修正             大谷 //
// 2010/01/27 売上高の率を自動で半期ごとに計算するように変更                //
//            商管・試修調整入力後にさらに再計算させるようにする       大谷 //
// 2010/01/28 2010/01より前半期売上高比を自動計算するように変更             //
//            全体の割合に試修も追加                                   大谷 //
//            商管の売上高比を追加（全体の割合に加味させるかも）            //
// 2010/04/12 仕入計上処理が未処理のまま実行されてしまったのでチェック      //
//            を追加                                                   大谷 //
// 2010/10/06 １１期上期分の特注（00222）の売上モレを201009に調整      大谷 //
// 2011/06/07 2011/04より試験修理部門に581追加                         大谷 //
// 2011/06/08 500部門の経費が試験修理部門に配布されていたのを               //
//            2011/06より配布しないように変更                          大谷 //
// 2013/01/28 製品名の頭文字がDPEのものを液体ポンプ(バイモル)で集計するよう //
//            に変更                                                   大谷 //
// 2013/01/31 SQL分が２つあったのでもう一つにも追加                    大谷 //
// 2013/12/02 2013年11月月次より、特注の棚卸高取得方法を変更           大谷 //
// 2015/05/11 機工売上高の取得を追加                                   大谷 //
// 2015/06/03 プログラムミスを修正                                     大谷 //
// 2015/06/10 機工の計算を追加                                         大谷 //
// 2015/09/03 業務委託収入が漏れの為０なのでプログラムを一部変更       大谷 //
//            2015/09/03で検索して来月は元に戻す？                          //
// 2016/04/21 製造部(582)の特注への配賦を追加。その他文言調整          大谷 //
// 2017/10/31 機工の買掛に調達部門費配賦率を追加                       大谷 //
// 2017/11/08 4・5・10・11の場合は３月と９月を使用するよう変更         大谷 //
// 2018/06/29 多部門のT部品購入抜出しでCCを除外していた所を削除        大谷 //
// 2018/10/05 暫定版の時に、機工用の調達部門費配賦率が取得できない為        //
//            直近の配賦率を使用するようにSQLを変更                    大谷 //
// 2018/10/17 コメントを修正                                           大谷 //
// 2020/10/14 機工なくなるので配賦関係をコメント化                     大谷 //
// 2020/12/21 機工なくなるので買掛をコメント化                         大谷 //
// 2021/01/20 1月は買掛があるのでSQLを修正して再度                     大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');
require_once ('../tnk_func.php');
access_log();                               // Script Name は自動取得
$_SESSION['site_index'] = 10;               // 月次損益関係=10 最後のメニューは 99 を使用
$_SESSION['site_id']    =  7;               // 下位メニュー無し (0 <=)

// if(!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])){
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
    header("Location: http:" . WEB_HOST . "menu.php");
    exit();
}

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);
///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
$d_start = $yyyymm . '01';   
$d_end   = $yyyymm . '31';
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// 対象前半期
$yyyy = substr($yyyymm,0,4);
$mm   = substr($yyyymm,4,2);
if (($mm>=4) && ($mm<=9)) {
    $z_start_yyyy = $yyyy - 1;
    $z_start_ym   = $z_start_yyyy . '10';
    $z_end_ym     = $yyyy . '03';
    $z_start_ymd  = $z_start_ym . '01';
    $z_end_ymd    = $z_end_ym . '31';
} elseif (($mm>=10) && ($mm<=12)) {
    $z_start_ym   = $yyyy . '04';
    $z_end_ym     = $yyyy . '09';
    $z_start_ymd  = $z_start_ym . '01';
    $z_end_ymd    = $z_end_ym . '31';
} else {
    $z_start_yyyy = $yyyy - 1;
    $z_start_ym   = $z_start_yyyy . '04';
    $z_end_ym     = $z_start_yyyy . '09';
    $z_start_ymd  = $z_start_ym . '01';
    $z_end_ymd    = $z_end_ym . '31';
}
///// yymm形式
$ym4 = substr($yyyymm, 2, 4);

////////// 登録済みのチェック
$query = sprintf("SELECT pl_bs_ym FROM act_pl_history WHERE pl_bs_ym=%d", $yyyymm);
if ((getUniResult($query,$res_chk)) > 0) {
    $_SESSION["s_sysmsg"] .= sprintf("損益計算は実行済みです<br>第 %d期 %d月",$ki,$tuki);
    // $_SESSION['s_sysmsg'] .= "$query <br>";     // debug
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
////////// サービス割合登録済みのチェック
$query = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date=%d and (total_item='Ｃ製特注' or total_item='Ｃ組特注' or total_item='Ｃ外特注')", $yyyymm);
if ((getUniResult($query,$res_chk)) > 0) {
} else {
    $_SESSION["s_sysmsg"] .= sprintf("先にサービス割合の登録を行ってください。");
    // $_SESSION['s_sysmsg'] .= "$query <br>";     // debug
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
////////// 仕入計上処理済のチェック
$query = sprintf("SELECT sum_payable, sum_provide, cnt_payable, cnt_provide FROM act_purchase_header WHERE purchase_ym=%d AND item='バイモル'", $yyyymm);
if ((getUniResult($query,$res_chk)) > 0) {
} else {
    $_SESSION["s_sysmsg"] .= sprintf("先に経理メニューの仕入計上処理を行ってください。");
    // $_SESSION['s_sysmsg'] .= "$query <br>";     // debug
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    $_SESSION["s_sysmsg"] .= "データベースに接続できません";
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/***** 売    上    高 *****/
$res = array();
$query = sprintf("SELECT 全体, カプラ, リニア FROM wrk_uriage WHERE 年月=%d", $yyyymm);
if ((getResult($query,$res)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
    $uri   = $res[0]['全体'];
    $uri_c = $res[0]['カプラ'];
    $uri_l = $res[0]['リニア'];
        ///// act_invent_history の場合は調整もあるのでチェック act_adjust_history をみる
    $query = sprintf("SELECT SUM(kin) FROM act_adjust_history WHERE pl_bs_ym=%d AND note LIKE '%%売上高調整'", $yyyymm); // 全体
    getUniResult($query, $res_adjust);
    if ($res_adjust != 0) {
        $uri = ($uri + ($res_adjust));      // マイナスの場合を考慮して()を使う
    }
    $query = sprintf("INSERT INTO act_pl_history (pl_bs_ym, kin, note) VALUES(%d, %d, '%s')", $yyyymm, $uri, "全体売上高");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION["s_sysmsg"] .= sprintf("全体売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
        ///// act_invent_history の場合は調整もあるのでチェック act_adjust_history をみる
    $query = sprintf("SELECT SUM(kin) FROM act_adjust_history WHERE pl_bs_ym=%d AND note='カプラ売上高調整'", $yyyymm); // カプラ
    getUniResult($query, $res_adjust);
    if ($res_adjust != 0) {
        $uri_c = ($uri_c + ($res_adjust));      // マイナスの場合を考慮して()を使う
    }
    
    // 商品管理業務の売上登録（2009/09からカプラに商管の売上高が入っている）
    if ($yyyymm >= 200909) {
        $query = "SELECT
                            COUNT(数量) AS t_ken,
                            SUM(数量) AS t_kazu,
                            SUM(Uround(数量*単価,0)) AS t_kingaku
                        FROM
                            hiuuri";
        //////////// SQL where 句を 共用する
        $search = "WHERE 計上日>=$d_start AND 計上日<=$d_end";
        $search .= " AND (assyno LIKE 'NKB%%')";
        $query = sprintf("$query %s", $search);     // SQL query 文の完成
        $_SESSION['sales_search'] = $search;        // SQLのwhere句を保存
        $res_sum = array();
        if (getResult($query, $res_sum) <= 0) {
            $t_kingaku = 0;
        } else {
            $t_kingaku = $res_sum[0]['t_kingaku'];
        }
        
        $uri_c = $uri_c - $t_kingaku;               // カプラ売上高の中に商管売上高が入っている為マイナス
        
        $res_chk = array();
        $query_chk = sprintf("SELECT kin FROM act_pl_history WHERE pl_bs_ym=%d AND note='商管売上高'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
                                // 新規登録
            $query = sprintf("INSERT INTO act_pl_history (pl_bs_ym, kin, note) VALUES (%d, %d, '商管売上高')", $yyyymm, $t_kingaku);
            if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
                $_SESSION['s_sysmsg'] .= "<br>データベースの新規登録に失敗しました";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {                // UPDATE
            $query = sprintf("UPDATE act_pl_history SET kin=%d WHERE pl_bs_ym=%d AND note='商管売上高'", $t_kingaku, $yyyymm);
            if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
                $_SESSION['s_sysmsg'] .= "<br>データベースのUPDATEに失敗しました No$NG_row";
                query_affected_trans($con, "rollback");         // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
    }
        
    $query = sprintf("INSERT INTO act_pl_history (pl_bs_ym, kin, note, allo) VALUES(%d, %d, '%s', 1.00000)", $yyyymm, $uri_c, "カプラ売上高");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION["s_sysmsg"] .= sprintf("カプラ売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
        ///// act_invent_history の場合は調整もあるのでチェック act_adjust_history をみる
    $query = sprintf("SELECT SUM(kin) FROM act_adjust_history WHERE pl_bs_ym=%d AND note='リニア売上高調整'", $yyyymm); // リニア
    getUniResult($query, $res_adjust);
    if ($res_adjust != 0) {
        $uri_l = ($uri_l + ($res_adjust));      // マイナスの場合を考慮して()を使う
    }
    $query = sprintf("INSERT INTO act_pl_history (pl_bs_ym, kin, note, allo) VALUES(%d, %d, '%s', 1.00000)", $yyyymm, $uri_l, "リニア売上高");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION["s_sysmsg"] .= sprintf("リニア売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // トランザクション rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("売上高の対象データがありません。<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
// 前半期売上高
$res = array();
$query = sprintf("SELECT SUM(全体), SUM(カプラ), SUM(リニア) FROM wrk_uriage WHERE 年月>=%d AND 年月<=%d", $z_start_ym, $z_end_ym);
if ((getResult($query,$res)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
    $uri_total   = $res[0][0];
    $uri_c_total = $res[0][1];
    $uri_l_total = $res[0][2];
        ///// act_invent_history の場合は調整もあるのでチェック act_adjust_history をみる
    $query = sprintf("SELECT SUM(kin) FROM act_adjust_history WHERE pl_bs_ym>=%d AND pl_bs_ym<=%d AND note LIKE '%%売上高調整'", $z_start_ym, $z_end_ym); // 全体
    getUniResult($query, $res_adjust);
    if ($res_adjust != 0) {
        $uri_total = ($uri_total + ($res_adjust));          // マイナスの場合を考慮して()を使う
    }
        ///// act_invent_history の場合は調整もあるのでチェック act_adjust_history をみる
    $query = sprintf("SELECT SUM(kin) FROM act_adjust_history WHERE pl_bs_ym>=%d AND pl_bs_ym<=%d AND note='カプラ売上高調整'", $z_start_ym, $z_end_ym); // カプラ
    getUniResult($query, $res_adjust);
    if ($res_adjust != 0) {
        $uri_c_total = ($uri_c_total + ($res_adjust));      // マイナスの場合を考慮して()を使う
    }
        ///// act_invent_history の場合は調整もあるのでチェック act_adjust_history をみる
    $query = sprintf("SELECT SUM(kin) FROM act_adjust_history WHERE pl_bs_ym>=%d AND pl_bs_ym<=%d AND note='リニア売上高調整'", $z_start_ym, $z_end_ym); // リニア
    getUniResult($query, $res_adjust);
    if ($res_adjust != 0) {
        $uri_l_total = ($uri_l_total + ($res_adjust));      // マイナスの場合を考慮して()を使う
    }
}

// 前半期売上より商管の売上高比を計算
$query = sprintf("SELECT SUM(kin) FROM act_pl_history WHERE pl_bs_ym>=%d AND pl_bs_ym<=%d AND note='商管売上高'", $z_start_ym, $z_end_ym);
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $b_total_kingaku = 0;
    $b_allo       = 0.00000;
} else {
    $b_total_kingaku = $res_sum[0][0];
    $b_allo       = Uround(($b_total_kingaku / $uri_total),4);    // 商管の売上高比率(全体売上）
}
$res_chk = array();
$query = sprintf("UPDATE act_pl_history SET allo=%1.4f WHERE pl_bs_ym=%d AND note='商管売上高'", $b_allo, $yyyymm);
if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
    $_SESSION['s_sysmsg'] .= "<br>データベースのUPDATEに失敗しました No$NG_row";
    query_affected_trans($con, "rollback");         // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

// 試験・修理業務の売上登録
$query = "SELECT
                    COUNT(数量) AS t_ken,
                    SUM(数量) AS t_kazu,
                    SUM(Uround(数量*単価,0)) AS t_kingaku
              FROM
                    hiuuri";
//////////// SQL where 句を 共用する
$search = "WHERE 計上日>=$d_start AND 計上日<=$d_end";
$search .= " AND (assyno LIKE 'SS%%')";
$query = sprintf("$query %s", $search);     // SQL query 文の完成
$_SESSION['sales_search'] = $search;        // SQLのwhere句を保存
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $t_kingaku = 0;
} else {
    $t_kingaku = $res_sum[0]['t_kingaku'];
}

if (($yyyymm >= 200904) && ($yyyymm <= 200909)) {       // 上期は固定値で0.00900
    $ss_allo = 0.00900;
} else {
    $query = "SELECT
                    COUNT(数量) AS t_ken,
                    SUM(数量) AS t_kazu,
                    SUM(UROUND(数量*単価,0)) AS t_kingaku
              FROM
                    hiuuri";
    //////////// SQL where 句を 共用する
    $search = "WHERE 計上日>=$z_start_ymd AND 計上日<=$z_end_ymd";
    $search .= " AND (assyno LIKE 'SS%%')";
    $query = sprintf("$query %s", $search);     // SQL query 文の完成
    $_SESSION['sales_search'] = $search;        // SQLのwhere句を保存
    $res_sum = array();
    if (getResult($query, $res_sum) <= 0) {
        $total_kingaku = 0;
        $ss_allo       = 0.00000;
    } else {
        $total_kingaku = $res_sum[0]['t_kingaku'];
        $ss_allo       = Uround(($total_kingaku / $uri_total),4);    // 試験・修理業務の売上高比率(全体売上）
    }
}
// リニア売上高計算（機工の割合計算に使用）
$uri_l_last= $uri_l - $t_kingaku;

$res_chk = array();
$query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $yyyymm);
if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
                        // 新規登録
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '試修売上高', %1.5f)", $yyyymm, $t_kingaku, $ss_allo);
    if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
        $_SESSION['s_sysmsg'] .= "<br>データベースの新規登録に失敗しました";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {                // UPDATE
    $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='試修売上高'", $t_kingaku, $ss_allo, $yyyymm);
    if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
        $_SESSION['s_sysmsg'] .= "<br>データベースのUPDATEに失敗しました No$NG_row";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
// バイモルの売上登録
$query = "select
                    count(数量) as t_ken,
                    sum(数量) as t_kazu,
                    sum(Uround(数量*単価,0)) as t_kingaku
              from
                    hiuuri
                  left outer join
                        miitem as m
                  on assyno=m.mipn";
//////////// SQL where 句を 共用する
$search = "where 計上日>=$d_start and 計上日<=$d_end";
$search .= " and (assyno like 'LC%%' or assyno like 'LR%%' or m.midsc like 'DPE%%')";
$query = sprintf("$query %s", $search);     // SQL query 文の完成
$_SESSION['sales_search'] = $search;        // SQLのwhere句を保存
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $t_kingaku = 0;
} else {
    $t_kingaku = $res_sum[0]['t_kingaku'];
}
$query = "select
                    count(数量) as t_ken,
                    sum(数量) as t_kazu,
                    sum(Uround(数量*単価,0)) as t_kingaku
              from
                    hiuuri
                  left outer join
                        miitem as m
                  on assyno=m.mipn";
//////////// SQL where 句を 共用する
$search = "where 計上日>=$z_start_ymd and 計上日<=$z_end_ymd";
//$search .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
$search .= " and (assyno like 'LC%%' or assyno like 'LR%%' or m.midsc like 'DPE%%')";
$query = sprintf("$query %s", $search);     // SQL query 文の完成
$_SESSION['sales_search'] = $search;        // SQLのwhere句を保存
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $total_kingaku = 0;
    $bimor_allo    = 0.00000;
} else {
    $total_kingaku = $res_sum[0]['t_kingaku'];
    $bimor_allo    = Uround(($total_kingaku / $uri_l_total),5);    // バイモルの売上高比率
}

$res_chk = array();
$query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='バイモル売上高'", $yyyymm);
if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
                        // 新規登録
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'バイモル売上高', %1.5f)", $yyyymm, $t_kingaku, $bimor_allo);
    if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
        $_SESSION['s_sysmsg'] .= "<br>データベースの新規登録に失敗しました";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {                // UPDATE
    $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='バイモル売上高'", $t_kingaku, $bimor_allo, $yyyymm);
    if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
        $_SESSION['s_sysmsg'] .= "<br>データベースのUPDATEに失敗しました No$NG_row";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}

// 機工の売上登録
$query = "select
                    count(数量) as t_ken,
                    sum(数量) as t_kazu,
                    sum(Uround(数量*単価,0)) as t_kingaku
              from
                    hiuuri";
//////////// SQL where 句を 共用する
$search = "where 計上日>=$d_start and 計上日<=$d_end";
//$search .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
$search .= " and 事業部='T'";
$query = sprintf("$query %s", $search);     // SQL query 文の完成
$_SESSION['sales_search'] = $search;        // SQLのwhere句を保存
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $total_kingaku = 0;
    $tool_allo    = 0.00000;
} else {
    $total_kingaku = $res_sum[0]['t_kingaku'];
    $tool_allo    = Uround(($total_kingaku / $uri_l_last),5);    // 機工の売上高比率
}

$res_chk = array();
$query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工売上高'", $yyyymm);
if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
                        // 新規登録
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '機工売上高', %1.5f)", $yyyymm, $total_kingaku, $tool_allo);
    if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
        $_SESSION['s_sysmsg'] .= "<br>データベースの新規登録に失敗しました";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {                // UPDATE
    $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='機工売上高'", $total_kingaku, $tool_allo, $yyyymm);
    if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
        $_SESSION['s_sysmsg'] .= "<br>データベースのUPDATEに失敗しました No$NG_row";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
// カプラ特注の売上登録
$query = "select
                        count(数量) as t_ken,
                        sum(数量) as t_kazu,
                        sum(Uround(数量*単価,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on 計画番号=plan_no";
//////////// SQL where 句を 共用する
$search = "where 計上日>=$d_start and 計上日<=$d_end";
$search .= " and 事業部='C' and note15 like 'SC%%'";
$query = sprintf("$query %s", $search);     // SQL query 文の完成
$_SESSION['sales_search'] = $search;        // SQLのwhere句を保存
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $t_kingaku = 0;
} else {
    $t_kingaku = $res_sum[0]['t_kingaku'];
    if ($yyyymm == 201009) {
        $t_kingaku = $t_kingaku + 6249616;
    }
}
$query = "select
                        count(数量) as t_ken,
                        sum(数量) as t_kazu,
                        sum(Uround(数量*単価,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on 計画番号=plan_no";
//////////// SQL where 句を 共用する
$search = "where 計上日>=$z_start_ymd and 計上日<=$z_end_ymd";
$search .= " and 事業部='C' and note15 like 'SC%%'";
$query = sprintf("$query %s", $search);     // SQL query 文の完成
$_SESSION['sales_search'] = $search;        // SQLのwhere句を保存
$res_sum = array();
if (getResult($query, $res_sum) <= 0) {
    $total_kingaku = 0;
    $ctoku_allo   = 0.00000;
} else {
    $total_kingaku = $res_sum[0]['t_kingaku'];
    $ctoku_allo    = Uround(($total_kingaku / $uri_c_total),5);    // カプラ特注の売上高比率
}
$res_chk = array();
$query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注売上高'", $yyyymm);
if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
                        // 新規登録
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'カプラ特注売上高', %1.5f)", $yyyymm, $t_kingaku, $ctoku_allo);
    if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
        $_SESSION['s_sysmsg'] .= "<br>データベースの新規登録に失敗しました";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {                // UPDATE
    $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='カプラ特注売上高'", $t_kingaku, $ctoku_allo, $yyyymm);
    if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
        $_SESSION['s_sysmsg'] .= "<br>データベースのUPDATEに失敗しました No$NG_row";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}

/***** 期首材料仕掛品棚卸高 *****/
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体期末棚卸高'", $p1_ym);
if ((getUniResult($query,$res_kin)) > 0) {     // データ無しのチェック 優先順位の括弧に注意
        ///// 対象前月の期末棚卸高を対象月の期首棚卸高に登録
    $res_kin = ($res_kin * (1));               // 符号反転やめた 損益計算書上でマイナス表示させる
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "全体期首棚卸高");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("全体の期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $invent = $res_kin;     // 全体期首棚卸高
} else {        // act_pl_history(損益計算経歴に)前月の棚卸高がなければact_invent_history(棚卸経歴)から取得
    $query = sprintf("select sum(kin) from act_invent_history where pl_bs_ym=%d", $p1_ym); // 全体
    getUniResult($query,$res_kin);
    if ($res_kin != 0) {
             ///// act_invent_history の場合は調整もあるのでチェック act_adjust_history をみる
        $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note like '%%期末棚卸高調整'", $p1_ym); // 全体
        getUniResult($query, $res_adjust);
        if ($res_adjust != 0) {
            $res_kin = ($res_kin + ($res_adjust));
        }
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "全体期首棚卸高");
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("全体の期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $invent = $res_kin;     // 全体期首棚卸高
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("全体の期末棚卸高の対象データがありません。<br>前月年月 %d", $p1_ym);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期末棚卸高'", $p1_ym);
if ((getUniResult($query,$res_kin)) > 0) {     // データ無しのチェック 優先順位の括弧に注意
        ///// 対象前月の期末棚卸高を対象月の期首棚卸高に登録
    $res_kin = ($res_kin * (1));               // 符号反転やめた 損益計算書上でマイナス表示させる
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "カプラ期首棚卸高");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $invent_c = $res_kin;     // カプラ期首棚卸高
} else {        // act_pl_history(損益計算経歴に)前月の棚卸高がなければact_invent_history(棚卸経歴)から取得
    $query = sprintf("select sum(kin) from act_invent_history where pl_bs_ym=%d and note='カプラ'", $p1_ym);
    getUniResult($query,$res_kin);
    if ($res_kin != 0) {
             ///// act_invent_history の場合は調整もあるのでチェック act_adjust_history をみる
        $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='カプラ期末棚卸高調整'", $p1_ym); // カプラ
        getUniResult($query, $res_adjust);
        if ($res_adjust != 0) {
            $res_kin = ($res_kin + ($res_adjust));
        }
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "カプラ期首棚卸高");
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("カプラの期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $invent_c = $res_kin;     // カプラ期首棚卸高
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("カプラの期末棚卸高の対象データがありません。<br>前月年月 %d", $p1_ym);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
// カプラ特注期首・期末棚卸高
// 2013/11以降は特注の棚卸高の取得方法を変更（手動入力）
if ($yyyymm >= 201311) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注期末棚卸高'", $p1_ym);
    if ((getUniResult($query,$res_kin)) > 0) {     // データ無しのチェック 優先順位の括弧に注意
        ///// 対象前月の期末棚卸高を対象月の期首棚卸高に登録
        $res_kin = ($res_kin * (1));               // 符号反転やめた 損益計算書上でマイナス表示させる
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "カプラ特注期首棚卸高");
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("カプラ特注期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {    // act_pl_history(損益計算経歴に)前月の棚卸高がなければact_invent_history(棚卸経歴)から取得
        $query = sprintf("select sum(kin) from act_invent_history where pl_bs_ym=%d and note='特注合計'", $p1_ym);
        getUniResult($query,$res_kin);
        if ($res_kin != 0) {
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "カプラ特注期首棚卸高");
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注の期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("カプラ特注の期末棚卸高の対象データがありません。<br>前月年月 %d", $p1_ym);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    ///// act_invent_history より期末棚卸高 取得
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注合計'", $yyyymm);
    if (getUniResult($query,$ctoku_kin) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ特注期末棚卸高の対象データがありません。<br>月次年月 %d", $yyyymm);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注期末棚卸高')", $yyyymm, $ctoku_kin);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ特注期末棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $search = "where invent_ym={$p1_ym} and item='カプラ特注'";
    //////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
    $query = sprintf("select sum_money_z, sum_money_t, sum_count from inventory_monthly_header %s", $search);
    $res_sum = array();         // 初期化
    if ( getResult($query, $res_sum) <= 0) {         // $maxrows の取得
        $_SESSION['s_sysmsg'] .= sprintf("カプラ特注の期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");      // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_sum[0][1], "カプラ特注期首棚卸高");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ特注の期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");      // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $search = "where invent_ym={$yyyymm} and item='カプラ特注'";
    //////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
    $query = sprintf("select sum_money_z, sum_money_t, sum_count from inventory_monthly_header %s", $search);
    $res_sum = array();         // 初期化
    if ( getResult($query, $res_sum) <= 0) {         // $maxrows の取得
        $_SESSION['s_sysmsg'] .= sprintf("カプラ特注の期末棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_sum[0][1], "カプラ特注期末棚卸高");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ特注の期末棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}

$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期末棚卸高'", $p1_ym);
if ((getUniResult($query,$res_kin)) > 0) {     // データ無しのチェック 優先順位の括弧に注意
        ///// 対象前月の期末棚卸高を対象月の期首棚卸高に登録
    $res_kin = ($res_kin * (1));               // 符号反転やめた 損益計算書上でマイナス表示させる
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "リニア期首棚卸高");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("リニア期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $invent_l = $res_kin;     // リニア期首棚卸高
} else {        // act_pl_history(損益計算経歴に)前月の棚卸高がなければact_invent_history(棚卸経歴)から取得
    $query = sprintf("select sum(kin) from act_invent_history where pl_bs_ym=%d and note='リニア'", $p1_ym);
    getUniResult($query,$res_kin);
    if ($res_kin != 0) {
             ///// act_invent_history の場合は調整もあるのでチェック act_adjust_history をみる
        $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='リニア期末棚卸高調整'", $p1_ym); // リニア
        getUniResult($query, $res_adjust);
        if ($res_adjust != 0) {
            $res_kin = ($res_kin + ($res_adjust));
        }
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "リニア期首棚卸高");
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("リニアの期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $invent_l = $res_kin;     // リニア期首棚卸高
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("リニアの期末棚卸高の対象データがありません。<br>前月年月 %d", $p1_ym);
        query_affected_trans($con, "rollback");         // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}

// バイモル期首・期末棚卸高
$search = "where invent_ym={$p1_ym} and item='バイモル'";
//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
$query = sprintf("select sum_money_z, sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // 初期化
if ( getResult($query, $res_sum) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= sprintf("バイモルの期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");      // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_sum[0][1], "バイモル期首棚卸高");
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("バイモルの期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$search = "where invent_ym={$yyyymm} and item='バイモル'";
//////////// 合計レコード数取得     (対象データの最大数をページ制御に使用)
$query = sprintf("select sum_money_z, sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // 初期化
if ( getResult($query, $res_sum) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= sprintf("バイモルの期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");      // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_sum[0][1], "バイモル期末棚卸高");
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("バイモルの期末棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

// 機工期首・期末棚卸高
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工期末棚卸高'", $p1_ym);
if ((getUniResult($query,$res_kin)) > 0) {     // データ無しのチェック 優先順位の括弧に注意
    ///// 対象前月の期末棚卸高を対象月の期首棚卸高に登録
    $res_kin = ($res_kin * (1));               // 符号反転やめた 損益計算書上でマイナス表示させる
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "機工期首棚卸高");
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("機工期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {    // act_pl_history(損益計算経歴に)前月の棚卸高がなければact_invent_history(棚卸経歴)から取得
    $query = sprintf("select sum(kin) from act_invent_history where pl_bs_ym=%d and note='ツール合計'", $p1_ym);
    getUniResult($query,$res_kin);
    if ($res_kin != 0) {
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values(%d, %d, '%s')", $yyyymm, $res_kin, "機工期首棚卸高");
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("機工の期首棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("機工の期末棚卸高の対象データがありません。<br>前月年月 %d", $p1_ym);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
///// act_invent_history より期末棚卸高 取得
$query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール合計'", $yyyymm);
if (getUniResult($query,$ctoku_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("機工期末棚卸高の対象データがありません。<br>月次年月 %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '機工期末棚卸高')", $yyyymm, $ctoku_kin);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("機工期末棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** 材料費(仕入高) *****/
    ///// 要素別買掛表の計算(仕入高でＣＬ比率を決める)
$query = sprintf("select kin1 from pl_bs_summary where t_id='E' and pl_bs_ym=%d order by t_row ASC", $yyyymm);
$res = array();
if ((getResult($query, $res)) > 0) {
    $shiire_c = ($res[0][0] - $res[2][0]);      // 買掛金１〜５ − 有償支給未収入金１〜５ カプラ
    $shiire_l = ($res[1][0] - $res[3][0]);      // 買掛金１〜５ − 有償支給未収入金１〜５ リニア
    ///// 調整が必要な場合は調整する
    $query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='カプラ仕入高調整'", $yyyymm);
    if ((getUniResult($query, $adjust_c)) > 0) {
        $shiire_c = ($shiire_c + ($adjust_c));
    }
    $query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='リニア仕入高調整'", $yyyymm);
    if ((getUniResult($query, $adjust_l)) > 0) {
        $shiire_l = ($shiire_l + ($adjust_l));
    }
    $shiire   = ($shiire_c + $shiire_l);            // 全体
    $c_ritu   = Uround(($shiire_c / $shiire),5);    // カプラの材料比率
    $l_ritu   = Uround(($shiire_l / $shiire),5);    // リニアの材料比率
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '全体仕入高', 1.00000)", $yyyymm, $shiire);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("全体仕入高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'カプラ仕入高', %1.5f)", $yyyymm, $shiire_c, $c_ritu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ仕入高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'リニア仕入高', %1.5f)", $yyyymm, $shiire_l, $l_ritu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("リニア仕入高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("要素別買掛表の対象データがありません。<br>月次年月 %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// カプラ材料費・リニア材料費は
    ///// 売上原価の材料費からＣＬ材料費を計算(仕入高ＣＬ比率) 以下の売上原価で処理する
    ///// カプラ特注の材料費

// バイモル
$query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
            from act_purchase_header
            where purchase_ym={$yyyymm} and item='バイモル'";
$res = array();     // 初期化
if ( getResultTrs($con, $query, $res) <= 0) {
    $paya_l_bimor_kin = $res[0][0];         // 買掛
    $prov_l_bimor_kin = $res[0][1];         // 有償支給
} else {
    $paya_l_bimor_kin = $res[0][0];         // 買掛
    $prov_l_bimor_kin = $res[0][1];         // 有償支給
}
$l_bimor_sum_kin = ($paya_l_bimor_kin - $prov_l_bimor_kin);
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'バイモル仕入高', 1.00000)", $yyyymm, $l_bimor_sum_kin);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("バイモル仕入高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

// 機工
$str_ymd = $yyyymm . '01';
$end_ymd = $yyyymm . '99';
$query = "select sum(Uround(order_price * siharai,0)) from act_payable as a 
            LEFT OUTER JOIN parts_stock_master AS m ON (m.parts_no=a.parts_no) 
            where act_date>={$str_ymd} and act_date<={$end_ymd} and div='T'";
            
$res = array();     // 初期化
if ( getResultTrs($con, $query, $res) <= 0) {
    $tool_kin = 0;                  // 買掛 事業部T
} else {
    $tool_kin = $res[0][0];         // 買掛 事業部T
}
/* 在庫がなくなるので買掛もCLに */

$query = "select sum(Uround(order_price * siharai,0)) from act_payable as a 
            LEFT OUTER JOIN parts_stock_master AS m ON (m.parts_no=a.parts_no) 
            LEFT OUTER JOIN miitem ON (mipn = a.parts_no) 
            where act_date>={$str_ymd} and act_date<={$end_ymd} and ((a.div<>'T' and a.div<>'C' and a.parts_no like 'T%' and ( mepnt like 'ADR%%' or mepnt like 'L-25%%' )))";
            

$res = array();     // 初期化
if ( getResultTrs($con, $query, $res) <= 0) {
    $tool_kin_o = 0;                  // 買掛 事業部T以外で頭がTのもの
} else {
    $tool_kin_o = $res[0][0];         // 買掛 事業部T以外で頭がTのもの
}

$tool_kin = $tool_kin + $tool_kin_o;

if($yyyymm==202006) {
    $tool_kin = $tool_kin - 600000;
}

///// 配賦率取得用 年月日
$yyyy_hai = substr($yyyymm, 0,4);
$mm_hai   = substr($yyyymm, 4,2);

if($mm_hai == 4) {
    $hai_ym = $yyyy_hai . '03';
} elseif($mm_hai == 5) {
    $hai_ym = $yyyy_hai . '03';
} elseif($mm_hai == 10) {
    $hai_ym = $yyyy_hai . '09';
} elseif($mm_hai == 11) {
    $hai_ym = $yyyy_hai . '09';
} else {
    $hai_ym = $yyyymm;
}
/* 機工なくなるので配賦なし
$query_a = sprintf("SELECT * FROM indirect_cost_allocate WHERE total_date<=%d and item='リニア' ORDER BY total_date DESC limit 1", $hai_ym);
$res_a = array();
$rows_a = getResult($query_a, $res_a);
if ($res_a[0]['suppli_section_cost'] == '') {
    $allo_suppli = 0;
} else {
    $allo_suppli     = $res_a[0]['suppli_section_cost'] / 100;
    $tool_kin_suppli = round($tool_kin * $allo_suppli);
    $tool_kin        = $tool_kin + $tool_kin_suppli;
}

// 機工の仕入高 リニアからの配賦だが赤字になりそうなら微調整(こっそり)
if ($hai_ym==201802) {
    $tool_kin = $tool_kin - 260000;
}
if ($hai_ym==201809) {
    $tool_kin = $tool_kin - 1134000;
}
if ($hai_ym==201901) {
    $tool_kin = $tool_kin - 1000000;
}
*/
/*
if ($hai_ym==202103) {
    $tool_kin = $tool_kin - 382140;
}
*/
$res_chk = array();
$query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工仕入高'", $yyyymm);
if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
                        // 新規登録
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '機工仕入高', 1.00000)", $yyyymm, $tool_kin);
    if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
        $_SESSION['s_sysmsg'] .= "<br>データベースの新規登録に失敗しました";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {                // UPDATE
    $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='機工仕入高'", $tool_kin, $yyyymm);
    if(query_affected_trans($con, $query) <= 0){        // 更新用クエリーの実行
        $_SESSION['s_sysmsg'] .= "<br>データベースのUPDATEに失敗しました No$NG_row";
        query_affected_trans($con, "rollback");         // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}

/***** 労    務    費 *****/
    ///// act_cl_history のＣＬ別経費実績表から取得
$query = sprintf("select sum(kin00), sum(kin01), sum(kin02) from act_cl_history where pl_bs_ym=%d and actcod>=8101 and actcod<=8130", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_roumu = $res[0][0];
    $l_roumu = $res[0][1];
    $roumu   = $res[0][2];
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体労務費')", $yyyymm, $roumu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("全体労務費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ労務費')", $yyyymm, $c_roumu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ労務費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア労務費')", $yyyymm, $l_roumu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("リニア労務費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("労務費の対象データがありません。<br>月次年月 %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

    // カプラ特注労務費
$ctoku_roumu = 0;
    // 525 部門労務費
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=525 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += $res[0][0];
    // 556 部門労務費
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=556 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += $res[0][0];
    // カプラ特注間接費 サービス割合より
$query = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date=%d and (total_item='Ｃ製特注' or total_item='Ｃ組特注' or total_item='Ｃ外特注')", $yyyymm);
$res = array();
getResult($query, $res);
$ctoku_roumu += $res[0][0];
    // 523 カプラ組立HA労務費配賦50％
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=523 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * 0.5),0);
    // 500 生産部労務費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=500 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);
    // 510 生産部C労務費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=510 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);
    // 518 製造１課労務費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=518 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
    // 582 製造部労務費配賦 （2016/04〜）
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=582 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);
    // 511 生産管理課C担当労務費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=511 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);
    // 512 生産管理課計画C担当労務費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=512 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);
    // 513 購買課労務費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=513 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);
    // 514 カプラ資材労務費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=514 and actcod>=8101 and actcod<=8130", $ym4);
$res = array();
getResult($query, $res);
$ctoku_roumu += Uround(($res[0][0] * $ctoku_allo),0);

$query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注労務費'", $yyyymm);
if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
    // 新規登録
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注労務費')", $yyyymm, $ctoku_roumu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ特注労務費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ特注労務費'", $ctoku_roumu, $yyyymm);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ特注労務費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
    
/***** 経          費 *****/
    ///// act_cl_history のＣＬ別経費実績表から取得 ***** 製造経費の経費 *****
$query = sprintf("select sum(kin00), sum(kin01), sum(kin02) from act_cl_history where pl_bs_ym=%d and actcod<=8000", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_keihi = $res[0][0];
    $l_keihi = $res[0][1];
    $keihi   = $res[0][2];
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体製造経費')", $yyyymm, $keihi);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("全体製造経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ製造経費')", $yyyymm, $c_keihi);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ製造経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア製造経費')", $yyyymm, $l_keihi);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("リニア製造経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("製造経費の経費 対象データがありません。<br>月次年月 %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

    // バイモル製造経費
$b_keihi = 0;
    // 560 部門経費
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=560 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$b_keihi += $res[0][0];
    // 500 生産部経費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=500 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$b_keihi += Uround(($res[0][0] * $bimor_allo),0);

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル製造経費')", $yyyymm, $b_keihi);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("バイモル製造経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    // 試験修理製造経費
$s_keihi = 0;
    // 559 部門経費
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=559 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$s_keihi += $res[0][0];
    // 2011/04 より 581 部門経費 追加
if ($yyyymm >= 201104) {
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=581 and actcod<=8000", $ym4);
    $res = array();
    getResult($query, $res);
    $s_keihi += $res[0][0];
}
if ($yyyymm < 201106) {     // 2011年6月より配賦しない
        // 500 生産部経費配賦
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=500 and actcod<=8000", $ym4);
    $res = array();
    getResult($query, $res);
    $s_keihi += Uround(($res[0][0] * $ss_allo),0);
}

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修製造経費')", $yyyymm, $s_keihi);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("試修製造経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

// カプラ特注製造経費
$ctoku_keihi = 0;
    // 525 部門製造経費
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=525 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += $res[0][0];
    // 556 部門製造経費
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=556 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += $res[0][0];
    // 523 カプラ組立HA製造経費配賦50％
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=523 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * 0.5),0);
    // 500 生産部製造経費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=500 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 510 生産部C製造経費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=510 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 518 製造１課製造経費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=518 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 582 製造部製造経費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=582 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 511 生産管理課C担当製造経費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=511 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 512 生産管理課計画C担当製造経費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=512 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 513 購買課製造経費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=513 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);
    // 514 カプラ資材製造経費配賦
$query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=514 and actcod<=8000", $ym4);
$res = array();
getResult($query, $res);
$ctoku_keihi += Uround(($res[0][0] * $ctoku_allo),0);

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注製造経費')", $yyyymm, $ctoku_keihi);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ特注製造経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/***** 期末材料仕掛品棚卸高 *****/
    ///// act_invent_history より棚卸高 取得
$query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='カプラ'", $yyyymm);
if (getUniResult($query,$c_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ期末棚卸高の対象データがありません。<br>月次年月 %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='リニア'", $yyyymm);
if (getUniResult($query,$l_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア期末棚卸高の対象データがありません。<br>月次年月 %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// 調整が必要な場合は調整する
$query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='カプラ期末棚卸高調整'", $yyyymm);
if ((getUniResult($query, $adjust_c)) > 0) {
    $c_kin = ($c_kin + ($adjust_c));
}
$query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='リニア期末棚卸高調整'", $yyyymm);
if ((getUniResult($query, $adjust_l)) > 0) {
    $l_kin = ($l_kin + ($adjust_l));
}
$all_kin = (($c_kin + $l_kin) * (1));       // 全体棚卸高 符号の反転はやめた 損益計算書の表示上でマイナスさせる
$c_kin   = ($c_kin * (1));                  // カプラ棚卸高 〃
$l_kin   = ($l_kin * (1));                  // リニア棚卸高 〃
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体期末棚卸高')", $yyyymm, $all_kin);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("全体期末棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ期末棚卸高')", $yyyymm, $c_kin);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ期末棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア期末棚卸高')", $yyyymm, $l_kin);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア期末棚卸高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** 売  上  原  価 *****/
///// 全体の売上原価に期首棚卸高を足した物から全体の製造経費 労務費・経費と期末棚卸高を引いた残りを全体の材料費
///// として仕入高のＣＬ比率を使用して材料費をＣＬ別に分ける。それからＣＬ別に期首棚卸高・材料費・労務費・経費を
///// 足して期末棚卸高を引いてＣＬ別の売上原価を算出する。
    // 全体売上原価   = (pl_bs_summary の t_id='A' t_row=2 pl_bs_ym=月次年月)
    // 全体材料費     = (全体売上原価 - (全体期首棚卸高 + 全体労務費 + 全体製造経費 - 全体期末棚卸高))
    // カプラ材料費   = Uround((全体材料費 * カプラ仕入高=>allo),0)
    // リニア材料費   = (全体材料費 - カプラ材料費)
    // カプラ売上原価 = (カプラ期首棚卸高 + カプラ材料費 + カプラ労務費 + カプラ製造経費 - カプラ期末棚卸高)
    // リニア売上原価 = (リニア期首棚卸高 + リニア材料費 + リニア労務費 + リニア製造経費 - リニア期末棚卸高)
$query = sprintf("select kin1 from pl_bs_summary where t_id='A' and t_row=2 and pl_bs_ym=%d", $yyyymm);
getUniResult($query,$res_kin);
if ($res_kin != 0) {
    $uri_genka = $res_kin;                                              // 全体売上原価
        ///// 調整がある場合には調整する
    $query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='売上原価調整'", $yyyymm);
    if ((getUniResult($query, $adjust)) > 0) {
        $uri_genka = ($uri_genka + ($adjust));
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("売上原価の対照データがない<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$material    = ($uri_genka - ($invent + $roumu + $keihi - $all_kin));       // 全体材料費 $all_kin はマイナス
$material_c  = Uround(($material * $c_ritu), 0);                            // カプラ材料費
$material_l  = ($material - $material_c);                                   // リニア材料費
$uri_genka_c = ($invent_c + $material_c + $c_roumu + $c_keihi - $c_kin);    // カプラ売上原価 $c_kin はマイナス
$uri_genka_l = ($invent_l + $material_l + $l_roumu + $l_keihi - $l_kin);    // リニア売上原価 $l_kin はマイナス
    ///// 材料費の登録
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体材料費')", $yyyymm, $material);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("全体材料費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ材料費')", $yyyymm, $material_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ材料費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア材料費')", $yyyymm, $material_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア材料費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// 売上原価の登録
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体売上原価')", $yyyymm, $uri_genka);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("全体売上原価の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ売上原価')", $yyyymm, $uri_genka_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ売上原価の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア売上原価')", $yyyymm, $uri_genka_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア売上原価の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** 売 上 総 利 益 *****/
    ///// 売上総利益 = (売上高 - 売上原価)
$gross_profit   = ($uri - $uri_genka);              // 全体売上総利益
$gross_profit_c = ($uri_c - $uri_genka_c);          // カプラ売上総利益
$gross_profit_l = ($uri_l - $uri_genka_l);          // リニア売上総利益
    ///// 登録
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体総利益')", $yyyymm, $gross_profit);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("全体総利益の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ総利益')", $yyyymm, $gross_profit_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ総利益の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア総利益')", $yyyymm, $gross_profit_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア総利益の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** 販管費の人件費 *****/
    ///// act_cl_history のＣＬ別経費実績表から取得
$query = sprintf("select sum(kin10), sum(kin11), sum(kin12) from act_cl_history where pl_bs_ym=%d and actcod>=8101 and actcod<=8130", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_jin = $res[0][0];
    $l_jin = $res[0][1];
    $jin   = $res[0][2];
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体人件費')", $yyyymm, $jin);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("全体人件費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ人件費')", $yyyymm, $c_jin);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ人件費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア人件費')", $yyyymm, $l_jin);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("リニア人件費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("販管費の人件費　対象データがありません。<br>月次年月 %d", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** 販管費の 経 費 *****/
    ///// act_cl_history のＣＬ別経費実績表から取得
$query = sprintf("select sum(kin10), sum(kin11), sum(kin12) from act_cl_history where pl_bs_ym=%d and actcod<=8000", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_kei = $res[0][0];
    $l_kei = $res[0][1];
    $kei   = $res[0][2];
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体経費')", $yyyymm, $kei);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("全体経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ経費')", $yyyymm, $c_kei);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア経費')", $yyyymm, $l_kei);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("リニア経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("販管費の経費　対象データがありません。<br>月次年月 %d", $yyyymm);
    query_affected_trans($con, "rollback");         // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** 販管費の 合 計 *****/
$hankan   = ($jin + $kei);      // 全体販管費
$hankan_c = ($c_jin + $c_kei);  // カプラ販管費
$hankan_l = ($l_jin + $l_kei);  // リニア販管費
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体販管費')", $yyyymm, $hankan);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("全体販管費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ販管費')", $yyyymm, $hankan_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ販管費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア販管費')", $yyyymm, $hankan_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア販管費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/***** 営  業  利  益 *****/
    ///// 営業利益 = (売上総利益 - 販管費 計)
$ope_profit   = ($gross_profit - $hankan);              // 全体営業利益
$ope_profit_c = ($gross_profit_c - $hankan_c);          // カプラ営業利益
$ope_profit_l = ($gross_profit_l - $hankan_l);          // リニア営業利益
    ///// 登録
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体営業利益')", $yyyymm, $ope_profit);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("全体営業利益の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ営業利益')", $yyyymm, $ope_profit_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ営業利益の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア営業利益')", $yyyymm, $ope_profit_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア営業利益の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** 業務委託 収 入 *****/
$query = sprintf("select kin2 from pl_bs_summary where t_id='A' and t_row=7 and pl_bs_ym=%d", $yyyymm);
getUniResult($query,$res_kin);
/* 2015/09/03
if ($res_kin != 0) {
*/
    $gyoumu = $res_kin;     // 全体業務委託収入
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体業務委託収入')", $yyyymm, $gyoumu);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("全体業務委託収入の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    ///// その他も取得しておく
    $query = sprintf("select kin2 from pl_bs_summary where t_id='A' and t_row=5 and pl_bs_ym=%d", $yyyymm);
    if (getUniResult($query,$p_other) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("営業外収益 その他のデータがない<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
/* 2015/09/03
} else {
    ///// 2003/01 以前は科目を取っていなかったのでここを見ることになる
    $query = sprintf("select kin from act_adjust_history where pl_bs_ym=%d and note='業務委託収入'", $yyyymm);
    if ((getUniResult($query, $gyoumu)) > 0) {
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体業務委託収入')", $yyyymm, $gyoumu);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("全体業務委託収入の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("業務委託収入の対照データがない<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
*/
    ///// ここでカプラとリニアの配賦率を取得する予定 (前期売上高比)
    ///// 第３期は（第２期のデータで カプラ=72.61% リニア=27.39%）
    ///// 第４期は（第３期のデータで カプラ=72.61% リニア=27.39%）
    ///// 第５期上期から カプラ=77.87% リニア=22.13% へ変更 半期毎に見直す予定
    ///// 第５期下期から カプラ=76.73% リニア=23.27% へ変更 半期毎に見直す予定
    ///// 第６期上期から カプラ=80.71% リニア=19.29% へ変更 半期毎に見直す予定
    ///// 第６期下期から カプラ=80.27% リニア=19.73% へ変更 半期毎に見直す予定
    ///// 第７期上期から カプラ=78.87% リニア=24.63% へ変更
    ///// 第７期下期から カプラ=81.27% リニア=18.73% へ変更
    ///// 第８期下期から カプラ=82.14% リニア=17.86% へ変更
    ///// 第９期上期から カプラ=82.42% リニア=17.58% へ変更
    ///// 第９期下期から カプラ=83.65% リニア=16.35% へ変更
    ///// 全て調整後（商管・試修調整入力nkb_inputの後に率と金額を再調整する）
if ($yyyymm <= 201001) {       // 2009年12月までは固定値で
    $zenki_uriagehi_c = 0.8365;     // 前期半期分の売上高比をマスターから取得する予定
    $zenki_uriagehi_l = 0.1635;
} else {
    $zenki_uriagehi_c = Uround(($uri_c_total / $uri_total),4);    // カプラの売上高比率(全体売上）
    $zenki_uriagehi_l = 1 - $zenki_uriagehi_c - $ss_allo;         // リニアの売上高比率(1-カプラ売上高比-試修売上高比）
    // 全体の割合に商管も加味する場合
    //$zenki_uriagehi_l = 1 - $zenki_uriagehi_c - $ss_allo - $b_allo;         // リニアの売上高比率(1-カプラ売上高比-試修売上高比-商管売上高比）
}

// 2005年11月以降の月次以降リニア固有の業務委託収入に対応
if ($yyyymm >= 200512) {
    if ($yyyymm >= 200907) {
        $tmp_gyoumu_l = 0;                      // 200907以降はリニア固有なし
    } elseif ($yyyymm == 200906) {
    // 200906はリニア固有分の調整済み(2か月分マイナス)の為元に戻す
        $tmp_gyoumu_l = 1550450;
        $gyoumu = ($gyoumu + $tmp_gyoumu_l * 2);
    } else {
        $tmp_gyoumu_l = 1550450;
        $gyoumu = ($gyoumu - $tmp_gyoumu_l);    // 全体からリニア固有の分を先に引いておく
    }
} elseif ($yyyymm == 200511) {
    $tmp_gyoumu_l = 2713288;                // 初回の11月だけ金額が違う (月割りの10月分と11月分を合算した金額)
    $gyoumu = ($gyoumu - $tmp_gyoumu_l);    // 全体からリニア固有の分を先に引いておく
} else {
    $tmp_gyoumu_l = 0;                      // 過去を実行した場合の対応
}
$gyoumu_c = Uround(($gyoumu * $zenki_uriagehi_c), 0);       // カプラ業務委託収入
if ($yyyymm == 200906) {
    $gyoumu_l = ($gyoumu - $gyoumu_c - $tmp_gyoumu_l * 2);  // リニア業務委託収入
    $gyoumu = ($gyoumu - $tmp_gyoumu_l * 2);    // 全体の業務委託収入を元に戻す(C/L配賦終了のため)
} else {
    $gyoumu_l = ($gyoumu - $gyoumu_c + $tmp_gyoumu_l);          // リニア業務委託収入
    $gyoumu = ($gyoumu + $tmp_gyoumu_l);        // 全体の業務委託収入を元に戻す(C/L配賦終了のため)
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ業務委託収入')", $yyyymm, $gyoumu_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ業務委託収入の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア業務委託収入')", $yyyymm, $gyoumu_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア業務委託収入の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
if ($yyyymm == 200906) {
    $gyoumu_l_chosei = $gyoumu_l + 3100900;
} elseif ($yyyymm == 200905) {
    $gyoumu_l_chosei = $gyoumu_l - 1550450;
} elseif ($yyyymm == 200904) {
    $gyoumu_l_chosei = $gyoumu_l - 1550450;
} else {
    $gyoumu_l_chosei = $gyoumu_l;
}

$gyoumu_b     = Uround(($gyoumu_l_chosei * $bimor_allo),0);    // バイモル業務委託収入
$gyoumu_s     = Uround(($gyoumu_l_chosei * $ss_allo),0);       // 試験修理業務委託収入
$gyoumu_ctoku = Uround(($gyoumu_c * $ctoku_allo),0);           // カプラ特注業務委託収入

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル業務委託収入')", $yyyymm, $gyoumu_b);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("バイモル業務委託収入の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修業務委託収入')", $yyyymm, $gyoumu_s);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("試修業務委託収入の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注業務委託収入')", $yyyymm, $gyoumu_ctoku);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ特注業務委託収入の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** 仕  入  割  引 *****/
$query = sprintf("select kin2 from pl_bs_summary where t_id='A' and t_row=6 and pl_bs_ym=%d", $yyyymm);
if (getUniResult($query,$s_wari) > 0) {
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体仕入割引')", $yyyymm, $s_wari);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("全体仕入割引の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("仕入割引の対照データがない<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// 第３期は カプラ=72.61% リニア=27.39%
$s_wari_c = Uround(($s_wari * $zenki_uriagehi_c), 0);      // カプラ仕入割引
$s_wari_l = ($s_wari - $s_wari_c);              // リニア仕入割引
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ仕入割引')", $yyyymm, $s_wari_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ仕入割引の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア仕入割引')", $yyyymm, $s_wari_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア仕入割引の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

$s_wari_b     = Uround(($s_wari_l * $bimor_allo),0);    // バイモル仕入割引
$s_wari_s     = Uround(($s_wari_l * $ss_allo),0);       // 試験修理仕入割引
$s_wari_ctoku = Uround(($s_wari_c * $ctoku_allo),0);    // カプラ特注仕入割引

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル仕入割引')", $yyyymm, $s_wari_b);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("バイモル仕入割引の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修仕入割引')", $yyyymm, $s_wari_s);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("試修仕入割引の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注仕入割引')", $yyyymm, $s_wari_ctoku);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ特注仕入割引の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** そ    の    他 *****/
    ///// A5 = その他だが 業務委託収入の科目がなかった過去のために
    ///// その他 = (A5 - 業務委託収入)
if (!isset($p_other)) {     // $p_other がセットされていなければ上記が適用
    $query = sprintf("select kin2 from pl_bs_summary where t_id='A' and t_row=5 and pl_bs_ym=%d", $yyyymm);
    if (getUniResult($query,$other) > 0) {
        $p_other = ($other - $gyoumu);      // 全体営業外収益その他
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体営業外収益その他')", $yyyymm, $p_other);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("全体仕入割引の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("営業外収益A5の対照データがない<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    ///// 営業外収益 その他 A5 $p_other の登録
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体営業外収益その他')", $yyyymm, $p_other);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("全体営業外収益その他の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
    ///// 第３期は カプラ=72.61% リニア=27.39%
$p_other_c = Uround(($p_other * $zenki_uriagehi_c), 0);        // カプラ営業外収益その他
$p_other_l = ($p_other - $p_other_c);               // リニア営業外収益その他
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ営業外収益その他')", $yyyymm, $p_other_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ営業外収益その他の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア営業外収益その他')", $yyyymm, $p_other_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア営業外収益その他の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$p_other_b     = Uround(($p_other_l * $bimor_allo),0);    // バイモル営業外収益その他
$p_other_s     = Uround(($p_other_l * $ss_allo),0);       // 試験修理営業外収益その他
$p_other_ctoku = Uround(($p_other_c * $ctoku_allo),0);    // カプラ特注営業外収益その他

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル営業外収益その他')", $yyyymm, $p_other_b);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("バイモル営業外収益その他の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修営業外収益その他')", $yyyymm, $p_other_s);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("試修営業外収益その他の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注営業外収益その他')", $yyyymm, $p_other_ctoku);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ特注営業外収益その他の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** 営業外収益合計 *****/
$nonope_p_sum   = ($gyoumu + $s_wari + $p_other);         // 全体営業外収益計
$nonope_p_c_sum = ($gyoumu_c + $s_wari_c + $p_other_c);   // カプラ営業外収益計
$nonope_p_l_sum = ($gyoumu_l + $s_wari_l + $p_other_l);   // リニア営業外収益計
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体営業外収益計')", $yyyymm, $nonope_p_sum);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("全体営業外収益合計の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ営業外収益計')", $yyyymm, $nonope_p_c_sum);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ営業外収益合計の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア営業外収益計')", $yyyymm, $nonope_p_l_sum);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア営業外収益合計の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** 支  払  利  息 *****/
                        // kin1 になるのに注意
$query = sprintf("select kin1 from pl_bs_summary where t_id='A' and t_row=8 and pl_bs_ym=%d", $yyyymm);
if (getUniResult($query,$risoku) > 0) {
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体支払利息')", $yyyymm, $risoku);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("全体支払利息の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("支払利息の対照データがない<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// 第３期は カプラ=72.61% リニア=27.39%
$risoku_c = Uround(($risoku * $zenki_uriagehi_c), 0);      // カプラ支払利息
$risoku_l = ($risoku - $risoku_c);              // リニア支払利息
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ支払利息')", $yyyymm, $risoku_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ支払利息の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア支払利息')", $yyyymm, $risoku_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア支払利息の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$risoku_b     = Uround(($risoku_l * $bimor_allo),0);    // バイモル支払利息
$risoku_s     = Uround(($risoku_l * $ss_allo),0);       // 試験修理支払利息
$risoku_ctoku = Uround(($risoku_c * $ctoku_allo),0);    // カプラ特注支払利息

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル支払利息')", $yyyymm, $risoku_b);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("バイモル支払利息の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修支払利息')", $yyyymm, $risoku_s);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("試修支払利息の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注支払利息')", $yyyymm, $risoku_ctoku);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ特注支払利息の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** そ    の    他 *****/        // 営業外費用
                        // kin1 になるのに注意
$query = sprintf("select kin1 from pl_bs_summary where t_id='A' and t_row=9 and pl_bs_ym=%d", $yyyymm);
if (getUniResult($query,$l_other) > 0) {
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体営業外費用その他')", $yyyymm, $l_other);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("全体営業外費用その他の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("営業外費用その他の対照データがない<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// 第３期は カプラ=72.61% リニア=27.39%
$l_other_c = Uround(($l_other * $zenki_uriagehi_c), 0);        // カプラ営業外費用その他
$l_other_l = ($l_other - $l_other_c);               // リニア営業外費用その他
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ営業外費用その他')", $yyyymm, $l_other_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ営業外費用その他の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア営業外費用その他')", $yyyymm, $l_other_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア営業外費用その他の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$l_other_b     = Uround(($l_other_l * $bimor_allo),0);    // バイモル営業外費用その他
$l_other_s     = Uround(($l_other_l * $ss_allo),0);       // 試験修理営業外費用その他
$l_other_ctoku = Uround(($l_other_c * $ctoku_allo),0);    // カプラ特注営業外費用その他

$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル営業外費用その他')", $yyyymm, $l_other_b);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("バイモル営業外費用その他の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修営業外費用その他')", $yyyymm, $l_other_s);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("試修営業外費用その他の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注営業外費用その他')", $yyyymm, $l_other_ctoku);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ特注営業外費用その他の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** 営業外費用合計 *****/
$nonope_l_sum   = ($risoku + $l_other);             // 全体営業外費用計
$nonope_l_c_sum = ($risoku_c + $l_other_c);         // カプラ営業外費用計
$nonope_l_l_sum = ($risoku_l + $l_other_l);         // リニア営業外費用計
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体営業外費用計')", $yyyymm, $nonope_l_sum);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("全体営業外収益合計の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ営業外費用計')", $yyyymm, $nonope_l_c_sum);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ営業外費用合計の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア営業外費用計')", $yyyymm, $nonope_l_l_sum);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア営業外費用合計の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/***** 経  常  利  益 *****/
$query = sprintf("select kin2 from pl_bs_summary where t_id='A' and t_row=10 and pl_bs_ym=%d", $yyyymm);
if (getUniResult($query,$current_profit) > 0) {
    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'AS全体経常利益')", $yyyymm, $current_profit);
    if (query_affected_trans($con, $query) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("全体経常利益の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] .= sprintf("経常利益の対照データがない<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
    ///// 期末材料仕掛品棚卸高・売上原価 等の調整があるため経常利益は計算で求める
$current_p   = ($ope_profit + ($nonope_p_sum) - ($nonope_l_sum));       // 全体経常利益 マイナスを考慮して()を使う
$current_p_c = ($ope_profit_c + ($nonope_p_c_sum) - ($nonope_l_c_sum)); // カプラ経常利益
$current_p_l = ($ope_profit_l + ($nonope_p_l_sum) - ($nonope_l_l_sum)); // リニア経常利益
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体経常利益')", $yyyymm, $current_p);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("全体経常利益の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ経常利益')", $yyyymm, $current_p_c);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("カプラ経常利益の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア経常利益')", $yyyymm, $current_p_l);
if (query_affected_trans($con, $query) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("リニア経常利益の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$current_sai = number_format($current_profit - $current_p);     // 経常利益のAS/400 との差異
$_SESSION["s_sysmsg"] .= sprintf("<font color='white'>経常利益の差額=%s</font><br>",$current_sai);


/////////// commit トランザクション終了
query_affected_trans($con, "commit");
$_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>第%d期 %d月の損益計算完了</font>",$ki,$tuki);
header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
exit();

