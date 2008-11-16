=== unAPI Server ===
Tags: unapi, metadata, record surrogates, machine-readable records
Contributors: mjgiarlo, pbinkley, jbrinley, lbjay
Stable tag: trunk

The unAPI Server is a WordPress plug-in implementing the final version of the unAPI specification, “a tiny HTTP API for the few basic operations necessary to copy discrete, identified content from any kind of web application.” Read the final version of the unAPI spec at http://unapi.info/. 

The server provides records for each WordPress post in the following formats: OAI-Dublin Core (oai_dc), Metadata Object Description Schema (MODS), SRW-Dublin Core (srw_dc), MARCXML, and RSS.  The specification makes use of LINK tag auto-discovery and an unendorsed microformat for machine-readable metadata records.

== Downloading ==

1. Grab the zip file from http://downloads.wordpress.org/plugin/unapi.zip
2. Check out from svn: svn co http://svn.wp-plugins.org/unapi/trunk

== Installation ==

1. Upload the unapi folder containing all source files to your plug-ins folder, usually `wp-content/plugins/`
2. Login as an administrator and activate the plug-in via the Plugins menu
3. Choose an identifier prefix -- an arbitrary string, preferably URI-valid  -- and enter it via the unAPI submenu of the Options menu.  Or, alternatively, use the default value - no fuss, no muss.
4. Voila!  All of the posts in your WordPress blog are now published via unAPI.  

== Frequently Asked Questions ==

= unAPI?  What the heck is that? =

This is a glib answer, and a none-too-useful one at that.  But you might hazard a peek at the following Ariadne article for more information: http://www.ariadne.ac.uk/issue48/chudnov-et-al/

= Hey, nothing's different.  What gives? =

I said machine-readable, right?  Be glad; we've verified you're not a machine. :)

In order to see unAPI in action, you may install an unAPI client such as these Greasemonkey scripts:

* By Xiaoming Liu: http://lxming.blogspot.com/2006_05_21_lxming_archive.html
* By Alf Eaton: http://cipolo.med.yale.edu/pipermail/gcs-pcs-list/2006-June/000951.html

= Yeah, but is it -really- working? =

If you'd like, you may validate your unAPI service by entering a post URL -- any regular WP post URL will do -- over at http://validator.unapi.info/

= What should I use for my identifier prefix? =

An unAPI identifier is typically composed of a prefix and an integer, but may be any string.  Since WordPress already identifies individual posts with a locally unique integer, we only need to tack on a prefix.  The default should work for you, but you can feel free to change it.  Some folks prefer OAI-interoperable identifiers such as "oai:domain.tld:blogname:".  Others opt for simplicity, such as the default prefix, "unleash.it:".  Still others use valid URLs, such as "http://domain.tld/blog/archive/".  It really is up to you; democracy, ho!

= Something is broked! =

Oh, okay, why don't you send me an e-mail?  Try leftwing at alumni rutgers edu.  Patches welcome.
