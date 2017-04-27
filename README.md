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
 -  Add admin bar alert (ala comments)
 - Nonce checks etc.

Helpful : https://10up.com/blog/2017/wp-options-table/

# NOTE
This plugin was originally developed with the intention of being a test. We give back-end developer candidates access to this plugin as a three-hour trial project. They refactor it, we judge their skill based on how they do.  The `master` branch of this _will_ be updated and refactored, and the `to-be-refactored` branch will essentially live on as this test.  You will note - they are basically the same right now.
