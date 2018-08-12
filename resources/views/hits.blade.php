@extends('layout')

@section('style')
    <style>
        .hit_row {
            font-size:smaller;
        }
    </style>

@endsection

@section('content')
    <table class="table-bordered table">
        <tr>
            <th>Original URL</th>
            <td><a href="{{ $link->url }}" target="_blank">{{ $link->url }}</a></td>
        </tr>
        <tr>
            <th>Shorten URL</th>
            <td><a href="{{ $link->getShortenUrl() }}" target="_blank">{{ $link->getShortenUrl() }}</a></td>
        </tr>
        <tr>
            <th>Expiration date</th>
            <td>{{ $link->expired_at->toDayDateTimeString() }} ({{ $link->expired_at->diffForHumans(Carbon\Carbon::now()) }})</td>
        </tr>
    </table>

    <div style="margin: 20px 0;">
        Unique hits for last 14 days: {{ $uniqueHits }}
    </div>

    @if($hits->count())
        <table class="table">
            <tr>
                <th>Date</th>
                <th>IP</th>
                <th>User-Agent</th>
                <th>Session Id</th>
            </tr>
            @foreach($hits as $hit)
                <tr class="hit_row">
                    <td class="text-nowrap">{{ $hit->created_at->toDayDateTimeString() }}</td>
                    <td>{{ $hit->ip }}</td>
                    <td>{{ $hit->user_agent }}</td>
                    <td>{{ $hit->session_id }}</td>
                </tr>
            @endforeach
        </table>
        {{ $hits->links() }}
    @else
        Nothing yet.
    @endif

@endsection
