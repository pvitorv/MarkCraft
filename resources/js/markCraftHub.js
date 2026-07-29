import { markCraftToolsMethods } from './markCraftTools';

export default function markCraftHub() {
    const tools = markCraftToolsMethods();

    return {
        ...tools,

        open: null,
        navOpen: false,
        creditsOpen: false,

        init() {
            const params = new URLSearchParams(window.location.search);
            const hub = params.get('hub');
            const tool = params.get('tool');
            if (hub === 'ferramentas' || hub === 'packs' || hub === 'apoiar') {
                this.openHub(hub);
            }
            if (tool && ['encurtador', 'conversor-imagens', 'conversor-pdf', 'compressor-pdf'].includes(tool)) {
                this.openTool(tool);
            }
            if (params.get('credits') === '1' || window.location.hash === '#creditos') {
                this.$nextTick(() => this.openCredits());
            }
            window.addEventListener('mc-open-credits', () => this.openCredits());
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (this.creditsOpen) {
                        this.closeCredits();
                        return;
                    }
                    if (this.navOpen) {
                        this.closeNav();
                        return;
                    }
                    if (this.open) {
                        if (this.tool && this.open === 'ferramentas') {
                            this.backToTools();
                            return;
                        }
                        this.closeHub();
                    }
                }
            });
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024 && this.navOpen) {
                    this.closeNav();
                }
            });
        },

        syncBodyScrollLock() {
            document.body.classList.toggle('overflow-hidden', this.navOpen || this.creditsOpen);
        },

        toggleNav() {
            this.navOpen = !this.navOpen;
            this.syncBodyScrollLock();
        },

        closeNav() {
            this.navOpen = false;
            this.syncBodyScrollLock();
        },

        openCredits() {
            this.navOpen = false;
            this.creditsOpen = true;
            this.syncBodyScrollLock();
        },

        closeCredits() {
            this.creditsOpen = false;
            this.syncBodyScrollLock();
        },

        openHub(id) {
            this.closeNav();
            this.open = id;
            if (id !== 'ferramentas') {
                this.tool = null;
            }
        },

        openTool(slug) {
            this.closeNav();
            this.tool = slug;
            this.toolError = null;
            this.toolMessage = null;
            this.open = 'ferramentas';
        },

        closeHub() {
            this.open = null;
            this.tool = null;
            this.toolBusy = false;
            this.toolError = null;
            this.toolMessage = null;
        },

        isOpen(id) {
            return this.open === id;
        },
    };
}
