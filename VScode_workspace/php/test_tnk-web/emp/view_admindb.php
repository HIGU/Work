<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file アドミニストレータＤＢ処理                   //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  view_admindb.php                                     //
// 2002/08/07 セッション管理を追加 & register_globals = Off 対応            //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
//////////////////////////////////////////////////////////////////////////////
// access_log("view_admindb.php");     // Script Name 手動設定
access_log(substr(__FILE__, strlen($_SERVER['DOCUMENT_ROOT'])));
echo view_file_name(__FILE__);
?>
<form method="post" action="emp_menu.php?func=<?php echo(FUNC_DBADMIN); ?>" onSubmit="return chkUserQuery(this)">

<table width='100%'>
    <tr><td bgcolor="#003e7c" align="center">
        <font color="#ffffff">データベース処理</font></td></tr>

    <tr><td bgcolor="#cdcdcd">
        <table width="100%" border='0'>
            <tr><td width=120><font size=-1>ホスト</font></td>
                <td><font size=-1><?php echo(DB_HOST); ?></font></td>
                <td width=120><font size=-1>ポート</font></td>
                <td><font size=-1><?php echo(DB_PORT); ?></font></td>
            <tr><td width=120><font size=-1>データベース</font></td>
                <td><font size=-1>PostgreSQL</font></td>
                <td width=120><font size=-1>データベース名</font></td>
                <td><font size=-1><?php echo(DB_NAME); ?></font></td>
            <tr><td width=120><font size=-1>ユーザー</font></td>
                <td><font size=-1><?php echo(DB_USER); ?></font></td>
                <td width=120><font size=-1>パスワード</font></td>
                <td><font size=-1><?php echo(DB_PASSWD); ?></font></td>
        </table>
    </td></tr>

    <tr><td valign="bottom"><font size=-1><br>ユーザークエリー </font>
        <a href="help.htm" target="_blank">
            <img border=0 src="../img/help.gif" alt="ヘルプ" width=22 height=16>
        </a>
        <input type='submit' name='exec' value='実行'>
    </td></tr>
    <tr><td>
        select script,count(*) from access_log where ip_addr<>'10.1.3.136' group by script order by count DESC limit 30
    </td></tr>
    <tr><td>
        select host,count(*) from access_log where ip_addr<>'10.1.3.136' group by host order by count DESC limit 30 
    </td></tr>
    <tr><td>
        select * from access_log where ip_addr<>'10.1.3.136' order by date_log DESC,time_log DESC limit 60 offset 0
    </td></tr>
    <tr><td>
        select a.ip_addr,a.host,a.uid,u.name,date_log,time_log,script from access_log as a left outer join user_detailes as u using(uid) where a.ip_addr<>'10.1.3.136' order by a.date_log DESC,a.time_log DESC limit 60 offset 0
    </td></tr>
    <tr><td valign="bottom">
<?php
    if(isset($_POST['userquery'])){ 
        $_POST['userquery']=StripSlashes($_POST['userquery']);
        echo("<textarea name='userquery' cols=75 rows=2 wrap='virtual'>" . $_POST['userquery'] . "</textarea>");
    }else{
        echo("<textarea name='userquery' cols=75 rows=2 wrap='virtual'></textarea>");
    }
?>
<!--    <input type='submit' name='exec' value='実行'>
-->
    </td></tr>
</table>

<table width="100%">
    <tr><td><hr></td></tr>
    <tr><td valign="bottom"><font size=-1>結果 <input type='submit' name='exec' value='実 行'> <input type="submit" value="クリア" name="clr"></font></td></tr>
<?php
    if(isset($_POST['userquery'])&&!isset($_POST['clr'])){
        $len=strlen($_POST['userquery']);
        $query="";
        for($i=0;$i<$len;$i++){
            $query .=substr($_POST['userquery'],$i,1);
            if(substr($query,strlen($query)-1,1)==";"||$i==$len-1){
                if($query){
                    $field=array();
                    $res=array();
                    if(($rows=getResultWithField($query,$field,$res))>=0){
                        echo("<tr><td>実行クエリー  " . $query . "</td></tr>");
                        echo("<tr><td><table border=1><tr>");

                        $num=count($field);
                        for($n=0;$n<$num;$n++)
                            echo("<td nowrap>" . $field[$n] . "</td>\n");
    
                        for($r=0;$r<$rows;$r++){
                            echo("<tr>");
                            for($n=0;$n<$num;$n++)
                                echo("<td nowrap>" . $res[$r][$n] . "</td>\n");
                            echo("</tr>");
                        }
                        echo("</tr></table></td></tr>");
                    }else{
                        echo("<tr><td>実行クエリー  " . $query);
                        echo("<font size=-1 color='#ff7e00'><br>データベースへの問い合わせに失敗しました。");
                        echo("<br>接続のプロパティを確認して下さい。</font></td></tr>");
                    }
                }
                $query="";
            }
        }
    }
?>
</table>
</form>
