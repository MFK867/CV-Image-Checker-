<?php
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if picture was validated
    if (isset($_POST['validated_picture']) && $_POST['validated_picture']) {
        // Save picture data
        $_SESSION['validated_picture'] = $_POST['validated_picture'];
        $_SESSION['picture_feedback'] = $_POST['picture_feedback'] ?? '';
    }
    
    require_once 'process.php';
    exit;
}

// Get validated picture from session if exists
$validatedPicture = $_SESSION['validated_picture'] ?? null;
$pictureFeedback = $_SESSION['picture_feedback'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About section Generator with Picture Validation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .section-header {
            background: linear-gradient(90deg, #4CAF50 0%, #45a049 100%);
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 20px 0 10px 0;
            font-weight: 600;
        }
        .generated-box {
            background-color: #f8f9fa;
            padding: 24px;
            border-radius: 12px;
            border-left: 4px solid #4CAF50;
            margin: 16px 0;
            line-height: 1.6;
            font-size: 15px;
        }
        .nav-active {
            color: #10b981;
            font-weight: 600;
        }
        .webcam-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        #video {
            width: 100%;
            border-radius: 10px;
            transform: scaleX(-1);
        }
        .canvas-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        .feedback-item {
            padding: 8px 12px;
            margin: 4px 0;
            border-radius: 6px;
            font-size: 14px;
        }
        .feedback-good {
            background-color: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        .feedback-warning {
            background-color: #fef3c7;
            color: #92400e;
            border-left: 4px solid #f59e0b;
        }
        .feedback-error {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        .picture-preview {
            border: 2px solid #10b981;
            border-radius: 10px;
            padding: 10px;
            background: white;
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
    
    .webcam-container {
        position: relative;
        width: 100%;
        max-width: 640px;
        margin: 0 auto;
    }
    #video {
        width: 100%;
        border-radius: 10px;
        transform: scaleX(-1);
        border: 2px solid #e5e7eb;
    }
    .canvas-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }
    
    /* Feedback item styles */
    .feedback-item {
        padding: 10px 12px;
        margin-bottom: 8px;
        border-radius: 8px;
        font-size: 14px;
        display: flex;
        align-items: flex-start;
        transition: all 0.3s ease;
    }
    .feedback-good {
        background-color: #d1fae5;
        color: #065f46;
        border-left: 4px solid #10b981;
    }
    .feedback-warning {
        background-color: #fef3c7;
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }
    .feedback-error {
        background-color: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }
    .feedback-info {
        background-color: #e0f2fe;
        color: #075985;
        border-left: 4px solid #0ea5e9;
    }
    
    /* Requirements checklist */
    .requirement-circle {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }
    .requirement-circle.pending {
        background-color: #e5e7eb;
        color: #6b7280;
    }
    .requirement-circle.success {
        background-color: #10b981;
        color: white;
    }
    .requirement-circle.warning {
        background-color: #f59e0b;
        color: white;
    }
    .requirement-circle.error {
        background-color: #ef4444;
        color: white;
    }
    
    /* Status indicator */
    .status-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }
    .status-idle {
        background-color: #6b7280;
    }
    .status-processing {
        background-color: #f59e0b;
        animation: pulse 1.5s infinite;
    }
    .status-success {
        background-color: #10b981;
    }
    .status-error {
        background-color: #ef4444;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .nav-active {
        color: #10b981;
        font-weight: 600;
    }
    .section-header {
        background: linear-gradient(90deg, #4CAF50 0%, #45a049 100%);
        color: white;
        padding: 10px 15px;
        border-radius: 8px;
        margin: 20px 0 10px 0;
        font-weight: 600;
    }
    .generated-box {
        background-color: #f8f9fa;
        padding: 24px;
        border-radius: 12px;
        border-left: 4px solid #4CAF50;
        margin: 16px 0;
        line-height: 1.6;
        font-size: 15px;
    }
    .professional-badge {
    position: absolute;
    bottom: 10px;
    left: 10px;
    background: rgba(0, 0, 255, 0.8);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.professional-frame {
    border: 4px solid #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 4px;
}

.professional-frame::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.9) 0%, 
        rgba(248, 250, 252, 0.8) 50%,
        rgba(241, 245, 249, 0.9) 100%);
    pointer-events: none;
    z-index: 1;
}

    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-6">
                    <a href="index.php" class="flex items-center space-x-2 text-gray-800 hover:text-green-600">
                        <i class="fas fa-university text-green-600"></i>
                        <span class="font-semibold">Placement Bureau</span>
                    </a>
                    
                    <div class="flex space-x-6 ml-8">
                        <a href="index.php" class="nav-active">
                            <i class="fas fa-file-alt mr-1"></i> CV Generator
                        </a>
                    </div>
                </div>
                
                <div class="text-sm text-gray-500">
                    The University of Faisalabad
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header -->
        <div class="text-center mb-8 bg-white p-6 rounded-xl shadow-sm">
            <img src="https://tuf.edu.pk/Main/frontend/images/logo.png" alt="University Logo" class="h-16 mx-auto mb-4">
            <h1 class="text-3xl font-bold text-gray-800">The University of Faisalabad</h1>
            <h2 class="text-xl text-green-600 font-semibold mb-2">Placement Bureau</h2>
            <h3 class="text-2xl font-bold text-gray-700 mb-2">
                <i class="fas fa-file-alt text-green-500"></i> About Section Generator with Picture Validation
            </h3>
            <p class="text-gray-600">Step 1: Validate your professional picture | Step 2: Fill CV details</p>
            <div class="w-32 h-1 bg-green-500 mx-auto mt-4"></div>
        </div>

        <?php if (isset($_GET['success']) && isset($_GET['id'])): ?>
            <!-- Results Section -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Generated Results</h2>
                <div class="generated-box">
                    <p>Your CV has been generated successfully!</p>
                    <div class="mt-4">
                        <a href="result.php?id=<?= htmlspecialchars($_GET['id']) ?>" 
                           class="inline-block bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 font-semibold">
                            <i class="fas fa-eye mr-2"></i> View Generated About Me
                        </a>
                        <a href="index.php" class="inline-block ml-4 text-green-600 hover:text-green-800">
                            <i class="fas fa-plus mr-1"></i> Create Another
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Picture Validation Section -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-6 border-l-4 border-blue-500 pl-3">
                <i class="fas fa-camera text-blue-500 mr-2"></i> Step 1: Validate Your Professional Picture
            </h3>
            
            <?php if ($validatedPicture): ?>
                <!-- Picture Preview -->
                <div class="mb-6 picture-preview">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-semibold text-gray-800">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i> Picture Validated!
                        </h4>
                        <button onclick="resetPicture()" class="text-sm text-red-600 hover:text-red-800">
                            <i class="fas fa-times mr-1"></i> Change Picture
                        </button>
                    </div>
                    
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="md:w-1/3">
                            <img src="<?= htmlspecialchars($validatedPicture) ?>" 
                                 alt="Validated Picture" 
                                 class="w-full rounded-lg shadow">
                        </div>
                        
                        <div class="md:w-2/3">
                            <h5 class="font-semibold text-gray-700 mb-3">Validation Feedback:</h5>
                            <div id="feedbackList" class="space-y-2">
                                <?php 
                                if ($pictureFeedback) {
                                    $feedbackItems = json_decode($pictureFeedback, true);
                                    if (is_array($feedbackItems)) {
                                        foreach ($feedbackItems as $item) {
                                            $class = 'feedback-good';
                                            if (strpos($item, '⚠️') !== false) $class = 'feedback-warning';
                                            if (strpos($item, '❌') !== false) $class = 'feedback-error';
                                            echo '<div class="feedback-item ' . $class . '">' . htmlspecialchars($item) . '</div>';
                                        }
                                    }
                                }
                                ?>
                            </div>
                            
                            <div class="mt-4 p-4 bg-green-50 rounded-lg">
                                <p class="text-green-700 text-sm">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Your picture meets professional standards. Continue to fill your CV details below.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden input for validated picture -->
                <input type="hidden" name="validated_picture" value="<?= htmlspecialchars($validatedPicture) ?>">
                <input type="hidden" name="picture_feedback" value="<?= htmlspecialchars($pictureFeedback) ?>">
                
            <?php else: ?>
                <!-- Picture Validation Interface -->
                <div id="pictureValidationSection">
                    <div class="mb-6">
                        <p class="text-gray-600 mb-4">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            Upload or capture a professional picture for your CV. The system will validate:
                        </p>
                        
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <i class="fas fa-user text-blue-500 text-xl mb-2"></i>
                                <p class="text-xs font-medium">One Person</p>
                            </div>
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <i class="fas fa-eye text-blue-500 text-xl mb-2"></i>
                                <p class="text-xs font-medium">Straight Look</p>
                            </div>
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <i class="fas fa-arrows-alt-h text-blue-500 text-xl mb-2"></i>
                                <p class="text-xs font-medium">No Head Tilt</p>
                            </div>
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <i class="fas fa-lips text-blue-500 text-xl mb-2"></i>
                                <p class="text-xs font-medium">Mouth Closed</p>
                            </div>
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <i class="fas fa-lightbulb text-blue-500 text-xl mb-2"></i>
                                <p class="text-xs font-medium">Good Lighting</p>
                            </div>
                        </div>
                    </div>

                    <!-- Mode Selection -->
                    <div class="mb-8">
                        <h4 class="font-semibold text-gray-700 mb-4">Choose Validation Method:</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button id="webcamModeBtn" 
                                    class="p-4 rounded-xl border-2 border-green-500 bg-green-50 text-green-700 hover:bg-green-100 transition text-center"
                                    onclick="switchMode('webcam')">
                                <i class="fas fa-video text-2xl mb-2"></i>
                                <h4 class="font-bold">Use Webcam</h4>
                                <p class="text-sm mt-1">Real-time validation with live feedback</p>
                            </button>
                            
                            <button id="uploadModeBtn"
                                    class="p-4 rounded-xl border-2 border-blue-500 bg-blue-50 text-blue-700 hover:bg-blue-100 transition text-center"
                                    onclick="switchMode('upload')">
                                <i class="fas fa-upload text-2xl mb-2"></i>
                                <h4 class="font-bold">Upload Picture</h4>
                                <p class="text-sm mt-1">Validate existing photo file</p>
                            </button>
                        </div>
                    </div>

                   <!-- Webcam Section -->
<div id="webcamSection" class="hidden">
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Camera Preview -->
        <div class="lg:w-2/3">
            <div class="webcam-container mb-4">
                <video id="video" autoplay playsinline></video>
                <canvas id="canvas" class="canvas-overlay"></canvas>
            </div>
            
            <div class="flex justify-center space-x-4 mb-4">
                <button id="startWebcam" 
                        class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 font-semibold">
                    <i class="fas fa-play mr-2"></i> Start Webcam
                </button>
                <button id="validateWebcam" 
                        class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 font-semibold hidden">
                    <i class="fas fa-check-circle mr-2"></i> Validate & Continue
                </button>
                <button id="stopWebcam" 
                        class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 font-semibold hidden">
                    <i class="fas fa-stop mr-2"></i> Stop
                </button>
            </div>
        </div>
        
        <!-- Real-time Feedback Panel -->
        <div class="lg:w-1/3">
            <div class="bg-white rounded-xl shadow-lg p-5 h-full">
                <h4 class="font-bold text-gray-800 mb-4 border-b pb-2">
                    <i class="fas fa-comment-dots text-blue-500 mr-2"></i> Live Feedback
                </h4>
                
                <div id="realtimeFeedback" class="space-y-3">
                    <!-- Feedback will appear here -->
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-camera text-3xl mb-3"></i>
                        <p>Start webcam to see live feedback</p>
                    </div>
                </div>
                
                <!-- Requirements Checklist -->
                <div class="mt-6 pt-4 border-t">
                    <h5 class="font-semibold text-gray-700 mb-3">
                        <i class="fas fa-list-check text-green-500 mr-2"></i> Requirements
                    </h5>
                    
                    <div id="requirementsChecklist" class="space-y-2">
                        <div class="flex items-center">
                            <div class="requirement-circle pending mr-3">
                                <i class="fas fa-user text-xs"></i>
                            </div>
                            <span class="text-sm">Single face detected</span>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="requirement-circle pending mr-3">
                                <i class="fas fa-arrows-alt-h text-xs"></i>
                            </div>
                            <span class="text-sm">Head straight (no tilt)</span>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="requirement-circle pending mr-3">
                                <i class="fas fa-eye text-xs"></i>
                            </div>
                            <span class="text-sm">Looking straight at camera</span>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="requirement-circle pending mr-3">
                                <i class="fas fa-lips text-xs"></i>
                            </div>
                            <span class="text-sm">Mouth closed</span>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="requirement-circle pending mr-3">
                                <i class="fas fa-lightbulb text-xs"></i>
                            </div>
                            <span class="text-sm">Good lighting</span>
                        </div>
                    </div>
                </div>
                
                <!-- Status Indicator -->
                <div id="statusIndicator" class="mt-6 p-3 bg-gray-100 rounded-lg hidden">
                    <div class="flex items-center">
                        <div class="status-dot mr-3"></div>
                        <div>
                            <h6 class="font-medium text-sm" id="statusTitle">Status</h6>
                            <p class="text-xs text-gray-600" id="statusText">Waiting for camera</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

                    <!-- Upload Section -->
                    <div id="uploadSection" class="hidden">
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center mb-6">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <h4 class="text-lg font-semibold text-gray-700 mb-2">Upload Your Picture</h4>
                            <p class="text-gray-500 mb-4">JPG, PNG format (max 5MB)</p>
                            
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
                                <i class="fas fa-check-circle mr-2"></i> Validate & Continue
                            </button>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <!-- <div id="previewSection" class="hidden">
                        <div class="text-center mb-6">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Validation Result</h4>
                            <div class="relative inline-block">
                                <img id="resultImage" class="rounded-xl max-w-full h-auto max-h-64 mx-auto shadow">
                                <div id="validationStatus" class="absolute top-4 right-4 px-4 py-2 rounded-full text-white font-bold"></div>
                            </div>
                        </div>
                        
                        <div id="issuesList" class="mb-6"></div>
                        
                        <div class="flex justify-center space-x-4">
                            <button id="confirmPicture" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 hidden">
                                <i class="fas fa-check mr-2"></i> Use This Picture
                            </button>
                            <button onclick="resetValidation()" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600">
                                <i class="fas fa-redo mr-2"></i> Try Again
                            </button>
                        </div>
                    </div> -->
                    <!-- Preview Section -->
<div id="previewSection" class="hidden">
    <div class="text-center mb-6">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Validation Result</h4>
        <div class="relative inline-block">
            <img id="resultImage" class="rounded-xl max-w-full h-auto max-h-64 mx-auto shadow-lg">
            <div id="validationStatus" class="absolute top-4 right-4 px-4 py-2 rounded-full text-white font-bold">
                <!-- Status will be shown here -->
            </div>
        </div>
    </div>
    
    <div id="issuesList" class="mb-6"></div>
    
    <div class="flex justify-center space-x-4">
        <button id="continueBtn" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 font-semibold hidden">
            <i class="fas fa-arrow-right mr-2"></i> Continue to CV Form
        </button>
        
        <button id="downloadBtn" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 font-semibold hidden">
            <i class="fas fa-download mr-2"></i> Download Picture
        </button>
        
        <button id="newValidationBtn" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 font-semibold">
            <i class="fas fa-redo mr-2"></i> Try Another Picture
        </button>
        
        <!-- Old button for compatibility -->
        <button id="confirmPictureBtn" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 hidden">
            <i class="fas fa-check mr-2"></i> Use This Picture
        </button>
    </div>
    
    <div class="mt-6 text-center text-sm text-gray-500">
        <p><i class="fas fa-info-circle mr-1"></i> Click "Continue to CV Form" to proceed with this validated picture</p>
    </div>
</div>
                </div>
            <?php endif; ?>
        </div>

        <!-- CV Form Section -->
        <form method="POST" action="" class="bg-white rounded-xl shadow-lg p-6" id="cvForm">
            <h3 class="text-xl font-bold text-gray-800 mb-6 border-l-4 border-green-500 pl-3">
                <i class="fas fa-user-circle text-green-500 mr-2"></i> 
                Step 2: Fill Your CV Details
                <?php if (!$validatedPicture): ?>
                    <span class="text-sm font-normal text-gray-500 ml-2">
                        (Complete Step 1 first)
                    </span>
                <?php endif; ?>
            </h3>
            
            <?php if (!$validatedPicture): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-xl mr-3"></i>
                        <div>
                            <p class="font-medium text-yellow-800">Please validate your picture first</p>
                            <p class="text-sm text-yellow-600 mt-1">Complete Step 1 above before filling CV details</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Personal Information -->
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Personal Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Full Name *</label>
                        <input type="text" name="name" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                               placeholder="Faisal Kamran" <?= $validatedPicture ? '' : 'disabled' ?>>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                               placeholder="drazinabjaved@gmail.com" <?= $validatedPicture ? '' : 'disabled' ?>>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Mobile Number *</label>
                        <input type="text" name="mobile" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                               placeholder="0312-XXXXXXX" <?= $validatedPicture ? '' : 'disabled' ?>>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Address</label>
                        <input type="text" name="address" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                               <?= $validatedPicture ? '' : 'disabled' ?>>
                    </div>
                </div>
            </div>

            <!-- Education -->
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Education</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2">University *</label>
                        <input type="text" name="university" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="The University of Faisalabad" <?= $validatedPicture ? '' : 'disabled' ?>>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Degree Name *</label>
                        <input type="text" name="degree" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="Bachelor of Computer Science" <?= $validatedPicture ? '' : 'disabled' ?>>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Department *</label>
                        <input type="text" name="department" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="Computer Science" <?= $validatedPicture ? '' : 'disabled' ?>>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">CGPA/Percentage *</label>
                        <input type="text" name="cgpa" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="3.5/4.0 or 85%" <?= $validatedPicture ? '' : 'disabled' ?>>
                    </div>
                </div>
            </div>

            <!-- Experience -->
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Experience</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Organization</label>
                        <input type="text" name="organization" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="Nextech" <?= $validatedPicture ? '' : 'disabled' ?>>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Designation *</label>
                        <input type="text" name="designation" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="Web Developer" <?= $validatedPicture ? '' : 'disabled' ?>>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Years of Experience *</label>
                        <input type="text" name="experience_years" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="2 years" <?= $validatedPicture ? '' : 'disabled' ?>>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Industry *</label>
                        <input type="text" name="industry" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                               placeholder="Information Technology" <?= $validatedPicture ? '' : 'disabled' ?>>
                    </div>
                </div>
            </div>

            <!-- Skills & Achievements -->
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Skills & Achievements</h4>
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Technical Skills *</label>
                        <textarea name="technical_skills" rows="3" required 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                  placeholder="PHP, Laravel, JavaScript, React, MySQL..." <?= $validatedPicture ? '' : 'disabled' ?>></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Key Achievements *</label>
                        <textarea name="achievements" rows="3" required 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                  placeholder="Describe your key achievements..." <?= $validatedPicture ? '' : 'disabled' ?>></textarea>
                    </div>
                </div>
            </div>

            <!-- Aspiration -->
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">Career Aspiration</h4>
                <div>
                    <label class="block text-gray-700 mb-2">Future Aspirations *</label>
                    <select name="aspiration" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg" <?= $validatedPicture ? '' : 'disabled' ?>>
                        <option value="">Select your aspiration</option>
                        <option value="Interested in studying abroad">Interested in studying abroad</option>
                        <option value="Want to serve Family Business">Want to serve Family Business</option>
                        <option value="New Start-up (Business)">New Start-up (Business)</option>
                        <option value="Work as a freelancer">Work as a freelancer</option>
                        <option value="Interested in Job">Interested in Job</option>
                        <option value="Want to continue studies in Pakistan">Want to continue studies in Pakistan</option>
                    </select>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center pt-6 border-t">
                <?php if ($validatedPicture): ?>
                    <button type="submit" 
                            class="bg-green-500 text-white px-10 py-4 rounded-xl hover:bg-green-600 font-bold text-lg transition-all hover:scale-105">
                        <i class="fas fa-magic mr-2"></i> Generate My About Me Section
                    </button>
                    <p class="text-gray-500 text-sm mt-4">
                        <i class="fas fa-check-circle text-green-500 mr-1"></i>
                        Picture validated! All fields are now enabled.
                    </p>
                <?php else: ?>
                    <button type="button" 
                            class="bg-gray-300 text-gray-500 px-10 py-4 rounded-xl font-bold text-lg cursor-not-allowed"
                            disabled>
                        <i class="fas fa-lock mr-2"></i> Complete Step 1 First
                    </button>
                    <p class="text-gray-500 text-sm mt-4">
                        <i class="fas fa-info-circle mr-1"></i>
                        Validate your picture above to unlock CV form
                    </p>
                <?php endif; ?>
            </div>
        </form>
    </div>


<script>
    // Variables
    let currentMode = 'webcam';
    let stream = null;
    let currentImageData = null;
    let validationResult = null;
    let realtimeInterval = null;
    let requirementsState = {
        singleFace: 'pending',
        headStraight: 'pending',
        lookingStraight: 'pending',
        mouthClosed: 'pending',
        goodLighting: 'pending'
    };

    // Switch between modes
    function switchMode(mode) {
        currentMode = mode;
        
        // Update button styles
        document.getElementById('webcamModeBtn').classList.remove('border-green-500', 'bg-green-50', 'text-green-700');
        document.getElementById('uploadModeBtn').classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
        
        if (mode === 'webcam') {
            document.getElementById('webcamModeBtn').classList.add('border-green-500', 'bg-green-50', 'text-green-700');
            document.getElementById('uploadModeBtn').classList.add('border-gray-300', 'bg-gray-50', 'text-gray-500');
            document.getElementById('webcamSection').classList.remove('hidden');
            document.getElementById('uploadSection').classList.add('hidden');
            stopWebcam();
        } else {
            document.getElementById('uploadModeBtn').classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
            document.getElementById('webcamModeBtn').classList.add('border-gray-300', 'bg-gray-50', 'text-gray-500');
            document.getElementById('uploadSection').classList.remove('hidden');
            document.getElementById('webcamSection').classList.add('hidden');
            stopWebcam();
        }
        
        document.getElementById('previewSection').classList.add('hidden');
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
            document.getElementById('validateWebcam').classList.remove('hidden');
            document.getElementById('stopWebcam').classList.remove('hidden');
            
            // Start real-time validation
            startRealtimeValidation();
            
            // Update status
            updateStatus('processing', 'Camera Active', 'Analyzing your pose...');
            
        } catch (error) {
            showNotification('error', 'Webcam Error', 'Could not access webcam. Please check permissions.');
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
        document.getElementById('validateWebcam').classList.add('hidden');
        document.getElementById('stopWebcam').classList.add('hidden');
        
        // Stop real-time validation
        stopRealtimeValidation();
        
        // Reset UI
        resetFeedbackUI();
        updateStatus('idle', 'Camera Stopped', 'Start webcam to begin validation');
    }

    // Real-time validation
    function startRealtimeValidation() {
        // Clear previous feedback
        document.getElementById('realtimeFeedback').innerHTML = `
            <div class="text-center text-gray-500 py-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-3"></div>
                <p class="text-sm">Analyzing your pose...</p>
            </div>
        `;
        
        // Reset requirements
        resetRequirements();
        
        realtimeInterval = setInterval(async () => {
            if (!stream) return;
            
            const video = document.getElementById('video');
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            const imageData = canvas.toDataURL('image/jpeg', 0.7);
            
            try {
                const response = await fetch('http://localhost:5001/api/validate-webcam-frame', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ image: imageData })
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        displayRealtimeFeedback(result);
                        updateRequirementsFromFeedback(result);
                    }
                }
            } catch (error) {
                console.error('Realtime validation error:', error);
            }
        }, 1000); // Validate every second
    }

    function stopRealtimeValidation() {
        if (realtimeInterval) {
            clearInterval(realtimeInterval);
            realtimeInterval = null;
        }
    }

    function displayRealtimeFeedback(result) {
        const container = document.getElementById('realtimeFeedback');
        
        if (!result.realtime_feedback || result.realtime_feedback.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-500 py-4">
                    <i class="fas fa-camera text-2xl mb-3"></i>
                    <p class="text-sm">Adjust your pose to see feedback</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        
        result.realtime_feedback.forEach(item => {
            let icon = 'fa-info-circle';
            let bgClass = 'feedback-info';
            
            if (item.includes('✅')) {
                icon = 'fa-check-circle';
                bgClass = 'feedback-good';
            } else if (item.includes('⚠️')) {
                icon = 'fa-exclamation-triangle';
                bgClass = 'feedback-warning';
            } else if (item.includes('❌')) {
                icon = 'fa-times-circle';
                bgClass = 'feedback-error';
            }
            
            // Remove emoji from text
            const text = item.replace(/✅|⚠️|❌/g, '').trim();
            
            html += `
                <div class="feedback-item ${bgClass}">
                    <i class="fas ${icon} mt-0.5 mr-3"></i>
                    <span class="flex-1">${text}</span>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Update status based on validation result
        if (result.valid) {
            updateStatus('success', 'Perfect!', 'All requirements met');
        } else {
            const issueCount = result.issues ? result.issues.length : 0;
            if (issueCount === 0) {
                updateStatus('processing', 'Adjusting...', 'Keep improving your pose');
            } else if (issueCount <= 2) {
                updateStatus('warning', 'Needs Improvement', `${issueCount} issues to fix`);
            } else {
                updateStatus('error', 'Major Issues', `${issueCount} issues to fix`);
            }
        }
    }

    function updateRequirementsFromFeedback(result) {
        // Reset requirements state
        requirementsState = {
            singleFace: 'pending',
            headStraight: 'pending',
            lookingStraight: 'pending',
            mouthClosed: 'pending',
            goodLighting: 'pending'
        };
        
        // Analyze feedback to update requirements
        if (result.realtime_feedback) {
            result.realtime_feedback.forEach(item => {
                if (item.includes('Single face detected') && item.includes('✅')) {
                    requirementsState.singleFace = 'success';
                } else if (item.includes('Multiple faces') || item.includes('No face detected')) {
                    requirementsState.singleFace = 'error';
                }
                
                if (item.includes('Head straight') && item.includes('✅')) {
                    requirementsState.headStraight = 'success';
                } else if (item.includes('Head tilted') || item.includes('Head is tilted')) {
                    requirementsState.headStraight = 'error';
                }
                
                if (item.includes('Looking straight') && item.includes('✅')) {
                    requirementsState.lookingStraight = 'success';
                } else if (item.includes('Looking') && item.includes('⚠️')) {
                    requirementsState.lookingStraight = 'warning';
                }
                
                if (item.includes('Mouth closed') && item.includes('✅')) {
                    requirementsState.mouthClosed = 'success';
                } else if (item.includes('Mouth is open')) {
                    requirementsState.mouthClosed = 'error';
                }
                
                if (item.includes('Good lighting')) {
                    requirementsState.goodLighting = 'success';
                }
            });
        }
        
        // Update the checklist UI
        updateRequirementsChecklist();
    }

    function updateRequirementsChecklist() {
        const checklist = document.getElementById('requirementsChecklist');
        const items = checklist.querySelectorAll('.flex.items-center');
        
        // Update each requirement
        items.forEach((item, index) => {
            const circle = item.querySelector('.requirement-circle');
            let state = 'pending';
            let icon = item.querySelector('i').className;
            
            switch(index) {
                case 0: // Single face
                    state = requirementsState.singleFace;
                    break;
                case 1: // Head straight
                    state = requirementsState.headStraight;
                    break;
                case 2: // Looking straight
                    state = requirementsState.lookingStraight;
                    break;
                case 3: // Mouth closed
                    state = requirementsState.mouthClosed;
                    break;
                case 4: // Good lighting
                    state = requirementsState.goodLighting;
                    break;
            }
            
            // Update circle
            circle.className = `requirement-circle ${state} mr-3`;
            
            // Update text color based on state
            const text = item.querySelector('span');
            if (state === 'success') {
                text.className = 'text-sm text-green-600';
            } else if (state === 'warning') {
                text.className = 'text-sm text-yellow-600';
            } else if (state === 'error') {
                text.className = 'text-sm text-red-600';
            } else {
                text.className = 'text-sm text-gray-600';
            }
        });
    }

    function resetRequirements() {
        requirementsState = {
            singleFace: 'pending',
            headStraight: 'pending',
            lookingStraight: 'pending',
            mouthClosed: 'pending',
            goodLighting: 'pending'
        };
        updateRequirementsChecklist();
    }

    function resetFeedbackUI() {
        document.getElementById('realtimeFeedback').innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-camera text-3xl mb-3"></i>
                <p>Start webcam to see live feedback</p>
            </div>
        `;
        
        resetRequirements();
    }

    function updateStatus(status, title, text) {
        const indicator = document.getElementById('statusIndicator');
        const dot = indicator.querySelector('.status-dot');
        const titleEl = document.getElementById('statusTitle');
        const textEl = document.getElementById('statusText');
        
        indicator.classList.remove('hidden');
        
        // Update dot color
        dot.className = 'status-dot mr-3';
        if (status === 'idle') {
            dot.classList.add('status-idle');
        } else if (status === 'processing') {
            dot.classList.add('status-processing');
        } else if (status === 'success') {
            dot.classList.add('status-success');
        } else if (status === 'error') {
            dot.classList.add('status-error');
        } else if (status === 'warning') {
            dot.classList.add('status-processing'); // Use processing animation for warning
        }
        
        // Update text
        titleEl.textContent = title;
        textEl.textContent = text;
    }

    // Fix: Check if backend is running
    async function checkBackendConnection() {
        try {
            const response = await fetch('http://localhost:5001/api/health', {
                method: 'GET',
                timeout: 3000
            });
            return response.ok;
        } catch (error) {
            console.log('Backend not available:', error);
            return false;
        }
    }

    async function validateCurrentFrame() {
        if (!stream) return;
        
        // Check backend connection first
        const isBackendConnected = await checkBackendConnection();
        if (!isBackendConnected) {
            showNotification('error', 'Backend Connection', 'Picture validator backend is not running. Please start it on port 5001.');
            return;
        }
        
        showNotification('loading', 'Validating', 'Capturing and analyzing your picture...');
        
        const video = document.getElementById('video');
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        const imageData = canvas.toDataURL('image/jpeg', 0.8);
        await validateImage(imageData, 'webcam');
    }

    // File upload handling
    document.getElementById('fileInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (!file.type.match('image.*')) {
                showNotification('error', 'Invalid File', 'Please select an image file');
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                showNotification('error', 'File Too Large', 'Please select an image smaller than 5MB');
                return;
            }
            
            document.getElementById('fileName').textContent = `Selected: ${file.name}`;
            document.getElementById('validateUpload').classList.remove('hidden');
            
            const reader = new FileReader();
            reader.onload = function(e) {
                currentImageData = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    async function validateUploadedImage() {
        if (!currentImageData) {
            showNotification('error', 'No Image', 'Please select an image first');
            return;
        }
        
        // Check backend connection
        const isBackendConnected = await checkBackendConnection();
        if (!isBackendConnected) {
            showNotification('error', 'Backend Connection', 'Picture validator backend is not running. Please start it on port 5001.');
            return;
        }
        
        await validateImage(currentImageData, 'upload');
    }

    // Main validation function - FIXED
    // async function validateImage(imageData, mode) {
    //     showNotification('loading', 'Validating', 'Analyzing your picture...');
        
    //     try {
    //         const response = await fetch('http://localhost:5001/api/validate-image', {
    //             method: 'POST',
    //             headers: { 
    //                 'Content-Type': 'application/json',
    //                 'Accept': 'application/json'
    //             },
    //             body: JSON.stringify({
    //                 image: imageData,
    //                 mode: mode,
    //                 draw_feedback: true
    //             })
    //         });
            
    //         if (!response.ok) {
    //             throw new Error(`HTTP error! status: ${response.status}`);
    //         }
            
    //         const result = await response.json();
            
    //         if (result.success) {
    //             // Clear any previous notification
    //             const notification = document.querySelector('.notification');
    //             if (notification) notification.remove();
                
    //             displayResult(result);
    //         } else {
    //             showNotification('error', 'Validation Failed', result.error || 'Unknown error');
    //         }
    //     } catch (error) {
    //         console.error('Validation API error:', error);
    //         showNotification('error', 'Connection Error', 
    //             'Could not connect to validation service. ' +
    //             'Make sure picture-checker-api is running: ' +
    //             'Open terminal and run: cd picture-checker-api && python app.py'
    //         );
    //     }
    // }
    // Main validation function - UPDATED for professional format
async function validateImage(imageData, mode) {
    showNotification('loading', 'Validating', 'Analyzing your picture...');
    
    try {
        const response = await fetch('http://localhost:5001/api/validate-image', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                image: imageData,
                mode: mode,
                draw_feedback: true,
                professional_format: true  // Request professional format
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            // Clear any previous notification
            const notification = document.querySelector('.notification');
            if (notification) notification.remove();
            
            displayResult(result);
        } else {
            showNotification('error', 'Validation Failed', result.error || 'Unknown error');
        }
    } catch (error) {
        console.error('Validation API error:', error);
        showNotification('error', 'Connection Error', 
            'Could not connect to validation service. ' +
            'Make sure picture-checker-api is running: ' +
            'Open terminal and run: cd picture-checker-api && python app.py'
        );
    }
}
    // Display validation result - UPDATED with Continue button
    // function displayResult(result) {
    //     // Hide other sections
    //     document.getElementById('webcamSection').classList.add('hidden');
    //     document.getElementById('uploadSection').classList.add('hidden');
        
    //     // Show preview section
    //     const previewSection = document.getElementById('previewSection');
    //     previewSection.classList.remove('hidden');
        
    //     // Display image
    //     const resultImg = document.getElementById('resultImage');
    //     resultImg.src = result.annotated_image || result.image || '';
        
    //     // Display status
    //     const statusDiv = document.getElementById('validationStatus');
    //     if (result.valid) {
    //         statusDiv.textContent = '✅ APPROVED';
    //         statusDiv.className = 'absolute top-4 right-4 px-4 py-2 rounded-full bg-green-500 text-white font-bold';
            
    //         // Show success notification
    //         showNotification('success', 'Picture Approved!', 'Your picture meets all professional requirements.');
            
    //         // Update buttons - Show Continue and Download
    //         document.getElementById('continueBtn').classList.remove('hidden');
    //         document.getElementById('downloadBtn').classList.remove('hidden');
    //         document.getElementById('newValidationBtn').classList.remove('hidden');
    //         document.getElementById('confirmPictureBtn').classList.add('hidden'); // Hide old button
            
    //     } else {
    //         statusDiv.textContent = '❌ NEEDS IMPROVEMENT';
    //         statusDiv.className = 'absolute top-4 right-4 px-4 py-2 rounded-full bg-red-500 text-white font-bold';
            
    //         showNotification('warning', 'Improvements Needed', 'Check the issues below');
            
    //         // Hide Continue button if not valid
    //         document.getElementById('continueBtn').classList.add('hidden');
    //         document.getElementById('downloadBtn').classList.add('hidden');
    //         document.getElementById('newValidationBtn').classList.remove('hidden');
    //         document.getElementById('confirmPictureBtn').classList.add('hidden');
    //     }
        
    //     // Display issues or success feedback
    //     const issuesList = document.getElementById('issuesList');
    //     if (result.issues && result.issues.length > 0) {
    //         issuesList.innerHTML = `
    //             <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
    //                 <h5 class="font-semibold text-red-800 mb-3 flex items-center">
    //                     <i class="fas fa-exclamation-triangle mr-2"></i> Issues Found:
    //                 </h5>
    //                 <ul class="space-y-2">
    //                     ${result.issues.map(issue => `
    //                         <li class="flex items-start">
    //                             <i class="fas fa-circle text-red-500 text-xs mt-1 mr-2"></i>
    //                             <span class="text-red-700">${issue}</span>
    //                         </li>
    //                     `).join('')}
    //                 </ul>
    //             </div>
    //         `;
    //     } else if (result.feedback && result.feedback.length > 0) {
    //         issuesList.innerHTML = `
    //             <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
    //                 <h5 class="font-semibold text-green-800 mb-3 flex items-center">
    //                     <i class="fas fa-check-circle mr-2"></i> All Requirements Met:
    //                 </h5>
    //                 <ul class="space-y-2">
    //                     ${result.feedback.map(item => `
    //                         <li class="flex items-start">
    //                             <i class="fas fa-check text-green-500 text-xs mt-1 mr-2"></i>
    //                             <span class="text-green-700">${item.replace(/✅|⚠️|❌/g, '').trim()}</span>
    //                         </li>
    //                     `).join('')}
    //                 </ul>
    //             </div>
    //         `;
    //     } else {
    //         issuesList.innerHTML = `
    //             <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
    //                 <p class="text-blue-700 text-center">
    //                     <i class="fas fa-info-circle mr-2"></i>
    //                     Picture validation completed
    //                 </p>
    //             </div>
    //         `;
    //     }
        
    //     // Stop real-time validation if running
    //     stopRealtimeValidation();
    //     stopWebcam();
        
    //     // Store result globally for use in confirmPicture
    //     validationResult = result;
    // }
function displayResult(result) {
    // Hide other sections
    document.getElementById('webcamSection').classList.add('hidden');
    document.getElementById('uploadSection').classList.add('hidden');
    
    // Show preview section
    const previewSection = document.getElementById('previewSection');
    previewSection.classList.remove('hidden');
    
    // Display image
    const resultImg = document.getElementById('resultImage');
    resultImg.src = result.annotated_image || result.image || '';
    
    // Remove any existing professional badges
    const existingBadge = resultImg.parentElement.querySelector('.professional-badge');
    if (existingBadge) {
        existingBadge.remove();
    }
    
    // Remove professional frame class
    resultImg.classList.remove('professional-frame');
    
    // Add professional styling if image has blue background
    // In displayResult function, update this part:
if (result.has_white_background) {
    resultImg.classList.add('professional-frame');
    
    // Add professional badge for white background
    const badge = document.createElement('div');
    badge.className = 'professional-badge';
    badge.innerHTML = '<i class="fas fa-crown mr-1"></i> Professional';
    
    const imgContainer = resultImg.parentElement;
    if (!imgContainer.querySelector('.professional-badge')) {
        imgContainer.appendChild(badge);
    }
    
    // Update download button text for professional format
    const downloadBtn = document.getElementById('downloadBtn');
    if (downloadBtn) {
        downloadBtn.innerHTML = '<i class="fas fa-download mr-2"></i> Download PNG';
        downloadBtn.onclick = function() {
            downloadImage(result.annotated_image, `professional_white_bg_${Date.now()}.png`);
        };
    }
}
    // Display status
    const statusDiv = document.getElementById('validationStatus');
    if (result.valid) {
        statusDiv.textContent = '✅ APPROVED';
        statusDiv.className = 'absolute top-4 right-4 px-4 py-2 rounded-full bg-green-500 text-white font-bold';
        
        // Show success notification
        showNotification('success', 'Picture Approved!', 
            result.has_blue_background 
                ? 'Your professional picture with blue background is ready!' 
                : 'Your picture meets all professional requirements.');
        
        // Update buttons - Show Continue and Download
        document.getElementById('continueBtn').classList.remove('hidden');
        document.getElementById('downloadBtn').classList.remove('hidden');
        document.getElementById('newValidationBtn').classList.remove('hidden');
        document.getElementById('confirmPictureBtn').classList.add('hidden'); // Hide old button
        
    } else {
        statusDiv.textContent = '❌ NEEDS IMPROVEMENT';
        statusDiv.className = 'absolute top-4 right-4 px-4 py-2 rounded-full bg-red-500 text-white font-bold';
        
        showNotification('warning', 'Improvements Needed', 'Check the issues below');
        
        // Hide Continue button if not valid
        document.getElementById('continueBtn').classList.add('hidden');
        document.getElementById('downloadBtn').classList.add('hidden');
        document.getElementById('newValidationBtn').classList.remove('hidden');
        document.getElementById('confirmPictureBtn').classList.add('hidden');
    }
    
    // Display format info if available
    const formatInfo = document.getElementById('formatInfo');
    if (formatInfo) {
        if (result.has_blue_background) {
            formatInfo.classList.remove('hidden');
            formatInfo.innerHTML = `
                <div class="flex items-center justify-center text-sm text-blue-600 mt-2">
                    <i class="fas fa-palette mr-2"></i>
                    <span>Professional format: PNG with blue background</span>
                </div>
            `;
        } else {
            formatInfo.classList.add('hidden');
        }
    }
    
    // Display issues or success feedback
    const issuesList = document.getElementById('issuesList');
    if (result.issues && result.issues.length > 0) {
        issuesList.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <h5 class="font-semibold text-red-800 mb-3 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Issues Found:
                </h5>
                <ul class="space-y-2">
                    ${result.issues.map(issue => `
                        <li class="flex items-start">
                            <i class="fas fa-circle text-red-500 text-xs mt-1 mr-2"></i>
                            <span class="text-red-700">${issue}</span>
                        </li>
                    `).join('')}
                </ul>
            </div>
        `;
    } else if (result.feedback && result.feedback.length > 0) {
        issuesList.innerHTML = `
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <h5 class="font-semibold text-green-800 mb-3 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i> All Requirements Met:
                </h5>
                <ul class="space-y-2">
                    ${result.feedback.map(item => `
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-500 text-xs mt-1 mr-2"></i>
                            <span class="text-green-700">${item.replace(/✅|⚠️|❌/g, '').trim()}</span>
                        </li>
                    `).join('')}
                </ul>
                ${result.has_blue_background ? `
                    <div class="mt-3 pt-3 border-t border-green-200">
                        <div class="flex items-center text-blue-600">
                            <i class="fas fa-palette mr-2"></i>
                        ' </div>
                    </div>
                ` : ''}
            </div>
        `;
    } else {
        issuesList.innerHTML = `
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <p class="text-blue-700 text-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    Picture validation completed
                </p>
            </div>
        `;
    }
    
    // Stop real-time validation if running
    stopRealtimeValidation();
    stopWebcam();
    
    // Store result globally for use in confirmPicture
    validationResult = result;
}
    // Continue to CV Form function
    function continueToCVForm() {
        if (!validationResult || !validationResult.valid) {
            showNotification('error', 'Cannot Continue', 'Please validate a picture first');
            return;
        }
        
        // Save picture to session and redirect
        confirmPicture(validationResult);
    }

    // Confirm picture and enable form
    function confirmPicture(result) {
        // Show loading
        showNotification('loading', 'Saving Picture', 'Preparing your CV form...');
        
        // Create a form and submit to save picture to session
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'save-picture.php';
        
        const pictureInput = document.createElement('input');
        pictureInput.type = 'hidden';
        pictureInput.name = 'validated_picture';
        pictureInput.value = result.annotated_image || result.image;
        form.appendChild(pictureInput);
        
        const feedbackInput = document.createElement('input');
        feedbackInput.type = 'hidden';
        feedbackInput.name = 'picture_feedback';
        feedbackInput.value = JSON.stringify(result.feedback || []);
        form.appendChild(feedbackInput);
        
        document.body.appendChild(form);
        form.submit();
    }

    // Reset functions
    function resetPicture() {
        // Submit to clear session
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'clear-picture.php';
        document.body.appendChild(form);
        form.submit();
    }

    function resetValidation() {
        document.getElementById('previewSection').classList.add('hidden');
        document.getElementById('fileName').textContent = '';
        document.getElementById('fileInput').value = '';
        document.getElementById('validateUpload').classList.add('hidden');
        currentImageData = null;
        validationResult = null;
        
        switchMode(currentMode);
        updateStatus('idle', 'Ready', 'Start validation process');
    }

    // Download picture
    function downloadValidatedPicture() {
        if (!validationResult || !validationResult.annotated_image) {
            showNotification('error', 'No Picture', 'No validated picture to download');
            return;
        }
        
        const link = document.createElement('a');
        link.href = validationResult.annotated_image;
        link.download = `professional_picture_${Date.now()}.jpg`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showNotification('success', 'Downloaded', 'Picture saved to your device');
    }

    // Notification system
    function showNotification(type, title, message) {
        // Remove existing notifications
        const existing = document.querySelector('.notification');
        if (existing) existing.remove();
        
        const notification = document.createElement('div');
        notification.className = `notification fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 max-w-sm transform transition-transform duration-300 translate-x-0`;
        
        let icon = 'fa-info-circle';
        let bgColor = 'bg-blue-100';
        let textColor = 'text-blue-800';
        let borderColor = 'border-blue-300';
        
        switch(type) {
            case 'success':
                icon = 'fa-check-circle';
                bgColor = 'bg-green-100';
                textColor = 'text-green-800';
                borderColor = 'border-green-300';
                break;
            case 'error':
                icon = 'fa-times-circle';
                bgColor = 'bg-red-100';
                textColor = 'text-red-800';
                borderColor = 'border-red-300';
                break;
            case 'warning':
                icon = 'fa-exclamation-triangle';
                bgColor = 'bg-yellow-100';
                textColor = 'text-yellow-800';
                borderColor = 'border-yellow-300';
                break;
            case 'loading':
                icon = 'fa-spinner fa-spin';
                bgColor = 'bg-purple-100';
                textColor = 'text-purple-800';
                borderColor = 'border-purple-300';
                break;
        }
        
        notification.innerHTML = `
            <div class="flex items-start ${bgColor} ${textColor} ${borderColor} border p-4 rounded-lg">
                <i class="fas ${icon} text-xl mr-3"></i>
                <div class="flex-1">
                    <h4 class="font-semibold">${title}</h4>
                    <p class="text-sm mt-1">${message}</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds (except loading)
        if (type !== 'loading') {
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
        }
        
        return notification;
    }

    // Initialize backend check on page load
    window.addEventListener('load', async function() {
        // Check backend connection
        const isConnected = await checkBackendConnection();
        if (!isConnected) {
            console.warn('Picture validator backend not connected on port 5001');
        }
        
        // Initialize UI
        <?php if (!$validatedPicture): ?>
            switchMode('webcam');
            updateStatus('idle', 'Ready to Start', 'Choose validation method above');
        <?php endif; ?>
    });

    // Event listeners
    document.getElementById('startWebcam').addEventListener('click', startWebcam);
    document.getElementById('stopWebcam').addEventListener('click', stopWebcam);
    document.getElementById('validateWebcam').addEventListener('click', validateCurrentFrame);
    document.getElementById('validateUpload').addEventListener('click', validateUploadedImage);
    
    // New button listeners
    document.getElementById('continueBtn').addEventListener('click', continueToCVForm);
    document.getElementById('downloadBtn').addEventListener('click', downloadValidatedPicture);
    document.getElementById('newValidationBtn').addEventListener('click', resetValidation);
    document.getElementById('confirmPictureBtn').addEventListener('click', function() {
        if (validationResult) {
            confirmPicture(validationResult);
        }
    });
</script>
</body>
</html>