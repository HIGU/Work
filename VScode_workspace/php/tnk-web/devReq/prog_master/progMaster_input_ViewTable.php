<?php
//////////////////////////////////////////////////////////////////////////////
// プログラムマスターの照会・メンテナンス                                   //
//                          MVC View 部   テーブルデータのみ表示(Ajax対応)  //
// ＊＊＊このファイルはAjax()を使用しているため特別にUTF-8の文字コードである//
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/26 Created   progMaster_input_ViewTable.php                      //
//////////////////////////////////////////////////////////////////////////////
$_SESSION['s_sysmsg'] = '';     // 見つからなかった時のエラーメッセージを抑止
?>
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>プログラム マスター 一覧</caption>
            <tr><td> <!-- ダミー -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr>
            <td class='winbox' align='right' rowspan='5' nowrap><?=$r + 1 + $model->get_offset()?></td>
            <td class='winbox' align='center' rowspan='5' nowrap>
            <?php $res[$r][0] = str_replace('#', 'シャープ', $res[$r][0]); ?>
                <a href='<?=$menu->out_self(), "?pid={$res[$r][0]}&pdir={$res[$r][2]}&current_menu=edit&", $model->get_htmlGETparm(), "&pidKey={$pidKey}", "&id={$uniq}"?>'
                 style='text-decoration:none;'>
                    編集
                </a>
            </td>
            <th class='winbox' nowrap>プログラムID</th>
            <th class='winbox' nowrap>プログラム名</th>
            <th class='winbox' nowrap>ディレクトリ</th>
            </tr>
            <tr>
            <?php $res[$r][0] = str_replace('シャープ', '#', $res[$r][0]); ?>
            <!-- プログラムID -->
            <td class='winbox' align='left' nowrap><B><?=$res[$r][0]?></B></td>
            <!-- プログラム名 -->
            <td class='winbox' align='left' nowrap><?=mb_convert_encoding($res[$r][1], 'UTF-8', 'UTF-8')?></td>
            <!-- ディレクトリ -->
            <td class='winbox' align='left' nowrap><?=mb_convert_encoding($res[$r][2], 'UTF-8', 'UTF-8')?></td>
            </tr>
            <tr>
            <th class='winbox' colspan='2' nowrap>コメント</th>
            <th class='winbox' nowrap>DB使用</th>
            </tr>
            <tr>
            <!-- コメント -->
            <td class='winbox' align='left' colspan='2' nowrap><?=mb_convert_encoding($res[$r][3], 'UTF-8', 'UTF-8')?></td>
            <?php
            $db_use = 0;
            for ($i=4; $i<16; $i++) {
                if ($res[$r][$i] != '') {
                    $db_use = 1;
                }
            }
            if ($db_use == 1) {
            $db_url = 'progMaster_input_db_detail.php?db1='. $res[$r][4] .'&db2='. $res[$r][5] .'&db3='. $res[$r][6] .'&db4='. $res[$r][7] .'&db5='. $res[$r][8] .'&db6='. $res[$r][9] .'&db7='. $res[$r][10] .'&db8='. $res[$r][11] .'&db9='. $res[$r][12] .'&db10='. $res[$r][13] .'&db11='. $res[$r][14] .'&db12='. $res[$r][15];
            ?>
            <!-- DB使用 -->
            <td class='winbox' align='center' nowrap><a href='<?php echo $db_url ?>' onclick="ProgMaster.win_open('<?php echo $db_url ?>', 1000, 440); return false;" title='クリックで使用ＤＢの詳細を表示します。'>○</a></td>
            </tr>
            <?php
            } else {
            ?>
            <!-- DB未使用 -->
            <td class='winbox' align='center' nowrap>×</td>
            </tr>
            <?php
            }
            ?>
            <tr>
            <th class='winboxb' nowrap>登録日時</th>
            <td class='winboxb'  colspan='2' align='left' nowrap>　<?=$res[$r][16]?>　</td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ダミー -->
        </table>
    <?php } elseif ($pidKey != '') { ?>
        <p>
        <div class='caption_font'>上記のプログラム名に合致するデータはありません！</div>
        </p>
    <?php } else { ?>
        <p>
        <div class='caption_font'>プログラム名欄に１文字入力する毎に検索結果を表示します。(インクリメンタルサーチ)</div>
        </p>
    <?php } ?>
