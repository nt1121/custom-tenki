<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWeatherForecastItem extends Model
{
    use HasFactory;

    /**
     * モデルに関連付けるテーブル
     *
     * @var string
     */
    protected $table = 'user_weather_forecast_item';

    /**
     * テーブルに関連付ける主キー
     *
     * @var string
     */
    protected $primaryKey = ['user_id', 'weather_forecast_item_id'];

    /**
     * モデルのIDを自動増分するか
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * モデルにタイムスタンプを付けるか
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['user_id', 'weather_forecast_item_id', 'display_order'];
}
