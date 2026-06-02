<?php
require( '../wp-load.php' );
$id = $_GET['id'];
query_posts( "p=$id" );
if (have_posts()) :
  while (have_posts()) : the_post();
	$postID = $post->ID;
	$Difficulty = get_post_meta($post->ID, "Difficulty", true);
	$Distance = get_post_meta($post->ID, "Distance", true);
	$ElevationDrop = get_post_meta($post->ID, "Elevation Drop", true);
	$gradient = round( $ElevationDrop / $Distance); 
	$feetPerMile = round ( 5.29 *( $ElevationDrop / $Distance));
	$putInLat = get_post_meta($post->ID, "Put In Lat", true);
	$putInLong = get_post_meta($post->ID, "Put In Long", true);
	$takeOutLat = get_post_meta($post->ID, "Take Out Lat", true);
	$takeOutLong = get_post_meta($post->ID, "Take Out Long", true);
  endwhile;  

 endif;
 
?>


<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<style type="text/css">
  html { height: 100% }
  body { height: 100%; margin: 0px; padding: 0px }
  #map_canvas { height: 100% }
</style>
<?php

?>
<script type="text/javascript"
    src="http://maps.google.com/maps/api/js?sensor=false">
</script>
<script type="text/javascript">
  function initialize() {
     var myLatlng =  new google.maps.LatLng(<?php echo $putInLat; ?>, <?php echo $putInLong; ?>);
	 var takeoutLatlng =  new google.maps.LatLng(<?php echo $takeOutLat; ?>, <?php echo $takeOutLong; ?>);
  var myOptions = {
    zoom: 12,
    center: myLatlng,
    mapTypeId: google.maps.MapTypeId.TERRAIN
  }
  var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
    
  var marker = new google.maps.Marker({
      position: myLatlng, 
      map: map, 
      title:"Put In"
  });
  
  var marker2 = new google.maps.Marker({
      position: takeoutLatlng, 
      map: map, 
      title:"Take Out"
  });
  
  }

</script>
</head>
<body onload="initialize()">
  <div id="map_canvas" style="width:100%; height:100%"></div>
</body>
</html>