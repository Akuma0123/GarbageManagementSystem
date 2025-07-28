<?php
header('Content-Type: application/json');

$requests = [];

$conn = new mysqli("localhost", "your_db_user", "your_db_password", "your_db_name");
if ($conn->connect_error) {
  echo json_encode(["requests" => []]);
  exit;
}

$sql = "SELECT id, location, lat, lng, description, urgency FROM requests";
$result = $conn->query($sql);

if ($result) {
  while ($row = $result->fetch_assoc()) {
    $requests[] = [
      "id" => (int) $row["id"],
      "location" => $row["location"],
      "coords" => [(float) $row["lat"], (float) $row["lng"]],
      "description" => $row["description"],
      "urgency" => $row["urgency"]
    ];
  }
}

$conn->close();

echo json_encode(["requests" => $requests]);