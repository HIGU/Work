<?php

class updateAction extends Action{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        $exec = $cgi->post("exec");
        
        if ($exec != "") {
            $id = $cgi->post("id");
            
            $ctrl = new Ctrl_File_Data("Data_Model_User",APP_DATA_DIR."data.txt");
            
            $query = new Data_Query();
            $query->set("id",$id);
            
            $obj = $ctrl->find($query);
            
            $ctrl->delete($obj);
            
            $context->set("update_success",1);
        }
    
    }
}

?>