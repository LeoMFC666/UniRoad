<x-emails.shell title="Verifique sua conta na UniRoad">
    <h1 style="margin:0 0 14px;color:#ffffff;font-size:26px;line-height:1.2;">Confirme seu e-mail</h1>

    <p style="margin:0 0 18px;color:#cbd5e1;font-size:15px;line-height:1.6;">
        Olá, {{ $user->name }}. Sua conta na UniRoad foi criada com sucesso.
    </p>

    <p style="margin:0 0 22px;color:#cbd5e1;font-size:15px;line-height:1.6;">
        Para proteger seu acesso, confirme que este e-mail pertence a você.
    </p>

    <p style="margin:0 0 26px;">
        <a href="{{ $verificationUrl }}" style="display:inline-block;border-radius:999px;background:#22d3ee;color:#020617;font-size:14px;font-weight:800;text-decoration:none;padding:13px 22px;">
            Verificar minha conta
        </a>
    </p>

    <p style="margin:0;color:#93a4c6;font-size:13px;line-height:1.6;">
        Se você não criou uma conta na UniRoad, ignore esta mensagem.
    </p>
</x-emails.shell>
