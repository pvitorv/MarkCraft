import { jsPDF } from 'jspdf';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function downloadBlob(blob, filename) {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 2500);
}

function loadImageFile(file) {
    return new Promise((resolve, reject) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => {
            URL.revokeObjectURL(url);
            resolve(img);
        };
        img.onerror = () => {
            URL.revokeObjectURL(url);
            reject(new Error('Não foi possível ler a imagem.'));
        };
        img.src = url;
    });
}

function canvasToBlob(canvas, mime, quality) {
    return new Promise((resolve, reject) => {
        canvas.toBlob((blob) => {
            if (!blob) {
                reject(new Error('Falha ao exportar imagem.'));
                return;
            }
            resolve(blob);
        }, mime, quality);
    });
}

async function getPdfjs() {
    const pdfjs = await import('pdfjs-dist');
    const worker = await import('pdfjs-dist/build/pdf.worker.min.mjs?url');
    pdfjs.GlobalWorkerOptions.workerSrc = worker.default;
    return pdfjs;
}

export function markCraftToolsMethods() {
    return {
        tool: null,
        toolBusy: false,
        toolError: null,
        toolMessage: null,

        shorten: {
            url: '',
            utm_source: '',
            utm_medium: '',
            utm_campaign: '',
            result: null,
        },

        imageConv: {
            format: 'image/jpeg',
            quality: 0.86,
            files: [],
            done: 0,
        },

        pdfConv: {
            mode: 'pdf2img',
            format: 'image/png',
            quality: 0.9,
            scale: 2,
            files: [],
            done: 0,
        },

        pdfCompress: {
            quality: 0.72,
            scale: 1.5,
            file: null,
            before: 0,
            after: 0,
        },

        openTool(slug) {
            this.tool = slug;
            this.toolError = null;
            this.toolMessage = null;
            this.openHub('ferramentas');
        },

        toolTitle() {
            const map = {
                encurtador: 'Encurtador de URL',
                'conversor-imagens': 'Conversor de imagens',
                'conversor-pdf': 'Conversor de PDF',
                'compressor-pdf': 'Compressor de PDF',
            };
            return map[this.tool] || 'Ferramenta';
        },

        backToTools() {
            this.tool = null;
            this.toolError = null;
            this.toolMessage = null;
            this.toolBusy = false;
        },

        async copyText(text) {
            try {
                await navigator.clipboard.writeText(text);
                this.toolMessage = 'Copiado!';
            } catch {
                this.toolError = 'Não foi possível copiar. Selecione e copie manualmente.';
            }
        },

        async runShorten() {
            this.toolBusy = true;
            this.toolError = null;
            this.toolMessage = null;
            this.shorten.result = null;
            try {
                const res = await fetch('/api/tools/shorten', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        url: this.shorten.url,
                        utm_source: this.shorten.utm_source || null,
                        utm_medium: this.shorten.utm_medium || null,
                        utm_campaign: this.shorten.utm_campaign || null,
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const msg = data?.message || data?.errors?.url?.[0] || 'Falha ao encurtar.';
                    throw new Error(msg);
                }
                this.shorten.result = data;
                this.toolMessage = 'Link curto criado.';
            } catch (e) {
                this.toolError = e.message || 'Erro ao encurtar.';
            } finally {
                this.toolBusy = false;
            }
        },

        onImageFiles(event) {
            this.imageConv.files = Array.from(event.target.files || []);
            this.imageConv.done = 0;
            this.toolError = null;
            this.toolMessage = null;
        },

        async runImageConvert() {
            if (!this.imageConv.files.length) {
                this.toolError = 'Selecione ao menos uma imagem.';
                return;
            }
            this.toolBusy = true;
            this.toolError = null;
            this.toolMessage = null;
            this.imageConv.done = 0;
            const ext = this.imageConv.format === 'image/png' ? 'png'
                : this.imageConv.format === 'image/webp' ? 'webp' : 'jpg';
            try {
                for (const file of this.imageConv.files) {
                    const img = await loadImageFile(file);
                    const canvas = document.createElement('canvas');
                    canvas.width = img.naturalWidth || img.width;
                    canvas.height = img.naturalHeight || img.height;
                    const ctx = canvas.getContext('2d');
                    if (this.imageConv.format === 'image/jpeg') {
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                    }
                    ctx.drawImage(img, 0, 0);
                    const blob = await canvasToBlob(
                        canvas,
                        this.imageConv.format,
                        Number(this.imageConv.quality),
                    );
                    const base = file.name.replace(/\.[^.]+$/, '') || 'imagem';
                    downloadBlob(blob, `${base}.${ext}`);
                    this.imageConv.done += 1;
                }
                this.toolMessage = `${this.imageConv.done} arquivo(s) convertidos.`;
            } catch (e) {
                this.toolError = e.message || 'Falha na conversão.';
            } finally {
                this.toolBusy = false;
            }
        },

        onPdfConvFiles(event) {
            this.pdfConv.files = Array.from(event.target.files || []);
            this.pdfConv.done = 0;
            this.toolError = null;
            this.toolMessage = null;
        },

        async runPdfConvert() {
            if (!this.pdfConv.files.length) {
                this.toolError = 'Selecione arquivo(s).';
                return;
            }
            this.toolBusy = true;
            this.toolError = null;
            this.toolMessage = null;
            this.pdfConv.done = 0;
            try {
                if (this.pdfConv.mode === 'pdf2img') {
                    await this.pdfToImages();
                } else {
                    await this.imagesToPdf();
                }
            } catch (e) {
                this.toolError = e.message || 'Falha na conversão de PDF.';
            } finally {
                this.toolBusy = false;
            }
        },

        async pdfToImages() {
            const pdfjs = await getPdfjs();
            const ext = this.pdfConv.format === 'image/jpeg' ? 'jpg'
                : this.pdfConv.format === 'image/webp' ? 'webp' : 'png';
            for (const file of this.pdfConv.files) {
                if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                    throw new Error(`Arquivo inválido: ${file.name}`);
                }
                const data = new Uint8Array(await file.arrayBuffer());
                const pdf = await pdfjs.getDocument({ data }).promise;
                const base = file.name.replace(/\.[^.]+$/, '') || 'pdf';
                for (let pageNum = 1; pageNum <= pdf.numPages; pageNum += 1) {
                    const page = await pdf.getPage(pageNum);
                    const viewport = page.getViewport({ scale: Number(this.pdfConv.scale) || 2 });
                    const canvas = document.createElement('canvas');
                    canvas.width = viewport.width;
                    canvas.height = viewport.height;
                    const ctx = canvas.getContext('2d');
                    await page.render({ canvasContext: ctx, viewport, canvas }).promise;
                    const blob = await canvasToBlob(
                        canvas,
                        this.pdfConv.format,
                        Number(this.pdfConv.quality),
                    );
                    downloadBlob(blob, `${base}-p${pageNum}.${ext}`);
                    this.pdfConv.done += 1;
                }
            }
            this.toolMessage = `${this.pdfConv.done} página(s) exportada(s).`;
        },

        async imagesToPdf() {
            const doc = new jsPDF({ unit: 'pt', format: 'a4', compress: true });
            let first = true;
            for (const file of this.pdfConv.files) {
                if (!file.type.startsWith('image/')) {
                    throw new Error(`Não é imagem: ${file.name}`);
                }
                const img = await loadImageFile(file);
                const pageW = doc.internal.pageSize.getWidth();
                const pageH = doc.internal.pageSize.getHeight();
                const margin = 24;
                const maxW = pageW - margin * 2;
                const maxH = pageH - margin * 2;
                const ratio = Math.min(maxW / img.width, maxH / img.height);
                const w = img.width * ratio;
                const h = img.height * ratio;
                const x = (pageW - w) / 2;
                const y = (pageH - h) / 2;
                if (!first) {
                    doc.addPage();
                }
                first = false;
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                canvas.getContext('2d').drawImage(img, 0, 0);
                const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
                doc.addImage(dataUrl, 'JPEG', x, y, w, h);
                this.pdfConv.done += 1;
            }
            doc.save('markcraft-imagens.pdf');
            this.toolMessage = `PDF com ${this.pdfConv.done} imagem(ns) baixado.`;
        },

        onPdfCompressFile(event) {
            const file = event.target.files?.[0] || null;
            this.pdfCompress.file = file;
            this.pdfCompress.before = file?.size || 0;
            this.pdfCompress.after = 0;
            this.toolError = null;
            this.toolMessage = null;
        },

        async runPdfCompress() {
            if (!this.pdfCompress.file) {
                this.toolError = 'Selecione um PDF.';
                return;
            }
            this.toolBusy = true;
            this.toolError = null;
            this.toolMessage = null;
            try {
                const pdfjs = await getPdfjs();
                const data = new Uint8Array(await this.pdfCompress.file.arrayBuffer());
                const pdf = await pdfjs.getDocument({ data }).promise;
                let doc = null;
                for (let pageNum = 1; pageNum <= pdf.numPages; pageNum += 1) {
                    const page = await pdf.getPage(pageNum);
                    const viewport = page.getViewport({ scale: Number(this.pdfCompress.scale) || 1.5 });
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.floor(viewport.width);
                    canvas.height = Math.floor(viewport.height);
                    const ctx = canvas.getContext('2d');
                    await page.render({ canvasContext: ctx, viewport, canvas }).promise;
                    const dataUrl = canvas.toDataURL('image/jpeg', Number(this.pdfCompress.quality));
                    const orient = canvas.width >= canvas.height ? 'l' : 'p';
                    if (!doc) {
                        doc = new jsPDF({
                            unit: 'pt',
                            format: [canvas.width, canvas.height],
                            orientation: orient,
                            compress: true,
                        });
                    } else {
                        doc.addPage([canvas.width, canvas.height], orient);
                    }
                    doc.addImage(dataUrl, 'JPEG', 0, 0, canvas.width, canvas.height);
                }
                if (!doc) {
                    throw new Error('PDF sem páginas.');
                }
                const out = doc.output('blob');
                this.pdfCompress.after = out.size;
                const base = this.pdfCompress.file.name.replace(/\.[^.]+$/, '') || 'documento';
                downloadBlob(out, `${base}-compacto.pdf`);
                const pct = this.pdfCompress.before
                    ? Math.round((1 - out.size / this.pdfCompress.before) * 100)
                    : 0;
                this.toolMessage = pct > 0
                    ? `Compactado (~${pct}% menor).`
                    : 'PDF reexportado (tamanho pode variar conforme o original).';
            } catch (e) {
                this.toolError = e.message || 'Falha ao compactar PDF.';
            } finally {
                this.toolBusy = false;
            }
        },

        formatBytes(n) {
            if (!n) return '—';
            if (n < 1024) return `${n} B`;
            if (n < 1024 * 1024) return `${(n / 1024).toFixed(1)} KB`;
            return `${(n / (1024 * 1024)).toFixed(2)} MB`;
        },
    };
}
