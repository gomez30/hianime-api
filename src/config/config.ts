const config = {
  baseurl: 'https://aniwatch.co.at',
  baseurl2: 'https://aniwatch.co.at',
  origin: '*',
  port: Number(process.env.PORT) || 5000,

  headers: {
    'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64; rv:122.0) Gecko/20100101 Firefox/122.0',
  },

  logLevel: 'INFO',
  enableLogging: process.env.ENABLE_LOGGING === 'true',
  isProduction: process.env.NODE_ENV === 'production',
  isDevelopment: process.env.NODE_ENV === 'development',
  isVercel: !!process.env.VERCEL,
  isRender: !!process.env.RENDER,
  environment: process.env.VERCEL ? 'vercel' : process.env.RENDER ? 'render' : 'local',
};

export default config;
