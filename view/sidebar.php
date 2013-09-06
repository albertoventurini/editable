<!-- <script src="jstree/jquery.jstree.js"></script> -->


<div id="sidebar">

<?php

/*function render_pagelist_recursive($node)
{
	echo "<ul>";
	
	foreach($node as $element)
	{
		if(array_key_exists("id", $element))
		{
			echo "<li>";
			echo "<a href=\"controller.php?action=show&id=" . $element["id"] . "\">";
			echo $element["title"];
			echo "</a>";
			echo "</li>";
		}
		else
			render_pagelist_recursive($element);
	}
	
	echo "</ul>";
}*/


function render_pagelist_recursive($node)
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



render_pagelist_recursive($pagelist);

?>

</div>

<!--
<script>

$(function() {
	$("#sidebar").jstree({
		"plugins" : [ "themes", "html_data" ]
	});
});


</script>
-->
