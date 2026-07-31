const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('markcraftDesktop', {
  isDesktop: true,
  saveExport: (payload) => ipcRenderer.invoke('markcraft:save-export', payload),
  exportsRoot: () => ipcRenderer.invoke('markcraft:exports-root'),
  openExportsFolder: () => ipcRenderer.invoke('markcraft:open-exports'),
});
