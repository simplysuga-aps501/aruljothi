@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <x-adminlte-card title="Month-wise Lead Summary" theme="primary" icon="fas fa-chart-bar" collapsible>
                <canvas id="monthlyChart" height="180"></canvas>
            </x-adminlte-card>
        </div>
        <div class="col-md-6">
            <x-adminlte-card title="Platform-wise Lead Summary" theme="info" icon="fas fa-layer-group" collapsible>
                <canvas id="platformChart" height="180"></canvas>
            </x-adminlte-card>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
        // Common options
        const chartOptions = {
            responsive: true,
            layout: {
                padding: {
                    top: 25
                }
            },
            plugins: {
                legend: { position: 'bottom' },
                datalabels: {
                    display: function(context) {
                            return context.dataset.data[context.dataIndex] !== 0;
                        },
                    anchor: 'end',
                    align: 'end',
                    color: '#000',
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    formatter: Math.round
                }
            },
            scales: {
                x: { stacked: false },
                y: {
                    beginAtZero: true
                }
            }
        };

        // MONTHLY CHART
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: @json($monthlyLabels),
                datasets: [
                    {
                        label: 'Created',
                        backgroundColor: '#3490dc',
                        data: @json($monthlyCreated)
                    },
                    {
                        label: 'Completed',
                        backgroundColor: '#38c172',
                        data: @json($monthlyCompleted)
                    },
                    {
                        label: 'Cancelled',
                        backgroundColor: '#e3342f',
                        data: @json($monthlyCancelled)
                    }
                ]
            },
            options: chartOptions,
            plugins: [ChartDataLabels]
        });

        // PLATFORM-WISE CREATED CHART
        const platformCtx = document.getElementById('platformChart').getContext('2d');
        const platformLabels = @json($platformLabels);
        const rawPlatformData = @json($platformData);

        const colors = ['#A0522D', '#FFDE03', '#607D8B'];

        let colorIndex = 0;

        const platformDatasets = [];

        Object.entries(rawPlatformData).forEach(([platform, dataPerMonth]) => {
            const created = [];

            platformLabels.forEach(label => {
                const count = dataPerMonth[label] || 0;
                created.push(count);
            });

            platformDatasets.push({
                label: platform,
                data: created,
                backgroundColor: colors[colorIndex % colors.length],
            });

            colorIndex++;
        });


        const platformChart = new Chart(platformCtx, {
            type: 'bar',
            data: {
                labels: platformLabels,
                datasets: platformDatasets
            },
            options: chartOptions,
            plugins: [ChartDataLabels]
        });
    </script>
@stop
