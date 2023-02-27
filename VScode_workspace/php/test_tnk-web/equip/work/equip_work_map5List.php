<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理システムの現在運転状況一覧マップ表示(レイアウト)Include file //
// Copyright (C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
//               Designed  Norihisa.ooya                                    //
// Changed history                                                          //
// 2004/10/06 Created  equip_work_map5List.php (include file)               //
// 2006/03/09 4602(RM-1号機）削除 事務所右に新設備予定場所(130行目)         //
// 2007/06/28 各設備写真ファイル変更    大谷                                //
// 2007/06/29 4103P.F3号機社員変更  大谷                                    //
//////////////////////////////////////////////////////////////////////////////
?>
<table width='60%' height='60%' cellspacing='2' cellpadding='0' border='1' bgcolor='#f7f7f7' bordercolor='#1a6699'>
    <tr>
        <td colspan='4' height='12'></td>
        <td colspan='2' bgcolor='#f0f0f0' width='70' height='12' nowrap valign='middle' align='center'>↑ リニア</td>
        <td rowspan='2' bgcolor='#1a6699' width='30' colspan='2'><font color='#1a6699'>壁</font></td>
    </tr>
    <tr>
        <td bgcolor='#f0f0f0' valign='middle' align='center' width='12' height='135'>ドア</td>
        <td bgcolor='#ffffff' colspan='3' rowspan='4' nowrap valign='center' align='center' width='210' height='290'>
        <?php mac_state_view('4101') ?>
            <input type='image' alt='機械No4101 S.P.F.' height='195' width='195' border='0' src='../img/4101L.jpg' onclick='win_open("../img/4101L.jpg","機械No4101 S.P.F.")'></td>
        <td colspan='2' rowspan='13' width='70'></td>
        <td width='12' height='135'></td>
        <td bgcolor='#ffffff' nowrap valign='center' align='center' width='70' height='135'>
        <?php mac_state_view('4503') ?>
            <input type='image' alt='機械No4503 NC-3' height='65' width='65' border='0' src='../img/4503.jpg' onclick='win_open("../img/4503L.jpg","機械No4503 NC-3")'></td>
        <td bgcolor='#ffffff' nowrap valign='center' align='center' width='70' height='135'>
        <?php mac_state_view('4502') ?>
            <input type='image' alt='機械No4502 NC-2' height='65' width='65' border='0' src='../img/4502.jpg' onclick='win_open("../img/4502L.jpg","機械No4502 NC-2")'></td>
        <td height='135' width='12'></td>
        <td bgcolor='#ffffff' nowrap valign='center' align='center' width='70' height='135'>
        <?php mac_state_view('4501') ?>
        <input type='image' alt='機械No4501 NC-1' height='65' width='65' border='0' src='../img/4501.jpg' onclick='win_open("../img/4501L.jpg","機械No4501 NC-1")'></td>
        <td bgcolor='#f0f0f0' valign='middle' align='center' width='12' height='135'>ドア</td>
    </tr>
    <tr>
        <td rowspan='3' width='12'></td>
        <td colspan='9' height='20'></td>
    </tr>
    <tr>
        <td rowspan='9' bgcolor='#1a6699' width='30' valign='center' align='center' colspan='2'><font color='#1a6699'>壁</font></td>
        <td rowspan='2' width='12' height='135'></td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center' width='70' height='135'>
        <?php mac_state_view('4601') ?>
            <input type='image' alt='機械No4601 SSR-50' height='65' width='65' border='0' src='../img/4601.jpg' onclick='win_open("../img/4601L.jpg","機械No4601 SSR-50")'></td>
        <td bgcolor='#ffffff' nowrap valign='center' align='center' height='135' width='70'>
        <!----- 機械番号確定まで隠し <?php mac_state_view('4601') ?> ----->
            <font size ='2'>30PM<BR>刻印機</font>
            <input type='image' alt='機械No 30PM刻印機' height='65' width='65' border='0' src='../img/30pm.jpg' onclick='win_open("../img/30pm.jpg","機械No 30PM刻印機")'></td>
        <td bgcolor='#ffffff' nowrap valign='center' align='center' width='70' height='135'>
        <?php mac_state_view('4510') ?>
            <input type='image' alt='機械No4510 シマダ-1' height='65' width='65' border='0' src='../img/4510.jpg' onclick='win_open("../img/4510L.jpg","機械No4510 シマダ-1")'></td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'  width='70' height='270'>
        <?php mac_state_view('4509') ?>
            <input type='image' alt='機械No4509 TMC1-4' height='65' width='70' border='0' src='../img/4509.jpg' onclick='win_open("../img/4509L.jpg","機械No4509 TMC1-4")'></td>
    </tr>
    <tr>
        <td bgcolor='#ffffff' nowrap valign='center' align='center' height='135' width='70'>
        <?php mac_state_view('4504') ?>
            <input type='image' alt='機械No4504 NC-4' height='65' width='65' border='0' src='../img/4504.jpg' onclick='win_open("../img/4504L.jpg","機械No4504 NC-4")'></td>
    </tr>
    <tr>
        <td bgcolor='#f0f0f0' valign='middle' align='center' height='12'>←４工場</td>
        <td bgcolor='#ffffff' colspan='3' rowspan='4' nowrap valign='center' align='center' width='210' height='290'>
        <?php mac_state_view('4102') ?>
            <input type='image' alt='機械No4102 N.P.F.' height='195' width='195' border='0' src='../img/4102L.jpg' onclick='win_open("../img/4102L.jpg","機械No4102 N.P.F.")'></td>
        <td colspan='1' height='12'></td>
        <td bgcolor='#ffffff' colspan='2' nowrap valign='center' align='center' height='135' width='152'>
        <?php mac_state_view('4308') ?>
            <input type='image' alt='機械No4308 OBS60-2' height='65' width='65' border='0' src='../img/4308.jpg' onclick='win_open("../img/4308L.jpg","機械No4308 OBS60-2")'></td>
        <td></td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center' width='70' height='147'>
        <?php mac_state_view('4508') ?>
            <input type='image' alt='機械No4508 TMC1-3' height='65' width='65' border='0' src='../img/4508.jpg' onclick='win_open("../img/4508L.jpg","機械No4508 TMC1-3")'></td>
    </tr>
    <tr>
        <td height='12'></td>
        <td rowspan='5'></td>
        <td colspan='2'></td>
    </tr>
    <tr>
        <td rowspan='3'></td>
        <td colspan='3'></td>
        <td bgcolor='#ffffff' rowspan='2' valign='center' align='center' width='70' height='142'>
        <?php mac_state_view('4507') ?>
        <input type='image' alt='機械No4507 TMC1-2' height='65' width='65' border='0' src='../img/4507.jpg' onclick='win_open("../img/4507L.jpg","機械No4507 TMC1-2")'></td>
    </tr>
    <tr>
        <td bgcolor='#ffffff' colspan='2' nowrap valign='center' align='center' height='135' width='152'>
        <?php mac_state_view('4307') ?>
            <input type='image' alt='機械No4307 OBS60-1' height='65' width='65' border='0' src='../img/4307.jpg' onclick='win_open("../img/4307L.jpg","機械No4307 OBS60-1")'></td>
    </tr>
    <tr>
        <td colspan='3'></td>
        <td colspan='3'></td>
        <td bgcolor='#ffffff' rowspan='2' valign='center' align='center' width='70' height='142'>
        <?php mac_state_view('4506') ?>
            <input type='image' alt='機械No4506 TMC1-1' height='65' width='65' border='0' src='../img/4506.jpg' onclick='win_open("../img/4506L.jpg","機械No4506 TMC1-1")'></td>
    </tr>
    <tr>
        <td height='12'></td>
        <td bgcolor='#ffffff' colspan='3' rowspan='4' nowrap valign='center' align='center' width='210' height='290'>
        <!----- 機械番号確定まで隠し <?php mac_state_view('4103') ?> ----->
            <font size ='2'>N.P.F-2</font>
            <input type='image' alt='機械No.4103 P.F.3号機' height='195' width='195' border='0' src='../img/4103L.jpg' onclick='win_open("../img/4103L.jpg","機械No.4103 P.F.3号機")'></td>
        <td bgcolor='#ffffff' colspan='2' nowrap valign='center' align='center' width='152' height='135'>
        <?php mac_state_view('4505') ?>
            <input type='image' alt='機械No4505 TMC15-0' height='65' width='65' border='0' src='../img/4505.jpg' onclick='win_open("../img/4505L.jpg","機械No4505 TMC15-0")'></td>
        <td colspan='2'></td>
    </tr>
    <tr>
        <td colspan='1' height='12'></td>
        <td bgcolor='#f0f0f0' colspan='3' width='222' height='100' valign='middle' align='center'>完成品置き場</td>
        <td rowspan='2'></td>
    </tr>
    <tr>
        <td colspan='8' height='20'></td>
        <td></td>
    </tr>
    <tr>
        <td colspan='1'></td>
        <td rowspan='2' bgcolor='#1a6699' width='30' colspan='2'><font color='#1a6699'>壁</font></td>
        <td colspan='1' height='12'></td>
        <td bgcolor='#ffffff' nowrap valign='center' align='center' width='70' height='135'>
        <?php mac_state_view('4604') ?>
            <input type='image' alt='機械No4604 RM-3' height='65' width='65' border='0' src='../img/4604L.jpg' onclick='win_open("../img/4604L.jpg","機械No4604 RM-3")'></td>
        <td bgcolor='#ffffff' nowrap valign='center' align='center' width='70' height='135'>
        <?php mac_state_view('4603') ?>
            <input type='image' alt='機械No4603 RM-2' height='65' width='65' border='0' src='../img/4603L.jpg' onclick='win_open("../img/4603L.jpg","元機械No4603 RM-2")'></td>
        <td bgcolor='#ffffff' colspan='3' nowrap valign='center' align='center' width='70' height='135'>
        <!----- 機械番号確定まで隠し <?php mac_state_view('4511') ?> ----->
            <font size ='2'>テクノワシノ J1-1</font>
            <input type='image' alt='機械No4511 J1-1' height='65' width='65' border='0' src='../img/4602.jpg' onclick='win_open("../img/4602L.jpg","機械No4511 J1-1")'>
        </td>
    </tr>
    <tr>
        <td colspan='4  ' height='12'></td>
        <td colspan='2' bgcolor='#f0f0f0' nowrap valign='middle' align='center' height='12'>ドア</td>
        <td colspan='2' bgcolor='#f0f0f0' nowrap valign='middle' align='center' height='12'>ドア</td>
        <td colspan='11' height='12'></td>
    </tr>
</table>
