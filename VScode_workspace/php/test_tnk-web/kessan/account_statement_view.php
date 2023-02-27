<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 勘定科目内訳明細書                                          //
// Copyright(C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2020/06/12 Created   account_statement_view.php                          //
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
$p1_ki    = 21;

///// 前期末 年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // 前期末年月

///// 期・半期の取得
$tuki_chk   =12;
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
    $menu->set_title("第 {$ki} 期　本決算　勘　定　科　目　内　訳　明　細　書");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　勘　定　科　目　内　訳　明　細　書");
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
td.winboxt {
    border-style:           solid;
    border-width:           20px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    writing-mode       :    tb-rl;
}
-->
</style>
</head>
<body>
<?= $menu->out_title_border() ?>
        <?php
            //  bgcolor='#ceffce' 黄緑
            //  bgcolor='#ffffc6' 薄い黄色
            //  bgcolor='#d6d3ce' Win グレイ
        ?>
    <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>1.現金および預金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winboxt' nowrap bgcolor='#ffffff' rowspan='2' align='left'><div class='pt11b'>現金</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>科目</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='4'><div class='pt11b' align='center'>内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>金額</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>現金</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='4'><div class='pt11b' align='center'>手許残高</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winboxt' nowrap bgcolor='#ffffff' rowspan='5' align='center'><div class='pt11b'>預金内訳</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>銀行名</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>支店名</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>普通預金</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>当座預金</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>定期預金</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>計</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>三菱UFJ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>池上</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#f5f5f5' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>三菱UFJ信託</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>本店</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#f5f5f5' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>足利</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>氏家</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#f5f5f5' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'><div class='pt11b' align='center'>小計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='6' align='right'><div class='pt11b'>現金預金合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>2.売掛金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>社名</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>住所</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php if ($nk_uri_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>日東工器株式会社</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>東京都大田区仲池上2丁目9番4号</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($mt_uri_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>株式会社 メドテック</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>山形県山形市若宮1-1-36</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($snk_uri_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>白河日東工器株式会社</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>福島県白河市双石横峰12番</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>3.棚卸資産の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>内訳</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='6'><div class='pt11b'>原材料及び貯蔵品</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>仕掛品</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>合計</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>生産用部品</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>半成部品</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>原材料</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ＣＣ部品</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>貯蔵品</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>小計</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>資材・検査</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>工作</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>外注</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>組立</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>その他</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>4.前払費用の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>社名</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>5.未収入金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>6.その他流動資産の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>勘定科目</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php if ($karibara_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>仮払金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($tatekae_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>立替金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($hokaryudo_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>その他</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>7.有形固定資産及び減価償却費の内訳　・・・　別紙明細書参照</div>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>8.電話加入権の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>電話番号</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－8851</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>発着両用（代表）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－8852</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>発着両用</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－8853</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>（休止）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－9153</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ダイヤルイン（総務）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－9250</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ダイヤルイン（購買）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－7471</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ダイヤルイン</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－3044</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ピンク電話（食堂）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－6481</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>商品管理</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－6482</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>商品管理</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－7367</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ＦＡＸ（商品管理）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－7038</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ＦＡＸ（事務所棟・ＩＳＤＮ）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－1324</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ＦＡＸ（第6工場1階事務所）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－7652</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>試験修理直通</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－5105</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>交換機</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－7011</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ＴＶ会議用ＩＳＤＮ</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－7735</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>サーバー室用</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－8853</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>（休止）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>9.ソフトウェアの内訳　・・・　別紙明細書参照</div>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>10.繰延税金資産の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期首残高</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期増加額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期減少額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期末残高</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'><div class='pt11b'>固定資産</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>11.長期貸付金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期首残高</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期増加額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期減少額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期末残高</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'><div class='pt11b'>従業員貸付金</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>12.長期前払費用の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期首残高</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期増加額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期減少額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期末残高</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>13.その他投資等の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>支払先</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'><div class='pt11b'>出資金</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>情報通信システム協同組合</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>10,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>10,000</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>14.買掛金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>会社名</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>本社住所</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php for ($i = 1; $i < 11; $i++) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'><?= $kaikake_top[$i][0] ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'><?= $kaikake_top[$i][1] ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>その他</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>15.未払金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>会社名・区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>本社住所・内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php for ($i = 1; $i < 11; $i++) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'><?= $miharai_top[$i][0] ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'><?= $miharai_top[$i][1] ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>その他</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>16.未払消費税等の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#FFFFFF' align='left'>
                        <div class='pt11b'>仮払消費税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='left'>
                        <div class='pt11b'>（予定納付額700円含む）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                        <!--
                        <div class='pt11b'><?= mb_ereg_replace('-', '△', number_format($karibara_zei_total)) ?></div>
                        -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#FFFFFF' align='left'>
                        <div class='pt11b'>仮受消費税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#FFFFFF' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>17.未払法人税等の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期首残高</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期支払額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期戻入額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期設定額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期末残高</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>未払法人税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>0</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>18.未払費用の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>会社名・区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>本社住所・内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>19.預り金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>本社住所・内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php if ($gen_shotoku_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>源泉所得税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($gen_jyu_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>源泉住民税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($ken_hoken_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>健康保険料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($kou_hoken_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>厚生年金保険料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($azu_sonota_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>その他</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>20.引当金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>期首残高</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>当期増加額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='2'><div class='pt11b'>当期減少額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>期末残高</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>目的使用</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>その他</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>賞与引当金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>退職給付引当金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>21.資本金及び剰余金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>種類</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期首残高</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期増加額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期減少額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期末残高</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>資本金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt11b'>普通株式</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>資本準備金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>その他資本剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>その他利益剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>22.諸税公課の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>製造用</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>販管費及び一般管理費</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>合計金額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>備考</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php if ($kotei_zei_total_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>固定資産税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($inshi_zei_total_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>印紙税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($touroku_zei_total_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>登録免許税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>23.雑収入の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>備考</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>24.法人税・住民税及び事業税の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>備考</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>当期法人税住民税事業税引当額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>預金利息等に対する源泉所得税額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>700</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <!--
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='登録' onClick='return data_input_click(this)'>
        </form>
        -->
</body>
</html>
