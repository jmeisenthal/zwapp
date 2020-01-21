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

		function __construct($id, $collection) {
			$this->id = $id;
			$this->collection = $collection;
		}

		function __get($property) {
			
		}
	}

	abstract class Collection {
		private $collection;
		private $list;

		function __construct($collection) {
			$this->collection = $collection;
		}

		abstract function create($document);

		function getList() {
			if ($this->list == NULL) {
				$cursor = $this.collection.aggregate([{$sort: {sort: 1}}]);
				$this->list = array();
				forreach($cursor as document) {
					array_push($this->list, create(document));
				}
			}

			return $this->list;
		}
	}

	class Publisher extends Document {

	}

?>