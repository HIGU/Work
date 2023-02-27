<?php
//////////////////////////////////////////////////////////////////////////////
// 経理部門コード・配賦率の保守 act_table.php → act_table_new.php          //
// Copyright (C) 2002-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/09/17 Created  act_table_mnt_new.php                                //
// 2002/09/20 サイトメニューに追加                                          //
// 2002/09/28 配賦率テーブルを act_allocation & allocation_item に変更      //
//            そのためプログラムを大幅に変更 ファイル名変更(表題)           //
// 2002/11/26 損益グループの配賦率照会がほぼ完成 1項目クエリー              //
//                         全体で100％のチェック未完成？                    //
// 2003/02/26 body に onLoad を追加し初期入力個所に focus() させた          //
// 2003/05/15 新組織体系に対応 所属コードを追加 大分類項目保守でメンテ      //
// 2004/10/28 user_check()functionを追加し編集出来るユーザーを限定          //
// 2005/10/27 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2007/04/17 権限に300144を追加 大谷                                       //
// 2009/03/12 権限に014737を追加 桝                                         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

////////////// サイト設定
$menu->set_site(INDEX_PL, 10);                    // site_index=INDEX_PL(損益メニュー) site_id=10(部門コード)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('経理部門コードのメンテナンス');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();

$flag = 3;

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

<script type='text/javascript' language='JavaScript' src='act_table_mnt.js'></script>
<style type='text/css'>
    <!--
    select{
        background-color:teal;
        color:white;
    }
    textarea{
        background-color:black;
        color:white;
    }
    input.sousin{
        background-color:red;
    }
    input.text{
        background-color:black;
        color:white;
    }
    .pt11{
        font-size:11pt;
    }
    .margin1{
        margin:1%;
    }
    .margin0{
        margin:0%;
    }
    .pt12b{
        font:bold 12pt;
    }
    .y_b{
        background-color:yellow;
        color:blue;
    }
    .r_b{
        background-color:red;
        color:black;
    }
    .r_w{
        background-color:red;
        color:white;
    }
    .b_w{
        background-color:blue;
        color:white;
    }
    .fsp{
        font-size:8pt;
    }
    .fmp{
        font-size:10pt;
    }
    .flp{
        font-size:12pt;
    }
    .fllbp{
        font-size:16pt;
        font-weight:bold;
    }
    .fmp-n{
        background-color:yellow;
        color:blue;
        font-size:10pt;
        font-weight:normal;
    }
    input.blue{
        color:blue;
    }
    input.red{
        color:red;
    }
    input.green{
        color:green;
    }
    -->
</style>
</head>
<body onLoad='document.select_form.act_id.focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <form name='select_form' method='post' action='<?=$menu->out_self()?>' onSubmit='return act_id_chk(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='left' nowrap>
                        区分を選択して部門コードを入力し実行ボタンを押して下さい。<br>
                        <table class='winbox_field' width='100%' align='center' border='1' cellspacing='0' cellpadding='2'>
                            <tr align='center'>
                                <td class='winbox' nowrap <?php if($flag==1) echo "class='b_w'" ?>>
                                    <input type='radio' name='act_sel' value='add' id='add'
                                        <?php if($flag==1) echo 'checked' ?>
                                    >
                                    <label for='add'>追加</label>
                                </td>
                                <td class='winbox' nowrap <?php if($flag==2) echo "class='b_w'" ?>>
                                    <input type='radio' name='act_sel' value='chg' id='chg'
                                        <?php if($flag==2) echo'checked' ?>
                                    >
                                    <label for='chg'>変更</label>
                                </td>
                                <td class='winbox' nowrap <?php if($flag==3) echo "class='b_w'" ?>>
                                    <input type='radio' name='act_sel' value='del' id='del'
                                    <?php if($flag==3) echo 'checked' ?>
                                    >
                                    <label for='del'>削除</label>
                                </td>
                        </table>
                        <div align='center'>
                            部門コード<input type='text' name='act_id' size='7' maxlength='6' value='<?php echo $_POST['act_id'] ?>'>
                            <input type='submit' name='edit' value='実行' >
                        </div>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    <?php
        if ($flag == 1) {
            echo "<form method='post' action='act_table_mnt_new.php' onSubmit='return act_add_chk(this)'>\n";
            echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
            echo "  <th nowrap>コード</th><th nowrap>部 門 名</th><th nowrap>短縮名</th><th class='b_w'>登録</th>\n";
            echo "  <tr align='center'>\n";
            echo "      <td>" . $_POST['act_id'] . "</td>\n";
            echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
            echo "      <td><input type='text' name='act_name' size='23' maxlength='20'></td>\n";
            echo "      <td><input type='text' name='s_name' size='9' maxlength='8'></td>\n";
            echo "      <td><input type='submit' name='act_add' value='実行' >\n";
            echo "  </tr>\n";
            echo "</table>\n";
            echo "</form>\n";
        }
        else if($flag == 2) {
            echo "<form method='post' action='act_table_mnt_new.php' onSubmit='return act_chk_name(this)'>\n";
            echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
            echo "  <th nowrap>コード</th><th nowrap>部 門 名</th><th nowrap>短縮名</th><th class='b_w'>変更</th>\n";
            echo "  <tr align='center'>\n";
            echo "      <td>" . $_POST['act_id'] . "</td>\n";
            echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
            echo "      <td><input type='text' name='act_name' size='23' maxlength='20' value='" . trim($res[0]['act_name']) . "'></td>\n";
            echo "      <td><input type='text' name='s_name' size='9' maxlength='8' value='" . trim($res[0]['s_name']) . "'></td>\n";
            echo "      <td><input type='submit' name='act_chg' value='実行' >\n";
            echo "  </tr>\n";
            echo "</table>\n";
            echo "</form>\n";
        }
        else if($flag == 3) {
            echo "<table bgcolor='#d6d3ce' border='1' cellspacing='1' cellpadding='2'>\n";
            echo "  <th nowrap>コード</th><th nowrap>部 門 名</th><th nowrap>短縮名</th><th class='r_w'>削除</th>\n";
            echo "  <tr align='center'>\n";
            echo "      <td>" . $_POST['act_id'] . "</td>\n";
            echo "          <input type='hidden' name='act_id' value='" . $_POST['act_id'] . "'>\n";
            echo "      <td><input type='text' name='act_name' size='23' maxlength='20' value='" . $res[0]['act_name'] . "'></td>\n";
            echo "      <td><input type='text' name='s_name' size='9' maxlength='8' value='" . $res[0]['s_name'] . "'></td>\n";
            echo "      <td><input type='submit' name='act_del' value='実行' >\n";
            echo "  </tr>\n";
            echo "</table>\n";
            echo "</form>\n";
        }

        // view 一覧表示
        $res = array(
            ['act_id'=>1, 'act_name'=>"test", 's_name'=>"dummy", 'act_flg'=>'t',  'rate_flg'=>'1'],
            ['act_id'=>2, 'act_name'=>"test", 's_name'=>"dummy", 'act_flg'=>'f',  'rate_flg'=>'2'],
            ['act_id'=>3, 'act_name'=>"test", 's_name'=>"dummy", 'act_flg'=>'' ,  'rate_flg'=>'3'],
            ['act_id'=>4, 'act_name'=>"test", 's_name'=>"dummy", 'act_flg'=>'t',  'rate_flg'=>'1'],
            ['act_id'=>5, 'act_name'=>"test", 's_name'=>"dummy", 'act_flg'=>'f',  'rate_flg'=>'2'],
            ['act_id'=>6, 'act_name'=>"test", 's_name'=>"dummy", 'act_flg'=>'' ,  'rate_flg'=>'3'],
            ['act_id'=>7, 'act_name'=>"test", 's_name'=>"dummy", 'act_flg'=>'t',  'rate_flg'=>'1'],
            ['act_id'=>8, 'act_name'=>"test", 's_name'=>"dummy", 'act_flg'=>'f',  'rate_flg'=>'2'],
            ['act_id'=>9, 'act_name'=>"test", 's_name'=>"dummy", 'act_flg'=>'' ,  'rate_flg'=>'3'],
            ['act_id'=>10, 'act_name'=>"test", 's_name'=>"dummy", 'act_flg'=>'t', 'rate_flg'=>'1'],
        );


//          echo "<hr>\n";
        echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
        echo "  <form action='act_table_mnt_new.php' method='post'>\n";
        echo "  <caption>経理部門コード・配賦率一覧\n";
        echo "      <input type='submit' name='backward' value='前頁'>\n";
        echo "      <input type='submit' name='forward' value='次頁'>\n";
        echo "  </caption>\n";
        echo "  </form>\n";
        ////////////////////////////////////////////// 大分類の配賦率 category_item cate_allocation

        $res_cate = array();

            /***** フィールド名設定 *****/
        echo "  <th nowrap class='fmp-n'>No</th><th nowrap class='fmp-n'>コード</th><th nowrap class='fmp-n'>部 門 名</th><th nowrap class='fmp-n'>短縮名</th>\n";
        for($i=0;$i<2;$i++){

            echo "<th nowrap class='fmp-n'>test</th>\n";

        }
        echo "<th colspan='2' align='center' class='fmp-n'>直接/間接/販管費/その他</th>\n";
            /***** フィールド名 End *****/
        for($r=0;$r<10;$r++){
            print("<tr>\n");
            echo "  <form method='post' action='act_table_mnt_new.php'>\n";
            print(" <td align='center'><input type='submit' name='copy' value='" . ($r +  1) . "'></td>\n");
            echo "      <input type='hidden' name='act_sel' value='chg'>\n";
            echo "      <input type='hidden' name='act_id' value='" . $res[$r]['act_id'] . "'>\n";
            echo("<td nowrap align='left' class='flp'>" . $res[$r]['act_id'] . "</td>\n");
            echo("<td nowrap align='left' class='fmp'>" . $res[$r]['act_name'] . "</td>\n");
            echo("<td nowrap align='left' class='fmp'>" . $res[$r]['s_name'] . "</td>\n");
            for($i=0;$i<2;$i++){
                //////////////////////////// １項目クエリー ユニークな配賦率の取得
                echo("<td nowrap align='center'>---</td>\n");
            }
            echo "  </form>\n";
            echo "  <form method='post' action='act_table_mnt_new.php'>\n";

            if($res[$r]['act_flg'] == 't')
                echo "  <td><input type='submit' name='act_flg' value='直接部門' class='blue'></td>\n";

            else if($res[$r]['act_flg'] == 'f')
                echo "  <td><input type='submit' name='act_flg' value='間接部門' class='red'></td>\n";

            else
                echo "  <td><input type='submit' name='act_flg' value='販管部門' class='green'></td>\n";

            if($res[$r]['rate_flg'] == '1')
                echo "  <td align='center'><input type='submit' name='rate_flg' value='機械賃率対象' class='blue'></td>\n";

            else if($res[$r]['rate_flg'] == '2')
                echo "  <td align='center'><input type='submit' name='rate_flg' value='賃率管理配賦' class='red'></td>\n";

            else
                echo "  <td align='center'><input type='submit' name='rate_flg' value='機械賃率除外'></td>\n";

            echo "      <input type='hidden' name='act_id' value='" . $res[$r]['act_id'] . "'>\n";
            echo "  </form>\n";
            print("</tr>\n");
        }
        print("</table>\n");
        //////////////////////////////////////////////// 小分類の各配賦率 allocation_item act_allocation

        for($r=0;$r<3;$r++){ ////////////// PAGE 数 分回す

            $res_allo = array(
                ['allo_id'=>1, 'allo_item'=>"test"],
                ['allo_id'=>2, 'allo_item'=>"test"],
                ['allo_id'=>3, 'allo_item'=>"test"],
                ['allo_id'=>4, 'allo_item'=>"test"],
                ['allo_id'=>5, 'allo_item'=>"test"],
                ['allo_id'=>6, 'allo_item'=>"test"],
                ['allo_id'=>7, 'allo_item'=>"test"],
                ['allo_id'=>8, 'allo_item'=>"test"],
                ['allo_id'=>9, 'allo_item'=>"test"],
                ['allo_id'=>10, 'allo_item'=>"test"],
            );

            for($i=0;$i<2;$i++){

                $res_act = array();

                echo "<hr>\n";
                echo "<table bgcolor='#d6d3ce' align='center' cellspacing='1' cellpadding='3' border='1'>\n";
                echo "  <form action='act_table_mnt_new.php' method='post'>\n";

                echo "  <caption>" . $res_allo[$i]['allo_item'] . "\n";

                echo "      <input type='submit' name='backward' value='前頁'>\n";
                echo "      <input type='submit' name='forward' value='次頁'>\n";
                echo "  </caption>\n";
                echo "  </form>\n";
                echo "  <th nowrap class='fmp-n'>コード</th><th nowrap class='fmp-n'>部 門 名</th><th nowrap class='fmp-n'>短縮名</th>\n";

                for($j=0;$j<$rows_act;$j++){

                    $res_name = array();

                    echo "<th nowrap class='y_b'>" . $res_name[0]['s_name'] . "</th>\n";
                }

                echo "<tr>\n";

                echo("<td nowrap align='left' class='flp'>" . $res[$r]['act_id'] . "</td>\n");
                echo("<td nowrap align='left' class='fmp'>" . $res[$r]['act_name'] . "</td>\n");
                echo("<td nowrap align='left' class='fmp'>" . $res[$r]['s_name'] . "</td>\n");

                for($j=0;$j<$rows_act;$j++){
                    if($res_act[$j]['allo_rate'] == "")
                        echo("<td nowrap align='center'>---</td>\n");
                    else
                        echo("<td nowrap align='right' class='flp'>" . $res_act[$j]['allo_rate'] . "</td>\n");
                }

                echo "</tr>\n";
                echo "</table>\n";
            }
        }
    ?>
    </center>
</body>
</html>
