@extends('layouts.base')


@section('content')
    <h1 style="text-align:center;">10 most popular language versions</h1>
    <div><p id="demo"></p></div>
    <canvas id="myChart" width="200" height="500"></canvas>
@endsection

@push('scripts')
    <script src="{{url( 'vendor/Chart.min.js' )}}"></script>
    <script>
        let data = {!! json_encode($results, JSON_HEX_TAG) !!};
        //data = data.slice(0,10);

        let labels = data.map(a => a.id_lang);
        let values = data.map(a => a.games);

        let ctx = document.getElementById('myChart').getContext('2d');
        let myChart = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: labels,
                datasets: [{
                    label: '# of games played',
                    data: values,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 0, 0, 0.2)',
                        'rgba(0, 255, 0, 0.2)',
                        'rgba(0, 0, 255, 0.2)',
                        'rgba(100, 102, 100, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 0, 0, 1)',
                        'rgba(0, 255, 0, 1)',
                        'rgba(0, 0, 255, 1)',
                        'rgba(100, 102, 100, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                legend: {
                    display: false,
                },
                scales: {
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: "language"
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }],
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: "# of games"
                        },
                    }]
                }
            }
        });
    </script>
@endpush
