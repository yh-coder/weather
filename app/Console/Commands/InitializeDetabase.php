<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use \SplFileObject;
use \Exception;

class InitializeDetabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'initdb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        // テーブル作成
        $sql = preg_replace('/\n/', '', file_get_contents(database_path().'/init/initializa.sql'));
        foreach (explode(';', $sql) as $stat) {
            if (!empty($stat)) {
                DB::statement($stat);
            }
        }

        // データ投入

        // 地域・国 リレーション

        // UN m49
        $csv = new SplFileObject(database_path().'/init/un_m49.csv');
        $csv->setFlags(SplFileObject::READ_CSV);
        $csv->seek(1);
        while (! $csv->eof()) {
            $row = $csv->current();
            DB::insert('insert into tmp_un_m49 (region_name, sub_region_name, iso_alpha3) values (?, ?, ?)', [$row[3], $row[5], $row[10]]);
            $csv->next();
        }

        // ISO3166
        $csv = new SplFileObject(database_path().'/init/iso3166.csv');
        $csv->setFlags(SplFileObject::READ_CSV);
        $csv->seek(1);
        while (! $csv->eof()) {
            $row = $csv->current();
            DB::insert('insert into tmp_iso3166 (country_name, country_code, iso_alpha3) values (?, ?, ?)', [$row[0], $row[2], $row[3]]);
            $csv->next();
        }
        
        // 地域
        $regions = DB::table('tmp_un_m49')
            ->select(DB::raw('region_name, sub_region_name'))
            ->where('region_name', '<>', '')
            ->where('sub_region_name', '<>', '')
            ->groupBy('region_name', 'sub_region_name')
            ->orderBy('region_name', 'sub_region_name')
            ->get();
        foreach ($regions as $region) {
            DB::insert('insert into region (name) values (?)', [$region->sub_region_name]);
        }

        // 国
        $countries = DB::table('tmp_un_m49')
            ->join('tmp_iso3166', 'tmp_un_m49.iso_alpha3', '=', 'tmp_iso3166.iso_alpha3')
            ->join('region', 'tmp_un_m49.sub_region_name', '=', 'region.name')
            ->select('tmp_iso3166.country_name', 'tmp_iso3166.country_code', 'region.id')
            ->get();
        foreach ($countries as $country) {
            DB::insert('insert into country (name, code, region_id) values (?, ?, ?)', [$country->country_name, $country->country_code, $country->id]);
        }

        // tmp削除
        DB::statement('drop table tmp_un_m49');
        DB::statement('drop table tmp_iso3166');

        // 街
        DB::statement(file_get_contents(database_path().'/init/city.list.sql'));
    }
}
