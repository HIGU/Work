<?php
//////////////////////////////////////////////////////////////////////////////
// 機械稼動管理システムの 加工指示(指示メンテナンス)  フレーム リスト 定義  //
// Copyright (C) 2004 2006-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/07/27 Created  equip_workMnt_List.php                               //
// 2004/08/03 中断中の計画を表示する部分に中断 指示 日時を 追加 EXPLAINでchk//
// 2004/08/05 手入力を登録出来る様に変更 部品マスターのチェックのみ         //
// 2004/08/08 フレーム版の戻り先をapplication→_parentに変更(FRAME無し対応) //
//            中断指示日時の取得にequip_index()関数を使用するようにSQL文変更//
// 2006/03/27 加工完了指示入力後、完了画面を維持する                        //
// 2007/03/27 set_site()メソッドを INDEX_EQUIP へ変更  上記の画面維持を全て //
//            に適用。運転開始時に中断計画がある場合はメッセージを出力する  //
// 2007/07/27 指示番号による工程選択メニューを追加、手入力のロジック変更    //
//            $menu->out_retF2Script()追加 と baseJS.keyInUpper(this) 追加  //
// 2007/09/18 E_ALL | E_STRICT へ変更                                       //
// 2018/05/18 ７工場を追加。コード４の登録を強制的に7に変更            大谷 //
// 2018/12/26 ７工場を真鍮とSUSに分離                                  大谷 //
// 2018/12/27 equip_header_to_csv辺りで速度が遅くなってる気がするので       //
//            一時コメント化。netmoniはないので影響は無い筈            大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');             // 設備メニュー 共通 function (function.phpを含む)
require_once ('../EquipControllerHTTP.php');        // TNK 全共通 MVC Controller Class
require_once ('../../MenuHeader.php');              // TNK 全共通 menu class
access_log();                                       // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();           // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

///// 設備専用セッションクラスのインスタンスを作成
$equipSession = new equipSession();

$request = new Request();

////////////// サイト設定
$menu->set_site(INDEX_EQUIP, 23);           // site_index=40(設備メニュー) site_id=23(指示メンテナンス)
//////////// 子フレームに対応させるため自分自身をフレーム宣言のスクリプト名に変える
$menu->set_self(EQUIP2 . 'work_mnt/equip_workMnt_Main.php');
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('機械稼動管理 指示メンテナンス');
//////////// 表題の設定
$menu->set_caption('作業区分を選択して下さい');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('work_edit',  EQUIP2 . 'work_mnt/equip_edit_chart.php');

///// 上で自分自身を変えているため current_scriptを指定している
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存

/////////// 工場区分を取得する
$factory = $equipSession->getFactory();
switch ($factory) {
case 1:
case 2:
case 4:
case 5:
case 6:
case 7:
case 8:
    $fact_where = "AND factory = {$factory}";
    break;
default:
    $fact_where = '';
    break;
}

/////////// 運転指示メニューの選択設定
$equipment_select = $request->get('equipment_select');

/////////// POST Data のローカル変数の登録＆初期化
$mac_no     = $request->get('mac_no');
$siji_no    = $request->get('siji_no');
$parts_no   = $request->get('parts_no');
$koutei     = $request->get('koutei');
$plan_cnt   = $request->get('plan_cnt');

$init_data_input    = $request->get('init_data_input');
$init_data_cut      = $request->get('init_data_cut');
$init_data_edit     = $request->get('init_data_edit');
$init_data_end      = $request->get('init_data_end');
$plan_to_start      = $request->get('plan_to_start');
$break_restart      = $request->get('break_restart');
$break_del          = $request->get('break_del');
$init_data_cancel   = $request->get('init_data_cancel');

/******* IE 対策 *********/
if (isset($_POST['init_siji_no'])) {
    $siji_no = $_POST['init_siji_no'];
    $init_data_input = '確認';
}

///////////// POST Data がある時だけローカル変数に登録
if (isset($_POST['m_no'])) $m_no = $_POST['m_no'];
if (isset($_POST['s_no'])) $s_no = $_POST['s_no'];
if (isset($_POST['b_no'])) $b_no = $_POST['b_no'];
if (isset($_POST['k_no'])) $k_no = $_POST['k_no'];
if (isset($_POST['p_no'])) $p_no = $_POST['p_no'];

///////////// メニュー切替ロジック
/////////////////////////////////////////////////// 運転開始時の 取消
if ($init_data_cancel != '') {
    $equipment_select = 'init_data_input';  // 運転開始 画面へ
    $init_data_input = '';                  // 指示番号入力画面
}

/////////////////////////////////////////////////// 運転開始
while ($init_data_input == '登録') {                   // 新規データ追加
    if ($mac_no == '') {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>機械を選択して下さい！</font>";
        break;
    }
    if (!partsNoCheck($parts_no)) {
        $equipment_select = 'init_data_input';  // 運転開始 画面へ
        $init_data_input = '';                  // 指示番号入力画面
        break;
    }
    ////////// 先に機械名を取得
    $queryName = "
        SELECT mac_name FROM equip_machine_master2 WHERE mac_no={$mac_no}
    ";
    $mac_name = '';
    getUniResult($queryName, $mac_name);
    ////////// 中断計画をチェックして、あればメッセージを出力
    $query = "
        SELECT siji_no
        , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI') AS str_timestamp
        FROM equip_work_log2_header
        WHERE mac_no={$mac_no} AND end_timestamp IS NULL AND work_flg IS FALSE
        ORDER BY str_timestamp DESC
    ";
    $res = array();
    if (($rows=getResult2($query, $res)) >= 1) {
        $_SESSION['s_sysmsg'] = "[$mac_no] {$mac_name} を運転開始しました。\\n\\n中断計画があります。 \\n\\n指示番号：{$res[0][0]} \\n\\n日時：{$res[0][1]} \\n\\nがあります。\\n\\n確認して下さい。";
    }
    ////////// ヘッダーより運転中の機械をチェック
    $query = "
        SELECT mac_no, siji_no, parts_no, koutei, plan_cnt
        FROM
            equip_work_log2_header
        WHERE
            work_flg IS TRUE
            and mac_no='$mac_no' LIMIT 1
    ";
    $res = array();
    if (($rows=getResult($query, $res)) >= 1) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>[{$mac_no}] {$mac_name} は既に運転開始されています！</font>";
    } else {
        ////////// ヘッダーより過去のデータの重複チェック
        $query = "
            SELECT mac_no, siji_no, parts_no, koutei, plan_cnt
            FROM
                equip_work_log2_header
            WHERE
                mac_no={$mac_no}
                and siji_no={$siji_no}
                and koutei={$koutei} limit 1
        ";
        $res = array();
        if (($rows=getResult($query, $res)) >= 1) {
            $_SESSION['s_sysmsg'] = "<font color='yellow'>{$mac_name} 機械番号:{$mac_no} 指示番号:$siji_no 工程:$koutei は過去のデータと重複しています</font>";
        } else {
            ////////// 登録実行
            $str_timestamp = date('Y-m-d H:i:s');
            add_equip_header($mac_no, $siji_no, $parts_no, $koutei, $plan_cnt, $str_timestamp);
            //equip_header_to_csv();
            if ($_SESSION['s_sysmsg'] == '') {
                $_SESSION['s_sysmsg'] = "{$mac_name} 機械番号 : {$mac_no} \\n\\n指示番号 : {$siji_no} \\n\\n工程 : {$koutei}\\n\\n で運転開始しました。";
            }
        }
    }
    $equipment_select = 'init_data_input';  // 運転開始 画面へ
    $init_data_input = '';                  // 指示番号入力画面
    break;
}

/***** 共用アイテムマスターチェック関数 *****/
function partsNoCheck($parts_no)
{
    $query = "SELECT trim(substr(midsc, 1, 26)) AS name FROM miitem WHERE mipn='{$parts_no}'";
    if (getUniResult($query, $name) <= 0) {
        $_SESSION['s_sysmsg'] = "{$parts_no} はマスター未登録です。";
        return '';
    } else {
        return $name;
    }
}
/////////////////// 運転開始 確認データ生成
if ($init_data_input == '確認') {                   // 新規データ追加
    $equipment_select = 'init_data_input';
    $query = "SELECT inst.inst_no
                , inst.mac_no
                , inst.koutei
                , inst.pro_mark
                , inst.parts_no
                , trim(substr(item.midsc, 1, 26)) as name
                , inst_h.inst_qt
                , mast.mac_name
            FROM
                equip_work_instruction as inst
            LEFT OUTER JOIN
                equip_work_inst_header as inst_h
            USING
                (inst_no)
            LEFT OUTER JOIN
                equip_machine_master2 as mast
            ON
                inst.mac_no = mast.mac_no
            LEFT OUTER JOIN
                miitem as item
            ON
                inst.parts_no = item.mipn
            WHERE
                inst_no={$siji_no} -- 2007/07/17 and koutei=1
        ";
    $res = array();
    if (($rows=getResult2($query, $res)) <= 0) {    // 内作指示の工程明細にデータがあるかチェック
        $_SESSION['s_sysmsg'] = "<font color='yellow'>指示番号： {$siji_no}<br> データがありません！<br> 手入力して下さい！</font>";
        $inst_no  = $siji_no;
        $mac_no[0]   = '';
        $koutei[0]   = '';
        $pro_mark[0] = '';
        $parts_no[0] = '';
        $name[0]     = '';
        $plan_cnt[0] = '';
        $init_data_input = '手入力';
    } else {
        for ($i=0; $i<$rows; $i++) {
            $inst_no  = $res[$i][0];
            $mac_no[$i]   = $res[$i][1];
            $koutei[$i]   = $res[$i][2];
            $pro_mark[$i] = $res[$i][3];
            $parts_no[$i] = $res[$i][4];
            $name[$i]     = $res[$i][5];
            $plan_cnt[$i] = $res[$i][6];
        }
    }
}

///////////////////////////////// 運転中断 CSVは削除してデータを再作成 equip_work_log2_headerはwork_flg IS FALSE
if ($init_data_cut != '') {
    $query = "select mac_no, siji_no, koutei, parts_no
                from equip_work_log2_header
                where work_flg IS TRUE and mac_no=$m_no and siji_no=$s_no and koutei=$k_no";
    $res = array();
    if (($rows=getResult($query,$res)) >= 1) {          // データベースのヘッダーより運転中のデータをチェック
        break_equip_header($m_no, $s_no, $b_no, $k_no, FALSE);
        //equip_header_to_csv();
    } else {
        $_SESSION['s_sysmsg'] = "機械番号:$m_no 指示番号:$s_no 部品番号:$b_no 工程:$k_no では登録されていません";
    }
    $equipment_select = 'init_data_cut';    // 運転中断 画面へ
    $init_data_cut = '';                    // 運転中断の一覧 画面へ
}

//////////////////////////////////// 指示変更 修正するためデータを再作成
if ($init_data_edit != '') {
    /************************** 現在は使用していない
    $query = "select mac_no
                    , siji_no
                    , parts_no
                    , koutei
                from
                    equip_work_log2_header
                where
                    work_flg IS TRUE and mac_no={$m_no} and siji_no={$s_no} and koutei={$k_no}
            ";
    $res = array();
    if (($rows=getResult($query,$res)) >= 1) {          // データベースのヘッダーより運転中のデータをチェック
        chg_equip_header_work($m_no, $s_no, $b_no, $k_no, $mac_no, $siji_no, $parts_no, $koutei, $plan_cnt);
        equip_header_to_csv();
    } else {
        $_SESSION['s_sysmsg'] = "機械番号:$m_no 指示番号:$s_no 工程:$k_no では登録されていません";
    }
    ***************************/
}

//////////////////////////////////// 加工完了
if ($init_data_end != '') {
    $query = "select mac_no,siji_no,parts_no,koutei from equip_work_log2_header where work_flg=TRUE 
            and mac_no='$m_no' and siji_no='$s_no' and parts_no='$b_no' and koutei='$k_no'";
    $res = array();
    if (($rows=getResult($query, $res)) >= 1) {         // データベースのヘッダーより運転中のデータをチェック
        end_equip_header($m_no,$s_no,$b_no,$k_no,$_POST['jisseki']);
        //equip_header_to_csv();
    } else {
        $_SESSION['s_sysmsg'] = "機械番号:$m_no 指示番号:$s_no 部品番号:$b_no 工程:$k_no では登録されていません";
    }
    $equipment_select = 'init_data_end';    // 加工完了画面を維持する
}

//////////////////////////////////// 予定計画より運転開始
if ($plan_to_start != '') {
    $query_plan = "select mac_no,siji_no,parts_no,koutei from equip_plan where plan_flg=TRUE 
            and mac_no='$m_no' and siji_no='$s_no' and parts_no='$b_no' and koutei='$k_no'";
    $query_header = "select mac_no,siji_no,parts_no,koutei from equip_work_log2_header where 
                mac_no='$m_no' and siji_no='$s_no' and parts_no='$b_no' and koutei='$k_no'";
    $query_header_mac = "select mac_no,siji_no,parts_no,koutei from equip_work_log2_header where 
                work_flg=TRUE and mac_no='$m_no' limit 1";
    $res=array();
    if (($rows=getResult($query_plan, $res)) >= 1) {                // equip_plan のデータをチェック
        if (($rows=getResult($query_header, $res)) == 0) {          // equip_work_log2_header の全データをチェック
            if (($rows=getResult($query_header_mac,$res)) == 0) {   // equip_work_log2_header の運転中機械№をチェック
                trans_equip_plan_to_start($m_no,$s_no,$b_no,$k_no,$p_no);   // Transaction 処理
                //equip_header_to_csv();
            } else {
                $_SESSION['s_sysmsg'] = "機械番号:$m_no は現在運転中です";
            }
        } else {
            $_SESSION['s_sysmsg'] = "機械番号:$m_no 指示番号:$s_no 部品番号:$b_no 工程:$k_no は既に実績があります";
        }
    } else {
        $_SESSION['s_sysmsg'] = "機械番号:$m_no 指示番号:$s_no 部品番号:$b_no 工程:$k_no では予定計画に見つかりません";
    }
}

////////////////////////////////////// 中断データの再開
if ($break_restart != '') {
    $query = "select mac_no from equip_work_log2_header where mac_no='$m_no' and work_flg is TRUE and end_timestamp is NULL";
    $res = array();
    if(($rows=getResult($query, $res)) >= 1) {                  // ヘッダーに既にないかチェック
        $_SESSION['s_sysmsg'] = "<font color='yellow'>機械番号 = $m_no は現在稼動中です</font>";
    } else {
        break_equip_header($m_no, $s_no, $b_no, $k_no, TRUE);       // TRUE=再開
        //equip_header_to_csv();
    }
    $equipment_select = 'break_data';   // 中断計画 画面を維持する
}
////////////////////////////////////// 中断データの完全削除
if ($break_del != '') {
    del_equip_header_work($m_no,$s_no,$b_no,$k_no);     // 削除(トランザクション処理)
    //equip_header_to_csv();
    $equipment_select = 'break_data';   // 中断計画 画面を維持する
}

///////////// 運転開始時の機械№・名称をマスターから取得
if ($equipment_select == 'init_data_input') {
    $query = "select mac_no, mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    {$fact_where}
                order by mac_no ASC
    ";
    $mac_res = array();
    if ( ($mac_rows=getResult($query, $mac_res)) <= 0) {
        $_SESSION['s_sysmsg'] = '機械マスターの取得に失敗';
    } else {
        $mac_name = array();
        for ($i=0; $i<$mac_rows; $i++) {
            $mac_name[$i] = $mac_res[$i][0] . ' ' . $mac_res[$i][1];
        }
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
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<?php // echo $menu->out_site_java() ?>
<style type="text/css">
<!--
select {
    background-color:   teal;
    color:              white;
}
.pt10 {
    font-size:      0.85em;
}
.pt11b {
    font-size:      0.95em;
    font-weight:    bold;
}
.pt12b {
    font-size:      1.05em;
    font-weight:    bold;
}
.right {
    text-align:right;
}
.center {
    text-align:center;
}
.left {
    text-align:left;
}
.margin1 {
    margin:1%;
}
.margin0 {
    margin:0%;
}
.fc_red {
    color:              red;
    background-color:   blue;
}
.fc_gray {
    background-color:   #d6d3ce;
    border:             0px none #d6d3ce;
}
.fc_yellow {
    color:              yellow;
    background-color:   blue;
}
.fc_white {
    color:              white;
    background-color:   blue;
}
caption {
    font-size:   0.95em; /* 11pt */
    font-weight: bold;
}
th {
    font-size:          0.95em;
    font-weight:        bold;
    color:              blue;
    background-color:   yellow;
}
input.number {
    width:              30px;
    font-size:          0.9em;
    font-weight:        bold;
    color:              blue;
}
input.editButton {
    font-size:          0.9em;
    font-weight:        bold;
    color:              blue;
}
.siji {
    font-size:          1.0em;
    font-weight:        bold;
}
-->
</style>
<script language='JavaScript' src='../equipment.js'></script>
<script language='JavaScript'>
<!--
    <?php
    if ($equipment_select == 'init_data_input') {
        if ($init_data_input == '') {
            echo 'function set_focus() {';
            echo '    document.siji_form.init_siji_no.focus();', "\n";
            echo '    document.siji_form.init_siji_no.select();', "\n";
            echo '}';
        } else if($init_data_input == '確認') {
            echo 'function set_focus() {';
            echo '    document.siji_form.init_data_input.focus();', "\n";
            echo '}';
        } else {
            echo 'function set_focus() {';
            echo '}';
        }
    } else {
        echo 'function set_focus() {';
        echo '}';
    }
    ?>

function selectCopy(obj, obj2)
{
    for (var i=0; i<obj.options.length; i++) {
        if (obj.options[i].selected) {
            obj2.options[i].selected = true;
        } else {
            obj2.options[i].selected = false;
        }
    }
}
// -->
</script>

</head>
<body class='margin0' onLoad='set_focus()'>
    <center>
        <?php
        switch ($equipment_select) {
        case 'init_data_input':     // 入力（機械 運転 開始）
            if ($init_data_input == '') {
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<form name='siji_form' action='", $current_script, "' method='post' onSubmit='return chk_equip_inst(this)'>\n";
                echo " <tr>\n";
                echo "     <td align='left' nowrap>\n";
                echo "         加工 指示番号の入力\n";
                echo "         <input tabindex='1' type='text' class='siji' name='init_siji_no' size='6' value='$siji_no' maxlength='5'>\n";
                echo "     </td>\n";
                echo "     <td align='center' nowrap>\n";
                echo "         <input tabindex='1' type='submit' name='init_data_input' value='確認'>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo "</form>\n";
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ダミーEnd ------------------>\n";
            } elseif ($init_data_input == '確認') {
                echo "<table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                echo "<table class='winbox_field' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
                echo "<form name='siji_form' action='", $current_script, "' method='post'>\n";
                echo " <tr>\n";
                echo "     <th class='winbox' width='40'>1</th>\n";
                echo "     <td class='winbox' width='120' align='center' nowrap>\n";
                echo "         指示番号\n";
                echo "     </td>\n";
                echo "     <td class='winbox' width='200' align='center' nowrap>\n";
                echo "         <input type='text' class='siji fc_gray' name='siji_no' size='6' value='{$inst_no}' maxlength='5' style='text-align: center;' readonly>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>2</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         機械番号\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         <select name='mac_no' class='siji' onChange='selectCopy(this, document.siji_form.macName);'>\n";
                for ($i=0; $i<$mac_rows; $i++) {
                    if ($i == 0) {
                        echo "         <option value=''>選択して下さい</option>\n";
                    }
                    if ($mac_no[0] == $mac_res[$i][0]) {
                        echo "        <option value='{$mac_res[$i][0]}' selected>{$mac_name[$i]}</option>\n";
                    } else {
                        echo "        <option value='{$mac_res[$i][0]}'>{$mac_name[$i]}</option>\n";
                    }
                }
                echo "         </select>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>3</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         機械名称\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         <select name='macName' class='siji' disabled>\n";
                for ($i=0; $i<$mac_rows; $i++) {
                    if ($i == 0) {
                        echo "         <option value=''>未設定</option>\n";
                    }
                    if ($mac_no[0] == $mac_res[$i][0]) {
                        echo "        <option value='{$mac_res[$i][0]}' selected>{$mac_res[$i][1]}</option>\n";
                    } else {
                        echo "        <option value='{$mac_res[$i][0]}'>{$mac_res[$i][1]}</option>\n";
                    }
                }
                echo "         </select>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>4</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         部品番号\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         <input type='text' class='siji fc_gray' name='parts_no' size='11' value='{$parts_no[0]}' maxlength='9' style='text-align: center;' readonly>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>5</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         部 品 名\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' class='siji' nowrap>\n";
                echo "         {$name[0]}\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>6</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         工程番号\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         <select name='koutei' class='siji' onChange='selectCopy(this, document.siji_form.kouteiName);'>\n";
                for ($i=0; $i<$rows; $i++) {
                    echo "             <option value='{$koutei[$i]}'>{$koutei[$i]}</option>\n";
                }
                echo "         </select>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>7</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         工程記号\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         <select name='kouteiName' class='siji' disabled>\n";
                for ($i=0; $i<$rows; $i++) {
                    echo "             <option value='{$pro_mark[$i]}'>{$pro_mark[$i]}</option>\n";
                }
                echo "         </select>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th class='winbox'>8</th>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         計 画 数\n";
                echo "     </td>\n";
                echo "     <td class='winbox' align='center' nowrap>\n";
                echo "         <input type='text' class='siji fc_gray' name='plan_cnt' size='6' value='{$plan_cnt[0]}' maxlength='6' style='text-align: right;' readonly>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <td class='winbox' align='center' colspan='3'>\n";
                echo "         <input type='submit' name='init_data_input' value='登録'>\n";
                echo "             &nbsp;&nbsp;\n";
                echo "         <input type='submit' name='init_data_cancel' value='取消'>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo "</form>\n";
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ダミーEnd ------------------>\n";
            } elseif ($init_data_input == '手入力') {
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<form action='", $current_script, "' method='post' onSubmit='return chk_equipment_nippou(this)'>\n";
                echo " <tr>\n";
                echo "     <th width='40'>1</th>\n";
                echo "     <td width='300' align='left' nowrap>\n";
                echo "         機械番号を入力\n";
                echo "         <select name='mac_no' class='siji'>\n";
                for ($i=0; $i<$mac_rows; $i++) {
                    if ($mac_no == $mac_res[$i][0]) {
                        echo "        <option value='{$mac_res[$i][0]}' selected>{$mac_name[$i]}</option>\n";
                    } else {
                        echo "        <option value='{$mac_res[$i][0]}'>{$mac_name[$i]}</option>\n";
                    }
                }
                echo "         </select>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th>2</th>\n";
                echo "     <td align='left' nowrap>\n";
                echo "         加工 指示番号　\n";
                echo "         <input type='text' name='siji_no' class='siji fc_gray' size='6' value='$siji_no' maxlength='5' readonly>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th>3</th>\n";
                echo "     <td align='left' nowrap>\n";
                echo "         部品番号を入力\n";
                echo "         <input type='text' name='parts_no' class='siji' size='11' value='{$parts_no[0]}' maxlength='9' onKeyUp='baseJS.keyInUpper(this);'>\n";
                echo "         {$name[0]}\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th>4</th>\n";
                echo "     <td align='left' nowrap>\n";
                echo "         工程番号を入力\n";
                echo "         <input type='text' name='koutei' class='siji' size='3' value='{$koutei[0]}' maxlength='1'>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <th>5</th>\n";
                echo "     <td align='left' nowrap>\n";
                echo "         生産計画数を入力\n";
                echo "         <input type='text' name='plan_cnt' class='siji' size='8' value='{$plan_cnt[0]}' maxlength='7'>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo " <tr>\n";
                echo "     <td align='center' colspan='3'>\n";
                echo "         <input type='submit' name='init_data_input' value='登録'>\n";
                echo "             &nbsp;&nbsp;\n";
                echo "         <input type='button' name='init_data_cancel' value='取消' onClick='location.replace(\"{$current_script}?init_data_cancel=yes\");'>\n";
                echo "     </td>\n";
                echo " </tr>\n";
                echo "</form>\n";
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ダミーEnd ------------------>\n";
            }
            break;
        case 'init_data_cut':       // 運転中断（機械 運転 停止）
            $query = "select mac_no
                            , m.mac_name
                            , siji_no
                            , parts_no
                            , koutei
                            , plan_cnt
                            , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as str_timestamp
                        from
                            equip_work_log2_header
                        left outer join
                            equip_machine_master2 as m
                        using(mac_no)
                        where
                            work_flg IS TRUE and end_timestamp IS NULL
                            {$fact_where}
                        order by
                            str_timestamp DESC
                    ";
            $res = array();
            if ( ($rows = getResult($query, $res)) >= 1) {  // データベースのヘッダーより運転中データを取得
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                // echo "<caption>現在 加工中の物を中断する。</caption>\n";
                echo " <th width='40' class='fc_white'>中断</th>
                        <th width='80'>機械番号</th><th width='80'>機械名</th><th width='80'>指示番号</th>
                        <th width='80'>部品番号</th><th width='40'>工程</th><th width='80'>計画数</th>
                        <th nowrap>開始 年月日 時間</th>\n";
                for ($r=0; $r<$rows; $r++) {
                    echo "<form name='cut_form' action='", $current_script, "' method='post' onSubmit='return chk_cut_form(this)'>\n";
                    echo "<input type='hidden' name='m_no' value='" . $res[$r][0] . "'>\n";
                    echo "<input type='hidden' name='m_name' value='" . $res[$r][1] . "'>\n";
                    echo "<input type='hidden' name='s_no' value='" . $res[$r][2] . "'>\n";
                    echo "<input type='hidden' name='b_no' value='" . $res[$r][3] . "'>\n";
                    echo "<input type='hidden' name='k_no' value='" . $res[$r][4] . "'>\n";
                    echo "<tr>\n";
                    echo " <td align='center'>
                                <input type='submit' class='number' name='init_data_cut' value='" . ($r + 1) . "'>
                            </td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][0] . "</td>\n";
                    echo " <td align='left' nowrap>" . $res[$r][1] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][4] . "</td>\n";
                    echo " <td align='right' nowrap>" . number_format($res[$r][5]) . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][6] . "</td>\n";
                    echo "</tr>\n";
                    echo "</form>\n";
                }
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ダミーEnd ------------------>\n";
            }
            break;
        case 'init_data_edit':      // 修正（加工指示変更）
            $query = "select mac_no
                            , m.mac_name
                            , siji_no
                            , parts_no
                            , koutei
                            , plan_cnt
                            , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as str_timestamp
                        from
                            equip_work_log2_header
                        left outer join
                            equip_machine_master2 as m
                        using(mac_no)
                        where
                            work_flg IS TRUE and end_timestamp is NULL
                            {$fact_where}
                        order by
                            str_timestamp DESC
                    ";
            $res = array();
            if (($rows=getResult($query,$res)) >= 1) {  // データベースのヘッダーより運転中データを取得
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                // echo "<caption>入力データの修正</caption>\n";
                echo " <th width='20' nowrap>No.</th><th width='40' class='fc_yellow'>編集</th>
                        <th width='80'>機械番号</th><th width='80'>機械名</th><th width='80'>指示番号</th>
                        <th width='80'>部品番号</th><th width='40'>工程</th><th width='80'>計画数</th>
                        <th>開始 年月日 時間</th>\n";
                for ($r=0; $r<$rows; $r++) {
                    echo "<form action='", $menu->out_action('work_edit'), "' method='post' target='application'>\n";
                    echo "<input type='hidden' name='mac_no' value='" . $res[$r][0] . "'>\n";
                    echo "<input type='hidden' name='siji_no' value='" . $res[$r][2] . "'>\n";
                    echo "<input type='hidden' name='koutei' value='" . $res[$r][4] . "'>\n";
                    echo "<tr>\n";
                    $num = $r+1;
                    echo "<td align='center'>$num</td>\n";
                    echo "<td align='center'><input type='submit' class='editButton' name='init_data_edit' value='変更'></td>\n";
                    echo "<td align='center' nowrap>" . $res[$r][0] . "<input type='hidden' name='mac_no' size='4' value='" . $res[$r][0] . "' maxlength='4' class='center'></td>\n";
                    echo "<td align='left' nowrap>" . $res[$r][1] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][4] . "</td>\n";
                    echo " <td align='right' nowrap>" . number_format($res[$r][5]) . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][6] . "</td>\n";
                    echo "</tr>\n";
                    echo "</form>\n";
                }
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ダミーEnd ------------------>\n";
            }
            break;
        case 'init_data_end':       // 加工完了
            $query = "select mac_no
                            ,m.mac_name
                            ,h.siji_no
                            ,h.parts_no
                            ,h.koutei
                            ,h.plan_cnt
                            , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as str_timestamp
                        from
                            equip_work_log2_header as h
                        left outer join
                            equip_machine_master2 as m
                        using(mac_no)
                        where
                            work_flg IS TRUE and end_timestamp IS NULL
                            {$fact_where}
                        order by
                            str_timestamp DESC
                    ";
            $res = array();
            if (($rows=getResult($query,$res)) >= 1) {  // データベースのヘッダーより運転中データを取得
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                // echo "<caption>加工 完了 指示</caption>\n";
                echo " <th width='40' class='fc_white'>完了</th><th width='80'>機械番号</th><th width='80'>機械名</th><th width='80'>指示番号</th><th width='80'>部品番号</th><th width='80'>工程番号</th><th width='80'>計画数</th><th width='80'>実績数</th>\n";
                for ($r=0; $r<$rows; $r++) {
                    echo "<form action='", $current_script, "' method='post' onSubmit='return chk_end_inst(this)'>\n";
                    echo "<input type='hidden' name='m_no' value='" . $res[$r][0] . "'>\n";
                    echo "<input type='hidden' name='m_name' value='" . $res[$r][1] . "'>\n";
                    echo "<input type='hidden' name='s_no' value='" . $res[$r][2] . "'>\n";
                    echo "<input type='hidden' name='b_no' value='" . $res[$r][3] . "'>\n";
                    echo "<input type='hidden' name='k_no' value='" . $res[$r][4] . "'>\n";
                    echo "<tr>\n";
                    echo " <td align='center'>
                                <input type='submit' class='number' name='init_data_end' value='" . ($r + 1) . "'>
                            </td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][0] . "</td>\n";
                    echo " <td align='left' nowrap>" . $res[$r][1] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][4] . "</td>\n";
                    echo " <td align='right' nowrap>" . number_format($res[$r][5]) . "</td>\n";
                    echo " <td align='center' nowrap><input type='text' name='jisseki' size='8' value='" . $res[$r][5] . "' maxlength='7' class='right'></td>\n";
                    echo "</tr>\n";
                    echo "</form>\n";
                }
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ダミーEnd ------------------>\n";
            }
            break;
        case 'plan_data':       // 予定計画はスケジューラーへ移行するため以下は使わない
            $query = "select mac_no, siji_no, buhin_no, koutei, plan_su, plan_str, plan_end
                        from
                            equip_plan
                        where
                            plan_flg IS TRUE
                            -- {$fact_where}
                        order by plan_str, plan_end
            ";
            $res = array();
            if (($rows=getResult($query, $res)) >= 1) {     // equip_plan より予定計画を取得
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                // echo "<caption>予定計画より運転開始指示</caption>\n";
                echo " <th width='40' class='fc_white'>開始</th><th width='80'>機械番号</th><th width='80'>指示番号</th><th width='80'>部品番号</th><th width='80'>工程番号</th><th width='80'>計画数</th><th nowrap>開始 年月日</th><th nowrap>終了 年月日</th>\n";
                for ($r=0; $r<$rows; $r++) {
                    echo "<form action='", $current_script, "' method='post'>\n";
                    echo "<input type='hidden' name='m_no' value='" . $res[$r][0] . "'>\n";
                    echo "<input type='hidden' name='s_no' value='" . $res[$r][1] . "'>\n";
                    echo "<input type='hidden' name='b_no' value='" . $res[$r][2] . "'>\n";
                    echo "<input type='hidden' name='k_no' value='" . $res[$r][3] . "'>\n";
                    echo "<input type='hidden' name='p_no' value='" . $res[$r][4] . "'>\n";
                    echo "<tr>\n";
                    echo " <td align='center'><input type='submit' class='number' name='plan_to_start' value='" . ($r + 1) . "'></td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][0] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][1] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
                    echo " <td align='right' nowrap>" . $res[$r][4] . "</td>\n";
                    echo " <td align='center' nowrap>" . date("Y/m/d",$res[$r][5]) . "</td>\n";
                    echo " <td align='center' nowrap>" . date("Y/m/d",$res[$r][6]) . "</td>\n";
                    echo "</tr>\n";
                    echo "</form>\n";
                }
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ダミーEnd ------------------>\n";
            }
            break;
        case 'break_data':      // 中断計画
            $query = "
                SELECT mac_no
                    , m.mac_name
                    , siji_no
                    , parts_no
                    , koutei
                    , plan_cnt
                    , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') AS str_timestamp
                    , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYYMMDDHH24MISS') AS searchTime
                FROM
                    equip_work_log2_header AS h
                LEFT OUTER JOIN
                    equip_machine_master2 AS m
                USING(mac_no)
                WHERE
                    work_flg IS FALSE AND end_timestamp IS NULL
                    {$fact_where}
                ORDER BY str_timestamp DESC
            ";
            $res = array();
            if (($rows=getResult($query,$res)) >= 1) {  // データベースのヘッダー履歴より中断計画を取得
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
                echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
                echo "<caption>現在 中断されている計画 (削除は完全削除なので注意)</caption>\n";
                echo " <th width='40' class='fc_white'>再開</th><th width='40' class='fc_red'>削除</th>
                        <th width='70'>機械番号</th><th width='80'>機械名</th><th width='70'>指示番号</th>
                        <th width='80'>部品番号</th><th width='40'>工程</th><th width='80'>計画数</th>
                        <th nowrap>開始 年月日 時間</th>
                        <th nowrap>中断 指示 日時</th>\n";
                for ($r=0; $r<$rows; $r++) {
                    ///// ここで中断時間を取得する
                    $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as cut_timestamp
                                from
                                    equip_work_log2
                                where
                                    equip_index(mac_no, siji_no, koutei, date_time) >= '{$res[$r][0]}{$res[$r][2]}{$res[$r][4]}{$res[$r][7]}'
                                    -- date_time > '{$res[$r][6]}'
                                and
                                    equip_index(mac_no, siji_no, koutei, date_time) <  '{$res[$r][0]}{$res[$r][2]}{$res[$r][4]}99999999'
                                    -- mac_no={$res[$r][0]} and siji_no={$res[$r][2]} and koutei={$res[$r][4]} and mac_state=9 -- 中断
                                order by
                                    equip_index(mac_no, siji_no, koutei, date_time) DESC
                                    -- date_time DESC
                                offset 0 limit 1
                    ";
                    if (getUniResult($query, $cut_timestamp) <= 0) {
                        $cut_timestamp = '　';
                    }
                    echo "<form name='break_form' action='", $current_script, "' method='post'>\n";
                    echo "<input type='hidden' name='m_no' value='" . $res[$r][0] . "'>\n";
                    echo "<input type='hidden' name='m_name' value='" . $res[$r][1] . "'>\n";
                    echo "<input type='hidden' name='s_no' value='" . $res[$r][2] . "'>\n";
                    echo "<input type='hidden' name='b_no' value='" . $res[$r][3] . "'>\n";
                    echo "<input type='hidden' name='k_no' value='" . $res[$r][4] . "'>\n";
                    echo "<input type='hidden' name='p_no' value='" . $res[$r][5] . "'>\n";
                    echo "<tr>\n";
                    echo " <td align='center'>
                                <input type='submit' class='number' name='break_restart' value='" . ($r + 1) . "' onClick='return chk_break_restart(m_no.value, m_name.value, s_no.value, b_no.value)'>
                            </td>\n";
                    echo " <td align='center'>
                                <input type='submit' class='number' name='break_del' value='" . ($r + 1) . "' onClick='return chk_break_del(m_no.value, m_name.value, s_no.value, b_no.value)'>
                            </td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][0] . "</td>\n";
                    echo " <td align='left' nowrap>" . $res[$r][1] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][4] . "</td>\n";
                    echo " <td align='right' nowrap>" . number_format($res[$r][5]) . "</td>\n";
                    echo " <td align='center' nowrap>" . $res[$r][6] . "</td>\n";
                    echo " <td align='center' nowrap>{$cut_timestamp}</td>\n";
                    echo "</tr>\n";
                    echo "</form>\n";
                }
                echo "</table>\n";
                echo "    </td></tr>\n";
                echo "</table> <!----------------- ダミーEnd ------------------>\n";
            }
            break;
        default:            // 現在加工中データ表示
            echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
            // echo "<caption>現在 加工中</caption>\n";
            echo "<tr><td>\n";  // ダミー
            echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
            echo " <th>No.</th><th>機械番号</th><th>機械名</th><th>指示番号</th>
                    <th>部品番号</th><th>部品名</th><th>工程</th><th>計画数</th><th>開始 年月日 時間</th>
                    <th>CSV</th>\n";
            
            ///////////////////// 中村留のCSVファイルを使用して表示する｡-->CSVの出力チェック(*)のみに変更
            if ( ($fp = fopen(EQUIP_INDEX, 'r')) ) {
                $row_csv = 0;
                while ($csv_data[$row_csv] = fgetcsv ($fp, 100, ",")) {
                    $row_csv++;
                }
                fclose ($fp);
            } else {
                $row_csv = 0;
                $_SESSION['s_sysmsg'] = 'CSVファイルを開くことが出来ません！';
            }
            
            //////////////////// 中村留 以外 ヘッダーファイルを使用して表示する｡
            $query = "select mac_no
                            , mac_name
                            , siji_no
                            , parts_no
                            , midsc
                            , koutei
                            , plan_cnt
                            , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as str_timestamp
                    from (
                                equip_work_log2_header
                            left outer join
                                equip_machine_master2
                            using(mac_no)
                        )
                        left outer join
                            miitem
                        on parts_no=mipn
                    where
                        work_flg is TRUE
                        {$fact_where}
                        -- and csv_flg != '1'
                    order by
                        str_timestamp DESC
                    ";
            $res_list = array();
            if ( ($rows_list=getResult2($query, $res_list)) >= 1) {
                $r = 1;     // 行番号
                foreach ($res_list as $row) {
                    echo "<tr>\n";
                    echo "<td align='center' nowrap>$r</td>\n";
                    $r++;
                    $c = 0;
                    foreach ($row as $col) {
                        if ($c == 6) {                  // 計画数
                            printf("<td width='80' align='right' nowrap>%s</td>\n", number_format($col));
                        } elseif ($c == 4) {            // 部品名の表示制御
                            if ($col != '') {
                                printf("<td align='left' nowrap>%s</td>\n", mb_substr($col, 0,10) ); // 部品名
                            } else {
                                echo "<td align='center' nowrap>-----</td>\n";
                            }
                        } elseif ($c == 1) {            // 機械名
                            echo "<td align='left' nowrap>$col</td>\n";
                        } elseif ($c == 7) {
                            printf("<td align='center' nowrap>%s</td>\n", $col);
                        } else {
                            printf("<td align='center' nowrap>%s</td>\n", $col);
                            if ($c == 0) {
                                $chk_mac_no = $col;         // 機械番号
                            } elseif ($c == 2) {
                                $chk_siji_no = $col;        // 指示番号
                            } elseif ($c == 5) {
                                $chk_koutei = $col;         // 工程番号
                            }
                        }
                        $c++;
                    }
                    $csv_chk = FALSE;
                    for ($i=0; $i<$row_csv; $i++) {
                        if ( ((int)$csv_data[$i][0] == (int)$chk_mac_no) && ((int)$csv_data[$i][1] == (int)$chk_siji_no) && ((int)$csv_data[$i][3] == (int)$chk_koutei) ) {
                            $csv_chk = TRUE;
                            break;
                        }
                    }
                    if ($csv_chk) {
                        echo "<td align='center' nowrap>*</td>\n";     // CSV(中留のnetmoni)出力されている
                    } else {
                        echo "<td align='center' nowrap>　</td>\n";     // CSV出力されていない
                    }
                    echo "</tr>\n";
                }
            }
            echo "</table>\n";
            echo "</td></tr>\n";  // ダミー
            echo "</table>\n";
            break;      // 必要ないが一応
        }
        ?>
    </center>
</body>
<?php echo $menu->out_retF2Script()?>
<?php echo $menu->out_alert_java(FALSE)?>
</html>
<?php
ob_end_flush();
?>
