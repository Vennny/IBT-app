function getKeysLabelsValues(data) {
    let keys = Object.keys(data[0]);
    let labels = data.map(a => a[keys[0]]);
    let values = data.map(a => a[keys[1]]);

    return [keys, labels, values];
}

function createGraph(data) {
    const [keys, labels, values] = getKeysLabelsValues(data);

    let ctx = document.getElementById('chart').getContext('2d');

    new Chart(ctx, {
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
                            return numeral(value).format('0,0')
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
}

function createDatasetTable(data){

    const [keys, labels, values] = getKeysLabelsValues(data);

    keys.forEach(function (key) {
        $('#thead').append( '<th>' + key + '</th>' );
    });

    labels.forEach(function (label, i){
        $('#tbody').append('<tr><td>' + label + '</td><td>' + values[i] + '</td></tr>');
    });
}

function showDataset() {
    let x = $('.table');
    if (x.is(':visible')) {
        x.css("display", "none");
    } else {
        x.css("display", "table");
    }
}
