@extends('layouts.app')

@section('title', 'Graph')

@section('content')
    <canvas id="chart" width="100%" height="50%"></canvas>


    <button class="btn btn-primary" style="margin-top: 20px" onclick="showDataset()">See the dataset</button>
    <div id="data" style="margin-top: 10px; display: none;"  ></div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>
    <script src="{{ asset('js/graph.js') }}"></script>
    <script>
        $(document).ready(function(){
            let data = {!! json_encode($results, JSON_HEX_TAG) !!};
            console.log(data);

            createGraph(data);

            createDatasetTable(data);
        });
    </script>
@endpush
