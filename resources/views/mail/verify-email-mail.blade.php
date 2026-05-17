<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Titip.in</title>
    <style>
        @media screen and (max-width: 600px) {
            .main-card {
                width: 100% !important;
            }
            .bola-atas {
                width: 90px !important;
                height: 90px !important;
            }
            .bola-bawah {
                width: 120px !important;
                height: 120px !important;
            }
            .btn-verify {
                width: 100% !important;
                box-sizing: border-box !important;
                padding: 11px 16px !important;
                font-size: 14px !important;
            }
            .content-padding {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
            .content-padding p {
                font-size: 14px !important;
            }
            .greeting {
                font-size: 15px !important;
            }
            .logo {
                font-size: 28px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #F6F4EE; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">

    <div class="bola-atas" style="position: fixed; top: -40px; right: -40px; width: 160px; height: 160px; background-color: #E3E7D3; border-radius: 50%; z-index: 0; pointer-events: none;"></div>

    <div class="bola-bawah" style="position: fixed; bottom: -60px; left: -60px; width: 220px; height: 220px; background-color: #EBE4D5; border-radius: 50%; z-index: 0; pointer-events: none;"></div>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F6F4EE; padding: 40px 20px; min-height: 100vh; box-sizing: border-box;">
        <tr>
            <td align="center">

                <table class="main-card" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 600px; background-color: #FFFFFF; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.04); position: relative; z-index: 10;">

                    <tr>
                        <td style="padding: 40px 40px 20px 40px; text-align: center;">
                            <h1 class="logo" style="margin: 0; font-family: Georgia, 'Times New Roman', serif; font-style: italic; color: #1A1A1A; font-size: 36px; letter-spacing: -0.5px;">
                                Titip<span style="color: #C97A53;">.in</span>
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td class="content-padding" style="padding: 0 40px 30px 40px; color: #4A4A4A; line-height: 1.6; font-size: 16px;">
                            <p class="greeting" style="margin-top: 0; margin-bottom: 20px; color: #1A1A1A; font-size: 18px;">Halo <strong>{{ $name }}</strong>,</p>

                            <p style="margin-bottom: 20px;">Selamat datang di Titip.in! Platform yang menghubungkan kita untuk jasa titip dan barang preloved dengan mudah, tanpa ribet.</p>

                            <p style="margin-bottom: 30px;">Untuk menjaga keamanan transaksi dan mulai menggunakan semua fitur Titip.in, silakan verifikasi alamat emailmu dengan menekan tombol di bawah ini:</p>

                            <div style="text-align: center; margin-bottom: 35px;">
                                <a href="{{ $url }}" class="btn-verify" style="display: inline-block; background-color: #C97A53; color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 50px; font-weight: bold; font-size: 16px; letter-spacing: 0.5px;">
                                    Mulai Sekarang
                                </a>
                            </div>

                            <p style="font-size: 14px; color: #888888; margin-bottom: 8px;">Tombol di atas tidak berfungsi? Copy & paste link berikut di browser kamu:</p>
                            <p style="font-size: 14px; margin-bottom: 30px; word-break: break-all;">
                                <a href="{{ $url }}" style="color: #C97A53;">{{ $url }}</a>
                            </p>

                            <p style="margin: 0;">Salam hangat,<br><strong style="color: #1A1A1A;">Tim Titip.in</strong></p>
                        </td>
                    </tr>

                    <tr>
                        <td class="content-padding" style="background-color: #FAFAFA; padding: 24px 40px; text-align: center; font-size: 13px; color: #999999; border-top: 1px solid #F0F0F0;">
                            <p style="margin: 0 0 8px 0; font-weight: bold; color: #777777;">Titip.in — Platform Jastip & Preloved Terpercaya.</p>
                            <p style="margin: 0;">Email ini dikirim secara otomatis. Jika kamu tidak merasa mendaftar di Titip.in, abaikan saja email ini.</p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>