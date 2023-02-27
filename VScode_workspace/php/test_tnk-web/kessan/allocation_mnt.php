<?php
//////////////////////////////////////////////////////////////////////////////
// アロケーション(小分類)配賦マスターの保守                                 //
//            (allocation_item act_allocation の２つのテーブルを使用)       //
// Copyright (C) 2002-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/11/12 Created   allocation.mnt.php                                  //
// 2002/11/12               スタイルシートを別ファイルへallocation.css      //
// 2002/11/26 category_mnt.php と同じように UPDATE 時にユニークに           //
//             なるように変更 orign_id を編集できるように変更。配布元       //
// 2003/05/15 $_SESSION['allo_id'] = ""; をコメントアウト ロジックミス      //
// 2003/12/04 配賦率の入力がブランクの場合 SQL文に 0 を強制的に入れる       //
// 2004/10/28 user_check()functionを追加し編集出来るユーザーを限定          //
// 2005/11/02 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2007/04/18 ユーザーに300144大谷を追加 大谷                               //
// 2009/03/11 ユーザーに014737桝を追加 桝                                   //
// 2016/06/09 機械賃率配賦時の注意を追加                                    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

////////////// サイト設定
$menu->set_site(INDEX_PL, 12);              // site_index=INDEX_PL(損益メニュー) site_id=12(小分類配布率)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('小分類(配賦)項目マスター・配賦率のメンテナンス');

///////// 配賦項目マスター
$allo_id_view = array(
    '1',
    '2',
    '3',
    '4',
    '5',
);
$allo_item_view = array(
    'test1',
    'test2',
    'test3',
    'test4',
    'test5',
);
$allo_id_max = 5;
$rows_item=5;

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>

<link rel='stylesheet' href='allocation.css' type='text/css'>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <tr>
                <form action='allocation_mnt.php' method='post'>
                    <td align='center'>
                        配賦項目マスター
                        <select name='allo_id' class='pt11b'>
                            <?php
                            for($i=0;$i<$rows_item;$i++){
                                if($_SESSION['allo_id'] == $allo_id_view[$i])
                                    echo "<option value='" . $allo_id_view[$i] . "' selected>" . $allo_item_view[$i] . "</option>\n";
                                else
                                    echo "<option value='" . $allo_id_view[$i] . "'>" . $allo_item_view[$i] . "</option>\n";
                            }
                            if($_SESSION['allo_id'] == $allo_id_max)
                                echo "<option value='$allo_id_max' class='fc_red' selected>新規 追加</option>\n";
                            else
                                echo "<option value='$allo_id_max' class='fc_red'>新規 追加</option>\n";
                            ?>
                        </select>
                    </td>
                    <td align='center'>
                        <input type='submit' name='select' value='実行' >
                    </td>
                </form>
            </tr>
        </table>
        ※ 機械賃率配賦時には同率は禁止。なるべく10の位で配賦できるように丸める。<BR>
           新規追加時は部門を機械賃率の部門順にすべて追加すること。
        <?php
        if(false){ ////////////////////////////// 追加のブラウザー表示
        ?>
        <hr>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <form action='allocation_mnt.php' method='post'>
                <caption>新　規　登　録</caption>
                <th>分類項目名</th><th>配賦方法 ・ 備　考</th><th colspan='2'>グループ</th>
                <tr>
                    <td><input type='text' name='allo_item' value='' size='40' maxlength='20'></td>
                    <td><input type='text' name='allo_method' value='' size='80' maxlength='50'></td>
                    <?php
                    echo "<td><input type='text' name='allo_group' value='allo_id' size='7' maxlength='6' class='right'></td>\n";
                    echo "<td><input type='submit' name='register' value='登録' ></td>\n";
                    ?>
                </tr>
            </form>
        </table>
        <?php
        }
        if(true){ ////////////////////////////// 変更・削除のブラウザー表示
        ?>
        <hr>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <form action='allocation_mnt.php' method='post'>
                <caption>変更・削除</caption>
                <th>分類項目名</th><th>配賦方法 ・ 備　考</th><th>Group</th><th><input type='submit' name='delete' value='削除' class='fc_red'></th>
                <tr>
                    <td><input type='text' name='allo_item' value='<?php echo "allo_item" ?>' size='40' maxlength='20'></td>
                    <td><input type='text' name='allo_method' value='<?php echo "allo_method" ?>' size='80' maxlength='50'></td>
                    <td><input type='text' name='allo_group' value='<?php echo "allo_group" ?>' size='7' maxlength='6' class='right'></td>
                    <td><input type='submit' name='change' value='変更' class='fc_blue'></td>
                </tr>
            </form>
        </table>
        <hr>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <form action='allocation_mnt.php' method='post'>
                <?php /////////////////////////////////////////////// 配賦部門の追加・変更・削除

                $res_allo = array(
                    ["s_name"=>"test1", "act_id"=>"1", "orign_id"=>"1", "allo_id"=>"1", "dest_id"=>"1", "group_id"=>"1", "allo_rate"=>"dummy"],
                    ["s_name"=>"test1", "act_id"=>"2", "orign_id"=>"2", "allo_id"=>"2", "dest_id"=>"2", "group_id"=>"2", "allo_rate"=>"dummy"],
                    ["s_name"=>"test1", "act_id"=>"3", "orign_id"=>"3", "allo_id"=>"3", "dest_id"=>"3", "group_id"=>"3", "allo_rate"=>"dummy"],
                    ["s_name"=>"test1", "act_id"=>"4", "orign_id"=>"4", "allo_id"=>"4", "dest_id"=>"4", "group_id"=>"4", "allo_rate"=>"dummy"],
                    ["s_name"=>"test1", "act_id"=>"5", "orign_id"=>"5", "allo_id"=>"5", "dest_id"=>"5", "group_id"=>"5", "allo_rate"=>"dummy"]
                );
                $rows_allo = 5;

                echo "<caption>部門のメンテナンス　<input type='submit' name='all_del' value='一括削除' class='fc_red'>\n";
                echo "<input type='submit' name='all_add' value='一括登録' class='fc_blue'></caption>\n";
                echo "<th>配賦元</th><th>分類</th><th>登録・削除</th><th>配賦部門名</th><th>配賦率</th><th>Group</th><th>部門/更新</th>\n";
                echo "</form>\n";
                for($a=0;$a<$rows_allo;$a++){
                    echo "<form action='allocation_mnt.php' method='post'>\n";
                    echo "<tr>\n";
                    if($res_allo[$a]['dest_id'] == ""){
                        echo "  <td align='center'>---</td>\n";
                        echo "  <td align='center'>---</td>\n";
                        echo "  <td align='center'><input type='submit' name='allo_add' value='登録' class='fc_blue'></td>\n";
                        echo "  <td>" . $res_allo[$a]['s_name'] . "</td>\n";
                        echo "  <td align='center'>---</td>\n";
                        echo "  <td align='center'>---</td>\n";
                        echo "  <td align='center'>" . $res_allo[$a]['act_id'] . "</td>\n";
                        echo "  <input type='hidden' name='act_id' value='" . $res_allo[$a]['act_id'] . "'>\n";
                        echo "  <input type='hidden' name='select' value='" . $_POST['select'] . "'>\n"; //// 再表示のため
                    }else{
                        echo "  <td align='right'><input type='text' name='orign_id' value='" . $res_allo[$a]['orign_id'] . "' size='5' maxlength='4' class='right'></td>\n";
                        echo "  <td align='right'>" . $res_allo[$a]['allo_id'] . "</td>\n";
                        echo "  <td align='center'><input type='submit' name='allo_del' value='削除' class='fc_red'></td>\n";
                        echo "  <td>" . $res_allo[$a]['s_name'] . "</td>\n";
                        echo "  <td align='right'><input type='text' name='allo_rate' value='" . $res_allo[$a]['allo_rate'] . "' size='5' maxlength='3' class='right'></td>\n";
                        echo "  <td align='right'><input type='text' name='group_id' value='" . $res_allo[$a]['group_id'] . "' size='5' maxlength='4' class='right'></td>\n";
                        echo "  <td align='center'><input type='submit' name='dest_update' value='" . $res_allo[$a]['dest_id'] . "' class='fc_blue'></td>\n";
                        echo "  <input type='hidden' name='dest_id' value='" . $res_allo[$a]['dest_id'] . "'>\n";
                        echo "  <input type='hidden' name='allo_id' value='" . $res_allo[$a]['allo_id'] . "'>\n";
                        echo "  <input type='hidden' name='select' value='select'>\n"; //// 再表示のため
                    }
                    echo "</tr>\n";
                    echo "</form>\n";
                }
                ?>
        </table>
        <?php
        }
        ?>
    </center>
</body>
</html>
