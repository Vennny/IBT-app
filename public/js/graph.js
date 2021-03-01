let graph;

//label customizations
$("#title").on('input', function (){
    changeGraphTitle(this);
});

$("#x-axis-label").on('input', function (){
    changeXAxisLabel(this);
});

$("#y-axis-label").on('input', function (){
    changeYAxisLabel(this);
});

$("#title-font-slider").on('input', function (){
    changeGraphTitleFontSize(this);
});

$("#x-font-slider").on('input', function (){
    changeXAxisLabelFontSize(this);
});

$("#y-font-slider").on('input', function (){
    changeYAxisLabelFontSize(this);
});

//button clicks
$("#show-dataset").click(function() {
    toggleDataset();
})

$("#show-request").click(function() {
    toggleRequest();
})

$("#download-graph-pdf").click(function() {
    saveAsPDF();
})

$("#download-graph-png").click(function() {
    saveAsPNG();
})

$("#download-csv").click(function() {
    saveCSV();
})

function saveCSV() {
    let table = $('#dataset-table');
    table.css("display", "table");
    table.table2csv();
    table.css("display", "none");
}

function saveAsPNG() {
    html2canvas($("#graph-container")).then(canvas => {
        let link = document.createElement('a');
        link.href = canvas.toDataURL();
        link.download = 'graph.png';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
}

function saveAsPDF() {
    console.log("save");
    html2canvas($("#graph-container")).then(canvas => {
        let img = canvas.toDataURL(); //image data of canvas
        let pdf =  new jsPDF("p", "mm", "a4");
        const width = pdf.internal.pageSize.width;
        const height = (canvas.height * width) / canvas.width;

        pdf.addImage(img, 'PNG', 0, 0, width, height);
        pdf.save('graph.pdf');
    });
}

function getKeysLabelsValues(data) {
    let keys = Object.keys(data[0]);
    let labels = data.map(a => a[keys[0]]);
    let values = data.map(a => a[keys[1]]);

    return [keys, labels, values];
}

function insertDefaultAxisLabels(keys){
    $('#y-axis-label').val(keys[0]);
    $('#x-axis-label').val(keys[1]);
}

function createGraph(data, percentage) {
    const [keys, labels, values] = getKeysLabelsValues(data);

    let ctx = document.getElementById('chart').getContext('2d');

    graph = new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: labels,
            datasets: [{
                label: 'amount',
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
            title: {
                fontSize: 25
            },
            maintainAspectRatio: true,
            legend: {
                display: false,
            },
            scales: {
                yAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: keys[0]
                    },
                    ticks: {
                        beginAtZero: true
                    }
                }],
                xAxes: [{
                    ticks: {
                        callback: function (value) {
                            if (percentage) {
                                return (value * 100).toFixed(0) + " %"
                            } else {
                                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");

                            }
                        }
                    },
                    scaleLabel: {
                        display: true,
                        labelString: keys[1]
                    },
                }]
            }
        }
    });

    insertDefaultAxisLabels(keys);
}

function changeGraphTitle(element){
    let value = $(element).val()
    if (value) {
        graph.options.title.text = value;
        graph.options.title.display = true;
    } else {
        graph.options.title.display = false;
    }
    graph.update();
}

function changeXAxisLabel(element){
    let value = $(element).val()
    if (value) {
        graph.options.scales.xAxes[0].scaleLabel.labelString = value;
        graph.options.scales.xAxes[0].scaleLabel.display = true;
    } else {
        graph.options.scales.xAxes[0].scaleLabel.display = false;
    }
    graph.update();
}

function changeYAxisLabel(element){
    let value = $(element).val()
    if (value) {
        graph.options.scales.yAxes[0].scaleLabel.labelString = value;
        graph.options.scales.yAxes[0].scaleLabel.display = true;
    } else {
        graph.options.scales.yAxes[0].scaleLabel.display = false;
    }
    graph.update();
}

function changeGraphTitleFontSize(element){
    graph.options.title.fontSize = $(element).val();
    graph.update();
}

function changeXAxisLabelFontSize(element){
    graph.options.scales.xAxes[0].scaleLabel.fontSize = $(element).val();
    graph.update();
}

function changeYAxisLabelFontSize(element){
    graph.options.scales.yAxes[0].scaleLabel.fontSize = $(element).val();
    graph.update();
}

//table changes
function createDatasetTable(data){
    const [keys, labels, values] = getKeysLabelsValues(data);

    let tr = $('<tr></tr>')
    keys.forEach(function (key) {
        tr.append( '<th>' + key + '</th>' );
    });
    $('.dataset #thead').append(tr);

    labels.forEach(function (label, i){
        $('.dataset #tbody').append('<tr><td>' + label + '</td><td>' + values[i] + '</td></tr>');
    });
}

function toggleRequest() {
    let request = $('.request');
    let dataset = $('.dataset');

    if (request.is(':visible')) {
        request.css("display", "none");
    } else {
        dataset.css("display", "none");
        request.css("display", "table");
    }
}

function toggleDataset() {
    let dataset = $('.dataset');
    let request = $('.request');

    if (dataset.is(':visible')) {
        dataset.css("display", "none");
    } else {
        request.css("display", "none");
        dataset.css("display", "table");
    }
}

function noDataContentSwitch(){
    $(".error").append("<h1>No matching data found</h1>");

    $('#chart').remove();
    $('.form-row').remove();
    $('.export-buttons').remove();
    $('.show-dataset').remove();
}
