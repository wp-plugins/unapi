<?php

/*
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
*/

require_once ('../../../wp-config.php');

// get parameters
$id = ( isset($_GET['id']) ) ? urldecode($_GET['id']) : null;
$format = ( isset($_GET['format']) ) ? urldecode($_GET['format']) : null;

// get blog name
$blogName = get_bloginfo('name');

$usesPermalink = $wp_rewrite->using_permalinks() && ( 'on' == get_option('unapi_usePermalink') );

$formatsList = array(
	'oai_dc',
	'rss',
	'marcxml',
	'srw_dc',
	'mods'
);

// validate format
if ( $format )
	if ( !in_array($format, $formatsList) )
		unapi_error(406);

// validate id 
if ( $id ) {
	if ( $usesPermalink ) {
		$id = $wpdb->get_var("SELECT ID FROM wp_posts WHERE guid='" . mysql_escape_string($id)  . "';");
		if ( !is_numeric($id) )
			unapi_error(404);			// bad identifier
	} else {
		if ( strpos($id, $idPrefix) === 0 )
			$id = substr($id, strlen($idPrefix)); 	// strip off prefix, leaving id of posting
		else
			unapi_error(404); 			// bad identifier (doesn't start with prefix)
	}

	// fetch post
	$post = get_post($id);
	if ( !is_object($post) )
		 unapi_error(404);			// no such post  //echo $post;
	else 
		if ( $post->post_status != 'publish' )
			unapi_error(404);		// post exists but hasn't been published, so treat as non-existent
}

// create XML for responses
$xmlHeader = '<?xml version="1.0" encoding="' . get_settings('blog_charset') . '"?>' . "\n";
$formats   = '<format name="marcxml" type="application/xml" docs="http://www.loc.gov/standards/marcxml/" />' . "\n";
$formats  .= '<format name="mods" type="application/xml" docs="http://www.loc.gov/standards/mods/" />' . "\n";
$formats  .= '<format name="oai_dc" type="application/xml" docs="http://www.openarchives.org/OAI/2.0/oai_dc.xsd" />' . "\n";
$formats  .= '<format name="rss" type="application/rss+xml" docs="http://www.rssboard.org/rss-2-0/" />' . "\n";
$formats  .= '<format name="srw_dc" type="application/xml" docs="http://www.loc.gov/standards/sru/dc-schema.xsd" />' . "\n";

// main brancher: select response depending on presence/absence of identifier and format
if ( $format )
	( $id ) ? unapi_type3url() : unapi_error(400);
else
	( $id ) ? unapi_type2url() : unapi_type1url();


/*
 * type1url (no identifier, no format): return list of formats 
 *
 *
 */
function unapi_type1url() {
	global $xmlHeader, $formats;
	header('Content-type: application/xml; charset=' . get_settings('blog_charset'), true);
	echo $xmlHeader .
		"<formats>\n" .
		$formats . 
		'</formats>';
} // type1url()

/*
 * type2url: identifier, no format - return list of formats for this identifier
 *
 *
 */
function unapi_type2url() {
	global $xmlHeader, $formats, $id, $idPrefix;
	header('Content-type: application/xml; charset=' . get_settings('blog_charset'), true);
	header('HTTP/1.0 300 Multiple Choices');
	echo $xmlHeader .
		'<formats id="'. $idPrefix . $id . '">' . "\n" .
		$formats .
		'</formats>';
} // type2url()

/*
 * type3url: identifier and format - return status 300 and multiple links
 *
 * Gathers necessary information such as author and blog name, and calls
 * the appropriate function to build the metadata record in the requested
 * format.
 */
function unapi_type3url() {
	global $xmlHeader, $formats, $id, $format, $post;
	$contentType = ( 'rss' == $format ) ? 'application/rss+xml' : 'application/xml';
	header('Content-type: ' . $contentType . '; charset=' . get_settings('blog_charset'), true);
	
	echo $xmlHeader;
	eval('unapi_show_' . $format . '();');
} // type3url()

/*
 * error - return error in status code
 *
 *
 */
function unapi_error($statusCode) {
	global $statusString;

	$statusString[400] = 'Bad Request';
	$statusString[404] = 'Not Found';
	$statusString[406] = 'Not Acceptable';

	header('HTTP/1.0 ' . $statusCode . ' ' . $statusString[$statusCode]);
	echo $statusCode . ' ' . $statusString[$statusCode];
	die();
} // error()

/*
 * output an oai_dc record from a post
 *
 *
 *
 */
function unapi_show_oai_dc() {
	global $id, $blogName;
	foreach(array_merge(get_posts('include=' . $id), get_pages('include=' . $id)) as $post) : setup_postdata($post);
?>
	<oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
		<dc:identifier><?php the_permalink_rss(); ?></dc:identifier>
		<dc:title><?php the_title_rss(); ?></dc:title>
		<dc:type>text</dc:type>
		<dc:creator><?php the_author(); ?></dc:creator>
		<dc:publisher><?php echo htmlspecialchars($blogName); ?></dc:publisher>
		<dc:date><?php the_modified_date('r'); ?></dc:date>
		<dc:format>application/xml</dc:format>
		<dc:language><?php echo get_option('rss_language'); ?></dc:language>
<?php
	foreach ( (array) get_the_category() as $cat ) {
?>
		<dc:subject scheme="local"><?php echo $cat->cat_name; ?></dc:subject>
<?php
	}
?>
		<dc:description>'<?php the_excerpt_rss();?>'</dc:description>
	</oai_dc:dc>
<?php 
	endforeach;
}

/*
 * output an RSS record from a post
 *
 *
 */
function unapi_show_rss() {
	global $id, $blogName;
	foreach(array_merge(get_posts('include=' . $id), get_pages('include=' . $id)) as $post) : setup_postdata($post);
?>
  <rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
      <title><?php echo htmlspecialchars($blogName); ?></title>
      <link><?php echo get_bloginfo('url'); ?></link>
      <description><?php echo htmlspecialchars(get_bloginfo('description')); ?></description>
      <pubDate><?php the_modified_date('r'); ?></pubDate>
      <language><?php echo get_option('rss_language'); ?></language>
      <item>
	 <title><?php the_title_rss(); ?></title>
         <link><?php the_permalink_rss(); ?></link>
	 <comments><?php echo get_permalink() . "#comments"; ?></comments>
	 <pubDate><?php the_modified_date('r'); ?></pubDate>
	 <dc:creator><?php the_author(); ?></dc:creator>
	<?php

	foreach ( (array) get_the_category() as $cat ) {
		echo "\t\t<category>" . $cat->cat_name . "</category>\n";
	}
?>
	 <guid isPermaLink="true"><?php the_permalink_rss(); ?></guid>
	 <description><![CDATA['<?php the_excerpt_rss();?>']]></description>
	 <wfw:commentRSS><?php echo get_permalink() . "feed/"; ?></wfw:commentRSS>
       </item>
     </channel>
   </rss>
<?php
	endforeach;
}

/*
 * output a mods record from a post
 *
 *
 *
 */
function unapi_show_mods() {
	global $id, $blogName;
	foreach(array_merge(get_posts('include=' . $id), get_pages('include=' . $id)) as $post) : setup_postdata($post);
?>
  <mods xmlns:xlink="http://www.w3.org/1999/xlink" version="3.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.loc.gov/mods/v3" xsi:schemaLocation="http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd">
	<titleInfo>
		<title><?php the_title_rss(); ?></title>
	</titleInfo>
	<name type="personal">
		<namePart><?php the_author(); ?></namePart>
	</name>
	<originInfo>
		<publisher><?php echo htmlspecialchars($blogName); ?></publisher>
		<dateIssued><?php the_modified_date('r'); ?></dateIssued>
	</originInfo>
	<language>
		<languageTerm authority="rfc3066" type="code"><?php echo get_option('rss_language'); ?></languageTerm>
	</language>
	<physicalDescription>
  	<form authority="marcform">electronic</form>
  	<digitalOrigin>born digital</digitalOrigin>
  	<reformattingQuality>access</reformattingQuality>
  	<internetMediaType>application/xml</internetMediaType>
	</physicalDescription>
        <typeOfResource>text</typeOfResource>
	<location>
		<url><?php the_permalink_rss(); ?></url>
	</location>
	<abstract>'<?php the_excerpt_rss(); ?>'</abstract>
        <subject authority="local">
        <?php
	foreach ( (array) get_the_category() as $cat ) {
		echo '                 <topic>' . $cat->cat_name . "</topic>\n";
	}
?>
        </subject>
  </mods>
<?php
	endforeach;
}

/*
 * output a marcxml record from a post
 *
 *
 *
 */
function unapi_show_marcxml() {
	global $id, $blogName;
	foreach(array_merge(get_posts('include=' . $id), get_pages('include=' . $id)) as $post) : setup_postdata($post);
?>
  <marc:record xmlns:marc="http://www.loc.gov/MARC21/slim">
	<marc:leader>nm 22 uu 4500</marc:leader>
	<marc:controlfield tag="008">s ||||||||||||||||||||||</marc:controlfield>
        <marc:datafield tag="041" ind1="0" ind2="7">
		<marc:subfield code="a"><?php echo get_option('rss_language'); ?></marc:subfield>
	        <marc:subfield code="2">rfc3066</marc:subfield>
	</marc:datafield>
	<marc:datafield tag="245" ind1="1" ind2="0">
        	<marc:subfield code="a"><?php the_title_rss(); ?></marc:subfield>
	</marc:datafield>
	<marc:datafield tag="260" ind1="" ind2="">
		<marc:subfield code="b"><?php echo htmlspecialchars($blogName); ?></marc:subfield>
		<marc:subfield code="c"><?php the_modified_date('r'); ?></marc:subfield>
	</marc:datafield>
	<marc:datafield tag="520" ind1="" ind2="">
                <marc:subfield code="a">'<?php the_excerpt_rss(); ?>'</marc:subfield>
	</marc:datafield>
	<marc:datafield tag="650" ind1="1" ind2="">
        <?php
	$i = 0;
	foreach ( (array) get_the_category() as $cat ) {
		$j = ( 0 == $i ) ? "a" : "x";
		echo '<marc:subfield code="' . $j . '">' . $cat->cat_name . "</marc:subfield>\n";
		$i++;
	}
?>
        </marc:datafield>
        <marc:datafield tag="700" ind1="1" ind2="">
        	<marc:subfield code="a"><?php the_author(); ?></marc:subfield>
	</marc:datafield>
	<marc:datafield tag="856" ind1="" ind2="">
		<marc:subfield code="u"><?php the_permalink_rss(); ?></marc:subfield>
	</marc:datafield>
  </marc:record>
<?php
	endforeach;
}

/*
 * output an SRW_DC record from a post
 * 
 *
 *
 */
function unapi_show_srw_dc() {
	global $id, $blogName;
	foreach(array_merge(get_posts('include=' . $id), get_pages('include=' . $id)) as $post) : setup_postdata($post);
?>
  <srw_dc:dc xmlns:srw_dc="info:srw/schema/1/dc-schema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://purl.org/dc/elements/1.1/" xsi:schemaLocation="info:srw/schema/1/dc-schema http://www.loc.gov/standards/sru/dc-schema.xsd">
	<title><?php the_title_rss(); ?></title>
        <creator><?php the_author(); ?></creator>
	<type>text</type>
	<format>application/xml</format>
	<publisher><?php echo htmlspecialchars($blogName); ?></publisher>
	<date><?php the_modified_date('r'); ?></date>
        <description>'<?php the_excerpt_rss(); ?>'</description>
        <?php
	foreach ( (array) get_the_category() as $cat ) {
		echo '<subject>' . $cat->cat_name . "</subject>\n";
	}
?>
	<identifier><?php the_permalink_rss(); ?></identifier>
	<language><?php echo get_option('rss_language'); ?></language>
  </srw_dc:dc>
<?php
	endforeach;
}
?>