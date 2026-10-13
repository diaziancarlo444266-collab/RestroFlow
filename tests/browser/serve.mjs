import { spawn, spawnSync } from 'node:child_process';
import { existsSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

// All child processes use this isolated database; the project's .env is untouched.
const database = resolve('storage/framework/browser-auth.sqlite');
if (!existsSync(database)) writeFileSync(database, '');
const env = {
    ...process.env,
    APP_ENV: 'testing', APP_URL: 'http://127.0.0.1:8019',
    DB_CONNECTION: 'sqlite', DB_DATABASE: database,
    CACHE_STORE: 'database', SESSION_DRIVER: 'file', MAIL_MAILER: 'log', LOG_CHANNEL: 'single',
    SESSION_COOKIE: 'restroflow_browser_test', BCRYPT_ROUNDS: '4',
};
for (const args of [['artisan', 'migrate', '--seed', '--force', '--no-interaction'], ['tests/browser/seed.php']]) {
    const result = spawnSync('php', args, { env, stdio: 'inherit' });
    if (result.status !== 0) process.exit(result.status || 1);
}
const server = spawn('php', ['artisan', 'serve', '--host=127.0.0.1', '--port=8019', '--no-reload'], { env, stdio: 'inherit' });
for (const signal of ['SIGINT', 'SIGTERM']) process.on(signal, () => { server.kill(); process.exit(); });
server.on('exit', code => process.exit(code || 0));
