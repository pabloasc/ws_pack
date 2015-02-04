<?php
	ws_pack_members_expose_functions();
	
	function ws_pack_members_expose_functions() {
		expose_function("members.get_friends",
		                "ws_pack_get_friends",
		                 array(),
		                 '',
		                 'GET',
		                 true,
		                 true
		                );
		
		expose_function("members.search_members",
		                "ws_pack_search_members",
		                 array(
							"search_str" => array(
								"type" => "string",
								"required" => true
							)
						 ),
		                 '',
		                 'GET',
		                 true,
		                 true
		                );
		expose_function("members.get_member",
		                "ws_pack_get_member",
		                 array(
							"guid" => array(
								"type" => "string",
								"required" => true
							)
						 ),
		                 '',
		                 'GET',
		                 true,
		                 true
		                );
	}



	function ws_pack_get_friends() {
	    $result = false;

	    $user = elgg_get_logged_in_user_entity();
		$api_application = ws_pack_get_current_api_application();
		
		if (!empty($user) && !empty($api_application)) {
			$params = array();
			$params["relationship"] = "friend";
			$params["friends"] = true;
			$params["type"] = "user";

			$search_results = elgg_get_entities_from_relationship($params);
			if ($search_results === false) {
				// error
			} else {
				$search_results["entities"] = ws_pack_export_entities($search_results);
				$result = new SuccessResult($search_results);
			}
		}
		
		if($result === false) {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		}
		
		return $result;
	}

	function ws_pack_search_members($search_str) {
	    $result = false;

	    $user = elgg_get_logged_in_user_entity();
		$api_application = ws_pack_get_current_api_application();
		
		if (!empty($user) && !empty($api_application)) {
			$params = array();
			$params["query"] = $search_str;
			$params["type"] = "user";

			$search_results = elgg_trigger_plugin_hook("search", "user", $params, array());
			if ($search_results === false) {
				// error
			} else {
				$search_results["entities"] = ws_pack_export_entities($search_results["entities"]);
				$result = new SuccessResult($search_results);
			}
		}
		
		if($result === false) {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		}
		
		return $result;
	}
	
	function ws_pack_get_member($guid) {
	    $result = false;

	    $user = elgg_get_logged_in_user_entity();
		$api_application = ws_pack_get_current_api_application();
		
		if (!empty($user) && !empty($api_application)) {
			
			$member_result = get_entity($guid);
			if ($member_result === false) {
				// error
			} else {
				$member_result = ws_pack_export_entity($member_result);
				$result = new SuccessResult($member_result);
			}
		}
		
		if($result === false) {
			$result = new ErrorResult(elgg_echo("ws_pack:error:notfound"));
		}
		
		return $result;
	}

?>