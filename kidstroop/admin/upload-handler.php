<?php
// upload-handler.php

require_once __DIR__ . '/../../includes/config.php';

class ActivityUploadHandler {
    private $pdo;
    private $uploadDir;
    private $allowedTypes = [
        'story' => ['image/jpeg', 'image/png'],
        'art' => ['image/jpeg', 'image/png'],
        'math' => ['text/html']
    ];
    
    public function __construct() {
        $this->pdo = getDBConnection();
        $this->uploadDir = __DIR__ . '/../../uploads/';
        $this->initializeDirectories();
    }
    
    private function initializeDirectories() {
        // Create category directories if they don't exist
        foreach (array_keys($this->allowedTypes) as $category) {
            $dir = $this->uploadDir . $category;
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    public function handleUpload($files, $data) {
        try {
            if (!isset($files['activity_file']) || !isset($data['category'])) {
                throw new Exception('Missing required fields');
            }

            $file = $files['activity_file'];
            $category = $data['category'];
            
            // Validate category
            if (!array_key_exists($category, $this->allowedTypes)) {
                throw new Exception('Invalid category');
            }
            
            // Validate file type
            $fileType = $file['type'];
            if (!in_array($fileType, $this->allowedTypes[$category])) {
                throw new Exception("Invalid file type for {$category}. Allowed types: " . 
                    implode(', ', $this->allowedTypes[$category]));
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . date('Ymd') . '.' . $extension;
            $categoryDir = $this->uploadDir . $category . '/';
            $filepath = $categoryDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to upload file');
            }
            
            // Get category ID
            $stmt = $this->pdo->prepare("SELECT id FROM activity_categories WHERE slug = ?");
            $stmt->execute([$category]);
            $categoryId = $stmt->fetchColumn();
            
            // Save to database
            $stmt = $this->pdo->prepare("
                INSERT INTO activities (
                    category_id, 
                    title, 
                    description, 
                    file_path, 
                    file_type, 
                    file_size, 
                    created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $relativePath = 'uploads/' . $category . '/' . $filename;
            $stmt->execute([
                $categoryId,
                $data['title'],
                $data['description'] ?? '',
                $relativePath,
                $fileType,
                $file['size'],
                $_SESSION['admin_id'] ?? null
            ]);
            
            return [
                'success' => true,
                'message' => 'Activity uploaded successfully',
                'activity_id' => $this->pdo->lastInsertId(),
                'file_path' => $relativePath
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}

// Handle upload request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $handler = new ActivityUploadHandler();
    $result = $handler->handleUpload($_FILES, $_POST);
    
    echo json_encode($result);
}
?>