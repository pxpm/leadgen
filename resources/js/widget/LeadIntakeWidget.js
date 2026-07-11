export class LeadIntakeWidget {
    constructor() {
        this.tenant = null;
        this.config = null;
        this.lead = null;
        this.isOpen = false;
        this.isComplete = false;
        this.root = null;
        this.overlay = null;
        this.panel = null;
        this.bodyEl = null;
        this.inputEl = null;
        this.selectedServices = new Set();
    }

    async init(slug) {
        try {
            const resp = await fetch('/api/widget/' + slug + '/config');
            if (!resp.ok) return;
            this.config = await resp.json();
            this.tenant = slug;
            this.buildUI(false);
            this.tryResume();
        } catch (e) { /* silent */ }
    }

    async initMissedCall(mc) {
        this.isFullscreen = true;
        try {
            const [convResp, cfgResp] = await Promise.all([
                fetch('/api/widget/conversations/' + mc.token),
                fetch('/api/widget/' + mc.tenantSlug + '/config'),
            ]);
            if (!convResp.ok) return;
            const d = await convResp.json();
            this.lead = d.lead;

            if (cfgResp.ok) {
                this.config = await cfgResp.json();
            } else {
                this.config = { tenant: { name: mc.tenantName || 'Assistente', primary_color: '#2563eb', locale: 'pt' }, greeting: null, services: [] };
            }

            this.buildUI(true);

            if (d.intent_selection) {
                this.addMessage('bot', d.intent_selection.welcome_message);
                this.renderIntentChips(d.intent_selection);
            }

            (d.messages || []).forEach(m => this.addMessage(m.role === 'assistant' ? 'bot' : 'user', m.content));
            this.open();
        } catch (e) { /* silent */ }
    }

    open() {
        if (!this.root || this.isComplete) return;
        this.overlay.classList.add('lgw-show');
        this.panel.classList.add('lgw-open');
        this.isOpen = true;
        if (!this.lead) this.startConversation();
    }

    close() {
        this.overlay.classList.remove('lgw-show');
        this.panel.classList.remove('lgw-open');
        this.isOpen = false;
    }

    toggle() { this.isOpen ? this.close() : this.open(); }

    async startConversation() {
        try {
            const resp = await fetch('/api/widget/' + this.tenant + '/conversations', { method: 'POST' });
            if (!resp.ok) return;
            const d = await resp.json();
            this.lead = d.lead;
            this.saveSession();
            this.addMessage('bot', this.config.greeting || 'Olá! Como posso ajudar?');
            if (this.config.services && this.config.services.length) {
                this.renderServiceChips(this.config.services);
            }
        } catch (e) { this.addMessage('bot', 'Ligação perdida.'); }
    }

    async sendMessage(text, serviceKeys = null, intent = null) {
        if (!this.lead || this.isComplete) return;
        this.addMessage('user', text);
        this.inputEl.value = '';
        this.selectedServices.clear();
        this.setTyping(true);
        try {
            const body = { message: text };
            if (serviceKeys && serviceKeys.length) body.service_keys = serviceKeys;
            if (intent) body.intent = intent;
            const resp = await fetch('/api/widget/conversations/' + this.lead.session_token + '/messages', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body),
            });
            const d = await resp.json();
            this.setTyping(false);
            if (d.is_complete) { this.isComplete = true; this.addMessage('bot', d.reply); this.showDone(); }
            else {
                this.addMessage('bot', d.reply);
                if (d.phase === 'service_selection' && d.services && d.services.length) {
                    this.renderServiceChips(d.services);
                } else {
                    this.renderChips(d.next_field);
                }
            }
        } catch (e) { this.setTyping(false); this.addMessage('bot', 'Erro de ligação.'); }
    }

    async uploadFile(file) {
        if (!this.lead) return;
        const fd = new FormData(); fd.append('file', file);
        try {
            await fetch('/api/widget/conversations/' + this.lead.session_token + '/uploads', { method: 'POST', body: fd });
            this.addMessage('user', '📎 ' + file.name);
        } catch (e) { this.addMessage('bot', 'Erro ao enviar.'); }
    }

    buildUI(fullscreen) {
        const c = this.config.tenant.primary_color || '#2563eb';
        const name = this.config.tenant.name || 'Assistente';

        this.root = document.createElement('div');
        this.root.className = 'lgw-root' + (fullscreen ? ' lgw-fullscreen' : '');

        // Overlay (hidden in fullscreen)
        this.overlay = document.createElement('div');
        this.overlay.className = 'lgw-overlay';
        if (!fullscreen) this.overlay.onclick = () => this.close();
        this.root.appendChild(this.overlay);

        // Float button (hidden in fullscreen)
        if (!fullscreen) {
            const fl = document.createElement('button');
            fl.className = 'lgw-float';
            fl.style.background = c;
            fl.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
            fl.onclick = () => this.toggle();
            this.root.appendChild(fl);
        }

        // Panel
        this.panel = document.createElement('div');
        this.panel.className = 'lgw-panel';
        this.panel.style.setProperty('--lgw-primary', c);
        this.panel.innerHTML =
            '<div class="lgw-header">' +
            '<div class="lgw-header-avatar">🏠</div>' +
            '<div class="lgw-header-text"><div class="lgw-header-name">' + name + '</div><div class="lgw-header-status">Online</div></div>' +
            '<button class="lgw-header-close"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>' +
            '</div>' +
            '<div class="lgw-body"></div>' +
            '<div class="lgw-typing"><span></span><span></span><span></span></div>' +
            '<div class="lgw-footer">' +
            '<button class="lgw-btn lgw-btn-plus" title="Anexar ficheiro"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg></button>' +
            '<input class="lgw-input" placeholder="Escreva a sua mensagem...">' +
            '<button class="lgw-btn lgw-btn-attach" title="Anexar ficheiro"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg></button>' +
            '<button class="lgw-btn lgw-btn-send"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg></button>' +
            '<input type="file" accept="image/*" capture="environment" style="display:none">' +
            '</div>';
        this.root.appendChild(this.panel);

        document.body.appendChild(this.root);

        this.bodyEl = this.panel.querySelector('.lgw-body');
        this.inputEl = this.panel.querySelector('.lgw-input');

        // Close button
        const closeBtn = this.panel.querySelector('.lgw-header-close');
        if (closeBtn) closeBtn.onclick = () => this.close();

        // Send button
        const sendBtn = this.panel.querySelector('.lgw-btn-send');
        sendBtn.onclick = () => {
            const t = this.inputEl.value.trim();
            if (!t) return;
            const keys = this.selectedServices.size ? Array.from(this.selectedServices) : null;
            this.sendMessage(t, keys);
        };

        // Enter key to send
        this.inputEl.onkeydown = (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                const t = this.inputEl.value.trim();
                if (t) this.sendMessage(t);
            }
        };

        // Plus toggle reveals/hides attach button
        const plusBtn = this.panel.querySelector('.lgw-btn-plus');
        const attachBtn = this.panel.querySelector('.lgw-btn-attach');
        if (plusBtn && attachBtn) {
            plusBtn.onclick = () => {
                const visible = attachBtn.classList.toggle('lgw-attach-visible');
                plusBtn.classList.toggle('lgw-plus-active', visible);
            };
        }

        // Attach button opens file picker
        if (attachBtn) {
            attachBtn.onclick = () => this.panel.querySelector('input[type=file]').click();
        }

        // File input handler
        const fileInput = this.panel.querySelector('input[type=file]');
        if (fileInput) {
            fileInput.onchange = (e) => {
                if (e.target.files[0]) {
                    this.uploadFile(e.target.files[0]);
                    e.target.value = '';
                    // Hide attach after upload
                    if (attachBtn) attachBtn.classList.remove('lgw-attach-visible');
                    if (plusBtn) plusBtn.classList.remove('lgw-plus-active');
                }
            };
        }
    }

    renderChips(nextField) {
        const ex = this.panel.querySelector('.lgw-chips'); if (ex) ex.remove();
        if (!nextField || nextField.type !== 'select' || !nextField.options) return;
        const d = document.createElement('div'); d.className = 'lgw-chips';
        const isMulti = nextField.multi === true;
        nextField.options.forEach(o => {
            const b = document.createElement('button'); b.textContent = o.label;
            if (isMulti) {
                b.onclick = () => {
                    b.classList.toggle('lgw-chip-selected');
                    this.buildMultiInput(nextField.options);
                };
            } else {
                b.onclick = () => { this.sendMessage(o.label); d.remove(); };
            }
            d.appendChild(b);
        });
        this.panel.querySelector('.lgw-body').appendChild(d);
    }

    buildMultiInput(options) {
        const selected = this.panel.querySelectorAll('.lgw-chips .lgw-chip-selected');
        const labels = Array.from(selected).map(el => el.textContent);
        this.inputEl.value = labels.join(', ');
    }

    renderIntentChips(intentSelection) {
        const ex = this.panel.querySelector('.lgw-chips'); if (ex) ex.remove();
        const d = document.createElement('div'); d.className = 'lgw-chips';
        const intents = intentSelection.intents || {};
        Object.keys(intents).forEach(key => {
            const b = document.createElement('button'); b.textContent = intents[key];
            b.onclick = () => {
                this.sendMessage(intents[key], null, key);
                d.remove();
            };
            d.appendChild(b);
        });
        this.panel.querySelector('.lgw-body').appendChild(d);
    }

    renderServiceChips(services) {
        const ex = this.panel.querySelector('.lgw-chips'); if (ex) ex.remove();
        this.selectedServices.clear();
        const d = document.createElement('div'); d.className = 'lgw-chips';
        services.forEach(s => {
            const b = document.createElement('button');
            b.textContent = (s.icon || '🔧') + ' ' + s.name;
            b.dataset.key = s.key;
            b.onclick = () => {
                if (this.selectedServices.has(s.key)) {
                    this.selectedServices.delete(s.key);
                    b.classList.remove('lgw-chip-selected');
                } else {
                    this.selectedServices.add(s.key);
                    b.classList.add('lgw-chip-selected');
                }
                this.updateInputFromServices(services);
            };
            d.appendChild(b);
        });
        this.panel.querySelector('.lgw-body').appendChild(d);
    }

    appendToInput(text) {
        const current = this.inputEl.value.trim();
        this.inputEl.value = current ? current + ', ' + text : text;
        this.inputEl.focus();
    }

    updateInputFromServices(services) {
        const names = [];
        services.forEach(s => {
            if (this.selectedServices.has(s.key)) names.push(s.name);
        });
        this.inputEl.value = names.join(', ');
    }

    addMessage(role, text) {
        const w = document.createElement('div'); w.className = 'lgw-msg lgw-msg-' + role;
        const b = document.createElement('div'); b.className = 'lgw-bubble'; b.textContent = text;
        w.appendChild(b); this.bodyEl.appendChild(w);
        this.bodyEl.scrollTop = this.bodyEl.scrollHeight;
    }

    setTyping(v) { const el = this.panel.querySelector('.lgw-typing'); if (el) el.style.display = v ? 'flex' : 'none'; }

    showDone() {
        const f = this.panel.querySelector('.lgw-footer');
        if (f) f.innerHTML = '<div class="lgw-done"><div class="lgw-done-icon"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg></div><div class="lgw-done-title">Obrigado!</div><div class="lgw-done-sub">Entraremos em contacto em breve.</div></div>';
    }

    async tryResume() {
        const s = this.loadSession(); if (!s) return;
        try {
            const r = await fetch('/api/widget/conversations/' + s.token);
            if (!r.ok) return;
            const d = await r.json();
            this.lead = d.lead;
            if (d.intent_selection) {
                this.addMessage('bot', d.intent_selection.welcome_message);
                this.renderIntentChips(d.intent_selection);
            }
            (d.messages || []).forEach(m => this.addMessage(m.role === 'assistant' ? 'bot' : 'user', m.content));
        } catch (e) { /* expired */ }
    }

    saveSession() { if (this.lead) try { localStorage.setItem('lgw_session', JSON.stringify({ token: this.lead.session_token, id: this.lead.id })); } catch (e) {} }
    loadSession() { try { return JSON.parse(localStorage.getItem('lgw_session')); } catch (e) { return null; } }
}
