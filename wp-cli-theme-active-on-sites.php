<?php

/*
Plugin Name: WP-CLI Theme Active on Sites
Plugin URI:  https://github.com/twelch555/wp-cli-theme-active-on-sites
Description: A WP-CLI command to list all sites in a Multisite network that have activated a given theme
Version:     0.1
Author:      Troy Welch
License:     GPLv2
*/

/*
 * TODO
 *
 * Write unit tests
 *
 */

namespace WP_CLI\Theme\Active_On_Sites;
use WP_CLI;

if ( ! defined( 'WP_CLI' ) ) {
	return;
}

WP_CLI::add_command( 'theme active-on-sites', __NAMESPACE__ . '\invoke' );

/**
 * List all sites in a Multisite network that have activated a given theme.
 *
 * ## OPTIONS
 *
 * <theme_slug>
 * : The theme to locate
 *
 * [--field=<field>]
 * : Prints the value of a single field for each site.
 *
 * [--fields=<fields>]
 * : Limit the output to specific object fields.
 *
 * [--format=<format>]
 * : Render output in a particular format.
 * ---
 * default: table
 * options:
 *   - table
 *   - csv
 *   - ids
 *   - json
 *   - count
 *   - yaml
 * ---
 * ## AVAILABLE FIELDS
 *
 * These fields will be displayed by default for each blog:
 *
 * * blog_id
 * * url
 *
 * ## EXAMPLES
 *
 * wp theme active-on-sites chaplin
 *
 * @param array $args
 * @param array $assoc_args
 */
function invoke( $args, $assoc_args ) {
	reset_display_errors();

	list( $target_theme ) = $args;

	WP_CLI::line();
	pre_flight_checks( $target_theme );
	$found_sites = find_sites_with_theme( $target_theme );

	WP_CLI::line();
	display_results( $target_theme, $found_sites, $assoc_args );
}

/**
 * Re-set `display_errors` after WP-CLI overrides it
 *
 * Normally WP-CLI disables `display_errors`, regardless of `WP_DEBUG`. This makes it so that `WP_DEBUG` is
 * respected again, so that errors are caught more easily during development.
 *
 * Note that any errors/notices/warnings that PHP throws before this function is called will not be shown, so
 * you should still examine the error log every once in a while.
 *
 * @see https://github.com/wp-cli/wp-cli/issues/706#issuecomment-203610437
 */
function reset_display_errors() {
	add_filter( 'enable_wp_debug_mode_checks', '__return_true' );
	wp_debug_mode();
}

/**
 * Check for errors, unmet requirements, etc
 *
 * @param string $target_theme
 */
function pre_flight_checks( $target_theme ) {
	if ( ! is_multisite() ) {
		WP_CLI::error( "This only works on Multisite installations. Use `wp theme list -active` on regular installations." );
	}

	# if theme is installed on server
	if ( ! WP_CLI::launch_self( 'theme list', array(), array( 'format' => 'csv', 'field' => 'name', 'name' => $target_theme )  )  ) {
		WP_CLI::error( "$target_theme is not installed." );
	}

}

/**
 * Find the sites that have the theme activated
 *
 * @param string $target_theme
 *
 * @return array
 */
function find_sites_with_theme( $target_theme ) {
	$sites       = get_sites( array( 'number' => 10000 ) );
	$found_sites = array();
	$notify      = new \cli\progress\Bar( 'Checking sites', count( $sites ) );

	foreach ( $sites as $site ) {
		switch_to_blog( $site->blog_id );

		$active_theme = get_option( 'active_theme', array() );
		if ( is_array( $active_theme ) ) {
			$active_theme = array_map( 'dirname', $active_theme );
			if ( in_array( $target_theme, $active_theme, true ) ) {
				$found_sites[] = array(
					'blog_id' => $site->blog_id,
					'url'     => trailingslashit( get_site_url( $blog->blog_id ) ),
				);
			}
		}

		restore_current_blog();
		$notify->tick();
	}
	$notify->finish();

	return $found_sites;
}


/**
 * Display a list of sites where the theme is active
 *
 * @param string $target_theme
 * @param array  $found_sites
 * @param array $assoc_args
 */
function display_results( $target_theme, $found_sites, $assoc_args ) {
	if ( ! $found_sites ) {
		WP_CLI::line( "$target_theme is not active on any sites." );
		return;
	}

	if ( isset( $assoc_args['fields'] ) ) {
		$assoc_args['fields'] = explode( ',', $assoc_args['fields'] );
	} else {
		$assoc_args['fields'] = array( 'blog_id', 'url' );
	}

	WP_CLI::line( "Sites where $target_theme is active:" );

	$formatter = new \WP_CLI\Formatter( $assoc_args );
	$formatter->display_items( $found_sites );
}
