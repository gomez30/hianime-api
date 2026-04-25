import { Hono } from 'hono';
import { cors } from 'hono/cors';
import { logger } from 'hono/logger';
import hiAnimeRoutes from './routes/routes';
import config from './config/config';
import { AppError } from './utils/errors';
import { fail } from './utils/response';
/**
 * Create the core Hono app instance
 * Used by both Vercel (api/index.ts) and Render (index.ts)
 */
export function createApp() {
    const app = new Hono();
    // CORS Configuration
    const origins = config.origin.includes(',')
        ? config.origin.split(',').map(o => o.trim())
        : config.origin === '*'
            ? '*'
            : [config.origin];
    app.use('*', cors({
        origin: origins,
        allowMethods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        allowHeaders: ['Content-Type', 'Authorization', 'X-Requested-With'],
        exposeHeaders: ['Content-Length', 'X-Request-Id'],
        maxAge: 600,
        credentials: true,
    }));
    // Logging Middleware
    if (!config.isProduction || config.enableLogging) {
        app.use('/api/v2/*', logger());
    }
    // Root endpoint - API info
    app.get('/', (c) => {
        return c.json({
            status: 'ok',
            message: 'HiAnime API v2',
            version: '2.2.0',
            environment: config.environment,
            endpoints: {
                health: '/ping',
                home: '/api/v2/home',
                search: '/api/v2/search',
                anime: '/api/v2/anime/:id',
                characters: '/api/v2/characters/:id',
                episodes: '/api/v2/episodes/:id',
                genres: '/api/v2/genres',
                news: '/api/v2/news',
            },
        });
    });
    // Health Check
    app.get('/ping', (c) => {
        return c.json({
            status: 'ok',
            timestamp: new Date().toISOString(),
            environment: config.environment,
            uptime: process.uptime(),
        });
    });
    // Favicon (prevent 404 errors)
    app.get('/favicon.ico', (c) => {
        return c.body(null, 204);
    });
    // API Routes
    app.route('/api/v2', hiAnimeRoutes);
    // 404 Handler
    app.notFound((c) => {
        return fail(c, 'Route not found', 404);
    });
    // Error Handler
    app.onError((err, c) => {
        if (err instanceof AppError) {
            return fail(c, err.message, err.statusCode, err.details);
        }
        console.error(`[${config.environment}] Unexpected Error:`, err.message);
        if (!config.isProduction) {
            console.error('Stack:', err.stack);
        }
        return fail(c, 'Internal server error', 500);
    });
    return app;
}
