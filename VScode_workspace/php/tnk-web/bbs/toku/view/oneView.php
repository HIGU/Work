<?php

class oneView extends View{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        
        $cachemode = $context->get("cachemode");
        
        if ($cachemode != "yes") {
            $id = $cgi->param("id");
        } else {
            $id = $context->get("one_id");
        }
        
        $ctrl = new Ctrl_File_Data("Data_Model_User",APP_DATA_FILE);
        
        $query = new Data_Query();
        $query->set("id",$id);
        
        $obj = $ctrl->find($query);
        
        require( $this->getTemplateName($context) );
    }
}

?>