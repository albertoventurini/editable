<script src="jquery.js"></script>
<script src="ckeditor/ckeditor.js"></script>
<script>

// source: https://gist.github.com/oomlaut/1101534
jQuery.extend({
	parseQueryString: function() {
		var nvpair = {};
		var querystring = window.location.search.replace('?', '');
		var pairs = querystring.split('&');
		$.each(pairs, function(i, v) {
			var pair = v.split('=');
			nvpair[pair[0]] = pair[1];
		});
		
		return nvpair;
	}
});


$(document).ready(function() {

	$("#buttonSave").click(function() {
		var new_content = CKEDITOR.instances.pagecontent.getData();
		var new_title = $('#pagetitle').text();
		var qs = jQuery.parseQueryString();
		
		var page;
		if('id' in qs) page = 'id=' + qs['id'];
		if('path' in qs) page = 'path=' + qs['path'];

        $.ajax({
			url: 'controller.php?action=update&' + page,
			type: 'POST',
			data: { 'content': new_content, 'title': new_title },
			complete: function() {
				window.location.href = "controller.php?action=show&" + page;
			}
		});
	});

});

</script>


<?php include "sidebar.php"; ?>


<div id="mainpanel">	




<div id="pageheader">

<div id="pagebuttons">
<button id="buttonSave" class="buttonAction" style="float:right;">Save</button>
<form method="get" action="controller.php" style="float:right;">
<input type="hidden" name="action" value="show" />
<input type="hidden" name="id" value="<?php echo $page->get_id(); ?>" />
<button id="buttonCancel" class="buttonAction">Cancel</button>
</form>
</div>





<div id="pagetitle" class="editable" contenteditable="true">
<?php echo $page->get_title(); ?>
</div>


<div id="pageinfo">
<p></p>
</div>


</div> <!-- pageheader -->




<div id="pagecontent" class="editable" contenteditable="true">
<?php
$content = $page->get_content();
if(empty($content)) echo "<p></p>";
else echo $content;
?>
</div>

<script>
CKEDITOR.disableAutoInline = true;
CKEDITOR.inline("pagecontent");
</script>


</div> <!-- mainpanel -->
