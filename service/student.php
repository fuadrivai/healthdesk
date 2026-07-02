<?php

require_once __DIR__ . '/../config.php';

function getStudentData(): array
{
	global $conn;

	$sql = 'SELECT student.*, levell.level_name AS level_name FROM student LEFT JOIN levell ON student.`level` = levell.id_level';
	$result = $conn->query($sql);

	if ($result === false) {
		return [];
	}

	$students = [];
	while ($row = $result->fetch_assoc()) {
		$students[] = $row;
	}

	return $students;
}

function getStudentPaginatedData(int $page = 1, int $limit = 10): array
{
	global $conn;

	$page = max(1, $page);
	$limit = max(1, min(100, $limit));
	$offset = ($page - 1) * $limit;
	$search = trim((string)($_GET['search'] ?? $_POST['search'] ?? ''));
	$hasSearch = $search !== '';
	$searchSql = '';

	if ($hasSearch) {
		$searchLike = $conn->real_escape_string($search);
		$searchSql = " WHERE CAST(student.id AS CHAR) LIKE '%{$searchLike}%' OR COALESCE(student.rfidid, '') LIKE '%{$searchLike}%' OR COALESCE(student.nis, '') LIKE '%{$searchLike}%' OR student.student_name LIKE '%{$searchLike}%' OR COALESCE(student.grade, '') LIKE '%{$searchLike}%' OR CAST(student.level_id AS CHAR) LIKE '%{$searchLike}%' OR COALESCE(student.phone, '') LIKE '%{$searchLike}%' OR COALESCE(levell.level_name, '') LIKE '%{$searchLike}%'";
	}

	$totalSql = 'SELECT COUNT(*) AS total FROM student LEFT JOIN levell ON student.`level` = levell.id_level' . $searchSql;
	$totalResult = $conn->query($totalSql);
	$total = 0;
	if ($totalResult !== false) {
		$totalRow = $totalResult->fetch_assoc();
		$total = (int)($totalRow['total'] ?? 0);
	}

	$dataSql = 'SELECT student.*, levell.level_name AS level_name FROM student LEFT JOIN levell ON student.`level` = levell.id_level' . $searchSql . ' ORDER BY student.id DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
	$dataResult = $conn->query($dataSql);
	$students = [];

	if ($dataResult !== false) {
		while ($row = $dataResult->fetch_assoc()) {
			$students[] = $row;
		}
	}

	return [
		'students' => $students,
		'total' => $total,
		'page' => $page,
		'limit' => $limit,
		'totalPages' => $total > 0 ? (int)ceil($total / $limit) : 0,
		'search' => $search,
	];
}

function sendJsonResponse(array $payload, int $statusCode = 200): void
{
	header('Content-Type: application/json; charset=utf-8');
	http_response_code($statusCode);
	echo json_encode($payload);
	exit;
}

function handleStudentRequest(): void
{
	$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
	$page = (int)($_GET['page'] ?? $_POST['page'] ?? 1);
	$limit = (int)($_GET['limit'] ?? $_POST['limit'] ?? 10);

	if ($action !== 'list') {
		sendJsonResponse([
			'success' => false,
			'message' => 'Invalid action.',
		], 400);
	}

	$result = getStudentPaginatedData($page, $limit);

	sendJsonResponse([
		'success' => true,
		'message' => 'Student data loaded successfully.',
		'count' => count($result['students']),
		'data' => $result['students'],
		'total' => $result['total'],
		'page' => $result['page'],
		'limit' => $result['limit'],
		'totalPages' => $result['totalPages'],
		'search' => $result['search'],
	]);
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
	handleStudentRequest();
}