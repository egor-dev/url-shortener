<?php

namespace Tests\Feature;

use App\Link;
use App\LinkHit;
use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class UrlShortenerFeatureTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();
        $this->withoutMiddleware();
        //$this->followingRedirects();
    }

    public function test_see_main_page()
    {
        $response = $this->get('/');
        $this->assertEquals(200, $response->getStatusCode());
        $response->assertSee('URL Shortener');
        $response->assertSee('URL');
        $response->assertSee('Custom code');
        $response->assertSee('Custom code');
        $response->assertSee('Shorten!');
    }

    public function test_see_statistics_page()
    {
        Link::create([
            'url' => 'http://google.com',
            'code' => 'mytext',
            'expired_at' => Carbon::now()->addYear(),
        ]);

        $response = $this->get('/mytext/hits');

        $this->assertEquals(200, $response->getStatusCode());
        $response->assertSee('URL Shortener');
        $response->assertSee('Original URL');
        $response->assertSee('Shorten URL');
        $response->assertSee('Expiration date');
        $response->assertSee('Unique hits for last 14 days');
    }

    public function test_it_shorten_url()
    {
        $this->followingRedirects();

        $response = $this->post('/', [
            'url'=>'http://google.com'
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $response->assertSee('Here is your shorten url');
    }

//    public function test_following_short_link_redirects_to_original_url()
//    {
//        $originalUrl = 'http://google.com';
//
//        Link::create([
//            'url' => $originalUrl,
//            'code' => 'mytext',
//            'expired_at' => Carbon::now()->addDays(30),
//        ]);
//
//        $this->get('/mytext', ['user_agent'=>'some_user_agent'])->assertRedirect($originalUrl);
//    }

//    public function test_following_short_link_creates_link_hit()
//    {
//        Link::create([
//            'url' => 'http://google.com',
//            'code' => 'mytext',
//            'expired_at' => Carbon::now()->addDays(30),
//        ]);
//
//        $this->get('/mytext', ['user_agent'=>'some_user_agent'])->assertStatus(200);
//
//        $this->assertDatabaseHas('link_hits', [
//            'link_id' => 1,
//            'user_agent' => 'some_user_agent',
//            'ip' => '127.0.0.1'
//        ]);
//    }

    public function test_create_link_with_custom_code()
    {
        $this->followingRedirects();

        $customCode = 'mytext';
        $response = $this->post('/', [
            'url' => 'http://google.com',
            'custom_code' => $customCode,
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $response->assertSee('Here is your shorten url');

        $appUrl = env('APP_URL');
        $response->assertSee("$appUrl/$customCode");
    }

    public function test_create_link_with_custom_expired_at()
    {
        $this->followingRedirects();

        $expiredAt = Carbon::now()->addWeek();
        $response = $this->post('/', [
            'url' => 'http://google.com',
            'expired_at' => $expiredAt->toDateString(),
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $response->assertSee('Here is your shorten url')
            ->assertSee($expiredAt->toDayDateTimeString());
    }
}
