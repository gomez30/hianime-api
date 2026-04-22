import { createApp } from './src/createApp';
import config from './src/config/config';

const app = createApp();
const PORT = config.port;

Bun.serve({
  port: PORT,
  hostname: '0.0.0.0',
  fetch: app.fetch,
});

console.log(`🚀 Server running at http://0.0.0.0:${PORT}`);
console.log(`📊 Environment: ${config.environment}`);
console.log(`🔧 Production: ${config.isProduction}`);
