export default function markCraftHub() {
    return {
        open: null,

        init() {
            const params = new URLSearchParams(window.location.search);
            const hub = params.get('hub');
            if (hub === 'ferramentas' || hub === 'packs' || hub === 'apoiar') {
                this.openHub(hub);
            }
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.open) {
                    this.closeHub();
                }
            });
        },

        openHub(id) {
            this.open = id;
        },

        closeHub() {
            this.open = null;
        },

        isOpen(id) {
            return this.open === id;
        },
    };
}
