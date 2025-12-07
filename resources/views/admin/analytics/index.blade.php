@extends('layouts.dashboard')

@section('title', 'Analytics Dashboard - SKY LAINI')

@push('styles')
<style>
    .stat-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        padding: 24px;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        border-color: rgba(245, 158, 11, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }
    
    .chart-card {
        background: rgba(20, 20, 20, 0.6);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 24px;
        padding: 24px;
    }
    
    .growth-positive {
        color: #22c55e;
    }
    
    .growth-negative {
        color: #ef4444;
    }
    
    .metric-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .progress-ring {
        transform: rotate(-90deg);
    }
    
    .agent-row {
        transition: all 0.3s ease;
    }
    
    .agent-row:hover {
        background: rgba(255, 255, 255, 0.05);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-white mb-2">📊 Analytics Dashboard</h1>
            <p class="text-gray-400 font-medium">Takwimu za biashara yako</p>
        </div>
        
        <div class="flex items-center gap-3">
            <select id="periodFilter" class="bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-white text-sm focus:outline-none focus:border-amber-500">
                <option value="7">Wiki Hii</option>
                <option value="30" selected>Mwezi Huu</option>
                <option value="90">Miezi 3</option>
                <option value="365">Mwaka Huu</option>
            </select>
            <button onclick="refreshData()" class="px-4 py-2 bg-amber-500 text-black font-bold rounded-xl hover:bg-amber-400 transition">
                🔄 Refresh
            </button>
        </div>
    </div>

    <!-- Top Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="metric-icon bg-amber-500/20 text-amber-500">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-sm font-bold {{ ($stats['month']['revenue_growth'] ?? 0) >= 0 ? 'growth-positive' : 'growth-negative' }}">
                    {{ ($stats['month']['revenue_growth'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['month']['revenue_growth'] ?? 0 }}%
                </span>
            </div>
            <div class="text-3xl font-black text-white mb-1">TZS {{ number_format($stats['month']['revenue'] ?? 0) }}</div>
            <div class="text-gray-500 text-sm font-medium">Mapato ya Mwezi</div>
        </div>

        <!-- Total Requests -->
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="metric-icon bg-blue-500/20 text-blue-500">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <span class="text-sm font-bold {{ ($stats['month']['requests_growth'] ?? 0) >= 0 ? 'growth-positive' : 'growth-negative' }}">
                    {{ ($stats['month']['requests_growth'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['month']['requests_growth'] ?? 0 }}%
                </span>
            </div>
            <div class="text-3xl font-black text-white mb-1">{{ number_format($stats['month']['requests'] ?? 0) }}</div>
            <div class="text-gray-500 text-sm font-medium">Maombi ya Mwezi</div>
        </div>

        <!-- Completion Rate -->
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="metric-icon bg-green-500/20 text-green-500">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-black text-white mb-1">{{ $stats['completion_rate'] ?? 0 }}%</div>
            <div class="text-gray-500 text-sm font-medium">Kiwango cha Kukamilika</div>
        </div>

        <!-- Active Agents -->
        <div class="stat-card">
            <div class="flex items-center justify-between mb-4">
                <div class="metric-icon bg-purple-500/20 text-purple-500">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-black text-white mb-1">
                {{ $stats['agents']['online'] ?? 0 }}/{{ $stats['agents']['total'] ?? 0 }}
            </div>
            <div class="text-gray-500 text-sm font-medium">Mawakala Hai</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Requests Trend Chart -->
        <div class="chart-card">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-white">📈 Trend ya Maombi</h3>
            </div>
            <div class="h-72">
                <canvas id="requestsTrendChart"></canvas>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="chart-card">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-white">💰 Mapato</h3>
            </div>
            <div class="h-72">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Second Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Popular Line Types -->
        <div class="chart-card">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-white">📱 Laini Zinazopendwa</h3>
            </div>
            <div class="h-64">
                <canvas id="lineTypesChart"></canvas>
            </div>
        </div>

        <!-- Hourly Distribution -->
        <div class="chart-card">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-white">🕐 Saa za Shughuli</h3>
            </div>
            <div class="h-64">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>

        <!-- Top Agents -->
        <div class="chart-card">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-white">🏆 Mawakala Bora</h3>
                <a href="{{ route('leaderboard') }}" class="text-amber-500 text-sm font-bold hover:text-amber-400">Ona Wote →</a>
            </div>
            <div class="space-y-3">
                @foreach(array_slice($topAgents ?? [], 0, 5) as $index => $agent)
                    <div class="agent-row flex items-center gap-3 p-3 rounded-xl">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                            {{ $index === 0 ? 'bg-amber-500 text-black' : ($index === 1 ? 'bg-gray-400 text-black' : ($index === 2 ? 'bg-amber-700 text-white' : 'bg-white/10 text-white')) }}">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1">
                            <div class="font-bold text-white text-sm">{{ $agent['name'] }}</div>
                            <div class="text-xs text-gray-400">⭐ {{ $agent['rating'] }} • {{ $agent['completed_jobs'] }} jobs</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold text-amber-500">TZS {{ number_format($agent['earnings']) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Today's Stats -->
    <div class="chart-card">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-white">📅 Leo</h3>
            <span class="text-sm text-gray-400">{{ now()->format('l, d M Y') }}</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center p-6 bg-white/5 rounded-2xl">
                <div class="text-4xl font-black text-white mb-2">{{ $stats['today']['requests'] ?? 0 }}</div>
                <div class="text-gray-400">Maombi Mapya</div>
            </div>
            <div class="text-center p-6 bg-white/5 rounded-2xl">
                <div class="text-4xl font-black text-green-500 mb-2">{{ $stats['today']['completed'] ?? 0 }}</div>
                <div class="text-gray-400">Yaliyokamilika</div>
            </div>
            <div class="text-center p-6 bg-white/5 rounded-2xl">
                <div class="text-4xl font-black text-amber-500 mb-2">TZS {{ number_format($stats['today']['revenue'] ?? 0) }}</div>
                <div class="text-gray-400">Mapato ya Leo</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: { color: 'rgba(255,255,255,0.7)', font: { family: 'Inter' } }
            }
        },
        scales: {
            x: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { color: 'rgba(255,255,255,0.5)' }
            },
            y: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { color: 'rgba(255,255,255,0.5)' }
            }
        }
    };

    // Requests Trend Chart
    const requestsTrendData = @json($requestsTrend ?? []);
    new Chart(document.getElementById('requestsTrendChart'), {
        type: 'line',
        data: {
            labels: requestsTrendData.labels || [],
            datasets: (requestsTrendData.datasets || []).map(ds => ({
                label: ds.label,
                data: ds.data,
                borderColor: ds.color,
                backgroundColor: ds.color + '20',
                tension: 0.4,
                fill: true,
            }))
        },
        options: chartOptions
    });

    // Revenue Chart
    const revenueData = @json($revenueData ?? []);
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: revenueData.labels || [],
            datasets: (revenueData.datasets || []).map(ds => ({
                label: ds.label,
                data: ds.data,
                backgroundColor: ds.color,
                borderRadius: 8,
            }))
        },
        options: chartOptions
    });

    // Line Types Pie Chart
    const lineTypesData = @json($popularLineTypes ?? []);
    new Chart(document.getElementById('lineTypesChart'), {
        type: 'doughnut',
        data: {
            labels: lineTypesData.labels || [],
            datasets: [{
                data: lineTypesData.values || [],
                backgroundColor: ['#e60000', '#ff0000', '#0066cc', '#00aa00', '#ff6600'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { color: 'rgba(255,255,255,0.7)' }
                }
            }
        }
    });

    // Hourly Chart
    const hourlyData = @json($hourlyDistribution ?? []);
    new Chart(document.getElementById('hourlyChart'), {
        type: 'bar',
        data: {
            labels: hourlyData.labels || [],
            datasets: [{
                label: 'Maombi',
                data: hourlyData.values || [],
                backgroundColor: '#f59e0b',
                borderRadius: 4,
            }]
        },
        options: {
            ...chartOptions,
            plugins: { legend: { display: false } }
        }
    });

    function refreshData() {
        location.reload();
    }
</script>
@endpush
