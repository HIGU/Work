<?php
//////////////////////////////////////////////////////////////////////////////
// 組立の作業管理 着手・実績データ 照会   着手一覧画面      MVC View 部     //
//                                      テーブルデータのみ表示  Ajax対応版  //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/01/20 Created   assembly_process_show_ViewStartTable.php            //
//            DBから取得した作業者等の２バイト文字をUTF-8に変換するのがコツ //
//            製品名のように半角カナに変換している物も最後にUTF-8へ変換する //
//            ControlForm等は関数元での変換が対応できないため避ける事       //
// 2007/03/19 文字コードの問題のためout_action('引当構成表')→'AlloConfView'//
// 2007/03/26 パラメーターにmaterial=1を追加し、戻り時にpage_keepさせる。   //
//            計画番号クリック時の行番号保存処理を追加                      //
//////////////////////////////////////////////////////////////////////////////
$_SESSION['s_sysmsg'] = '';     // 見つからなかった時のエラーメッセージを抑止
?>
        <table class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox'>&nbsp;</th>
            <th class='winbox pt12b' width='80' nowrap>計画番号</th>
            <th class='winbox pt12b' width='80' nowrap>製品番号</th>
            <th class='winbox pt12b' width='180' nowrap>製　品　名</th>
            <th class='winbox pt12b' width='80' nowrap>計画残</th>
            <th class='winbox pt12b' width='80' nowrap>社員番号</th>
            <th class='winbox pt12b' width='80' nowrap>作業者</th>
            <th class='winbox pt12b' width='120' nowrap>組立着手</th>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <?php $recNo = ($r + 1 + $this->model->get_offset() )?>
            <?php if ($session->get_local('recNo') == $recNo) { ?>
            <tr style='background-color:#ffffc6;'>
            <?php } else { ?>
            <tr>
            <?php } ?>
            <!-- No. -->
            <td class='winbox pt12b' align='right' nowrap><?php echo $recNo ?></td>
            <!-- 計画番号 -->
            <td class='winbox pt12b' align='right' nowrap>
                <a
                href='<?php echo "JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}&{$uniq}\"); location.replace(\"", $menu->out_action('AlloConfView'), '?plan_no=', urlencode($res[$r][0]), "&material=1&id={$uniq}\");"?>'
                style='text-decoration:none;'
                onMouseover="status='この計画番号の引当部品構成表にジャンプします。';return true;"
                onMouseout="status=''"
                title='この計画番号の引当部品構成表にジャンプします。'
                >
                    <?=$res[$r][0]?>
                </a>
            </td>
            <!-- 製品番号 -->
            <td class='winbox pt12b' align='left' nowrap><?=$res[$r][1]?></td>
            <!-- 製品名 -->
            <td class='winbox pt12b' align='left' nowrap>
                <?=mb_convert_encoding(mb_convert_kana($res[$r][2], 'k'), 'UTF-8', 'EUC-JP')?>
            </td>
            <!-- 計画残数 -->
            <td class='winbox pt12b' align='right' nowrap onDblClick='alert("計画残／計画数は\n\n<?=$res[$r][3]?>／<?=$res[$r][13]?>\n\nです。")'>
                <?=$res[$r][3]?>
            </td>
            <!-- 社員番号 -->
            <td class='winbox pt12b' align='center' nowrap><?=$res[$r][4]?></td>
            <!-- 作業者 -->
            <td class='winbox pt12b' align='left' nowrap onDblClick='alert("社員番号\n\n <?=$res[$r][4]?>")'>
                <?=mb_convert_encoding($res[$r][5], 'UTF-8', 'EUC-JP')?>
            </td>
            <!-- 組立着手日時 -->
            <td class='winbox pt12b' align='center' nowrap onDblClick='alert("開始時間の詳細\n\n<?=$res[$r][10]?>")'>
                <?=$res[$r][6]?>
            </td>
            </tr>
        <?php } ?>
        </table>
