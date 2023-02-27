<?php

class resAction extends Action{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        $script = $cgi->post("script");
        $script = stripcslashes($script);
        
        $data = new Data_Model_Res($cgi);
        
        if ($err = $data->check()) {
            trigger_error($err);
        } else {
            // クッキーに名前保存
            $cgi->cookie->set("cname",$data->get("name"));
            
            $id = $cgi->post("id");
            
            $ctrl = new Ctrl_File_Data("Data_Model_User",APP_DATA_FILE);
            
            $query = new Data_Query();
            $query->set("id", $id);
            
            $obj = $ctrl->find($query);
            
            if (empty($obj)) { 
                trigger_error("親記事が見つかりません");
            }
            
            if (APP_KIJI_UP) {
                // マスタファイルに保存(記事を一番上にあげる)
                $ctrl->delete($obj);
                $ctrl->insert($obj,"first");
            }
            
            // レスファイルに保存
            $file = new File_Data(APP_RES_DIR.$id.".cgi");
            
            $file->writeFirst($data->encData());
            
            /*
            if (count($file->get()) > APP_DATA_SAVE_MAX) {
                $newar = $file->slice(0,APP_DATA_SAVE_MAX);
                $file->overwrite($newar);
            }
            */
            
            // 全ページキャッシュ作成
            Cache_Writer::execute($context);
            
            // 個別ページキャッシュ作成
            $context->set("one_id",$data->get("id"));
            Cache_Writer::execute($context, "one");
            
            return "write";
        }
    }
}

?>