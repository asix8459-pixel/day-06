<?php 
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_services_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentId = $_POST['user_id'];
    $firstName = $_POST['first_name'];
    $middleName = $_POST['middleName'];
    $lastName = $_POST['lastName'];
    $birthDate = $_POST['birthDate'];
    $nationality = $_POST['nationality'];
    $religion = $_POST['religion'];
    $biologicalSex = $_POST['biologicalSex'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $currentAddress = $_POST['currentAddress'];
    $permanentAddress = $_POST['permanentAddress'];
    $motherName = $_POST['motherName'];
    $motherWork = $_POST['motherWork'];
    $motherContact = $_POST['motherContact'];
    $fatherName = $_POST['fatherName'];
    $fatherWork = $_POST['fatherWork'];
    $fatherContact = $_POST['fatherContact'];
    $siblingsCount = $_POST['siblingsCount'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $role = "Student"; // Always Student
    $year = $_POST['year'];
    $section = $_POST['section'];
    $course = $_POST['course'];
    $department = NULL;

    $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ? OR email = ?");
    $checkStmt->bind_param("ss", $studentId, $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $successMessage = "Error: User ID or Email already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users 
        (user_id, first_name, middle_name, last_name, birth_date, nationality, religion, biological_sex, email, phone, current_address, permanent_address, role, year, section, course, department, password_hash, mother_name, mother_work, mother_contact, father_name, father_work, father_contact, siblings_count) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("sssssssssssssssssssssssss", $studentId, $firstName, $middleName, $lastName, $birthDate, $nationality, $religion, $biologicalSex, $email, $phone, $currentAddress, $permanentAddress, $role, $year, $section, $course, $department, $password, $motherName, $motherWork, $motherContact, $fatherName, $fatherWork, $fatherContact, $siblingsCount);

        if ($stmt->execute()) {
            $successMessage = "New record created successfully";
        } else {
            $successMessage = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $checkStmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: url('assets/neust.jpg') no-repeat center center/cover;
            margin: 0;
        }
        .container {
            width: 400px;
            background: rgba(255, 255, 255, 0.94);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .step {
            width: 30px;
            height: 30px;
            background: lightgray;
            color: black;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            transition: background 0.3s, color 0.3s;
        }
        .step.active {
            background: #007bff;
            color: white;
        }
        .form-step {
            display: none;
        }
        .form-step.active {
            display: block;
        }
        .error {
            color: red;
            font-size: 12px;
            display: none;
            margin-top: 4px;
        }
        .password-requirements {
            font-size: 12px;
            color: gray;
        }
        .password-requirements span {
            display: block;
        }
        button {
            padding: 10px;
            margin-top: 10px;
            border: none;
            background: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input:focus, select:focus {
            border-color: #007bff;
            outline: none;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        p {
            text-align: center;
            margin-top: 20px;
        }
        p a {
            color: #007bff;
            text-decoration: none;
        }
        p a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="progress-bar">
            <div class="step active">1</div>
            <div class="step">2</div>
            <div class="step">3</div>
            <div class="step">4</div>
        </div>
        <form id="registrationForm" method="POST" action="">
            <div class="form-step active">
                <h2>Personal Information</h2>
                <input type="text" id="studentId" name="user_id" placeholder="Student ID" required>
                <input type="text" id="firstName" name="first_name" placeholder="First Name" required>
                <input type="text" id="middleName" name="middleName" placeholder="Middle Name">
                <input type="text" id="lastName" name="lastName" placeholder="Last Name" required>
                <input type="date" id="birthDate" name="birthDate" required>
                <input type="text" id="nationality" name="nationality" placeholder="Nationality" required>
                <input type="text" id="religion" name="religion" placeholder="Religion" required>
                <select id="biologicalSex" name="biologicalSex" required>
                    <option value="">Select Biological Sex</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
                <!-- Role is always student -->
                <input type="hidden" id="role" name="role" value="Student">
                <div id="studentFields">
                    <input type="number" id="year" name="year" placeholder="Year" required>
                    <input type="text" id="section" name="section" placeholder="Section" required>
                    <input type="text" id="course" name="course" placeholder="Course" required>
                </div>
                <span class="error">All fields are required.</span>
                <button type="button" class="next">Next</button>
            </div>
            <div class="form-step">
                <h2>Contact Information</h2>
                <input type="email" id="email" name="email" placeholder="Email" required>
                <input type="text" id="phone" name="phone" placeholder="Phone Number" required>
                <input type="text" id="currentAddress" name="currentAddress" placeholder="Current Address" required>
                <input type="text" id="permanentAddress" name="permanentAddress" placeholder="Permanent Address" required>
                <span class="error">All fields are required.</span>
                <button type="button" class="prev">Previous</button>
                <button type="button" class="next">Next</button>
            </div>
            <div class="form-step">
                <h2>Family Information</h2>
                <input type="text" id="motherName" name="motherName" placeholder="Mother's Name" required>
                <input type="text" id="motherWork" name="motherWork" placeholder="Mother's Work" required>
                <input type="text" id="motherContact" name="motherContact" placeholder="Mother's Contact Number" required>
                <input type="text" id="fatherName" name="fatherName" placeholder="Father's Name" required>
                <input type="text" id="fatherWork" name="fatherWork" placeholder="Father's Work" required>
                <input type="text" id="fatherContact" name="fatherContact" placeholder="Father's Contact Number" required>
                <input type="number" id="siblingsCount" name="siblingsCount" placeholder="Number of Siblings" required>
                <span class="error">All fields are required.</span>
                <button type="button" class="prev">Previous</button>
                <button type="button" class="next">Next</button>
            </div>
            <div class="form-step">
                <h2>Account Security</h2>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <div class="password-requirements">
                    <span>✔ Minimum 8 characters</span>
                    <span>✔ At least one uppercase letter</span>
                    <span>✔ At least one lowercase letter</span>
                    <span>✔ At least one number</span>
                </div>
                <input type="password" id="confirmPassword" placeholder="Confirm Password" required>
                <span class="error" id="passwordMatchError">Passwords do not match.</span>
                <button type="button" class="prev">Previous</button>
                <button type="submit">Submit</button>
            </div>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let currentStep = 0;
            const formSteps = document.querySelectorAll(".form-step");
            const steps = document.querySelectorAll(".step");
            const nextButtons = document.querySelectorAll(".next");
            const prevButtons = document.querySelectorAll(".prev");
            
            function updateStep() {
                formSteps.forEach((step, index) => {
                    step.classList.toggle("active", index === currentStep);
                    steps[index].classList.toggle("active", index === currentStep);
                });
            }

            nextButtons.forEach(btn => btn.addEventListener("click", () => {
                if (currentStep < formSteps.length - 1) {
                    currentStep++;
                    updateStep();
                }
            }));
            
            prevButtons.forEach(btn => btn.addEventListener("click", () => {
                if (currentStep > 0) {
                    currentStep--;
                    updateStep();
                }
            }));
            
            document.getElementById("registrationForm").addEventListener("submit", function (e) {
                const password = document.getElementById("password").value;
                const confirmPassword = document.getElementById("confirmPassword").value;
                const passwordError = document.getElementById("passwordMatchError");
                const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
                
                if (!passwordRegex.test(password)) {
                    alert("Password must be at least 8 characters long and include uppercase, lowercase, and a number.");
                    e.preventDefault();
                }
                
                if (password !== confirmPassword) {
                    passwordError.style.display = "block";
                    e.preventDefault();
                } else {
                    passwordError.style.display = "none";
                }
            });
        });
    </script>
</body>
</html>