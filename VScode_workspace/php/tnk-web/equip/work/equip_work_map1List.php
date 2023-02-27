<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理システムの現在運転状況一覧マップ表示(レイアウト)Include file //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
//               Designed  Norihisa.ooya                                    //
// Changed history                                                          //
// 2004/10/01 Created  equip_work_map1List.php (include file)               //
// 2006/03/09 レイアウト大幅変更(TOS並び替え、Mazak・30SH刻印機追加         //
// 2007/01/16 左下刻印機レイアウト変更                                      //
// 2007/06/28 レイアウト変更(NC-13,NC-14,WSC-BII,20SH刻印機削除WT-150S追加) //
// 2007/06/29 WT-150S写真変更   大谷                                        //
//////////////////////////////////////////////////////////////////////////////
?>
<table width='60%' height='60%' cellspacing='2' cellpadding='0' border='1' bgcolor='#f7f7f7' bordercolor='#1a6699'>
    <col span='1' valign='bottom' align='center' width='10'>
    <col span='15' valign='bottom' align='center'>
    <tr valign='middle' align='center'>
        <td border='1' rowspan='2' width='19' height='151' nowrap></td>
        <td nowrap width='70' valign='bottom' align='center' height='16'></td>
        <td nowrap width='70' valign='bottom' align='center' height='16'></td>
        <td nowrap width='70' valign='bottom' align='center' height='16'></td>
        <td bgcolor='#f0f0f0' width='70' nowrap valign='bottom' align='center' height='16'><center><b>↑ 2工場</b></center></td>
        <td bgcolor='#f0f0f0' nowrap height='16' width='30'><center><b>ドア</b></center></td>
        <td colspan='2' valign='bottom' align='center' nowrap height='16' width='140'></td>
        <td width='70' nowrap valign='bottom' align='center' height='16'></td>
        <td bgcolor='#f0f0f0' rowspan='3' colspan='5' nowrap valign='middle' align='center' width='280' height='286'><b>事務所</b></td>
        <td rowspan='2' width='10'></td>
    </tr>
    <tr valign='middle' align='center'>
        <td nowrap width='70' valign='bottom' align='center' height='135'>
        </td>
        <td nowrap width='70' valign='bottom' align='center' height='135'>
        </td>
        <td bgcolor='#ffffff' nowrap width='70' valign='bottom' align='center' height='135'>
            <?php mac_state_view('1212') ?>
            <input type='image' alt='機械No1212 NC-12' height='65' width='65' border='0' src='../img/1212.jpg' onClick='win_open("../img/1212L.jpg","機械No1212 NC-12")'>
        </td>
        <td bgcolor='#ffffff' width='70' nowrap valign='bottom' align='center' height='135'>
            <?php mac_state_view('1211') ?>
            <input type='image' alt='機械No1211 NC-11' height='65' width='65' border='0' src='../img/1211.jpg' onClick='win_open("../img/1211L.jpg","機械No1211 NC-11")'>
        </td>
        <td height='135' width='30'></td>
        <td bgcolor='#ffffff' colspan='2' rowspan='1' valign='bottom' align='center' nowrap height='135' width='140'>
            <?php mac_state_view('1226') ?>
            <input type='image' alt='機械No1226 Mazak' height='65' width='65' border='0' src='../img/1226.jpg' onClick='win_open("../img/1226L.jpg","機械No1226 NC-26")'>
        </td>
        <td width='70' height='135'></td>
    </tr>
    <tr valign='middle' align='center'>
        <td bgcolor='#f0f0f0' rowspan='2' width='19' valign='middle' nowrap height='270'><b>←６工場</b></td>
        <td rowspan='2' nowrap width='70' height='270' valign='bottom' align='center'></td>
        <td nowrap width='70' height='135' valign='bottom' align='center'></td>
        <td nowrap width='70' height='135' valign='bottom' align='center'></td>
        <td nowrap width='70' height='135' valign='bottom' align='center'></td>
        <td rowspan='2' width='30' height='270'></td>
        <td bgcolor='#ffffff' colspan='2' rowspan='1' valign='bottom' align='center' height='135' width='140'>
            <?php mac_state_view('1258') ?>
            <input type='image' alt='機械No1258 BS18-Ⅲ' height='65' width='65' border='0' src='../img/1258.jpg' onClick='win_open("../img/1258L.jpg","機械No1258 BS18-Ⅲ")'>
        </td>
        <td colspan='1' rowspan='2' width='70' height='270' nowrap valign='bottom' align='center'></td>
        <td bgcolor='#f0f0f0' rowspan='2' width='10' height='270'><b>→４工場</b></td>
    </tr>
    <tr valign='top' align='left'>
        <td bgcolor='#ffffff' nowrap width='70' height='135' valign='bottom' align='center'>
            <?php mac_state_view('1215') ?>
            <input type='image' alt='機械No1215 NC-15' height='65' width='65' border='0' src='../img/1215.jpg' onClick='win_open("../img/1215L.jpg","機械No1215 NC-15")'>
        </td>
        <td bgcolor='#ffffff' nowrap width='70' height='135' valign='bottom' align='center'>
            <?php mac_state_view('1218') ?>
            <input type='image' alt='機械No1218 NC-18' height='65' width='65' border='0' src='../img/1218.jpg' onClick='win_open("../img/1218L.jpg","機械No1218 NC-18")'>
        </td>
        <td bgcolor='#ffffff' width='70' height='135' nowrap valign='bottom' align='center'>
            <?php mac_state_view('1217') ?>
            <input type='image' alt='機械No1217 NC-17' height='65' width='65' border='0' src='../img/1217.jpg' onClick='win_open("../img/1217L.jpg","機械No1217 NC-17")'>
        </td>
        <td bgcolor='#ffffff' colspan='1' rowspan='1' valign='bottom' align='center' height='135'>
            <?php mac_state_view('1252') ?>
            <input type='image' alt='機械No1252 SL-15' height='65' width='65' border='0' src='../img/1252.jpg' onClick='win_open("../img/1252L.jpg","機械No1252 SL-15")'>
        </td>
        <td bgcolor='#ffffff' colspan='1' rowspan='1' valign='bottom' align='center' height='135'>
            <?php mac_state_view('1251') ?>
            <input type='image' alt='機械No1251 ZL-25m' height='65' width='65' border='0' src='../img/1251.jpg' onClick='win_open("../img/1251L.jpg","機械No1251 ZL-25m")'>
        </td>
        <td colspan='2' nowrap valign='bottom' align='center' width='140' height='135'></td>
        <td bgcolor='#ffffff' colspan='2' nowrap valign='bottom' align='center' height='135' width='140'>
            <?php mac_state_view('1256') ?>
            <input type='image' alt='機械No1256 MACV1E' height='65' width='65' border='0' src='../img/1256.jpg' onClick='win_open("../img/1256L.jpg","機械No1256 MACV1E")'>
        </td>
    </tr>
    <tr>
        <td width='19' nowrap height='135'></td>
        <td colspan='5' nowrap valign='bottom' align='center' height='135'></td>
        <td bgcolor='#ffffff' colspan='2' valign='bottom' align='center' height='135' width='140'>
            <?php mac_state_view('1224') ?>
            <input type='image' alt='機械No1224 TW-10' height='65' width='65' border='0' src='../img/1224.jpg' onClick='win_open("../img/1224L.jpg","機械No1224 TW-10")'>
        </td>
        <td bgcolor='#ffffff' colspan='2' nowrap valign='bottom' align='center' height='135' width='140'>
            <?php mac_state_view('1223') ?>
            <input type='image' alt='機械No1223 TW-20' height='65' width='65' border='0' src='../img/1223.jpg' onClick='win_open("../img/1223L.jpg","機械No1223 TW-20")'>
        </td>
        <td nowrap valign='bottom' align='center' height='135' width='70'></td>
        <td bgcolor='#ffffff' colspan='2' nowrap valign='bottom' align='center' height='135' width='140'>
            <?php mac_state_view('1253') ?>
            <input type='image' alt='機械No1253 U-M48' height='65' width='65' border='0' src='../img/1253.jpg' onClick='win_open("../img/1253L.jpg","機械No1253 U-M48")'>
        </td>
        <td height='135' width='10'></td>
    </tr>
    <tr>
        <td rowspan='1' width='19' valign='middle' nowrap height='22'><b>ドア</b></td>
        <td colspan='13' nowrap valign='bottom' align='center' height='22'></td>
    </tr>
    <tr>
        <td rowspan='5' width='19' nowrap height='556'></td>
        <td height='30' nowrap width='70' valign='bottom' align='center'></td>
        <td height='30' nowrap width='70' valign='bottom' align='center'></td>
        <td bgcolor='#ffffff' colspan='2' height='135' nowrap valign='bottom' align='center' width='140'>
            <?php mac_state_view('1120') ?>
            <input type='image' alt='機械No1120 ツガミ-2' height='65' width='65' border='0' src='../img/1120.jpg' onClick='win_open("../img/1120L.jpg","機械No1120 ツガミ-2")'>
        </td>
        <td height='135' width='3'></td>
        <td bgcolor='#ffffff' colspan='3' valign='center' align='center' height='135' nowrap width='140'>
            <?php mac_state_view('1225') ?>
            <input type='image' alt='機械No1225 WT-150' height='65' width='65' border='0' src='../img/1225.jpg' onClick='win_open("../img/1225L.jpg","機械No1225 NC-24")'>
        </td>
        <td bgcolor='#ffffff' colspan='3' valign='center' align='center' height='135' nowrap width='140'>
            <!----- 機械番号確定まで隠し <?php mac_state_view('4602') ?> ----->
            <font size ='2'>WT-150S</font>
            <input type='image' alt='WT-150S' height='65' width='65' border='0' src='../img/wt150s.jpg' onClick='win_open("../img/wt150sL.jpg","WT-150S")'>
        </td>
        <td colspan='1' height='135' nowrap valign='bottom' align='center'></td>
        <td colspan='1' valign='bottom' align='center' height='135' nowrap>
        </td>
        <td colspan='2' height='135' nowrap valign='bottom' align='center'></td>
    </tr>
    <tr>
        <td bgcolor='#ffffff' height='135' nowrap width='70' valign='bottom' align='center'>
            <?php mac_state_view('1191') ?>
            <input type='image' alt='機械No1191 20PM刻印機' height='65' width='65' border='0' src='../img/1191.jpg' onClick='win_open("../img/1191L.jpg","機械No1191 20PM刻印機")'>
        </td>
        <td nowrap width='70' valign='bottom' align='center' height='135'></td>
        <td bgcolor='#ffffff' colspan='2' nowrap valign='bottom' align='center' height='135' width='140'>
            <?php mac_state_view('1119') ?>
            <input type='image' alt='機械No1119 ツガミ-1' height='65' width='65' border='0' src='../img/1119.jpg' onClick='win_open("../img/1119L.jpg","ツガミ-1 BS18-Ⅲ")'>
        </td>
        <td height='135' width='30'></td>
        <td colspan='1' valign='bottom' align='center' height='135'></td>
        <td colspan='2' valign='bottom' align='center' height='135' nowrap width='140'>
        </td>
        <td colspan='3' nowrap valign='bottom' align='center' height='135' width='140'></td>
        <td bgcolor='#ffffff' colspan='1' valign='bottom' align='center' height='135' nowrap>
            <?php mac_state_view('1131') ?>
            <input type='image' alt='機械No1131 CBM3穴あけ機' height='65' width='65' border='0' src='../img/1131.jpg' onClick='win_open("../img/1131L.jpg","機械No1131 CBM3穴あけ機")'>
        </td>
        <td colspan='1' nowrap valign='bottom' align='center' height='135' width='70'></td>
        <td bgcolor='#f0f0f0' valign='middle' width='10' height='135'><b>ドア</b></td>
    </tr>
    <tr>
        <td bgcolor='#ffffff' nowrap width='70' valign='bottom' align='center' height='135'>
            <?php mac_state_view('1192') ?>
            <input type='image' alt='機械No1192 40PH刻印機' height='65' width='65' border='0' src='../img/1192.jpg' onClick='win_open("../img/1192L.jpg","機械No1191 40PH刻印機")'>
        </td>
        <td  nowrap width='70' valign='bottom' align='center' height='135'></td>
        <td bgcolor='#ffffff' colspan='2' nowrap valign='bottom' align='center' height='135' width='140'>
            <?php mac_state_view('1118') ?>
            <input type='image' alt='機械No1118 BS18-Ⅲ' height='65' width='65' border='0' src='../img/1118.jpg' onClick='win_open("../img/1118L.jpg","機械No1188 アクメ-2")'>
        </td>
        <td height='135' width='30'></td>
        <td valign='bottom' align='center' height='135'></td>
        <td bgcolor='#ffffff' rowspan='2' valign='bottom' align='center' width='70' height='270'>
            <?php mac_state_view('1116') ?>
            <input type='image' alt='機械No1116 TOS-16' height='65' width='65' border='0' src='../img/1116.jpg' onClick='win_open("../img/1116L.jpg","機械No1116 TOS-16")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='bottom' align='center' width='70' height='270'>
            <?php mac_state_view('1114') ?>
            <input type='image' alt='機械No1114 TOS-14' height='65' width='65' border='0' src='../img/1114.jpg' onClick='win_open("../img/1114L.jpg","機械No1114 TOS-14")'>
        </td>
        <td rowspan='2' nowrap valign='bottom' align='center' width='70' height='270'>
            <?php mac_state_view('1111') ?>
            <input type='image' alt='機械No1111 TOS-11' height='65' width='65' border='0' src='../img/1111.jpg' onClick='win_open("../img/1111L.jpg","機械No1111 TOS-11")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='bottom' align='center' width='70' height='270'>
            <?php mac_state_view('1115') ?>
            <input type='image' alt='機械No1115 TOS-15' height='65' width='65' border='0' src='../img/1115.jpg' onClick='win_open("../img/1115L.jpg","機械No1115 TOS-15")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='bottom' align='center' width='70' height='270'>
            <?php mac_state_view('1112') ?>
            <input type='image' alt='機械No1112 TOS-12' height='65' width='65' border='0' src='../img/1112.jpg' onClick='win_open("../img/1112L.jpg","機械No1112 TOS-12")'>
        </td>
        <td bgcolor='#ffffff' rowspan='2' nowrap valign='bottom' align='center' width='70' height='270'>
            <?php mac_state_view('1106') ?>
            <input type='image' alt='機械No1106 TOS-6' height='65' width='65' border='0' src='../img/1106.jpg' onClick='win_open("../img/1106L.jpg","機械No1258 TOS-6")'>
        </td>
    </tr>
    <tr>
        <td bgcolor='#ffffff' nowrap width='70' valign='bottom' align='center' height='135'>
            <?php mac_state_view('1194') ?>
            <input type='image' alt='機械No1194 30PH刻印機' height='65' width='65' border='0' src='../img/1194.jpg' onClick='win_open("../img/1194L.jpg","機械No1194 30PH刻印機")'>
        </td>
        <td bgcolor='#ffffff' nowrap width='70' valign='bottom' align='center' height='135'>
            <?php mac_state_view('1197') ?>
            <input type='image' alt='機械No1197 20PH刻印機' height='65' width='65' border='0' src='../img/1197.jpg' onClick='win_open("../img/1197L.jpg","機械No1197 20PH刻印機")'>
        </td>
        <td bgcolor='#ffffff' colspan='2' nowrap valign='bottom' align='center' height='135' width='140'>
            <?php mac_state_view('1117') ?>
            <input type='image' alt='機械No1117 アクメ-1' height='65' width='65' border='0' src='../img/1117.jpg' onClick='win_open("../img/1117L.jpg","機械No1117 アクメ-1")'>
        </td>
        <td height='135' width='30'></td>
        <td bgcolor='#ffffff' colspan='1' valign='bottom' align='center' height='135' nowrap>
            <?php mac_state_view('1180') ?>
            <input type='image' alt='機械No1180 20PF刻印機' height='65' width='65' border='0' src='../img/1180.jpg' onClick='win_open("../img/1180L.jpg","機械No1180 20PF刻印機")'>
        </td>
        <td bgcolor='#ffffff' valign='bottom' align='center' height='100'>
            <?php mac_state_view('1190') ?>
            <input type='image' alt='機械No1190 30SH刻印機' height='65' width='65' border='0' src='../img/1190.jpg' onClick='win_open("../img/1190L.jpg","機械No1190 30SH刻印機")'>
        </td>
    </tr>
    <tr>
        <td colspan='4' nowrap valign='bottom' align='center' height='16'></td>
        <td bgcolor='#f0f0f0' align='center' width='30' height='16'><b>ドア</b></td>
        <td colspan='7' nowrap valign='bottom' align='center' height='16'></td>
    </tr>
</table>
