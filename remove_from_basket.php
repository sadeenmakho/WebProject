<?php
require_once 'functions.inc.php';

start_session_once();

$id = get_value('id');

if ($id != '' && isset($_SESSION['basket'][$id])) {
    unset($_SESSION['basket'][$id]);
}

redirect_to('basket.php');
?>