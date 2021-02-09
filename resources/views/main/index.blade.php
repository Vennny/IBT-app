@extends('layouts.app')

@section('title', 'Query Builder')

@section('content')
    <div id="loader">
        <div class="loader_text" style="display: none;">
            <h1>This might take a few minutes...</h1>
        </div>
        <div class="icon" style="display: none;"></div>
    </div>
    <div class="administration-form">
        <br>
        <h1>Bulid Query</h1>
        <br>

        <form id="query_builder" action="/" method="POST">
            @csrf

            <div class="form-group">
                <label for="chart_type">Chart type: </label>
                <select id="chart_type" name="chart_type">
                    <option value="popular">Most popular chart</option>
                {{-- <option value="1">Time chart</option> --}}
                </select><br>

                <div class="count">
                    <label for="count">Count most selected: </label>
                    <select id="count" name="count">
                        <option value="category">category</option>
                        <option value="word">words</option>
                    </select><br>
                </div>

                <div class="language">
                    <label for="language">In language:</label>
                    <select id="language" name="language">
                        @foreach($languages as $lang)
                            <option value="{{$lang->id}}">{{$lang->show_name}}</option>
                        @endforeach
                    </select><br>
                </div>

                <div style="display: none" class="countries_datalist">
                    <label for="country">From player from country: </label>
                    <input list="country" name="country" class="datalist-input" />
                    <datalist id="country">
                        <option value="">
                        @foreach($countries as $country)
                            <option value="{{$country['name']}}">
                        @endforeach
                    </datalist>
                </div>

                <div style="display: none" class="category">
                    <label for="category">Category name:</label>
                    <input type="text" id="category" name="category" value=""><br>
                </div>
                <div style="display: none" class="letter">
                    <label for="letter">Starting letter:</label>
                    <input type="text" id="letter" name="letter" maxlength="1" value=""><br>
                </div>

                <div hidden class="word">
                    <label for="word">Word</label><br>
                    <input type="text" id="word" name="word" value=""><br>
                </div>

                <div class="limit">
                    <label for="limit">Select number of entries</label>
                    <input type="number" id="limit" name="limit" min="1" value="5"><br>
                </div>
            </div>


            <button onclick="sendQuery()" class="btn btn-primary" >Confirm</button>
        </form>
    </div>
@endsection
@push('scripts')
    <script>



        $(document).ready(function(){
            /*
            let countries = {!! json_encode($countries, JSON_HEX_TAG) !!};

            $("#count").change(function (){
                const div = document.createElement('div');
                div.className = 'country';
                div.innerHTML = `
                    <label for="country">Playing in country: </label>
                    <input list="country" name="country" class="datalist-input" />
                    <datalist id="country">
                        <option value="All">
                  `;

                console.log(countries);

                countries.forEach(function (country){
                    div.innerHTML =  div.innerHTML.concat(`<option value="`, country.name, `">`);
                })

                div.innerHTML =  div.innerHTML.concat(`</datalist>`);

                document.getElementById('countries_datalist').appendChild(div);
            });
            */

            $("#count").change(function (){
                $('#countries_datalist').show();
                if (document.getElementsByClassName('countries_datalist'))

                if(this.value === 'word'){
                  document.getElementsByClassName('countries_datalist')[0].style.display = 'block';
                  document.getElementsByClassName('category')[0].style.display = 'block';
                    document.getElementsByClassName('letter')[0].style.display = 'block';
                }
                else {
                  document.getElementsByClassName('countries_datalist')[0].style.display = 'none';
                  document.getElementsByClassName('category')[0].style.display = 'none';
                    document.getElementsByClassName('letter')[0].style.display = 'none';
                }
            });

            $('#country').change(function() {
                if ( this.value !== 'all')
                {
                    //let textnode = document.createTextNode("<label for='country2'>Playing in country: </label><select id='country2' name='country2'><option value='en'>english</option><option value='es'>spanish</option><option value='de'>german</option> </select><br>");
                    let textnode = document.createTextNode("<h2>FAWGFAf</h2>")
                    let div = document.getElementsByClassName('countries_datalist');
                    div.appendChild(textnode);
                }
            });
        });
    </script>
@endpush
