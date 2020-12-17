@extends('base')


@section('content')
    <table id="content" class="table table-striped table-bordered table-responsive-md">
        <thead>
        <tr>
            <th>Language</th>
            <th>Number of games</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($results as $result)
            <tr>
                <td>{{ $result->id_lang }}</td>
                <td>{{ $result->games }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
