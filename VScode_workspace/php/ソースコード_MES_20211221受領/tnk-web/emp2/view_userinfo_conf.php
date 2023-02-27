<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 従業員新規登録 確認フォーム                  //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  view_userinfo_conf.php                               //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
// 2003/04/21 現在年齢を表示するように追加(確認)                            //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
//////////////////////////////////////////////////////////////////////////////
//    access_log("view_userinfo_conf.php");       // Script Name 手動設定
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
    $query="select uid from user_master where uid='" . $_POST["userid"] . "'";
    $res=array();
    if(!getResult($query,$res)){
        $spell=$_POST["spell_1"] . " " . $_POST["spell_2"];
        $mailaddr=makeMailAddress($spell);
        // $passwd=makePassword();
        $passwd = $_POST["userid"];
        $mailaddr_pos=strpos(trim($mailaddr),'@');
        $acount=trim(substr($mailaddr,0,$mailaddr_pos));
?>
        <form method="post" action="add_userinfo.php">
        <table width="100%">
            <tr><td colspan=2 bgcolor="#003e7c" align="center">
                <font color="#ffffff">新規登録内容の確認</font></td></tr>

            <tr><td width="15%">権限</td>
                <td>
<?php
        $query="select authority_name from authority_master where aid=" . $_POST["authority"];
        $res=array();
        if(getResult($query,$res))
            echo(trim($res[0]['authority_name']));
        echo("<input type='hidden' name='authority' value=" . $_POST["authority"] . ">\n");
?>
                </td>
            </tr>
            <tr><td width="15%">社員No.</td>
                <td>
<?php 
        echo($_POST["userid"]); 
        echo("<input type='hidden' name='userid' value='" . $_POST["userid"] . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%">初期パスワード</td>
                <td><font color="#ff7e00">
<?php
        echo($passwd);
        echo("<input type='hidden' name='passwd' value='" . $passwd . "'>\n");
?>
                </font></td>
            </tr>
            <tr><td width="15%">メールアドレス</td>
                <td><font color="#ff7e00">
<?php
        echo($mailaddr);
        echo("<input type='hidden' name='mailaddr' value='" . $mailaddr . "'>\n");
        echo("<input type='hidden' name='acount' value='" . $acount . "'>\n");
?>
                </font></td>
            </tr>

            <tr><td width="15%">氏名</td>
                <td>
<?php
        $name = $_POST["name_1"] . " " . $_POST["name_2"];
        echo($name);
        echo("<input type='hidden' name='name' value='" . $name . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%">フリガナ</td>
                <td>
<?php
        $kana=$_POST["kana_1"] . " " . $_POST["kana_2"];
        echo($kana);
        echo("<input type='hidden' name='kana' value='" . $kana . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%">スペル</td>
                <td>
<?php
        echo($spell);
        echo("<input type='hidden' name='spell' value='" . $spell . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%">所属</td>
                <td>
<?php
        $query = "select section_name from section_master where sid=" . $_POST["section"];
        $res=array();
        if(getResult($query,$res))
            $section_name = trim($res[0]['section_name']);
        echo($section_name);
        echo("<input type='hidden' name='section' value=" . $_POST["section"] . ">\n");
        echo("<input type='hidden' name='section_name' value='" . $section_name . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%">職位</td>
                <td>
<?php
        $query="select position_name from position_master where pid=" . $_POST["position"];
        $res=array();
        if(getResult($query,$res))
            $position_name=trim($res[0]['position_name']);
            echo($position_name);
            echo("<input type='hidden' name='position' value=" . $_POST["position"] . ">\n");
            
?>
                </td>
            </tr>
            <tr><td width="15%">等級</td>
                <td>
<?php
        echo($_POST["class"]);
        echo("<input type='hidden' name='class' value='" . $_POST["class"] . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%">郵便番号</td>
                <td>
<?php
        $zipcode = $_POST["zipcode_1"] . $_POST["zipcode_2"];
        echo($_POST["zipcode_1"] . "-" . $_POST["zipcode_2"]);
        echo("<input type='hidden' name='zipcode' value='" . $zipcode . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%">住所</td>
                <td>
<!-- 変更個所 2001/11/29 ここから -->
<?php
        echo($_POST["address"]);
        echo("<input type='hidden' name='address' value='" . $_POST["address"] . "'>\n");
?>
<!-- ここまで-->
                </td>
            </tr>

            <tr><td width="15%">電話番号</td>
                <td>
<?php
        echo($_POST["tel"]);
        echo("<input type='hidden' name='tel' value='" . $_POST["tel"] . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%">生年月日</td>
                <td>
<?php
        $birthday = $_POST["birthday_1"] . "-" . $_POST["birthday_2"] . "-" . $_POST["birthday_3"];
        echo($birthday);
        echo("<input type='hidden' name='birthday' value='" . $birthday . "'>\n");
/********* 2003/04/21 現在年齢を計算して表示する｡機能追加 *********/
        $birth_f = substr($birthday,0,10);
        $res_age = array();
        $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
        if (($rows_age=getResult($query_age,$res_age)) > 0)
            printf("　現在年齢<font color='red'><b>　%s歳　%sヶ月　%s日</b></font>", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
/********* 2003/04/21 END *********/
    ?>
                </td>
            </tr>
            <tr><td width="15%">入社年月日</td>
                <td>
<?php
        $entrydate = $_POST["entrydate_1"] . "-" . $_POST["entrydate_2"] . "-" . $_POST["entrydate_3"];
        echo($entrydate);
        echo("<input type='hidden' name='entrydate' value='" . $entrydate . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%">健康保険</td>
                <td><br></td>
            </tr>
            <tr><td width="15%" align="right">加入日</td>
                <td>
<?php   if ($_POST["helthins_date_1"] == ""){
            $helthins_date = "";
        }else{
            $helthins_date = $_POST["helthins_date_1"] . "-" . $_POST["helthins_date_2"] . "-" . $_POST["helthins_date_3"];
        }
        echo($helthins_date);
        echo("<input type='hidden' name='helthins_date' value='" . $helthins_date . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%" align="right">記号/番号</td>
                <td>
<?php
        if ($_POST["helthins_no_1"] == ""){
            $helthins_no = "";
        }else{
            $helthins_no = $_POST["helthins_no_1"] . "/" . $_POST["helthins_no_2"];
        }
        echo($helthins_no);
        echo("<input type='hidden' name='helthins_no' value='" . $helthins_no . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%">厚生年金</td>
                <td><br></td>
            </tr>
            <tr><td width="15%" align="right">加入日</td>
                <td>
<?php
        if ($_POST["welperins_date_1"] == ""){
            $welperins_date = "";
        }else{
            $welperins_date = $_POST["welperins_date_1"] . "-" . $_POST["welperins_date_2"] . "-" . $_POST["welperins_date_3"];
        }
        echo($welperins_date);
        echo("<input type='hidden' name='welperins_date' value='" . $welperins_date . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%" align="right">記号/番号</td>
                <td>
<?php
        if ($_POST["welperins_no_1"] == ""){
            $welperins_no = "";
        }else{
            $welperins_no = $_POST["welperins_no_1"] . "/" . $_POST["welperins_no_2"];
        }
        echo($welperins_no);
        echo("<input type='hidden' name='welperins_no' value='" . $welperins_no . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%">雇用保険</td>
                <td><br></td>
            </tr>
            <tr><td width="15%" align="right">加入日</td>
                <td>
<?php
        if ($_POST["unemploy_date_1"] == ""){
            $unemploy_date = "";
        }else{
            $unemploy_date = $_POST["unemploy_date_1"] . "-" . $_POST["unemploy_date_2"] . "-" . $_POST["unemploy_date_3"];
        }
        echo($unemploy_date);
        echo("<input type='hidden' name='unemploy_date' value='" . $unemploy_date . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%" align="right">記号/番号</td>
                <td>
<?php
        if ($_POST["unemploy_no_1"] == ""){
            $unemploy_no = "";
        }else{
            $unemploy_no = $_POST["unemploy_no_1"] . "/" . $_POST["unemploy_no_2"];
        }
        echo($unemploy_no);
        echo("<input type='hidden' name='unemploy_no' value='" . $unemploy_no . "'>\n");
?>
                </td>
            </tr>
            <tr><td width="15%">特記事項</td>
                <td>
<!-- 変更個所 2001/11/29 ここから -->
<?php
        echo($_POST["info"]);
        echo("<input type='hidden' name='info' value='" . $_POST["info"] . "'>\n");
?>
<!-- ここまで -->
                </td>
            </tr>
            <tr><td width="15%">画像データ</td>
                <td>
<?php
        $img_file = StripSlashes($_POST["img_file"]);
        echo($img_file);
?>
                </td>
            </tr>
            <tr><td width="15%"><br></td>
                <td>
<?php
//      $photo = StripSlashes($_FILES['photo']['tmp_name']);
//      echo ("photo=" . $_FILES['photo']['tmp_name']);     //debug 用
        if($_FILES['photo']['tmp_name'] != ""){
            $photo = StripSlashes($_FILES['photo']['tmp_name']);
            $file = INS_IMG . $_SESSION["User_ID"];
            copy($_FILES['photo']['tmp_name'], $file);
            echo("<img src='$file?" . uniqid(abcdef) . "' align='left' alt='画像イメージ' width=256 height=384 border=0>\n");
            echo("<input type='hidden' name='photo' value=1>\n");       // add_userinfo.phpで処理
        }else{
            if($_POST["photoid"]){      // 更新ボタンを押したときの対応 ????
                $file=IND . $_POST["userid"] . ".gif";
                getObject($_POST["photoid"],$file);
                echo("<img src='$file?new' width=256 height=384 border=0></td>\n");
            }else   if(file_exists(INS_IMG . $_SESSION["User_ID"])){    // 訂正時の対応
                $file = INS_IMG . $_SESSION["User_ID"];
                echo("<img src='$file?" . uniqid(abcdef) . "' align='left' alt='画像イメージ' width=256 height=384 border=0>\n");
            }
        }
        echo("<input type='hidden' name='photoid' value=" . $_POST["photoid"] . ">\n");
        
?>
                </td>
            </tr>

            <tr><td colspan=2><hr></td>
            </tr>
            <tr><td colspan=2>上記の内容でよろしければ [登録実行] を押下してください。
                <br>必要ならば [戻る] にて入力を再度行ってください。
                    <br><br><input type="submit" value="登録実行">
    </form>
    <form method="post" action="emp_menu.php?func=<?php echo(FUNC_NEWUSER) ?>">
        <input type='hidden' name='func' value='<?php echo(FUNC_NEWUSER) ?>'>
        <input type='hidden' name='authority' value='<?php echo($_POST["authority"]) ?>'>
        <input type='hidden' name='userid' value='<?php echo($_POST["userid"]) ?>'>
        <input type='hidden' name='name_1' value='<?php echo($_POST["name_1"]) ?>'>
        <input type='hidden' name='name_2' value='<?php echo($_POST["name_2"]) ?>'>
        <input type='hidden' name='kana_1' value='<?php echo($_POST["kana_1"]) ?>'>
        <input type='hidden' name='kana_2' value='<?php echo($_POST["kana_2"]) ?>'>
        <input type='hidden' name='spell_1' value='<?php echo($_POST["spell_1"]) ?>'>
        <input type='hidden' name='spell_2' value='<?php echo($_POST["spell_2"]) ?>'>
        <input type='hidden' name='section' value='<?php echo($_POST["section"]) ?>'>
        <input type='hidden' name='position' value='<?php echo($_POST["position"]) ?>'>
        <input type='hidden' name='class' value='<?php echo($_POST["class"]) ?>'>
        <input type='hidden' name='zipcode_1' value='<?php echo($_POST["zipcode_1"]) ?>'>
        <input type='hidden' name='zipcode_2' value='<?php echo($_POST["zipcode_2"]) ?>'>
        <input type='hidden' name='address' value='<?php echo($_POST["address"]) ?>'>
        <input type='hidden' name='tel' value='<?php echo($_POST["tel"]) ?>'>
        <input type='hidden' name='birthday_1' value='<?php echo($_POST["birthday_1"]) ?>'>
        <input type='hidden' name='birthday_2' value='<?php echo($_POST["birthday_2"]) ?>'>
        <input type='hidden' name='birthday_3' value='<?php echo($_POST["birthday_3"]) ?>'>
        <input type='hidden' name='entrydate_1' value='<?php echo($_POST["entrydate_1"]) ?>'>
        <input type='hidden' name='entrydate_2' value='<?php echo($_POST["entrydate_2"]) ?>'>
        <input type='hidden' name='entrydate_3' value='<?php echo($_POST["entrydate_3"]) ?>'>
        <input type='hidden' name='helthins_date_1' value='<?php echo($_POST["helthins_date_1"]) ?>'>
        <input type='hidden' name='helthins_date_2' value='<?php echo($_POST["helthins_date_2"]) ?>'>
        <input type='hidden' name='helthins_date_3' value='<?php echo($_POST["helthins_date_3"]) ?>'>
        <input type='hidden' name='helthins_no_1' value='<?php echo($_POST["helthins_no_1"]) ?>'>
        <input type='hidden' name='helthins_no_2' value='<?php echo($_POST["helthins_no_2"]) ?>'>
        <input type='hidden' name='welperins_date_1' value='<?php echo($_POST["welperins_date_1"]) ?>'>
        <input type='hidden' name='welperins_date_2' value='<?php echo($_POST["welperins_date_2"]) ?>'>
        <input type='hidden' name='welperins_date_3' value='<?php echo($_POST["welperins_date_3"]) ?>'>
        <input type='hidden' name='welperins_no_1' value='<?php echo($_POST["welperins_no_1"]) ?>'>
        <input type='hidden' name='welperins_no_2' value='<?php echo($_POST["welperins_no_2"]) ?>'>
        <input type='hidden' name='unemploy_date_1' value='<?php echo($_POST["unemploy_date_1"]) ?>'>
        <input type='hidden' name='unemploy_date_2' value='<?php echo($_POST["unemploy_date_2"]) ?>'>
        <input type='hidden' name='unemploy_date_3' value='<?php echo($_POST["unemploy_date_3"]) ?>'>
        <input type='hidden' name='unemploy_no_1' value='<?php echo($_POST["unemploy_no_1"]) ?>'>
        <input type='hidden' name='unemploy_no_2' value='<?php echo($_POST["unemploy_no_2"]) ?>'>
        <input type='hidden' name='info' value='<?php echo($_POST["info"]) ?>'>
        <input type='hidden' name='img_file' value='<?php echo($_POST["img_file"]) ?>'>
                        <p><input type="submit" value="戻る" ></p></td>
    </form>
            </tr>
        </table>
<?php
    }else{
?>
        <table width="100%">
            <tr><td colspan=2 bgcolor="#003e7c" align="center">
                <font color="#ffffff">新規登録内容の確認</font></td>
            </tr>
        </table>
    <form method="post" action="emp_menu.php?func=<?php echo(FUNC_NEWUSER) ?>">
        <input type='hidden' name='func' value='<?php echo(FUNC_NEWUSER) ?>'>
        <input type='hidden' name='authority' value='<?php echo($_POST["authority"]) ?>'>
        <input type='hidden' name='userid' value='<?php echo($_POST["userid"]) ?>'>
        <input type='hidden' name='name_1' value='<?php echo($_POST["name_1"]) ?>'>
        <input type='hidden' name='name_2' value='<?php echo($_POST["name_2"]) ?>'>
        <input type='hidden' name='kana_1' value='<?php echo($_POST["kana_1"]) ?>'>
        <input type='hidden' name='kana_2' value='<?php echo($_POST["kana_2"]) ?>'>
        <input type='hidden' name='spell_1' value='<?php echo($_POST["spell_1"]) ?>'>
        <input type='hidden' name='spell_2' value='<?php echo($_POST["spell_2"]) ?>'>
        <input type='hidden' name='section' value='<?php echo($_POST["section"]) ?>'>
        <input type='hidden' name='position' value='<?php echo($_POST["position"]) ?>'>
        <input type='hidden' name='class' value='<?php echo($_POST["class"]) ?>'>
        <input type='hidden' name='zipcode_1' value='<?php echo($_POST["zipcode_1"]) ?>'>
        <input type='hidden' name='zipcode_2' value='<?php echo($_POST["zipcode_2"]) ?>'>
        <input type='hidden' name='address' value='<?php echo($_POST["address"]) ?>'>
        <input type='hidden' name='tel' value='<?php echo($_POST["tel"]) ?>'>
        <input type='hidden' name='birthday_1' value='<?php echo($_POST["birthday_1"]) ?>'>
        <input type='hidden' name='birthday_2' value='<?php echo($_POST["birthday_2"]) ?>'>
        <input type='hidden' name='birthday_3' value='<?php echo($_POST["birthday_3"]) ?>'>
        <input type='hidden' name='entrydate_1' value='<?php echo($_POST["entrydate_1"]) ?>'>
        <input type='hidden' name='entrydate_2' value='<?php echo($_POST["entrydate_2"]) ?>'>
        <input type='hidden' name='entrydate_3' value='<?php echo($_POST["entrydate_3"]) ?>'>
        <input type='hidden' name='helthins_date_1' value='<?php echo($_POST["helthins_date_1"]) ?>'>
        <input type='hidden' name='helthins_date_2' value='<?php echo($_POST["helthins_date_2"]) ?>'>
        <input type='hidden' name='helthins_date_3' value='<?php echo($_POST["helthins_date_3"]) ?>'>
        <input type='hidden' name='helthins_no_1' value='<?php echo($_POST["helthins_no_1"]) ?>'>
        <input type='hidden' name='helthins_no_2' value='<?php echo($_POST["helthins_no_2"]) ?>'>
        <input type='hidden' name='welperins_date_1' value='<?php echo($_POST["welperins_date_1"]) ?>'>
        <input type='hidden' name='welperins_date_2' value='<?php echo($_POST["welperins_date_2"]) ?>'>
        <input type='hidden' name='welperins_date_3' value='<?php echo($_POST["welperins_date_3"]) ?>'>
        <input type='hidden' name='welperins_no_1' value='<?php echo($_POST["welperins_no_1"]) ?>'>
        <input type='hidden' name='welperins_no_2' value='<?php echo($_POST["welperins_no_2"]) ?>'>
        <input type='hidden' name='unemploy_date_1' value='<?php echo($_POST["unemploy_date_1"]) ?>'>
        <input type='hidden' name='unemploy_date_2' value='<?php echo($_POST["unemploy_date_2"]) ?>'>
        <input type='hidden' name='unemploy_date_3' value='<?php echo($_POST["unemploy_date_3"]) ?>'>
        <input type='hidden' name='unemploy_no_1' value='<?php echo($_POST["unemploy_no_1"]) ?>'>
        <input type='hidden' name='unemploy_no_2' value='<?php echo($_POST["unemploy_no_2"]) ?>'>
        <input type='hidden' name='info' value='<?php echo($_POST["info"]) ?>'>
        <input type='hidden' name='img_file' value='<?php echo($_POST["img_file"]) ?>'>
                        <p><input type="submit" value="戻る" ></p></td>
    </form>
<script language="javascript">
    alert("社員Noが他のユーザーと重複しています。管理者にお問い合わせください。");
</script>
<?php
    }
?>
