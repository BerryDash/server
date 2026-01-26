<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
exitWithMessage(json_encode(["success" => false, "message" => "You must use client version 26.1 or higher to upload a marketplace icon"]));