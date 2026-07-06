<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

function sendJsonResponse(array $payload, int $statusCode = 200): void
{
	header('Content-Type: application/json; charset=utf-8');
	http_response_code($statusCode);
	echo json_encode($payload);
	exit;
}

function getStudentPickerData(int $page, int $limit, string $search): array
{
	global $conn;

	$page = max(1, $page);
	$limit = max(1, min(50, $limit));
	$offset = ($page - 1) * $limit;

	$searchSql = '';
	if ($search !== '') {
		$escaped = $conn->real_escape_string($search);
		$searchSql = " WHERE student.student_name LIKE '%{$escaped}%' OR COALESCE(student.nis, '') LIKE '%{$escaped}%' OR COALESCE(student.grade, '') LIKE '%{$escaped}%' OR COALESCE(levell.level_name, '') LIKE '%{$escaped}%'";
	}

	$countSql = 'SELECT COUNT(*) AS total FROM student LEFT JOIN levell ON student.`level` = levell.id_level' . $searchSql;
	$countResult = $conn->query($countSql);
	$total = 0;
	if ($countResult !== false) {
		$row = $countResult->fetch_assoc();
		$total = (int)($row['total'] ?? 0);
	}

	$sql = 'SELECT student.id, student.student_name, student.grade, student.`level`, levell.level_name FROM student LEFT JOIN levell ON student.`level` = levell.id_level' . $searchSql . ' ORDER BY student.id DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
	$result = $conn->query($sql);

	$rows = [];
	if ($result !== false) {
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
	}

	return [
		'rows' => $rows,
		'total' => $total,
		'page' => $page,
		'limit' => $limit,
		'totalPages' => $total > 0 ? (int)ceil($total / $limit) : 0,
		'search' => $search,
	];
}

function getCategoryOptions(): array
{
	global $conn;

	$sql = 'SELECT id, name FROM sick_category ORDER BY name ASC';
	$result = $conn->query($sql);

	$rows = [];
	if ($result !== false) {
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
	}

	return $rows;
}

function getLevelOptions(): array
{
	global $conn;

	$sql = 'SELECT id_level, level_name FROM levell ORDER BY level_name ASC';
	$result = $conn->query($sql);

	$rows = [];
	if ($result !== false) {
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
	}

	return $rows;
}

function getVisitors(array $filters = []): array
{
	global $conn;

	$search = trim((string)($filters['search'] ?? ''));
	$xendit = trim((string)($filters['xendit'] ?? ''));
	$division = trim((string)($filters['division'] ?? ''));
	$startDate = trim((string)($filters['start_date'] ?? ''));
	$endDate = trim((string)($filters['end_date'] ?? ''));

	$where = [];

	if ($search !== '') {
		$escapedSearch = $conn->real_escape_string($search);
		$where[] = "(visitor.name LIKE '%{$escapedSearch}%' OR COALESCE(visitor.nis, '') LIKE '%{$escapedSearch}%' OR COALESCE(visitor.xendit, '') LIKE '%{$escapedSearch}%')";
	}

	if ($xendit !== '') {
		$escapedXendit = $conn->real_escape_string($xendit);
		$where[] = "COALESCE(visitor.xendit, '') = '{$escapedXendit}'";
	}

	if ($division !== '') {
		$escapedDivision = $conn->real_escape_string($division);
		$where[] = "visitor.`level` = '{$escapedDivision}'";
	}

	if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $startDate) === 1) {
		$escapedStartDate = $conn->real_escape_string($startDate);
		$where[] = "visitor.`date` >= '{$escapedStartDate}'";
	}

	if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $endDate) === 1) {
		$escapedEndDate = $conn->real_escape_string($endDate);
		$where[] = "visitor.`date` <= '{$escapedEndDate}'";
	}

	$whereSql = count($where) > 0 ? (' WHERE ' . implode(' AND ', $where)) : '';

	$sql = 'SELECT visitor.*, sick_category.name AS category_name FROM visitor LEFT JOIN sick_category ON visitor.sick_category_id = sick_category.id' . $whereSql . ' ORDER BY visitor.id DESC';
	$result = $conn->query($sql);

	$rows = [];
	if ($result !== false) {
		while ($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}
	}

	return $rows;
}

function outputVisitorExcel(array $filters = []): void
{
	$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
	$sheet = $spreadsheet->getActiveSheet();
	$sheet->setTitle('Visitors');

	$rows = getVisitors($filters);
	$fileName = 'visitor_export_' . date('Ymd_His') . '.xlsx';

	$sheet->fromArray([
		'No',
		'Name',
		'Level',
		'Grade',
		'Category',
		'Date',
		'Time',
		'Accidental',
		'Status',
		'Result',
		'Item Used',
		'Note',
	], null, 'A1');

	$excelRows = [];
	foreach ($rows as $index => $row) {
		$statusCode = (int)($row['status'] ?? 0);
		$statusLabel = 'Waiting';
		if ($statusCode === 1) {
			$statusLabel = 'Treatment';
		} elseif ($statusCode === 2) {
			$statusLabel = 'Recovered';
		}

		$excelRows[] = [
			$index + 1,
			(string)($row['name'] ?? ''),
			(string)($row['level'] ?? ''),
			(string)($row['grade'] ?? ''),
			(string)($row['category_name'] ?? ''),
			(string)($row['date'] ?? ''),
			(string)($row['time'] ?? ''),
			(string)($row['accidental'] ?? ''),
			$statusLabel,
			(string)($row['result'] ?? ''),
			(string)($row['item_used'] ?? ''),
			(string)($row['note'] ?? ''),
		];
	}

	if (count($excelRows) > 0) {
		$sheet->fromArray($excelRows, null, 'A2');
	}

	$sheet->getStyle('A1:L1')->getFont()->setBold(true);
	$sheet->freezePane('A2');
	foreach (range('A', 'L') as $column) {
		$sheet->getColumnDimension($column)->setAutoSize(true);
	}

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment; filename="' . $fileName . '"');
	header('Cache-Control: max-age=0');
	header('Pragma: public');

	$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
	$writer->save('php://output');
	$spreadsheet->disconnectWorksheets();
	unset($spreadsheet);
	exit;
}

function getDashboardData(): array
{
	global $conn;

	$summary = [
		'todayVisitors' => 0,
		'todaySick' => 0,
		'waiting' => 0,
		'recovered' => 0,
		'treatment' => 0,
	];

	$summarySql = "SELECT
		COUNT(*) AS today_visitors,
		COUNT(DISTINCT CASE WHEN student_id IS NOT NULL AND student_id > 0 THEN student_id END) AS today_sick,
		SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS waiting_total,
		SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS treatment_total,
		SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) AS recovered_total
	FROM visitor
	WHERE `date` = CURDATE()";

	$summaryResult = $conn->query($summarySql);
	if ($summaryResult !== false) {
		$row = $summaryResult->fetch_assoc();
		$summary['todayVisitors'] = (int)($row['today_visitors'] ?? 0);
		$summary['todaySick'] = (int)($row['today_sick'] ?? 0);
		$summary['waiting'] = (int)($row['waiting_total'] ?? 0);
		$summary['treatment'] = (int)($row['treatment_total'] ?? 0);
		$summary['recovered'] = (int)($row['recovered_total'] ?? 0);
	}

	$trendMap = [];
	$trendSql = "SELECT
		DATE_FORMAT(`date`, '%Y-%m-%d') AS day_key,
		COUNT(*) AS visitors_total,
		COUNT(DISTINCT CASE WHEN student_id IS NOT NULL AND student_id > 0 THEN student_id END) AS sick_total
	FROM visitor
	WHERE `date` BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
	GROUP BY `date`
	ORDER BY `date` ASC";

	$trendResult = $conn->query($trendSql);
	if ($trendResult !== false) {
		while ($row = $trendResult->fetch_assoc()) {
			$key = (string)($row['day_key'] ?? '');
			if ($key !== '') {
				$trendMap[$key] = [
					'visitors' => (int)($row['visitors_total'] ?? 0),
					'sick' => (int)($row['sick_total'] ?? 0),
				];
			}
		}
	}

	$trend = [];
	$startTs = strtotime('-6 day');
	for ($i = 0; $i < 7; $i++) {
		$dayTs = strtotime('+' . $i . ' day', $startTs);
		$key = date('Y-m-d', $dayTs);
		$trend[] = [
			'date' => $key,
			'label' => date('D', $dayTs),
			'visitors' => (int)($trendMap[$key]['visitors'] ?? 0),
			'sick' => (int)($trendMap[$key]['sick'] ?? 0),
		];
	}

	$categories = [];
	$categorySql = "SELECT
		COALESCE(sick_category.name, 'Unknown') AS category_name,
		COUNT(*) AS total
	FROM visitor
	LEFT JOIN sick_category ON visitor.sick_category_id = sick_category.id
	WHERE visitor.`date` >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
	GROUP BY visitor.sick_category_id, sick_category.name
	ORDER BY total DESC
	LIMIT 5";

	$categoryResult = $conn->query($categorySql);
	if ($categoryResult !== false) {
		while ($row = $categoryResult->fetch_assoc()) {
			$categories[] = [
				'name' => (string)($row['category_name'] ?? 'Unknown'),
				'total' => (int)($row['total'] ?? 0),
			];
		}
	}

	$recent = [];
	$recentSql = "SELECT
		visitor.id,
		visitor.`date`,
		visitor.`time`,
		visitor.name,
		visitor.`level`,
		visitor.grade,
		visitor.status,
		COALESCE(sick_category.name, '-') AS category_name
	FROM visitor
	LEFT JOIN sick_category ON visitor.sick_category_id = sick_category.id
	ORDER BY visitor.id DESC
	LIMIT 5";

	$recentResult = $conn->query($recentSql);
	if ($recentResult !== false) {
		while ($row = $recentResult->fetch_assoc()) {
			$recent[] = $row;
		}
	}

	return [
		'summary' => $summary,
		'trend' => $trend,
		'categories' => $categories,
		'recent' => $recent,
		'generatedAt' => date('c'),
	];
}

function getVisitorById(int $id): ?array
{
	global $conn;

	$sql = 'SELECT * FROM visitor WHERE id = ? LIMIT 1';
	$stmt = $conn->prepare($sql);
	if ($stmt === false) {
		return null;
	}

	$stmt->bind_param('i', $id);
	if (!$stmt->execute()) {
		$stmt->close();
		return null;
	}

	$result = $stmt->get_result();
	$row = $result ? $result->fetch_assoc() : null;
	$stmt->close();

	return $row ?: null;
}

function deleteVisitorImageFile(?string $imagePath): void
{
	if ($imagePath === null) {
		return;
	}

	$imagePath = trim($imagePath);
	if ($imagePath === '') {
		return;
	}

	$fullPath = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $imagePath);
	$uploadBase = realpath(__DIR__ . '/../assets/uploads/visitors');

	if ($uploadBase === false || $fullPath === false) {
		return;
	}

	if (strpos($fullPath, $uploadBase) !== 0) {
		return;
	}

	if (is_file($fullPath)) {
@unlink($fullPath);
	}
}

function handleVisitorImageUpload(?array $file): array
{
	if ($file === null || !isset($file['error'])) {
		return ['success' => true, 'path' => null];
	}

	if ((int)$file['error'] === UPLOAD_ERR_NO_FILE) {
		return ['success' => true, 'path' => null];
	}

	if ((int)$file['error'] !== UPLOAD_ERR_OK) {
		return ['success' => false, 'message' => 'Failed to upload image.'];
	}

	$tmpName = (string)($file['tmp_name'] ?? '');
	$imageInfo = $tmpName !== '' ? @getimagesize($tmpName) : false;
	if ($imageInfo === false || !isset($imageInfo[2])) {
		return ['success' => false, 'message' => 'Uploaded file must be an image.'];
	}

	$extension = image_type_to_extension((int)$imageInfo[2], false);
	$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
	if (!in_array($extension, $allowedExtensions, true)) {
		return ['success' => false, 'message' => 'Unsupported image format.'];
	}

	$uploadDir = __DIR__ . '/../assets/uploads/visitors';
	if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
		return ['success' => false, 'message' => 'Failed to prepare upload directory.'];
	}

	try {
		$random = bin2hex(random_bytes(8));
	} catch (Throwable $e) {
		$random = uniqid('', true);
		$random = str_replace('.', '', $random);
	}

	$fileName = 'visitor_' . date('YmdHis') . '_' . $random . '.' . $extension;
	$destination = $uploadDir . '/' . $fileName;
	if (!move_uploaded_file($tmpName, $destination)) {
		return ['success' => false, 'message' => 'Failed to save uploaded image.'];
	}

	return ['success' => true, 'path' => 'assets/uploads/visitors/' . $fileName];
}

function getStudentIdentity(int $studentId): ?array
{
	global $conn;

	$sql = 'SELECT COALESCE(rfidid, "") AS xendit, COALESCE(nis, "") AS nis FROM student WHERE id = ? LIMIT 1';
	$stmt = $conn->prepare($sql);

	if ($stmt === false) {
		return null;
	}

	$stmt->bind_param('i', $studentId);
	if (!$stmt->execute()) {
		$stmt->close();
		return null;
	}

	$result = $stmt->get_result();
	$row = $result ? $result->fetch_assoc() : null;
	$stmt->close();

	if (!$row) {
		return null;
	}

	$xendit = trim((string)($row['xendit'] ?? ''));
	$nis = trim((string)($row['nis'] ?? ''));

	return [
		'xendit' => $xendit === '' ? null : $xendit,
		'nis' => $nis === '' ? null : $nis,
	];
}

function createVisitor(array $payload): bool
{
	global $conn;
	$gradeValue = $payload['grade'] ?? '';
	$imageValue = (string)($payload['image'] ?? '');

	$sql = 'INSERT INTO visitor (student_id, name, `level`, grade, xendit, nis, sick_category_id, `date`, `time`, intervention, accidental, status, result, item_used, note, image) VALUES (?, ?, ?, NULLIF(?, ""), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
	$stmt = $conn->prepare($sql);

	if ($stmt === false) {
		return false;
	}

	$stmt->bind_param(
		'isssssissssissss',
		$payload['student_id'],
		$payload['name'],
		$payload['level'],
		$gradeValue,
		$payload['xendit'],
		$payload['nis'],
		$payload['sick_category_id'],
		$payload['date'],
		$payload['time'],
		$payload['intervention'],
		$payload['accidental'],
		$payload['status'],
		$payload['result'],
		$payload['item_used'],
		$payload['note'],
		$imageValue,
	);

	$ok = $stmt->execute();
	$stmt->close();
	return $ok;
}

function updateVisitor(array $payload): bool
{
	global $conn;
	$gradeValue = $payload['grade'] ?? '';
	$imageValue = (string)($payload['image'] ?? '');

	$sql = 'UPDATE visitor SET student_id = ?, name = ?, `level` = ?, grade = NULLIF(?, ""), xendit = ?, nis = ?, sick_category_id = ?, `date` = ?, `time` = ?, intervention = ?, accidental = ?, status = ?, result = ?, item_used = ?, note = ?, image = ? WHERE id = ?';
	$stmt = $conn->prepare($sql);

	if ($stmt === false) {
		return false;
	}

	$stmt->bind_param(
		'isssssissssissssi',
		$payload['student_id'],
		$payload['name'],
		$payload['level'],
		$gradeValue,
		$payload['xendit'],
		$payload['nis'],
		$payload['sick_category_id'],
		$payload['date'],
		$payload['time'],
		$payload['intervention'],
		$payload['accidental'],
		$payload['status'],
		$payload['result'],
		$payload['item_used'],
		$payload['note'],
		$imageValue,
		$payload['id'],
	);

	$ok = $stmt->execute();
	$stmt->close();
	return $ok;
}

function deleteVisitor(int $id): bool
{
	global $conn;

	$sql = 'DELETE FROM visitor WHERE id = ?';
	$stmt = $conn->prepare($sql);

	if ($stmt === false) {
		return false;
	}

	$stmt->bind_param('i', $id);
	$ok = $stmt->execute();
	$stmt->close();
	return $ok;
}

function visitorPayloadFromRequest(): array
{
	$studentId = (int)($_POST['student_id'] ?? 0);
	$studentIdentity = $studentId > 0 ? getStudentIdentity($studentId) : null;
	$name = trim((string)($_POST['name'] ?? ''));
	$level = trim((string)($_POST['level'] ?? ''));
	$gradeRaw = trim((string)($_POST['grade'] ?? ''));
	$grade = $gradeRaw === '' ? null : $gradeRaw;
	$sickCategoryIdRaw = $_POST['sick_category_id'] ?? null;
	$sickCategoryId = ($sickCategoryIdRaw === '' || $sickCategoryIdRaw === null) ? null : (int)$sickCategoryIdRaw;
	$date = trim((string)($_POST['date'] ?? ''));
	$time = trim((string)($_POST['time'] ?? ''));
	$intervention = trim((string)($_POST['intervention'] ?? ''));
	$accidental = trim((string)($_POST['accidental'] ?? ''));
	$status = (int)($_POST['status'] ?? 0);
	$result = trim((string)($_POST['result'] ?? ''));
	$itemUsed = trim((string)($_POST['item_used'] ?? ''));
	$note = trim((string)($_POST['note'] ?? ''));

	return [
		'student_id' => $studentId,
		'name' => $name,
		'level' => $level,
		'grade' => $grade,
		'xendit' => $studentIdentity['xendit'] ?? '',
		'nis' => $studentIdentity['nis'] ?? '',
		'sick_category_id' => $sickCategoryId,
		'date' => $date,
		'time' => $time,
		'intervention' => $intervention === '' ? null : $intervention,
		'accidental' => $accidental === '' ? null : $accidental,
		'status' => $status,
		'result' => $result === '' ? null : $result,
		'item_used' => $itemUsed === '' ? null : $itemUsed,
		'note' => $note === '' ? null : $note,
		'image' => '',
	];
}

function validateVisitorPayload(array $payload): ?string
{
	if ($payload['student_id'] <= 0) {
		return 'Student is required.';
	}
	if ($payload['name'] === '' || $payload['level'] === '') {
		return 'Name and level are required.';
	}
	if (!array_key_exists('xendit', $payload) || !array_key_exists('nis', $payload)) {
		return 'Student identity mapping is invalid.';
	}
	if ($payload['sick_category_id'] === null || (int)$payload['sick_category_id'] <= 0) {
		return 'Sick category is required.';
	}
	if ($payload['date'] === '' || $payload['time'] === '') {
		return 'Date and time are required.';
	}
	return null;
}

function handleHealthdeskRequest(): void
{
	$action = $_GET['action'] ?? $_POST['action'] ?? 'visitors';

	if ($action === 'students') {
		$page = (int)($_GET['page'] ?? 1);
		$limit = (int)($_GET['limit'] ?? 10);
		$search = trim((string)($_GET['search'] ?? ''));
		$result = getStudentPickerData($page, $limit, $search);

		sendJsonResponse([
			'success' => true,
			'data' => $result['rows'],
			'total' => $result['total'],
			'page' => $result['page'],
			'limit' => $result['limit'],
			'totalPages' => $result['totalPages'],
			'search' => $result['search'],
		]);
	}

	if ($action === 'categories') {
		$rows = getCategoryOptions();
		sendJsonResponse([
			'success' => true,
			'data' => $rows,
			'count' => count($rows),
		]);
	}

	if ($action === 'levels') {
		$rows = getLevelOptions();
		sendJsonResponse([
			'success' => true,
			'data' => $rows,
			'count' => count($rows),
		]);
	}

	if ($action === 'visitors') {
		$rows = getVisitors([
			'search' => trim((string)($_GET['search'] ?? '')),
			'xendit' => trim((string)($_GET['xendit'] ?? '')),
			'division' => trim((string)($_GET['division'] ?? '')),
			'start_date' => trim((string)($_GET['start_date'] ?? '')),
			'end_date' => trim((string)($_GET['end_date'] ?? '')),
		]);
		sendJsonResponse([
			'success' => true,
			'data' => $rows,
			'count' => count($rows),
		]);
	}

	if ($action === 'visitor_export') {
		outputVisitorExcel([
			'search' => trim((string)($_GET['search'] ?? '')),
			'division' => trim((string)($_GET['division'] ?? '')),
			'start_date' => trim((string)($_GET['start_date'] ?? '')),
			'end_date' => trim((string)($_GET['end_date'] ?? '')),
		]);
	}

	if ($action === 'dashboard') {
		$data = getDashboardData();
		sendJsonResponse([
			'success' => true,
			'data' => $data,
		]);
	}

	if ($action === 'visitor_by_xendit') {
		$xendit = trim((string)($_GET['xendit'] ?? ''));
		if ($xendit === '') {
			sendJsonResponse(['success' => false, 'message' => 'xendit is required.'], 422);
		}

		$rows = getVisitors([
			'xendit' => $xendit,
			'start_date' => trim((string)($_GET['startdate'] ?? '')),
			'end_date' => trim((string)($_GET['enddate'] ?? '')),
		]);

		sendJsonResponse([
			'success' => true,
			'data' => $rows,
			'count' => count($rows),
			'xendit' => $xendit,
			'startdate' => trim((string)($_GET['startdate'] ?? '')),
			'enddate' => trim((string)($_GET['enddate'] ?? '')),
		]);
	}

	if ($action === 'visitor_create') {
		$payload = visitorPayloadFromRequest();
		$imageUpload = handleVisitorImageUpload($_FILES['image'] ?? null);
		if (!$imageUpload['success']) {
			sendJsonResponse(['success' => false, 'message' => $imageUpload['message'] ?? 'Failed to upload image.'], 422);
		}
		$payload['image'] = (string)($imageUpload['path'] ?? '');
		$error = validateVisitorPayload($payload);
		if ($error !== null) {
			deleteVisitorImageFile($payload['image']);
			sendJsonResponse(['success' => false, 'message' => $error], 422);
		}

		if (!createVisitor($payload)) {
			deleteVisitorImageFile($payload['image']);
			sendJsonResponse(['success' => false, 'message' => 'Failed to create visitor.'], 500);
		}

		sendJsonResponse(['success' => true, 'message' => 'Visitor created successfully.']);
	}

	if ($action === 'visitor_update') {
		$id = (int)($_POST['id'] ?? 0);
		$payload = visitorPayloadFromRequest();
		$payload['id'] = $id;
		$existingVisitor = $id > 0 ? getVisitorById($id) : null;
		$imageUpload = handleVisitorImageUpload($_FILES['image'] ?? null);
		if (!$imageUpload['success']) {
			sendJsonResponse(['success' => false, 'message' => $imageUpload['message'] ?? 'Failed to upload image.'], 422);
		}
		$payload['image'] = (string)($imageUpload['path'] ?? ($existingVisitor['image'] ?? ''));
		$error = validateVisitorPayload($payload);
		if ($id <= 0) {
			deleteVisitorImageFile($imageUpload['path'] ?? null);
			sendJsonResponse(['success' => false, 'message' => 'Invalid visitor ID.'], 422);
		}
		if ($error !== null) {
			deleteVisitorImageFile($imageUpload['path'] ?? null);
			sendJsonResponse(['success' => false, 'message' => $error], 422);
		}

		if (!updateVisitor($payload)) {
			deleteVisitorImageFile($imageUpload['path'] ?? null);
			sendJsonResponse(['success' => false, 'message' => 'Failed to update visitor.'], 500);
		}

		if (!empty($imageUpload['path']) && !empty($existingVisitor['image']) && $existingVisitor['image'] !== $imageUpload['path']) {
			deleteVisitorImageFile((string)$existingVisitor['image']);
		}

		sendJsonResponse(['success' => true, 'message' => 'Visitor updated successfully.']);
	}

	if ($action === 'visitor_delete') {
		$id = (int)($_POST['id'] ?? 0);
		if ($id <= 0) {
			sendJsonResponse(['success' => false, 'message' => 'Invalid visitor ID.'], 422);
		}

		if (!deleteVisitor($id)) {
			sendJsonResponse(['success' => false, 'message' => 'Failed to delete visitor.'], 500);
		}

		sendJsonResponse(['success' => true, 'message' => 'Visitor deleted successfully.']);
	}

	sendJsonResponse(['success' => false, 'message' => 'Invalid action.'], 400);
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
	handleHealthdeskRequest();
}