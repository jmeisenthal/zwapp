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
		private static function getChildId_default($child) { 
			return $child->id; 
		}

		function __construct($cv_query, MongoDB\Collection $collection, $getChildId) {
			$this->cv_query = $cv_query;
			$this->collection = $collection;
			$this->getChildId = $getChildId;
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
				$childIdGetter = $this->getChildId;
				foreach($cv_children as $child) {
					// save as a map of name values keyed by id:
					$index = $this->getChildId ? $childIdGetter($child) : self::getChildId_default($child);
					$children[$index] = $child->name;
				}

				$this->collection->updateOne(['_id' => $this->id],['$set'=>array('childen'=>$children)]);
			}

			return $children;
		}

		/***************************************
		* Get the top children of this document in sort order according to the children's collection
		****************************************/
		function getTopChildren($size = 9) {
			$topChildren = [];
			$childrenIds = $this->getChildren();
			$childCollectionList = [];

			// Get the collection list for the child type
			// TODO: ideally more seamless, but for now, do via a switch statement
			$children_prop = $this->cv_query->children_prop;

			switch ($children_prop) {
				case 'characters':
					$childCollectionList = Collection::getCharacters()->getList();
					break;
				
				default:
					throw new \Exception("No handler for child prop \"$children_prop\"");
					// throw new \Exception("No handler for child prop \"{$this->cv_query->children_prop}\"");
					# code...
					break;
			}

			foreach($childCollectionList as $child_doc) {
				if (array_key_exists($child_doc->id, $childrenIds)) {
					// $topChildren[] = $child_doc;
					// print_r("Found child {$child_doc->id}\n");
					// if (count($topChildren) === $size) {
					// 	return $topChildren;
					// }
				}
				else {
					// print_r("Not found child {$child_doc->id}\n");

				}
			}
			return $topChildren;
		}
	}

	class Collection {
		static $client = NULL;
		// private static function getChildId_default($child) { 
		// 	return $child->id; 
		// }
		private $cv_queryLambda,$collection;
		private $list, $map;

		function __construct($cv_queryLambda, MongoDB\Collection $collection, $getChildId = NULL) {
			$this->cv_queryLambda = $cv_queryLambda;
			$this->collection = $collection;
			$this->getChildId = $getChildId;
		}

		private function init() {
			if ($this->list == NULL) {
				$this->list = array();
				$this->map = array();
				$cursor = $this->collection->aggregate([['$sort' => ['sort' => 1]]]);
				// Can't call lambda as a member directly
				$cv_creator = $this->cv_queryLambda;
				foreach($cursor as $data) {
					$document = new Document($cv_creator($data->_id), $this->collection, $this->getChildId);
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

		function getTopMatches($id_array) {
			$cursor = $this->collection->aggregate([['$match' => ['_id' => ['$in' => $id_array]]], ['$sort' => ['sort' => 1]]]);
			// var_dump($cursor);
			$top_matches = [];
			$map = $this->getMap();

			foreach($cursor as $match) {
					// array_push($this->list, $document);
				// array_push($top_matches, $match);
				$top_matches[] = $map[$match->_id];
				print_r("\nMatch id? {$map[$match->_id]->id}\n");
			}
			return $top_matches;
			// $cursor = $this->collection->aggregate([['$match' => ['id' => ['$in' => $id_array]]], ['$sort' => ['sort' => 1]]]);
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
			$getChildId = function($child) { return "4005-" . $child->id; };
			return new Collection($queryLambda, self::getClient()->zwapp->publishers, $getChildId);
		}

		public static function getCharacters() {
			$queryLambda = function($id) { 
				return new ComicVine\Character($id); 
			};
			return new Collection($queryLambda, self::getClient()->zwapp->characters);
		}
	}

?>