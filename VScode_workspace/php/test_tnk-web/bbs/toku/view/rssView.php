<?php

include_once(APP_LIB_DIR.'class/Data_RSS.php');

class rssView extends View{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        
        $code = ($cgi->param("c") != "") ? $cgi->param("c") : "";
        
        $ctrl = new Ctrl_File_Data("Data_Model_User",APP_DATA_FILE);
        
        $query= new Data_Query();
        $query->setStart(0);
        $query->setLength(APP_RSS_VIEW_COUNT);
        
        $objs = $ctrl->get($query);
        
        if (count($objs) > 0) {
            $obj = $objs[0];
            if (empty($obj)) exit;
            $lastBuild = $this->dateConv($obj->get("date"));
        }
        
        $rss = new Data_RSS(APP_TITLE,"http://rd.phpspot.org/?phpspot","","ja","",$lastBuild);
        
        $host = $cgi->env("HTTP_HOST");
        $uri  = $cgi->env("REQUEST_URI");
        $scriptpath = "http://".$host.dirname($uri)."/";
        
        for ($i=0;$i<APP_RSS_VIEW_COUNT;$i++) {
            $obj = $objs[$i];
            if (empty($obj)) continue;
            $date = $obj->get("date");
            $date = $this->dateConv($date);
            $htmllink = $scriptpath."html/".$obj->get("id").".html";
            $title = $obj->get("name")." - ".$obj->get("title");
            $rss_item = new Data_RSS_Item($title,$htmllink,"",$date);
            $rss->addItem($rss_item);
        }
        
        if ($code == "u") {
            $rss_text = $rss->toString("UTF-8");
            if (function_exists("mb_convert_encoding")) {
                $rss_text = mb_convert_encoding($rss_text,"UTF-8","EUC-JP");
            } else {
                include_once(APP_LIB_DIR.'lib/jcode.php');
                require(APP_LIB_DIR."lib/code_table.jis2ucs");
                $rss_text = EUCtoUTF8($rss_text,$table_jis_utf8);
            }
            header ("Content-type: text/xml; charset=UTF-8");
            echo $rss_text;
        } else {
            header ("Content-type: text/xml; charset=EUC-JP");
            echo $rss->toString("EUC-JP");
        }
    }
    
    function dateConv($str)
    {
        return date("r",$str);
    }
}

?>