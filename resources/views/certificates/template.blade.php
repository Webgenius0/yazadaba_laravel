<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .certificate-container {
            border: 5px solid #333;
            padding: 40px;
            width: 80%;
            margin: 50px auto;
            background: linear-gradient(to right, #f8f9fa, #e2e3e5);
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .certificate-header {
            font-size: 42px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .user-name {
            font-size: 30px;
            font-weight: bold;
            color: #2980b9;
            margin-top: 20px;
        }

        .course-name {
            font-size: 26px;
            color: #8e44ad;
            margin-top: 10px;
        }

        .footer {
            margin-top: 40px;
            font-size: 18px;
            color: #7f8c8d;
        }

        /* Responsive design */
        @media screen and (max-width: 768px) {
            .certificate-container {
                width: 90%;
                padding: 30px;
            }

            .certificate-header {
                font-size: 32px;
            }

            .user-name {
                font-size: 26px;
            }

            .course-name {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

<div class="certificate-container">
    <div class="certificate-header">
        Certificate of Completion
    </div>

    <div class="user-name">
        Congratulations, {{ $user->name }}!
    </div>

    <div class="course-name">
        You have successfully completed the course: {{ $course->name }}
    </div>

    <div class="footer">

    </div>
</div>
</body>
</html>
