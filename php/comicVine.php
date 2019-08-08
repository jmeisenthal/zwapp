<?php 

namespace ComicVine;

/**
 * 
 */
abstract class Query
{
	const ComicVine_API = 'https://comicvine.gamespot.com/api/';
	const ComicVine_KEY = 'api_key=a587d36b7356338e04d0b132d105c9384a11691a';

	private $type;
	private $result;
	private $query_data;
	private $id;

	function __construct($type, $query_data, $id = NULL)
	{
		$this->type = $type;
		$this->query_data = $query_data;
		$this-id = $id;
	}

	private function execute_query() {
		$url = self::ComicVine_API . $type . ($id ? '/' . $id : '') .'/?' . self::ComicVine_KEY . http_build_query(query_data);
		$response = json_decode(file_get_contents($url));
		// Should check for errors etc here:
		
	}

	protected function get_result() {
		if (is_null($this->result)) {
			$this->result = $this->execute_query();
		}

		return $this->result;
	}
}

/**
 * 
 */
class Publisher extends Query
{
	
	function __construct($id)
	{
		parent::__construct('publisher', $id, ["format"=>"json", "field_list"=>"image,id,name"]);
	}

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