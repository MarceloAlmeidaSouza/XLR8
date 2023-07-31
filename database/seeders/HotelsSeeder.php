<?php

namespace Database\Seeders;

use GuzzleHttp\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HotelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->loadDataFromURL('https://xlr8-interview-files.s3.eu-west-2.amazonaws.com/source_1.json');
        $this->loadDataFromURL('https://xlr8-interview-files.s3.eu-west-2.amazonaws.com/source_2.json');
    }

    private function loadDataFromURL(string $url){
        $client = new Client([]);
        $response = $client->get($url);
        $contents = json_decode($response->getBody()->getContents());
        $col = collect($contents->message)->map(function($tag){
            return [
               'name' => $tag[0],
               'latitude' => $tag[1],
               'longitude' => $tag[2],
               'price' => $tag[3],
            ];
        });

        foreach($col->toArray() as $row)
            DB::table('hotels')->insert([$row]);
    }
}
