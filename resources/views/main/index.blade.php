@extends('layouts.base')

@section('title', 'Query Builder')

@section('content')
    <div class="administration-form">
        <br>
        <h1>Bulid Query</h1>
        <br>

        <form action="{{ route('main.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name">10 most popular language versions sorted by games played </label>
                <input type="checkbox" name="checkbox" class="check">
            </div>

            <button type="submit" class="btn btn-primary">Confirm</button>
        </form>
    </div>
@endsection
