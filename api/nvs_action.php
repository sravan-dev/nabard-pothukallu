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
        $targetDir = "../uploads/nvs/$subDir/";
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
            $sql = "SELECT m.*, h.settlement_name,
                    (SELECT COUNT(*) FROM nvs_participants p WHERE p.meeting_id = m.id) as real_participants_count,
                    (SELECT file_path FROM nvs_meeting_files f WHERE f.meeting_id = m.id AND f.file_type='photo' LIMIT 1) as cover_photo
                    FROM nvs_meetings m 
                    JOIN hamlets h ON m.hamlet_id = h.id
                    ORDER BY m.meeting_date DESC";
            $stmt = $pdo->query($sql);
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_families_by_hamlet':
        try {
            $hamlet_id = $_POST['hamlet_id'];
            $stmt = $pdo->prepare("SELECT id, net_plan_number, beneficiary_name FROM families WHERE hamlet_id = ? ORDER BY net_plan_number ASC");
            $stmt->execute([$hamlet_id]);
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_single':
        try {
            $id = $_POST['id'];
            
            // Meeting Details
            $stmt = $pdo->prepare("SELECT * FROM nvs_meetings WHERE id = ?");
            $stmt->execute([$id]);
            $meeting = $stmt->fetch();

            // Participants (with details)
            $stmt = $pdo->prepare("
                SELECT p.family_id, f.net_plan_number, f.beneficiary_name 
                FROM nvs_participants p 
                JOIN families f ON p.family_id = f.id 
                WHERE p.meeting_id = ?
            ");
            $stmt->execute([$id]);
            $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Files
            $stmt = $pdo->prepare("SELECT * FROM nvs_meeting_files WHERE meeting_id = ?");
            $stmt->execute([$id]);
            $files = $stmt->fetchAll();

            echo json_encode(['status' => 'success', 'data' => $meeting, 'participants' => $participants, 'files' => $files]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
    case 'update':
        try {
            $pdo->beginTransaction();

            $hamlet_id = $_POST['hamlet_id'];
            $meeting_date = $_POST['meeting_date'];
            $major_decisions = sanitize($_POST['major_decisions']);
            
            $participant_ids = isset($_POST['participant_ids']) ? explode(',', $_POST['participant_ids']) : [];
            $participants_count = count($participant_ids);

            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO nvs_meetings (hamlet_id, meeting_date, participants_count, major_decisions) VALUES (?, ?, ?, ?)");
                $stmt->execute([$hamlet_id, $meeting_date, $participants_count, $major_decisions]);
                $meeting_id = $pdo->lastInsertId();
            } else {
                $meeting_id = $_POST['id'];
                $stmt = $pdo->prepare("UPDATE nvs_meetings SET hamlet_id=?, meeting_date=?, participants_count=?, major_decisions=? WHERE id=?");
                $stmt->execute([$hamlet_id, $meeting_date, $participants_count, $major_decisions, $meeting_id]);
                
                // Clear old participants
                $pdo->prepare("DELETE FROM nvs_participants WHERE meeting_id = ?")->execute([$meeting_id]);
            }

            // Insert Participants
            if (!empty($participant_ids)) {
                $stmt = $pdo->prepare("INSERT INTO nvs_participants (meeting_id, family_id) VALUES (?, ?)");
                foreach ($participant_ids as $fid) {
                    if(trim($fid)) $stmt->execute([$meeting_id, trim($fid)]);
                }
            }

            // Handle Photo Upload
            $newPhoto = handleUpload('photo', 'photos');
            if ($newPhoto) {
                 $stmt = $pdo->prepare("INSERT INTO nvs_meeting_files (meeting_id, file_type, file_path) VALUES (?, 'photo', ?)");
                 $stmt->execute([$meeting_id, $newPhoto]);
            }

            // Handle Minutes Upload
            $newMinutes = handleUpload('minutes', 'minutes');
            if ($newMinutes) {
                $stmt = $pdo->prepare("INSERT INTO nvs_meeting_files (meeting_id, file_type, file_path) VALUES (?, 'minutes', ?)");
                $stmt->execute([$meeting_id, $newMinutes]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Meeting saved successfully']);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = $_POST['id'];
            // Files deletion logic
            $stmt = $pdo->prepare("SELECT file_path, file_type FROM nvs_meeting_files WHERE meeting_id = ?");
            $stmt->execute([$id]);
            $files = $stmt->fetchAll();
            foreach ($files as $f) {
                $subDir = ($f['file_type'] == 'photo') ? 'photos' : 'minutes';
                $path = "../uploads/nvs/$subDir/" . $f['file_path'];
                if (file_exists($path)) @unlink($path);
            }

            $stmt = $pdo->prepare("DELETE FROM nvs_meetings WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Meeting deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;
        
    case 'delete_file':
        try {
            $file_id = $_POST['file_id'];
            $stmt = $pdo->prepare("SELECT * FROM nvs_meeting_files WHERE id = ?");
            $stmt->execute([$file_id]);
            $file = $stmt->fetch();
            
            if ($file) {
                 $subDir = ($file['file_type'] == 'photo') ? 'photos' : 'minutes';
                 $path = "../uploads/nvs/$subDir/" . $file['file_path'];
                 if (file_exists($path)) @unlink($path);
                 
                 $pdo->prepare("DELETE FROM nvs_meeting_files WHERE id = ?")->execute([$file_id]);
                 echo json_encode(['status' => 'success', 'message' => 'File deleted']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);
        break;
}
?>
