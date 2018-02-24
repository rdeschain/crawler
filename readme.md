### Simple web crawler that finds and tweets unlocked-articles
There is a bit of infrastructure and setup involved to correctly run this code.

#### Install Laravel
This projects runs [Laravel 5.3](https://laravel.com/). Here is how to [install](https://laravel.com/docs/5.3/installation) this version:
`composer create-project laravel/laravel <your_project_dir> --prefer-dist 5.3`

It is assumed that PHP 7.0+ is installed, but most likely PHP 5.6+ would also work.

Run `composer install` after installing the framework and pulling down the repo. You'll also need the included packages. You'll need to follow these steps to post to [Twitter](https://github.com/thujohn/twitter).

#### Queues and Queue workers
The crawler depends on [queuing](https://laravel.com/docs/5.3/queues) to mange the list of pending links to be processed.
The advantage of using queues is that workers can be scaled up or down depending on how 'aggressive' the crawler should act by processing through the queue faster.
Installing and configuring Supervisor is best way to manage workers. Laravel provides some documentation on [setting up Supervisor](https://laravel.com/docs/5.3/queues#supervisor-configuration).
You are able to use any number of [queue drivers](https://laravel.com/docs/5.3/queues#driver-prerequisites). I typically use Redis.

#### Database
Visited links are stored in the database and is the mechanism to prevent the crawler from crawling forever. Here are required tables.

"CREATE TABLE `t_articles` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `timestamp` int(15) DEFAULT NULL,
   `date` date DEFAULT NULL,
   `encoded_url` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
   `url` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
   `free` int(1) DEFAULT '0',
   `tweeted` int(1) DEFAULT '0',
   `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
   `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`),
   UNIQUE KEY `id_UNIQUE` (`id`),
   UNIQUE KEY `url_UNIQUE` (`encoded_url`),
   KEY `date` (`date`),
   KEY `timestamp` (`timestamp`)
 ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;"

 "CREATE TABLE `t_urls` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `encoded_url` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `visited` int(11) DEFAULT '0',
    `url` varchar(1024) CHARACTER SET utf8 DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `id_UNIQUE` (`id`),
    UNIQUE KEY `url_UNIQUE` (`encoded_url`)
  ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;"

  "CREATE TABLE `t_failed_jobs` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `connection` mediumtext CHARACTER SET utf8,
     `queue` mediumtext CHARACTER SET utf8,
     `payload` longtext CHARACTER SET utf8,
     `exception` longtext CHARACTER SET utf8,
     `failed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (`id`)
   ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;"

#### Commands

The crawler uses a custom [Artisan Command](https://laravel.com/docs/5.3/artisan).
To run a full crawl simply mark all visited urls as not visited: `UPDATE t_urls SET visited=0` or truncate the table t_urls
and then run this Artisan command in your project directory `php artisan run:crawler https://theathletic.com crawl`.
The crawler will then index the entire site.

The hourly crawler only marks the homepage as not visited and runs a shallow crawl.