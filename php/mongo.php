<?php
	namespace ZwappMongo;
	// require 'vendor/autoload.php'; // include Composer's autoloader

	use MongoDB;
	use ComicVine;

	require_once '/Development/zwapp/public/php/init_logger.php';

	// print_r("Logger:");
	// var_dump($logger);
	// $logger->debug("Logger in mongo.php");

	// Collections in Zwapp are initialized via curated scrapes from the ComicVine wiki. 
	// Accessing Zwapp DB objects transparently get and cache properties needed from the ComicVine API.
	
	class Document {
        // Make members public so not accessed via __get & __set:
		public $children;
        public $cv_query, $collection, $getChildId, $logger;
		private static function getChildId_default($child) { 
			return $child->id; 
		}

		function __construct($cv_query, MongoDB\Collection $collection, $getChildId) {
			$this->cv_query = $cv_query;
			$this->collection = $collection;
			$this->getChildId = $getChildId;
			global $logger;
			$this->logger = $logger;
		}

		function __get($property) {
			// id accessible via cv_query:
			if ($property == "id") {
				return $this->cv_query->id;
			}
			$doc = $this->collection->findOne(['_id' => $this->id]);

			if (is_null($doc["cv_init"])) {
				$doc = $this->setData();
			}

			return $doc[$property];
		}

        function __set($property, $value) {
            $this->collection->updateOne(['_id' => $this->id],['$set'=>array($property=>$value)]);
        }

		function setData() {
			$this->logger->debug("SETTING DATA!!!!");
			$doc = array('_id' => $this->id);
			foreach ($this->cv_query->to_array() as $prop => $value) {
				$doc[$prop] = $value;
			}
			$doc["cv_init"] = "true";

			$this->collection->updateOne(['_id' => $this->id],['$set'=>$doc]);

			return $doc;
		}

        function getChildren() {
            $doc = $this->collection->findOne(['_id' => $this->id]);
            print_r("\nDoc: ");
            var_dump($doc);
            // $test = $doc['children'];
            // $this->logger->debug("db children: $test");
            $children = [];
            $children_doc = $doc['children'];
            if (!is_null($children_doc)) {
                $doc_count = $children_doc->count();
                $this->logger->debug("getChildren doc count: $doc_count");
                foreach($children_doc as $id=>$child) {
                    $children[$id] = $child;
                    // $this->logger->debug("Id: $id, Child: $child");
                }
            } else {
                // $doc_children = $doc->children;
                $this->logger->debug("GETTING CHILDREN!!!!");
                $children = [];
                $cv_children = $this->cv_query->getChildren();
                $childIdGetter = $this->getChildId;
                foreach($cv_children as $child) {
                    // save as a map of name values keyed by id:
                    $index = $this->getChildId ? $childIdGetter($child) : self::getChildId_default($child);
                    $children[$index] = $child->name;
                }

                $this->collection->updateOne(['_id' => $this->id],['$set'=>array('children'=>$children)]);
            }

            return $children;
        }

        function getListProp($prop) {
            $doc = $this->collection->findOne(['_id' => $this->id]);
            // print_r("\nDoc: ");
            // var_dump($doc);
            // $test = $doc['children'];
            // $this->logger->debug("db children: $test");
            $list = [];
            $list_doc = $doc[$prop];
            if (!is_null($list_doc)) {
                $doc_count = $list_doc->count();
                $this->logger->debug("getlist doc count: $doc_count");
                foreach($list_doc as $id=>$item) {
                    $list[$id] = $item;
                    // $this->logger->debug("Id: $id, Child: $child");
                }
            } else {
                // $doc_children = $doc->children;
                $this->logger->debug("GETTING CHILDREN!!!!");
                $list = [];
                $cv_list = $this->cv_query->getProp($prop);
                $listItemIdGetter = $this->getListItemId;
                foreach($cv_list as $item) {
                    // save as a map of name values keyed by id:
                    $index = $this->getListId ? $listItemIdGetter($child) : self::getChildId_default($item);
                    $list[$index] = $item->name;
                }

                $this->collection->updateOne(['_id' => $this->id],['$set'=>array($prop=>$list)]);
            }

            return $list;
        }

		/***************************************
		* Get the top children of this document in sort order according to the children's collection
		****************************************/
		function getTopChildren($length = 9) {
			$time1 = microtime(TRUE);
			// $topChildren = [];
			$this->logger->debug("getTopChildren...");
			$childrenIds = array_keys($this->getChildren());
			// $childrenIds = array_keys($this->getChildren());
			// $childCollectionList = [];
			// $count = count($childrenIds);
			$isnull = is_null($childrenIds);
			// $class = get_class($childrenIds);
			// $methods = join(get_class_methods($class), ",");
			$this->logger->debug("getTopChildren2: childrenIds: $isnull");

			// // Get the collection list for the child type
			// TODO: ideally more seamless, but for now, do via a switch statement
			$children_prop = $this->cv_query->children_prop;
			$childCollection;
			$time2 = microtime(TRUE);

			switch ($children_prop) {
				case 'characters':
					$childCollection = Collection::getCharacters();
					break;
                case 'volumes':
                    $childCollection = Collection::getVolumes();
                    break;

				default:
					throw new \Exception("No handler for child prop \"$children_prop\"");
					// throw new \Exception("No handler for child prop \"{$this->cv_query->children_prop}\"");
					# code...
					break;
			}
			$time3 = microtime(TRUE);
			$this->logger->debug("getTopChildren3");

			$topChildren = array_slice($childCollection->getTopMatches($childrenIds), 0, $length);
			$this->logger->debug("getTopChildren4");

			$time4 = microtime(TRUE);

			$diff1 = $time2 - $time1;
			$diff2 = $time3 - $time2;
			$diff3 = $time4 - $time3;
			$this->logger->debug("getChildren: $diff1");
			$this->logger->debug("getCharacters: $diff2");
			$this->logger->debug("getTopMatches: $diff3");
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

		public function getTopMatches($id_array) {
			// $id_array = [];
			global $logger;
			$isnull = is_null($id_array);
			$logger->debug("getTopMatches: id_array null? $isnull");
			$type = gettype($id_array);
			$logger->debug("getTopMatches: id_array is a $type");
			// $id_test = join($id_array, ",");
			// $logger->debug("getTopMatches(id_array) : $id_test");
			$cursor = $this->collection->aggregate([['$match' => ['_id' => ['$in' => $id_array]]], ['$sort' => ['sort' => 1]]]);

			$top_matches = [];
			$map = $this->getMap();

			foreach($cursor as $match) {
				$top_matches[] = $map[$match->_id];
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

        /*
         * Maps out the chracter--volume relationships for the given publisher ID, 
         * and determine the relative weights of the characters' significance within the publisher.
         */
        private static function mapPublisherCharactersVolumes($publisher) {
            // Test to make sure only called once:
            if (is_null($publisher->volumes)) {
                $publisher_volumes = array_keys($publisher->getListProp("volumes")); // List of volume id's
                
                $volumes = self::getVolumes()->getMap();
                $characters = self::getCharacters()->getMap();

                foreach($publisher_volumes as $volume_id) {
                    $volume = $volumes[$volume_id];
                    $character_credits = $volume->getListProp("character_credits");
                    $place = 1;
                    foreach($character_credits as $character_credit) {
                        // Consider first 20 characters to be potentially main characters
                        if ($place > 20) {
                            break;
                        }

                        $character = $characters[$character_credit->id];

                        // Record the volume and place for character (the place determines the significance of the volume to the character):
                        $character_volumes = $character->volumes;
                        if (is_null($character_volumes)) {
                            $character_volumes = [];
                            $character->volumes = $character_volumes;
                        }
                        $character_volumes[$volume_id] = $place;

                        // Add to the sum of appearances for the character (determines the significance of the character to the publisher):
                        $character_appearances_count = $character->appearances_count;
                        if (is_null($character_appearances_count)) {
                            $character_appearances_count = 0;
                        }
                        $character->appearances_count = $character_appearances_count + $character_credit->count;

                        $place++;
                    }
                }
            }
        }

        private static function getPublisherCharacters(Document $publisher) {
            // Init the mapping:
            self::mapPublisherCharactersVolumes($publisher);

            // Find the characters for this publisher sorted by appearances_count:
            $collection = self::getClient()->zwapp->characters;
            $cursor = $characters.aggregate([['publisher->id' => $publisher->id], ['$sort' => ['appearances_count' => 1]]]);
        }

        private static function getCharacterVolumes(Document $character) {
            if (is_null($character->volumes)) {
            }

            return $character->volumes;
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
            $getChildren = function() {

            };
            return new Collection($queryLambda, self::getClient()->zwapp->characters);
        }

        public static function getVolumes() {
            $queryLambda = function($id) { 
                return new ComicVine\Volume($id); 
            };
            return new Collection($queryLambda, self::getClient()->zwapp->volumes);
        }
    }

?>