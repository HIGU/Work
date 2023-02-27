<?php
header("Content-type:text/html;charset=euc-jp");
?>
<html xmlns:x>
<head>
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Expires" content="Thu, 01 Dec 1990 006:00:00 GMT">
<title><?php echo $title; ?></title>
<style type="text/css">
<!--
body{
    font-size:12px;
    background-color:<?php echo APP_HTML_BGCOLOR; ?>;
}
.waku{
    border: 1pt solid #e0e0e0;
    background-color:<?php echo APP_HTML_WAKUBGCOLOR; ?>;
    padding:8px;
    line-height:130%;
}
tr,td{
    font-size:12px;
}
.waku_b{
    background-color:#f5f5f5;
    border: 1pt solid #e0e0e0;
    padding:8px;
    line-height:130%;
}
.waku_i{
    margin-top:8px;
    background-color:#ffffff;
    border: 1pt solid #e0e0e0;
    padding:12px;
    line-height:130%;
}
.res{
    margin-left:12px;
}
hr{
    color: #e0e0e0;
    height:1px;
}
h1{
    font-size:14px;
}
-->
</style>
<?php
$header_jsdir = dirname(APP_FILENAME);
if (substr($header_jsdir,-1) != "/") {
    $header_jsdir .= "/";
}
echo "<?import namespace=x implementation=\"".$header_jsdir."js/editbox.htc\" />";
?>
</head>
<body>
