<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #2196F3;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background: #fff;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Supplier Application</h1>
        </div>
        
        <div class="content">
            <h2>New Application Received</h2>
            
            <p>A new supplier application has been submitted by {{SHOP_NAME}}.</p>
            
            <p>Please review the application in the admin dashboard:</p>
            
            <a href="https://slsupplyhub.com/admin/supplier-applications.php" class="button">
                Review Application
            </a>
            
            <h3>Application Details:</h3>
            <ul>
                <li>Shop Name: {{SHOP_NAME}}</li>
                <li>Submission Date: {{SUBMISSION_DATE}}</li>
            </ul>
            
            <p>Remember to review the application within 2-3 business days.</p>
        </div>
    </div>
</body>
</html>
