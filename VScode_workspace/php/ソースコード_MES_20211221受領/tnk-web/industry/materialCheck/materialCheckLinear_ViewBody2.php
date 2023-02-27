<?php
//////////////////////////////////////////////////////////////////////////////
// リニア仕切見直し 総材料費登録確認用 照会メニュー                         //
// Copyright (C) 2008-2009 Norihisa.Ohya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2008/02/12 Created  materialCheckLinear_ViewBody2.php                    //
//                     (materialCheck_ViewBody.phpを改造                    //
// 2008/02/27 対象期間を2008/01/31に変更                                    //
//            ○×の基準年月日を2007/12/01に変更                            //
//            Uround((m_time + g_time) * assy_rate, 2) → 手作業賃率を変更  //
//            Uround((m_time + g_time) * 57.00, 2) シミュレーション         //
// 2008/02/29 対象期間を2008/03/31に変更                                    //
//            ○×の基準年月日を2008/02/01に変更                            //
// 2008/03/08 ○×の基準年月日を2008/03/01に変更                            //
//            最新の総材料費で未登録がある場合は△を表示するように変更      //
// 2008/03/12 生産計画無しのチェックを計画Noの先頭が'ZZ'の物から'Z'に変更   //
//            （同時に2人以上が入力した時の対応で計画番号を変更した為）     //
// 2008/03/24 生産計画無しのチェックを'Z'に変更した時に、抜き出す文字を先頭 //
//            2文字のままだったのを1文字に訂正                         大谷 //
// 2008/04/14 Uround((m_time + g_time) * 37.00, 2) に変更              大谷 //
// 2008/05/20 Uround((m_time + g_time) * 43.24, 2) に変更              大谷 //
// 2008/07/24 Uround((m_time + g_time) * 53.24, 2) に変更              大谷 //
// 2008/07/24 Uround((m_time + g_time) * 43.24, 2) に変更                   //
//            対象期間を2008/06/31に変更                               大谷 //
// 2008/09/04 Uround((m_time + g_time) * 44.00, 2) に変更              大谷 //
// 2008/09/09 掛率計算を年度末を基準に２年になるように変更             大谷 //
// 2009/02/17 計上日を2009年1月までに変更                              大谷 //
// 2009/03/12 手作業工数・外注工数を表示するように変更(現在コメント化) 大谷 //
// 2009/09/10 計上日を2009年8月までに変更                              大谷 //
// 2009/11/27 計上日を2009年11月までに変更                             大谷 //
// 2010/03/10 計上日を2010年2月までに変更                              大谷 //
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
if ($request->get('recNo') != '') {
    $session->add_local('recNo', $request->get('recNo'));
    exit();
}
if ($request->get('page_keep') == '') $session->add_local('recNo', -1);
// access_log();                               // Script Name は自動取得

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
$menu->set_self(INDUST . 'materialCheck/materialCheckLinear_Main2.php');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('引当構成表の表示',   INDUST . 'material/allo_conf_parts_view.php');
$menu->set_action('引当構成表の表示',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
$menu->set_action('総材料費の登録',     INDUST . 'material/material_entry/materialCost_entry_main.php');
$menu->set_action('総材料費の履歴',     INDUST . 'material/materialCost_view_assy.php');
$menu->set_action('総材料費明細',       INDUST . 'material/materialCost_view.php');
//////////// リターンアドレスへのGETデーターセット
$menu->set_retGET('page_keep', 'on');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = $menu->set_useNotCache('mtcheck');

//////////// 旧製品や材料費が旧のものを対象外にする
oldProductRegister($request, $session);

//////////// 一頁の行数
define('PAGE', '200');      // とりあえず

//////////// 対象データの取得
$query = "
    SELECT
        u.assyno                    AS 製品番号
        ,
        trim(substr(m.midsc,1,30))  AS 製品名
        ,
        count(u.assyno)             AS 件数
        ,
        (SELECT sum_price + Uround((m_time + g_time) * 44.00, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < 20100304 AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS 最新総材料費
        ,
        (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < 20100304 AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS 総材料登録日
        ,
        (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno LIMIT 1)
                                    AS 最新仕切単価
        ,
        (SELECT to_char(regdate, 'FM9999/99/99') FROM sales_price_nk WHERE parts_no = u.assyno LIMIT 1)
                                    AS 仕切登録日
        ---------------- リスト外 -----------------
        ,
        (SELECT plan_no FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS 総材料計画
        ,
        (SELECT a_rate FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < 20100304 AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 自動機賃率,      --8
        (SELECT a_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < 20100304 AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 自動機工数,      --9
        (SELECT m_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < 20100304 AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 手作業工数,      --10
        (SELECT g_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < 20100304 AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 外注工数         --11
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
    WHERE 計上日 >= 20070201 AND 計上日 <= 20100231 AND 事業部 = 'L' AND datatype='1'
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
define('LIMIT_YMD', '2010/03/01');

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

function comp_date($assyNo)
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
    $rows=getResult2($query, $res_i);
    $comp_year  = substr($res_i[0][0], 0, 4);
    $comp_month = substr($res_i[0][0], 4, 2);
    if ($comp_month < 4) {
        $comp_year  = $comp_year + 1;
    } else {
        $comp_year  = $comp_year + 2;
    }
    $comp_date = $comp_year . '03' . '31';
    if ($comp_date <= 20100231) {
        return false;
    } else {
        return true;
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
<link rel='stylesheet' href='materialCheck.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<!-- <script type='text/javascript' src='materialCheck.js?<?php echo $uniq ?>'></script> -->

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
            for ($r=0; $r<$rows; $r++) {
                ///// ダブルクリックで対象外
                $oldProduct = "onDblClick='if (confirm(\"対象外にします。よろしいですか？\")) { baseJS.Ajax(\"materialCheckLinear_ViewBody2.php?recNo={$r}\");location.replace(\"materialCheckLinear_ViewBody2.php?assyNo=" . urlencode($res[$r][0]) . "&del=yes&page_keep=on&id={$uniq}#mark\");}'";
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
                echo "<td class='winbox pt11b' width='10%' align='center' nowrap><a href='JavaScript:baseJS.Ajax(\"materialCheckLinear_ViewBody2.php?recNo={$r}\");location.replace(\"{$menu->out_action('総材料費の履歴')}?assy=", urlencode($res[$r][0]), "&material=1\")' target='_parent' style='text-decoration:none;'>{$res[$r][0]}</a></td>\n";
                echo "<td class='winbox pt11 ' width='26%' align='left'  >{$res[$r][1]}</td>\n";
                echo "<td class='winbox pt11 ' width=' 7%' align='right' >{$res[$r][8]}</td>\n";
                echo "<td class='winbox pt11 ' width=' 7%' align='right' >{$res[$r][9]}</td>\n";
                //echo "<td class='winbox pt11 ' width=' 7%' align='right' >{$res[$r][10]}</td>\n";
                //echo "<td class='winbox pt11 ' width=' 7%' align='right' >{$res[$r][11]}</td>\n";
                echo "<td class='winbox pt11 ' width='10%' align='right' ><a href='JavaScript:baseJS.Ajax(\"materialCheckLinear_ViewBody2.php?recNo={$r}\");location.replace(\"{$menu->out_action('総材料費明細')}?plan_no=", urlencode("{$res[$r][7]}"), "&assy_no=", urlencode($res[$r][0]), "&material=1\")' target='_parent' style='text-decoration:none;'>", number_format($res[$r][3], 2), "</a></td>\n";
                if (substr($res[$r][7], 0, 1) != 'Z') {
                    echo "<td class='winbox pt11b' width='11%' align='center'>{$res[$r][4]}</td>\n";
                } else {
                    echo "<td class='winbox pt11b' width='11%' align='center'><a href='JavaScript:baseJS.Ajax(\"materialCheckLinear_ViewBody2.php?recNo={$r}\");location.replace(\"{$menu->out_action('総材料費明細')}?plan_no=", urlencode("{$res[$r][7]}"), "&assy_no=", urlencode($res[$r][0]), "&material=1\")' target='_parent' style='text-decoration:none;'>{$res[$r][4]}</a></td>\n";
                }
                if ($res[$r][4] < LIMIT_YMD) {
                    if (newAssyNoCheck($res[$r][0])) {
                        //echo "<td class='winbox pt11b' width=' 5%' align='center'>旧</td>\n";
                        if (comp_date($res[$r][0])) {
                            echo "<td class='winbox pt11b' width=' 5%' align='center'>1.18</td>\n";
                        } else {
                            echo "<td class='winbox pt11b' width=' 5%' align='center'>旧</td>\n";
                        }
                    } else {
                        if (comp_date($res[$r][0])) {
                            echo "<td class='winbox pt11b' width=' 5%' align='center'>1.18</td>\n";
                        } else {
                            if (inputCheck($res[$r][0])) {
                                echo "<td class='winbox pt11b' width=' 5%' align='center'>△</td>\n";
                            } else {
                                echo "<td class='winbox pt11b' width=' 5%' align='center'>×</td>\n";
                            }
                        }
                        //if (inputCheck($res[$r][0])) {
                        //    echo "<td class='winbox pt11b' width=' 5%' align='center'>△</td>\n";
                        //} else {
                        //    echo "<td class='winbox pt11b' width=' 5%' align='center'>×</td>\n";
                        //}
                    }
                } else {
                    if (comp_date($res[$r][0])) {
                        echo "<td class='winbox pt11b' width=' 5%' align='center'>1.18</td>\n";
                    } else {
                        echo "<td class='winbox pt11b' width=' 5%' align='center'>○</td>\n";
                    }
                    //echo "<td class='winbox pt11b' width=' 5%' align='center'>○</td>\n";
                }
                echo "<td class='winbox pt11 ' width=' 8%' align='right' >", number_format($res[$r][5], 2), "</td>\n";
                echo "<td class='winbox pt11b' width='12%' align='center'>{$res[$r][6]}</td>\n";
                echo "</tr>\n";
            }
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
