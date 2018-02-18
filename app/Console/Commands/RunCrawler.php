<?php

namespace App\Console\Commands;

use App\Jobs\ProcessUrl;
use App\Url;
use Illuminate\Console\Command;

class RunCrawler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:crawler {url} {queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will crawl the site and put new urls on queue name passed.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $queue = $this->argument('queue');
        $url = $this->argument('url');

        //reset or create initial record for homepage
        Url::updateOrCreate(['url' => $url], ['visited' => 0]);

        dispatch((new ProcessUrl($url, $queue))->onQueue($queue));

    }
}
