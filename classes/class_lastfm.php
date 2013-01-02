<? 
define('LASTFM_API_URL', 'http://ws.audioscrobbler.com/2.0/?method=');

class LastFM
{
	public static function get_artist_events($ArtistID, $Artist, $Limit = 15)
	{
		global $Cache;
		$ArtistEvents = $Cache->get_value('artist_events_' . $ArtistID);
		if (empty($ArtistEvents)) {
			$ArtistEvents = self::lastfm_request("artist.getEvents", array("artist" => $Artist, "limit" => $Limit));
			$Cache->cache_value('artist_events_' . $ArtistID, $ArtistEvents, 432000);
		}
		return $ArtistEvents;
	}

	public static function get_user_info($Username)
	{
		global $Cache;
		$Response = $Cache->get_value('lastfm_user_info_' . $Username);
		if (empty($Response)) {
			$Response = self::lastfm_request("user.getInfo", array("user" => $Username));
			$Cache->cache_value('lastfm_user_info_' . $Username, $Response, 86400);
		}
		return $Response;
	}

	public static function get_recent_tracks($Username, $Limit = 15)
	{
		global $Cache;
		$Response = $Cache->get_value('lastfm_recent_tracks_' . $Username);
		if (empty($Response)) {
			$Response = self::lastfm_request("user.getRecentTracks", array("user" => $Username, "limit" => $Limit));
			$Cache->cache_value('lastfm_recent_tracks_' . $Username, $Response, 7200);
		}
		return $Response;
	}

	public static function get_top_artists($Username, $Limit = 15)
	{
		global $Cache;
		$Response = $Cache->get_value('lastfm_top_artists_' . $Username);
		if (empty($Response)) {
			$Response = self::lastfm_request("user.getTopArtists", array("user" => $Username, "limit" => $Limit));
			$Cache->cache_value('lastfm_top_artists_' . $Username, $Response, 86400);
		}
		return $Response;
	}

	public static function get_top_albums($Username, $Limit = 15)
	{
		global $Cache;
		$Response = $Cache->get_value('lastfm_top_albums_' . $Username);
		if (empty($Response)) {
			$Response = self::lastfm_request("user.getTopAlbums", array("user" => $Username, "limit" => $Limit));
			$Cache->cache_value('lastfm_top_albums_' . $Username, $Response, 86400);
		}
		return $Response;
	}

	public static function get_top_tracks($Username, $Limit = 15)
	{
		global $Cache;
		$Response = $Cache->get_value('lastfm_top_tracks_' . $Username);
		if (empty($Response)) {
			$Response = self::lastfm_request("user.getTopTracks", array("user" => $Username, "limit" => $Limit));
			$Cache->cache_value('lastfm_top_tracks_' . $Username, $Response, 86400);
		}
		return $Response;
	}

	public static function compare_user_with($Username1, $Limit = 15)
	{
		global $Cache, $LoggedUser, $DB;
		$DB->query("SELECT username FROM lastfm_users WHERE ID='$LoggedUser[ID]'");
		if ($DB->record_count() > 0) {
			list($Username2) = $DB->next_record();
			//Make sure the usernames are in the correct order to avoid dupe cache keys.
			if (strcasecmp($Username1, $Username2)) {
				$Temp = $Username1;
				$UsernameA = $Username2;
				$Username1 = $Temp;
			}
			$Response = $Cache->get_value('lastfm_compare_' . $Username1 . '_' . $Username2);
			if (empty($Response)) {
				$Response = self::lastfm_request("tasteometer.compare", array("type1" => "user", "type2" => "user", "value1" => $Username1, "value2" => $Username2, "limit" => $Limit));
				$Cache->cache_value('lastfm_compare_' . $Username1 . '_' . $Username2, $Response, 86400);
			}
			return $Response;
		}
	}

	private static function lastfm_request($Method, $Args)
	{
		if (!defined('LASTFM_API_KEY')) {
			return false;
		}
		$Url = LASTFM_API_URL . $Method;
		if (is_array($Args)) {
			foreach ($Args as $Key => $Value) {
				$Url .= "&" . $Key . "=" . urlencode($Value);
			}
			$Url .= "&format=json&api_key=" . LASTFM_API_KEY;

			$Curl = curl_init();
			curl_setopt($Curl, CURLOPT_HEADER, 0);
			curl_setopt($Curl, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($Curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($Curl, CURLOPT_URL, $Url);
			$Return = curl_exec($Curl);
			curl_close($Curl);
			return json_decode($Return, true);
		}
	}
}

	
