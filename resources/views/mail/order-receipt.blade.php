<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Brightside receipt</title>
</head>
<body style="margin:0;padding:0;background:#f3efe7;color:#171a2b;font-family:'Instrument Sans',Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3efe7;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fffdf8;border:1px solid #e3ddd1;border-radius:20px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 32px 18px;">
                            <div style="font-size:22px;font-weight:700;letter-spacing:-0.03em;color:#2547d0;">brightside<span style="color:#171a2b;">.</span></div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px;">
                            <h1 style="margin:0 0 8px;font-size:30px;line-height:1.15;letter-spacing:-0.03em;">You're in. Happy learning.</h1>
                            <p style="margin:0;color:#5c6072;font-size:15px;line-height:1.6;">
                                Thanks for enrolling in <strong style="color:#171a2b;">{{ $order->course_title }}</strong>.
                                Here is your receipt.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8f5ee;border-radius:14px;padding:18px 20px;">
                                <tr>
                                    <td style="padding:5px 0;color:#6b6f80;font-size:13px;">Order number</td>
                                    <td align="right" style="padding:5px 0;font-size:13px;font-weight:600;">{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;color:#6b6f80;font-size:13px;">Course</td>
                                    <td align="right" style="padding:5px 0;font-size:13px;font-weight:600;">{{ $order->course_title }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:5px 0;color:#6b6f80;font-size:13px;">Date</td>
                                    <td align="right" style="padding:5px 0;font-size:13px;font-weight:600;">{{ $paidAt }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 0 0;border-top:1px solid #e3ddd1;color:#6b6f80;font-size:13px;">Total paid</td>
                                    <td align="right" style="padding:12px 0 0;border-top:1px solid #e3ddd1;font-size:18px;font-weight:700;">{{ $amount }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 28px;">
                            <a href="{{ $orderUrl }}" style="display:inline-block;background:#2547d0;color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;padding:12px 20px;border-radius:10px;">
                                View receipt &amp; start learning
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;background:#f8f5ee;border-top:1px solid #e3ddd1;color:#6b6f80;font-size:12px;line-height:1.6;">
                            Questions? Reply to this email or contact
                            <a href="mailto:{{ $supportEmail }}" style="color:#2547d0;">{{ $supportEmail }}</a>.
                            <br>Brightside · Keep learning, keep shining.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
