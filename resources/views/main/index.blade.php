@extends('layouts.app')

@section('title', 'Query Builder')

@section('content')
    <div id="loader">
        <div class="icon"></div>
        <div class="loader_text">
            <h1>This might take a few minutes...</h1><br>
            <h2>Do not close this page</h2>
        </div>
    </div>
    <div class="administration-form">
        <br>
        <h1>Build Query</h1>
        <br>

        <form id="query_builder" action="/" method="POST">
            @csrf

            <div class="form-group">
                <label for="chart_type">Chart type: </label>
                <select id="chart_type" name="chart_type">
                    <option value="popular">Answer/category popularity</option>
                    <option value="total">Total amount of answers</option>
                    <option value="time" disabled>Time chart</option>
                </select><br>

                <div class="count">
                    <label for="count">Count most selected: </label>
                    <select id="count" name="count">
                        <option id="count_category" value="category">category</option>
                        <option id="count_answer" value="answer">answers</option>
                    </select><br>
                </div>

                <div class="form-answer-switch"></div>

                <div class="language">
                    <label for="language">In language:</label>
                    <select id="language" name="language">
                        @foreach($languages as $lang)
                            <option value="{{$lang->id}}">{{$lang->show_name}}</option>
                        @endforeach
                    </select><br>
                </div>

                <div class="limit">
                    <label for="limit">Select number of entries</label>
                    <input type="number" id="limit" name="limit" min="1" value="5"><br>
                </div>

                <div class="percentage-switch"></div>
            </div>


            <button onclick="sendQuery()" class="btn btn-primary" >Confirm</button>
        </form>
    </div>
@endsection
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.5/jspdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
    <script src="{{ asset('js/loading.js') }}"></script>
    <script src="{{ asset('js/form.js') }}"></script>
    <script>
        $(document).ready(function(){
            let countries = @json($countries->all());

            $("#chart_type").change(function (){
                changeFormType(countries);
            });

            $("#count").change(function (){
                changeCountForm(countries);
            });

            // $("#download-csv").click(function() {
            //     console.log("here");
            //     let csv = $(this).table2CSV();
            //     window.location.href = 'data:text/csv;charset=UTF-8,'
            //         + encodeURIComponent(csv);
            // })
        });
    </script>
@endpush
