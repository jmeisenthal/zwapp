<?php 
	if (!is_null($twig)) {
		return;
	}

	$vendor = dirname(__FILE__) . "/../../vendor/";
	require_once($vendor . "autoload.php");

	// Include the primary templates dir and the img dir, in case svg's are templated in with css classes:
	$loader = new Twig\Loader\FilesystemLoader([dirname(__FILE__) . "/../../templates",dirname(__FILE__) . "/../../img"]);
	$twig = new \Twig\Environment($loader);
 