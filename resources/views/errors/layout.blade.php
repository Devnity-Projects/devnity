<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro {{ $exception->getStatusCode() ?? 'Desconhecido' }} - Devnity</title>
    <link rel="icon" type="image/jpeg" href="/images/logo.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .error-number {
            font-size: 8rem;
            line-height: 1;
            background: linear-gradient(45deg, #fff, #e5e7eb);
            -webkit-background-clip: text;
            -webkit-text-stroke: 3px transparent;
            color: transparent;
            text-shadow: 0 0 30px rgba(255,255,255,0.5);
        }
        
        @media (max-width: 768px) {
            .error-number {
                font-size: 6rem;
            }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-4xl mx-auto">
        <!-- Main Content Container -->
        <div class="glass-effect rounded-3xl p-8 md:p-12 shadow-2xl">
            <!-- Error Icon -->
            <div class="mb-8">
                <div class="animate-float">
                    <svg class="w-20 h-20 md:w-24 md:h-24 mx-auto text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Error Number -->
            <div class="mb-6">
                <h1 class="error-number font-black">{{ $exception->getStatusCode() ?? '???' }}</h1>
            </div>
            
            <!-- Title -->
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                @switch($exception->getStatusCode() ?? 0)
                    @case(400)
                        Requisição Inválida
                        @break
                    @case(401)
                        Não Autorizado
                        @break
                    @case(403)
                        Acesso Negado
                        @break
                    @case(422)
                        Dados Inválidos
                        @break
                    @case(429)
                        Muitas Tentativas
                        @break
                    @case(502)
                        Gateway Inválido
                        @break
                    @case(503)
                        Serviço Indisponível
                        @break
                    @case(504)
                        Timeout do Gateway
                        @break
                    @default
                        Algo deu errado
                @endswitch
            </h2>
            
            <!-- Description -->
            <div class="bg-white/10 rounded-2xl p-6 mb-8 backdrop-blur-sm">
                <p class="text-white/80 text-lg leading-relaxed mb-4">
                    @switch($exception->getStatusCode() ?? 0)
                        @case(400)
                            A requisição enviada contém dados inválidos ou malformados.
                            @break
                        @case(401)
                            Você precisa fazer login para acessar esta página.
                            @break
                        @case(403)
                            Você não tem permissão para acessar este recurso.
                            @break
                        @case(422)
                            Os dados enviados não puderam ser processados.
                            @break
                        @case(429)
                            Muitas tentativas foram feitas. Tente novamente mais tarde.
                            @break
                        @case(502)
                            O servidor gateway recebeu uma resposta inválida.
                            @break
                        @case(503)
                            O serviço está temporariamente indisponível.
                            @break
                        @case(504)
                            O servidor gateway não respondeu a tempo.
                            @break
                        @default
                            Ocorreu um erro inesperado. Nossa equipe foi notificada.
                    @endswitch
                </p>
                <p class="text-white/70 text-base">
                    @if($exception->getStatusCode() === 401)
                        Faça login e tente novamente.
                    @elseif($exception->getStatusCode() === 403)
                        Entre em contato com o administrador se necessário.
                    @elseif($exception->getStatusCode() === 429)
                        Aguarde alguns minutos antes de tentar novamente.
                    @else
                        Tente recarregar a página ou volte mais tarde.
                    @endif
                </p>
            </div>
            
            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                @if($exception->getStatusCode() === 401)
                    <a href="/login" class="bg-white/20 hover:bg-white/30 text-white px-8 py-3 rounded-xl font-medium transition-all duration-300 backdrop-blur-sm border border-white/20 hover:border-white/40">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        Fazer Login
                    </a>
                @else
                    <button onclick="window.location.reload()" class="bg-white/20 hover:bg-white/30 text-white px-8 py-3 rounded-xl font-medium transition-all duration-300 backdrop-blur-sm border border-white/20 hover:border-white/40">
                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Tentar Novamente
                    </button>
                @endif
                
                <a href="/" class="bg-transparent hover:bg-white/10 text-white/80 hover:text-white px-8 py-3 rounded-xl font-medium transition-all duration-300 border border-white/30 hover:border-white/50">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Voltar ao Início
                </a>
            </div>
            
            <!-- Error Details (only in development) -->
            @if(config('app.debug') && $exception->getMessage())
                <div class="bg-yellow-500/20 border border-yellow-400/30 rounded-2xl p-6 mb-8 backdrop-blur-sm text-left">
                    <h3 class="text-white font-medium text-lg mb-3 text-center">Detalhes do Erro (Desenvolvimento)</h3>
                    <div class="bg-black/20 rounded-lg p-4 overflow-auto">
                        <pre class="text-white/90 text-sm whitespace-pre-wrap">{{ $exception->getMessage() }}</pre>
                    </div>
                </div>
            @endif
            
            <!-- Help Section -->
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                <h3 class="text-white font-medium text-lg mb-4">Precisa de ajuda?</h3>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="mailto:suporte@devnity.com.br" class="flex items-center gap-2 text-white/80 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        suporte@devnity.com.br
                    </a>
                    <span class="text-white/40 hidden sm:block">•</span>
                    <a href="/dashboard" class="flex items-center gap-2 text-white/80 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Dashboard
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center">
            <div class="flex items-center justify-center gap-2 text-white/60 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
                <span class="font-semibold">Devnity</span>
                <span>•</span>
                <span>Soluções em Desenvolvimento</span>
            </div>
            <p class="text-white/40 text-xs mt-2">
                © {{ date('Y') }} Devnity. Todos os direitos reservados.
            </p>
        </div>
    </div>
</body>
</html>