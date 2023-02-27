<?php
//////////////////////////////////////////////////////////////////////////////
// カプラ改定仕切単価登録・照会画面 View部                                  //
// Copyright (C) 2010-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/05/13 Created  materialNew_ViewBody.php                             //
// 2011/03/04 手作業賃率を$rateでマスター化                                 //
// 2011/06/28 未登録が多かったため表示件数を減らして対処                    //
// 2021/09/21 総材料費の登録基準日を翌月の5日に変更                         //
// 2021/09/22 基準日を登録日から計画の完成日へ変更し翌月の1日へ             //
//            期間を過去３年から半年に変更（前回分以降）                    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT)
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');                // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');                // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');              // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK 全共通 MVC Controller Class

///// セッションのインスタンスを作成
$session = new Session();
///// リクエストのインスタンスを作成
$request = new Request();
$result  = new Result();
if ($request->get('recNo') != '') {
    $session->add_local('recNo', $request->get('recNo'));
    exit();
}
if ($request->get('page_keep') == '') $session->add_local('recNo', -1);
// access_log();                               // Script Name は自動取得
if($request->get('cost_input') != ''){
    $input_cost = 1;
} else {
    $input_cost = 0;
}

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(-1);                 // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(INDEX_INDUST, 999);         // site_index=30(生産メニュー) site_id=999(未定)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('総材料費の見直し確認用');
//////////// 表題の設定
$menu->set_caption('総材料費の見直し確認用');
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須
//////////// 自分をフレーム定義に変える
$menu->set_self(INDUST . 'material_new/materialNew_Main.php');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('引当構成表の表示',   INDUST . 'material/allo_conf_parts_view.php');
$menu->set_action('引当構成表の表示',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
$menu->set_action('総材料費の登録',     INDUST . 'material/material_entry/materialCost_entry_main.php');
$menu->set_action('総材料費の履歴',     INDUST . 'material/materialCost_view_assy.php');
$menu->set_action('総材料費明細',       INDUST . 'material/materialCost_view.php');
$menu->set_action('仕切掛率の登録',       INDUST . 'material_new/materialPartsCredit_Main.php');
//////////// リターンアドレスへのGETデーターセット
$menu->set_retGET('page_keep', 'on');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = $menu->set_useNotCache('mtcheck');

//////////// 旧製品や材料費が旧のものを対象外にする
oldProductRegister($request, $session);

//////////// 一頁の行数
define('PAGE', '200');      // とりあえず

////// 呼出元の保存
$product_master_referer = 'http:' . WEB_HOST . 'industry/material_new/materialNew_Main.php';        // 呼出もとのURLをセッションに保存
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // 呼出もとのURLをセッションに保存
$session->add('product_master_referer', $product_master_referer);

//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['ind_ym'])) {
    $ind_ym = $_SESSION['ind_ym']; 
}
$ind_y   = substr($ind_ym,0,4);
$ind_m   = substr($ind_ym,4,2);
// 過去３年間
/*
$str_ymd = $ind_ym - 300;
$str_ymd = $str_ymd . '01';
$end_ymd = $ind_ym . '31';
*/
// 2022/03からこっち 過去半年間の期間で 202103 202003 202008 

$end_ymd = $ind_ym . '31';
if ($ind_m < 6) {
    $str_ymd = $ind_ym - 100;
    $str_ymd = $ind_ym + 6;
    $str_ymd = $str_ymd . '01';
} else {
    $str_ymd = $ind_ym - 5;
    $str_ymd = $str_ymd . '01';
}

// 総材料費取得日付設定
if (substr($ind_ym,4,2)!=12) {
    $chk_ymd  = $ind_ym + 1;
    //$cost_ymd = $chk_ymd . '15';
    //$cost_ymd = $chk_ymd . '05';
    $cost_ymd = $chk_ymd . '01';
    $chk_ymd  = $chk_ymd . '01';
    
} else {
    $chk_ymd = $ind_ym + 100;
    $chk_ymd = $chk_ymd - 11;
    //$cost_ymd = $chk_ymd . '15';
    //$cost_ymd = $chk_ymd . '05';
    $cost_ymd = $chk_ymd . '01';
    $chk_ymd = $chk_ymd . '01';
}
if ($ind_ym < 200710) {
    $rate = 25.60;  // カプラ標準 2007/10/01価格改定以前
} elseif ($ind_ym < 201104) {
    $rate = 57.00;  // カプラ標準 2007/10/01価格改定以降
} else {
    $rate = 45.00;  // カプラ標準 2011/04/01価格改定以降
}

//////////// 対象データの取得
$query = "
    SELECT
        u.assyno                    AS 製品番号
        ,
        trim(substr(m.midsc,1,30))  AS 製品名
        ,
        count(u.assyno)             AS 件数
        ,
        (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header as c LEFT OUTER JOIN assembly_completion_history AS hist USING(plan_no) WHERE comp_date < {$cost_ymd} AND c.assy_no = u.assyno ORDER BY c.assy_no DESC, regdate DESC LIMIT 1)
                                    AS 最新総材料費
        ,
        (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header as c LEFT OUTER JOIN assembly_completion_history AS hist USING(plan_no) WHERE comp_date < {$cost_ymd} AND c.assy_no = u.assyno ORDER BY c.assy_no DESC, regdate DESC LIMIT 1)
                                    AS 総材料登録日
        ,
        (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno LIMIT 1)
                                    AS 最新仕切単価
        ,
        (SELECT to_char(regdate, 'FM9999/99/99') FROM sales_price_nk WHERE parts_no = u.assyno LIMIT 1)
                                    AS 仕切登録日
        ---------------- リスト外 -----------------
        ,
        (SELECT plan_no FROM material_cost_header as c LEFT OUTER JOIN assembly_completion_history AS hist USING(plan_no) WHERE comp_date < {$cost_ymd} AND c.assy_no = u.assyno ORDER BY c.assy_no DESC, regdate DESC LIMIT 1)
                                    AS 総材料計画
        ,
        (SELECT a_rate FROM material_cost_header as c LEFT OUTER JOIN assembly_completion_history AS hist USING(plan_no) WHERE comp_date < {$cost_ymd} AND c.assy_no = u.assyno ORDER BY c.assy_no DESC, regdate DESC LIMIT 1)                     AS 自動機賃率,      --8
        (SELECT a_time FROM material_cost_header as c LEFT OUTER JOIN assembly_completion_history AS hist USING(plan_no) WHERE comp_date < {$cost_ymd} AND c.assy_no = u.assyno ORDER BY c.assy_no DESC, regdate DESC LIMIT 1)                     AS 自動機工数,      --9
        (SELECT m_time FROM material_cost_header as c LEFT OUTER JOIN assembly_completion_history AS hist USING(plan_no) WHERE comp_date < {$cost_ymd} AND c.assy_no = u.assyno ORDER BY c.assy_no DESC, regdate DESC LIMIT 1)                     AS 手作業工数,      --10
        (SELECT g_time FROM material_cost_header as c LEFT OUTER JOIN assembly_completion_history AS hist USING(plan_no) WHERE comp_date < {$cost_ymd} AND c.assy_no = u.assyno ORDER BY c.assy_no DESC, regdate DESC LIMIT 1)                     AS 外注工数         --11
    FROM
          hiuuri AS u
    LEFT OUTER JOIN
          assembly_schedule AS a
    ON (u.計画番号 = a.plan_no)
    LEFT OUTER JOIN
          miitem AS m
    ON (u.assyno = m.mipn)
    LEFT OUTER JOIN
          material_old_product AS mate
    ON (u.assyno = mate.assy_no)
    WHERE 計上日 >= {$str_ymd} AND 計上日 <= {$end_ymd} AND 事業部 = 'C' AND (note15 NOT LIKE 'SC%%' OR note15 IS NULL) AND datatype='1'
        AND mate.assy_no IS NULL
        -- これを追加すれば自動機の登録があるもの AND (SELECT a_rate FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) > 0
    GROUP BY u.assyno, m.midsc
    ORDER BY u.assyno ASC
    OFFSET 0 LIMIT 5000
";
if ( ($rows=getResult2($query, $res)) <= 0) {
    $session->add('s_sysmsg', '対象データがありません！');
}

////////// ○×の基準年月日
$chk_y = substr($chk_ymd, 0, 4);
$chk_m = substr($chk_ymd, 4, 2);
$chk_d = substr($chk_ymd, 6, 2);
$chk_ymdt = $chk_y . $chk_m . $chk_d;
define('LIMIT_YMD', $chk_ymdt);

////////// 枝版が一つ進んだ製品があるかチェック
function newAssyNoCheck($assyNo)
{
    $eda = substr($assyNo, 8, 1); $eda += 1;
    $assyNew = substr($assyNo, 0, 8) . $eda;
    $query = "
        SELECT comp_date FROM assembly_completion_history WHERE assy_no = '{$assyNew}' LIMIT 1
    ";
    if (getUniResult($query, $check) <= 0) {
        return false;
    } else {
        return true;
    }
}

////////// 旧製品や材料費が旧登録のままの製品を登録し対象外とする
function oldProductRegister($request, $session)
{
    if ($request->get('assyNo') == '') return;
    if ($request->get('del') != 'yes') return;
    $assyNo = $request->get('assyNo');
    if ($session->get('User_ID') == '010561' || $session->get('User_ID') == '300144') {
        $query = "SELECT midsc FROM miitem WHERE mipn = '{$assyNo}'";
        if (getUniResult($query, $res) > 0) {
            $uid = $_SERVER['REMOTE_ADDR'] . ' ' . gethostbyaddr($_SERVER['REMOTE_ADDR']) . ' ' . $session->get('User_ID');
            $note = $request->get('note');
            if (newAssyNoCheck($assyNo)) $note = '旧製品'; else $note = '対象外';
            $sql = "INSERT INTO material_old_product (assy_no, note, last_date, last_user) values('{$assyNo}', '{$note}', CURRENT_TIMESTAMP, '{$uid}')";
            $query = "SELECT assy_no FROM material_old_product WHERE assy_no = '{$assyNo}'";
            if (getUniResult($query, $check) > 0) {
                $session->add('s_sysmsg', "{$assyNo} {$res} は既に対象外になっています！");
            } elseif (query_affected($sql) > 0) {
                $session->add('s_sysmsg', "{$assyNo} {$res} を対象外にしました。");
            } else {
                $session->add('s_sysmsg', "{$assyNo} {$res} を対象外の登録に失敗しました。！");
            }
        } else {
            $session->add('s_sysmsg', "製品番号 [{$assyNo}] が正しくありません！");
        }
    } else {
        $session->add('s_sysmsg', '対象外の登録をする権限がありません！');
    }
}

////////////// 計画一覧を取得し最新の物が未登録かどうかをチェック
function inputCheck($assyNo)
{
    $assy = $assyNo;
    $query = "SELECT CASE
                        WHEN to_char(mate.regdate, 'HH24:MI:SS') = '00:00:00' THEN ''
                        WHEN mate.plan_no IS NULL THEN '未登録'
                        ELSE ''
                     END                                                       AS 登録
                FROM
                    assembly_completion_history AS hist
                LEFT OUTER JOIN
                    material_cost_header AS mate USING(plan_no)
                LEFT OUTER JOIN
                    assembly_schedule AS asse USING(plan_no)
                LEFT OUTER JOIN
                    miitem AS item ON (hist.assy_no=item.mipn)
                WHERE hist.assy_no LIKE '{$assy}' -- '{$assy}'
                ORDER BY hist.assy_no DESC, hist.comp_date DESC --計画日 DESC
                LIMIT 1";
    $rows=getResult2($query, $res_i);
    if ($res_i[0][0] == '') {
        return false;
    } else {
        return true;
    }
}


function comp_date($assyNo, $endymd) // 掛率計算
{
    $assy = $assyNo;
    $query = "SELECT hist.comp_date                               AS 初回完成日
                FROM
                    assembly_completion_history AS hist
                LEFT OUTER JOIN
                    material_cost_header AS mate USING(plan_no)
                LEFT OUTER JOIN
                    assembly_schedule AS asse USING(plan_no)
                LEFT OUTER JOIN
                    miitem AS item ON (hist.assy_no=item.mipn)
                WHERE hist.assy_no LIKE '{$assy}' -- '{$assy}'
                ORDER BY hist.assy_no DESC, hist.comp_date ASC --計画日 DESC
                LIMIT 1";
    $rows       = getResult2($query, $res_i);
    $comp_year  = substr($res_i[0][0], 0, 4);
    $comp_month = substr($res_i[0][0], 4, 2);
    if ($comp_month < 4) {
        $comp_year  = $comp_year + 1;
    } else {
        $comp_year  = $comp_year + 2;
    }
    $comp_date = $comp_year . '03' . '31';
    if ($comp_date <= $endymd) {
        return false;
    } else {
        return true;
    }
}
function ger_credit($assyNo)
{
    $per  = 0;
    $assy = $assyNo;
    $res_p = array();
    $query = "SELECT credit_per                               AS 掛率
                FROM
                    parts_credit_per
                WHERE parts_no = '{$assy}'
                LIMIT 1";
    if ($rows_p=getResult2($query, $res_p) > 0) {
        $per  = $res_p[0][0];
    }
    return $per;
}

function input_credit($result, $session)
{
    $alert = '';
    $up_num = 0;
    $in_num = 0;
    $ms_num = 0;
    $ok_num = 0;
    if (getCheckAuthority(22)) {                    // 認証チェック
        $res_c   = array();
        $res_c   = $result->get_array2('res_c');
        $num     = $result->get('num');
        $in_date = $result->get('in_date');
        $in_date = $in_date - 1 + 1;
        for ($r=0; $r<$num; $r++) {
            $query = sprintf("SELECT parts_no FROM sales_price_new WHERE parts_no='%s' and cost_ym=%d", $res_c[$r][0], $in_date);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
                $query = sprintf("UPDATE sales_price_new SET cost_new='%s', credit_per='%s', new_price='%s', plan_no='%s', last_date=CURRENT_TIMESTAMP, last_host='%s' WHERE parts_no='%s' and cost_ym=%d", $res_c[$r][3], $res_c[$r][12], $res_c[$r][13], $res_c[$r][7], $_SESSION['User_ID'], $res_c[$r][0], $in_date);
                if (query_affected($query) <= 0) {
                    $ms_num++;
                } else {
                    $up_num++;
                    $ok_num++;
                }
            } else {                                    // 登録なし INSERT 新規   
                $query = sprintf("INSERT INTO sales_price_new (cost_ym, parts_no, cost_new, credit_per, new_price, plan_no, last_date, last_host)
                                  VALUES (%d, '%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                    $in_date, $res_c[$r][0], $res_c[$r][3], $res_c[$r][12], $res_c[$r][13], $res_c[$r][7], $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                    $ms_num++;
                } else {
                    $in_num++;
                    $ok_num++;
                }
            }
        }
        $alert  = $in_date . '改定仕切の更新: ' . $ok_num . '/' . $num . '件登録しました。';
        $alert .= $in_date . '改定仕切の更新: ' . $in_num . '/' . $num . '件 追加 ';
        $alert .= $in_date . '改定仕切の更新: ' . $up_num . '/' . $num . '件 変更 ';
        $session->add('s_sysmsg', $alert);
        //$session->add('s_sysmsg', '{$in_date} 改定仕切の更新: {$in_num}/{$num} 件 追加 ');
        //$session->add('s_sysmsg', '{$in_date} 改定仕切の更新: {$up_num}/{$num} 件 変更 ');
        
        //$alert = "{$in_date} 改定仕切の更新: {$ok_num}/{$num} 件登録しました。";
        //$alert = "{$in_date} 改定仕切の更新: {$ok_num}/{$num} 件登録しました。\n";
        //$alert .= "{$in_date} 改定仕切の更新: {$in_num}/{$num} 件 追加 \n";
        //$alert .= "{$in_date} 改定仕切の更新: {$up_num}/{$num} 件 変更 \n";
    } else {                                        // 権限なしエラー
        $session->add('s_sysmsg', '編集権限がありません。必要な場合には、担当者に連絡して下さい。');
        //$alert = "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
    }
}

///////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<link rel='stylesheet' href='materialNew.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<!-- <script type='text/javascript' src='materialNew.js?<?php echo $uniq ?>'></script> -->

<script type='text/javascript'>
<!--
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
 -->

<style type="text/css">
<!--
body {
    background-image:   none;
    overflow-x:         hidden;
    overflow-y:         scroll;
}
-->
</style>
</head>
<body>
    <center>
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <?php
            $credit_chk = 0;    // 掛率未登録チェック用
            for ($r=0; $r<$rows; $r++) {
                ///// ダブルクリックで対象外
                $oldProduct = "onDblClick='if (confirm(\"対象外にします。よろしいですか？\")) { baseJS.Ajax(\"materialCheck_ViewBody.php?recNo={$r}\");location.replace(\"materialNew_ViewBody.php?assyNo=" . urlencode($res[$r][0]) . "&del=yes&page_keep=on&id={$uniq}#mark\");}'";
                if ($session->get_local('recNo') == $r) {
                    echo "<tr style='background-color:#ffffc6;' {$oldProduct}>\n";
                    echo "    <td class='winbox pt10b' width=' 4%' nowrap align='right'><a name='mark' style='color:black;'>", ($r + 1), "</a></td>\n";
                } elseif ($res[$r][4] < LIMIT_YMD) {
                    echo "<tr style='background-color:#e6e6e6;' {$oldProduct}>\n";
                    echo "    <td class='winbox pt10b' width=' 4%' nowrap align='right'>", ($r + 1), "</td>\n";
                } else {
                    echo "<tr {$oldProduct}>\n";
                    echo "    <td class='winbox pt10b' width=' 4%' nowrap align='right'>", ($r + 1), "</td>\n";
                }
                echo "<td class='winbox pt11b' width='10%' align='center'><a href='JavaScript:baseJS.Ajax(\"materialCheck_ViewBody.php?recNo={$r}\");location.replace(\"{$menu->out_action('総材料費の履歴')}?assy=", urlencode($res[$r][0]), "&material=1\")' target='_parent' style='text-decoration:none;'>{$res[$r][0]}</a></td>\n";
                echo "<td class='winbox pt11 ' width='26%' align='left'  >{$res[$r][1]}</td>\n";
                //echo "<td class='winbox pt11 ' width=' 7%' align='right' >{$res[$r][8]}</td>\n";
                //echo "<td class='winbox pt11 ' width=' 7%' align='right' >{$res[$r][9]}</td>\n";
                //echo "<td class='winbox pt11 ' width=' 7%' align='right' >{$res[$r][10]}</td>\n";
                //echo "<td class='winbox pt11 ' width=' 7%' align='right' >{$res[$r][11]}</td>\n";
                echo "<td class='winbox pt11 ' width='10%' align='right' ><a href='JavaScript:baseJS.Ajax(\"materialNew_ViewBody.php?recNo={$r}\");location.replace(\"{$menu->out_action('総材料費明細')}?plan_no=", urlencode("{$res[$r][7]}"), "&assy_no=", urlencode($res[$r][0]), "&material=1\")' target='_parent' style='text-decoration:none;'>", number_format($res[$r][3], 2), "</a></td>\n";
                if (substr($res[$r][7], 0, 1) != 'Z') {
                    echo "<td class='winbox pt11b' width='11%' align='center'>{$res[$r][4]}</td>\n";
                } else {
                    echo "<td class='winbox pt11b' width='11%' align='center'><a href='JavaScript:baseJS.Ajax(\"materialCheck_ViewBody.php?recNo={$r}\");location.replace(\"{$menu->out_action('総材料費明細')}?plan_no=", urlencode("{$res[$r][7]}"), "&assy_no=", urlencode($res[$r][0]), "&material=1\")' target='_parent' style='text-decoration:none;'>{$res[$r][4]}</a></td>\n";
                }
                if ($res[$r][4] < LIMIT_YMD) {
                    if (newAssyNoCheck($res[$r][0])) {
                        //echo "<td class='winbox pt11b' width=' 5%' align='center'>旧</td>\n";
                        if (comp_date($res[$r][0], $end_ymd)) {
                            echo "<td class='winbox pt11b' width=' 5%' align='center'>1.18</td>\n";
                            $res[$r][12] = 1.18;
                        } else {
                            $res[$r][12] = ger_credit($res[$r][0]);
                            if ($res[$r][12] == 0) {
                                $credit_chk = $credit_chk + 1;
                                echo "<td class='winbox pt11b' width=' 5%' align='center'><a href='JavaScript:baseJS.Ajax(\"materialNewLinear_ViewBody.php?recNo={$r}\");location.replace(\"{$menu->out_action('仕切掛率の登録')}?assy_no=", urlencode($res[$r][0]), "&material=1\")' target='_parent' style='text-decoration:none;'>未</a></td>\n";
                            } else {
                                echo "<td class='winbox pt11b' width=' 5%' align='center'>{$res[$r][12]}</td>\n";
                            }
                        }
                    } else {
                        if (comp_date($res[$r][0], $end_ymd)) {
                            echo "<td class='winbox pt11b' width=' 5%' align='center'>1.18</td>\n";
                            $res[$r][12] = 1.18;
                        } else {
                            $res[$r][12] = ger_credit($res[$r][0]);
                            if ($res[$r][12] == 0) {
                                $credit_chk = $credit_chk + 1;
                                echo "<td class='winbox pt11b' width=' 5%' align='center'><a href='JavaScript:baseJS.Ajax(\"materialNewLinear_ViewBody.php?recNo={$r}\");location.replace(\"{$menu->out_action('仕切掛率の登録')}?&assy_no=", urlencode($res[$r][0]), "&material=1\")' target='_parent' style='text-decoration:none;'>未</a></td>\n";
                            } else {
                                echo "<td class='winbox pt11b' width=' 5%' align='center'>{$res[$r][12]}</td>\n";
                            }
                        }
                        //if (inputCheck($res[$r][0])) {
                        //    echo "<td class='winbox pt11b' width=' 5%' align='center'>△</td>\n";
                        //} else {
                        //    echo "<td class='winbox pt11b' width=' 5%' align='center'>×</td>\n";
                        //}
                    }
                } else {
                    if (comp_date($res[$r][0], $end_ymd)) {
                        echo "<td class='winbox pt11b' width=' 5%' align='center'>1.18</td>\n";
                        $res[$r][12] = 1.18;
                    } else {
                        $res[$r][12] = ger_credit($res[$r][0]);
                        if ($res[$r][12] == 0) {
                            $credit_chk = $credit_chk + 1;
                            echo "<td class='winbox pt11b' width=' 5%' align='center'><a href='JavaScript:baseJS.Ajax(\"materialNewLinear_ViewBody.php?recNo={$r}\");location.replace(\"{$menu->out_action('仕切掛率の登録')}?&assy_no=", urlencode($res[$r][0]), "&material=1\")' target='_parent' style='text-decoration:none;'>未</a></td>\n";
                        } else {
                            echo "<td class='winbox pt11b' width=' 5%' align='center'>{$res[$r][12]}</td>\n";
                        }
                    }
                }
                $res[$r][13] = round(($res[$r][3] * $res[$r][12]),2);
                echo "<td class='winbox pt11 ' width=' 8%' align='right' >", number_format($res[$r][13], 2), "</td>\n";
                //echo "<td class='winbox pt11 ' width=' 8%' align='right' >", number_format($res[$r][5], 2), "</td>\n";
                //echo "<td class='winbox pt11b' width='12%' align='center'>{$res[$r][6]}</td>\n";
                echo "</tr>\n";
            }
            echo "<tr>\n";
            echo "    <td class='winbox pt11b' colspan='7' align='right'>\n";
            if ($credit_chk == 0 ) {
                $result->add_array2('res_c', $res);
                $result->add('num', $rows);
                $result->add('in_date', $ind_ym);
                if($input_cost == 1){
                    input_credit($result, $session);
                }
                echo "<form method='post' action=''>";
                echo "<input type='submit' class='pt11b' name='cost_input' value='最新仕切の登録' onclick='return confirm(\"{$ind_y}年{$ind_m}月末の仕切単価を登録します。よろしいですか？\");'>";
                echo "</form>";
                echo "<a href='materialNew_csv.php?indym={$ind_ym}&costymd={$cost_ymd}&strymd={$str_ymd}&endymd={$end_ymd}'>";
                echo "CSV出力";
                echo "</a>";
            } else {
                echo "未登録あり <font color='red'>{$credit_chk} </font>件";
            } 
            echo "  </td>\n";
            echo "</tr>\n";
            ?>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
