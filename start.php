<?php

define("WS_PACK_API_NO_RESULTS", -100);
define("WS_PACK_API_REGISTRATION_DISABLED", -110);

require_once(dirname(__FILE__) . "/lib/events.php");
require_once(dirname(__FILE__) . "/lib/functions.php");
require_once(dirname(__FILE__) . "/lib/hooks.php");

elgg_register_event_handler("plugins_boot", "system", "ws_pack_plugins_boot");
elgg_register_event_handler("init", "system", "ws_pack_init");
elgg_register_event_handler("pagesetup", "system", "ws_pack_pagesetup");

/**
 * Validate a given SSO secret as soon as possible
 * 
 * @return void
 */
function ws_pack_plugins_boot() {
	// check for sso login
	if (!elgg_is_logged_in()) {
		if (($user_guid = get_input("u", false)) && ($sso_secret = get_input("s", false))) {
			if (ws_pack_validate_sso_secret($user_guid, $sso_secret)) {
				// valid user, login
				try {
					$user = get_user($user_guid);
					login($user);
				} catch(Exception $e) {
					// something went wrong, continue
				}
			}
		}
	}
}

/**
 * Initialize Elgg, prepare some libraries
 *
 * @return void
 */
function ws_pack_init() {
	
	// register libraries
	elgg_register_library("ws_pack.auth", dirname(__FILE__) . "/lib/webservices/auth.php");
	elgg_register_library("ws_pack.river", dirname(__FILE__) . "/lib/webservices/river.php");
	elgg_register_library("ws_pack.groups", dirname(__FILE__) . "/lib/webservices/groups.php");
	elgg_register_library("ws_pack.users", dirname(__FILE__) . "/lib/webservices/users.php");
	elgg_register_library("ws_pack.system", dirname(__FILE__) . "/lib/webservices/system.php");
	
	elgg_register_library("simple_html_dom", dirname(__FILE__) . "/vendors/simplehtmldom/simple_html_dom.php");
	
	// add subtype class
	add_subtype("object", APIApplication::SUBTYPE, "APIApplication");
	add_subtype("object", APIApplicationUserSetting::SUBTYPE, "APIApplicationUserSetting");
	
	// register plugin hooks
	elgg_register_plugin_hook_handler("register", "menu:ws_pack:applications", "ws_pack_applications_menu_hook_handler");
	elgg_register_plugin_hook_handler("register", "menu:entity", "ws_pack_entity_menu_hook_handler");
	elgg_register_plugin_hook_handler("register", "menu:annotation", "ws_pack_annotation_menu_hook_handler");
	elgg_register_plugin_hook_handler("rest", "init", "ws_pack_rest_init_hook_handler");
	elgg_register_plugin_hook_handler("api_key", "use", "ws_pack_api_key_use_hook_handler");
	elgg_register_plugin_hook_handler("container_permissions_check", "object", "ws_pack_container_write_hook_handler");
	
	// register event handlers
	elgg_register_event_handler("created", "river", "ws_pack_created_river_event_handler");
	
	// register actions
	elgg_register_action("ws_pack/application/activate", dirname(__FILE__) . "/actions/application/activate.php", "admin");
	elgg_register_action("ws_pack/application/deactivate", dirname(__FILE__) . "/actions/application/deactivate.php", "admin");
	elgg_register_action("ws_pack/application/disable", dirname(__FILE__) . "/actions/application/disable.php", "admin");
	elgg_register_action("ws_pack/application/delete", dirname(__FILE__) . "/actions/application/delete.php", "admin");
	
	elgg_register_action("ws_pack/push_service/delete", dirname(__FILE__) . "/actions/push_service/delete.php", "admin");
	elgg_register_action("ws_pack/push_service/delete_user", dirname(__FILE__) . "/actions/push_service/delete_user.php");
	
	// register shutdown function
	register_shutdown_function("ws_pack_shutdown_user_counter");
}

/**
 * Perform actions during page setup
 * 
 * @return void
 */
function ws_pack_pagesetup() {
	elgg_register_admin_menu_item('administer', 'ws_pack', 'administer_utilities');
}
