<?php

include_once(APP_LIB_DIR.'class/Data_RSS.php');

class jscriptView extends View{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        
        $code = ($cgi->param("c") != "") ? $cgi->param("c") : "";
        
        if ($code != "") {
            include_once(APP_LIB_DIR.'lib/jcode.php');
        }
        
        $title = APP_TITLE;
        
        $ctrl = new Ctrl_File_Data("Data_Model_User",APP_DATA_FILE);
        
        $query= new Data_Query();
        $query->setStart(0);
        $query->setLength(APP_JS_VIEW_COUNT);
        
        $objs = $ctrl->get($query);
        
        $host = $cgi->env("HTTP_HOST");
        $uri  = $cgi->env("REQUEST_URI");
        $script=$cgi->env("SCRIPT_NAME");
        
        $scripturi  = "http://".$host.$script;
        $scriptpath = "http://".$host.dirname($uri)."/";
        
        require( $this->getTemplateName($context) );
    }
    function dateConv($str)
    {
        $yyyy = substr($str,0,4);
        $mm   = substr($str,4,2);
        $dd   = substr($str,6,2);
        $hh   = substr($str,8,2);
        $ii   = substr($str,10,2);
        $ss   = substr($str,12,2);
        
        $time = mktime($hh,$ii,$ss,$mm,$dd,$yyyy);
        
        return date("YÇ¯m·îdÆü",$time);
    }
}

?>