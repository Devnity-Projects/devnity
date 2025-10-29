import { router } from '@inertiajs/vue3';
import { onBeforeUnmount } from 'vue';

declare global {
  interface Window {
    gtag?: (...args: any[]) => void;
    dataLayer?: any[];
  }
}

const ANALYTICS_ID = import.meta.env.VITE_GOOGLE_ANALYTICS_ID;
let unsubscribeNavigation: (() => void) | null = null;

export function useAnalytics() {
  const isAnalyticsReady = () => {
    return ANALYTICS_ID && typeof window.gtag !== 'undefined';
  };

  const waitForAnalytics = (callback: () => void, maxWait = 5000) => {
    const checkInterval = 100;
    let elapsed = 0;
    
    const check = () => {
      if (isAnalyticsReady()) {
        callback();
      } else if (elapsed < maxWait) {
        elapsed += checkInterval;
        setTimeout(check, checkInterval);
      }
    };
    
    check();
  };

  const trackPageView = (url: string) => {
    const sendTrack = () => {
      if (isAnalyticsReady()) {
        window.gtag!('config', ANALYTICS_ID, {
          page_path: url,
          page_title: document.title,
        });
      }
    };

    // Se gtag já existe, rastreia imediatamente
    if (isAnalyticsReady()) {
      sendTrack();
    } else if (ANALYTICS_ID) {
      // Senão, aguarda até 5 segundos para que gtag seja carregado
      waitForAnalytics(sendTrack);
    }
  };

  const trackEvent = (eventName: string, eventData?: Record<string, any>) => {
    const sendEvent = () => {
      if (isAnalyticsReady()) {
        window.gtag!('event', eventName, eventData || {});
      }
    };

    if (isAnalyticsReady()) {
      sendEvent();
    } else if (ANALYTICS_ID) {
      waitForAnalytics(sendEvent);
    }
  };

  const initTracking = () => {
    if (!ANALYTICS_ID) {
      console.warn('Google Analytics ID não configurado. Defina VITE_GOOGLE_ANALYTICS_ID nas variáveis de ambiente.');
      return;
    }

    // Rastrear visualização inicial
    trackPageView(window.location.pathname);

    // Rastrear navegações subsequentes do Inertia
    if (unsubscribeNavigation) {
      unsubscribeNavigation();
    }

    unsubscribeNavigation = router.on('navigate', (event) => {
      trackPageView(event.detail.page.url);
    });
  };

  // Limpar listeners quando o componente desmontar
  onBeforeUnmount(() => {
    if (unsubscribeNavigation) {
      unsubscribeNavigation();
      unsubscribeNavigation = null;
    }
  });

  return {
    trackPageView,
    trackEvent,
    initTracking,
    isAnalyticsReady,
  };
}
