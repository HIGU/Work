<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理システムの現在運転状況一覧マップ表示(レイアウト)Include file //
// Copyright (C) 2021-2021 Norihisa.ooya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2021/06/22 Created  equip_work_map7susList.php (include file)            //
// 2021/07/27 NTX-2の写真を追加                                             //
//////////////////////////////////////////////////////////////////////////////
?>
<style type='text/css'>
.mapimage {
        background: white url('map7.jpg') left top auto no-repeat;
    }
</style>
<table width='100%' height='100%' cellspacing='3' cellpadding='0' border='2' bgcolor='#f7f7f7' bordercolor='#1a6699' class='mapimage'>
    <tr>
        <td rowspan='3'></td>
        <!--
        <td width='70' height='30'></td>
        <td width='70' height='30' rowspan='3' bgcolor='#f0f0f0'>2次加工</td>
        <td height='30' bgcolor='#f0f0f0'><center><b>ドア</b></center></td>
        <td height='30' colspan='4'></td>
        <td height='30' bgcolor='#f0f0f0' nowrap><b>↑ １工場</b></td>
        -->
        <td hcolspan='16'></td>
        <td rowspan='3'></td>
        <!--
        <td height='30' bgcolor='#f0f0f0'><b>事務所 →<b></td>
        -->
    </tr>
    <tr>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1230') ?>
            <input type='image' alt='機械No1230 B0385L' height='65' width='65' border='0' src='../img/1230.jpg' onClick='win_open("../img/1230L.jpg","機械No1230 B0385L")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1233') ?>
            <input type='image' alt='機械No1233 NZX-1' height='65' width='65' border='0' src='../img/1233.jpg' onClick='win_open("../img/1233L.jpg","機械No1233 NZX-1")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1234') ?>
            <input type='image' alt='機械No1234 NZX-2' height='65' width='65' border='0' src='../img/1234.jpg' onClick='win_open("../img/1234L.jpg","機械No1234 NZX-2")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1235') ?>
            <input type='image' alt='機械No1235 MC20' height='65' width='65' border='0' src='../img/1235.jpg' onClick='win_open("../img/1235L.jpg","機械No1235 MC20")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1257') ?>
            <input type='image' alt='機械No1257 NTX-1' height='65' width='65' border='0' src='../img/1257.jpg' onClick='win_open("../img/1257L.jpg","機械No1257 NTX-1")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            タキサワ<BR>MC
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            SL-15
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            日立<BR>MC
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1228') ?>
            <input type='image' alt='機械No1228 WT-300' height='65' width='65' border='0' src='../img/1228.jpg' onClick='win_open("../img/1228L.jpg","機械No1228 WT-300")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1226') ?>
            <input type='image' alt='機械No1226 マザック' height='65' width='65' border='0' src='../img/1226.jpg' onClick='win_open("../img/1226L.jpg","機械No1226 マザック")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1229') ?>
            <input type='image' alt='機械No1229 BNE' height='65' width='65' border='0' src='../img/1229.jpg' onClick='win_open("../img/1229L.jpg","機械No1229 BNE")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1258') ?>
            <input type='image' alt='機械No1258 BS-18' height='65' width='65' border='0' src='../img/1258.jpg' onClick='win_open("../img/1258L.jpg","機械No1258 BS-18")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1225') ?>
            <input type='image' alt='機械No1225 WT-150' height='65' width='65' border='0' src='../img/1225.jpg' onClick='win_open("../img/1225L.jpg","機械No1225 WT-150")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1227') ?>
            <input type='image' alt='機械No1227 WT-150S' height='65' width='65' border='0' src='../img/1227.jpg' onClick='win_open("../img/1227L.jpg","機械No1227 WT-150S")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1224') ?>
            <input type='image' alt='機械No1224 TW-10' height='65' width='65' border='0' src='../img/1224.jpg' onClick='win_open("../img/1224L.jpg","機械No1224 TW-10")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1259') ?>
            <input type='image' alt='機械No1259 NTX-2' height='65' width='65' border='0' src='../img/1259.jpg' onClick='win_open("../img/1259L.jpg","機械No1259 NTX-2")'>
        </td>
    </tr>
    <tr>
    </tr>
    <tr>
        <td bgcolor='#f0f0f0'><b>シ<BR>ャ<BR>ッ<BR>タ<BR>ー</b></td>
    </tr>
    <tr>
        <td rowspan='3'></td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            仕掛品<BR>置場
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            2次<BR>加工
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1349') ?>
            <input type='image' alt='機械No1349 WTS' height='65' width='65' border='0' src='../img/1349.jpg' onClick='win_open("../img/1349L.jpg","機械No1349 WTS")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1365') ?>
            <input type='image' alt='機械No1365 WT-1' height='65' width='65' border='0' src='../img/1365.jpg' onClick='win_open("../img/1365L.jpg","機械No1365 WT-1")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1366') ?>
            <input type='image' alt='機械No1366 WT-2' height='65' width='65' border='0' src='../img/1366.jpg' onClick='win_open("../img/1366L.jpg","機械No1366 WT-2")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1348') ?>
            <input type='image' alt='機械No1348 BNF-2' height='65' width='65' border='0' src='../img/1348.jpg' onClick='win_open("../img/1348L.jpg","機械No1348 BNF-2")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1347') ?>
            <input type='image' alt='機械No1347 BNF-1' height='65' width='65' border='0' src='../img/1347.jpg' onClick='win_open("../img/1347L.jpg","機械No1347 BNF-1")'>
        </td>
        <td colspan='1'></td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1364') ?>
            <input type='image' alt='機械No1364 BND-4' height='65' width='65' border='0' src='../img/1364.jpg' onClick='win_open("../img/1364L.jpg","機械No1364 BND-4")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1346') ?>
            <input type='image' alt='機械No1346 BNE-1' height='65' width='65' border='0' src='../img/1346.jpg' onClick='win_open("../img/1346L.jpg","機械No1346 BNE-1")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1367') ?>
            <input type='image' alt='機械No1367 ﾂｶﾞﾐ-1' height='65' width='65' border='0' src='../img/1367.jpg' onClick='win_open("../img/1367L.jpg","機械No1367 ﾂｶﾞﾐ-1")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1369') ?>
            <input type='image' alt='機械No1369 L20' height='65' width='65' border='0' src='../img/1369.jpg' onClick='win_open("../img/1369L.jpg","機械No1369 L20")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1373') ?>
            <input type='image' alt='機械No1373 L32' height='65' width='65' border='0' src='../img/1373.jpg' onClick='win_open("../img/1373L.jpg","機械No1373 L32")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1374') ?>
            <input type='image' alt='機械No1374 BNA' height='65' width='65' border='0' src='../img/1374.jpg' onClick='win_open("../img/1374L.jpg","機械No1374 BNA")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1372') ?>
            <input type='image' alt='機械No1372 ABX-2' height='65' width='65' border='0' src='../img/1372.jpg' onClick='win_open("../img/1372L.jpg","機械No1372 ABX-2")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='center' align='center'>
            <?php mac_state_view('1368') ?>
            <input type='image' alt='機械No1368 ABX-1' height='65' width='65' border='0' src='../img/1368.jpg' onClick='win_open("../img/1368L.jpg","機械No1368 ABX-1")'>
        </td>
    </tr>
    <!--
    <tr>
        <td height='80' colspan='4' bgcolor='#f0f0f0'><center>洗浄機</center></td>
    </tr>
    -->
    <tr>
    </tr>
    <tr>
        <td colspan='5'></td>
        <td bgcolor='#f0f0f0'><center><b>６工場 ↓</b></center></td>
        <td colspan='11'></td>
    </tr>
</table>
