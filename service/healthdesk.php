<?php

require_once __DIR__ . '/../config.php';

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