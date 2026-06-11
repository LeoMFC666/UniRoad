/* =============================================================================
   ARTISANFLOW UTILITIES - Performance and Enhancement Library
   ============================================================================= */

/**
 * Debounce utility - Prevents excessive function calls
 */
function debounce(func, wait = 250) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle utility - Limits function calls to once per interval
 */
function throttle(func, limit = 250) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Request animation frame wrapper for smooth animations
 */
function requestAnimFrame(callback) {
    return (
        window.requestAnimationFrame ||
        window.webkitRequestAnimationFrame ||
        window.mozRequestAnimationFrame ||
        function(callback) {
            window.setTimeout(callback, 1000 / 60);
        }
    )(callback);
}

/**
 * Cancela quadro de animacao
 */
function cancelAnimFrame(id) {
    return (
        window.cancelAnimationFrame ||
        window.webkitCancelAnimationFrame ||
        window.mozCancelAnimationFrame ||
        function(id) {
            window.clearTimeout(id);
        }
    )(id);
}

/* =============================================================================
   TOAST NOTIFICATION SYSTEM
   ============================================================================= */

class ToastManager {
    constructor() {
        this.toasts = [];
        this.container = this.createContainer();
    }

    createContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }

    show(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast ${type} animate-slide-in-right`;
        
        const iconMap = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        
        toast.innerHTML = `
            <span class="toast-icon">${iconMap[type] || '•'}</span>
            <span class="toast-message">${this.escapeHtml(message)}</span>
        `;
        
        this.container.appendChild(toast);
        this.toasts.push(toast);
        
        if (duration > 0) {
            setTimeout(() => this.remove(toast), duration);
        }
        
        return toast;
    }

    success(message, duration) {
        return this.show(message, 'success', duration);
    }

    error(message, duration) {
        return this.show(message, 'error', duration || 5000);
    }

    warning(message, duration) {
        return this.show(message, 'warning', duration || 4000);
    }

    info(message, duration) {
        return this.show(message, 'info', duration);
    }

    remove(toast) {
        toast.style.animation = 'fadeOut 300ms ease-out forwards';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            this.toasts = this.toasts.filter(t => t !== toast);
        }, 300);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    clear() {
        this.toasts.forEach(toast => this.remove(toast));
    }
}

const toastManager = new ToastManager();

/* =============================================================================
   MODAL DIALOG SYSTEM
   ============================================================================= */

class ModalDialog {
    constructor(title, message, buttons = []) {
        this.title = title;
        this.message = message;
        this.buttons = buttons;
        this.element = null;
    }

    show() {
        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay';
            
            const content = document.createElement('div');
            content.className = 'modal-content';
            
            let buttonsHtml = '';
            this.buttons.forEach((btn, index) => {
                const btnClass = btn.type === 'primary' ? 'primary' : '';
                buttonsHtml += `
                    <button class="${btnClass}" data-button-index="${index}">
                        ${this.escapeHtml(btn.label)}
                    </button>
                `;
            });
            
            content.innerHTML = `
                <div class="modal-header">${this.escapeHtml(this.title)}</div>
                <div class="modal-body">${this.escapeHtml(this.message)}</div>
                <div class="modal-footer">${buttonsHtml}</div>
            `;
            
            overlay.appendChild(content);
            document.body.appendChild(overlay);
            this.element = overlay;
            
            content.querySelectorAll('button').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    const index = parseInt(e.target.dataset.buttonIndex);
                    this.close();
                    resolve(index);
                });
            });
            
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    this.close();
                    resolve(-1);
                }
            });
        });
    }

    close() {
        if (this.element && this.element.parentNode) {
            this.element.style.animation = 'fadeOut 300ms ease-out forwards';
            setTimeout(() => {
                if (this.element && this.element.parentNode) {
                    this.element.parentNode.removeChild(this.element);
                }
            }, 300);
        }
    }

    static confirm(title, message) {
        const dialog = new ModalDialog(title, message, [
            { label: 'Cancelar', type: 'secondary' },
            { label: 'Confirmar', type: 'primary' }
        ]);
        return dialog.show();
    }

    static alert(title, message) {
        const dialog = new ModalDialog(title, message, [
            { label: 'Ok', type: 'primary' }
        ]);
        return dialog.show();
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

/* =============================================================================
   PERFORMANCE MONITORING
   ============================================================================= */

class PerformanceMonitor {
    constructor() {
        this.marks = {};
    }

    start(label) {
        this.marks[label] = performance.now();
    }

    end(label) {
        if (!this.marks[label]) {
            console.warn(`No start mark for ${label}`);
            return null;
        }
        const duration = performance.now() - this.marks[label];
        delete this.marks[label];
        return duration;
    }

    measure(label, callback) {
        this.start(label);
        const result = callback();
        const duration = this.end(label);
        console.log(`${label}: ${duration.toFixed(2)}ms`);
        return result;
    }

    static getMetrics() {
        if (!window.performance || !performance.getEntriesByType) {
            return null;
        }
        
        const paintEntries = performance.getEntriesByType('paint');
        const navigationTiming = performance.getEntriesByType('navigation')[0];
        
        return {
            paint: paintEntries,
            navigation: navigationTiming,
            memory: performance.memory ? {
                usedJSHeapSize: performance.memory.usedJSHeapSize,
                totalJSHeapSize: performance.memory.totalJSHeapSize,
                jsHeapSizeLimit: performance.memory.jsHeapSizeLimit
            } : null
        };
    }
}

const perfMonitor = new PerformanceMonitor();

/* =============================================================================
   EVENT DELEGATION MANAGER
   ============================================================================= */

class EventManager {
    constructor() {
        this.listeners = new Map();
    }

    on(element, event, selector, callback) {
        if (!element) return;
        
        const handler = (e) => {
            if (selector) {
                const target = e.target.closest(selector);
                if (target) callback.call(target, e);
            } else {
                callback.call(element, e);
            }
        };
        
        const key = `${element}:${event}:${selector}`;
        if (!this.listeners.has(element)) {
            this.listeners.set(element, []);
        }
        
        this.listeners.get(element).push({ event, handler, key });
        element.addEventListener(event, handler);
    }

    off(element, event, selector) {
        if (!this.listeners.has(element)) return;
        
        const handlers = this.listeners.get(element);
        const key = `${element}:${event}:${selector}`;
        
        handlers.forEach((item, index) => {
            if (item.key === key) {
                element.removeEventListener(item.event, item.handler);
                handlers.splice(index, 1);
            }
        });
    }

    offAll(element) {
        if (!this.listeners.has(element)) return;
        
        const handlers = this.listeners.get(element);
        handlers.forEach(item => {
            element.removeEventListener(item.event, item.handler);
        });
        this.listeners.delete(element);
    }

    clear() {
        this.listeners.forEach((handlers, element) => {
            handlers.forEach(item => {
                element.removeEventListener(item.event, item.handler);
            });
        });
        this.listeners.clear();
    }
}

const eventManager = new EventManager();

/* =============================================================================
   DOM UTILITIES
   ============================================================================= */

class DOMUtils {
    static createElement(tag, className, innerHTML) {
        const el = document.createElement(tag);
        if (className) el.className = className;
        if (innerHTML) el.innerHTML = innerHTML;
        return el;
    }

    static addClass(element, ...classes) {
        element.classList.add(...classes);
        return element;
    }

    static removeClass(element, ...classes) {
        element.classList.remove(...classes);
        return element;
    }

    static toggleClass(element, className, force) {
        element.classList.toggle(className, force);
        return element;
    }

    static hasClass(element, className) {
        return element.classList.contains(className);
    }

    static setStyle(element, styles) {
        Object.assign(element.style, styles);
        return element;
    }

    static setAttribute(element, name, value) {
        element.setAttribute(name, value);
        return element;
    }

    static getAttr(element, name) {
        return element.getAttribute(name);
    }

    static removeAttr(element, name) {
        element.removeAttribute(name);
        return element;
    }

    static show(element) {
        element.style.display = '';
        return element;
    }

    static hide(element) {
        element.style.display = 'none';
        return element;
    }

    static isVisible(element) {
        return element.offsetParent !== null;
    }

    static animate(element, animation, duration = 300) {
        return new Promise((resolve) => {
            element.addEventListener('animationend', resolve, { once: true });
            element.style.animation = `${animation} ${duration}ms ease-out`;
        });
    }

    static fadeIn(element, duration = 300) {
        element.style.animation = `fadeIn ${duration}ms ease-out`;
        return element;
    }

    static fadeOut(element, duration = 300) {
        element.style.animation = `fadeOut ${duration}ms ease-out`;
        return element;
    }

    static slideInUp(element, duration = 300) {
        element.style.animation = `slideInUp ${duration}ms ease-out`;
        return element;
    }

    static slideInDown(element, duration = 300) {
        element.style.animation = `slideInDown ${duration}ms ease-out`;
        return element;
    }

    static slideInLeft(element, duration = 300) {
        element.style.animation = `slideInLeft ${duration}ms ease-out`;
        return element;
    }

    static slideInRight(element, duration = 300) {
        element.style.animation = `slideInRight ${duration}ms ease-out`;
        return element;
    }

    static scaleIn(element, duration = 300) {
        element.style.animation = `scaleIn ${duration}ms ease-out`;
        return element;
    }

    static remove(element) {
        if (element && element.parentNode) {
            element.parentNode.removeChild(element);
        }
        return element;
    }

    static removeAllChildren(element) {
        while (element.firstChild) {
            element.removeChild(element.firstChild);
        }
        return element;
    }

    static escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
}

/* =============================================================================
   KEYBOARD SHORTCUT MANAGER
   ============================================================================= */

class KeyboardManager {
    constructor() {
        this.shortcuts = new Map();
        this.keysPressed = new Set();
        this.setup();
    }

    setup() {
        document.addEventListener('keydown', (e) => this.handleKeyDown(e));
        document.addEventListener('keyup', (e) => this.handleKeyUp(e));
    }

    handleKeyDown(e) {
        const key = this.normalizeKey(e.key);
        this.keysPressed.add(key);
        
        const combo = this.getCombo();
        if (this.shortcuts.has(combo)) {
            e.preventDefault();
            this.shortcuts.get(combo)();
        }
    }

    handleKeyUp(e) {
        const key = this.normalizeKey(e.key);
        this.keysPressed.delete(key);
    }

    register(combo, callback) {
        this.shortcuts.set(combo.toLowerCase(), callback);
    }

    unregister(combo) {
        this.shortcuts.delete(combo.toLowerCase());
    }

    getCombo() {
        const keys = Array.from(this.keysPressed).sort();
        return keys.join('+').toLowerCase();
    }

    normalizeKey(key) {
        const map = {
            'Control': 'ctrl',
            'Meta': 'cmd',
            'Shift': 'shift',
            'Alt': 'alt',
            ' ': 'space',
            'Enter': 'enter',
            'Backspace': 'backspace',
            'Delete': 'delete',
            'Escape': 'escape'
        };
        return map[key] || key.toLowerCase();
    }

    isCtrlPressed() {
        return this.keysPressed.has('ctrl') || this.keysPressed.has('cmd');
    }

    isShiftPressed() {
        return this.keysPressed.has('shift');
    }

    isAltPressed() {
        return this.keysPressed.has('alt');
    }

    clear() {
        this.shortcuts.clear();
        this.keysPressed.clear();
    }
}

const keyboardManager = new KeyboardManager();

/* =============================================================================
   STORAGE MANAGER
   ============================================================================= */

class StorageManager {
    static set(key, value, isLocal = true) {
        try {
            const storage = isLocal ? localStorage : sessionStorage;
            storage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error('Storage error:', e);
            return false;
        }
    }

    static get(key, isLocal = true) {
        try {
            const storage = isLocal ? localStorage : sessionStorage;
            const item = storage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            console.error('Storage error:', e);
            return null;
        }
    }

    static remove(key, isLocal = true) {
        try {
            const storage = isLocal ? localStorage : sessionStorage;
            storage.removeItem(key);
            return true;
        } catch (e) {
            console.error('Storage error:', e);
            return false;
        }
    }

    static clear(isLocal = true) {
        try {
            const storage = isLocal ? localStorage : sessionStorage;
            storage.clear();
            return true;
        } catch (e) {
            console.error('Storage error:', e);
            return false;
        }
    }

    static exists(key, isLocal = true) {
        const storage = isLocal ? localStorage : sessionStorage;
        return storage.getItem(key) !== null;
    }
}

/* =============================================================================
   NETWORK REQUEST MANAGER
   ============================================================================= */

class NetworkManager {
    static getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : null;
    }

    static async get(url, options = {}) {
        return this.request(url, { ...options, method: 'GET' });
    }

    static async post(url, data, options = {}) {
        return this.request(url, {
            ...options,
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                ...options.headers
            },
            body: JSON.stringify(data)
        });
    }

    static async put(url, data, options = {}) {
        return this.request(url, {
            ...options,
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                ...options.headers
            },
            body: JSON.stringify(data)
        });
    }

    static async delete(url, options = {}) {
        return this.request(url, {
            ...options,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': this.getCsrfToken(),
                ...options.headers
            }
        });
    }

    static async request(url, options = {}) {
        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                ...options
            });

            const contentType = response.headers.get('content-type');
            let data;

            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                data = await response.text();
            }

            if (!response.ok) {
                const error = new Error(data.message || `HTTP ${response.status}`);
                error.status = response.status;
                error.data = data;
                throw error;
            }

            return {
                success: true,
                status: response.status,
                data: data
            };
        } catch (error) {
            return {
                success: false,
                status: error.status || 0,
                error: error.message,
                data: error.data
            };
        }
    }
}

/* =============================================================================
   EXPORT UTILITIES
   ============================================================================= */

class ExportUtils {
    static downloadJSON(data, filename = 'data.json') {
        const json = JSON.stringify(data, null, 2);
        const blob = new Blob([json], { type: 'application/json' });
        this.downloadBlob(blob, filename);
    }

    static downloadText(text, filename = 'data.txt') {
        const blob = new Blob([text], { type: 'text/plain' });
        this.downloadBlob(blob, filename);
    }

    static downloadCSV(data, filename = 'data.csv') {
        const csv = this.arrayToCSV(data);
        const blob = new Blob([csv], { type: 'text/csv' });
        this.downloadBlob(blob, filename);
    }

    static downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    static arrayToCSV(array) {
        if (!array || array.length === 0) return '';
        
        const headers = Object.keys(array[0]);
        const rows = array.map(obj => {
            return headers.map(header => {
                const value = obj[header];
                return typeof value === 'string' && value.includes(',')
                    ? `"${value}"`
                    : value;
            }).join(',');
        });
        
        return [headers.join(','), ...rows].join('\n');
    }

    static copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        } else {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            return Promise.resolve();
        }
    }
}

/* =============================================================================
   INITIALIZATION
   ============================================================================= */

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        console.log('🎨 Utilitarios ArtisanFlow carregados com sucesso');
    });
} else {
    console.log('🎨 Utilitarios ArtisanFlow carregados com sucesso');
}

// Make utilities globally available (namespace + atalhos para módulos Vite separados)
window.AF = {
    debounce,
    throttle,
    toastManager,
    ModalDialog,
    perfMonitor,
    eventManager,
    DOMUtils,
    keyboardManager,
    StorageManager,
    NetworkManager,
    ExportUtils,
    requestAnimFrame,
    cancelAnimFrame
};

window.toastManager = toastManager;
window.ModalDialog = ModalDialog;
window.DOMUtils = DOMUtils;
window.NetworkManager = NetworkManager;
window.ExportUtils = ExportUtils;
window.keyboardManager = keyboardManager;
window.StorageManager = StorageManager;
