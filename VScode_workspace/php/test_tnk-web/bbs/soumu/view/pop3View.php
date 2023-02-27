<?php

class pop3View extends View{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        
        $mailcount = $context->get("mailcount");
        
        require( $this->getTemplateName($context) );
    }
}

?>