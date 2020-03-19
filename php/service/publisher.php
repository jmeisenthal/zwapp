<?php 
	require_once('../comicVine.php');
	require_once('../init_twig.php');

	$ids = $_GET['id'];
	if (isset($ids) && $ids) {
		$cache = $_SESSION['publishers'];
		if (is_null($cache)) {
			$cache = new stdClass;
			$_SESSION['publishers'] = $cache;
		}

		// Allow for multiple id's:
		foreach(explode(',', $ids) as $id) {
			if (is_null($cache->$id)) {
				$cache->$id = new ComicVine\Publisher($id);
			}

			$template = $_GET['template'] ?: 'radial_nav__choice.html';

			$twig->load($template)->display($cache->$id->to_array());			
		}
	}

	// wiki query for char by appearance:https://comicvine.gamespot.com/characters/?sortBy=appearance
 ?>