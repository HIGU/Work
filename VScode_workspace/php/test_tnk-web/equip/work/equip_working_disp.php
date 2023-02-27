<?php
//////////////////////////////////////////////////////////////////////////////
// 機械運転 状況 選択 フォーム2 (新版)                                      //
// Copyright (C) 2002-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/02/15 Created  equipment_working_disp.php                           //
// 2002/08/08 register_globals = Off 対応                                   //
// 2002/08/27 フレーム対応                                                  //
// 2003/02/14 フォントをstyle指定に変更しブラウザーによる変更不可(IE)にした //
// 2003/06/18 マスターから動的に監視対象機械を取得するように変更            //
// 2004/03/04 稼動していない物と未登録の物ををsubmit出来ないようにした。    //
// 2004/03/05 新版テーブルに全面改訂                                        //
// 2004/07/23 Class MenuHeader を使用                                       //
// 2005/06/24 F2/F12キーで戻るための対応で JavaScriptの set_focus()を追加   //
// 2018/05/18 ７工場を追加。コード４の登録を強制的に7に変更            大谷 //
// 2021/06/22 ７工場SUSが8工場となってしまうので7になるよう変更        大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);              // E_ALL='2047' debug 用
// ini_set('display_errors', '1');                 // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);            // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                       // 出力バッファをgzip圧縮
session_start();                                // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');         // 設備 共通 function
require_once ('../../MenuHeader.php');          // TNK 全共通 menu class
access_log();                                   // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                      // 認証チェック1=一般以上 戻り先=セッションより タイトル未設定

////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);

if (isset($_REQUEST['status'])) {
    $status = $_REQUEST['status'];
    $_SESSION['equip_work_status'] = $status;
} else {
    $status = $_SESSION['equip_work_status'];
}
////////////// サイト設定
if ($status == 'graph') {
    $menu->set_site(40, 11);                    // site_index=40(設備メニュー) site_id=10(現在稼動中の写真)
} else {
    $menu->set_site(40, 10);                    // site_index=40(設備メニュー) site_id=10(現在稼動中の写真)
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($status == 'graph') {
    $menu->set_title('現在 運転 状況 グラフ 機械選択');
} else {
    $menu->set_title('現在 運転 状況 表形式 機械選択');
}
//////////// 表題の設定
$menu->set_caption('赤字は現在稼動していない機械です。');
//////////// 呼出先のaction名とアドレス設定
if ($status == 'graph') {
    $menu->set_action('稼動状況',   EQUIP2 . 'work/equip_work_graph.php');
} else {
    $menu->set_action('稼動状況',   EQUIP2 . 'work/equip_work_chart.php');
}
$menu->set_action('現在稼動表旧', EQUIP2 . 'equip_machine_disp.php');   // 旧タイプを残す

$uniq = uniqid('href');         // <link href に変数でセットし必ず読み込ませるようにする。
$_SESSION['s_offset'] = 0;      // postgreSQLのクエリーoffset値(初期化)
define('MAC_ROW', '10');        // イメージ表示の行数
define('MAC_COL',  '5');        // イメージ表示の列数

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    $factory = '';
}
///// リクエストが無ければセッションから工場区分を取得する。(通常はこのパターン)
if ($factory == '') {
    $factory = @$_SESSION['factory'];
}

//////////// 機械マスターから設備番号・設備名のリストを取得
if ($factory == '') {
    $query = "select mac_no                 AS mac_no
                    , substr(mac_name,1,7)  AS mac_name
                    , factory               AS factory
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                order by mac_no ASC
    ";
} else {
    $query = "select mac_no                 AS mac_no
                    , substr(mac_name,1,7)  AS mac_name
                    , factory               AS factory
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                    and
                    factory='{$factory}'
                order by mac_no ASC
    ";
}
$mac_res = array();
if ( ($mac_rows=getResult($query, $mac_res)) <= 0) {
    $_SESSION['s_sysmsg'] = "<font color='yellow'>機械マスターに登録がありません！</font>";
    header('Location: ' . $menu->out_RetUrl());        // 直前の呼出元へ戻る
    exit();
} else {
    $mac_no   = array();        // 機械№
    $mac_name = array();        // 機械名称
    $factory  = array();        // 工場名 例：1工場
    for ($i=0; $i<$mac_rows; $i++) {
        $mac_no[$i]   = $mac_res[$i][0];
        $mac_name[$i] = $mac_res[$i][0] . ' ' . $mac_res[$i][1];
        if ($mac_res[$i][2]==8) {
            $mac_res[$i][2] = 7;
        }
        $factory[$i]  = $mac_res[$i][2];
    }
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<link rel='stylesheet' href='../equipment.css?<?php echo $uniq ?>' type='text/css' media='screen'> <!-- ファイル指定の場合 -->
<style type="text/css">
<!--
.pt_small {
    font-size:9pt;
}
.fc_red {
    color:red;
}
.fc_blue {
    color:blue;
}
-->
</style>
<script language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>
</head>

<body onLoad='set_focus()' bgcolor='#ffffff' text='#000000'>
    <center>
<?= $menu->out_title_border() ?>
        <!-- bgcolor='#d6d3ce' -->
        <table width='100%' border='0'>
            <tr>
                <td align='center' class='pt11b fc_red'>
                    <?= $menu->out_caption() . "\n" ?>
                </td>
            </tr>
            <tr>
                <td align='center' class='pt10'>
                    機械マスターに登録されていて、監視中に設定されているものが表示されます。
                </td>
            </tr>
        </table>
        <table width='100%' cellspacing='2' cellpadding='0' border='0'>
            <?php
                $k = 0;     // mac_no[$k] 等のindex
                for ($i=0; $i<MAC_ROW; $i++) {        // 行数
                    echo "<tr>\n";
                    for ($j=0; $j<MAC_COL; $j++) {    // 列数
                        echo "<td align='center'>\n";
                        echo "    <form method='post' action='", $menu->out_action('稼動状況'), "'>\n";
                        if (isset($mac_no[$k])) {   // 機械Noがセットされているか？
                            $img_file = "../img/{$mac_no[$k]}.jpg";        // imageファイル名生成
                            if (equip_working_chk($mac_no[$k])) {   // 現在稼動中か？
                                echo "<input type='hidden' name='mac_no' value='{$mac_no[$k]}'>\n";
                                if ( file_exists($img_file) ) {             // ファイルの存在チェック
                                    echo "<input type='image' alt='機械No{$mac_name[$k]}' border=0 src='{$img_file}'>\n";
                                } else {
                                    echo "<input type='image' alt='機械No{$mac_name[$k]}' border=0 src='../img/other.jpg'>\n";
                                }
                                echo "<br clear='all'><font class='pt_small fc_blue'>{$mac_name[$k]} ", mb_convert_kana($factory[$k], "N"), "工場</font>\n";
                            } else {
                                echo "<input type='hidden' name='mac_no' value='{$mac_no[$k]}'>\n";
                                if ( file_exists($img_file) ) {             // ファイルの存在チェック
                                    echo "<img alt='機械No{$mac_name[$k]}' border=0 src='{$img_file}'>\n";
                                } else {
                                    echo "<img alt='機械No{$mac_name[$k]}' border=0 src='../img/other.jpg'>\n";
                                }
                                echo "<br clear='all'><font class='pt_small fc_red'>{$mac_name[$k]} ", mb_convert_kana($factory[$k], "N"), "工場</font>\n";
                            }
                        } else {
                            // echo "<img alt='---------------' border=0 src='../img/other.jpg'>\n";
                            // echo "<br clear='all'><font class='pt_small'>未登録</font>\n";
                            echo "</form>\n";
                            echo "</td>\n";
                            break;
                        }
                        $k++;       // index をプラス
                        echo "    </form>\n";
                        echo "</td>\n";
                    }
                    echo "</tr>\n";
                    if (!isset($mac_no[$k])) {   // 機械Noがセットされていなければ？
                        break;
                    }
                }
            ?>
        </table>
    </center>
</body>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
