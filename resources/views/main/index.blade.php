@extends('layouts.app')

@section('title', 'Query Builder')

@section('content')
    <div id="loader">
        <div class="icon"></div>
        <div class="loader-text">
            <h1 id="loadingText">Executing query, do not close this page!</h1><br>
            <h2 id="loadingWarning">This might take a few minutes...</h2>
        </div>
    </div>
    <div class="administration-form">
        <br>
        <h1>Build Query</h1>
        <br>

        <form id="queryBuilder" action="/" method="POST">
            @csrf

            <div class="form-group">
                <label for="graphType">Chart type: </label>
                <select id="graphType" name="graphType">
                    <option value="popular">Answer/category popularity</option>
                    <option value="total">Total amount of answers</option>
                    <option value="time">Time chart</option>
                </select><br>

                <div id="wordDiv"></div>

                <div id="countDiv">
                    <div class="count">
                        <label for="count">Count most selected: </label>
                        <select id="count" name="count">
                            <option id="countCategory" value="category">category</option>
                            <option id="countAnswer" value="answer">answers</option>
                        </select><br>
                    </div>
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

                <div id="limitDiv">
                    <div class="limit">
                        <label for="limit">Select number of entries</label>
                        <input type="number" id="limit" name="limit" min="1" value="5" required><br>
                    </div>
                </div>

                <div class="percentage-switch"></div>
            </div>

            <button type="submit" class="btn btn-primary" >Confirm</button>
        </form>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/loading.js') }}"></script>
    <script src="{{ asset('js/form.js') }}"></script>
    <script>
        $(document).ready(function(){
            let countries = @json($countries->all());


            setCountries(countries);
        });
    </script>
@endpush
