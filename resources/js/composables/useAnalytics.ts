import { router } from '@inertiajs/vue3';

declare global {
  interface Window {
    gtag?: (...args: any[]) => void;
  }
}

export function useAnalytics() {
  const trackPageView = (url: string) => {
    if (typeof window.gtag !== 'undefined') {
      window.gtag('config', import.meta.env.VITE_GOOGLE_ANALYTICS_ID, {
        page_path: url,
      });
    }
  };

  const initTracking = () => {
    // Track initial page view
    trackPageView(window.location.pathname);

    // Track subsequent Inertia page navigations
    router.on('navigate', (event) => {
      trackPageView(event.detail.page.url);
    });
  };

  return {
    trackPageView,
    initTracking,
  };
}
