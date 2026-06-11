<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Criar Roadmap</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">

    @fonts
    @ddfsnStyles

    @vite([
        'resources/css/app.css',
        'resources/js/artisanflow-editor.js'
    ])

    <style>
        :root {
            --af-bg: #080a10;
            --af-panel: rgba(17, 21, 32, 0.86);
            --af-panel-strong: #111520;
            --af-ink: #f7f8ff;
            --af-muted: #778098;
            --af-line: rgba(148, 163, 184, 0.16);
            --af-brand: #22d3c5;
            --af-brand-dark: #0f766e;
            --af-danger: #fb7185;
            --af-canvas: #080a10;
            --af-shadow: 0 20px 54px rgba(0, 0, 0, 0.35);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--af-bg);
            color: var(--af-ink);
            font-family: Inter, Arial, sans-serif;
            overflow: hidden;
        }

        button:not(:disabled),
        [role="button"]:not(:disabled) {
            cursor: pointer;
        }

        .af-editor {
            height: 100vh;
            display: grid;
            grid-template-rows: 46px 1fr;
            background: var(--af-bg);
        }

        .af-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 0 14px;
            background: rgba(8, 10, 16, 0.96);
            border-bottom: 1px solid var(--af-line);
            backdrop-filter: blur(14px);
            z-index: 20;
        }

        .af-topbar :where(a, button),
        .af-toolbar-group :where(a, button) {
            min-height: 30px;
            border-radius: 8px !important;
            border: 1px solid rgba(148, 163, 184, 0.18) !important;
            background: rgba(17, 21, 32, 0.72) !important;
            color: var(--af-ink) !important;
            box-shadow: none !important;
        }

        .af-topbar :where(a, button):hover,
        .af-toolbar-group :where(a, button):hover {
            border-color: rgba(34, 211, 197, 0.42) !important;
            color: #ffffff !important;
        }

        .af-left,
        .af-right {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .af-logo {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid rgba(34, 211, 197, 0.28);
        }

        .af-title-stack {
            display: grid;
            gap: 2px;
            min-width: 170px;
        }

        .af-title-input {
            width: min(34vw, 360px);
            min-width: 150px;
            border: 0;
            border-bottom: 1px solid transparent;
            background: transparent;
            color: var(--af-ink);
            font-size: 14px;
            font-weight: 800;
            line-height: 1.2;
            outline: none;
            padding: 0;
        }

        .af-title-input:focus {
            border-bottom-color: rgba(15, 118, 110, 0.45);
        }

        .af-kicker {
            color: var(--af-muted);
            font-size: 10px;
            line-height: 1;
        }

        .af-workspace {
            display: grid;
            grid-template-columns: 286px 1fr;
            min-height: 0;
            position: relative;
        }

        .af-sidebar {
            min-height: 0;
            overflow-y: auto;
            padding: 14px;
            border-right: 1px solid var(--af-line);
            background: rgba(10, 13, 22, 0.92);
        }

        .af-side-stack {
            display: grid;
            gap: 14px;
        }

        .af-tool-switch {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 8px;
            border: 1px solid var(--af-line);
            border-radius: 8px;
            background: rgba(17, 21, 32, 0.86);
        }

        .af-tool-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 34px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            border-radius: 7px;
            background: rgba(8, 10, 16, 0.42);
            color: var(--af-muted);
            font-size: 12px;
            font-weight: 800;
            transition: background 150ms ease, border-color 150ms ease, color 150ms ease;
        }

        .af-tool-button.is-active {
            border-color: rgba(34, 211, 197, 0.5);
            background: rgba(34, 211, 197, 0.12);
            color: #5eead4;
        }

        .af-tool-icon {
            display: block;
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
            color: #f8fafc;
            pointer-events: none;
        }

        .af-tool-button:hover .af-tool-icon,
        .af-tool-button:focus-visible .af-tool-icon,
        .af-tool-button.is-active .af-tool-icon {
            color: #ffffff;
        }

        .af-toolbar-separator {
            width: 1px;
            height: 22px;
            background: rgba(148, 163, 184, 0.18);
        }

        .af-text-tool {
            min-width: 34px;
            font-size: 15px;
        }

        .af-history-button {
            min-width: 42px;
            min-height: 40px;
            font-size: 20px;
        }

        .af-sidebar [data-slot="card"] {
            background: rgba(17, 21, 32, 0.86) !important;
            color: var(--af-ink) !important;
            border-color: rgba(148, 163, 184, 0.16) !important;
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.22);
        }

        .af-sidebar [data-slot="heading"] {
            color: var(--af-ink) !important;
        }

        .af-sidebar [data-slot="card-header"],
        .af-sidebar [data-slot="card-body"] {
            color: var(--af-ink) !important;
        }

        .af-sidebar [role="alert"],
        .af-topbar [role="alert"] {
            border: 1px solid rgba(34, 211, 197, 0.14);
            background: rgba(34, 211, 197, 0.09) !important;
            color: #5eead4 !important;
        }

        .af-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .af-section-title h2,
        .af-section-title h3 {
            margin: 0;
            font-size: 14px;
            line-height: 1.2;
            color: var(--af-ink);
        }

        .af-element-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .af-element {
            display: grid;
            gap: 8px;
            min-height: 82px;
            padding: 11px;
            border: 1px solid var(--af-line);
            border-radius: 8px;
            background: var(--af-panel-strong);
            color: var(--af-ink);
            text-align: left;
            transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
        }

        .af-element:hover {
            transform: translateY(-1px);
            border-color: rgba(34, 211, 197, 0.42);
            box-shadow: 0 12px 26px rgba(34, 211, 197, 0.12);
        }

        .af-element-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .af-swatch {
            width: 18px;
            height: 18px;
            border-radius: 5px;
            border: 1px solid rgba(23, 32, 51, 0.18);
        }

        .af-element strong {
            display: block;
            font-size: 13px;
            line-height: 1.2;
        }

        .af-element span:last-child {
            color: var(--af-muted);
            font-size: 11px;
            line-height: 1.35;
        }

        .af-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .af-stat {
            border: 1px solid var(--af-line);
            border-radius: 8px;
            background: var(--af-panel-strong);
            padding: 12px;
        }

        .af-stat strong {
            display: block;
            font-size: 22px;
            line-height: 1;
            color: var(--af-ink);
        }

        .af-stat span {
            display: block;
            margin-top: 6px;
            color: var(--af-muted);
            font-size: 12px;
        }

        .af-help-list {
            display: grid;
            gap: 9px;
            margin: 0;
            padding: 0;
            list-style: none;
            color: var(--af-muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .af-help-list li {
            display: grid;
            grid-template-columns: 18px 1fr;
            gap: 8px;
        }

        .af-canvas-wrap {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle, rgba(148, 163, 184, 0.22) 1px, transparent 1.5px),
                var(--af-canvas);
            background-size: 22px 22px;
        }

        .af-canvas-wrap.is-pan-mode {
            cursor: grab;
        }

        .af-canvas-wrap.is-panning {
            cursor: grabbing;
        }

        #roadmap-canvas,
        #af-connections {
            transform-origin: 0 0;
        }

        .af-canvas-toolbar {
            position: absolute;
            top: 16px;
            left: 16px;
            right: 16px;
            z-index: 12;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            pointer-events: none;
        }

        .af-module-strip,
        .af-toolbar-group,
        .af-status-pill {
            pointer-events: auto;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--af-line);
            border-radius: 8px;
            background: rgba(17, 21, 32, 0.82);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(12px);
            padding: 7px;
        }

        .af-module-strip {
            max-width: min(42vw, 520px);
            overflow-x: auto;
            scrollbar-width: none;
        }

        .af-module-strip::-webkit-scrollbar {
            display: none;
        }

        .af-module-tab,
        .af-module-add {
            min-height: 34px;
            border: 1px solid transparent;
            border-radius: 6px;
            background: rgba(15, 23, 42, 0.72);
            color: var(--af-muted);
            font: inherit;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
            cursor: pointer;
            transition: border-color 0.16s ease, background 0.16s ease, color 0.16s ease, transform 0.16s ease;
        }

        .af-module-tab {
            padding: 0 12px;
        }

        .af-module-add {
            width: 34px;
            flex: 0 0 34px;
            padding: 0;
            color: #f8fafc;
        }

        .af-module-tab:hover,
        .af-module-add:hover {
            border-color: rgba(96, 165, 250, 0.45);
            background: rgba(30, 41, 59, 0.92);
            color: #f8fafc;
            transform: translateY(-1px);
        }

        .af-module-tab.is-active {
            border-color: rgba(45, 212, 191, 0.48);
            background: rgba(20, 184, 166, 0.16);
            color: #5eead4;
        }

        .af-status-pill {
            color: var(--af-muted);
            font-size: 12px;
            padding: 10px 12px;
        }

        #roadmap-canvas {
            position: relative;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        #af-connections {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            overflow: visible;
            pointer-events: none;
            z-index: 2;
        }

        .af-empty {
            position: absolute;
            inset: 50% auto auto 50%;
            z-index: 4;
            width: min(420px, calc(100% - 48px));
            transform: translate(-50%, -50%);
            border: 1px dashed rgba(148, 163, 184, 0.24);
            border-radius: 8px;
            background: rgba(17, 21, 32, 0.82);
            color: var(--af-ink);
            padding: 22px;
            text-align: center;
            box-shadow: var(--af-shadow);
        }

        .af-empty h2 {
            margin: 0 0 8px;
            font-size: 18px;
        }

        .af-empty p {
            margin: 0;
            color: var(--af-muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .roadmap-node {
            position: absolute;
            width: 220px;
            min-height: 126px;
            color: var(--af-ink);
            border: 1px solid rgba(148, 163, 184, 0.24);
            border-radius: 8px;
            box-shadow: 0 18px 44px rgba(0, 0, 0, 0.36);
            cursor: grab;
            user-select: none;
            z-index: 5;
            overflow: visible;
            display: flex;
            flex-direction: column;
        }

        .roadmap-node:active {
            cursor: grabbing;
        }

        .roadmap-node.is-selected {
            border-color: rgba(124, 58, 237, 0.72);
            box-shadow:
                0 18px 44px rgba(0, 0, 0, 0.36),
                0 0 0 2px rgba(124, 58, 237, 0.2),
                0 0 24px rgba(124, 58, 237, 0.24);
        }

        .af-node-body {
            min-height: inherit;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 14px;
            overflow: hidden;
            border-radius: 6px;
            background: #171c29;
            border-top: 3px solid var(--af-brand);
        }

        .af-node-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .af-node-kind {
            color: var(--af-muted);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .af-node-title {
            font-size: 15px;
            line-height: 1.25;
            font-weight: 800;
            outline: none;
            word-break: break-word;
            min-height: 20px;
            cursor: text;
            width: 100%;
        }

        .af-node-content {
            font-size: 13px;
            color: #79839d;
            outline: none;
            margin: 0;
            overflow: auto;
            word-break: break-word;
            line-height: 1.35;
            cursor: text;
            width: 100%;
            max-height: 56px;
        }

        .af-node-actions {
            display: flex;
            gap: 6px;
            align-items: center;
            justify-content: flex-end;
            margin-top: auto;
        }

        .af-node-actions button {
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(8, 10, 16, 0.52);
            color: var(--af-danger);
            border-radius: 6px;
            padding: 5px 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
        }

        .af-node-note-add {
            align-items: center;
            background: rgba(124, 58, 237, 0.22) !important;
            border-color: rgba(124, 58, 237, 0.46) !important;
            color: #ffffff !important;
            display: none;
            font-size: 16px !important;
            height: 26px;
            justify-content: center;
            padding: 0 !important;
            width: 26px;
        }

        .roadmap-node.is-selected .af-node-note-add {
            display: inline-flex;
        }

        .af-node-note-list {
            display: grid;
            gap: 7px;
            margin-top: 2px;
        }

        .af-node-note-button {
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.18), rgba(15, 23, 42, 0.88)) !important;
            border: 1px solid rgba(96, 165, 250, 0.72) !important;
            border-radius: 8px !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
            color: #dbeafe !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            justify-content: center;
            min-height: 28px;
            overflow: hidden;
            padding: 0 10px !important;
            text-overflow: ellipsis;
            transition: background 140ms ease, border-color 140ms ease, color 140ms ease;
            white-space: nowrap;
            width: 100%;
        }

        .af-node-note-button:hover {
            background: linear-gradient(180deg, rgba(34, 211, 197, 0.16), rgba(30, 41, 59, 0.94)) !important;
            border-color: rgba(34, 211, 197, 0.74) !important;
            color: #ffffff !important;
        }

        .af-font-size-control {
            align-items: center;
            color: #cbd5e1;
            display: inline-flex;
            gap: 6px;
            font-size: 11px;
            font-weight: 800;
            pointer-events: auto;
            position: relative;
            z-index: 60;
        }

        .af-font-size-control select {
            background: rgba(8, 10, 16, 0.8);
            border: 1px solid rgba(96, 165, 250, 0.34);
            border-radius: 6px;
            color: #ffffff;
            font: inherit;
            min-height: 28px;
            outline: none;
            padding: 0 8px;
            pointer-events: auto;
            position: relative;
            z-index: 61;
        }

        .af-download-link {
            border: 1px solid rgba(96, 165, 250, 0.28);
            background: rgba(96, 165, 250, 0.12);
            color: #93c5fd;
            border-radius: 6px;
            padding: 5px 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .roadmap-node.type-milestone {
            background: #171c29;
            --af-brand: #f59e0b;
            border-radius: 8px;
            transform: rotate(45deg);
            aspect-ratio: 1;
        }

        .roadmap-node.type-milestone .af-node-body,
        .roadmap-node.type-milestone .af-port,
        .roadmap-node.type-milestone .af-resize-handle {
            transform: rotate(-45deg);
        }

        .roadmap-node.type-task {
            background: #171c29;
            --af-brand: #22d3ee;
            border-radius: 8px;
        }

        .roadmap-node.type-activity {
            background: #171c29;
            --af-brand: #38bdf8;
            border-color: rgba(56, 189, 248, 0.34);
            border-radius: 8px;
            min-height: 210px;
            min-width: 340px;
        }

        .roadmap-node.type-activity .af-node-body {
            gap: 8px;
            padding: 18px;
        }

        .roadmap-node.type-decision {
            background: #171c29;
            --af-brand: #8b5cf6;
            border-radius: 22px 8px 22px 8px;
        }

        .roadmap-node.type-goal {
            background: #171c29;
            --af-brand: #a855f7;
            border-radius: 999px;
        }

        .roadmap-node.type-resource {
            background: #171c29;
            --af-brand: #14b8a6;
            border-radius: 8px;
            transform: skew(-3deg);
        }

        .roadmap-node.type-note {
            background: transparent;
            --af-brand: #f472b6;
            border: 2px dashed rgba(244, 114, 182, 0.72);
            border-radius: 8px;
            min-width: 280px;
            min-height: 150px;
        }

        .roadmap-node.type-note .af-node-body {
            background: rgba(17, 21, 32, 0.78);
            border-top: 0;
            box-shadow: inset 0 0 0 1px rgba(244, 114, 182, 0.08);
        }

        .roadmap-node.type-text {
            background: transparent;
            --af-brand: #60a5fa;
            border: 0;
            box-shadow: none;
            color: #ffffff;
        }

        .roadmap-node.type-text .af-node-body {
            background: transparent;
            border-top: 0;
            padding: 8px;
            justify-content: center;
        }

        .roadmap-node.type-text .af-node-kind,
        .roadmap-node.type-text .af-node-content {
            display: none;
        }

        .roadmap-node.type-text .af-node-actions {
            display: none;
        }

        .roadmap-node.type-text.is-selected .af-node-actions {
            align-items: center;
            background: rgba(17, 21, 32, 0.94);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.32);
            display: flex;
            gap: 8px;
            left: 50%;
            padding: 7px;
            pointer-events: auto;
            position: absolute;
            top: -56px;
            transform: translateX(-50%);
            width: max-content;
            z-index: 80;
        }

        .roadmap-node.type-text.is-selected .af-port.top {
            top: -86px;
            z-index: 20;
        }

        .roadmap-node.type-text .af-node-title {
            font-size: 40px;
            line-height: 1.08;
            text-align: center;
            font-weight: 900;
            color: #ffffff;
        }

        .roadmap-node.type-start {
            background: #171c29;
            --af-brand: #22c55e;
            border-radius: 999px;
            min-height: 92px;
        }

        .roadmap-node.type-start .af-node-body {
            border-radius: 999px;
        }

        .roadmap-node.type-end {
            background: #171c29;
            --af-brand: #ef4444;
            border-radius: 999px;
            min-height: 92px;
        }

        .roadmap-node.type-end .af-node-body {
            border-radius: 999px;
        }

        .roadmap-node.type-attachment {
            background: #171c29;
            --af-brand: #38bdf8;
            border-style: dashed;
        }

        .af-activity-attachments {
            display: grid;
            gap: 6px;
            max-height: 82px;
            overflow: auto;
        }

        .af-activity-attachments.is-empty {
            border: 1px dashed rgba(96, 165, 250, 0.24);
            border-radius: 6px;
            color: #93a4c6;
            font-size: 11px;
            padding: 7px 8px;
        }

        .af-activity-file {
            align-items: center;
            display: grid;
            gap: 6px;
            grid-template-columns: minmax(0, 1fr) auto;
        }

        .af-activity-file a {
            border: 1px solid rgba(96, 165, 250, 0.36);
            border-radius: 6px;
            color: #dbeafe;
            font-size: 11px;
            font-weight: 800;
            overflow: hidden;
            padding: 5px 7px;
            text-decoration: none;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .af-node-actions [data-action="activity-file"] {
            color: #bfdbfe !important;
        }

        .af-activity-file [data-action="activity-file-remove"] {
            align-items: center;
            color: #fecdd3 !important;
            display: inline-flex;
            height: 24px;
            justify-content: center;
            padding: 0 !important;
            width: 24px;
        }

        .roadmap-node.type-image {
            background: transparent;
            border: 0;
            box-shadow: none;
        }

        .roadmap-node.type-video {
            background: #0f172a;
            border: 1px solid rgba(96, 165, 250, 0.5);
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.26);
            overflow: visible;
        }

        .af-image-body {
            width: 100%;
            height: 100%;
            border-radius: 8px;
            overflow: hidden;
        }

        .af-node-image {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            user-select: none;
            pointer-events: none;
        }

        .af-video-body,
        .af-node-video {
            width: 100%;
            height: 100%;
        }

        .af-video-body {
            background: #020617;
            border-radius: 8px;
            overflow: hidden;
        }

        .af-node-video {
            border: 0;
            display: block;
            pointer-events: none;
        }

        .af-video-placeholder {
            align-items: center;
            color: #cbd5e1;
            display: grid;
            gap: 6px;
            height: 100%;
            justify-items: center;
            padding: 18px;
            text-align: center;
        }

        .af-video-placeholder span {
            color: #f8fafc;
            font-size: 15px;
            font-weight: 900;
        }

        .af-video-placeholder small {
            color: #93c5fd;
            font-size: 12px;
        }

        .roadmap-node.type-resource .af-node-body {
            transform: skew(3deg);
        }

        .af-resize-handle {
            position: absolute;
            right: 6px;
            bottom: 6px;
            width: 13px;
            height: 13px;
            border-radius: 4px;
            background: var(--af-brand);
            border: 2px solid white;
            cursor: nwse-resize;
            z-index: 20;
        }

        .af-port {
            position: absolute;
            display: flex;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: #7c3aed;
            color: white;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            cursor: crosshair;
            opacity: 0;
            box-shadow: 0 8px 20px rgba(124, 58, 237, 0.42);
            z-index: 30;
            pointer-events: none;
            transition:
                opacity 150ms ease,
                transform 150ms ease,
                box-shadow 150ms ease;
        }

        .roadmap-node:hover .af-port,
        .roadmap-node.is-selected .af-port {
            opacity: 1;
            pointer-events: auto;
        }

        .af-port.top {
            left: 50%;
            top: -38px;
            transform: translateX(-50%) scale(0.82);
        }

        .roadmap-node:hover .af-port.top,
        .roadmap-node.is-selected .af-port.top {
            transform: translateX(-50%) scale(1);
        }

        .af-port.right {
            right: -38px;
            top: 50%;
            transform: translateY(-50%) scale(0.82);
        }

        .roadmap-node:hover .af-port.right,
        .roadmap-node.is-selected .af-port.right {
            transform: translateY(-50%) scale(1);
        }

        .af-port.bottom {
            left: 50%;
            bottom: -38px;
            transform: translateX(-50%) scale(0.82);
        }

        .roadmap-node:hover .af-port.bottom,
        .roadmap-node.is-selected .af-port.bottom {
            transform: translateX(-50%) scale(1);
        }

        .af-port.left {
            left: -38px;
            top: 50%;
            transform: translateY(-50%) scale(0.82);
        }

        .roadmap-node:hover .af-port.left,
        .roadmap-node.is-selected .af-port.left {
            transform: translateY(-50%) scale(1);
        }

        .roadmap-node.type-milestone .af-port.top {
            left: 16%;
            top: 16%;
            transform: rotate(-45deg) scale(0.82);
        }

        .roadmap-node.type-milestone:hover .af-port.top,
        .roadmap-node.type-milestone.is-selected .af-port.top {
            transform: rotate(-45deg) scale(1);
        }

        .roadmap-node.type-milestone .af-port.right {
            right: 16%;
            top: 16%;
            transform: rotate(-45deg) scale(0.82);
        }

        .roadmap-node.type-milestone:hover .af-port.right,
        .roadmap-node.type-milestone.is-selected .af-port.right {
            transform: rotate(-45deg) scale(1);
        }

        .roadmap-node.type-milestone .af-port.bottom {
            left: 84%;
            bottom: 16%;
            transform: rotate(-45deg) scale(0.82);
        }

        .roadmap-node.type-milestone:hover .af-port.bottom,
        .roadmap-node.type-milestone.is-selected .af-port.bottom {
            transform: rotate(-45deg) scale(1);
        }

        .roadmap-node.type-milestone .af-port.left {
            left: 16%;
            top: 84%;
            transform: rotate(-45deg) scale(0.82);
        }

        .roadmap-node.type-milestone:hover .af-port.left,
        .roadmap-node.type-milestone.is-selected .af-port.left {
            transform: rotate(-45deg) scale(1);
        }

        .roadmap-node.type-milestone {
            transform: none;
            background: transparent;
            border: 0;
            padding: 0;
            overflow: visible;
        }

        .roadmap-node.type-milestone::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(245, 158, 11, 0.5);
            clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
            pointer-events: none;
        }

        .roadmap-node.type-milestone .af-node-body {
            position: absolute;
            inset: 3px;
            transform: none;
            clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
            justify-content: center;
            padding: 28px 34px;
            text-align: center;
        }

        .roadmap-node.type-milestone .af-port,
        .roadmap-node.type-milestone .af-resize-handle {
            transform: none;
        }

        .roadmap-node.type-milestone .af-resize-handle {
            right: 50%;
            bottom: -44px;
            transform: translateX(50%);
        }

        .roadmap-node.type-milestone .af-port.top {
            left: 50%;
            top: -34px;
            transform: translateX(-50%) scale(0.82);
        }

        .roadmap-node.type-milestone:hover .af-port.top,
        .roadmap-node.type-milestone.is-selected .af-port.top {
            transform: translateX(-50%) scale(1);
        }

        .roadmap-node.type-milestone .af-port.right {
            right: -34px;
            top: 50%;
            transform: translateY(-50%) scale(0.82);
        }

        .roadmap-node.type-milestone:hover .af-port.right,
        .roadmap-node.type-milestone.is-selected .af-port.right {
            transform: translateY(-50%) scale(1);
        }

        .roadmap-node.type-milestone .af-port.bottom {
            left: 50%;
            bottom: -34px;
            transform: translateX(-50%) scale(0.82);
        }

        .roadmap-node.type-milestone:hover .af-port.bottom,
        .roadmap-node.type-milestone.is-selected .af-port.bottom {
            transform: translateX(-50%) scale(1);
        }

        .roadmap-node.type-milestone .af-port.left {
            left: -34px;
            top: 50%;
            transform: translateY(-50%) scale(0.82);
        }

        .roadmap-node.type-milestone:hover .af-port.left,
        .roadmap-node.type-milestone.is-selected .af-port.left {
            transform: translateY(-50%) scale(1);
        }

        .af-minimap {
            position: absolute;
            right: 16px;
            bottom: 16px;
            width: 184px;
            height: 124px;
            background: rgba(17, 21, 32, 0.86);
            color: var(--af-ink);
            border: 1px solid var(--af-line);
            border-radius: 8px;
            z-index: 10;
            box-shadow: 0 16px 34px rgba(23, 32, 51, 0.12);
            overflow: hidden;
        }

        .af-minimap::before {
            content: "Mapa";
            position: absolute;
            top: 8px;
            left: 10px;
            color: var(--af-muted);
            font-size: 11px;
            font-weight: 800;
        }

        .af-minimap-node {
            position: absolute;
            border-radius: 3px;
            background: var(--af-brand);
            opacity: 0.78;
        }

        .af-selection-box {
            position: absolute;
            z-index: 9;
            border: 1px solid rgba(96, 165, 250, 0.82);
            background: rgba(96, 165, 250, 0.12);
            pointer-events: none;
            border-radius: 6px;
        }

        .af-context-menu {
            position: fixed;
            z-index: 1000;
            min-width: 150px;
            padding: 6px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 8px;
            background: rgba(17, 21, 32, 0.96);
            box-shadow: 0 18px 44px rgba(0, 0, 0, 0.36);
            backdrop-filter: blur(12px);
        }

        .af-context-menu button {
            width: 100%;
            display: block;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: var(--af-ink);
            padding: 9px 10px;
            text-align: left;
            font-size: 13px;
            font-weight: 700;
        }

        .af-context-menu button:hover {
            background: rgba(124, 58, 237, 0.22);
        }

        @media (max-width: 980px) {
            body {
                overflow: hidden;
            }

            .af-editor {
                min-height: 100vh;
                height: 100vh;
                grid-template-rows: 54px 1fr;
            }

            .af-topbar {
                flex-wrap: nowrap;
                padding: 0 10px;
            }

            .af-right {
                width: auto;
                justify-content: flex-start;
                overflow: visible;
                padding-bottom: 2px;
            }

            .af-workspace {
                grid-template-columns: 1fr;
            }

            .af-sidebar {
                position: absolute;
                top: 12px;
                left: 12px;
                z-index: 16;
                width: min(292px, calc(100vw - 24px));
                max-height: calc(100vh - 84px);
                border: 1px solid var(--af-line);
                border-radius: 8px;
                box-shadow: var(--af-shadow);
            }

            .af-canvas-wrap {
                min-height: 0;
            }

            .af-canvas-toolbar {
                align-items: flex-start;
                flex-direction: column;
                right: auto;
            }

            .af-module-strip {
                max-width: min(292px, calc(100vw - 24px));
            }

            .af-minimap {
                display: none;
            }
        }

        @media (max-width: 760px) {
            .af-kicker,
            .af-right [role="alert"],
            .af-right [title="Baixar estrutura em JSON"] {
                display: none !important;
            }

            .af-title-input {
                width: min(34vw, 180px);
                min-width: 118px;
                font-size: 13px;
            }

            .af-sidebar {
                width: min(292px, calc(100vw - 24px));
            }
        }
    .roadmap-node.type-milestone {
        aspect-ratio: 1;
        background: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        overflow: visible !important;
        padding: 0 !important;
        transform: none !important;
    }

    .roadmap-node.type-milestone::before {
        content: "";
        position: absolute;
        inset: 0;
        background: rgba(245, 158, 11, 0.45);
        clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
        pointer-events: none;
    }

    .roadmap-node.type-milestone .af-node-body {
        position: absolute;
        inset: 3px;
        align-items: center;
        background: rgba(17, 24, 39, 0.96) !important;
        border: 1px solid rgba(245, 158, 11, 0.8) !important;
        border-radius: 0 !important;
        clip-path: polygon(50% 0, 100% 50%, 50% 100%, 0 50%);
        justify-content: center;
        min-height: 0;
        padding: 30px 36px;
        text-align: center;
        transform: none !important;
    }

    .roadmap-node.type-milestone .af-port {
        transform: scale(0.85) !important;
    }

    .roadmap-node.type-milestone:hover .af-port,
    .roadmap-node.type-milestone.is-selected .af-port {
        transform: scale(1) !important;
    }

    .roadmap-node.type-milestone .af-port.top {
        left: 50%;
        top: -42px;
        transform: translateX(-50%) scale(0.85) !important;
    }

    .roadmap-node.type-milestone:hover .af-port.top,
    .roadmap-node.type-milestone.is-selected .af-port.top {
        transform: translateX(-50%) scale(1) !important;
    }

    .roadmap-node.type-milestone .af-port.right {
        right: -42px;
        top: 50%;
        transform: translateY(-50%) scale(0.85) !important;
    }

    .roadmap-node.type-milestone:hover .af-port.right,
    .roadmap-node.type-milestone.is-selected .af-port.right {
        transform: translateY(-50%) scale(1) !important;
    }

    .roadmap-node.type-milestone .af-port.bottom {
        bottom: -42px;
        left: 50%;
        transform: translateX(-50%) scale(0.85) !important;
    }

    .roadmap-node.type-milestone:hover .af-port.bottom,
    .roadmap-node.type-milestone.is-selected .af-port.bottom {
        transform: translateX(-50%) scale(1) !important;
    }

    .roadmap-node.type-milestone .af-port.left {
        left: -42px;
        top: 50%;
        transform: translateY(-50%) scale(0.85) !important;
    }

    .roadmap-node.type-milestone:hover .af-port.left,
    .roadmap-node.type-milestone.is-selected .af-port.left {
        transform: translateY(-50%) scale(1) !important;
    }

    .roadmap-node.type-milestone .af-resize-handle {
        bottom: -46px;
        right: 50%;
        transform: translateX(50%) !important;
    }
    .af-download-link {
        align-items: center;
        background: rgba(37, 99, 235, 0.14);
        border: 1px solid rgba(96, 165, 250, 0.32);
        border-radius: 6px;
        color: #bfdbfe;
        display: inline-flex;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        padding: 8px 10px;
        text-decoration: none;
    }

    .roadmap-node.type-start,
    .roadmap-node.type-end,
    .roadmap-node.type-task,
    .roadmap-node.type-decision,
    .roadmap-node.type-goal,
    .roadmap-node.type-resource,
    .roadmap-node.type-attachment {
        background: transparent !important;
    }

    .roadmap-node.type-start .af-node-body,
    .roadmap-node.type-end .af-node-body,
    .roadmap-node.type-goal .af-node-body {
        border-radius: 999px;
    }

    .af-unsaved-modal[hidden] {
        display: none !important;
    }

    .af-unsaved-modal {
        align-items: center;
        background: rgba(15, 23, 42, 0.48);
        backdrop-filter: blur(2px);
        display: flex;
        inset: 0;
        justify-content: center;
        padding: 24px;
        position: fixed;
        z-index: 100;
    }

    .af-unsaved-dialog {
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 8px;
        box-shadow: 0 22px 60px rgba(0, 0, 0, 0.28);
        color: #0f172a;
        display: grid;
        gap: 16px;
        max-width: 640px;
        padding: 28px 32px;
        width: min(640px, 100%);
    }

    .af-unsaved-dialog h2 {
        font-size: 26px;
        line-height: 1.2;
        margin: 0;
    }

    .af-unsaved-dialog p {
        color: #111827;
        font-size: 15px;
        line-height: 1.5;
        margin: 0;
    }

    .af-unsaved-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .af-unsaved-actions button {
        border-radius: 8px;
        border: 1px solid rgba(15, 23, 42, 0.16);
        color: #111827;
        font-size: 13px;
        font-weight: 800;
        min-height: 38px;
        padding: 0 16px;
    }

    .af-unsaved-danger {
        background: #111827;
        border-color: #111827 !important;
        color: #ffffff !important;
    }

    .af-unsaved-secondary {
        background: #ffffff;
        border-color: rgba(15, 23, 42, 0.22) !important;
    }

    .af-note-modal[hidden] {
        display: none !important;
    }

    .af-note-modal {
        align-items: center;
        background: rgba(2, 6, 23, 0.72);
        backdrop-filter: blur(8px);
        display: flex;
        inset: 0;
        justify-content: center;
        padding: 24px;
        position: fixed;
        z-index: 105;
    }

    .af-note-dialog {
        background: linear-gradient(180deg, rgba(17, 21, 32, 0.98), rgba(8, 10, 16, 0.98));
        border: 1px solid rgba(96, 165, 250, 0.24);
        border-radius: 8px;
        box-shadow: 0 24px 80px rgba(0, 0, 0, 0.42), 0 0 0 1px rgba(34, 211, 197, 0.08);
        color: #f8fafc;
        display: grid;
        gap: 16px;
        max-width: 760px;
        padding: 34px 38px 26px;
        position: relative;
        width: min(760px, 100%);
    }

    .af-note-close {
        background: transparent;
        border: 0;
        color: #cbd5e1;
        font-size: 34px;
        line-height: 1;
        position: absolute;
        right: 18px;
        top: 16px;
    }

    .af-note-close:hover {
        color: #22d3c5;
    }

    .af-note-field {
        display: grid;
        gap: 8px;
    }

    .af-note-field span {
        color: #dbeafe;
        font-size: 15px;
        font-weight: 900;
    }

    .af-note-field input,
    .af-note-field textarea {
        background: rgba(15, 23, 42, 0.9);
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-radius: 8px;
        caret-color: #22d3c5;
        color: #f8fafc;
        font: inherit;
        font-size: 15px;
        outline: none;
        padding: 11px 12px;
        resize: vertical;
    }

    .af-note-field input::placeholder,
    .af-note-field textarea::placeholder {
        color: #64748b;
    }

    .af-note-field input:focus,
    .af-note-field textarea:focus {
        border-color: rgba(34, 211, 197, 0.76);
        box-shadow: 0 0 0 3px rgba(34, 211, 197, 0.14);
    }

    .af-note-actions {
        align-items: center;
        border-top: 1px solid rgba(148, 163, 184, 0.18);
        display: grid;
        gap: 10px;
        grid-template-columns: auto 1fr auto auto;
        padding-top: 16px;
    }

    .af-note-actions button {
        border-radius: 8px;
        border: 1px solid rgba(148, 163, 184, 0.22);
        font-size: 13px;
        font-weight: 900;
        min-height: 38px;
        padding: 0 14px;
    }

    .af-note-primary {
        background: #111827;
        border-color: rgba(96, 165, 250, 0.46) !important;
        color: #ffffff;
    }

    .af-note-primary:hover {
        background: #1d4ed8;
        border-color: rgba(147, 197, 253, 0.82) !important;
    }

    .af-video-modal[hidden] {
        display: none !important;
    }

    .af-video-modal {
        align-items: center;
        background: rgba(2, 6, 23, 0.72);
        backdrop-filter: blur(8px);
        display: flex;
        inset: 0;
        justify-content: center;
        padding: 24px;
        position: fixed;
        z-index: 106;
    }

    .af-video-dialog {
        background: linear-gradient(180deg, rgba(17, 21, 32, 0.98), rgba(8, 10, 16, 0.98));
        border: 1px solid rgba(96, 165, 250, 0.28);
        border-radius: 8px;
        box-shadow: 0 24px 80px rgba(0, 0, 0, 0.42), 0 0 0 1px rgba(34, 211, 197, 0.08);
        color: #f8fafc;
        display: grid;
        gap: 14px;
        max-width: 560px;
        padding: 30px 34px 26px;
        position: relative;
        width: min(560px, 100%);
    }

    .af-video-dialog h2 {
        font-size: 24px;
        line-height: 1.2;
        margin: 0;
    }

    .af-video-dialog p {
        color: #bfdbfe;
        font-size: 14px;
        line-height: 1.5;
        margin: 0;
    }

    .af-video-close {
        background: transparent;
        border: 0;
        color: #cbd5e1;
        font-size: 32px;
        line-height: 1;
        position: absolute;
        right: 16px;
        top: 14px;
    }

    .af-video-close:hover {
        color: #22d3c5;
    }

    .af-video-field {
        display: grid;
        gap: 8px;
    }

    .af-video-field span {
        color: #dbeafe;
        font-size: 14px;
        font-weight: 900;
    }

    .af-video-field input {
        background: rgba(15, 23, 42, 0.9);
        border: 1px solid rgba(148, 163, 184, 0.24);
        border-radius: 8px;
        caret-color: #22d3c5;
        color: #f8fafc;
        font: inherit;
        min-height: 44px;
        outline: none;
        padding: 0 12px;
    }

    .af-video-field input:focus {
        border-color: rgba(34, 211, 197, 0.76);
        box-shadow: 0 0 0 3px rgba(34, 211, 197, 0.14);
    }

    .af-video-error {
        background: rgba(248, 113, 113, 0.12);
        border: 1px solid rgba(248, 113, 113, 0.32);
        border-radius: 8px;
        color: #fecaca;
        font-size: 13px;
        font-weight: 800;
        padding: 9px 10px;
    }

    .af-video-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .af-video-actions button {
        border-radius: 8px;
        border: 1px solid rgba(148, 163, 184, 0.22);
        font-size: 13px;
        font-weight: 900;
        min-height: 38px;
        padding: 0 14px;
    }

    .af-video-secondary {
        background: rgba(15, 23, 42, 0.86);
        color: #e2e8f0;
    }

    .af-video-primary {
        background: linear-gradient(135deg, #2563eb, #06b6d4);
        border-color: rgba(96, 165, 250, 0.52) !important;
        color: #ffffff;
    }

    .af-note-secondary {
        background: rgba(15, 23, 42, 0.72);
        color: #e2e8f0;
    }

    .af-note-delete {
        background: rgba(244, 63, 94, 0.12);
        border-color: rgba(244, 63, 94, 0.36) !important;
        color: #fecdd3;
    }
</style>
<style id="af-mobile-roadmap-editor">
    .af-tools-toggle {
        display: none;
    }

    @media (max-width: 768px) {
        html,
        body {
            height: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .af-editor {
            grid-template-rows: auto minmax(0, 1fr);
            height: 100dvh;
            min-height: 100dvh;
            overflow: hidden;
        }

        .af-topbar {
            align-items: center;
            display: grid;
            gap: 8px;
            grid-template-columns: auto minmax(0, 1fr) auto;
            min-height: 54px;
            padding: 6px 8px;
        }

        .af-left,
        .af-right {
            gap: 6px;
            min-width: 0;
        }

        .af-logo,
        .af-kicker,
        .af-status-pill,
        .af-right [role="alert"],
        .af-right [title="Baixar estrutura em JSON"] {
            display: none !important;
        }

        .af-title-stack {
            min-width: 0;
        }

        .af-title-input {
            font-size: 13px;
            max-width: 100%;
            min-height: 34px;
            min-width: 0;
            width: 100%;
        }

        .af-topbar :where(a, button),
        .af-toolbar-group :where(a, button),
        .af-tool-button {
            min-height: 42px;
            min-width: 42px;
            touch-action: manipulation;
        }

        .af-tools-toggle {
            align-items: center;
            background: rgba(13, 148, 136, 0.18);
            border: 1px solid rgba(45, 212, 191, 0.46);
            border-radius: 8px;
            color: #99f6e4;
            display: inline-flex;
            font-size: 12px;
            font-weight: 800;
            justify-content: center;
            letter-spacing: 0;
            padding: 0 12px;
        }

        body.af-tools-open .af-tools-toggle {
            background: rgba(45, 212, 191, 0.24);
            border-color: rgba(94, 234, 212, 0.72);
            color: #ccfbf1;
        }

        .af-workspace {
            display: block;
            height: 100%;
            min-height: 0;
            overflow: hidden;
            position: relative;
        }

        .af-canvas-wrap {
            height: 100%;
            min-height: 0;
            overflow: hidden !important;
            overscroll-behavior: none;
            touch-action: none;
        }

        #roadmap-canvas,
        #af-connections {
            touch-action: none;
        }

        .af-sidebar {
            background: rgba(10, 13, 22, 0.98);
            border: 1px solid rgba(96, 165, 250, 0.24);
            border-left: 0;
            border-radius: 0 10px 10px 0;
            bottom: 0;
            box-shadow: 20px 0 48px rgba(0, 0, 0, 0.44);
            height: 100%;
            left: 0;
            max-height: 100%;
            max-width: min(86vw, 320px);
            min-height: 0;
            overflow-x: hidden;
            overflow-y: auto;
            padding: 14px;
            position: absolute;
            top: 0;
            transform: translateX(calc(-100% - 12px));
            transition: transform 180ms ease;
            width: min(86vw, 320px);
            z-index: 42;
        }

        body.af-tools-open .af-sidebar {
            transform: translateX(0);
        }

        .af-side-stack {
            align-items: stretch;
            display: flex;
            flex-direction: column;
            gap: 10px;
            height: auto;
            min-width: 0;
            padding-bottom: 10px;
        }

        .af-sidebar [data-slot="card"],
        .af-sidebar .af-panel {
            flex: 0 0 auto;
            max-height: none;
            overflow: auto;
            width: 100%;
        }

        .af-canvas-toolbar {
            align-items: stretch;
            display: flex;
            flex-direction: column;
            gap: 8px;
            left: 8px;
            max-width: calc(100vw - 16px);
            right: 8px;
            top: 8px;
            z-index: 15;
        }

        .af-toolbar-group,
        .af-module-strip {
            max-width: 100%;
            overflow-x: auto;
            scrollbar-width: none;
            white-space: nowrap;
        }

        .af-toolbar-group::-webkit-scrollbar,
        .af-module-strip::-webkit-scrollbar {
            display: none;
        }

        .af-module-tab {
            min-height: 38px;
        }

        .af-context-menu {
            min-width: 170px;
            padding: 8px;
        }

        .af-context-menu button {
            font-size: 14px;
            min-height: 44px;
            padding: 12px 14px;
        }

        .af-note-dialog,
        .af-video-dialog,
        .af-unsaved-dialog {
            max-height: calc(100dvh - 32px);
            overflow: auto;
            padding: 24px 18px 18px;
            width: calc(100vw - 24px);
        }

        .af-note-actions {
            grid-template-columns: 1fr;
        }

        .af-note-actions span {
            display: none;
        }
    }

    @media (max-width: 480px) {
        .af-sidebar {
            max-width: min(88vw, 316px);
            width: min(88vw, 316px);
        }

        .af-topbar {
            grid-template-columns: auto minmax(0, 1fr);
        }

        .af-right {
            grid-column: 1 / -1;
            justify-content: flex-start;
            overflow-x: auto;
            padding-bottom: 2px;
        }
    }
</style>
</head>

<body>
<div class="af-editor">
    <header class="af-topbar">
        <div class="af-left">
            <x-btn href="{{ route('roadmaps.index', $turma->id) }}" style="ghost" size="sm" title="Voltar para a lista">
                Voltar
            </x-btn>

            <img src="{{ asset('img/logo.png') }}" class="af-logo" alt="UniRoad">

            <div class="af-title-stack">
                <span class="af-kicker">Editor de roadmap - {{ $turma->nome ?? 'Turma '.$turma->id }}</span>
                <input id="roadmap-title" class="af-title-input" value="Roadmap sem titulo" aria-label="Titulo do roadmap">
            </div>
        </div>

        <div class="af-right">
            <x-badge style="secondary" size="sm">Rascunho</x-badge>

            <button type="button" class="af-tools-toggle" data-roadmap-tools-toggle aria-controls="af-roadmap-tools" aria-expanded="false">
                Ferramentas
            </button>

            <x-btn style="outline" size="sm" onclick="ArtisanFlow.exportJSON()" title="Baixar estrutura em JSON">
                Exportar
            </x-btn>

            <x-btn style="primary" size="sm" onclick="ArtisanFlow.save()" title="Salvar roadmap">
                Salvar
            </x-btn>
        </div>
    </header>

    <main class="af-workspace">
        <aside class="af-sidebar" id="af-roadmap-tools">
            <div class="af-side-stack">
                <x-card>
                    <x-card.header>
                        <x-card.title :as-heading="false">Elementos</x-card.title>
                        <x-slot:action>
                            <x-badge style="info" size="sm">Arraste depois de criar</x-badge>
                        </x-slot:action>
                    </x-card.header>

                    <x-card.body>
                        <div class="af-element-grid">
                            <button type="button" class="af-element" onclick="ArtisanFlow.addNode('milestone')" title="Adicionar marco">
                                <span class="af-element-top">
                                    <span class="af-swatch" style="background:#fde68a"></span>
                                    <x-badge style="warning" size="sm">Marco</x-badge>
                                </span>
                                <strong>Marco</strong>
                                <span>Entrega, prova ou checkpoint importante.</span>
                            </button>

                            <button type="button" class="af-element" onclick="ArtisanFlow.addNode('task')" title="Adicionar tarefa">
                                <span class="af-element-top">
                                    <span class="af-swatch" style="background:#bfdbfe"></span>
                                    <x-badge style="info" size="sm">Tarefa</x-badge>
                                </span>
                                <strong>Tarefa</strong>
                                <span>Atividade objetiva para o aluno cumprir.</span>
                            </button>

                            <button type="button" class="af-element" onclick="ArtisanFlow.addNode('activity')" title="Adicionar atividade com entrega">
                                <span class="af-element-top">
                                    <span class="af-swatch" style="background:#67e8f9"></span>
                                    <x-badge style="info" size="sm">Atividade</x-badge>
                                </span>
                                <strong>Atividade</strong>
                                <span>Entrega com descrição, anexos e submissão.</span>
                            </button>

                            <button type="button" class="af-element" onclick="ArtisanFlow.addNode('decision')" title="Adicionar decisao">
                                <span class="af-element-top">
                                    <span class="af-swatch" style="background:#bbf7d0"></span>
                                    <x-badge style="success" size="sm">Decisao</x-badge>
                                </span>
                                <strong>Decisao</strong>
                                <span>Ramo condicional para seguir caminhos.</span>
                            </button>

                            <button type="button" class="af-element" onclick="ArtisanFlow.addNode('goal')" title="Adicionar objetivo">
                                <span class="af-element-top">
                                    <span class="af-swatch" style="background:#ddd6fe"></span>
                                    <x-badge style="secondary" size="sm">Objetivo</x-badge>
                                </span>
                                <strong>Objetivo</strong>
                                <span>Resultado esperado em uma etapa.</span>
                            </button>

                            <button type="button" class="af-element" onclick="ArtisanFlow.addNode('resource')" title="Adicionar recurso">
                                <span class="af-element-top">
                                    <span class="af-swatch" style="background:#a7f3d0"></span>
                                    <x-badge style="success" size="sm">Recurso</x-badge>
                                </span>
                                <strong>Recurso</strong>
                                <span>Link, leitura ou material de apoio.</span>
                            </button>

                            <button type="button" class="af-element" onclick="ArtisanFlow.addNode('note')" title="Adicionar nota">
                                <span class="af-element-top">
                                    <span class="af-swatch" style="background:#fecdd3"></span>
                                    <x-badge style="danger" size="sm">Nota</x-badge>
                                </span>
                                <strong>Nota</strong>
                                <span>Observacao curta para orientar o fluxo.</span>
                            </button>
                        </div>
                    </x-card.body>
                </x-card>

                <x-card style="ghost">
                    <x-card.header>
                        <x-card.title :as-heading="false">Resumo</x-card.title>
                    </x-card.header>

                    <x-card.body>
                        <div class="af-stats">
                            <div class="af-stat">
                                <strong id="af-node-count">0</strong>
                                <span>blocos</span>
                            </div>
                            <div class="af-stat">
                                <strong id="af-connection-count">0</strong>
                                <span>conexões</span>
                            </div>
                        </div>
                    </x-card.body>
                </x-card>

                <x-card style="ghost">
                    <x-card.header>
                        <x-card.title :as-heading="false">Como montar</x-card.title>
                    </x-card.header>

                    <x-card.body>
                        <ul class="af-help-list">
                            <li><span>1</span><span>Crie blocos pela biblioteca acima.</span></li>
                            <li><span>2</span><span>Edite textos direto dentro do bloco.</span></li>
                            <li><span>3</span><span>Use os conectores nas bordas para criar caminhos.</span></li>
                        </ul>
                    </x-card.body>
                </x-card>
            </div>
        </aside>

        <section class="af-canvas-wrap" data-artisanflow-surface>
            <div class="af-canvas-toolbar">
                <div class="af-module-strip" id="af-module-strip" aria-label="Modulos do roadmap"></div>

                <div class="af-toolbar-group" aria-label="Acoes do canvas">
                    <button type="button" class="af-tool-button af-history-button" onclick="ArtisanFlow.undo()" title="Desfazer">
                        <span>&#8630;</span>
                    </button>

                    <button type="button" class="af-tool-button af-history-button" onclick="ArtisanFlow.redo()" title="Refazer">
                        <span>&#8631;</span>
                    </button>

                    <button type="button" class="af-tool-button af-text-tool" onclick="ArtisanFlow.addNode('text')" title="Inserir texto">
                        <span>A</span>
                    </button>

                    <button type="button" class="af-tool-button" onclick="ArtisanFlow.triggerAttachmentUpload()" title="Anexar arquivo de texto" aria-label="Anexar arquivo de texto">
                        @svg('gmdi-document-scanner-o', 'af-tool-icon')
                    </button>

                    <button type="button" class="af-tool-button" onclick="ArtisanFlow.triggerImageUpload()" title="Adicionar imagem" aria-label="Adicionar imagem">
                        @svg('ionicon-image', 'af-tool-icon')
                    </button>

                    <button type="button" class="af-tool-button" onclick="ArtisanFlow.openVideoModal()" title="Adicionar vídeo do YouTube" aria-label="Adicionar vídeo do YouTube">
                        @svg('ri-video-upload-fill', 'af-tool-icon')
                    </button>
                </div>

                <div class="af-status-pill" id="af-editor-status">
                    Pronto para montar o roadmap
                </div>
            </div>

            <svg id="af-connections"></svg>
            <div id="roadmap-canvas"></div>

            <div class="af-empty" id="af-empty-state">
                <h2>Comece com um bloco</h2>
                <p>Escolha um elemento na lateral ou crie uma tarefa rapida. Depois conecte os pontos para formar o caminho de aprendizagem.</p>
            </div>

            <div class="af-minimap" id="af-minimap" aria-hidden="true"></div>
            <input type="file" id="af-attachment-upload" accept=".txt,.md,.rtf,.doc,.docx,.pdf,.csv,.json,.xml,.html,.odt" multiple hidden onchange="ArtisanFlow.handleAttachmentFiles(this.files); this.value = '';">
            <input type="file" id="af-image-upload" accept="image/*" multiple hidden onchange="ArtisanFlow.handleImageFiles(this.files); this.value = '';">
        </section>
    </main>
</div>

<div class="af-unsaved-modal" id="af-unsaved-modal" hidden>
    <div class="af-unsaved-dialog" role="dialog" aria-modal="true" aria-labelledby="af-unsaved-title">
        <h2 id="af-unsaved-title">Alterações não salvas</h2>
        <p>Você tem mudanças no roadmap que ainda não foram salvas. Deseja descartar essas alterações ou voltar para continuar editando?</p>
        <div class="af-unsaved-actions">
            <button type="button" class="af-unsaved-danger" data-unsaved-action="discard">Descartar alteracoes</button>
            <button type="button" class="af-unsaved-secondary" data-unsaved-action="stay">Voltar a editar</button>
        </div>
    </div>
</div>

<script>
    window.editorMode = "create";
    window.turmaId = {{ $turma->id }};
    window.roadmapMeta = {
        id: null,
        turmaId: "{{ $turma->id }}",
        storeUrl: "{{ route('roadmaps.store', $turma->id) }}",
        indexUrl: "{{ route('roadmaps.index', $turma->id) }}"
    };

    window.initialGraph = {
        nodes: [],
        connections: []
    };
</script>

@ddfsnScripts
</body>
</html>
