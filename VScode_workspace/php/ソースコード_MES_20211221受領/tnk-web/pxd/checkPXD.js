var plugin = 0;
if (navigator.mimeTypes && navigator.mimeTypes["application/pxd"]) { 
    // For Netscape and Firefox
    plugin = 1;
} else if (navigator.userAgent && (navigator.userAgent.indexOf("MSIE") >= 0) &&
    (navigator.userAgent.indexOf("Win") >= 0)) {
    // For Internet Exporer
    r = '\n';
    document.write('<SCRIPT LANGUAGE="VBScript"\>' + r);
    document.write('on error resume next' + r);
    document.write('plugin = ( IsObject(CreateObject("pxd.app1")))' + r);
    document.write('if ( plugin <= 0 ) then plugin = ( IsObject(CreateObject("pxd.app1")))' + r);
    document.write('</SCRIPT\>' + r);
}
if (!plugin) {
    // location.replace('downloadPXDoc.php');
    window.open('/pxd/downloadPXDoc.php', 'down_win', "width=10,height=10");
    /*****
    r = '\n';
    document.write('<table border="1" cellspacing="0" width="100%" bgcolor="#FFFF77"\>' + r);
    document.write('<tr\><td\>' + r);
    document.write('<font size="-1" color="#000000"\>' + r);
    document.write('<a href="http://www.pxdoc.com/download.htm" target="_blank"\>' + r);
    document.write('このサイトの印刷機能をご利用の際は、印刷ソフト（PXDoc）が必要です。無料ダウンロードはここをクリックしてください。' + r);
    document.write('</a\>' + r);
    document.write('</font\>' + r);
    document.write('</td\></tr\>');
    document.write('</table\>');
    *****/
}
