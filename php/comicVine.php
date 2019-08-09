<?php 

namespace ComicVine;
	ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0)'); 

/**
 * 
 */
abstract class Query
{
	const ComicVine_API = 'https://comicvine.gamespot.com/api/';
	const ComicVine_KEY = ['api_key'=>'a587d36b7356338e04d0b132d105c9384a11691a'];

	private $type;
	private $result;
	private $query_data;
	private $id;

	function __construct($type, $query_data, $id = NULL)
	{
		$this->type = $type;
		$this->query_data = self::ComicVine_KEY + $query_data;
		$this->id = $id;
	}

	private function execute_query() {
		$url = self::ComicVine_API . $this->type . ($this->id ? ('/' . $this->id) : '') .'/?' . http_build_query($this->query_data);
		$this->result = json_decode(file_get_contents($url))->results;
		// Should check for errors etc here:
		
	}

	protected function get_result() {
		if (is_null($this->result)) {
			$this->execute_query();
		}

		error_log("Result name: " . print_r($this->result->name, true));
		return $this->result;
	}

	// public function __get($name) {
	// 	return $this->get_result()->name;
	// }
}

/**
 * 
 */
class Publisher extends Query
{
	
	function __construct($id)
	{
		parent::__construct('publisher', ["format"=>"json", "field_list"=>"image,id,name"], $id);
	}

	// public function __get($name) {
	// 	if ($name == 'icon_url') {
	// 		return $this->get_result()->image->icon_url;
	// 	}
	// 	return parent::__get($name);
	// }

	public function get_name() {
		return $this->get_result()->name;
	}

	public function get_id() {
		return $this->get_result()->id;
	}

	public function get_icon_url() {
		return $this->get_result()->image->icon_url;
	}
}

 ?>