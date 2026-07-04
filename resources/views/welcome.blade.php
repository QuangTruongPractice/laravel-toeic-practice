<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ config('app.name') }} — Đăng nhập / Đăng ký</title>
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="alternate icon" href="/favicon.ico">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#FFFFFF;
    --ink:#211E3D;
    --ink-soft:#6B6890;
    --indigo:#4F46E5;
    --indigo-dark:#4338CA;
    --blue:#3B82F6;
    --violet:#8B5CF6;
    --lavender:#F5F3FF;
    --lavender-2:#EEF2FF;
    --amber:#FBBF24;
    --line:#E7E5F5;
    --danger:#EF4444;
    --success:#10B981;
    --radius:20px;
    --radius-sm:12px;
    --shadow-soft:0 20px 45px -20px rgba(79,70,229,.25);
  }
  *{box-sizing:border-box; margin:0; padding:0;}
  html,body{height:100%;}
  body{
    font-family:'Inter',sans-serif;
    background:var(--bg);
    color:var(--ink);
    min-height:100vh;
    overflow-x:hidden;
    -webkit-font-smoothing:antialiased;
  }
  h1,h2,h3,.display{font-family:'Sora',sans-serif;}
  .mono{font-family:'JetBrains Mono',monospace;}

  /* ---------- layout ---------- */
  .page{
    display:grid;
    grid-template-columns: 1.05fr 1fr;
    min-height:100vh;
  }
  @media (max-width: 940px){
    .page{grid-template-columns:1fr;}
  }

  /* ---------- LEFT: motivation / illustration panel ---------- */
  .panel{
    position:relative;
    background:
      radial-gradient(120% 140% at 15% 10%, #EEF2FF 0%, transparent 55%),
      linear-gradient(160deg, #F7F6FF 0%, #FFFFFF 55%);
    overflow:hidden;
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    padding:52px 56px;
    border-right:1px solid var(--line);
  }
  @media (max-width: 940px){
    .panel{
      padding:36px 28px 44px;
      min-height:340px;
      border-right:none;
      border-bottom:1px solid var(--line);
    }
  }

  .blob{
    position:absolute;
    border-radius:50%;
    filter:blur(50px);
    opacity:.55;
    animation:drift 16s ease-in-out infinite;
  }
  .blob1{width:340px;height:340px; background:radial-gradient(circle, #C7D2FE, transparent 70%); top:-90px; left:-90px; animation-duration:18s;}
  .blob2{width:280px;height:280px; background:radial-gradient(circle, #DDD6FE, transparent 70%); bottom:-60px; right:-60px; animation-duration:22s; animation-delay:-4s;}
  .blob3{width:200px;height:200px; background:radial-gradient(circle, #BFDBFE, transparent 70%); bottom:30%; left:8%; animation-duration:14s; animation-delay:-8s;}
  @keyframes drift{
    0%,100%{ transform:translate(0,0) scale(1); }
    50%{ transform:translate(18px,-22px) scale(1.06); }
  }

  .particle{
    position:absolute;
    border-radius:50%;
    background:var(--indigo);
    opacity:.28;
    animation:float-p 9s ease-in-out infinite;
  }
  @keyframes float-p{
    0%,100%{ transform:translateY(0); opacity:.18; }
    50%{ transform:translateY(-24px); opacity:.4; }
  }

  .brand-mark{
    position:relative; z-index:2;
    display:flex; align-items:center; gap:10px;
  }
  .brand-mark .logo-badge{
    width:38px; height:38px; border-radius:11px;
    background:linear-gradient(135deg, var(--indigo), var(--blue));
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 8px 18px -6px rgba(79,70,229,.55);
  }
  .brand-mark .logo-badge svg{width:20px; height:20px;}
  .brand-mark span{font-weight:700; font-size:19px; letter-spacing:-0.01em;}

  /* signature: orbiting achievement ring around mascot */
  .stage{
    position:relative; z-index:2;
    width:100%; max-width:400px;
    margin:18px auto;
    aspect-ratio:1/1;
    display:flex; align-items:center; justify-content:center;
  }
  @media (max-width:940px){ .stage{ max-width:230px; margin:6px auto;} }

  .orbit-ring{
    position:absolute; inset:6%;
    border:1.5px dashed #D7D3F5;
    border-radius:50%;
    animation:spin 34s linear infinite;
  }
  .orbit-item{
    position:absolute; top:50%; left:50%;
    width:46px; height:46px;
    margin:-23px 0 0 -23px;
    display:flex; align-items:center; justify-content:center;
    border-radius:50%;
    background:#fff;
    box-shadow:0 10px 22px -8px rgba(79,70,229,.35);
  }
  .orbit-item svg{width:20px;height:20px;}
  .orbit-item.i1{ transform: rotate(0deg) translate(158px) rotate(0deg); animation: counter-spin 34s linear infinite; }
  .orbit-item.i2{ transform: rotate(90deg) translate(158px) rotate(-90deg); animation: counter-spin 34s linear infinite; }
  .orbit-item.i3{ transform: rotate(180deg) translate(158px) rotate(-180deg); animation: counter-spin 34s linear infinite; }
  .orbit-item.i4{ transform: rotate(270deg) translate(158px) rotate(-270deg); animation: counter-spin 34s linear infinite; }
  @media (max-width:940px){
    .orbit-item.i1,.orbit-item.i2,.orbit-item.i3,.orbit-item.i4{ }
  }
  @keyframes spin{ to{ transform:rotate(360deg); } }
  /* counter rotate wrapper so icons stay upright while orbiting */
  .orbit-wrap{ position:absolute; inset:0; animation:spin 34s linear infinite; }
  .orbit-wrap .orbit-item{ animation:counter-spin 34s linear infinite; }
  @keyframes counter-spin{ to{ transform:rotate(-360deg) translate(0); } }

  .mascot{ position:relative; z-index:3; width:44%; }
  .mascot svg{ width:100%; height:auto; filter:drop-shadow(0 20px 30px rgba(79,70,229,.18)); }

  .streak-chip{
    position:absolute; z-index:4; top:8%; right:2%;
    background:#fff; border:1px solid var(--line);
    border-radius:14px; padding:8px 12px;
    display:flex; align-items:center; gap:8px;
    box-shadow:var(--shadow-soft);
    animation:bob 4.5s ease-in-out infinite;
  }
  @media (max-width:940px){ .streak-chip{ top:0; right:0; padding:6px 9px; } }
  @keyframes bob{ 0%,100%{transform:translateY(0);} 50%{transform:translateY(-6px);} }
  .streak-chip .fire{ width:18px; height:18px; }
  .streak-chip .val{ font-size:13px; font-weight:700; }
  .streak-chip .lbl{ font-size:9px; color:var(--ink-soft); letter-spacing:.06em; }

  .panel-copy{ position:relative; z-index:2; text-align:center; max-width:380px; margin:0 auto; }
  .panel-copy .eyebrow{
    display:inline-flex; align-items:center; gap:6px;
    font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase;
    color:var(--indigo); background:var(--lavender-2);
    padding:6px 12px; border-radius:999px; margin-bottom:14px;
  }
  .panel-copy h1{
    font-size:26px; line-height:1.3; font-weight:700; color:var(--ink);
  }
  .panel-copy h1 .accent{
    background:linear-gradient(90deg, var(--indigo), var(--blue));
    -webkit-background-clip:text; background-clip:text; color:transparent;
  }
  .panel-copy p{
    margin-top:10px; font-size:13.5px; color:var(--ink-soft); line-height:1.6;
  }
  @media (max-width:940px){ .panel-copy h1{ font-size:20px;} .panel-copy p{display:none;} }

  .panel-stats{
    position:relative; z-index:2;
    display:flex; justify-content:center; gap:28px;
    margin-top:8px;
  }
  @media (max-width:940px){ .panel-stats{ display:none; } }
  .panel-stats .stat b{ display:block; font-family:'Sora',sans-serif; font-size:19px; color:var(--ink); }
  .panel-stats .stat span{ font-size:11px; color:var(--ink-soft); }

  /* ---------- RIGHT: auth card ---------- */
  .form-panel{
    display:flex; align-items:center; justify-content:center;
    padding:40px 28px;
    animation: fade-up .7s cubic-bezier(.2,.7,.2,1) both;
  }
  @keyframes fade-up{
    from{ opacity:0; transform:translateY(14px); }
    to{ opacity:1; transform:translateY(0); }
  }

  .card{
    width:100%; max-width:400px;
    background:rgba(255,255,255,.7);
    backdrop-filter:blur(18px);
    -webkit-backdrop-filter:blur(18px);
    border:1px solid rgba(231,229,245,.9);
    border-radius:var(--radius);
    box-shadow:var(--shadow-soft);
    padding:34px 32px 30px;
  }
  .card-head{ text-align:center; margin-bottom:22px; }
  .card-head h2{ font-size:22px; font-weight:700; }
  .card-head p{ font-size:13px; color:var(--ink-soft); margin-top:5px; }

  .tabs{
    position:relative;
    display:grid; grid-template-columns:1fr 1fr;
    background:var(--lavender); border-radius:999px;
    padding:4px; margin-bottom:26px;
  }
  .tabs .indicator{
    position:absolute; top:4px; left:4px;
    width:calc(50% - 4px); height:calc(100% - 8px);
    background:#fff; border-radius:999px;
    box-shadow:0 4px 14px -4px rgba(79,70,229,.35);
    transition:transform .35s cubic-bezier(.65,0,.35,1);
  }
  .tabs.reg .indicator{ transform:translateX(100%); }
  .tabs button{
    position:relative; z-index:1;
    border:none; background:transparent; cursor:pointer;
    padding:9px 0; font-family:'Sora',sans-serif; font-weight:600; font-size:13.5px;
    color:var(--ink-soft); transition:color .25s ease;
  }
  .tabs button.active{ color:var(--indigo-dark); }

  .forms-wrap{ position:relative; overflow:hidden; }
  form{ display:flex; flex-direction:column; gap:15px; }
  form.hidden-form{ display:none; }
  form.enter{ animation:slide-in .4s ease both; }
  @keyframes slide-in{ from{opacity:0; transform:translateX(14px);} to{opacity:1; transform:translateX(0);} }

  .field{ display:flex; flex-direction:column; gap:6px; }
  .field label{ font-size:12.5px; font-weight:600; color:var(--ink); }
  .field .input-wrap{ position:relative; }
  .field input{
    width:100%;
    border:1.5px solid var(--line);
    border-radius:var(--radius-sm);
    padding:11px 13px;
    font-size:13.5px; font-family:'Inter',sans-serif;
    color:var(--ink);
    background:#fff;
    outline:none;
    transition:border-color .2s ease, box-shadow .2s ease;
  }
  .field input::placeholder{ color:#B3AFD6; }
  .field input:focus{
    border-color:var(--indigo);
    box-shadow:0 0 0 4px rgba(79,70,229,.14);
  }
  .field input.error{ border-color:var(--danger); }
  .field input.error:focus{ box-shadow:0 0 0 4px rgba(239,68,68,.12); }
  .field .error-msg{
    font-size:11.5px; color:var(--danger); display:none; align-items:center; gap:5px;
  }
  .field .error-msg.show{ display:flex; }

  .toggle-eye{
    position:absolute; right:11px; top:50%; transform:translateY(-50%);
    border:none; background:none; cursor:pointer; padding:4px;
    color:var(--ink-soft); display:flex;
  }
  .toggle-eye svg{ width:17px; height:17px; }
  .toggle-eye:hover{ color:var(--indigo); }

  .row-between{ display:flex; align-items:center; justify-content:space-between; font-size:12.5px; }
  .checkbox-row{ display:flex; align-items:center; gap:8px; }
  .checkbox-row input[type=checkbox]{
    appearance:none; width:16px; height:16px; border:1.5px solid var(--line); border-radius:5px;
    cursor:pointer; position:relative; flex:none; transition:.2s;
  }
  .checkbox-row input[type=checkbox]:checked{ background:var(--indigo); border-color:var(--indigo); }
  .checkbox-row input[type=checkbox]:checked::after{
    content:""; position:absolute; left:4px; top:1px; width:5px; height:9px;
    border:solid #fff; border-width:0 2px 2px 0; transform:rotate(45deg);
  }
  .checkbox-row label{ color:var(--ink-soft); cursor:pointer; }
  .link{ color:var(--indigo); font-weight:600; text-decoration:none; }
  .link:hover{ text-decoration:underline; }

  .btn-primary{
    margin-top:4px;
    border:none; border-radius:var(--radius-sm);
    padding:12px 0;
    background:linear-gradient(135deg, var(--indigo), var(--blue));
    color:#fff; font-family:'Sora',sans-serif; font-weight:600; font-size:14px;
    cursor:pointer;
    box-shadow:0 12px 24px -10px rgba(79,70,229,.55);
    transition:transform .18s ease, box-shadow .18s ease, filter .18s ease;
  }
  .btn-primary:hover{ transform:translateY(-1px) scale(1.01); box-shadow:0 16px 28px -10px rgba(79,70,229,.6); }
  .btn-primary:active{ transform:translateY(0) scale(.99); }

  .divider{ display:flex; align-items:center; gap:12px; margin:6px 0 2px; color:var(--ink-soft); font-size:11.5px; }
  .divider::before, .divider::after{ content:""; flex:1; height:1px; background:var(--line); }

  .social-row{ display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .btn-social{
    display:flex; align-items:center; justify-content:center; gap:8px;
    border:1.5px solid var(--line); border-radius:var(--radius-sm);
    background:#fff; padding:10px 0; font-size:13px; font-weight:600; color:var(--ink);
    cursor:pointer; transition:border-color .2s ease, transform .18s ease, background .2s ease;
  }
  .btn-social:hover{ border-color:#C7C3EF; background:var(--lavender); transform:translateY(-1px); }
  .btn-social svg{ width:16px; height:16px; }

  .switch-line{ text-align:center; font-size:12.5px; color:var(--ink-soft); margin-top:20px; }

  .terms-row{ display:flex; align-items:flex-start; gap:8px; font-size:12px; color:var(--ink-soft); line-height:1.5; }
  .terms-row input{ margin-top:2px; }
</style>
</head>
<body>

<div class="page">

  <!-- ============ LEFT MOTIVATION PANEL ============ -->
  <aside class="panel">
    <div class="blob blob1"></div>
    <div class="blob blob2"></div>
    <div class="blob blob3"></div>
    <div class="particle" style="width:6px;height:6px; top:14%; left:30%; animation-delay:-1s;"></div>
    <div class="particle" style="width:4px;height:4px; top:70%; left:20%; animation-delay:-3s;"></div>
    <div class="particle" style="width:8px;height:8px; top:60%; left:78%; animation-delay:-5s;"></div>
    <div class="particle" style="width:5px;height:5px; top:22%; left:82%; animation-delay:-2s;"></div>
    <div class="particle" style="width:5px;height:5px; top:44%; left:60%; animation-delay:-6s;"></div>

    <div class="brand-mark">
      <span class="logo-badge">
        <svg viewBox="0 0 24 24" fill="none"><path d="M4 6.5C4 5.67 4.67 5 5.5 5H12V19H5.5C4.67 19 4 18.33 4 17.5V6.5Z" stroke="white" stroke-width="1.6" stroke-linejoin="round"/><path d="M20 6.5C20 5.67 19.33 5 18.5 5H12V19H18.5C19.33 19 20 18.33 20 17.5V6.5Z" stroke="white" stroke-width="1.6" stroke-linejoin="round"/></svg>
      </span>
      <span>Edulys</span>
    </div>

    <div class="stage">
      <div class="orbit-ring"></div>
      <div class="orbit-wrap">
        <div class="orbit-item i1" title="Sách">
          <svg viewBox="0 0 24 24" fill="none"><path d="M4 6.5C4 5.67 4.67 5 5.5 5H12V19H5.5C4.67 19 4 18.33 4 17.5V6.5Z" stroke="#4F46E5" stroke-width="1.6" stroke-linejoin="round"/><path d="M20 6.5C20 5.67 19.33 5 18.5 5H12V19H18.5C19.33 19 20 18.33 20 17.5V6.5Z" stroke="#4F46E5" stroke-width="1.6" stroke-linejoin="round"/></svg>
        </div>
        <div class="orbit-item i2" title="Ngôi sao">
          <svg viewBox="0 0 24 24" fill="#FBBF24"><path d="M12 2.5l2.85 6.32 6.9.68-5.2 4.63 1.55 6.87L12 17.6l-6.1 3.4 1.55-6.87-5.2-4.63 6.9-.68L12 2.5z"/></svg>
        </div>
        <div class="orbit-item i3" title="Tên lửa">
          <svg viewBox="0 0 24 24" fill="none"><path d="M12 2.5c3 2 4.5 5.6 4.2 9.7-1 .6-2.6 1.4-4.2 1.4s-3.2-.8-4.2-1.4C7.5 8.1 9 4.5 12 2.5z" stroke="#3B82F6" stroke-width="1.6" stroke-linejoin="round"/><path d="M9.3 13.6L7 16.5l1 3 2.4-2.2M14.7 13.6L17 16.5l-1 3-2.4-2.2" stroke="#3B82F6" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="1.4" stroke="#3B82F6" stroke-width="1.4"/></svg>
        </div>
        <div class="orbit-item i4" title="Bóng đèn">
          <svg viewBox="0 0 24 24" fill="none"><path d="M9 18h6M10 21h4M8 11a4 4 0 118 0c0 1.8-1 2.6-1.7 3.4-.5.5-.8 1-.8 1.6H10.5c0-.6-.3-1.1-.8-1.6C8.98 13.6 8 12.8 8 11z" stroke="#8B5CF6" stroke-width="1.6" stroke-linejoin="round"/></svg>
        </div>
      </div>

      <div class="mascot">
        <svg viewBox="0 0 220 220" fill="none">
          <ellipse cx="110" cy="196" rx="62" ry="10" fill="#E7E5F5"/>
          <path d="M60 150c0-33 22-56 50-56s50 23 50 56" stroke="#4F46E5" stroke-width="4" stroke-linecap="round"/>
          <circle cx="110" cy="76" r="30" fill="#FDE9C7"/>
          <path d="M84 70c0-18 12-30 26-30s26 12 26 30c0 4-8 6-26 6s-26-2-26-6z" fill="#4338CA"/>
          <rect x="76" y="120" width="68" height="52" rx="14" fill="#4F46E5"/>
          <path d="M90 150c8 6 32 6 40 0" stroke="#C7D2FE" stroke-width="3" stroke-linecap="round"/>
          <rect x="66" y="152" width="34" height="24" rx="4" fill="#fff" stroke="#DDD6FE" stroke-width="2" transform="rotate(-8 66 152)"/>
          <rect x="122" y="150" width="34" height="24" rx="4" fill="#fff" stroke="#DDD6FE" stroke-width="2" transform="rotate(8 122 150)"/>
          <circle cx="96" cy="74" r="3" fill="#211E3D"/>
          <circle cx="122" cy="74" r="3" fill="#211E3D"/>
          <path d="M100 86c4 3 14 3 18 0" stroke="#211E3D" stroke-width="2.4" stroke-linecap="round"/>
        </svg>
      </div>

      <div class="streak-chip">
        <svg class="fire" viewBox="0 0 24 24" fill="#F59E0B"><path d="M12 2c1 3-2 4-2 7a4 4 0 108 0c2 2 3 5 3 7a9 9 0 11-18 0c0-4 3-7 4-9 1 2 2 3 3 3-1-3 1-6 2-8z"/></svg>
        <span><span class="val mono">07</span><br><span class="lbl">NGÀY LIÊN TIẾP</span></span>
      </div>
    </div>

    <div class="panel-copy">
      <span class="eyebrow">
        <svg viewBox="0 0 24 24" fill="none" width="12" height="12"><path d="M12 2l2.4 5.3L20 8l-4 4 1 5.7L12 15l-5 2.7L8 12l-4-4 5.6-.7L12 2z" fill="currentColor"/></svg>
        Nền tảng học tập
      </span>
      <h1>Học mỗi ngày một chút, <span class="accent">tiến bộ mỗi ngày một xa.</span></h1>
      <p>"Small progress each day leads to big achievements." Cùng hàng ngàn học viên xây dựng thói quen học tập mỗi ngày. 🚀</p>
    </div>

    <div class="panel-stats">
      <div class="stat"><b>128K+</b><span>Học viên</span></div>
      <div class="stat"><b>4.9/5</b><span>Đánh giá</span></div>
      <div class="stat"><b>2.4K</b><span>Khoá học</span></div>
    </div>
  </aside>

  <!-- ============ RIGHT AUTH CARD ============ -->
  <main class="form-panel">
    <div class="card">
      @php
        $showRegister = old('name') || $errors->has('name') || $errors->has('email') || $errors->has('password_confirmation') || $errors->has('password');
      @endphp

      <div class="card-head">
        <h2 id="headTitle">{{ $showRegister ? 'Tạo tài khoản mới' : 'Chào mừng trở lại' }}</h2>
        <p id="headSub">{{ $showRegister ? 'Bắt đầu hành trình học tập của bạn ngay hôm nay' : 'Đăng nhập để tiếp tục hành trình học tập của bạn' }}</p>
      </div>

      <div class="forms-wrap">
        <div id="tabs" class="tabs {{ $showRegister ? 'reg' : '' }}">
          <div class="indicator"></div>
          <button type="button" class="{{ $showRegister ? '' : 'active' }}" data-tab="login">Đăng nhập</button>
          <button type="button" class="{{ $showRegister ? 'active' : '' }}" data-tab="register">Đăng ký</button>
        </div>

        <!-- LOGIN FORM -->
        <form id="loginForm" class="{{ $showRegister ? 'hidden-form' : '' }}" action="{{ route('login') }}" method="POST" novalidate>
          @csrf
          <div class="field">
            <label for="loginEmail">Email</label>
            <div class="input-wrap">
              <input type="email" name="email" id="loginEmail" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="username" required>
            </div>
            <span class="error-msg {{ $errors->has('email') ? 'show' : '' }}" id="loginEmailErr">{{ $errors->has('email') ? $errors->first('email') : '⚠ Vui lòng nhập email hợp lệ' }}</span>
          </div>

          <div class="field">
            <label for="loginPassword">Mật khẩu</label>
            <div class="input-wrap">
              <input type="password" name="password" id="loginPassword" placeholder="••••••••" autocomplete="current-password" required>
              <button type="button" class="toggle-eye" data-target="loginPassword">
                <svg viewBox="0 0 24 24" fill="none"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/></svg>
              </button>
            </div>
            <span class="error-msg {{ $errors->has('password') ? 'show' : '' }}" id="loginPasswordErr">{{ $errors->has('password') ? $errors->first('password') : '⚠ Mật khẩu phải có ít nhất 6 ký tự' }}</span>
          </div>

          <div class="row-between">
            <div class="checkbox-row">
              <input type="checkbox" name="remember" id="remember">
              <label for="remember">Ghi nhớ đăng nhập</label>
            </div>
            <a href="{{ route('password.request') ?? '#' }}" class="link">Quên mật khẩu?</a>
          </div>

          <button type="submit" class="btn-primary">Đăng nhập</button>

          <p class="switch-line">Chưa có tài khoản? <a href="#" class="link" data-switch="register">Đăng ký ngay</a></p>
        </form>

        <!-- REGISTER FORM -->
        <form id="registerForm" class="{{ $showRegister ? '' : 'hidden-form' }}" action="{{ route('register') }}" method="POST" novalidate>
          @csrf
          <div class="field">
            <label for="regName">Họ và tên</label>
            <div class="input-wrap">
              <input type="text" name="name" id="regName" value="{{ old('name') }}" placeholder="Nguyễn Văn A" autocomplete="name" required>
            </div>
            <span class="error-msg {{ $errors->has('name') ? 'show' : '' }}" id="regNameErr">{{ $errors->has('name') ? $errors->first('name') : '' }}</span>
          </div>

          <div class="field">
            <label for="regEmail">Email</label>
            <div class="input-wrap">
              <input type="email" name="email" id="regEmail" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required>
            </div>
            <span class="error-msg {{ $errors->has('email') ? 'show' : '' }}" id="regEmailErr">{{ $errors->has('email') ? $errors->first('email') : '⚠ Email này đã được sử dụng' }}</span>
          </div>

          <div class="field">
            <label for="regUsername">Tên đăng nhập</label>
            <div class="input-wrap">
              <input type="text" name="username" id="regUsername" placeholder="vana2026" autocomplete="username">
            </div>
          </div>

          <div class="field">
            <label for="regPassword">Mật khẩu</label>
            <div class="input-wrap">
              <input type="password" name="password" id="regPassword" placeholder="••••••••" autocomplete="new-password" required>
              <button type="button" class="toggle-eye" data-target="regPassword">
                <svg viewBox="0 0 24 24" fill="none"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/></svg>
              </button>
            </div>
            <span class="error-msg {{ $errors->has('password') ? 'show' : '' }}" id="regPasswordErr">{{ $errors->has('password') ? $errors->first('password') : '' }}</span>
          </div>

          <div class="field">
            <label for="regConfirm">Xác nhận mật khẩu</label>
            <div class="input-wrap">
              <input type="password" name="password_confirmation" id="regConfirm" placeholder="••••••••" autocomplete="new-password" required>
              <button type="button" class="toggle-eye" data-target="regConfirm">
                <svg viewBox="0 0 24 24" fill="none"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/></svg>
              </button>
            </div>
            <span class="error-msg {{ $errors->has('password_confirmation') ? 'show' : '' }}" id="regConfirmErr">{{ $errors->has('password_confirmation') ? $errors->first('password_confirmation') : '⚠ Mật khẩu xác nhận không khớp' }}</span>
          </div>

          <div class="terms-row">
            <input type="checkbox" id="terms">
            <label for="terms">Tôi đồng ý với <a href="#" class="link">Điều khoản sử dụng</a> và <a href="#" class="link">Chính sách bảo mật</a></label>
          </div>

          <button type="submit" class="btn-primary">Tạo tài khoản</button>

          <p class="switch-line">Đã có tài khoản? <a href="#" class="link" data-switch="login">Đăng nhập</a></p>
        </form>
      </div>
    </div>
  </main>
</div>

<script>
  const tabs = document.getElementById('tabs');
  const tabBtns = tabs.querySelectorAll('button');
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  const headTitle = document.getElementById('headTitle');
  const headSub = document.getElementById('headSub');

  const copy = {
    login: { title: 'Chào mừng trở lại', sub: 'Đăng nhập để tiếp tục hành trình học tập của bạn' },
    register: { title: 'Tạo tài khoản mới', sub: 'Bắt đầu hành trình học tập của bạn ngay hôm nay' }
  };

  function switchTab(target){
    tabBtns.forEach(b => b.classList.toggle('active', b.dataset.tab === target));
    tabs.classList.toggle('reg', target === 'register');

    if(target === 'register'){
      loginForm.classList.add('hidden-form');
      registerForm.classList.remove('hidden-form');
      registerForm.classList.remove('enter'); void registerForm.offsetWidth; registerForm.classList.add('enter');
    } else {
      registerForm.classList.add('hidden-form');
      loginForm.classList.remove('hidden-form');
      loginForm.classList.remove('enter'); void loginForm.offsetWidth; loginForm.classList.add('enter');
    }
    headTitle.textContent = copy[target].title;
    headSub.textContent = copy[target].sub;
  }

  tabBtns.forEach(b => b.addEventListener('click', () => switchTab(b.dataset.tab)));
  document.querySelectorAll('[data-switch]').forEach(el=>{
    el.addEventListener('click', (e)=>{ e.preventDefault(); switchTab(el.dataset.switch); });
  });

  // password show/hide
  document.querySelectorAll('.toggle-eye').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const input = document.getElementById(btn.dataset.target);
      const isPass = input.type === 'password';
      input.type = isPass ? 'text' : 'password';
      btn.style.color = isPass ? 'var(--indigo)' : '';
    });
  });

  // optional client-side validation before submission
  loginForm.addEventListener('submit', (e)=>{
    const email = document.getElementById('loginEmail');
    const pass = document.getElementById('loginPassword');
    const emailErr = document.getElementById('loginEmailErr');
    const passErr = document.getElementById('loginPasswordErr');

    const emailOk = email.value.trim().length > 2;
    const passOk = pass.value.trim().length >= 6;

    email.classList.toggle('error', !emailOk);
    emailErr.classList.toggle('show', !emailOk);
    pass.classList.toggle('error', !passOk);
    passErr.classList.toggle('show', !passOk);

    if(!emailOk || !passOk){
      e.preventDefault();
    }
  });

  registerForm.addEventListener('submit', (e)=>{
    const pass = document.getElementById('regPassword');
    const confirm = document.getElementById('regConfirm');
    const confirmErr = document.getElementById('regConfirmErr');

    const match = pass.value === confirm.value && pass.value.length >= 6;
    confirm.classList.toggle('error', !match);
    confirmErr.classList.toggle('show', !match);

    if(!match){
      e.preventDefault();
    }
  });

  // clear error state while typing
  document.querySelectorAll('input').forEach(inp=>{
    inp.addEventListener('input', ()=>{
      inp.classList.remove('error');
      const err = document.getElementById(inp.id + 'Err');
      if(err) err.classList.remove('show');
    });
  });
</script>
</body>
</html>