<?php

class writeAction extends Action{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        $script = $cgi->post("script");
        $script = stripcslashes($script);
        
        $data = new Data_Model_User($cgi);
        
        if ($err = $data->check()) {
            trigger_error($err);
        } else {
            // クッキーに名前保存
            $cgi->cookie->setExpire(time()+3600*7*24);
            $cgi->cookie->set("cname",$data->get("name"));
            
            $data->saveFile("file",APP_DATA_DIR.$data->get("id"));
            
            // マスタファイルに保存
            $file = new File_Data(APP_DATA_FILE);
            $file->writeFirst($data->encData());
            
            if (count($file->get()) > APP_DATA_SAVE_MAX) {
                $newar = $file->slice(0,APP_DATA_SAVE_MAX);
                $file->overwrite($newar);
            }
            
            // 全ページキャッシュ作成
            Cache_Writer::execute($context);
            // 個別ページキャッシュ作成
            $context->set("one_id",$data->get("id"));
            Cache_Writer::execute($context, "one");
        }
    }
}

?>