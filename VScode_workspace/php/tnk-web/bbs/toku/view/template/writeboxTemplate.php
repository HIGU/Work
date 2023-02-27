var cname = '<?php echo $cname;?>';

if (window.createPopup) {
    document.write("<x:editbox />");
} else {
    document.write("<table width=\"95%\">");
    document.write("<form method=\"post\" action=\"\" name=\"fm1\">");
    document.write("<input type=\"hidden\" name=\"m\" value=\"write\">");
    document.write("<tr>");
    document.write("<td width=1% nowrap align=right valign=top>名前 : </td>");
    document.write("<td width=99%><input type=\"text\" size=\"20\" name=\"name\" value=\""+cname+"\"></td>");
    document.write("</tr>");
    document.write("<tr>");
    document.write("<td width=1% nowrap align=right valign=top>タイトル : </td>");
    document.write("<td width=99%><input type=\"text\" size=\"80\" name=\"title\"></td>");
    document.write("</tr>");
    document.write("<tr>");
    document.write("<td width=1% nowrap align=right valign=top>メッセージ :</td>");
    document.write("<td width=99%><textarea name=\"message\" rows=\"5\" cols=\"80\"></textarea></td>");
    document.write("</tr>");
    document.write("<tr>");
    document.write("<td width=1% nowrap align=right valign=top>削除キー : </td>");
    document.write("<td width=99%><input type=\"password\" size=\"9\" name=\"delkey\"></td>");
    document.write("</tr>");
    document.write("<tr>");
    document.write("<td width=1% nowrap></td>");
    document.write("<td width=99%><input type=\"submit\" value=\"　　投稿　　\">");
    document.write("</td>");
    document.write("</tr>");
    document.write("</form>");
    document.write("</table>");
}