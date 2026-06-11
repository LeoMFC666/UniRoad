<x-emails.shell title="Código de verificação">
    <h1 style="margin:0 0 14px;color:#ffffff;font-size:26px;line-height:1.2;">Redefinição de senha</h1>

    <p style="margin:0 0 18px;color:#cbd5e1;font-size:15px;line-height:1.6;">
        Olá, {{ $user->name }}. Recebemos uma solicitação para redefinir a senha da sua conta na UniRoad.
    </p>

    <p style="margin:0 0 16px;color:#cbd5e1;font-size:15px;line-height:1.6;">
        Use o código abaixo para confirmar que este e-mail pertence a você:
    </p>

    <div style="margin:22px 0;padding:18px 22px;border-radius:12px;background:#071225;border:1px solid rgba(34,211,238,0.45);text-align:center;">
        <div style="font-size:34px;letter-spacing:8px;font-weight:800;color:#22d3ee;">{{ $code }}</div>
    </div>

    <p style="margin:0 0 12px;color:#93a4c6;font-size:14px;line-height:1.6;">
        O código expira em {{ $expiresInMinutes }} minutos. Se você não pediu essa alteração, ignore esta mensagem.
    </p>
</x-emails.shell>
