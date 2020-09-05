<?php

function outescape($value) {
    return htmlspecialchars($value);;
}

function outprint($value) {
    echo outescape($value);
}

?>
