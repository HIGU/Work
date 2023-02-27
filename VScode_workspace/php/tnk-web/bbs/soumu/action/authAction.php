<?php

class authAction extends Action{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        if ($cgi->post("user") != "") {
            
            $user = $cgi->post("user");
            $pass = $cgi->post("pass");
            
            $cgi->cookie->set("USER",$user);
            $cgi->cookie->set("PASS",$pass);
            
            $goto = $cgi->post("goto");
            
            header("Location:$goto");
            exit;
        }
    }
}

?>