<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - <?= date('F Y', strtotime("$year-$month-01")) ?></title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10.5pt;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
        }
        .logo-box {
            flex: 0 0 200px;
            text-align: left;
        }
        .logo {
            max-width: 100%;
            height: auto;
            display: block;
        }
        .company-details {
            text-align: right;
            font-size: 9pt;
            margin-top: 30px;
            color: #000;
            line-height: 1.5;
        }
        .divider {
            border-top: 1.5px solid #000;
            margin: 5px 0 20px 0;
        }
        .title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 25px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        /* Info Table */
        .info-table th, .info-table td {
            border: 1px solid #ccc;
            padding: 6px 10px;
            text-align: left;
        }
        .info-table th {
            width: 40%;
            background-color: #fcfcfc;
            font-weight: bold;
        }
        
        /* Salary Table */
        .salary-table th, .salary-table td {
            border: 1px solid #ccc;
            padding: 6px 10px;
        }
        .salary-table th {
            background-color: #fcfcfc;
            font-weight: bold;
            text-align: left;
        }
        .salary-table .amt {
            text-align: right;
            width: 30%;
        }
        .salary-table .bold {
            font-weight: bold;
            background-color: #f7f7f7;
        }
        
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .signature-box {
            text-align: center;
            width: 250px;
        }
        .signature-area {
            height: 70px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 5px;
        }
        .stamp-img {
            max-height: 65px;
            max-width: 180px;
            object-fit: contain;
        }
        .signature-label {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 9.5pt;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            font-size: 10pt;
        }
        .footer p {
            margin: 2px 0;
        }

        .no-print {
            margin-top: 30px;
            text-align: center;
        }
        .btn {
            padding: 10px 20px;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 0; padding: 0; }
            .container { padding: 0; width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="logo-box">
            <img src="<?= base_url('Granth logo.jpg') ?>" alt="Granth logo" class="logo">
        </div>
        <div style="margin-top: 30px;" class="company-details">
            Top Floor, 12/A, Krishna Parisar, Ujjain, Madhya Pradesh, India<br>
            Email: info@granthinfotech.in | Phone: +91 9179187199
        </div>
    </div>
    
    <div class="divider"></div>
    
    <!-- Title -->
    <div class="title">
        Salary Slip - <?= date('F Y', strtotime("$year-$month-01")) ?>
    </div>
    
    <!-- Employee Info -->
    <table class="info-table">
        <tr>
            <th>Employee Name</th>
            <td><?= esc($employee['name']) ?></td>
        </tr>
        <tr>
            <th>Employee Code</th>
            <td><?= esc($employee['emp_code']) ?></td>
        </tr>
        <tr>
            <th>Date of Joining</th>
            <td><?= date('d M Y', strtotime($employee['date_of_joining'])) ?></td>
        </tr>
        <tr>
            <th>Total Working Days</th>
            <td><?= esc($salary['days_in_month'] ?? date('t', strtotime("$year-$month-01"))) ?> (Considered as regular month)</td>
        </tr>
        <tr>
            <th>Paid Days</th>
            <td><?= esc($salary['effective_days']) ?></td>
        </tr>
    </table>
    
    <!-- Earnings & Deductions -->
    <table class="salary-table">
        <thead>
            <tr class="bold">
                <th>Earnings</th>
                <th class="amt">Calculated Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $earnings = $salary['earnings'] ?? [];
            if (empty($earnings)) {
                $earnings = [
                    ['name' => 'Basic Salary', 'amount' => $salary['monthly_salary']]
                ];
            }
            $gross = 0;
            foreach ($earnings as $item): 
                $gross += $item['amount'];
            ?>
            <tr>
                <td><?= esc($item['name']) ?></td>
                <td class="amt"><?= number_format($item['amount'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
            
            <tr class="bold">
                <td>Gross Salary</td>
                <td class="amt"><?= number_format($gross, 2) ?></td>
            </tr>
            
            <!-- Deductions -->
            <tr>
                <td>Deductions (TDS/PF)</td>
                <td class="amt"><?= number_format($salary['statutory_deductions'] ?? 0, 2) ?></td>
            </tr>
            <tr>
                <td>Unpaid/Leave/Absent Day Deduction</td>
                <td class="amt"><?= number_format($salary['deduction'], 2) ?></td>
            </tr>
            
            <tr class="bold">
                <td>Net Salary Payable</td>
                <td class="amt"><?= number_format($salary['net_salary'], 2) ?></td>
            </tr>
        </tbody>
    </table>
    
    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-box" style="text-align: left;">
            <div class="signature-area">
                <!-- Employer Signature Image -->
                <img src="<?= base_url('assets/img/signature_seal_granth_infotech.png') ?>" alt="Employer Signature" style="max-width: 150px; max-height: 60px;">
            </div>
            <div class="signature-label">Employer Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-area"></div>
            <div class="signature-label">Employee Signature</div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p>Thanks</p>
        <p><strong>Granth Infotech Pvt Ltd</strong></p>
    </div>
</div>

<div class="no-print">
    <button class="btn" onclick="window.print()">Print Payslip</button>
</div>

</body>
</html>
