<?php
// process.php - Handle form submission and call Python API

// Start session to store results
session_start();

// Collect form data
$data = [
    'name' => $_POST['name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'mobile' => $_POST['mobile'] ?? '',
    'address' => $_POST['address'] ?? '',
    'university' => $_POST['university'] ?? '',
    'degree' => $_POST['degree'] ?? '',
    'department' => $_POST['department'] ?? '',
    'cgpa' => $_POST['cgpa'] ?? '',
    'organization' => $_POST['organization'] ?? '',
    'designation' => $_POST['designation'] ?? '',
    'experience_years' => $_POST['experience_years'] ?? '',
    'industry' => $_POST['industry'] ?? '',
    'technical_skills' => $_POST['technical_skills'] ?? '',
    'achievements' => $_POST['achievements'] ?? '',
    'aspiration' => $_POST['aspiration'] ?? '',
    'num_versions' => 1
];

// Call Python API
$api_url = 'http://localhost:5000/api/generate-cv';
$ch = curl_init($api_url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['data' => $data]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $result = json_decode($response, true);
    
    // Generate unique ID for this generation
    $generation_id = uniqid('cv_');
    
    // Store in session
    $_SESSION['cv_results'][$generation_id] = [
        'input' => $data,
        'output' => $result,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Redirect to success page
    header("Location: index.php?success=1&id=" . $generation_id);
    exit;
} else {
    // Error handling
    echo "<h2>Error Generating CV</h2>";
    echo "<p>Please check if:</p>";
    echo "<ul>";
    echo "<li>Python backend is running (python app.py)</li>";
    echo "<li>Ollama is running (ollama serve)</li>";
    echo "<li>Llama model is installed (ollama pull llama3.2)</li>";
    echo "</ul>";
    echo "<pre>Error: " . htmlspecialchars($response) . "</pre>";
    echo '<a href="index.php">Go Back</a>';
}
?>