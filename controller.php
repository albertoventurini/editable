<?php
require "model.php";


function fetch_page()
{
	try
	{
		if(array_key_exists("id", $_GET))
			$page = Page::find($_GET["id"]);
		elseif(array_key_exists("path", $_GET))
			$page = Page::find_by_path($_GET["path"]);
		else
			die("Page not specified");
	}
	catch (Exception $e) { die("Exception: " . $e->getMessage()); }
	
	return $page;
}

function fetch_pagelist()
{
	try { $pagelist = build_pagelist(); }
	catch (Exception $e) { die("Exception: " . $e->getMessage()); }
	
	return $pagelist;
}


function action_new()
{
	echo "Hello new!";
}

function action_create()
{
	$title = $_GET["title"];
	$parent_id = $_GET["parent_id"];
	
	// to add: error handling
	if(empty($title))
		die("Error creating page: title not specified");
	
	if(empty($parent_id))
		die("Error creating page: parent_id not specified");
	
	$page = Page::create($title, $parent_id);
	
	try { $page->save(); }
	catch (Exception $e) { die("Exception: " . $e->getMessage()); }
	
	// redirect to show action
	header("Location: " . $_SERVER["SCRIPT_NAME"] . "?action=show&id=" . $page->get_id());
}

function action_show()
{
	$page = fetch_page();
	$pagelist = fetch_pagelist();
	
	include "view/preamble.html";
	include "view/show.php";
	include "view/postscript.html";
}

function action_edit()
{
	$page = fetch_page();
	$pagelist = fetch_pagelist();
	
	include "view/preamble.html";
	include "view/edit.php";
	include "view/postscript.html";
}

function action_update()
{
	$page = fetch_page();
	
	if(array_key_exists("title", $_POST))
		$page->set_title($_POST["title"]);
	
	if(array_key_exists("content", $_POST))
		$page->set_content($_POST["content"]);
	
	try { $page->save(); }
	catch (Exception $e) { die("Exception: " . $e->getMessage()); }
	
	// redirect to show action
	//header("Location: " . $_SERVER["SCRIPT_NAME"] . "?action=show&id=" . $page->get_id());
}

function action_delete()
{
	echo "Hello delete!";
}

function action_search()
{
	echo "Hello search!";
}


////////////////////////////////////////////////////////////////////////////


$action = $_GET["action"];

switch($action)
{
	case "new": action_new(); break;
	case "create": action_create(); break;
	case "show": action_show(); break;
	case "edit": action_edit(); break;
	case "update": action_update(); break;
	case "delete": action_delete(); break;
	case "search": action_search(); break;
}

?>
