<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Attendance Scan - Attendance System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Attendance System</h1>
                <p class="text-gray-600">{{ now()->format('l, F d, Y') }}</p>
                <p class="text-2xl font-semibold text-blue-600 mt-2" id="current-time"></p>
            </div>

            <!-- User Info -->
            <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Welcome,</p>
                        <p class="text-xl font-bold text-gray-800">{{ $user->name }}</p>
                        @if($user->role)
                            <p class="text-sm text-gray-600">{{ $user->role->name }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <a href="/admin/logout" class="text-sm text-red-600 hover:underline">Logout</a>
                    </div>
                </div>
            </div>

            <!-- Status Message -->
            <div id="message" class="hidden mb-6 p-4 rounded-lg"></div>

            <!-- Current Status -->
            <div id="status-card" class="mb-6 p-4 bg-gray-50 rounded-lg hidden">
                <h3 class="font-semibold text-gray-700 mb-2">Today's Status:</h3>
                <div id="status-content"></div>
            </div>

            <!-- Action Button -->
            <button 
                id="attendance-btn" 
                onclick="markAttendance()"
                class="w-full bg-blue-600 text-white py-4 rounded-lg font-semibold hover:bg-blue-700 transition transform hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Mark Attendance
            </button>

            <div class="mt-6 text-center">
                <a href="{{ route('attendance.qr') }}" class="text-sm text-blue-600 hover:underline">
                    View QR Code
                </a>
            </div>
        </div>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            });
            document.getElementById('current-time').textContent = timeString;
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Show message
        function showMessage(message, type = 'success') {
            const messageEl = document.getElementById('message');
            messageEl.className = `mb-6 p-4 rounded-lg ${
                type === 'success' ? 'bg-green-100 text-green-800 border border-green-300' :
                type === 'error' ? 'bg-red-100 text-red-800 border border-red-300' :
                'bg-blue-100 text-blue-800 border border-blue-300'
            }`;
            messageEl.textContent = message;
            messageEl.classList.remove('hidden');
            
            setTimeout(() => {
                messageEl.classList.add('hidden');
            }, 5000);
        }

        // Get user status
        async function getUserStatus() {
            try {
                const response = await fetch('/attendance/status', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();

                if (data.success && data.attendance) {
                    const statusCard = document.getElementById('status-card');
                    const statusContent = document.getElementById('status-content');
                    
                    let html = `
                        <p class="text-sm"><span class="font-medium">Time In:</span> ${data.attendance.time_in}</p>
                    `;
                    
                    if (data.attendance.time_out) {
                        html += `<p class="text-sm"><span class="font-medium">Time Out:</span> ${data.attendance.time_out}</p>`;
                        document.getElementById('attendance-btn').textContent = 'Already Checked Out';
                        document.getElementById('attendance-btn').disabled = true;
                    } else {
                        document.getElementById('attendance-btn').textContent = 'Check Out';
                        document.getElementById('attendance-btn').disabled = false;
                    }
                    
                    statusContent.innerHTML = html;
                    statusCard.classList.remove('hidden');
                } else {
                    document.getElementById('status-card').classList.add('hidden');
                    document.getElementById('attendance-btn').textContent = 'Check In';
                    document.getElementById('attendance-btn').disabled = false;
                }
            } catch (error) {
                console.error('Error fetching status:', error);
            }
        }

        // Mark attendance
        async function markAttendance() {
            const btn = document.getElementById('attendance-btn');
            btn.disabled = true;
            btn.textContent = 'Getting location...';

            try {
                // Get geolocation
                let latitude = null;
                let longitude = null;

                if (navigator.geolocation) {
                    try {
                        const position = await new Promise((resolve, reject) => {
                            navigator.geolocation.getCurrentPosition(resolve, reject, {
                                enableHighAccuracy: true,
                                timeout: 5000,
                                maximumAge: 0
                            });
                        });
                        latitude = position.coords.latitude;
                        longitude = position.coords.longitude;
                    } catch (geoError) {
                        console.warn('Geolocation error:', geoError);
                        // Continue without location if user denies or error occurs
                    }
                }

                btn.textContent = 'Processing...';

                const response = await fetch('/attendance/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        latitude: latitude,
                        longitude: longitude
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const action = data.action === 'check-in' ? 'checked in' : 'checked out';
                    let message = `${data.user} successfully ${action} at ${data.time}`;
                    if (data.location) {
                        message += `\nLocation: ${data.location}`;
                    }
                    showMessage(message, 'success');
                    getUserStatus();
                } else {
                    showMessage(data.message, 'error');
                    btn.disabled = false;
                    btn.textContent = 'Mark Attendance';
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'error');
                btn.disabled = false;
                btn.textContent = 'Mark Attendance';
            }
        }

        // Load initial status on page load
        getUserStatus();
    </script>
</body>
</html>
