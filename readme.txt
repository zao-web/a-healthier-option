=== WP eCommerce ===
Contributors: JustinSainton, lisaleague
Donate link: https://zao.is
Tags: developer, performance
Requires at least: 4.5
Tested up to: 4.7
Stable tag: 0.1.0

Unhealthy options table bogging your site down? This can help.

== Description ==

By default, the wp_options table in WordPress works great for storing options.

Where it gets unweildy and problematic is in a few scenarios. Most good hosting
citizens enable an external persistent object cache, like Memcached or Redis.

By default these object cache stores limit their storage buckets to 1MB each.
This is generally sufficient. Where it becomes insufficient currently is with
autoloaded options.

When the alloptions cache bucket exceeds 1MB, it no longer works, and this can
cause things to break in interesting and unexpected ways.  This plugin aims to
inform site administrators when there is an issue, empower them to fix the issue manually - and ideally, fix it for them without them having to worry about it.

Big thanks to Lisa League of Qpractice for partnering with us to open source this!

== Installation ==

1. Upload the folder 'a-healthier-option' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

= Updating =

Before updating please make a backup of your existing files and database. Just in case.
After upgrading from earlier versions look for link "Update Store". This will update your database structure to work with new version.

== Changelog ==

= 0.1.0 [2017-4-13] =

* Initial release.
