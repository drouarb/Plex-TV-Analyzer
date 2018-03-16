<?php
include_once('tvclass.php');
if(!empty($_POST['show_id'])) {
	$show_id = intval($_POST['show_id']);
	$show_name = $_POST['show'];
	$lang = $_POST['lang'];
	$shows = TVAnalyzer::AnalyzeShow($show_name,$show_id, $lang);
	if(!is_array($shows)) {
	echo $shows;
	exit();
	}
 	foreach($shows as $show)
 	{
 		foreach($show->Episodes as $epi) {
 			echo '<tr';
 			if($epi->Missing) echo ' class="table-danger"';
 			echo '>
 				<td class="episode">S'. str_pad($epi->SeasonNumber, 2, "0", STR_PAD_LEFT)
						. 'E' . str_pad($epi->EpisodeNumber, 2, "0", STR_PAD_LEFT).'</td>
 				<td>'.$epi->EpisodeName.'</td>
 				<td>';
 				echo $epi->Missing ? 'Missing!' : 'Yes';
 				echo '</td>
 				</tr>';
 		}
    }
}
?>
