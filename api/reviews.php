<?php
require_once __DIR__ . '/../config/db.php';
$conn = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Honeypot: a real visitor never fills this hidden field; bots often do.
    if (!empty($input['website'])) {
        json_response(['success' => true, 'message' => 'Thanks! Your review has been submitted and will appear once approved.']);
    }

    if (!check_rate_limit('submit_review', 5, 3600)) {
        json_response(['error' => 'You have submitted a few reviews recently. Please try again later.'], 429);
    }

    $productId = (int)($input['product_id'] ?? 0);
    $name = trim($input['reviewer_name'] ?? '');
    $rating = (int)($input['rating'] ?? 0);
    $text = trim($input['review_text'] ?? '');

    if (!$productId || $name === '' || $rating < 1 || $rating > 5) {
        json_response(['error' => 'Please provide your name and a rating between 1 and 5.'], 400);
    }

    $check = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $check->bind_param('i', $productId);
    $check->execute();
    if (!$check->get_result()->fetch_assoc()) {
        json_response(['error' => 'Product not found.'], 404);
    }

    $stmt = $conn->prepare("INSERT INTO product_reviews (product_id, reviewer_name, rating, review_text, is_approved) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param('isis', $productId, $name, $rating, $text);
    $stmt->execute();

    json_response(['success' => true, 'message' => 'Thanks! Your review has been submitted and will appear once approved.']);
}

// ---- GET: list approved reviews + average for a product ----
$productId = (int)($_GET['product_id'] ?? 0);
if (!$productId) json_response(['error' => 'product_id is required'], 400);

$stmt = $conn->prepare("SELECT reviewer_name, rating, review_text, created_at FROM product_reviews WHERE product_id = ? AND is_approved = 1 ORDER BY created_at DESC");
$stmt->bind_param('i', $productId);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$avg = 0;
$count = count($reviews);
if ($count) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $avg = round($sum / $count, 1);
}

json_response(['reviews' => $reviews, 'average_rating' => $avg, 'count' => $count]);
