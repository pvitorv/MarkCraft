/**
 * MarkCraft Desktop — janela Electron + Laravel LOCAL.
 * Instalador NSIS cria atalho; ao abrir, sobe artisan serve se necessário.
 */
const { app, BrowserWindow, ipcMain, shell, dialog } = require('electron');
const path = require('path');
const fs = require('fs');
const os = require('os');
const { spawn } = require('child_process');
const http = require('http');

const APP_URL = process.env.MARKCRAFT_DESKTOP_URL || 'http://127.0.0.1:8000';
const MARKCRAFT_ROOT = process.env.MARKCRAFT_ROOT
  || 'C:\\laragon\\www\\MarkCraft';
const EXPORTS_ROOT = process.env.MARKCRAFT_EXPORTS_DIR
  || path.join(os.homedir(), 'MarkCraftExports');

const CATEGORIES = ['Instagram', 'Facebook', 'LinkedIn', 'Blog', 'Apresentacao', 'Outros'];

let phpServer = null;
let mainWindow = null;

function ensureExportTree() {
  fs.mkdirSync(EXPORTS_ROOT, { recursive: true });
  for (const cat of CATEGORIES) {
    fs.mkdirSync(path.join(EXPORTS_ROOT, cat), { recursive: true });
  }
}

function urlReachable(url, timeoutMs = 1500) {
  return new Promise((resolve) => {
    const req = http.get(url, (res) => {
      res.resume();
      resolve(res.statusCode >= 200 && res.statusCode < 500);
    });
    req.on('error', () => resolve(false));
    req.setTimeout(timeoutMs, () => {
      req.destroy();
      resolve(false);
    });
  });
}

function findPhpBin() {
  const candidates = [
    process.env.PHP_BIN,
    'C:\\laragon\\bin\\php\\php-8.3.16-Win32-vs16-x64\\php.exe',
    'C:\\laragon\\bin\\php\\php-8.3.12-Win32-vs16-x64\\php.exe',
    'C:\\laragon\\bin\\php\\php-8.2.27-Win32-vs16-x64\\php.exe',
    'php',
  ].filter(Boolean);

  for (const bin of candidates) {
    if (bin === 'php') {
      return bin;
    }
    if (fs.existsSync(bin)) {
      return bin;
    }
  }

  // Procura pasta Laragon php
  const phpRoot = 'C:\\laragon\\bin\\php';
  if (fs.existsSync(phpRoot)) {
    const dirs = fs.readdirSync(phpRoot).filter((d) => d.startsWith('php-'));
    dirs.sort().reverse();
    for (const d of dirs) {
      const exe = path.join(phpRoot, d, 'php.exe');
      if (fs.existsSync(exe)) {
        return exe;
      }
    }
  }

  return 'php';
}

async function ensureLaravelUp() {
  const healthUrls = [`${APP_URL.replace(/\/$/, '')}/up`, APP_URL];
  for (const u of healthUrls) {
    if (await urlReachable(u)) {
      return true;
    }
  }

  if (!fs.existsSync(path.join(MARKCRAFT_ROOT, 'artisan'))) {
    return false;
  }

  const php = findPhpBin();
  try {
    phpServer = spawn(php, ['artisan', 'serve', '--host=127.0.0.1', '--port=8000'], {
      cwd: MARKCRAFT_ROOT,
      windowsHide: true,
      stdio: 'ignore',
    });
    phpServer.on('error', () => {
      phpServer = null;
    });
  } catch {
    return false;
  }

  for (let i = 0; i < 20; i += 1) {
    await new Promise((r) => setTimeout(r, 500));
    if (await urlReachable(`${APP_URL.replace(/\/$/, '')}/up`) || await urlReachable(APP_URL)) {
      return true;
    }
  }

  return false;
}

function createWindow() {
  ensureExportTree();

  mainWindow = new BrowserWindow({
    width: 1440,
    height: 900,
    minWidth: 1024,
    minHeight: 700,
    title: 'MarkCraft Desktop',
    backgroundColor: '#07090c',
    icon: path.join(__dirname, 'build', 'icon.png'),
    webPreferences: {
      preload: path.join(__dirname, 'preload.cjs'),
      contextIsolation: true,
      nodeIntegration: false,
      sandbox: false,
    },
  });

  mainWindow.loadURL(APP_URL);

  mainWindow.webContents.setWindowOpenHandler(({ url }) => {
    shell.openExternal(url);
    return { action: 'deny' };
  });

  mainWindow.on('closed', () => {
    mainWindow = null;
  });
}

ipcMain.handle('markcraft:save-export', async (_event, payload = {}) => {
  ensureExportTree();
  const filename = String(payload.filename || `arte-${Date.now()}.bin`).replace(/[<>:"/\\|?*]/g, '_');
  let category = String(payload.category || 'Outros');
  if (!CATEGORIES.includes(category)) {
    category = 'Outros';
  }
  const dir = path.join(EXPORTS_ROOT, category);
  fs.mkdirSync(dir, { recursive: true });
  const target = path.join(dir, filename);
  const data = Buffer.from(payload.data || []);
  fs.writeFileSync(target, data);

  return { ok: true, path: target, root: EXPORTS_ROOT };
});

ipcMain.handle('markcraft:exports-root', async () => {
  ensureExportTree();
  return { root: EXPORTS_ROOT, categories: CATEGORIES };
});

ipcMain.handle('markcraft:open-exports', async () => {
  ensureExportTree();
  await shell.openPath(EXPORTS_ROOT);
  return { ok: true };
});

app.whenReady().then(async () => {
  const ok = await ensureLaravelUp();
  if (!ok) {
    dialog.showMessageBoxSync({
      type: 'warning',
      title: 'MarkCraft Desktop',
      message: 'Não consegui falar com o MarkCraft local.',
      detail: [
        `URL: ${APP_URL}`,
        `Pasta: ${MARKCRAFT_ROOT}`,
        '',
        'Abra o Laragon (Start All) ou rode:',
        '  cd C:\\laragon\\www\\MarkCraft',
        '  php artisan serve',
        '',
        'Depois clique OK para tentar abrir mesmo assim.',
      ].join('\n'),
    });
  }

  createWindow();

  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) {
      createWindow();
    }
  });
});

app.on('window-all-closed', () => {
  if (phpServer && !phpServer.killed) {
    try {
      phpServer.kill();
    } catch {
      /* ignore */
    }
  }
  if (process.platform !== 'darwin') {
    app.quit();
  }
});
