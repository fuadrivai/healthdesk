<?php

require_once __DIR__ . '/../config.php';

function getDivisionData(): array
{
	global $conn;

	$sql = 'SELECT id_level, level_name FROM levell ORDER BY id_level ASC';
	$result = $conn->query($sql);

	if ($result === false) {
		return [];
	}

	$divisions = [];
	while ($row = $result->fetch_assoc()) {
		$divisions[] = $row;
	}

	return $divisions;
}

function sendJsonResponse(array $payload, int $statusCode = 200): void
{
	header('Content-Type: application/json; charset=utf-8');
	http_response_code($statusCode);
	echo json_encode($payload);
	exit;
}

function handleDivisionRequest(): void
{
	$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

	if ($action !== 'list') {
		sendJsonResponse([
			'success' => false,
			'message' => 'Invalid action.',
		], 400);
	}

	$divisions = getDivisionData();

	sendJsonResponse([
		'success' => true,
		'message' => 'Division data loaded successfully.',
		'count' => count($divisions),
		'data' => $divisions,
	]);
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
	handleDivisionRequest();
}