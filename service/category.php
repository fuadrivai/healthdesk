<?php

require_once __DIR__ . '/../config.php';

function sendJsonResponse(array $payload, int $statusCode = 200): void
{
	header('Content-Type: application/json; charset=utf-8');
	http_response_code($statusCode);
	echo json_encode($payload);
	exit;
}

function getCategories(): array
{
	global $conn;

	$sql = 'SELECT id, name, severity, color_badge FROM sick_category ORDER BY id DESC';
	$result = $conn->query($sql);

	if ($result === false) {
		return [];
	}

	$rows = [];
	while ($row = $result->fetch_assoc()) {
		$rows[] = $row;
	}

	return $rows;
}

function createCategory(string $name, string $severity, ?string $colorBadge): bool
{
    global $conn;

    $sql = "INSERT INTO sick_category (name, severity, color_badge)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Prepare statement failed.',
            'mysql_errno' => $conn->errno,
            'mysql_error' => $conn->error,
            'sql' => $sql,
        ], 500);
    }

    if (!$stmt->bind_param("sss", $name, $severity, $colorBadge)) {
        $stmt->close();

        sendJsonResponse([
            'success' => false,
            'message' => 'Bind parameter failed.',
            'mysql_errno' => $stmt->errno,
            'mysql_error' => $stmt->error,
        ], 500);
    }

    if (!$stmt->execute()) {
        $error = [
            'success' => false,
            'message' => 'Execute failed.',
            'mysql_errno' => $stmt->errno,
            'mysql_error' => $stmt->error,
            'data' => [
                'name' => $name,
                'severity' => $severity,
                'color_badge' => $colorBadge,
            ],
        ];

        $stmt->close();

        sendJsonResponse($error, 500);
    }

    $stmt->close();

    return true;
}

function updateCategory(int $id, string $name, string $severity, ?string $colorBadge): bool
{
	global $conn;

	$sql = 'UPDATE sick_category SET name = ?, severity = ?, color_badge = ? WHERE id = ?';
	$stmt = $conn->prepare($sql);

	if ($stmt === false) {
		return false;
	}

	$stmt->bind_param('sssi', $name, $severity, $colorBadge, $id);
	$ok = $stmt->execute();
	$stmt->close();

	return $ok;
}

function deleteCategory(int $id): bool
{
	global $conn;

	$sql = 'DELETE FROM sick_category WHERE id = ?';
	$stmt = $conn->prepare($sql);

	if ($stmt === false) {
		return false;
	}

	$stmt->bind_param('i', $id);
	$ok = $stmt->execute();
	$stmt->close();

	return $ok;
}

function normalizeColorBadge(?string $color): ?string
{
	$value = trim((string)$color);
	return $value === '' ? null : $value;
}

function handleCategoryRequest(): void
{
	$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

	if ($action === 'list') {
		$rows = getCategories();
		sendJsonResponse([
			'success' => true,
			'message' => 'Category data loaded successfully.',
			'count' => count($rows),
			'data' => $rows,
		]);
	}

	if ($action === 'create') {
		$name = trim((string)($_POST['name'] ?? ''));
		$severity = trim((string)($_POST['severity'] ?? ''));
		$colorBadge = normalizeColorBadge($_POST['color_badge'] ?? null);

		if ($name === '' || $severity === '') {
			sendJsonResponse([
				'success' => false,
				'message' => 'Name and severity are required.',
			], 422);
		}

		if (!createCategory($name, $severity, $colorBadge)) {
			sendJsonResponse([
				'success' => false,
				'message' => 'Failed to create category.',
			], 500);
		}

		sendJsonResponse([
			'success' => true,
			'message' => 'Category created successfully.',
		]);
	}

	if ($action === 'update') {
		$id = (int)($_POST['id'] ?? 0);
		$name = trim((string)($_POST['name'] ?? ''));
		$severity = trim((string)($_POST['severity'] ?? ''));
		$colorBadge = normalizeColorBadge($_POST['color_badge'] ?? null);

		if ($id <= 0 || $name === '' || $severity === '') {
			sendJsonResponse([
				'success' => false,
				'message' => 'ID, name, and severity are required.',
			], 422);
		}

		if (!updateCategory($id, $name, $severity, $colorBadge)) {
			sendJsonResponse([
				'success' => false,
				'message' => 'Failed to update category.',
			], 500);
		}

		sendJsonResponse([
			'success' => true,
			'message' => 'Category updated successfully.',
		]);
	}

	if ($action === 'delete') {
		$id = (int)($_POST['id'] ?? 0);

		if ($id <= 0) {
			sendJsonResponse([
				'success' => false,
				'message' => 'ID is required.',
			], 422);
		}

		if (!deleteCategory($id)) {
			sendJsonResponse([
				'success' => false,
				'message' => 'Failed to delete category.',
			], 500);
		}

		sendJsonResponse([
			'success' => true,
			'message' => 'Category deleted successfully.',
		]);
	}

	sendJsonResponse([
		'success' => false,
		'message' => 'Invalid action.',
	], 400);
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
	handleCategoryRequest();
}