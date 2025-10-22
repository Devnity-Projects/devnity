<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada - Devnity</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
        
        .number-404 {
            font-size: 12rem;
            line-height: 1;
            background: linear-gradient(45deg, #fff, #e5e7eb);
            -webkit-background-clip: text;
            -webkit-text-stroke: 4px transparent;
            color: transparent;
            text-shadow: 0 0 30px rgba(255,255,255,0.5);
        }
        
        @media (max-width: 768px) {
            .number-404 {
                font-size: 8rem;
            }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-4xl mx-auto">
        <!-- Main Content Container -->
        <div class="glass-effect rounded-3xl p-8 md:p-12 shadow-2xl">
            <!-- 404 Number -->
            <div class="mb-8">
                <div class="animate-float">
                    <h1 class="number-404 font-black">404</h1>
                </div>
            </div>
            
            <!-- Title -->
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-4">
                Página não encontrada
            </h2>
            
            <!-- Description -->
            <div class="bg-white/10 rounded-2xl p-6 mb-8 backdrop-blur-sm">
                <p class="text-white/80 text-lg leading-relaxed mb-4">
                    Oops! A página que você está procurando não existe ou foi movida.
                </p>
                <p class="text-white/70 text-base">
                    Verifique se o endereço está correto ou navegue de volta para a página inicial.
                </p>
            </div>
            
            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
                <a href="/" class="bg-white/20 hover:bg-white/30 text-white px-8 py-3 rounded-xl font-medium transition-all duration-300 backdrop-blur-sm border border-white/20 hover:border-white/40">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Voltar ao Início
                </a>
                
                <button onclick="history.back()" class="bg-transparent hover:bg-white/10 text-white/80 hover:text-white px-8 py-3 rounded-xl font-medium transition-all duration-300 border border-white/30 hover:border-white/50">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Voltar
                </button>
            </div>
            
            <!-- Suggestions -->
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                <h3 class="text-white font-medium text-lg mb-3">Sugestões:</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                    <a href="/projects" class="flex items-center gap-3 text-white/80 hover:text-white p-3 rounded-lg hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Projetos</div>
                            <div class="text-sm text-white/60">Gerencie seus projetos</div>
                        </div>
                    </a>
                    
                    <a href="/tasks" class="flex items-center gap-3 text-white/80 hover:text-white p-3 rounded-lg hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Tarefas</div>
                            <div class="text-sm text-white/60">Organize suas atividades</div>
                        </div>
                    </a>
                    
                    <a href="/clients" class="flex items-center gap-3 text-white/80 hover:text-white p-3 rounded-lg hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Clientes</div>
                            <div class="text-sm text-white/60">Gerencie seus clientes</div>
                        </div>
                    </a>
                    
                    <a href="/dashboard" class="flex items-center gap-3 text-white/80 hover:text-white p-3 rounded-lg hover:bg-white/10 transition-colors">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <div>
                            <div class="font-medium">Dashboard</div>
                            <div class="text-sm text-white/60">Visão geral do sistema</div>
                        </div>
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