<?php

namespace Cloudcake\Prefs\Models;

use Cloudcake\Prefs\Preferences;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property array $config
 */
class UserPreference extends Model
{
    use HasUuids;

    private static array $cachedConfig = [];
    private static string $cachedConfigKey = 'filament-preferences-%s';

    private static int $cacheTTL = 360;

    protected function casts(): array
    {
        return [
            'configurable_id'   => 'string',
            'configurable_type' => 'string',
            'config'            => 'array'
        ];
    }

    protected $fillable = [
        'configurable_id',
        'configurable_type',
        'config'
    ];

    public static function boot(): void
    {
        parent::boot();

        static::saved(function (UserPreference $model) {
            static::$cachedConfig[$model->configurable_id] = $model;

            Cache::put(static::getCacheKey($model->configurable_id), $model, static::$cacheTTL);
        });
    }

    public function getTable(): string
    {
        return Preferences::getTableName();
    }

    public static function current(): static
    {
        $user = auth()->user();

        if (!$user)
            return new static();

        if (isset(static::$cachedConfig[$user->getKey()]))
            return static::$cachedConfig[$user->getKey()];

        $config = Cache::get(self::getCacheKey());

        if (!$config)
            $config = static::firstOrCreate([
                'configurable_id'   => $user->getKey(),
                'configurable_type' => $user->getMorphClass(),
            ]);

        static::$cachedConfig[$user->getKey()] = $config;

        return $config;
    }

    private static function getCacheKey(?string $key = null): string
    {
        return sprintf(self::$cachedConfigKey, $key ?? auth()->user()->getKey());
    }
}