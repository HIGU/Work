<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 勘定科目組替表                                              //
// Copyright(C) 2018-2022 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2018/06/26 Created   account_transfer_view.php                           //
// 2018/10/17 19期第2四半期決算の結果を受けて修正                           //
// 2019/01/10 前払消費税をマイナスに19期第3四半期                           //
// 2019/04/09 貯蔵品を2019/03のデータに変更                                 //
// 2019/05/17 日付の取得方法の変更                                          //
// 2019/10/07 貯蔵品を2019/09のデータに変更                                 //
// 2020/04/06 貯蔵品を2020/03のデータに変更                                 //
// 2020/04/13 eCA用のデータ抜出しを追加                                     //
// 2020/06/25 勘定内訳明細書用のデータを追加（20期分）                      //
// 2020/06/30 減価償却費明細書用のデータを追加（20期分）                    //
// 2020/07/08 貯蔵品を2020/06のデータに変更                                 //
// 2021/01/13 各種データを追加（21期12月分）                                //
// 2021/04/08 各種データを追加（21期3月分）                                 //
// 2022/01/12 各種データを追加（22期12月分）                                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

////////////// サイト設定
// $menu->set_site(10, 7);                     // site_index=10(損益メニュー) site_id=7(月次損益)
//////////// 表題の設定
$menu->set_caption('栃木日東工器(株)');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('抽象化名',   PL . 'address.php');


///// 対象当月
$ki2_ym   = 202211;
$yyyymm   = 202211;
$ki       = 22;
$b_yyyymm = $yyyymm - 100;
$p1_ki    = 11;

///// 前期末 年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // 前期末年月

///// 期・半期の取得
$tuki_chk = 12;
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $hanki = '４';
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $hanki = '１';
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $hanki = '２';
} elseif ($tuki_chk >= 10) {    //第３四半期
    $hanki = '３';
}

///// 年月範囲の取得
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 10) {    //第３四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
}
///// TNK期 → NK期へ変換
$nk_ki   = $ki + 44;
$nk_p1ki = $p1_ki + 44;

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($tuki_chk == 3) {
    $menu->set_title("第 {$ki} 期　本決算　勘　定　科　目　組　替　表");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　勘　定　科　目　組　替　表");
}

if (isset($_POST['input_data'])) {                        // 当月データの登録
    ///////// 項目とインデックスの関連付け
    $item = array();
    $item[0]   = "現金及び預金";
    $item[1]   = "貸借製品";
    $item[2]   = "貸借仕掛品";
    $item[3]   = "貸借原材料及び貯蔵品";
    $item[4]   = "未収入金";
    $item[5]   = "その他の流動資産";
    $item[6]   = "建物";
    $item[7]   = "構築物";
    $item[8]   = "機械及び装置";
    $item[9]   = "車輌運搬具";
    $item[10]  = "工具器具及び備品";
    $item[11]  = "リース資産";
    $item[12]  = "減価償却累計額";
    $item[13]  = "電話加入権";
    $item[14]  = "その他の投資等";
    $item[15]  = "未払消費税等";
    $item[16]  = "買掛金";
    $item[17]  = "未払金";
    $item[18]  = "その他の流動負債";
    $item[19]  = "期首仕掛品";
    $item[20]  = "期首原材料及び貯蔵品";
    $item[21]  = "期末仕掛品";
    $item[22]  = "期末原材料及び貯蔵品";
    $item[23]  = "雑収入";
    $item[24]  = "その他の営業外費用";
    $item[25]  = "固定資産除却損";
    $item[26]  = "eca法人税、住民税及び事業税";
    $item[27]  = "販管費旅費交通費";
    $item[28]  = "販管費広告宣伝費";
    $item[29]  = "販管費業務委託費";
    $item[30]  = "販管費諸税公課";
    $item[31]  = "販管費事務用消耗品費";
    $item[32]  = "販管費雑費";
    $item[33]  = "販管費地代家賃";
    $item[34]  = "販管費厚生福利費";
    $item[35]  = "販管費退職給付費用";
    $item[36]  = "製造経費旅費交通費";
    $item[37]  = "製造経費業務委託費";
    $item[38]  = "製造経費雑費";
    $item[39]  = "製造経費地代家賃";
    $item[40]  = "製造経費厚生福利費";
    $item[41]  = "製造経費退職給付費用";
    $item[42]  = "固定資産売却損";
    $item[43]  = "有償支給未収入金";
    $item[44]  = "立替金";
    $item[45]  = "明細未収入金";
    $item[46]  = "仮払金";
    $item[47]  = "明細その他流動資産";
    $item[48]  = "資産金額建物";
    $item[49]  = "減価償却累計額(建物)";
    $item[50]  = "資産金額機械及び装置";
    $item[51]  = "減価償却累計額(機械及び装置)";
    $item[52]  = "資産金額車輌運搬具";
    $item[53]  = "減価償却累計額(車輌運搬具)";
    $item[54]  = "資産金額工具器具備品";
    $item[55]  = "減価償却累計額(工具器具備品)";
    $item[56]  = "資産金額リース資産";
    $item[57]  = "減価償却累計額(リース資産)";
    $item[58]  = "eca法定福利費";                       // 販管費
    $item[59]  = "eca福利厚生費";                       // 販管費
    $item[60]  = "eca倉敷料";                           // 販管費
    $item[61]  = "eca地代家賃";                         // 販管費
    $item[62]  = "eca業務委託費";                       // 販管費
    $item[63]  = "eca支払手数料";                       // 販管費
    $item[64]  = "eca求人費";                           // 販管費
    $item[65]  = "eca諸会費";                           // 販管費
    $item[66]  = "eca雑費";                             // 販管費
    $item[67]  = "eca未収収益";                         // 販管費
    $item[68]  = "資産金額構築物";
    $item[69]  = "減価償却累計額(構築物)";
    $item[70]  = "eca広告宣伝費";                       // 販管費
    $item[71]  = "貯蔵品";
    $item[72]  = "評価切下げ部品";
    $item[73]  = "評価切下げ材料";
    $item[74]  = "工作仕掛明細";
    $item[75]  = "外注仕掛明細";
    $item[76]  = "検査仕掛明細";
    $item[77]  = "電話期首残高";
    $item[78]  = "施設期首残高";
    $item[79]  = "ソフト期首残高";
    $item[80]  = "電話期中増加";
    $item[81]  = "施設期中増加";
    $item[82]  = "ソフト期中増加";
    $item[83]  = "電話期中減少";
    $item[84]  = "施設期中減少";
    $item[85]  = "ソフト期中減少";
    ///////// 各データの保管
    $input_data = array();
    $input_data[0]   = $genyo_total_kin;
    $input_data[1]   = $seihin_total_kin;
    $input_data[2]   = $sikakari_total_kin;
    $input_data[3]   = $gencho_total_kin;
    $input_data[4]   = $ryu_mishu_kin;
    $input_data[5]   = $hokaryudo_total_kin;
    $input_data[6]   = $tate_all_shisan_kin;
    $input_data[7]   = $kouchiku_shisan_kin;
    $input_data[8]   = $kikai_shisan_kin;
    $input_data[9]   = $sharyo_shisan_kin;
    $input_data[10]  = $jyubihin_all_shisan_kin;
    $input_data[11]  = $lease_shisan_kin;
    $input_data[12]  = $gensyo_total_mi_kin;
    $input_data[13]  = $denwa_kin;
    $input_data[14]  = $toushi_total_kin;
    $input_data[15]  = $mihazei_total_kin;
    $input_data[16]  = $kaikake_total_kin;
    $input_data[17]  = $miharai_total_kin;
    $input_data[18]  = $sonota_ryudo_total_kin;
    $input_data[19]  = $z_sikakari_total_kin;
    $input_data[20]  = $z_gencho_total_kin;
    $input_data[21]  = $kimatsu_sikakari_total_kin;
    $input_data[22]  = $kimatsu_gencho_total_kin;
    $input_data[23]  = $eigyo_shueki_total_kin;
    $input_data[24]  = $sonota_eihiyo_kin;
    $input_data[25]  = $kotei_jyoson_kin;
    $input_data[26]  = $eca_hojin_zeito_total_kin;
    $input_data[27]  = $han_ryohi_total_kin;
    $input_data[28]  = $han_kokoku_total_kin;
    $input_data[29]  = $han_gyomu_total_kin;
    $input_data[30]  = $han_zeikoka_total_kin;
    $input_data[31]  = $han_jimuyo_total_kin;
    $input_data[32]  = $han_zappi_total_kin;
    $input_data[33]  = $han_yachin_total_kin;
    $input_data[34]  = $han_kofukuri_total_kin;
    $input_data[35]  = $han_taikyufu_total_kin;
    $input_data[36]  = $sei_ryohi_total_kin;
    $input_data[37]  = $sei_gyomu_total_kin;
    $input_data[38]  = $sei_zappi_total_kin;
    $input_data[39]  = $sei_yachin_total_kin;
    $input_data[40]  = $sei_kofukuri_total_kin;
    $input_data[41]  = $sei_taikyufu_total_kin;
    $input_data[42]  = $kotei_baison_kin;
    $input_data[43]  = $yumi_kin;
    $input_data[44]  = $tatekae_kin;
    $input_data[45]  = $mishu_kin;
    $input_data[46]  = $karibara_kin;
    $input_data[47]  = $hokaryudo_kin;
    $input_data[48]  = $tate_shutoku_kin;
    $input_data[49]  = $tate_rui_kin;
    $input_data[50]  = $kikai_kin;
    $input_data[51]  = -$kikai_gen_kin;
    $input_data[52]  = $sharyo_kin;
    $input_data[53]  = -$sharyo_gen_kin;
    $input_data[54]  = $kikougu_shutoku_kin;
    $input_data[55]  = $kikougu_rui_kin;
    $input_data[56]  = $lease_kin;
    $input_data[57]  = -$lease_gen_kin;
    $input_data[58]  = $han_hofukuri_kin;
    $input_data[59]  = $han_kofukuri_kin;
    $input_data[60]  = $han_kura_kin;
    $input_data[61]  = $han_yachin_kin;
    $input_data[62]  = $han_gyomu_kin;
    $input_data[63]  = $han_tesu_kin;
    $input_data[64]  = $han_kyujin_kin;
    $input_data[65]  = $han_kaihi_kin;
    $input_data[66]  = $han_zappi_kin;
    $input_data[67]  = $mishueki_kin;
    $input_data[68]  = $kouchiku_kin;
    $input_data[69]  = -$kouchiku_gen_kin;
    $input_data[70]  = $han_kokoku_kin;
    $input_data[71]  = $chozo_kin;
    $input_data[72]  = $hyoka_buhin_kin;
    $input_data[73]  = $hyoka_zai_kin;
    $input_data[74]  = $tana_kou_kin;
    $input_data[75]  = $tana_gai_kin;
    $input_data[76]  = $tana_ken_kin;
    $input_data[77]  = $den_kishu_kin;
    $input_data[78]  = $shi_kishu_kin;
    $input_data[79]  = $sft_kishu_kin;
    $input_data[80]  = $den_zou_kin;
    $input_data[81]  = $shi_zou_kin;
    $input_data[82]  = $sft_zou_kin;
    $input_data[83]  = $den_gen_kin;
    $input_data[84]  = $shi_gen_kin;
    $input_data[85]  = $sft_gen_kin;
    ///////// 各データの登録
    insert_date($item,$yyyymm,$input_data);
}


function insert_date($item,$yyyymm,$input_data) 
{
    $num_input = count($input_data);
    for ($i = 0; $i < $num_input; $i++) {
        $query = sprintf("select rep_kin from financial_report_data where rep_ymd=%d and rep_note='%s'", $yyyymm, $item[$i]);
        $res_in = array();
        if (getResult2($query,$res_in) <= 0) {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// Insert Start
            $query = sprintf("insert into financial_report_data (rep_ymd, rep_kin, rep_note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i], $item[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 決算書データ 新規 登録完了</font>",$yyyymm);
        } else {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update financial_report_data set rep_kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where rep_ymd=%d and rep_note='%s'", $input_data[$i], $_SESSION['User_ID'], $yyyymm, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 決算書データ 変更 完了</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "決算書のデータを登録しました。";
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
<?= $menu->out_jsBaseClass() ?>
<script type=text/javascript language='JavaScript'>
<!--
function data_input_click(obj) {
    return confirm("当月のデータを登録します。\n既にデータがある場合は上書きされます。");
}
// -->
</script>
<style type='text/css'>
<!--
.pt10b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          black;
}
.pt11b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   #ffffff;
    color:              blue;
    font:bold           12pt;
    font-family:        monospace;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <?php
            //  bgcolor='#ceffce' 黄緑
            //  bgcolor='#ffffc6' 薄い黄色
            //  bgcolor='#d6d3ce' Win グレイ
        ?>
    <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <center>（貸借対照表）</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>　</th>
                    <th class='winbox' nowrap colspan='4'>試算表</th>
                    <th class='winbox' nowrap colspan='3'>決算書(B/S)</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap colspan='2'>勘定科目</th>
                    <th class='winbox' nowrap>金額</th>
                    <th class='winbox' nowrap>備考</th>
                    <th class='winbox' nowrap colspan='2'>勘定科目</th>
                    <th class='winbox' nowrap>金額</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>１</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>現金</div><BR>
                        <div class='pt10b'>預金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>現金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>現金及び預金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>当座預金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>普通預金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>定期預金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>大口定期</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>２</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>在庫</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>製品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>製品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>製品仕掛品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>仕掛品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>部品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>原材料及び貯蔵品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>部品仕掛品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>原材料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の棚卸品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>貯蔵品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>３</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>有償支給未収入金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>未収入金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>未収入金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>未収収益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>立替金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>仮払金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>４</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>有形</div><BR>
                        <div class='pt10b'>固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>建物</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>有形</div><BR>
                        <div class='pt10b'>固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>建物</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>設備</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>（設備加算）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>構築物</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>構築物</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>機械装置</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>機械及び装置</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>車輌運搬具</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>車輌運搬具</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>器具工具</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>工具器具及び備品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>什器備品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>リース資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>リース資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>減価償却累計額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>簿価</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>減価償却累計額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>５</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>無形</div><BR>
                        <div class='pt10b'>固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>電話加入権</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>無形</div><BR>
                        <div class='pt10b'>固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>電話加入権</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>６</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>投資等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>出資金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>投資等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の投資等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>差入敷金保証金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>   
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='15' valign='top'>
                        <div class='pt10b'>７</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>仮払消費税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>(仮消 輸入含む)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>未払消費税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>前払消費税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>仮受消費税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>未払消費税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>(四半期計上分)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>買掛金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>買掛金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>買掛金期日振込</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>未払金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>未払金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>未払金期日指定</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>前受金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <center>（損益計算書）</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>　</th>
                    <th class='winbox' nowrap colspan='4'>試算表</th>
                    <th class='winbox' nowrap colspan='3'>決算書(P/L,製造原価報告書、経費明細書）</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap colspan='2'>勘定科目</th>
                    <th class='winbox' nowrap>金額</th>
                    <th class='winbox' nowrap>備考</th>
                    <th class='winbox' nowrap colspan='2'>勘定科目</th>
                    <th class='winbox' nowrap>金額</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>No.</th>
                    <th class='winbox' nowrap colspan='7'>製造原価報告書</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>１</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>材料費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>期首棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>損益計算書</div><BR>
                        <div class='pt10b'>製造原価</div><BR>
                        <div class='pt10b'>報告書</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>期首製品棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>仕掛品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>原材料及び貯蔵品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>２</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>材料費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>期末棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>損益計算書</div><BR>
                        <div class='pt10b'>製造原価</div><BR>
                        <div class='pt10b'>報告書</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>期末製品棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>仕掛品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>原材料及び貯蔵品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>No.</th>
                    <th class='winbox' nowrap colspan='7'>P / L   営業外損益、特別損益、他</th>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>営業外収益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>雑収入</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>営業外収益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>雑収入</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>業務委託収入</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>２</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>営業外費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の営業外費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='2' valign='top'>
                        <div class='pt10b'>営業外費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の営業外費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>固定資産売却損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>営業外費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>固定資産売却損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>固定資産除却損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>固定資産除却損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>３</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>法人税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>法人税及び住民税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>法人税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='2' valign='top'>
                        <div class='pt10b'>法人税、住民税</div><BR>
                        <div class='pt10b'>及び事業税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'  rowspan='2'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>事業税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>法人税等調整額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>No.</th>
                    <th class='winbox' nowrap colspan='7'>経    費    明   細    書</th>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>旅費交通費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>旅費交通費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>海外出張費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>２</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>広告宣伝費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>広告宣伝費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>求人費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>３</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>業務委託費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>業務委託費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>支払手数料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>４</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>事業等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>諸税公課</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>諸税公課</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>５</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>事務用消耗品費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>事務用消耗品費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>工場消耗品費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>６</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>雑費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>雑費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>保証修理費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>諸会費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>７</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>地代家賃</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>地代家賃</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>倉敷料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>８</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>法定福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>厚生福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>厚生福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>９</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>退職給与金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>退職給付費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>退職給付費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１０</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>旅費交通費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>旅費交通費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>海外出張費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１１</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>業務委託費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>業務委託費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>支払手数料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='6' valign='top'>
                        <div class='pt10b'>１２</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='6' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>雑費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='6' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>雑費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>広告宣伝費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>求人費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>保証修理費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>諸会費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１３</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>地代家賃</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>地代家賃</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>倉敷料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１４</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>労務費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>法定福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>労務費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>厚生福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>厚生福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１５</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>労務費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>退職給与金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>労務費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>退職給付費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>退職給付費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format(700) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='登録' onClick='return data_input_click(this)'>
        </form>
    </center>
</body>
</html>
