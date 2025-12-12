<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $fillable = ['key','value','business_id'];
    public $timestamps = true;

    public static function get($key, $default = null) {
        $query = static::where('key',$key);
        if (session()->has('business_id')) {
            $query->where('business_id', session('business_id'));
        }
        $c = $query->first();
        return $c ? $c->value : $default;
    }

    public static function set($key, $value) {
        $attrs = ['key'=>$key];
        if (session()->has('business_id')) {
            $attrs['business_id'] = session('business_id');
        }
        return static::updateOrCreate($attrs, ['value'=>$value]);
    }
}
