<?php
define("APP_LIB_DIR", "ld/");
$_APP_REQUIRES = array( APP_LIB_DIR.'config.php', APP_LIB_DIR.'applibs.php' );
foreach ($_APP_REQUIRES as $_APP_REQUIRE) {
    if (is_readable($_APP_REQUIRE) && is_file($_APP_REQUIRE))
    {require_once($_APP_REQUIRE);}
}
?>