<?php 
	require('../comicVine.php');
	require('../mongo.php');
	require('../init_twig.php');
	require '../init_logger.php';

	$characters = [];
	$publisher_id = $_GET['publisher'];
	global $logger;
	$logger->debug("service/characters publisher: $publisher_id");
	if (isset($publisher_id) && $publisher_id) {
	$logger->debug("service/characters publisher: 2");
		$characters = ZwappMongo\Collection::getPublisherCharacters($publisher_id);
		// $publisher = ZwappMongo\Collection::getPublishers()->getMap()[$publisher_id];
		// $characters = $publisher->getTopChildren();
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

		$twig->load($template)->display($character_formatted);			
	}
 ?>