<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
    exit;
}
validate_csrf();

$action = isset($_POST['action']) ? $_POST['action'] : '';

function handleUpload($fileInputName, $subDir) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../uploads/success_stories/$subDir/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES[$fileInputName]['name']);
        $targetPath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetPath)) {
            return $fileName;
        }
    }
    return null;
}

switch ($action) {
    case 'fetch_all':
        try {
            $stmt = $pdo->query("SELECT *, 
                (SELECT COUNT(*) FROM success_videos v WHERE v.story_id = s.id) as video_count 
                FROM success_stories s 
                ORDER BY story_date DESC");
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_single':
        try {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT * FROM success_stories WHERE id = ?");
            $stmt->execute([$id]);
            $story = $stmt->fetch();

            $stmt = $pdo->prepare("SELECT * FROM success_videos WHERE story_id = ?");
            $stmt->execute([$id]);
            $videos = $stmt->fetchAll();

            echo json_encode(['status' => 'success', 'data' => $story, 'videos' => $videos]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
    case 'update':
        try {
            $pdo->beginTransaction();
            
            $story_date = $_POST['story_date'];
            $description = sanitize($_POST['description']);
            
            // Photo Upload
            $photo = null;
            if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                 $photo = handleUpload('photo', 'photos');
                 if ($_POST['old_photo'] && file_exists("../uploads/success_stories/photos/" . $_POST['old_photo'])) {
                    @unlink("../uploads/success_stories/photos/" . $_POST['old_photo']);
                }
            } else {
                $photo = $_POST['old_photo'] ?? null;
            }

            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO success_stories (story_date, description, photo) VALUES (?, ?, ?)");
                $stmt->execute([$story_date, $description, $photo]);
                $story_id = $pdo->lastInsertId();
            } else {
                $story_id = $_POST['id'];
                $stmt = $pdo->prepare("UPDATE success_stories SET story_date=?, description=?, photo=? WHERE id=?");
                $stmt->execute([$story_date, $description, $photo, $story_id]);
            }

            // Handle New Video Uploads
            // Expecting video_files[] and video_remarks[]
            if (isset($_FILES['video_files']) && !empty($_FILES['video_files']['name'][0])) {
                $files = $_FILES['video_files'];
                $remarks = $_POST['video_remarks'] ?? [];
                
                $stmt = $pdo->prepare("INSERT INTO success_videos (story_id, video_path, remarks) VALUES (?, ?, ?)");
                
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $targetDir = "../uploads/success_stories/videos/";
                        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
                        
                        $fileName = time() . '_' . $i . '_' . basename($files['name'][$i]);
                        $targetPath = $targetDir . $fileName;
                        
                        if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                            $remark = isset($remarks[$i]) ? sanitize($remarks[$i]) : '';
                            $stmt->execute([$story_id, $fileName, $remark]);
                        }
                    }
                }
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Success Story saved successfully']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = $_POST['id'];
            
            // Delete Photo
            $stmt = $pdo->prepare("SELECT photo FROM success_stories WHERE id = ?");
            $stmt->execute([$id]);
            $photo = $stmt->fetchColumn();
            if ($photo && file_exists("../uploads/success_stories/photos/" . $photo)) {
                @unlink("../uploads/success_stories/photos/" . $photo);
            }
            
            // Delete Videos
            $stmt = $pdo->prepare("SELECT video_path FROM success_videos WHERE story_id = ?");
            $stmt->execute([$id]);
            $videos = $stmt->fetchAll();
            foreach ($videos as $v) {
                if (file_exists("../uploads/success_stories/videos/" . $v['video_path'])) {
                    @unlink("../uploads/success_stories/videos/" . $v['video_path']);
                }
            }

            // DB Delete
            $stmt = $pdo->prepare("DELETE FROM success_stories WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Story deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_video':
        try {
            $video_id = $_POST['video_id'];
            $stmt = $pdo->prepare("SELECT video_path FROM success_videos WHERE id = ?");
            $stmt->execute([$video_id]);
            $path = $stmt->fetchColumn();
            
            if ($path && file_exists("../uploads/success_stories/videos/" . $path)) {
                @unlink("../uploads/success_stories/videos/" . $path);
            }
            
            $pdo->prepare("DELETE FROM success_videos WHERE id = ?")->execute([$video_id]);
            echo json_encode(['status' => 'success', 'message' => 'Video deleted']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}
?>
