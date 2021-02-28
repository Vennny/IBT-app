@extends('layouts.app')

@section('title', 'Graph')

@section('content')
    <canvas id="chart" width="100%" height="60%"></canvas>

    <div class="error"></div>

    <div class="form-row">
        <div class="col">
            <label for="title">Graph title:</label>
            <input type="text" class="form-control" id="title">
            <input type="range" class="font-slider" id="title-font-slider" min="1" max="50">
        </div>
        <div class="col">
            <label for="title">X axis label:</label>
            <input type="text" class="form-control" id="x-axis-label">
            <input type="range" class="font-slider" id="x-font-slider" min="1" max="50">
        </div>
        <div class="col">
            <label for="title">Y axis label:</label>
            <input type="text" class="form-control" id="y-axis-label">
            <input type="range" class="font-slider" id="y-font-slider" min="1" max="50">
        </div>
    </div>

    <div class="graph-data">

        <div class="graph-buttons-container">
            <div class="graph-buttons dataset-switch-buttons">
                <button class="btn btn-secondary show-dataset">Show dataset</button>
                <button class="btn btn-secondary show-request">Show request</button>
            </div>

            <div class="graph-buttons export-buttons">
                <button class="btn btn-primary" disabled>Export graph to pdf</button>
                <button class="btn btn-primary" disabled>Export dataset to csv</button>
            </div>
        </div>

        <table class="dataset table table-striped table-bordered table-hover">
            <thead id="thead"></thead>
            <tbody id="tbody"></tbody>
        </table>

        <table class="request table table-striped table-bordered table-hover">
            <thead><tr><th colspan="{{count($request)}}">Query Details</th></tr></thead>
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
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>
    <script src="{{ asset('js/graph.js') }}"></script>
    <script>
        $(document).ready(function(){
            let data =  @json($results);

            if (Array.isArray(data) && data.length > 0) {
                console.log(data);

                createGraph(data);

                createDatasetTable(data);
            } else {
                noDataContentSwitch();
            }

        });
    </script>
@endpush
