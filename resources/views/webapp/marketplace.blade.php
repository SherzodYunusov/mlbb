<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#07071a">
    <title>MLBB Market</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@1.7.2/dist/axios.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bg: '#050514', surface: 'rgba(255,255,255,0.04)', surface2: 'rgba(255,255,255,0.06)', surface3: 'rgba(255,255,255,0.09)',
                        card: 'rgba(255,255,255,0.04)', line: 'rgba(255,255,255,0.08)',
                        gold: '#f0b429', ink: '#e4e4f4', muted: '#7878a8',
                    }
                }
            }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        *,*::before,*::after{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
        html,body{margin:0;padding:0;color:#e4e4f4;font-family:'Inter',system-ui,sans-serif;min-height:100vh;overscroll-behavior:none}

        /* ── BACKGROUND — deep gaming gradient ── */
        body{background:#050514;background-image:radial-gradient(ellipse 80% 60% at 50% -10%,rgba(124,58,237,.18) 0%,transparent 70%),radial-gradient(ellipse 50% 40% at 90% 80%,rgba(99,102,241,.1) 0%,transparent 60%)}

        /* ── Gradients ── */
        .g-gold{background:linear-gradient(135deg,#f0b429,#ffe066,#c49a21);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .g-purple{background:linear-gradient(135deg,#c4b5fd,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

        /* ── Inputs ── */
        .field{width:100%;background:rgba(255,255,255,.05);border:1.5px solid rgba(255,255,255,.1);border-radius:14px;color:#e4e4f4;padding:14px 16px;font-size:16px;font-family:inherit;transition:border-color .2s,box-shadow .2s;appearance:none;-webkit-appearance:none;backdrop-filter:blur(8px)}
        .field:focus{outline:none;border-color:#7c3aed;box-shadow:0 0 0 3px rgba(124,58,237,.25),0 0 20px rgba(124,58,237,.15)}
        .field::placeholder{color:rgba(120,120,168,.7)}
        .field-sm{padding:10px 14px;font-size:14px;border-radius:12px}
        select.field{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%237878a8'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;background-size:20px;padding-right:44px;cursor:pointer}
        select.field option{background:#0f0f28}
        textarea.field{resize:none;line-height:1.6}

        /* ── Buttons ── */
        .btn{display:flex;align-items:center;justify-content:center;gap:8px;border-radius:14px;border:none;font-family:inherit;cursor:pointer;transition:transform .15s,box-shadow .15s,opacity .2s;-webkit-user-select:none;user-select:none}
        .btn:active{transform:scale(.96)}
        .btn:disabled{opacity:.45;cursor:not-allowed;transform:none}
        .btn-primary{background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;box-shadow:0 4px 24px rgba(124,58,237,.5);padding:15px 20px;width:100%;font-size:15px;font-weight:600}
        .btn-primary:hover:not(:disabled){box-shadow:0 6px 32px rgba(124,58,237,.65);transform:translateY(-1px)}
        .btn-gold{background:linear-gradient(135deg,#f0b429,#d4a017);color:#07071a;box-shadow:0 4px 24px rgba(240,180,41,.45);padding:15px 20px;width:100%;font-size:15px;font-weight:700}
        .btn-gold:hover:not(:disabled){box-shadow:0 6px 32px rgba(240,180,41,.6);transform:translateY(-1px)}
        .btn-ghost{background:rgba(255,255,255,.05);border:1.5px solid rgba(255,255,255,.1);color:#7878a8;padding:15px 20px;width:100%;font-size:15px;font-weight:500;backdrop-filter:blur(8px)}
        .btn-view{background:linear-gradient(135deg,rgba(124,58,237,.2),rgba(109,40,217,.15));color:#c4b5fd;border:1px solid rgba(124,58,237,.3);padding:9px 12px;font-size:12px;font-weight:600;border-radius:10px;width:100%;transition:all .2s}
        .btn-view:hover{background:linear-gradient(135deg,rgba(124,58,237,.35),rgba(109,40,217,.25));transform:translateY(-1px);box-shadow:0 4px 16px rgba(124,58,237,.3)}

        /* ── Glass Cards ── */
        .card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:16px;backdrop-filter:blur(12px)}
        .card2{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:14px;padding:12px 14px;backdrop-filter:blur(8px)}

        /* ── Premium Account Card ── */
        .acc-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:20px;overflow:hidden;display:flex;flex-direction:column;transition:transform .2s cubic-bezier(.34,1.56,.64,1),box-shadow .2s;cursor:pointer;backdrop-filter:blur(12px)}
        .acc-card:hover{transform:translateY(-3px);box-shadow:0 12px 40px rgba(124,58,237,.2),0 0 0 1px rgba(124,58,237,.15)}
        .acc-card:active{transform:translateY(-1px)}

        /* ── Thumbnail image ── */
        .thumb-img{width:100%;height:100%;object-fit:cover;display:block;opacity:0;transition:opacity .4s ease}
        .thumb-img.loaded{opacity:1}

        /* ── Card image gradient overlay ── */
        .card-img-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(5,5,20,.95) 0%,rgba(5,5,20,.5) 40%,transparent 70%)}

        /* ── Detail carousel (Swiper) ── */
        .detail-swiper{--swiper-pagination-color:rgba(255,255,255,.92);--swiper-pagination-bullet-inactive-color:rgba(255,255,255,.35);--swiper-pagination-bullet-inactive-opacity:1;--swiper-pagination-bullet-size:7px;--swiper-pagination-bullet-horizontal-gap:4px}
        .detail-swiper .swiper-pagination{bottom:10px}
        .detail-img{width:100%;height:100%;object-fit:contain;display:block;opacity:0;transition:opacity .3s ease}
        .detail-img.loaded{opacity:1}

        /* ── Rank badge ── */
        .rank-badge{font-size:9px;font-weight:800;padding:3px 8px;border-radius:20px;text-transform:uppercase;letter-spacing:.8px;backdrop-filter:blur(8px)}

        /* ── Stat pill ── */
        .stat-pill{display:inline-flex;align-items:center;gap:3px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:3px 8px;font-size:10px;font-weight:700;color:#fff;backdrop-filter:blur(4px)}

        /* ── Toggle ── */
        .toggle{width:52px;height:28px;border-radius:14px;position:relative;cursor:pointer;transition:background .22s;flex-shrink:0}
        .toggle-thumb{width:22px;height:22px;border-radius:50%;background:#fff;position:absolute;top:3px;left:3px;transition:transform .22s cubic-bezier(.34,1.56,.64,1);box-shadow:0 1px 4px rgba(0,0,0,.4)}

        /* ── Progress ── */
        .prog-track{height:3px;background:rgba(255,255,255,.1);border-radius:3px;overflow:hidden}
        .prog-fill{height:100%;background:linear-gradient(90deg,#7c3aed,#f0b429);border-radius:3px;transition:width .4s ease}

        /* ── Upload ── */
        .drop-zone{border:2px dashed rgba(255,255,255,.12);border-radius:18px;transition:border-color .2s,background .2s}
        .drop-zone.dz-active{border-color:#7c3aed;background:rgba(124,58,237,.07)}

        /* ── Sell step dot ── */
        .s-dot{width:8px;height:8px;border-radius:50%;transition:background .3s,transform .3s}

        /* ── BOTTOM NAV — glassmorphism ── */
        .bnav{position:fixed;bottom:0;left:0;right:0;max-width:512px;margin:0 auto;background:rgba(5,5,20,.75);border-top:1px solid rgba(255,255,255,.08);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);display:flex;z-index:50;padding-bottom:env(safe-area-inset-bottom,0px)}
        .bnav-btn{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;padding:10px 4px;border:none;background:transparent;color:rgba(120,120,168,.7);font-family:inherit;cursor:pointer;transition:color .2s;position:relative}
        .bnav-btn.on{color:#c4b5fd}
        .bnav-btn.on svg{filter:drop-shadow(0 0 6px rgba(167,139,250,.6))}
        .bnav-btn span{font-size:10px;font-weight:600}
        .bnav-dot{width:4px;height:4px;border-radius:50%;background:#a78bfa;margin:0 auto;box-shadow:0 0 6px rgba(167,139,250,.8);animation:dotPop .2s cubic-bezier(.34,1.56,.64,1)}
        @keyframes dotPop{from{transform:scale(0)}to{transform:scale(1)}}

        /* ── Search bar special ── */
        .search-field{background:rgba(255,255,255,.06);border:1.5px solid rgba(255,255,255,.1);border-radius:16px;color:#e4e4f4;padding:12px 16px 12px 42px;font-size:15px;font-family:inherit;width:100%;transition:all .25s;backdrop-filter:blur(12px)}
        .search-field:focus{outline:none;border-color:rgba(124,58,237,.8);box-shadow:0 0 0 3px rgba(124,58,237,.2),0 0 24px rgba(124,58,237,.12);background:rgba(255,255,255,.08)}
        .search-field::placeholder{color:rgba(120,120,168,.6)}

        /* ── Filter chip ── */
        .chip{flex-shrink:0;padding:6px 14px;border-radius:20px;font-size:11px;font-weight:700;border:1.5px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:rgba(120,120,168,.9);transition:all .2s;white-space:nowrap;cursor:pointer}
        .chip.on{background:rgba(124,58,237,.25);border-color:rgba(124,58,237,.6);color:#c4b5fd;box-shadow:0 0 12px rgba(124,58,237,.25)}

        /* ── Skeleton ── */
        @keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
        .skeleton{background:linear-gradient(90deg,rgba(255,255,255,.04) 25%,rgba(255,255,255,.08) 50%,rgba(255,255,255,.04) 75%);background-size:200% 100%;animation:shimmer 1.5s ease-in-out infinite;border-radius:8px}

        /* ── Video overlay ── */
        .video-overlay{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;background:rgba(5,5,20,.55);cursor:pointer;transition:background .2s}
        .video-overlay:hover{background:rgba(5,5,20,.4)}
        .play-btn{width:56px;height:56px;border-radius:50%;background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(8px);box-shadow:0 0 24px rgba(124,58,237,.3)}

        /* ── Animations ── */
        @keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
        @keyframes pop{0%{transform:scale(0)}55%{transform:scale(1.12)}100%{transform:scale(1)}}
        @keyframes draw{to{stroke-dashoffset:0}}
        @keyframes ring{to{transform:scale(1.8);opacity:0}}
        @keyframes spin{to{transform:rotate(360deg)}}
        @keyframes glow{0%,100%{opacity:.6}50%{opacity:1}}
        .anim-fadeup{animation:fadeUp .35s cubic-bezier(.22,1,.36,1) both}
        .anim-pop{animation:pop .45s cubic-bezier(.175,.885,.32,1.275) both}
        .anim-draw{stroke-dasharray:80;stroke-dashoffset:80;animation:draw .55s ease forwards .35s}
        .anim-spin{animation:spin .8s linear infinite}
        .anim-ring{animation:ring 1.6s ease-out infinite}

        /* ── Detail modal ── */
        .detail-modal{position:fixed;inset:0;z-index:60;background:#050514;display:flex;flex-direction:column;overflow:hidden}

        /* ── Status badge ── */
        .status-badge{font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px}

        /* ── Action sheet ── */
        .action-sheet-overlay{position:fixed;inset:0;z-index:80;background:rgba(5,5,20,.8);backdrop-filter:blur(8px)}
        .action-sheet{position:fixed;bottom:0;left:0;right:0;max-width:512px;margin:0 auto;z-index:81;background:rgba(15,15,35,.95);border-radius:24px 24px 0 0;border-top:1px solid rgba(255,255,255,.1);padding:20px 20px calc(20px + env(safe-area-inset-bottom,0px));backdrop-filter:blur(20px)}

        /* ── Account action buttons ── */
        .acc-action-btn{display:flex;align-items:center;justify-content:center;gap:6px;border-radius:10px;border:1.5px solid;font-size:12px;font-weight:600;padding:7px 10px;cursor:pointer;transition:transform .1s,opacity .15s;font-family:inherit}
        .acc-action-btn:active{transform:scale(.95)}

        /* ── "Yangi" badge pulse ── */
        @keyframes newPulse{0%,100%{box-shadow:0 0 0 0 rgba(16,185,129,.4)}50%{box-shadow:0 0 0 4px rgba(16,185,129,0)}}
        .badge-new{animation:newPulse 2s ease-in-out infinite}

        [x-cloak]{display:none!important}
        ::-webkit-scrollbar{width:0;height:0}
        .pb-nav{padding-bottom:calc(68px + env(safe-area-inset-bottom,0px))}
        .hide-scrollbar{scrollbar-width:none;-ms-overflow-style:none}
        .hide-scrollbar::-webkit-scrollbar{display:none}
    </style>
</head>
<body>
<div x-data="app()" x-init="init()" x-cloak class="max-w-lg mx-auto">

    {{-- Auth xato (token eskirgan) --}}
    <div x-show="authError"
         class="fixed top-0 left-0 right-0 z-[100] max-w-lg mx-auto px-4 pt-4">
        <div class="p-4 rounded-2xl bg-red-500/15 border border-red-500/30 text-red-400 text-sm text-center">
            🔐 <span x-text="authError"></span><br>
            <span class="text-xs text-muted mt-1 block">Botga qaytib /start yuboring</span>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         TAB: MARKET
    ══════════════════════════════════════ --}}
    <div x-show="tab === 'market'" class="min-h-screen flex flex-col pb-nav">

        <header class="px-4 pt-5 pb-3 flex-shrink-0">
            {{-- Logo + Total --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-2xl flex items-center justify-center"
                         style="background:linear-gradient(135deg,rgba(124,58,237,.4),rgba(109,40,217,.2));border:1px solid rgba(124,58,237,.4);box-shadow:0 0 16px rgba(124,58,237,.2)">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"
                                  fill="#f0b429" stroke="#f0b429" stroke-width=".5" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-base font-black g-gold leading-tight tracking-tight">MLBB Market</p>
                        <p class="text-[10px] text-muted leading-none mt-0.5">Premium akkauntlar bozori</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-muted uppercase tracking-widest">Jami</p>
                    <p class="text-sm font-bold g-purple" x-text="total + ' ta'"></p>
                </div>
            </div>

            {{-- Search --}}
            <div class="relative mb-3">
                <input type="text" x-model="search" placeholder="Qidirish..." class="search-field">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4.5 h-4.5 pointer-events-none"
                     style="color:rgba(120,120,168,.7);width:18px;height:18px"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <button x-show="search" @click="search=''"
                        class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full flex items-center justify-center"
                        style="background:rgba(255,255,255,.1);color:rgba(120,120,168,.8)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Rank chips + Sort --}}
            <div class="flex items-center gap-2">
                <div class="flex gap-1.5 overflow-x-auto flex-1 hide-scrollbar pb-0.5">
                    <template x-for="r in rankFilters" :key="r.value">
                        <button @click="selectedRank = r.value"
                                class="chip"
                                :class="selectedRank === r.value ? 'on' : ''"
                                :style="selectedRank === r.value ? `border-color:${r.color}60;background:${r.color}22;color:#fff;box-shadow:0 0 12px ${r.color}30` : ''"
                                x-text="r.label">
                        </button>
                    </template>
                </div>
                {{-- Sort --}}
                <div class="flex-shrink-0">
                    <select x-model="sort" class="field field-sm"
                            style="padding:8px 32px 8px 12px;font-size:12px;border-radius:12px;background-image:url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%237878a8'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E\");background-repeat:no-repeat;background-position:right 8px center;background-size:14px">
                        <option value="newest">🕐 Yangi</option>
                        <option value="oldest">🕐 Eski</option>
                        <option value="price_asc">💰 Arzon</option>
                        <option value="price_desc">💰 Qimmat</option>
                        <option value="views_desc">👁 Ko'p ko'rilgan</option>
                    </select>
                </div>
            </div>

            {{-- Price filter --}}
            <div class="mt-2">
                <button @click="showPriceFilter=!showPriceFilter"
                        class="flex items-center gap-1.5 text-xs font-medium transition-colors"
                        :class="(priceMin||priceMax) ? 'text-violet-400' : 'text-muted'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                    </svg>
                    <span x-text="showPriceFilter ? 'Narx filtri ▲' : 'Narx filtri ▼'"></span>
                    <span x-show="priceMin||priceMax" class="ml-1 px-1.5 py-0.5 bg-violet-500/20 text-violet-400 rounded-full text-[10px] font-bold">faol</span>
                </button>

                <div x-show="showPriceFilter"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="flex items-center gap-2 mt-2">
                    <div class="relative flex-1">
                        <input type="number" x-model="priceMin" placeholder="Min narx"
                               inputmode="numeric" class="field field-sm w-full pr-8">
                        <button x-show="priceMin" @click="priceMin=''"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-muted hover:text-ink text-base leading-none">×</button>
                    </div>
                    <span class="text-muted text-sm">—</span>
                    <div class="relative flex-1">
                        <input type="number" x-model="priceMax" placeholder="Max narx"
                               inputmode="numeric" class="field field-sm w-full pr-8">
                        <button x-show="priceMax" @click="priceMax=''"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-muted hover:text-ink text-base leading-none">×</button>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-2">

            {{-- Skeleton --}}
            <div x-show="loading" class="grid grid-cols-2 gap-3">
                <template x-for="i in 12" :key="i">
                    <div class="acc-card">
                        <div class="skeleton w-full rounded-none" style="height:164px"></div>
                        <div class="p-3 space-y-2">
                            <div class="skeleton h-2.5 rounded-full w-1/2"></div>
                            <div class="skeleton h-4 rounded-full w-3/4"></div>
                            <div class="skeleton h-8 rounded-xl w-full mt-1"></div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Empty --}}
            <div x-show="!loading && filteredAccounts.length === 0"
                 class="flex flex-col items-center justify-center py-20 text-center">
                <div class="text-5xl mb-4" style="filter:drop-shadow(0 0 20px rgba(124,58,237,.4))">🎮</div>
                <p class="text-sm font-bold text-ink mb-1">Akkaunt topilmadi</p>
                <p class="text-xs text-muted">Filtrni o'zgartiring yoki qidiruvni tozalang</p>
            </div>

            {{-- Cards --}}
            <div x-show="!loading && filteredAccounts.length > 0" class="grid grid-cols-2 gap-3 anim-fadeup">
                <template x-for="acc in filteredAccounts" :key="acc.id">
                    <div x-data="{imgLoaded:false}" class="acc-card" @click="openDetail(acc)">

                        {{-- Image zone --}}
                        <div class="relative flex-shrink-0" style="height:164px;background:#080818">

                            {{-- Skeleton --}}
                            <div class="skeleton absolute inset-0 rounded-none" x-show="!imgLoaded && acc.thumbnail"></div>

                            {{-- No image --}}
                            <div x-show="!acc.thumbnail"
                                 class="absolute inset-0 flex items-center justify-center"
                                 style="background:linear-gradient(135deg,rgba(124,58,237,.1),rgba(5,5,20,.8))">
                                <svg class="w-10 h-10" style="color:rgba(124,58,237,.35)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM3 10.5a9 9 0 1018 0 9 9 0 00-18 0z"/>
                                </svg>
                            </div>

                            {{-- Image --}}
                            <img x-show="acc.thumbnail" :src="acc.thumbnail"
                                 @load="imgLoaded=true" :class="imgLoaded?'loaded':''"
                                 class="thumb-img absolute inset-0"
                                 loading="lazy" decoding="async" draggable="false">

                            {{-- Gradient overlay --}}
                            <div class="card-img-overlay"></div>

                            {{-- Rank badge — top left --}}
                            <div class="absolute top-2 left-2 z-10">
                                <template x-if="levelInfo(acc.collection_level)?.img">
                                    <img :src="levelInfo(acc.collection_level).img"
                                         class="w-8 h-8 object-contain drop-shadow-lg"
                                         style="filter:drop-shadow(0 2px 6px rgba(0,0,0,.6))"
                                         loading="lazy" draggable="false">
                                </template>
                                <template x-if="!levelInfo(acc.collection_level)?.img">
                                    <span class="rank-badge text-white"
                                          :style="`background:${rankColor(acc.collection_level)}cc`"
                                          x-text="rankShort(acc.collection_level)"></span>
                                </template>
                            </div>

                            {{-- Top-right badges --}}
                            <div class="absolute top-2.5 right-2.5 z-10 flex flex-col items-end gap-1">
                                <template x-if="isNew(acc.created_at)">
                                    <span class="badge-new text-[9px] font-black px-2 py-0.5 rounded-full text-white"
                                          style="background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 2px 10px rgba(16,185,129,.5)">NEW</span>
                                </template>
                                <template x-if="acc.ready_for_transfer">
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full text-white/90"
                                          style="background:rgba(255,255,255,.13);border:1px solid rgba(255,255,255,.2);backdrop-filter:blur(6px)">⚡</span>
                                </template>
                            </div>

                            {{-- Stats pills over gradient --}}
                            <div class="absolute bottom-2.5 left-2.5 right-2.5 z-10 flex items-center justify-between">
                                <div class="flex gap-1">
                                    <span class="stat-pill">⚔️ <span x-text="acc.heroes_count"></span></span>
                                    <span class="stat-pill">👗 <span x-text="acc.skins_count"></span></span>
                                </div>
                                <div x-show="acc.views > 0" class="flex items-center gap-0.5 text-[10px] text-white/50">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span x-text="acc.views"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="p-3 flex flex-col gap-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span x-text="levelInfo(acc.collection_level)?.emoji ?? '🎮'" class="text-base leading-none flex-shrink-0"></span>
                                <p class="text-[11px] font-semibold truncate" style="color:rgba(196,181,253,.75)"
                                   x-text="levelInfo(acc.collection_level)?.short ?? acc.collection_level"></p>
                            </div>
                            <p class="text-[15px] font-black g-gold leading-none" x-text="fmtPrice(acc.price)"></p>
                            <div class="flex gap-1.5">
                                <button class="btn btn-view" style="padding:8px 10px;font-size:12px;flex:1">Ko'rish →</button>
                                <template x-if="acc.seller_telegram_id != tgId">
                                    <button @click.stop="openBuyModal(acc)"
                                            class="btn btn-gold"
                                            style="padding:8px 10px;font-size:12px;flex:1">
                                        🛒 Olish
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Load More --}}
            <div x-show="!loading && hasMore && filteredAccounts.length > 0" class="flex justify-center py-5">
                <button @click="loadMore()"
                        class="btn btn-ghost"
                        style="width:180px;font-size:13px"
                        :disabled="loadingMore">
                    <template x-if="!loadingMore">
                        <span>Yana ko'rsatish ↓</span>
                    </template>
                    <template x-if="loadingMore">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 anim-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>Yuklanmoqda...
                        </span>
                    </template>
                </button>
            </div>

        </main>
    </div>

    {{-- ══════════════════════════════════════
         TAB: SELL
    ══════════════════════════════════════ --}}
    <div x-show="tab === 'sell'" class="min-h-screen flex flex-col pb-nav">

        <header class="flex-shrink-0 px-5 pt-5 pb-3" x-show="sellStep !== 'success'">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-lg font-bold g-gold">E'lon joylash</h1>
                    <p class="text-xs text-muted mt-0.5">MLBB akkaunt sotish</p>
                </div>
                <div class="flex items-center gap-1.5">
                    <template x-for="i in 3" :key="i">
                        <div class="s-dot" :class="sellStep>=i?'bg-gold':'bg-line'"
                             :style="sellStep==i?'transform:scale(1.35)':''"></div>
                    </template>
                </div>
            </div>
            <div class="prog-track">
                <div class="prog-fill" :style="`width:${(sellStep/3)*100}%`"></div>
            </div>
            <div class="flex justify-between mt-2">
                <span class="text-xs text-muted" x-text="`${sellStep}/3 qadam`"></span>
                <span class="text-xs g-purple font-medium" x-text="sellStepTitle"></span>
            </div>
        </header>

        {{-- Step 1: Media --}}
        <section x-show="sellStep===1" class="flex-1 overflow-y-auto px-5 py-3 anim-fadeup">
            <h2 class="text-lg font-bold mb-0.5">Media fayllar</h2>
            <p class="text-sm text-muted mb-4">Skrinshotlar va ixtiyoriy video</p>

            <div class="drop-zone p-5 text-center mb-4 cursor-pointer"
                 :class="{'dz-active':dragging}" x-show="images.length < 5"
                 @dragover.prevent="dragging=true" @dragleave.prevent="dragging=false"
                 @drop.prevent="onDrop($event)" @click="$refs.imgIn.click()">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-14 h-14 rounded-2xl bg-[rgba(124,58,237,0.18)] flex items-center justify-center">
                        <svg class="w-7 h-7" style="color:#7c3aed" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Rasmlar yuklash</p>
                        <p class="text-xs text-muted mt-0.5">Maks <b class="text-ink">5 ta</b> · <b class="text-ink">10 MB</b></p>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-xs px-2.5 py-1 rounded-full bg-surface text-muted">JPG</span>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-surface text-muted">PNG</span>
                        <span class="text-xs px-2.5 py-1 rounded-full bg-surface text-muted">WEBP</span>
                    </div>
                </div>
                <input type="file" x-ref="imgIn" class="hidden" multiple accept="image/jpeg,image/png,image/webp" @change="onImgSelect($event)">
            </div>

            <div class="grid grid-cols-3 gap-2.5 mb-2" x-show="images.length>0">
                <template x-for="(src,i) in previews" :key="i">
                    <div class="relative aspect-square rounded-2xl overflow-hidden bg-surface">
                        <img :src="src" class="w-full h-full object-cover">
                        <span class="absolute bottom-1.5 left-1.5 text-[11px] font-semibold bg-black/60 text-white px-1.5 py-0.5 rounded-lg" x-text="i+1"></span>
                        <button @click.stop="removeImg(i)" class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-black/70 flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>
                <div x-show="images.length>0 && images.length<5" @click="$refs.imgIn.click()"
                     class="aspect-square rounded-2xl border-2 border-dashed border-line flex items-center justify-center cursor-pointer">
                    <svg class="w-6 h-6 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
            </div>
            <p class="text-red-400 text-xs mb-3" x-show="serr.images" x-text="serr.images"></p>

            <div class="flex items-center gap-3 my-4">
                <div class="flex-1 h-px bg-line"></div>
                <span class="text-xs text-muted">Video (ixtiyoriy)</span>
                <div class="flex-1 h-px bg-line"></div>
            </div>

            <div x-show="!video">
                <div class="drop-zone p-4 cursor-pointer" @click="$refs.vidIn.click()">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-[rgba(240,180,41,0.15)] flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" style="color:#f0b429" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold">Video yuklash</p>
                            <p class="text-xs text-muted mt-0.5">MP4 · MOV · Maks <b class="text-ink">50 MB</b></p>
                        </div>
                    </div>
                    <input type="file" x-ref="vidIn" class="hidden" accept=".mp4,.mov,.avi,.webm,video/*" @change="onVidSelect($event)">
                </div>
            </div>
            <div x-show="video" class="relative rounded-2xl overflow-hidden bg-black">
                <video :src="vidPreview" class="w-full max-h-48 object-contain" controls preload="metadata"></video>
                <button @click="removeVideo()" class="absolute top-2 right-2 w-8 h-8 rounded-full bg-black/80 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <div class="absolute bottom-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded-lg" x-text="vidName"></div>
            </div>
            <p class="text-red-400 text-xs mt-2" x-show="serr.video" x-text="serr.video"></p>
        </section>

        {{-- Step 2: Details --}}
        <section x-show="sellStep===2" class="flex-1 overflow-y-auto px-5 py-3 anim-fadeup">
            <h2 class="text-lg font-bold mb-0.5">Akkaunt ma'lumotlari</h2>
            <p class="text-sm text-muted mb-4">Aniq va to'g'ri kiriting</p>
            <div class="mb-4">
                <label class="text-sm font-medium block mb-2">Narx <span class="text-red-400">*</span></label>
                <div class="relative">
                    <input type="number" x-model="sform.price" placeholder="150000" inputmode="numeric" min="0"
                           class="field pr-14" :class="{'border-red-500':serr.price}">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold g-gold">so'm</span>
                </div>
                <div class="mt-2 card2 flex items-center justify-between" x-show="sform.price>0">
                    <span class="text-xs text-muted">Ko'rinish</span>
                    <span class="text-base font-bold g-gold" x-text="fmtPrice(sform.price)"></span>
                </div>
                <p class="text-red-400 text-xs mt-1" x-show="serr.price" x-text="serr.price"></p>
            </div>
            <div class="mb-4" x-data="{lvOpen: false}">
                <label class="text-sm font-medium block mb-2">Kolleksiya darajasi <span class="text-red-400">*</span></label>

                {{-- Trigger tugma --}}
                <button type="button"
                        @click="lvOpen = !lvOpen"
                        class="field flex items-center gap-3 text-left"
                        :class="serr.collection_level ? 'border-red-500' : (lvOpen ? 'border-[#7c3aed]' : '')">
                    <template x-if="!sform.collection_level">
                        <span class="text-muted flex-1">Tanlang...</span>
                    </template>
                    <template x-if="sform.collection_level">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <span class="text-lg leading-none" x-text="levelInfo(sform.collection_level)?.emoji"></span>
                            <span class="text-sm font-semibold text-ink truncate" x-text="sform.collection_level"></span>
                            <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full text-white flex-shrink-0"
                                  :style="`background:${levelInfo(sform.collection_level)?.color}`"
                                  x-text="(levelInfo(sform.collection_level)?.num ?? '') + '/8'"></span>
                        </div>
                    </template>
                    <svg class="w-4 h-4 text-muted flex-shrink-0 transition-transform duration-200"
                         :class="lvOpen ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Ro'yxat (ochiladi/yopiladi) --}}
                <div x-show="lvOpen"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="mt-2 rounded-2xl overflow-hidden border border-line"
                     style="background:#0f0f28">
                    <template x-for="lv in collectionLevels" :key="lv.ru">
                        <button type="button"
                                @click="sform.collection_level = lv.ru; lvOpen = false; delete serr.collection_level"
                                class="w-full flex items-center gap-3 px-4 py-3 text-left transition-colors border-b border-line last:border-b-0"
                                :style="sform.collection_level === lv.ru ? `background:${lv.color}18` : 'background:transparent'">
                            {{-- Emoji --}}
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden"
                                 :style="lv.img ? 'background:rgba(255,255,255,0.05)' : `background:${lv.color}22`">
                                <template x-if="lv.img">
                                    <img :src="lv.img" class="w-8 h-8 object-contain" loading="lazy" draggable="false">
                                </template>
                                <template x-if="!lv.img">
                                    <span x-text="lv.emoji ?? '🎮'" class="text-lg"></span>
                                </template>
                            </div>
                            {{-- Nom --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-ink leading-snug" x-text="lv.ru"></p>
                                {{-- Progress dots --}}
                                <div class="flex gap-0.5 mt-1.5">
                                    <template x-for="n in 8" :key="n">
                                        <div class="h-1 rounded-full flex-1 transition-colors"
                                             :style="n <= lv.num ? `background:${lv.color}` : 'background:#252550'"></div>
                                    </template>
                                </div>
                            </div>
                            {{-- Daraja raqami --}}
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full text-white"
                                      :style="`background:${lv.color}`"
                                      x-text="lv.num + '/8'"></span>
                                <svg x-show="sform.collection_level === lv.ru"
                                     class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     :style="`color:${lv.color}`">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                <div x-show="sform.collection_level !== lv.ru" class="w-4"></div>
                            </div>
                        </button>
                    </template>
                </div>

                <p class="text-red-400 text-xs mt-1.5" x-show="serr.collection_level" x-text="serr.collection_level"></p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium block mb-2">Qahramonlar <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <input type="number" x-model="sform.heroes_count" placeholder="120" inputmode="numeric" min="0" max="131"
                               class="field" :class="{'border-red-500':serr.heroes_count}">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium block mb-2">Skinlar <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <input type="number" x-model="sform.skins_count" placeholder="85" inputmode="numeric" min="0" max="1070"
                               class="field" :class="{'border-red-500':serr.skins_count}">
                    </div>
                </div>
            </div>
        </section>

        {{-- Step 3: Description --}}
        <section x-show="sellStep===3" class="flex-1 overflow-y-auto px-5 py-3 anim-fadeup">
            <h2 class="text-lg font-bold mb-0.5">Qo'shimcha</h2>
            <p class="text-sm text-muted mb-4">Tavsif va transfer holati</p>
            <div class="mb-5">
                <label class="text-sm font-medium block mb-2">Tavsif (ixtiyoriy)</label>
                <textarea x-model="sform.description" placeholder="Xaridor uchun foydali ma'lumot..." class="field" rows="4" maxlength="2000"></textarea>
                <div class="flex justify-end mt-1">
                    <span class="text-xs text-muted" x-text="`${sform.description.length}/2000`"></span>
                </div>
            </div>
            <div class="card mb-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold">Transferga tayyor</p>
                        <p class="text-xs text-muted mt-0.5">Darhol boshqa foydalanuvchiga o'tkazilishi mumkin</p>
                    </div>
                    <div class="toggle" :style="sform.ready?'background:#7c3aed':'background:#252550'" @click="sform.ready=!sform.ready">
                        <div class="toggle-thumb" :style="sform.ready?'transform:translateX(24px)':''"></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <p class="text-xs font-bold text-muted uppercase tracking-widest mb-3">Yuborish ko'rinishi</p>
                <div class="space-y-2.5">
                    <div class="flex justify-between"><span class="text-xs text-muted">💰 Narx</span><span class="text-sm font-bold g-gold" x-text="sform.price?fmtPrice(sform.price):'—'"></span></div>
                    <div class="h-px bg-line"></div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted">🏆 Kolleksiya</span>
                        <div x-show="sform.collection_level && levelInfo(sform.collection_level)" class="flex items-center gap-1.5">
                            <span x-text="levelInfo(sform.collection_level)?.emoji"></span>
                            <span class="text-xs font-bold text-white px-2 py-0.5 rounded-full"
                                  :style="`background:${levelInfo(sform.collection_level)?.color}`"
                                  x-text="levelInfo(sform.collection_level)?.short"></span>
                        </div>
                        <span x-show="!sform.collection_level" class="text-xs text-muted">—</span>
                    </div>
                    <div class="flex justify-between"><span class="text-xs text-muted">⚔️ Qahramonlar</span><span class="text-xs font-medium text-ink" x-text="sform.heroes_count?sform.heroes_count+' ta':'—'"></span></div>
                    <div class="flex justify-between"><span class="text-xs text-muted">👗 Skinlar</span><span class="text-xs font-medium text-ink" x-text="sform.skins_count?sform.skins_count+' ta':'—'"></span></div>
                    <div class="flex justify-between"><span class="text-xs text-muted">🔄 Transfer</span><span class="text-xs font-semibold" :class="sform.ready?'text-green-400':'text-muted'" x-text="sform.ready?'Tayyor ✓':'Tayyor emas'"></span></div>
                </div>
            </div>
        </section>

        {{-- Success --}}
        <section x-show="sellStep==='success'" class="flex-1 flex flex-col items-center justify-center px-5 py-10 anim-fadeup">
            <div class="relative mb-8 flex items-center justify-center">
                <div class="absolute w-28 h-28 rounded-full bg-[rgba(124,58,237,0.15)] anim-ring"></div>
                <div class="absolute w-28 h-28 rounded-full bg-[rgba(124,58,237,0.1)] anim-ring" style="animation-delay:.6s"></div>
                <div class="anim-pop w-28 h-28 rounded-full flex items-center justify-center"
                     style="background:linear-gradient(135deg,#7c3aed,#5b21b6);box-shadow:0 0 60px rgba(124,58,237,.5)">
                    <svg class="w-14 h-14" fill="none" viewBox="0 0 24 24">
                        <path class="anim-draw" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <h2 class="text-2xl font-extrabold mb-2">Yuborildi! 🎉</h2>
            <p class="text-muted text-center text-sm leading-relaxed mb-8">Akkauntingiz adminga yuborildi.<br>Tez orada ko'rib chiqishadi.</p>
            <button @click="switchTab('market'); sellStep=1; resetSellForm()"
                    class="btn btn-primary" style="width:200px;font-size:14px">← Katalogga qaytish</button>
        </section>

        {{-- Sell footer --}}
        <footer class="flex-shrink-0 px-5 pt-3 pb-4" x-show="sellStep!=='success'">
            <div x-show="sellGlobalErr" class="mb-3 p-3 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm" x-text="sellGlobalErr"></div>
            <div x-show="sellStep===1"><button @click="sellNext()" class="btn btn-primary">Davom etish →</button></div>
            <div x-show="sellStep===2" class="flex gap-3">
                <button @click="sellBack()" class="btn btn-ghost" style="width:30%">← Orqaga</button>
                <button @click="sellNext()" class="btn btn-primary" style="flex:1">Davom etish →</button>
            </div>
            <div x-show="sellStep===3" class="flex gap-3">
                <button @click="sellBack()" class="btn btn-ghost" style="width:30%" :disabled="sellLoading">← Orqaga</button>
                <button @click="submitSell()" class="btn btn-gold" style="flex:1" :disabled="sellLoading">
                    <template x-if="!sellLoading"><span class="flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Yuborish</span></template>
                    <template x-if="sellLoading"><span class="flex items-center gap-2"><svg class="w-4 h-4 anim-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>Yuklanmoqda...</span></template>
                </button>
            </div>
        </footer>
    </div>

    {{-- ══════════════════════════════════════
         TAB: PROFILE
    ══════════════════════════════════════ --}}
    <div x-show="tab === 'profile'" class="min-h-screen flex flex-col pb-nav">

        <div class="px-5 pt-6 pb-4 flex-shrink-0">
            <div class="flex items-center gap-4 mb-5">
                <div class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0">
                    <div class="w-full h-full flex items-center justify-center text-2xl font-extrabold text-white"
                         style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                        <span x-text="(tgUser?.first_name||'U')[0].toUpperCase()"></span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-base font-bold text-ink truncate"
                       x-text="tgUser ? (tgUser.first_name + (tgUser.last_name?' '+tgUser.last_name:'')) : 'Mehmon'"></p>
                    <p class="text-sm text-muted truncate"
                       x-text="tgUser?.username ? '@'+tgUser.username : 'Username yo\'q'"></p>
                    <p class="text-xs text-muted mt-0.5" x-text="tgId ? 'ID: '+tgId : ''"></p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 mb-1">
                <div class="card2 text-center">
                    <p class="text-xl font-extrabold text-ink" x-text="myAccounts.length"></p>
                    <p class="text-[10px] text-muted mt-0.5">Jami</p>
                </div>
                <div class="card2 text-center">
                    <p class="text-xl font-extrabold text-green-400"
                       x-text="myAccounts.filter(a=>a.status==='active').length"></p>
                    <p class="text-[10px] text-muted mt-0.5">Sotuvda</p>
                </div>
                <div class="card2 text-center">
                    <p class="text-xl font-extrabold" style="color:#a78bfa"
                       x-text="myAccounts.filter(a=>a.status==='sold').length"></p>
                    <p class="text-[10px] text-muted mt-0.5">Sotildi</p>
                </div>
            </div>
            <div x-show="myAccounts.filter(a=>a.status==='archived').length > 0"
                 class="card2 flex items-center justify-between mt-2">
                <p class="text-xs text-muted">📦 Arxivlangan</p>
                <p class="text-sm font-bold text-muted"
                   x-text="myAccounts.filter(a=>a.status==='archived').length + ' ta'"></p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-5">
            <p class="text-xs font-bold text-muted uppercase tracking-widest mb-3">Mening e'lonlarim</p>

            <div x-show="myLoading" class="space-y-3">
                <template x-for="i in 3" :key="i">
                    <div class="card flex items-center gap-3">
                        <div class="skeleton w-14 h-14 rounded-xl flex-shrink-0"></div>
                        <div class="flex-1 space-y-2">
                            <div class="skeleton h-3 rounded-full w-2/3"></div>
                            <div class="skeleton h-3 rounded-full w-1/3"></div>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="!myLoading && myLoaded && myAccounts.length===0"
                 class="flex flex-col items-center py-12 text-center">
                <div class="text-4xl mb-3">📭</div>
                <p class="text-sm font-semibold text-ink mb-1">E'lon yo'q</p>
                <p class="text-xs text-muted mb-4">Hali akkaunt joylashmadingiz</p>
                <button @click="switchTab('sell')" class="btn btn-primary" style="width:160px;font-size:13px">
                    ➕ E'lon berish
                </button>
            </div>

            <div x-show="!myLoading && myAccounts.length>0" class="space-y-3 anim-fadeup">
                <template x-for="a in myAccounts" :key="a.id">
                    <div class="card">
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-xl overflow-hidden bg-surface flex-shrink-0 flex items-center justify-center">
                                <template x-if="a.thumbnail">
                                    <img :src="a.thumbnail" class="w-full h-full object-cover" loading="lazy">
                                </template>
                                <template x-if="!a.thumbnail">
                                    <span class="text-2xl">🎮</span>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-muted truncate" x-text="a.collection_level"></p>
                                <p class="text-sm font-bold g-gold" x-text="fmtPrice(a.price)"></p>
                                <p class="text-[10px] text-muted mt-0.5" x-text="a.created_at"></p>
                            </div>
                            <div>
                                <span class="status-badge"
                                      :style="`background:${statusInfo(a.status).bg};color:${statusInfo(a.status).color}`"
                                      x-text="statusInfo(a.status).text"></span>
                            </div>
                        </div>

                        {{-- Action tugmalar (faqat active/pending uchun) --}}
                        <template x-if="a.status === 'active' || a.status === 'pending'">
                            <div class="flex gap-2 mt-3 pt-3 border-t border-line">
                                <button @click="openEditModal(a)"
                                        class="acc-action-btn"
                                        style="border-color:rgba(99,179,237,.3);color:#63b3ed;background:rgba(99,179,237,.07);min-width:44px">
                                    ✏️
                                </button>
                                <button @click="openMarkSoldModal(a)"
                                        class="acc-action-btn flex-1"
                                        style="border-color:rgba(167,139,250,.3);color:#a78bfa;background:rgba(124,58,237,.08)">
                                    💜 Sotildi deb belgilash
                                </button>
                                <button @click="openDeleteModal(a)"
                                        class="acc-action-btn"
                                        style="border-color:rgba(239,68,68,.3);color:#f87171;background:rgba(239,68,68,.07);min-width:44px">
                                    🗑
                                </button>
                            </div>
                        </template>

                        {{-- Faqat o'chirish (sold/archived uchun) --}}
                        <template x-if="a.status === 'sold' || a.status === 'archived'">
                            <div class="flex mt-3 pt-3 border-t border-line">
                                <button @click="openDeleteModal(a)"
                                        class="acc-action-btn w-full"
                                        style="border-color:rgba(239,68,68,.25);color:#f87171;background:rgba(239,68,68,.06)">
                                    🗑 O'chirish
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div x-show="!tgId" class="flex flex-col items-center py-12 text-center">
                <div class="text-4xl mb-3">🔐</div>
                <p class="text-sm font-semibold text-ink">Telegram orqali kiring</p>
                <p class="text-xs text-muted mt-1">Botni oching va /start yuboring</p>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         DELETE MODAL
    ══════════════════════════════════════ --}}
    <template x-teleport="body">
        <div x-show="deleteModal.open" x-transition.opacity class="action-sheet-overlay"
             @click.self="deleteModal.open = false"></div>
        <div x-show="deleteModal.open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="transform translate-y-full"
             x-transition:enter-end="transform translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="transform translate-y-0"
             x-transition:leave-end="transform translate-y-full"
             class="action-sheet">
            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mb-4"
                     style="background:rgba(239,68,68,.12);border:1.5px solid rgba(239,68,68,.25)">
                    <span class="text-2xl">🗑️</span>
                </div>
                <p class="text-base font-bold text-ink mb-1">E'lonni o'chirish</p>
                <p class="text-xs text-muted leading-relaxed">
                    <span class="font-semibold text-ink" x-text="deleteModal.account?.collection_level"></span>
                    e'lonini butunlay o'chirasizmi?<br>Bu amalni qaytarib bo'lmaydi.
                </p>
            </div>
            <div class="flex gap-3">
                <button @click="deleteModal.open = false"
                        class="btn btn-ghost flex-1" style="padding:13px">
                    Bekor
                </button>
                <button @click="confirmDelete()"
                        :disabled="deleteModal.loading"
                        class="btn flex-1 font-semibold"
                        style="background:rgba(239,68,68,.15);color:#f87171;border:1.5px solid rgba(239,68,68,.3);border-radius:14px;padding:13px">
                    <span x-show="!deleteModal.loading">🗑 O'chirish</span>
                    <span x-show="deleteModal.loading" class="anim-spin inline-block">⟳</span>
                </button>
            </div>
        </div>
    </template>

    {{-- ══════════════════════════════════════
         MARK-SOLD MODAL
    ══════════════════════════════════════ --}}
    <template x-teleport="body">
        <div x-show="soldModal.open" x-transition.opacity class="action-sheet-overlay"
             @click.self="soldModal.open = false"></div>
        <div x-show="soldModal.open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="transform translate-y-full"
             x-transition:enter-end="transform translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="transform translate-y-0"
             x-transition:leave-end="transform translate-y-full"
             class="action-sheet">
            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mb-4"
                     style="background:rgba(167,139,250,.12);border:1.5px solid rgba(167,139,250,.3)">
                    <span class="text-2xl">💜</span>
                </div>
                <p class="text-base font-bold text-ink mb-1">Sotildi deb belgilash</p>
                <p class="text-xs text-muted leading-relaxed">
                    <span class="font-semibold text-ink" x-text="soldModal.account?.collection_level"></span>
                    akkauntingizni sotilgan deb belgilaysizmi?<br>
                    <span class="text-[11px]">E'lon bozordan olinadi, lekin tarixingizda saqlanib qoladi.</span>
                </p>
            </div>
            <div class="flex gap-3">
                <button @click="soldModal.open = false"
                        class="btn btn-ghost flex-1" style="padding:13px">
                    Bekor
                </button>
                <button @click="confirmMarkSold()"
                        :disabled="soldModal.loading"
                        class="btn flex-1 font-semibold"
                        style="background:rgba(124,58,237,.15);color:#a78bfa;border:1.5px solid rgba(124,58,237,.3);border-radius:14px;padding:13px">
                    <span x-show="!soldModal.loading">✅ Tasdiqlash</span>
                    <span x-show="soldModal.loading" class="anim-spin inline-block">⟳</span>
                </button>
            </div>
        </div>
    </template>

    {{-- ══════════════════════════════════════
         EDIT MODAL (Full screen)
    ══════════════════════════════════════ --}}
    <div x-show="editModal.open"
         class="detail-modal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4">

        {{-- Header --}}
        <div class="flex-shrink-0 flex items-center gap-3 px-4 py-3 border-b border-line">
            <button @click="editModal.open = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-surface2">
                <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="flex-1">
                <p class="text-sm font-bold text-ink">✏️ Tahrirlash</p>
                <p class="text-xs text-muted truncate" x-text="editModal.account?.collection_level"></p>
            </div>
        </div>

        {{-- Form --}}
        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-5">

            {{-- Kolleksiya darajasi --}}
            <div x-data="{lvOpen2: false}">
                <label class="text-sm font-medium block mb-2">Kolleksiya darajasi <span class="text-red-400">*</span></label>
                <button type="button" @click="lvOpen2 = !lvOpen2"
                        class="field flex items-center gap-3 text-left w-full"
                        :class="eform.collection_level ? '' : 'border-line'">
                    <template x-if="!eform.collection_level">
                        <span class="text-muted flex-1">Tanlang...</span>
                    </template>
                    <template x-if="eform.collection_level">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <span class="text-lg leading-none" x-text="levelInfo(eform.collection_level)?.emoji"></span>
                            <span class="text-sm font-semibold text-ink truncate" x-text="eform.collection_level"></span>
                            <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full text-white flex-shrink-0"
                                  :style="`background:${levelInfo(eform.collection_level)?.color}`"
                                  x-text="(levelInfo(eform.collection_level)?.num ?? '') + '/8'"></span>
                        </div>
                    </template>
                    <svg class="w-4 h-4 text-muted flex-shrink-0 transition-transform duration-200" :class="lvOpen2 ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="lvOpen2"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mt-2 rounded-2xl overflow-hidden border border-line" style="background:#0f0f28">
                    <template x-for="lv in collectionLevels" :key="lv.ru">
                        <button type="button"
                                @click="eform.collection_level = lv.ru; lvOpen2 = false"
                                class="w-full flex items-center gap-3 px-4 py-3 text-left border-b border-line last:border-b-0"
                                :style="eform.collection_level === lv.ru ? `background:${lv.color}18` : 'background:transparent'">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden"
                                 :style="lv.img ? 'background:rgba(255,255,255,0.05)' : `background:${lv.color}22`">
                                <template x-if="lv.img">
                                    <img :src="lv.img" class="w-8 h-8 object-contain" loading="lazy" draggable="false">
                                </template>
                                <template x-if="!lv.img">
                                    <span x-text="lv.emoji ?? '🎮'" class="text-lg"></span>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-ink" x-text="lv.ru"></p>
                                <div class="flex gap-0.5 mt-1.5">
                                    <template x-for="n in 8" :key="n">
                                        <div class="h-1 rounded-full flex-1"
                                             :style="n <= lv.num ? `background:${lv.color}` : 'background:#252550'"></div>
                                    </template>
                                </div>
                            </div>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full text-white flex-shrink-0"
                                  :style="`background:${lv.color}`" x-text="lv.num + '/8'"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Narx --}}
            <div>
                <label class="text-sm font-medium block mb-2">Narx (so'm) <span class="text-red-400">*</span></label>
                <input type="number" x-model="eform.price" placeholder="450000"
                       inputmode="numeric" min="1000"
                       class="field w-full" :class="eerr.price ? 'border-red-500' : ''">
                <p class="text-red-400 text-xs mt-1" x-show="eerr.price" x-text="eerr.price"></p>
            </div>

            {{-- Qahramonlar + Skinlar --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm font-medium block mb-2">Qahramonlar <span class="text-red-400">*</span></label>
                    <input type="number" x-model="eform.heroes_count" placeholder="120"
                           inputmode="numeric" min="0" max="131"
                           class="field w-full" :class="eerr.heroes_count ? 'border-red-500' : ''">
                    <p class="text-red-400 text-xs mt-1" x-show="eerr.heroes_count" x-text="eerr.heroes_count"></p>
                </div>
                <div>
                    <label class="text-sm font-medium block mb-2">Skinlar <span class="text-red-400">*</span></label>
                    <input type="number" x-model="eform.skins_count" placeholder="85"
                           inputmode="numeric" min="0" max="1070"
                           class="field w-full" :class="eerr.skins_count ? 'border-red-500' : ''">
                    <p class="text-red-400 text-xs mt-1" x-show="eerr.skins_count" x-text="eerr.skins_count"></p>
                </div>
            </div>

            {{-- Tavsif --}}
            <div>
                <label class="text-sm font-medium block mb-2">Tavsif <span class="text-muted text-xs">(ixtiyoriy)</span></label>
                <textarea x-model="eform.description" placeholder="Akkaunt haqida qo'shimcha ma'lumot..."
                          rows="3" maxlength="2000"
                          class="field w-full resize-none" style="height:auto"></textarea>
                <p class="text-right text-xs text-muted mt-1" x-text="(eform.description?.length ?? 0) + '/2000'"></p>
            </div>

            {{-- Transfer --}}
            <label class="flex items-center gap-3 cursor-pointer">
                <div class="relative">
                    <input type="checkbox" x-model="eform.ready_for_transfer" class="sr-only">
                    <div class="w-11 h-6 rounded-full transition-colors duration-200"
                         :class="eform.ready_for_transfer ? 'bg-violet-600' : 'bg-surface3'"></div>
                    <div class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform duration-200"
                         :class="eform.ready_for_transfer ? 'translate-x-5' : 'translate-x-0'"></div>
                </div>
                <div>
                    <p class="text-sm font-medium text-ink">Transfer uchun tayyor</p>
                    <p class="text-xs text-muted">Akkaunt boshqa qurilmaga o'tkazilishi mumkin</p>
                </div>
            </label>

            <div x-show="eerr.global" class="p-3 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm" x-text="eerr.global"></div>
            <div class="h-2"></div>
        </div>

        {{-- Footer --}}
        <div class="flex-shrink-0 px-5 py-3 border-t border-line"
             style="padding-bottom:calc(12px + env(safe-area-inset-bottom,0px))">
            <button @click="submitEdit()"
                    :disabled="editModal.loading"
                    class="btn btn-primary w-full">
                <template x-if="!editModal.loading">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Saqlash
                    </span>
                </template>
                <template x-if="editModal.loading">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 anim-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Saqlanmoqda...
                    </span>
                </template>
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         DETAIL MODAL (Full screen)
    ══════════════════════════════════════ --}}
    <div x-show="detail.open"
         x-data="{vidState: 'idle'}"
         x-init="$watch('detail.open', v => { if(!v) vidState='idle'; })"
         class="detail-modal" x-transition.opacity>

        {{-- Header --}}
        <div class="flex items-center gap-3 px-4 flex-shrink-0 border-b border-line"
             style="padding-top:calc(12px + env(safe-area-inset-top,0px));padding-bottom:12px">
            <button @click="closeDetail()"
                    class="w-9 h-9 rounded-full bg-surface flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-ink truncate" x-text="detail.account?.collection_level"></p>
                <p class="text-xs text-muted" x-text="detail.account ? fmtPrice(detail.account.price) : ''"></p>
            </div>
            <template x-if="detail.account && levelInfo(detail.account.collection_level)?.img">
                <img :src="levelInfo(detail.account.collection_level).img"
                     class="w-9 h-9 object-contain flex-shrink-0"
                     style="filter:drop-shadow(0 2px 8px rgba(0,0,0,.5))"
                     loading="lazy" draggable="false">
            </template>
            <template x-if="detail.account && !levelInfo(detail.account.collection_level)?.img">
                <span class="rank-badge text-white flex-shrink-0"
                      :style="`background:${rankColor(detail.account?.collection_level||'')}`"
                      x-text="rankShort(detail.account?.collection_level||'')"></span>
            </template>
        </div>

        {{-- Scrollable content --}}
        <div class="flex-1 overflow-y-auto">

            {{-- Rasmlar — vertikal scroll --}}
            <template x-if="detail.account && detailImgs.length > 0">
                <div>
                    <template x-for="(img, i) in detailImgs" :key="i">
                        <div x-data="{loaded: false}"
                             class="relative w-full flex-shrink-0"
                             style="background:#07071a;min-height:200px">
                            {{-- Skeleton --}}
                            <div x-show="!loaded"
                                 class="skeleton absolute inset-0" style="min-height:200px"></div>
                            {{-- Rasm --}}
                            <img :src="img"
                                 @load="loaded=true"
                                 :class="loaded ? 'opacity-100' : 'opacity-0'"
                                 class="w-full h-auto block transition-opacity duration-300"
                                 style="object-fit:contain"
                                 loading="lazy" decoding="async" draggable="false">
                            {{-- Raqam --}}
                            <div x-show="detailImgs.length > 1"
                                 class="absolute top-2 right-2 bg-black/50 text-white text-xs px-2 py-0.5 rounded-full font-medium"
                                 x-text="(i+1) + '/' + detailImgs.length"></div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- No image placeholder --}}
            <div x-show="detail.account && detailImgs.length===0"
                 class="h-48 bg-surface flex items-center justify-center text-5xl">🎮</div>

            {{-- Video — Telegram uslubi (preload=none, bosib yuklanadi) --}}
            <div x-show="detail.account?.video" class="px-4 pt-4">
                <p class="text-xs font-bold text-muted uppercase tracking-widest mb-2">Video</p>

                <div class="relative rounded-2xl overflow-hidden bg-black" style="min-height:180px">

                    {{-- Video element (doim DOMda, lekin idle/loading da yashirin) --}}
                    <video x-ref="detailVideo"
                           :src="detail.account?.video"
                           :poster="detail.account?.thumbnail || ''"
                           preload="none"
                           playsinline
                           controls
                           class="w-full object-contain block"
                           style="max-height:240px"
                           x-show="vidState === 'playing'"></video>

                    {{-- Overlay: idle + loading holatlari --}}
                    <div x-show="vidState !== 'playing'"
                         class="absolute inset-0 flex flex-col items-center justify-center"
                         style="min-height:180px">

                        {{-- Xira poster (thumbnail) --}}
                        <img x-show="detail.account?.thumbnail"
                             :src="detail.account?.thumbnail"
                             class="absolute inset-0 w-full h-full object-cover"
                             style="filter:blur(14px);transform:scale(1.08);opacity:0.35"
                             draggable="false">

                        {{-- Qora overlay --}}
                        <div class="absolute inset-0" style="background:rgba(7,7,26,0.55)"></div>

                        {{-- IDLE: yuklab ko'rish tugmasi --}}
                        <div x-show="vidState === 'idle'"
                             @click="vidState='loading'; $nextTick(() => { const v=$refs.detailVideo; v.load(); v.addEventListener('canplay', ()=>{ vidState='playing'; v.play().catch(()=>{}); }, {once:true}); v.addEventListener('error', ()=>{ vidState='idle'; }, {once:true}); })"
                             class="relative z-10 flex flex-col items-center gap-3 cursor-pointer select-none">

                            {{-- Doira tugma --}}
                            <div style="width:68px;height:68px;border-radius:50%;background:rgba(255,255,255,0.13);border:2px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(6px);transition:transform .12s"
                                 @mousedown="$el.style.transform='scale(0.93)'"
                                 @mouseup="$el.style.transform='scale(1)'"
                                 @touchstart.passive="$el.style.transform='scale(0.93)'"
                                 @touchend.passive="$el.style.transform='scale(1)'">
                                <svg style="width:30px;height:30px;color:white;margin-left:4px" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>

                            {{-- Hajm --}}
                            <div class="text-center">
                                <p class="text-white text-sm font-semibold">Video ko'rish</p>
                                <p x-show="detail.account?.video_size"
                                   class="text-xs font-medium mt-0.5"
                                   style="color:rgba(255,255,255,0.65)"
                                   x-text="'💾 ' + detail.account?.video_size + ' MB'"></p>
                            </div>
                        </div>

                        {{-- LOADING: spinner --}}
                        <div x-show="vidState === 'loading'"
                             class="relative z-10 flex flex-col items-center gap-3">
                            <div style="width:56px;height:56px;border-radius:50%;border:3px solid rgba(255,255,255,0.18);border-top-color:rgba(255,255,255,0.9);animation:spin .75s linear infinite"></div>
                            <p style="color:rgba(255,255,255,0.75);font-size:13px">Yuklanmoqda...</p>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div class="px-4 py-4 space-y-4" x-show="detail.account">

                {{-- Collection Level Card --}}
                <template x-if="detail.account && levelInfo(detail.account.collection_level)">
                    <div class="rounded-2xl overflow-hidden"
                         :style="`background:linear-gradient(135deg,${levelInfo(detail.account.collection_level).color}18,${levelInfo(detail.account.collection_level).color}06);border:1px solid ${levelInfo(detail.account.collection_level).color}35`">
                        <div class="px-4 pt-3 pb-2 flex items-center gap-3">
                            {{-- Rank image/emoji --}}
                            <div class="w-14 h-14 flex items-center justify-center flex-shrink-0">
                                <template x-if="levelInfo(detail.account.collection_level)?.img">
                                    <img :src="levelInfo(detail.account.collection_level).img"
                                         class="w-14 h-14 object-contain"
                                         style="filter:drop-shadow(0 4px 12px rgba(0,0,0,.5))"
                                         loading="lazy" draggable="false">
                                </template>
                                <template x-if="!levelInfo(detail.account.collection_level)?.img">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl"
                                         :style="`background:${levelInfo(detail.account.collection_level).color}25`">
                                        <span x-text="levelInfo(detail.account.collection_level).emoji ?? '🎮'"></span>
                                    </div>
                                </template>
                            </div>
                            {{-- Names --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="text-base font-extrabold text-ink"
                                       x-text="detail.account.collection_level"></p>
                                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full text-white"
                                          :style="`background:${levelInfo(detail.account.collection_level).color}`"
                                          x-text="`${levelInfo(detail.account.collection_level).num} / 8`"></span>
                                </div>
                                <p class="text-xs text-muted mt-0.5"
                                   x-text="'Daraja ' + levelInfo(detail.account.collection_level).num + ' / 8'"></p>
                            </div>
                        </div>
                        {{-- Progress bar --}}
                        <div class="h-1.5 mx-4 mb-3 rounded-full" style="background:rgba(0,0,0,0.2)">
                            <div class="h-full rounded-full transition-all duration-700"
                                 :style="`width:${levelInfo(detail.account.collection_level).num / 8 * 100}%;background:${levelInfo(detail.account.collection_level).color}`">
                            </div>
                        </div>
                    </div>
                </template>

                <div class="flex items-center justify-between">
                    <span class="text-3xl font-extrabold g-gold" x-text="detail.account ? fmtPrice(detail.account.price) : ''"></span>
                    <div x-show="detail.account?.ready_for_transfer"
                         class="flex items-center gap-1.5 text-xs text-green-400 bg-green-500/10 border border-green-500/30 px-3 py-1.5 rounded-full font-semibold">
                        <span>✓ Transferga tayyor</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="card2 flex items-center gap-3">
                        <span class="text-2xl">⚔️</span>
                        <div>
                            <p class="text-xs text-muted">Qahramonlar</p>
                            <p class="text-lg font-bold text-ink" x-text="detail.account?.heroes_count + ' ta'"></p>
                        </div>
                    </div>
                    <div class="card2 flex items-center gap-3">
                        <span class="text-2xl">👗</span>
                        <div>
                            <p class="text-xs text-muted">Skinlar</p>
                            <p class="text-lg font-bold text-ink" x-text="detail.account?.skins_count + ' ta'"></p>
                        </div>
                    </div>
                </div>

                <div class="card2 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[rgba(124,58,237,0.2)] flex items-center justify-center text-base flex-shrink-0">👤</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-muted">Sotuvchi</p>
                        <p class="text-sm font-semibold text-ink truncate" x-text="detail.account?.seller_name"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1 text-xs text-muted">
                            <span>👁</span>
                            <span x-text="detail.account?.views ?? 0"></span>
                        </div>
                        <a :href="`/profile/${detail.account?.seller_telegram_id}?viewer_id=${tgId}`"
                           target="_blank"
                           class="flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1.5 rounded-xl"
                           style="background:rgba(124,58,237,0.15);border:1px solid rgba(124,58,237,0.3);color:#a78bfa;text-decoration:none">
                            <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Profil
                        </a>
                    </div>
                </div>

                <div x-show="detail.account?.description">
                    <p class="text-xs font-bold text-muted uppercase tracking-widest mb-2">Tavsif</p>
                    <div class="card2 text-sm text-muted leading-relaxed" x-text="detail.account?.description"></div>
                </div>

                <div x-show="buyModal.success && buyModal.account?.id === detail.account?.id"
                     class="p-4 rounded-2xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm text-center">
                    ✅ So'rovingiz adminga yuborildi!
                </div>
                <div x-show="buyModal.error && buyModal.account?.id === detail.account?.id"
                     class="p-4 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm text-center"
                     x-text="buyModal.error"></div>

                <div class="h-4"></div>
            </div>

            {{-- ── Comments ── --}}
            <div class="px-4 pb-2" x-show="detail.account">

                <div class="h-px bg-line mb-4"></div>

                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold text-muted uppercase tracking-widest">💬 Izohlar</p>
                    <span class="text-xs text-muted" x-show="!commentsLoading"
                          x-text="comments.length + ' ta'"></span>
                </div>

                {{-- Skeleton --}}
                <div x-show="commentsLoading" class="space-y-4">
                    <template x-for="i in 3" :key="i">
                        <div class="flex gap-3">
                            <div class="skeleton w-9 h-9 rounded-full flex-shrink-0"></div>
                            <div class="flex-1 space-y-2 pt-1">
                                <div class="skeleton h-2.5 rounded-full w-1/4"></div>
                                <div class="skeleton h-3 rounded-full w-3/4"></div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Empty --}}
                <div x-show="!commentsLoading && comments.length === 0"
                     class="text-center py-5 text-sm text-muted">
                    Hali izoh yo'q. Birinchi bo'ling! 💬
                </div>

                {{-- Comment list --}}
                <div x-show="!commentsLoading" class="space-y-5">
                    <template x-for="c in comments" :key="c.id">
                        <div>
                            {{-- Top-level comment --}}
                            <div class="flex gap-3">
                                <div class="w-9 h-9 rounded-full flex-shrink-0 flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                                     style="background:linear-gradient(135deg,#7c3aed,#a78bfa)"
                                     x-text="c.sender.initials"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-xs font-semibold text-ink" x-text="c.sender.name"></span>
                                        <span x-show="c.is_seller"
                                              class="text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                                              style="background:rgba(240,180,41,0.15);color:#f0b429">Muallif</span>
                                        <span class="text-[10px] text-muted" x-text="timeAgo(c.created_at)"></span>
                                        <span x-show="c.edited_at" class="text-[10px] text-muted italic">(tahrirlangan)</span>
                                    </div>

                                    {{-- Edit mode --}}
                                    <template x-if="editingComment && editingComment.id === c.id">
                                        <div class="mt-1.5">
                                            <textarea x-model="editingComment.editText"
                                                      class="field field-sm w-full"
                                                      rows="2" maxlength="500"></textarea>
                                            <div class="flex gap-3 mt-1.5">
                                                <button @click="saveEdit()"
                                                        class="text-xs font-semibold text-purple-400">Saqlash</button>
                                                <button @click="editingComment=null"
                                                        class="text-xs text-muted">Bekor</button>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Message --}}
                                    <template x-if="!editingComment || editingComment.id !== c.id">
                                        <p class="text-sm text-ink mt-0.5 leading-relaxed break-words" x-text="c.message"></p>
                                    </template>

                                    {{-- Actions --}}
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <button x-show="tgId"
                                                @click="replyTo = {id: c.id, name: c.sender.name}"
                                                class="text-[11px] text-muted">↩ Javob</button>
                                        <button x-show="c.can_edit && (!editingComment || editingComment.id !== c.id)"
                                                @click="editingComment = {id: c.id, editText: c.message}"
                                                class="text-[11px] text-muted">Tahrir</button>
                                        <button x-show="c.can_edit"
                                                @click="deleteComment(c.id)"
                                                class="text-[11px] text-red-500/70">O'chirish</button>
                                    </div>
                                </div>
                            </div>

                            {{-- Replies --}}
                            <div x-show="c.replies && c.replies.length > 0" class="ml-12 mt-3 space-y-3">
                                <template x-for="r in c.replies" :key="r.id">
                                    <div class="flex gap-2">
                                        <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold text-white"
                                             style="background:linear-gradient(135deg,#5b21b6,#7c3aed)"
                                             x-text="r.sender.initials"></div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-xs font-semibold text-ink" x-text="r.sender.name"></span>
                                                <span x-show="r.is_seller"
                                                      class="text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                                                      style="background:rgba(240,180,41,0.15);color:#f0b429">Muallif</span>
                                                <span class="text-[10px] text-muted" x-text="timeAgo(r.created_at)"></span>
                                                <span x-show="r.edited_at" class="text-[10px] text-muted italic">(tahrirlangan)</span>
                                            </div>

                                            {{-- Reply edit mode --}}
                                            <template x-if="editingComment && editingComment.id === r.id">
                                                <div class="mt-1.5">
                                                    <textarea x-model="editingComment.editText"
                                                              class="field field-sm w-full"
                                                              rows="2" maxlength="500"></textarea>
                                                    <div class="flex gap-3 mt-1.5">
                                                        <button @click="saveEdit()"
                                                                class="text-xs font-semibold text-purple-400">Saqlash</button>
                                                        <button @click="editingComment=null"
                                                                class="text-xs text-muted">Bekor</button>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- Reply message --}}
                                            <template x-if="!editingComment || editingComment.id !== r.id">
                                                <p class="text-sm text-ink mt-0.5 leading-relaxed break-words" x-text="r.message"></p>
                                            </template>

                                            {{-- Reply actions --}}
                                            <div class="flex items-center gap-3 mt-1">
                                                <button x-show="r.can_edit && (!editingComment || editingComment.id !== r.id)"
                                                        @click="editingComment = {id: r.id, editText: r.message}"
                                                        class="text-[11px] text-muted">Tahrir</button>
                                                <button x-show="r.can_edit"
                                                        @click="deleteComment(r.id, c.id)"
                                                        class="text-[11px] text-red-500/70">O'chirish</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Comment input --}}
                <div class="mt-5" x-show="tgId">
                    {{-- Reply indicator --}}
                    <div x-show="replyTo"
                         class="flex items-center justify-between mb-2 px-3 py-2 rounded-xl"
                         style="background:rgba(124,58,237,0.1);border:1px solid rgba(124,58,237,0.2)">
                        <span class="text-xs text-purple-400">
                            ↩ <span x-text="replyTo?.name"></span>ga javob
                        </span>
                        <button @click="replyTo=null" class="text-muted">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="flex gap-2">
                        <textarea x-model="newComment"
                                  placeholder="Izoh yozing..."
                                  class="field field-sm flex-1"
                                  style="resize:none;min-height:42px;max-height:120px"
                                  rows="1"
                                  maxlength="500"
                                  @input="$el.style.height='auto';$el.style.height=$el.scrollHeight+'px'"
                                  @keydown.enter.prevent="if(!$event.shiftKey) postComment()"></textarea>
                        <button @click="postComment()"
                                :disabled="!newComment.trim()"
                                class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center self-end"
                                style="background:linear-gradient(135deg,#7c3aed,#6d28d9)"
                                :style="!newComment.trim() ? 'opacity:0.4' : ''">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                      d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Not logged in --}}
                <div x-show="!tgId" class="mt-4 text-center text-xs text-muted py-3">
                    Izoh qoldirish uchun bot orqali kiring
                </div>

                <div class="h-4"></div>
            </div>
        </div>

        {{-- Buy footer --}}
        <div class="flex-shrink-0 px-4 py-3 border-t border-line"
             style="padding-bottom:calc(12px + env(safe-area-inset-bottom,0px))"
             x-show="detail.account">
            <template x-if="detail.account && detail.account.seller_telegram_id != tgId">
                <button @click="openBuyModal(detail.account)"
                        class="btn btn-gold"
                        :disabled="buyModal.success && buyModal.account?.id === detail.account?.id">
                    💰 Sotib olish
                </button>
            </template>
            <template x-if="detail.account && detail.account.seller_telegram_id == tgId">
                <div class="text-center text-sm text-muted py-1">Bu sizning akkauntingiz</div>
            </template>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         BUY MODAL (bottom sheet)
    ══════════════════════════════════════ --}}
    <div x-show="buyModal.open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-[70] flex items-end justify-center"
         style="background:rgba(7,7,26,.8);backdrop-filter:blur(6px)">

        <div class="w-full max-w-lg bg-card rounded-t-3xl border-t border-line p-6"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="transform translate-y-full"
             x-transition:enter-end="transform translate-y-0">

            <div class="w-10 h-1 bg-line rounded-full mx-auto mb-5"></div>

            <template x-if="buyModal.account">
                <div>
                    <div class="flex items-start gap-4 mb-5">
                        <div class="w-16 h-16 rounded-2xl bg-surface overflow-hidden flex-shrink-0 flex items-center justify-center">
                            <template x-if="buyModal.account.thumbnail">
                                <img :src="buyModal.account.thumbnail" class="w-full h-full object-cover" loading="lazy">
                            </template>
                            <template x-if="!buyModal.account.thumbnail">
                                <div class="text-2xl">🎮</div>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="rank-badge text-white inline-block mb-1"
                                  :style="`background:${rankColor(buyModal.account.collection_level)}`"
                                  x-text="buyModal.account.collection_level"></span>
                            <p class="text-xl font-extrabold g-gold" x-text="fmtPrice(buyModal.account.price)"></p>
                            <p class="text-xs text-muted mt-0.5">⚔️ <span x-text="buyModal.account.heroes_count"></span> ta &nbsp; 👗 <span x-text="buyModal.account.skins_count"></span> ta</p>
                        </div>
                    </div>
                    <div x-show="buyModal.success" class="mb-4 p-4 rounded-2xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm text-center">✅ So'rovingiz adminga yuborildi!</div>
                    <div x-show="buyModal.error" class="mb-4 p-4 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm text-center" x-text="buyModal.error"></div>
                    <div class="flex gap-3">
                        <button @click="closeBuyModal()" class="btn btn-ghost" style="width:30%">Yopish</button>
                        <button @click="confirmBuy()" class="btn btn-gold" style="flex:1" :disabled="buyModal.loading||buyModal.success">
                            <template x-if="!buyModal.loading"><span>💰 Sotib olish</span></template>
                            <template x-if="buyModal.loading">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 anim-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>Yuklanmoqda...
                                </span>
                            </template>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Upload overlay --}}
    <div x-show="sellLoading" class="fixed inset-0 z-[80] flex flex-col items-center justify-center"
         style="background:rgba(7,7,26,.9);backdrop-filter:blur(8px)">
        <div class="w-20 h-20 rounded-3xl card flex items-center justify-center mb-4">
            <svg class="w-9 h-9 anim-spin" style="color:#7c3aed" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-ink mb-1">Yuborilmoqda...</p>
        <div class="w-48 prog-track mt-3"><div class="prog-fill" :style="`width:${uploadPct}%`"></div></div>
    </div>

    {{-- ══════════════════════════════════════
         BOTTOM NAV (3 tabs)
    ══════════════════════════════════════ --}}
    <nav class="bnav">
        <button @click="switchTab('market')" class="bnav-btn" :class="tab==='market'?'on':''">
            <svg class="w-5 h-5 transition-all duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span>Bozor</span>
            <div x-show="tab==='market'" class="bnav-dot"></div>
        </button>

        <a href="/webapp/requests" class="bnav-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <span>Qidiruv</span>
        </a>

        <button @click="switchTab('sell')" class="bnav-btn sell-pill-btn" :class="tab==='sell'?'on':''">
            <div class="w-8 h-8 rounded-2xl flex items-center justify-center transition-all duration-200"
                 :style="tab==='sell' ? 'background:linear-gradient(135deg,#7c3aed,#6d28d9);box-shadow:0 4px 16px rgba(124,58,237,.5)' : 'background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1)'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <span>Sotish</span>
        </button>

        <button @click="switchTab('profile')" class="bnav-btn" :class="tab==='profile'?'on':''">
            <svg class="w-5 h-5 transition-all duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>Profil</span>
            <div x-show="tab==='profile'" class="bnav-dot"></div>
        </button>
    </nav>

</div>

<script>
function app() {
    return {
        // ── Navigation ──
        tab: 'market',
        tgId: null,
        tgUser: null,
        authError: '',

        // ── Market ──
        accounts: [],
        loading: true,
        loadingMore: false,
        hasMore: false,
        nextPage: 2,
        total: 0,
        search: '',
        selectedRank: 'all',
        sort: 'newest',
        priceMin: '',
        priceMax: '',
        showPriceFilter: false,
        _searchTimer: null,

        // ── MLBB Collection Level tizimi (8 daraja) ──
        collectionLevels: [
            { ru: 'Коллекционер-любитель',   short: 'Любитель',   num: 1, color: '#7B8FA1', img: '/images/ranks/rank-1.png?v=2' },
            { ru: 'Младший коллекционер',    short: 'Младший',    num: 2, color: '#3DAA7A', img: '/images/ranks/rank-2.png?v=2' },
            { ru: 'Опытный коллекционер',    short: 'Опытный',    num: 3, color: '#29B6C5', img: '/images/ranks/rank-3.png?v=2' },
            { ru: 'Коллекционер-эксперт',    short: 'Эксперт',    num: 4, color: '#5C6BC0', img: '/images/ranks/rank-4.png?v=2' },
            { ru: 'Знаменитый коллекционер', short: 'Знаменитый', num: 5, color: '#9C27B0', img: '/images/ranks/rank-5.png?v=2' },
            { ru: 'Коллекционер-гуру',       short: 'Гуру',       num: 6, color: '#D81B9A', img: '/images/ranks/rank-6.png?v=2' },
            { ru: 'Мегаколлекционер',        short: 'Мега',       num: 7, color: '#E65100', img: '/images/ranks/rank-7.png?v=2' },
            { ru: 'Мировой коллекционер',    short: 'Мировой',    num: 8, color: '#C62828', img: '/images/ranks/rank-8.png?v=2' },
        ],

        // ── Detail modal ──
        detail: { open: false, account: null },

        // ── Comments ──
        comments: [],
        commentsLoading: false,
        newComment: '',
        replyTo: null,
        editingComment: null,

        // ── Buy modal ──
        buyModal: { open: false, account: null, loading: false, success: false, error: '' },

        // ── Profile action modals ──
        deleteModal: { open: false, account: null, loading: false },
        soldModal:   { open: false, account: null, loading: false },
        editModal:   { open: false, account: null, loading: false },
        eform: { price: '', heroes_count: '', skins_count: '', collection_level: '', description: '', ready_for_transfer: false },
        eerr:  {},

        // ── Profile ──
        myAccounts: [],
        myLoading: false,
        myLoaded: false,

        // ── Sell ──
        sellStep: 1,
        sellLoading: false,
        uploadPct: 0,
        sellGlobalErr: '',
        images: [], previews: [], video: null, vidPreview: null, vidName: '', dragging: false,
        serr: {},
        sform: { price:'', heroes_count:'', skins_count:'', collection_level:'', description:'', ready: false },

        // ── Computed ──
        get rankFilters() {
            return [
                { value: 'all', label: 'Barchasi', color: '#7c3aed' },
                ...this.collectionLevels.map(l => ({ value: l.ru, label: l.short, color: l.color })),
            ];
        },
        get filteredAccounts() {
            return this.accounts;
        },
        get sellStepTitle() {
            return ['', '— Media', "— Ma'lumotlar", '— Tavsif'][this.sellStep] ?? '';
        },
        get detailImgs() {
            const acc = this.detail.account;
            if (!acc) return [];
            return acc.images?.length ? acc.images : (acc.thumbnail ? [acc.thumbnail] : []);
        },

        // ── Init ──
        async init() {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            }

            const params = new URLSearchParams(location.search);
            const token  = params.get('token');
            const tab    = params.get('tab');

            if (token) {
                history.replaceState({}, '', location.pathname);
            }

            if (tab && ['market','sell','profile'].includes(tab)) {
                this.tab = tab;
            }

            // ── localStorage dan o'qish (har doim) ──
            const saved = localStorage.getItem('mlbb_user');
            if (saved) {
                try {
                    const d = JSON.parse(saved);
                    this.tgId   = d.tgId   ?? null;
                    this.tgUser = d.tgUser ?? null;
                } catch(_) {}
            }

            // ── Token URL da bo'lsa — yangilash ──
            if (token) {
                try {
                    const res = await axios.post('/api/auth/verify', { token });
                    this.tgId   = res.data.tg_id;
                    this.tgUser = { first_name: res.data.first_name, username: res.data.username };
                    localStorage.setItem('mlbb_user', JSON.stringify({ tgId: this.tgId, tgUser: this.tgUser }));
                } catch(e) {
                    // Token eskirgan yoki yaroqsiz — localStorage dagi ma'lumot ishlatiladi
                    if (!this.tgId) {
                        this.authError = 'Havola eskirgan. Botdan /start yuboring.';
                    }
                }
            }

            if (this.tab === 'profile' && this.tgId) this.fetchMyAccounts();
            await this.fetchAccounts();

            // Deep link: ?account=12 — akkauntni avtomatik ochish
            const accountId = params.get('account');
            if (accountId) {
                const found = this.accounts.find(a => a.id == accountId);
                if (found) {
                    this.openDetail(found);
                } else {
                    // Akkaunt birinchi sahifada bo'lmasligi mumkin — to'g'ridan fetch
                    try {
                        const r = await axios.get('/api/accounts', { params: { search: '' } });
                        const all = r.data.data ?? [];
                        const acc = all.find(a => a.id == accountId);
                        if (acc) this.openDetail(acc);
                    } catch(_) {}
                }
            }

            // Search debounce watchers
            this.$watch('search', () => {
                clearTimeout(this._searchTimer);
                this._searchTimer = setTimeout(() => this.fetchAccounts(), 400);
            });
            this.$watch('selectedRank', () => this.fetchAccounts());
            this.$watch('sort',         () => this.fetchAccounts());
            this.$watch('priceMin', () => {
                clearTimeout(this._priceTimer);
                this._priceTimer = setTimeout(() => this.fetchAccounts(), 600);
            });
            this.$watch('priceMax', () => {
                clearTimeout(this._priceTimer);
                this._priceTimer = setTimeout(() => this.fetchAccounts(), 600);
            });

            // Browser back tugmasi — modal yopilsin
            window.addEventListener('popstate', () => {
                if (this.detail.open) this._doCloseDetail();
            });
        },

        // ── Navigation ──
        switchTab(t) {
            this.tab = t;
            window.scrollTo(0, 0);
            if (t === 'profile' && !this.myLoaded && this.tgId) this.fetchMyAccounts();
        },

        // ── Market: fetch & pagination ──
        _apiParams(page) {
            const p = { page };
            if (this.search)                         p.search    = this.search;
            if (this.selectedRank !== 'all')         p.rank      = this.selectedRank;
            if (this.sort && this.sort !== 'newest') p.sort      = this.sort;
            if (this.priceMin)                       p.price_min = this.priceMin;
            if (this.priceMax)                       p.price_max = this.priceMax;
            return p;
        },

        async fetchAccounts() {
            this.loading  = true;
            this.accounts = [];
            this.nextPage = 2;
            try {
                const res = await axios.get('/api/accounts', { params: this._apiParams(1) });
                this.accounts = res.data.data    ?? [];
                this.hasMore  = res.data.has_more ?? false;
                this.nextPage = res.data.next_page ?? 2;
                this.total    = res.data.total    ?? this.accounts.length;
            } catch(e) { console.error(e); }
            finally { this.loading = false; }
        },

        async loadMore() {
            if (this.loadingMore || !this.hasMore) return;
            this.loadingMore = true;
            try {
                const res = await axios.get('/api/accounts', { params: this._apiParams(this.nextPage) });
                this.accounts = [...this.accounts, ...(res.data.data ?? [])];
                this.hasMore  = res.data.has_more ?? false;
                this.nextPage = res.data.next_page ?? this.nextPage + 1;
            } catch(e) { console.error(e); }
            finally { this.loadingMore = false; }
        },

        // ── Helpers ──
        levelInfo(level) {
            if (!level) return null;
            return this.collectionLevels.find(l => l.ru === level) ?? null;
        },
        rankColor(level) {
            const info = this.levelInfo(level);
            return info ? info.color : '#7878a8';
        },
        rankShort(level) {
            const info = this.levelInfo(level);
            return info ? info.short : (level ?? '');
        },
        fmtPrice(v) {
            return new Intl.NumberFormat('uz-UZ').format(v) + " so'm";
        },
        isNew(isoDate) {
            if (!isoDate) return false;
            return (Date.now() - new Date(isoDate).getTime()) < 24 * 60 * 60 * 1000;
        },

        // ── Detail modal ──
        openDetail(acc) {
            this.detail = { open: true, account: acc };
            this.comments = []; this.newComment = ''; this.replyTo = null; this.editingComment = null;
            this.fetchComments(acc.id);
            history.pushState({ modal: 'detail' }, '');
            // Ko'rildi soni
            axios.post(`/api/accounts/${acc.id}/view`).then(r => {
                if (this.detail.account?.id === acc.id) {
                    this.detail.account = { ...this.detail.account, views: r.data.views };
                }
            }).catch(() => {});
        },
        shareAccount(acc) {
            const botUsername = '{{ ltrim(config("services.telegram.bot_username"), "@") }}';
            const text = encodeURIComponent(`🎮 MLBB akkaunt sotuvda!\n💰 ${this.fmtPrice(acc.price)}\n⚔️ ${acc.heroes_count} ta qahramon | 👗 ${acc.skins_count} ta skin\n\n@${botUsername} orqali ko'rish`);
            const url  = encodeURIComponent(`https://t.me/${botUsername}?start=acc_${acc.id}`);
            window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank');
        },
        closeDetail() {
            this._doCloseDetail();
            // Agar pushState orqali kelgan bo'lsa — orqaga
            if (history.state?.modal === 'detail') history.back();
        },
        _doCloseDetail() {
            this.detail.open = false;
            setTimeout(() => { this.detail.account = null; this.comments = []; }, 300);
        },

        // ── Comments ──
        async fetchComments(accountId) {
            this.commentsLoading = true;
            try {
                const params = this.tgId ? { telegram_id: this.tgId } : {};
                const res = await axios.get(`/api/accounts/${accountId}/comments`, { params });
                this.comments = res.data.data ?? [];
            } catch(e) { console.error(e); }
            finally { this.commentsLoading = false; }
        },
        async postComment() {
            if (!this.newComment.trim()) return;
            if (!this.tgId) { alert("Izoh qoldirish uchun bot orqali kiring"); return; }
            const text = this.newComment;
            this.newComment = '';
            try {
                const res = await axios.post(`/api/accounts/${this.detail.account.id}/comments`, {
                    telegram_id: this.tgId,
                    message: text,
                    parent_id: this.replyTo?.id ?? null,
                });
                if (this.replyTo) {
                    const parent = this.comments.find(c => c.id === this.replyTo.id);
                    if (parent) parent.replies.push(res.data.comment);
                } else {
                    this.comments.unshift(res.data.comment);
                }
                this.replyTo = null;
            } catch(e) {
                this.newComment = text;
                alert(e.response?.data?.message ?? 'Xatolik yuz berdi');
            }
        },
        async deleteComment(commentId, parentId = null) {
            try {
                await axios.delete(`/api/comments/${commentId}`, { data: { telegram_id: this.tgId } });
                if (parentId) {
                    const parent = this.comments.find(c => c.id === parentId);
                    if (parent) parent.replies = parent.replies.filter(r => r.id !== commentId);
                } else {
                    this.comments = this.comments.filter(c => c.id !== commentId);
                }
                if (this.editingComment?.id === commentId) this.editingComment = null;
            } catch(e) { alert(e.response?.data?.message ?? 'Xato'); }
        },
        async saveEdit() {
            if (!this.editingComment || !this.editingComment.editText?.trim()) return;
            try {
                await axios.put(`/api/comments/${this.editingComment.id}`, {
                    telegram_id: this.tgId,
                    message: this.editingComment.editText,
                });
                const c = this.comments.find(c => c.id === this.editingComment.id);
                if (c) { c.message = this.editingComment.editText; c.edited_at = new Date().toISOString(); }
                else {
                    this.comments.forEach(p => {
                        const r = p.replies?.find(r => r.id === this.editingComment.id);
                        if (r) { r.message = this.editingComment.editText; r.edited_at = new Date().toISOString(); }
                    });
                }
                this.editingComment = null;
            } catch(e) { alert(e.response?.data?.message ?? 'Xato'); }
        },
        timeAgo(iso) {
            const s = Math.floor((Date.now() - new Date(iso)) / 1000);
            if (s < 60)   return s + 's';
            if (s < 3600) return Math.floor(s/60) + 'daq';
            if (s < 86400)return Math.floor(s/3600) + 'soat';
            return Math.floor(s/86400) + 'kun';
        },

        // ── Buy ──
        openBuyModal(acc) {
            this.buyModal = { open: true, account: acc, loading: false, success: false, error: '' };
        },
        closeBuyModal() {
            const was = this.buyModal.success;
            this.buyModal.open = false;
            if (was) this.fetchAccounts();
        },
        async confirmBuy() {
            if (!this.tgId) { this.buyModal.error = 'Telegram ID aniqlanmadi'; return; }
            this.buyModal.loading = true;
            this.buyModal.error   = '';
            try {
                await axios.post(`/api/buy/${this.buyModal.account.id}`, { telegram_id: this.tgId });
                this.buyModal.success = true;
            } catch(e) {
                this.buyModal.error = e.response?.data?.error ?? 'Xatolik yuz berdi';
            } finally {
                this.buyModal.loading = false;
            }
        },

        // ── Profile ──
        async fetchMyAccounts() {
            if (!this.tgId) return;
            this.myLoading = true;
            try {
                const res = await axios.get('/api/accounts/mine', { params: { telegram_id: this.tgId } });
                this.myAccounts = res.data.data ?? [];
                this.myLoaded   = true;
            } catch(e) { console.error(e); }
            finally { this.myLoading = false; }
        },
        statusInfo(status) {
            const m = {
                pending:  { text: "Ko'rib chiqilmoqda", color: '#f0b429', bg: 'rgba(240,180,41,.15)' },
                active:   { text: 'Sotuvda',            color: '#22c55e', bg: 'rgba(34,197,94,.15)'   },
                sold:     { text: 'Sotildi',             color: '#a78bfa', bg: 'rgba(167,139,250,.15)' },
                rejected: { text: 'Rad etildi',          color: '#ef4444', bg: 'rgba(239,68,68,.15)'   },
                archived: { text: 'Arxivlandi',          color: '#7878a8', bg: 'rgba(120,120,168,.15)' },
                draft:    { text: 'Qoralama',            color: '#7878a8', bg: 'rgba(120,120,168,.15)' },
            };
            return m[status] ?? { text: status, color: '#7878a8', bg: 'rgba(120,120,168,.15)' };
        },

        // ── Profile: account actions ──
        openDeleteModal(account) {
            this.deleteModal = { open: true, account, loading: false };
        },
        openMarkSoldModal(account) {
            this.soldModal = { open: true, account, loading: false };
        },
        async confirmDelete() {
            if (!this.deleteModal.account || !this.tgId) return;
            this.deleteModal.loading = true;
            try {
                await axios.delete(`/api/accounts/${this.deleteModal.account.id}`, {
                    data: { telegram_id: this.tgId }
                });
                this.myAccounts = this.myAccounts.filter(a => a.id !== this.deleteModal.account.id);
                this.deleteModal.open = false;
            } catch(e) {
                console.error(e);
            } finally {
                this.deleteModal.loading = false;
            }
        },
        async confirmMarkSold() {
            if (!this.soldModal.account || !this.tgId) return;
            this.soldModal.loading = true;
            try {
                await axios.post(`/api/accounts/${this.soldModal.account.id}/mark-sold`, {
                    telegram_id: this.tgId
                });
                const idx = this.myAccounts.findIndex(a => a.id === this.soldModal.account.id);
                if (idx !== -1) this.myAccounts[idx] = { ...this.myAccounts[idx], status: 'sold' };
                this.soldModal.open = false;
            } catch(e) {
                console.error(e);
            } finally {
                this.soldModal.loading = false;
            }
        },

        // ── Edit modal ──
        openEditModal(a) {
            this.eform = {
                price:              a.price,
                heroes_count:       a.heroes_count,
                skins_count:        a.skins_count,
                collection_level:   a.collection_level,
                description:        a.description ?? '',
                ready_for_transfer: a.ready_for_transfer ?? false,
            };
            this.eerr = {};
            this.editModal = { open: true, account: a, loading: false };
        },
        async submitEdit() {
            if (!this.editModal.account || !this.tgId) return;
            this.eerr = {};
            let ok = true;
            if (!this.eform.price || +this.eform.price < 1000)        { this.eerr.price        = 'Kamida 1 000 so\'m'; ok=false; }
            if (!this.eform.heroes_count && this.eform.heroes_count !== 0) { this.eerr.heroes_count = 'Sonni kiriting'; ok=false; }
            else if (+this.eform.heroes_count > 131)                   { this.eerr.heroes_count = 'Maksimum 131 ta'; ok=false; }
            if (!this.eform.skins_count && this.eform.skins_count !== 0)   { this.eerr.skins_count  = 'Sonni kiriting'; ok=false; }
            else if (+this.eform.skins_count > 1070)                   { this.eerr.skins_count  = 'Maksimum 1070 ta'; ok=false; }
            if (!this.eform.collection_level)                          { this.eerr.collection_level = 'Darajani tanlang'; ok=false; }
            if (!ok) return;

            this.editModal.loading = true;
            try {
                const res = await axios.put(`/api/accounts/${this.editModal.account.id}`, {
                    telegram_id:       this.tgId,
                    price:             this.eform.price,
                    heroes_count:      this.eform.heroes_count,
                    skins_count:       this.eform.skins_count,
                    collection_level:  this.eform.collection_level,
                    description:       this.eform.description,
                    ready_for_transfer: this.eform.ready_for_transfer,
                });
                // myAccounts ni yangilash
                const idx = this.myAccounts.findIndex(a => a.id === this.editModal.account.id);
                if (idx !== -1) {
                    this.myAccounts[idx] = { ...this.myAccounts[idx], ...res.data.account };
                }
                // Katalog ochiq bo'lsa — uni ham yangilash
                const mIdx = this.accounts.findIndex(a => a.id === this.editModal.account.id);
                if (mIdx !== -1) {
                    this.accounts[mIdx] = { ...this.accounts[mIdx], ...res.data.account };
                }
                this.editModal.open = false;
            } catch(e) {
                this.eerr.global = e.response?.data?.message ?? 'Xatolik yuz berdi';
            } finally {
                this.editModal.loading = false;
            }
        },

        // ── Sell: Media ──
        onImgSelect(e)  { this.addImgs([...e.target.files]); e.target.value = ''; },
        onDrop(e)        { this.dragging=false; this.addImgs([...e.dataTransfer.files].filter(f=>f.type.startsWith('image/'))); },
        addImgs(files) {
            delete this.serr.images;
            for (const f of files) {
                if (this.images.length >= 5) { this.serr.images='Maks 5 ta rasm'; break; }
                if (f.size > 10*1024*1024)   { this.serr.images=`"${f.name}" 10MB dan katta`; continue; }
                const idx = this.images.length;
                this.images.push(f); this.previews.push('');
                const fr = new FileReader();
                fr.onload = e => { this.previews[idx]=e.target.result; this.previews=[...this.previews]; };
                fr.readAsDataURL(f);
            }
        },
        removeImg(i) { this.images.splice(i,1); this.previews.splice(i,1); this.previews=[...this.previews]; },
        onVidSelect(e) { const f=e.target.files[0]; if(f) this.addVideo(f); e.target.value=''; },
        addVideo(f) {
            delete this.serr.video;
            if (f.size > 50*1024*1024) { this.serr.video='Video 50MB dan katta'; return; }
            this.video   = f;
            this.vidName = f.name;
            const fr = new FileReader();
            fr.onload = e => this.vidPreview = e.target.result;
            fr.readAsDataURL(f);
        },
        removeVideo() { this.video=null; this.vidPreview=null; this.vidName=''; },

        // ── Sell: Validation & Nav ──
        sellValidate() {
            this.serr = {}; this.sellGlobalErr = '';
            if (this.sellStep === 2) {
                let ok = true;
                if (!this.sform.price || +this.sform.price<=0)             { this.serr.price='Narxni kiriting'; ok=false; }
                if (!this.sform.collection_level)                            { this.serr.collection_level='Darajani tanlang'; ok=false; }
                if (!this.sform.heroes_count || +this.sform.heroes_count < 0)   { this.serr.heroes_count = 'Sonni kiriting'; ok=false; }
                else if (+this.sform.heroes_count > 131)                        { this.serr.heroes_count = 'Maksimum 131 ta'; ok=false; }
                if (!this.sform.skins_count  || +this.sform.skins_count < 0)   { this.serr.skins_count  = 'Sonni kiriting'; ok=false; }
                else if (+this.sform.skins_count > 1070)                        { this.serr.skins_count  = 'Maksimum 1070 ta'; ok=false; }
                return ok;
            }
            return true;
        },
        sellNext() {
            if (this.sellValidate()) { this.sellStep++; window.scrollTo(0,0); }
        },
        sellBack() {
            this.sellStep--; this.serr={}; window.scrollTo(0,0);
        },

        // ── Sell: Submit ──
        async submitSell() {
            if (!this.tgId) { this.sellGlobalErr="Telegram ID aniqlanmadi"; return; }
            this.sellLoading=true; this.uploadPct=0; this.sellGlobalErr='';
            const fd = new FormData();
            fd.append('telegram_id',        this.tgId);
            fd.append('price',              this.sform.price);
            fd.append('heroes_count',       this.sform.heroes_count);
            fd.append('skins_count',        this.sform.skins_count);
            fd.append('collection_level',   this.sform.collection_level);
            fd.append('description',        this.sform.description);
            fd.append('ready_for_transfer', this.sform.ready ? '1' : '0');
            this.images.forEach((img,i) => fd.append(`images[${i}]`, img));
            if (this.video) fd.append('video', this.video);
            try {
                await axios.post('/api/accounts', fd, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                    onUploadProgress: ev => { this.uploadPct = ev.total ? Math.round(ev.loaded/ev.total*100) : 50; },
                });
                this.sellStep = 'success';
                this.myLoaded = false;
            } catch(e) {
                const data = e.response?.data;
                if (data?.errors) {
                    const errs = data.errors;
                    if (['price','heroes_count','skins_count','collection_level'].some(k=>errs[k])) this.sellStep=2;
                    Object.keys(errs).forEach(k => { this.serr[k]=errs[k][0]; });
                } else {
                    this.sellGlobalErr = data?.message ?? 'Xatolik yuz berdi';
                }
            } finally {
                this.sellLoading = false;
            }
        },

        resetSellForm() {
            this.sform={price:'',heroes_count:'',skins_count:'',collection_level:'',description:'',ready:false};
            this.images=[]; this.previews=[]; this.video=null; this.vidPreview=null; this.vidName='';
            this.serr={}; this.sellGlobalErr='';
        },
    }
}
</script>
</body>
</html>
