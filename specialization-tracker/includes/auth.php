<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

function is_logged_in()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: /specialization-tracker/login.php');
        exit;
    }
}

function require_role($roles)
{
    require_login();

    if (!is_array($roles)) {
        $roles = [$roles];
    }

    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles, true)) {
        header('Location: /specialization-tracker/index.php');
        exit;
    }
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function get_current_user_data($conn)
{
    if (!is_logged_in()) {
        return null;
    }

    $user_id = (int) $_SESSION['user_id'];

    $sql = "SELECT * FROM users WHERE id = $user_id LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) === 1) {
        return mysqli_fetch_assoc($result);
    }

    return null;
}

function redirect_by_role($role)
{
    switch ($role) {
        case 'super_admin':
            header('Location: /specialization-tracker/admin/super_dashboard.php');
            break;
        case 'admin':
            header('Location: /specialization-tracker/admin/dashboard.php');
            break;
        case 'coordinator':
            header('Location: /specialization-tracker/coordinator/dashboard.php');
            break;
        case 'mentor':
            header('Location: /specialization-tracker/mentor/dashboard.php');
            break;
        case 'student':
            header('Location: /specialization-tracker/student/dashboard.php');
            break;
        default:
            header('Location: /specialization-tracker/index.php');
            break;
    }
    exit;
}

function get_role_label($role)
{
    return ucwords(str_replace('_', ' ', $role));
}

function get_allowed_specializations($student, $selected_specialization_types = [])
{
    global $conn;

    $cgpa = isset($student['cgpa']) ? (float) $student['cgpa'] : 0;
    $kt_status = $student['kt_status'] ?? 'yes';
    $semester = isset($student['semester']) ? (int) $student['semester'] : 0;

    $has_honours = in_array('honours', $selected_specialization_types, true);

    $rules = [
        'honours_min_semester' => 4,
        'honours_max_semester' => 8,
        'honours_min_cgpa' => 7.0,
        'honours_research_min_semester' => 7,
        'honours_research_max_semester' => 8,
        'honours_research_min_cgpa' => 7.5
    ];

    $rule_sql = "SELECT rule_key, rule_value FROM specialization_rules";
    $rule_result = mysqli_query($conn, $rule_sql);
    if ($rule_result) {
        while ($row = mysqli_fetch_assoc($rule_result)) {
            if (array_key_exists($row['rule_key'], $rules)) {
                $rules[$row['rule_key']] = (float) $row['rule_value'];
            }
        }
    }

    $eligibility = [
        'honours' => [
            'eligible' => false,
            'message' => ''
        ],
        'minor' => [
            'eligible' => true,
            'message' => 'Minor is open. Certificate upload and approval are required.'
        ],
        'honours_with_research' => [
            'eligible' => false,
            'message' => ''
        ]
    ];

    // Honours logic
    if (
        $semester >= (int) $rules['honours_min_semester'] &&
        $semester <= (int) $rules['honours_max_semester'] &&
        $cgpa >= (float) $rules['honours_min_cgpa'] &&
        strtolower($kt_status) === 'no'
    ) {
        $eligibility['honours']['eligible'] = true;
        $eligibility['honours']['message'] = 'Eligible for Honours.';
    } else {
        $eligibility['honours']['eligible'] = false;
        $eligibility['honours']['message'] = 'Not eligible now. Offline exam required if failed in criteria.';
    }

    // Honours with Research logic
    if ($has_honours) {
        $eligibility['honours_with_research']['eligible'] = false;
        $eligibility['honours_with_research']['message'] = 'Cannot select Honours with Research because Honours is already selected.';
    } elseif (
        $semester >= (int) $rules['honours_research_min_semester'] &&
        $semester <= (int) $rules['honours_research_max_semester'] &&
        $cgpa >= (float) $rules['honours_research_min_cgpa'] &&
        strtolower($kt_status) === 'no'
    ) {
        $eligibility['honours_with_research']['eligible'] = true;
        $eligibility['honours_with_research']['message'] = 'Eligible for Honours with Research.';
    } else {
        $eligibility['honours_with_research']['eligible'] = false;
        $eligibility['honours_with_research']['message'] = 'Eligible only in semester 7-8, CGPA >= 7.5, and no KT.';
    }

    return $eligibility;
}

function get_dashboard_counts($conn)
{
    $counts = [
        'users' => 0,
        'students' => 0,
        'mentors' => 0,
        'applications' => 0,
        'certificates' => 0
    ];

    $map = [
        'users' => 'SELECT COUNT(*) AS total FROM users',
        'students' => 'SELECT COUNT(*) AS total FROM students',
        'mentors' => 'SELECT COUNT(*) AS total FROM mentors',
        'applications' => 'SELECT COUNT(*) AS total FROM student_specializations',
        'certificates' => 'SELECT COUNT(*) AS total FROM certificates'
    ];

    foreach ($map as $key => $sql) {
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $counts[$key] = (int) ($row['total'] ?? 0);
        }
    }

    return $counts;
}
?>