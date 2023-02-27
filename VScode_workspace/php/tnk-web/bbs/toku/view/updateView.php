<?php

class updateView extends View{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        $id = $cgi->param("id");
        
        $ctrl = new Ctrl_File_Data("Data_Model_User",APP_DATA_DIR."data.txt");
        
        $query = new Data_Query();
        $query->set("id",$id);
        
        $obj = $ctrl->find($query);
        
        require( $this->getTemplateName($context) );
    }
}

?>