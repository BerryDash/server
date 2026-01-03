<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
echo getClientVersion() == "1.8.2" || getClientVersion() == "1.4.0-beta1" ? "1" : "2";