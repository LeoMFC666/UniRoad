<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Visualizar Roadmap</title>

    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">

    @fonts
    @ddfsnStyles

    @vite('resources/css/app.css')

    <style>
        body {
            margin: 0;
            background: linear-gradient(180deg, #07080f 0%, #081a33 45%, #001d84 100%);
            color: white;
            font-family: Inter, Arial, sans-serif;
            overflow: hidden;
        }

        .af-editor {
            height: 100vh;
            display: grid;
            grid-template-rows: 64px 1fr;
        }

        .af-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            background: rgba(7, 8, 15, 0.92);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .af-toast {
            position: fixed;
            right: 18px;
            top: 76px;
            z-index: 60;
            max-width: min(360px, calc(100vw - 32px));
            border: 1px solid rgba(34, 197, 94, 0.36);
            border-radius: 10px;
            background: rgba(20, 83, 45, 0.9);
            color: #dcfce7;
            font-size: 13px;
            font-weight: 800;
            padding: 12px 14px;
            box-shadow: 0 18px 44px rgba(0, 0, 0, 0.28);
        }

        .af-left,
        .af-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .af-view-module-strip {
            align-items: center;
            display: flex;
            gap: 6px;
            max-width: min(44vw, 520px);
            overflow-x: auto;
            padding: 4px;
            scrollbar-width: none;
        }

        .af-view-module-strip::-webkit-scrollbar {
            display: none;
        }

        .af-view-module-tab {
            background: rgba(17, 24, 39, 0.88);
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 8px;
            color: #93a4c6;
            cursor: pointer;
            font: inherit;
            font-size: 12px;
            font-weight: 800;
            min-height: 32px;
            padding: 0 12px;
            white-space: nowrap;
        }

        .af-view-module-tab.is-active {
            background: rgba(20, 184, 166, 0.16);
            border-color: rgba(45, 212, 191, 0.44);
            color: #5eead4;
        }

        .af-logo {
            width: 36px;
            height: 36px;
            border-radius: 50%;
        }

        .af-btn {
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.08);
            color: white;
            padding: 9px 14px;
            border-radius: 12px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }

        .af-btn.primary {
            background: #0043cb;
            border-color: #0043cb;
        }

        .af-workspace {
            display: grid;
            grid-template-columns: 280px 1fr;
            height: calc(100vh - 64px);
        }

        .af-sidebar {
            background: rgba(2, 6, 23, 0.78);
            border-right: 1px solid rgba(255,255,255,0.08);
            padding: 24px;
            overflow-y: auto;
        }

        .af-panel {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 18px;
        }

        .af-panel p {
            font-size: 13px;
            color: rgba(255,255,255,0.68);
            line-height: 1.6;
        }

        .af-canvas-wrap {
            position: relative;
            overflow: auto;
            background:
                radial-gradient(circle at top, rgba(0, 67, 203, 0.18), transparent 30%),
                #f8fafc;
        }

        .af-canvas-wrap::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(15,23,42,0.18) 1px, transparent 1px);
            background-size: 22px 22px;
            pointer-events: none;
        }

        .af-readonly-canvas {
            position: relative;
            z-index: 1;
            padding: 80px;
            min-height: 100%;
        }

        .roadmap-node {
            position: relative;
            width: 180px;
            background: white;
            color: #111827;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 18px 45px rgba(15,23,42,0.16);
            padding: 14px;
            margin-bottom: 20px;
        }

        .roadmap-node strong {
            display: block;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .roadmap-node p {
            font-size: 12px;
            color: #475569;
            margin: 0;
        }

        .af-minimap {
            position: absolute;
            right: 24px;
            bottom: 24px;
            width: 180px;
            height: 120px;
            background: rgba(255,255,255,0.82);
            border: 1px solid rgba(0,67,203,0.22);
            border-radius: 14px;
            z-index: 3;
        }

        #af-connections {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            overflow: visible;
            pointer-events: none;
            z-index: 2;
            transform-origin: 0 0;
        }

        #roadmap-canvas {
            position: relative;
            width: 100%;
            height: 100%;
            z-index: 3;
            transform-origin: 0 0;
        }

        .af-canvas-wrap {
            cursor: grab;
        }

        .af-canvas-wrap.is-panning {
            cursor: grabbing;
        }

        .roadmap-node {
            position: absolute;
            width: 220px;
            min-height: 126px;
            color: #f7f8ff;
            border: 1px solid rgba(148, 163, 184, 0.24);
            border-radius: 8px;
            box-shadow: 0 18px 44px rgba(0, 0, 0, 0.36);
            user-select: none;
            overflow: visible;
        }

        .af-node-body {
            min-height: inherit;
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 14px;
            border-radius: 6px;
            background: #171c29;
            border-top: 3px solid var(--af-brand, #60a5fa);
        }

        .af-node-kind {
            color: #778098;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .af-node-title {
            font-size: 15px;
            line-height: 1.25;
            font-weight: 800;
            word-break: break-word;
        }

        .af-node-content {
            font-size: 13px;
            color: #79839d;
            word-break: break-word;
            line-height: 1.35;
        }

        .roadmap-node.type-start,
        .roadmap-node.type-end {
            border-radius: 999px;
            min-height: 92px;
        }

        .roadmap-node.type-start { --af-brand: #22c55e; }
        .roadmap-node.type-end { --af-brand: #ef4444; }
        .roadmap-node.type-start .af-node-body,
        .roadmap-node.type-end .af-node-body {
            border-radius: 999px;
        }

        .roadmap-node.type-milestone {
            --af-brand: #f59e0b;
            transform: rotate(45deg);
            aspect-ratio: 1;
        }

        .roadmap-node.type-milestone .af-node-body {
            transform: rotate(-45deg);
        }

        .roadmap-node.type-note {
            --af-brand: #f472b6;
            background: transparent;
            border: 2px dashed rgba(244, 114, 182, 0.7);
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
            border: 0;
            box-shadow: none;
        }

        .roadmap-node.type-text .af-node-body {
            background: transparent;
            border-top: 0;
            justify-content: center;
        }

        .roadmap-node.type-text .af-node-kind,
        .roadmap-node.type-text .af-node-content {
            display: none;
        }

        .roadmap-node.type-text .af-node-title {
            font-size: 40px;
            line-height: 1.08;
            text-align: center;
            font-weight: 900;
            color: #ffffff;
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

        .roadmap-node.type-activity {
            --af-brand: #38bdf8;
            background: #171c29;
            border-color: rgba(56, 189, 248, 0.34);
            min-height: 230px;
            min-width: 340px;
        }

        .af-image-body,
        .af-node-image,
        .af-video-body,
        .af-node-video {
            width: 100%;
            height: 100%;
        }

        .af-node-image {
            display: block;
            object-fit: contain;
        }

        .af-video-body {
            background: #020617;
            border-radius: 8px;
            overflow: hidden;
        }

        .af-node-video {
            border: 0;
            display: block;
        }

        .af-activity-attachments,
        .af-activity-due,
        .af-activity-review-list,
        .af-activity-submit {
            display: grid;
            gap: 7px;
            margin-top: 8px;
        }

        .af-activity-file,
        .af-activity-meta,
        .af-activity-review-card,
        .af-activity-blocked,
        .af-activity-submission-status {
            border: 1px solid rgba(96, 165, 250, 0.28);
            border-radius: 7px;
            color: #dbeafe;
            font-size: 11px;
            font-weight: 800;
            padding: 6px 8px;
        }

        .af-activity-meta,
        .af-activity-blocked {
            background: rgba(2, 6, 23, 0.38);
            color: #bfdbfe;
            font-weight: 700;
            line-height: 1.45;
        }

        .af-activity-blocked {
            border-color: rgba(251, 191, 36, 0.45);
            color: #fde68a;
        }

        .af-activity-review-card {
            background: rgba(2, 6, 23, 0.5);
            display: grid;
            gap: 7px;
            line-height: 1.45;
        }

        .af-activity-review-title {
            align-items: flex-start;
            display: flex;
            gap: 8px;
            justify-content: space-between;
        }

        .af-activity-review-title strong,
        .af-activity-submission-status strong {
            color: #ffffff;
        }

        .af-activity-review-title span,
        .af-activity-review-card small,
        .af-activity-submission-status small {
            color: #93a4c6;
            display: block;
            font-size: 10px;
            font-weight: 700;
        }

        .af-activity-file {
            overflow: hidden;
            text-decoration: none;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .af-activity-due {
            grid-template-columns: minmax(0, 1fr) auto;
        }

        .af-activity-grade {
            align-items: center;
            display: grid;
            gap: 6px;
            grid-template-columns: minmax(0, 1fr) auto;
        }

        .af-activity-submit textarea,
        .af-activity-due input,
        .af-activity-grade input,
        .af-activity-submit input[type="file"] {
            border: 1px solid rgba(148, 163, 184, 0.24);
            border-radius: 7px;
            background: rgba(2, 6, 23, 0.58);
            color: #f8fafc;
            font: inherit;
            font-size: 12px;
            padding: 7px 8px;
            width: 100%;
        }

        .af-activity-submit input[type="file"]::file-selector-button {
            background: rgba(96, 165, 250, 0.16);
            border: 1px solid rgba(96, 165, 250, 0.36);
            border-radius: 6px;
            color: #dbeafe;
            cursor: pointer;
            font: inherit;
            font-size: 11px;
            font-weight: 800;
            margin-right: 8px;
            padding: 5px 8px;
        }

        .af-activity-submit textarea {
            min-height: 64px;
            resize: vertical;
        }

        .af-activity-submit button,
        .af-activity-due button,
        .af-activity-grade button {
            border: 1px solid rgba(34, 211, 238, 0.46);
            border-radius: 7px;
            background: rgba(14, 165, 233, 0.16);
            color: #e0f2fe;
            font-size: 12px;
            font-weight: 900;
            min-height: 30px;
            padding: 0 10px;
        }

        .af-activity-review-list {
            max-height: 210px;
            overflow: auto;
            padding-right: 2px;
        }

        .af-activity-empty {
            color: #93a4c6;
            font-size: 11px;
            font-weight: 700;
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

        .af-node-note-list {
            display: grid;
            gap: 7px;
            margin-top: 8px;
        }

        .af-node-note-button {
            background: linear-gradient(180deg, rgba(37, 99, 235, 0.18), rgba(15, 23, 42, 0.88));
            border: 1px solid rgba(96, 165, 250, 0.72);
            border-radius: 8px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
            color: #dbeafe;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            min-height: 28px;
            overflow: hidden;
            padding: 0 10px;
            text-overflow: ellipsis;
            transition: background 140ms ease, border-color 140ms ease, color 140ms ease;
            white-space: nowrap;
            width: 100%;
        }

        .af-node-note-button:hover {
            background: linear-gradient(180deg, rgba(34, 211, 197, 0.16), rgba(30, 41, 59, 0.94));
            border-color: rgba(34, 211, 197, 0.74);
            color: #ffffff;
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
            gap: 18px;
            max-width: 760px;
            padding: 38px 40px 26px;
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

        .af-note-dialog h2 {
            font-size: 28px;
            line-height: 1.2;
            margin: 0;
        }

        .af-note-dialog p {
            color: #cbd5e1;
            font-size: 15px;
            line-height: 1.5;
            margin: 0;
        }

        .af-note-resources {
            border-top: 1px solid rgba(148, 163, 184, 0.18);
            display: grid;
            gap: 10px;
            padding-top: 14px;
        }

        .af-note-resources strong {
            color: #dbeafe;
            font-size: 16px;
        }

        .af-note-resources ul {
            display: grid;
            gap: 8px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .af-note-resources li::before {
            content: "👉";
            margin-right: 8px;
        }
    body {
        background: #080a10;
        color: #f8fafc;
        overflow: hidden;
    }

    .af-viewer,
    .af-editor {
        background: #080a10;
        color: #f8fafc;
        display: grid;
        grid-template-rows: 46px 1fr;
        height: 100vh;
    }

    .af-topbar {
        align-items: center;
        background: rgba(8, 10, 16, 0.96) !important;
        border-bottom: 1px solid rgba(148, 163, 184, 0.16) !important;
        color: #f8fafc;
        display: flex;
        gap: 12px;
        min-height: 46px;
        padding: 0 12px;
    }

    .af-topbar a,
    .af-topbar button {
        background: rgba(17, 24, 39, 0.88);
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 8px;
        color: #f8fafc;
    }

    .af-topbar .af-view-module-tab {
        color: #93a4c6;
    }

    .af-topbar .af-view-module-tab.is-active {
        color: #5eead4;
    }

    .af-workspace {
        background: #080a10;
        display: grid;
        grid-template-columns: 286px 1fr;
        min-height: 0;
    }

    .af-sidebar {
        background: rgba(10, 13, 22, 0.96) !important;
        border-right: 1px solid rgba(148, 163, 184, 0.16) !important;
        color: #f8fafc;
        overflow: auto;
        padding: 14px;
    }

    .af-panel {
        background: #111520 !important;
        border: 1px solid rgba(148, 163, 184, 0.18) !important;
        border-radius: 8px !important;
        color: #f8fafc;
        box-shadow: none !important;
    }

    .af-panel h3 {
        color: #f8fafc;
        font-size: 17px;
        letter-spacing: 0;
        margin: 0 0 12px;
    }

    .af-panel p,
    .af-panel li,
    .af-panel span {
        color: #93a4c6 !important;
    }

    .af-canvas-wrap {
        background:
            radial-gradient(circle, rgba(148, 163, 184, 0.24) 1px, transparent 1.5px),
            #080a10 !important;
        background-size: 22px 22px !important;
        cursor: grab;
    }

    .af-canvas-wrap.is-panning {
        cursor: grabbing;
    }

    .af-canvas-wrap::before {
        display: none !important;
    }

    #roadmap-canvas {
        height: 100%;
        position: relative;
        width: 100%;
    }

    .af-minimap {
        background: rgba(17, 24, 39, 0.82) !important;
        border: 1px solid rgba(96, 165, 250, 0.25) !important;
        border-radius: 8px !important;
        color: #93a4c6;
    }

    .af-zoom {
        display: none !important;
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
        margin-top: 10px;
        padding: 8px 10px;
        text-decoration: none;
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

    .roadmap-node.type-activity {
        --af-brand: #38bdf8;
        border-color: rgba(56, 189, 248, 0.34);
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
    .roadmap-node .af-node-body {
        background: rgba(17, 24, 39, 0.96);
        border: 1px solid color-mix(in srgb, var(--af-brand, #60a5fa) 70%, transparent);
        border-top: 3px solid var(--af-brand, #60a5fa);
        border-radius: 8px;
        color: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 7px;
        height: 100%;
        justify-content: center;
        padding: 16px;
        width: 100%;
    }

    .roadmap-node.type-start .af-node-body,
    .roadmap-node.type-end .af-node-body {
        border-radius: 999px;
        padding-inline: 28px;
    }

    .roadmap-node.type-note .af-node-body {
        background: rgba(17, 24, 39, 0.78) !important;
        border: 1px dashed rgba(244, 114, 182, 0.82) !important;
        border-radius: 8px;
        box-shadow: inset 0 0 0 1px rgba(244, 114, 182, 0.08);
    }

    .roadmap-node.type-text {
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    .roadmap-node.type-text .af-node-body {
        align-items: center;
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        padding: 0;
        text-align: center;
    }

    .roadmap-node.type-text .af-node-kind,
    .roadmap-node.type-text .af-node-content {
        display: none !important;
    }

    .roadmap-node.type-text .af-node-title {
        color: #f8fafc;
        font-size: 40px;
        font-weight: 800;
        line-height: 1.05;
    }

    .roadmap-node.type-image {
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
        overflow: visible;
    }

    .roadmap-node.type-video {
        background: #0f172a;
        border: 1px solid rgba(96, 165, 250, 0.5);
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.26);
        overflow: visible;
    }

    .roadmap-node.type-image .af-image-body,
    .roadmap-node.type-image .af-node-image,
    .roadmap-node.type-video .af-video-body,
    .roadmap-node.type-video .af-node-video {
        display: block;
        height: 100%;
        width: 100%;
    }

    .roadmap-node.type-image .af-node-image {
        object-fit: contain;
    }

    .roadmap-node.type-video .af-video-body {
        background: #020617;
        border-radius: 8px;
        overflow: hidden;
    }

    .roadmap-node.type-video .af-node-video {
        border: 0;
    }

    .roadmap-node.type-attachment .af-node-content {
        max-height: 72px;
        overflow: hidden;
    }
    body.af-create-skin .af-skin-hidden {
        display: none !important;
    }

    body.af-create-skin .af-section-title {
        align-items: center;
        display: flex;
        gap: 8px;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    body.af-create-skin .af-section-title h3 {
        margin: 0;
    }

    body.af-create-skin .af-section-title span {
        background: rgba(20, 184, 166, 0.16);
        border: 1px solid rgba(45, 212, 191, 0.34);
        border-radius: 6px;
        color: #5eead4;
        font-size: 11px;
        font-weight: 800;
        padding: 4px 7px;
    }

    body.af-create-skin .af-stats {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-top: 14px;
    }

    body.af-create-skin .af-stat {
        background: rgba(15, 23, 42, 0.82);
        border: 1px solid rgba(148, 163, 184, 0.16);
        border-radius: 8px;
        display: grid;
        gap: 4px;
        padding: 14px;
    }

    body.af-create-skin .af-stat strong {
        color: #f8fafc;
        font-size: 22px;
        line-height: 1;
    }

    body.af-create-skin .af-help ol {
        color: #93a4c6;
        display: grid;
        gap: 10px;
        margin: 0;
        padding-left: 18px;
    }

    body.af-readonly-skin .roadmap-node {
        cursor: default;
    }

    body.af-readonly-skin .af-canvas-wrap {
        cursor: grab;
        overflow: hidden !important;
        overscroll-behavior: none;
    }

    body.af-readonly-skin .af-canvas-wrap.is-panning {
        cursor: grabbing;
    }

    body.af-readonly-skin .af-canvas-wrap,
    body.af-readonly-skin #roadmap-canvas,
    body.af-readonly-skin #af-connections,
    body.af-readonly-skin .af-canvas-wrap *,
    body.af-readonly-skin .roadmap-node,
    body.af-readonly-skin .af-node-body,
    body.af-readonly-skin .af-image-body,
    body.af-readonly-skin .af-node-image {
        touch-action: none;
        user-select: none;
        -webkit-user-drag: none;
    }

    body.af-readonly-skin .af-node-image {
        pointer-events: none;
    }

    body.af-readonly-skin .af-download-link {
        pointer-events: auto;
        touch-action: manipulation;
        user-select: none;
    }

    body.af-create-skin .roadmap-node,
    body.af-create-skin .roadmap-node .af-node-body {
        outline: 0 !important;
    }

    body.af-create-skin .roadmap-node.type-start,
    body.af-create-skin .roadmap-node.type-end,
    body.af-create-skin .roadmap-node.type-task,
    body.af-create-skin .roadmap-node.type-decision,
    body.af-create-skin .roadmap-node.type-goal,
    body.af-create-skin .roadmap-node.type-resource,
    body.af-create-skin .roadmap-node.type-note,
    body.af-create-skin .roadmap-node.type-attachment {
        background: transparent !important;
        border: 0 !important;
    }

    body.af-create-skin .roadmap-node.type-task { --af-brand: #22d3ee; }
    body.af-create-skin .roadmap-node.type-decision { --af-brand: #8b5cf6; }
    body.af-create-skin .roadmap-node.type-goal { --af-brand: #a855f7; }
    body.af-create-skin .roadmap-node.type-resource { --af-brand: #14b8a6; }

    body.af-create-skin .roadmap-node.type-goal .af-node-body {
        border-radius: 999px;
    }

    body.af-create-skin .roadmap-node.type-resource {
        transform: skew(-3deg);
    }

    body.af-create-skin .roadmap-node.type-resource .af-node-body {
        transform: skew(3deg);
    }

    body.af-create-skin .roadmap-node.type-start .af-node-body,
    body.af-create-skin .roadmap-node.type-end .af-node-body {
        box-shadow: none !important;
        outline: 0 !important;
    }

    body.af-create-skin .roadmap-node.type-start .af-node-body {
        border-color: #22c55e !important;
    }

    body.af-create-skin .roadmap-node.type-end .af-node-body {
        border-color: #ef4444 !important;
    }

    body.af-create-skin .af-minimap {
        bottom: 18px !important;
        display: block !important;
        height: 112px !important;
        left: auto !important;
        overflow: hidden;
        padding: 12px !important;
        position: absolute !important;
        right: 18px !important;
        top: auto !important;
        width: 150px !important;
        z-index: 20;
    }

    body.af-create-skin .af-minimap span {
        color: #93a4c6;
        display: block;
        font-size: 11px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    body.af-create-skin .af-minimap-stage {
        height: calc(100% - 22px);
        position: relative;
        width: 100%;
    }

    body.af-create-skin .af-minimap-stage i {
        background: #14b8a6;
        border-radius: 2px;
        display: block;
        min-height: 5px;
        min-width: 8px;
        position: absolute;
    }

    body.af-create-skin .af-minimap-view {
        border: 1px solid rgba(96, 165, 250, 0.45);
        border-radius: 4px;
        inset: 0;
        opacity: 0.55;
        position: absolute;
    }
</style>
<style id="af-mobile-roadmap-viewer">
    .af-info-toggle {
        display: none;
    }

    @media (max-width: 768px) {
        html,
        body {
            height: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        .af-editor,
        .af-viewer {
            grid-template-rows: auto minmax(0, 1fr);
            height: 100dvh;
            min-height: 100dvh;
            overflow: hidden;
        }

        .af-topbar {
            display: grid !important;
            gap: 6px;
            grid-template-columns: auto minmax(0, 1fr) auto;
            min-height: 56px;
            padding: 6px 8px !important;
        }

        .af-left {
            gap: 7px;
            min-width: 0;
        }

        .af-left strong {
            display: block;
            font-size: 13px;
            line-height: 1.15;
            max-width: 112px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .af-logo {
            height: 26px;
            width: 26px;
        }

        .af-view-module-strip {
            grid-column: 2;
            max-width: 100%;
            min-width: 0;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .af-view-module-strip::-webkit-scrollbar {
            display: none;
        }

        .af-view-module-tab,
        .af-topbar a,
        .af-topbar button {
            min-height: 40px;
            touch-action: manipulation;
            white-space: nowrap;
        }

        .af-right {
            gap: 6px;
            justify-content: flex-end;
            min-width: 0;
        }

        .af-info-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 900;
            padding: 0 12px;
        }

        .af-workspace {
            display: block !important;
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
            width: 100%;
        }

        #roadmap-canvas,
        #af-connections {
            touch-action: none;
        }

        .af-sidebar {
            border: 1px solid rgba(96, 165, 250, 0.22) !important;
            border-radius: 0 10px 10px 0 !important;
            box-shadow: 20px 0 48px rgba(0, 0, 0, 0.44) !important;
            height: 100%;
            left: 0;
            max-width: min(82vw, 310px);
            overflow-y: auto;
            padding: 14px !important;
            position: absolute;
            top: 0;
            transform: translateX(calc(-100% - 12px));
            transition: transform 180ms ease;
            width: min(82vw, 310px);
            z-index: 30;
        }

        body.af-info-open .af-sidebar {
            transform: translateX(0);
        }

        body.af-info-open .af-info-toggle {
            border-color: rgba(34, 211, 197, 0.62) !important;
            color: #5eead4 !important;
        }

        .af-panel {
            margin-bottom: 12px;
        }

        .af-minimap {
            bottom: 10px !important;
            height: 74px !important;
            right: 10px !important;
            width: 112px !important;
        }

        .af-note-dialog {
            max-height: calc(100dvh - 32px);
            overflow: auto;
            padding: 24px 18px 18px;
            width: calc(100vw - 24px);
        }
    }

    @media (max-width: 480px) {
        .af-topbar {
            grid-template-columns: auto minmax(0, 1fr);
        }

        .af-right {
            grid-column: 1 / -1;
            justify-content: flex-start;
            overflow-x: auto;
        }

        .af-view-module-strip {
            grid-column: 2;
        }

        .af-left strong {
            max-width: 136px;
        }

        .af-sidebar {
            max-width: min(88vw, 316px);
            width: min(88vw, 316px);
        }
    }
</style>
</head>

<body>
@php
    $graph = method_exists($roadmap, 'getGraph')
        ? $roadmap->getGraph()
        : $roadmap->getRoadmapGraph();
@endphp

<div class="af-editor">
    <header class="af-topbar">
        <div class="af-left">
            <a href="{{ route('roadmaps.index', $turma->id) }}" class="af-btn">← Voltar</a>
            <img src="{{ asset('img/logo.png') }}" class="af-logo">
            <strong>{{ $roadmap->titulo ?? 'Roadmap' }}</strong>
        </div>

        <div class="af-view-module-strip" id="af-view-module-strip" aria-label="Modulos do roadmap"></div>

        <div class="af-right">
            <button type="button" class="af-info-toggle" data-roadmap-info-toggle aria-controls="af-roadmap-info" aria-expanded="false">
                Info
            </button>
            @if(auth()->user()->role === 'docente' && $turma->docente_id === auth()->id())
                <a href="{{ route('roadmaps.edit', [$turma->id, $roadmap->id]) }}" class="af-btn primary">Editar</a>
            @endif
        </div>
    </header>

    @if(session('success'))
        <div class="af-toast">{{ session('success') }}</div>
    @endif

    <main class="af-workspace">
        <aside class="af-sidebar" id="af-roadmap-info">
            <div class="af-panel">
                <h3>Resumo</h3>
                <p>{{ $roadmap->descricao ?: 'Visualização do roadmap.' }}</p>
            </div>

            <div class="af-panel">
                <h3>Informações</h3>
                <p>Total de blocos: {{ count($graph['nodes'] ?? []) }}</p>
                <p>Turma: {{ $turma->nome ?? $turma->id }}</p>
            </div>
        </aside>

        <section class="af-canvas-wrap">
            <svg id="af-connections"></svg>
            <div id="roadmap-canvas"></div>

            <div class="af-minimap"></div>
        </section>
    </main>
</div>

<script>
    const readonlyGraph = @json($graph);
    const viewport = { x: 0, y: 0, scale: 1 };
    const zoom = { min: 0.35, max: 2.5, step: 0.0018 };
    let panning = null;
    const touchPointers = new Map();
    let pinchGesture = null;

    function escapeHTML(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    const escapeHtml = escapeHTML;

    function nodeKind(type) {
        return {
            start: 'Inicio',
            end: 'Fim',
            milestone: 'Marco',
            task: 'Tarefa',
            activity: 'Atividade',
            decision: 'Decisao',
            goal: 'Objetivo',
            resource: 'Recurso',
            note: 'Nota',
            text: 'Texto',
            attachment: 'Anexo',
            image: 'Imagem',
            video: 'Vídeo'
        }[type] || 'Bloco';
    }

    function normalizeYouTubeUrl(value) {
        const raw = String(value || '').trim();
        if (!raw) return null;

        const idFromText = raw.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/|live\/))([A-Za-z0-9_-]{11})/)?.[1]
            || raw.match(/^[A-Za-z0-9_-]{11}$/)?.[0];

        let videoId = idFromText || null;

        try {
            const url = new URL(raw);
            const host = url.hostname.replace(/^www\./, '');
            const isYouTube = host === 'youtube.com' || host === 'm.youtube.com' || host === 'youtu.be' || host === 'youtube-nocookie.com';

            if (!isYouTube) return null;

            if (!videoId && host === 'youtu.be') {
                videoId = url.pathname.split('/').filter(Boolean)[0] || null;
            }

            if (!videoId && (host === 'youtube.com' || host === 'm.youtube.com')) {
                videoId = url.searchParams.get('v')
                    || url.pathname.match(/^\/(?:embed|shorts|live)\/([A-Za-z0-9_-]{11})/)?.[1]
                    || null;
            }
        } catch (_) {
            if (!videoId) return null;
        }

        if (!/^[A-Za-z0-9_-]{11}$/.test(videoId || '')) return null;

        return {
            id: videoId,
            embedUrl: `https://www.youtube-nocookie.com/embed/${videoId}`,
        };
    }

    function videoNodeMarkup(node) {
        const video = normalizeYouTubeUrl(node.videoUrl || node.content || node.videoEmbedUrl || '');

        if (!video) {
            return `
                <div class="af-video-body">
                    <div class="af-video-placeholder">
                        <span>${escapeHTML(node.title || 'Vídeo')}</span>
                        <small>URL do YouTube indisponivel</small>
                    </div>
                </div>`;
        }

        return `
            <div class="af-video-body">
                <iframe
                    class="af-node-video"
                    src="${escapeHTML(video.embedUrl)}"
                    title="${escapeHTML(node.title || 'Vídeo do YouTube')}"
                    loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen></iframe>
            </div>`;
    }

    function applyViewport() {
        viewport.scale = Math.min(zoom.max, Math.max(zoom.min, Number(viewport.scale) || 1));
        const transform = `translate(${viewport.x}px, ${viewport.y}px) scale(${viewport.scale})`;
        document.getElementById('roadmap-canvas')?.style.setProperty('transform', transform);
        document.getElementById('af-connections')?.style.setProperty('transform', transform);
    }

    function zoomCanvasAt(event) {
        const wrap = document.querySelector('.af-canvas-wrap');
        const rect = wrap?.getBoundingClientRect();

        if (!rect || event.target.closest?.('input, textarea, select, [contenteditable="true"], .af-note-modal')) {
            return;
        }

        event.preventDefault();

        const oldScale = Number(viewport.scale) || 1;
        const nextScale = Math.min(zoom.max, Math.max(zoom.min, oldScale * Math.exp(-event.deltaY * zoom.step)));

        if (Math.abs(nextScale - oldScale) < 0.001) return;

        const mouseX = event.clientX - rect.left;
        const mouseY = event.clientY - rect.top;
        const canvasX = (mouseX - viewport.x) / oldScale;
        const canvasY = (mouseY - viewport.y) / oldScale;

        viewport.scale = nextScale;
        viewport.x = mouseX - canvasX * nextScale;
        viewport.y = mouseY - canvasY * nextScale;
        applyViewport();
    }

    function trackTouchPointer(event) {
        if (event.pointerType !== 'touch') return;

        touchPointers.set(event.pointerId, {
            clientX: event.clientX,
            clientY: event.clientY
        });
    }

    function getPinchPoints() {
        return Array.from(touchPointers.values()).slice(0, 2);
    }

    function getPinchDistance(points) {
        return Math.hypot(points[1].clientX - points[0].clientX, points[1].clientY - points[0].clientY);
    }

    function getPinchCenter(points) {
        return {
            clientX: (points[0].clientX + points[1].clientX) / 2,
            clientY: (points[0].clientY + points[1].clientY) / 2
        };
    }

    function startPinchGesture() {
        const wrap = document.querySelector('.af-canvas-wrap');
        const rect = wrap?.getBoundingClientRect();
        const points = getPinchPoints();

        if (!rect || points.length < 2) return;

        const distance = getPinchDistance(points);
        if (distance < 8) return;

        const center = getPinchCenter(points);
        const centerX = center.clientX - rect.left;
        const centerY = center.clientY - rect.top;
        const startScale = Number(viewport.scale) || 1;

        panning = null;
        pinchGesture = {
            startDistance: distance,
            startScale,
            canvasX: (centerX - viewport.x) / startScale,
            canvasY: (centerY - viewport.y) / startScale
        };

        wrap.classList.remove('is-panning');
        wrap.classList.add('is-pinching');
    }

    function updatePinchGesture() {
        if (!pinchGesture) return;

        const wrap = document.querySelector('.af-canvas-wrap');
        const rect = wrap?.getBoundingClientRect();
        const points = getPinchPoints();

        if (!rect || points.length < 2) return;

        const distance = getPinchDistance(points);
        const center = getPinchCenter(points);
        const centerX = center.clientX - rect.left;
        const centerY = center.clientY - rect.top;
        const nextScale = Math.min(
            zoom.max,
            Math.max(zoom.min, pinchGesture.startScale * (distance / pinchGesture.startDistance))
        );

        viewport.scale = nextScale;
        viewport.x = centerX - pinchGesture.canvasX * nextScale;
        viewport.y = centerY - pinchGesture.canvasY * nextScale;
        applyViewport();
    }

    function finishPinchGesture() {
        pinchGesture = null;
        document.querySelector('.af-canvas-wrap')?.classList.remove('is-pinching');
    }

    function releaseTouchPointer(event) {
        if (event.pointerType !== 'touch') return false;

        const wasPinching = Boolean(pinchGesture);
        touchPointers.delete(event.pointerId);

        if (pinchGesture && touchPointers.size < 2) {
            finishPinchGesture();
        }

        return wasPinching;
    }

    function getPortPosition(node, port) {
        return {
            top: { x: node.x + node.width / 2, y: node.y },
            right: { x: node.x + node.width, y: node.y + node.height / 2 },
            bottom: { x: node.x + node.width / 2, y: node.y + node.height },
            left: { x: node.x, y: node.y + node.height / 2 }
        }[port || 'left'];
    }

    function makePath(start, end, dashed = false) {
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        const dx = Math.max(Math.abs(end.x - start.x) * 0.48, 48);
        const direction = end.x >= start.x ? 1 : -1;
        path.setAttribute('d', `M ${start.x} ${start.y} C ${start.x + (dx * direction)} ${start.y}, ${end.x - (dx * direction)} ${end.y}, ${end.x} ${end.y}`);
        path.setAttribute('fill', 'none');
        path.setAttribute('stroke', '#60a5fa');
        path.setAttribute('stroke-width', '2.5');
        path.setAttribute('stroke-linecap', 'round');
        if (dashed) path.setAttribute('stroke-dasharray', '7 6');
        return path;
    }

        function escapeAttribute(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function renderReadonlyRoadmap() {
        const canvas = document.getElementById('roadmap-canvas');
        const svg = document.getElementById('af-connections');
        const nodes = (readonlyGraph.nodes || []).map((node, index) => ({
            x: node.x ?? 120 + index * 230,
            y: node.y ?? 100 + index * 90,
            width: node.width ?? 220,
            height: node.height ?? 126,
            type: node.type ?? 'task',
            title: node.title ?? node.text ?? 'Sem titulo',
            content: node.content ?? node.description ?? '',
            ...node
        }));
        const connections = readonlyGraph.connections || [];
        canvas.innerHTML = '';
        svg.innerHTML = '';

        nodes.forEach((normalized) => {
            const el = document.createElement('div');
            el.className = `roadmap-node type-${normalized.type}`;
            el.style.left = `${normalized.x}px`;
            el.style.top = `${normalized.y}px`;
            el.style.width = `${normalized.width}px`;
            el.style.height = `${normalized.height}px`;
            el.innerHTML = normalized.type === 'image'
                ? `<div class="af-image-body"><img class="af-node-image" src="${escapeHTML(normalized.imageSrc || '')}" alt="${escapeHTML(normalized.title)}"></div>`
                : `<div class="af-node-body"><span class="af-node-kind">${escapeHTML(nodeKind(normalized.type))}</span><div class="af-node-title">${escapeHTML(normalized.title)}</div><div class="af-node-content">${escapeHTML(normalized.content)}</div></div>`;
                    if (normalized.type === 'video') {
                        el.innerHTML = videoNodeMarkup(normalized);
                    }

                    if (normalized.type === 'attachment') {
                        el.innerHTML = `
                            <div class="af-node-body">
                                <span class="af-node-kind">${escapeHtml(normalized.kind || 'Anexo')}</span>
                                <div class="af-node-title">${escapeHtml(normalized.title || normalized.fileName || 'Arquivo anexado')}</div>
                                <div class="af-node-content">${escapeHtml(normalized.content || 'Arquivo disponivel para download.')}</div>
                                ${normalized.fileData ? `<a class="af-download-link" href="${escapeAttribute(normalized.fileData)}" download="${escapeAttribute(normalized.fileName || 'anexo')}">Baixar</a>` : ''}
                            </div>`;
                    }

                    canvas.appendChild(el);
        });

        connections.forEach(connection => {
            const from = nodes.find(node => node.id === connection.from);
            const to = nodes.find(node => node.id === connection.to);
            if (!from || !to) return;
            const dashed = from.type === 'note' || to.type === 'note';
            svg.appendChild(makePath(getPortPosition(from, connection.fromPort), getPortPosition(to, connection.toPort), dashed));
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const wrap = document.querySelector('.af-canvas-wrap');

        const canStartPan = (event) => {
            if (event.button !== 0 || !wrap?.contains(event.target)) return false;

            return !event.target.closest('a, button, input, textarea, select, [data-no-pan]');
        };

        const canTouchCanvas = (event) => {
            return event.pointerType === 'touch'
                && wrap?.contains(event.target)
                && !event.target.closest('a, button, input, textarea, select, iframe, [data-no-pan]');
        };

        document.addEventListener('pointerdown', event => {
            if (canTouchCanvas(event)) {
                trackTouchPointer(event);
                wrap.setPointerCapture?.(event.pointerId);

                if (touchPointers.size >= 2) {
                    event.preventDefault();
                    event.stopPropagation();
                    startPinchGesture();
                    return;
                }
            }

            if (!canStartPan(event)) return;

            event.preventDefault();
            event.stopPropagation();

            wrap.setPointerCapture?.(event.pointerId);
            panning = { startX: event.clientX, startY: event.clientY, originX: viewport.x, originY: viewport.y };
            wrap.classList.add('is-panning');
        }, true);

        document.addEventListener('pointermove', event => {
            if (event.pointerType === 'touch' && touchPointers.has(event.pointerId)) {
                trackTouchPointer(event);

                if (pinchGesture) {
                    event.preventDefault();
                    updatePinchGesture();
                    return;
                }
            }

            if (!panning) return;
            event.preventDefault();

            viewport.x = panning.originX + (event.clientX - panning.startX);
            viewport.y = panning.originY + (event.clientY - panning.startY);
            applyViewport();
        }, true);

        const endPan = event => {
            if (!event) {
                touchPointers.clear();
                finishPinchGesture();
            }

            if (event?.pointerType === 'touch' && releaseTouchPointer(event)) {
                return;
            }

            if (event?.pointerId !== undefined) {
                wrap?.releasePointerCapture?.(event.pointerId);
            }

            panning = null;
            wrap?.classList.remove('is-panning');
        };

        document.addEventListener('pointerup', endPan, true);
        document.addEventListener('pointercancel', endPan, true);
        wrap?.addEventListener('wheel', zoomCanvasAt, { passive: false });
        window.addEventListener('blur', endPan);

        document.addEventListener('dragstart', event => {
            if (wrap?.contains(event.target) && !event.target.closest('a, button, [data-no-pan]')) {
                event.preventDefault();
            }
        }, true);

        document.addEventListener('auxclick', event => {
            if (event.button === 1 && event.target.closest('.af-canvas-wrap')) event.preventDefault();
        });

        try {
            renderReadonlyRoadmap();
        } catch (error) {
            console.error('UniRoad show: falha ao renderizar preview inicial do roadmap.', error);
        }
    });
</script>

@ddfsnScripts
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.body.classList.add('af-create-skin', 'af-readonly-skin');

        const infoToggle = document.querySelector('[data-roadmap-info-toggle]');
        const infoPanel = document.getElementById('af-roadmap-info');
        const closeInfoPanel = () => {
            document.body.classList.remove('af-info-open');
            infoToggle?.setAttribute('aria-expanded', 'false');
        };

        infoToggle?.addEventListener('click', (event) => {
            event.preventDefault();
            const shouldOpen = !document.body.classList.contains('af-info-open');
            document.body.classList.toggle('af-info-open', shouldOpen);
            infoToggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeInfoPanel();
            }
        });

        document.querySelector('.af-canvas-wrap')?.addEventListener('pointerdown', (event) => {
            if (!document.body.classList.contains('af-info-open')) {
                return;
            }

            if (infoPanel?.contains(event.target) || infoToggle?.contains(event.target)) {
                return;
            }

            closeInfoPanel();
        }, true);

        const sidebar = document.querySelector('.af-sidebar');
        const nodes = Array.isArray(window.roadmapNodes) ? window.roadmapNodes : [];
        const connections = Array.isArray(window.roadmapConnections) ? window.roadmapConnections : [];

        if (sidebar) {
            sidebar.innerHTML = `
                <section class="af-panel">
                    <h3>Resumo</h3>
                    <p>Visualizacao do roadmap.</p>
                    <div class="af-stats">
                        <div class="af-stat"><strong id="af-readonly-node-count">${nodes.length}</strong><span>blocos</span></div>
                        <div class="af-stat"><strong id="af-readonly-connection-count">${connections.length}</strong><span>conexões</span></div>
                    </div>
                </section>

                <section class="af-panel af-help">
                    <h3>Navegacao</h3>
                    <ol>
                        <li>Segure o botao esquerdo e arraste para andar pelo mapa.</li>
                        <li>Use o botao Editar para alterar o roadmap.</li>
                    </ol>
                </section>
            `;
        }

        window.refreshReadonlySidebar = (nextNodes = [], nextConnections = []) => {
            const nodeCount = document.getElementById('af-readonly-node-count');
            const connectionCount = document.getElementById('af-readonly-connection-count');

            if (nodeCount) nodeCount.textContent = nextNodes.length;
            if (connectionCount) connectionCount.textContent = nextConnections.length;
        };

        document.querySelectorAll('.af-toolbar-floating, .af-history-button, [data-af-add-node], [data-action="undo"], [data-action="redo"]').forEach((item) => {
            item.classList.add('af-skin-hidden');
        });

        document.querySelectorAll('.af-topbar a, .af-topbar button').forEach((item) => {
            const label = item.textContent.trim().toLowerCase();
            const href = item.getAttribute('href') || '';

            if (label === 'editar' || href.includes('/edit')) {
                item.classList.add('af-skin-hidden');
                item.setAttribute('aria-hidden', 'true');
            }
        });

        const refreshReadonlyMinimap = () => {
            const minimap = document.querySelector('.af-minimap');
            const canvas = document.querySelector('#roadmap-canvas');
            const wrap = document.querySelector('.af-canvas-wrap');

            if (!minimap || !canvas || !wrap) return;

            const nodes = [...canvas.querySelectorAll('.roadmap-node')];
            minimap.innerHTML = '<span>Mapa</span><div class="af-minimap-stage"></div>';

            const stage = minimap.querySelector('.af-minimap-stage');
            if (!stage || !nodes.length) return;

            const bounds = nodes.reduce((box, node) => {
                const left = Number.parseFloat(node.style.left) || 0;
                const top = Number.parseFloat(node.style.top) || 0;
                const width = Number.parseFloat(node.style.width) || node.offsetWidth || 160;
                const height = Number.parseFloat(node.style.height) || node.offsetHeight || 90;

                return {
                    minX: Math.min(box.minX, left),
                    minY: Math.min(box.minY, top),
                    maxX: Math.max(box.maxX, left + width),
                    maxY: Math.max(box.maxY, top + height),
                };
            }, { minX: Infinity, minY: Infinity, maxX: -Infinity, maxY: -Infinity });

            const pad = 80;
            const worldWidth = Math.max(1, bounds.maxX - bounds.minX + pad * 2);
            const worldHeight = Math.max(1, bounds.maxY - bounds.minY + pad * 2);

            nodes.forEach((node) => {
                const left = Number.parseFloat(node.style.left) || 0;
                const top = Number.parseFloat(node.style.top) || 0;
                const width = Number.parseFloat(node.style.width) || node.offsetWidth || 160;
                const height = Number.parseFloat(node.style.height) || node.offsetHeight || 90;
                const marker = document.createElement('i');

                marker.style.left = `${((left - bounds.minX + pad) / worldWidth) * 100}%`;
                marker.style.top = `${((top - bounds.minY + pad) / worldHeight) * 100}%`;
                marker.style.width = `${Math.max(8, (width / worldWidth) * 100)}%`;
                marker.style.height = `${Math.max(5, (height / worldHeight) * 100)}%`;
                stage.appendChild(marker);
            });

            const view = document.createElement('b');
            view.className = 'af-minimap-view';
            stage.appendChild(view);
        };

        window.refreshReadonlyMinimap = refreshReadonlyMinimap;
        window.setTimeout(refreshReadonlyMinimap, 80);
    });
</script>
@php
    $activitySubmitUrlTemplate = route('roadmaps.activities.submit', [$turma->id, $roadmap->id, '__NODE_ID__']);
@endphp
<script id="af-show-conteudo-json-runtime">
(() => {
    const savedGraphRaw = @json($roadmap->conteudo_json ?? null);
    const activitySubmitUrlTemplate = @json($activitySubmitUrlTemplate);
    const activityMeta = @json($activityContext['activities'] ?? []);
    const activitySubmissions = @json($activitySubmissions ?? []);
    const activityStaffSubmissions = @json($activityContext['staffSubmissions'] ?? []);
    const canReviewActivities = @json($activityContext['canReview'] ?? false);
    const canSubmitActivities = @json(auth()->user()->role === 'aluno');
    const csrfToken = @json(csrf_token());

    const parseMaybeJson = (value) => {
        if (!value) return null;
        if (typeof value === 'string') {
            try {
                return JSON.parse(value);
            } catch (_) {
                return null;
            }
        }
        return value;
    };

    const normalizeGraph = (value) => {
        const parsed = parseMaybeJson(value);

        if (!parsed) return null;

        if (Array.isArray(parsed.modules) && parsed.modules.length) {
            const modules = parsed.modules.map((module, index) => ({
                id: String(module.id || `module-${index + 1}`),
                title: module.title || module.name || `Módulo ${index + 1}`,
                nodes: Array.isArray(module.nodes) ? module.nodes : [],
                connections: Array.isArray(module.connections) ? module.connections : [],
                viewport: module.viewport || { x: 0, y: 0, scale: 1 },
            }));
            const first = modules[0];

            return {
                nodes: first.nodes,
                connections: first.connections,
                modules,
                activeModuleId: first.id,
                viewport: first.viewport || { x: 0, y: 0, scale: 1 },
            };
        }

        if (parsed.nodes || parsed.connections) {
            return {
                nodes: Array.isArray(parsed.nodes) ? parsed.nodes : [],
                connections: Array.isArray(parsed.connections) ? parsed.connections : [],
                modules: [{
                    id: 'module-1',
                    title: 'Módulo 1',
                    nodes: Array.isArray(parsed.nodes) ? parsed.nodes : [],
                    connections: Array.isArray(parsed.connections) ? parsed.connections : [],
                    viewport: parsed.viewport || { x: 0, y: 0, scale: 1 },
                }],
                activeModuleId: 'module-1',
                viewport: parsed.viewport || { x: 0, y: 0, scale: 1 },
            };
        }

        if (parsed.data && (parsed.data.nodes || parsed.data.connections)) {
            return {
                nodes: Array.isArray(parsed.data.nodes) ? parsed.data.nodes : [],
                connections: Array.isArray(parsed.data.connections) ? parsed.data.connections : [],
                modules: [{
                    id: 'module-1',
                    title: 'Módulo 1',
                    nodes: Array.isArray(parsed.data.nodes) ? parsed.data.nodes : [],
                    connections: Array.isArray(parsed.data.connections) ? parsed.data.connections : [],
                    viewport: parsed.data.viewport || { x: 0, y: 0, scale: 1 },
                }],
                activeModuleId: 'module-1',
                viewport: parsed.data.viewport || { x: 0, y: 0, scale: 1 },
            };
        }

        return null;
    };

    const graph = normalizeGraph(savedGraphRaw);

    let activeModuleId = graph.activeModuleId || graph.modules?.[0]?.id || 'module-1';

    const getActiveGraph = () => {
        const active = graph.modules?.find((module) => module.id === activeModuleId)
            || graph.modules?.[0]
            || graph;

        activeModuleId = active.id || activeModuleId;

        return {
            id: active.id || 'module-1',
            title: active.title || 'Módulo 1',
            nodes: Array.isArray(active.nodes) ? active.nodes : [],
            connections: Array.isArray(active.connections) ? active.connections : [],
            viewport: active.viewport || { x: 0, y: 0, scale: 1 },
        };
    };

    const syncActiveGlobals = () => {
        const active = getActiveGraph();

        window.roadmapNodes = active.nodes;
        window.roadmapConnections = active.connections;
        window.artisanFlowData = {
            ...graph,
            nodes: active.nodes,
            connections: active.connections,
            activeModuleId: active.id,
            viewport: active.viewport,
        };
        window.showRoadmapGraph = window.artisanFlowData;

        return active;
    };

    window.showRoadmapRawConteudoJson = savedGraphRaw;
    syncActiveGlobals();

    if (!graph) {
        console.warn('UniRoad show: conteudo_json vazio ou em formato invalido.', savedGraphRaw);
        return;
    }

    const renderModuleSelector = () => {
        const strip = document.getElementById('af-view-module-strip');
        const modules = graph.modules?.length ? graph.modules : [getActiveGraph()];

        if (!strip) return;

        strip.innerHTML = '';

        modules.forEach((module, index) => {
            const button = document.createElement('button');

            button.type = 'button';
            button.className = `af-view-module-tab${module.id === activeModuleId ? ' is-active' : ''}`;
            button.textContent = module.title || `Módulo ${index + 1}`;
            button.addEventListener('click', () => setActiveModule(module.id));

            strip.appendChild(button);
        });
    };

    const applyModuleViewport = (active) => {
        if (typeof viewport === 'undefined') return;

        viewport.x = Number(active.viewport?.x) || 0;
        viewport.y = Number(active.viewport?.y) || 0;
        viewport.scale = Math.min(zoom.max, Math.max(zoom.min, Number(active.viewport?.scale) || 1));

        if (typeof applyViewport === 'function') {
            applyViewport();
        }
    };

    const setActiveModule = (id) => {
        activeModuleId = id;
        const active = syncActiveGlobals();

        renderModuleSelector();
        render();
        applyModuleViewport(active);
        window.refreshReadonlySidebar?.(active.nodes, active.connections);
        window.setTimeout(() => window.refreshReadonlyMinimap?.(), 60);
    };

    window.setReadonlyRoadmapModule = setActiveModule;

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const normalizeNodeNotes = (notes = []) => Array.isArray(notes)
        ? notes.map((note, index) => ({
            id: String(note.id || `note-${index}`),
            title: note.title || note.name || `Nota ${index + 1}`,
            body: note.body || note.content || note.description || '',
            resources: Array.isArray(note.resources) ? note.resources : [],
        }))
        : [];

    const nodeNotesMarkup = (node) => {
        const notes = normalizeNodeNotes(node.notes);

        if (!notes.length || ['image', 'text'].includes(node.type || 'task')) {
            return '';
        }

        return `
            <div class="af-node-note-list">
                ${notes.map((note, index) => `
                    <button type="button" class="af-node-note-button" data-note-index="${index}" data-no-pan="true">
                        ${escapeHtml(note.title)}
                    </button>
                `).join('')}
            </div>
        `;
    };

    const ensureReadonlyNoteModal = () => {
        let modal = document.getElementById('af-readonly-note-modal');

        if (modal) return modal;

        modal = document.createElement('div');
        modal.id = 'af-readonly-note-modal';
        modal.className = 'af-note-modal';
        modal.hidden = true;
        modal.innerHTML = `
            <div class="af-note-dialog" role="dialog" aria-modal="true">
                <button type="button" class="af-note-close" data-note-close="true" title="Fechar">×</button>
                <h2></h2>
                <p></p>
                <div class="af-note-resources" hidden>
                    <strong>Recursos</strong>
                    <ul></ul>
                </div>
            </div>
        `;

        modal.addEventListener('click', (event) => {
            if (event.target === modal || event.target.closest('[data-note-close]')) {
                modal.hidden = true;
            }
        });

        document.body.appendChild(modal);
        return modal;
    };

    const openReadonlyNoteModal = (note) => {
        const modal = ensureReadonlyNoteModal();
        const resources = Array.isArray(note.resources) ? note.resources : [];

        modal.querySelector('h2').textContent = note.title || 'Nota';
        modal.querySelector('p').textContent = note.body || 'Sem descrição.';
        modal.querySelector('.af-note-resources').hidden = resources.length === 0;
        modal.querySelector('.af-note-resources ul').innerHTML = resources
            .map((item) => `<li>${escapeHtml(item)}</li>`)
            .join('');
        modal.hidden = false;
    };

    const normalizeActivityAttachments = (attachments = []) => Array.isArray(attachments)
        ? attachments.filter((item) => item && (item.fileData || item.name || item.fileName))
        : [];

    const activitySubmitUrl = (nodeId) => activitySubmitUrlTemplate.replace('__NODE_ID__', encodeURIComponent(nodeId || ''));

    const formatFileSize = (size) => {
        const bytes = Number(size) || 0;

        if (!bytes) return '';
        if (bytes < 1024) return `${bytes} B`;
        if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;

        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    };

    const fileLinksMarkup = (files = []) => Array.isArray(files) && files.length
        ? files.map((file) => `
            <a class="af-activity-file" href="${escapeHtml(file.url || file.fileData || '#')}" ${file.url ? '' : `download="${escapeHtml(file.name || file.fileName || 'arquivo')}"`} data-no-pan="true">
                ${escapeHtml(file.name || file.fileName || 'Arquivo enviado')}${file.size ? ` · ${escapeHtml(formatFileSize(file.size))}` : ''}
            </a>
        `).join('')
        : '';

    const activityAttachmentsMarkup = (node) => {
        const meta = activityMeta[node.id] || {};
        const attachments = normalizeActivityAttachments(meta.attachments || node.activityAttachments);

        if (!attachments.length) {
            return '';
        }

        return `<div class="af-activity-attachments">${fileLinksMarkup(attachments)}</div>`;
    };

    const activityMetaMarkup = (node) => {
        const meta = activityMeta[node.id] || {};
        const items = [];

        if (meta.dueDateLabel) items.push(`Entrega ate ${escapeHtml(meta.dueDateLabel)}`);
        if (meta.createdAt) items.push(`Criada em ${escapeHtml(meta.createdAt)}`);

        return items.length ? `<div class="af-activity-meta" data-no-pan="true">${items.join('<br>')}</div>` : '';
    };

    const activityDueMarkup = (node) => {
        const meta = activityMeta[node.id] || {};

        if (!canReviewActivities || !meta.updateUrl) return '';

        return `
            <form class="af-activity-due" method="POST" action="${escapeHtml(meta.updateUrl)}" data-no-pan="true">
                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                <input type="hidden" name="_method" value="PATCH">
                <input type="date" name="data_entrega" value="${escapeHtml(meta.dueDate || '')}" aria-label="Data de entrega">
                <button type="submit">Salvar prazo</button>
            </form>
        `;
    };

    const activityReviewMarkup = (node) => {
        if (!canReviewActivities) return '';

        const submissions = Array.isArray(activityStaffSubmissions[node.id])
            ? activityStaffSubmissions[node.id]
            : [];

        return `
            <div class="af-activity-review-list" data-no-pan="true">
                ${submissions.length ? submissions.map((submission) => `
                    <section class="af-activity-review-card">
                        <div class="af-activity-review-title">
                            <div>
                                <strong>${escapeHtml(submission.studentName || 'Aluno')}</strong>
                                <span>${escapeHtml(submission.studentEmail || '')}</span>
                            </div>
                            <small>${escapeHtml(submission.createdAt || 'data registrada')}</small>
                        </div>
                        ${submission.content ? `<div>${escapeHtml(submission.content)}</div>` : '<small>Sem descrição enviada.</small>'}
                        ${fileLinksMarkup(submission.files)}
                        ${submission.updatedAt ? `<small>Atualizado em ${escapeHtml(submission.updatedAt)}</small>` : ''}
                        ${submission.gradeUrl ? `
                            <form class="af-activity-grade" method="POST" action="${escapeHtml(submission.gradeUrl)}">
                                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                                <input type="hidden" name="_method" value="PATCH">
                                <input type="number" name="nota" min="0" max="100" value="${escapeHtml(submission.grade ?? '')}" placeholder="Nota de 0 a 100" aria-label="Nota do aluno">
                                <button type="submit">Salvar nota</button>
                            </form>
                        ` : ''}
                    </section>
                `).join('') : '<div class="af-activity-empty">Nenhuma submissão enviada ainda.</div>'}
            </div>
        `;
    };

    const activitySubmissionMarkup = (node) => {
        if (canReviewActivities) {
            return activityReviewMarkup(node);
        }

        const meta = activityMeta[node.id] || {};
        const submission = activitySubmissions[node.id];

        if (submission) {
            const files = Array.isArray(submission.files) ? submission.files : [];

            return `
                <div class="af-activity-submission-status" data-no-pan="true">
                    <strong>Entrega enviada</strong>
                    <small>${escapeHtml(submission.sentAt || 'data registrada')}</small>
                    ${submission.content ? `<div>${escapeHtml(submission.content)}</div>` : ''}
                    ${files.length ? fileLinksMarkup(files) : ''}
                    <div>Nota: ${submission.grade === null || submission.grade === undefined || submission.grade === '' ? 'aguardando avaliação' : `${escapeHtml(submission.grade)}/100`}</div>
                </div>
            `;
        }

        if (!canSubmitActivities) {
            return '';
        }

        if (meta.isPastDue) {
            return `<div class="af-activity-blocked" data-no-pan="true">O prazo terminou e novos envios estão bloqueados.</div>`;
        }

        return `
            <form class="af-activity-submit" method="POST" action="${escapeHtml(activitySubmitUrl(node.id))}" enctype="multipart/form-data" data-no-pan="true">
                <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                <textarea name="conteudo" rows="2" placeholder="Mensagem opcional"></textarea>
                <input type="file" name="arquivos[]" multiple>
                <button type="submit">Enviar atividade</button>
            </form>
        `;
    };

    const activityNodeMarkup = (node) => `
        <div class="af-node-body">
            <span class="af-node-kind">${escapeHtml(node.kind || 'Atividade')}</span>
            <div class="af-node-title">${escapeHtml(node.title || 'Atividade')}</div>
            <div class="af-node-content">${escapeHtml(node.content || '')}</div>
            ${activityMetaMarkup(node)}
            ${activityAttachmentsMarkup(node)}
            ${activityDueMarkup(node)}
            ${nodeNotesMarkup(node)}
            ${activitySubmissionMarkup(node)}
        </div>
    `;

    const getConnectionEndpoint = (connection, side) => {
        if (side === 'source') {
            return connection.source || connection.sourceId || connection.from || connection.fromNodeId;
        }

        return connection.target || connection.targetId || connection.to || connection.toNodeId;
    };

    const portPoint = (node, port = 'right') => {
        const x = Number(node.x) || 0;
        const y = Number(node.y) || 0;
        const width = Number(node.__viewWidth || node.width) || 180;
        const height = Number(node.__viewHeight || node.height) || 92;

        if (port === 'left') return { x, y: y + height / 2 };
        if (port === 'top') return { x: x + width / 2, y };
        if (port === 'bottom') return { x: x + width / 2, y: y + height };

        return { x: x + width, y: y + height / 2 };
    };

    const render = () => {
        const canvas = document.querySelector('#roadmap-canvas');
        const svg = document.querySelector('#af-connections');
        const active = syncActiveGlobals();

        if (!canvas) return;

        canvas.querySelectorAll('.roadmap-node').forEach((node) => node.remove());

        active.nodes.forEach((node) => {
            const element = document.createElement('article');
            const type = node.type || 'task';
            const notes = normalizeNodeNotes(node.notes);

            element.className = `roadmap-node type-${type}`;
            element.dataset.nodeId = node.id;
            element.dataset.type = type;
            const viewWidth = type === 'activity'
                ? Math.max(Number(node.width) || 0, canReviewActivities ? 520 : 360)
                : (Number(node.width) || 180);
            const viewHeight = type === 'activity'
                ? Math.max(Number(node.height) || 0, canReviewActivities ? 360 : 250)
                : (Number(node.height) || 92);

            node.__viewWidth = viewWidth;
            node.__viewHeight = viewHeight;
            element.style.left = `${Number(node.x) || 0}px`;
            element.style.top = `${Number(node.y) || 0}px`;
            element.style.width = `${viewWidth}px`;
            element.style.height = `${viewHeight}px`;

            if (type === 'image') {
                element.innerHTML = `<div class="af-image-body"><img class="af-node-image" src="${escapeHtml(node.imageSrc || node.content || '')}" alt="${escapeHtml(node.title || 'Imagem')}"></div>`;
            } else if (type === 'video') {
                element.innerHTML = videoNodeMarkup(node);
            } else if (type === 'activity') {
                element.innerHTML = activityNodeMarkup(node);
            } else if (type === 'attachment') {
                element.innerHTML = `
                    <div class="af-node-body">
                        <span class="af-node-kind">${escapeHtml(node.kind || 'Anexo')}</span>
                        <div class="af-node-title">${escapeHtml(node.title || node.fileName || 'Arquivo anexado')}</div>
                        <div class="af-node-content">${escapeHtml(node.content || 'Arquivo disponivel para download.')}</div>
                        ${nodeNotesMarkup(node)}
                        ${node.fileData ? `<a class="af-download-link" href="${escapeHtml(node.fileData)}" download="${escapeHtml(node.fileName || 'anexo')}">Baixar</a>` : ''}
                    </div>`;
            } else {
                element.innerHTML = `
                    <div class="af-node-body">
                        <span class="af-node-kind">${escapeHtml(node.kind || type)}</span>
                        <div class="af-node-title" style="${type === 'text' ? `font-size:${Math.min(96, Math.max(24, Number(node.fontSize) || 56))}px` : ''}">${escapeHtml(node.title || 'Sem titulo')}</div>
                        <div class="af-node-content">${escapeHtml(node.content || '')}</div>
                        ${nodeNotesMarkup(node)}
                    </div>`;
            }

            element.querySelectorAll('.af-node-note-button').forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    openReadonlyNoteModal(notes[Number(button.dataset.noteIndex)] || {});
                });
            });

            canvas.appendChild(element);
        });

        if (!svg) return;

        svg.innerHTML = '';
        svg.setAttribute('width', '100%');
        svg.setAttribute('height', '100%');

        const nodeById = new Map(active.nodes.map((node) => [node.id, node]));

        active.connections.forEach((connection) => {
            const source = nodeById.get(getConnectionEndpoint(connection, 'source'));
            const target = nodeById.get(getConnectionEndpoint(connection, 'target'));

            if (!source || !target) return;

            const start = portPoint(source, connection.fromPort || connection.sourcePort || 'right');
            const end = portPoint(target, connection.toPort || connection.targetPort || 'left');
            const dx = Math.max(80, Math.abs(end.x - start.x) * 0.45);
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');

            path.setAttribute('d', `M ${start.x} ${start.y} C ${start.x + dx} ${start.y}, ${end.x - dx} ${end.y}, ${end.x} ${end.y}`);
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke', '#60a5fa');
            path.setAttribute('stroke-width', '3');
            path.setAttribute('stroke-linecap', 'round');

            if (source.type === 'note' || target.type === 'note') {
                path.setAttribute('stroke-dasharray', '7 6');
            }

            svg.appendChild(path);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            renderModuleSelector();
            render();
            applyModuleViewport(getActiveGraph());
            window.refreshReadonlySidebar?.(window.roadmapNodes || [], window.roadmapConnections || []);
            window.setTimeout(() => window.refreshReadonlyMinimap?.(), 80);
        }, { once: true });
    } else {
        renderModuleSelector();
        render();
        applyModuleViewport(getActiveGraph());
        window.refreshReadonlySidebar?.(window.roadmapNodes || [], window.roadmapConnections || []);
        window.setTimeout(() => window.refreshReadonlyMinimap?.(), 80);
    }
})();
</script>
</body>
</html>
