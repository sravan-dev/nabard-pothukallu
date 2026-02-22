<?php
ob_start();
require_once __DIR__ . '/includes/config.php';

check_auth();

$modules = [
    'Dashboard',
    'Project At Glance - Hamlets',
    'Project At Glance - Settlement/Hamlet',
    'Project At Glance - Family Details',
    'PTDC',
    'PTDC Meetings',
    'NVS',
    'NVS Meetings',
    'Programmes / Activities',
    'Success Stories',
    'Staff Entry',
    'Target Entry',
    'Project Components',
    'Individual Distribution Details',
    'JLG/Group Details',
    'Group/JLG Distribution Details',
    'General Activity Meetings',
    'Social Auditing',
    'Users',
    'Settings'
];

function pdf_escape_text($text) {
    $text = str_replace('\\', '\\\\', $text);
    $text = str_replace('(', '\\(', $text);
    $text = str_replace(')', '\\)', $text);
    $text = str_replace(array("\r", "\n"), '', $text);
    return $text;
}

function build_simple_pdf($contentStream) {
    $objects = [];
    $objects[] = "<< /Type /Catalog /Pages 2 0 R >>";
    $objects[] = "<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
    $objects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>";
    $objects[] = "<< /Length " . strlen($contentStream) . " >>\nstream\n" . $contentStream . "\nendstream";
    $objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    $count = count($objects);

    for ($i = 1; $i <= $count; $i++) {
        $offsets[$i] = strlen($pdf);
        $pdf .= $i . " 0 obj\n" . $objects[$i - 1] . "\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n";
    $pdf .= "0 " . ($count + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= $count; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer\n";
    $pdf .= "<< /Size " . ($count + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n";
    $pdf .= $xrefOffset . "\n";
    $pdf .= "%%EOF";

    return $pdf;
}

$title = SITE_TITLE . ' ' . SITE_SUP . ' - Modules Done Record';
$generatedAt = date('d M Y, h:i A');

$streamLines = [];
$streamLines[] = "BT";
$streamLines[] = "/F1 16 Tf";
$streamLines[] = "50 800 Td";
$streamLines[] = "(" . pdf_escape_text($title) . ") Tj";
$streamLines[] = "0 -24 Td";
$streamLines[] = "/F1 10 Tf";
$streamLines[] = "(" . pdf_escape_text('Generated: ' . $generatedAt) . ") Tj";
$streamLines[] = "0 -18 Td";
$streamLines[] = "(" . pdf_escape_text('Total Modules: ' . count($modules)) . ") Tj";
$streamLines[] = "0 -28 Td";
$streamLines[] = "/F1 12 Tf";

foreach ($modules as $index => $module) {
    $streamLines[] = "(" . pdf_escape_text(sprintf('%02d. %s', $index + 1, $module)) . ") Tj";
    if ($index !== count($modules) - 1) {
        $streamLines[] = "0 -16 Td";
    }
}

$streamLines[] = "ET";
$contentStream = implode("\n", $streamLines);
$pdf = build_simple_pdf($contentStream);

if (ob_get_length()) {
    ob_clean();
}

$fileName = 'modules_done_record_' . date('Ymd_His') . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($pdf));

echo $pdf;
exit;
?>
