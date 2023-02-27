<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費の登録 materialCost_entry_ViewBody.php                           //
// Copyright (C) 2007-2019 Norihisa.Ohya                                    //
// Changed history                                                          //
// 2007/05/23 Created   materialCost_entry_ViewBody.php                     //
// 2007/06/19 削除した番号-1の番号にマーカーをつけるように変更              //
// 2007/06/21 phpショートタグ→標準タグへ。 HTMLの余分なソースを削除 小林   //
//            $menu->out_retF2Script() 追加 番号クリックを全体クリックへ小林//
// 2007/06/22 $uniqの２重設定を修正。小林                                   //
// 2007/09/18 E_ALL | E_STRICT へ変更 小林                                  //
// 2019/05/18 markがうまく反応しなかった為trではなくNoにつけるよう変更      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT)
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php');     // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 21);                    // site_index=30(生産メニュー) site_id=21(総材料費の登録)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('総 材 料 費 の 登 録 (工程明細)');
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須
//////////// 自分をフレーム定義に変える
//$menu->set_self(INDUST . 'material/materialCost_entry_main.php');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('旧総材料費登録',   INDUST . 'material/materialCost_entry_old.php');
//////////// 戻先へのGETデータ設定
$menu->set_retGET('page_keep', 'On');

$request = new Request;
$session = new Session;
//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// 一頁の行数
define('PAGE', '300');

//////////// メッセージ出力フラグ
$msg_flg = 'site';

//////////// エラーログの出力先
$error_log_name = '/tmp/materialCost_entry_error.log';

//////////// 計画番号・製品番号を取得
$plan_no = $session->get('plan_no');
$assy_no = $session->get('assy_no');

//////////// 総材料費の最新登録の戻り先製品番号指定
if (substr($plan_no, 0, 2) == 'ZZ') $menu->set_retGET('assy', $assy_no);

//////////// 前回データのコピーボタンが押された場合
if ($request->get('pre_copy') != '') {
    $query = "SELECT midsc FROM miitem WHERE mipn='{$assy_no}'";
    if ( getUniResult($query, $assy_name) <= 0) {           // 製品名の取得
        $_SESSION['s_sysmsg'] .= "製品名の取得に失敗";      // .= メッセージを追加する
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
        exit();
    }
    $query = "SELECT plan_no FROM material_cost_header WHERE assy_no='{$assy_no}'
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
        $query = "INSERT INTO material_cost_history (
                        plan_no, assy_no, parts_no, pro_no, pro_mark,
                        par_parts, pro_price, pro_num, intext, last_date, last_user)
                  SELECT
                        '{$plan_no}', '{$assy_no}', parts_no, pro_no, pro_mark,
                        par_parts, pro_price, pro_num, intext, CURRENT_TIMESTAMP, '{$_SESSION['User_ID']}'
                  FROM material_cost_history
                  WHERE plan_no='{$pre_plan_no}' and assy_no='{$assy_no}'
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

//////////// SQL 文の WHERE 句を 共用する
$search = sprintf("WHERE plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);
// $search = '';

//////////// 合計レコード数・総材料費の取得     (対象データの最大数をページ制御に使用)
$query = sprintf("SELECT count(*), sum(Uround(pro_price * pro_num, 2)) FROM material_cost_history %s", $search);
$res_sum = array();
if ( getResult2($query, $res_sum) <= 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "合計レコード数の取得に失敗";      // .= メッセージを追加する
    $msg_flg = 'alert';
}
$maxrows = $res_sum[0][0];
$sum_kin = $res_sum[0][1];

//////////// 計画番号単位の工程明細の作表
$query = sprintf("
        SELECT
            mate.last_user  AS \"Level\",                   -- 0
            parts_no        as 部品番号,                    -- 1
            midsc           as 部品名,                      -- 2
            pro_num         as 使用数,                      -- 3
            pro_no          as 工程,                        -- 4
            pro_mark        as 工程名,                      -- 5
            pro_price       as 工程単価,                    -- 6
            Uround(pro_num * pro_price, 2)
                            as 工程金額,                    -- 7
            CASE
                WHEN intext = '0' THEN '外作'
                WHEN intext = '1' THEN '内作'
                ELSE intext
            END             as 内外作,                      -- 8
            par_parts       as 親番号                       -- 9
        FROM
            -- material_cost_history
            material_cost_level_as('{$plan_no}') AS mate
        LEFT OUTER JOIN
             miitem ON parts_no=mipn
        -- %s 
        -- ORDER BY par_parts ASC, parts_no ASC, pro_no ASC
        
    ", $search);       // 共用 $search で検索
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    // header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    // exit();
    $num = count($field);       // フィールド数取得
    $final_flg = 0;             // 完了フラグ 0=NG
} else {
    $num = count($field);       // フィールド数取得
    $final_flg = 1;             // 完了フラグ 1=OK
    $query = "SELECT parts_no FROM material_cost_level_as('{$plan_no}')";
    $chk_rows = getResult2($query, $res_chk);
    if ($chk_rows != $maxrows) {
        $_SESSION['s_sysmsg'] .= "レベル表示：{$chk_rows} と実データ：{$maxrows} のレコード数が一致していません！　直接入力メニューを使用して下さい。";    // .= に注意
        $msg_flg = 'alert';
        $old_menu = 'on';
        $_GET['page_keep'] = '1';   // エラーの場合はページを維持するため page_keepを使用
    }
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
-->

<script type='text/javascript'>
<!--
function targetEdit(url, row)
{
    document.targetCopy.action = url;
    document.targetCopy.number.value = row;
    document.targetCopy.submit();
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.mhForm.backwardStack.focus();  // IE/NN 両対応
    // document.entry_form.parts_no.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.entry_form.parts_no.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font-size:          0.75em;
    font-family:        monospace;
}
.pt10b {
    font-size:          0.85em;
    font-weight:        bold;
    font-family:        monospace;
}
.pt11b {
    font-size:          0.95em;
    font-weight:        bold;
    font-family:        monospace;
}
a {
    font-size:          0.9em;
    font-weight:        bold;
    color:              blue;
    text-decoration:    none;
}
a:hover {
    background-color:   blue;
    color:              white;
}
.list tr.mouseOver {
    background-color:   #ceffce;
}
.list td.Edit {
    background-color:   white;
    font-size:          1.1em;
}
-->
</style>
<form name='targetCopy' action='' target='footer' action='post'>
<input type='hidden' name='number' value=''>
</form>
</head>
<body onLoad='set_focus()' bgcolor='#d6d3ce'>
    <center>
       <!--------------- ここから本文の表を表示する -------------------->
        <table width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field list' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                    <!--  bgcolor='#ffffc6' 薄い黄色 -->
                    <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
            <?php
            for ($r=0; $r<$rows; $r++) {
                if ($request->get('mark') != '') {
                    if ($request->get('parts_no') == $res[$r][1] && $request->get('pro_mark') == $res[$r][5]) {    //登録した番号と一緒ならマーカー
                        if ($request->get('par_parts') != '') {
                            if ($request->get('par_parts') == $res[$r][9]) {
                                echo "<tr style='background-color:#ffffc6;' onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                            } else {
                                echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                            }
                        } else {
                            echo "<tr style='background-color:#ffffc6;' onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                        }
                    } else {
                        echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                    }
                } else if ($request->get('c_mark') != '') {
                    if ($request->get('c_number') == 1) {
                        if ($request->get('c_number') == $r) {    //1の場合削除した番号にマーカーを付ける
                            echo "<tr style='background-color:#ffffc6;' onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                        } else {
                            echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                        }
                    } else {
                        if ($request->get('c_number')-1 == $r) {    //削除した番号の一つ上にマーカーを付ける
                            echo "<tr style='background-color:#ffffc6;' onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                        } else {
                            echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                        }
                    }
                } else if ($request->get('no_del_mark') != '') {
                    if ($request->get('no_del_num') == $r) {    //削除に失敗した場合番号にマーカーを付ける
                        echo "<tr style='background-color:#ffffc6;' onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                    } else {
                        echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                    }
                } else {
                    echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                }
                /////////////////////////
                
                if ($request->get('mark') != '') {
                    if ($request->get('parts_no') == $res[$r][1] && $request->get('pro_mark') == $res[$r][5]) {    //登録した番号と一緒ならマーカー
                        if ($request->get('par_parts') != '') {
                            if ($request->get('par_parts') == $res[$r][9]) {
                            ?>
                                <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                    onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                    <a href='javascript:void(0);' name='mark'><?php echo ($r + 1) ?></a>
                                </td>    <!-- 行ナンバーの表示 -->
                            <?php
                            } else {
                            ?>
                                <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                    onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                    <a href='javascript:void(0);'><?php echo ($r + 1) ?></a>
                                </td>    <!-- 行ナンバーの表示 -->
                            <?php
                            }
                        } else {
                        ?>
                            <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                <a href='javascript:void(0);' name='mark'><?php echo ($r + 1) ?></a>
                            </td>    <!-- 行ナンバーの表示 -->
                        <?php
                        }
                    } else {
                    ?>
                        <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                            onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                            <a href='javascript:void(0);'><?php echo ($r + 1) ?></a>
                        </td>    <!-- 行ナンバーの表示 -->
                    <?php
                    }
                } else if ($request->get('c_mark') != '') {
                    if ($request->get('c_number') == 1) {
                        if ($request->get('c_number') == $r) {    //1の場合削除した番号にマーカーを付ける
                        ?>
                            <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                <a href='javascript:void(0);' name='mark'><?php echo ($r + 1) ?></a>
                            </td>    <!-- 行ナンバーの表示 -->
                        <?php
                        } else {
                        ?>
                            <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                <a href='javascript:void(0);'><?php echo ($r + 1) ?></a>
                            </td>    <!-- 行ナンバーの表示 -->
                        <?php
                        }
                    } else {
                        if ($request->get('c_number')-1 == $r) {    //削除した番号の一つ上にマーカーを付ける
                        ?>
                            <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                <a href='javascript:void(0);' name='mark'><?php echo ($r + 1) ?></a>
                            </td>    <!-- 行ナンバーの表示 -->
                        <?php
                        } else {
                        ?>
                            <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                                onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                                <a href='javascript:void(0);'><?php echo ($r + 1) ?></a>
                            </td>    <!-- 行ナンバーの表示 -->
                        <?php
                        }
                    }
                } else if ($request->get('no_del_mark') != '') {
                    if ($request->get('no_del_num') == $r) {    //削除に失敗した場合番号にマーカーを付ける
                    ?>
                        <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                            onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                            <a href='javascript:void(0);' name='mark'><?php echo ($r + 1) ?></a>
                        </td>    <!-- 行ナンバーの表示 -->
                    <?php
                    } else {
                    ?>
                        <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                            onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                            <a href='javascript:void(0);'><?php echo ($r + 1) ?></a>
                        </td>    <!-- 行ナンバーの表示 -->
                    <?php
                    }
                } else {
                ?>
                    <td class='winbox pt10b' nowrap width='4%' align='right' onMouseOver='this.className="Edit";' onMouseOut='this.className="winbox pt10b";'
                        onClick='targetEdit("<?php echo 'materialCost_entry_ViewFooter.php'?>", <?php echo $r ?>);'>
                        <a href='javascript:void(0);'><?php echo ($r + 1) ?></a>
                    </td>    <!-- 行ナンバーの表示 -->
                <?php
                }
                /////////////////
                ?>
                
                <?php
                for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                    switch ($i) {
                    case 0:
                        echo "<td class='winbox' width='5%' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        break;
                    case 1:
                        echo "<td class='winbox' width='8%' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        break;
                    case 2:
                        echo "<td class='winbox' width='39%' align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        break;
                    case 3:
                        echo "<td class='winbox' nowrap width='6%' align='right'><div class='pt9'>", number_format($res[$r][$i], 4), "</div></td>\n";
                        break;
                    case 4:
                        echo "<td class='winbox' width='4%' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        break;
                    case 5:
                        echo "<td class='winbox' width='6%' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        break;
                    case 6:
                        echo "<td class='winbox' nowrap width='7%' align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                        break;
                    case 7:
                        echo "<td class='winbox' nowrap width='7%' align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                        break;
                    case 8    :
                        echo "<td class='winbox' width='6%' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        break;
                    default:
                        if ($res[$r][$i] != '') {
                            echo "<td class='winbox' width='8%' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                        } else {    // 親番号がない場合を想定 $i=8
                            echo "<td class='winbox' width='8%' nowrap align='center'><div class='pt9'>&nbsp;</div></td>\n";
                        }
                    }
                }
                ?>
                </tr>
            <?php
            }
            ?>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <?php echo $menu->out_retF2Script() ?>
    </center>
</body>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
