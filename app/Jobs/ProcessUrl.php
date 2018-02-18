<?php

namespace App\Jobs;

use App\Url;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Goutte\Client;

class ProcessUrl implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $url;

    /**
     * ProcessUrl constructor.
     * @param $url
     * @param $queue
     */
    public function __construct($url, $queue = 'crawl')
    {
        $this->url = $url;
        $this->queue = $queue;
    }

    /**
     *
     */
    public function handle()
    {

        /**
         * check if url already exists in table
         * If it does then see if it was already visted else mark it as visted and continue
         * This should save some processing time by quiting earlier
         */

        echo "incoming: " . $this->url . "\n";

        $urlRecord = Url::where('encoded_url', base64_encode(urlencode($this->url)))->first();

        if ($urlRecord === null) {

            Url::updateOrCreate(['encoded_url' => base64_encode(urlencode($this->url)),
                         'url' => $this->url,
                         'visited' => 1]);
        } else {

            if ($urlRecord->visited) {
                echo "already visited url \n";
                return;

            } else {

                $urlRecord->visited = 1;
                $urlRecord->save();
            }
        }

        /**
         * TO-DO thinking about not hard coding the domain
         */
        if (!preg_match('/^(https:\/\/theathletic\.com)/', $this->url, $matches)) {
            echo "missing or wrong domain \n";
            return;
        }
        $base_url = $matches[0];

        //test to see if url is able to be parsed
        $client = new Client();
        try {
            $crawler = $client->request('GET', $this->url);
        } catch (\Exception $e) {
            echo "busted request \n";
            return;
        }

        //check response
        $response = $client->getInternalResponse();
        if ($response->getStatus() != '200') {
            echo "bad response \n";
            return;
        }

        /**
         * check to see if this is an article.
         * if it is, then do not continue to dispatch jobs
         * Check if paywall div is present
         * -> if present then dispatch save article job
         */
        if (preg_match('/theathletic\.com\/\d+\/(\d{4}\/\d{2}\/\d{2})\//', $this->url, $output) && preg_match('/<meta property.*content="article"/', $response->getContent())) {

            echo "$this->url\n";
            $date = str_replace('/', '-', $output[1]);

            if (preg_match('/the-paywall-v3/', $response->getContent())) {

                echo "paid article \n";
                dispatch((new SaveArticle($this->url, 0, $date))->onQueue('article'));

            } else {

                echo "free article \n";
                dispatch((new SaveArticle($this->url, 1, $date))->onQueue('article'));
            }

            //stop when we hit an article
            return;
        }

        //find all links on page and construct full url if required
        $hrefs = $crawler->filter('a')->each(function ($node) use ($base_url) {

            $ref = $node->attr('href');
            if (preg_match('/^\//', $node->attr('href'))) {
                $ref = $base_url . $node->attr('href');
            }
            return $ref;
        });

        /**
         * Only push correct domain
         **/
        foreach ($hrefs as $url) {

            if (preg_match('/^https:\/\/theathletic\.com/', $url)) {

                echo "dispatching: $url \n";
                dispatch((new ProcessUrl($url, $this->queue))->onQueue($this->queue));
            }
        }
    }
}
