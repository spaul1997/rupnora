@props([
    'preheader' => '',
    'accent' => '#6144ac',
])
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>{{ $title ?? 'Rupnora' }}</title>
<!--[if mso]>
<noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
<![endif]-->
<style>
    body, table, td { font-family: Arial, Helvetica, sans-serif; }
    img { border: 0; outline: none; text-decoration: none; }
    a { text-decoration: none; }
    @media (max-width: 620px) {
        .container { width: 100% !important; }
        .content-pad { padding-left: 24px !important; padding-right: 24px !important; }
    }
</style>
</head>
<body style="margin:0; padding:0; background-color:#f6f3f9;">
<div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">
    {{ $preheader }}&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f3f9;">
    <tr>
        <td align="center" style="padding:32px 16px;">

            <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:600px; background-color:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #e3e3e3;">
                <tr>
                    <td style="height:4px; line-height:4px; font-size:0; background-color:{{ $accent }};">&nbsp;</td>
                </tr>
                <!-- <tr>
                    <td align="center" style="padding:32px 24px 4px;">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('logo.png') }}" width="130" alt="Rupnora" style="display:block; height:auto; width:130px; max-width:130px;">
                        </a>
                    </td>
                </tr> -->
                <tr>
                    <td class="content-pad" style="padding:12px 40px 40px;">
                        {{ $slot }}
                    </td>
                </tr>
            </table>

            <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:600px;">
                <tr>
                    <td align="center" style="padding:24px 24px 0; font-family:Arial,sans-serif; font-size:12px; line-height:19px; color:#9e9fa5;">
                        <p style="margin:0 0 6px;">Certified Jewellery &middot; Easy Returns &middot; Secure Payments</p>
                        <p style="margin:0 0 6px;">Questions? Write to <a href="mailto:support@rupnora.in" style="color:#6144ac;">support@rupnora.in</a></p>
                        <p style="margin:0;">&copy; {{ date('Y') }} Rupnora Jewellery. All rights reserved.</p>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>
</body>
</html>
