<p align="center">
    <img src="./public/img/logo.png" width="220" alt="Logo UniRoad">
</p>

<h1 align="center">UniRoad</h1>

<p align="center">
    Plataforma de gerenciamento de estudos com criação e visualização de roadmaps educacionais.
</p>

<p align="center">
    <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
    <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
    <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
    <img src="https://img.shields.io/badge/Blade-Templates-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Blade">
</p>

<p align="center">
    <img src="https://img.shields.io/badge/JavaScript-Editor-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
    <img src="https://img.shields.io/badge/Vite-Build-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
    <img src="https://img.shields.io/badge/Composer-Dependencies-885630?style=for-the-badge&logo=composer&logoColor=white" alt="Composer">
    <img src="https://img.shields.io/badge/NPM-Packages-CB3837?style=for-the-badge&logo=npm&logoColor=white" alt="NPM">
</p>

---

## Sobre a UniRoad

A **UniRoad** é uma plataforma de gerenciamento de estudos voltada para instituições de ensino. A aplicação organiza alunos, professores, administradores e turmas, tendo como principal recurso a criação de **roadmaps educacionais**.

A proposta da plataforma é permitir que professores criem planos de estudo visuais e estruturados, ajudando alunos a acompanharem melhor sua jornada de aprendizado.

---

## Funcionalidades

### Administrador

- Gerencia usuários do sistema.
- Gerencia alunos, professores e turmas.
- Visualiza turmas e roadmaps.
- Pode excluir roadmaps.
- Não pode criar ou editar roadmaps.

### Professor

- Acessa suas turmas.
- Adiciona e remove alunos de suas turmas.
- Cria, edita e gerencia roadmaps.
- Organiza conteúdos em etapas, blocos e conexões visuais.

### Aluno

- Acessa apenas as turmas em que está matriculado.
- Visualiza roadmaps disponíveis.
- Acompanha os planos de estudo criados pelos professores.

---

## Principais recursos

- Autenticação de usuários.
- Controle de permissões por tipo de usuário.
- Dashboard por perfil.
- Gerenciamento de turmas.
- Gerenciamento de alunos e professores.
- Editor visual de roadmaps.
- Visualizador de roadmaps.
- Página institucional.
- Página de contato.
- Estrutura preparada para envio de e-mails.
- Validações e reforços de segurança no backend.

---

## Tecnologias utilizadas

| Tecnologia | Uso na UniRoad |
|---|---|
| **Laravel** | Framework principal da aplicação |
| **PHP** | Linguagem backend |
| **Blade** | Templates das interfaces |
| **MySQL** | Banco de dados |
| **JavaScript** | Interações do editor visual |
| **Vite** | Build dos assets |
| **Composer** | Gerenciamento de dependências PHP |
| **NPM** | Gerenciamento de dependências frontend |
| **ArtisanFlow / AlpineFlow** | Componentes e fluxos visuais do editor |

---

## Instalação local

Clone o repositório:

```bash
git clone https://github.com/LeoMFC666/UniRoad.git
```

Entre na pasta do projeto:

```bash
cd UniRoad
```

Instale as dependências PHP:

```bash
composer install
```

Instale as dependências frontend:

```bash
npm install
```

Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

Gere a chave da aplicação:

```bash
php artisan key:generate
```

Configure o banco de dados no `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uniroad
DB_USERNAME=root
DB_PASSWORD=
```

Execute as migrations:

```bash
php artisan migrate
```

Compile os assets:

```bash
npm run dev
```

Inicie o servidor local:

```bash
php artisan serve
```

Acesse:

```txt
http://127.0.0.1:8000
```

---

## Configuração de e-mail

A UniRoad possui estrutura preparada para envio de e-mails com Laravel Mail.

Exemplo de configuração com Resend:

```env
MAIL_MAILER=resend
RESEND_API_KEY=
MAIL_FROM_ADDRESS=onboarding@resend.dev
MAIL_FROM_NAME="UniRoad"

CONTACT_NOTIFICATION_EMAIL=
EMAIL_EXTERNAL_SENDING_ENABLED=false
```



---

## Segurança

A UniRoad possui medidas de segurança aplicadas, incluindo:

- Validação de dados no backend.
- Controle de permissões por perfil.
- Proteção contra acesso indevido por URL manual.
- Sanitização de textos salvos nos roadmaps.
- Limite de tamanho e estrutura para JSON de roadmaps.
- Rate limit em rotas sensíveis.
- Proteção CSRF nos formulários.
- Senhas armazenadas com hash.
- Logs sem exposição desnecessária de dados pessoais.
- Separação de permissões entre administrador, professor e aluno.

---

## Testes

Execute os testes com:

```bash
php artisan test
```

Limpe caches e configurações com:

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

---

## Estrutura de permissões

```txt
Administrador
 ├── Gerencia usuários
 ├── Gerencia professores
 ├── Gerencia alunos
 ├── Gerencia turmas
 └── Visualiza e exclui roadmaps

Professor
 ├── Acessa suas turmas
 ├── Gerencia alunos da turma
 ├── Cria roadmaps
 ├── Edita roadmaps
 └── Organiza planos de estudo

Aluno
 ├── Acessa turmas matriculadas
 └── Visualiza roadmaps disponíveis
```

---

## Objetivo do projeto

A UniRoad foi desenvolvida para facilitar a organização dos estudos em ambientes acadêmicos, oferecendo uma plataforma visual, moderna e intuitiva para criação de roadmaps educacionais.

A ferramenta busca aproximar professores e alunos por meio de planos de estudo mais claros, organizados e fáceis de acompanhar.

---

## Status

Projeto em desenvolvimento.

Funcionalidades principais já implementadas:

- Autenticação.
- Perfis de usuário.
- Gerenciamento de turmas.
- Editor de roadmaps.
- Visualizador de roadmaps.
- Página de contato.
- Estrutura de e-mails.
- Reforços de segurança.

---

## Autor

Desenvolvido por **Leonardo M Casoti** e **Nicolas Oliveira Pacheco**.
