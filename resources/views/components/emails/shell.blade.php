@props(['title'])

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;background:#080a10;color:#f8fafc;font-family:Inter,Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#080a10;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#111520;border:1px solid rgba(148,163,184,0.22);border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 32px;border-bottom:1px solid rgba(148,163,184,0.16);">
                            <div style="font-size:22px;font-weight:800;color:#ffffff;">UniRoad</div>
                            <div style="margin-top:6px;font-size:13px;color:#93a4c6;">Roadmaps claros para jornadas de aprendizado.</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            {{ $slot }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;border-top:1px solid rgba(148,163,184,0.16);font-size:12px;color:#93a4c6;">
                            Esta mensagem foi enviada pela UniRoad. Seus dados são tratados com segurança.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
