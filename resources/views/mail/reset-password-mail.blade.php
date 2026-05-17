<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Titip.in</title>
    <style>
        @media screen and (max-width: 600px) {
            .main-card {
                width: 100% !important;
            }
            .bola-atas {
                width: 100px !important;
                height: 100px !important;
                margin-bottom: -50px !important;
                margin-right: -10px !important;
            }
            .bola-bawah {
                width: 130px !important;
                height: 130px !important;
                margin-top: -60px !important;
                margin-left: -20px !important;
            }
            .btn-verify {
                width: 100% !important;
                box-sizing: border-box !important;
                padding: 12px 20px !important;
                font-size: 15px !important;
            }
            .content-padding {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #F6F4EE; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F6F4EE; padding: 40px 20px; overflow: hidden;">
        <tr>
            <td align="center">
                
                <div style="width: 100%; max-width: 600px; text-align: right; margin-bottom: -80px; position: relative; z-index: 1;">
                    <div class="bola-atas" style="display: inline-block; width: 160px; height: 160px; background-color: #E3E7D3; border-radius: 50%; margin-right: -20px;"></div>
                </div>

                <table class="main-card" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 600px; background-color: #FFFFFF; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.04); position: relative; z-index: 10;">
                    
                    <tr>
                        <td style="padding: 40px 40px 20px 40px; text-align: center;">
                            <h1 style="margin: 0; font-family: Georgia, 'Times New Roman', serif; font-style: italic; color: #1A1A1A; font-size: 36px; letter-spacing: -0.5px;">
                                Titip<span style="color: #C97A53;">.in</span>
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td class="content-padding" style="padding: 0 40px 30px 40px; color: #4A4A4A; line-height: 1.6; font-size: 16px;">
                            <p style="margin-top: 0; margin-bottom: 20px; color: #1A1A1A; font-size: 18px;">Halo <strong>{{ $name }}</strong>,</p>
                            
                            <p style="margin-bottom: 20px;">Kami menerima permintaan untuk mengatur ulang <em>(reset)</em> password akun Titip.in kamu.</p>
                            
                            <p style="margin-bottom: 30px;">Yuk, klik tombol di bawah ini untuk membuat password baru. Ingat, link ini hanya berlaku selama <strong>60 menit</strong> ke depan.</p>

                            <div style="text-align: center; margin-bottom: 35px;">
                                <a href="{{ $url }}" class="btn-verify" style="display: inline-block; background-color: #C97A53; color: #FFFFFF; text-decoration: none; padding: 14px 32px; border-radius: 50px; font-weight: bold; font-size: 16px; letter-spacing: 0.5px;">
                                    Atur Ulang Password
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
                            <p style="margin: 0;">Jika kamu tidak merasa meminta reset password, abaikan saja email ini. Akunmu tetap aman bersama kami.</p>
                        </td>
                    </tr>
                    
                </table>

                <div style="width: 100%; max-width: 600px; text-align: left; margin-top: -100px; position: relative; z-index: 1;">
                    <div class="bola-bawah" style="display: inline-block; width: 220px; height: 220px; background-color: #EBE4D5; border-radius: 50%; margin-left: -40px;"></div>
                </div>

            </td>
        </tr>
    </table>

</body>
</html>