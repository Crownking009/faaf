<?php
/**
 * Calculates an approximate delivery fee.
 * Uses the free OpenStreetMap Nominatim geocoder to turn the customer's
 * address into coordinates, then the Haversine (straight-line distance)
 * formula from the store's coordinates. No paid API key required.
 *
 * GET /api/distance.php?address=...
 */
require_once __DIR__ . '/../config/db.php';
$conn = db();

$address = trim($_GET['address'] ?? '');
if ($address === '' || strlen($address) < 6) {
    json_response(['error' => 'Please enter a more complete delivery address.'], 400);
}

$storeLat = (float) get_setting('store_lat', '6.6469');
$storeLng = (float) get_setting('store_lng', '3.2871');
$ratePerKm = (float) get_setting('delivery_rate_per_km', '300');
$baseFee = (float) get_setting('delivery_base_fee', '500');

// ---- Geocode via Nominatim (OpenStreetMap) ----
$query = $address . ', Lagos, Nigeria';
$url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
    'q' => $query,
    'format' => 'json',
    'limit' => 1,
    'countrycodes' => 'ng',
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    // Nominatim requires a descriptive User-Agent identifying the app
    CURLOPT_HTTPHEADER => ['User-Agent: FAAFCollectionsWebsite/1.0 (contact: store)'],
]);
$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError || !$response) {
    json_response(['error' => 'Could not reach the address lookup service. Please try again, or choose pickup.'], 502);
}

$geo = json_decode($response, true);

if (empty($geo) || !isset($geo[0]['lat'])) {
    // Fallback: ask the user to confirm/refine instead of blocking checkout entirely
    json_response([
        'error' => 'We could not locate that address automatically. Please add a nearby landmark or your Local Government Area and try again.',
        'geocoded' => false,
    ], 422);
}

$destLat = (float) $geo[0]['lat'];
$destLng = (float) $geo[0]['lon'];

// ---- Haversine formula ----
function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $earthRadius = 6371; // km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

$distanceKm = haversineKm($storeLat, $storeLng, $destLat, $destLng);
// Add 25% to approximate real road distance vs straight-line ("as the crow flies")
$roadEstimateKm = round($distanceKm * 1.25, 1);

$freeThreshold = (float) get_setting('free_delivery_threshold', '0');
$cartSubtotal = isset($_GET['subtotal']) ? (float) $_GET['subtotal'] : 0;

$fee = $baseFee + ($roadEstimateKm * $ratePerKm);
$fee = round($fee / 50) * 50; // round to nearest ₦50

$freeDeliveryApplied = false;
if ($freeThreshold > 0 && $cartSubtotal >= $freeThreshold) {
    $fee = 0;
    $freeDeliveryApplied = true;
}

json_response([
    'geocoded' => true,
    'matched_address' => $geo[0]['display_name'] ?? $address,
    'distance_km' => $roadEstimateKm,
    'delivery_fee' => (float) $fee,
    'base_fee' => $baseFee,
    'rate_per_km' => $ratePerKm,
    'free_delivery_applied' => $freeDeliveryApplied,
    'lat' => $destLat,
    'lng' => $destLng,
    'note' => $freeDeliveryApplied
        ? "Your order qualifies for free delivery!"
        : 'Distance is an estimate based on straight-line geocoding, not live traffic routing.',
]);
