<?php

namespace Tests\Unit;

use App\Acme\UrlShortener;
use App\Link;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UrlShortenerUnitTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var UrlShortener
     */
    public $shortener;
    public $defaultExpiredAt;

    protected function setUp()
    {
        parent::setUp();
        $this->shortener = $this->app->make(UrlShortener::class);
        $this->defaultExpiredAt = Carbon::now()->addDays(30);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_it_shortens_url()
    {
        $link = $this->shortener->shorten('http://google.com', $this->defaultExpiredAt);

        $this->assertEquals(12, \strlen($link->code));
    }

    public function test_it_makes_two_different_codes_for_same_url()
    {
        $firstLink = $this->shortener->shorten('http://google.com', $this->defaultExpiredAt);
        $secondLink = $this->shortener->shorten('http://google.com', $this->defaultExpiredAt);

        $this->assertNotEquals($firstLink->code, $secondLink->code);
    }

    public function test_it_throws_exception_on_invalid_url()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->shortener->shorten('hello world', $this->defaultExpiredAt);
    }

    public function test_it_creates_link_with_custom_code()
    {
        $url = 'http://google.com';
        $customCode = 'mytext';

        $code = $this->shortener->shorten($url, $this->defaultExpiredAt, $customCode);

        $this->assertEquals('mytext', $code->code);

        $this->assertDatabaseHas('links', [
            'url' => $url,
            'code' => $customCode
        ]);
    }

    public function test_it_throws_exception_on_invalid_custom_code()
    {
        $url = 'http://google.com';
        $invalidCustomCode = 'my_text';
        $this->expectException('InvalidArgumentException');
        $this->shortener->shorten($url, $this->defaultExpiredAt, $invalidCustomCode);
        $this->assertDatabaseMissing('links', [
            'url' => $url,
            'code' => $invalidCustomCode
        ]);

        $invalidCustomCode = 'привет';
        $this->expectException('InvalidArgumentException');
        $this->shortener->shorten($url, $this->defaultExpiredAt, $invalidCustomCode);
        $this->assertDatabaseMissing('links', [
            'url' => $url,
            'code' => $invalidCustomCode
        ]);
    }

    public function test_it_throws_exception_on_too_long_custom_code()
    {
        $url = 'http://google.com';
        $invalidCustomCode = 'myteeeeeeeeeeeeeeeext';

        $this->expectException('InvalidArgumentException');

        $this->shortener->shorten($url, $this->defaultExpiredAt, $invalidCustomCode);

        $this->assertDatabaseMissing('links', [
            'url' => $url,
            'code' => $invalidCustomCode
        ]);
    }

    public function test_it_throws_exception_on_creating_link_with_already_existing_custom_code()
    {
        Link::create([
            'url' => 'http://google.com',
            'code' => 'mytext',
            'expired_at' => Carbon::now()->addWeek(),
        ]);

        $this->expectException('App\Acme\Exception\CodeAlreadyExistException');
        $this->shortener->shorten('http://google.com', Carbon::now()->addWeek(), 'mytext');
    }

    public function test_it_creates_link_with_same_custom_code_when_previous_expired()
    {
        Link::create([
            'url' => 'http://google.com',
            'code' => 'mytext',
            'expired_at' => Carbon::now()->subDay(),
        ]);

        $link = $this->shortener->shorten('http://google.com', Carbon::now()->addWeek(), 'mytext');
        $this->assertEquals('mytext', $link->code);
    }

    public function test_it_creates_link_with_custom_expiration_date()
    {
        $url = 'http://google.com';
        $customExpiredAt = Carbon::now()->addWeek();

        $this->shortener->shorten($url, $customExpiredAt);

        $this->assertDatabaseHas('links', [
            'url' => $url,
            'expired_at' => $customExpiredAt
        ]);
    }

    public function test_it_throws_exception_on_invalid_expiration_date()
    {
        $url = 'http://google.com';

        $this->expectException('InvalidArgumentException');
        $this->shortener->shorten($url, Carbon::now()->subWeek());

        $this->expectException('InvalidArgumentException');
        $this->shortener->shorten($url, Carbon::now()->subSecond());

        $this->expectException('InvalidArgumentException');
        $this->shortener->shorten($url, Carbon::now()->addYears(2));

        $this->assertDatabaseMissing('links', [
            'url' => $url,
        ]);
    }
}
