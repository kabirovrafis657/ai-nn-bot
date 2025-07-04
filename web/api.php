<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include '../login.php';
include '../languages.php';

function processImageAPI() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['image']) || !isset($input['mode'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters']);
        return;
    }
    
    $base64Image = $input['image'];
    $mode = $input['mode'];
    $userId = $input['userId'] ?? 'web_user_' . time();
    
    try {
        // Set user mode in database
        $db = mysqli_connect("localhost", "develosh_ai", "Shaha2001", "develosh_ai");
        $query = "INSERT INTO `users` (`id`, `mode`, `count`, `date`, `language`) VALUES ('$userId', '$mode', '1', CURRENT_TIMESTAMP, 'en') ON DUPLICATE KEY UPDATE mode='$mode';";
        $db->query($query);
        
        // Process the image using the existing undress function
        $processedImage = processImageWithAI($base64Image, $userId, $mode);
        
        if ($processedImage) {
            echo json_encode([
                'success' => true,
                'processedImage' => $processedImage
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Image processing failed']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
}

function processImageWithAI($base64Image, $userId, $mode) {
    // This would integrate with your existing AI processing pipeline
    // For now, we'll return a placeholder
    
    try {
        // Use the existing main_undress function but modify it for web use
        // You would need to modify the main_undress function to return the processed image
        // instead of sending it via Telegram
        
        // For demo purposes, return the original image
        return $base64Image;
        
    } catch (Exception $e) {
        error_log("Image processing error: " . $e->getMessage());
        return false;
    }
}

// Route the request
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($path) {
    case '/api/process':
        processImageAPI();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
?>