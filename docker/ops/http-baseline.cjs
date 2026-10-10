const endpoints = process.argv.slice(2);
if (!endpoints.length) throw new Error('Supply explicit HTTP endpoints');
Promise.all(endpoints.map(async url => {
  const start = Date.now();
  try {
    const response = await fetch(url, { method: 'HEAD', redirect: 'manual', signal: AbortSignal.timeout(15000) });
    console.log(JSON.stringify({ time: new Date().toISOString(), url, status: response.status, duration_ms: Date.now() - start }));
  } catch (error) {
    console.log(JSON.stringify({ time: new Date().toISOString(), url, error: error.cause?.code || error.name }));
  }
}));
