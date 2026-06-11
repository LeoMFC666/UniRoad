@if(session('success'))
    <div class="mx-4 mt-3 rounded-xl border border-emerald-300/25 bg-emerald-400/10 px-4 py-3 text-sm font-medium text-emerald-100 shadow-lg shadow-emerald-950/20 sm:mx-6">
        {{ session('success') }}
    </div>
@endif

@if(session('status') && session('status') !== 'verification-link-sent' && session('status') !== 'profile-updated' && session('status') !== 'password-updated')
    <div class="mx-4 mt-3 rounded-xl border border-cyan-300/25 bg-cyan-400/10 px-4 py-3 text-sm font-medium text-cyan-100 shadow-lg shadow-cyan-950/20 sm:mx-6">
        {{ session('status') }}
    </div>
@endif
