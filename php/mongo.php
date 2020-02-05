<?php
	namespace ZwappMongo;
	// require 'vendor/autoload.php'; // include Composer's autoloader

	use MongoDB;
	use ComicVine;

	// TODO: this is a problem:
	// static $client = new MongoDB\Client("mongodb://localhost:27017");

	// $collection = $client->demo->beers;
	// Collections in Zwapp are initialized via curated scrapes from the ComicVine wiki. 
	// Accessing Zwapp DB objects transparently get and cache properties needed from the ComicVine API.
	
	class Document {
		private $children;

		function __construct($cv_query, MongoDB\Collection $collection) {
			$this->cv_query = $cv_query;
			$this->collection = $collection;
		}

		function __get($property) {
			// id accessible via cv_query:
			if ($property == "id") {
				return $this->cv_query->id;
			}
			$doc = $this->collection->find(['_id' => $this->id])->toArray()[0];

			if (!$doc->cv_init) {
				$doc = $this->setData();
			}

			return $doc[$property];
		}

		function setData() {
			$doc = array('_id' => $this->id);
			foreach ($this->cv_query->to_array() as $prop => $value) {
				$doc[$prop] = $value;
			}
			$doc->cv_init = true;

			$this->collection->updateOne(['_id' => $this->id],['$set'=>$doc]);

			return $doc;
		}

		function getChildren() {
			$doc = $this->collection->find(['_id' => $this->id])->toArray()[0];
			if (is_null($doc->children)) {
				$children = [];
				$cv_children = $this->cv_query->getChildren();
				foreach($cv_children as $child) {
					// save as a map of name values keyed by id:
					$children[$child->id] = $child->name;
				}

				$this->collection->updateOne(['_id' => $this->id],['$set'=>array('childen'=>$children)]);
			}

			return $children;
		}

		/***************************************
		* Get the top children of this document in sort order according to the children's collection
		*/
		function getTopChildren($size = 9) {

		}
	}

	class Collection {
		static $client = NULL;
		private $cv_queryLambda,$collection;
		private $list, $map;

		function __construct($cv_queryLambda, MongoDB\Collection $collection) {
			$this->cv_queryLambda = $cv_queryLambda;
			$this->collection = $collection;
		}

		private function init() {
			if ($this->list == NULL) {
				$this->list = array();
				$this->map = array();
				$cursor = $this->collection->aggregate(array(array('$sort' => array('sort' => 1))));
				$cv_creator = $this->cv_queryLambda;
				foreach($cursor as $data) {
					$document = new Document($cv_creator($data->_id), $this->collection);
					array_push($this->list, $document);
					$this->map["$data->_id"] = $document;
				}
			}
		}

		function getList() {
			$this->init();

			return $this->list;
		}

		function getMap() {
			$this->init();

			return $this->map;
		}

		private static function getClient() {
			if (is_null(self::$client)) {
				self::$client = new MongoDB\Client("mongodb://localhost:27017");
			}

			return self::$client;
		}

		public static function getPublishers() {
			$queryLambda = function($id) { 
				return new ComicVine\Publisher($id); 
			};
			return new Collection($queryLambda, self::getClient()->zwapp->publishers);
		}

		public static function getCharacters() {
			$queryLambda = function($id) { 
				return new ComicVine\Character($id); 
			};
			return new Collection($queryLambda, self::getClient()->zwapp->characters);
		}
	}

?>