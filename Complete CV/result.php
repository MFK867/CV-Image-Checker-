<?php
// result.php - Show generated CV results
session_start();

$id = $_GET['id'] ?? '';
$result = $_SESSION['cv_results'][$id] ?? null;

if (!$result) {
    header("Location: index.php");
    exit;
}

$input = $result['input'];
$output = $result['output'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Results - The University of Faisalabad</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8 bg-white p-6 rounded-xl shadow-sm">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-file-alt text-green-500"></i> Generated CV Summary
            </h1>
            <p class="text-gray-600">Professional "About Me" section tailored for your career goals</p>
            <div class="flex justify-center space-x-4 mt-4">
                <a href="index.php" class="text-green-600 hover:text-green-800">
                    <i class="fas fa-plus mr-1"></i> Create Another
                </a>
                <a href="javascript:window.print()" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-print mr-1"></i> Print
                </a>
            </div>
        </div>

        <!-- Generated Content -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-2">
                    <?= htmlspecialchars($input['name']) ?> - <?= htmlspecialchars($input['designation']) ?>
                </h2>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-calendar mr-1"></i> <?= $result['timestamp'] ?>
                    <span class="mx-2">•</span>
                    <i class="fas fa-graduation-cap mr-1"></i> <?= htmlspecialchars($input['university']) ?>
                    <span class="mx-2">•</span>
                    <i class="fas fa-bullseye mr-1"></i> <?= htmlspecialchars($input['aspiration']) ?>
                </div>
            </div>

            <?php if (isset($output['generated_sections'])): ?>
                <?php foreach ($output['generated_sections'] as $index => $section): ?>
                    <div class="border-l-4 border-green-500 bg-green-50 p-6 rounded-r-lg mb-6">
                        <div class="prose max-w-none">
                            <?= nl2br(htmlspecialchars($section['text'])) ?>
                        </div>
                        
                        <div class="mt-6 flex flex-wrap gap-3">
                            <button onclick="copyToClipboard('<?= addslashes($section['text']) ?>')" 
                                    class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 text-sm">
                                <i class="far fa-copy mr-1"></i> Copy Text
                            </button>
                            
                            <a href="data:text/plain;charset=utf-8,<?= rawurlencode($section['text']) ?>" 
                               download="<?= htmlspecialchars($input['name']) ?>_cv.txt"
                               class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 text-sm">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-exclamation-triangle text-3xl mb-4"></i>
                    <p>No content generated. Please try again.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- User Info -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Your Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p><strong class="text-gray-700">Name:</strong> <?= htmlspecialchars($input['name']) ?></p>
                    <p><strong class="text-gray-700">Email:</strong> <?= htmlspecialchars($input['email']) ?></p>
                    <p><strong class="text-gray-700">University:</strong> <?= htmlspecialchars($input['university']) ?></p>
                </div>
                <div>
                    <p><strong class="text-gray-700">Degree:</strong> <?= htmlspecialchars($input['degree']) ?></p>
                    <p><strong class="text-gray-700">CGPA:</strong> <?= htmlspecialchars($input['cgpa']) ?></p>
                    <p><strong class="text-gray-700">Experience:</strong> <?= htmlspecialchars($input['experience_years']) ?></p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 pt-6 border-t border-gray-200">
            <p class="text-gray-600">
                <i class="fas fa-university mr-2"></i>The University of Faisalabad Placement Bureau
            </p>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied to clipboard!');
            });
        }
    </script>
</body>
</html>