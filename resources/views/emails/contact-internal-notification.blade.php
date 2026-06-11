<x-emails.shell title="Novo contato comercial">
    <h1 style="margin:0 0 16px;font-size:26px;line-height:1.2;color:#ffffff;">Novo contato comercial</h1>
    <p style="margin:0 0 18px;font-size:16px;line-height:1.7;color:#dbeafe;">
        Um interessado preencheu o formulário de contato da UniRoad.
    </p>
    <table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;background:rgba(15,23,42,0.78);border:1px solid rgba(148,163,184,0.18);border-radius:8px;overflow:hidden;">
        <tr>
            <td style="padding:12px 14px;color:#93a4c6;font-size:13px;">Nome</td>
            <td style="padding:12px 14px;color:#ffffff;font-size:14px;">{{ $lead->name }}</td>
        </tr>
        <tr>
            <td style="padding:12px 14px;color:#93a4c6;font-size:13px;border-top:1px solid rgba(148,163,184,0.12);">E-mail</td>
            <td style="padding:12px 14px;color:#ffffff;font-size:14px;border-top:1px solid rgba(148,163,184,0.12);">{{ $lead->email }}</td>
        </tr>
    </table>
</x-emails.shell>
