<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Picture Validator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .webcam-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
        }
        #video {
            width: 100%;
            border-radius: 10px;
            transform: scaleX(-1); /* Mirror effect */
        }
        .canvas-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        .status-box {
            transition: all 0.3s ease;
        }
        .status-valid {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        .status-invalid {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-6">
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-green-600">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                    <a href="{{ route('cv.form') }}" class="text-gray-700 hover:text-green-600">
                        <i class="fas fa-file-alt mr-1"></i> CV Generator
                    </a>
                    <a href="{{ route('picture.validator') }}" class="text-green-600 font-semibold">
                        <i class="fas fa-camera mr-1"></i> Picture Validator
                    </a>
                </div>
                <div class="text-sm text-gray-500">
                    The University of Faisalabad
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-camera text-green-500"></i> Professional Picture Validator
            </h1>
            <p class="text-gray-600">Validate your professional photo for CV, LinkedIn, or official documents</p>
            <div class="w-32 h-1 bg-green-500 mx-auto mt-4"></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Panel - Guidelines -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-list-check mr-2"></i> Requirements
                    </h3>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                <i class="fas fa-user text-green-600 text-xs"></i>
                            </div>
                            <span class="text-gray-700">Exactly one person in frame</span>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                <i class="fas fa-eye text-green-600 text-xs"></i>
                            </div>
                            <span class="text-gray-700">Face looking straight at camera</span>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                <i class="fas fa-arrows-alt-h text-green-600 text-xs"></i>
                            </div>
                            <span class="text-gray-700">Head not tilted left or right</span>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                <i class="fas fa-lips text-green-600 text-xs"></i>
                            </div>
                            <span class="text-gray-700">Mouth closed</span>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                <i class="fas fa-lightbulb text-green-600 text-xs"></i>
                            </div>
                            <span class="text-gray-700">Good lighting and clear image</span>
                        </div>
                    </div>
                    
                    <h4 class="text-md font-semibold text-gray-800 mb-3">
                        <i class="fas fa-tips mr-2"></i> Tips for Best Results
                    </h4>
                    
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                            <i class="fas fa-circle text-green-500 text-xs mt-1 mr-2"></i>
                            <span>Use natural light facing towards you</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-circle text-green-500 text-xs mt-1 mr-2"></i>
                            <span>Plain background works best</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-circle text-green-500 text-xs mt-1 mr-2"></i>
                            <span>Wear professional attire</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-circle text-green-500 text-xs mt-1 mr-2"></i>
                            <span>Position camera at eye level</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Panel - Camera/Upload -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <!-- Mode Selection -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Select Mode</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button id="webcamMode" 
                                    class="p-6 rounded-xl border-2 border-green-500 bg-green-50 text-green-700 hover:bg-green-100 transition text-center"
                                    onclick="switchMode('webcam')">
                                <i class="fas fa-video text-3xl mb-3"></i>
                                <h4 class="font-bold text-lg">Use Webcam</h4>
                                <p class="text-sm mt-2">Real-time validation with guidance</p>
                            </button>
                            
                            <button id="uploadMode"
                                    class="p-6 rounded-xl border-2 border-blue-500 bg-blue-50 text-blue-700 hover:bg-blue-100 transition text-center"
                                    onclick="switchMode('upload')">
                                <i class="fas fa-upload text-3xl mb-3"></i>
                                <h4 class="font-bold text-lg">Upload Picture</h4>
                                <p class="text-sm mt-2">Validate existing photo</p>
                            </button>
                        </div>
                    </div>

                    <!-- Webcam Section -->
                    <div id="webcamSection" class="hidden">
                        <div class="webcam-container mb-4">
                            <video id="video" autoplay playsinline></video>
                            <canvas id="canvas" class="canvas-overlay"></canvas>
                        </div>
                        
                        <div class="flex justify-center space-x-4 mb-6">
                            <button id="startWebcam" 
                                    class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 font-semibold">
                                <i class="fas fa-play mr-2"></i> Start Webcam
                            </button>
                            <button id="captureBtn" 
                                    class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 font-semibold hidden">
                                <i class="fas fa-camera mr-2"></i> Capture & Validate
                            </button>
                            <button id="stopWebcam" 
                                    class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 font-semibold hidden">
                                <i class="fas fa-stop mr-2"></i> Stop
                            </button>
                        </div>
                    </div>

                    <!-- Upload Section -->
                    <div id="uploadSection" class="hidden">
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center mb-6">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <h4 class="text-lg font-semibold text-gray-700 mb-2">Upload Your Picture</h4>
                            <p class="text-gray-500 mb-4">JPG, PNG, or WebP format</p>
                            
                            <input type="file" id="fileInput" accept="image/*" class="hidden">
                            <button onclick="document.getElementById('fileInput').click()"
                                    class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 font-semibold">
                                <i class="fas fa-folder-open mr-2"></i> Choose File
                            </button>
                            
                            <div id="fileName" class="mt-3 text-sm text-gray-600"></div>
                        </div>
                        
                        <div class="text-center">
                            <button id="validateUpload" 
                                    class="bg-green-500 text-white px-8 py-3 rounded-lg hover:bg-green-600 font-semibold hidden">
                                <i class="fas fa-check-circle mr-2"></i> Validate Picture
                            </button>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div id="previewSection" class="hidden">
                        <div class="text-center mb-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Validation Result</h4>
                            <div class="relative inline-block">
                                <img id="resultImage" class="rounded-xl max-w-full h-auto max-h-96 mx-auto">
                                <div id="validationStatus" class="absolute top-4 right-4 px-4 py-2 rounded-full text-white font-bold">
                                    <!-- Status will be shown here -->
                                </div>
                            </div>
                        </div>
                        
                        <div id="issuesList" class="mb-6"></div>
                        
                        <div class="flex justify-center space-x-4">
                            <button id="downloadBtn" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 hidden">
                                <i class="fas fa-download mr-2"></i> Download
                            </button>
                            <button id="newValidation" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600">
                                <i class="fas fa-redo mr-2"></i> New Validation
                            </button>
                        </div>
                    </div>

                    <!-- Status -->
                    <div id="statusContainer" class="mt-6">
                        <div id="statusBox" class="status-box p-4 rounded-lg hidden">
                            <div class="flex items-center">
                                <i id="statusIcon" class="fas fa-info-circle text-xl mr-3"></i>
                                <div>
                                    <h4 id="statusTitle" class="font-semibold"></h4>
                                    <p id="statusMessage" class="text-sm"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Variables
        let currentMode = 'webcam';
        let stream = null;
        let currentImageData = null;
        let validationResult = null;

        // Switch between modes
        function switchMode(mode) {
            currentMode = mode;
            
            // Update button styles
            document.getElementById('webcamMode').classList.remove('border-green-500', 'bg-green-50', 'text-green-700');
            document.getElementById('uploadMode').classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
            
            if (mode === 'webcam') {
                document.getElementById('webcamMode').classList.add('border-green-500', 'bg-green-50', 'text-green-700');
                document.getElementById('uploadMode').classList.add('border-gray-300', 'bg-gray-50', 'text-gray-500');
                document.getElementById('webcamSection').classList.remove('hidden');
                document.getElementById('uploadSection').classList.add('hidden');
                stopWebcam(); // Stop any running webcam
            } else {
                document.getElementById('uploadMode').classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
                document.getElementById('webcamMode').classList.add('border-gray-300', 'bg-gray-50', 'text-gray-500');
                document.getElementById('uploadSection').classList.remove('hidden');
                document.getElementById('webcamSection').classList.add('hidden');
                stopWebcam();
            }
            
            document.getElementById('previewSection').classList.add('hidden');
            hideStatus();
        }

        // Webcam functions
        async function startWebcam() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: { ideal: 640 },
                        height: { ideal: 480 },
                        facingMode: 'user' 
                    }, 
                    audio: false 
                });
                
                const video = document.getElementById('video');
                video.srcObject = stream;
                
                document.getElementById('startWebcam').classList.add('hidden');
                document.getElementById('captureBtn').classList.remove('hidden');
                document.getElementById('stopWebcam').classList.remove('hidden');
                
                showStatus('info', 'Webcam Active', 'Adjust your pose based on the overlay guidance');
            } catch (error) {
                showStatus('error', 'Webcam Error', 'Could not access webcam. Please check permissions.');
                console.error('Webcam error:', error);
            }
        }

        function stopWebcam() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            
            const video = document.getElementById('video');
            video.srcObject = null;
            
            document.getElementById('startWebcam').classList.remove('hidden');
            document.getElementById('captureBtn').classList.add('hidden');
            document.getElementById('stopWebcam').classList.add('hidden');
            
            hideStatus();
        }

        function captureFromWebcam() {
            const video = document.getElementById('video');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            
            // Flip horizontally for mirror effect
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            // Convert to base64
            const imageData = canvas.toDataURL('image/jpeg', 0.8);
            validateImage(imageData, 'webcam');
        }

        // File upload handling
        document.getElementById('fileInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (!file.type.match('image.*')) {
                    showStatus('error', 'Invalid File', 'Please select an image file (JPG, PNG, etc.)');
                    return;
                }
                
                if (file.size > 5 * 1024 * 1024) { // 5MB limit
                    showStatus('error', 'File Too Large', 'Please select an image smaller than 5MB');
                    return;
                }
                
                document.getElementById('fileName').textContent = `Selected: ${file.name}`;
                document.getElementById('validateUpload').classList.remove('hidden');
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    currentImageData = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        function validateUploadedImage() {
            if (currentImageData) {
                validateImage(currentImageData, 'upload');
            } else {
                showStatus('error', 'No Image', 'Please select an image first');
            }
        }

        // Validation function
        async function validateImage(imageData, mode) {
            showStatus('loading', 'Validating', 'Analyzing your picture...');
            
            try {
                const response = await fetch('{{ route("picture.validate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        image: imageData,
                        mode: mode
                    })
                });
                
                const result = await response.json();
                validationResult = result;
                
                if (result.success) {
                    displayResult(result);
                } else {
                    showStatus('error', 'Validation Failed', result.error || 'Unknown error');
                }
            } catch (error) {
                showStatus('error', 'Connection Error', 'Could not connect to validation service');
                console.error('Validation error:', error);
            }
        }

        // Display validation result
        function displayResult(result) {
            // Hide other sections
            document.getElementById('webcamSection').classList.add('hidden');
            document.getElementById('uploadSection').classList.add('hidden');
            
            // Show preview section
            document.getElementById('previewSection').classList.remove('hidden');
            
            // Display image
            const resultImg = document.getElementById('resultImage');
            resultImg.src = result.annotated_image || result.image || '';
            
            // Display status
            const statusDiv = document.getElementById('validationStatus');
            if (result.valid) {
                statusDiv.textContent = '✅ APPROVED';
                statusDiv.className = 'absolute top-4 right-4 px-4 py-2 rounded-full bg-green-500 text-white font-bold';
                showStatus('success', 'Picture Approved', 'Your picture meets all professional requirements!');
                
                // Show download button
                document.getElementById('downloadBtn').classList.remove('hidden');
                document.getElementById('downloadBtn').onclick = function() {
                    downloadImage(result.annotated_image || result.image, `professional_photo_${Date.now()}.jpg`);
                };
            } else {
                statusDiv.textContent = '❌ NEEDS IMPROVEMENT';
                statusDiv.className = 'absolute top-4 right-4 px-4 py-2 rounded-full bg-red-500 text-white font-bold';
                showStatus('warning', 'Improvements Needed', 'Check the issues below');
                
                document.getElementById('downloadBtn').classList.add('hidden');
            }
            
            // Display issues
            const issuesList = document.getElementById('issuesList');
            if (result.issues && result.issues.length > 0) {
                issuesList.innerHTML = `
                    <h5 class="font-semibold text-gray-800 mb-2">Issues Found:</h5>
                    <ul class="space-y-2">
                        ${result.issues.map(issue => `
                            <li class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-red-500 mt-1 mr-2"></i>
                                <span class="text-gray-700">${issue}</span>
                            </li>
                        `).join('')}
                    </ul>
                `;
            } else {
                issuesList.innerHTML = `
                    <div class="text-center text-green-600">
                        <i class="fas fa-check-circle text-3xl mb-2"></i>
                        <p class="font-semibold">All requirements met!</p>
                    </div>
                `;
            }
        }

        // Status display
        function showStatus(type, title, message) {
            const statusBox = document.getElementById('statusBox');
            const statusIcon = document.getElementById('statusIcon');
            const statusTitle = document.getElementById('statusTitle');
            const statusMessage = document.getElementById('statusMessage');
            
            statusBox.className = `status-box p-4 rounded-lg flex items-center`;
            
            switch(type) {
                case 'success':
                    statusBox.classList.add('status-valid');
                    statusIcon.className = 'fas fa-check-circle text-xl mr-3';
                    break;
                case 'error':
                    statusBox.classList.add('status-invalid');
                    statusIcon.className = 'fas fa-times-circle text-xl mr-3';
                    break;
                case 'warning':
                    statusBox.className = 'status-box p-4 rounded-lg flex items-center bg-yellow-100 text-yellow-800';
                    statusIcon.className = 'fas fa-exclamation-triangle text-xl mr-3';
                    break;
                case 'info':
                    statusBox.className = 'status-box p-4 rounded-lg flex items-center bg-blue-100 text-blue-800';
                    statusIcon.className = 'fas fa-info-circle text-xl mr-3';
                    break;
                case 'loading':
                    statusBox.className = 'status-box p-4 rounded-lg flex items-center bg-purple-100 text-purple-800';
                    statusIcon.className = 'fas fa-spinner fa-spin text-xl mr-3';
                    break;
            }
            
            statusTitle.textContent = title;
            statusMessage.textContent = message;
            statusBox.classList.remove('hidden');
        }

        function hideStatus() {
            document.getElementById('statusBox').classList.add('hidden');
        }

        // Utility functions
        function downloadImage(dataUrl, filename) {
            const link = document.createElement('a');
            link.href = dataUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function resetValidation() {
            document.getElementById('previewSection').classList.add('hidden');
            document.getElementById('fileName').textContent = '';
            document.getElementById('fileInput').value = '';
            document.getElementById('validateUpload').classList.add('hidden');
            currentImageData = null;
            validationResult = null;
            
            switchMode(currentMode);
        }

        // Event listeners
        document.getElementById('startWebcam').addEventListener('click', startWebcam);
        document.getElementById('stopWebcam').addEventListener('click', stopWebcam);
        document.getElementById('captureBtn').addEventListener('click', captureFromWebcam);
        document.getElementById('validateUpload').addEventListener('click', validateUploadedImage);
        document.getElementById('newValidation').addEventListener('click', resetValidation);
        
        // Initialize
        switchMode('webcam');
    </script>
</body>
</html>