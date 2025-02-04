<?php
session_start();
include('../includes/db.php');

// Ensure only admin can access this page
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['form_id'])) {
    $form_id_to_delete = $_GET['form_id'];

    // Fetch the user_id associated with the form to update the user's form_submitted count
    $stmt = $pdo->prepare("SELECT user_id FROM user_funds WHERE id = ?");
    $stmt->execute([$form_id_to_delete]);
    $form = $stmt->fetch();

    if ($form) {
        $user_id = $form['user_id'];

        // Delete the form from the user_funds table
        $stmt = $pdo->prepare("DELETE FROM user_funds WHERE id = ?");
        $stmt->execute([$form_id_to_delete]);

        // Decrement the form_submitted count in the users table
        $stmt = $pdo->prepare("UPDATE users SET form_submitted = form_submitted - 1 WHERE id = ?");
        $stmt->execute([$user_id]);

        // Set success message in session
        $_SESSION['success_message'] = "Form deleted successfully!";
    }
}

// Redirect after deletion
header("Location: submit_form.php");
exit();
?>
