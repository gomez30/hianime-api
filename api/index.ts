import { handle } from 'hono/vercel';
import { createApp } from '../src/createApp';

const app = createApp();

export default handle(app);
