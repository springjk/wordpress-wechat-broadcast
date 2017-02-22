<?php

/* use for show remote image */

header('Content-type: image/jpeg');

echo file_get_contents(isset($_GET["url"]) ? $_GET["url"] : '');