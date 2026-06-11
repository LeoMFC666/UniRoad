const ArtisanFlow = {
    data: {
        nodes: [],
        connections: []
    },
    modules: [],
    activeModuleId: null,

    dragging: null,
    resizing: null,
    connecting: null,
    selectedNodeId: null,
    selectedNodeIds: new Set(),
    selectedConnectionId: null,
    clipboard: null,
    pasteCount: 0,
    selectionBox: null,
    contextMenu: null,
    longPress: null,
    suppressCanvasClick: false,
    suppressNodeClick: false,
    toolMode: "select",
    viewport: { x: 0, y: 0, scale: 1 },
    zoom: { min: 0.35, max: 2.5, step: 0.0018 },
    panning: null,
    touchPointers: new Map(),
    pinchGesture: null,
    history: [],
    redoHistory: [],
    historyLimit: 80,
    textEditing: null,
    savedSignature: "",
    allowNavigation: false,
    pendingNavigationUrl: null,
    unsavedGuardsReady: false,
    activeNodeNoteEditor: null,

    labels: {
        start: { title: "Inicio", content: "Comece aqui", kind: "Inicio" },
        end: { title: "Fim", content: "Encerramento", kind: "Fim" },
        milestone: { title: "Novo marco", content: "Checkpoint importante", kind: "Marco" },
        task: { title: "Nova tarefa", content: "Descreva a atividade", kind: "Tarefa" },
        activity: { title: "Nova atividade", content: "Descreva a entrega esperada", kind: "Atividade" },
        decision: { title: "Nova decisao", content: "Defina a condicao", kind: "Decisao" },
        goal: { title: "Novo objetivo", content: "Resultado esperado", kind: "Objetivo" },
        resource: { title: "Novo recurso", content: "Material de apoio", kind: "Recurso" },
        note: { title: "Nova nota", content: "Observacao para o aluno", kind: "Nota" },
        text: { title: "Inserir um titulo", content: "", kind: "Texto" },
        attachment: { title: "Novo anexo", content: "Arquivo de texto", kind: "Anexo" },
        image: { title: "Imagem", content: "", kind: "Imagem" },
        video: { title: "Vídeo do YouTube", content: "Assista direto no roadmap", kind: "Vídeo" }
    },

    init() {
        this.setupModules(window.initialGraph || { nodes: [], connections: [] });
        this.normalizeNodes({ seedDefaults: this.shouldSeedDefaultNodes() });
        this.renderModules();
        this.render();
        this.applyViewport();
        this.updateToolButtons();

        const canvasWrap = document.querySelector(".af-canvas-wrap");
        canvasWrap?.addEventListener("pointerdown", this.onCanvasPointerDown.bind(this));
        canvasWrap?.addEventListener("wheel", this.onCanvasWheel.bind(this), { passive: false });
        document.addEventListener("pointerdown", event => {
            if (!event.target.closest?.("[contenteditable='true']")) {
                document.activeElement?.blur?.();
            }
        }, true);
        document.addEventListener("pointermove", this.onPointerMove.bind(this));
        document.addEventListener("pointerup", this.onPointerUp.bind(this));
        document.addEventListener("pointercancel", this.onPointerUp.bind(this));
        document.addEventListener("keydown", this.onKeyDown.bind(this));
        this.setupUnsavedGuards();
        this.setupToolsDrawer();
        document.addEventListener("click", event => {
            if (!event.target.closest?.(".af-context-menu")) {
                this.hideContextMenu();
            }
        });
        document.addEventListener("auxclick", event => {
            if (event.button === 1 && event.target.closest?.(".af-canvas-wrap")) {
                event.preventDefault();
            }
        });
        window.addEventListener("blur", () => this.hideContextMenu());
        window.setTimeout(() => this.captureSavedState(), 0);
    },

    setupModules(graph = { nodes: [], connections: [] }) {
        const normalized = this.normalizeGraphModules(graph);

        this.modules = normalized.modules;
        this.activeModuleId = normalized.activeModuleId;
        this.data = this.getActiveModule();
        this.viewport = this.normalizeViewport(this.data.viewport);
    },

    normalizeGraphModules(graph) {
        const parsed = graph?.conteudo || graph?.data || graph || {};

        if (Array.isArray(parsed.modules) && parsed.modules.length) {
            const modules = parsed.modules.map((module, index) => ({
                id: String(module.id || `module-${index + 1}`),
                title: module.title || module.name || `Módulo ${index + 1}`,
                nodes: Array.isArray(module.nodes) && module.nodes.length ? module.nodes : this.createDefaultNodes(),
                connections: Array.isArray(module.connections) ? module.connections : [],
                viewport: this.normalizeViewport(module.viewport)
            }));
            const fallbackId = modules[0]?.id || "module-1";
            const activeModuleId = modules.some(module => module.id === parsed.activeModuleId)
                ? parsed.activeModuleId
                : fallbackId;

            return { modules, activeModuleId };
        }

        return {
            modules: [{
                id: "module-1",
                title: "Módulo 1",
                nodes: Array.isArray(parsed.nodes) ? parsed.nodes : [],
                connections: Array.isArray(parsed.connections) ? parsed.connections : [],
                viewport: this.normalizeViewport(parsed.viewport)
            }],
            activeModuleId: "module-1"
        };
    },

    getActiveModule() {
        if (!this.modules.length) {
            this.modules = [{
                id: "module-1",
                title: "Módulo 1",
                nodes: [],
                connections: [],
                viewport: { x: 0, y: 0, scale: 1 }
            }];
            this.activeModuleId = "module-1";
        }

        const module = this.modules.find(item => item.id === this.activeModuleId) || this.modules[0];
        this.activeModuleId = module.id;
        module.nodes = Array.isArray(module.nodes) ? module.nodes : [];
        module.connections = Array.isArray(module.connections) ? module.connections : [];
        module.viewport = this.normalizeViewport(module.viewport);

        return module;
    },

    shouldSeedDefaultNodes() {
        return this.modules.length === 1
            && this.getActiveModule().nodes.length === 0;
    },

    createDefaultNodes() {
        return [
            {
                id: crypto.randomUUID(),
                x: 160,
                y: 180,
                width: 190,
                height: 92,
                type: "start",
                title: "Inicio",
                content: "Primeiro passo"
            },
            {
                id: crypto.randomUUID(),
                x: 520,
                y: 180,
                width: 190,
                height: 92,
                type: "end",
                title: "Fim",
                content: "Conclusao"
            }
        ];
    },

    isProtectedNode(nodeOrId) {
        const node = typeof nodeOrId === "string"
            ? this.data.nodes.find(item => item.id === nodeOrId)
            : nodeOrId;

        return node?.type === "start" || node?.type === "end";
    },

    getEditableSelectedIds() {
        return this.getSelectedNodeIds().filter(id => !this.isProtectedNode(id));
    },

    commitActiveModule() {
        const module = this.getActiveModule();

        module.nodes = Array.isArray(this.data.nodes) ? this.data.nodes : [];
        module.connections = Array.isArray(this.data.connections) ? this.data.connections : [];
        module.viewport = { ...this.viewport };
    },

    loadGraph(graph) {
        this.setupModules(graph || { nodes: [], connections: [] });
        this.normalizeNodes({ seedDefaults: this.shouldSeedDefaultNodes() });
        this.resetTransientState();
        this.history = [];
        this.redoHistory = [];
        this.renderModules();
        this.render();
        this.applyViewport();
        window.setTimeout(() => this.captureSavedState(), 0);
    },

    resetTransientState() {
        this.selectedNodeId = null;
        this.selectedNodeIds = new Set();
        this.selectedConnectionId = null;
        this.connecting = null;
        this.dragging = null;
        this.resizing = null;
        this.selectionBox?.element?.remove();
        this.selectionBox = null;
        this.hideContextMenu();
    },

    normalizeNodes(options = {}) {
        this.data.nodes = (this.data.nodes || []).map((node, index) => {
            const normalized = {
                x: node.x ?? 120 + (index * 230),
                y: node.y ?? 100 + (index * 90),
                width: node.width ?? 220,
                height: node.height ?? 126,
                type: node.type ?? "task",
                title: node.title ?? node.text ?? "Novo bloco",
                content: node.content ?? node.description ?? "Descreva esta etapa",
                ...node
            };

            normalized.notes = this.normalizeNodeNotes(normalized.notes);

            if (normalized.type === "text") {
                normalized.fontSize = this.normalizeTextFontSize(normalized.fontSize);
            }

            return normalized;
        });

        if (options.seedDefaults && this.data.nodes.length === 0) {
            this.data.nodes = this.createDefaultNodes();
        }

        this.data.connections = this.data.connections || [];
        this.selectedNodeIds = this.selectedNodeIds || new Set();
    },

    normalizeNodeNotes(notes = []) {
        return Array.isArray(notes)
            ? notes.map((note, index) => ({
                id: String(note.id || crypto.randomUUID()),
                title: note.title || note.name || `Nota ${index + 1}`,
                body: note.body || note.content || note.description || "",
                resources: Array.isArray(note.resources) ? note.resources : []
            }))
            : [];
    },

    normalizeTextFontSize(value) {
        const size = Number(value) || 40;
        return Math.min(96, Math.max(24, size));
    },

    normalizeViewport(value = {}) {
        const scale = Number(value?.scale) || 1;

        return {
            x: Number(value?.x) || 0,
            y: Number(value?.y) || 0,
            scale: Math.min(this.zoom.max, Math.max(this.zoom.min, scale))
        };
    },

    renderModules() {
        const strip = document.getElementById("af-module-strip");

        if (!strip) return;

        strip.innerHTML = "";

        this.modules.forEach((module, index) => {
            const button = document.createElement("button");

            button.type = "button";
            button.className = `af-module-tab${module.id === this.activeModuleId ? " is-active" : ""}`;
            button.textContent = module.title || `Módulo ${index + 1}`;
            button.title = "Abrir modulo. Duplo clique para renomear.";
            button.addEventListener("click", () => this.switchModule(module.id));
            button.addEventListener("dblclick", () => this.renameModule(module.id));

            strip.appendChild(button);
        });

        const addButton = document.createElement("button");

        addButton.type = "button";
        addButton.className = "af-module-add";
        addButton.textContent = "+";
        addButton.title = "Criar novo modulo";
        addButton.addEventListener("click", () => this.addModule());

        strip.appendChild(addButton);
    },

    switchModule(id) {
        if (id === this.activeModuleId) return;

        const nextModule = this.modules.find(module => module.id === id);
        if (!nextModule) return;

        this.commitActiveModule();
        this.activeModuleId = id;
        this.data = this.getActiveModule();
        this.viewport = this.normalizeViewport(this.data.viewport);
        this.resetTransientState();
        this.history = [];
        this.redoHistory = [];
        this.normalizeNodes({ seedDefaults: false });
        this.renderModules();
        this.render();
        this.applyViewport();
        this.updateStatus(`${this.data.title || "Módulo"} aberto`);
    },

    addModule() {
        this.commitActiveModule();

        const module = {
            id: crypto.randomUUID(),
            title: `Módulo ${this.modules.length + 1}`,
            nodes: this.createDefaultNodes(),
            connections: [],
            viewport: { x: 0, y: 0, scale: 1 }
        };

        this.modules.push(module);
        this.activeModuleId = module.id;
        this.data = module;
        this.viewport = { x: 0, y: 0, scale: 1 };
        this.resetTransientState();
        this.history = [];
        this.redoHistory = [];
        this.renderModules();
        this.render();
        this.applyViewport();
        this.updateStatus(`${module.title} criado`);
    },

    renameModule(id) {
        const module = this.modules.find(item => item.id === id);
        if (!module) return;

        const nextTitle = window.prompt("Nome do módulo", module.title || "Módulo");
        if (!nextTitle?.trim()) return;

        module.title = nextTitle.trim();
        this.renderModules();
        this.updateStatus("Módulo renomeado");
    },

    render() {
        const canvas = document.getElementById("roadmap-canvas");

        if (!canvas) {
            console.error("Canvas #roadmap-canvas não encontrado");
            return;
        }

        canvas.innerHTML = "";
        canvas.onclick = event => {
            if (this.suppressCanvasClick) {
                this.suppressCanvasClick = false;
                return;
            }

            if (event.target === canvas) {
                this.clearSelection();
            }
        };

        this.data.nodes.forEach(node => {
            const el = document.createElement("div");
            const isSelected = this.isNodeSelected(node.id);

            el.className = `roadmap-node type-${node.type || "task"}${isSelected ? " is-selected" : ""}`;
            el.dataset.nodeId = node.id;
            el.dataset.type = node.type || "task";
            el.dataset.title = node.title || "";
            el.dataset.content = node.content || "";
            if (node.videoUrl) el.dataset.videoUrl = node.videoUrl;
            if (node.videoEmbedUrl) el.dataset.videoEmbedUrl = node.videoEmbedUrl;
            el.style.left = `${node.x}px`;
            el.style.top = `${node.y}px`;
            el.style.width = `${node.width}px`;
            el.style.height = `${node.height}px`;

            el.innerHTML = this.getNodeMarkup(node);

            el.querySelector(".af-node-title")?.addEventListener("input", event => {
                node.title = event.target.innerText.trim();
                this.updateStatus("Texto atualizado");
            });

            el.querySelector(".af-node-content")?.addEventListener("input", event => {
                node.content = event.target.innerText.trim();
                this.updateStatus("Descrição atualizada");
            });

            el.querySelectorAll("[contenteditable='true']").forEach(editable => {
                editable.addEventListener("focus", () => {
                    this.textEditing = {
                        before: this.getGraphSnapshot(),
                        text: this.getNodeTextSignature(node)
                    };
                });

                editable.addEventListener("blur", () => {
                    if (this.textEditing && this.textEditing.text !== this.getNodeTextSignature(node)) {
                        this.pushHistorySnapshot(this.textEditing.before);
                        this.updateStatus("Texto alterado");
                    }

                    this.textEditing = null;
                });
            });

            el.querySelector('[data-action="remove"]')?.addEventListener("click", event => {
                event.stopPropagation();
                this.removeNode(node.id);
            });

            el.querySelector('[data-action="note-add"]')?.addEventListener("click", event => {
                event.stopPropagation();
                this.openNodeNoteModal(node.id);
            });

            el.querySelector('[data-action="activity-file"]')?.addEventListener("click", event => {
                event.stopPropagation();
                this.openActivityAttachmentPicker(node.id);
            });

            el.querySelectorAll('[data-action="activity-file-remove"]').forEach(button => {
                button.addEventListener("click", event => {
                    event.stopPropagation();
                    this.removeActivityAttachment(node.id, button.dataset.attachmentId);
                });
            });

            el.querySelectorAll(".af-node-note-button").forEach(button => {
                button.addEventListener("click", event => {
                    event.stopPropagation();
                    this.openNodeNoteModal(node.id, button.dataset.noteId);
                });
            });

            el.querySelector(".af-font-size-select")?.addEventListener("change", event => {
                event.stopPropagation();
                this.saveHistory();
                node.fontSize = this.normalizeTextFontSize(event.target.value);
                this.render();
                this.selectNode(node.id);
                this.updateStatus("Tamanho do texto atualizado");
            });

            el.querySelector(".af-font-size-control")?.addEventListener("pointerdown", event => {
                event.stopPropagation();
            });

            el.addEventListener("click", event => {
                if (this.suppressNodeClick) {
                    this.suppressNodeClick = false;
                    return;
                }

                if (
                    event.target.closest(".af-port") ||
                    event.target.closest(".af-resize-handle") ||
                    event.target.closest('[data-action="remove"]') ||
                    event.target.closest(".af-font-size-control")
                ) {
                    return;
                }

                this.selectNode(node.id, event.ctrlKey || event.metaKey || event.shiftKey);
            });

            el.addEventListener("contextmenu", event => {
                event.preventDefault();
                event.stopPropagation();

                if (!this.isNodeSelected(node.id)) {
                    this.selectNode(node.id, event.ctrlKey || event.metaKey || event.shiftKey);
                }

                this.showContextMenu(event.clientX, event.clientY);
            });

            el.addEventListener("pointerdown", event => {
                if (event.button === 1) {
                    event.preventDefault();
                    this.hideContextMenu();
                    this.startPan(event);
                    return;
                }

                if (this.toolMode === "pan") {
                    if (event.target.closest("button, .af-port, .af-resize-handle")) {
                        return;
                    }

                    event.preventDefault();
                    this.hideContextMenu();
                    this.startPan(event);
                    return;
                }

                if (
                    event.target.closest(".af-resize-handle") ||
                    event.target.closest(".af-port") ||
                    event.target.closest("[contenteditable]") ||
                    event.target.closest("button") ||
                    event.target.closest("a") ||
                    event.target.closest("input, textarea, select, label")
                ) {
                    return;
                }

                event.preventDefault();
                this.hideContextMenu();

                const additiveSelection = event.ctrlKey || event.metaKey || event.shiftKey;

                if (additiveSelection || !this.isNodeSelected(node.id)) {
                    this.selectNode(node.id, additiveSelection);
                }

                this.scheduleLongPress(event, {
                    type: "node",
                    nodeId: node.id
                });

                this.dragging = {
                    id: node.id,
                    startX: event.clientX,
                    startY: event.clientY,
                    nodes: this.getDragNodes(node.id),
                    moved: false,
                    before: this.getGraphSnapshot()
                };
            });

            el.querySelector(".af-resize-handle").addEventListener("pointerdown", event => {
                event.stopPropagation();
                event.preventDefault();

                this.resizing = {
                    id: node.id,
                    startX: event.clientX,
                    startY: event.clientY,
                    width: node.width,
                    height: node.height,
                    before: this.getGraphSnapshot()
                };
            });

            el.querySelectorAll(".af-port").forEach(port => {
                port.addEventListener("pointerdown", event => {
                    event.stopPropagation();
                    event.preventDefault();

                    this.connecting = {
                        from: node.id,
                        fromPort: port.dataset.port,
                        x: event.clientX,
                        y: event.clientY
                    };

                    this.updateStatus("Solte em outro conector para criar o caminho");
                });
            });

            canvas.appendChild(el);
        });

        this.drawConnections();
        this.updateUI();
        this.applyViewport();
    },

    getNodeMarkup(node) {
        const ports = `
            <span class="af-port top" data-port="top" title="Conectar pelo topo">↑</span>
            <span class="af-port right" data-port="right" title="Conectar pela direita">→</span>
            <span class="af-port bottom" data-port="bottom" title="Conectar por baixo">↓</span>
            <span class="af-port left" data-port="left" title="Conectar pela esquerda">←</span>
        `;
        const removeAction = this.isProtectedNode(node)
            ? ""
            : `<button type="button" data-action="remove" title="Remover bloco">Remover</button>`;
        const canHaveNotes = !["image", "text", "video"].includes(node.type);
        const addNoteAction = canHaveNotes
            ? `<button type="button" class="af-node-note-add" data-action="note-add" title="Adicionar nota ao bloco">+</button>`
            : "";
        const noteButtons = canHaveNotes ? this.getNodeNotesMarkup(node) : "";
        const fontSizeControl = node.type === "text" ? this.getTextFontSizeControl(node) : "";
        const activityAttachments = node.type === "activity" ? this.getActivityAttachmentsMarkup(node) : "";
        const activityAttachAction = node.type === "activity"
            ? `<button type="button" data-action="activity-file" title="Adicionar anexo da atividade">Anexar</button>`
            : "";

        if (node.type === "image") {
            return `
                ${ports}
                <div class="af-image-body">
                    <img class="af-node-image" src="${this.escapeHTML(node.imageSrc || "")}" alt="${this.escapeHTML(node.title || "Imagem")}">
                </div>
                <span class="af-resize-handle" title="Redimensionar"></span>
            `;
        }

        if (node.type === "video") {
            const video = this.normalizeYouTubeUrl(node.videoUrl || node.content || node.videoEmbedUrl || "");

            return `
                ${ports}
                <div class="af-video-body">
                    ${video ? `
                        <iframe
                            class="af-node-video"
                            src="${this.escapeHTML(video.embedUrl)}"
                            title="${this.escapeHTML(node.title || "Vídeo do YouTube")}"
                            loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen></iframe>
                    ` : `
                        <div class="af-video-placeholder">
                            <span>${this.escapeHTML(node.title || "Vídeo")}</span>
                            <small>URL do YouTube indisponivel</small>
                        </div>
                    `}
                </div>
                <span class="af-resize-handle" title="Redimensionar"></span>
            `;
        }

        if (node.type === "attachment") {
            return `
                ${ports}
                <div class="af-node-body">
                    <div class="af-node-meta">
                        <span class="af-node-kind">${this.escapeHTML(this.getNodeKind(node.type))}</span>
                    </div>
                    <div class="af-node-title" contenteditable="true" spellcheck="true">${this.escapeHTML(node.title)}</div>
                    <div class="af-node-content" contenteditable="true" spellcheck="true">${this.escapeHTML(node.content || "")}</div>
                    ${noteButtons}

                    <div class="af-node-actions">
                        ${addNoteAction}
                        <a class="af-download-link" href="${this.escapeHTML(node.fileData || "#")}" download="${this.escapeHTML(node.fileName || node.title || "anexo")}" title="Baixar anexo">Baixar</a>
                        <button type="button" data-action="remove" title="Remover bloco">Remover</button>
                    </div>
                </div>

                <span class="af-resize-handle" title="Redimensionar"></span>
            `;
        }

        return `
            ${ports}
            <div class="af-node-body">
                <div class="af-node-meta">
                    <span class="af-node-kind">${this.escapeHTML(this.getNodeKind(node.type))}</span>
                </div>
                <div class="af-node-title" contenteditable="true" spellcheck="true" style="${node.type === "text" ? `font-size:${this.normalizeTextFontSize(node.fontSize)}px` : ""}">${this.escapeHTML(node.title)}</div>
                <div class="af-node-content" contenteditable="true" spellcheck="true">${this.escapeHTML(node.content || "")}</div>
                ${activityAttachments}
                ${noteButtons}

                <div class="af-node-actions">
                    ${fontSizeControl}
                    ${activityAttachAction}
                    ${addNoteAction}
                    ${removeAction}
                </div>
            </div>

            <span class="af-resize-handle" title="Redimensionar"></span>
        `;
    },

    getTextFontSizeControl(node) {
        const current = this.normalizeTextFontSize(node.fontSize);
        const options = [
            [26, "Pequeno"],
            [34, "Medio"],
            [42, "Grande"],
            [54, "Extra"]
        ];

        return `
            <label class="af-font-size-control" title="Tamanho da fonte">
                <span>Tamanho</span>
                <select class="af-font-size-select">
                    ${options.map(([value, label]) => `<option value="${value}"${value === current ? " selected" : ""}>${label}</option>`).join("")}
                </select>
            </label>
        `;
    },

    normalizeActivityAttachments(attachments = []) {
        return Array.isArray(attachments)
            ? attachments
                .filter(item => item && (item.fileData || item.name || item.fileName))
                .map((item, index) => ({
                    id: String(item.id || `activity-file-${index}`),
                    name: item.name || item.fileName || `Anexo ${index + 1}`,
                    type: item.type || item.fileType || "",
                    size: Number(item.size || 0),
                    fileData: item.fileData || "",
                    preview: item.preview || item.content || ""
                }))
            : [];
    },

    getActivityAttachmentsMarkup(node) {
        const attachments = this.normalizeActivityAttachments(node.activityAttachments);

        if (!attachments.length) {
            return `<div class="af-activity-attachments is-empty">Nenhum anexo da atividade.</div>`;
        }

        return `
            <div class="af-activity-attachments">
                ${attachments.map(attachment => `
                    <div class="af-activity-file">
                        <a href="${this.escapeHTML(attachment.fileData || "#")}" download="${this.escapeHTML(attachment.name)}" title="Baixar ${this.escapeHTML(attachment.name)}">${this.escapeHTML(attachment.name)}</a>
                        <button type="button" data-action="activity-file-remove" data-attachment-id="${this.escapeHTML(attachment.id)}" title="Remover anexo">×</button>
                    </div>
                `).join("")}
            </div>
        `;
    },

    openActivityAttachmentPicker(nodeId) {
        let input = document.getElementById("af-activity-upload");

        if (!input) {
            input = document.createElement("input");
            input.type = "file";
            input.id = "af-activity-upload";
            input.multiple = true;
            input.hidden = true;
            input.accept = ".txt,.md,.rtf,.doc,.docx,.pdf,.csv,.json,.xml,.html,.odt,image/*";
            document.body.appendChild(input);
        }

        input.onchange = () => {
            this.handleActivityAttachmentFiles(nodeId, input.files);
            input.value = "";
        };
        input.click();
    },

    async handleActivityAttachmentFiles(nodeId, files) {
        const node = this.data.nodes.find(item => item.id === nodeId);
        const list = Array.from(files || []);

        if (!node || !list.length) return;

        this.saveHistory();
        node.activityAttachments = this.normalizeActivityAttachments(node.activityAttachments);

        for (const file of list) {
            const fileData = await this.readFileAsDataURL(file);
            const preview = await this.readFilePreview(file);

            node.activityAttachments.push({
                id: crypto.randomUUID(),
                name: file.name,
                type: file.type,
                size: file.size,
                fileData,
                preview
            });
        }

        this.render();
        this.selectNode(node.id);
        this.updateStatus(`${list.length} anexo${list.length > 1 ? "s" : ""} da atividade adicionado${list.length > 1 ? "s" : ""}`);
    },

    removeActivityAttachment(nodeId, attachmentId) {
        const node = this.data.nodes.find(item => item.id === nodeId);

        if (!node || !attachmentId) return;

        this.saveHistory();
        node.activityAttachments = this.normalizeActivityAttachments(node.activityAttachments)
            .filter(item => item.id !== attachmentId);
        this.render();
        this.selectNode(node.id);
        this.updateStatus("Anexo da atividade removido");
    },

    getNodeNotesMarkup(node) {
        const notes = this.normalizeNodeNotes(node.notes);
        if (!notes.length) return "";

        return `
            <div class="af-node-note-list">
                ${notes.map(note => `
                    <button type="button" class="af-node-note-button" data-note-id="${this.escapeHTML(note.id)}" title="Abrir nota">
                        ${this.escapeHTML(note.title)}
                    </button>
                `).join("")}
            </div>
        `;
    },

    ensureNodeNoteModal() {
        let modal = document.getElementById("af-node-note-modal");

        if (modal) return modal;

        modal = document.createElement("div");
        modal.id = "af-node-note-modal";
        modal.className = "af-note-modal";
        modal.hidden = true;
        modal.innerHTML = `
            <div class="af-note-dialog" role="dialog" aria-modal="true" aria-labelledby="af-note-title-label">
                <button type="button" class="af-note-close" data-note-modal-action="close" title="Fechar">×</button>
                <label class="af-note-field">
                    <span>Titulo da nota</span>
                    <input id="af-note-title-input" type="text" maxlength="80" placeholder="Ex: Visual Design Principles">
                </label>
                <label class="af-note-field">
                    <span>Descrição</span>
                    <textarea id="af-note-body-input" rows="5" placeholder="Explique o conteudo desta nota."></textarea>
                </label>
                <label class="af-note-field">
                    <span>Recursos</span>
                    <textarea id="af-note-resources-input" rows="3" placeholder="Um recurso por linha"></textarea>
                </label>
                <div class="af-note-actions">
                    <button type="button" class="af-note-delete" data-note-modal-action="delete">Excluir nota</button>
                    <span></span>
                    <button type="button" class="af-note-secondary" data-note-modal-action="close">Cancelar</button>
                    <button type="button" class="af-note-primary" data-note-modal-action="save">Salvar nota</button>
                </div>
            </div>
        `;

        modal.addEventListener("click", event => {
            if (event.target === modal) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation?.();
                this.closeNodeNoteModal();
                return;
            }

            const action = event.target.closest("[data-note-modal-action]")?.dataset.noteModalAction;
            if (!action) return;

            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation?.();
            if (action === "close") this.closeNodeNoteModal();
            if (action === "save") this.saveNodeNoteFromModal();
            if (action === "delete") this.deleteNodeNoteFromModal();
        });

        document.body.appendChild(modal);
        return modal;
    },

    openNodeNoteModal(nodeId, noteId = null) {
        const node = this.data.nodes.find(item => item.id === nodeId);
        if (!node) return;

        node.notes = this.normalizeNodeNotes(node.notes);

        const note = noteId
            ? node.notes.find(item => item.id === noteId)
            : null;
        const modal = this.ensureNodeNoteModal();

        this.activeNodeNoteEditor = { nodeId, noteId: note?.id || null };

        modal.querySelector("#af-note-title-input").value = note?.title || "Nova nota";
        modal.querySelector("#af-note-body-input").value = note?.body || "";
        modal.querySelector("#af-note-resources-input").value = (note?.resources || []).join("\n");
        modal.querySelector("[data-note-modal-action='delete']").hidden = !note;
        modal.hidden = false;
        modal.querySelector("#af-note-title-input")?.focus();
    },

    closeNodeNoteModal() {
        const modal = document.getElementById("af-node-note-modal");
        if (modal) modal.hidden = true;
        this.activeNodeNoteEditor = null;
    },

    saveNodeNoteFromModal() {
        const modal = document.getElementById("af-node-note-modal");
        const state = this.activeNodeNoteEditor;
        if (!modal || !state) return;

        const node = this.data.nodes.find(item => item.id === state.nodeId);
        if (!node) return;

        const title = modal.querySelector("#af-note-title-input")?.value?.trim() || "Nova nota";
        const body = modal.querySelector("#af-note-body-input")?.value?.trim() || "";
        const resources = (modal.querySelector("#af-note-resources-input")?.value || "")
            .split(/\r?\n/)
            .map(item => item.trim())
            .filter(Boolean);

        this.saveHistory();
        node.notes = this.normalizeNodeNotes(node.notes);

        if (state.noteId) {
            const note = node.notes.find(item => item.id === state.noteId);
            if (note) {
                note.title = title;
                note.body = body;
                note.resources = resources;
            }
        } else {
            node.notes.push({
                id: crypto.randomUUID(),
                title,
                body,
                resources
            });
        }

        this.closeNodeNoteModal();
        this.render();
        this.selectNode(node.id);
        this.updateStatus("Nota salva no bloco");
    },

    deleteNodeNoteFromModal() {
        const state = this.activeNodeNoteEditor;
        if (!state?.noteId) return;

        const node = this.data.nodes.find(item => item.id === state.nodeId);
        if (!node) return;

        this.saveHistory();
        node.notes = this.normalizeNodeNotes(node.notes).filter(note => note.id !== state.noteId);
        this.closeNodeNoteModal();
        this.render();
        this.selectNode(node.id);
        this.updateStatus("Nota removida do bloco");
    },

    addNode(type = "task") {
        const preset = this.labels[type] || this.labels.task;
        const offset = this.data.nodes.length * 42;
        const width = type === "note" ? 280 : (type === "activity" ? 340 : (type === "text" ? 260 : 220));
        const height = type === "note" ? 150 : (type === "activity" ? 210 : (type === "text" ? 100 : 126));
        const start = this.getNewNodePosition(offset, { width, height });
        const id = crypto.randomUUID();
        this.saveHistory();

        this.data.nodes.push({
            id,
            type,
            title: preset.title,
            content: preset.content,
            x: start.x,
            y: start.y,
            width,
            height,
            notes: [],
            ...(type === "text" ? { fontSize: 40 } : {}),
            ...(type === "activity" ? { activityAttachments: [] } : {})
        });

        this.selectedNodeId = id;
        this.selectedNodeIds = new Set([id]);
        this.render();
        this.updateStatus(`${preset.kind} adicionado`);
    },

    triggerAttachmentUpload() {
        document.getElementById("af-attachment-upload")?.click();
    },

    triggerImageUpload() {
        document.getElementById("af-image-upload")?.click();
    },

    ensureVideoModal() {
        let modal = document.getElementById("af-video-modal");

        if (modal) return modal;

        modal = document.createElement("div");
        modal.id = "af-video-modal";
        modal.className = "af-video-modal";
        modal.hidden = true;
        modal.innerHTML = `
            <div class="af-video-dialog" role="dialog" aria-modal="true" aria-labelledby="af-video-modal-title">
                <button type="button" class="af-video-close" data-video-action="close" title="Fechar">×</button>
                <h2 id="af-video-modal-title">Adicionar video do YouTube</h2>
                <p>Cole a URL do video para inserir um player direto no roadmap.</p>
                <label class="af-video-field">
                    <span>URL do YouTube</span>
                    <input id="af-video-url-input" type="url" inputmode="url" placeholder="https://www.youtube.com/watch?v=..." autocomplete="off">
                </label>
                <div class="af-video-error" id="af-video-error" hidden>Informe uma URL valida do YouTube.</div>
                <div class="af-video-actions">
                    <button type="button" class="af-video-secondary" data-video-action="close">Cancelar</button>
                    <button type="button" class="af-video-primary" data-video-action="save">Adicionar video</button>
                </div>
            </div>
        `;

        modal.addEventListener("click", event => {
            if (event.target === modal) {
                event.preventDefault();
                this.closeVideoModal();
                return;
            }

            const action = event.target.closest("[data-video-action]")?.dataset.videoAction;
            if (!action) return;

            event.preventDefault();
            event.stopPropagation();

            if (action === "close") this.closeVideoModal();
            if (action === "save") this.saveVideoFromModal();
        });

        modal.addEventListener("keydown", event => {
            if (event.key === "Enter") {
                event.preventDefault();
                this.saveVideoFromModal();
            }

            if (event.key === "Escape") {
                event.preventDefault();
                this.closeVideoModal();
            }
        });

        document.body.appendChild(modal);
        return modal;
    },

    openVideoModal() {
        const modal = this.ensureVideoModal();
        const input = modal.querySelector("#af-video-url-input");
        const error = modal.querySelector("#af-video-error");

        if (input) input.value = "";
        if (error) error.hidden = true;

        modal.hidden = false;
        window.setTimeout(() => input?.focus(), 0);
    },

    closeVideoModal() {
        const modal = document.getElementById("af-video-modal");
        if (modal) modal.hidden = true;
    },

    saveVideoFromModal() {
        const modal = document.getElementById("af-video-modal");
        const input = modal?.querySelector("#af-video-url-input");
        const error = modal?.querySelector("#af-video-error");
        const video = this.normalizeYouTubeUrl(input?.value || "");

        if (!video) {
            if (error) error.hidden = false;
            input?.focus();
            return;
        }

        this.addVideoNode(video);
        this.closeVideoModal();
    },

    addVideoNode(video) {
        const offset = this.data.nodes.length * 42;
        const start = this.getNewNodePosition(offset, { width: 420, height: 236 });
        const id = crypto.randomUUID();

        this.saveHistory();
        this.data.nodes.push({
            id,
            type: "video",
            title: "Vídeo do YouTube",
            content: video.url,
            videoUrl: video.url,
            videoId: video.id,
            videoEmbedUrl: video.embedUrl,
            x: start.x,
            y: start.y,
            width: 420,
            height: 236,
            notes: []
        });

        this.selectedNodeId = id;
        this.selectedNodeIds = new Set([id]);
        this.render();
        this.updateStatus("Vídeo adicionado");
    },

    normalizeYouTubeUrl(value) {
        const raw = String(value || "").trim();
        if (!raw) return null;

        const idFromText = raw.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/|live\/))([A-Za-z0-9_-]{11})/)?.[1]
            || raw.match(/^[A-Za-z0-9_-]{11}$/)?.[0];

        let videoId = idFromText || null;

        try {
            const url = new URL(raw);
            const host = url.hostname.replace(/^www\./, "");
            const isYouTube = host === "youtube.com" || host === "m.youtube.com" || host === "youtu.be" || host === "youtube-nocookie.com";

            if (!isYouTube) return null;

            if (!videoId && host === "youtu.be") {
                videoId = url.pathname.split("/").filter(Boolean)[0] || null;
            }

            if (!videoId && (host === "youtube.com" || host === "m.youtube.com")) {
                videoId = url.searchParams.get("v")
                    || url.pathname.match(/^\/(?:embed|shorts|live)\/([A-Za-z0-9_-]{11})/)?.[1]
                    || null;
            }
        } catch (_) {
            if (!videoId) return null;
        }

        if (!/^[A-Za-z0-9_-]{11}$/.test(videoId || "")) return null;

        return {
            id: videoId,
            url: `https://www.youtube.com/watch?v=${videoId}`,
            embedUrl: `https://www.youtube-nocookie.com/embed/${videoId}`
        };
    },

    async handleAttachmentFiles(files) {
        const list = Array.from(files || []);
        if (!list.length) return;

        this.saveHistory();

        for (const file of list) {
            const dataUrl = await this.readFileAsDataURL(file);
            const textPreview = await this.readFilePreview(file);
            const offset = this.data.nodes.length * 42;
            const start = this.getNewNodePosition(offset, { width: 260, height: 126 });

            this.data.nodes.push({
                id: crypto.randomUUID(),
                type: "attachment",
                title: file.name,
                content: textPreview || `${file.type || "Arquivo"} - ${Math.ceil(file.size / 1024)} KB`,
                fileName: file.name,
                fileType: file.type,
                fileData: dataUrl,
                x: start.x,
                y: start.y,
                width: 260,
                height: 126
            });
        }

        this.render();
        this.updateStatus(`${list.length} anexo${list.length > 1 ? "s" : ""} adicionado${list.length > 1 ? "s" : ""}`);
    },

    async handleImageFiles(files) {
        const list = Array.from(files || []);
        if (!list.length) return;

        this.saveHistory();
        this.updateStatus(`Otimizando ${list.length} imagem${list.length > 1 ? "s" : ""}...`);

        for (const file of list) {
            const image = await this.readImageAsOptimizedDataURL(file);
            const offset = this.data.nodes.length * 42;
            const width = Math.min(420, Math.max(180, Math.round((image.width || 780) / 3)));
            const height = Math.min(300, Math.max(120, Math.round((image.height || 540) / 3)));
            const start = this.getNewNodePosition(offset, { width, height });

            this.data.nodes.push({
                id: crypto.randomUUID(),
                type: "image",
                title: file.name,
                content: "",
                imageSrc: image.dataUrl,
                fileName: file.name,
                fileType: image.type || file.type || "image/jpeg",
                originalSize: file.size,
                optimizedSize: image.size,
                optimized: image.optimized,
                x: start.x,
                y: start.y,
                width,
                height
            });
        }

        this.render();
        this.updateStatus(`${list.length} imagem${list.length > 1 ? "s" : ""} adicionada${list.length > 1 ? "s" : ""}`);
    },

    readImageAsOptimizedDataURL(file) {
        const fallback = async () => {
            const dataUrl = await this.readFileAsDataURL(file);

            return {
                dataUrl,
                width: 780,
                height: 540,
                size: Math.ceil(String(dataUrl).length * 0.75),
                type: file.type || "image/*",
                optimized: false
            };
        };

        if (!file?.type?.startsWith("image/") || file.type === "image/svg+xml" || file.type === "image/gif") {
            return fallback();
        }

        return new Promise(resolve => {
            const image = new Image();
            const objectUrl = URL.createObjectURL(file);

            image.onload = () => {
                try {
                    const sourceWidth = image.naturalWidth || image.width || 780;
                    const sourceHeight = image.naturalHeight || image.height || 540;
                    const maxSide = 1000;
                    const ratio = Math.min(1, maxSide / Math.max(sourceWidth, sourceHeight));
                    const width = Math.max(1, Math.round(sourceWidth * ratio));
                    const height = Math.max(1, Math.round(sourceHeight * ratio));
                    const canvas = document.createElement("canvas");
                    const context = canvas.getContext("2d", { willReadFrequently: true });

                    canvas.width = width;
                    canvas.height = height;

                    context.clearRect(0, 0, width, height);
                    context.drawImage(image, 0, 0, width, height);

                    const hasTransparency = this.canvasHasTransparentPixels(context, width, height);
                    let type = "image/jpeg";
                    let dataUrl = "";

                    if (hasTransparency) {
                        dataUrl = canvas.toDataURL("image/webp", 0.82);
                        type = dataUrl.startsWith("data:image/webp") ? "image/webp" : "image/png";

                        if (type === "image/png") {
                            dataUrl = canvas.toDataURL("image/png");
                        }
                    } else {
                        const exportCanvas = document.createElement("canvas");
                        const exportContext = exportCanvas.getContext("2d", { alpha: false });

                        exportCanvas.width = width;
                        exportCanvas.height = height;
                        exportContext.fillStyle = "#ffffff";
                        exportContext.fillRect(0, 0, width, height);
                        exportContext.drawImage(canvas, 0, 0);

                        dataUrl = exportCanvas.toDataURL("image/jpeg", 0.76);
                    }

                    URL.revokeObjectURL(objectUrl);

                    resolve({
                        dataUrl,
                        width,
                        height,
                        size: Math.ceil(dataUrl.length * 0.75),
                        type,
                        optimized: true,
                        transparent: hasTransparency
                    });
                } catch (error) {
                    console.warn("Não foi possível otimizar a imagem, usando arquivo original.", error);
                    URL.revokeObjectURL(objectUrl);
                    resolve(fallback());
                }
            };

            image.onerror = () => {
                URL.revokeObjectURL(objectUrl);
                resolve(fallback());
            };

            image.src = objectUrl;
        });
    },

    setupToolsDrawer() {
        const toggle = document.querySelector("[data-roadmap-tools-toggle]");
        const panel = document.getElementById(toggle?.getAttribute("aria-controls") || "af-roadmap-tools")
            || document.querySelector(".af-sidebar");

        if (!toggle || !panel) return;

        const setOpen = (open) => {
            document.body.classList.toggle("af-tools-open", open);
            toggle.setAttribute("aria-expanded", open ? "true" : "false");
        };

        toggle.addEventListener("click", event => {
            event.preventDefault();
            event.stopPropagation();
            setOpen(!document.body.classList.contains("af-tools-open"));
        });

        document.addEventListener("pointerdown", event => {
            if (!document.body.classList.contains("af-tools-open")) return;
            if (toggle.contains(event.target) || panel.contains(event.target)) return;

            setOpen(false);
        }, true);

        document.addEventListener("keydown", event => {
            if (event.key === "Escape") {
                setOpen(false);
            }
        });

        panel.addEventListener("click", event => {
            if (event.target.closest?.(".af-element")) {
                window.setTimeout(() => setOpen(false), 0);
            }
        });
    },

    canvasHasTransparentPixels(context, width, height) {
        try {
            const pixels = context.getImageData(0, 0, width, height).data;

            for (let index = 3; index < pixels.length; index += 4) {
                if (pixels[index] < 255) {
                    return true;
                }
            }
        } catch (error) {
            console.warn("Não foi possível detectar transparência da imagem.", error);
        }

        return false;
    },

    readFileAsDataURL(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    },

    readFileAsText(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(String(reader.result || ""));
            reader.onerror = reject;
            reader.readAsText(file);
        });
    },

    async readFilePreview(file) {
        const name = file.name.toLowerCase();
        const looksTextual = file.type.startsWith("text/") || /\.(txt|md|csv|json|xml|html|css|js|php|blade|rtf|log)$/i.test(name);

        if (!looksTextual) {
            return "";
        }

        try {
            const text = await this.readFileAsText(file);
            return text.replace(/\s+/g, " ").trim().slice(0, 180);
        } catch (error) {
            return "";
        }
    },

    getNewNodePosition(offset = 0, size = {}) {
        const canvasWrap = document.querySelector(".af-canvas-wrap");
        const canvasBox = canvasWrap?.getBoundingClientRect();
        const scale = this.viewport.scale || 1;
        const width = Number(size.width) || 220;
        const height = Number(size.height) || 126;
        const spread = Math.min(offset, 126) / scale;

        if (!canvasBox) {
            return {
                x: 120 + spread,
                y: 120 + spread
            };
        }

        const centerX = (canvasBox.width / 2 - this.viewport.x) / scale;
        const centerY = (canvasBox.height / 2 - this.viewport.y) / scale;

        return {
            x: Math.max(24, centerX - width / 2 + spread),
            y: Math.max(24, centerY - height / 2 + spread)
        };
    },

    removeNode(id) {
        if (this.isProtectedNode(id)) {
            this.updateStatus("Início e Fim não podem ser removidos");
            return;
        }

        this.saveHistory();
        this.data.nodes = this.data.nodes.filter(node => node.id !== id);
        this.data.connections = this.data.connections.filter(
            connection => connection.from !== id && connection.to !== id
        );

        if (this.selectedNodeId === id) {
            this.selectedNodeId = null;
        }
        this.selectedNodeIds.delete(id);

        this.render();
        this.updateStatus("Bloco removido");
    },

    selectNode(id, additive = false) {
        if (!additive) {
            this.selectedNodeIds.clear();
        }

        if (additive && this.selectedNodeIds.has(id)) {
            this.selectedNodeIds.delete(id);
            this.selectedNodeId = this.getSelectedNodeIds().at(-1) || null;
        } else {
            this.selectedNodeIds.add(id);
            this.selectedNodeId = id;
        }

        if (this.selectedNodeIds.size === 0) {
            this.selectedNodeId = null;
        }

        this.applySelection();
        this.updateStatus(this.selectedNodeIds.size > 1 ? `${this.selectedNodeIds.size} blocos selecionados` : "Bloco selecionado");
    },

    selectAllNodes() {
        this.selectedNodeIds = new Set(this.data.nodes.map(node => node.id));
        this.selectedNodeId = this.getSelectedNodeIds().at(-1) || null;
        this.applySelection();
        this.updateStatus(`${this.selectedNodeIds.size} blocos selecionados`);
    },

    isNodeSelected(id) {
        return this.selectedNodeIds.has(id) || this.selectedNodeId === id;
    },

    getNodeTextSignature(node) {
        return JSON.stringify({
            title: node?.title || "",
            content: node?.content || ""
        });
    },

    getSelectedNodeIds() {
        return Array.from(this.selectedNodeIds);
    },

    getDragNodes(activeNodeId) {
        if (!this.isNodeSelected(activeNodeId)) {
            return [{
                id: activeNodeId,
                originX: this.data.nodes.find(node => node.id === activeNodeId)?.x || 0,
                originY: this.data.nodes.find(node => node.id === activeNodeId)?.y || 0
            }];
        }

        const selected = new Set(this.getSelectedNodeIds());

        return this.data.nodes
            .filter(node => selected.has(node.id))
            .map(node => ({
                id: node.id,
                originX: node.x,
                originY: node.y
            }));
    },

    clearSelection() {
        if (!this.selectedNodeId && this.selectedNodeIds.size === 0 && !this.selectedConnectionId) return;

        this.selectedNodeId = null;
        this.selectedNodeIds.clear();
        this.selectedConnectionId = null;
        this.applySelection();
        this.drawConnections();
        this.updateStatus("Selecao limpa");
    },

    applySelection() {
        document.querySelectorAll(".roadmap-node").forEach(node => {
            node.classList.toggle("is-selected", this.isNodeSelected(node.dataset.nodeId));
        });
    },

    selectConnection(id) {
        this.selectedConnectionId = id;
        this.selectedNodeId = null;
        this.selectedNodeIds.clear();
        this.applySelection();
        this.drawConnections();
        this.updateStatus("Conexao selecionada");
    },

    copySelectedNodes() {
        const selectedIds = this.getEditableSelectedIds();
        if (!selectedIds.length) {
            this.updateStatus("Início e Fim não podem ser copiados");
            return false;
        }

        const selectedSet = new Set(selectedIds);
        this.clipboard = {
            nodes: this.data.nodes
                .filter(node => selectedSet.has(node.id))
                .map(node => ({ ...node })),
            connections: this.data.connections
                .filter(connection => selectedSet.has(connection.from) && selectedSet.has(connection.to))
                .map(connection => ({ ...connection }))
        };
        this.pasteCount = 0;
        this.updateStatus(`${selectedIds.length} bloco${selectedIds.length > 1 ? "s" : ""} copiado${selectedIds.length > 1 ? "s" : ""}`);
        return true;
    },

    cutSelectedNodes() {
        const selectedCount = this.getEditableSelectedIds().length;
        if (!this.copySelectedNodes()) return;

        this.deleteSelectedNodes();
        this.updateStatus(`${selectedCount} bloco${selectedCount > 1 ? "s" : ""} recortado${selectedCount > 1 ? "s" : ""}`);
    },

    pasteClipboard(pastePoint = null) {
        if (!this.clipboard?.nodes?.length) {
            this.updateStatus("Nada para colar");
            return;
        }

        this.saveHistory();
        const idMap = new Map();
        const offset = 36 * (++this.pasteCount);
        const bounds = this.clipboard.nodes.reduce((acc, node) => ({
            minX: Math.min(acc.minX, node.x),
            minY: Math.min(acc.minY, node.y),
            maxX: Math.max(acc.maxX, node.x + node.width),
            maxY: Math.max(acc.maxY, node.y + node.height)
        }), { minX: Infinity, minY: Infinity, maxX: -Infinity, maxY: -Infinity });
        const offsetX = pastePoint
            ? pastePoint.x - (bounds.minX + ((bounds.maxX - bounds.minX) / 2))
            : offset;
        const offsetY = pastePoint
            ? pastePoint.y - (bounds.minY + ((bounds.maxY - bounds.minY) / 2))
            : offset;
        const pastedNodes = this.clipboard.nodes.map(node => {
            const id = crypto.randomUUID();
            idMap.set(node.id, id);

            return {
                ...node,
                id,
                x: node.x + offsetX,
                y: node.y + offsetY
            };
        });

        const pastedConnections = (this.clipboard.connections || [])
            .filter(connection => idMap.has(connection.from) && idMap.has(connection.to))
            .map(connection => ({
                ...connection,
                id: crypto.randomUUID(),
                from: idMap.get(connection.from),
                to: idMap.get(connection.to)
            }));

        this.data.nodes.push(...pastedNodes);
        this.data.connections.push(...pastedConnections);
        this.selectedNodeIds = new Set(pastedNodes.map(node => node.id));
        this.selectedNodeId = pastedNodes.at(-1)?.id || null;
        this.render();
        this.updateStatus(`${pastedNodes.length} bloco${pastedNodes.length > 1 ? "s" : ""} colado${pastedNodes.length > 1 ? "s" : ""}`);
    },

    deleteSelectedNodes() {
        const selectedIds = this.getEditableSelectedIds();
        if (!selectedIds.length) {
            this.updateStatus("Início e Fim não podem ser removidos");
            return;
        }

        this.saveHistory();
        const selectedSet = new Set(selectedIds);
        this.data.nodes = this.data.nodes.filter(node => !selectedSet.has(node.id));
        this.data.connections = this.data.connections.filter(
            connection => !selectedSet.has(connection.from) && !selectedSet.has(connection.to)
        );
        this.selectedNodeId = null;
        this.selectedNodeIds.clear();
        this.hideContextMenu();
        this.render();
        this.updateStatus(`${selectedIds.length} bloco${selectedIds.length > 1 ? "s" : ""} removido${selectedIds.length > 1 ? "s" : ""}`);
    },

    deleteSelectedConnection() {
        if (!this.selectedConnectionId) return false;

        this.data.connections = this.data.connections.filter(connection => connection.id !== this.selectedConnectionId);
        this.selectedConnectionId = null;
        this.drawConnections();
        this.updateUI();
        this.updateStatus("Conexao removida");
        return true;
    },

    onKeyDown(event) {
        const key = event.key.toLowerCase();
        const command = event.ctrlKey || event.metaKey;

        if (command && key === "s") {
            event.preventDefault();
            if (this.activeNodeNoteEditor) {
                this.saveNodeNoteFromModal();
                return;
            }

            this.save({ redirect: false });
            return;
        }

        if (this.isTypingTarget(event.target)) return;

        if (command && key === "a") {
            event.preventDefault();
            this.selectAllNodes();
            return;
        }

        if (command && key === "z") {
            event.preventDefault();
            this.undo();
            return;
        }

        if (command && key === "y") {
            event.preventDefault();
            this.redo();
            return;
        }

        if (command && key === "c") {
            event.preventDefault();
            this.copySelectedNodes();
            return;
        }

        if (command && key === "x") {
            event.preventDefault();
            this.cutSelectedNodes();
            return;
        }

        if (command && key === "v") {
            event.preventDefault();
            this.pasteClipboard();
            return;
        }

        if (event.key === "Delete" || event.key === "Backspace") {
            event.preventDefault();
            if (this.deleteSelectedConnection()) {
                return;
            }
            this.deleteSelectedNodes();
            return;
        }

        if (event.key === "Escape") {
            this.connecting = null;
            this.hideContextMenu();
            this.clearSelection();
            this.drawConnections();
        }
    },

    isTypingTarget(target) {
        return Boolean(target?.closest?.("input, textarea, select, [contenteditable='true']"));
    },

    onCanvasPointerDown(event) {
        if (event.pointerType === "touch") {
            this.trackTouchPointer(event);

            if (this.touchPointers.size >= 2) {
                event.preventDefault();
                this.hideContextMenu();
                this.startPinchGesture();
                return;
            }
        }

        if (event.button === 1) {
            event.preventDefault();
            this.hideContextMenu();
            this.startPan(event);
            return;
        }

        if (event.pointerType === "touch" && !event.target.closest?.(".roadmap-node, button, a, input, textarea, select, [contenteditable='true']")) {
            event.preventDefault();
            this.hideContextMenu();
            this.scheduleLongPress(event, {
                type: "canvas",
                canvasPoint: this.screenToCanvas(event.clientX, event.clientY)
            });
            this.startPan(event);
            return;
        }

        if (this.toolMode === "pan") {
            if (event.button !== 0 || event.target.closest?.("button, a, input, textarea, select")) {
                return;
            }

            event.preventDefault();
            this.hideContextMenu();
            this.startPan(event);
            return;
        }

        if (event.button !== 0 || event.target.closest?.(".roadmap-node, button, a, input, textarea, select")) {
            return;
        }

        event.preventDefault();
        this.hideContextMenu();

        const wrap = document.querySelector(".af-canvas-wrap");
        if (!wrap) return;

        const box = document.createElement("div");
        box.className = "af-selection-box";
        wrap.appendChild(box);

        this.selectionBox = {
            element: box,
            startX: event.clientX,
            startY: event.clientY,
            additive: event.shiftKey || event.ctrlKey || event.metaKey,
            baseIds: new Set(this.getSelectedNodeIds()),
            moved: false
        };

        if (!this.selectionBox.additive) {
            this.selectedNodeIds.clear();
            this.selectedNodeId = null;
            this.applySelection();
        }
    },

    updateSelectionBox(event) {
        if (!this.selectionBox) return;

        const wrap = document.querySelector(".af-canvas-wrap");
        const wrapBox = wrap.getBoundingClientRect();
        const left = Math.min(this.selectionBox.startX, event.clientX);
        const top = Math.min(this.selectionBox.startY, event.clientY);
        const right = Math.max(this.selectionBox.startX, event.clientX);
        const bottom = Math.max(this.selectionBox.startY, event.clientY);
        const width = right - left;
        const height = bottom - top;

        if (width > 3 || height > 3) {
            this.selectionBox.moved = true;
        }

        Object.assign(this.selectionBox.element.style, {
            left: `${left - wrapBox.left}px`,
            top: `${top - wrapBox.top}px`,
            width: `${width}px`,
            height: `${height}px`
        });

        const nextSelection = new Set(this.selectionBox.additive ? this.selectionBox.baseIds : []);
        document.querySelectorAll(".roadmap-node").forEach(node => {
            const nodeBox = node.getBoundingClientRect();
            const intersects = nodeBox.left <= right && nodeBox.right >= left && nodeBox.top <= bottom && nodeBox.bottom >= top;
            if (intersects) {
                nextSelection.add(node.dataset.nodeId);
            }
        });

        this.selectedNodeIds = nextSelection;
        this.selectedNodeId = this.getSelectedNodeIds().at(-1) || null;
        this.applySelection();
    },

    finishSelectionBox() {
        if (!this.selectionBox) return;

        const moved = this.selectionBox.moved;
        this.selectionBox.element.remove();
        this.selectionBox = null;
        this.suppressCanvasClick = true;
        this.updateStatus(moved && this.selectedNodeIds.size ? `${this.selectedNodeIds.size} bloco${this.selectedNodeIds.size > 1 ? "s" : ""} selecionado${this.selectedNodeIds.size > 1 ? "s" : ""}` : "Selecao limpa");
    },

    scheduleLongPress(event, options = {}) {
        if (event.pointerType !== "touch") return;

        this.cancelLongPress();

        const pointerId = event.pointerId;
        const startX = event.clientX;
        const startY = event.clientY;

        this.longPress = {
            pointerId,
            startX,
            startY,
            clientX: startX,
            clientY: startY,
            type: options.type,
            nodeId: options.nodeId || null,
            canvasPoint: options.canvasPoint || null,
            timer: window.setTimeout(() => {
                if (!this.longPress || this.longPress.pointerId !== pointerId) return;
                this.openLongPressMenu();
            }, 520)
        };
    },

    updateLongPress(event) {
        if (!this.longPress || event.pointerId !== this.longPress.pointerId) return;

        const moved = Math.hypot(event.clientX - this.longPress.startX, event.clientY - this.longPress.startY);
        if (moved > 10) {
            this.cancelLongPress();
        }
    },

    cancelLongPress() {
        if (!this.longPress) return;

        window.clearTimeout(this.longPress.timer);
        this.longPress = null;
    },

    openLongPressMenu() {
        const press = this.longPress;
        if (!press) return;

        this.cancelLongPress();
        this.panning = null;
        this.dragging = null;
        this.resizing = null;
        this.connecting = null;
        this.selectionBox?.element?.remove();
        this.selectionBox = null;
        document.querySelector(".af-canvas-wrap")?.classList.remove("is-panning");

        if (press.type === "node" && press.nodeId) {
            if (!this.isNodeSelected(press.nodeId)) {
                this.selectNode(press.nodeId);
            }

            this.suppressNodeClick = true;
            this.showContextMenu(press.clientX, press.clientY, ["copy", "cut", "delete"]);
            return;
        }

        this.showContextMenu(press.clientX, press.clientY, ["paste"], press.canvasPoint || this.screenToCanvas(press.clientX, press.clientY));
    },

    showContextMenu(x, y, actions = null, pastePoint = null) {
        this.hideContextMenu();

        const menuActions = actions || ["copy", "cut", "delete"];
        if (!actions && !this.getSelectedNodeIds().length) return;

        const labels = {
            copy: "Copiar",
            cut: "Cortar",
            delete: "Deletar",
            paste: "Colar"
        };

        const menu = document.createElement("div");
        menu.className = "af-context-menu";
        menu.innerHTML = menuActions
            .map(action => `<button type="button" data-menu-action="${action}">${labels[action] || action}</button>`)
            .join("");

        menu.addEventListener("pointerdown", event => {
            event.stopPropagation();
        });

        menu.addEventListener("click", event => {
            const action = event.target.closest("button")?.dataset.menuAction;
            if (!action) return;

            event.preventDefault();
            event.stopPropagation();
            if (action === "copy") this.copySelectedNodes();
            if (action === "cut") this.cutSelectedNodes();
            if (action === "delete") this.deleteSelectedNodes();
            if (action === "paste") this.pasteClipboard(pastePoint);
            this.hideContextMenu();
        });

        document.body.appendChild(menu);
        const menuBox = menu.getBoundingClientRect();
        const left = Math.min(x, window.innerWidth - menuBox.width - 10);
        const top = Math.min(y, window.innerHeight - menuBox.height - 10);
        menu.style.left = `${Math.max(10, left)}px`;
        menu.style.top = `${Math.max(10, top)}px`;
        this.contextMenu = menu;
    },

    hideContextMenu() {
        this.contextMenu?.remove();
        this.contextMenu = null;
    },

    clearCanvas() {
        if (!this.data.nodes.length && !this.data.connections.length) {
            this.updateStatus("O canvas ja esta vazio");
            return;
        }

        if (!window.confirm("Limpar todos os blocos e conexões deste rascunho?")) {
            return;
        }

        this.saveHistory();
        this.data.nodes = [];
        this.data.connections = [];
        this.render();
        this.updateStatus("Canvas limpo");
    },

    getGraphSnapshot() {
        return JSON.parse(JSON.stringify({
            nodes: this.data.nodes || [],
            connections: this.data.connections || []
        }));
    },

    pushHistorySnapshot(snapshot) {
        if (!snapshot) return;

        this.history.push(snapshot);
        if (this.history.length > this.historyLimit) {
            this.history.shift();
        }
        this.redoHistory = [];
    },

    saveHistory() {
        this.pushHistorySnapshot(this.getGraphSnapshot());
    },

    restoreSnapshot(snapshot) {
        const restored = JSON.parse(JSON.stringify(snapshot || { nodes: [], connections: [] }));
        const module = this.getActiveModule();

        module.nodes = Array.isArray(restored.nodes) ? restored.nodes : [];
        module.connections = Array.isArray(restored.connections) ? restored.connections : [];
        this.data = module;
        this.selectedNodeId = null;
        this.selectedNodeIds.clear();
        this.selectedConnectionId = null;
        this.connecting = null;
        this.dragging = null;
        this.resizing = null;
        this.render();
    },

    undo() {
        const snapshot = this.history.pop();
        if (!snapshot) {
            this.updateStatus("Nada para desfazer");
            return;
        }

        this.redoHistory.push(this.getGraphSnapshot());
        this.restoreSnapshot(snapshot);
        this.updateStatus("Alteracao desfeita");
    },

    redo() {
        const snapshot = this.redoHistory.pop();
        if (!snapshot) {
            this.updateStatus("Nada para refazer");
            return;
        }

        this.history.push(this.getGraphSnapshot());
        this.restoreSnapshot(snapshot);
        this.updateStatus("Alteracao refeita");
    },

    setToolMode(mode) {
        this.toolMode = mode === "pan" ? "pan" : "select";
        this.connecting = null;
        this.selectionBox?.element?.remove();
        this.selectionBox = null;
        this.panning = null;
        this.hideContextMenu();
        this.updateToolButtons();
        this.updateStatus(this.toolMode === "pan" ? "Modo mao ativo" : "Modo selecao ativo");
    },

    togglePanMode() {
        this.setToolMode(this.toolMode === "pan" ? "select" : "pan");
    },

    updateToolButtons() {
        document.querySelectorAll("[data-tool-mode]").forEach(button => {
            const active = button.dataset.toolMode === this.toolMode;
            button.classList.toggle("is-active", active);
            button.setAttribute("aria-pressed", active ? "true" : "false");
        });

        document.querySelector(".af-canvas-wrap")?.classList.toggle("is-pan-mode", this.toolMode === "pan");
    },

    startPan(event) {
        if (this.pinchGesture) return;

        this.panning = {
            startX: event.clientX,
            startY: event.clientY,
            originX: this.viewport.x,
            originY: this.viewport.y
        };

        document.querySelector(".af-canvas-wrap")?.classList.add("is-panning");
    },

    finishPan() {
        this.panning = null;
        document.querySelector(".af-canvas-wrap")?.classList.remove("is-panning");
        this.updateStatus("Canvas reposicionado");
    },

    trackTouchPointer(event) {
        if (event.pointerType !== "touch") return;

        this.touchPointers.set(event.pointerId, {
            clientX: event.clientX,
            clientY: event.clientY
        });
    },

    releaseTouchPointer(event) {
        if (event.pointerType !== "touch") return false;

        const wasPinching = Boolean(this.pinchGesture);
        this.touchPointers.delete(event.pointerId);

        if (this.pinchGesture && this.touchPointers.size < 2) {
            this.finishPinchGesture();
        }

        return wasPinching;
    },

    getPinchPoints() {
        return Array.from(this.touchPointers.values()).slice(0, 2);
    },

    getPinchCenter(points) {
        return {
            clientX: (points[0].clientX + points[1].clientX) / 2,
            clientY: (points[0].clientY + points[1].clientY) / 2
        };
    },

    getPinchDistance(points) {
        return Math.hypot(points[1].clientX - points[0].clientX, points[1].clientY - points[0].clientY);
    },

    startPinchGesture() {
        this.cancelLongPress();

        const wrap = document.querySelector(".af-canvas-wrap");
        const rect = wrap?.getBoundingClientRect();
        const points = this.getPinchPoints();

        if (!rect || points.length < 2) return;

        const distance = this.getPinchDistance(points);
        if (distance < 8) return;

        const center = this.getPinchCenter(points);
        const centerX = center.clientX - rect.left;
        const centerY = center.clientY - rect.top;
        const startScale = this.viewport.scale || 1;

        this.panning = null;
        this.dragging = null;
        this.resizing = null;
        this.selectionBox?.element?.remove();
        this.selectionBox = null;
        this.connecting = null;

        this.pinchGesture = {
            startDistance: distance,
            startScale,
            canvasX: (centerX - this.viewport.x) / startScale,
            canvasY: (centerY - this.viewport.y) / startScale
        };

        wrap.classList.remove("is-panning");
        wrap.classList.add("is-pinching");
        this.drawConnections();
    },

    updatePinchGesture() {
        if (!this.pinchGesture) return;

        const wrap = document.querySelector(".af-canvas-wrap");
        const rect = wrap?.getBoundingClientRect();
        const points = this.getPinchPoints();

        if (!rect || points.length < 2) return;

        const distance = this.getPinchDistance(points);
        const center = this.getPinchCenter(points);
        const centerX = center.clientX - rect.left;
        const centerY = center.clientY - rect.top;
        const nextScale = Math.min(
            this.zoom.max,
            Math.max(this.zoom.min, this.pinchGesture.startScale * (distance / this.pinchGesture.startDistance))
        );

        this.viewport.scale = nextScale;
        this.viewport.x = centerX - this.pinchGesture.canvasX * nextScale;
        this.viewport.y = centerY - this.pinchGesture.canvasY * nextScale;
        this.applyViewport();
        this.updateStatus(`Zoom ${Math.round(nextScale * 100)}%`);
    },

    finishPinchGesture() {
        this.pinchGesture = null;
        document.querySelector(".af-canvas-wrap")?.classList.remove("is-pinching");
    },

    applyViewport() {
        this.viewport = this.normalizeViewport(this.viewport);
        const transform = `translate(${this.viewport.x}px, ${this.viewport.y}px) scale(${this.viewport.scale})`;

        document.getElementById("roadmap-canvas")?.style.setProperty("transform", transform);
        document.getElementById("af-connections")?.style.setProperty("transform", transform);
    },

    screenToCanvas(clientX, clientY) {
        const wrap = document.querySelector(".af-canvas-wrap");
        const parent = wrap?.getBoundingClientRect();

        if (!parent) {
            return { x: clientX, y: clientY };
        }

        return {
            x: (clientX - parent.left - this.viewport.x) / this.viewport.scale,
            y: (clientY - parent.top - this.viewport.y) / this.viewport.scale
        };
    },

    onCanvasWheel(event) {
        if (event.target.closest?.("input, textarea, select, [contenteditable='true'], .af-note-modal, .af-video-modal")) {
            return;
        }

        event.preventDefault();

        const wrap = document.querySelector(".af-canvas-wrap");
        const rect = wrap?.getBoundingClientRect();
        if (!rect) return;

        const oldScale = this.viewport.scale || 1;
        const nextScale = Math.min(
            this.zoom.max,
            Math.max(this.zoom.min, oldScale * Math.exp(-event.deltaY * this.zoom.step))
        );

        if (Math.abs(nextScale - oldScale) < 0.001) return;

        const mouseX = event.clientX - rect.left;
        const mouseY = event.clientY - rect.top;
        const canvasX = (mouseX - this.viewport.x) / oldScale;
        const canvasY = (mouseY - this.viewport.y) / oldScale;

        this.viewport.scale = nextScale;
        this.viewport.x = mouseX - canvasX * nextScale;
        this.viewport.y = mouseY - canvasY * nextScale;
        this.applyViewport();
        this.updateStatus(`Zoom ${Math.round(nextScale * 100)}%`);
    },

    onPointerMove(event) {
        this.updateLongPress(event);

        if (event.pointerType === "touch" && this.touchPointers.has(event.pointerId)) {
            this.trackTouchPointer(event);

            if (this.pinchGesture) {
                event.preventDefault();
                this.updatePinchGesture();
                return;
            }
        }

        if (this.panning) {
            event.preventDefault?.();
            this.viewport.x = this.panning.originX + (event.clientX - this.panning.startX);
            this.viewport.y = this.panning.originY + (event.clientY - this.panning.startY);
            this.applyViewport();
            return;
        }

        if (this.selectionBox) {
            this.updateSelectionBox(event);
        }

        if (this.dragging) {
            const deltaX = (event.clientX - this.dragging.startX) / this.viewport.scale;
            const deltaY = (event.clientY - this.dragging.startY) / this.viewport.scale;

            if (Math.abs(event.clientX - this.dragging.startX) > 3 || Math.abs(event.clientY - this.dragging.startY) > 3) {
                this.dragging.moved = true;
            }

            this.dragging.nodes.forEach(dragNode => {
                const node = this.data.nodes.find(item => item.id === dragNode.id);
                if (!node) return;

                node.x = dragNode.originX + deltaX;
                node.y = dragNode.originY + deltaY;

                const el = document.querySelector(`[data-node-id="${node.id}"]`);
                if (el) {
                    el.style.left = `${node.x}px`;
                    el.style.top = `${node.y}px`;
                }
            });

            this.drawConnections();
            this.updateMinimap();
        }

        if (this.resizing) {
            const node = this.data.nodes.find(item => item.id === this.resizing.id);
            if (!node) return;

            node.width = Math.max(160, this.resizing.width + ((event.clientX - this.resizing.startX) / this.viewport.scale));
            node.height = Math.max(100, this.resizing.height + ((event.clientY - this.resizing.startY) / this.viewport.scale));

            const el = document.querySelector(`[data-node-id="${node.id}"]`);
            if (el) {
                el.style.width = `${node.width}px`;
                el.style.height = `${node.height}px`;
            }

            this.drawConnections();
            this.updateMinimap();
        }

        if (this.connecting) {
            this.connecting.x = event.clientX;
            this.connecting.y = event.clientY;
            this.drawConnections();
        }
    },

    onPointerUp(event) {
        this.cancelLongPress();

        if (event.pointerType === "touch" && this.releaseTouchPointer(event)) {
            return;
        }

        if (this.panning) {
            this.finishPan();
            return;
        }

        if (this.selectionBox) {
            this.finishSelectionBox();
        }

        if (this.dragging) {
            if (this.dragging.moved) {
                this.suppressNodeClick = true;
            }

            this.updateStatus("Bloco reposicionado");
        }

        if (this.resizing) {
            this.updateStatus("Bloco redimensionado");
        }

        this.dragging = null;
        this.resizing = null;

        if (this.connecting) {
            const target = document.elementFromPoint(event.clientX, event.clientY);
            const port = target?.closest?.(".af-port");
            const nodeEl = port?.closest(".roadmap-node") || target?.closest?.(".roadmap-node");
            const to = nodeEl?.dataset?.nodeId;
            const toPort = port?.dataset?.port || this.getNearestPortName(nodeEl, event.clientX, event.clientY);

            if (to) {
                const duplicated = this.data.connections.some(connection =>
                    connection.from === this.connecting.from && connection.to === to
                );

                if (to && to !== this.connecting.from && !duplicated) {
                    this.data.connections.push({
                        id: crypto.randomUUID(),
                        from: this.connecting.from,
                        fromPort: this.connecting.fromPort,
                        to,
                        toPort
                    });

                    this.updateStatus("Conexao criada");
                } else if (duplicated) {
                    this.updateStatus("Esta conexao ja existe");
                }
            } else {
                this.updateStatus("Conexao cancelada");
            }

            this.connecting = null;
            this.drawConnections();
            this.updateUI();
        }
    },

    getPortPosition(nodeId, portName) {
        const node = this.data.nodes.find(item => item.id === nodeId);

        if (!node) return null;

        const positions = {
            top: {
                x: node.x + node.width / 2,
                y: node.y
            },
            right: {
                x: node.x + node.width,
                y: node.y + node.height / 2
            },
            bottom: {
                x: node.x + node.width / 2,
                y: node.y + node.height
            },
            left: {
                x: node.x,
                y: node.y + node.height / 2
            }
        };

        return positions[portName] || positions.left;
    },

    getNearestPortName(nodeEl, clientX, clientY) {
        if (!nodeEl) return "left";

        const box = nodeEl.getBoundingClientRect();
        const ports = {
            top: {
                x: box.left + box.width / 2,
                y: box.top
            },
            right: {
                x: box.right,
                y: box.top + box.height / 2
            },
            bottom: {
                x: box.left + box.width / 2,
                y: box.bottom
            },
            left: {
                x: box.left,
                y: box.top + box.height / 2
            }
        };

        return Object.entries(ports)
            .map(([name, point]) => ({
                name,
                distance: Math.hypot(clientX - point.x, clientY - point.y)
            }))
            .sort((a, b) => a.distance - b.distance)[0]?.name || "left";
    },

    drawConnections() {
        const svg = document.getElementById("af-connections");
        const wrap = document.querySelector(".af-canvas-wrap");

        if (!svg || !wrap) return;

        svg.innerHTML = "";

        this.data.connections.forEach(connection => {
            const start = this.getPortPosition(connection.from, connection.fromPort);
            const end = this.getPortPosition(connection.to, connection.toPort);

            if (!start || !end) return;

            svg.appendChild(this.makePath(start, end, false, connection));
        });

        if (this.connecting) {
            const start = this.getPortPosition(this.connecting.from, this.connecting.fromPort);
            if (!start) return;

            const end = this.screenToCanvas(this.connecting.x, this.connecting.y);

            svg.appendChild(this.makePath(start, end, true));
        }
    },

    makePath(start, end, preview = false, connection = null) {
        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
        const dx = Math.max(Math.abs(end.x - start.x) * 0.48, 48);
        const direction = end.x >= start.x ? 1 : -1;
        const d = `M ${start.x} ${start.y} C ${start.x + (dx * direction)} ${start.y}, ${end.x - (dx * direction)} ${end.y}, ${end.x} ${end.y}`;

        path.setAttribute("d", d);
        path.setAttribute("fill", "none");
        path.setAttribute("stroke", preview ? "#93c5fd" : "#60a5fa");
        path.setAttribute("stroke-width", connection?.id === this.selectedConnectionId ? "4" : (preview ? "2" : "2.5"));
        path.setAttribute("stroke-linecap", "round");
        path.style.pointerEvents = preview ? "none" : "stroke";
        path.style.cursor = preview ? "default" : "pointer";

        if (connection?.id === this.selectedConnectionId) {
            path.setAttribute("stroke", "#facc15");
        }

        if (connection) {
            const fromNode = this.data.nodes.find(node => node.id === connection.from);
            const toNode = this.data.nodes.find(node => node.id === connection.to);

            if (fromNode?.type === "note" || toNode?.type === "note") {
                path.setAttribute("stroke-dasharray", "7 6");
            }

            path.dataset.connectionId = connection.id;
            path.addEventListener("pointerdown", event => {
                event.stopPropagation();
                this.selectConnection(connection.id);
            });
        }

        if (preview) {
            path.setAttribute("stroke-dasharray", "7 6");
        }

        return path;
    },

    updateUI() {
        const empty = document.getElementById("af-empty-state");
        const nodeCount = document.getElementById("af-node-count");
        const connectionCount = document.getElementById("af-connection-count");

        if (empty) {
            empty.hidden = this.data.nodes.length > 0;
        }

        if (nodeCount) {
            nodeCount.textContent = this.data.nodes.length;
        }

        if (connectionCount) {
            connectionCount.textContent = this.data.connections.length;
        }

        window.artisanFlowData = this.getSerializableGraph();
        this.updateMinimap();
    },

    updateMinimap() {
        const minimap = document.getElementById("af-minimap");
        if (!minimap) return;

        minimap.querySelectorAll(".af-minimap-node").forEach(node => node.remove());

        if (!this.data.nodes.length) return;

        const maxX = Math.max(...this.data.nodes.map(node => node.x + node.width), 1);
        const maxY = Math.max(...this.data.nodes.map(node => node.y + node.height), 1);
        const scale = Math.min(154 / maxX, 86 / maxY, 0.22);

        this.data.nodes.forEach(node => {
            const marker = document.createElement("span");
            marker.className = "af-minimap-node";
            marker.style.left = `${12 + node.x * scale}px`;
            marker.style.top = `${28 + node.y * scale}px`;
            marker.style.width = `${Math.max(8, node.width * scale)}px`;
            marker.style.height = `${Math.max(5, node.height * scale)}px`;
            minimap.appendChild(marker);
        });
    },

    updateStatus(message) {
        const status = document.getElementById("af-editor-status");
        if (status) {
            status.textContent = message;
        }
    },

    setupUnsavedGuards() {
        if (this.unsavedGuardsReady) return;

        this.unsavedGuardsReady = true;

        document.addEventListener("click", event => {
            const link = event.target.closest?.("a[href]");

            if (!link || link.target === "_blank" || link.hasAttribute("download") || link.closest(".af-download-link")) {
                return;
            }

            const href = link.href;
            const samePage = href && href.split("#")[0] === window.location.href.split("#")[0];

            if (samePage || this.allowNavigation || !this.hasUnsavedChanges()) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation?.();
            this.showUnsavedModal(href);
        }, true);

        window.addEventListener("beforeunload", event => {
            if (this.allowNavigation || !this.hasUnsavedChanges()) {
                return;
            }

            event.preventDefault();
            event.returnValue = "";
        });

        this.bindUnsavedModal();
    },

    bindUnsavedModal() {
        const modal = document.getElementById("af-unsaved-modal");

        if (!modal) return;

        modal.querySelector("[data-unsaved-action='discard']")?.addEventListener("click", event => {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation?.();
            this.discardUnsavedChanges();
        });

        modal.querySelector("[data-unsaved-action='stay']")?.addEventListener("click", event => {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation?.();
            this.closeUnsavedModal();
        });

        modal.addEventListener("click", event => {
            if (event.target === modal) {
                this.closeUnsavedModal();
            }
        });
    },

    getUnsavedSignature() {
        return JSON.stringify({
            title: document.getElementById("roadmap-title")?.value?.trim() || "",
            graph: this.getSerializableGraph()
        });
    },

    captureSavedState() {
        this.savedSignature = this.getUnsavedSignature();
    },

    hasUnsavedChanges() {
        if (!this.savedSignature) {
            this.captureSavedState();
            return false;
        }

        return this.getUnsavedSignature() !== this.savedSignature;
    },

    showUnsavedModal(nextUrl = null) {
        const modal = document.getElementById("af-unsaved-modal");

        if (!modal) return;

        this.pendingNavigationUrl = nextUrl;
        modal.hidden = false;
        modal.querySelector("[data-unsaved-action='stay']")?.focus?.();
    },

    closeUnsavedModal() {
        const modal = document.getElementById("af-unsaved-modal");

        if (modal) {
            modal.hidden = true;
        }

        this.pendingNavigationUrl = null;
    },

    discardUnsavedChanges() {
        const nextUrl = this.pendingNavigationUrl || window.roadmapMeta?.indexUrl || "/";

        this.allowNavigation = true;
        this.closeUnsavedModal();
        window.location.href = nextUrl;
    },

    exportJSON() {
        const payload = this.getPayload();
        const blob = new Blob([JSON.stringify(payload, null, 2)], { type: "application/json" });
        const url = URL.createObjectURL(blob);
        const link = document.createElement("a");

        link.href = url;
        link.download = `${this.slugify(payload.titulo)}.json`;
        link.click();

        URL.revokeObjectURL(url);
        this.updateStatus("JSON exportado");
    },

    getSerializableGraph() {
        this.commitActiveModule();

        const modules = this.modules.map(module => ({
            id: module.id,
            title: module.title || "Módulo",
            nodes: Array.isArray(module.nodes) ? module.nodes : [],
            connections: Array.isArray(module.connections) ? module.connections : [],
            viewport: this.normalizeViewport(module.viewport)
        }));
        const first = modules[0] || {
            id: "module-1",
            nodes: [],
            connections: [],
            viewport: { x: 0, y: 0, scale: 1 }
        };

        return {
            nodes: first.nodes,
            connections: first.connections,
            modules,
            activeModuleId: first.id || "module-1",
            viewport: this.normalizeViewport(first.viewport)
        };
    },

    async save(options = {}) {
        const shouldRedirect = options.redirect !== false;

        if (window.editorMode === "edit" && typeof window.saveRoadmapEdit === "function") {
            await window.saveRoadmapEdit({ redirect: shouldRedirect });
            return;
        }

        const meta = window.roadmapMeta || {};
        const endpoint = meta.storeUrl || meta.updateUrl || (
            meta.id && meta.turmaId ? `/turmas/${meta.turmaId}/roadmaps/${meta.id}` : null
        );
        const method = meta.id ? "PUT" : "POST";

        if (!endpoint) {
            this.updateStatus("Endpoint de salvamento não encontrado");
            return;
        }

        const title = document.getElementById("roadmap-title")?.value?.trim();
        if (!title) {
            this.updateStatus("Informe um titulo antes de salvar");
            document.getElementById("roadmap-title")?.focus();
            return;
        }

        this.updateStatus("Salvando roadmap...");

        try {
            const response = await fetch(endpoint, {
                method,
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || ""
                },
                body: JSON.stringify({
                    titulo: title,
                    descricao: "Criado no editor visual de roadmaps.",
                    conteudo_json: this.getSerializableGraph()
                })
            });

            if (!response.ok) {
                throw new Error(`Falha ao salvar (${response.status})`);
            }

            const result = await response.clone().json().catch(() => null);

            if (result?.id && !meta.id) {
                meta.id = result.id;
                if (result.updateUrl) meta.updateUrl = result.updateUrl;
                if (result.saveUrl) meta.saveUrl = result.saveUrl;
                if (result.showUrl) meta.showUrl = result.showUrl;
                if (result.updateUrl) meta.storeUrl = result.updateUrl;
                window.roadmapMeta = meta;
            }

            this.updateStatus("Roadmap salvo");
            this.captureSavedState();
            this.allowNavigation = shouldRedirect;

            if (shouldRedirect) {
                window.location.href = result?.showUrl || meta.showUrl || meta.indexUrl;
            }
        } catch (error) {
            console.error(error);
            this.updateStatus("Não foi possível salvar agora");
        }
    },

    getPayload() {
        return {
            titulo: document.getElementById("roadmap-title")?.value?.trim() || "roadmap",
            turmaId: window.turmaId,
            conteudo: this.getSerializableGraph()
        };
    },

    getNodeKind(type) {
        return this.labels[type]?.kind || "Bloco";
    },

    escapeHTML(value) {
        return String(value ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    },

    slugify(value) {
        return String(value || "roadmap")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/(^-|-$)/g, "") || "roadmap";
    }
};

window.ArtisanFlow = ArtisanFlow;

document.addEventListener("DOMContentLoaded", () => {
    ArtisanFlow.init();
});
