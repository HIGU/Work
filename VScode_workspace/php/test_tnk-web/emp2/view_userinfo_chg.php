<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file ユーザー情報の変更フォーム                   //
//                             Adminの パスワードの変更フォームと兼用       //
// Copyright (C) 2001-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  view_userinfo_chg.php                                //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
// 2003/01/31 現在年齢を計算して表示する機能を追加                          //
// 2003/06/09 Administrator権限でないとパスワードが***になるように変更      //
// 2004/03/31 職位マスター検索時の position_master where pflg=1<--追加      //
// 2004/04/16 $_SESSION['User_ID'] -> $_SESSION['Auth'] ミス入力を修正      //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2007/08/29 戻るボタンにページ制御のoffsetを追加(セッション使用で階層対応)//
// 2010/03/11 暫定的に大渕さん（970268）が登録できるように変更         大谷 //
// 2015/11/17 情報の変更などから戻った際に、検索が消える不具合を訂正の為    //
//            色々テスト（変更なし）                                   大谷 //
// 2019/01/31 暫定的に平石さん（300551）が登録できるように変更         大谷 //
//////////////////////////////////////////////////////////////////////////////
//    access_log("view_userinfo_chg.php");        // Script Name 手動設定
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
    $query = "select ud.name,ud.kana,sm.section_name,pm.position_name from user_detailes ud,section_master sm,position_master pm" . 
        " where ud.uid='" . $_POST['userid'] . "' and ud.sid=sm.sid and ud.pid=pm.pid";
    $res=array();
    getResult($query,$res);
    $name = $res[0]['name'];
    $kana = $res[0]['kana'];
    $section_name=$res[0]['section_name'];
    ///// 呼出し元のページオフセットを取得 2007/08/29
    if (isset($_REQUEST['offset'])) {
        $offset = $_REQUEST['offset'];
    } elseif (isset($_SESSION['view_userinfo_offset'])) {
        $offset = $_SESSION['view_userinfo_offset'];
    } else {
        $offset = 0;
    }
    $_SESSION['view_userinfo_offset'] = $offset;
    if (isset($_POST['inf']) && ($_SESSION['Auth'] >= AUTH_LEBEL2 || $_SESSION['User_ID'] == '970268' || $_SESSION['User_ID'] == '300551')) {
        $query="select * from user_detailes where uid='" . $_POST['userid'] . "'";
        $user_res=array();
        getResult($query,$user_res);
        
        $query="select * from user_master where uid='" . $_POST['userid'] . "'";
        $auth_res=array();
        getResult($query,$auth_res);
        $mailaddr = trim($auth_res[0]['mailaddr']);
        $mailaddr_pos = strpos(trim($mailaddr),'@');
        $acount = substr($mailaddr,0,$mailaddr_pos);
?>
<table width="100%">
    <tr><td colspan=2 bgcolor="#ff6600" align="center">
        <font color="#ffffff">ユーザーの情報修正</font></td>
    </tr>

    <tr>
        <td colspan=2>
        <table width="100%">
            <tr><td>
            <form method="post" action="emp_menu.php?func=<?php echo(FUNC_ADMINUSERINFO); ?>">
            <input type="submit" name="sect" value="所属について">
            <input type="submit" name="recv" value="教育について">
            <input type="submit" name="capa" value="資格について">
            <input type="submit" name="retire" value="退職について">
            <input type="hidden" name="userid" value=<?php echo($_POST['userid']); ?>>
            <input type="hidden" name="name" value="<?php echo($_POST['name']); ?>">
            <input type="hidden" name="section_name" value="<?php echo($_POST['section_name']); ?>">
            </form>
            </td>
            <td align="right">
            <form method="post" action="del_userinfo.php" onSubmit="return confirm('このユーザーの情報すべてを削除します。よろしいですか?')">

            <input type="submit" name="sect" value="情報の抹消">
            <input type="hidden" name="userid" value="<?php echo($_POST['userid']); ?>">
            <input type="hidden" name="photoid" value=<?php echo($user_res[0]['photo']); ?>>
            <input type="hidden" name="acount" value="<?php echo($_POST['acount']); ?>">
            </form>
            </td></tr>
        </table>
<?php
    if (0 == $_POST['retireflg']) {
        echo "<form method='post' action='emp_menu.php?func=" . FUNC_LOOKUP . "'>\n";
    } else {
        echo "<form method='post' action='emp_menu.php?func=" . FUNC_RETIREINFO . "'>\n";
    }
?>
            <input type='hidden' name='offset' value='<?php echo $offset ?>'>
            <p><input type='submit' value='戻る'></p>
        </form>
        <hr>
        </td>
    </tr>

    <form enctype="multipart/form-data" method="post" action="emp_menu.php?func=<?php echo(FUNC_CONFUSERINFO); ?>" onSubmit="return chkUserInfo(this)">
    <tr><td width="15%">権限</td>
        <td><select name="authority">
<?php
    $query="select * from authority_master order by aid asc";
    $res=array();
    if($rows=getResult($query,$res)){
        for($i=0;$i<$rows;$i++){
            if($auth_res[0]['aid']==$res[$i]['aid'])
                echo("<option selected value=" . $res[$i]['aid'] . ">" . $res[$i]['authority_name'] . "\n");
            else
                echo("<option value=" . $res[$i]['aid'] . ">" . $res[$i]['authority_name'] . "\n");
        }
    }
?>
        </select>
        </td>
    </tr>

    <tr><td width="15%">社員No.*</td>
        <td><?php echo(trim($_POST['userid'])); ?>

            <input type="hidden" name="userid" value=<?php echo(trim($_POST['userid'])); ?>>
            <input type="hidden" name="oldid" value=<?php echo(trim($_POST['userid'])); ?>>
        </td>
    </tr>
    <tr><td width="15%">パスワード</td>
        <td><?php 
                if ($_SESSION['Auth'] > AUTH_LEVEL3) {
                    echo trim($auth_res[0]['passwd']);
                } else {
                    echo str_repeat('*', strlen(trim($auth_res[0]['passwd'])));
                }
            ?>
            <input type="hidden" name="passwd" value=<?php echo(trim($auth_res[0]['passwd'])); ?>>
        </td>
    </tr>
    <tr><td width="15%">メールアドレス</td>
        <td><?php echo(trim($auth_res[0]['mailaddr'])); ?><input type="hidden" name="mailaddr" value=<?php echo(trim($auth_res[0]['mailaddr'])); ?>></td>
    </tr>
    <tr>
        <td width="15%">氏名*</td>
    </tr>
<?php
    $name_len=strlen(trim($user_res[0]['name']));
    $name_pos=strpos(trim($user_res[0]['name']),' ');
    $name_f=trim(substr($user_res[0]['name'],0,$name_pos));
    $name_r=trim(substr($user_res[0]['name'],$name_pos+1,$name_len));
    echo("  <tr>\n      <td align='right'>姓</td>\n     <td><input type='text' name='name_1' size=24 maxlength=8 value='$name_f'>名<input type='text' name='name_2' size=24 maxlength=8 value='$name_r'></td>\n </tr>\n");
?>
    <tr>
        <td width="15%">フリガナ*</td>
    </tr>
<?php
    $kana_len=strlen(trim($user_res[0]['kana']));
    $kana_pos=strpos(trim($user_res[0]['kana']),' ');
    $kana_f=trim(substr($user_res[0]['kana'],0,$kana_pos));
    $kana_r=trim(substr($user_res[0]['kana'],$kana_pos+1,$kana_len));
    echo("  <tr>\n      <td align='right'>姓</td>\n     <td><input type='text' name='kana_1' size=24 maxlength=16 value='$kana_f'>名<input type='text' name='kana_2' size=24 maxlength=16 value='$kana_r'></td>\n   </tr>\n");
?>
    <tr>
        <td width="15%">スペル*</td>
    </tr>
<?php
    $spell_len=strlen(trim($user_res[0]['spell']));
    $spell_pos=strpos(trim($user_res[0]['spell']),' ');
    $spell_f=trim(substr($user_res[0]['spell'],0,$spell_pos));
    $spell_r=trim(substr($user_res[0]['spell'],$spell_pos+1,$spell_len));
    echo("  <tr>\n      <td align='right'>名</td>\n <td><input type='text' name='spell_1' size=24 maxlength=16 value='$spell_f'>姓<input type='text' name='spell_2' size=24 maxlength=16 value='$spell_r'></td>\n   </tr>\n");
?>
    </tr>

    <tr>
        <td width="15%">所属</td>
        <td>
<?php
    $query="select section_name from section_master where sid=". $user_res[0]['sid'];
    $res=array();
    if(getResult($query,$res))
        echo($res[0]['section_name'] . "\n");
?>
        <input type="hidden" name="section" value=<?php echo($user_res[0]['sid']); ?>
        </td>
    </tr>

    <tr><td width="15%">職位</td>
        <td><select name="position">
<?php
    $query="select * from position_master where pflg=1 order by pid asc";
    $res=array();
    if($rows=getResult($query,$res)){
        for($i=0;$i<$rows;$i++)
            if($user_res[0]['pid']==$res[$i]['pid'])
                echo("<option selected value=" . $res[$i]['pid'] . ">" . $res[$i]['position_name'] . "\n");
            else
                echo("<option value=" . $res[$i]['pid'] . ">" . $res[$i]['position_name'] . "\n");
    }
?>
        </select>
        </td>
    </tr>

    <tr><td width="15%">等級</td>
        <td><input type="text" name="class" size=12 maxlength=8 value="<?php echo(trim($user_res[0]["class"])); ?>"></td>
    </tr>
    <tr><td width="15%">郵便番号*</td>
        <td><input type="text" name="zipcode_1" size=3 maxlength=3 value="<?php echo(trim(substr($user_res[0]['zipcode'],0,3))); ?>">
            <font size=+1> - </font><input type="text" name="zipcode_2" size=4 maxlength=4 value="<?php echo(trim(substr($user_res[0]['zipcode'],3,4))); ?>"></td>
    </tr>
    <tr><td width="15%">住所*</td>
<!-- 変更個所 2001/11/29 ここから -->
        <td><textarea rows=2 cols=64 name="address" wrap="virtual"><?php echo(trim($user_res[0]['address'])); ?></textarea></td>
<!-- ここまで -->
    </tr>
    <tr><td width="15%">電話番号*</td>
        <td><input type="text" name="tel" size=16 maxlength=13 value="<?php echo(trim($user_res[0]['tel'])); ?>"></font></td>
    </tr>
    <tr><td width="15%">生年月日*</td>
        <td><input type="text" name="birthday_1" size=4 maxlength=4 value="<?php echo(trim(substr($user_res[0]['birthday'],0,4))); ?>"><font size=+1> - </font><input type="text" name="birthday_2" size=2 maxlength=2 value="<?php echo(trim(substr($user_res[0]['birthday'],5,2))); ?>"><font size=+1> - </font><input type="text" name="birthday_3" size=2 maxlength=2 value="<?php echo(trim(substr($user_res[0]['birthday'],8,2))); ?>">
        <?php       /********* 2003/01/31 現在年齢を計算して表示する｡機能追加 *********/
            $birth_f = substr($user_res[0]['birthday'],0,10);
            $res_age = array();
            $query_age = sprintf("select extract(years from age('%s'::TIMESTAMP)) as years, extract(mons from age('%s'::TIMESTAMP)) as mons, extract(days from age('%s'::TIMESTAMP)) as days",$birth_f,$birth_f,$birth_f);
            if (($rows_age=getResult($query_age,$res_age)) > 0)
                printf("　現在年齢<font color='red'><b>　%s歳　%sヶ月　%s日</b></font>", $res_age[0]['years'], $res_age[0]['mons'], $res_age[0]['days']);
                    /********* 2003/01/31 END *********/
        ?></td>
    </tr>
    <tr><td width="15%" nowrap>入社年月日*</td>
        <td><input type="text" name="entrydate_1" size=4 maxlength=4 value="<?php echo(trim(substr($user_res[0]['enterdate'],0,4))); ?>"><font size=+1> - </font><input type="text" name="entrydate_2" size=2 maxlength=2 value="<?php echo(trim(substr($user_res[0]['enterdate'],5,2))); ?>"><font size=+1> - </font><input type="text" name="entrydate_3" size=2 maxlength=2 value="<?php echo(trim(substr($user_res[0]['enterdate'],8,2))); ?>"></td>
    </tr>
    <tr><td width="15%">健康保険</td>
        <td><br></td>
    </tr>
    <tr><td width="15%" align="right">加入日</td>
        <td><input type="text" name="helthins_date_1" size=4 maxlength=4 value="<?php echo(trim(substr($user_res[0]['helthins_date'],0,4))); ?>"><font size=+1> - </font><input type="text" name="helthins_date_2" size=2 maxlength=2 value="<?php echo(trim(substr($user_res[0]['helthins_date'],5,2))); ?>"><font size=+1> - </font><input type="text" name="helthins_date_3" size=2 maxlength=2 value="<?php echo(trim(substr($user_res[0]['helthins_date'],8,2))); ?>"></td>
    </tr>
    <tr><td width="15%" align="right">記号/番号</td>
        <td>
<?php
    $helth_len=strlen(trim($user_res[0]['helthins_no']));
    $helth_pos=strpos(trim($user_res[0]['helthins_no']),'/');
    $helth_f=trim(substr($user_res[0]['helthins_no'],0,$helth_pos));
    $helth_r=trim(substr($user_res[0]['helthins_no'],$helth_pos+1,$helth_len));
    echo("<input type='text' name='helthins_no_1' size=6 maxlength=4 value=$helth_f><font size=+1> / </font><input type='text' name='helthins_no_2' size=5 maxlength=3 value='$helth_r'>");
?>
</td>
    </tr>
    <tr><td width="15%">厚生年金</td>
        <td><br></td>
    </tr>
    <tr><td width="15%" align="right">加入日</td>
        <td><input type="text" name="welperins_date_1" size=4 maxlength=4 value="<?php echo(trim(substr($user_res[0]['welperins_date'],0,4))); ?>"><font size=+1> - </font><input type="text" name="welperins_date_2" size=2 maxlength=2 value="<?php echo(trim(substr($user_res[0]['welperins_date'],5,2))); ?>"><font size=+1> - </font><input type="text" name="welperins_date_3" size=2 maxlength=2 value="<?php echo(trim(substr($user_res[0]['welperins_date'],8,2))); ?>"></td>
    </tr>
    <tr><td width="15%" align="right">記号/番号</td>
        <td>
<?php
    $welperins_len=strlen(trim($user_res[0]['welperins_no']));
    $welperins_pos=strpos(trim($user_res[0]['welperins_no']),'/');
    $welperins_f=trim(substr($user_res[0]['welperins_no'],0,$welperins_pos));
    $welperins_r=trim(substr($user_res[0]['welperins_no'],$welperins_pos+1,$welperins_len));
    echo("<input type='text' name='welperins_no_1' size=6 maxlength=4 value=$welperins_f><font size=+1> / </font><input type='text' name='welperins_no_2' size=8 maxlength=6 value='$welperins_r'>");
?>
        </td>
    </tr>
    <tr><td width="15%">雇用保険</td>
        <td><br></td>
    </tr>
    <tr><td width="15%" align="right">加入日</td>
        <td><input type="text" name="unemploy_date_1" size=4 maxlength=4 value="<?php echo(trim(substr($user_res[0]['unemploy_date'],0,4))); ?>"><font size=+1> - </font><input type="text" name="unemploy_date_2" size=2 maxlength=2 value="<?php echo(trim(substr($user_res[0]['unemploy_date'],5,2))); ?>"><font size=+1> - </font><input type="text" name="unemploy_date_3" size=2 maxlength=2 value="<?php echo(trim(substr($user_res[0]['unemploy_date'],8,2))); ?>"></td>
    </tr>
    <tr><td width="15%" align="right">記号/番号</td>
        <td>
<?php
    $unemploy_len=strlen(trim($user_res[0]['unemploy_no']));
    $unemploy_pos=strpos(trim($user_res[0]['unemploy_no']),'/');
    $unemploy_f=trim(substr($user_res[0]['unemploy_no'],0,$unemploy_pos));
    $unemploy_r=trim(substr($user_res[0]['unemploy_no'],$unemploy_pos+1,$unemploy_len));
    echo("<input type='text' name='unemploy_no_1' size=6 maxlength=4 value=$unemploy_f><font size=+1> / </font><input type='text' name='unemploy_no_2' size=10 maxlength=8 value='$unemploy_r'>");
?>
        </td>
    <tr><td width="15%">特記事項</td>
<!-- 変更個所 2001/11/29 ここから -->
        <td><textarea rows=2 cols=64 name="info" wrap="virtual"><?php echo(trim($user_res[0][info])); ?></textarea></td>
<!-- ここまで -->   
    </tr>
    <tr><td width="15%">画像データ</td>
        <td><input type="hidden" name="MAX_FILE_SIZE" value=100000>
            <input type="file" name="photo" size=80 maxlength=256>
            <input type="hidden" name="img_file" value="">
            <input type="hidden" name="photoid" value=<?php echo($user_res[0]['photo']); ?>></td></tr>
            <tr><td colspan=2><hr></td>
    </tr>
    <tr><td colspan=2>上記の内容で登録を行います [登録確認] を押下してください。
                <br><br><input type="submit" name="chg" value="登録確認">
    </form>
<?php
    if (0 == $_POST['retireflg']) {
        echo "    <form method='post' action='emp_menu.php?func=" . FUNC_LOOKUP . "'>\n";
    } else {
        echo "    <form method='post' action='emp_menu.php?func=" . FUNC_RETIREINFO . "'>\n";
    }
?>
            <input type='hidden' name='offset' value='<?php echo $offset ?>'>
            <p><input type='submit' value='戻る'></p>
    </form>
    </td>
    </tr>
    <tr><td colspan=2><hr></td>
    </tr>
    <tr><td bgcolor="#ffff00" align="center">
        <p><font color="#000000">注意事項</font></p>
        </td><td><br></td>
    </tr>
    <tr><td colspan=2>
        <ol>
        <li>ユーザーに対する所属の変更、教育の履歴、資格の取得、退職にはその対象となる
            <br>ボタンを押下してください。
        <li>入力項目に&quot;*&quot;が付いている項目は必須です。正しい入力を行ってください。
        <li>数値、記号は半角にて入力してください。
            <br>漢字、平仮名、カタカナを入力する場合のみ全角としてください。
        <li>記入例
            <ul>
            <li>氏名:日東 太郎
            <li>フリガナ:ニットウ タロウ
            <li>スペル:taro nitto
            <li>電話番号:028-682-8851
            <li>年月日:1970-04-01
            </ul>
        </ol>
        </td>
    </tr>
</table>
<?php
    } elseif (isset($_POST['pwd']) || isset($_GET['pwd'])) {
        if (isset($_POST['userid'])) {
            $userid = $_POST['userid'];
            $kana = $_POST['kana'];
            $name = $_POST['name'];
            $section_name = $_POST['section_name'];
        } else {
            $userid = $_SESSION['userid'];
            $kana = $_SESSION['kana'];
            $name = $_SESSION['name'];
            $section_name = $_SESSION['section_name'];
        }
        $query = "select * from user_master where uid='" . $userid . "'";
        $res=array();
        getResult($query,$res);
        $mailaddr = trim($res[0]['mailaddr']);
        $mailaddr_pos = strpos(trim($mailaddr),'@');
        $acount = substr($mailaddr,0,$mailaddr_pos);
?>
<table width="100%">
    <tr><td colspan=2 bgcolor="#ff6600" align="center">
        <font color="#ffffff">ユーザーのパスワード変更</font></td>
    </tr>
    <tr><td colspan=2 valign="top">
        <table width="100%">
            <tr><td width="20%">社員No.</td>
                <td><?php echo($userid); ?></td>
            </tr>
            <tr><td width="20%">名前</td>
                <td><font size=1><?php echo($kana); ?></font><br><?php echo($name); ?></td>
            </tr>
            <tr><td width="20%">所属</td>
                <td><?php echo($section_name); ?></td>
            </tr>
            <tr><td width="20%">現在のパスワード</td>
                <td>
                    <?php
                    if ($_SESSION['Auth'] == AUTH_LEVEL3) {
                        echo trim($res[0]['passwd']);
                    } else {
                    echo str_repeat('*', strlen(trim($res[0]['passwd'])));
                    }
                    ?>
                </td>
            </tr>
        <form method="post" action="chg_passwd.php" onSubmit="return chkPasswd(this)">
            <tr><td colspan=2><hr>新しいパスワードを入力してください。</td></tr>
            <tr><td><input type="password" name="passwd" siza=12 maxlength=8></td><td><br></td></tr>
            <tr><td colspan=2>確認のパスワードを入力してください。</td></tr>
            <tr><td><input type="password" name="repasswd" siza=12 maxlength=8></td><td><br></td></tr>
            <tr>
            <td align="right">
                <input type="submit" value="変更">
                <input type="hidden" name="userid" value="<?php echo($userid); ?>">
                <input type="hidden" name="kana" value="<?php echo($kana); ?>">
                <input type="hidden" name="name" value="<?php echo($name); ?>">
                <input type="hidden" name="section_name" value="<?php echo($section_name); ?>">
                <input type="hidden" name="func" value=<?php echo(FUNC_CHGUSERINFO); ?>>
                <input type="hidden" name="pwd" value=1>
                <input type="hidden" name="acount" value="<?php echo($acount); ?>">
                </td>
            </tr>
        </form>
            <tr>
                <td>
                <form method='post' action='emp_menu.php?func=<?php echo FUNC_LOOKUP ?>'>
                    <input type='hidden' name='offset' value='<?php echo $offset ?>'>
                    <input type="submit" value="戻る">
                </form>
                </td>
            </tr>
        </table>
        </td>
    </tr>
</table>
<?php
    } else {
        echo ("不正な画面呼出が発生しました。<br>ユーザー認証が行われていない可能性があります。");
        // $_SESSION['s_sysmsg'] = "不正な画面呼出が発生しました。<br>ユーザー認証が行われていない可能性があります。";
        // header("Location: http:" . WEB_HOST . "index.php");
        // exit();
    }
?>
