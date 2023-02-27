<?php
//////////////////////////////////////////////////////////////////////////////
// リニア仕切見直し １月総材料費手動登録確認用 照会メニュー                 //
// Copyright (C) 2008 Norihisa.Ohya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2008/02/12 Created  materialCheckLinear_ViewBody.php                     //
//                     (materialCheck_ViewBody.phpを改造                    //
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
$menu->set_self(INDUST . 'materialCheck/materialCheckLinear_Main.php');
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

//////////// 一頁の行数
define('PAGE', '200');      // とりあえず

//////////// 対象データの取得
$query = "
    SELECT
        u.assyno                    AS 製品番号
        ,
        trim(substr(m.midsc,1,30))  AS 製品名
        ,
        u.計画番号                  AS 計画番号
        ,
        (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no = u.計画番号)
                                    AS 総材料費
        ,
        (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS 総材料登録日
        ,
        (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno LIMIT 1)
                                    AS 最新仕切単価
        ,
        (SELECT to_char(regdate, 'FM9999/99/99') FROM sales_price_nk WHERE parts_no = u.assyno LIMIT 1)
                                    AS 仕切登録日
        ,
        CASE
            WHEN to_char(mate.regdate, 'HH24:MI:SS') = '00:00:00' THEN '自動'
            ELSE '手動'
        END                                                       AS 登録
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
    WHERE 計上日 >= 20080101 AND 計上日 <= 20080131 AND 事業部 = 'L' AND datatype='1'
    ORDER BY u.assyno ASC
    OFFSET 0 LIMIT 5000
";
if ( ($rows=getResult2($query, $res)) <= 0) {
    $session->add('s_sysmsg', '対象データがありません！');
}

////////// ○×の基準年月日
define('LIMIT_YMD', '2008/01/01');

////////// 総材料費が手動で入力されているかチェック
function manualInputCheck($planNo)
{
    $query = "
        SELECT 
            CASE
                WHEN to_char(regdate, 'HH24:MI:SS') = '00:00:00' THEN '自動'
                ELSE '手動'
            END
        FROM material_cost_header
        WHERE plan_no = '{$planNo}' LIMIT 1
    ";
    if (getResult2($query, $res) <= 0) {
        return false;
    } else {
        if ($res[0][0] == '手動') {
            return true;
        } else {
            $query = "
                SELECT 
                    CASE
                        WHEN to_char(last_date, 'YYYY/MM/DD') > '2008/02/11' THEN '手動'
                        ELSE '自動'
                    END
                FROM material_cost_header
                WHERE plan_no = '{$planNo}' LIMIT 1
            ";
            if (getResult2($query, $res) <= 0) {
                return false;
            } else {
                if ($res[0][0] == '手動') {
                    return true;
                } else {
                    return false;
                }
            }
        }
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
                $oldProduct = "onDblClick='if (confirm(\"対象外にします。よろしいですか？\")) { baseJS.Ajax(\"materialCheck_ViewBody.php?recNo={$r}\");location.replace(\"materialCheck_ViewBody.php?assyNo=" . urlencode($res[$r][0]) . "&del=yes&page_keep=on&id={$uniq}#mark\");}'";
                if ($session->get_local('recNo') == $r) {
                    echo "<tr style='background-color:#ffffc6;' {$oldProduct}>\n";
                    echo "    <td class='winbox pt10b' width=' 5%' nowrap align='right'><a name='mark' style='color:black;'>", ($r + 1), "</a></td>\n";
                } elseif (!manualInputCheck($res[$r][2])) {
                    echo "<tr style='background-color:#e6e6e6;' {$oldProduct}>\n";
                    echo "    <td class='winbox pt10b' width=' 5%' nowrap align='right'>", ($r + 1), "</td>\n";
                } else {
                    echo "<tr {$oldProduct}>\n";
                    echo "    <td class='winbox pt10b' width=' 5%' nowrap align='right'>", ($r + 1), "</td>\n";
                }
                echo "<td class='winbox pt11b' width=' 8%' align='center'><a href='JavaScript:baseJS.Ajax(\"materialCheck_ViewBody.php?recNo={$r}\");location.replace(\"{$menu->out_action('総材料費の履歴')}?assy=", urlencode($res[$r][0]), "&material=1\")' target='_parent' style='text-decoration:none;'>{$res[$r][0]}</a></td>\n";
                echo "<td class='winbox pt11 ' width='35%' align='left'  >{$res[$r][1]}</td>\n";
                echo "<td class='winbox pt11 ' width=' 7%' align='right' >{$res[$r][2]}</td>\n";
                echo "<td class='winbox pt11 ' width='10%' align='right' >", number_format($res[$r][3], 2), "</td>\n";
                if (manualInputCheck($res[$r][2])) {
                    echo "<td class='winbox pt11b' width=' 5%' align='center'>○</td>\n";
                } else {
                    echo "<td class='winbox pt11b' width=' 5%' align='center'>×</td>\n";
                }
                echo "<td class='winbox pt11 ' width='10%' align='right' >", number_format($res[$r][5], 2), "</td>\n";
                echo "<td class='winbox pt11b' width='10%' align='center'>{$res[$r][6]}</td>\n";
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
