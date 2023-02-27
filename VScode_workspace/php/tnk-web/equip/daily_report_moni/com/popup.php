<HTML>
<HEAD>
<TITLE>ＰＯＰＵＰ</TITLE>
</HEAD>
<BODY onload="setURL()">
</BODY>
<SCRIPT language="JavaScript">
function setURL() {
   var args      = window.self.dialogArguments;
   var target    = args[0];
   document.writeln("<frameset cols=100%>");
   var frameStr = "<frame src =" + target;
   if (args.length > 1) {
       for (i=1; i<args.length; i++) {
           if (i==1) frameStr + "?";
           frameStr += args[i].name + "=" + args[i].value.replace(/ /g,"") + "&";
       }
   }
   document.writeln(frameStr);
   document.writeln("</frameset>");
}
</SCRIPT>
</HTML>