<?php

class errorView extends View{
    function dispatch(&$context)
    {
        if ($context->get("errstr") == "") {
            header("Location:".APP_FILENAME);
        }
        $cgi = $context->getCgi();
        
        if ($cgi->get("name") != ""){
            echo $cgi->get("name");
        }
        require( $this->getTemplateName($context) );
    }
}

?>