<?php
require( '../wp-load.php' );
?>

<h1>River List</h1>

<?php

$args = array( 'numberposts' => -1);
$posts= get_posts( $args );
if ($posts) {
	foreach ( $posts as $post ) {
		setup_postdata($post);
		//setup additional stuff
		$postID = $post->ID;
		$postTitle = get_the_title();
		$permalink = get_permalink();
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
			continue;

			?>

<h2><?php echo $postTitle?> </h2>
<ul>
	<li>  Difficulty : <?php echo $Difficulty?> </li>
	<li>  Distance : <?php echo $Distance?> </li>
	<li>  Lat : <?php echo $putInLat?> </li>
	<li>  Long : <?php echo $putInLong?> </li>
	<?php

	if ( has_post_thumbnail() ) {
		the_post_thumbnail('thumbnail');
	}

	?>
	</ul>
<?php

	}
}


?>



