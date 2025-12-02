<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - Attendance System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Attendance QR Code</h1>
            <p class="text-gray-600 mb-8">Scan this QR code to mark your attendance</p>
            
            <div class="bg-white p-4 rounded-lg inline-block border-4 border-blue-500 mb-6">
                {!! $qrCode !!}
            </div>
            
            <div class="space-y-4">
                <p class="text-sm text-gray-500">
                    Or visit: <a href="{{ route('attendance.scan') }}" class="text-blue-600 hover:underline">{{ route('attendance.scan') }}</a>
                </p>
                
                <a href="{{ route('attendance.scan') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    Go to Attendance Page
                </a>
            </div>
            
            <div class="mt-8 text-xs text-gray-400">
                <p>This QR code is for attendance tracking</p>
                <p>{{ now()->format('F d, Y') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
