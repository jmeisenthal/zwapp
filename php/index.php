
<?php
	require_once('php/init_twig.php');

	$twig->load('index.html')->display(array('application'=>'Zwapp!'));
?>


