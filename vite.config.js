import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const port = Number(env.VITE_PORT || 5173);
    const appUrl = new URL(env.APP_URL || 'http://localhost:8000');
    const loopbackHosts = ['localhost', '127.0.0.1', '[::1]'];
    const corsOrigins = loopbackHosts.includes(appUrl.hostname)
        ? loopbackHosts.map((hostname) => {
            const url = new URL(appUrl);
            url.hostname = hostname;
            return url.origin;
        })
        : [appUrl.origin];

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
                fonts: [
                    bunny('Instrument Sans', {
                        weights: [400, 500, 600],
                    }),
                ],
            }),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0',
            port,
            strictPort: true,
            cors: {
                origin: corsOrigins,
            },
            hmr: {
                host: env.VITE_HMR_HOST || appUrl.hostname,
                clientPort: port,
            },
            watch: {
                ignored: ['**/storage/framework/views/**'],
                usePolling: env.VITE_USE_POLLING === 'true',
            },
        },
    };
});
