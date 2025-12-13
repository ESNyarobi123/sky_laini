@extends('layouts.dashboard')

@section('title', 'Live Monitoring - SKY LAINI')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<style>
    /* Map Container */
    #monitoring-map {
        height: calc(100vh - 280px);
        min-height: 500px;
        border-radius: 1.5rem;
        z-index: 1;
    }

    /* Glass Cards */
    .glass-card {
        background: rgba(20, 20, 20, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    /* Stats Cards */
    .stat-mini {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
    }
    .stat-mini:hover {
        border-color: rgba(245, 158, 11, 0.3);
        transform: translateY(-2px);
    }

    /* Legend */
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        font-size: 12px;
    }
    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    .legend-dot.online { background: #22c55e; }
    .legend-dot.offline { background: #6b7280; }
    .legend-dot.busy { background: #f59e0b; }
    .legend-dot.customer { background: #3b82f6; }
    .legend-dot.booking { background: #a855f7; }
    .legend-dot.route { background: #a855f7; width: 20px; height: 3px; border-radius: 2px; }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Side Panel */
    .side-panel {
        position: absolute;
        top: 0;
        right: 0;
        width: 380px;
        height: 100%;
        background: rgba(15, 15, 15, 0.95);
        backdrop-filter: blur(20px);
        border-left: 1px solid rgba(255, 255, 255, 0.1);
        transform: translateX(100%);
        transition: transform 0.3s ease;
        z-index: 1000;
        overflow-y: auto;
    }
    .side-panel.open { transform: translateX(0); }

    /* Request Card */
    .request-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .request-card:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(245, 158, 11, 0.3);
    }
    .request-card.active {
        border-color: rgba(245, 158, 11, 0.5);
        background: rgba(245, 158, 11, 0.1);
    }

    /* Status Badges */
    .status-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
        text-transform: uppercase;
    }
    .status-pending { background: rgba(234, 179, 8, 0.2); color: #eab308; }
    .status-accepted { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
    .status-in_progress { background: rgba(168, 85, 247, 0.2); color: #a855f7; }
    .status-completed { background: rgba(34, 197, 94, 0.2); color: #22c55e; }

    /* Live indicator */
    .live-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        background: rgba(34, 197, 94, 0.15);
        border: 1px solid rgba(34, 197, 94, 0.3);
        border-radius: 20px;
    }
    .live-dot {
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
        animation: blink 1s infinite;
    }
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    /* Custom Markers */
    .custom-marker {
        background: transparent;
        border: none;
    }
    .marker-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
        color: white;
    }
    .marker-agent-online { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .marker-agent-offline { background: linear-gradient(135deg, #6b7280, #4b5563); }
    .marker-agent-busy { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .marker-customer { background: linear-gradient(135deg, #3b82f6, #2563eb); }

    /* Tabs */
    .tab-btn {
        padding: 10px 20px;
        background: transparent;
        border: none;
        color: #9ca3af;
        font-weight: 600;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.3s;
    }
    .tab-btn:hover { color: white; }
    .tab-btn.active {
        color: #f59e0b;
        border-bottom-color: #f59e0b;
    }

    /* Scrollbar */
    .side-panel::-webkit-scrollbar { width: 6px; }
    .side-panel::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
    .side-panel::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 3px; }
</style>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-white mb-1">🛰️ Live Monitoring</h1>
            <p class="text-gray-400">Real-time tracking ya Agents, Customers & Requests</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="live-indicator">
                <div class="live-dot"></div>
                <span class="text-green-400 font-bold text-sm">LIVE</span>
            </div>
            <span id="last-update" class="text-gray-500 text-sm">Updating...</span>
            <button onclick="togglePanel()" class="px-4 py-2 bg-white/10 text-white rounded-xl font-bold hover:bg-white/20 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                Details
            </button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div class="stat-mini rounded-2xl p-4">
            <div class="text-gray-400 text-xs font-bold uppercase mb-1">Online Agents</div>
            <div class="text-2xl font-black text-green-400" id="stat-online-agents">{{ $stats['online_agents'] }}</div>
        </div>
        <div class="stat-mini rounded-2xl p-4">
            <div class="text-gray-400 text-xs font-bold uppercase mb-1">Active Requests</div>
            <div class="text-2xl font-black text-amber-400" id="stat-active-requests">{{ $stats['active_requests'] }}</div>
        </div>
        <div class="stat-mini rounded-2xl p-4">
            <div class="text-gray-400 text-xs font-bold uppercase mb-1">Pending</div>
            <div class="text-2xl font-black text-blue-400" id="stat-pending">{{ $stats['pending_requests'] }}</div>
        </div>
        <div class="stat-mini rounded-2xl p-4">
            <div class="text-gray-400 text-xs font-bold uppercase mb-1">Completed Today</div>
            <div class="text-2xl font-black text-green-400" id="stat-completed">{{ $stats['completed_today'] }}</div>
        </div>
        <div class="stat-mini rounded-2xl p-4">
            <div class="text-gray-400 text-xs font-bold uppercase mb-1">Upcoming Bookings</div>
            <div class="text-2xl font-black text-purple-400" id="stat-bookings">{{ $stats['upcoming_bookings'] ?? 0 }}</div>
        </div>
        <div class="stat-mini rounded-2xl p-4">
            <div class="text-gray-400 text-xs font-bold uppercase mb-1">Revenue Today</div>
            <div class="text-xl font-black text-white" id="stat-revenue">TSh {{ number_format($stats['revenue_today']) }}</div>
        </div>
    </div>

    <!-- Map Container -->
    <div class="relative">
        <div class="glass-card rounded-3xl overflow-hidden">
            <!-- Legend -->
            <div class="absolute top-4 left-4 z-[500] flex flex-wrap gap-2">
                <div class="legend-item">
                    <div class="legend-dot online"></div>
                    <span class="text-white font-medium">Agent Online</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot busy"></div>
                    <span class="text-white font-medium">Agent Busy</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot offline"></div>
                    <span class="text-white font-medium">Agent Offline</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot customer"></div>
                    <span class="text-white font-medium">Customer</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot booking"></div>
                    <span class="text-white font-medium">Booking</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot route"></div>
                    <span class="text-white font-medium">Active Route</span>
                </div>
            </div>

            <!-- Map Controls -->
            <div class="absolute top-4 right-4 z-[500] flex flex-col gap-2">
                <button onclick="centerMap()" class="p-3 bg-gray-900/80 rounded-xl text-white hover:bg-gray-800 transition" title="Center Map">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </button>
                <button onclick="toggleHeatmap()" id="heatmap-btn" class="p-3 bg-gray-900/80 rounded-xl text-white hover:bg-gray-800 transition" title="Toggle Heatmap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                    </svg>
                </button>
                <button onclick="refreshData()" class="p-3 bg-gray-900/80 rounded-xl text-white hover:bg-gray-800 transition" title="Refresh">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>

            <!-- Map -->
            <div id="monitoring-map"></div>
        </div>

        <!-- Side Panel -->
        <div class="side-panel rounded-l-3xl" id="side-panel">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-white">Details</h3>
                    <button onclick="togglePanel()" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-white/10 mb-4 overflow-x-auto">
                    <button class="tab-btn active" data-tab="requests" onclick="switchTab('requests')">Requests</button>
                    <button class="tab-btn" data-tab="agents" onclick="switchTab('agents')">Agents</button>
                    <button class="tab-btn" data-tab="customers" onclick="switchTab('customers')">Customers</button>
                    <button class="tab-btn" data-tab="bookings" onclick="switchTab('bookings')">Bookings</button>
                </div>

                <!-- Tab Content: Requests -->
                <div class="tab-content" id="tab-requests">
                    <div id="requests-list" class="space-y-3">
                        <div class="text-gray-500 text-center py-8">Loading...</div>
                    </div>
                </div>

                <!-- Tab Content: Agents -->
                <div class="tab-content hidden" id="tab-agents">
                    <div id="agents-list" class="space-y-3">
                        <div class="text-gray-500 text-center py-8">Loading...</div>
                    </div>
                </div>

                <!-- Tab Content: Customers (ALL with location) -->
                <div class="tab-content hidden" id="tab-customers">
                    <div id="customers-list" class="space-y-3">
                        <div class="text-gray-500 text-center py-8">Loading...</div>
                    </div>
                </div>

                <!-- Tab Content: Bookings -->
                <div class="tab-content hidden" id="tab-bookings">
                    <div id="bookings-list" class="space-y-3">
                        <div class="text-gray-500 text-center py-8">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script>
    // Map initialization
    let map;
    let agentMarkers = {};
    let customerMarkers = {};
    let bookingMarkers = {};
    let routeLines = {};
    let heatLayer = null;
    let showHeatmap = false;
    let refreshInterval;

    // Tanzania center
    const DEFAULT_CENTER = [-6.7924, 39.2083]; // Dar es Salaam
    const DEFAULT_ZOOM = 12;

    // Initialize map
    function initMap() {
        map = L.map('monitoring-map', {
            center: DEFAULT_CENTER,
            zoom: DEFAULT_ZOOM,
            zoomControl: false
        });

        // Add dark tile layer
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '© OpenStreetMap contributors © CARTO',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        // Add zoom control to bottom right
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        // Initial data load
        refreshData();

        // Auto refresh every 10 seconds
        refreshInterval = setInterval(refreshData, 10000);
    }

    // Create custom marker icon
    function createMarkerIcon(type, label = '') {
        const colorClass = {
            'online': 'marker-agent-online',
            'offline': 'marker-agent-offline',
            'busy': 'marker-agent-busy',
            'customer': 'marker-customer'
        }[type] || 'marker-agent-online';

        return L.divIcon({
            className: 'custom-marker',
            html: `<div class="marker-icon ${colorClass}">${label}</div>`,
            iconSize: [40, 40],
            iconAnchor: [20, 20],
            popupAnchor: [0, -20]
        });
    }

    // Refresh data from server
    async function refreshData() {
        console.log('🔄 Refreshing map data...');
        try {
            const response = await fetch('{{ route("admin.monitoring.live-data") }}');
            console.log('📡 Response status:', response.status);
            
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            
            const data = await response.json();
            console.log('📊 Data received:', {
                agents: data.agents?.length || 0,
                customers: data.customers?.length || 0,
                all_customers: data.all_customers?.length || 0,
                active_requests: data.active_requests?.length || 0,
                upcoming_bookings: data.upcoming_bookings?.length || 0
            });

            updateMarkers(data);
            updateStats(data.stats);
            updatePanelContent(data);

            document.getElementById('last-update').textContent = 
                'Updated: ' + new Date().toLocaleTimeString();
                
            console.log('✅ Map updated successfully');
        } catch (error) {
            console.error('❌ Failed to refresh data:', error);
            document.getElementById('last-update').textContent = 
                'Error: ' + error.message;
        }
    }

    // Update markers on map
    function updateMarkers(data) {
        // Clear old route lines
        Object.values(routeLines).forEach(line => map.removeLayer(line));
        routeLines = {};

        // Update agent markers
        data.agents.forEach(agent => {
            const markerId = `agent-${agent.id}`;
            const type = agent.has_active_request ? 'busy' : (agent.is_online ? 'online' : 'offline');
            const icon = createMarkerIcon(type, '🏍️');

            const popupContent = `
                <div class="p-2" style="min-width: 200px;">
                    <div class="font-bold text-lg mb-2">${agent.name}</div>
                    <div class="text-sm space-y-1">
                        <div><strong>Phone:</strong> ${agent.phone || 'N/A'}</div>
                        <div><strong>Status:</strong> <span class="${agent.is_online ? 'text-green-600' : 'text-gray-500'}">${agent.is_online ? 'Online' : 'Offline'}</span></div>
                        <div><strong>Rating:</strong> ⭐ ${agent.rating || 0}</div>
                        <div><strong>Completed:</strong> ${agent.total_completed} jobs</div>
                        ${agent.has_active_request ? `
                            <div class="mt-2 p-2 bg-amber-50 rounded">
                                <div class="font-bold text-amber-700">Active Job:</div>
                                <div>#${agent.active_request.request_number}</div>
                                <div>Customer: ${agent.active_request.customer_name}</div>
                            </div>
                        ` : ''}
                    </div>
                    <button onclick="focusAgent(${agent.id})" class="mt-3 w-full py-2 bg-amber-500 text-white rounded font-bold text-sm">
                        Track Agent
                    </button>
                </div>
            `;

            if (agentMarkers[markerId]) {
                agentMarkers[markerId].setLatLng([agent.latitude, agent.longitude]);
                agentMarkers[markerId].setIcon(icon);
                agentMarkers[markerId].getPopup().setContent(popupContent);
            } else {
                agentMarkers[markerId] = L.marker([agent.latitude, agent.longitude], { icon })
                    .addTo(map)
                    .bindPopup(popupContent);
            }
        });

        // Update customer markers
        data.customers.forEach(customer => {
            const markerId = `customer-${customer.request_id}`;
            const icon = createMarkerIcon('customer', '👤');

            const popupContent = `
                <div class="p-2" style="min-width: 200px;">
                    <div class="font-bold text-lg mb-2">${customer.name}</div>
                    <div class="text-sm space-y-1">
                        <div><strong>Phone:</strong> ${customer.phone || 'N/A'}</div>
                        <div><strong>Address:</strong> ${customer.address || 'N/A'}</div>
                        <div><strong>Request:</strong> #${customer.request_number}</div>
                        <div><strong>Line:</strong> ${customer.line_type}</div>
                        <div><strong>Status:</strong> ${customer.status}</div>
                        ${customer.has_agent ? `<div><strong>Agent:</strong> ${customer.agent_name}</div>` : '<div class="text-amber-600">⏳ Waiting for agent...</div>'}
                    </div>
                    <button onclick="viewRequest(${customer.request_id})" class="mt-3 w-full py-2 bg-blue-500 text-white rounded font-bold text-sm">
                        View Details
                    </button>
                </div>
            `;

            if (customerMarkers[markerId]) {
                customerMarkers[markerId].setLatLng([customer.latitude, customer.longitude]);
                customerMarkers[markerId].getPopup().setContent(popupContent);
            } else {
                customerMarkers[markerId] = L.marker([customer.latitude, customer.longitude], { icon })
                    .addTo(map)
                    .bindPopup(popupContent);
            }
        });

        // Draw route lines for active requests
        data.active_requests.forEach(request => {
            if (request.route && request.route.start && request.route.end) {
                const lineId = `route-${request.id}`;
                const coords = [
                    [request.route.start.latitude, request.route.start.longitude],
                    [request.route.end.latitude, request.route.end.longitude]
                ];

                routeLines[lineId] = L.polyline(coords, {
                    color: '#a855f7',
                    weight: 4,
                    opacity: 0.8,
                    dashArray: '10, 10',
                    className: 'animated-line'
                }).addTo(map);

                // Add distance label
                const midPoint = [
                    (request.route.start.latitude + request.route.end.latitude) / 2,
                    (request.route.start.longitude + request.route.end.longitude) / 2
                ];

                if (request.distance_km) {
                    L.marker(midPoint, {
                        icon: L.divIcon({
                            className: 'distance-label',
                            html: `<div style="background: rgba(168,85,247,0.9); color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">${request.distance_km} km</div>`,
                            iconSize: [60, 20],
                            iconAnchor: [30, 10]
                        })
                    }).addTo(map);
                }
            }
        });
    }

    // Update stats display
    function updateStats(stats) {
        document.getElementById('stat-online-agents').textContent = stats.online_agents;
        document.getElementById('stat-active-requests').textContent = stats.active_requests;
        document.getElementById('stat-pending').textContent = stats.pending_requests;
        document.getElementById('stat-completed').textContent = stats.completed_today;
        document.getElementById('stat-bookings').textContent = stats.upcoming_bookings || 0;
        document.getElementById('stat-revenue').textContent = 'TSh ' + stats.revenue_today.toLocaleString();
    }

    // Update panel content
    function updatePanelContent(data) {
        // Requests list
        let requestsHtml = '';
        const allRequests = [...data.customers].sort((a, b) => {
            const statusOrder = { 'pending': 0, 'accepted': 1, 'in_progress': 2 };
            return (statusOrder[a.status] || 3) - (statusOrder[b.status] || 3);
        });

        if (allRequests.length === 0) {
            requestsHtml = '<div class="text-gray-500 text-center py-8">Hakuna request active</div>';
        } else {
            allRequests.forEach(req => {
                requestsHtml += `
                    <div class="request-card rounded-xl p-4" onclick="focusRequest(${req.request_id}, ${req.latitude}, ${req.longitude})">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-bold text-white">#${req.request_number}</span>
                            <span class="status-badge status-${req.status}">${req.status}</span>
                        </div>
                        <div class="text-sm text-gray-400 space-y-1">
                            <div>👤 ${req.name}</div>
                            <div>📍 ${req.address || 'Location set'}</div>
                            <div>📱 ${req.line_type}</div>
                            ${req.has_agent ? `<div class="text-green-400">🏍️ ${req.agent_name}</div>` : '<div class="text-amber-400">⏳ Waiting...</div>'}
                        </div>
                    </div>
                `;
            });
        }
        document.getElementById('requests-list').innerHTML = requestsHtml;

        // Agents list
        let agentsHtml = '';
        const sortedAgents = [...data.agents].sort((a, b) => {
            if (a.has_active_request !== b.has_active_request) return a.has_active_request ? -1 : 1;
            if (a.is_online !== b.is_online) return a.is_online ? -1 : 1;
            return 0;
        });

        sortedAgents.forEach(agent => {
            const statusClass = agent.has_active_request ? 'bg-amber-500' : (agent.is_online ? 'bg-green-500' : 'bg-gray-500');
            agentsHtml += `
                <div class="request-card rounded-xl p-4" onclick="focusAgent(${agent.id})">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-3 h-3 rounded-full ${statusClass}"></div>
                        <span class="font-bold text-white">${agent.name}</span>
                    </div>
                    <div class="text-sm text-gray-400 space-y-1">
                        <div>📞 ${agent.phone || 'N/A'}</div>
                        <div>⭐ ${agent.rating || 0} rating</div>
                        <div>✅ ${agent.total_completed} completed</div>
                        ${agent.has_active_request ? `
                            <div class="text-amber-400 mt-2">
                                🚀 Active: #${agent.active_request.request_number}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        document.getElementById('agents-list').innerHTML = agentsHtml || '<div class="text-gray-500 text-center py-8">No agents found</div>';

        // Customers list (ALL customers with location)
        let customersHtml = '';
        if (data.all_customers && data.all_customers.length > 0) {
            data.all_customers.forEach(customer => {
                const statusDot = customer.has_active_request ? 'bg-amber-500' : 'bg-blue-500';
                customersHtml += `
                    <div class="request-card rounded-xl p-4" onclick="focusCustomer(${customer.id}, ${customer.latitude}, ${customer.longitude})">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-3 h-3 rounded-full ${statusDot}"></div>
                            <span class="font-bold text-white">${customer.name}</span>
                        </div>
                        <div class="text-sm text-gray-400 space-y-1">
                            <div>📞 ${customer.phone || 'N/A'}</div>
                            <div>📊 ${customer.total_requests} requests</div>
                            ${customer.last_request_at ? `<div>⏰ Last: ${customer.last_request_at}</div>` : ''}
                            ${customer.has_active_request ? `
                                <div class="text-amber-400 mt-1">🚀 Active Request</div>
                            ` : ''}
                            <div class="text-gray-500 text-xs mt-1">📍 ${customer.location_updated_at || 'Unknown'}</div>
                        </div>
                    </div>
                `;
            });
        } else {
            customersHtml = '<div class="text-gray-500 text-center py-8">Hakuna customers wenye location</div>';
        }
        document.getElementById('customers-list').innerHTML = customersHtml;

        // Bookings list (upcoming)
        let bookingsHtml = '';
        const upcomingBookings = data.upcoming_bookings || [];
        if (upcomingBookings.length === 0) {
            bookingsHtml = '<div class="text-gray-500 text-center py-8">Hakuna booking zinazokuja</div>';
        } else {
            upcomingBookings.forEach(booking => {
                const hasLocation = booking.location && booking.location.latitude && booking.location.longitude;
                const clickHandler = hasLocation ? `onclick="focusBooking(${booking.id})"` : '';
                const cursorClass = hasLocation ? '' : 'opacity-70';
                const todayBadge = booking.is_today ? '<span class="ml-2 px-2 py-0.5 bg-green-500/20 text-green-400 text-xs rounded">LEO</span>' : '';
                
                bookingsHtml += `
                    <div class="request-card rounded-xl p-4 ${cursorClass}" ${clickHandler}>
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-bold text-white">#${booking.booking_number}${todayBadge}</span>
                            <span class="status-badge status-${booking.status}">${booking.status}</span>
                        </div>
                        <div class="text-sm text-gray-400 space-y-1">
                            <div>📅 ${booking.scheduled_date_display || booking.scheduled_date}</div>
                            <div>⏰ ${booking.time_slot}</div>
                            <div>📱 ${booking.line_type}</div>
                            <div>👤 ${booking.customer.name}</div>
                            ${booking.agent ? `<div class="text-green-400">🏍️ ${booking.agent.name}</div>` : ''}
                            ${hasLocation ? `<div class="text-purple-400">📍 ${booking.location.address || 'Location set'}</div>` : '<div class="text-red-400">⚠️ No location</div>'}
                        </div>
                    </div>
                `;
            });
        }
        document.getElementById('bookings-list').innerHTML = bookingsHtml;

        // Add all_customers markers to map (those without active requests)
        if (data.all_customers) {
            data.all_customers.forEach(customer => {
                // Skip if already shown as active request customer
                if (customer.has_active_request) return;
                
                const markerId = `all-customer-${customer.id}`;
                const icon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div class="marker-icon" style="background: linear-gradient(135deg, #60a5fa, #3b82f6); opacity: 0.7;">👤</div>`,
                    iconSize: [35, 35],
                    iconAnchor: [17, 17],
                    popupAnchor: [0, -17]
                });

                const popupContent = `
                    <div class="p-2" style="min-width: 180px;">
                        <div class="font-bold text-lg mb-2">${customer.name}</div>
                        <div class="text-sm space-y-1">
                            <div><strong>Phone:</strong> ${customer.phone || 'N/A'}</div>
                            <div><strong>Requests:</strong> ${customer.total_requests}</div>
                            ${customer.last_request_at ? `<div><strong>Last:</strong> ${customer.last_request_at}</div>` : ''}
                            <div class="text-gray-500 text-xs mt-1">Location: ${customer.location_updated_at}</div>
                        </div>
                    </div>
                `;

                if (!customerMarkers[markerId]) {
                    customerMarkers[markerId] = L.marker([customer.latitude, customer.longitude], { icon })
                        .addTo(map)
                        .bindPopup(popupContent);
                } else {
                    customerMarkers[markerId].setLatLng([customer.latitude, customer.longitude]);
                    customerMarkers[markerId].getPopup().setContent(popupContent);
                }
            });
        }

        // Add booking markers to map
        const bookingsData = data.upcoming_bookings || [];
        if (bookingsData.length > 0) {
            bookingsData.forEach(booking => {
                // Skip if no location
                if (!booking.location || !booking.location.latitude || !booking.location.longitude) {
                    console.log('Booking without location:', booking.booking_number);
                    return;
                }
                
                const markerId = `booking-${booking.id}`;
                const statusColor = {
                    'pending': '#eab308',
                    'confirmed': '#3b82f6',
                    'in_progress': '#a855f7'
                }[booking.status] || '#a855f7';
                
                const icon = L.divIcon({
                    className: 'custom-marker',
                    html: `<div class="marker-icon" style="background: linear-gradient(135deg, ${statusColor}, #7c3aed);">📅</div>`,
                    iconSize: [40, 40],
                    iconAnchor: [20, 20],
                    popupAnchor: [0, -20]
                });

                const popupContent = `
                    <div class="p-2" style="min-width: 220px;">
                        <div class="font-bold text-lg mb-2">📅 Booking #${booking.booking_number}</div>
                        <div class="text-sm space-y-1">
                            <div><strong>Date:</strong> ${booking.scheduled_date_display || booking.scheduled_date}${booking.is_today ? ' <span style="color: #22c55e">(LEO)</span>' : ''}</div>
                            <div><strong>Status:</strong> <span style="color: ${statusColor}; text-transform: uppercase; font-weight: bold;">${booking.status}</span></div>
                            <div><strong>Time:</strong> ${booking.time_slot || booking.scheduled_time || 'N/A'}</div>
                            <div><strong>Line:</strong> ${booking.line_type}</div>
                            <div><strong>Customer:</strong> ${booking.customer?.name || 'N/A'}</div>
                            <div><strong>Phone:</strong> ${booking.customer?.phone || 'N/A'}</div>
                            ${booking.location.address ? `<div><strong>Address:</strong> ${booking.location.address}</div>` : ''}
                            ${booking.agent ? `<div class="text-green-600"><strong>Agent:</strong> ${booking.agent.name}</div>` : '<div class="text-amber-600">⏳ Waiting for agent...</div>'}
                        </div>
                        <button onclick="focusBooking(${booking.id})" class="mt-3 w-full py-2 bg-purple-500 text-white rounded font-bold text-sm">
                            View Details
                        </button>
                    </div>
                `;

                if (bookingMarkers[markerId]) {
                    bookingMarkers[markerId].setLatLng([booking.location.latitude, booking.location.longitude]);
                    bookingMarkers[markerId].setIcon(icon);
                    bookingMarkers[markerId].getPopup().setContent(popupContent);
                } else {
                    bookingMarkers[markerId] = L.marker([booking.location.latitude, booking.location.longitude], { icon })
                        .addTo(map)
                        .bindPopup(popupContent);
                }
            });
        }
    }

    // Toggle side panel
    function togglePanel() {
        document.getElementById('side-panel').classList.toggle('open');
    }

    // Switch tabs
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));

        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        document.getElementById(`tab-${tabName}`).classList.remove('hidden');
    }

    // Focus on a specific agent
    function focusAgent(agentId) {
        const marker = agentMarkers[`agent-${agentId}`];
        if (marker) {
            map.setView(marker.getLatLng(), 15);
            marker.openPopup();
        }
    }

    // Focus on a specific request
    function focusRequest(requestId, lat, lng) {
        map.setView([lat, lng], 15);
        const marker = customerMarkers[`customer-${requestId}`];
        if (marker) {
            marker.openPopup();
        }
    }

    // Focus on a specific customer
    function focusCustomer(customerId, lat, lng) {
        map.setView([lat, lng], 15);
        const marker = customerMarkers[`all-customer-${customerId}`] || customerMarkers[`customer-${customerId}`];
        if (marker) {
            marker.openPopup();
        }
    }

    // Focus on a specific booking
    function focusBooking(bookingId) {
        const marker = bookingMarkers[`booking-${bookingId}`];
        if (marker) {
            map.setView(marker.getLatLng(), 15);
            marker.openPopup();
        }
    }

    // View request details
    async function viewRequest(requestId) {
        try {
            const response = await fetch(`{{ url('/admin/monitoring/request') }}/${requestId}`);
            const data = await response.json();
            console.log('Request details:', data);
            // Could open a modal here
        } catch (error) {
            console.error('Failed to get request details:', error);
        }
    }

    // Center map on all markers
    function centerMap() {
        const allMarkers = [...Object.values(agentMarkers), ...Object.values(customerMarkers), ...Object.values(bookingMarkers)];
        if (allMarkers.length > 0) {
            const group = L.featureGroup(allMarkers);
            map.fitBounds(group.getBounds().pad(0.1));
        } else {
            map.setView(DEFAULT_CENTER, DEFAULT_ZOOM);
        }
    }

    // Toggle heatmap
    async function toggleHeatmap() {
        showHeatmap = !showHeatmap;
        const btn = document.getElementById('heatmap-btn');

        if (showHeatmap) {
            btn.classList.add('bg-purple-600');
            
            try {
                const response = await fetch('{{ route("admin.monitoring.heatmap") }}?days=7');
                const data = await response.json();

                const heatData = data.heatmap_data.map(point => [point.lat, point.lng, point.weight]);
                
                if (heatLayer) {
                    map.removeLayer(heatLayer);
                }
                
                heatLayer = L.heatLayer(heatData, {
                    radius: 25,
                    blur: 15,
                    maxZoom: 17,
                    gradient: {
                        0.4: 'blue',
                        0.6: 'cyan',
                        0.7: 'lime',
                        0.8: 'yellow',
                        1.0: 'red'
                    }
                }).addTo(map);
            } catch (error) {
                console.error('Failed to load heatmap:', error);
            }
        } else {
            btn.classList.remove('bg-purple-600');
            if (heatLayer) {
                map.removeLayer(heatLayer);
                heatLayer = null;
            }
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', initMap);

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
</script>
@endpush
