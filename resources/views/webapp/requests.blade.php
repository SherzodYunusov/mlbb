<!DOCTYPE html>
<html lang="uz" class="h-full">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>Akkaunt qidiruv — MLBB Market</title>
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<style>
  :root {
    --bg:      #050514;
    --surface: rgba(255,255,255,.04);
    --border:  rgba(255,255,255,.08);
    --ink:     #e2e8f0;
    --muted:   #7878a8;
    --accent:  #7c3aed;
    --line:    rgba(255,255,255,.07);
  }
  *{box-sizing:border-box;margin:0;padding:0}
  html,body{height:100%;background:var(--bg);color:var(--ink);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;overscroll-behavior:none}

  body::before{
    content:'';position:fixed;inset:0;z-index:0;pointer-events:none;
    background:
      radial-gradient(ellipse 80% 40% at 20% 0%,rgba(124,58,237,.18) 0%,transparent 60%),
      radial-gradient(ellipse 60% 30% at 80% 100%,rgba(59,130,246,.12) 0%,transparent 60%);
  }

  /* Glass */
  .g-card{background:var(--surface);border:1px solid var(--border);border-radius:16px;backdrop-filter:blur(12px)}

  /* Request card */
  .req-card{background:var(--surface);border:1px solid var(--border);border-radius:18px;overflow:hidden;transition:box-shadow .2s}
  .req-card:hover{box-shadow:0 4px 24px rgba(124,58,237,.2)}

  /* Bottom nav */
  .bnav{position:fixed;bottom:0;left:0;right:0;z-index:40;
    background:rgba(5,5,20,.9);backdrop-filter:blur(20px);
    border-top:1px solid var(--line);
    padding-bottom:env(safe-area-inset-bottom,0px)}
  .bnav-btn{display:flex;flex-direction:column;align-items:center;gap:3px;padding:10px 8px 6px;
    flex:1;cursor:pointer;font-size:10px;font-weight:600;color:var(--muted);
    border:none;background:transparent;transition:color .15s;text-decoration:none}
  .bnav-btn.active{color:#a78bfa}
  .bnav-btn svg{transition:filter .15s}
  .bnav-btn.active svg{filter:drop-shadow(0 0 6px rgba(167,139,250,.7))}
  .bnav-dot{width:4px;height:4px;border-radius:50%;background:#a78bfa;margin-top:2px;animation:dotPop .3s ease}
  @keyframes dotPop{from{transform:scale(0)}to{transform:scale(1)}}

  /* Sell pill */
  .sell-pill{
    display:flex;align-items:center;gap:5px;
    padding:10px 18px;border-radius:999px;
    background:linear-gradient(135deg,#7c3aed,#4f46e5);
    color:#fff;font-weight:700;font-size:13px;
    border:none;cursor:pointer;
    box-shadow:0 4px 20px rgba(124,58,237,.5);
    transition:opacity .15s,transform .1s;
    text-decoration:none
  }
  .sell-pill:active{opacity:.85;transform:scale(.97)}

  /* Chips */
  .chip{display:inline-flex;align-items:center;gap:5px;
    padding:6px 14px;border-radius:999px;font-size:12px;font-weight:700;
    border:1px solid transparent;cursor:pointer;transition:all .15s;white-space:nowrap}

  /* Buttons */
  .btn-primary{padding:11px;border-radius:12px;
    background:linear-gradient(135deg,#7c3aed,#6d28d9);
    color:#fff;font-weight:700;font-size:14px;
    border:none;cursor:pointer;width:100%;
    box-shadow:0 4px 16px rgba(124,58,237,.4);transition:opacity .15s}
  .btn-primary:disabled{opacity:.5}
  .btn-ghost{padding:11px;border-radius:12px;
    background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
    color:var(--muted);font-weight:600;font-size:14px;cursor:pointer;width:100%}

  /* Input */
  .inp{width:100%;padding:12px 14px;background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.1);border-radius:12px;
    color:var(--ink);font-size:14px;outline:none;
    transition:border-color .15s,box-shadow .15s}
  .inp:focus{border-color:rgba(124,58,237,.6);box-shadow:0 0 0 3px rgba(124,58,237,.15)}
  .inp::placeholder{color:var(--muted)}
  textarea.inp{resize:none;min-height:100px}

  /* Comment */
  .comment-bubble{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);
    border-radius:12px;padding:10px 12px}

  /* Badge */
  .badge-pending{display:inline-flex;align-items:center;gap:4px;
    padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;
    background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.25);color:#fbbf24}
  .badge-active{display:inline-flex;align-items:center;gap:4px;
    padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;
    background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:#34d399}

  /* Skeleton */
  .skel{background:linear-gradient(90deg,rgba(255,255,255,.04) 25%,rgba(255,255,255,.08) 50%,rgba(255,255,255,.04) 75%);
    background-size:200% 100%;animation:shimmer 1.5s infinite;border-radius:8px}
  @keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}

  /* Bottom sheet */
  .sheet{background:rgba(10,8,35,.97);border-top:1px solid rgba(255,255,255,.1);border-radius:24px 24px 0 0}

  /* Toast */
  .toast{position:fixed;bottom:80px;left:50%;transform:translateX(-50%);
    padding:10px 20px;background:rgba(30,30,60,.95);border:1px solid rgba(255,255,255,.12);
    border-radius:20px;font-size:13px;font-weight:600;backdrop-filter:blur(16px);
    z-index:999;white-space:nowrap}

  ::-webkit-scrollbar{width:3px}
  ::-webkit-scrollbar-thumb{background:rgba(124,58,237,.4);border-radius:4px}
</style>
</head>
<body x-data="app()" x-init="boot()">

<div class="relative z-10 flex flex-col h-screen" style="padding-bottom:calc(68px + env(safe-area-inset-bottom,0px))">

  {{-- ── HEADER ── --}}
  <div class="flex-shrink-0 px-4 pt-safe"
       style="background:rgba(5,5,20,.85);backdrop-filter:blur(20px);border-bottom:1px solid var(--line)">
    <div class="flex items-center justify-between py-3 max-w-2xl mx-auto">
      <div>
        <h1 class="text-base font-extrabold" style="color:#c4b5fd">🔍 Akkaunt qidiruv</h1>
        <p class="text-[11px]" style="color:var(--muted)" x-text="total ? `${total} ta faol so'rov` : 'yuklanmoqda...'"></p>
      </div>
      <button @click="openForm()"
              class="sell-pill text-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M12 5v14M5 12h14"/>
        </svg>
        So'rov berish
      </button>
    </div>
  </div>

  {{-- ── LIST ── --}}
  <main class="flex-1 overflow-y-auto px-4 py-4 max-w-2xl mx-auto w-full">

    {{-- Skeleton --}}
    <div x-show="loading && requests.length === 0" class="space-y-4">
      <template x-for="i in 4" :key="i">
        <div class="g-card p-4 space-y-3">
          <div class="flex items-center gap-3">
            <div class="skel w-9 h-9 rounded-full"></div>
            <div class="flex-1 space-y-2">
              <div class="skel h-3 w-1/3 rounded"></div>
              <div class="skel h-2.5 w-1/4 rounded"></div>
            </div>
          </div>
          <div class="skel h-3 w-full rounded"></div>
          <div class="skel h-3 w-3/4 rounded"></div>
        </div>
      </template>
    </div>

    {{-- Empty --}}
    <div x-show="!loading && requests.length === 0"
         class="flex flex-col items-center justify-center py-24 text-center">
      <div class="text-6xl mb-4">🔍</div>
      <p class="font-bold text-white text-lg">Hali so'rovlar yo'q</p>
      <p class="text-sm mt-1" style="color:var(--muted)">Birinchi bo'lib akkaunt so'roving!</p>
      <button @click="openForm()" class="btn-primary mt-6" style="width:auto;padding:12px 28px">
        + So'rov berish
      </button>
    </div>

    {{-- Request cards --}}
    <div class="space-y-4">
      <template x-for="req in requests" :key="req.id">
        <div class="req-card">

          {{-- Header --}}
          <div class="px-4 pt-4 pb-3 border-b" style="border-color:var(--line)">
            <div class="flex items-start gap-3">

              {{-- Avatar --}}
              <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm"
                   style="background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;border:2px solid rgba(124,58,237,.4)"
                   x-text="(req.poster_name ?? 'U').replace('@','')[0]?.toUpperCase() ?? 'U'">
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="badge-active">✅ Faol</span>
                </div>
                <p class="text-[11px] mt-0.5" style="color:var(--muted)" x-text="timeAgo(req.created_at)"></p>
              </div>

              {{-- Close own request --}}
              <template x-if="tgId && req.poster_tg_id == tgId">
                <button @click="closeRequest(req.id)"
                        class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg"
                        style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.2);color:#f87171">
                  Yopish
                </button>
              </template>
            </div>
          </div>

          {{-- Description --}}
          <div class="px-4 py-3 space-y-3">

            <p class="text-sm leading-relaxed" style="color:var(--ink)" x-text="req.description"></p>

            {{-- Budget & Contact --}}
            <div class="flex flex-wrap gap-2">
              <template x-if="req.budget_min || req.budget_max">
                <span class="chip" style="background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.25);color:#34d399">
                  💰 <span x-text="budgetText(req)"></span>
                </span>
              </template>
            </div>

            {{-- Reply button — everyone can open comments --}}
            <button @click="openComment(req)"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold"
                    style="background:rgba(124,58,237,.12);border:1px solid rgba(124,58,237,.3);color:#a78bfa">
              <span x-text="req.poster_tg_id == tgId ? '💬 Kommentlar' : '💬 Javob yozish'"></span>
              <span x-show="req.comments_count > 0"
                    class="ml-1 text-[11px] opacity-60"
                    x-text="`(${req.comments_count})`"></span>
            </button>
          </div>

          {{-- Comments --}}
          <div x-show="req._commentsOpen" class="border-t px-4 pb-4 pt-3 space-y-3" style="border-color:var(--line)">

            {{-- Loading comments --}}
            <div x-show="req._commentsLoading" class="flex justify-center py-3">
              <div style="width:24px;height:24px;border:2px solid rgba(255,255,255,.15);border-top-color:#a78bfa;border-radius:50%;animation:spin .75s linear infinite"></div>
            </div>

            {{-- Comment list --}}
            <template x-for="c in (req._comments ?? [])" :key="c.id">
              <div class="comment-bubble">
                <div class="flex items-center gap-2 mb-1.5">
                  <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold flex-shrink-0"
                       :style="`background:${c.is_poster ? 'rgba(124,58,237,.4)' : 'rgba(59,130,246,.3)'};color:#fff`"
                       x-text="c.sender.initials"></div>

                  {{-- Nom → web profil (Telegram emas) --}}
                  <template x-if="c.sender.tg_id && !c.is_poster">
                    <a :href="`/profile/${c.sender.tg_id}?viewer_id=${tgId}`"
                       target="_blank"
                       class="text-xs font-semibold"
                       :style="`color:#93c5fd;text-decoration:none`"
                       x-text="c.sender.name"></a>
                  </template>
                  <template x-if="c.is_poster">
                    <span class="text-xs font-semibold" style="color:#a78bfa"
                          x-text="c.sender.name + ' 👤'"></span>
                  </template>

                  <span class="text-[10px] ml-auto" style="color:var(--muted)" x-text="timeAgo(c.created_at)"></span>
                  <template x-if="c.can_delete">
                    <button @click="deleteComment(req, c.id)"
                            class="text-[10px] ml-1"
                            style="color:rgba(239,68,68,.6)">✕</button>
                  </template>
                </div>
                <p class="text-sm leading-relaxed" style="color:var(--ink)" x-text="c.message"></p>
              </div>
            </template>

            {{-- Reply input --}}
            <template x-if="tgId">
              <div class="flex gap-2 mt-1">
                <input x-model="req._replyText"
                       @keydown.enter.prevent="submitComment(req)"
                       class="inp flex-1 py-2.5 px-3 text-sm"
                       placeholder="Javob yozing..."
                       style="border-radius:10px">
                <button @click="submitComment(req)"
                        :disabled="!(req._replyText?.trim())"
                        class="px-3 py-2 rounded-xl font-bold text-sm"
                        style="background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;border:none;cursor:pointer;transition:opacity .15s"
                        :style="!(req._replyText?.trim()) ? 'opacity:.4' : 'opacity:1'">
                  ➤
                </button>
              </div>
            </template>
            <template x-if="!tgId">
              <p class="text-xs text-center py-2" style="color:var(--muted)">
                Javob yozish uchun Telegram orqali kiring
              </p>
            </template>
          </div>

        </div>
      </template>
    </div>

    {{-- Load more --}}
    <div x-show="hasMore && !loading" class="flex justify-center py-5">
      <button @click="loadMore()"
              class="btn-ghost"
              style="width:180px;font-size:13px"
              :disabled="loadingMore">
        <span x-show="!loadingMore">Yana ko'rsatish ↓</span>
        <span x-show="loadingMore">Yuklanmoqda...</span>
      </button>
    </div>

  </main>
</div>

{{-- ── BOTTOM NAV ── --}}
<nav class="bnav">
  <div class="flex items-center justify-around max-w-2xl mx-auto px-2 py-1">
    <a href="/webapp" class="bnav-btn">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M3 3h7v7H3zm0 11h7v7H3zm11-11h7v7h-7zm0 11h7v7h-7z"/>
      </svg>
      Bozor
    </a>

    <a href="/webapp/requests" class="bnav-btn active">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
      </svg>
      Qidiruv
      <div class="bnav-dot"></div>
    </a>

    <a href="/webapp?tab=sell"
       class="bnav-btn sell-pill-btn">
      <div class="w-8 h-8 rounded-2xl flex items-center justify-center"
           style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1)">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
      </div>
      <span>Sotish</span>
    </a>

    <a :href="tgId ? `/profile/${tgId}?viewer_id=${tgId}` : '/webapp'" class="bnav-btn">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
      </svg>
      Profil
    </a>
  </div>
</nav>

{{-- ── NEW REQUEST BOTTOM SHEET ── --}}
<div x-show="formOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-end justify-center"
     style="background:rgba(5,5,20,.75);backdrop-filter:blur(6px)"
     @click.self="formOpen=false"
     style="display:none">

  <div class="sheet w-full max-w-2xl"
       x-transition:enter="transition ease-out duration-250"
       x-transition:enter-start="transform translate-y-full"
       x-transition:enter-end="transform translate-y-0">

    <div class="p-5 space-y-4" style="max-height:90vh;overflow-y:auto">

      {{-- Handle --}}
      <div class="w-10 h-1 rounded-full mx-auto mb-2" style="background:rgba(255,255,255,.15)"></div>

      <div class="flex items-center justify-between">
        <h2 class="text-lg font-extrabold text-white">🔍 Akkaunt so'rovi</h2>
        <button @click="formOpen=false" class="w-8 h-8 rounded-full flex items-center justify-center"
                style="background:rgba(255,255,255,.08);color:var(--muted)">✕</button>
      </div>

      <p class="text-sm" style="color:var(--muted)">Qanday akkaunt qidiriyotganingizni yozing. Sotuvchilar javob yozadi.</p>

      {{-- Description --}}
      <div>
        <label class="block text-xs font-semibold mb-1.5" style="color:var(--muted)">
          Tavsif <span style="color:#f87171">*</span>
        </label>
        <textarea x-model="form.description"
                  class="inp"
                  placeholder="Masalan: Rank 6+ akkaunt kerak, minimum 500 ta skin, transferga tayyor bo'lsin..."
                  maxlength="1000"></textarea>
        <p class="text-right text-[10px] mt-1" style="color:var(--muted)"
           x-text="`${form.description.length}/1000`"></p>
        <p x-show="ferr.description" class="text-xs mt-1" style="color:#f87171" x-text="ferr.description"></p>
      </div>

      {{-- Budget --}}
      <div>
        <label class="block text-xs font-semibold mb-1.5" style="color:var(--muted)">Byudjet (so'm)</label>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <input x-model="form.budget_min" type="number" min="0"
                   class="inp" placeholder="Min narx">
          </div>
          <div>
            <input x-model="form.budget_max" type="number" min="0"
                   class="inp" placeholder="Max narx">
          </div>
        </div>
      </div>

      {{-- Contact — hidden, auto-populated from tgUser.username --}}
      <input type="hidden" x-model="form.contact">

      {{-- Error --}}
      <div x-show="ferr.general"
           class="p-3 rounded-xl text-sm text-center"
           style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#f87171"
           x-text="ferr.general"></div>

      {{-- Buttons --}}
      <div class="flex gap-3 pb-2">
        <button @click="formOpen=false" class="btn-ghost" style="flex:1">Bekor</button>
        <button @click="submitForm()"
                :disabled="fLoading || !form.description.trim()"
                class="btn-primary"
                style="flex:2">
          <template x-if="!fLoading"><span>📨 Yuborish</span></template>
          <template x-if="fLoading">
            <span class="flex items-center justify-center gap-2">
              <svg class="w-4 h-4" style="animation:spin .75s linear infinite" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>Yuborilmoqda...
            </span>
          </template>
        </button>
      </div>

    </div>
  </div>
</div>

{{-- ── TOAST ── --}}
<div x-show="toast.show"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-end="opacity-0"
     class="toast" x-text="toast.msg" style="display:none"></div>

<style>
@keyframes spin{to{transform:rotate(360deg)}}
</style>

<script>
const BOT_USERNAME = '{{ $botUsername }}';

function app() {
    return {
        tgId:    null,
        tgUser:  null,
        requests: [],
        loading:  true,
        loadingMore: false,
        hasMore:  false,
        total:    0,
        page:     1,

        formOpen: false,
        fLoading: false,
        form: { description: '', budget_min: '', budget_max: '', contact: '' },
        ferr: {},

        toast: { show: false, msg: '' },

        async boot() {
            if (window.Telegram?.WebApp) {
                Telegram.WebApp.ready();
                Telegram.WebApp.expand();
            }

            // 1. localStorage — marketplace da login bo'lgan bo'lsa (mlbb_user kalit)
            try {
                const saved = localStorage.getItem('mlbb_user');
                if (saved) {
                    const d = JSON.parse(saved);
                    if (d?.tgId)   this.tgId   = d.tgId;
                    if (d?.tgUser) this.tgUser = d.tgUser;
                }
            } catch {}

            // 2. URL token (bot /start havolasidan kelgan bo'lsa)
            const urlParams = new URLSearchParams(location.search);
            const token     = urlParams.get('token');
            if (token) {
                try {
                    const r = await axios.post('/api/auth/verify', { token });
                    const id = r.data?.tg_id ?? r.data?.telegram_id;
                    if (id) {
                        this.tgId   = id;
                        this.tgUser = { first_name: r.data?.first_name, username: r.data?.username };
                        localStorage.setItem('mlbb_user', JSON.stringify({
                            tgId:   this.tgId,
                            tgUser: this.tgUser,
                        }));
                    }
                } catch {}
                history.replaceState({}, '', location.pathname);
            }

            await this.loadRequests(1);
        },

        async loadRequests(page = 1) {
            if (page === 1) { this.loading = true; this.requests = []; }
            else this.loadingMore = true;

            try {
                const { data } = await axios.get('/api/account-requests', { params: { page } });
                const items = (data.data ?? []).map(r => ({
                    ...r,
                    _commentsOpen:   false,
                    _commentsLoading: false,
                    _comments:       [],
                    _replyText:      '',
                }));
                if (page === 1) this.requests = items;
                else this.requests.push(...items);

                this.hasMore  = data.has_more;
                this.total    = data.total;
                this.page     = page;
            } catch {}

            this.loading     = false;
            this.loadingMore = false;
        },

        async loadMore() {
            await this.loadRequests(this.page + 1);
        },

        // ── Form ──
        openForm() {
            if (!this.tgId) {
                this.showToast('❗ Telegram orqali kiring');
                return;
            }
            // Username avtomatik to'ldiriladi
            const username = this.tgUser?.username ?? '';
            this.form = { description: '', budget_min: '', budget_max: '', contact: username };
            this.ferr = {};
            this.formOpen = true;
        },

        async submitForm() {
            this.ferr = {};
            if (!this.form.description.trim()) {
                this.ferr.description = 'Tavsif kiritilishi shart';
                return;
            }
            if (this.form.description.trim().length < 10) {
                this.ferr.description = 'Kamida 10 ta belgi kiriting';
                return;
            }

            this.fLoading = true;
            try {
                await axios.post('/api/account-requests', {
                    telegram_id:  this.tgId,
                    description:  this.form.description.trim(),
                    budget_min:   this.form.budget_min || null,
                    budget_max:   this.form.budget_max || null,
                    contact:      this.form.contact.replace('@', '') || null,
                });

                this.formOpen = false;
                this.showToast('✅ So\'rov yuborildi! Admin tasdiqlaydi.');
            } catch (e) {
                this.ferr.general = e.response?.data?.message ?? 'Xato yuz berdi';
            }
            this.fLoading = false;
        },

        // ── Close request ──
        async closeRequest(id) {
            if (!confirm('Bu so\'rovni yopishni tasdiqlaysizmi?')) return;
            try {
                await axios.post(`/api/account-requests/${id}/close`, { telegram_id: this.tgId });
                this.requests = this.requests.filter(r => r.id !== id);
                this.showToast('So\'rov yopildi');
            } catch {}
        },

        // ── Comments ──
        async openComment(req) {
            req._commentsOpen = !req._commentsOpen;
            if (req._commentsOpen && req._comments.length === 0) {
                await this.loadComments(req);
            }
        },

        async loadComments(req) {
            req._commentsLoading = true;
            try {
                const { data } = await axios.get(`/api/account-requests/${req.id}/comments`, {
                    params: { telegram_id: this.tgId ?? undefined },
                });
                req._comments = data.data ?? [];
            } catch {}
            req._commentsLoading = false;
        },

        async submitComment(req) {
            const msg = (req._replyText ?? '').trim();
            if (!msg || !this.tgId) return;

            try {
                const { data } = await axios.post(`/api/account-requests/${req.id}/comments`, {
                    telegram_id: this.tgId,
                    message:     msg,
                });
                req._comments.unshift(data.comment);
                req.comments_count = (req.comments_count ?? 0) + 1;
                req._replyText = '';
            } catch (e) {
                this.showToast('❗ ' + (e.response?.data?.message ?? 'Xato'));
            }
        },

        async deleteComment(req, commentId) {
            try {
                await axios.delete(`/api/account-request-comments/${commentId}`, {
                    data: { telegram_id: this.tgId },
                });
                req._comments = req._comments.filter(c => c.id !== commentId);
                req.comments_count = Math.max(0, (req.comments_count ?? 1) - 1);
            } catch (e) {
                this.showToast('❗ ' + (e.response?.data?.message ?? 'Xato'));
            }
        },

        // ── Helpers ──
        budgetText(req) {
            const fmt = v => Number(v).toLocaleString('ru-RU');
            if (req.budget_min && req.budget_max) return `${fmt(req.budget_min)} – ${fmt(req.budget_max)} so'm`;
            if (req.budget_max) return `max ${fmt(req.budget_max)} so'm`;
            if (req.budget_min) return `min ${fmt(req.budget_min)} so'm`;
            return '';
        },

        timeAgo(iso) {
            if (!iso) return '';
            const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
            if (diff < 60)   return `${diff}s oldin`;
            if (diff < 3600) return `${Math.floor(diff/60)}m oldin`;
            if (diff < 86400)return `${Math.floor(diff/3600)}s oldin`;
            return `${Math.floor(diff/86400)}k oldin`;
        },

        showToast(msg, ms = 2500) {
            this.toast = { show: true, msg };
            setTimeout(() => this.toast.show = false, ms);
        },
    };
}
</script>
</body>
</html>
