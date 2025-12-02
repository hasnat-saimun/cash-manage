<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $fillable = ['key','value'];
    public $timestamps = true;

    public static function get($key, $default = null) {
        $c = static::where('key',$key)->first();
        return $c ? $c->value : $default;
    }

    public static function set($key, $value) {
        return static::updateOrCreate(['key'=>$key], ['value'=>$value]);
    }
}
