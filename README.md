# A Healther Options Table

By default, the wp_options table in WordPress works great for storing options.

Where it gets unweildy and problematic is in a few scenarios. Most good hosting
citizens enable an external persistent object cache, like Memcached or Redis.

By default these object cache stores limit their storage buckets to 1MB each.
This is generally sufficient. Where it becomes insufficient currently is with
autoloaded options.

When the alloptions cache bucket exceeds 1MB, it no longer works, and this can
cause things to bring in interesting and unexpected ways.  This plugin aims to
inform store administrators when there is an issue, empower them to fix the issue manually - and ideally, fix it for them without them having to worry about it.

# TODOS

 - Support Memcached + Redis bucket checking out of the box (memcache_get_stats)
 - add daily cron check to run cleanup routines.
 - Create interface for manual intervention, along with admin bar alert (e.g. comments)
 - On manual intervention page, show problematic options that are being autoloaded with ability to change the autoload value.
 - Add information about the database type (MyISAM vs. InnoDB). InnoDB performance can be drastically improved by adding an index to the autoload column, while MyISAM appears to degrade with the same index.
  - Add information about total number of rows. While this may vary greatly based on the number of active plugins, if you have an options table with 50,000 options, or 500,000, or 5,000,000 (we've seen it all) - there's a problem somewhere.

Helpful : https://10up.com/blog/2017/wp-options-table/
