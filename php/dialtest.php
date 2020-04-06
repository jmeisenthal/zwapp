<?php
    require_once('php/init_twig.php');

    $twig->load('dialtest.html')->display(array('application'=>'Zwapp!'));
?>