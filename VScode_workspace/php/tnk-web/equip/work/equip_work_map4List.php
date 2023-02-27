<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理システムの現在運転状況一覧マップ表示(レイアウト)Include file //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
//               Designed  Norihisa.ooya                                    //
// Changed history                                                          //
// 2004/09/23 Created  equip_work_map4List.php (include file)               //
// 2006/03/09 レイアウト変更(1365・1366・1367)追加                          //
//////////////////////////////////////////////////////////////////////////////
?>
<table width='100%' height='75%' cellspacing='3' cellpadding='0' border='2' bgcolor='#f7f7f7' bordercolor='#1a6699'>
    <tr>
        <td width='10' rowspan='3'></td>
        <td width='70' height='30' rowspan='3' bgcolor='#f0f0f0'>材料置き場</td>
        <td height='30' bgcolor='#f0f0f0'><center><b>ドア</b></center></td>
        <td height='30' colspan='4'></td>
        <td height='30' bgcolor='#f0f0f0' nowrap><b>↑ １工場</b></td>
        <td height='30' colspan='4'></td>
        <td height='30' bgcolor='#f0f0f0'><b>事務所 →<b></td>
    </tr>
    <tr>
        <td width='60' height='40' rowspan='2'></td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1353') ?>
            <input type='image' alt='機械No1353 BNC-3' height='65' width='65' border='0' src='../img/1353.jpg' onClick='win_open("../img/1353L.jpg","機械No1353 BNC-3")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1352') ?>
            <input type='image' alt='機械No1352 BNC-2' height='65' width='65' border='0' src='../img/1352.jpg' onClick='win_open("../img/1352L.jpg","機械No1352 BNC-2")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1351') ?>
            <input type='image' alt='機械No1351 BNC-1' height='65' width='65' border='0' src='../img/1351.jpg' onClick='win_open("../img/1351L.jpg","機械No1351 BNC-1")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1361') ?>
            <input type='image' alt='機械No1361 BND-1' height='65' width='65' border='0' src='../img/1361.jpg' onClick='win_open("../img/1361L.jpg","機械No1361 BND-1")'>
        </td>
        <td rowspan='2' bgcolor='#ffffff' nowrap valign='center' align='center'>
            <?php mac_state_view('1364') ?>
            <input type='image' alt='機械No1364 BND-4' height='65' width='65' border='0' src='../img/1364.jpg' onClick='win_open("../img/1364L.jpg","機械No1364 BND-4")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1347') ?>
            <input type='image' alt='機械No1347 BNF-1' height='65' width='65' border='0' src='../img/1347.jpg' onClick='win_open("../img/1347L.jpg","機械No1347 BNF-1")'>
        </td>
        <td rowspan='2' bgcolor='#ffffff' nowrap valign='center' align='center'>
            <?php mac_state_view('1348') ?>
            <input type='image' alt='機械No1348 BNF-2' height='65' width='65' border='0' src='../img/1348.jpg' onClick='win_open("../img/1348L.jpg","機械No1348 BNF-2")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1363') ?>
            <input type='image' alt='機械No1363 BND-3' height='65' width='65' border='0' src='../img/1363.jpg' onClick='win_open("../img/1363L.jpg","機械No1363 BND-3")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1362') ?>
            <input type='image' alt='機械No1362 BND-2' height='65' width='65' border='0' src='../img/1362.jpg' onClick='win_open("../img/1362L.jpg","機械No1362 BND-2")'>
        </td>
        <td width='30' height='30'></td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1367') ?>
            <input type='image' alt='機械No1367 BA20' height='65' width='65' border='0' src='../img/1367.jpg' onClick='win_open("../img/1367L.jpg","機械No1367 ﾂｶﾞﾐ-1")'>
        </td>
    </tr>
    <tr>
        <td bgcolor='#ffffff' height='120' nowrap valign='center' align='center'>
            <?php mac_state_view('1307') ?>
            <input type='image' alt='機械No1307 NC-7' height='65' width='65' border='0' src='../img/1307.jpg' onClick='win_open("../img/1307L.jpg","機械No1307 NC-7")'>
        </td>
    </tr>
    <tr>
        <td width='10' height='100' bgcolor='#f0f0f0'><b>ドア</b></td>
    </tr>
    <tr>
        <td width='10' rowspan='3'></td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1345') ?>
            <input type='image' alt='機械No1345 LD-4' height='65' width='65' border='0' src='../img/1345.jpg' onClick='win_open("../img/1345L.jpg","機械No1345 LD-4")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1341') ?>
            <input type='image' alt='機械No1341 KNC-1' height='65' width='65' border='0' src='../img/1341.jpg' onClick='win_open("../img/1341L.jpg","機械No1341 KNC-1")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1342') ?>
            <input type='image' alt='機械No1342 LD-1' height='65' width='65' border='0' src='../img/1342.jpg' onClick='win_open("../img/1342L.jpg","機械No1342 LD-1")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1343') ?>
            <input type='image' alt='機械No1343 LD-2' height='65' width='65' border='0' src='../img/1343.jpg' onClick='win_open("../img/1343L.jpg","機械No1343 LD-2")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1344') ?>
            <input type='image' alt='機械No1344 LD-3' height='65' width='65' border='0' src='../img/1344.jpg' onClick='win_open("../img/1344L.jpg","機械No1344 LD-3")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1346') ?>
            <input type='image' alt='機械No1346 BNE-1' height='65' width='65' border='0' src='../img/1346.jpg' onClick='win_open("../img/1346L.jpg","機械No1346 BNE-1")'>
        </td>
        
        <td colspan='4'></td>
        
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1349') ?>
            <input type='image' alt='機械No1349 WTS-1' height='65' width='65' border='0' src='../img/1349.jpg' onClick='win_open("../img/1349L.jpg","機械No1349 WTS-1")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1365') ?>
            <input type='image' alt='機械No1365 WT-100-1' height='65' width='65' border='0' src='../img/1365.jpg' onClick='win_open("../img/1365L.jpg","機械No1365 WT-1")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1366') ?>
            <input type='image' alt='機械No1366 WT-100-2' height='65' width='65' border='0' src='../img/1366.jpg' onClick='win_open("../img/1366L.jpg","機械No1366 WT-2")'>
        </td>
    </tr>   
    <tr>
        <td height='80' colspan='4' bgcolor='#f0f0f0'><center>洗浄機</center></td>
    </tr>
    <tr>
        <td height='30' colspan='5'></td>
        <td height='30' bgcolor='#f0f0f0'><center><b>５工場 ↓</b></center></td>
    </tr>
</table>
