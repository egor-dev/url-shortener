<?php

namespace App\Http\Controllers;

use App\Link;
use App\LinkHit;
use Carbon\Carbon;
use App\Acme\UrlShortener;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Exception\RequestException;
use App\Acme\Exception\CodeAlreadyExistException;

/**
 * Class LinksController.
 * @package App\Http\Controllers
 */
class LinksController extends Controller
{
    /**
     * Главный экран.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $links = Link::query()->orderBy('id', 'desc')->take(100)->get();

        $picHits = \DB::table('pic_hits')
            ->selectRaw('filename, count(*) as hit_count')
            ->groupBy('filename')
            ->orderBy('filename')
            ->get();

        return view('index', compact('links', 'picHits'));
    }

    /**
     * Создает короткую ссылку.
     *
     * @param Request $request
     * @param \App\Http\Controllers\UrlShortener $urlShortener
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, UrlShortener $urlShortener)
    {
        $url = $request->get('url');

        try {
            if ($url === null) {
                flash()->error('Please, enter URL.')->important();
            } else {
                $expiredAt = $request->get('expired_at', Carbon::now()->addDays(config('app.max_code_lifetime_days')));
                if (is_string($expiredAt)) {
                    $expiredAt = Carbon::createFromFormat('Y-m-d', $expiredAt);
                }
                $customCode = $request->get('custom_code');
                $link = $urlShortener->shorten($url, $expiredAt, $customCode);
                $shortenUrl = $link->getShortenUrl();
                $hitsUrl = $link->getHitsUrl();
                flash()->success(
                    "<strong>Here is your shorten url: <a href='$shortenUrl' target='_blank'>$shortenUrl</a></strong><br>
                    Expiration date: {$expiredAt->toDayDateTimeString()}<br>
                    Statistics: <a href='$hitsUrl' target='_blank'>$hitsUrl</a>"
                )->important();
            }
        } catch (RequestException $exception) {
            flash()->error('Page does not exist.');
        } catch (\InvalidArgumentException $exception) {
            flash()->error($exception->getMessage());
        } catch (CodeAlreadyExistException $exception) {
            flash()->error('Sorry, this custom code already exist. Please, try something else.');
        }

        return redirect()->back()->withInput($request->all());
    }

    /**
     * Переход по короткой ссылке (редирект).
     *
     * @param $code
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function forward($code, Request $request)
    {
        $request->session()->start();

        /** @var Link $link */
        $link = Link::where('code', $code)->first();

        $hit = new LinkHit([
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'session_id' => $request->session()->getId(),
        ]);
        $link->hits()->save($hit);

        $redirectToUrl = $link->url;

        $randomImage = array_random($this->getImages());
        $imageSrc = Storage::disk('public')->url($randomImage);

        \DB::table('pic_hits')->insert(['filename'=>$randomImage]);

        return view('forward', compact('redirectToUrl', 'imageSrc'));
    }

    /**
     * Статистика.
     *
     * @param $code
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function hits($code)
    {
        $link = Link::withCode($code)->firstOrFail();

        $hits = $link->hits()->orderBy('id', 'desc')->paginate(10);

        $uniqueHits = $link->hits()
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->get()
            ->unique(function ($item) {
                return $item['user_agent'].$item['ip'].$item['session_id'];
            })
            ->count();
        
        return view('hits', compact('hits', 'link', 'uniqueHits'));
    }

    /**
     * @return array
     */
    private function getImages(): array
    {
        $images = array_filter(
            scandir(storage_path('img'), null),
            function ($item) {
                return ($item != '.') && ($item !== '..');
            }
        );

        return $images;
    }
}
