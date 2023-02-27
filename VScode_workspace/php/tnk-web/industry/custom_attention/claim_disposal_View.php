<?php
//////////////////////////////////////////////////////////////////////////////
// 不適合処置連絡書照会  claim_disposal_View.php                            //
// Copyright (C) 2013-2013 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/01/24 Created  claim_disposal_View.php                              //
// 2013/01/25 表示位置等微調整                                              //
// 2013/01/30 不適合処置と注意点をメニューで分割した                        //
// 2013/05/09 前方一致検索へ切替の為、変更を行った。                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                                 // ini_set()の次に指定すること Script 最上行
/////////////// 受け渡し変数の保管
if (isset($_GET['assy_no'])) {
    $_SESSION['assy_no'] = $_GET['assy_no'];                 // 検索用製品番号をセッションに保存
}if (isset($_GET['c_assy_no'])) {
    $_SESSION['c_assy_no'] = $_GET['c_assy_no'];             // 不適合製品番号をセッションに保存
}
if (isset($_GET['publish_no'])) {
    $_SESSION['publish_no'] = $_GET['publish_no'];           // 発行番号をセッションに保存
}
if ( isset($_SESSION['assy_no']) ) {
    $assy_no = $_SESSION['assy_no'];
} else {
    $assy_no = '';
}
if ( isset($_SESSION['c_assy_no']) ) {
    $c_assy_no = $_SESSION['c_assy_no'];
} else {
    $c_assy_no = '';
}
if ( isset($_SESSION['publish_no']) ) {
    $publish_no = $_SESSION['publish_no'];
} else {
    $publish_no = '';
}
require_once ('../../function.php');             // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');             // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');           // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class
access_log();                                    // Script Name は自動取得
///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('不適合処置連絡書の照会');
//////////// 戻先へのGETデータ設定
$menu->set_retGET('page_keep', 'On');    
////////////// リターンアドレス設定
$menu->set_RetUrl('http:' . WEB_HOST . 'industry/custom_attention/claim_disposal_Main.php');             // 通常は指定する必要はない
$query = "
        SELECT  publish_no          AS 発行番号                 -- 0
            ,   publish_date        AS 発行日                   -- 1
            ,   claim_no            AS クレーム処理票No         -- 2
            ,   claim_name          AS 件名                     -- 3
            ,   assy_no             AS ASSY番号                 -- 4
            ,   parts_no            AS 部品番号                 -- 5
            ,   claim_explain1      AS 不良箇所・内容説明１     -- 6
            ,   claim_explain2      AS 不良箇所・内容説明２     -- 7
            ,   ans_hope_date       AS 回答希望日               -- 8
            ,   delivery_date       AS 納入受付日               -- 9
            ,   process_name        AS 工程名                   --10
            ,   claim_sec           AS 発生部門                 --11
            ,   product_no          AS 製品機番                 --12
            ,   delivery_num        AS 納入数                   --13
            ,   bad_num             AS 不良数                   --14
            ,   bad_par             AS 不良数％                 --15
            ,   charge_no           AS 品管担当                 --16
            ,   occur_cause1        AS 発生原因１               --17
            ,   occur_cause2        AS 発生原因２               --18
            ,   outflow_cause1      AS 流出原因１               --19
            ,   outflow_cause2      AS 流出原因２               --20
            ,   occur_measures1     AS 発生原因対策１           --21
            ,   occur_measures2     AS 発生原因対策２           --22
            ,   outflow_measures1   AS 流出原因対策１           --23
            ,   outflow_measures2   AS 流出原因対策２           --24
        FROM
            claim_disposal_details
        WHERE assy_no = '{$c_assy_no}' and publish_no = '{$publish_no}'
        ORDER BY
            parts_no
    ";

$res = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = "不適合処置連絡書の登録がありません！{$c_assy_no} {$publish_no}";
} else {
    $publish_no     = $res[0][0];
    $publish_date   = $res[0][1];
    $claim_no       = $res[0][2];
    if ($res[0][3] == '') {
        $claim_name     = '　';
    } else {
        $claim_name     = $res[0][3];
    }
    $assy_no        = $res[0][4];
    if ($rows == 1) {
        if ($res[0][5] == '         ') {
            $parts_no1 = '　';
        } else {
            $parts_no1 = $res[0][5];
        }
        $parts_no2 = '　';
        $parts_no3 = '　';
    } elseif ($rows == 2) {
        if ($res[0][5] == '         ') {
            $parts_no1 = '　';
        } else {
            $parts_no1 = $res[0][5];
        }
        if ($res[1][5] == '         ') {
            $parts_no2 = '　';
        } else {
            $parts_no2 = $res[1][5];
        }
        $parts_no3 = '　';
    } elseif ($rows == 3) {
        if ($res[0][5] == '         ') {
            $parts_no1 = '　';
        } else {
            $parts_no1 = $res[0][5];
        }
        if ($res[1][5] == '         ') {
            $parts_no2 = '　';
        } else {
            $parts_no2 = $res[1][5];
        }
        if ($res[2][5] == '         ') {
            $parts_no3 = '　';
        } else {
            $parts_no3 = $res[2][5];
        }
    } else {
        $parts_no1 = '　';
        $parts_no2 = '　';
        $parts_no3 = '　';
    }
    if ($res[0][6] == '') {
        $claim_explain1 = '　';
    } else {
        $claim_explain1 = $res[0][6];
    }
    if ($res[0][7] == '') {
        $claim_explain2 = '　';
    } else {
        $claim_explain2 = $res[0][7];
    }
    $ans_hope_date  = $res[0][8];
    $delivery_date  = $res[0][9];
    if ($res[0][10] == '  ') {
        $process_name1  = '　';
    } else{
        $process_name1  = $res[0][10];
    }
    switch ($process_name1) {
        case '00':
            $process_name2  = '不明';
        break;
        case '01':
            $process_name2  = '設計';
        break;
        case '02':
            $process_name2  = '加工';
        break;
        case '03':
            $process_name2  = '組立';
        break;
        case '04':
            $process_name2  = '市販';
        break;
        case '05':
            $process_name2  = '運搬';
        break;
        case '06':
            $process_name2  = '保管';
        break;
        case '07':
            $process_name2  = '販売';
        break;
        case '08':
            $process_name2  = '顧客';
        break;
        case '09':
            $process_name2  = '修理';
        break;
        default:
            $process_name2  = '　';
        break;
    }
    if ($res[0][11] == ' ') {
        $claim_sec1  = '　';
    } else{
        $claim_sec1  = $res[0][11];
    }
    switch ($claim_sec1) {
        case 1:
            $claim_sec2  = '市場';
        break;
        case 2:
            $claim_sec2  = '社内';
        break;
        case 3:
            $claim_sec2  = 'その他';
        break;
        default:
            $claim_sec2  = '　';
        break;
    }
    if ($res[0][12] == '') {
        $product_no     = '　';
    } else {
        $product_no     = $res[0][12];
    }
    $delivery_num   = $res[0][13];
    $bad_num        = $res[0][14];
    $bad_par        = $res[0][15];
    if ($res[0][16] == '') {
        $charge_no     = '　';
    } else {
        $charge_no      = $res[0][16];
    }
    if ($res[0][17] == '') {
        $occur_cause1 = '　';
    } else {
        $occur_cause1 = $res[0][17];
    }
    if ($res[0][18] == '') {
        $occur_cause2 = '　';
    } else {
        $occur_cause2 = $res[0][18];
    }
    if ($res[0][19] == '') {
        $outflow_cause1 = '　';
    } else {
        $outflow_cause1 = $res[0][19];
    }
    if ($res[0][20] == '') {
        $outflow_cause2 = '　';
    } else {
        $outflow_cause2 = $res[0][20];
    }
    if ($res[0][21] == '') {
        $occur_measures1 = '　';
    } else {
        $occur_measures1 = $res[0][21];
    }
    if ($res[0][22] == '') {
        $occur_measures2 = '　';
    } else {
        $occur_measures2 = $res[0][22];
    }
    if ($res[0][23] == '') {
        $outflow_measures1 = '　';
    } else {
        $outflow_measures1 = $res[0][23];
    }
    if ($res[0][24] == '') {
        $outflow_measures2 = '　';
    } else {
        $outflow_measures2 = $res[0][24];
    }
}
$query = "
        SELECT  midsc          AS 製品名                 -- 0
        FROM
            miitem
        WHERE mipn = '{$assy_no}'
    ";

$res = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $assy_name = '　';
} else {
    $assy_name = $res[0][0];
}

if ($parts_no1 != '　') {
    $query = "
            SELECT  midsc          AS 部品名                 -- 0
            FROM
                miitem
            WHERE mipn = '{$parts_no1}'
        ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $parts_name1 = '　';
    } else {
        $parts_name1 = $res[0][0];
    }
} else {
    $parts_name1 = '　';
}
if ($parts_no2 != '　') {
    $query = "
            SELECT  midsc          AS 部品名                 -- 0
            FROM
                miitem
            WHERE mipn = '{$parts_no2}'
        ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $parts_name2 = '　';
    } else {
        $parts_name2 = $res[0][0];
    }
} else {
    $parts_name2 = '　';
}
if ($parts_no3 != '　') {
    $query = "
            SELECT  midsc          AS 部品名                 -- 0
            FROM
                miitem
            WHERE mipn = '{$parts_no3}'
        ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $parts_name3 = '　';
    } else {
        $parts_name3 = $res[0][0];
    }
} else {
    $parts_name3 = '　';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo  $menu->out_title() ?></title>
<?php echo  $menu->out_site_java() ?>
<?php echo  $menu->out_css() ?>
<link rel='stylesheet' href='custom_attention.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='claim_disposal.js'></script>
</head>
<center>
<?php echo  $menu->out_title_border() ?>
    <div class='caption_font'></div>
    
    <table class='list' bgcolor='#e6e6e6' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
            <?php echo $menu->out_caption(), "\n"?>
        </caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#e6e6e6' width='100%' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <th class='winbox' align='center' colspan='7'>
                不適合処置連絡書
            </th>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                発行番号
            </th>
            <td class='winbox' nowrap>
                <?php echo $publish_no ?>
            </td>
            <th class='winbox' nowrap>
                発行日
            </th>
            <td class='winbox' nowrap>
                <?php echo format_date($publish_date) ?>
            </td>
            <th class='winbox' nowrap colspan='2'>
                クレーム処理票No.
            </th>
            <td class='winbox' nowrap>
                <?php echo $claim_no ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                件名
            </th>
            <td class='winbox' nowrap colspan='6'>
                <?php echo $claim_name ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap rowspan='3'>
                ASSY番号
            </th>
            <td class='winbox' nowrap rowspan='3'>
                <?php echo $assy_no ?>
            </td>
            
            <td class='winbox' rowspan='3'>
                <?php echo $assy_name ?>
            </td>
            <th class='winbox' nowrap>
                部品番号
            </th>
            <td class='winbox' nowrap>
                <?php echo $parts_no1 ?>
            </td>
            <td class='winbox' colspan='2'>
                <?php echo $parts_name1 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                部品番号
            </th>
            <td class='winbox' nowrap>
                <?php echo $parts_no2 ?>
            </td>
            <td class='winbox' colspan='2'>
                <?php echo $parts_name2 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                部品番号
            </th>
            <td class='winbox' nowrap>
                <?php echo $parts_no3 ?>
            </td>
            <td class='winbox' colspan='2'>
                <?php echo $parts_name3 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' style='border-bottom:0' nowrap>
                不良箇所
            </th>
            <td class='winbox' style='border-bottom:0' nowrap colspan='6'>
                <?php echo $claim_explain1 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' style='border-top:0' nowrap>
                内容説明
            </th>
            <td class='winbox' style='border-top:0' nowrap colspan='6'>
                <?php echo $claim_explain2 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                回答希望日
            </th>
            <td class='winbox' nowrap colspan='2'>
                <?php echo format_date($ans_hope_date) ?>
            </td>
            <th class='winbox' nowrap colspan='2'>
                発生部門
            </th>
            <td class='winbox' style='text-align:right' nowrap>
                <?php echo $claim_sec1 ?>
            </td>
            <td class='winbox' nowrap>
                <?php echo $claim_sec2 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                納入受付日
            </th>
            <td class='winbox' nowrap colspan='2'>
                <?php echo format_date($delivery_date) ?>
            </td>
            <th class='winbox' nowrap colspan='2'>
                製品機番
            </th>
            <td class='winbox' nowrap colspan='2'>
                <?php echo $product_no ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                工程名
            </th>
            <td class='winbox' style='text-align:right' nowrap>
                <?php echo $process_name1 ?>
            </td>
            <td class='winbox' nowrap>
                <?php echo $process_name2 ?>
            </td>
            <th class='winbox' nowrap colspan='2'>
                納入数
            </th>
            <td class='winbox' style='border-right:0; text-align:right' nowrap>
                <?php echo number_format($delivery_num) ?>
            </td>
            <td class='winbox' style='border-left:0' nowrap>
                個
            </td>
        </tr>
        <tr>
            <th class='winbox' nowrap>
                不良数
            </th>
            <td class='winbox' style='border-right:0; text-align:right' nowrap>
                <?php echo number_format($bad_num) ?>
            </td>
            <td class='winbox' style='border-left:0' nowrap>
                個
            </td>
            <td class='winbox' style='border-right:0; text-align:right' nowrap>
                <?php echo number_format($bad_par, 2) ?>
            </td>
            <td class='winbox' style='border-left:0' nowrap>
                ％
            </td>
            <th class='winbox' nowrap>
                品管担当
            </th>
            <td class='winbox' nowrap>
                <?php echo $charge_no ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' style='border-bottom:0' nowrap rowspan='2'>
                発生原因
            </th>
            <td class='winbox' style='border-bottom:0' nowrap colspan='6'>
                <?php echo $occur_cause1 ?>
            </td>
        </tr>
        <tr>
            <td class='winbox' style='border-top:0' nowrap colspan='6'>
                <?php echo $occur_cause2 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' style='border-bottom:0' nowrap rowspan='2'>
                流出原因
            </th>
            <td class='winbox' style='border-bottom:0' nowrap colspan='6'>
                <?php echo $outflow_cause1 ?>
            </td>
        </tr>
        <tr>
            <td class='winbox' style='border-top:0' nowrap colspan='6'>
                <?php echo $outflow_cause2 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' style='border-bottom:0' nowrap rowspan='2'>
                発生原因対策
            </th>
            <td class='winbox' style='border-bottom:0' nowrap colspan='6'>
                <?php echo $occur_measures1 ?>
            </td>
        </tr>
        <tr>
            <td class='winbox' style='border-top:0' nowrap colspan='6'>
                <?php echo $occur_measures2 ?>
            </td>
        </tr>
        <tr>
            <th class='winbox' style='border-bottom:0' nowrap rowspan='2'>
                流出原因対策
            </th>
            <td class='winbox' style='border-bottom:0' nowrap colspan='6'>
                <?php echo $outflow_measures1 ?>
            </td>
        </tr>
        <tr>
            <td class='winbox' style='border-top:0' nowrap colspan='6'>
                <?php echo $outflow_measures2 ?>
            </td>
        </tr>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
