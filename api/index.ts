import app from '../src/app';

type VercelResponse = {
  status: (code: number) => VercelResponse;
  setHeader: (name: string, value: string) => VercelResponse;
  send: (body: string | object | Buffer) => VercelResponse;
  json: (body: object) => VercelResponse;
};

export default async function handler(
  req: {
    headers: Record<string, string | string[] | undefined>;
    method: string;
    url: string;
    body: unknown;
  },
  res: VercelResponse
) {
  try {
    const protocol = req.headers['x-forwarded-proto'] || 'https';
    const host = req.headers['x-forwarded-host'] || req.headers.host;
    const url = `${protocol}://${host}${req.url}`;
    const webRequest = new Request(url, {
      method: req.method,
      headers: new Headers(req.headers as Record<string, string>),
      body: req.method !== 'GET' && req.method !== 'HEAD' ? JSON.stringify(req.body) : undefined,
    });

    const webResponse = await app.fetch(webRequest);
    res.status(webResponse.status);
    webResponse.headers.forEach((value: string, key: string) => {
      res.setHeader(key, value);
    });

    const body = await webResponse.text();
    res.send(body);
  } catch (error: unknown) {
    const err = error as Error;
    console.error('Vercel handler error:', err);
    res.status(500).json({
      success: false,
      error: 'Internal Server Error',
      message: err.message,
    });
  }
}
