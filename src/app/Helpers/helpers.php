<?php

use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;

if (!function_exists('checkMenuPermissions')) {
    function checkMenuPermissions($mainMenu)
    {
        $permissions = [];
        if (array_key_exists('sub', $mainMenu)) {
            foreach ($mainMenu['sub'] as $menu) {
                if (array_key_exists('permission', $menu)) {
                    $permissions[] = $menu['permission'];
                }
            }
            $menuPermissions = Permission::whereIn('name', $permissions)->get();
            return $menuPermissions->intersect(Auth::user()->roles[0]->permissions)->isNotEmpty();
        } elseif (array_key_exists('permission', $mainMenu)) {
            return Auth::user()->roles[0]->hasPermissionTo($mainMenu['permission']);
        } else {
            return true;
        }
    }
}

if(!function_exists('arabicDate')){
    function arabicDate($date)
    {
        return Carbon::parse($date)->locale('ar')->isoFormat('D MMMM YYYY');
    }
}

if (!function_exists('adminUrl')) {
    function adminUrl($href)
    {
        return url(RouteServiceProvider::ADMIN_PREFIX . '/' . $href);
    }
}

if (!function_exists('mapArrayWithKey')) {
    function mapArrayWithKey($arr, $trans = null)
    {
        return collect($arr)->mapWithKeys(function ($type, $key) use ($trans) {
            return [$key => __("{$trans}.{$type}")];
        });
    }
}

if (!function_exists('saveSingleImage')) {
    function saveSingleImage($rImage, $imgName = '', $folder = '')
    {
        $image = is_array($rImage) ? $rImage[0] : $rImage;
        $randomText = Str::random(5);
        $authorityPath = public_path('/uploads') . (!$folder ?: '/' . $folder);
        $imageName = time() . "_{$imgName}_{$randomText}.png";
        $image->move($authorityPath, $imageName);
        return $imageName;
    }
}

if (!function_exists('getRandomNumberRand')) {
function getRandomNumberRand($length = 8)
{
    $stringSpace = '0123456789';
    $stringLength = strlen($stringSpace);
    $randomString = '';
    for ($i = 0; $i < $length; $i ++) {
        $randomString = $randomString . $stringSpace[rand(0, $stringLength - 1)];
    }
    return $randomString;
}
}
if (!function_exists('timeToStr')) {
    function timeToStr($timestamp, $num_times = 2)
    {
        $date = Carbon::create($timestamp->toDateTimeString())->locale('ar');
        return $date->ago(['parts' => $num_times]);
    }
}
if (!function_exists('is_json')) {
    function is_json($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }
}

if (!function_exists('get_faculties_departments')) {

    /**
     * get All departments per college
     * @param $faculties
     * @return array
     */
    function get_faculties_departments($faculties)
    {
        $departments=[];
        foreach ($faculties as $faculty){
            foreach ($faculty->getAllChildrenDepartments() as $department ){
                array_push($departments,['id'=>$department->id,'name'=>$faculty->name.' - '. $department->name]);
            }
        }
        return $departments;
    }
}
