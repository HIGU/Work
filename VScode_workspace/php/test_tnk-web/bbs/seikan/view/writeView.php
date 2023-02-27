<?php

class writeView extends View{
    function dispatch(&$context)
    {
        $cgi = $context->getCgi();
        require( $this->getTemplateName($context) );
    }
}

?>