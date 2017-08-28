# Laravel HTML Cache

<p>
	<a href="https://travis-ci.org/jkniest/HTMLCache">
		<img src="https://travis-ci.org/jkniest/HTMLCache.svg?branch=develop">
	</a><img src="https://img.shields.io/badge/Version-1.0.0-blue.svg">
	<a href="https://packagist.org/packages/jkniest/htmlcache"><img src="https://img.shields.io/packagist/dt/jkniest/htmlcache.svg"></a>
</p>

---

This package __speeds up__ your laravel application by caching the final rendered __html__. So your database queries and view loading algorithms don't need to run every single page load.

This package is made for you if you have a lot of static pages (or pages that don't change very often). That means, __portfolios__, __blogs__, __landing pages__ and more.

And it is highly customizable: You can even cache the same page for every user different, allowing that you can cache for example their account page or dashboard without worrying that another user can see these cached pages.

__One benefit against much other html caches:__ It will also cache the pages based on language, and (optionally) user id. And if there are special cases (for example a special GET-parameter) that needs to be used to generate multiple versions of the same page, the middleware can be easily modified.

---

## Table of contents
1. [Installation](#installation)
2. [Using](#using)   
2.1. [For all web routes](#for-all-web-routes)   
2.2. [Only for specific routes](#only-for-specific-routes)
3. [Configuration](#configuration)   
3.1. [Enable / Disable cache](#enable--disable-cache)   
3.2. [Caching prefix](#caching-prefix)   
3.3. [Caching time](#caching-time)   
3.4. [User specific caching](#user-specific)
4. [Ignoring routes](#ignoring-routes)
5. [Clear cache](#clear-cache)
6. [Override middlware](#override-middlware)

---

## Installation

The installation process is very straight-forward. It's like any other laravel package.

1) Add the package as a composer dependency by running the following command inside the console:
```shell
composer require jkniest/htmlcache
```

2) Add the package service provider in your packages configuration. Open up the `config/app.php` file and the following into your `providers` array:
```php
'providers' => [
	// ...
	JKniest\HtmlCache\HtmlCacheServiceProvider::class,
	// ...
]
```

3) Done! Now you can use this middleware inside your project. You now have multiple ways to use the middleware (see [Using](#using))

---

## Using

### For all web routes

If you want to apply this middleware to every single web route (web route = no api route) you can add this to your global web middleware group.

Add the following line into your `app/Http/Kernel.php` file inside the middleware-group web variable:
```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \JKniest\HtmlCache\Http\Middleware\CacheHtml::class,
        // ...
    ],
    // ...
]
```

Now every single web-route uses the html cache. And it will just work! You don't have to do another thing. If you want to dig deeper (and maybe want to ignore some specific routes), see [Configuration](#configuration).

### Only for specific routes

If you don't want to apply this middleware on any web-route you can use it only in specific routes or route-groups.

First you should add an alias to your `app/Http/Kernel.php` file:
```php
protected $routeMiddleware = [
    // ...
    'htmlcache' => \JKniest\HtmlCache\Http\Middleware\CacheHtml::class
    // ...
]
```

Now you can define the middleware in routes or route groups:

__Single Routes:__ (in `routes/web.php`)
```php
Route::middleware('htmlcache')->get('/route', 'Controller@action');
```

__Route groups:__ (in `routes/web.php`)
```php
Route::group(['middleware' => 'htmlcache'], function () {
    Route::get('/route', 'Controller@Action');
});
```

__Controllers:__ (in `app/Http/Controllers/...`)
```php
public function __construct()
{
    $this->middleware('htmlcache');
}
```

---

## Configuration

You can nearly configure anything inside the `.env` file.

### Enable / Disable cache

Enable or __disable__ the whole caching system. If this value is set to false no new pages will be cached and no old caches will be loaded.

```
HTML_CACHE_ENABLED=true
```

### Caching prefix

A simple prefix that will be added to all cache keys. The default key is `html_`. So a cache key would look something like this: `html_aboutus_en`

```
HTML_CACHE_PREFIX="html_"
```

### Caching time

The amount of minutes every single page should be cached. After these minutes the cache will be regenerated on the next page load. The default value is `360 Minutes` (= 6 hours)

```
HTML_CACHE_MINUTES=360
```

### User-Specific

If this value is set to true the cache key will contain the user id. If the user is not signed in, a `-1` will be used instead (so each guest does share the cached result and every signed in user does have it's own cached version). This is useful if you want to cache user-specific pages, like a dashboard. The default value is `false`. 

```
HTML_CACHE_USER_SPECIFIC=false
```

---

## Ignoring routes

It is possible to ignore specific routes. You need to publish the default configuration by using this command:

```shell
php artisan vendor:publish --tag=htmlcache
```

This will create a new file in your project: `config/htmlcache.php`. There you can configure any ignored routes by simply adding these to the `ignored` array:

```php
    /**
     * HTML cache ignored routes
     */
    'ignored' => [
        '/admin',
        '/another/ignored/route',
    ]
```

---

## Clear cache

The html cache package uses the default laravel cache helpers. So you simple run the artisan command to clear the cache:

```shell
php artisan cache:clear
```

This will remove every cached version of this plugin (and also of everything else). It is recommended to put this in your deployment workflow (for example in the deployment script in forge or as an deployment hook in envoyer)

---

## Override middlware

It is possible to override the middleware. So you could override the cache-key generation. In this short tutorial we will add another field to the cache key generation (the post id).

Let's say you have a forum and you can access each page with a get parameter. For example: `http://my-forum.dev?page=3`. In the default implementation all pages would share the same cached results (which means that the pagination isn't working anymore).

The simplest solution would be to override the middleware and extends the cache-key generation.

1. Create a new middleware inside the `app/Http/Middleware` folder. You can name it like you want.. in this case I will name it: `HtmlCache.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;

class HtmlCache extends \JKniest\HtmlCache\Http\Middleware\CacheHtml
{
    
}
```

Now we can override any methods. The method `getCacheKey` handles the generation of the cache key. The default implementation looks like this:

```php
    protected function getCacheKey(string $page)
    {
        $prefix = config('htmlcache.prefix');
        $locale = app()->getLocale();

        $page = str_replace('/', '_', trim($page, '/'));

        if (config('htmlcache.user_specific')) {
            $id = Auth::check() ? Auth::id() : -1;
            return "{$prefix}{$page}_{$locale}_{$id}";
        }

        return "{$prefix}{$page}_{$locale}";
    }
```

Let's implement our own (in the HtmlCache middleware that we just created):

```php
    protected function getCacheKey(string $page)
    {
        $key = parent::getCacheKey($page);

        if (request('page') !== null) {
            $key .= '_' . request('page');
        }

        return $key;
    }
```

The last step is to register our own middleware as an alias or in the kernel file (instead of using the default implementation):

In `app/Http/Kernel.php`:
```php
    protected $routeMiddleware = [
        // ...
        'htmlcache' => \App\Http\Middleware\HtmlCache::class,
        // ...
    ];
```

Of course you can always override any other method (like the `Handle` method itself).

---

## License

Copyright 2017 Jordan Kniest

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit
persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
