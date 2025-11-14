<div wire:poll.30s>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        {{-- Card section row --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div
                class="rounded-xl border border-neutral-200 bg-yellow-50 p-4 shadow dark:border-neutral-700 dark:bg-neutral-900">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Classifications Today</div>
                <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $stats['total_today'] ?? '0' }}
                </div>
            </div>

            <div
                class="rounded-xl border border-neutral-200 bg-green-100 p-4 shadow dark:border-neutral-700 dark:bg-neutral-900">
                <div class="text-sm text-gray-500 dark:text-gray-400">Biodegradable</div>
                <div class="mt-2 text-2xl font-semibold text-green-600 dark:text-green-400">
                    {{ $stats['biodegradable'] ?? '0' }}
                </div>
            </div>

            <div
                class="rounded-xl border border-neutral-200 bg-blue-100 p-4 shadow dark:border-neutral-700 dark:bg-neutral-900">
                <div class="text-sm text-gray-500 dark:text-gray-400">Non-Biodegradable</div>
                <div class="mt-2 text-2xl font-semibold text-cyan-600 dark:text-cyan-400">
                    {{ $stats['non_biodegradable'] ?? '0' }}
                </div>
            </div>
        </div>

        @if(Auth::check() && Auth::user()->role_id == 2)
            {{-- Camera Controls Section --}}
            <div class="p-6 flex flex-col gap-4 overflow-hidden rounded-xl bg-yellow-50 border border-neutral-200 shadow dark:border-neutral-700 dark:bg-neutral-900">

                {{-- Status Indicator --}}
                <div id="camera-status"
                    class="font-semibold text-neutral-700 dark:text-neutral-300 text-lg">
                    Camera Status: <span id="camera-status-text">Idle</span>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3">
                    <button id="btn-start-camera"
                        class="cursor-pointer px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition">
                        Start Camera
                    </button>

                    <button id="btn-stop-camera"
                        class="cursor-pointer px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                        Stop Camera
                    </button>
                </div>

                {{-- Loader --}}
                <div id="camera-loading" class="hidden mt-2">
                    <span class="animate-pulse text-neutral-500 dark:text-neutral-400">
                        Processing…
                    </span>
                </div>

            </div>
        @endif

        {{-- Script --}}
        <script>
            let statusInterval = null;
            let isPolling = false;
            let errorCount = 0;
            const MAX_ERRORS = 3;

            async function sendCameraCommand(endpoint) {
                const loading = document.getElementById('camera-loading');
                const statusText = document.getElementById('camera-status-text');

                loading.classList.remove('hidden');

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    });

                    const result = await response.json();

                    if (response.ok) {
                        statusText.textContent = result.message ?? 'OK';
                        // Reset error count on success
                        errorCount = 0;
                        // Restart polling if it was stopped
                        startPolling();
                    } else {
                        statusText.textContent = (result.message ?? 'Failed');
                    }

                } catch (err) {
                    console.error('Camera command error:', err);
                    statusText.textContent = 'Network Error';
                }

                loading.classList.add('hidden');
            }

            async function fetchStatus() {
                // Prevent overlapping requests
                if (isPolling) {
                    console.log('Skipping fetchStatus - already in progress');
                    return;
                }

                isPolling = true;

                try {
                    const res = await fetch("{{ route('camera.status') }}", {
                        headers: {
                            'Accept': 'application/json',
                        }
                    });

                    if (!res.ok) {
                        throw new Error(`HTTP ${res.status}`);
                    }

                    const data = await res.json();
                    document.getElementById('camera-status-text').textContent = data.running ? 'Running' : 'Idle';

                    if (data.last_result) {
                        console.log('Last result:', data.last_result);
                    }

                    // Reset error count on success
                    errorCount = 0;

                } catch (e) {
                    console.error('Status fetch error:', e);
                    errorCount++;

                    document.getElementById('camera-status-text').textContent = 'Disconnected';

                    // Stop polling after too many errors
                    if (errorCount >= MAX_ERRORS) {
                        console.warn(`Stopping polling after ${errorCount} errors`);
                        stopPolling();
                        document.getElementById('camera-status-text').textContent = 'Detection Service Offline';
                    }
                } finally {
                    isPolling = false;
                }
            }

            function startPolling() {
                if (statusInterval) {
                    return; // Already polling
                }

                console.log('Starting status polling');
                errorCount = 0;
                statusInterval = setInterval(fetchStatus, 5000);
                fetchStatus(); // Initial fetch
            }

            function stopPolling() {
                if (statusInterval) {
                    console.log('Stopping status polling');
                    clearInterval(statusInterval);
                    statusInterval = null;
                }
            }

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Page loaded - initializing camera controls');
                startPolling();

                document.getElementById('btn-start-camera').addEventListener('click', () => {
                    sendCameraCommand("{{ route('camera.start') }}");
                });

                document.getElementById('btn-stop-camera').addEventListener('click', () => {
                    sendCameraCommand("{{ route('camera.stop') }}");
                });
            });

            // Cleanup on page unload
            window.addEventListener('beforeunload', function() {
                stopPolling();
            });
        </script>

        {{-- Table section --}}
        <div
            class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 bg-yellow-50 shadow dark:border-neutral-700 dark:bg-neutral-900">
            <div class="flex items-center justify-between mb-4">
                <div class="p-4 text-lg font-semibold text-gray-800 dark:text-white">
                    Recent Classifications
                </div>
                <div>
                    <a href="{{ route('classifications_export.pdf') }}" class="mx-2 mt-2 px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                        Export Classifications to PDF
                    </a>
                    <a href="{{ route('classifications_export.csv') }}" class="mx-2 px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                        Export Classifications to CSV
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto px-4 pb-4">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                    <thead class="bg-gray-50 dark:bg-neutral-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">#</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">
                                Classification</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">
                                Confidence Score</th>
                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 dark:text-gray-300">
                                Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                        @forelse($classifications as $waste)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">{{ $loop->iteration }}
                                </td>
                                <td
                                    class="px-4 py-2 text-sm font-semibold {{ $waste->classification === 'Biodegradable' ? 'text-green-600 dark:text-green-400' : 'text-cyan-600 dark:text-cyan-400' }}">
                                    {{ $waste->classification }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200">
                                    {{ number_format($waste->score * 100, 2) }}%</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $waste->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No data
                                    available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
