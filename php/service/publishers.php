<?php 
	require_once('../comicVine.php');
	require_once('../mongo.php');
	require_once('../init_twig.php');
	require_once '../init_logger.php';

	// TODO: Make this or the URL a global property or via PHP globals
	// $client = new MongoDB\Client("mongodb://localhost:27017");

	$publishers = ZwappMongo\Collection::getPublishers()->getList();

	$counter = 0;

	foreach($publishers as $publisher) {
		global $logger;
		$logger->debug("service/publisher: publisher id: {$publisher->id}");
		$template = $_GET['template'] ?: 'radial_nav__choice.html';
		$counter++;
		$pub_formatted = [];
		// var_dump($publisher);
		// foreach($publisher->cv_query->getProperties() as $prop) {
		// foreach(["id", "name", "icon_url"] as $prop) {
		// 	$pub_formatted[$prop] = $publisher[$prop];
		// }
		
		$pub_formatted['id'] = $publisher->id; 
		$pub_formatted['name'] = $publisher->name;
		$pub_formatted['icon_url'] = $publisher->icon_url;
		$pub_formatted['type'] = "publisher";

		// We want to show the hint bubble for just the 3rd choice:
		$pub_formatted['show_hint'] = $counter == 3;

		$twig->load($template)->display($pub_formatted);
	}
 ?>