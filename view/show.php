<script src="jquery.js"></script>
<?php include "sidebar.php"; ?>
	
<div id="mainpanel">



<div id="pageheader">


<div id="pagebuttons">
<form method="get" action="controller.php">
<input type="hidden" name="action" value="edit" />
<input type="hidden" name="id" value="<?php echo $page->get_id(); ?>" />
<button id="buttonEdit" class="buttonAction" type="submit" style="float:right;">Edit</button>
</form>
</div>



<div id="pagetitle">
<?php echo $page->get_title(); ?>
</div>

<div id="pageinfo">
Last updated: <?php echo $page->get_last_updated(); ?>
</div>

</div>


<div id="pagecontent">
<?php
echo $page->get_content();
?>

</div>

</div> <!-- mainpanel -->


<!-- Script to toggle sidebar
<script>
$('#sidebar').toggle();
var styles = {
      marginLeft : "0",
      borderLeft: "#FFFFFF"
    };
$('#mainpanel').css( styles );
</script>
-->
