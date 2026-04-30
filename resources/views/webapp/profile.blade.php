<!DOCTYPE html>
<html lang="uz" class="h-full">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>{{ $user->username ? '@'.$user->username : $user->first_name }} — Profil</title>
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<style>
  :root {
    --bg:      #050514;
    --surface: rgba(255,255,255,.04);
    --border:  rgba(255,255,255,.08);
    --ink:     #e2e8f0;
    --muted:   #7878a8;
    --accent:  #7c3aed;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  html, body { height: 100%; background: var(--bg); color: var(--ink); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

  /* Bg mesh */
  body::before {
    content: '';
    position: fixed; inset: 0; z-index: 0;
    background:
      radial-gradient(ellipse 80% 40% at 20% 0%,   rgba(124,58,237,.18) 0%, transparent 60%),
      radial-gradient(ellipse 60% 30% at 80% 100%,  rgba(59,130,246,.12) 0%, transparent 60%);
    pointer-events: none;
  }

  /* Glass card */
  .g-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
  }

  /* Account card */
  .acc-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
  }
  .acc-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 32px rgba(124,58,237,.25);
  }

  /* Image zone */
  .img-zone {
    position: relative;
    height: 164px;
    background: linear-gradient(135deg, rgba(124,58,237,.15), rgba(59,130,246,.1));
  }
  .img-zone img.main-img {
    width: 100%; height: 100%; object-fit: cover; display: block;
  }
  .img-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(5,5,20,.9) 0%, rgba(5,5,20,.2) 50%, transparent 100%);
  }

  /* Stat pill */
  .stat-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 8px;
    background: rgba(0,0,0,.55);
    backdrop-filter: blur(8px);
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    color: #e2e8f0;
    border: 1px solid rgba(255,255,255,.12);
  }

  /* Rank badge small */
  .rank-sm {
    width: 32px; height: 32px;
    background: rgba(0,0,0,.5);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid rgba(255,255,255,.1);
    overflow: hidden;
    flex-shrink: 0;
  }

  /* NEW badge */
  .badge-new {
    animation: pulse-green 2s infinite;
  }
  @keyframes pulse-green {
    0%, 100% { box-shadow: 0 0 6px rgba(16,185,129,.5); }
    50%       { box-shadow: 0 0 14px rgba(16,185,129,.9); }
  }

  /* Buttons */
  .btn-buy {
    width: 100%;
    padding: 10px;
    border-radius: 12px;
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: opacity .15s, transform .1s;
    box-shadow: 0 4px 16px rgba(124,58,237,.4);
  }
  .btn-buy:active { opacity: .85; transform: scale(.98); }

  .btn-edit {
    flex: 1;
    padding: 8px;
    border-radius: 10px;
    background: rgba(59,130,246,.15);
    color: #60a5fa;
    font-weight: 600;
    font-size: 13px;
    border: 1px solid rgba(59,130,246,.3);
    cursor: pointer;
    transition: background .15s;
  }
  .btn-edit:hover { background: rgba(59,130,246,.25); }

  .btn-delete {
    flex: 1;
    padding: 8px;
    border-radius: 10px;
    background: rgba(239,68,68,.12);
    color: #f87171;
    font-weight: 600;
    font-size: 13px;
    border: 1px solid rgba(239,68,68,.25);
    cursor: pointer;
    transition: background .15s;
  }
  .btn-delete:hover { background: rgba(239,68,68,.22); }

  /* Back button */
  .btn-back {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: var(--muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: color .15s, border-color .15s;
    text-decoration: none;
  }
  .btn-back:hover { color: var(--ink); border-color: rgba(255,255,255,.2); }

  /* Avatar */
  .avatar {
    width: 72px; height: 72px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; font-weight: 800;
    flex-shrink: 0;
    border: 3px solid rgba(124,58,237,.5);
    box-shadow: 0 0 24px rgba(124,58,237,.3);
  }

  /* Toast */
  .toast {
    position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
    padding: 10px 20px;
    background: rgba(30,30,60,.95);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 20px;
    font-size: 13px; font-weight: 600;
    backdrop-filter: blur(16px);
    z-index: 999;
    transition: opacity .3s;
    white-space: nowrap;
  }

  /* Scrollbar */
  ::-webkit-scrollbar { width: 4px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: rgba(124,58,237,.4); border-radius: 4px; }
</style>
</head>
<body x-data="profileApp()" x-init="init()">

<div class="relative z-10 min-h-screen">

  {{-- ── HEADER ── --}}
  <div class="sticky top-0 z-40 px-4 py-3"
       style="background:rgba(5,5,20,.85);backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,255,255,.06)">
    <div class="max-w-2xl mx-auto flex items-center justify-between gap-3">
      <a href="/webapp" class="btn-back">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Bozor
      </a>
      <span class="text-xs text-[var(--muted)] font-medium">Foydalanuvchi profili</span>
      <div class="w-16"></div>
    </div>
  </div>

  <div class="max-w-2xl mx-auto px-4 pb-10">

    {{-- ── USER CARD ── --}}
    <div class="g-card mt-5 p-5">
      <div class="flex items-center gap-4">
        {{-- Avatar --}}
        <div class="avatar" :style="`background:linear-gradient(135deg,${avatarColor},${avatarColor2})`">
          <span x-text="initials"></span>
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
          <h1 class="text-xl font-bold text-white truncate" x-text="displayName"></h1>
          @if($user->username)
          <p class="text-sm text-[var(--muted)] mt-0.5">@{{ '@' }}{{ $user->username }}</p>
          @endif
          <div class="flex flex-wrap gap-2 mt-3">
            <span class="stat-pill" style="background:rgba(124,58,237,.2);border-color:rgba(124,58,237,.3);color:#a78bfa">
              <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-.293.707L13 10.414V17a1 1 0 01-.553.894l-4 2A1 1 0 017 19v-8.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
              {{ $accounts->count() }} ta e'lon
            </span>
            @if($isOwner)
            <span class="stat-pill" style="background:rgba(16,185,129,.15);border-color:rgba(16,185,129,.3);color:#34d399">
              <svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
              Mening profilim
            </span>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- ── ACCOUNTS ── --}}
    @if($accounts->isEmpty())
    <div class="g-card mt-5 py-16 text-center">
      <div class="text-5xl mb-4">🎮</div>
      <p class="text-[var(--muted)] text-sm">Hozircha faol e'lonlar yo'q</p>
    </div>
    @else

    <h2 class="text-sm font-semibold text-[var(--muted)] uppercase tracking-wider mt-6 mb-3 px-1">
      Faol e'lonlar ({{ $accounts->count() }})
    </h2>

    <div class="grid grid-cols-2 gap-3">
      @foreach($accounts as $account)
      @php
        $thumb = $account->media->where('type','thumbnail')->first()
              ?? $account->media->where('type','image')->first();
        $imgUrl = $thumb ? Storage::url($thumb->file_id) : null;
        $isNew  = $account->created_at->diffInHours(now()) < 48;
        $price  = number_format($account->price, 0, '.', ' ');
        $lvInfo = collect([
          ['ru'=>'Коллекционер-любитель',   'num'=>1,'color'=>'#7B8FA1','img'=>'/images/ranks/rank-1.png?v=2'],
          ['ru'=>'Младший коллекционер',    'num'=>2,'color'=>'#3DAA7A','img'=>'/images/ranks/rank-2.png?v=2'],
          ['ru'=>'Опытный коллекционер',    'num'=>3,'color'=>'#29B6C5','img'=>'/images/ranks/rank-3.png?v=2'],
          ['ru'=>'Коллекционер-эксперт',    'num'=>4,'color'=>'#5C6BC0','img'=>'/images/ranks/rank-4.png?v=2'],
          ['ru'=>'Знаменитый коллекционер', 'num'=>5,'color'=>'#9C27B0','img'=>'/images/ranks/rank-5.png?v=2'],
          ['ru'=>'Коллекционер-гуру',       'num'=>6,'color'=>'#D81B9A','img'=>'/images/ranks/rank-6.png?v=2'],
          ['ru'=>'Мегаколлекционер',        'num'=>7,'color'=>'#E65100','img'=>'/images/ranks/rank-7.png?v=2'],
          ['ru'=>'Мировой коллекционер',    'num'=>8,'color'=>'#C62828','img'=>'/images/ranks/rank-8.png?v=2'],
        ])->firstWhere('ru', $account->collection_level);
      @endphp
      <div class="acc-card">

        {{-- Image --}}
        <div class="img-zone">
          @if($imgUrl)
            <img src="{{ $imgUrl }}" alt="" class="main-img">
          @else
            <div class="w-full h-full flex items-center justify-content-center"
                 style="background:linear-gradient(135deg,rgba(124,58,237,.2),rgba(59,130,246,.1))">
              <span class="text-4xl mx-auto block text-center leading-[164px]">🎮</span>
            </div>
          @endif
          <div class="img-overlay"></div>

          {{-- Rank badge --}}
          <div class="absolute top-2 left-2 z-10">
            @if($lvInfo)
              <img src="{{ $lvInfo['img'] }}" alt="" class="w-8 h-8 object-contain"
                   style="filter:drop-shadow(0 2px 6px rgba(0,0,0,.6))">
            @else
              <span class="rank-sm text-xs font-bold text-white"
                    style="background:rgba(124,58,237,.4)">🎮</span>
            @endif
          </div>

          {{-- NEW badge --}}
          @if($isNew)
          <div class="absolute top-2 right-2 z-10">
            <span class="badge-new text-[9px] font-black px-2 py-0.5 rounded-full text-white"
                  style="background:linear-gradient(135deg,#10b981,#059669)">NEW</span>
          </div>
          @endif

          {{-- Bottom stats --}}
          <div class="absolute bottom-2 left-2 right-2 z-10 flex gap-1.5">
            <span class="stat-pill">⚔️ {{ $account->heroes_count }}</span>
            <span class="stat-pill">🎨 {{ $account->skins_count }}</span>
            @if($account->views > 0)
            <span class="stat-pill">👁 {{ $account->views }}</span>
            @endif
          </div>
        </div>

        {{-- Body --}}
        <div class="p-3 space-y-2.5">
          <div>
            <p class="text-[15px] font-bold text-white">{{ $price }} so'm</p>
            <p class="text-[11px] text-[var(--muted)] mt-0.5 truncate">{{ $account->collection_level }}</p>
          </div>

          @if($account->ready_for_transfer)
          <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-400"
                style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);border-radius:6px;padding:2px 7px">
            ✅ Transfer tayyor
          </span>
          @endif

          {{-- Buttons --}}
          @if($isOwner)
          <div class="flex gap-2">
            <a href="/webapp?edit={{ $account->id }}&viewer={{ request('viewer_id') }}"
               class="btn-edit text-center no-underline">✏️ Tahrir</a>
            <button class="btn-delete" onclick="confirmDelete({{ $account->id }}, '{{ $price }}')">
              🗑 O'chir
            </button>
          </div>
          @else
          <button class="btn-buy" onclick="buyAccount({{ $account->id }}, '{{ $botUsername }}')">
            🛒 Sotib olish
          </button>
          @endif
        </div>
      </div>
      @endforeach
    </div>
    @endif

  </div>{{-- /max-w --}}
</div>{{-- /z-10 --}}

{{-- Toast --}}
<div x-show="toast.show" x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-end="opacity-0"
     class="toast" x-text="toast.msg" style="display:none"></div>

{{-- Delete confirm modal --}}
<div id="deleteModal" style="display:none;position:fixed;inset:0;z-index:50;background:rgba(5,5,20,.8);backdrop-filter:blur(8px)"
     class="flex items-end justify-center pb-6 px-4">
  <div class="g-card w-full max-w-sm p-5 space-y-4">
    <div class="text-center">
      <div class="text-4xl mb-2">🗑️</div>
      <p class="font-bold text-white text-lg">E'lonni o'chirish</p>
      <p class="text-sm text-[var(--muted)] mt-1" id="deleteModalText"></p>
    </div>
    <div class="flex gap-3">
      <button onclick="closeDeleteModal()"
              class="flex-1 py-3 rounded-xl font-semibold text-sm"
              style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#94a3b8">
        Bekor
      </button>
      <button id="confirmDeleteBtn"
              class="flex-1 py-3 rounded-xl font-bold text-sm text-white"
              style="background:linear-gradient(135deg,#ef4444,#dc2626)">
        O'chirish
      </button>
    </div>
  </div>
</div>

<script>
const TELEGRAM_ID = {{ $user->telegram_id }};
const VIEWER_ID   = {{ request('viewer_id', 0) }};
const BOT_USERNAME = '{{ $botUsername }}';

// ── Alpine data ──
function profileApp() {
    return {
        toast: { show: false, msg: '' },
        displayName: '{{ addslashes($user->username ? "@{$user->username}" : $user->first_name) }}',
        initials: '{{ mb_strtoupper(mb_substr($user->first_name ?? "U", 0, 1)) }}',
        avatarColor:  '#7c3aed',
        avatarColor2: '#4f46e5',

        init() {
            // Telegram WebApp init
            if (window.Telegram?.WebApp) {
                Telegram.WebApp.ready();
                const tg = Telegram.WebApp;
                if (tg.colorScheme === 'dark' || true) {
                    // already dark
                }
            }
        },

        showToast(msg, ms = 2500) {
            this.toast = { show: true, msg };
            setTimeout(() => this.toast.show = false, ms);
        },
    };
}

// ── Buy ──
function buyAccount(accountId, botUsername) {
    if (!VIEWER_ID) {
        // Telegram bot orqali
        const url = `https://t.me/${botUsername}?start=acc_${accountId}`;
        if (window.Telegram?.WebApp) {
            Telegram.WebApp.openTelegramLink(url);
        } else {
            window.open(url, '_blank');
        }
        return;
    }

    // API orqali deal yaratish
    fetch(`/api/buy/${accountId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ telegram_id: VIEWER_ID }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Alpine.store && document.querySelector('[x-data]').__x.$data.showToast('✅ ' + data.message);
        } else {
            alert(data.error || 'Xato yuz berdi');
        }
    })
    .catch(() => alert('Tarmoq xatosi'));
}

// ── Delete ──
let _deleteId = null;

function confirmDelete(accountId, price) {
    _deleteId = accountId;
    document.getElementById('deleteModalText').textContent = `${price} so'mlik e'lonni o'chirasizmi?`;
    document.getElementById('deleteModal').style.display = 'flex';
    document.getElementById('confirmDeleteBtn').onclick = doDelete;
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    _deleteId = null;
}

function doDelete() {
    if (!_deleteId || !VIEWER_ID) return;
    const btn = document.getElementById('confirmDeleteBtn');
    btn.textContent = 'O\'chirilmoqda...';
    btn.disabled = true;

    fetch(`/api/accounts/${_deleteId}`, {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ telegram_id: VIEWER_ID }),
    })
    .then(r => r.json())
    .then(data => {
        closeDeleteModal();
        if (data.success) {
            // Kartani sahifadan olib tashlash
            document.querySelectorAll('.acc-card').forEach(card => {
                if (card.querySelector(`[onclick*="${_deleteId}"]`)) {
                    card.remove();
                }
            });
            location.reload();
        } else {
            alert(data.message || 'O\'chirishda xato');
        }
    })
    .catch(() => { btn.textContent = 'O\'chirish'; btn.disabled = false; });
}
</script>

</body>
</html>
