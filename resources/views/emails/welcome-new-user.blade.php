<x-emails.shell title="Bem-vindo à UniRoad">
    <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;color:#ffffff;">Bem-vindo, {{ $user->name }}!</h1>
    <p style="margin:0 0 18px;font-size:16px;line-height:1.7;color:#dbeafe;">
        Sua conta na UniRoad foi criada com sucesso. A plataforma organiza turmas, alunos e professores em roadmaps visuais para deixar cada etapa do aprendizado mais clara.
    </p>
    <p style="margin:0 0 24px;font-size:15px;line-height:1.7;color:#cbd5e1;">
        Acesse sua área para acompanhar suas turmas, visualizar roadmaps disponíveis e seguir as atividades planejadas pelos professores.
    </p>
    <a href="{{ url('/dashboard') }}" style="display:inline-block;background:#22d3c5;color:#07111f;text-decoration:none;font-weight:800;border-radius:999px;padding:12px 20px;">
        Acessar minha área
    </a>
</x-emails.shell>
