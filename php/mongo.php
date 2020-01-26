<?php
	namespace Mongo;
	require 'vendor/autoload.php'; // include Composer's autoloader

	$client = new MongoDB\Client("mongodb://localhost:27017");
	// $collection = $client->demo->beers;

	// Collections in Zwapp are initialized via curated scrapes from the ComicVine wiki. 
	// Accessing Zwapp DB objects transparently get and cache properties needed from the ComicVine API.
	
	abstract class Document {
		// private $collection;
		// private $id;

		function __construct(ComicVine\Query $cv_query, MongoDB\Collection $collection) {
			$this->cv_query = $cv_query;
			$this->collection = $collection;
		}

		function __get($property) {
			// id accessible via cv_query:
			if ($property == "id") {
				return $this->cv_query->id;
			}
			$doc = $this->collection.find({['_id' => $this->id]})->toArray()[0];

			if (!$doc->cv_init) {
				$doc = $this->setData();
			}

			return $doc[$property];
		}

		function setData() {
			$doc = array('_id' => $this->id);
			foreach ($$cv_query.to_array() as $prop => $value) {
				$doc[$prop] = $value;
			}
			$doc->cv_init = true;

			$collection->updateOne({['_id' => $this->id]},$doc);

			return $doc;
		}
	}

	abstract class Collection {
		private $collection;
		private $list;

		function __construct(ComicVine\Query $cv_query, MongoDB\Collection $collection) {
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

	public static

	class Publisher extends Document {

	}

?>