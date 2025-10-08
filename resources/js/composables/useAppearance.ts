import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark' | 'system';

// CÓDIGO ORIGINAL COMENTADO - Sistema de temas dinámico
// export function updateTheme(value: Appearance) {
//     if (typeof window === 'undefined') {
//         return;
//     }

//     if (value === 'system') {
//         const mediaQueryList = window.matchMedia('(prefers-color-scheme: dark)');
//         const systemTheme = mediaQueryList.matches ? 'dark' : 'light';

//         document.documentElement.classList.toggle('dark', systemTheme === 'dark');
//     } else {
//         document.documentElement.classList.toggle('dark', value === 'dark');
//     }
// }

// NUEVO CÓDIGO - Siempre forzar tema claro
export function updateTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Siempre forzar tema claro
    document.documentElement.classList.remove('dark');
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

// CÓDIGO ORIGINAL COMENTADO - Funciones para detectar tema del sistema
// const mediaQuery = () => {
//     if (typeof window === 'undefined') {
//         return null;
//     }

//     return window.matchMedia('(prefers-color-scheme: dark)');
// };

// const getStoredAppearance = () => {
//     if (typeof window === 'undefined') {
//         return null;
//     }

//     return localStorage.getItem('appearance') as Appearance | null;
// };

// const handleSystemThemeChange = () => {
//     const currentAppearance = getStoredAppearance();

//     updateTheme(currentAppearance || 'system');
// };

// CÓDIGO ORIGINAL COMENTADO - Inicialización con tema guardado o del sistema
// export function initializeTheme() {
//     if (typeof window === 'undefined') {
//         return;
//     }

//     // Initialize theme from saved preference or default to system...
//     const savedAppearance = getStoredAppearance();
//     updateTheme(savedAppearance || 'system');

//     // Set up system theme change listener...
//     mediaQuery()?.addEventListener('change', handleSystemThemeChange);
// }

// NUEVO CÓDIGO - Siempre inicializar con tema claro
export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Siempre inicializar con tema claro
    updateTheme();
}

// CÓDIGO ORIGINAL COMENTADO - Manejo dinámico de apariencia
// const appearance = ref<Appearance>('system');

// export function useAppearance() {
//     onMounted(() => {
//         const savedAppearance = localStorage.getItem('appearance') as Appearance | null;

//         if (savedAppearance) {
//             appearance.value = savedAppearance;
//         }
//     });

//     function updateAppearance(value: Appearance) {
//         appearance.value = value;

//         // Store in localStorage for client-side persistence...
//         localStorage.setItem('appearance', value);

//         // Store in cookie for SSR...
//         setCookie('appearance', value);

//         updateTheme(value);
//     }

//     return {
//         appearance,
//         updateAppearance,
//     };
// }

// NUEVO CÓDIGO - Siempre forzar tema claro
const appearance = ref<Appearance>('light');

export function useAppearance() {
    onMounted(() => {
        // Siempre forzar tema claro
        appearance.value = 'light';
    });

    function updateAppearance() {
        // Siempre forzar tema claro
        appearance.value = 'light';

        // Store in localStorage for client-side persistence...
        localStorage.setItem('appearance', 'light');

        // Store in cookie for SSR...
        setCookie('appearance', 'light');

        updateTheme();
    }

    return {
        appearance,
        updateAppearance,
    };
}
