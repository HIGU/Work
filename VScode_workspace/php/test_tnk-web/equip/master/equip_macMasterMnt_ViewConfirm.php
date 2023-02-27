<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械マスター の 照会 ＆ メンテナンス                               //
//              MVC View 部  確認画面(追加・変更・削除 共有)                //
// Copyright (C) 2002-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/03/13 Created   equip_macMasterMnt_ViewList.php                     //
// 2002/08/08 register_globals = Off 対応                                   //
// 2003/06/17 servey(監視フラグ) Y/N が変更できない不具合を修正 及び        //
//              各入力フォームをプルダウン式に変更                          //
// 2003/06/19 $uniq = uniqid('script')を追加して JavaScript Fileを必ず読む  //
// 2004/03/04 新版テーブル equip_machine_master2 への対応                   //
// 2004/07/12 Netmoni & FWS 方式を統一 スイッチ方式 そのため Net&FWS方式追加//
//            CSV 出力設定等を 監視方式へ 項目名変更                        //
// 2005/02/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/06/24 ディレクトリ変更 equip/ → equip/master/                      //
// 2005/06/28 MVCのView部へ変更  確認フォーム(追加・変更・削除)             //
// 2005/07/15 ../equipment.jp → machineMaster.js へ変更                    //
// 2005/08/19 ページ制御データを action=''に$model->get_htmlGETparm()で付加 //
// 2018/12/25 ７工場を真鍮とSUSに分離。後々の為。                      大谷 //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<style type='text/css'>
<!--
.center {
    text-align:         center;
}
.right {
    text-align:         right;
}
.left {
    text-align:         left;
}
.fc_yellow {
    color:              yellow;
    background-color:   blue;
}
.fc_red {
    color:              red;
    background-color:   blue;
}
.s_radio {
    color:              white;
    background-color:   blue;
    font-size:          11pt;
    font-weight:        bold;
}
.n_radio {
    font-size:          11pt;
}
-->
</style>
<script language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
    document.confirm_form.mac_no.focus();
    // document.confirm_form.mac_no.select();
}
// -->
</script>
<script language='JavaScript' src='machineMaster.js?<?php echo $uniq ?>'></script>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>

<table width='100%' border='0' cellspacing='0' cellpadding='0'>
    <tr><td>
        <form action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='center' nowrap>
                        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                            <tr align='center'>
                                <td class='winbox' nowrap>
                                    <span <?php if($current_menu=='apend') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                                        <input type='radio' name='current_menu' value='apend' id='apend' onClick='submit()'
                                        <?php if($current_menu=='apend') echo 'checked' ?>>
                                        <label for='apend'>マスター追加
                                    </span>
                                </td>
                                <td class='winbox' nowrap>
                                    <span <?php if($current_menu=='list') echo "class='s_radio'"; else echo "class='n_radio'" ?>>
                                        <input type='radio' name='current_menu' value='list' id='work' onClick='submit()'
                                        <?php if($current_menu=='list') echo 'checked' ?>>
                                        <label for='work'>マスター一覧
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        <style type='text/css'>
        <!--
        th {
            font-size:          11pt;
            font-weight:        bold;
            color:              white;
            background-color:   teal;
        }
        td {
            font-size:          11pt;
            font-weight:        normal;
        }
        caption {
            font-size:          11pt;
            font-weight:        bold;
        }
        input {
            font-size:          11pt;
            font-weight:        bold;
        }
        select {
            background-color:   lightblue;
            color:              black;
            font-size:          11pt;
            font-weight:        bold;
        }
        a {
            color: blue;
        }
        a:hover {
            background-color: blue;
            color: white;
        }
        -->
        </style>
        <form name='confirm_form' action='<?=$menu->out_self(), '?', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='post' onSubmit='return chk_equip_mac_mst_mnt(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='5'>
                <caption>マスター 確認</caption>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <th class='winbox' width='40'>1</th>
                    <td class='winbox' align='left' nowrap>
                        機械番号
                        <input type='text' name='mac_no' size='5' value='<?=$mac_no?>' maxlength='4' readonly style='background-color:#d6d3ce;'>
                        <input type='hidden' name='pmac_no' size='5' value='<?=$pmac_no?>'>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>2</th>
                    <td class='winbox' align='left' nowrap>
                        機械名称
                        <input type='text' name='mac_name' size='24' value='<?=$mac_name?>' maxlength='20' readonly style='background-color:#d6d3ce;'>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>3</th>
                    <td class='winbox' align='left' nowrap>
                        メーカー型式
                        <input type='text' name='maker_name' size='24' value='<?=$maker_name?>' maxlength='20' readonly style='background-color:#d6d3ce;'>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>4</th>
                    <td class='winbox' align='left' nowrap>
                        メーカー
                        <input type='text' name='maker' size='24' value='<?=$maker?>' maxlength='20' readonly style='background-color:#d6d3ce;'>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>5</th>
                    <td class='winbox' align='left' nowrap>
                        工場区分
                        <select name='factory'>
                            <% if ($factory == 1) {%><option value='1'>１工場</option><?php } ?>
                            <% if ($factory == 2) {%><option value='2'>２工場</option><?php } ?>
                            <% if ($factory == 4) {%><option value='4'>４工場</option><?php } ?>
                            <% if ($factory == 5) {%><option value='5'>５工場</option><?php } ?>
                            <% if ($factory == 6) {%><option value='6'>６工場</option><?php } ?>
                            <% if ($factory == 7) {%><option value='7'>７工場(真鍮)</option><?php } ?>
                            <% if ($factory == 8) {%><option value='8'>７工場(SUS)</option><?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>6</th>
                    <td class='winbox' align='left' nowrap>
                        有効・無効
                        <select name='survey'>
                            <% if ($survey == 'Y') {%><option value='Y'>有効</option><?php } ?>
                            <% if ($survey == 'N') {%><option value='N'>無効</option><?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>7</th>
                    <td class='winbox' align='left' nowrap>
                        インターフェース 設定
                        <select name='csv_flg'>
                            <% if ($csv_flg == 0) {%><option value='0'>なし</option><?php } ?>
                            <% if ($csv_flg == 1) {%><option value='1'>Netmoni</option><?php } ?>
                            <% if ($csv_flg == 2) {%><option value='2'>FWS1</option><?php } ?>
                            <% if ($csv_flg == 3) {%><option value='3'>FWS2</option><?php } ?>
                            <% if ($csv_flg == 4) {%><option value='4'>FWS3</option><?php } ?>
                            <% if ($csv_flg == 5) {%><option value='5'>FWS4</option><?php } ?>
                            <% if ($csv_flg == 6) {%><option value='6'>FWS5</option><?php } ?>
                            <% if ($csv_flg == 7) {%><option value='7'>FWS6</option><?php } ?>
                            <% if ($csv_flg == 8) {%><option value='8'>FWS7</option><?php } ?>
                            <% if ($csv_flg == 9) {%><option value='9'>FWS8</option><?php } ?>
                            <% if ($csv_flg == 10){%><option value='10'>FWS9</option><?php } ?>
                            <% if ($csv_flg == 11){%><option value='11'>FWS10</option><?php } ?>
                            <% if ($csv_flg == 12){%><option value='12'>FWS11</option><?php } ?>
                            <% if ($csv_flg == 101){%><option value='101'>Net&FWS</option><?php } ?>
                            <% if ($csv_flg == 201){%><option value='201'>その他</option><?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>8</th>
                    <td class='winbox' align='left' nowrap>
                        作業区 101 401 501等
                        <input type='text' name='sagyouku' size='3' value='<?=$sagyouku?>' maxlength='3' readonly style='background-color:#d6d3ce;'>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>9</th>
                    <td class='winbox' align='left' nowrap>
                        使用電力(KW)
                        <input type='text' name='denryoku' size='8' value='<?=$denryoku?>' maxlength='7' class='right' readonly style='background-color:#d6d3ce;'>
                    </td>
                </tr>
                <tr>
                    <th class='winbox'>10</th>
                    <td class='winbox' align='left' nowrap>
                        電力係数
                        <input type='text' name='keisuu' size='4' value='<?=$keisuu?>' maxlength='4' class='right' readonly style='background-color:#d6d3ce;'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center' nowrap>
                        <?php if ($current_menu == 'confirm_apend') { ?>
                        <input type='submit' name='apend' value='登録実行' style='color:blue;'>
                        &nbsp;&nbsp;
                        <input type='submit' name='cancel_apend' value='取消'>
                        <?php } elseif ($current_menu == 'confirm_edit') { ?>
                        <input type='submit' name='edit' value='変更実行' style='color:blue;'>
                        &nbsp;&nbsp;
                        <input type='submit' name='cancel_edit' value='取消'>
                        <?php } elseif ($current_menu == 'confirm_delete') { ?>
                        <input type='submit' name='delete' value='削除実行' style='color:red;' onClick='return confirm("削除したデータは元に戻せません。\n\n宜しいですか？")'>
                        &nbsp;&nbsp;
                        <input type='submit' name='cancel_del' value='取消'>
                        <?php } ?>
                    </td>
                </tr>
            </table>
                </td></tr> <!----------- ダミー(デザイン用) ------------>
            </table>
        </form>
    </td></tr>
    </table>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
