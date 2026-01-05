<?php
session_start();

// ✅ Admin Auth (redirect if not logged in)
if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header("Location: login.php");
    exit();
}

require('../config/connection.php');

// Check DB connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Escape and sanitize form data
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$product_name = $con->real_escape_string($_POST['product_name']);
$cat_no = $con->real_escape_string($_POST['cat_no']);
$batch_no = $con->real_escape_string($_POST['batch_no']);
$cas_no = $con->real_escape_string($_POST['cas_no']);
$synonyms = $con->real_escape_string($_POST['synonyms']);
$chemical_name = $con->real_escape_string($_POST['chemical_name']);
$mg = $con->real_escape_string($_POST['mg']);

$molecular_formula = $con->real_escape_string($_POST['molecular_formula']);
$solubility = $con->real_escape_string($_POST['solubility']);
$storage = $con->real_escape_string($_POST['storage']);
$shipping_condition = $con->real_escape_string($_POST['shipping_condition']);

$specification = $con->real_escape_string($_POST['specification']);
$purity = $con->real_escape_string($_POST['purity']);
$lab_test_number = $con->real_escape_string($_POST['lab_test_number']);

$note = $con->real_escape_string($_POST['note']);
$test_date = $con->real_escape_string($_POST['test_date']);
$retest_date = $con->real_escape_string($_POST['retest_date']);

// Insert or Update logic
if ($id > 0) {
    // Update existing record
    $sql = "UPDATE certificates SET 
        product_name = '$product_name',
        cat_no = '$cat_no',
        batch_no = '$batch_no',
        cas_no = '$cas_no',
        synonyms = '$synonyms',
        chemical_name = '$chemical_name',
        mg = '$mg',
        molecular_formula = '$molecular_formula',
        solubility = '$solubility',
        storage = '$storage',
        shipping_condition = '$shipping_condition',
        specification = '$specification',
        purity = '$purity',
        lab_test_number = '$lab_test_number',
        note = '$note',
        test_date = '$test_date',
        retest_date = '$retest_date'
    WHERE id = $id";

    if ($con->query($sql) === TRUE) {
        echo "Certificate updated successfully.";
    } else {
        echo "Update Error: " . $con->error;
    }
} else {
    // Insert new record
    $sql = "INSERT INTO certificates (
        product_name, cat_no, batch_no, cas_no, synonyms, chemical_name, mg,
        molecular_formula, solubility, storage, shipping_condition,
        specification, purity, lab_test_number, note, test_date, retest_date
    ) VALUES (
        '$product_name', '$cat_no', '$batch_no', '$cas_no', '$synonyms', '$chemical_name', '$mg',
        '$molecular_formula', '$solubility', '$storage', '$shipping_condition',
        '$specification', '$purity', '$lab_test_number', '$note', '$test_date', '$retest_date'
    )";

    if ($con->query($sql) === TRUE) {
        echo "Certificate submitted successfully.";
    } else {
        echo "Insert Error: " . $con->error;
    }
}

$con->close();
?>
