<?php

namespace App\Console;

use App\Article;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Thujohn\Twitter\Facades\Twitter;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\RunCrawler::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('run:crawler https://theathletic.com daily')->hourly();

        //tweet it!
        $schedule->call(function () {

            $article = Article::where([['free', 1], ['tweeted', 0]])->orderBy('date', 'asc')->first();

            try {

                if($article !== null) {
                    Twitter::postTweet(['status' => $article->url,
                                        'format' => 'json']);

                    $article->tweeted = 1;
                    $article->save();
                }

            } catch (\Exception $e) {

            }

        })->everyMinute();

    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
