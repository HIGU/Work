<?php
////////////////////////////////////////////////////////////////////////////////
// 機械稼働管理指示メンテナンス                                               //
//                                               MVC View 部 リスト表示(List) //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created monitoring_ViewList.php                                 //
// 2021/03/24 Release.                                                        //
// 2021/10/20 標準作業書をQC工程表（xls）に変更。 2901～2903はmac_noで        //
//            外観～(PDF)をmac_noへ変更                                  大谷 //
////////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);              // E_ALL='2047' debug 用
// ini_set('display_errors', '1');                 // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                       // 出力バッファをgzip圧縮
session_start();                                // ini_set()の次に指定すること Script 最上行

require_once ('../../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../../function.php');            // TNK 全共通 function
require_once ('../EquipControllerHTTP.php');    // TNK 全共通 MVC Controller Class
//class monitoring_Model
require_once ('monitoring_Model.php');          // MVC の Model部
//class monitoring_Controller
require_once ('monitoring_Controller.php');     // MVC の Controller部
access_log();                                   // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

///// 設備専用セッションクラスのインスタンスを作成
$equipSession = new equipSession();

$request = new Request();

////////////// target設定
// $menu->set_target('application');   // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');       // フレーム版の戻り先はtarget属性が必須

//////////// 呼出先のaction名とアドレス設定
$menu->set_action('運転グラフ', EQUIP2 . 'work/equip_work_monigraph.php');
$menu->set_action('現在稼動表', EQUIP2 . 'work/equip_work_monichart.php');
$menu->set_action('スケジュール', EQUIP2 . 'plan/equip_plan_monigraph.php');

// 運転グラフと運転状況 の[戻る]にURLがうまく入らないので、強制的にセット
$RetName = EQUIP2 . 'work/equip_work_monichart.php_ret';    // 戻り先のセッション変数名の生成ルール
$_SESSION["$RetName"] = EQUIP2 . 'monitoring/monitoring_Main.php?state=run';    // 戻り先をセット
$RetName = EQUIP2 . 'work/equip_work_monigraph.php_ret';    // 戻り先のセッション変数名の生成ルール
$_SESSION["$RetName"] = EQUIP2 . 'monitoring/monitoring_Main.php?state=run';    // 戻り先をセット

//////////// 子フレームに対応させるため自分自身をフレーム宣言のスクリプト名に変える
//$menu->set_self(EQUIP2 . 'monitoring/monitoring_ViewMain.php');
////////////// リターンアドレス設定
//$menu->set_RetUrl(EQUIP2 . 'monitoring/monitoring_ViewMain.php');   // 通常は指定する必要はない

// 運転グラフと運転状況 から戻ってきた際に、取得
if( isset($_SESSION['work_mac_no']) ) {
    $request->add('m_no', $_SESSION['work_mac_no']);
    unset($_SESSION['work_mac_no']);
}
if( isset($_SESSION['work_plan_no']) ) {
    $request->add('plan_no', $_SESSION['work_plan_no']);
    unset($_SESSION['work_plan_no']);
}

//////////// ビジネスモデル部のインスタンス生成
$model = new Monitoring_Model($request);

$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    ///// リクエストが無ければセッションから工場区分を取得する。(通常はこのパターン)
    $factory = @$_SESSION['factory'];
}

$selectMode   = $request->get('select_mode');   // ターゲットメニューを取得
$state   = $request->get('state');              // ターゲットメニューを取得

if ($selectMode == '' ) {
    if (isset($_REQUEST['selectMode'])) {
        $selectMode = $_REQUEST['selectMode'];
    } else {
        $selectMode = 'start';
    }
}

if ($state == '' ) {
    if (isset($_REQUEST['state'])) {
        $state = $_REQUEST['state'];
    } else {
        $state = 'init';
    }
}

//echo 'テストＭＳＧ：selectMode:【' . $selectMode . '】state:【' . $state . '】' . $current_script;
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
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<style type="text/css">
<!--
input.number {
    width:              30px;
    font-size:          0.9em;
    font-weight:        bold;
    color:              blue;
}
-->
</style>

<link rel='stylesheet' href='monitoring.css' type='text/css' media='screen'>

<script type='text/javascript' language='JavaScript' src='monitoring.js'>
</script>

</head>

<?php if( $selectMode == 'start' && $state != 'plan_load' && $state != 'delete' && $state != 'end' && $model->GetPlanNo() != '' ) { ?>
<body onLoad='init()'>
<?php } else { ?>
<body onLoad='init2()'>
<?php }?>

<center>

<form name='radioForm' method='post' action='<?php echo $current_script ?>' onSubmit='return true;'>
    <!-- 機械選択画面へ戻る。 -->
</form>

<?php
switch( $selectMode ) { // 作業区分を判別
    case 'start':       // 運転開始
        if( $state=='init' ) {
            $menu->set_caption($model->GetCaption('select'));
            
            echo "<table class='pt10' border='1' cellspacing='0'>\n";
            echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            echo "<table width='100%' class='pt20' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
            echo "  <tr>\n";
            echo "      <!--  bgcolor='#ffffc6' 薄い黄色 -->\n";
            echo "      <td class='winbox' style='background-color:yellow; color:blue;' colspan='3' align='center'>\n";
            echo "          <div class='caption_font'>{$menu->out_caption()}</div>\n";
            echo "      </td>\n";
            echo "  </tr>\n";
            
            echo "  <form name='select_form' method='post' action='{$current_script}' onSubmit='return setState(this)'>\n";
            echo "      <input type='hidden' name='state' id='id_state'>\n";
            echo "      <input type='hidden' name='m_no' id='id_m_no'>\n";
            echo "      <input type='hidden' name='m_name' id='id_m_name'>\n";
            echo "      <input type='hidden' name='plan_no' id='id_plan_no'>\n";
            
            if( ($rows=$model->GetFactoryMachineInfo($res, 6)) <= 0 ) {
                return ;    // 指定工場の機械情報がないとき。
            } else {
                $next = true;   // 3列表示したら次の行へ
                for( $r=0,$cnt=0; $r<$rows; $r++ ) {
                    if( $next ) {
            echo "      <tr>\n";
                    }
                    if( $res[$r][2] == 'Y' ) {  // 有効 表示
            echo "          <td align='center'>\n";
                                $plan_no = $model->GetRunningPlanNo($res[$r][0]);
//            echo "              <input type='submit' value='機械番号：{$res[$r][0]}\n機 械 名：{$res[$r][1]}\n計画番号：$plan_no' onClick='setSlectInfo($r)'>\n";
            echo "              <button type='submit' onClick='setSlectInfo($r)'>機械番号：{$res[$r][0]}<BR>機 械 名：{$res[$r][1]}<BR>計画番号：$plan_no</button>\n";
            echo "          </td>\n";
            echo "          <input type='hidden' name='m_no$r' id='id_m_no$r' value='{$res[$r][0]}'>\n";
            echo "          <input type='hidden' name='m_name$r' id='id_m_name$r' value='{$res[$r][1]}'>\n";
            echo "          <input type='hidden' name='plan_no$r' id='id_plan_no$r' value='$plan_no'>\n";
                            $cnt++;
                    }
                    if( $cnt >= 3) {    // 3列目表示したら次の行へ
                        $cnt = 0;
                        $next = true;
                    } else { 
                        $next = false;
                    }
                    if( $next ) {
            echo "      </tr>\n";
                    }
                }
                if( !$next ) {  // 3列分表示してないとき
                    for( ; $cnt<3; $cnt++) {
            echo "          <td>　</td>\n";  // 残りの列を　表示
                    }
            echo "      </tr>\n";
                }
            }
            echo "  </form>\n";
            echo "</table>\n";
            echo "</td></tr>\n";
            echo "</table> <!----------------- ダミーEnd --------------------->\n";
        } else {    // $state!='init'
//            $menu->set_caption($model->GetCaption());
            $style0 = "style='background-color:White'";
            $style1 = "style='background-color:CornSilk'";
            $style2 = "style='background-color:Gold'";
            $style3 = "style='background-color:LightCyan'";
            $button_style = "style='width:120px;height:50px'";
            if( $model->IsPlanNo() ) {
                $model->GetViewDate($request);
            }
            echo "<table class='pt10' border='1' cellspacing='0'>\n";
            echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            echo "<table width='100%' class='pt20' bgcolor='LightCyan' align='center' border='1' cellspacing='0' cellpadding='3'>\n"; // bgcolor='#d6d3ce'
/*
            echo "  <tr>\n";    // キャプション
            echo "      <!--  bgcolor='#ffffc6' 薄い黄色 -->\n";
            echo "      <td class='winbox' style='background-color:yellow; color:blue;' colspan='3' align='center'>\n";
            echo "          <div class='caption_font'>{$menu->out_caption()}</div>\n";
            echo "      </td>\n";
            echo "  </tr>\n";
/**/
            
            echo "  <form name='main_form' method='post' action='$current_script' onSubmit='return planNoCheck()'>\n";
            echo "      <input type='hidden' name='state' id='id_state'>\n";
            echo "      <tr $style1>\n";    // [戻る]・機械名
            echo "          <td nowrap align='center'>\n";
            echo "              <input type='button' $button_style value='機械選択へ戻る' onclick='document.radioForm.submit()'>\n";
            echo "          </td>\n";
            echo "          <input type='hidden' name='m_no' id='id_m_no' value='{$request->get('m_no')}'>\n";
            echo "          <td nowrap align='center' colspan='3'>\n";
                            $request->add('m_name', $model->GetMacName($request->get('m_no')));
                            if( $model->GetRunningPlanNo($request->get('m_no')) != '--------' ) {
            echo "              <a href='{$menu->out_action('運転グラフ')}?mac_no={$request->get('m_no')}' target='_parent'>{$request->get('m_name')}</a>\n";
                            } else {
            echo "              {$request->get('m_name')}\n";
                            }
            echo "          </td>\n";
            echo "          <td nowrap align='center' class='pt9'>\n";
                                $w_date = date('Y') . "/" . date('m') . "/" . date('d');
            echo "              作業日：$w_date\n";
            echo "          </td>\n";
            echo "          <input type='hidden' name='m_name' id='id_m_name' value='{$request->get('m_name')}'>\n";
            echo "      </tr>\n";

            echo "      <tr $style1>\n";    // 項目タイトル
            echo "          <td nowrap align='center'>ASSY No.</td>\n";
            echo "          <td nowrap align='center'>製品名</td>\n";
            echo "          <td nowrap align='center'>本体材質</td>\n";
            echo "          <td nowrap align='center'>計画No.</td>\n";
            echo "          <td nowrap align='center'>納期</td>\n";
            echo "      </tr>\n";
                        if( $model->IsPlanNo() ) {
            echo "      <tr $style1>\n";    // 項目内容
            echo "          <td nowrap align='center'>\n";
            echo "              {$model->GetPartsNo()}\n";
            echo "              <input type='hidden' name='b_no' value='{$model->GetPartsNo()}'>\n";
            echo "          </td>\n";
            echo "          <td nowrap align='center'>\n";
            echo "              {$model->GetPartsName()}\n";
            echo "          </td>\n";
            echo "          <td nowrap align='center' style='color:red'>情報なし</td>\n";
            echo "          <td nowrap align='center'>\n";
            echo "              {$model->GetPlanNo()}\n";
            echo "              <input type='hidden' name='plan_no' id='id_plan_no' value='{$request->get('plan_no')}'>\n";
            echo "          </td>\n";
            echo "          <td nowrap align='center'>{$model->GetDeadLines()}</td>\n";
            echo "      </tr>\n";
                        } else {
            echo "      <tr $style1>\n";
            echo "          <td nowrap align='center'>-----</td>\n";
            echo "          <td nowrap align='center'>-----</td>\n";
            echo "          <td nowrap align='center'>-----</td>\n";
            echo "          <td nowrap align='center'>\n";
            echo "              <input type='text' style='font-size:40px;height:50px' size='9' maxlength='8' name='plan_no' id='id_plan_no' onkeyup='obj_upper(this)'>";
            echo "              <input type='submit' $button_style value='読込み' name='plan_load' id='id_plan_load' onClick='setState(this);'>";
            echo "          </td>\n";
            echo "          <td nowrap align='center'>--月--日</td>\n";
            echo "      </tr>\n";
                        }

            if( $model->IsPlanNo() ) {  // 計画番号読み込みOKなら表示する。
            echo "      <tr>\n";    // 生産指示数・段取り指示書
            echo "          <td nowrap align='center' colspan='3' $style1>\n";
            echo "              生産指示数：" . number_format($model->GetPlan()) . " 個\n";
            echo "          </td>\n";
            echo "          <td nowrap align='center' colspan='2' $style2>\n";
                                $file_mac = $request->get('m_no');
                                $filename = "pdf/" . $file_mac . ".pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$file_mac}.pdf'>段取り指示書</a>\n";
                                } else {
            echo "                  段取り指示書\n";
                                }
            echo "          </td>\n";
            echo "      </tr>\n";
/*
            echo "      <tr>\n";    // 空欄・標準作業書
            echo "          <td nowrap align='center' colspan='3' $style0>　</td>\n";
            echo "          <td nowrap align='center' colspan='2' $style2>\n";
                                $file_parts = $model->GetPartsNo();
                                $filename = "pdf/" . $file_parts . "-H.pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$file_parts}-H.pdf'>標準作業書</a>\n";
                                } else {
            echo "                  標準作業書\n";
                                }
            echo "          </td>\n";
            echo "      </tr>\n";
*/
            echo "      <tr>\n";    // 空欄・QC工程表
            echo "          <td nowrap align='center' colspan='3' $style0>　</td>\n";
            echo "          <td nowrap align='center' colspan='2' $style2>\n";
                                $file_parts = $model->GetPartsNo();
                                $filename = "pdf/" . $request->get('m_no') . "-Q.pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$request->get('m_no')}-Q.pdf'>QC工程表</a>\n";
                                } else {
            echo "                  QC工程表\n";
                                }
            echo "          </td>\n";
            echo "      </tr>\n";
            
            echo "      <tr>\n";    // 総生産数・不具合情報（１行目）
            echo "          <td nowrap align='center' colspan='3' $style3>\n";
                            if( $model->GetRunningPlanNo($request->get('m_no')) != '--------' ) {
            echo "              <a href='{$menu->out_action('現在稼動表')}?mac_no={$request->get('m_no')}' target='_parent'>生産数</a>\n";
                            } else {
            echo "              総生産数\n";
                            }
                            $jisseki = $model->GetProNum($request->get('plan_no'),$request->get('m_no'));
            echo "          " . number_format($jisseki). "個\n";
            echo "          <input type='hidden' name='jisseki' value='$jisseki'>\n";
            echo "          </td>\n";
            if( $model->GetPlan() < $jisseki ) {
                $_SESSION['s_sysmsg'] = '生産数が、生産指示数を超えました！！';
            }
/*
            echo "          <td nowrap align='center' colspan='2' rowspan='2' $style2>\n";
                                $file_parts = $model->GetPartsNo();
                                $filename = "pdf/" . $file_parts . "-G.pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$file_parts}-G.pdf'>外観・着脱抜き取り数<BR>過去不具合情報</a>\n";
                                } else {
            echo "                  外観・着脱抜き取り数<BR>過去不具合情報\n";
                                }
            echo "          </td>\n";
*/
/*
                            // 外観を機械No-Gに変更
            echo "          <td nowrap align='center' colspan='2' rowspan='2' $style2>\n";
                                $file_parts = $model->GetPartsNo();
                                $filename = "pdf/" . $request->get('m_no') . "-G.pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$request->get('m_no')}-G.pdf'>外観・着脱抜き取り数<BR>過去不具合情報</a>\n";
                                } else {
            echo "                  外観・着脱抜き取り数<BR>過去不具合情報\n";
                                }
            echo "          </td>\n";
*/
                            // 外観を機械No-Gに変更
            echo "          <td nowrap align='center'  colspan='2' $style2>\n";
                                $file_parts = $model->GetPartsNo();
                                $filename = "pdf/" . $request->get('m_no') . "-G.pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$request->get('m_no')}-G.pdf'>製品検査規格</a>\n";
                                } else {
            echo "                  製品検査規格\n";
                                }
            echo "          </td>\n";

            echo "      </tr>\n";

            echo "      <tr>\n";    // 設備の状態・不具合情報（２行目）
                            $m_state = $model->GetRunState($request->get('plan_no'),$request->get('m_no'), $bg_color, $txt_color);
            echo "          <td nowrap align='center' colspan='3' style='color:$txt_color; background-color:$bg_color'>\n";
            echo "              設備の状態：$m_state\n";
            echo "          </td>\n";
                            // 過去不具合情報は製品番号
            echo "          <td nowrap align='center' colspan='2' $style2>\n";
                                $file_parts = $model->GetPartsNo();
                                $filename = "pdf/" . $request->get('m_no') . "-K.pdf";
                                if (file_exists($filename)) {
            echo "                  <a href='pdf/download_file.php/{$request->get('m_no')}-K.pdf'>過去不具合情報</a>\n";
                                } else {
            echo "                  過去不具合情報\n";
                                }
            echo "          </td>\n";
            echo "      </tr>\n";
                        if( $model->GetState() != 'start' ) {
            echo "      <tr $style0>\n";    // 開始・計画番号入力へ戻る
            echo "          <td nowrap align='center' colspan='5'>\n";
            echo "              <input type='submit' $button_style value='開始' name='start' id='id_start' onClick='setState(this);'>　　\n";
            echo "              <input type='submit' $button_style value='計画No.入力へ戻る' name='reset' id='id_reset' onClick='setState(this);'>\n";
            echo "          </td>\n";
            echo "      </tr>\n";
                        }
            }   // ↑↑↑ 計画番号読み込みOKなら表示する。
            
            if( $model->GetState() == 'start' ) {   // 運転開始後、表示する。
/**
            echo "      <tr>\n";    // QC工程表
            echo "          <td nowrap align='center'>\n";
                            $filename = "pdf/" . $file_parts . "-Q.pdf";
                            if (file_exists($filename)) {
            echo "              <a href='pdf/download_file.php/$file_parts-Q.pdf'>QC工程表</a>\n";
                            } else {
            echo "              QC工程表\n";
                            }
            echo "          </td>\n";
            echo "      </tr>\n";
/**/
            echo "      <tr $style0>\n";    // 稼働時間・ボタン表示領域
            echo "          <td nowrap align='center' colspan='5'>\n";
                            switch( $model->GetHeaderInfo() ) { // ヘッダー情報
                                case 'run':     // 開始
            echo "                  <input type='submit' $button_style value='完了' name='end' id='id_end' onClick='chk_end_inst(state, m_no.value, m_name.value, plan_no.value, b_no.value);'>　　";
            echo "                  <input type='submit' $button_style value='中断' name='break' id='id_break' onClick='chk_cut_form(state, m_no.value, m_name.value, plan_no.value, b_no.value);'>";
                                    break;
                                case 'break':   // 中断
            echo "                  <input type='submit' $button_style value='再開' name='restart' id='id_restart' onClick='chk_break_restart(state, m_no.value, m_name.value, plan_no.value, b_no.value);'>　";
            echo "                  <input type='submit' $button_style value='削除' name='delete' id='id_delete' onClick='chk_break_del(state, m_no.value, m_name.value, plan_no.value, b_no.value);'>　";
                                default:        // その他
            echo "                  <input type='submit' $button_style value='計画No.入力へ戻る' name='reset' id='id_reset' onClick='setState(this);'>";
                                    break;
                            }
            echo "          </td>\n";
            echo "      </tr>\n";
            }   // ↑↑↑ 運転開始後、表示する。
            
            echo "  </form>\n";
            echo "</table>\n";
            echo "</td></tr>\n";
            echo "</table> <!----------------- ダミーEnd --------------------->\n";
        }
        break;
    case 'break':   // 中断計画
        if( $state != 'init' ) {
            $model->GetViewDate($request); // 再開 or 削除 を行った後の表示項目を再読み込みする。
        }
        $query = "
            SELECT mac_no
                , m.mac_name
                , plan_no
                , parts_no
                , koutei
                , plan_cnt
                , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') AS str_timestamp
                , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYYMMDDHH24MISS') AS searchTime
            FROM
                equip_work_log2_header_moni AS h
            LEFT OUTER JOIN
                equip_machine_master2 AS m
            USING(mac_no)
            WHERE
                work_flg IS FALSE AND end_timestamp IS NULL
                AND factory = '" . $factory . "'
            ORDER BY str_timestamp DESC
        ";
        $res = array();
        if (($rows=getResult($query,$res)) >= 1) {  // データベースのヘッダー履歴より中断計画を取得
            echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
            echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
            $menu->set_caption("現在 中断されている計画 (削除は完全削除なので注意)");
            echo "  <tr>\n";    // キャプション
            echo "      <td class='winbox' style='background-color:yellow; color:blue;' colspan='10' align='center'>\n";
            echo "          <div class='caption_font'>{$menu->out_caption()}</div>\n";
            echo "      </td>\n";
            echo "  </tr>\n";
            
            echo "  <th width='40' class='fc_white'>再開</th>
                    <th width='40' class='fc_red'>削除</th>
                    <th width='70'>機械番号</th>
                    <th width='80'>機械名</th>
                    <th width='70'>計画番号</th>
                    <th width='80'>製品番号</th>
                    <th width='40'>工程</th>
                    <th width='80'>計画数</th>
                    <th nowrap>開始 年月日 時間</th>
                    <th nowrap>中断 指示 日時</th>\n";
            for ($r=0; $r<$rows; $r++) {
                ///// ここで中断時間を取得する
                $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as cut_timestamp
                            from
                                equip_work_log2_moni
                            where
                                plan_no='{$res[$r][2]}' and mac_no={$res[$r][0]} and koutei={$res[$r][4]} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') >={$res[$r][7]}
                            order by
                                date_time DESC
                            offset 0 limit 1
                ";
                /*
                $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as cut_timestamp
                            from
                                equip_work_log2_moni
                            where
                                equip_moni_index(mac_no, plan_no, koutei, date_time) >= '{$res[$r][0]}{$res[$r][2]}{$res[$r][4]}{$res[$r][7]}'
                                -- date_time > '{$res[$r][6]}'
                            and
                                equip_moni_index(mac_no, plan_no, koutei, date_time) <  '{$res[$r][0]}{$res[$r][2]}{$res[$r][4]}99999999'
                                -- mac_no={$res[$r][0]} and plan_no={$res[$r][2]} and koutei={$res[$r][4]} and mac_state=9 -- 中断
                            order by
                                equip_moni_index(mac_no, plan_no, koutei, date_time) DESC
                                -- date_time DESC
                            offset 0 limit 1
                ";
                */
                if (getUniResult($query, $cut_timestamp) <= 0) {
                    $cut_timestamp = '　';
                }
            echo "  <form name='break_form' action='", $current_script, "?select_mode=break' method='post'>\n";
            echo "      <input type='hidden' name='state'>\n";
            echo "      <input type='hidden' name='m_no' value='" . $res[$r][0] . "'>\n";
            echo "      <input type='hidden' name='m_name' value='" . $res[$r][1] . "'>\n";
            echo "      <input type='hidden' name='plan_no' value='" . $res[$r][2] . "'>\n";
            echo "      <input type='hidden' name='b_no' value='" . $res[$r][3] . "'>\n";
            echo "      <input type='hidden' name='k_no' value='" . $res[$r][4] . "'>\n";
            echo "      <input type='hidden' name='plan' value='" . $res[$r][5] . "'>\n";
            echo "      <tr>\n";    // 中断計画を表示
            echo "          <td align='center'>
                                <input type='submit' class='number' name='break_restart' value='" . ($r + 1) . "' onClick='return chk_break_restart(state, m_no.value, m_name.value, plan_no.value, b_no.value)'>
                            </td>\n";
            echo "          <td align='center'>
                                <input type='submit' class='number' name='break_del' value='" . ($r + 1) . "' onClick='return chk_break_del(state, m_no.value, m_name.value, plan_no.value, b_no.value)'>
                            </td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][0] . "</td>\n";
            echo "          <td align='left' nowrap>" . $res[$r][1] . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][4] . "</td>\n";
            echo "          <td align='right' nowrap>" . number_format($res[$r][5]) . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][6] . "</td>\n";
            echo "          <td align='center' nowrap>{$cut_timestamp}</td>\n";
            echo "      </tr>\n";
            echo "  </form>\n";
            }
            echo "</table>\n";
            echo "</td></tr>\n";
            echo "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            echo "<BR><font align='center' nowrap>現在、中断されている計画はありません。</font>\n";
        }
        break;
    case 'change':  // 指示変更
        $query = "select mac_no
                        , m.mac_name
                        , plan_no
                        , parts_no
                        , koutei
                        , plan_cnt
                        , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as str_timestamp
                    from
                        equip_work_log2_header_moni
                    left outer join
                        equip_machine_master2 as m
                    using(mac_no)
                    where
                        work_flg IS TRUE and end_timestamp is NULL
                        AND factory = '" . $factory . "'
                    order by
                        str_timestamp DESC
                ";
        $res = array();
        if (($rows=getResult($query,$res)) >= 1) {  // データベースのヘッダーより運転中データを取得
            echo "<table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
            echo "<tr><td> <!----------- ダミー(デザイン用) ------------>\n";
            echo "<table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>\n";
            $menu->set_caption("指示変更したい計画の [変更] ボタンを押して下さい。");
            echo "  <tr>\n";    // キャプション
            echo "      <td class='winbox' style='background-color:yellow; color:blue;' colspan='10' align='center'>\n";
            echo "          <div class='caption_font'>{$menu->out_caption()}</div>\n";
            echo "      </td>\n";
            echo "  </tr>\n";
            
            echo "  <th width='20' nowrap>No.</th>
                    <th width='40' class='fc_yellow'>編集</th>
                    <th width='80'>機械番号</th>
                    <th width='80'>機械名</th>
                    <th width='80'>計画番号</th>
                    <th width='80'>部品番号</th>
                    <th width='40'>工程</th>
                    <th width='80'>計画数</th>
                    <th>開始 年月日 時間</th>\n";
            for ($r=0; $r<$rows; $r++) {
            echo "  <form name='change_form' action='monitoring_edit_chart.php?select_mode=change' method='post' target='application'>\n";
            echo "      <input type='hidden' name='state' id='id_state'>\n";
            echo "      <input type='hidden' name='mac_no' value='" . $res[$r][0] . "'>\n";
            echo "      <input type='hidden' name='plan_no' value='" . $res[$r][2] . "'>\n";
            echo "      <input type='hidden' name='koutei' value='" . $res[$r][4] . "'>\n";
            echo "      <tr>\n";
                            $num = $r+1;
            echo "          <td align='center'>$num</td>\n";
            echo "          <td align='center'><input type='submit' class='editButton' name='edit' value='変更' onClick='setState(this);'></td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][0] . "<input type='hidden' name='mac_no' size='4' value='" . $res[$r][0] . "' maxlength='4' class='center'></td>\n";
            echo "          <td align='left' nowrap>" . $res[$r][1] . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][2] . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][3] . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][4] . "</td>\n";
            echo "          <td align='right' nowrap>" . number_format($res[$r][5]) . "</td>\n";
            echo "          <td align='center' nowrap>" . $res[$r][6] . "</td>\n";
            echo "      </tr>\n";
            echo "  </form>\n";
            }
            echo "</table>\n";
            echo "</td></tr>\n";
            echo "</table> <!----------------- ダミーEnd ------------------>\n";
        } else {
            echo "<BR><font align='center' nowrap>現在、指示変更できる計画はありません。</font>\n";
        }
        break;
    default:        // その他
            echo "<BR><font align='center' nowrap>システム担当者へ連絡して下さい。</font>\n";
        break;
}
?>
</center>
</body>

<!-- 自動更新時に実行される -->
<form name='reload_form' action='monitoring_ViewList.php' method='get' target='_self'>
    <input type='hidden' name='factory' value='<?php echo $factory?>'>
    <input type='hidden' name='selectMode' value='<?php echo $selectMode?>'>
    <input type='hidden' name='state' value='<?php echo $state?>'>
    <input type='hidden' name='m_no' value='<?php echo $request->get('m_no')?>'>
    <input type='hidden' name='m_name'  value='<?php echo $request->get('m_name')?>'>
    <input type='hidden' name='plan_no'  value='<?php echo $request->get('plan_no')?>'>
</form>

<?php echo $menu->out_alert_java()?>
</html>
