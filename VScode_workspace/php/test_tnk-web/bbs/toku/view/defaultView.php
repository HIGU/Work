<?php

include_once(APP_LIB_DIR.'class/Page_Index.php');

/**
 * デフォルトのVIEW
 */
class defaultView extends View{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        
        $cachemode = $context->get("cachemode");
        
        if ($cachemode != "yes") {
            $offset = $cgi->param("o");
        } else {
            $offset = $context->get("c_offset");
        }
        
        if ($offset == "") $offset = 0;
        
        if ($cachemode != "yes") {
            $cname = $cgi->cookie->get("cname");
        }
        
        $title = APP_TITLE;
        
        $ctrl = new Ctrl_File_Data("Data_Model_User",APP_DATA_FILE);
        
        $query= new Data_Query();
        $query->setStart($offset);
        $query->setLength(APP_DATA_VIEW_COUNT);
        
        $objs = $ctrl->get($query);
        
        $file = $ctrl->getFileData();
        
        $pagecount= APP_DATA_VIEW_COUNT;
        $allcount = count($file->get());
        
        $pageindex = new Page_Index($offset,$pagecount,$allcount);
        
        require( $this->getTemplateName($context) );
    }
}

?>