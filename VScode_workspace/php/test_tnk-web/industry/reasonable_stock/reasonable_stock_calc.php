<?php
//////////////////////////////////////////////////////////////////////////////
// 適正在庫数の計算 ロジック部                                              //
// Copyright(C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                 //
// Changed history                                                          //
// 2008/06/17 Created   reasonable_stock_calc.php                           //
//////////////////////////////////////////////////////////////////////////////
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));

//////////////// 認証チェック
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION["Auth"] <= 2) {
    $_SESSION['s_sysmsg'] = "認証されていないか認証期限が切れました。ログインからお願いします。";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}

/////// 年月の取得
if ($st_ym != 0) {
    $str_date = $st_ym - 300 . "31";
    $end_date = $st_ym + 1 . "01";
} else {
    return false;
}

/////// 在庫経歴より部品番号と出庫数合計を取得
$query = "
    SELECT   parts_no
            ,sum(stock_mv) 
            FROM parts_stock_history 
            WHERE ent_date > {$str_date} AND ent_date < {$end_date} 
            AND out_id = '2' AND den_kubun < '6' 
            GROUP BY parts_no
";
$res = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    return false;
} else {
    $num = count($field);
}

/////// 在庫経歴より移動表で計画No.が%で始まる物の出庫数合計を取得
$query_pl = "
    SELECT   parts_no
             ,sum(stock_mv) 
             FROM parts_stock_history 
             WHERE ent_date > {$str_date} AND ent_date < {$end_date} 
             AND out_id='2' AND den_kubun = '6' AND plan_no LIKE '$%%' ESCAPE '$' 
             GROUP BY parts_no
";
$res_pl = array();
if (($rows_pl = getResultWithField2($query_pl, $field_pl, $res_pl)) <= 0) {
    return false;
} else {
    $num_pl = count($field_pl);
}

/////// 出庫数合計に計画No.が％で始まる物の出庫合計を足す
for ($r=0; $r<$rows_pl; $r++) {
    for ($i=0; $i<$rows; $i++) {
        if($res_pl[$r][0] == $res[$i][0]) {
            $res[$i][1] = $res[$i][1] + $res_pl[$r][1];
        }
    }
}

/////// 出庫数合計÷３×２で適正在庫を計算
for ($r=0; $r<$rows; $r++) {
    $r_stock[$r] = round($res[$r][1] / 3 * 2, 0);
}

/////// parts_stock_historyの第１レコードのtnk_stockを取得
/////// tnk_stockが１以上の場合は2000年4月以前に在庫があったものとなるので
/////// 初回入庫日は第１レコードの値にする
for ($r=0; $r<$rows; $r++) {
    $parts_no = $res[$r][0];
    $query_in = "
        SELECT   parts_no
                 ,
                 CASE
                     WHEN tnk_stock > 0 THEN ent_date
                     WHEN nk_stock > 0 THEN ent_date
                     ELSE 0
                 END
                 FROM parts_stock_history 
                 WHERE parts_no='{$parts_no}' AND den_kubun <> '7'
                 ORDER BY ent_date ASC
                 LIMIT 1
    ";
    $res_in = array();
    if (($rows_in = getResultWithField2($query_in, $field_in, $res_in)) <= 0) {
        $first_date[$r] = 0;
    } else {
        if ($res_in[0][1] == 0) {
            $first_date[$r] = 0;
        } else {
            $first_date[$r] = $res_in[0][1];
        }
    }
}

/////// 在庫経歴より初回入庫日を取得（伝票区分5以下）
/////// 初回入庫日が登録済みの場合を除く
for ($r=0; $r<$rows; $r++) {
    if ($first_date[$r] == 0) {
        $parts_no = $res[$r][0];
        $query_in = "
            SELECT   parts_no
                     ,ent_date
                     FROM parts_stock_history 
                     WHERE (in_id='2' OR in_id='1') AND den_kubun < '6' 
                     AND parts_no='{$parts_no}'
                     ORDER BY ent_date ASC
                     LIMIT 1
        ";
        $res_in = array();
        if (($rows_in = getResultWithField2($query_in, $field_in, $res_in)) <= 0) {
            $first_date[$r] = 0;
        } else {
            $first_date[$r] = $res_in[0][1];
        }
    }
}

/////// 在庫経歴より伝票区分６の初回入庫日を取得（計画番号の頭が%のもののみ）
$query_in6 = "
    SELECT   parts_no
             ,min(ent_date) 
             FROM parts_stock_history 
             WHERE (in_id='2' OR in_id='1') AND den_kubun = '6' 
             AND plan_no LIKE '$%%' ESCAPE '$' 
             GROUP BY parts_no
             ORDER BY parts_no ASC
";
$res_in6 = array();
if (($rows_in6 = getResultWithField2($query_in6, $field_in6, $res_in6)) <= 0) {
    $num_in6 = 0;
} else {
    $num_in6 = count($field_in6);
}

/////// 伝票区分が6で計画番号の頭が%の物があるばあい初回入庫日を置換え
if ($num_in6 != 0) {
    for ($r=0; $r<$rows_in6; $r++) {
        for ($i=0; $i<$rows; $i++) {
            if ($res_in6[$r][0] == $res[$i][0]) {
                $first_date[$i] = $res_in6[$r][1];
            }
        }
    }
}

////////////// 適正在庫データの登録
for ($r=0; $r<$rows; $r++) {
    $query = sprintf("SELECT parts_no FROM reasonable_stock WHERE parts_no='%s' AND standard_ym=%d", $res[$r][0], $st_ym);
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE更新
        $query = sprintf("UPDATE reasonable_stock SET r_stock=%d, shipment_sum=%d, first_date=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE parts_no='%s' AND standard_ym=%d", $r_stock[$r], $res[$r][1], $first_date[$r], $_SESSION['User_ID'], $res[$r][0], $st_ym);                
        if (query_affected($query) <= 0) {
            return false;
        }
    } else {                                    // 登録なし INSERT 新規   
        $query = sprintf("INSERT INTO reasonable_stock (parts_no, standard_ym, r_stock, shipment_sum, first_date, last_date, last_user)
                            VALUES ('%s', %d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                            $res[$r][0], $st_ym, $r_stock[$r], $res[$r][1], $first_date[$r], $_SESSION['User_ID']);
        if (query_affected($query) <= 0) {
            return false;
        }
    }
}
return TRUE;    // 大文字小文字に依存しないが JavaScriptに合わせる。
?>

