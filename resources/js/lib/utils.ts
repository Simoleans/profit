import { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function urlIsActive(urlToCheck: NonNullable<InertiaLinkProps['href']>, currentUrl: string) {
    const checkUrl = toUrl(urlToCheck);

    // Comparación exacta para la página de inicio
    if (checkUrl === '/') {
        return currentUrl === '/';
    }

    // Para otras rutas, verificar si la URL actual comienza con la URL a verificar
    return currentUrl.startsWith(checkUrl);
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}
