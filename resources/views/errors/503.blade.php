<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site em Manutenção - Devnity</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .animate-bounce-slow {
            animation: bounce 2s infinite;
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
        
        .maintenance-icon {
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.1));
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-4xl mx-auto">
        <!-- Main Content Container -->
        <div class="glass-effect rounded-3xl p-8 md:p-12 shadow-2xl">
            <!-- Maintenance Icon -->
            <div class="mb-8">
                <div class="animate-float">
                    <svg class="w-24 h-24 md:w-32 md:h-32 mx-auto text-white maintenance-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Title -->
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-4">
                Site em Manutenção
            </h1>
            
            <!-- Subtitle -->
            <p class="text-xl md:text-2xl text-white/90 mb-8 font-light">
                Estamos atualizando nosso sistema para melhor atendê-lo
            </p>
            
            <!-- Description -->
            <div class="bg-white/10 rounded-2xl p-6 mb-8 backdrop-blur-sm">
                <p class="text-white/80 text-lg leading-relaxed mb-4">
                    Estamos implementando melhorias importantes em nossa plataforma. 
                    Durante este período, o acesso ao sistema estará temporariamente indisponível.
                </p>
                <p class="text-white/70 text-base">
                    Pedimos desculpas pelo inconveniente e agradecemos sua paciência.
                </p>
            </div>
            
            <!-- Status Indicators -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-white/10 rounded-xl p-4 backdrop-blur-sm">
                    <div class="flex items-center justify-center mb-2">
                        <div class="w-3 h-3 bg-yellow-400 rounded-full animate-pulse"></div>
                    </div>
                    <h3 class="text-white font-medium text-sm">Deploy em Progresso</h3>
                    <p class="text-white/70 text-xs mt-1">Atualizando sistema</p>
                </div>
                
                <div class="bg-white/10 rounded-xl p-4 backdrop-blur-sm">
                    <div class="flex items-center justify-center mb-2">
                        <svg class="w-6 h-6 text-blue-400 animate-pulse-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    <h3 class="text-white font-medium text-sm">Processando</h3>
                    <p class="text-white/70 text-xs mt-1">Aplicando mudanças</p>
                </div>
                
                <div class="bg-white/10 rounded-xl p-4 backdrop-blur-sm">
                    <div class="flex items-center justify-center mb-2">
                        <svg class="w-6 h-6 text-green-400 animate-bounce-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-white font-medium text-sm">Finalizando</h3>
                    <p class="text-white/70 text-xs mt-1">Quase pronto!</p>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="bg-white/20 rounded-full h-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-400 to-purple-400 h-full rounded-full animate-pulse" style="width: 75%"></div>
                </div>
                <p class="text-white/60 text-sm mt-2">Progresso estimado: 75%</p>
            </div>
            
            <!-- Contact Info -->
            <div class="bg-white/5 rounded-2xl p-6 border border-white/10">
                <h3 class="text-white font-medium text-lg mb-3">Precisa de ajuda urgente?</h3>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="mailto:suporte@devnity.com.br" class="flex items-center gap-2 text-white/80 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        suporte@devnity.com.br
                    </a>
                    <span class="text-white/40 hidden sm:block">•</span>
                    <a href="tel:+5511999999999" class="flex items-center gap-2 text-white/80 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        (11) 99999-9999
                    </a>
                </div>
            </div>
            
            <!-- Auto Refresh Notice -->
            <div class="mt-6 text-white/50 text-sm">
                <p>Esta página será atualizada automaticamente quando o sistema voltar ao ar.</p>
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
    
    <!-- Auto-refresh script -->
    <script>
        // Auto-refresh a cada 30 segundos
        setTimeout(function() {
            window.location.reload();
        }, 30000);
        
        // Adicionar um indicador visual de refresh
        let refreshCounter = 30;
        const refreshElement = document.createElement('div');
        refreshElement.className = 'fixed bottom-4 right-4 bg-black/20 text-white/60 px-3 py-2 rounded-lg text-xs backdrop-blur-sm';
        refreshElement.innerHTML = `Próxima atualização em <span id="countdown">${refreshCounter}</span>s`;
        document.body.appendChild(refreshElement);
        
        const countdownElement = document.getElementById('countdown');
        const countdownInterval = setInterval(() => {
            refreshCounter--;
            countdownElement.textContent = refreshCounter;
            
            if (refreshCounter <= 0) {
                clearInterval(countdownInterval);
                refreshElement.innerHTML = 'Atualizando...';
            }
        }, 1000);
    </script>
</body>
</html>