<!DOCTYPE html>
<html>
<head>
    <title>Account Deletion Confirmation</title>
    <style>
        .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-align: center;
            display: inline-block;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>Account Deletion Request</h1>
    <p>We received a request to delete your account. If this was you, please click the link below to confirm the deletion:</p>
    
    <p>
        <a href="{{ $deleteUrl }}" class="button">Confirm Account Deletion</a>
    </p>
    
    <p>If you didn't request this, please ignore this email.</p>
    
    <hr>
    <p>Thank you for being part of our community!</p>
</body>
</html>