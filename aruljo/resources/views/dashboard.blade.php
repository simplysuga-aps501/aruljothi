@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="m-0 text-dark">Dashboard</h1>
@stop

@section('content')
    <div class="row">
       @if($isEuser)
            {{-- Only Euser: show only their leads --}}
            <div class="row align-items-stretch w-100">
                <div class="col-md-6 mb-3">
                    <x-adminlte-card title="My Lead Status Summary" theme="success" icon="fas fa-chart-pie"
                                     class="h-100" collapsible>
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3">
                            @foreach($leadStatusCounts as $status => $count)
                                <div class="col mb-2">
                                    <div class="p-2 text-center rounded"
                                         style="border:1px solid #ccc; background-color: {{ $statusColors[$status] ?? '#f8f9fa' }}; color:black;">
                                        <h4>{{ $count }}</h4>
                                        <p>{{ $status }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-adminlte-card>
                </div>
            </div>
        @else
            {{-- Owner+Euser, Staff+Euser, or multiple roles: show full dashboard --}}
            <div class="row align-items-stretch w-100">
                <div class="col-md-6 mb-3">
                    <x-adminlte-card title="My Lead Status Summary" theme="success" icon="fas fa-chart-pie"
                                     class="h-100" collapsible>
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3">
                            @foreach($leadStatusCounts as $status => $count)
                                <div class="col mb-2">
                                    <div class="p-2 text-center rounded"
                                         style="border:1px solid #ccc; background-color: {{ $statusColors[$status] ?? '#f8f9fa' }}; color:black;">
                                        <h4>{{ $count }}</h4>
                                        <p>{{ $status }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-adminlte-card>
                </div>

                <div class="col-md-6 mb-3">
                    <x-adminlte-card title="Active Leads by Person" theme="secondary" icon="fas fa-user"
                                     class="h-100" collapsible>
                        <canvas id="userLeadsChart" height="180"></canvas>
                    </x-adminlte-card>
                </div>
            </div>

            <div class="row align-items-stretch w-100">
                <div class="col-md-6 mb-3">
                    <x-adminlte-card title="Month-wise Lead Summary" theme="primary" icon="fas fa-chart-bar"
                                     class="h-100" collapsible>
                        <canvas id="monthlyChart" height="180"></canvas>
                    </x-adminlte-card>
                </div>

                <div class="col-md-6 mb-3">
                    <x-adminlte-card title="Platform-wise Lead Summary" theme="info" icon="fas fa-layer-group"
                                     class="h-100" collapsible>
                        <canvas id="platformChart" height="180"></canvas>
                    </x-adminlte-card>
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
    {{-- Manifest and theme color for PWA --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0d47a1">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('/sw.js')
            .then(registration => console.log('Service Worker registered:', registration.scope))
            .catch(error => console.error('Service Worker registration failed:', error));
        });
      }
    </script>

    <script>
      let deferredPrompt;
      window.addEventListener('beforeinstallprompt', (e) => {
          e.preventDefault();
          deferredPrompt = e;
          const installButton = document.getElementById('installButton');
          if (installButton) {
              installButton.style.display = 'block';
              installButton.addEventListener('click', () => {
                  deferredPrompt.prompt();
                  deferredPrompt.userChoice.then((choiceResult) => {
                      if (choiceResult.outcome === 'accepted') {
                          console.log('User accepted install');
                      } else {
                          console.log('User dismissed install');
                      }
                      deferredPrompt = null;
                  });
              });
          }
      });
    </script>

    <script>
        // Common chart options
        const chartOptions = {
            responsive: true,
            layout: { padding: { top: 25 } },
            plugins: {
                legend: { position: 'bottom' },
                datalabels: {
                    display: ctx => ctx.dataset.data[ctx.dataIndex] !== 0,
                    anchor: 'end',
                    align: 'end',
                    color: '#000',
                    font: { weight: 'bold', size: 12 },
                    formatter: Math.round
                }
            },
            scales: {
                x: { stacked: false },
                y: { beginAtZero: true }
            }
        };

        // MONTHLY CHART
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: @json($monthlyLabels),
                datasets: [
                    { label: 'Created',   backgroundColor: '#3490dc', data: @json($monthlyCreated) },
                    { label: 'Completed', backgroundColor: '#38c172', data: @json($monthlyCompleted) },
                    { label: 'Cancelled', backgroundColor: '#e3342f', data: @json($monthlyCancelled) }
                ]
            },
            options: chartOptions,
            plugins: [ChartDataLabels]
        });

        // PLATFORM-WISE CREATED CHART
        const platformCtx = document.getElementById('platformChart').getContext('2d');
        const platformLabels = @json($platformLabels);
        const rawPlatformData = @json($platformData);

        const platforms = Object.keys(rawPlatformData);
        const totalPlatforms = platforms.length;

        function generateDistinctPastelColors(count) {
            const colors = [];
            for (let i = 0; i < count; i++) {
                const hue = Math.round((360 / count) * i);
                colors.push(`hsl(${hue}, 70%, 75%)`);
            }
            return colors;
        }

        const fillColors = generateDistinctPastelColors(totalPlatforms);
        const borderColors = fillColors.map(c => c.replace(/(\d+)%\)$/, (match, l) => `${Math.max(0, l - 20)}%)`));

        const platformDatasets = platforms.map((platform, index) => {
            const created = platformLabels.map(label => rawPlatformData[platform][label] || 0);
            return {
                label: platform,
                data: created,
                backgroundColor: fillColors[index],
                borderColor: borderColors[index],
                borderWidth: 1
            };
        });

        new Chart(platformCtx, {
            type: 'bar',
            data: {
                labels: platformLabels,
                datasets: platformDatasets
            },
            options: chartOptions,
            plugins: [ChartDataLabels]
        });

        // ACTIVE LEADS BY PERSON
        const userLeadsCtx = document.getElementById('userLeadsChart').getContext('2d');
        new Chart(userLeadsCtx, {
            type: 'bar',
            data: {
                labels: @json($userLabels),
                datasets: [
                    { label: 'New Lead',       backgroundColor: '#3490dc', data: @json(array_values($userNewLeads)) },
                    { label: 'Lead Follow-up', backgroundColor: '#ff9800', data: @json(array_values($userFollowUps)) },
                    { label: 'Quotation',      backgroundColor: '#4caf50', data: @json(array_values($userQuotations)) },
                    { label: 'PO Status',      backgroundColor: '#9c27b0', data: @json(array_values($userPOStatus)) }
                ]
            },
            options: {
                ...chartOptions,
                scales: { x: { stacked: false }, y: { beginAtZero: true } }
            },
            plugins: [ChartDataLabels]
        });
    </script>
@stop
