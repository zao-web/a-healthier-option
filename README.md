# A Healther Options Table

By default, the wp_options table in WordPress works great for storing options.

Where it gets unweildy and problematic is in a few scenarios. Most good hosting
citizens enable an external persistent object cache, like Memcached or Redis.

By default these object cache stores limit their storage buckets to 1MB each.
This is generally sufficient. Where it becomes insufficient currently is with
autoloaded options.

When the alloptions cache bucket exceeds 1MB, it no longer works, and this can
cause things to break in interesting and unexpected ways.  This plugin aims to
inform site administrators when there is an issue, empower them to fix the issue manually - and ideally, fix it for them without them having to worry about it.

# TODOS

 - Support Memcached + Redis bucket checking out of the box (memcache_get_stats)
 - add daily cron check to run cleanup routines.
 - Create interface for manual intervention, along with admin bar alert (e.g. comments)
 - On manual intervention page, show problematic options that are being autoloaded with ability to change the autoload value.
- Hook up edit/delete functionality in list table
- Hook up autoload toggling in list table
- Nonce/cap checks, etc. 

Helpful : https://10up.com/blog/2017/wp-options-table/
