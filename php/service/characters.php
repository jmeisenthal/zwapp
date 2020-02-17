<?php 
	require('../comicVine.php');
	require('../mongo.php');
	require('../init_twig.php');

	$characters = [];
	$publisher_id = $_GET['publisher'];
	if (isset($publisher_id) && $publisher_id) {
		$start = microtime(TRUE);
		$publisher = ZwappMongo\Collection::getPublishers()->getMap()[$publisher_id];
		$stop = microtime(TRUE);
		$time1 = $stop - $start;
		print_r("\nTime1: $time1\n");
		$start = microtime(TRUE);
		$characters = $publisher->getTopChildren();
		$stop = microtime(TRUE);
		$time1 = $stop - $start;
		print_r("\nTime2: $time1\n");
	}
	else {
		$characters = ZwappMongo\Collection::getCharacters()->getList();
	}

	foreach($characters as $character) {
		$template = $_GET['template'] ?: 'radial_nav__choice.html';
		$character_formatted = [];
		
		$character_formatted['id'] = $character->id; 
		$character_formatted['name'] = $character->name;
		$character_formatted['icon_url'] = $character->icon_url;
		$character_formatted['type'] = "character";

		// print_r("\nCharacter: ");
		// var_dump($character_formatted);

		$twig->load($template)->display($character_formatted);			
	}
 ?>