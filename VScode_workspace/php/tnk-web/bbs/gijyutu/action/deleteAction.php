<?php

class deleteAction extends Action{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        $exec = $cgi->post("exec");
        
        $admin = 0;
        if ($exec != "") {
            // 管理者認証
            if ($cgi->post("user") != "" && $cgi->post("pass") != "") {
                Auth_Page::execute($context);
                $admin = 1;
            }
            
            $id = $cgi->post("id");
            
            $ctrl = new Ctrl_File_Data("Data_Model_User",APP_DATA_FILE);
            
            $query = new Data_Query();
            $query->set("id", $id);
            
            $obj = $ctrl->find($query);
            
            if (!empty($obj)) {
                if ($admin == 0 && $obj->get("delkey") != md5($cgi->post("delkey"))) {
                    trigger_error("削除キーが一致しません");
                }
                
                $this->deleteDependenceFile($obj);
                $ctrl->delete($obj);
                
                // キャッシュ作成
                Cache_Writer::execute($context);
            } else {
                trigger_error("削除対象が見つかりません");
            }
            
            $context->set("delete_success",1);
            
            return "default";
        }
    }
    
    function deleteDependenceFile($obj)
    {
        $file1 = $obj->get("file");
        $file2 = $obj->get("file2");
        $resfile = APP_RES_DIR.$obj->get("id").".cgi";
        
        if (file_exists($file1)) { unlink($file1); }
        if (file_exists($file2)) { unlink($file2); }
        if (file_exists($resfile)) { unlink($resfile); }
    }
}

?>