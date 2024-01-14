<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AreaGroup;
use App\Models\Area;
use Illuminate\Support\Facades\DB;

class AreaGroupsAndAreasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Area::truncate();
        AreaGroup::truncate();

        $data = [
            ['name' => '北海道・東北地方', 'child' => [
                ['name' => '北海道', 'child' => [
                    ['name' => '稚内市', 'latitude' => '45.409', 'longitude' => '141.674'],
                    ['name' => '旭川市', 'latitude' => '43.768', 'longitude' => '142.370'],
                    ['name' => '網走市', 'latitude' => '44.021', 'longitude' => '144.270'],
                    ['name' => '釧路市', 'latitude' => '42.975', 'longitude' => '144.375'],
                    ['name' => '札幌市', 'latitude' => '43.064', 'longitude' => '141.347'],
                    ['name' => '室蘭市', 'latitude' => '42.317', 'longitude' => '140.988'],
                    ['name' => '函館市', 'latitude' => '41.776', 'longitude' => '140.737'],
                ]],
                ['name' => '青森県', 'child' => [
                    ['name' => '青森市', 'latitude' => '40.824', 'longitude' => '140.740'],
                    ['name' => 'むつ市', 'latitude' => '41.289', 'longitude' => '141.217'],
                    ['name' => '八戸市', 'latitude' => '40.500', 'longitude' => '141.500'],
                ]],
                ['name' => '岩手県', 'child' => [
                    ['name' => '盛岡市', 'latitude' => '39.704', 'longitude' => '141.153'],
                    ['name' => '宮古市', 'latitude' => '39.637', 'longitude' => '141.952'],
                    ['name' => '大船渡市', 'latitude' => '39.072', 'longitude' => '141.717'],
                ]],
                ['name' => '宮城県', 'child' => [
                    ['name' => '仙台市', 'latitude' => '38.269', 'longitude' => '140.872'],
                    ['name' => '白石市', 'latitude' => '38.003', 'longitude' => '140.618'],
                ]],
                ['name' => '秋田県', 'child' => [
                    ['name' => '秋田市', 'latitude' => '39.717', 'longitude' => '140.117'],
                    ['name' => '横手市', 'latitude' => '39.300', 'longitude' => '140.567'],
                ]],
                ['name' => '山形県', 'child' => [
                    ['name' => '山形市', 'latitude' => '38.241', 'longitude' => '140.363'],
                    ['name' => '米沢市', 'latitude' => '37.917', 'longitude' => '140.117'],
                    ['name' => '酒田市', 'latitude' => '38.917', 'longitude' => '139.855'],
                    ['name' => '新庄市', 'latitude' => '38.759', 'longitude' => '140.301'],
                ]],
                ['name' => '福島県', 'child' => [
                    ['name' => '福島市', 'latitude' => '37.750', 'longitude' => '140.468'],
                    ['name' => 'いわき市', 'latitude' => '37.050', 'longitude' => '140.883'],
                    ['name' => '会津若松市', 'latitude' => '37.480', 'longitude' => '139.942'],
                ]],
            ]],
            ['name' => '関東地方', 'child' => [
                ['name' => '茨城県', 'child' => [
                    ['name' => '水戸市', 'latitude' => '36.341', 'longitude' => '140.447'],
                    ['name' => '土浦市', 'latitude' => '36.090', 'longitude' => '140.210'],
                ]],
                ['name' => '栃木県', 'child' => [
                    ['name' => '宇都宮市', 'latitude' => '36.566', 'longitude' => '139.884'],
                    ['name' => '大田原市', 'latitude' => '36.867', 'longitude' => '140.033'],
                ]],
                ['name' => '群馬県', 'child' => [
                    ['name' => '前橋市', 'latitude' => '36.391', 'longitude' => '139.061'],
                    ['name' => 'みなかみ町', 'latitude' => '36.743', 'longitude' => '139.001'],
                ]],
                ['name' => '埼玉県', 'child' => [
                    ['name' => 'さいたま市', 'latitude' => '35.908', 'longitude' => '139.657'],
                    ['name' => '熊谷市', 'latitude' => '36.133', 'longitude' => '139.383'],
                    ['name' => '秩父市', 'latitude' => '35.990', 'longitude' => '139.076'],
                ]],
                ['name' => '千葉県', 'child' => [
                    ['name' => '千葉市', 'latitude' => '35.605', 'longitude' => '140.123'],
                    ['name' => '銚子市', 'latitude' => '35.734', 'longitude' => '140.826'],
                    ['name' => '館山市', 'latitude' => '34.983', 'longitude' => '139.867'],
                ]],
                ['name' => '東京都', 'child' => [
                    ['name' => '千代田区', 'latitude' => '35.683', 'longitude' => '139.754'],
                    ['name' => '大島町', 'latitude' => '34.750', 'longitude' => '139.355'],
                    ['name' => '八丈町', 'latitude' => '33.109', 'longitude' => '139.790'],
                    ['name' => '小笠原村', 'latitude' => '27.094', 'longitude' => '142.191'],
                ]],
                ['name' => '神奈川県', 'child' => [
                    ['name' => '横浜市', 'latitude' => '35.448', 'longitude' => '139.643'],
                    ['name' => '小田原市', 'latitude' => '35.256', 'longitude' => '139.160'],
                ]],
            ]],
            ['name' => '中部地方', 'child' => [
                ['name' => '新潟県', 'child' => [
                    ['name' => '新潟市', 'latitude' => '37.902', 'longitude' => '139.024'],
                    ['name' => '長岡市', 'latitude' => '37.450', 'longitude' => '138.850'],
                    ['name' => '上越市', 'latitude' => '37.148', 'longitude' => '138.236'],
                    ['name' => '佐渡市', 'latitude' => '38.018', 'longitude' => '138.368'],
                ]],
                ['name' => '富山県', 'child' => [
                    ['name' => '富山市', 'latitude' => '36.695', 'longitude' => '137.211'],
                    ['name' => '高岡市', 'latitude' => '36.750', 'longitude' => '137.017'],
                ]],
                ['name' => '石川県', 'child' => [
                    ['name' => '金沢市', 'latitude' => '36.594', 'longitude' => '136.626'],
                    ['name' => '輪島市', 'latitude' => '37.900', 'longitude' => '136.900'],
                ]],
                ['name' => '福井県', 'child' => [
                    ['name' => '福井市', 'latitude' => '35.850', 'longitude' => '136.225'],
                    ['name' => '敦賀市', 'latitude' => '35.645', 'longitude' => '136.056'],
                ]],
                ['name' => '山梨県', 'child' => [
                    ['name' => '甲府市', 'latitude' => '35.664', 'longitude' => '138.568'],
                    ['name' => '富士河口湖町', 'latitude' => '35.497', 'longitude' => '138.754'],
                ]],
                ['name' => '長野県', 'child' => [
                    ['name' => '長野市', 'latitude' => '36.651', 'longitude' => '138.181'],
                    ['name' => '松本市', 'latitude' => '36.233', 'longitude' => '137.967'],
                    ['name' => '飯田市', 'latitude' => '35.520', 'longitude' => '137.821'],
                ]],
                ['name' => '岐阜県', 'child' => [
                    ['name' => '岐阜市', 'latitude' => '35.423', 'longitude' => '136.760'],
                    ['name' => '高山市', 'latitude' => '36.133', 'longitude' => '137.250'],
                ]],
                ['name' => '静岡県', 'child' => [
                    ['name' => '静岡市', 'latitude' => '34.977', 'longitude' => '138.383'],
                    ['name' => '熱海市', 'latitude' => '35.089', 'longitude' => '139.069'],
                    ['name' => '三島市', 'latitude' => '35.117', 'longitude' => '138.917'],
                    ['name' => '浜松市', 'latitude' => '34.700', 'longitude' => '137.733'],
                ]],
                ['name' => '愛知県', 'child' => [
                    ['name' => '名古屋市', 'latitude' => '35.181', 'longitude' => '136.906'],
                    ['name' => '豊橋市', 'latitude' => '34.767', 'longitude' => '137.383'],
                ]],
            ]],
            ['name' => '近畿地方', 'child' => [
                ['name' => '三重県', 'child' => [
                    ['name' => '津市', 'latitude' => '34.730', 'longitude' => '136.509'],
                    ['name' => '尾鷲市', 'latitude' => '34.067', 'longitude' => '136.200'],
                ]],
                ['name' => '滋賀県', 'child' => [
                    ['name' => '大津市', 'latitude' => '35.004', 'longitude' => '135.868'],
                    ['name' => '彦根市', 'latitude' => '35.250', 'longitude' => '136.250'],
                ]],
                ['name' => '京都府', 'child' => [
                    ['name' => '京都市', 'latitude' => '35.021', 'longitude' => '135.754'],
                    ['name' => '舞鶴市', 'latitude' => '35.450', 'longitude' => '135.333'],
                ]],
                ['name' => '大阪府', 'child' => [
                    ['name' => '大阪市', 'latitude' => '34.694', 'longitude' => '135.502'],
                ]],
                ['name' => '兵庫県', 'child' => [
                    ['name' => '神戸市', 'latitude' => '34.691', 'longitude' => '135.183'],
                    ['name' => '豊岡市', 'latitude' => '35.533', 'longitude' => '134.833'],
                ]],
                ['name' => '奈良県', 'child' => [
                    ['name' => '奈良市', 'latitude' => '34.685', 'longitude' => '135.805'],
                    ['name' => '十津川村', 'latitude' => '33.988', 'longitude' => '135.792'],
                ]],
                ['name' => '和歌山県', 'child' => [
                    ['name' => '和歌山市', 'latitude' => '34.226', 'longitude' => '135.167'],
                    ['name' => '串本町', 'latitude' => '33.467', 'longitude' => '135.783'],
                ]],
            ]],
            ['name' => '中国・四国地方', 'child' => [
                ['name' => '鳥取県', 'child' => [
                    ['name' => '鳥取市', 'latitude' => '35.500', 'longitude' => '134.233'],
                    ['name' => '米子市', 'latitude' => '35.433', 'longitude' => '133.333'],
                ]],
                ['name' => '島根県', 'child' => [
                    ['name' => '松江市', 'latitude' => '35.472', 'longitude' => '133.051'],
                    ['name' => '浜田市', 'latitude' => '34.883', 'longitude' => '132.083'],
                    ['name' => '隠岐の島町', 'latitude' => '36.209', 'longitude' => '133.321'],
                ]],
                ['name' => '岡山県', 'child' => [
                    ['name' => '岡山市', 'latitude' => '34.662', 'longitude' => '133.935'],
                    ['name' => '津山市', 'latitude' => '35.050', 'longitude' => '134.000'],
                ]],
                ['name' => '広島県', 'child' => [
                    ['name' => '広島市', 'latitude' => '34.396', 'longitude' => '132.459'],
                    ['name' => '庄原市', 'latitude' => '34.850', 'longitude' => '133.017'],
                ]],
                ['name' => '山口県', 'child' => [
                    ['name' => '下関市', 'latitude' => '33.950', 'longitude' => '130.950'],
                    ['name' => '山口市', 'latitude' => '34.186', 'longitude' => '131.471'],
                    ['name' => '柳井市', 'latitude' => '33.967', 'longitude' => '132.117'],
                    ['name' => '萩市', 'latitude' => '34.400', 'longitude' => '131.417'],
                ]],
                ['name' => '徳島県', 'child' => [
                    ['name' => '徳島市', 'latitude' => '34.066', 'longitude' => '134.559'],
                    ['name' => '美波町', 'latitude' => '33.734', 'longitude' => '134.535'],
                ]],
                ['name' => '香川県', 'child' => [
                    ['name' => '高松市', 'latitude' => '34.340', 'longitude' => '134.043'],
                ]],
                ['name' => '愛媛県', 'child' => [
                    ['name' => '松山市', 'latitude' => '33.839', 'longitude' => '132.766'],
                    ['name' => '新居浜市', 'latitude' => '33.959', 'longitude' => '133.317'],
                    ['name' => '宇和島市', 'latitude' => '33.224', 'longitude' => '132.560'],
                ]],
                ['name' => '高知県', 'child' => [
                    ['name' => '高知市', 'latitude' => '33.560', 'longitude' => '133.531'],
                    ['name' => '室戸市', 'latitude' => '33.289', 'longitude' => '134.152'],
                    ['name' => '土佐清水市', 'latitude' => '32.781', 'longitude' => '132.954'],
                ]],
            ]],
            ['name' => '九州・沖縄地方', 'child' => [
                ['name' => '福岡県', 'child' => [
                    ['name' => '福岡市', 'latitude' => '33.606', 'longitude' => '130.418'],
                    ['name' => '北九州市', 'latitude' => '33.833', 'longitude' => '130.833'],
                    ['name' => '飯塚市', 'latitude' => '33.633', 'longitude' => '130.683'],
                    ['name' => '久留米市', 'latitude' => '33.317', 'longitude' => '130.517'],
                ]],
                ['name' => '佐賀県', 'child' => [
                    ['name' => '佐賀市', 'latitude' => '33.249', 'longitude' => '130.299'],
                    ['name' => '伊万里市', 'latitude' => '33.264', 'longitude' => '129.880'],
                ]],
                ['name' => '長崎県', 'child' => [
                    ['name' => '長崎市', 'latitude' => '32.745', 'longitude' => '129.874'],
                    ['name' => '佐世保市', 'latitude' => '33.159', 'longitude' => '129.723'],
                    ['name' => '対馬市', 'latitude' => '34.202', 'longitude' => '129.287'],
                    ['name' => '五島市', 'latitude' => '32.695', 'longitude' => '128.840'],
                ]],
                ['name' => '熊本県', 'child' => [
                    ['name' => '熊本市', 'latitude' => '32.790', 'longitude' => '130.742'],
                    ['name' => '阿蘇市', 'latitude' => '32.955', 'longitude' => '131.097'],
                    ['name' => '天草市', 'latitude' => '32.458', 'longitude' => '130.193'],
                    ['name' => '人吉市', 'latitude' => '32.217', 'longitude' => '130.750'],
                ]],
                ['name' => '大分県', 'child' => [
                    ['name' => '大分市', 'latitude' => '33.238', 'longitude' => '131.613'],
                    ['name' => '中津市', 'latitude' => '33.598', 'longitude' => '131.188'],
                    ['name' => '日田市', 'latitude' => '33.317', 'longitude' => '130.933'],
                    ['name' => '佐伯市', 'latitude' => '32.959', 'longitude' => '131.9'],
                ]],
                ['name' => '宮崎県', 'child' => [
                    ['name' => '宮崎市', 'latitude' => '31.911', 'longitude' => '131.424'],
                    ['name' => '延岡市', 'latitude' => '32.583', 'longitude' => '131.667'],
                    ['name' => '都城市', 'latitude' => '31.733', 'longitude' => '131.067'],
                    ['name' => '高千穂市', 'latitude' => '32.731', 'longitude' => '131.324'],
                ]],
                ['name' => '鹿児島県', 'child' => [
                    ['name' => '鹿児島市', 'latitude' => '31.596', 'longitude' => '130.557'],
                    ['name' => '鹿屋市', 'latitude' => '31.383', 'longitude' => '130.850'],
                    ['name' => '中種子町', 'latitude' => '30.533', 'longitude' => '130.958'],
                    ['name' => '奄美市', 'latitude' => '28.377', 'longitude' => '129.493'],
                ]],
                ['name' => '沖縄県', 'child' => [
                    ['name' => '那覇市', 'latitude' => '26.212', 'longitude' => '127.681'],
                    ['name' => '名護市', 'latitude' => '26.588', 'longitude' => '127.976'],
                    ['name' => '久米島町', 'latitude' => '26.354', 'longitude' => '126.770'],
                    ['name' => '南大東村', 'latitude' => '25.828', 'longitude' => '131.232'],
                    ['name' => '宮古島市', 'latitude' => '24.790', 'longitude' => '125.294'],
                    ['name' => '石垣市', 'latitude' => '24.345', 'longitude' => '124.157'],
                    ['name' => '与那国町', 'latitude' => '24.467', 'longitude' => '123.004'],
                ]],
            ]],
        ];

        try {
            DB::beginTransaction();

            $regionOrder = 0;
            $prefectureOrder = 0;
            $areaOrder = 0;

            foreach ($data as $data2) {
                $regionOrder++;
                $region = AreaGroup::create([
                    'name' => $data2['name'],
                    'display_order' => $regionOrder,
                ]);
                $prefectureOrder = 0;

                foreach ($data2['child'] as $data3) {
                    $prefectureOrder++;
                    $prefecture = AreaGroup::create([
                        'name' => $data3['name'],
                        'parent_area_group_id' => $region->id,
                        'display_order' => $prefectureOrder,
                    ]);
                    $areaOrder = 0;

                    foreach ($data3['child'] as $area) {
                        $areaOrder++;
                        Area::create([
                            'name' => $area['name'],
                            'area_group_id' => $prefecture->id,
                            'latitude' => $area['latitude'],
                            'longitude' => $area['longitude'],
                            'display_order' => $areaOrder,
                        ]);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('登録に失敗しました。');
        }
    }
}
