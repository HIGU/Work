<?php
//////////////////////////////////////////////////////////////////////////
// プログラム開発依頼書 照会&編集                                       //
// 2002/02/12 Copyright(C)2002-2003 Kobayashi tnksys@nitto-kohki.co.jp  //
// 変更経歴                                                             //
// 2002/08/09 register_globals = Off 対応                               //
// 2003/12/12 defineされた定数でディレクトリとメニュー名を使用する      //
//////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
// require("../define.php");
require_once ("../tnk_func.php");
// $sysmsg = $_SESSION["s_sysmsg"];
// $_SESSION["s_sysmsg"] = NULL;
access_log();                       // Script Name は自動取得
// $_SESSION["edit_dev_req"] = date("H:i");
if ( !isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"]) ) {
    $_SESSION["s_sysmsg"] = "認証されていないか認証期限が切れました。Login し直して下さい。";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}
// session_register("s_dev_req_No","s_dev_req_sdate","s_dev_req_edate","s_dev_req_section","s_dev_req_client");
// session_register("s_rec_No","s_dev_current_rec");        //表示用レコード№
if ($_POST["view_dev_req"]) {                       //dev_req_selectのボタンview_dev_req
    $_SESSION["s_dev_req_No"]      = $_POST["dev_req_No"];  //左記の変数の保存は呼出元へ返すため
    $_SESSION["s_dev_req_sdate"]   = $_POST["dev_req_sdate"];
    $_SESSION["s_dev_req_edate"]   = $_POST["dev_req_edate"];
    $_SESSION["s_dev_req_section"] = $_POST["s_dev_req_section"];
    $_SESSION["s_dev_req_client"]  = $_POST["dev_req_client"];
    $_SESSION["s_dev_req_sort"]    = $_POST["dev_req_sort"];
    $_SESSION["s_dev_req_kan"]     = $_POST["dev_req_kan"];
    $dev_req_No      = $_POST["dev_req_No"];                //ローカル変数に代入
    $dev_req_sdate   = $_POST["dev_req_sdate"];
    $dev_req_edate   = $_POST["dev_req_edate"];
    $dev_req_section = $_POST["s_dev_req_section"];
    $dev_req_client  = $_POST["dev_req_client"];
    $dev_req_sort    = $_POST["dev_req_sort"];
    $dev_req_kan     = $_POST["dev_req_kan"];
} else {
    $dev_req_No      = $_SESSION["s_dev_req_No"];       //左記の変数の保存は次へや前へのボタン操作時のため
    $dev_req_sdate   = $_SESSION["s_dev_req_sdate"];    //またUPDATE時の変数復元のためdev_req_update
    $dev_req_edate   = $_SESSION["s_dev_req_edate"];
    $dev_req_client  = $_SESSION["s_dev_req_client"];
    $dev_req_sort    = $_SESSION["s_dev_req_sort"];
    $dev_req_kan     = $_SESSION["s_dev_req_kan"];
}
$Auth = $_SESSION["Auth"];

if ($Auth>=3) {
    define("DISP_ROWS",5);
} else {
    define("DISP_ROWS",20);
}
if ($_POST["backward"] == "前へ") {
    $_SESSION["s_rec_No"] -= (DISP_ROWS+DISP_ROWS);
    if ($_SESSION["s_rec_No"] < 0) {
        $_SESSION["s_rec_No"] = 0;
    }
}
$s_rec_No = $_SESSION["s_rec_No"];

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>TNK 開発依頼書照会・編集</TITLE>
<style type="text/css">
<!--
input.sousin    {background-color:red;}
input.text      {background-color:black; color:white;}
.pt         {font-size:11pt;}
.fontred        {color:red;}
.textright      {text-align:right;}
-->
</style>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
// -->
</script>
</HEAD>
<BODY>
<table width=100%>
    <tr><td bgcolor="#003e7c" align="center">
        <font color="#ffffff" size="5">プログラム開発依頼一覧</font></td></tr>
</table>
<?php
    //              0    1      2        3      4    5    6        7    8      9      10      11        12     13
    $query="select 番号,依頼日,依頼部署,依頼者,目的,内容,予想効果,備考,優先度,作業区,進捗状況,開発工数,完了日,担当者 from dev_req ";
    if($dev_req_kan=="全て"){
        $query .= "where 完了日!='2050-01-01' ";                // 全ての代わり
        $dsp_kan = "完了区分＝全て";
    }elseif($dev_req_kan=="未完了"){
        $query .= "where (完了日='1970-01-01' or 完了日=NULL) and 優先度!='X' ";    // 未完了分(Linuxのスタート日)
        $dsp_kan = "完了区分＝未完了";
    }elseif($dev_req_kan=="保留他"){
        $query .= "where 優先度='X' ";                  // 保留・その他分
        $dsp_kan = "完了区分＝保留他";
    }else{
        $query .= "where 完了日>'1998-01-01' ";                 // 完了分(正規な完了日付のもの)
        $dsp_kan = "完了区分＝完了分";
    }
    if($dev_req_No!=""){
        $query .= "and 番号=$dev_req_No ";
    }elseif($dev_req_client!="" || $dev_req_sdate!="" || $dev_req_edate!=""){
        $query .= "and ";
        if($dev_req_client!=""){
            $query .= "依頼者='$dev_req_client' ";
            if($dev_req_sdate!=""){
                $query .= "and 依頼日>='$dev_req_sdate' ";
                if($dev_req_edate!=""){
                    $query .= "and 依頼日<='$dev_req_edate' ";
                }
            }
        }elseif($dev_req_sdate!=""){
            $query .= "依頼日>='$dev_req_sdate' ";
            if($dev_req_edate!=""){
                $query .= "and 依頼日<='$dev_req_edate' ";
            }
        }elseif($dev_req_edate!=""){
            $query .= "依頼日<='$dev_req_edate' ";
        }
    }
    $query .= "and del_flag<>TRUE ";        // 削除フラグが真でないもの
    
    if($dev_req_sort=="依頼日"){
        $query .= "order by 依頼日";
        $dsp_sort = "依頼日順";
    }elseif($dev_req_sort=="依頼部署"){
        $query .= "order by 依頼部署";
        $dsp_sort = "依頼部署順";
    }elseif($dev_req_sort=="依頼者"){
        $query .= "order by 依頼者";
        $dsp_sort = "依頼者順";
    }elseif($dev_req_sort=="完了日"){
        $query .= "order by 完了日 desc";
        $dsp_sort = "完了日順";
    }elseif($dev_req_sort=="開発工数"){
        $query .= "order by 開発工数 desc";
        $dsp_sort = "開発工数順";
    }elseif($dev_req_sort=="番号"){
        $query .= "order by 番号";
        $dsp_sort = "受付番号順";
    }
?>

<table width="100%">
    <hr color="navy">
    <script language="JavaScript" src="./dev_req.js">
    </script>
<?php
    $field=array();
    $res=array();
    if(($rows=getResultWithField($query,$field,$res))>=0){
        $num=count($field);
        for($r=0;$r<$rows;$r++){                // 各レコードの合計工数を算出
            for($n=0;$n<$num;$n++){
                if($n==11){
                    $t_kousuu  += $res[$r][$n]; // 合計工数
                }
            }
        }
        $ft_kousuu = number_format($t_kousuu);                    // ３桁ごとのカンマを付加
        $f_rows = number_format($rows);
        if($dev_req_sdate)
            $f_d_start = format_date($dev_req_sdate);
        else
            $f_d_start = "最初";
        if($dev_req_edate)
            $f_d_end = format_date($dev_req_edate);    // 日付を / でフォーマット
        else
            $f_d_end = "最後";
        print "<tr>\n";
        print "<td align=\"center\" nowrap><b><u>$dsp_sort ： $dsp_kan ： 依頼日＝ $f_d_start ～ $f_d_end 
            ： 合計件数＝$f_rows ： 合計工数＝$ft_kousuu <u><b></td>\n";
        print "</tr></table>\n";
        
        print "<div align='center'><table><tr>\n";
        print("<form method='post' action='dev_req_select.php'>\n");
            print("<td><input type='submit' value='戻る' name='return'></td>\n");
        print("</form>\n");
        if($rows >= ($s_rec_No + DISP_ROWS) )
            $limits = ($s_rec_No + DISP_ROWS);           // 一回の表示をDISP_ROWS行までに制限
        else
            $limits = $rows;
        if($s_rec_No >= 1){
            $back_logic = TRUE;
            print("<form method='post' action='edit_dev_req.php'>\n");
                print("<td><input type='submit' value='前へ' name='backward'></td>\n");
            print("</form>\n");
        }
        if( ($s_rec_No + DISP_ROWS) < $rows){
            $for_logic = TRUE;
            print("<form method='post' action='edit_dev_req.php'>\n");
                print("<td><input type='submit' value='次へ' name='forward'></td>\n");
            print("</form>\n");
        }
        print("</tr></table></div>\n");
        
/*  debug   echo("<tr><td>実行クエリー  " . $query . "</td></tr>"); */
        echo("<table border=\"1\" bgcolor=\"#e6e6fa\" cellspacing=\"0\" cellpadding=\"2\">");
        echo("\n<tr bgcolor=\"add8e6\" align=\"center\">\n");       // フィールド名 一行開始
        print "<th nowrap>No.</th>\n";                              // レコード番号追加
        for($n=0;$n<$num;$n++)
            echo("<th nowrap>" . $field[$n] . "</th>\n");
        echo("</tr>\n");                                            // フィールド名 一行終了
        for($r=$s_rec_No;$r<$limits;$r++){
            if($Auth>=3)        //権限がAdministratorの場合
                print("\n<form method='post' action='dev_req_update.php' onSubmit='return chk_dev_req_edit(this)'>\n");
            echo("<tr>\n");
            $No = $r + 1;
            print("<td align='right' nowrap> $No </td>\n");    // 表にレコード番号追加
            for($n=0;$n<$num;$n++){
                if($Auth>=3){   // 権限がAdministratorの場合
                    if($n==0)
                        print("<td align='right'><input type='submit' value='" . $res[$r][$n] . "' name='update_No'></td>\n");
                    if($n==1)
                        print("<td align='center'><input type='text' name='iraibi' size='12' maxlength='10' value='" . $res[$r][$n] . "'></td>\n");
                    if($n==2){
                        print("<td align='center'><select name='iraibusho'>\n");
                        $query_section="select * from section_master order by sid asc";
                        $res_section=array();
                        if($rows_section=getResult($query_section,$res_section)){
                            for($i=0;$i<$rows_section;$i++){
                                echo("<option ");
                                if($res[$r][$n]==$res_section[$i][0])    // なぜか sid が使えず数字の 0 にした。
                                    echo("selected ");
                                echo("value='" . $res_section[$i][0] . "'>" . rtrim($res_section[$i][section_name]) . "</option>\n");
                            }
                        }
                        print("</select></td>\n");
                    }
                    if($n==3){
                        print("<td align='center'><input type='text' name='iraisya' size='7' maxlength='6' value='" . ltrim($res[$r][$n]) . "'>\n");
                        $query_user="select name from user_detailes where uid='" . $res[$r][$n] . "'";
                        $res_user=array();
                        if($rows_user=getResult($query_user,$res_user))
                            print("<font size='1'>" . rtrim($res_user[0][name]) . "</font></td>\n");
                        else
                            print("-</td>\n");
                    }
                    if($n==4)
                        echo("<td align='left'><textarea name='mokuteki' 
                        cols='20' rows='3' wrap='soft'>" . $res[$r][$n] . "</textarea></td>\n");
                    if($n==5)
                        echo("<td align='left'><textarea name='naiyou' 
                        cols='30' rows='3' wrap='soft'>" . $res[$r][$n] . "</textarea></td>\n");
                    if($n==6)
                        print("<td align='center'><input type='text' class='textright' name='yosoukouka' size='12' maxlength='9' value='" . $res[$r][$n] . "'></td>\n");
                    if($n==7)
                        echo("<td align='left'><textarea name='bikou' 
                        cols='20' rows='3' wrap='soft'>" . $res[$r][$n] . "</textarea></td>\n");
                    
                    
                    
                    
                    
                    if($n==8){
                        print("<td align='center'><select name='yuusendo'>\n");
                        
                        print("<option value='A'");
                        if($res[$r][$n]=="A")
                            print("selected>A優先</option>\n");
                        else
                            print(">A優先</option>\n");
                        
                        print("<option value='B'");
                        if($res[$r][$n]=="B")
                            print("selected>B通常</option>\n");
                        else
                            print(">B通常</option>\n");
                        
                        print("<option value='X'");
                        if($res[$r][$n]=="X")
                            print("selected>X中止</option>\n");
                        else
                            print(">X中止</option>\n");
                        
                        print("<option value=' ' class='fontred'");
                        if(ltrim($res[$r][$n])=="")
                            print("selected><font color='red'>---</font></option>\n");
                        else
                            print(">---</option>\n");
                    }
                    if($n==9){
                        print("<td align='center'><select name='sagyouku'>\n");
                        
                        print("<option value='1'");
                        if($res[$r][$n]=="1")
                            print("selected>1開発</option>\n");
                        else
                            print(">1開発</option>\n");
                        
                        print("<option value='2'");
                        if($res[$r][$n]=="2")
                            print("selected>2他</option>\n");
                        else
                            print(">2他</option>\n");
                        
                        print("<option value=' ' class='fontred'");
                        if(ltrim($res[$r][$n])=="")
                            print("selected>未設</option>\n");
                        else
                            print(">未設</option>\n");
                    }
                    if($n==10)
                        echo("<td align='left'><textarea name='sintyoku' 
                        cols='30' rows='3' wrap='soft'>" . $res[$r][$n] . "</textarea></td>\n");
                    if($n==11)
                        print("<td align='center'><input type='text' class='textright' name='kousuu' size='7' maxlength='6' value='" . $res[$r][$n] . "'></td>\n");
                    if($n==12)
                        print("<td align='center'><input type='text' name='kanryou' size='12' maxlength='10' value='" . $res[$r][$n] . "'></td>\n");
                    if($n==13){
                        print("<td align='center'><select name='tantou'>\n");
                        print("<option value='010561'");
                        if($res[$r][$n]=="010561")
                            print("selected>小林</option>");
                        else
                            print(">小林</option>");
                        print("<option value='016713'");
                        if($res[$r][$n]=="016713")
                            print("selected>中山</option>");
                        else
                            print(">中山</option>");
                        print("<option value=''");
                        if($res[$r][$n]=="" || $res[$r][$n]=="      ") //初期は""だが更新すると"      "になるため
                            print("selected>Blank</option>");
                        else
                            print(">Blank</option>");
                        print("</select></td>\n");
                    }
                }else{                  // 他のユーザーの場合
                    if(ltrim($res[$r][$n])==""){
                        echo("<td align=\"center\" nowrap>-</td>\n");
                    }else{
                        if($n==2){
                            $query_section="select * from section_master where sid=" . $res[$r][$n] ;
                            $res_section=array();
                            if($rows_section=getResult($query_section,$res_section))
                                print("<td align='center'><font size='2'>" . rtrim($res_section[0][section_name]) . "</font></td>\n");
                            else
                                print("<td align='center' nowrap>-</td>\n");
                        }elseif($n==3){
                            $query_user="select name from user_detailes where uid='" . $res[$r][$n] . "'";
                            $res_user=array();
                            if($rows_user=getResult($query_user,$res_user))
                                print("<td align='center'><font size='1'>" . rtrim($res_user[0][name]) . "</font></td>\n");
                            else
                                print("<td align='center' nowrap>-</td>\n");
                        }elseif(($n==6 || $n==11) && $res[$r][$n]==0)
                            echo("<td align='center' nowrap>-</td>\n");
                        elseif($n==4 || $n==5 || $n==7 || $n==10)
                            echo("<td align='left'><textarea name='mokuteki' 
                            cols='30' rows='3' wrap='soft'>" . $res[$r][$n] . "</textarea></td>\n");
                        elseif($n==8){
                            if($res[$r][$n]=="A")
                                echo("<td align='center' nowrap>優先</td>\n");
                            elseif($res[$r][$n]=="B")
                                echo("<td align='center' nowrap>通常</td>\n");
                            elseif($res[$r][$n]=="X")
                                echo("<td align='center' nowrap>中止</td>\n");
                            else
                                echo("<td align='center' nowrap><font color='red'>未設定</font></td>\n");
                        }
                        elseif($n==9){
                            if($res[$r][$n]=="1")
                                echo("<td align='center' nowrap>開発</td>\n");
                            else
                                echo("<td align='center' nowrap>その他</td>\n");
                        }
                        elseif($n==12)
                            if($res[$r][$n]=="1970-01-01")  //Date型のスタート日1970なら未完了
                                echo("<td align='center'>-</td>\n");
                            else
                                echo("<td align='right'>" . $res[$r][$n] . "</td>\n");
                        elseif($n==13){
                            $query_user="select name from user_detailes where uid='" . $res[$r][$n] . "'";
                            $res_user=array();
                            if($rows_user=getResult($query_user,$res_user))
                                print("<td align='right'><font size='1'>" . rtrim($res_user[0][name]) . "</font></td>\n");
                            else
                                print("<td align='center'>-</td>\n");
                        }else
                            echo("<td align='right'>" . $res[$r][$n] . "</td>\n");
                    }
                }
            }
            echo("</tr>\n");
            if($Auth>=3)        //権限がAdministratorの場合
                print("</form>\n");
        }
        $_SESSION["s_dev_current_rec"] = $s_rec_No;
        $_SESSION["s_rec_No"] = $No;        // 次のレコード№にセット
        echo("</table>\n");
    }else{
        echo("<tr><td>実行クエリー  " . $query);
        echo("<font size=-1 color='#ff7e00'><br>データベースへの問い合わせに失敗しました。");
        echo("<br>接続のプロパティを確認して下さい。</font></td></tr>");
    }
    print "<div align='center'><table>\n";
    print "<tr>\n";
        print("<form method='post' action='dev_req_select.php'>\n");
            print("<td><input type='submit' value='戻る' name='return'></td>\n");
        print("</form>\n");
        if($back_logic){
            print("<form method='post' action='edit_dev_req.php'>\n");
                print("<td><input type='submit' value='前へ' name='backward'></td>\n");
            print("</form>\n");
        }
        if($for_logic){
            print("<form method='post' action='edit_dev_req.php'>\n");
                print("<td><input type='submit' value='次へ' name='forward'></td>\n");
            print("</form>\n");
        }
    print "</tr>\n";
    print("</table></div>\n");
?>
</BODY>
</HTML>
