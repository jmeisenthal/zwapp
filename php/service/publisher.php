<?php 
	require('../comicVine.php');
	require('../init_twig.php');

	$id = $_GET['id'];
	if (isset($id) && $id) {
		$cache = $_SESSION['publishers'];
		if (is_null($cache)) {
			$cache = new stdClass;
			$_SESSION['publishers'] = $cache;
		}
		if (is_null($cache->$id)) {
			$cache->$id = new ComicVine\Publisher($id);
		}

		$template = $_GET['template'] ?: 'radial_nav__choice.html';
		error_log("Hello Error Log!");
		error_log("Name: ".$cache->$id->get_name());  
		;
		error_log("As array: ".print_r(json_decode(json_encode($cache->$id), true));
		$twig->load($template)->display(json_decode(json_encode($cache->$id), true));

		// $twig->load($template)->display(json_decode(json_encode($cache->$id), true);
	}
 ?>