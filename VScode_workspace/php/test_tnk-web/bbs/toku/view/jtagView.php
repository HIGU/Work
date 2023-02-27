<?php

class jtagView extends View{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        
        $code = ($cgi->param("c") != "") ? $cgi->param("c") : "";
        
        $host = $cgi->env("HTTP_HOST");
        $uri  = $cgi->env("SCRIPT_NAME");
        
        $scripturi  = "http://".$host.$uri;
        
        require( $this->getTemplateName($context) );
    }
}

?>