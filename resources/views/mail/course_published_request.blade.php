<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Course Publish Request</title>
    <style>
        table,
        td,
        div,
        h1,
        p {
            font-family: Arial, sans-serif;
        }

        .button {
            background-color: #70bbd9;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            display: inline-block;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>

<body style="margin:0;padding:0;">
    <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
        <tr>
            <td align="center" style="padding:0;">
                <table role="presentation"
                    style="width:602px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;">
                    <tr>
                        <td style="padding:36px 30px 42px 30px;">
                            <table role="presentation"
                                style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
                                <tr>
                                    <td style="padding:0 0 36px 0;color:#153643;">
                                        <h1 style="font-size:24px;margin:0 0 20px 0;font-family:Arial,sans-serif;">
                                            Course Publish Request for {{ $courseName }}</h1>
                                        <p
                                            style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                            Hello {{ $userName }},</p>
                                        <p
                                            style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                            Your request to publish the course <strong>{{ $courseName }}</strong> has
                                            been received.</p>
                                        <p
                                            style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                            Please find the course details below:</p>
                                        <table role="presentation"
                                            style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
                                            <tr>
                                                <td
                                                    style="width:30%;padding:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                                    <strong>Course Name:</strong>
                                                </td>
                                                <td
                                                    style="width:70%;padding:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                                    {{ $courseName }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="width:30%;padding:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                                    <strong>Course Description:</strong>
                                                </td>
                                                <td
                                                    style="width:70%;padding:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                                    {{ $courseDescription }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="width:30%;padding:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                                    <strong>Course Price:</strong>
                                                </td>
                                                <td
                                                    style="width:70%;padding:0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;">
                                                    {{ $coursePrice }}</td>
                                            </tr>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px;background:#ee4c50;">
                            <table role="presentation"
                                style="width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;">
                                <tr>
                                    <td style="padding:0;width:50%;" align="left">
                                        <p
                                            style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">
                                            <a href="http://www.example.com"
                                                style="color:#ffffff;text-decoration:underline;">
                                                Email: {{ $userEmail }}<br />
                                                Current Time: {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}
                                            </a>
                                        </p>
                                    </td>

                                    <td style="padding:0;width:50%;" align="right">
                                        <table role="presentation"
                                            style="border-collapse:collapse;border:0;border-spacing:0;">
                                            <tr>
                                                <td style="padding:0 0 0 10px;width:38px;">
                                                    <a href="http://www.twitter.com/" style="color:#ffffff;">
                                                        <img src="https://assets.codepen.io/210284/tw_1.png"
                                                            alt="Twitter" width="38"
                                                            style="height:auto;display:block;border:0;" />
                                                    </a>
                                                </td>
                                                <td style="padding:0 0 0 10px;width:38px;">
                                                    <a href="http://www.facebook.com/" style="color:#ffffff;">
                                                        <img src="https://assets.codepen.io/210284/fb_1.png"
                                                            alt="Facebook" width="38"
                                                            style="height:auto;display:block;border:0;" />
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <p style="font-size:0.9em;">Regards,<br />{{ config('app.name') }}</p>
                                    <hr style="border:none;border-top:1px solid #eee" />
                                    <div
                                        style="float:right;padding:8px 0;color:#aaa;font-size:0.8em;line-height:1;font-weight:300">
                                        <p>{{ config('app.name') }} Inc</p>
                                        <p>1600 Amphitheatre Parkway</p>
                                        <p>California</p>
                                    </div>

                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
