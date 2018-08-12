<?php

namespace App\Acme;

use App\Link;
use Carbon\Carbon;

/**
 * Репозиторий коротких ссылок.
 *
 * @package App\Acme
 */
class LinkRepository
{
    /**
     * Проверяет существует ли ссылка с указанным кодом.
     *
     * @param string $code код
     * @return bool
     */
    public function isExist(string $code): bool
    {
        return Link::where('code', $code)
            ->where('expired_at', '>=', Carbon::now())
            ->exists();
    }

    /**
     * Создает короткую ссылку.
     *
     * @param string $url исходный URL
     * @param string $code код короткой ссылки
     * @param Carbon $expiredAt дата протухания
     * @return Link|\Illuminate\Database\Eloquent\Model
     */
    public function create(string $url, string $code, Carbon $expiredAt): Link
    {
        return Link::create([
            'url' => $url,
            'code' => $code,
            'expired_at' => $expiredAt,
        ]);
    }
}
