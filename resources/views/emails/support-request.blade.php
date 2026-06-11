<x-emails.shell title="Nova solicitação de suporte">
    <h1 style="margin:0 0 16px;font-size:26px;line-height:1.2;color:#ffffff;">Nova solicitação de suporte</h1>
    <p style="margin:0 0 18px;font-size:16px;line-height:1.7;color:#dbeafe;">
        Um usuário autenticado solicitou ajuda pelo painel da UniRoad.
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;background:rgba(15,23,42,0.78);border:1px solid rgba(148,163,184,0.18);border-radius:8px;overflow:hidden;">
        <tr>
            <td style="padding:12px 14px;color:#93a4c6;font-size:13px;">Nome</td>
            <td style="padding:12px 14px;color:#ffffff;font-size:14px;">{{ $payload['name'] }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;color:#93a4c6;font-size:13px;border-top:1px solid rgba(148,163,184,0.12);">E-mail</td>
            <td style="padding:12px 14px;color:#ffffff;font-size:14px;border-top:1px solid rgba(148,163,184,0.12);">{{ $payload['email'] }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;color:#93a4c6;font-size:13px;border-top:1px solid rgba(148,163,184,0.12);">Horário</td>
            <td style="padding:12px 14px;color:#ffffff;font-size:14px;border-top:1px solid rgba(148,163,184,0.12);">{{ $payload['sent_at']->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;color:#93a4c6;font-size:13px;border-top:1px solid rgba(148,163,184,0.12);">Navegador</td>
            <td style="padding:12px 14px;color:#ffffff;font-size:14px;border-top:1px solid rgba(148,163,184,0.12);">{{ $payload['user_agent'] }}</td>
        </tr>
    </table>

    <div style="margin-top:18px;padding:16px;background:rgba(14,165,233,0.12);border:1px solid rgba(103,232,249,0.24);border-radius:10px;color:#e0f2fe;font-size:15px;line-height:1.7;">
        {{ $payload['description'] }}
    </div>
</x-emails.shell>
