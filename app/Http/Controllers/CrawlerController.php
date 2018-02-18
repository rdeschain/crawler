<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessUrl;
use Illuminate\Http\Request;

use DB;


class CrawlerController extends Controller
{

    public function test(Request $request)
    {
        //dispatch((new SaveArticle('https://theathletic.com2', 1,'2012-03-11'))->onQueue('article'));
        //dispatch((new ProcessUrl('https://theathletic.com/243715/2018/02/16/angels-reliever-cam-bedrosian-rented-a-bucket-lift-to-rescue-his-cat-from-a-tree-this-offseason/','daily'))->onQueue('daily'));
        //dispatch((new ProcessUrl('https://theathletic.com','daily'))->onQueue('daily'));

    }

}