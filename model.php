<?php


/////////////////////////////////////////////////////////////////////////////////////////////


class Database
{
	private $hostname;
	private $username;
	private $password;
	private $database;
	
	private $mysqli;
	
	public function __construct($hostname, $username, $password, $database)
	{
		$this->hostname = $hostname;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
	}
	
	public function connect()
	{
		$this->mysqli = new mysqli($this->hostname, $this->username, $this->password, $this->database);
		if ($this->mysqli->connect_errno)
			throw new Exception('Connect failed: '. $mysqli->connect_error);
	}

	public function query($query)
	{
		if(!$this->mysqli) throw new Exception("Database not initialized");
		
		$res = $this->mysqli->query($query);
		if(!$res) throw new Exception("Error executing query: " . $this->mysqli->error);
		
		return $res;
	}
	
	public function create_tables()
	{
		$query = "CREATE TABLE pages(
			content TEXT,
			last_updated TIMESTAMP,
			title VARCHAR(128),
			id INT UNSIGNED NOT NULL AUTO_INCREMENT KEY,
			parent_id INT UNSIGNED,
			list_node_expanded TINYINT,
			FULLTEXT(title,content)
		) ENGINE MyISAM";
		
		try
		{
			$this->query($query);
		}
		catch(Exception $e) { throw $e; }

	
		$query = "CREATE TABLE tags(
			name VARCHAR(128),
			id INT UNSIGNED NOT NULL AUTO_INCREMENT KEY
		) ENGINE MyISAM";

		try
		{
			$this->query($query);
		}
		catch(Exception $e) { throw $e; }
	
	
		$query = "CREATE TABLE page_tag(
			page_id INT UNSIGNED,
			tag_id INT UNSIGNED
		) ENGINE MyISAM";
		
		try
		{
			$this->query($query);
		}
		catch(Exception $e) { throw $e; }

	}
	
	public function drop_tables()
	{
		$query = "DROP TABLE pages, tags, page_tag";
		
		try
		{
			$this->query($query);
		}
		catch(Exception $e) { throw $e; }
	}
}


/////////////////////////////////////////////////////////////////////////////////////////////


class Page
{
	private $id;
	private $parent_id = 0; // parent_id = 0 means that the page has no parent (it is at the top level)
	private $title;
	private $content;
	private $content_changed = 0;
	private $exists_in_db = 0;
	private $last_updated;
	private $list_node_expanded;
	
	private $db;
	
	private static function db()
	{
		global $db;
		if($db == NULL) throw new Exception("Database not initialised");
		else return $db;
	}
	
	// constructor is private - use the two factory functions "create", "find" and "find_by_id" instead
	private function __construct()
	{
	}
	
	// creates a new page
	public static function create($title, $parent_id)
	{
		$page = new Page();
		$page->set_title($title);
		$page->set_parent_id($parent_id);
		$page->exists_in_db = 0;

		return $page;
	}
	
	
	// finds the page in the database and returns it
	public static function find($id)
	{
		$page = new Page();
		
		try
		{
			$res = Page::db()->query("SELECT id, title, content, parent_id, DATE_FORMAT(last_updated,'%e %M %Y, %H:%i') AS last_updated, list_node_expanded from pages WHERE id=" . (string)$id);
			if($res->num_rows == 0) throw new Exception("Page does not exist");
			
			$page->id = $id;
			
			$row = $res->fetch_assoc();
			$page->content = $row['content'];
			$page->title = $row['title'];
			$page->parent_id = $row['parent_id'];
			$page->last_updated = $row['last_updated'];
		}
		catch(Exception $e) { throw $e;	}
		
		$page->exists_in_db = 1;
		
		return $page;
	}
	
	// $path is a string formatted as "/node1/node2/.../pagetitle"
	public static function find_by_path($path)
	{
		$path_array = explode("/", ltrim($path, "/"));

		$track_id = 0;
		
		foreach($path_array as $node)
		{
			$track_parent = $track_id;
			$query = "SELECT id FROM pages WHERE title = '" . $node . "'";
			$query .= " AND parent_id = " . (string)$track_parent;
			
			try
			{
				$res = Page::db()->query($query);
			}
			catch(Exception $e) { throw $e;	}
			
			$row = $res->fetch_assoc();
			$track_id = $row['id'];
		}
		
		return Page::find($track_id);
	}	
	
	// saves a page that was already in the database
	private function save_existing()
	{
		$query = "UPDATE pages SET title = '" . $this->title . "'";
		
		if($this->content_changed)
			$query .= ", content = '" . $this->content . "'";
		
		$query .= " WHERE ID = " . $this->id;

		try
		{
			Page::db()->query($query);
		}
		catch(Exception $e) { throw $e; }
	}
	
	// saves a new page
	private function save_new()
	{
		$query = "INSERT INTO pages(title, content, parent_id) VALUES(";
		$query .= "'" . $this->title . "', ";
		$query .= "'" . $this->content . "', ";
		$query .= $this->parent_id;
		$query .= ")";
		
		try
		{
			Page::db()->query($query);
		}
		catch(Exception $e) { throw $e; }
		
		
		// fetches the id of the new page
		$query = "SELECT id, DATE_FORMAT(last_updated,'%e %M %Y, %H:%i') AS last_updated FROM pages WHERE title = '" . $this->title . "' AND parent_id = " . $this->parent_id;

		try
		{
			$res = Page::db()->query($query);
		}
		catch(Exception $e) { throw $e; }
		
		$row = $res->fetch_assoc();
		$this->id = $row["id"];
		$this->last_updated = $row["last_updated"];
		
		
		$this->exists_in_db = 1;
	}
	
	// saves page into the database
	public function save()
	{
		try
		{
			if($this->exists_in_db) $this->save_existing();
			else $this->save_new();
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	// drops page from the database
	public function delete()
	{
	}
	
	// gets page path
	public function path()
	{
		$path = "/" . $this->title;
		$track_parent_id = $this->parent_id;
		
		while($track_parent_id != 0)
		{
			$query = "SELECT parent_id, title FROM pages WHERE id = " . (string)$track_parent_id;
			
			try
			{
				$res = Page::db()->query($query);
			}
			catch(Exception $e) { throw $e;	}
			
			$row = $res->fetch_assoc();
			$path = "/" . $row["title"] . $path;
			$track_parent_id = $row["parent_id"];
		}
		
		return $path;
	}
	
	// getters/setters
	public function set_title($title)
	{
		$this->title = $title;
	}
	
	public function set_content($content)
	{
		$this->content = $content;
		$this->content_changed = 1;
	}
	
	public function get_title()
	{
		return $this->title;
	}
	
	public function get_content()
	{
		return $this->content;
	}
	
	public function set_parent_id($parent_id)
	{
		$this->parent_id = $parent_id;
	}
	
	public function get_parent_id()
	{
		return $this->parent_id;
	}
	
	public function get_id()
	{
		if($this->exists_in_db == 0) throw new Exception("Unable to retrieve id, the page is not in the database yet.");
		else return $this->id;
	}
	
	public function get_last_updated()
	{
		return $this->last_updated;
	}
}


/////////////////////////////////////////////////////////////////////////////////////////////

/*
function build_pagelist_recursive($parent_id)
{
	global $db;
	
	$node = array();
	
	try
	{
		$res = $db->query("SELECT id, title FROM pages WHERE parent_id = " . $parent_id);
	}
	catch(Exception $e) { throw $e; }
	
	while($row = $res->fetch_assoc())
	{
		$children = build_pagelist_recursive($row["id"]);
		if(!empty($children)) $row["children"] = $children;
		array_push($node, $row);
	}
	
	return $node;
	
}

// returns an array of elements with the specified parent_id
function build_pagelist_recursive2($parent_id, $rows)
{
	$node = array();
	
	foreach($rows as $row)
	{
		if($row["parent_id"] == $parent_id)
		{
			$children = build_pagelist_recursive2($row["id"]);
			if(!empty($children)) $row["children"] = $children;
			array_push($node, $row);
		}
	}
	
	return $node;
}


function build_pagelist_old()
{
	return build_pagelist_recursive(0);
}
*/

function build_pagelist_recursive(&$tree, $parent_list)
{
	foreach($tree as &$node)
	{
		$node_id = $node["id"]; // can I do $node->id ??
		if(isset($parent_list[$node_id]))
		{
			$node["children"] = $parent_list[$node_id];
			//var_dump($node["children"]);
			build_pagelist_recursive($node["children"], $parent_list);
		}
	}
}


function build_pagelist()
{
	global $db;

	try
	{
		$res = $db->query("SELECT id, title, parent_id FROM pages");
	}
	catch(Exception $e) { throw $e; }
	
	$parent_list = array();
	
	while($row = $res->fetch_assoc())
	{
		$parent_id = $row["parent_id"];
		$parent_list[$parent_id][] = $row;
	}
	
	$tree = $parent_list[0];
	build_pagelist_recursive($tree, $parent_list);

	
	return $tree;
	
}


/////////////////////////////////////////////////////////////////////////////////////////////



$db = new Database("localhost", "alberto", "alberto", "editable");

try
{
	$db->connect();
}
catch(Exception $e)
{
	die("Exception: " . $e->getMessage());
}


//build_pagelist2();

/*
try {
	$res = $db->query("SELECT * from pages;");
	while ($row = $res->fetch_assoc())
	{
		echo " content = " . $row['content'] . "\n";
	}
} catch(Exception $e) {
	die("Exception: " . $e->getMessage());
}
*/

//$page = Page::find($db, 1);
//echo $page->get_content();
//
//$page->set_title("new title");
//$page->save($db);


/*
$db->drop_tables();
$db->create_tables();

$page = Page::create("Home", 0);
$page->set_content("content for this new page");
$page->save();

$p2 = Page::create("nuova-pagina", 1);
$p2->save();

$p3 = Page::create("sotto-pagina", 2);
$p3->set_content("hello world!!!!!");
$p3->save();

$p4 = Page::find_by_path("/Home/nuova-pagina/sotto-pagina");
echo $p2->path();
*/

?>
