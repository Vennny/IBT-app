@extends('layouts.app')

@section('title', 'Graph')

@section('content')
    <div id="graph-container">
        <canvas id="chart" width="100%" height="60%"></canvas>
    </div>

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
                <button id="show-dataset" class="btn btn-secondary">Show dataset</button>
                <button id="show-request" class="btn btn-secondary">Show request</button>
            </div>

            <div class="graph-buttons export-buttons">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Save graph as
                </button>
                <div class="dropdown-menu">
                    <button id="download-graph-pdf" class="dropdown-item">PDF</button>
                    <button id="download-graph-png" class="dropdown-item">PNG</button>
                </div>
                <button id="download-csv" class="btn btn-primary">Export dataset to csv</button>
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


@push('head-scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
    <script type="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.js"></script>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>
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
