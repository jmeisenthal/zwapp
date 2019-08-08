
<?php
	$vendor = dirname(__FILE__) . "/../vendor/";

	require_once($vendor . "autoload.php");

	$loader = new Twig\Loader\FilesystemLoader(dirname(__FILE__) . "/../templates");
	$twig = new \Twig\Environment($loader);
	
	$template = $twig->load('index.html');
	$template->display(array('application'=>'Zwapp!'));
?>


