<?php
class FileUploader {
    private static $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    private static $max_size = 10485760; // 10MB

    public static function uploadImage($file, $destination = null) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed with error code ' . $file['error']);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::$allowed_types)) {
            throw new Exception('Invalid file type. Allowed types: ' . implode(', ', self::$allowed_types));
        }

        if ($file['size'] > self::$max_size) {
            throw new Exception('File size exceeds limit of ' . (self::$max_size / 1048576) . 'MB');
        }

        // For Heroku, store in database as BLOB
        if (getenv('JAWSDB_MARIA_URL')) {
            return file_get_contents($file['tmp_name']);
        }

        // For local storage
        if ($destination) {
            $filename = uniqid() . '.' . $ext;
            $filepath = $destination . '/' . $filename;
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to move uploaded file');
            }
            return $filepath;
        }

        return file_get_contents($file['tmp_name']);
    }

    public static function getImageUrl($imageData, $default = '/assets/img/default.png') {
        if (empty($imageData)) {
            return $default;
        }

        // For Heroku, convert BLOB to base64
        if (getenv('JAWSDB_MARIA_URL')) {
            return 'data:image/jpeg;base64,' . base64_encode($imageData);
        }

        // For local storage
        return $imageData;
    }
}