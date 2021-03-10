let graph;
let graphDataOptions;

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

$("#movingAverage").on('change', function (){
    updateTimeGraph();
});

$("#rangeStart").on('change', function (){
    updateTimeGraph();
});

$("#rangeEnd").on('change', function (){
    updateTimeGraph();
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

//download functions
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
    html2canvas($("#graph-container")).then(canvas => {
        let img = canvas.toDataURL(); //image data of canvas
        let pdf =  new jsPDF("p", "mm", "a4");
        const width = pdf.internal.pageSize.width;
        const height = (canvas.height * width) / canvas.width;

        pdf.addImage(img, 'PNG', 0, 0, width, height);
        pdf.save('graph.pdf');
    });
}

//chart functions
function getKeysLabelsValues(data) {
    let keys = Object.keys(data[0]);
    let labels = data.map(a => a[keys[0]]);
    let values = data.map(a => a[keys[1]]);

    return [keys, labels, values];
}

function insertDefaultAxisLabels(keys) {
    $('#y-axis-label').val(keys[0]);
    $('#x-axis-label').val(keys[1]);
}

function getAmountOfDecimals(value) {
    if ((value % 1) !== 0){
        return value.toString().split(".")[1].length;
    }
}

const colors = [
    'rgba(255, 99, 132,',
    'rgba(54, 80, 235,',
    'rgba(255, 206, 86,',
    'rgba(75, 192, 192,',
    'rgba(153, 102, 255,',
    'rgba(255, 50, 50,',
    'rgba(50, 255, 50,',
    'rgba(50, 50, 255,',
    'rgba(100, 102, 100,',
    'rgba(255, 159, 64,'
];

function getColours(values) {
    let backgroundColors = [];
    let borderColors = [];

    values.forEach(function(val, index){
        backgroundColors.push(colors[index % colors.length]  + '0.4)');
        borderColors.push(colors[index % colors.length]  + '1)');
    })

    return {
        'backgroundColors': backgroundColors,
        'borderColors': borderColors
    };
}

function getGraphType(request) {
    let chartType;

    if (request['graphType'] === 'time'){
        chartType = 'line';
    } else {
        chartType = 'horizontalBar';
    }

    return chartType;
}

function resolveGraphDataOptions(data, request) {
    const [keys, labels, values] = getKeysLabelsValues(data);

    let percentage = 'percentage' in request;

    let label = 'amount';

    if (percentage) {
        values.forEach(function(value, index){
            this[index] = (value * 100).toFixed(2);
        }, values)

        keys[1] = 'percentage';
        label = 'percentage';
    }

    let graphType = getGraphType(request);

    if (graphType === 'line'){
        keys.reverse();
    }

    return {
        'type': graphType,
        'keys': keys,
        'labels': labels,
        'label' : label,
        'values': values,
        'percentage': percentage,
        'colors': getColours(values)
    };
}

function createGraph(data, request) {
    graphDataOptions = resolveGraphDataOptions(data, request)

    let ctx = document.getElementById('chart').getContext('2d');
    graph = new Chart(ctx, {
        type: graphDataOptions.type,
        data: {
            labels: graphDataOptions.labels,
            datasets: [{
                label: graphDataOptions.label,
                data: graphDataOptions.values,
                backgroundColor: graphDataOptions.colors.backgroundColors,
                borderColor: graphDataOptions.colors.borderColors,
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
                        labelString: graphDataOptions.keys[0]
                    },
                    ticks: {
                        callback: function (value) {
                            if (graphDataOptions.percentage && graphDataOptions.type === 'line') {
                                return value.toFixed(getAmountOfDecimals(value)) + " %"
                            } else {
                                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                            }
                        }
                    }
                }],
                xAxes: [{
                    ticks: {
                        callback: function (value) {
                            if (graphDataOptions.percentage && graphDataOptions.type !== 'line') {
                                return value.toFixed(getAmountOfDecimals(value)) + " %"
                            } else {
                                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                            }
                        }
                    },
                    scaleLabel: {
                        display: true,
                        labelString: graphDataOptions.keys[1]
                    },
                }]
            }
        }
    });

    insertDefaultAxisLabels(graphDataOptions.keys);
}

//graph update functions
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

function updateTimeGraph() {
    let newData = alterDataRangeStart($("#rangeStart").val());
    newData = alterDataRangeEnd($("#rangeEnd").val(), newData);
    newData = alterDataMovingAverage($("#movingAverage").val(), newData);

    graph.data.labels = newData.labels;
    graph.data.datasets[0].data = newData.values;
    graph.update();
}

function alterDataRangeStart(startingDate, data = null) {
    let labels;
    let values;

    if (data) {
        labels = data.labels;
        values = data.values;
    } else {
        values = graphDataOptions.values.slice();
        labels = graphDataOptions.labels.slice();
    }


    let i;
    for (i = 0; i < labels.length; i++) {
        if (labels[i] === startingDate) {
            break;
        }
    }

    if (i !== labels.length) {
        labels.splice(0, i);
        values.splice(0, i);
    }

    return {"labels": labels, "values": values};
}

function alterDataRangeEnd(endingDate, data = null) {
    let labels;
    let values;

    if (data) {
        labels = data.labels;
        values = data.values;
    } else {
        values = graphDataOptions.values.slice();
        labels = graphDataOptions.labels.slice();
    }

    let i;
    for (i = labels.length; i > 0; i--) {
        if (labels[i] === endingDate) {
            break;
        }
    }

    if (i !== 0) {
        labels.splice(i+1, labels.length);
        values.splice(i+1, values.length);
    }

    return {"labels": labels, "values": values};
}

function alterDataMovingAverage(daysAmount, data = null) {
    let valuesAmount = graphDataOptions.values.length;

    if (!daysAmount){
        daysAmount = 1;
    }

    daysAmount = parseInt(daysAmount);

    if (isNaN(daysAmount)
        || valuesAmount <= daysAmount
        || daysAmount <= 0
    ){
        return;
    }

    let labels;
    let oldValues;

    if (data) {
        oldValues = data.values;
        labels = data.labels;
    } else {
        oldValues = graphDataOptions.values.slice();
        labels = graphDataOptions.labels.slice();
    }

    let newValues = [];
    let total;

    for (let i = daysAmount; i < valuesAmount; i++) {
        total = 0;
        for (let j = (i - daysAmount); j < i; j++) {
            total += parseFloat(oldValues[j]);
        }

        newValues.push(total / daysAmount);
    }

    labels.splice(0, daysAmount);

    return {"labels": labels, "values": newValues};
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
    $('#show-dataset').remove();
}
