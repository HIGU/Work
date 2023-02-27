<?php
//////////////////////////////////////////////////////////////////////////////
// 生産システムの部品・製品関係のアイテムマスターの照会・メンテ             //
//                          MVC View 部   テーブルデータのみ表示(Ajax対応)  //
// ＊＊＊このファイルはAjax()を使用しているため特別にUTF-8の文字コードである//
// Copyright (C) 2005-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/09/14 Created   parts_item_ViewTable.php                            //
// 2005/09/23 [合致するデータはありません] のメッセージを追加               //
// 2009/07/24 部品番号の途中に＃が入ったときの問題対応                 大谷 //
//////////////////////////////////////////////////////////////////////////////
$_SESSION['s_sysmsg'] = '';     // 見つからなかった時のエラーメッセージを抑止
?>
    <?php if ($rows >= 1) { ?>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>部品・製品 のアイテム マスター 一覧</caption>
            <tr><td> <!-- ダミー -->
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox' nowrap>部品番号</th>
            <th class='winbox' nowrap>部品名称</th>
            <th class='winbox' nowrap>材　　質</th>
            <th class='winbox' nowrap>親機種</th>
            <th class='winbox' nowrap>AS登録日</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr>
            <td class='winbox' align='right' nowrap><?=$r + 1 + $model->get_offset()?></td>
            <td class='winbox' align='center' nowrap>
            <?php $res[$r][0] = str_replace('#', 'シャープ', $res[$r][0]); ?>
                <a href='<?=$menu->out_self(), "?parts_no={$res[$r][0]}&current_menu=edit&", $model->get_htmlGETparm(), "&partsKey={$partsKey}", "&id={$uniq}"?>'
                 style='text-decoration:none;'>
                    編集
                </a>
            </td>
            <?php $res[$r][0] = str_replace('シャープ', '#', $res[$r][0]); ?>
            <!-- 部品・製品 番号 -->
            <td class='winbox' align='center' nowrap><?=$res[$r][0]?></td>
            <!-- 部品・製品 名称 -->
            <td class='winbox' align='left' nowrap><?=mb_convert_encoding($res[$r][1], 'UTF-8', 'UTF-8')?></td>
            <!-- 材質 -->
            <td class='winbox' align='left' nowrap><?=mb_convert_encoding($res[$r][2], 'UTF-8', 'UTF-8')?></td>
            <!-- 親機種 -->
            <td class='winbox' align='left' nowrap><?=mb_convert_encoding($res[$r][3], 'UTF-8', 'UTF-8')?></td>
            <!-- AS登録日 -->
            <td class='winbox' align='center' nowrap><?=$res[$r][4]?></td>
            </tr>
        <?php } ?>
        </table>
            </td></tr> <!-- ダミー -->
        </table>
    <?php } elseif ($partsKey != '') { ?>
        <p>
        <div class='caption_font'>上記の部品番号に合致するデータはありません！</div>
        </p>
    <?php } else { ?>
        <p>
        <div class='caption_font'>部品番号欄に１文字入力する毎に検索結果を表示します。(インクリメンタルサーチ)</div>
        </p>
    <?php } ?>
