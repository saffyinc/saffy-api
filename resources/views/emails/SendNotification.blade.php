<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Message</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:20px;">
        <tr>
            <td align="center">

                <!-- Card Container -->
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#4f46e5; color:#ffffff; padding:20px; text-align:center;">
                            <h1 style="margin:0; font-size:24px;">📩 New Contact Message</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px; color:#333333;">

                            <p style="margin:0 0 15px;"><strong>Name:</strong><br>{{ $name }}</p>

                            <p style="margin:0 0 15px;"><strong>Email:</strong><br>{{ $email }}</p>

                            <p style="margin:0 0 15px;"><strong>Subject:</strong><br>{{ $mailSubject }}</p>

                            <p style="margin:0 0 10px;"><strong>Message:</strong></p>
                            <p style="margin:0; padding:15px; background:#f9fafb; border-radius:6px; line-height:1.5;">
                                {{ $contactMessage }}
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9fafb; padding:15px; text-align:center; font-size:12px; color:#888;">
                            This message was sent from your website contact form.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
