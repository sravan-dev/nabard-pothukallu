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
        $targetDir = "../uploads/ptdc/$subDir/";
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
            $sql = "SELECT m.*, 
                    (SELECT COUNT(*) FROM ptdc_participants p WHERE p.meeting_id = m.id) as real_participants_count,
                    (SELECT file_path FROM ptdc_meeting_files f WHERE f.meeting_id = m.id AND f.file_type='photo' LIMIT 1) as cover_photo
                    FROM ptdc_meetings m 
                    ORDER BY m.meeting_date DESC";
            $stmt = $pdo->query($sql);
            $data = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'fetch_families':
        try {
            $stmt = $pdo->query("SELECT id, net_plan_number, beneficiary_name FROM families ORDER BY net_plan_number ASC");
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
            $stmt = $pdo->prepare("SELECT * FROM ptdc_meetings WHERE id = ?");
            $stmt->execute([$id]);
            $meeting = $stmt->fetch();

            // Participants
            $stmt = $pdo->prepare("
                SELECT p.family_id, f.net_plan_number, f.beneficiary_name 
                FROM ptdc_participants p 
                JOIN families f ON p.family_id = f.id 
                WHERE p.meeting_id = ?
            ");
            $stmt->execute([$id]);
            $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Files
            $stmt = $pdo->prepare("SELECT * FROM ptdc_meeting_files WHERE meeting_id = ?");
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

            $venue = sanitize($_POST['venue']);
            $meeting_date = $_POST['meeting_date'];
            $guests = sanitize($_POST['guests']);
            $major_decisions = sanitize($_POST['major_decisions']);
            // Participants is an array of family IDs, comma separated string from POST
            $participant_ids = isset($_POST['participant_ids']) ? explode(',', $_POST['participant_ids']) : [];
            $participants_count = count($participant_ids);

            if ($action === 'create') {
                $stmt = $pdo->prepare("INSERT INTO ptdc_meetings (venue, meeting_date, participants_count, guests, major_decisions) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$venue, $meeting_date, $participants_count, $guests, $major_decisions]);
                $meeting_id = $pdo->lastInsertId();
            } else {
                $meeting_id = $_POST['id'];
                $stmt = $pdo->prepare("UPDATE ptdc_meetings SET venue=?, meeting_date=?, participants_count=?, guests=?, major_decisions=? WHERE id=?");
                $stmt->execute([$venue, $meeting_date, $participants_count, $guests, $major_decisions, $meeting_id]);
                
                // Clear old participants to re-add
                $pdo->prepare("DELETE FROM ptdc_participants WHERE meeting_id = ?")->execute([$meeting_id]);
            }

            // Insert Participants
            if (!empty($participant_ids)) {
                $stmt = $pdo->prepare("INSERT INTO ptdc_participants (meeting_id, family_id) VALUES (?, ?)");
                foreach ($participant_ids as $fid) {
                    if(trim($fid)) $stmt->execute([$meeting_id, trim($fid)]);
                }
            }

            // Handle Photo Upload
            $newPhoto = handleUpload('photo', 'photos');
            if ($newPhoto) {
                // If update, maybe delete old photo? For now, we allow multiple photos, so just add.
                 $stmt = $pdo->prepare("INSERT INTO ptdc_meeting_files (meeting_id, file_type, file_path) VALUES (?, 'photo', ?)");
                 $stmt->execute([$meeting_id, $newPhoto]);
            }

            // Handle Minutes Upload
            $newMinutes = handleUpload('minutes', 'minutes');
            if ($newMinutes) {
                // Determine if we should replace existing minutes? The user req implied 'Minutes upload option', possibly singular.
                // Let's check if minutes exist and replace, or just add. Let's add for history, but maybe UI only shows latest.
                // Actually, let's replace for simplicity if logic dictates one minutes file. 
                // But structure supports multiple. Let's just append.
                $stmt = $pdo->prepare("INSERT INTO ptdc_meeting_files (meeting_id, file_type, file_path) VALUES (?, 'minutes', ?)");
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
            // Files deletion
            $stmt = $pdo->prepare("SELECT file_path, file_type FROM ptdc_meeting_files WHERE meeting_id = ?");
            $stmt->execute([$id]);
            $files = $stmt->fetchAll();
            foreach ($files as $f) {
                $subDir = ($f['file_type'] == 'photo') ? 'photos' : 'minutes';
                $path = "../uploads/ptdc/$subDir/" . $f['file_path'];
                if (file_exists($path)) @unlink($path);
            }

            $stmt = $pdo->prepare("DELETE FROM ptdc_meetings WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Meeting deleted successfully']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;
        
    case 'delete_file':
        try {
            $file_id = $_POST['file_id'];
            $stmt = $pdo->prepare("SELECT * FROM ptdc_meeting_files WHERE id = ?");
            $stmt->execute([$file_id]);
            $file = $stmt->fetch();
            
            if ($file) {
                 $subDir = ($file['file_type'] == 'photo') ? 'photos' : 'minutes';
                 $path = "../uploads/ptdc/$subDir/" . $file['file_path'];
                 if (file_exists($path)) @unlink($path);
                 
                 $pdo->prepare("DELETE FROM ptdc_meeting_files WHERE id = ?")->execute([$file_id]);
                 echo json_encode(['status' => 'success', 'message' => 'File deleted']);
            } else {
                 throw new Exception("File not found");
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
