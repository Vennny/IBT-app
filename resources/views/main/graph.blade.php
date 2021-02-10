@extends('layouts.app')

@section('title', 'Graph')

@section('content')
    <canvas id="chart" width="100%" height="50%"></canvas>

    <button class="btn btn-primary show-dataset" onclick="toggleDataset()">See the dataset</button>
    <button class="btn btn-primary show-dataset" onclick="toggleRequest()">See the request</button>

    <table class="dataset table table-striped table-bordered table-responsive-md">
        <thead id="thead"></thead>
        <tbody id="tbody"></tbody>
    </table>

    <table class="request table table-striped table-bordered table-responsive-md">
        <tbody>
            <tr>
            @foreach($request AS $input)
                <td>{{$input}}</td>
            @endforeach
            </tr>
            <tr>
                <td colspan="{{count($request)}}">{{$query}}</td>
            </tr>
        </tbody>
    </table>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>
    <script src="{{ asset('js/graph.js') }}"></script>
    <script>
        $(document).ready(function(){
            let data =  @json($results);
            console.log(data);

            createGraph(data);

            createDatasetTable(data);
        });
    </script>
@endpush
