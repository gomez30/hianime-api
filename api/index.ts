import { handle } from '@hono/node-server/vercel';
import { createApp } from '../src/createApp';

const app = createApp();

export default handle(app);
