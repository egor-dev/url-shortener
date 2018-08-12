@extends('layout')

@section('content')
    <div class="container text-center">
        <img src="{{ $imageSrc }}" />
    </div>
    <script>
        window.setTimeout(function(){
            window.location.href = "{{ $redirectToUrl }}";
        }, 5000);
    </script>
@endsection