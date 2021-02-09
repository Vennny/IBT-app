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

function createDatasetTable(data) {
    let tablearea = document.getElementById('data');
    let table = document.createElement('table');
    table.classList.add('table', 'table-striped', 'table-bordered', 'table-responsive-md');
    let thead = document.createElement('thead');
    let thead_tr = document.createElement('tr');

    const [keys, labels, values] = getKeysLabelsValues(data);

    keys.forEach(function (key, i) {
        thead_tr.appendChild( document.createElement('th') );
        thead_tr.cells[i].appendChild( document.createTextNode(key) )
    });

    thead.appendChild(thead_tr);
    table.appendChild(thead);

    labels.forEach(function (label, i){
        let tr = document.createElement('tr');

        tr.appendChild( document.createElement('td') );
        tr.appendChild( document.createElement('td') );

        tr.cells[0].appendChild( document.createTextNode(label) )
        tr.cells[1].appendChild( document.createTextNode(values[i]) );

        table.appendChild(tr);
    });

    tablearea.appendChild(table);
}

function showDataset() {
    let x = document.getElementById("data");
    if (x.style.display === "none") {
        x.style.display = "block";
    } else {
        x.style.display = "none";
    }
}
