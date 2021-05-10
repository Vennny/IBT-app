/**
 * Jméno, Město Analyzer
 * Bachelor's Thesis
 * author: Václav Trampeška
 */

let graph;
let graphDataOptions;

//label customizations
$('#title').on('input', function() {
    changeGraphTitle(this);
});

$('#x-axis-label').on('input', function() {
    changeXAxisLabel(this);
});

$('#y-axis-label').on('input', function() {
    changeYAxisLabel(this);
});

$('#title-font-slider').on('input', function() {
    changeGraphTitleFontSize(this);
});

$('#x-font-slider').on('input', function() {
    changeXAxisLabelFontSize(this);
});

$('#y-font-slider').on('input', function() {
    changeYAxisLabelFontSize(this);
});

//time graph customizations
$('#movingAverage').on('change', function() {
    updateTimeGraph();
});

$('#rangeStart').on('change', function() {
    updateTimeGraph();
});

$('#rangeEnd').on('change', function() {
    updateTimeGraph();
});

//button clicks
$('#show-dataset').click(function() {
    toggleDataset();
})

$('#show-request').click(function() {
    toggleRequest();
})

$('#zeroCheck').on('change', function () {
    toggleStartsAtZero(this);
})

$('#download-graph-pdf').click(function() {
    saveAsPDF();
})

$('#download-graph-png').click(function() {
    saveAsPNG();
})

$('#download-csv').click(function() {
    saveCSV();
})

//download functions
function saveCSV() {
    let table = $('#dataset-table');

    let options = {
        'separator': ';',
        'trimContent': true
    }

    table.css('display', 'table');
    table.table2csv(options);
    table.css('display', 'none');
}

function saveAsPNG() {
    html2canvas($('#graph-container')).then(canvas => {
        let link = document.createElement('a');
        link.href = canvas.toDataURL();
        link.download = 'graph.png';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
}

function saveAsPDF() {
    html2canvas($('#graph-container')).then(canvas => {
        let img = canvas.toDataURL(); //image data of canvas
        let pdf =  new jsPDF('p', 'mm', 'a4');
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
        return value.toString().split('.')[1].length;
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
    'rgba(255, 159, 64,',
    'rgba(46, 204, 113,',
    'rgba(52, 152, 219,',
    'rgba(155, 89, 182,',
    'rgba(52, 73, 94,',
    'rgba(26, 188, 156,',
    'rgba(241, 196, 15,',
    'rgba(230, 126, 34,',
    'rgba(231, 76, 60,',
    'rgba(18, 203, 196,',
    'rgba(253, 167, 223,',
    'rgba(131, 52, 113,',
    'rgba(61, 193, 211,',
    'rgba(6, 82, 221,',
    'rgba(0, 148, 50,',
    'rgba(234, 32, 39,',
    'rgba(196, 229, 56,',
    'rgba(236, 240, 241,',
    'rgba(149, 165, 166,'
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
        //change results to value out of 100 %
        values.forEach(function(value, index){
            this[index] = (value * 100).toFixed(2);
        }, values)

        keys[1] = 'percentage';
        label = 'percentage';
    }

    let graphType = getGraphType(request);

    if (graphType === 'line'){
        //switch labels
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
                        beginAtZero: true,
                        callback: function (value) {
                            if (graphDataOptions.percentage && graphDataOptions.type === 'line') {
                                return value.toFixed(getAmountOfDecimals(value)) + ' %'
                            } else {
                                return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                            }
                        }
                    }
                }],
                xAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function (value) {
                            if (graphDataOptions.percentage && graphDataOptions.type !== 'line') {
                                return value.toFixed(getAmountOfDecimals(value)) + ' %'
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
function toggleStartsAtZero(checkbox){
    if (graphDataOptions.type === 'line'){
        graph.options.scales.yAxes[0].ticks.beginAtZero = checkbox.checked;
    } else {
        graph.options.scales.xAxes[0].ticks.beginAtZero = checkbox.checked;
    }

    graph.update();
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

function updateTimeGraph() {
    //apply all modifiers
    let newData = alterDataRangeStart($('#rangeStart').val());
    newData = alterDataRangeEnd($('#rangeEnd').val(), newData);
    newData = alterDataMovingAverage($('#movingAverage').val(), newData);

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
    for (i = 0; i < labels.length-1; i++) {
        if (labels[i] >= startingDate) {
            break;
        }
    }

    if (i !== labels.length-1) {
        labels.splice(0, i);
        values.splice(0, i);
    }

    return {'labels': labels, 'values': values};
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

    let i = labels.length-1;
    for (i; i > 0; i--) {
        if (labels[i] >= endingDate) {
            break;
        }
    }

    if (i !== 0 && i !== labels.length-1) {
        labels.splice(i+1, labels.length);
        values.splice(i+1, values.length);
    }

    return {'labels': labels, 'values': values};
}

function alterDataMovingAverage(daysAmount, data = null) {
    let valuesAmount = graphDataOptions.values.length;
    daysAmount = parseInt(daysAmount);

    if (isNaN(daysAmount)
        || valuesAmount <= daysAmount
        || daysAmount <= 0
    ) {
        daysAmount = 1;
        $('#movingAverage').val('1');
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

    labels.splice(0, daysAmount-1);

    return {'labels': labels, 'values': newValues};
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
        request.css('display', 'none');
    } else {
        dataset.css('display', 'none');
        request.css('display', 'table');
    }
}

function toggleDataset() {
    let dataset = $('.dataset');
    let request = $('.request');

    if (dataset.is(':visible')) {
        dataset.css('display', 'none');
    } else {
        request.css('display', 'none');
        dataset.css('display', 'table');
    }
}

//no data page
function noDataContentSwitch(){
    $('.error').append('<h1>No matching results found</h1>');

    $('#chart').remove();
    $('.form-row').remove();
    $('.export-buttons').remove();
    $('#show-dataset').remove();
}
