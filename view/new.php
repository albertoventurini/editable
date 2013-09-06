<?php

function render_pagelist_as_dropdown($node)
{
	echo "<ul>";
	
	foreach($node as $element)
	{
		echo "<li>";
		echo "<a href=\"controller.php?action=show&amp;id=" . $element["id"] . "\">";
		echo $element["title"];
		echo "</a>";

		if(array_key_exists("children", $element))
			render_pagelist_recursive($element["children"]);

		echo "</li>";
	}
	
	echo "</ul>";
}



render_pagelist_as_dropdown($pagelist);

?>

<div>

<form method="get" action="controller.php">
Title: <input type="text" name="Title" size="21" maxlength="120" />

</form>


</div>
