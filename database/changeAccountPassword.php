<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
if (
    getClientVersion() == "1.2-beta2" ||
    getClientVersion() == "1.2" ||
    getClientVersion() == "1.21" ||
    getClientVersion() == "1.3-beta1" ||
    getClientVersion() == "1.3-beta2" ||
    getClientVersion() == "1.3" ||
    getClientVersion() == "1.33" ||
    getClientVersion() == "1.4.0-beta1" ||
    getClientVersion() == "1.4.0" ||
    getClientVersion() == "1.4.1" ||
    getClientVersion() == "1.5.0" ||
    getClientVersion() == "1.5.1" ||
    getClientVersion() == "1.5.2"
) {
    exitWithMessage("-1", getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2");
}

exitWithMessage(json_encode(["success" => false, "message" => "You must use client version 26.1 or higher to change your password in game"]));