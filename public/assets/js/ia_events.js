// public/assets/js/ia_events.js

class IAEventClient {
    constructor() {
        this.eventSource = null;
        this.reconnectTimeout = 5000;
        this.soundEnabled = true;
        this.soundMap = {
            'ping': '/assets/sounds/ping.mp3',
            'chime': '/assets/sounds/chime.mp3',
            'bell': '/assets/sounds/bell.mp3'
        };
        this.init();
    }
    
    init() {
        this.connect();
        this.loadConfig();
    }
    
    connect() {
        if (this.eventSource) {
            this.eventSource.close();
        }
        
        const prefix = window.SYS_BASE_URL || '';
        this.eventSource = new EventSource(prefix + '/public_sse.php');
        
        this.eventSource.addEventListener('new_captacoes', (e) => {
            const data = JSON.parse(e.data);
            this.onNewCaptacoes(data);
        });
        
        this.eventSource.onerror = () => {
            console.log('SSE desconectado, reconectando...');
            setTimeout(() => this.connect(), this.reconnectTimeout);
        };
    }
    
    async loadConfig() {
        try {
            const prefix = window.SYS_BASE_URL || '';
            const response = await fetch(prefix + '/licitacoes/api/ia-config', {
                headers: {
                    'Accept': 'application/json'
                }
            });
            const config = await response.json();
            this.soundEnabled = config.sound_alerts_enabled;
        } catch (e) {
            console.error('Erro ao carregar config:', e);
        }
    }
    
    onNewCaptacoes(data) {
        // Atualiza badge do Radar IA
        const radarBadge = document.querySelector('.nav-tab[href*="captacoes"] .pulse-dot');
        if (radarBadge) {
            radarBadge.style.display = 'inline-block';
            radarBadge.style.animation = 'pulse-anim 2s infinite';
        }
        
        // Toca som de notificação
        if (this.soundEnabled && data.sound) {
            this.playSound(data.sound);
        }
        
        // Exibe toast notification
        this.showToast(`${data.count || data.data?.length || 0} nova(s) oportunidade(s) captada(s)!`, 'success');
        
        // Dispara evento personalizado
        window.dispatchEvent(new CustomEvent('ia-new-captacoes', { detail: data }));
        
        // Recarrega lista se estiver na página de captações
        if (window.location.pathname.includes('/captacoes')) {
            setTimeout(() => location.reload(), 2000);
        }
    }
    
    playSound(soundKey) {
        const soundUrl = this.soundMap[soundKey] || this.soundMap['ping'];
        const audio = new Audio(soundUrl);
        audio.volume = 0.3;
        audio.play().catch(e => console.log('Áudio bloqueado:', e));
    }
    
    showToast(message, type = 'info') {
        // Verifica se existe sistema de toast no layout
        const toastEvent = new CustomEvent('show-toast', {
            detail: { message, type, duration: 5000 }
        });
        window.dispatchEvent(toastEvent);
        
        // Fallback: alert
        if (typeof window.showToast !== 'function') {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }
}

// Inicializa quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.iaEvents = new IAEventClient();
});