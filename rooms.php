<?php
include 'config.php';
session_start();
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "You must be logged in to apply for a room."]);
    exit;
}
$user_id = $_SESSION['user_id'];
// Handle room application
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_room'])) {
    $room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
    // Agreement gate: require latest active policy acceptance
    require_once __DIR__ . '/includes/DormAgreementService.php';
    $agreementService = new DormAgreementService($conn);
    $activeAgreement = $agreementService->getActiveAgreement();
    if ($activeAgreement && !$agreementService->hasUserAccepted($user_id, (int)$activeAgreement['id'])) {
        echo json_encode(["success" => false, "message" => "You must accept the latest Dormitory Agreement & Policy before applying."]);
        exit;
    }
    // Check if user already has an approved application
    $approvedQuery = "SELECT * FROM student_room_applications WHERE user_id = ? AND status = 'Approved'";
    $stmt = $conn->prepare($approvedQuery);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "You already have an approved application for a room."]);
        exit;
    }
    // Validate room existence
    $roomQuery = "SELECT * FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($roomQuery);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Selected room does not exist."]);
        exit;
    }
    // Check if user already applied for the room
    $checkQuery = "SELECT * FROM student_room_applications WHERE user_id = ? AND room_id = ? AND status = 'Pending'";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $user_id, $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $insertQuery = "INSERT INTO student_room_applications (user_id, room_id, status, applied_at, price_per_month) VALUES (?, ?, 'Pending', NOW(), (SELECT price_per_month FROM rooms WHERE id = ?))";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sii", $user_id, $room_id, $room_id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Application submitted successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error submitting application."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "You already have a pending application for this room."]);
    }
    exit;
}
$query = "SELECT * FROM rooms";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rooms</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .room-card { transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out; }
        .room-card:hover { transform: scale(1.05); box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2); }
        .room-img { width: 100%; height: 200px; object-fit: cover; border-top-left-radius: 10px; border-top-right-radius: 10px; }
        .modal-header { background-color: #003366; color: white; border-bottom: none; }
        .btn-primary, .btn-secondary { background-color: #003366; color: white; }
        .modal-centered { display: flex; align-items: center; justify-content: center; }
        .modal-content { border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .modal-body.success { background-color: #d4edda; color: #155724; }
        .modal-body.error { background-color: #f8d7da; color: #721c24; }
        .modal-body .icon { font-size: 4rem; margin-bottom: 1rem; }
        .modal-body.success .icon { color: #155724; }
        .modal-body.error .icon { color: #721c24; }
        .room-name { font-weight: bold; text-align: left; margin-bottom: 10px; margin-left: 100px; }
        .modal.fade .modal-dialog {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .modal-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
    </style>
    <title>Apply for Dormitory Room</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/student_theme.css">
</head>
<body>
    <?php include 'student_header.php'; // Include the header file ?>
<?php include('student_header.php'); ?>
    <div class="container mt-4">
        <h2 class="text-center mb-4">Available Rooms</h2>
        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-lg room-card">
                        <img src="<?= htmlspecialchars($row['image']) ?>" class="room-img" alt="Room Image">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            <p>Total Beds: <?= htmlspecialchars($row['total_beds']) ?></p>
                            <p>Occupied Beds: <?= htmlspecialchars($row['occupied_beds']) ?></p>
                            <p>Available Beds: <?= htmlspecialchars($row['total_beds'] - $row['occupied_beds']) ?></p>
                            <p>Price per Month: ₱<?= htmlspecialchars($row['price_per_month']) ?></p>
                            <button class="btn btn-info w-100 view-details-btn" data-room-id="<?= $row['id'] ?>" data-room-name="<?= htmlspecialchars($row['name']) ?>" data-total-beds="<?= htmlspecialchars($row['total_beds']) ?>" data-occupied-beds="<?= htmlspecialchars($row['occupied_beds']) ?>" data-image="<?= htmlspecialchars($row['image']) ?>" data-amenities="<?= htmlspecialchars($row['amenities'] ?? 'Not specified') ?>" data-price="<?= htmlspecialchars($row['price_per_month']) ?>">View Details</button>
                            <button class="btn btn-success w-100 apply-room-btn mt-2" data-room-id="<?= $row['id'] ?>">Apply for Room</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <!-- Room Details Modal -->
    <div class="modal fade" id="roomDetailsModal" tabindex="-1" aria-labelledby="roomDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Room Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h5 id="roomName" class="room-name"></h5>
                    <div class="row">
                        <div class="col-md-6">
                            <img src="" class="img-fluid mb-3" id="roomImage" alt="Room Image">
                        </div>
                        <div class="col-md-6">
                            <p>Total Beds: <span id="totalBeds"></span></p>
                            <p>Occupied Beds: <span id="occupiedBeds"></span></p>
                            <p>Available Beds: <span id="availableBeds"></span></p>
                            <p>Price per Month: ₱<span id="price"></span></p>
                            <p>Amenities: <span id="amenities"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer mt-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Apply for Room Modal -->
    <div class="modal fade" id="applyRoomModal" tabindex="-1" aria-labelledby="applyRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Apply for Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="applyRoomForm">
                        <input type="hidden" name="room_id" id="room_id">
                        <div class="modal-footer mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit Application</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Agreement Modal (shown before apply if not yet accepted) -->
    <div class="modal fade" id="agreementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="agreementTitle">Dormitory Agreement & Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="agreementContent" style="max-height: 420px; overflow-y: auto; border: 1px solid #dee2e6; padding: 12px; border-radius: 8px;"></div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="agreeCheckbox">
                        <label class="form-check-label" for="agreeCheckbox">I have read and agree to the Dormitory Agreement & Policy</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <a id="downloadAgreementLink" class="btn btn-outline-secondary" target="_blank">Download PDF</a>
                    <button type="button" class="btn btn-primary" id="agreeBtn" disabled>I Agree</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Confirmation Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="messageModalBody">
                    <!-- Message content will be inserted here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let applyRoomModal = new bootstrap.Modal(document.getElementById("applyRoomModal"));
            let roomDetailsModal = new bootstrap.Modal(document.getElementById("roomDetailsModal"));
            let messageModal = new bootstrap.Modal(document.getElementById("messageModal"));
            let agreementModal = new bootstrap.Modal(document.getElementById("agreementModal"));
            function escapeHtml(str){
                return str.replace(/[&<>"]/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]); });
            }
            document.querySelectorAll(".view-details-btn").forEach(button => {
                button.addEventListener("click", function() {
                    document.getElementById("roomImage").src = this.getAttribute("data-image");
                    document.getElementById("roomName").textContent = this.getAttribute("data-room-name");
                    document.getElementById("totalBeds").textContent = this.getAttribute("data-total-beds");
                    document.getElementById("occupiedBeds").textContent = this.getAttribute("data-occupied-beds");
                    document.getElementById("availableBeds").textContent = this.getAttribute("data-total-beds") - this.getAttribute("data-occupied-beds");
                    document.getElementById("price").textContent = this.getAttribute("data-price");
                    document.getElementById("amenities").textContent = this.getAttribute("data-amenities");
                    roomDetailsModal.show();
                });
            });
            document.querySelectorAll(".apply-room-btn").forEach(button => {
                button.addEventListener("click", function() {
                    document.getElementById("room_id").value = this.getAttribute("data-room-id");
                    fetch("check_dorm_agreement.php")
                        .then(r => r.json())
                        .then(data => {
                            if (data.success && (data.accepted || !data.hasActive)) {
                                applyRoomModal.show();
                            } else if (data.success && data.hasActive && !data.accepted) {
                                document.getElementById("agreementTitle").textContent = data.agreement.title;
                                document.getElementById("agreementContent").innerHTML = escapeHtml(data.agreement.content).replace(/\n/g, '<br>');
                                document.getElementById("agreeCheckbox").checked = false;
                                document.getElementById("agreeBtn").disabled = true;
                                document.getElementById("downloadAgreementLink").href = "download_dorm_agreement.php?id=" + data.agreement.id;
                                agreementModal.show();
                            } else {
                                applyRoomModal.show();
                            }
                        })
                        .catch(() => applyRoomModal.show());
                });
            });
            document.getElementById("agreeCheckbox").addEventListener("change", function(){
                document.getElementById("agreeBtn").disabled = !this.checked;
            });
            document.getElementById("agreeBtn").addEventListener("click", function(){
                const btn = this;
                btn.disabled = true;
                fetch('dorm_agreement_accept.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: new URLSearchParams({ action: 'accept' }) })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            agreementModal.hide();
                            applyRoomModal.show();
                        } else {
                            document.getElementById("messageModalBody").classList.add("error");
                            document.getElementById("messageModalBody").classList.remove("success");
                            document.getElementById("messageModalBody").innerHTML = data.message || 'Failed to record acceptance.';
                            messageModal.show();
                        }
                        btn.disabled = false;
                    })
                    .catch(() => {
                        document.getElementById("messageModalBody").classList.add("error");
                        document.getElementById("messageModalBody").classList.remove("success");
                        document.getElementById("messageModalBody").innerHTML = 'Network error. Please try again.';
                        messageModal.show();
                        btn.disabled = false;
                    });
            });
            document.getElementById("applyRoomForm").addEventListener("submit", function(event) {
                event.preventDefault();
                let formData = new FormData(this);
                formData.append("apply_room", true);
                fetch("rooms.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById("messageModalBody").classList.add("success");
                        document.getElementById("messageModalBody").classList.remove("error");
                        document.getElementById("messageModalBody").innerHTML = data.message;
                    } else {
                        document.getElementById("messageModalBody").classList.add("error");
                        document.getElementById("messageModalBody").classList.remove("success");
                        document.getElementById("messageModalBody").innerHTML = data.message;
                    }
                    messageModal.show();
                    setTimeout(() => {
                        messageModal.hide();
                        if (data.success) location.reload();
                    }, 2000);
                })
                .catch(error => console.error("Error:", error));
            });
        });
    </script>
</body>
</html>