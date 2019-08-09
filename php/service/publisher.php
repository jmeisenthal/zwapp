<?php 
	require('../comicVine.php');
	require('../init_twig.php');

	$id = $_GET['id'];
	if (isset($id) && $id) {
		$cache = NULL;//$_SESSION['publishers'];
		if (is_null($cache)) {
			$cache = new stdClass;
			$_SESSION['publishers'] = $cache;
		}
		if (is_null($cache->$id)) {
			$cache->$id = new ComicVine\Publisher($id);
		}

		$template = $_GET['template'] ?: 'radial_nav__choice.html';
		error_log("Hello Error Log!");
		$test = $cache->$id;
		error_log("Name: ".$test->name);  

		error_log("As array: ".var_dump($test->to_array()));
		$twig->load($template)->display($cache->$id->to_array());

		// $twig->load($template)->display(json_decode(json_encode($cache->$id), true);
	}
 ?>