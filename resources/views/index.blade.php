@extends('layout')

@section('title', 'URL Shortener')

@section('content')
    {!! Form::open(['method'=>'POST']) !!}

    <div class="form-group">
        <label for="email">URL:</label>
        {!! Form::input('text', 'url', null, ['class'=>'form-control', 'placeholder'=>'URL', 'style'=>'width:100%']) !!}
    </div>
    <div class="form-group">
        <label for="email">Custom Code:</label>
        {!! Form::input('text', 'custom_code', null, ['class'=>'form-control col-md-6', 'placeholder'=>'Custom code', 'maxlength' => config('app.code_length')]) !!}
    </div>
    <div class="form-group">
        <label for="email">Expiration Date:</label>
        {!! Form::date('expired_at', \Carbon\Carbon::now()->addYear(), ['class'=>'form-control col-md-6']) !!}
    </div>
    {!! Form::submit('Shorten!', ['class'=>'btn btn-primary col-md-3']) !!}

    {!! Form::close() !!}

@endsection

@section('footer')
    @if($picHits->count())
        <div class="card bg-light" style="margin-bottom: 30px;">
            <div class="card-body">
                <h5>Ad Showings</h5>
                <small>For demo purposes</small>
                <br>
                <br>
                <div class="row container">
                @foreach($picHits as $pic)
                    <div class="card" style="width:200px; margin-right: 10px;">
                        <img class="card-img-top" src="{{ Storage::disk('public')->url($pic->filename) }}" alt="{{ $pic->filename }}">
                        <div class="card-img-overlay">
                            <span class="badge badge-pill badge-light">{{ $pic->hit_count }}</span>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($links->count())
        <div class="card bg-light">
            <div class="card-body">
                <h5>Last 100 URLs</h5>
                <small>For demo purposes</small>
                <br>
                <br>
                <table class="table-condensed table">
                    @foreach($links as $link)
                        <tr>
                            <td><a href="{{ $link->getShortenUrl() }}">{{ $link->getShortenUrl() }}</a></td>
                            <td><a href="{{ $link->url }}">{{ $link->url }}</a></td>
                            <td>{{ $link->expired_at->toDayDateTimeString() }}</td>
                            <td><a href="{{ $link->getHitsUrl() }}">Hits</a></td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endif
@endsection
