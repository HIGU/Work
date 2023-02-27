<?php

include_once(APP_LIB_DIR.'lib/pop3.php');
include_once(APP_LIB_DIR.'lib/pop3ex.php');

class pop3Action extends Action{
    function dispatch(&$context)
    {
        if (!APP_MAIL_POST) { return; }
        
        $p = new pop3ex(APP_MAIL_HOST,APP_MAIL_UID,APP_MAIL_PASS);
        
        if($p->can_execute()){
            if(!$p->open()){ // 接続エラー
                trigger_error("メールサーバに接続できません。");
            }
            $p->get_messages();
            $mails = $p->get_mails();
            $p->delete_all(); // 閲覧メールは削除
            $p->close();
        } else {
            $mails = array();
        }
        
        $upmailcount = 0;
        foreach($mails as $mail){
            $from = $mail->get("from");
            $subject = $mail->get("subject");
            $at_count = $mail->attach_count();
            $attaches = $mail->attaches;
            $body = $mail->get("body");
            
            if (function_exists("mb_convert_encoding")) {
                $subject = mb_convert_encoding($subject,"EUC-JP","JIS");
                $body = mb_convert_encoding($body,"EUC-JP","JIS");
            } else {
                include_once(APP_LIB_DIR.'lib/jcode.php');
                $subject = JIStoEUC($subject);
                $body = JIStoEUC($body);
            }
            
            $data = new Data_Model_User();
            $data->convert("id");
            $data->convert("date");
            $data->convert("title");
            
            $name = $subject;
            $message = $body;
            
            $data->set("name",$name);
            $data->convert("name");
            $data->set("message", $message);
            $data->convert("message");
            
            $file = new File_Data(APP_DATA_FILE);
            $file->writeFirst($data->encData());
            
            // キャッシュ作成
            Cache_Writer::execute($context);
            
            // 個別ページキャッシュ作成
            $context->set("one_id",$data->get("id"));
            Cache_Writer::execute($context, "one");
            
            $upmailcount++;
        }
        $context->set("mailcount",$upmailcount);
    }
}

?>