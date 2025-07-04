<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include the necessary files from parent directory
require_once '../../login.php';
require_once '../../languages.php';

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
        if (!$db) {
            throw new Exception('Database connection failed');
        }
        
        $query = "INSERT INTO `users` (`id`, `mode`, `count`, `date`, `language`) VALUES ('$userId', '$mode', '1', CURRENT_TIMESTAMP, 'en') ON DUPLICATE KEY UPDATE mode='$mode';";
        $db->query($query);
        
        // Process the image using the existing AI processing pipeline
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
        error_log("API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
    }
}

function processImageWithAI($base64Image, $userId, $mode) {
    try {
        // For demo purposes, we'll simulate the AI processing
        // In a real implementation, this would call your existing main_undress function
        
        // Simulate processing delay
        usleep(500000); // 0.5 second delay
        
        // For now, return a modified version of the original image
        // You would integrate this with your existing AI processing pipeline
        return $base64Image;
        
    } catch (Exception $e) {
        error_log("Image processing error: " . $e->getMessage());
        return false;
    }
}

// Execute the API
processImageAPI();
?>