<?php
require( '../wp-load.php' );

function excerpt($limit) {
    $excerpt = explode(' ', get_the_excerpt(), $limit);
    if (count($excerpt)>=$limit) {
        array_pop($excerpt);
        $excerpt = implode(" ",$excerpt).'...';
    } else {
        $excerpt = implode(" ",$excerpt);
    }
    $excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
    return $excerpt;
}
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
<script type="text/javascript"
    src="http://maps.google.com/maps/api/js?sensor=true">
</script>




<script type="text/javascript">
  function initialize() {
      var myLatlng =  new google.maps.LatLng(-40.358072, -72.376791);<?php
    //setup river locations from DB
$args = array( 'numberposts' => -1, 'category_name' => 'map');
$posts= get_posts( $args );
if ($posts) {
	foreach ( $posts as $post ) {
	$postID = $post->ID;
	$putInLat = get_post_meta($post->ID, "Put In Lat", true);
	$putInLong = get_post_meta($post->ID, "Put In Long", true);
	if (($putInLat=="")||($putInLong==""))
			{continue;}

	echo "\n    var pos$postID =  new google.maps.LatLng($putInLat, $putInLong);";
}}

?>

    var myOptions = {
    zoom: 8,
    center: myLatlng,
    mapTypeId: google.maps.MapTypeId.TERRAIN
  }
  var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);


      /*
      add custom control to reset map  form        http://www.w3schools.com/googleAPI/google_maps_controls.asp see London example
      try Google Maps - 45° Perspective View
      */
<?php
$args = array( 'numberposts' => -1, 'category_name' => 'map');
$posts= get_posts( $args );
if ($posts) {
	foreach ( $posts as $post ) {
	setup_postdata($post);
	$postID = $post->ID;
	$postTitle = get_the_title();
	$permalink = get_permalink();
	//$excerpt =
	$Difficulty = get_post_meta($post->ID, "Difficulty", true);
	$Distance = get_post_meta($post->ID, "Distance", true);
	$ElevationDrop = get_post_meta($post->ID, "Elevation Drop", true);
	$gradient = round( $ElevationDrop / $Distance); 
	$feetPerMile = round ( 5.29 *( $ElevationDrop / $Distance));
	$putInLat = get_post_meta($post->ID, "Put In Lat", true);
	$putInLong = get_post_meta($post->ID, "Put In Long", true);
	$takeOutLat = get_post_meta($post->ID, "Take Out Lat", true);
	$takeOutLong = get_post_meta($post->ID, "Take Out Long", true);

	if (($putInLat=="")||($putInLong==""))
			{continue;}

     // get thumbnail image
     $thumbnail = "";
    if ( has_post_thumbnail() ) { // check if the post has a Post Thumbnail assigned to it.
        $thumbnail = get_the_post_thumbnail( $postID, 'thumbnail', array( 'class' => 'center' ) );
    }


?>


      <?php echo "//Build River info for River $postTitle \n";?>
      //content for infowindow
      var contentString<?php echo $postID;?> = '<div id="content"> <div id="siteNotice"></div> <h1 id="firstHeading" class="firstHeading"><?php echo $postTitle;?></h1>' +
          '<div id="bodyContent"> <?php echo $thumbnail;?><br/><?php  echo trim(excerpt(20)); ?><p><a target="_blank" href="<?php echo $permalink; ?>">River Details here</a></p></div></div>';
      //infowindow
      var infowindow<?php echo $postID;?> = new google.maps.InfoWindow({ content: contentString<?php echo $postID;?>, maxWidth: 150 });
      //marker
      <?php
	  echo "var marker$postID = new google.maps.Marker({ position: pos$postID,  map: map, title:'$postTitle' });\n";
	  ?>
      //listener
      google.maps.event.addListener(marker<?php echo $postID;?>, 'click', function () {
          infowindow<?php echo $postID;?>.open(map, marker<?php echo $postID;?>);
      });
      <?php
}  }
  ?>
   

  }

</script>
</head>
<body onload="initialize()">
  <div id="map_canvas" style="width:100%; height:100%"></div>
</body>
</html>