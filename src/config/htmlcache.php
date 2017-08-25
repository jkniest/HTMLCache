<?php

return [

    /**
     * HTML cache enabled
     *
     * Should the html cache system be enabled? If this value is set to false it will
     * never cache any response and it will not load any cached version.
     *
     * You can override this value by providing a "HTML_CACHE_ENABLED" key inside your
     * .env file.
     */
    'enabled' => env('HTML_CACHE_ENABLED', true),

    /**
     * HTML cache prefix
     *
     * A cache prefix for the html cache. You should change this value if there are multiple
     * application with this plugin on one cache system (for example redis or memcached).
     * The default prefix is "html_".
     *
     * You can override the value by providing a "HTML_CACHE_PREFIX" key inside your
     * .env file.
     */
    'prefix' => env('HTML_CACHE_PREFIX', 'html_'),

    /**
     * HTML cache time in minutes
     *
     * How many minutes should the response be cached? After this time (in minutes) the cache
     * will be regenerated. The default time in minutes is 360 (6 hours).
     *
     * You can override the value by providing a "HTML_CACHE_MINUTES" key inside your
     * .env file.
     */
    'minutes' => env('HTML_CACHE_MINUTES', 360),

    /**
     * HTML cache user specific
     *
     * Should the cache be user specific? This would append the cache key by the user id. If the
     * user is not signed in a -1 will be used.
     *
     * This can be useful if you want to cache user specific pages like a billing overview or a
     * profile page.
     *
     * The default value is false.
     *
     * You can override the value by providing a "HTML_CACHE_USER_SPECIFIC" key inside your
     * .env file.
     */
    'user_specific' => env('HTML_CACHE_USER_SPECIFIC', false),

    /**
     * HTML cache ignored routes
     *
     * These routes will be completely ignored by the caching. The only way to override these
     * values is to publish the configuration file.
     */
    'ignored' => [
        # /path/to/my/ignored/route
    ]
];
