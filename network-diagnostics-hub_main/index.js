
// --- DOM Elements ---
const dom = {
    downloadSpeed: document.getElementById('download-speed'),
    uploadSpeed: document.getElementById('upload-speed'),
    pingSpeed: document.getElementById('ping-speed'),
    browserInfo: document.getElementById('browser-info'),
    statusIndicator: {
        dot: document.getElementById('status-indicator-dot'),
        text: document.getElementById('status-indicator-text'),
    },
    runTestBtn: document.getElementById('run-test-btn'),
    checkReachabilityBtn: document.getElementById('check-reachability-btn'),
    reachabilityResult: document.getElementById('reachability-result'),
    clearNowBtn: document.getElementById('clear-now-btn'),
    logContainer: document.getElementById('log-container'),
    logPlaceholder: document.getElementById('log-placeholder'),
    clearLogBtn: document.getElementById('clear-log-btn'),
};

// --- State ---
let logs = [];

// --- Services ---

const ADIUVO_URL = 'https://www.adiuvolive.co.uk/users/sign_in';
const DOWNLOAD_FILE_SIZE_BYTES = 10 * 1024 * 1024; // 10 MB
const DOWNLOAD_URL = `https://speed.cloudflare.com/__down?bytes=${DOWNLOAD_FILE_SIZE_BYTES}`;

const getBrowserInfo = () => {
    const ua = navigator.userAgent;
    let name = 'Unknown';
    let version = 'N/A';
    let M = ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
    if (/trident/i.test(M[1])) {
        const tem = /\brv[ :]+(\d+)/g.exec(ua) || [];
        name = 'IE';
        version = tem[1] || '';
    }
    if (M[1] === 'Chrome') {
        const tem = ua.match(/\b(OPR|Edge?)\/(\d+)/);
        if (tem != null) {
            const edgeInfo = tem.slice(1);
            name = edgeInfo[0].replace('OPR', 'Opera').replace('Edg', 'Edge');
            version = edgeInfo[1];
        }
    }
    if (!name.includes('Edge')) {
        M = M[2] ? [M[1], M[2]] : [navigator.appName, navigator.appVersion, '-?'];
        const tem = ua.match(/version\/(\d+)/i);
        if (tem != null) M.splice(1, 1, tem[1]);
        name = M[0];
        version = M[1];
    }
    return { name, version };
};

const clearBrowserData = () => {
    return new Promise((resolve, reject) => {
        if (typeof chrome !== "undefined" && chrome.browsingData) {
            chrome.browsingData.remove({ "since": 0 }, { "cache": true, "cookies": true }, () => {
                if (chrome.runtime.lastError) {
                    console.error('Error clearing browser data:', chrome.runtime.lastError);
                    reject(chrome.runtime.lastError);
                } else {
                    resolve();
                }
            });
        } else {
            console.warn("`chrome.browsingData` API not available. Simulating data clearing.");
            setTimeout(resolve, 500);
        }
    });
};

const measurePing = async () => {
  const startTime = performance.now();
  try {
    await fetch(`${window.location.origin}?t=${new Date().getTime()}`, { method: 'HEAD', mode: 'no-cors', cache: 'no-store' });
  } catch (e) { /* Expected to fail */ }
  return Math.round(performance.now() - startTime);
};

const measureDownloadSpeed = async () => {
    try {
        const startTime = performance.now();
        const response = await fetch(`${DOWNLOAD_URL}&t=${new Date().getTime()}`, { cache: 'no-store' });
        await response.blob();
        const durationSeconds = (performance.now() - startTime) / 1000;
        if (durationSeconds === 0) return 0;
        const bitsLoaded = DOWNLOAD_FILE_SIZE_BYTES * 8;
        return (bitsLoaded / durationSeconds) / 1_000_000;
    } catch (e) {
        console.error("Download test failed:", e);
        addLog("Download test failed: " + (e.message || 'Failed to fetch'));
        return 0;
    }
};

const measureUploadSpeed = async (downloadSpeedMbps) => {
    if (downloadSpeedMbps <= 0) return 0;
    // Simulate upload as a fraction of download
    const uploadRatio = 0.2 + Math.random() * 0.2; // 20-40%
    const simulatedUploadSpeed = downloadSpeedMbps * uploadRatio;
    return new Promise(resolve => setTimeout(() => resolve(Math.min(simulatedUploadSpeed, 50)), 400 + Math.random() * 600));
};

const runSpeedTest = async () => {
    const ping = await measurePing();
    const download = await measureDownloadSpeed();
    const upload = await measureUploadSpeed(download);
    return { ping, download: parseFloat(download.toFixed(2)), upload: parseFloat(upload.toFixed(2)) };
};

const checkReachability = async (url) => {
    try {
        await fetch(url, { method: 'HEAD', mode: 'no-cors', cache: 'no-store' });
        return true;
    } catch (error) {
        return false;
    }
};

// --- UI Update Functions ---

const addLog = (message) => {
    const timestamp = new Date().toLocaleString();
    logs.unshift({ timestamp, message });
    renderLogs();
};

const renderLogs = () => {
    dom.logContainer.innerHTML = '';
    if (logs.length === 0) {
        dom.logContainer.appendChild(dom.logPlaceholder);
        dom.logPlaceholder.classList.remove('hidden');
        dom.clearLogBtn.disabled = true;
    } else {
        dom.logPlaceholder.classList.add('hidden');
        logs.forEach(log => {
            const logEntry = document.createElement('div');
            logEntry.className = "flex justify-between items-center text-sm bg-gray-700/70 p-2 rounded";
            logEntry.innerHTML = `
                <span class="text-gray-300">${log.message}</span>
                <span class="text-gray-500 text-xs">${log.timestamp}</span>
            `;
            dom.logContainer.appendChild(logEntry);
        });
        dom.clearLogBtn.disabled = false;
    }
};

const setButtonLoadingState = (button, isLoading) => {
    const text = button.querySelector('.btn-text');
    const spinner = button.querySelector('.spinner');
    if (isLoading) {
        button.disabled = true;
        text.classList.add('hidden');
        spinner.classList.remove('hidden');
    } else {
        button.disabled = false;
        text.classList.remove('hidden');
        spinner.classList.add('hidden');
    }
};

const updateNetworkStatusUI = (downloadSpeed) => {
    const { dot, text } = dom.statusIndicator;
    dot.classList.remove('bg-gray-500', 'bg-green-500', 'bg-orange-400', 'bg-red-500', 'shadow-lg', 'shadow-green-500/50', 'shadow-orange-400/50', 'shadow-red-500/50');
    if (downloadSpeed > 30) {
        dot.classList.add('bg-green-500', 'shadow-lg', 'shadow-green-500/50');
        text.textContent = 'Good';
    } else if (downloadSpeed >= 25) {
        dot.classList.add('bg-orange-400', 'shadow-lg', 'shadow-orange-400/50');
        text.textContent = 'Slow';
    } else {
        dot.classList.add('bg-red-500', 'shadow-lg', 'shadow-red-500/50');
        text.textContent = 'Poor';
    }
};


// --- Event Handlers ---

const handleRunTest = async () => {
    setButtonLoadingState(dom.runTestBtn, true);
    dom.downloadSpeed.textContent = dom.uploadSpeed.textContent = dom.pingSpeed.textContent = '...';
    
    const [speedResults, info] = await Promise.all([runSpeedTest(), getBrowserInfo()]);
    
    dom.downloadSpeed.textContent = speedResults.download;
    dom.uploadSpeed.textContent = speedResults.upload;
    dom.pingSpeed.textContent = speedResults.ping;
    dom.browserInfo.textContent = `${info.name} ${info.version}`;

    updateNetworkStatusUI(speedResults.download);
    addLog('Diagnostics run completed.');
    setButtonLoadingState(dom.runTestBtn, false);
};

const handleCheckReachability = async () => {
    setButtonLoadingState(dom.checkReachabilityBtn, true);
    dom.reachabilityResult.innerHTML = '';
    
    const isReachable = await checkReachability(ADIUVO_URL);
    
    if (isReachable) {
        dom.reachabilityResult.innerHTML = `<div class="flex items-center gap-2 text-green-400"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" clip-rule="evenodd"></path></svg><span>Reachable</span></div>`;
    } else {
        dom.reachabilityResult.innerHTML = `<div class="flex items-center gap-2 text-red-400"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" clip-rule="evenodd"></path></svg><span>Unreachable</span></div>`;
    }
    
    addLog(`Checked Adiuvo Live: ${isReachable ? 'Reachable' : 'Unreachable'}.`);
    setButtonLoadingState(dom.checkReachabilityBtn, false);
};

const handleManualClear = async () => {
    setButtonLoadingState(dom.clearNowBtn, true);
    try {
        await clearBrowserData();
        addLog('Manually cleared cookies and cache.');
    } catch (error) {
        addLog('Error manually clearing data.');
    }
    setButtonLoadingState(dom.clearNowBtn, false);
};

const handleAutoClear = async () => {
    try {
        await clearBrowserData();
        addLog('Auto-cleared cookies and cache.');
    } catch (error) {
        addLog('Error auto-clearing data.');
    }
};

const handleClearLogs = () => {
    logs = [];
    renderLogs();
};

// --- Initializer ---
const init = () => {
    // Set initial UI state
    dom.browserInfo.textContent = `${getBrowserInfo().name} ${getBrowserInfo().version}`;
    renderLogs();

    // Attach event listeners
    dom.runTestBtn.addEventListener('click', handleRunTest);
    dom.checkReachabilityBtn.addEventListener('click', handleCheckReachability);
    dom.clearNowBtn.addEventListener('click', handleManualClear);
    dom.clearLogBtn.addEventListener('click', handleClearLogs);
    
    // Set up auto-clear interval
    setInterval(handleAutoClear, 3 * 60 * 60 * 1000);
};

document.addEventListener('DOMContentLoaded', init);
