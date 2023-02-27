<?php
//////////////////////////////////////////////////////////////////////////////
// 設備・機械マスター の 照会 ＆ メンテナンス                               //
//              MVC View 部     リスト表示                                  //
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
// 2005/06/28 MVCのView部へ変更  List表示 equip_macMasterMnt_ListView.php   //
// 2005/07/15 ../equipment.jp → machineMaster.js ヘ変更                    //
// 2005/08/19 ページ制御データを<a href=''に$model->get_htmlGETparm()で付加 //
// 2018/05/18 ７工場を追加。コード４の登録を強制的に7に変更            大谷 //
// 2018/12/25 ７工場を真鍮とSUSに分離。後々の為。                      大谷 //
//////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><%= $menu->out_title() %></title>
<%= $menu->out_site_java() %>
<%= $menu->out_css() %>

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
}
// -->
</script>
<script language='JavaScript' src='machineMaster.js?<?php echo $uniq ?>'></script>
</head>
<body onLoad='set_focus()'>
    <center>
<%= $menu->out_title_border() %>
    
    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
    <tr><td>
        <form action='<%=$menu->out_self(), "?id={$uniq}"%>' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='center' nowrap>
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
                    <td class='winbox' nowrap>
                        <%=$pageControll%>
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
        <?php if ($rows >= 1) { ?>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <caption>マスター 一覧</caption>
                <tr><td> <!-- ダミー -->
            <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <th class='winbox'>&nbsp;</th>
                <th class='winbox'>&nbsp;</th>
                <th class='winbox' nowrap>機械番号</th>
                <th class='winbox' width='80'>機械名称</th>
                <th class='winbox' nowrap>メーカー型式</th>
                <th class='winbox' nowrap>メーカー名</th>
                <th class='winbox' nowrap>工場区分</th>
                <th class='winbox' nowrap>有効</th>
                <th class='winbox' nowrap>Interface</th>
                <th class='winbox' nowrap>作業区</th>
                <th class='winbox' nowrap>使用電力</th>
                <th class='winbox' nowrap>電力係数</th>
            <?php for ($r=0; $r<$rows; $r++) { ?>
                <tr>
                <td class='winbox' align='right' nowrap><%=$r + 1 + $model->get_offset()%></td>
                <td class='winbox' align='center' nowrap><a href='<%=$menu->out_self(), "?mac_no={$res[$r][0]}&current_menu=edit&", $model->get_htmlGETparm(), "&id={$uniq}"%>'
                    style='text-decoration:none;'>編集</a></td>
                <td class='winbox' align='center' nowrap><%=$res[$r][0]%></td>
                <td class='winbox' align='left' nowrap><%=$res[$r][1]%></td>
                <td class='winbox' align='left' nowrap><%=$res[$r][2]%></td>
                <td class='winbox' align='left' nowrap><%=$res[$r][3]%></td>
                <?php if ($res[$r][4] == 1) {?>
                <td class='winbox' align='center' nowrap>１工場</td>
                <?php } elseif ($res[$r][4] == 2) {?>
                <td class='winbox' align='center' nowrap>２工場</td>
                <?php } elseif ($res[$r][4] == 4) {?>
                <td class='winbox' align='center' nowrap>４工場</td>
                <?php } elseif ($res[$r][4] == 5) {?>
                <td class='winbox' align='center' nowrap>５工場</td>
                <?php } elseif ($res[$r][4] == 6) {?>
                <td class='winbox' align='center' nowrap>６工場</td>
                <?php } elseif ($res[$r][4] == 7) {?>
                <td class='winbox' align='center' nowrap>７工場(真鍮)</td>
                <?php } elseif ($res[$r][4] == 8) {?>
                <td class='winbox' align='center' nowrap>７工場(SUS)</td>
                <?php } ?>
                <!-- 有効・無効 -->
                <td class='winbox' align='center' nowrap><%=$res[$r][5]%></td>
                <!-- インターフェース -->
                <td class='winbox' align='center' nowrap><%=$res[$r][6]%></td>
                <!-- 作業区 -->
                <td class='winbox' align='center' nowrap><%=$res[$r][7]%></td>
                <td class='winbox' align='right' nowrap><%=$res[$r][8]%></td>
                <td class='winbox' align='right' nowrap><%=$res[$r][9]%></td>
                </tr>
            <?php } ?>
            </table>
                </td></tr> <!-- ダミー -->
            </table>
        <?php } ?>
    </td></tr>
    </table>
    </center>
</body>
<%=$menu->out_alert_java()%>
</html>
