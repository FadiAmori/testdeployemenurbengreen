<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Comment Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #70c745;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 20px -30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px 0;
        }
        .statute-title {
            font-size: 20px;
            font-weight: bold;
            color: #70c745;
            margin-bottom: 10px;
        }
        .comment-box {
            background-color: #f9f9f9;
            border-left: 4px solid #70c745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .commenter-info {
            font-weight: bold;
            color: #555;
            margin-bottom: 10px;
        }
        .button {
            display: inline-block;
            background-color: #70c745;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #5fb534;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #777;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 New Comment on Your Statute</h1>
        </div>
        
        <div class="content">
            <p>Hello,</p>
            
            <p>Someone has commented on your statute!</p>
            
            <div class="statute-title">
                📜 {{ $statute->titre }}
            </div>
            
            <div class="comment-box">
                <div class="commenter-info">
                    👤 {{ $commenter->name ?? 'Anonymous' }} wrote:
                </div>
                <p>{{ $comment->description }}</p>
            </div>
            
            <p>
                <a href="{{ url('/statutes') }}" class="button">View Your Statute</a>
            </p>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from UrbanGreen Blog.</p>
            <p>&copy; {{ date('Y') }} UrbanGreen. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
