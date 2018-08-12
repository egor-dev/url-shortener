<?php

namespace App\Acme;

use App\Link;
use Carbon\Carbon;
use App\Acme\Exception\CodeAlreadyExistException;

/**
 * Сокращатель ссылок.
 *
 * @package App\Acme
 */
class UrlShortener
{
    /**
     * Максимальная длина кода короткой ссылки.
     *
     * @var int
     */
    private $maxCodeLength;

    /**
     * Максимально количество дней жизни короткой ссылки.
     *
     * @var int
     */
    private $maxCodeLifetimeDays;

    /**
     * Репозиторий коротких ссылок.
     *
     * @var LinkRepository
     */
    private $repository;

    public function __construct(LinkRepository $repository)
    {
        $this->repository = $repository;
        $this->maxCodeLength = config('app.max_code_length');
        $this->maxCodeLifetimeDays = config('app.max_code_lifetime_days');
    }

    /**
     * Сократить ссылку.
     *
     * @param string $url исходный URL
     * @param Carbon $expiredAt дата протухания
     * @param null|string $customCode свой код
     * @throws CodeAlreadyExistException
     * @throws \InvalidArgumentException
     * @return Link
     */
    public function shorten(string $url, Carbon $expiredAt, string $customCode = null): Link
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);

        $this->checkUrlValid($url);
        $this->checkExpirationValid($expiredAt);

        $code = $customCode === null ?
            $this->generateCode($url) :
            $this->validateCustomCode($customCode);

        $this->checkCodeDoNotExist($code);

        return $this->repository->create($url, $code, $expiredAt);
    }

    /**
     * @param string $url
     */
    private function checkUrlValid(string $url): void
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid url.');
        }
    }

    /**
     * @param Carbon $expiredAt
     */
    private function checkExpirationValid(Carbon $expiredAt): void
    {
        if (!$expiredAt->between(Carbon::now(), Carbon::now()->addDays($this->maxCodeLifetimeDays))) {
            throw new \InvalidArgumentException('Invalid expiration date.');
        }
    }

    /**
     * @param $code
     * @throws CodeAlreadyExistException
     */
    private function checkCodeDoNotExist($code): void
    {
        if ($this->repository->isExist($code)) {
            throw new CodeAlreadyExistException('Code already exist.');
        }
    }

    /**
     * @param string $customCode
     * @return string
     */
    private function validateCustomCode(string $customCode): string
    {
        $length = \strlen($customCode);
        if ($length > $this->maxCodeLength) {
            throw new \InvalidArgumentException('Invalid custom code.');
        }

        if (preg_match('/[^A-Za-z0-9]+/', $customCode)) {
            throw new \InvalidArgumentException('Invalid custom code.');
        }

        return $customCode;
    }

    /**
     * @return bool|string
     */
    private function generateCode()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';

        for ($i = 0; $i < $this->maxCodeLength; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }
}
