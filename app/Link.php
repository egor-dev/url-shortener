<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Короткая ссылка.
 *
 * @property int $id
 * @property string $code
 * @property string $url
 * @property string $created_at
 * @package App
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Link withCode($code)
 * @mixin \Eloquent
 */
class Link extends Model
{
    protected $fillable = ['code', 'url', 'expired_at'];

    protected $dates = ['expired_at', 'created_at'];

    /**
     * Ссылка имеет много переходов.
     *
     * @return HasMany
     */
    public function hits(): HasMany
    {
        return $this->hasMany(LinkHit::class);
    }

    /**
     * Получить короткую ссылку.
     *
     * @return string
     */
    public function getShortenUrl(): string
    {
        return env('APP_URL').'/'.$this->code;
    }

    /**
     * Получить URL на статистику переходов по данной короткой ссылке.
     *
     * @return string
     */
    public function getHitsUrl()
    {
        return $this->getShortenUrl().'/hits';
    }

    /**
     * @param $query
     * @param $code
     * @return mixed
     */
    public function scopeWithCode(Builder $query, $code)
    {
        return $query->where('code', $code);
    }
}
