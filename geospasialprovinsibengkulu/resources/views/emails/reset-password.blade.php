<!DOCTYPE html>
<html lang="id" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Reset Kata Sandi - Geoportal Provinsi Bengkulu</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: #f3f4f6;
            color: #374151;
            line-height: 1.6;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }

        /* Header */
        .email-header {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 50%, #ef4444 100%);
            padding: 40px 32px;
            text-align: center;
        }
        .email-header .logo-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 16px;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.25));
        }
        .email-header h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .email-header p {
            color: rgba(255,255,255,0.80);
            font-size: 13px;
            margin-top: 4px;
        }

        /* Body */
        .email-body {
            padding: 40px 32px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 16px;
        }
        .message-text {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 12px;
            line-height: 1.7;
        }

        /* CTA Button */
        .cta-wrapper {
            text-align: center;
            margin: 32px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #ffffff !important;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            padding: 14px 36px;
            border-radius: 10px;
            letter-spacing: 0.2px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
            transition: all 0.2s ease;
        }

        /* Info Box */
        .info-box {
            background: #fef9c3;
            border: 1px solid #fde047;
            border-radius: 10px;
            padding: 16px 20px;
            margin: 24px 0;
        }
        .info-box p {
            font-size: 13px;
            color: #854d0e;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .info-box p svg {
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* URL Fallback */
        .url-fallback {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 20px 0;
            word-break: break-all;
            font-size: 12px;
            color: #6b7280;
        }
        .url-fallback strong {
            display: block;
            font-size: 12px;
            color: #374151;
            margin-bottom: 6px;
        }

        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #f3f4f6;
            margin: 24px 0;
        }

        /* Footer */
        .email-footer {
            background: #f9fafb;
            border-top: 1px solid #f3f4f6;
            padding: 24px 32px;
            text-align: center;
        }
        .email-footer p {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.6;
        }
        .email-footer .footer-brand {
            font-size: 13px;
            font-weight: 600;
            color: #dc2626;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">

        <!-- HEADER -->
        <div class="email-header">
            <img src="{{ asset('Logo Provinsi Bengkulu.png') }}" alt="Logo Geoportal Provinsi Bengkulu" class="logo-img" />
            <h1>Reset Kata Sandi</h1>
            <p>Geoportal Provinsi Bengkulu</p>
        </div>

        <!-- BODY -->
        <div class="email-body">
            <p class="greeting">Halo, {{ $userName }}!</p>

            <p class="message-text">
                Kami menerima permintaan untuk mereset kata sandi akun Anda di
                <strong>Geoportal Provinsi Bengkulu</strong>.
            </p>

            <p class="message-text">
                Klik tombol di bawah ini untuk membuat kata sandi baru Anda:
            </p>

            <!-- CTA BUTTON -->
            <div class="cta-wrapper">
                <a href="{{ $resetUrl }}" class="cta-button">
                    🔑 &nbsp; Reset Kata Sandi Sekarang
                </a>
            </div>

            <!-- WARNING BOX -->
            <div class="info-box">
                <p>
                    <svg width="16" height="16" fill="none" stroke="#92400e" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Link ini hanya berlaku selama <strong>60 menit</strong>. Setelah itu, Anda perlu meminta link baru.
                </p>
            </div>

            <p class="message-text">
                Jika Anda tidak merasa meminta reset kata sandi, abaikan email ini. Akun Anda tetap aman dan tidak ada perubahan yang terjadi.
            </p>

            <hr class="divider">

            <!-- URL FALLBACK -->
            <div class="url-fallback">
                <strong>Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut di browser Anda:</strong>
                {{ $resetUrl }}
            </div>
        </div>

        <!-- FOOTER -->
        <div class="email-footer">
            <p class="footer-brand">Geoportal Provinsi Bengkulu</p>
            <p>
                Email ini dikirim secara otomatis oleh sistem.<br>
                Jangan balas email ini.
            </p>
            <p style="margin-top: 8px;">
                &copy; {{ date('Y') }} Dinas Komunikasi, Informatika dan Statistik Provinsi Bengkulu
            </p>
        </div>

    </div>
</body>
</html>
