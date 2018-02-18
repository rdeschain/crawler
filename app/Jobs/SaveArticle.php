<?php

namespace App\Jobs;

use App\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SaveArticle implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $url;
    protected $free;
    protected $date;
    protected $timestamp;

    /**
     * SaveArticle constructor.
     * @param $url
     * @param $free
     * @param $date
     */
    public function __construct($url, $free, $date)
    {
        $this->url = $url;
        $this->free = $free;
        $this->date = $date;
        $this->timestamp = strtotime($date);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!Article::where('encoded_url', base64_encode(urlencode($this->url)))->exists()) {

            Article::create(['encoded_url' => base64_encode(urlencode($this->url)),
                             'free' => $this->free,
                             'url' => $this->url,
                             'date' => $this->date,
                             'timestamp' => $this->timestamp]);
        }
    }
}
