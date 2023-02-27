<?php

class writeboxView extends View{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        $cname = $cgi->get("cname");
        require( $this->getTemplateName($context) );
    }
}

?>