<?php

/*
Plugin Name: unAPI Server
Plugin URI: http://www.lackoftalent.org/michael/blog/unapi-wordpress-plug-in/
Description: Implements unAPI 1.0 specification, providing machine-readable metadata records for posts and pages.  Hat tip: <a href="http://www.wallandbinkley.com/quaedam/" target="_blank">Peter Binkley</a> for writing the first unAPI plug-in, on which subsequent versions have been heavily based.
Version: 1.2
Author: Michael J. Giarlo
Author URI: http://purl.org/net/leftwing/blog
Contributor: Peter Binkley
Contributor URI: http://www.wallandbinkley.com/quaedam/
Contributor: Jonathan Brinley
Contributor URI: http://xplus3.net/

unAPI Server for Wordpress
Copyright (C) 2006  Peter Binkley and Michael J. Giarlo (leftwing@alumni.rutgers.edu)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

HISTORY:
Version: 0.1, 2006-02-18 [Peter Binkley]
Version: 0.2, 2006-05-18 [Michael J. Giarlo]
Version: 0.3, 2006-06-28 "
Version: 1.0, 2007-01-01 "
Version: 1.1, 2007-01-07 "
Version: 1.2, 2007-12-19 [Jonathan Brinley]
*/

add_action('wp_head', 'unapi_link');
add_action('admin_head', 'unapi_js');
add_action('admin_menu', 'unapi_admin_menu');

add_filter('the_content', 'unapi_abbr');

add_option('unapi_idPrefix', 'unapi-id:', 'An arbitrary identifier prefix for objects to be published via unAPI');
add_option('unapi_usePermalink', 'on', 'Set to on to use WP permalinks as unAPI identifiers - the default behavior');

function unapi_abbr($content) {
	global $wp_query;
	$idPrefix = (get_option('unapi_usePermalink') == 'on') ? 
		get_bloginfo('wpurl') . "/?p=" . $wp_query->post->ID :
		get_option('unapi_idPrefix') . $wp_query->post->ID;
	return '<abbr class="unapi-id" title="' . $idPrefix . '">' .
		"<!-- &nbsp; --></abbr>\n" .
		$content;
}

function unapi_link() {
	echo "	<!-- unAPI -->\n";
	echo '	<link rel="unapi-server" type="application/xml" title="unAPI" href="' .
		get_bloginfo('wpurl') .
		'/wp-content/plugins/unapi/server.php"/>' .
		"\n";
}

function unapi_js() {
	echo "<!-- javascript field toggler -->\n";
	echo '<script type="text/javascript">' . "\n<!--\n" .
		"function permalinkToggle() {document.form1.unapi_idPrefix.disabled = !document.form1.unapi_idPrefix.disabled;}\n -->\n" .
		"</script>\n";
}

function unapi_admin_menu() {
	if ( function_exists('add_options_page') ) {
		add_options_page('unAPI Configuration', 'unAPI', 9, __FILE__, 'unapi_manage');
	}
}

function unapi_manage() {
	if ( isset($_POST['unapi_idPrefix']) || isset($_POST['unapi_usePermalink']) ) {
		update_option('unapi_idPrefix', $_POST['unapi_idPrefix']);
		update_option('unapi_usePermalink', $_POST['unapi_usePermalink']);
		echo '<div class="updated"><p><strong>Options saved.</strong></p></div>';
	}
	$idPrefix = get_option('unapi_idPrefix');
	$checked = ('on' == get_option('unapi_usePermalink')) ? ' checked="checked" ' : '';
	$disabled = ('on' == get_option('unapi_usePermalink')) ? ' disabled="disabled" ' : '';
	echo '<div class="wrap"> ' .
		'<h2>unAPI Options</h2>' .
		'<form name="form1" method="post" action="' . $_SERVER['REQUEST_URI'] . '">' .
		'<fieldset class="options"><legend>Enter an identifier prefix</legend><br/>' .
		'<input type="text" size="75" name="unapi_idPrefix" ' . $disabled . 
			'value="' . $idPrefix . '"/>' .
		'<br/><br/><input id="upl" type="checkbox" name="unapi_usePermalink" ' . $checked .
			'onclick="permalinkToggle();"/> ' .
			'<label for="upl">Use Permalink</label>' .
		'</fieldset>' .
		'<p class="submit">' .
		'<input type="submit" name="Submit" value="Update Options &raquo;" />' .
		'</p>' .
		'</form>' .
		'</div>';
}
?>