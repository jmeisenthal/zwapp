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
	private $children;
	private $field_list;
	private $id;

	function __construct($type, $field_list, $id = NULL)
	{
		$this->type = $type;
		$this->field_list = $field_list;
		// $this->query_data = self::ComicVine_KEY + $query_data;
		$this->id = $id;
	}

	function build_query_url($a_field_list):string {
		$query_params = http_build_query(self::ComicVine_KEY + ["format"=>"json", "field_list"=>$a_field_list]);
		$url = self::ComicVine_API . $this->type . ($this->id ? ('/' . $this->id) : '') .'/?' . $query_params;
		return $url;
	}

	function execute_query($a_field_list = NULL) {
		$a_field_list = $a_field_list ?: $this->field_list;
		$url = $this->build_query_url($a_field_list);
		$a_result = json_decode(file_get_contents($url))->results;
		return $a_result;
	}

	protected function get_result() {
		if (is_null($this->result)) {
			$this->result = $this->execute_query();
		}

		// error_log("Result name: " . print_r($this->result->name, true));
		return $this->result;
	}

	abstract public function getChildren();

	public function __get($prop) {
		return $this->get_result()->$prop;
	}

	abstract protected function getProperties();

	public function to_array() {
		$arr = [];
		foreach ($this->getProperties() as $value) {
			$arr[$value] = $this->$value;
		}
		return $arr;
	}
}

/**
 * 
 */
class Publisher extends Query
{
	
	function __construct($id)
	{
		parent::__construct('publisher', "image,id,name", $id);
		// parent::__construct('publisher', ["format"=>"json", "field_list"=>"image,id,name,characters"], $id);
	}

	protected function getProperties() {
		return ["id", "name", "icon_url"];
	}

	public function getChildren() {

	}

	public function __get($prop) {
		if ($prop == 'icon_url') {
			return $this->get_result()->image->icon_url;
		}
		return parent::__get($prop);
	}
}

/**
 * 
 */
class Character extends Query
{
	
	function __construct($id)
	{
		parent::__construct('publisher', ["format"=>"json", "field_list"=>"image,id,name"], $id);
	}

	public function getProperties() {
		return ["id", "name", "icon_url"];
	}

	public function getChildren() {
		
	}

	public function __get($prop) {
		if ($prop == 'icon_url') {
			return $this->get_result()->image->icon_url;
		}
		return parent::__get($prop);
	}
}

/**
 * 
 */
class Volume extends Query
{
	
	function __construct($id)
	{
		parent::__construct('publisher', ["format"=>"json", "field_list"=>"image,id,name"], $id);
	}

	public function getProperties() {
		return ["id", "name", "icon_url"];
	}

	public function getChildren() {
		
	}

	public function __get($prop) {
		if ($prop == 'icon_url') {
			return $this->get_result()->image->icon_url;
		}
		return parent::__get($prop);
	}
}

/**
 * 
 */
class Issue extends Query
{
	
	function __construct($id)
	{
		parent::__construct('publisher', ["format"=>"json", "field_list"=>"image,id,name"], $id);
	}

	public function getProperties() {
		return ["id", "name", "icon_url"];
	}

	public function getChildren() {
		
	}

	public function __get($prop) {
		if ($prop == 'icon_url') {
			return $this->get_result()->image->icon_url;
		}
		return parent::__get($prop);
	}
}

 ?>