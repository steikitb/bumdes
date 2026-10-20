const { app, BrowserWindow, dialog } = require('electron');
const { spawn, exec } = require('child_process');
const path = require('path');
const net = require('net');
const fs = require('fs');

let mainWindow;
let phpProcess;

const gotTheLock = app.requestSingleInstanceLock();

if (!gotTheLock) {
    app.quit();
} else {
    app.on('second-instance', (event, commandLine, workingDirectory) => {
        if (mainWindow) {
            if (mainWindow.isMinimized()) mainWindow.restore();
            mainWindow.focus();
        }
    });

    app.on('ready', createWindow);
    
    app.on('window-all-closed', function () {
        if (process.platform !== 'darwin') app.quit();
    });
    
    app.on('will-quit', () => {
        // 9. Perbaikan Backup Berkala: Membaca pengaturan path backup kustom jika ada
        try {
            const dbDir = path.join(app.getPath('userData'), 'database');
            const dbPath = path.join(dbDir, 'koperasi.sqlite');
            const backupConfigPath = path.join(dbDir, 'backup_config.json');
            
            let backupDir = path.join(app.getPath('documents'), 'Backup_Koperasi');
            
            if (fs.existsSync(backupConfigPath)) {
                try {
                    const conf = JSON.parse(fs.readFileSync(backupConfigPath, 'utf8'));
                    if (conf.backup_path) backupDir = conf.backup_path;
                } catch(e) {}
            }

            if (!fs.existsSync(backupDir)) {
                fs.mkdirSync(backupDir, { recursive: true });
            }
            if (fs.existsSync(dbPath)) {
                const dateStr = new Date().toISOString().replace(/:/g, '-').split('.')[0];
                const destPath = path.join(backupDir, `koperasi_auto_${dateStr}.sqlite`);
                fs.copyFileSync(dbPath, destPath);
                console.log(`Auto backup berhasil: ${destPath}`);
            }
        } catch(e) {
            console.error("Auto backup gagal:", e);
        }

        if (phpProcess) {
            if (process.platform === 'win32') {
                exec(`taskkill /pid ${phpProcess.pid} /t /f`, () => {});
            } else {
                try { process.kill(-phpProcess.pid); } catch (e) { phpProcess.kill(); }
            }
        }
    });
}

function getFreePort() {
    return new Promise((resolve, reject) => {
        const srv = net.createServer();
        srv.listen(0, '127.0.0.1', () => {
            const port = srv.address().port;
            srv.close((err) => resolve(port));
        });
        srv.on('error', reject);
    });
}

// 2. Perbaikan Race Condition: Menunggu hingga port PHP benar-benar LISTENING
function waitForServer(port, callback) {
    const client = new net.Socket();
    client.once('connect', () => {
        client.destroy();
        callback();
    }).once('error', (err) => {
        setTimeout(() => waitForServer(port, callback), 200);
    });
    client.connect({ port: port, host: '127.0.0.1' });
}

async function createWindow() {
    const port = await getFreePort();
    
    const isPackaged = app.isPackaged;
    const baseDir = isPackaged ? process.resourcesPath : __dirname;

    const dbDir = path.join(app.getPath('userData'), 'database');
    if (!fs.existsSync(dbDir)) {
        fs.mkdirSync(dbDir, { recursive: true });
    }
    
    const dbPath = path.join(dbDir, 'koperasi.sqlite');
    const templateDbPath = path.join(baseDir, 'sistem', 'database', 'koperasi.sqlite');
    
    if (!fs.existsSync(dbPath)) {
        if (fs.existsSync(templateDbPath)) {
            fs.copyFileSync(templateDbPath, dbPath);
        } else {
            console.error("Database template tidak ditemukan di: " + templateDbPath);
        }
    }

    let phpPath = 'php';
    const bundledPhpPath = path.join(baseDir, 'php', process.platform === 'win32' ? 'php.exe' : 'php');
    
    if (fs.existsSync(bundledPhpPath)) {
        phpPath = bundledPhpPath;
    } else if (isPackaged) {
        dialog.showErrorBox('Fatal Error', 'PHP Binary tidak ditemukan. Pastikan folder php disertakan saat build.');
        app.quit();
        return;
    }

    const publikPath = path.join(baseDir, 'publik');
    const routerPath = path.join(publikPath, 'router.php');

    phpProcess = spawn(phpPath, ['-S', `127.0.0.1:${port}`, '-t', publikPath, routerPath], {
        env: { ...process.env, DATABASE_DIR: dbDir },
        detached: true
    });

    phpProcess.stderr.on('data', (data) => {
        console.log(`PHP Server Log: ${data}`);
    });

    mainWindow = new BrowserWindow({
        width: 1280,
        height: 800,
        minWidth: 1024,
        minHeight: 768,
        autoHideMenuBar: true,
        title: "Sistem Akuntansi Koperasi",
        webPreferences: {
            nodeIntegration: false,
            contextIsolation: true
        }
    });

    // Load URL HANYA setelah PHP server terdeteksi aktif
    waitForServer(port, () => {
        mainWindow.loadURL(`http://127.0.0.1:${port}/`);
    });

    mainWindow.on('closed', function () {
        mainWindow = null;
    });
}

process.on('exit', () => {
    if (phpProcess) {
        if (process.platform === 'win32') {
            exec(`taskkill /pid ${phpProcess.pid} /t /f`, () => {});
        } else {
            try { process.kill(-phpProcess.pid); } catch (e) { phpProcess.kill(); }
        }
    }
});
process.on('SIGINT', () => { app.quit(); });
process.on('SIGTERM', () => { app.quit(); });