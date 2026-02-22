<?php
require_once 'includes/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT f.*, h.settlement_name 
            FROM families f 
            JOIN hamlets h ON f.hamlet_id = h.id 
            WHERE f.id = ?
        ");
        $stmt->execute([$id]);
        $family = $stmt->fetch();
    } catch (PDOException $e) {
        die("Error fetching details");
    }
}

if (!$family) {
    die("Family details not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Details - #<?php echo htmlspecialchars($family['net_plan_number']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Outfit', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-300 min-h-screen p-6 flex justify-center">

    <div class="max-w-md w-full bg-slate-800 rounded-2xl shadow-2xl border border-slate-700 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-center">
            <h1 class="text-3xl font-bold text-white mb-1">NABARD</h1>
            <p class="text-blue-100 text-sm tracking-widest uppercase">Project at a Glance</p>
        </div>

        <div class="p-6 space-y-6">
            
            <!-- Main Info -->
            <div class="text-center">
                <div class="inline-block px-4 py-1.5 bg-blue-500/10 text-blue-400 rounded-full text-sm font-mono font-bold mb-4 border border-blue-500/20">
                    Net Plan #<?php echo htmlspecialchars($family['net_plan_number']); ?>
                </div>
                <h2 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($family['beneficiary_name']); ?></h2>
                <p class="text-slate-400"><?php echo htmlspecialchars($family['settlement_name']); ?> (Settlement)</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-slate-700/50 p-4 rounded-xl text-center border border-slate-600">
                    <div class="text-slate-400 text-xs uppercase tracking-wider mb-1">Age</div>
                    <div class="text-2xl font-bold text-white"><?php echo htmlspecialchars($family['age']); ?></div>
                </div>
                <div class="bg-slate-700/50 p-4 rounded-xl text-center border border-slate-600">
                    <div class="text-slate-400 text-xs uppercase tracking-wider mb-1">Members</div>
                    <div class="text-2xl font-bold text-white"><?php echo htmlspecialchars($family['total_members']); ?></div>
                </div>
            </div>

            <!-- Photos -->
            <div>
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 border-b border-slate-700 pb-2">Timeline Photos</h3>
                <div class="space-y-4">
                    <?php
                    $photoFields = [
                        'photo_initial' => 'Initial Stage',
                        'photo_year1' => 'Year 1',
                        'photo_year2' => 'Year 2',
                        'photo_year3' => 'Year 3',
                        'photo_year4' => 'Year 4',
                        'photo_year5' => 'Year 5',
                    ];

                    foreach ($photoFields as $key => $label) {
                        if (!empty($family[$key])) {
                            echo '
                            <div class="relative group rounded-xl overflow-hidden border border-slate-600">
                                <img src="uploads/families/'.htmlspecialchars($family[$key]).'" class="w-full h-48 object-cover">
                                <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                                    <span class="text-white font-medium text-sm">'.$label.'</span>
                                </div>
                            </div>';
                        }
                    }

                    // If no photos check
                    $hasAnyPhoto = false;
                    foreach ($photoFields as $key => $label) { if (!empty($family[$key])) $hasAnyPhoto = true; }
                    if (!$hasAnyPhoto) {
                        echo '<div class="text-center text-slate-500 text-sm py-4 italic">No photos uploaded yet.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="bg-slate-900/50 p-4 text-center border-t border-slate-700">
             <p class="text-xs text-slate-500">&copy; <?php echo date('Y'); ?> NABARD Project</p>
        </div>
    </div>

</body>
</html>
