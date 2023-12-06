<?php

namespace App\Models;

use App\Enums\EnvironmentEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class WhiteList extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['who'];


    protected static function boot()
    {
        parent::boot();
        static::created(function (WhiteList $whiteList) {
            $testIp = DB::connection('test_server')->table('white_lists')->where('ip', $whiteList->ip)->where('env', $whiteList->env)->first();

            if (!$testIp) {
                DB::connection('test_server')->table('white_lists')->insert([
                    'ip' => $whiteList->ip,
                    'env' => $whiteList->env,
                    'desc' => $whiteList->desc,
                    'who' => 108,
                ]);
            }
        });

        static::deleted(function (WhiteList $whiteList) {
            DB::connection('test_server')->table('white_lists')->where('ip', $whiteList->ip)->where('env', $whiteList->env)->delete();
        });
    }

    public function who(): BelongsTo
    {
        return $this->belongsTo(User::class, 'who');
    }
}
