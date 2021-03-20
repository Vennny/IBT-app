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
            <div class="form-row main-div">
                <div class="form-group col-md-6">
                    <label for="graphType">Chart type: </label>
                    <select id="graphType" class="custom-select" name="graphType">
                        <option value="popular">Answer/category popularity</option>
                        <option value="total">Total amount of answers</option>
                        <option value="time">Time graph</option>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <div id="countTableDiv"></div>
                </div>
            </div>

            <div class="form-row">
                <div id="wordDiv"></div>
            </div>

            <div class="form-row">
                <div id="countriesDiv"></div>
            </div>

            <div class="form-row">
                <div id="categoriesDiv"></div>
            </div>

            <div class="form-row main-div">
                <div class="col language">
                    <label for="language">In language:</label>
                    <select id="language" class="custom-select" name="language" required>
                        <option disabled selected value> -- select language -- </option>
                        <option value="all">all</option>
                        @foreach($languages as $lang)
                            <option value="{{$lang->id}}">{{$lang->show_name}}</option>
                        @endforeach
                    </select><br>
                </div>

                <div id="letterDiv"></div>

                <div id="limitDiv"></div>
            </div>

            <div class="form-row">
                <div class="percentage-switch"></div>
            </div><br>

            <div id="countryDatalist"></div>

            <button type="submit" class="btn btn-primary">Confirm</button>
        </form>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('js/loading.js') }}"></script>
    <script src="{{ asset('js/form.js') }}"></script>
    <script>
        $(document).ready(function(){
            let countries = @json($countries->all());


            setCountriesDataset(countries);
        });
    </script>
@endpush
