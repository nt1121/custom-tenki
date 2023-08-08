<?php

namespace App\Services;

use App\Models\Area;
use App\Models\AreaGroup;
use Illuminate\Support\Facades\Cache;

class AreaGroupService
{
    /**
     * 地域グループとそれに紐づく地域グループ、地域の配列を取得する
     *
     * @param  null|int $id 地域グループID
     * @return array|bool
     */
    public static function getAreaGroupAndChildren(?int $id): array|bool
    {
        // キャッシュが存在する場合はキャッシュから取得
        $cacheKey = 'api_area_data_area_group_id_' . (is_null($id) ? 'null' : $id);
        $data = Cache::get($cacheKey);

        if (is_null($data)) {
            $data = [];

            if (is_null($id)) {
                $data['id'] = null;
                $data['name'] = null;
                $data['parent_area_group_id'] = null;
                $areaGroups = AreaGroup::whereNull('parent_area_group_id')
                    ->orderBy('display_order', 'asc')
                    ->get()
                    ->makeHidden(['parent_area_group_id', 'display_order', 'created_at', 'updated_at'])
                    ->toArray();
            } else {
                $areaGroup = AreaGroup::find($id);

                if (empty($areaGroup)) {
                    return false;
                }

                $data['id'] = $areaGroup->id;
                $data['name'] = $areaGroup->name;
                $data['parent_area_group_id'] = $areaGroup->parent_area_group_id;
                $areaGroups = AreaGroup::where('parent_area_group_id', $id)
                    ->orderBy('display_order', 'asc')
                    ->get()
                    ->makeHidden(['parent_area_group_id', 'display_order', 'created_at', 'updated_at'])
                    ->toArray();
            }

            $areas = Area::where('area_group_id', $id)
                ->orderBy('display_order', 'asc')
                ->get()
                ->makeHidden(['latitude', 'longitude', 'area_group_id', 'display_order', 'created_at', 'updated_at'])
                ->toArray();

            foreach ($areas as $key => $area) {
                $areas[$key]['is_area'] = true;
            }

            $data['children'] = array_merge($areaGroups, $areas);
            // キャッシュに保存
            Cache::put($cacheKey, $data, 3600);
        }

        return $data;
    }
}
