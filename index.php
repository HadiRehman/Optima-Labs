<?php
require('config/connection.php');
$certificate = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $batch_number = trim($_POST['batch_number'] ?? '');

    if (!empty($batch_number)) {
        $stmt = $con->prepare("SELECT * FROM certificates WHERE batch_no = ?");
        $stmt->bind_param("s", $batch_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $certificate = $result->fetch_assoc();
        } else {
            $error = "No certificate found for this Batch Number.";
        }
    } else {
        $error = "Please enter a Batch Number.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Certificate Verification</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <link rel="stylesheet" href="assets/css/About.css">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
  <style>
        body {
            background: #fff;
            font-family: 'Segoe UI', sans-serif;
        }
        .verification-box {
            max-width: 500px;
            margin: 0 auto;
            text-align: center;
        }
        .certificate-container {
            width: 800px;
            margin: 30px auto;
            padding: 50px 60px;
            background-image: url('assets/images/certificate-bg.jpg');
            background-size: cover;
            background-position: center;
            color: #000;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }
        .title {
            font-weight: bold;
        }
        .download-btn {
            display: block;
            margin: 20px auto;
        }
    </style>
</head>
<body>

<div class="banner">
    <div class="overlay"></div>

    <div class="navbar">
      <a href="index.php">Home</a>
      <a href="about.html">About</a>
      <a href="contact.html">Contact</a>
<a href="blogs.php">Blogs</a>
      <a href="lab-analyst-request.html">Lab Analysis Request</a>
    </div>

    <div class="banner-content">
      <h1>Verification</h1>
      
      <p>Here you can verify the authenticity of any test conducted by our company</p>
    </div>
  </div>
<br><br>
<?php if (!$certificate): ?>
<div class="verification-box">
    <h1>Verification</h1>

    <form method="post" class="mt-4">
        <input type="text" name="batch_number" class="form-control mb-3" placeholder="Enter Your Batch Number" required>
        <!-- Optional Key Field -->
        <!-- <input type="text" name="unique_key" class="form-control mb-3" placeholder="Unique key"> -->
        <button type="submit" class="btn btn-success">Verify</button>
    </form>

    <?php if ($error): ?>
        <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($certificate): ?>
    <style>
        .certificate-container {
            width: 800px;
            height: auto;
            margin: 50px auto;
            background-image: url('assets/images/certificate-bg.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            padding: 50px 60px;
            font-family: Arial, sans-serif;
            position: relative;
            color: #000;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
            page-break-after: always;
        }
        .section {
            padding: 10px 10px;
        }
        .section .title {
            font-weight: bold;
            text-transform: uppercase;
        }
        .row {
            justify-content: space-between;
            height: 40px;
        }
        .checkbox-group {
            display: flex;
            gap: 20px;
            margin-top: 5px;
        }
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .checkbox-group input[type="checkbox"] {
            transform: scale(1.2);
        }
        .sign-area {
            /* margin-top: 30px; */
        }
    </style>

    <div class="certificate-container" id="certificate">
        <div class="row">
            <div class="col-sm-6"><h2 style="font-size: 28px; margin-bottom: 40px;">Certificate of Analysis</h2></div>
            <div class="col-sm-6" style="text-align: right;"><img src="assets/images/optima-labs-logo-small.png" height="100" style="margin-top: -30px;"/></div>
        </div>
        <br><br>
        <div class="row section">
            <div class="col-sm-12"><span class="title">Client Name:</span> Peptides Lab UK - <a href="https://peptideslabuk.com/">peptideslabuk.com</a></div>
        </div>
        <div class="row section">
            <div class="col-sm-6"><span class="title">Product ID:</span> <?= htmlspecialchars($certificate['cat_no']) ?></div>
            <div class="col-sm-6"><span class="title">Batch Number:</span> <?= htmlspecialchars($certificate['batch_no'] ?? '25066A') ?></div>
        </div>

        <div class="row section">
            <div class="col-sm-6"><span class="title">Product Name:</span> <?= htmlspecialchars($certificate['product_name']) ?></div>
            <div class="col-sm-6"><span class="title">CAS Number:</span> <?= htmlspecialchars($certificate['cas_no']) ?></div>
        </div>

        <div class="col-sm-12" style="text-align: center; position: absolute; left: 0"><img src="assets/images/optima-labs-logo.png" style="opacity: 0.15;"/></div>

        <div class="section"><span class="title">Synonyms:</span> <?= htmlspecialchars($certificate['synonyms']) ?></div>
        <div class="section"><span class="title">Formula:</span> <?= htmlspecialchars($certificate['molecular_formula']) ?></div>
        <div class="section"><span class="title">Solubility:</span> <?= htmlspecialchars($certificate['solubility']) ?></div>
        <div class="section"><span class="title">Shipping Condition:</span> <?= htmlspecialchars($certificate['shipping_condition']) ?></div>
        <div class="section"><span class="title">Long Term Storage:</span> <?= nl2br(htmlspecialchars($certificate['storage'])) ?></div>
        <div class="section"><span class="title">Specification:</span> White Powder</div>
        <div class="section"><span class="title">Purity:</span> <?= htmlspecialchars($certificate['purity']) ?></div>

        <div class="section">
            <span class="title">Tests:</span>
            <div class="checkbox-group">
                <label><input type="checkbox" checked disabled> FTIR</label>
                <label><input type="checkbox" disabled> GCMS</label>
                <label><input type="checkbox" checked disabled> NMR</label>
                <label><input type="checkbox" checked disabled> HPLC</label>
            </div>
        </div>

        <div class="section"><span class="title">Lab Test Number:</span> <?= htmlspecialchars($certificate['lab_test_number'] ?? '866402') ?></div>

        <?php 
            if($certificate['note'])
            {
        ?>
        <div class="section">
            <span class="title">Additional Information / Notes:</span><br>
            <small>
                <?= htmlspecialchars($certificate['note']) ?>
            </small>
        </div>
        <?php
            }
        ?>
        <div class="sign-area">
            <div class="section"><span class="title">COA Approved By:</span> QC Analyst & Lab Lead - R. Schneider</div>

            <div class="row section">
                <div><span class="title">Test Date:</span> <?= date('d-M-Y', strtotime($certificate['test_date'])) ?></div>
                <div><span class="title">Retest Date:</span> <?= date('d-M-Y', strtotime($certificate['retest_date'])) ?></div>
            </div>
        </div>
    </div>

    <button onclick="downloadCertificate()" class="btn btn-primary download-btn">Download Certificate (JPG)</button>
    <br><br>
<?php endif; ?>

</body>
</html>


<script>
    function downloadCertificate() {
        const div = document.getElementById('certificate');

        // Wait for all images inside the div to load
        const images = div.querySelectorAll('img');
        const imagePromises = Array.from(images).map(img => {
            if (!img.complete) {
                return new Promise(resolve => {
                    img.onload = img.onerror = resolve;
                });
            }
            return Promise.resolve();
        });

        Promise.all(imagePromises).then(() => {
            html2canvas(div, {
                scale: 3,            // Higher scale = better quality
                useCORS: true,       // Enables cross-origin image rendering
                backgroundColor: '#fff', // JPG doesn't support transparency
                scrollY: -window.scrollY
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'certificate.jpg';
                link.href = canvas.toDataURL('image/jpeg', 1.0); // JPG format
                link.click();
            }).catch(err => {
                console.error('Screenshot failed:', err);
                alert('Unable to capture screenshot. See console for details.');
            });
        });
    }
</script>