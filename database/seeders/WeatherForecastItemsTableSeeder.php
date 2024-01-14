<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WeatherForecastItem;

class WeatherForecastItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WeatherForecastItem::truncate();

        $data = [
            ['name' => 'weather', 'display_name' => '天気', 'display_order' => 1],
            ['name' => 'temp', 'display_name' => '気温', 'display_order' => 2],
            ['name' => 'pop', 'display_name' => '降水確率', 'display_order' => 3],
            ['name' => 'rain_3h', 'display_name' => '降水量', 'display_order' => 4],
            ['name' => 'humidity', 'display_name' => '湿度', 'display_order' => 5],
            ['name' => 'wind', 'display_name' => '風向風速', 'display_order' => 6],
        ];

        foreach ($data as $item) {
            WeatherForecastItem::create($item);
        }
    }
}
