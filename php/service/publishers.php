<?php 
	require('../comicVine.php');
	require('../mongo.php');
	require('../init_twig.php');

	// TODO: Make this or the URL a global property or via PHP globals
	// $client = new MongoDB\Client("mongodb://localhost:27017");

	$publishers = ZwappMongo\Collection::getPublishers();
	foreach($publishers->getList() as $publisher) {
		$template = $_GET['template'] ?: 'radial_nav__choice.html';
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

		$twig->load($template)->display($pub_formatted);			
	}
 ?>