const http = require('http');
const fs = require('fs');
const path = require('path');

const root = __dirname;
const port = Number(process.env.PORT || 8000);
const host = '127.0.0.1';

const types = {
  '.html': 'text/html; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.svg': 'image/svg+xml',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.webp': 'image/webp',
  '.txt': 'text/plain; charset=utf-8',
};

http.createServer((req, res) => {
  const url = new URL(req.url, `http://${host}:${port}`);
  const requestedPath = decodeURIComponent(url.pathname === '/' ? '/index.html' : url.pathname);
  const fullPath = path.normalize(path.join(root, requestedPath));

  if (!fullPath.startsWith(root)) {
    res.writeHead(403, { 'Content-Type': 'text/plain; charset=utf-8' });
    res.end('Forbidden');
    return;
  }

  if (path.extname(fullPath).toLowerCase() === '.php') {
    res.writeHead(501, { 'Content-Type': 'text/html; charset=utf-8' });
    res.end(`<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PHP Required</title>
<style>
body{margin:0;font-family:Arial,sans-serif;background:#f7f1e3;color:#262217;display:grid;place-items:center;min-height:100vh;padding:24px;}
main{max-width:560px;background:#fffdf8;border:1px solid #e7ddc4;border-radius:12px;padding:28px;box-shadow:0 18px 40px rgba(21,19,15,.1);}
h1{margin:0 0 10px;font-size:24px;}
p{line-height:1.6;margin:0 0 12px;color:#7d7660;}
code{background:#f7f1e3;padding:2px 6px;border-radius:5px;color:#15130f;}
a{color:#8a5a1d;font-weight:700;}
</style>
</head>
<body>
<main>
<h1>PHP is required for the admin panel</h1>
<p>The storefront preview server can show HTML, CSS, and JavaScript, but it cannot run PHP admin pages.</p>
<p>Open <code>/admin/login.html</code> from the local preview server to access the HTML admin panel.</p>
<p><a href="/index.html">Back to storefront</a></p>
</main>
</body>
</html>`);
    return;
  }

  fs.readFile(fullPath, (err, data) => {
    if (err) {
      res.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
      res.end('Not found');
      return;
    }

    res.writeHead(200, { 'Content-Type': types[path.extname(fullPath).toLowerCase()] || 'application/octet-stream' });
    res.end(data);
  });
}).listen(port, host, () => {
  console.log(`Static preview running at http://${host}:${port}/`);
});
