<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#07071a">
    <title>Akkaunt Sotish — MLBB Market</title>

    {{-- Telegram WebApp SDK --}}
    <script src="https://telegram.org/js/telegram-web-app.js"></script>

    {{-- Tailwind CDN (Play) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    {{-- Axios --}}
    <script src="https://cdn.jsdelivr.net/npm/axios@1.7.2/dist/axios.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bg:      '#07071a',
                        surface: '#0f0f28',
                        card:    '#161635',
                        line:    '#252550',
                        purple:  '#7c3aed',
                        'purple-dim': 'rgba(124,58,237,0.18)',
                        gold:    '#f0b429',
                        'gold-dim': 'rgba(240,180,41,0.15)',
                        ink:     '#e4e4f4',
                        muted:   '#7878a8',
                    }
                }
            }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        *, *::before, *::after { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        html, body {
            margin: 0; padding: 0;
            background: #07071a;
            color: #e4e4f4;
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            overscroll-behavior: none;
        }

        /* ---- Gradients ---- */
        .text-gold {
            background: linear-gradient(135deg, #f0b429 0%, #ffe066 50%, #c49a21 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .text-purple-grad {
            background: linear-gradient(135deg, #a78bfa, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ---- Inputs ---- */
        .field {
            width: 100%;
            background: #161635;
            border: 1.5px solid #252550;
            border-radius: 14px;
            color: #e4e4f4;
            padding: 14px 16px;
            font-size: 16px;          /* prevents iOS zoom */
            font-family: inherit;
            transition: border-color .2s, box-shadow .2s;
            appearance: none;
            -webkit-appearance: none;
        }
        .field:focus { outline: none; border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.2); }
        .field::placeholder { color: #7878a8; }
        .field-error { border-color: #f87171 !important; }

        select.field {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%237878a8'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 20px;
            padding-right: 44px;
            cursor: pointer;
        }
        select.field option { background: #161635; }

        textarea.field { resize: none; line-height: 1.6; }

        /* ---- Buttons ---- */
        .btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 15px 20px;
            border-radius: 14px; border: none;
            font-size: 15px; font-weight: 600; font-family: inherit;
            cursor: pointer; transition: transform .12s, opacity .2s, box-shadow .2s;
            -webkit-user-select: none; user-select: none;
        }
        .btn:active { transform: scale(0.97); }
        .btn:disabled { opacity: .45; cursor: not-allowed; transform: none; }

        .btn-primary { background: linear-gradient(135deg, #7c3aed, #6d28d9); color: #fff; box-shadow: 0 4px 20px rgba(124,58,237,.4); }
        .btn-gold     { background: linear-gradient(135deg, #f0b429, #d4a017); color: #07071a; box-shadow: 0 4px 20px rgba(240,180,41,.4); }
        .btn-ghost    { background: transparent; border: 1.5px solid #252550; color: #7878a8; }

        /* ---- Upload zone ---- */
        .drop-zone {
            border: 2px dashed #252550;
            border-radius: 18px;
            transition: border-color .2s, background .2s;
        }
        .drop-zone.active { border-color: #7c3aed; background: rgba(124,58,237,.07); }

        /* ---- Toggle ---- */
        .toggle {
            width: 52px; height: 28px; border-radius: 14px;
            position: relative; cursor: pointer;
            transition: background .22s;
            flex-shrink: 0;
        }
        .toggle-thumb {
            width: 22px; height: 22px; border-radius: 50%; background: #fff;
            position: absolute; top: 3px; left: 3px;
            transition: transform .22s cubic-bezier(.34,1.56,.64,1);
            box-shadow: 0 1px 4px rgba(0,0,0,.4);
        }
        .toggle[on] .toggle-thumb  { transform: translateX(24px); }

        /* ---- Progress bar ---- */
        .prog-track { height: 3px; background: #252550; border-radius: 3px; overflow: hidden; }
        .prog-fill  { height: 100%; background: linear-gradient(90deg, #7c3aed, #f0b429); border-radius: 3px; transition: width .4s ease; }

        /* ---- Step dot ---- */
        .s-dot { width: 8px; height: 8px; border-radius: 50%; transition: background .3s, transform .3s; }

        /* ---- Cards ---- */
        .card  { background: #161635; border: 1px solid #252550; border-radius: 18px; padding: 16px; }
        .card2 { background: #0f0f28; border: 1px solid #252550; border-radius: 14px; padding: 12px 14px; }

        /* ---- Animations ---- */
        @keyframes fadeUp   { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
        @keyframes fadeDown { from { opacity:1; transform:translateY(0); } to { opacity:0; transform:translateY(-14px); } }
        @keyframes pop      { 0%{transform:scale(0)}55%{transform:scale(1.12)}100%{transform:scale(1)} }
        @keyframes draw     { to { stroke-dashoffset: 0; } }
        @keyframes ring     { to { transform:scale(1.8); opacity:0; } }
        @keyframes spin     { to { transform:rotate(360deg); } }
        @keyframes shimmer  { 0%{transform:translateX(-100%)} 100%{transform:translateX(200%)} }

        .anim-fadeup  { animation: fadeUp .3s ease both; }
        .anim-pop     { animation: pop .45s cubic-bezier(.175,.885,.32,1.275) both; }
        .anim-ring    { animation: ring 1.6s ease-out infinite; }
        .anim-draw    { stroke-dasharray:80; stroke-dashoffset:80; animation: draw .55s ease forwards .35s; }
        .anim-spin    { animation: spin .8s linear infinite; }

        .shimmer { position:relative; overflow:hidden; }
        .shimmer::after {
            content:''; position:absolute; inset:0 -50%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.06), transparent);
            animation: shimmer 2s infinite;
        }

        [x-cloak] { display:none !important; }

        /* Safe area bottom */
        .pb-nav { padding-bottom: calc(16px + env(safe-area-inset-bottom, 0px)); }
        ::-webkit-scrollbar { width: 0; }
    </style>
</head>
<body>
<div x-data="app()" x-init="init()" x-cloak
     class="max-w-lg mx-auto min-h-screen flex flex-col">

    {{-- ═══════════════════════════════════════
         HEADER + PROGRESS
    ═══════════════════════════════════════ --}}
    <header class="flex-shrink-0 px-5 pt-5 pb-3" x-show="step !== 'success'">
        <div class="flex items-center justify-between mb-4">

            {{-- Logo --}}
            <div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-purple-dim flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"
                                  fill="#f0b429" stroke="#f0b429" stroke-width="1" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-gold">MLBB Market</span>
                </div>
                <p class="text-xs text-muted mt-0.5 ml-10">Akkaunt sotish</p>
            </div>

            {{-- Step dots --}}
            <div class="flex items-center gap-1.5">
                <template x-for="i in 3" :key="i">
                    <div class="s-dot"
                         :class="step >= i ? 'bg-gold' : 'bg-line'"
                         :style="step == i ? 'transform:scale(1.35)' : ''">
                    </div>
                </template>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="prog-track">
            <div class="prog-fill" :style="`width:${(step/3)*100}%`"></div>
        </div>

        {{-- Step label --}}
        <div class="flex items-center justify-between mt-2.5">
            <span class="text-xs text-muted" x-text="`${step} / 3 qadam`"></span>
            <span class="text-xs font-medium text-purple-grad" x-text="stepTitle"></span>
        </div>
    </header>

    {{-- ═══════════════════════════════════════
         STEP 1 — MEDIA
    ═══════════════════════════════════════ --}}
    <section x-show="step === 1"
             x-transition:enter="anim-fadeup"
             class="flex-1 overflow-y-auto px-5 py-4">

        <h2 class="text-lg font-bold mb-0.5">Media fayllar</h2>
        <p class="text-sm text-muted mb-5">Skrinshotlar va ixtiyoriy video</p>

        {{-- Image drop zone --}}
        <div class="drop-zone p-5 text-center mb-4 cursor-pointer"
             :class="{'active': dragging}"
             x-show="images.length < 5"
             @dragover.prevent="dragging=true"
             @dragleave.prevent="dragging=false"
             @drop.prevent="onDrop($event)"
             @click="$refs.imgInput.click()">

            <div class="flex flex-col items-center gap-3">
                <div class="w-14 h-14 rounded-2xl bg-purple-dim flex items-center justify-center">
                    <svg class="w-7 h-7" style="color:#7c3aed" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold">Rasmlar yuklash</p>
                    <p class="text-xs text-muted mt-0.5">Bosing yoki sudrab tashlang</p>
                    <p class="text-xs text-muted">Maks <strong class="text-ink">5 ta</strong>, har biri <strong class="text-ink">10 MB</strong></p>
                </div>
                <div class="flex gap-2">
                    <span class="text-xs px-2.5 py-1 rounded-full bg-surface text-muted">JPG</span>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-surface text-muted">PNG</span>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-surface text-muted">WEBP</span>
                </div>
            </div>

            <input type="file" x-ref="imgInput" class="hidden"
                   multiple accept="image/jpeg,image/png,image/webp"
                   @change="onImgSelect($event)">
        </div>

        {{-- Image thumbnails --}}
        <div class="grid grid-cols-3 gap-2.5 mb-1" x-show="images.length > 0">
            <template x-for="(src, i) in previews" :key="i">
                <div class="relative aspect-square rounded-2xl overflow-hidden bg-surface">
                    <img :src="src" class="w-full h-full object-cover">
                    {{-- index badge --}}
                    <span class="absolute bottom-1.5 left-1.5 text-[11px] font-semibold
                                 bg-black/60 text-white px-1.5 py-0.5 rounded-lg"
                          x-text="i+1"></span>
                    {{-- remove --}}
                    <button @click.stop="removeImg(i)"
                            class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full
                                   bg-black/70 flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>

            {{-- Add-more tile --}}
            <div x-show="images.length > 0 && images.length < 5"
                 @click="$refs.imgInput.click()"
                 class="aspect-square rounded-2xl border-2 border-dashed border-line
                        flex items-center justify-center cursor-pointer
                        hover:border-purple transition-colors">
                <svg class="w-6 h-6 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
        </div>

        {{-- Image progress bar --}}
        <div class="flex items-center gap-2 mb-1" x-show="images.length > 0">
            <div class="flex-1 prog-track">
                <div class="prog-fill" :style="`width:${images.length/5*100}%`"></div>
            </div>
            <span class="text-xs text-muted" x-text="`${images.length}/5`"></span>
        </div>

        {{-- Image error --}}
        <p class="text-red-400 text-xs mb-4" x-show="err.images" x-text="err.images"></p>

        {{-- Divider --}}
        <div class="flex items-center gap-3 my-5">
            <div class="flex-1 h-px bg-line"></div>
            <span class="text-xs text-muted">Video (ixtiyoriy)</span>
            <div class="flex-1 h-px bg-line"></div>
        </div>

        {{-- Video upload zone --}}
        <div x-show="!video">
            <div class="drop-zone p-4 cursor-pointer" @click="$refs.vidInput.click()">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gold-dim flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" style="color:#f0b429" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Video yuklash</p>
                        <p class="text-xs text-muted mt-0.5">MP4 format • Maks <strong class="text-ink">50 MB</strong></p>
                    </div>
                </div>
                <input type="file" x-ref="vidInput" class="hidden"
                       accept="video/mp4,video/quicktime"
                       @change="onVidSelect($event)">
            </div>
        </div>

        {{-- Video preview --}}
        <div x-show="video" class="relative rounded-2xl overflow-hidden bg-black">
            <video :src="vidPreview" class="w-full max-h-52 object-contain" controls></video>
            <button @click="removeVideo()"
                    class="absolute top-2 right-2 w-8 h-8 rounded-full bg-black/80
                           flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <span class="absolute bottom-2 left-2 text-xs bg-black/70 text-white px-2 py-0.5 rounded-lg">Video ✓</span>
        </div>
        <p class="text-red-400 text-xs mt-2" x-show="err.video" x-text="err.video"></p>

    </section>

    {{-- ═══════════════════════════════════════
         STEP 2 — DETAILS
    ═══════════════════════════════════════ --}}
    <section x-show="step === 2"
             x-transition:enter="anim-fadeup"
             class="flex-1 overflow-y-auto px-5 py-4">

        <h2 class="text-lg font-bold mb-0.5">Akkaunt ma'lumotlari</h2>
        <p class="text-sm text-muted mb-5">Aniq va to'g'ri kiriting</p>

        {{-- Price --}}
        <div class="mb-4">
            <label class="text-sm font-medium block mb-2">
                Narx <span class="text-red-400">*</span>
            </label>
            <div class="relative">
                <input type="number" x-model="form.price"
                       placeholder="150000" inputmode="numeric" min="0"
                       class="field pr-14"
                       :class="{'field-error': err.price}">
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-gold">so'm</span>
            </div>
            <p class="text-red-400 text-xs mt-1.5" x-show="err.price" x-text="err.price"></p>

            {{-- Price formatted preview --}}
            <div class="mt-2 card2 flex items-center justify-between" x-show="form.price > 0">
                <span class="text-xs text-muted">Ko'rinish</span>
                <span class="text-base font-bold text-gold" x-text="fmtPrice(form.price)"></span>
            </div>
        </div>

        {{-- Collection level --}}
        <div class="mb-4">
            <label class="text-sm font-medium block mb-2">
                Kolleksiya darajasi <span class="text-red-400">*</span>
            </label>
            <select x-model="form.collection_level" class="field"
                    :class="{'field-error': err.collection_level}">
                <option value="" disabled>Tanlang...</option>
                <optgroup label="⭐ Elite">
                    <option>Elite Collector</option>
                </optgroup>
                <optgroup label="🔵 Epic">
                    <option>Epic Collector</option>
                </optgroup>
                <optgroup label="🌟 Legend">
                    <option>Legend Collector</option>
                    <option>Legend Honor Collector</option>
                </optgroup>
                <optgroup label="🔮 Mythic">
                    <option>Mythic Collector</option>
                    <option>Mythic Honor Collector</option>
                    <option>Mythic Glory Collector</option>
                    <option>Mythic Immortal Collector</option>
                </optgroup>
            </select>
            <p class="text-red-400 text-xs mt-1.5" x-show="err.collection_level" x-text="err.collection_level"></p>
        </div>

        {{-- Heroes + Skins --}}
        <div class="grid grid-cols-2 gap-3 mb-5">
            <div>
                <label class="text-sm font-medium block mb-2">
                    Qahramonlar <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <input type="number" x-model="form.heroes_count"
                           placeholder="120" inputmode="numeric" min="0"
                           class="field pl-10"
                           :class="{'field-error': err.heroes_count}">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base leading-none">⚔️</span>
                </div>
                <p class="text-red-400 text-xs mt-1" x-show="err.heroes_count" x-text="err.heroes_count"></p>
            </div>
            <div>
                <label class="text-sm font-medium block mb-2">
                    Skinlar <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <input type="number" x-model="form.skins_count"
                           placeholder="85" inputmode="numeric" min="0"
                           class="field pl-10"
                           :class="{'field-error': err.skins_count}">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base leading-none">👗</span>
                </div>
                <p class="text-red-400 text-xs mt-1" x-show="err.skins_count" x-text="err.skins_count"></p>
            </div>
        </div>

        {{-- Stats summary card --}}
        <div class="card flex items-center justify-around text-center"
             x-show="form.heroes_count || form.skins_count">
            <div>
                <p class="text-2xl font-extrabold text-gold" x-text="form.heroes_count || '—'"></p>
                <p class="text-xs text-muted mt-0.5">Qahramonlar</p>
            </div>
            <div class="w-px h-10 bg-line"></div>
            <div>
                <p class="text-2xl font-extrabold text-purple-grad" x-text="form.skins_count || '—'"></p>
                <p class="text-xs text-muted mt-0.5">Skinlar</p>
            </div>
            <div class="w-px h-10 bg-line"></div>
            <div>
                <p class="text-xs font-semibold text-ink leading-tight"
                   x-text="form.collection_level || '—'"></p>
                <p class="text-xs text-muted mt-0.5">Kolleksiya</p>
            </div>
        </div>

    </section>

    {{-- ═══════════════════════════════════════
         STEP 3 — DESCRIPTION + REVIEW
    ═══════════════════════════════════════ --}}
    <section x-show="step === 3"
             x-transition:enter="anim-fadeup"
             class="flex-1 overflow-y-auto px-5 py-4">

        <h2 class="text-lg font-bold mb-0.5">Qo'shimcha</h2>
        <p class="text-sm text-muted mb-5">Tavsif va transfer holati</p>

        {{-- Description --}}
        <div class="mb-5">
            <label class="text-sm font-medium block mb-2">Tavsif (ixtiyoriy)</label>
            <textarea x-model="form.description"
                      placeholder="Xaridor uchun foydali ma'lumot..."
                      class="field" rows="4" maxlength="2000"></textarea>
            <div class="flex justify-end mt-1">
                <span class="text-xs text-muted" x-text="`${form.description.length}/2000`"></span>
            </div>
        </div>

        {{-- Transfer toggle --}}
        <div class="card mb-5">
            <div class="flex items-center justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold">Transferga tayyor</p>
                    <p class="text-xs text-muted mt-0.5 leading-relaxed">
                        Akkaunt darhol boshqa foydalanuvchiga o'tkazilishi mumkin
                    </p>
                </div>
                <div class="toggle flex-shrink-0"
                     :style="form.ready ? 'background:#7c3aed' : 'background:#252550'"
                     :attr_on="form.ready ? true : undefined"
                     @click="form.ready = !form.ready">
                    <div class="toggle-thumb" :style="form.ready ? 'transform:translateX(24px)' : ''"></div>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2" x-show="form.ready">
                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                <span class="text-xs text-green-400 font-medium">Transfer uchun tayyor ✓</span>
            </div>
        </div>

        {{-- Full summary --}}
        <div class="card">
            <p class="text-xs font-bold text-muted uppercase tracking-widest mb-3">Yuborish ko'rinishi</p>
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted">💰 Narx</span>
                    <span class="text-sm font-bold text-gold"
                          x-text="form.price ? fmtPrice(form.price) : '—'"></span>
                </div>
                <div class="h-px bg-line"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted">🏆 Kolleksiya</span>
                    <span class="text-xs font-medium text-ink"
                          x-text="form.collection_level || '—'"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted">⚔️ Qahramonlar</span>
                    <span class="text-xs font-medium text-ink"
                          x-text="form.heroes_count ? form.heroes_count+' ta' : '—'"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted">👗 Skinlar</span>
                    <span class="text-xs font-medium text-ink"
                          x-text="form.skins_count ? form.skins_count+' ta' : '—'"></span>
                </div>
                <div class="h-px bg-line"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted">📸 Media</span>
                    <span class="text-xs font-medium text-ink" x-text="mediaLabel"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted">🔄 Transfer</span>
                    <span class="text-xs font-semibold"
                          :class="form.ready ? 'text-green-400' : 'text-muted'"
                          x-text="form.ready ? 'Tayyor ✓' : 'Tayyor emas'"></span>
                </div>
            </div>
        </div>

    </section>

    {{-- ═══════════════════════════════════════
         SUCCESS
    ═══════════════════════════════════════ --}}
    <section x-show="step === 'success'"
             class="flex-1 flex flex-col items-center justify-center px-5 py-10 anim-fadeup">

        {{-- Rings + icon --}}
        <div class="relative mb-8 flex items-center justify-center">
            <div class="absolute w-28 h-28 rounded-full bg-purple-dim anim-ring"></div>
            <div class="absolute w-28 h-28 rounded-full bg-purple-dim anim-ring" style="animation-delay:.6s"></div>

            <div class="anim-pop w-28 h-28 rounded-full flex items-center justify-center"
                 style="background:linear-gradient(135deg,#7c3aed,#5b21b6);
                        box-shadow:0 0 60px rgba(124,58,237,.5)">
                <svg class="w-14 h-14" fill="none" viewBox="0 0 24 24">
                    <path class="anim-draw"
                          stroke="white" stroke-width="2.5"
                          stroke-linecap="round" stroke-linejoin="round"
                          d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>

        <h2 class="text-2xl font-extrabold mb-2">Muvaffaqiyatli! 🎉</h2>
        <p class="text-muted text-center text-sm leading-relaxed mb-8">
            Akkauntingiz adminga yuborildi.<br>
            Tez orada ko'rib chiqishadi.
        </p>

        {{-- Closing countdown --}}
        <div class="flex items-center gap-2.5 text-sm text-muted">
            <svg class="w-4 h-4 anim-spin" style="color:#7c3aed" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span x-text="`${countdown} soniyada yopiladi`"></span>
        </div>

    </section>

    {{-- ═══════════════════════════════════════
         NAV BUTTONS
    ═══════════════════════════════════════ --}}
    <footer class="flex-shrink-0 px-5 pt-3 pb-nav" x-show="step !== 'success'">

        {{-- Global error --}}
        <div x-show="globalErr"
             class="mb-3 p-3 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm"
             x-text="globalErr"></div>

        {{-- Step 1 --}}
        <div x-show="step === 1">
            <button @click="next()" class="btn btn-primary">
                Davom etish
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        {{-- Step 2 --}}
        <div x-show="step === 2" class="flex gap-3">
            <button @click="back()" class="btn btn-ghost" style="width:30%">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Orqaga
            </button>
            <button @click="next()" class="btn btn-primary" style="flex:1">
                Davom etish
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        {{-- Step 3 — submit --}}
        <div x-show="step === 3" class="flex gap-3">
            <button @click="back()" class="btn btn-ghost" style="width:30%" :disabled="loading">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Orqaga
            </button>
            <button @click="submit()" class="btn btn-gold" style="flex:1" :disabled="loading">
                <template x-if="!loading">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Yuborish
                    </span>
                </template>
                <template x-if="loading">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 anim-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Yuklanmoqda...
                    </span>
                </template>
            </button>
        </div>
    </footer>

    {{-- ═══════════════════════════════════════
         LOADING OVERLAY
    ═══════════════════════════════════════ --}}
    <div x-show="loading"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 flex flex-col items-center justify-center"
         style="background:rgba(7,7,26,.85);backdrop-filter:blur(8px)">

        {{-- Spinner card --}}
        <div class="shimmer w-20 h-20 rounded-3xl card flex items-center justify-center mb-4">
            <svg class="w-9 h-9 anim-spin" style="color:#7c3aed" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        </div>

        <p class="text-sm font-medium text-ink mb-1">Yuborilmoqda...</p>
        <p class="text-xs text-muted mb-4">Iltimos kuting</p>

        {{-- Upload progress bar --}}
        <div class="w-48 prog-track">
            <div class="prog-fill" :style="`width:${uploadPct}%`"></div>
        </div>
        <p class="text-xs text-muted mt-2" x-text="`${uploadPct}%`"></p>
    </div>

</div>

{{-- ═══════════════════════════════════════
     ALPINE APP
═══════════════════════════════════════ --}}
<script>
function app() {
    return {
        /* ---- state ---- */
        step: 1,
        loading: false,
        uploadPct: 0,
        countdown: 3,
        globalErr: '',

        /* ---- telegram ---- */
        tgId: null,

        /* ---- form ---- */
        form: {
            price: '',
            heroes_count: '',
            skins_count: '',
            collection_level: '',
            description: '',
            ready: false,
        },

        /* ---- media ---- */
        images: [],
        previews: [],
        video: null,
        vidPreview: null,
        dragging: false,

        /* ---- validation ---- */
        err: {},

        /* ---- computed ---- */
        get stepTitle() {
            return ['', '— Media', '— Ma\'lumotlar', '— Tavsif'][this.step] ?? '';
        },
        get mediaLabel() {
            const p = [];
            if (this.images.length) p.push(`${this.images.length} ta rasm`);
            if (this.video)         p.push('1 ta video');
            return p.length ? p.join(', ') : 'Yo\'q';
        },

        /* ---- init ---- */
        init() {
            const tg = window.Telegram?.WebApp;
            if (tg) {
                tg.ready();
                tg.expand();
                try { tg.setHeaderColor('#07071a'); tg.setBackgroundColor('#07071a'); } catch (_) {}
                this.tgId = tg.initDataUnsafe?.user?.id ?? null;
            }
            /* dev fallback — remove in production */
            if (!this.tgId) this.tgId = {{ env('APP_DEBUG') ? env('TELEGRAM_TEST_ID', 'null') : 'null' }};
        },

        /* -------- IMAGES -------- */
        onImgSelect(e) {
            this.addImgs([...e.target.files]);
            e.target.value = '';
        },
        onDrop(e) {
            this.dragging = false;
            const files = [...e.dataTransfer.files].filter(f => f.type.startsWith('image/'));
            this.addImgs(files);
        },
        addImgs(files) {
            delete this.err.images;
            for (const f of files) {
                if (this.images.length >= 5)       { this.err.images = 'Maksimum 5 ta rasm yuklash mumkin'; break; }
                if (f.size > 10 * 1024 * 1024)    { this.err.images = `"${f.name}" hajmi 10 MB dan katta`;  continue; }
                if (!['image/jpeg','image/png','image/webp'].includes(f.type)) {
                    this.err.images = 'Faqat JPG, PNG yoki WEBP formatlar qabul qilinadi'; continue;
                }
                const idx = this.images.length;
                this.images.push(f);
                this.previews.push('');
                const fr = new FileReader();
                fr.onload = e => { this.previews[idx] = e.target.result; this.previews = [...this.previews]; };
                fr.readAsDataURL(f);
            }
        },
        removeImg(i) {
            this.images.splice(i, 1);
            this.previews.splice(i, 1);
            this.previews = [...this.previews];
            delete this.err.images;
        },

        /* -------- VIDEO -------- */
        onVidSelect(e) {
            const f = e.target.files[0];
            if (f) this.addVideo(f);
            e.target.value = '';
        },
        addVideo(f) {
            delete this.err.video;
            if (f.size > 50 * 1024 * 1024)                          { this.err.video = 'Video hajmi 50 MB dan katta'; return; }
            if (!['video/mp4','video/quicktime'].includes(f.type))   { this.err.video = 'Faqat MP4 format qabul qilinadi'; return; }
            this.video = f;
            const fr = new FileReader();
            fr.onload = e => this.vidPreview = e.target.result;
            fr.readAsDataURL(f);
        },
        removeVideo() {
            this.video = null;
            this.vidPreview = null;
            delete this.err.video;
        },

        /* -------- VALIDATION -------- */
        validate() {
            this.err = {};
            this.globalErr = '';

            if (this.step === 1) {
                return !this.err.images && !this.err.video;
            }

            if (this.step === 2) {
                let ok = true;
                if (!this.form.price || +this.form.price <= 0)       { this.err.price            = 'Narxni kiriting';                   ok = false; }
                if (!this.form.collection_level)                       { this.err.collection_level  = 'Kolleksiya darajasini tanlang';    ok = false; }
                if (!this.form.heroes_count || +this.form.heroes_count < 0) { this.err.heroes_count = 'Qahramonlar sonini kiriting'; ok = false; }
                if (!this.form.skins_count  || +this.form.skins_count  < 0) { this.err.skins_count  = 'Skinlar sonini kiriting';     ok = false; }
                return ok;
            }

            return true;
        },

        /* -------- NAV -------- */
        next() {
            if (!this.validate()) return;
            this.step++;
            window.scrollTo(0, 0);
        },
        back() {
            this.step--;
            this.err = {};
            this.globalErr = '';
            window.scrollTo(0, 0);
        },

        /* -------- SUBMIT -------- */
        async submit() {
            if (!this.validate()) return;
            if (!this.tgId) { this.globalErr = 'Telegram foydalanuvchi aniqlanmadi.'; return; }

            this.loading   = true;
            this.uploadPct = 0;

            const fd = new FormData();
            fd.append('telegram_id',        this.tgId);
            fd.append('price',              this.form.price);
            fd.append('heroes_count',       this.form.heroes_count);
            fd.append('skins_count',        this.form.skins_count);
            fd.append('collection_level',   this.form.collection_level);
            fd.append('description',        this.form.description);
            fd.append('ready_for_transfer', this.form.ready ? '1' : '0');
            this.images.forEach((img, i) => fd.append(`images[${i}]`, img));
            if (this.video) fd.append('video', this.video);

            try {
                await axios.post('/api/accounts', fd, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                    onUploadProgress: ev => {
                        this.uploadPct = ev.total ? Math.round(ev.loaded / ev.total * 100) : 50;
                    },
                });

                this.uploadPct = 100;
                this.step = 'success';
                this.startCountdown();

            } catch (e) {
                const data = e.response?.data;
                if (data?.errors) {
                    const errs = data.errors;
                    /* redirect to step with errors */
                    const step2Keys = ['price', 'heroes_count', 'skins_count', 'collection_level'];
                    if (step2Keys.some(k => errs[k])) this.step = 2;
                    Object.keys(errs).forEach(k => { this.err[k] = errs[k][0]; });
                } else {
                    this.globalErr = data?.message ?? 'Xatolik yuz berdi. Qayta urinib ko\'ring.';
                }
            } finally {
                this.loading = false;
            }
        },

        /* -------- COUNTDOWN -------- */
        startCountdown() {
            const t = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) { clearInterval(t); window.Telegram?.WebApp?.close(); }
            }, 1000);
        },

        /* -------- HELPERS -------- */
        fmtPrice(v) {
            return new Intl.NumberFormat('uz-UZ').format(v) + ' so\'m';
        },
    }
}
</script>
</body>
</html>
