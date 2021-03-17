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

        @if($request['graphType'] === 'time')
            <div class="col">
                <label for="title">Day moving average: </label>
                <input type="text" class="form-control" id="movingAverage" value="1">
            </div>
            <div class="col">
                <label for="title">Day range from: </label>
                <input type="text" class="form-control" id="rangeStart" value="{{$data[0]['day']}}">
            </div>
            <div class="col">
                <label for="title">Day range to: </label>
                <input type="text" class="form-control" id="rangeEnd" value="{{$data[count($data)-1]['day']}}">
            </div>
        @endif
    </div>

    <div class="graph-data">
        <div class="graph-buttons-container">
            <div class="graph-buttons dataset-switch-buttons">
                <button id="show-dataset" class="btn btn-secondary">Show dataset</button>
                <button id="show-request" class="btn btn-secondary">Show request</button>
            </div>


            <div class="graph-buttons zero-check">
                <input class="form-check-input" type="checkbox" value="" id="zeroCheck" checked>
                <label class="form-check-label" for="flexCheckDefault">Start graph values axis at zero</label>
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


        <table id="dataset-table" class="dataset table table-striped table-bordered table-hover">
            <thead id="thead"></thead>
            <tbody id="tbody"></tbody>
        </table>

        <table class="request table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th colspan="100%">Query Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                @foreach($request AS $input)
                    @if(is_array($input))
                        @if(!empty(array_filter($input)))
                            @foreach($input as $item)
                                @if(!empty($item))
                                  <td>{{$item }}</td>
                                @endif
                            @endforeach
                        @endif
                    @else
                        <td>{{$input}}</td>
                    @endif
                @endforeach
                </tr>
                <tr>
                    <td colspan="100%">{{$query}}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>
    <script type="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.js"></script>
    <script src="{{ asset('js/graph.js') }}"></script>
    <script>
        $(document).ready(function(){
            let data =  @json($data);
            let request = @json($request);

            if (Array.isArray(data) && data.length > 0) {
                createGraph(data, request);

                createDatasetTable(data);
            } else {
                noDataContentSwitch();
            }
        });
    </script>
@endpush
