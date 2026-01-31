<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Generation Results</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .generated-box {
            background-color: #f8f9fa;
            padding: 24px;
            border-radius: 12px;
            border-left: 4px solid #4CAF50;
            margin: 16px 0;
            line-height: 1.6;
            font-size: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .metadata {
            opacity: 0.7;
            font-size: 13px;
            margin: 8px 0;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                <i class="fas fa-file-alt text-green-500"></i> CV Generation Results
            </h1>
            <p class="text-gray-600">Your AI-generated CV summaries</p>
            <div class="w-24 h-1 bg-green-500 mx-auto mt-4"></div>
        </div>

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('form') }}" class="inline-flex items-center text-green-600 hover:text-green-800">
                <i class="fas fa-arrow-left mr-2"></i> Back to Form
            </a>
        </div>

        <!-- Generated Results -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">
                    Generated Summaries for {{ $inputData['name'] }}
                </h2>
                <span class="text-sm text-gray-500">
                    {{ $timestamp->format('Y-m-d H:i:s') }}
                </span>
            </div>

            @if(empty($generatedSections))
                <div class="text-center py-12">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-4xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Results Generated</h3>
                    <p class="text-gray-600">Something went wrong during generation. Please try again.</p>
                    <a href="{{ route('form') }}" class="inline-block mt-4 bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600">
                        Try Again
                    </a>
                </div>
            @else
                @foreach($generatedSections as $index => $section)
                    <div class="mb-8 pb-8 border-b border-gray-200 last:border-b-0 last:pb-0 last:mb-0">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">Version {{ $index + 1 }}</h3>
                                <div class="metadata">
                                    <i class="fas fa-calendar mr-1"></i> {{ $section['timestamp'] ?? 'N/A' }}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-globe mr-1"></i> {{ $section['market'] ?? 'N/A' }}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-bullseye mr-1"></i> {{ $section['aspiration'] ?? 'N/A' }}
                                </div>
                            </div>
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">
                                {{ str_word_count($section['text']) }} words
                            </span>
                        </div>

                        <div class="generated-box">
                            {!! nl2br(e($section['text'])) !!}
                        </div>

                        <div class="flex flex-wrap gap-3 mt-4">
                            <button onclick="copyToClipboard('{{ $section['text'] }}', {{ $index }})" 
                                    class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition text-sm">
                                <i class="far fa-copy mr-1"></i> Copy
                            </button>
                            
                            <a href="data:text/plain;charset=utf-8,{{ rawurlencode($section['text']) }}" 
                               download="{{ str_replace(' ', '_', $inputData['name']) }}_cv_v{{ $index + 1 }}.txt"
                               class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition text-sm">
                                <i class="fas fa-download mr-1"></i> Download TXT
                            </a>
                            
                            <a href="mailto:?subject=CV Summary - {{ $inputData['name'] }}&body={{ rawurlencode($section['text']) }}"
                               class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition text-sm">
                                <i class="fas fa-envelope mr-1"></i> Email
                            </a>
                            
                            <button onclick="printSection({{ $index }})" 
                                    class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition text-sm">
                                <i class="fas fa-print mr-1"></i> Print
                            </button>
                        </div>

                        <div id="copy-message-{{ $index }}" class="text-green-600 text-sm mt-2 hidden">
                            <i class="fas fa-check mr-1"></i> Copied to clipboard!
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- User Info Summary -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Your Submitted Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-700 mb-2"><i class="fas fa-user text-green-500 mr-2"></i> Personal</h4>
                    <p><strong>Name:</strong> {{ $inputData['name'] }}</p>
                    <p><strong>Email:</strong> {{ $inputData['email'] }}</p>
                    <p><strong>Mobile:</strong> {{ $inputData['mobile'] }}</p>
                    <p><strong>University:</strong> {{ $inputData['university'] }}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium text-gray-700 mb-2"><i class="fas fa-briefcase text-green-500 mr-2"></i> Professional</h4>
                    <p><strong>Designation:</strong> {{ $inputData['designation'] }}</p>
                    <p><strong>Experience:</strong> {{ $inputData['experience_years'] }}</p>
                    <p><strong>Industry:</strong> {{ $inputData['industry'] }}</p>
                    <p><strong>Aspiration:</strong> {{ $inputData['aspiration'] }}</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-12 pt-6 border-t border-gray-200">
            <p class="text-gray-600">
                The University of Faisalabad Placement Bureau
            </p>
            <p class="text-sm text-gray-500 mt-2">
                Generated on {{ $timestamp->format('F d, Y') }}
            </p>
        </div>
    </div>

    <script>
        function copyToClipboard(text, index) {
            navigator.clipboard.writeText(text).then(() => {
                const message = document.getElementById(`copy-message-${index}`);
                message.classList.remove('hidden');
                setTimeout(() => {
                    message.classList.add('hidden');
                }, 3000);
            });
        }

        function printSection(index) {
            const content = document.querySelectorAll('.generated-box')[index];
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>CV Summary - Version ${index + 1}</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
                        .content { white-space: pre-wrap; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>CV Summary - {{ $inputData['name'] }}</h1>
                        <p>Generated on {{ $timestamp->format('F d, Y') }}</p>
                        <p>Version ${index + 1}</p>
                    </div>
                    <div class="content">${content.innerText}</div>
                    <div class="footer">
                        The University of Faisalabad Placement Bureau
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</body>
</html>