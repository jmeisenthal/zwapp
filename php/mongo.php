<?php
	namespace Mongo;
	require 'vendor/autoload.php'; // include Composer's autoloader

	$client = new MongoDB\Client("mongodb://localhost:27017");
	$collection = $client->demo->beers;

	// Collections in Zwapp are initialized via curated scrapes from the ComicVine wiki. 
	// Accessing Zwapp DB objects transparently get and cache properties needed from the ComicVine API.
	
	abstract class Document {
		private $collection;
		private $id;
		private $cv_init;
	}

	abstract class Collection {
		private $collection;
		private $list;

		function _construct($collection) {
			$this->collection = $collection;
		}

		function getList() {
			if ($this->list == NULL) {
				$cursor = $this.collection.aggregate([{$sort: {sort: 1}}])
			}
		}
	}

?>