<?php
{
require_once'ld/loader.php';
$_APP_Context = new Context();
$viewName = Action_Manager::dispatch($_APP_Context);
View_Manager::dispatch($_APP_Context, $viewName);
}
?>
