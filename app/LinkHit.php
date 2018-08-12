<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Переход по короткой ссылке.
 *
 * @property string $user_agent
 * @property int $link_id
 * @property string $ip
 * @property string $session_id
 *
 * @package App
 * @mixin \Eloquent
 */
class LinkHit extends Model
{
    protected $fillable = ['user_agent', 'ip', 'session_id'];

    /**
     * Принадлежит короткой ссылке.
     *
     * @return BelongsTo
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}
