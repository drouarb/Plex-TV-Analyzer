<?php
class TVAnalyzer {

	// CHANGE THIS URL TO POINT TO YOUR PLEX SERVER
	private static $PlexURL = 'http://localhost:32400';
	private static $PlexToken = "";

	private static $TVDBSearchURL = 'http://www.thetvdb.com/api/GetSeries.php?seriesname=';
	private static $TVDBLookURL = 'http://www.thetvdb.com/api/D3C42D98171EFD99/series/%s/all/%s.xml';
	public static function AnalyzeShow($show_name,$show_id, $lang) {
		if (!is_int($show_id))
			return 'null';
		$refshows = array();
		$show = TVAnalyzer::GetUserShowEpisodes($show_name,$show_id);
		$refshows[] = TVAnalyzer::GetTVDBShowEpisodes($show, $lang);
		return $refshows;
	}

	public static function GetUserShows() {
		$shows = array();
		$sectionUrl = TVAnalyzer::$PlexURL.'/library/sections';
		$xml = simplexml_load_string(TVAnalyzer::GetUrlSource($sectionUrl));
		foreach ($xml->Directory as $sec) {
			if ((string) $sec['type'] == 'show') {
				$shows[(string)$sec["title"]] = (string)$sec["language"];
				$secXML = simplexml_load_string(TVAnalyzer::GetUrlSource($sectionUrl.'/'.$sec['key'].'/all'));
				foreach ($secXML->Directory as $sho) {
					$shows["&nbsp;&nbsp;&nbsp;&nbsp;" . strval($sho['title'])] = $sho['ratingKey'];
				}

			}
		}
		return $shows;
	}

	private static function GetUserShowEpisodes($show_name,$show_id) {
		$show = new Show();
		$show->ShowName = $show_name;
		$show->Episodes = array();
		$showXML = simplexml_load_string(TVAnalyzer::GetUrlSource(TVAnalyzer::$PlexURL.'/library/metadata/'.$show_id.'/allLeaves'));
		foreach ($showXML->Video as $epi) {
			$newepisode = new Episode();
			$newepisode->EpisodeNumber = intval($epi['index']);
			$newepisode->SeasonNumber = intval($epi['parentIndex']);
			if (!TVAnalyzer::ContainsEpisode($newepisode, $show)) {
				$show->Episodes[] = $newepisode;
			}
		}
		return $show;
	}


/*$files = TVAnalyzer::GetFilesinFolder($folder_path);
// go through all the files and if they match the regex, add them to an array for reference

 foreach ($files as $file) {
 if (preg_match('/(.+)\ss([0-9]+)e([0-9]+)/ms', $file, $matches) < 1) */

private static function GetTVDBShowEpisodes($original_show, $lang) {

	// Url encode the name of the show
	$fixed_name = urlencode($original_show->ShowName);

	// create the search url by appending the show name onto the search url
	$show_url = TVAnalyzer::$TVDBSearchURL.$fixed_name."&language=".$lang;

	// Load the xml object from the source of the search result
	$xml = simplexml_load_string(TVAnalyzer::GetUrlSource($show_url));

	// Get the series id from the best match from TVDB
	$series_id = (string) $xml->Series[0]->seriesid;

	// Create a lookup url from the TVDB Id
	$lookup_url = sprintf(TVAnalyzer::$TVDBLookURL, $series_id, $lang);

	// Create an XML object from the source of the lookup
	$xml = simplexml_load_string(TVAnalyzer::GetUrlSource($lookup_url));

	// Create a new show object and load the member variables from teh xml object
	$show = new Show();
	$show->ShowName = strval($xml->Series->SeriesName);
	$show->TVDBId = intval($xml->Series->id);

	// Pass back the ID to the original show so the IDs match on a lookup
	$original_show->TVDBId = $show->TVDBId;

	// Create a new array for all teh episodes associated with this show
	$show->Episodes = array();

	// Go through the XML object and add an episode for each one in the XMl
	foreach ($xml->Episode as $episode) {
		if (intval($episode->SeasonNumber) < 1)
			continue;
		$newepisode = new Episode();
		$newepisode->EpisodeName = strval($episode->EpisodeName);
		$newepisode->EpisodeNumber = intval($episode->EpisodeNumber);
		$newepisode->SeasonNumber = intval($episode->SeasonNumber);
		if (!TVAnalyzer::ContainsEpisode($newepisode, $show)) {
			$newepisode->Missing = !TVAnalyzer::ContainsEpisode($newepisode, $original_show);
			$show->Episodes[] = $newepisode;
		}
	}
	return $show;
}

// Check to see if a show contains an episode
private static function ContainsEpisode($episode, $show) {
	// Go through all the episodes associated with the show and see if the episodes are from the same season and episode number
	foreach ($show->Episodes as $ep) {
		if ($ep->EpisodeNumber === $episode->EpisodeNumber && $ep->SeasonNumber === $episode->SeasonNumber) {
			return true;
		}
	}
	return false;
}

// Get the source for a specific URL
private static function GetUrlSource($url) {
	$request_url = parse_url($url);
	$plex = parse_url(TVAnalyzer::$PlexURL);
	if ($request_url["host"] == $plex["host"] && $request_url["port"] == $plex["port"] &&
		$request_url["scheme"] == $plex["scheme"] && TVAnalyzer::$PlexToken != "") {
		parse_str($parse_str['query'], $query);
		if (count($query) == 0)
			$url = $url . "?X-Plex-Token=" . TVAnalyzer::$PlexToken;
		else
			$url = $url . "&X-Plex-Token=" . TVAnalyzer::$PlexToken;
	}

	$session = curl_init($url);
	curl_setopt($session, CURLOPT_HEADER, false);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($session, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($session, CURLOPT_MAXREDIRS, 3);
	$response = curl_exec($session);
	curl_close($session);
	return $response;
}

}class Episode {
	public $EpisodeNumber;
	public $SeasonNumber;
	public $EpisodeName;
	public $Missing;
	public $ShowName;
}
class Show {
	public $ShowName;
	public $TVDBId;
	public $Episodes;
}

?>
