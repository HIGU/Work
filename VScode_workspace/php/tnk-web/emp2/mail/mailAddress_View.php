<?php
//////////////////////////////////////////////////////////////////////////////
// 社員マスターのメールアドレス 照会・メンテナンス                          //
//                                                      MVC View 部         //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/11/15 Created   mailAddress_View.php                                //
//////////////////////////////////////////////////////////////////////////////
?>
<link rel='stylesheet' href='mail/mailAddress.css?<?= $uniq ?>' type='text/css' media='screen'>
<script language='JavaScript' src='mail/mailAddress.js?=<?= $uniq ?>'></script>
<table width='100%' border='0'>
<tr>
    <td colspan='2' bgcolor='#003e7c' align='center' class='nasiji'>
        <span style='color:white;'>従業員のメールアドレス照会・編集</span>
    </td>
</tr>
<tr>
  <td>
    <form name='ControlForm' action='<?='emp_menu.php?func=', FUNC_MAIL, '&', $model->get_htmlGETparm(), "&id={$uniq}"?>' method='post'>
    <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr><td> <!----------- ダミー(デザイン用) ------------>
    <table class='winbox_field' bgcolor='eFeFeF' align='center' border='1' cellspacing='0' cellpadding='3'>
        <tr>
            <td nowrap <?php if($condition=='genzai') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='document.ControlForm.elements["genzai"].checked = true; document.ControlForm.submit();'
            >
                <input type='radio' name='condition' value='genzai' id='genzai' onClick='submit()'
                <?php if($condition=='genzai') echo 'checked' ?>>
                <label for='genzai'>現在勤務者一覧</label>
            </td>
            <td nowrap <?php if($condition=='syukko') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='document.ControlForm.elements["syukko"].checked = true; document.ControlForm.submit();'
            >
                <input type='radio' name='condition' value='syukko' id='syukko' onClick='submit()'
                <?php if($condition=='syukko') echo 'checked' ?>>
                <label for='syukko'>出向者一覧</label>
            </td>
            <td nowrap <?php if($condition=='taishoku') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='document.ControlForm.elements["taishoku"].checked = true; document.ControlForm.submit();'
            >
                <input type='radio' name='condition' value='taishoku' id='taishoku' onClick='submit()'
                <?php if($condition=='taishoku') echo 'checked' ?>>
                <label for='taishoku'>退職者一覧</label>
            </td>
            <td nowrap <?php if($condition=='ALL') echo "class='winbox s_radio'"; else echo "class='winbox n_radio'" ?>
                onClick='document.ControlForm.elements["ALL"].checked = true; document.ControlForm.submit();'
            >
                <input type='radio' name='condition' value='ALL' id='ALL' onClick='submit()'
                <?php if($condition=='ALL') echo 'checked' ?>>
                <label for='ALL'>全ての一覧</label>
            </td>
            <input type='hidden' name='showMenu'  value='Mail'>
        </tr>
    </table>
        </td></tr>
    </table> <!----------------- ダミーEnd ------------------>
    </form>
    
    <table class='list' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>メールアドレスの照会・編集</caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#eFeFeF' align='center' border='1' cellspacing='0' cellpadding='3'>
        <form name='mail_form' action='<?='emp_menu.php?func=', FUNC_MAIL, '&', $model->get_htmlGETparm(), "&id={$uniq}"?>'
            method='post' onSubmit='return mailAddress.mail_formCheck(this)'
        >
        <tr>
            <td class='winbox' nowrap>
                社員番号
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='uid' value='<?=$uid?>' size='6' maxlength='6'
                    style='ime-mode:disabled;'
                    onChange='this.value=this.value.toUpperCase()'
                <?=$readonly?>
                >
            </td>
            <td class='winbox' nowrap>
                氏　名
            </td>
            <td class='winbox' nowrap>
                <input type='text' name='name' value='<?=$name?>' size='8' maxlength='10' class='pt10' readonly style='background-color:#e6e6e6;'>
            </td>
            <td class='winbox' nowrap>
                メールアドレス
            </td>
            <td class='winbox pt8' nowrap>
                <input type='text' name='mailaddr' value='<?=$mailaddr?>' size='20' maxlength='40' class='pt8'>
            </td>
            <td class='winbox' nowrap>
                <input type='submit' name='mailEdit' value='登録' class='pt12b'>
                <!-- <input type='reset' name='mailEdit' value='取消' class='pt12b'> -->
            </td>
            <input type='hidden' name='showMenu' value='Mail'>
            <input type='hidden' name='condition' value='<?=$condition?>'>
        </tr>
        </form>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
    
    <table class='list' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
        <caption>
            <table border='0' width='100%'>
                <tr>
                    <td align='right' nowrap width='60%'>
                        メールアドレスの一覧 &nbsp;&nbsp;
                    </td>
                    <td align='center' nowrap width='40%'>
                        <form name='pageForm' action='<?='emp_menu.php?func=', FUNC_MAIL, "&id={$uniq}"?>' method='post'>
                        <?=$pageControl?>
                        <input type='hidden' name='showMenu' value='Mail'>
                        <input type='hidden' name='condition' value='<?=$condition?>'>
                        </form>
                    </td>
                </tr>
            </table>
        </caption>
        <tr><td> <!-- ダミー -->
    <table class='winbox_field' bgcolor='#eFeFeF' align='center' border='1' cellspacing='0' cellpadding='3'>
        <th class='winbox' width='30'>&nbsp;</th>
        <th class='winbox' width='40'>&nbsp;</th>
        <th class='winbox' width='40'>&nbsp;</th>
        <th class='winbox' nowrap>社員番号</th>
        <th class='winbox' nowrap>氏　名</th>
        <th class='winbox' nowrap>メールアドレス</th>
        <th class='winbox' nowrap>更新日</th>
    <?php if ($rows >= 1) { ?>
        <?php for ($r=0; $r<$rows; $r++) { ?>
            <tr>
            <td class='winbox' align='right' nowrap><?=$r + 1 + $model->get_offset()?></td>
            <td class='winbox' align='center' nowrap>
                <a href='<?='emp_menu.php?func=', FUNC_MAIL, "&uid={$res[$r][0]}&showMenu=Mail&mailOmit=go&mailaddr=", urlencode($res[$r][2]), '&condition=', urlencode($condition), '&', $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'
                    onClick='return confirm("実際に使われていなければ削除しても問題ありません。\n\n試行してみますか？")'
                >
                    削除
                </a>
            </td>
            <td class='winbox' align='center' nowrap>
                <a href='<?='emp_menu.php?func=', FUNC_MAIL, "&uid={$res[$r][0]}&showMenu=Mail&mailCopy=go&mailaddr=", urlencode($res[$r][2]), "&name=go&name=", urlencode($res[$r][1]), '&condition=', urlencode($condition), '&', $model->get_htmlGETparm(), "&id={$uniq}"?>'
                    style='text-decoration:none;'>編集
                </a>
            </td>
            <!-- 社員番号 -->
            <td class='winbox' align='center'><?=$res[$r][0]?></td>
            <!-- 氏名 -->
            <td class='winbox' align='left'><?=$res[$r][1]?></td>
            <!-- メールアドレス -->
            <td class='winbox' align='left'><?=$res[$r][2]?></td>
            <!-- 更新日 -->
            <td class='winbox' align='center'><?=$res[$r][3]?></td>
            </tr>
        <?php } ?>
    <?php } else { ?>
        <tr><td class='winbox' align='center' colspan='7'>
            登録がありません。
        </td></tr>
    <?php } ?>
    </table>
        </td></tr> <!-- ダミー -->
    </table>
  </td>
</tr>
</table>
<?=$menu->out_alert_java()?>
