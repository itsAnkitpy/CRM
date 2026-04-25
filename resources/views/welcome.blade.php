<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ config('app.name', 'Highland Core') }} — Unified Sales & Support CRM</title>
@php($landlordLoginUrl = route('filament.admin.auth.login'))

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Montserrat:wght@500;600;700;800;900&display=swap" rel="stylesheet">

@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = { theme: { extend: {
        colors: {
          navy: { DEFAULT: '#1b3a6b', light: '#2c5282', dark: '#0f2347' },
          teal: { DEFAULT: '#00969b', light: '#00b5bb', dark: '#007a7f' },
          cream: '#f5f7fa', dark: '#1a1a2e', muted: '#64748b',
        },
        fontFamily: {
          sans: ['Inter','ui-sans-serif','system-ui','sans-serif'],
          heading: ['Montserrat','sans-serif'],
        },
        boxShadow: {
          soft: '0 1px 2px rgba(15,35,71,.04), 0 8px 24px rgba(15,35,71,.06)',
          glow: '0 20px 60px -20px rgba(0,150,155,.45)',
        },
      }}}
    </script>
@endif
<style>
  html, body { font-family: 'Inter', sans-serif; }
  h1, h2, h3, h4, h5, h6 { font-family: 'Montserrat', sans-serif; letter-spacing: -0.015em; }

  /* Subtle grid/dot background */
  .bg-dots {
    background-image: radial-gradient(rgba(27,58,107,0.09) 1px, transparent 1px);
    background-size: 22px 22px;
  }
  .bg-grid {
    background-image:
      linear-gradient(to right, rgba(27,58,107,0.06) 1px, transparent 1px),
      linear-gradient(to bottom, rgba(27,58,107,0.06) 1px, transparent 1px);
    background-size: 44px 44px;
  }

  /* Hero glow */
  .hero-glow::before{
    content:""; position:absolute; inset:-40% -10% auto auto;
    width:780px; height:780px; border-radius:50%;
    background: radial-gradient(closest-side, rgba(0,181,187,0.22), transparent 70%);
    filter: blur(20px); z-index:0; pointer-events:none;
  }
  .hero-glow::after{
    content:""; position:absolute; inset:auto auto -30% -10%;
    width:620px; height:620px; border-radius:50%;
    background: radial-gradient(closest-side, rgba(27,58,107,0.18), transparent 70%);
    filter: blur(20px); z-index:0; pointer-events:none;
  }

  /* Floating animations */
  @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
  .float-slow { animation: float 6s ease-in-out infinite; }
  .float-med  { animation: float 4.5s ease-in-out infinite; animation-delay: .4s; }

  @keyframes pulseRing {
    0%   { box-shadow: 0 0 0 0 rgba(34,197,94,.55); }
    70%  { box-shadow: 0 0 0 14px rgba(34,197,94,0); }
    100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
  }
  .pulse-ring { animation: pulseRing 1.8s infinite; }

  /* Waveform bars animating */
  @keyframes wave {
    0%,100% { transform: scaleY(0.35); }
    50%     { transform: scaleY(1); }
  }
  .wave-bar { transform-origin: center; animation: wave 1.1s ease-in-out infinite; }

  /* Ticker / logo cloud */
  @keyframes marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
  .marquee-track { animation: marquee 38s linear infinite; }

  /* Gradient text */
  .grad-text {
    background: linear-gradient(90deg, #00969b 0%, #00b5bb 40%, #2c5282 100%);
    -webkit-background-clip: text; background-clip: text; color: transparent;
  }

  /* Accent rule under eyebrows */
  .eyebrow-rule::before {
    content: ""; display: inline-block; width: 28px; height: 1px;
    background: #00969b; vertical-align: middle; margin-right: 10px;
  }

  /* Bento card hover */
  .bento-card { transition: transform .35s cubic-bezier(.4,0,.2,1), box-shadow .35s ease, border-color .35s ease; }
  .bento-card:hover { transform: translateY(-4px); border-color: rgba(0,150,155,0.35); box-shadow: 0 20px 50px -20px rgba(15,35,71,0.25); }

  /* Stepper connector */
  .step-connector::after {
    content:""; position:absolute; top:24px; left:100%; width:100%; height:2px;
    background-image: linear-gradient(90deg, rgba(0,150,155,.55) 50%, transparent 0);
    background-size: 14px 2px; background-repeat: repeat-x;
  }
  @media (max-width: 767px){ .step-connector::after { display:none } }

  /* Stat count */
  .stat-num { font-feature-settings: "tnum" 1; }

  /* Subtle shine on primary button */
  .btn-shine { position: relative; overflow: hidden; }
  .btn-shine::after{
    content:""; position:absolute; inset:0;
    background: linear-gradient(120deg, transparent 40%, rgba(255,255,255,0.35) 50%, transparent 60%);
    transform: translateX(-120%); transition: transform .7s ease;
  }
  .btn-shine:hover::after { transform: translateX(120%); }

  /* FAQ marker removal */
  details > summary { list-style: none; }
  details > summary::-webkit-details-marker { display: none; }

  /* Scroll reveal */
  .reveal { opacity: 0; transform: translateY(18px); transition: opacity .7s ease, transform .7s ease; }
  .reveal.in { opacity: 1; transform: translateY(0); }
</style>
</head>
<body class="bg-cream text-dark antialiased min-h-screen flex flex-col">

<!-- Announcement bar -->
<div class="w-full bg-navy-dark text-white/90 text-xs sm:text-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-center gap-3">
    <span class="inline-flex items-center gap-1.5 bg-teal/20 text-teal-light px-2 py-0.5 rounded-full text-[11px] font-semibold tracking-wide uppercase">New</span>
    <span class="truncate">Twilio-powered browser calling is now live for all early-access teams.</span>
    <a href="#features" class="hidden sm:inline-flex items-center gap-1 font-medium text-teal-light hover:text-white transition-colors">
      Read more
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </a>
  </div>
</div>

<!-- Navigation -->
<header class="w-full sticky top-0 z-50 bg-cream/80 backdrop-blur-md border-b border-transparent" id="siteHeader">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
    <a href="/" class="flex items-center">
      <img src="{{ asset('images/logo.png') }}" alt="Highland Core" class="h-14 w-auto md:h-16 mix-blend-multiply">
    </a>

    <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-muted">
      <a href="#features" class="hover:text-teal transition-colors">Features</a>
      <a href="#product" class="hover:text-teal transition-colors">Product</a>
      <a href="#how-it-works" class="hover:text-teal transition-colors">How it works</a>
      <a href="#testimonials" class="hover:text-teal transition-colors">Customers</a>
      <a href="#faq" class="hover:text-teal transition-colors">FAQ</a>
    </nav>

    <div class="flex items-center gap-2 sm:gap-4">
      <a href="{{ $landlordLoginUrl }}" class="hidden sm:inline-block text-sm font-medium text-navy hover:text-teal transition-colors px-2">Login</a>
      <a href="{{ $landlordLoginUrl }}" class="btn-shine bg-navy hover:bg-navy-light text-white px-4 sm:px-5 py-2.5 rounded-lg font-semibold text-sm transition-colors shadow-sm inline-flex items-center gap-2">
        Open admin login
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
      </a>
    </div>
  </div>
</header>

<main class="flex-grow">

  <!-- Hero -->
  <section class="relative overflow-hidden hero-glow">
    <div class="absolute inset-0 bg-grid opacity-60 pointer-events-none"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-24 lg:pt-24 lg:pb-32 grid lg:grid-cols-12 gap-12 lg:gap-16 items-center z-10">

      <div class="lg:col-span-6">
        <div class="inline-flex items-center gap-2 bg-white border border-gray-200 shadow-soft rounded-full pl-1.5 pr-4 py-1.5 mb-7">
          <span class="inline-flex items-center gap-1 bg-teal/10 text-teal-dark text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full">
            <span class="w-1.5 h-1.5 rounded-full bg-teal animate-pulse"></span> Beta
          </span>
          <span class="text-xs sm:text-sm text-muted font-medium">Free for early-access teams · No credit card</span>
        </div>

        <h1 class="font-heading font-extrabold text-navy text-[2.6rem] sm:text-5xl lg:text-[4.2rem] leading-[1.02] tracking-tight mb-6">
          One workspace to<br/>
          <span class="grad-text">close deals</span> and<br/>
          resolve tickets.
        </h1>

        <p class="text-lg text-muted leading-relaxed max-w-xl mb-8">
          Highland Core unifies your sales pipeline, support tickets, and browser calling into a single timeline — so every customer interaction is connected, searchable, and handed off without friction.
        </p>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4 mb-10">
          <a href="{{ $landlordLoginUrl }}" class="btn-shine bg-teal hover:bg-teal-light text-white px-7 py-4 rounded-xl font-semibold transition-all shadow-glow inline-flex items-center justify-center gap-2">
            Log in to Highland Core
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </a>
          <a href="#product" class="bg-white border border-gray-200 hover:border-teal/40 text-navy px-7 py-4 rounded-xl font-semibold transition-all shadow-sm inline-flex items-center justify-center gap-3">
            <span class="w-8 h-8 rounded-full bg-teal/10 text-teal flex items-center justify-center">
              <svg class="w-4 h-4 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            </span>
            Watch 2-min tour
          </a>
        </div>

        <!-- Trust indicators -->
        <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-muted font-medium">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-teal" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            Sales, support & telephony in one
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-teal" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            Encrypted in transit & at rest
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-teal" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            Setup in under 10 minutes
          </div>
        </div>
      </div>

      <!-- Hero mockup cluster -->
      <div class="lg:col-span-6 relative">
        <div class="absolute inset-0 bg-gradient-to-tr from-teal/20 via-transparent to-navy/10 rounded-[2rem] -rotate-1 scale-[1.03]"></div>

        <!-- Main app window -->
        <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
          <div class="bg-gray-50 border-b border-gray-100 px-4 py-3 flex items-center gap-2">
            <div class="flex gap-1.5">
              <div class="w-3 h-3 rounded-full bg-red-400"></div>
              <div class="w-3 h-3 rounded-full bg-amber-400"></div>
              <div class="w-3 h-3 rounded-full bg-green-400"></div>
            </div>
            <div class="mx-auto bg-white rounded-md text-xs text-center text-muted px-20 py-1 border border-gray-200 inline-flex items-center gap-1">
              <svg class="w-3 h-3 text-teal" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
              app.highlandcore.io
            </div>
          </div>

          <!-- App chrome: sidebar + workspace -->
          <div class="grid grid-cols-12">
            <!-- Sidebar -->
            <aside class="hidden sm:flex col-span-3 bg-navy-dark text-white/80 flex-col py-5 px-3 gap-1 text-xs">
              <div class="px-2 mb-4 flex items-center gap-2">
                <div class="w-7 h-7 rounded-md bg-teal flex items-center justify-center text-white font-bold text-xs">H</div>
                <div>
                  <div class="font-semibold text-white text-[11px]">Highland Core</div>
                  <div class="text-white/50 text-[10px]">Sales workspace</div>
                </div>
              </div>
              <div class="bg-white/10 text-white rounded-md px-2.5 py-1.5 flex items-center gap-2 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-teal-light"></span>Pipeline</div>
              <div class="px-2.5 py-1.5 flex items-center gap-2 text-white/60">Contacts</div>
              <div class="px-2.5 py-1.5 flex items-center gap-2 text-white/60">Tickets <span class="ml-auto bg-teal text-white text-[9px] font-bold rounded-full px-1.5 py-0.5">3</span></div>
              <div class="px-2.5 py-1.5 flex items-center gap-2 text-white/60">Calls</div>
              <div class="px-2.5 py-1.5 flex items-center gap-2 text-white/60">Reports</div>
              <div class="mt-auto pt-4 border-t border-white/10 text-[10px] text-white/40 px-2">Connected to Twilio ✓</div>
            </aside>

            <!-- Workspace -->
            <div class="col-span-12 sm:col-span-9 p-5 bg-gradient-to-b from-white to-cream/60">
              <div class="flex justify-between items-start mb-5">
                <div>
                  <div class="text-[10px] uppercase tracking-widest text-muted font-semibold mb-1">Q2 Pipeline</div>
                  <h3 class="font-heading font-bold text-navy text-lg leading-tight">Active Deals</h3>
                </div>
                <div class="text-right">
                  <div class="text-[10px] text-muted uppercase tracking-wider">Weighted value</div>
                  <div class="font-heading text-xl font-bold text-navy stat-num">$184.2K</div>
                </div>
              </div>

              <!-- Mini kanban -->
              <div class="grid grid-cols-3 gap-2 mb-4">
                <div>
                  <div class="text-[10px] font-semibold text-muted mb-1.5 flex justify-between"><span>DISCOVERY</span><span>4</span></div>
                  <div class="h-1 rounded-full bg-gray-200 mb-2"><div class="h-full rounded-full bg-gray-400 w-1/4"></div></div>
                  <div class="space-y-1.5">
                    <div class="bg-white border border-gray-100 rounded-lg p-2 shadow-sm text-[10px]">
                      <div class="font-semibold text-navy truncate">Northwind Co.</div>
                      <div class="text-muted">$12,500</div>
                    </div>
                    <div class="bg-white border border-gray-100 rounded-lg p-2 shadow-sm text-[10px]">
                      <div class="font-semibold text-navy truncate">Acme LLC</div>
                      <div class="text-muted">$8,200</div>
                    </div>
                  </div>
                </div>
                <div>
                  <div class="text-[10px] font-semibold text-muted mb-1.5 flex justify-between"><span>PROPOSAL</span><span>3</span></div>
                  <div class="h-1 rounded-full bg-gray-200 mb-2"><div class="h-full rounded-full bg-teal w-1/2"></div></div>
                  <div class="space-y-1.5">
                    <div class="bg-white border border-teal/30 rounded-lg p-2 shadow-sm text-[10px] ring-1 ring-teal/10">
                      <div class="font-semibold text-navy truncate">Global Industries</div>
                      <div class="flex justify-between mt-0.5"><span class="text-muted">$18,500</span><span class="text-amber-700 font-medium">Follow up</span></div>
                    </div>
                    <div class="bg-white border border-gray-100 rounded-lg p-2 shadow-sm text-[10px]">
                      <div class="font-semibold text-navy truncate">Ridgeview</div>
                      <div class="text-muted">$9,750</div>
                    </div>
                  </div>
                </div>
                <div>
                  <div class="text-[10px] font-semibold text-muted mb-1.5 flex justify-between"><span>NEGOTIATION</span><span>2</span></div>
                  <div class="h-1 rounded-full bg-gray-200 mb-2"><div class="h-full rounded-full bg-teal-dark w-4/5"></div></div>
                  <div class="space-y-1.5">
                    <div class="bg-navy text-white border border-navy rounded-lg p-2 shadow-sm text-[10px]">
                      <div class="font-semibold truncate">Acme Corp Enterprise</div>
                      <div class="flex justify-between mt-0.5"><span>$24,000</span><span class="text-teal-light font-medium">92%</span></div>
                    </div>
                    <div class="bg-white border border-gray-100 rounded-lg p-2 shadow-sm text-[10px]">
                      <div class="font-semibold text-navy truncate">Brightstar</div>
                      <div class="text-muted">$15,400</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Timeline row -->
              <div class="bg-white border border-gray-100 rounded-xl p-3 flex items-center gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-full bg-teal/10 text-teal flex items-center justify-center flex-shrink-0">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-[11px] font-semibold text-navy truncate">Call with Sarah Jenkins · TechCorp</div>
                  <div class="text-[10px] text-muted">12 min · Linked to "Acme Corp Enterprise"</div>
                </div>
                <div class="flex items-end gap-0.5">
                  <div class="w-0.5 bg-teal rounded-full wave-bar h-2.5"></div>
                  <div class="w-0.5 bg-teal rounded-full wave-bar h-4" style="animation-delay:.1s"></div>
                  <div class="w-0.5 bg-teal rounded-full wave-bar h-3" style="animation-delay:.2s"></div>
                  <div class="w-0.5 bg-teal rounded-full wave-bar h-5" style="animation-delay:.3s"></div>
                  <div class="w-0.5 bg-teal rounded-full wave-bar h-2" style="animation-delay:.4s"></div>
                  <div class="w-0.5 bg-teal rounded-full wave-bar h-4" style="animation-delay:.5s"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Floating incoming call card -->
        <div class="absolute -bottom-8 -left-4 sm:-left-12 w-64 sm:w-72 float-slow z-10">
          <div class="bg-navy-dark text-white rounded-2xl shadow-2xl p-4 border border-white/10 relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-teal/30 blur-2xl"></div>
            <div class="flex items-center gap-3 relative">
              <div class="relative">
                <div class="w-10 h-10 rounded-full bg-white text-navy flex items-center justify-center font-bold pulse-ring">SJ</div>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-[10px] uppercase tracking-widest text-teal-light font-bold">Incoming · 00:14</div>
                <div class="font-semibold text-sm truncate">Sarah Jenkins</div>
                <div class="text-[11px] text-white/60 truncate">TechCorp · VIP customer</div>
              </div>
              <button class="w-9 h-9 rounded-full bg-green-500 hover:bg-green-400 flex items-center justify-center transition-colors shadow-lg">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Floating ticket card -->
        <div class="absolute -top-6 -right-2 sm:-right-8 w-60 float-med z-10">
          <div class="bg-white rounded-2xl shadow-2xl p-4 border border-gray-100">
            <div class="flex items-center justify-between mb-2">
              <span class="text-[10px] font-bold uppercase tracking-widest text-teal-dark bg-teal/10 px-2 py-0.5 rounded-full">Ticket #4821</span>
              <span class="text-[10px] text-muted">2m ago</span>
            </div>
            <div class="font-heading font-semibold text-navy text-sm mb-1">API rate limit question</div>
            <div class="text-[11px] text-muted leading-relaxed mb-3">"We're hitting limits during our nightly sync…"</div>
            <div class="flex items-center gap-2">
              <div class="flex -space-x-1.5">
                <div class="w-6 h-6 rounded-full bg-teal text-white text-[10px] flex items-center justify-center font-bold border-2 border-white">MR</div>
                <div class="w-6 h-6 rounded-full bg-navy text-white text-[10px] flex items-center justify-center font-bold border-2 border-white">AK</div>
              </div>
              <span class="text-[10px] text-muted">Assigned · SLA 4h</span>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Logo cloud / trust strip -->
    <div class="relative border-t border-gray-200/60 bg-white/60 backdrop-blur-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center text-xs font-semibold tracking-widest uppercase text-muted mb-6">Built on infrastructure teams already trust</div>
        <div class="flex flex-wrap justify-center items-center gap-x-10 gap-y-4 text-navy/60">
          <div class="font-heading font-bold text-lg tracking-tight opacity-70">Laravel</div>
          <div class="font-heading font-bold text-lg tracking-tight opacity-70">Twilio</div>
          <div class="font-heading font-bold text-lg tracking-tight opacity-70">PostgreSQL</div>
          <div class="font-heading font-bold text-lg tracking-tight opacity-70">Meilisearch</div>
          <div class="font-heading font-bold text-lg tracking-tight opacity-70">Redis</div>
          <div class="font-heading font-bold text-lg tracking-tight opacity-70">Filament</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Metrics Band -->
  <section class="bg-navy text-white py-16 relative overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-20"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-2 md:grid-cols-4 gap-8">
      <div class="reveal">
        <div class="text-5xl lg:text-6xl font-heading font-extrabold grad-text stat-num leading-none" data-count="3" data-suffix="×">3×</div>
        <div class="text-sm text-white/70 mt-3 leading-snug">Faster first response when calls and tickets live together.</div>
      </div>
      <div class="reveal">
        <div class="text-5xl lg:text-6xl font-heading font-extrabold grad-text stat-num leading-none" data-count="42" data-suffix="%">42%</div>
        <div class="text-sm text-white/70 mt-3 leading-snug">Less time spent tool-switching between sales and support.</div>
      </div>
      <div class="reveal">
        <div class="text-5xl lg:text-6xl font-heading font-extrabold grad-text stat-num leading-none" data-count="10" data-suffix="m">10m</div>
        <div class="text-sm text-white/70 mt-3 leading-snug">Average time from signup to your first logged call.</div>
      </div>
      <div class="reveal">
        <div class="text-5xl lg:text-6xl font-heading font-extrabold grad-text stat-num leading-none" data-count="99.9" data-suffix="%">99.9%</div>
        <div class="text-sm text-white/70 mt-3 leading-snug">Platform uptime backed by our early-access SLA.</div>
      </div>
    </div>
  </section>

  <!-- Features — Bento grid -->
  <section id="features" class="bg-white py-24 lg:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="max-w-3xl mb-14">
        <div class="eyebrow-rule text-teal-dark font-bold tracking-widest uppercase text-xs mb-4">Why Highland Core</div>
        <h2 class="text-4xl lg:text-5xl font-extrabold text-navy font-heading leading-[1.05] tracking-tight mb-5">
          Everything your sales and support teams need.<br/>
          <span class="text-muted font-bold">Nothing they don't.</span>
        </h2>
        <p class="text-lg text-muted max-w-2xl">A purpose-built stack — browser calling, unified pipeline, ticket workflows, and reporting — engineered around how real teams actually work.</p>
      </div>

      <div class="grid grid-cols-12 gap-5 lg:gap-6">

        <!-- Big card: Browser calling -->
        <div class="bento-card col-span-12 lg:col-span-7 bg-gradient-to-br from-navy to-navy-dark text-white rounded-3xl p-8 lg:p-10 border border-navy-dark relative overflow-hidden">
          <div class="absolute -right-24 -top-24 w-72 h-72 rounded-full bg-teal/20 blur-3xl"></div>
          <div class="relative">
            <div class="flex items-center gap-2 mb-6">
              <div class="w-10 h-10 rounded-xl bg-teal flex items-center justify-center shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
              </div>
              <span class="text-xs font-bold uppercase tracking-widest text-teal-light">Telephony</span>
            </div>
            <h3 class="font-heading text-3xl lg:text-4xl font-bold mb-4 leading-tight">Browser calling that logs itself.</h3>
            <p class="text-white/70 leading-relaxed max-w-lg mb-8">Twilio-powered inbound and outbound calls — with auto-transcription, recordings, and call notes linked directly to the contact, deal, or ticket they belong to.</p>

            <!-- Mini call UI -->
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-5 max-w-md">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal to-teal-dark text-white flex items-center justify-center font-bold text-sm">MJ</div>
                  <div>
                    <div class="font-semibold text-sm">Marcus Johnson</div>
                    <div class="text-xs text-white/50">Ridgeview Logistics · Deal $42K</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-[10px] text-white/50 uppercase tracking-wider">Duration</div>
                  <div class="text-sm font-semibold stat-num">04:12</div>
                </div>
              </div>
              <div class="flex items-center gap-1 h-8 mb-4">
                <div class="flex-1 bg-teal/20 rounded-sm wave-bar" style="height:40%"></div>
                <div class="flex-1 bg-teal/40 rounded-sm wave-bar" style="height:80%; animation-delay:.1s"></div>
                <div class="flex-1 bg-teal/30 rounded-sm wave-bar" style="height:60%; animation-delay:.2s"></div>
                <div class="flex-1 bg-teal rounded-sm wave-bar" style="height:100%; animation-delay:.3s"></div>
                <div class="flex-1 bg-teal/50 rounded-sm wave-bar" style="height:70%; animation-delay:.4s"></div>
                <div class="flex-1 bg-teal/30 rounded-sm wave-bar" style="height:50%; animation-delay:.5s"></div>
                <div class="flex-1 bg-teal/40 rounded-sm wave-bar" style="height:75%; animation-delay:.6s"></div>
                <div class="flex-1 bg-teal/20 rounded-sm wave-bar" style="height:45%; animation-delay:.7s"></div>
                <div class="flex-1 bg-teal/50 rounded-sm wave-bar" style="height:85%; animation-delay:.8s"></div>
                <div class="flex-1 bg-teal/30 rounded-sm wave-bar" style="height:55%; animation-delay:.9s"></div>
                <div class="flex-1 bg-teal/40 rounded-sm wave-bar" style="height:65%; animation-delay:1s"></div>
                <div class="flex-1 bg-teal/25 rounded-sm wave-bar" style="height:35%; animation-delay:1.1s"></div>
              </div>
              <div class="flex gap-2">
                <button class="flex-1 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold rounded-lg py-2 transition-colors">Mute</button>
                <button class="flex-1 bg-white/10 hover:bg-white/20 text-white text-xs font-semibold rounded-lg py-2 transition-colors">Hold</button>
                <button class="flex-1 bg-red-500 hover:bg-red-400 text-white text-xs font-semibold rounded-lg py-2 transition-colors">End</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Medium card: Pipeline -->
        <div class="bento-card col-span-12 lg:col-span-5 bg-cream rounded-3xl p-8 border border-gray-200 relative overflow-hidden">
          <div class="flex items-center gap-2 mb-6">
            <div class="w-10 h-10 rounded-xl bg-navy text-white flex items-center justify-center shadow-md">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h6"/></svg>
            </div>
            <span class="text-xs font-bold uppercase tracking-widest text-teal-dark">Pipeline</span>
          </div>
          <h3 class="font-heading text-2xl font-bold text-navy mb-3">Kanban that understands your stages.</h3>
          <p class="text-muted leading-relaxed mb-5">Drag to advance deals, configure stage-specific fields, and convert a won deal into a new support account in one click.</p>

          <div class="bg-white rounded-xl border border-gray-200 p-3 shadow-sm">
            <div class="flex items-center justify-between text-[11px] font-semibold text-muted mb-2">
              <span>Stage velocity</span><span class="text-navy">This week</span>
            </div>
            <div class="grid grid-cols-5 gap-1.5 items-end h-20">
              <div class="bg-teal/20 rounded-t-md" style="height:35%"></div>
              <div class="bg-teal/40 rounded-t-md" style="height:55%"></div>
              <div class="bg-teal/60 rounded-t-md" style="height:75%"></div>
              <div class="bg-teal rounded-t-md" style="height:90%"></div>
              <div class="bg-navy rounded-t-md" style="height:100%"></div>
            </div>
            <div class="grid grid-cols-5 gap-1.5 text-[9px] text-muted mt-1 text-center font-medium">
              <span>New</span><span>Qual</span><span>Disc</span><span>Prop</span><span>Won</span>
            </div>
          </div>
        </div>

        <!-- Small card: Tickets -->
        <div class="bento-card col-span-12 sm:col-span-6 lg:col-span-4 bg-white rounded-3xl p-8 border border-gray-200">
          <div class="w-12 h-12 rounded-xl bg-teal/10 text-teal flex items-center justify-center mb-5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <h3 class="font-heading text-xl font-bold text-navy mb-3">Support tickets with SLA guardrails.</h3>
          <p class="text-muted leading-relaxed text-sm">Track first-response and resolution times, auto-escalate breaches, and share context between sales and support with zero manual handoff.</p>
        </div>

        <!-- Small card: Analytics -->
        <div class="bento-card col-span-12 sm:col-span-6 lg:col-span-4 bg-white rounded-3xl p-8 border border-gray-200">
          <div class="w-12 h-12 rounded-xl bg-teal/10 text-teal flex items-center justify-center mb-5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          </div>
          <h3 class="font-heading text-xl font-bold text-navy mb-3">Reports that match your workflow.</h3>
          <p class="text-muted leading-relaxed text-sm">Agent performance, deal velocity, ticket SLAs — exportable to PDF and Excel without custom plumbing.</p>
        </div>

        <!-- Small card: Security -->
        <div class="bento-card col-span-12 sm:col-span-12 lg:col-span-4 bg-gradient-to-br from-teal/10 to-teal/5 rounded-3xl p-8 border border-teal/20">
          <div class="w-12 h-12 rounded-xl bg-teal text-white flex items-center justify-center mb-5 shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          </div>
          <h3 class="font-heading text-xl font-bold text-navy mb-3">Security & roles, built in.</h3>
          <p class="text-muted leading-relaxed text-sm">Role-based permissions, full activity audit log, and encrypted call recordings — Spatie-grade access control from day one.</p>
        </div>

      </div>
    </div>
  </section>

  <!-- Product Showcase (split: copy + big mock) -->
  <section id="product" class="py-24 lg:py-32 bg-cream relative overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-60 pointer-events-none"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <div class="grid lg:grid-cols-12 gap-12 lg:gap-16 items-start mb-20">
        <div class="lg:col-span-5 lg:sticky lg:top-28">
          <div class="eyebrow-rule text-teal-dark font-bold tracking-widest uppercase text-xs mb-4">The Product</div>
          <h2 class="text-4xl lg:text-5xl font-extrabold text-navy font-heading leading-[1.05] tracking-tight mb-6">
            One timeline. <br/>Every interaction.
          </h2>
          <p class="text-lg text-muted leading-relaxed mb-8">
            Calls, emails, tickets, deal updates — all stitched into a single chronological thread per contact. Nothing falls through the cracks when the sales rep hands off to support.
          </p>

          <ul class="space-y-4 mb-10">
            <li class="flex items-start gap-3">
              <div class="w-6 h-6 rounded-full bg-teal/10 text-teal flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <div><span class="font-semibold text-navy">Universal inbox.</span> <span class="text-muted">Email, SMS, voice, and in-app messages in one feed.</span></div>
            </li>
            <li class="flex items-start gap-3">
              <div class="w-6 h-6 rounded-full bg-teal/10 text-teal flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <div><span class="font-semibold text-navy">Smart linking.</span> <span class="text-muted">Every call, email, and note auto-links to the right deal or ticket.</span></div>
            </li>
            <li class="flex items-start gap-3">
              <div class="w-6 h-6 rounded-full bg-teal/10 text-teal flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              </div>
              <div><span class="font-semibold text-navy">Seamless handoff.</span> <span class="text-muted">Convert won deals to support accounts with full context preserved.</span></div>
            </li>
          </ul>

          <a href="{{ $landlordLoginUrl }}" class="inline-flex items-center gap-2 text-teal-dark font-semibold hover:text-teal transition-colors group">
            See the full product tour
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </a>
        </div>

        <div class="lg:col-span-7">
          <!-- Big dashboard mock -->
          <div class="bg-white rounded-2xl shadow-soft border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 border-b border-gray-100 px-4 py-3 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="flex gap-1.5">
                  <div class="w-2.5 h-2.5 rounded-full bg-red-400"></div>
                  <div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div>
                  <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                </div>
              </div>
              <div class="text-[11px] text-muted font-medium">Contact · Sarah Jenkins · TechCorp</div>
              <div class="flex gap-1">
                <div class="w-6 h-6 rounded bg-gray-100"></div>
                <div class="w-6 h-6 rounded bg-gray-100"></div>
              </div>
            </div>

            <!-- Contact header -->
            <div class="bg-gradient-to-r from-navy via-navy-dark to-navy text-white px-6 py-5 flex items-center gap-4">
              <div class="w-14 h-14 rounded-full bg-white text-navy font-bold flex items-center justify-center text-lg">SJ</div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <h4 class="font-heading font-bold text-lg">Sarah Jenkins</h4>
                  <span class="inline-flex items-center gap-1 bg-teal/20 text-teal-light text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full">VIP</span>
                </div>
                <div class="text-sm text-white/70">VP Operations · TechCorp · Deal $42,000</div>
              </div>
              <button class="hidden sm:inline-flex items-center gap-1.5 bg-teal hover:bg-teal-light text-white text-xs font-semibold px-3 py-2 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                Call
              </button>
            </div>

            <!-- Timeline -->
            <div class="p-6 space-y-5">
              <div class="text-[10px] font-bold uppercase tracking-widest text-muted">Activity timeline</div>

              <!-- Event: Call -->
              <div class="relative pl-8">
                <div class="absolute left-0 top-0 w-6 h-6 rounded-full bg-teal text-white flex items-center justify-center ring-4 ring-teal/10">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                </div>
                <div class="absolute left-[11px] top-7 w-0.5 h-full bg-gray-200"></div>
                <div class="bg-cream border border-gray-100 rounded-xl p-4">
                  <div class="flex items-center justify-between mb-1">
                    <div class="font-semibold text-sm text-navy">Outbound call · 12m 04s</div>
                    <div class="text-[10px] text-muted">Today · 2:14 PM</div>
                  </div>
                  <div class="text-sm text-muted mb-3">Reviewed pricing for the enterprise tier. Sarah to confirm with procurement by Friday.</div>
                  <div class="flex items-center gap-2">
                    <button class="inline-flex items-center gap-1 text-[11px] font-semibold text-teal-dark hover:text-teal bg-white border border-gray-200 rounded-md px-2 py-1">
                      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7.75a.75.75 0 011.14-.64l3.5 2.25a.75.75 0 010 1.28l-3.5 2.25A.75.75 0 018 12.25v-4.5z"/></svg>
                      Play recording
                    </button>
                    <span class="text-[10px] text-muted">Auto-linked to deal · Acme Enterprise</span>
                  </div>
                </div>
              </div>

              <!-- Event: Email -->
              <div class="relative pl-8">
                <div class="absolute left-0 top-0 w-6 h-6 rounded-full bg-navy text-white flex items-center justify-center ring-4 ring-navy/10">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                </div>
                <div class="absolute left-[11px] top-7 w-0.5 h-full bg-gray-200"></div>
                <div class="bg-cream border border-gray-100 rounded-xl p-4">
                  <div class="flex items-center justify-between mb-1">
                    <div class="font-semibold text-sm text-navy">Email · Re: Contract terms</div>
                    <div class="text-[10px] text-muted">Yesterday</div>
                  </div>
                  <div class="text-sm text-muted">"Thanks for the walkthrough — the team is aligned. Let's do a final call Thursday…"</div>
                </div>
              </div>

              <!-- Event: Ticket -->
              <div class="relative pl-8">
                <div class="absolute left-0 top-0 w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center ring-4 ring-amber-500/10">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01"/></svg>
                </div>
                <div class="bg-cream border border-gray-100 rounded-xl p-4">
                  <div class="flex items-center justify-between mb-1">
                    <div class="font-semibold text-sm text-navy">Ticket #4821 · API rate limit question</div>
                    <div class="text-[10px] text-muted bg-amber-50 text-amber-700 border border-amber-100 px-2 py-0.5 rounded-full">SLA 2h left</div>
                  </div>
                  <div class="text-sm text-muted">Support opened a ticket after the call. Assigned to Marcus Rivera.</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- How it works -->
  <section id="how-it-works" class="bg-white py-24 lg:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-20">
        <div class="eyebrow-rule text-teal-dark font-bold tracking-widest uppercase text-xs mb-4">How it works</div>
        <h2 class="text-4xl lg:text-5xl font-extrabold text-navy font-heading leading-[1.05] tracking-tight mb-5">Get up and running in minutes.</h2>
        <p class="text-lg text-muted">No onboarding calls. No consulting engagements. Just sign up and go.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-6 relative">
        <div class="relative step-connector">
          <div class="w-12 h-12 rounded-xl bg-navy text-white flex items-center justify-center font-heading font-bold text-lg mb-5 shadow-md">01</div>
          <h4 class="text-xl font-bold text-navy mb-3 font-heading">Create your workspace</h4>
          <p class="text-muted leading-relaxed">Sign up, invite your team, and configure pipelines that match how you actually sell. Role-based permissions from day one.</p>
        </div>
        <div class="relative step-connector">
          <div class="w-12 h-12 rounded-xl bg-navy text-white flex items-center justify-center font-heading font-bold text-lg mb-5 shadow-md">02</div>
          <h4 class="text-xl font-bold text-navy mb-3 font-heading">Import & connect calling</h4>
          <p class="text-muted leading-relaxed">Bring in existing leads via CSV and enable browser calling through Twilio with a single click.</p>
        </div>
        <div class="relative">
          <div class="w-12 h-12 rounded-xl bg-teal text-white flex items-center justify-center font-heading font-bold text-lg mb-5 shadow-md">03</div>
          <h4 class="text-xl font-bold text-navy mb-3 font-heading">Close deals & resolve tickets</h4>
          <p class="text-muted leading-relaxed">Every interaction is on one timeline. Sales and support stop stepping on each other.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonial -->
  <section id="testimonials" class="py-24 bg-gradient-to-br from-navy-dark via-navy to-navy-dark relative overflow-hidden">
    <div class="absolute inset-0 bg-dots opacity-15"></div>
    <div class="absolute top-0 right-0 w-[600px] h-[600px] rounded-full bg-teal/20 blur-3xl translate-x-1/3 -translate-y-1/3"></div>

    <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <svg class="w-14 h-14 text-teal mb-8 opacity-80" fill="currentColor" viewBox="0 0 24 24">
        <path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179z"/>
      </svg>
      <blockquote class="font-heading text-white font-semibold text-2xl lg:text-4xl leading-[1.2] tracking-tight mb-10 text-pretty">
        We cut our ticket resolution time nearly in half after moving from our old CRM. Having calls, deal history, and support tickets in one thread means our team finally stops losing context at the handoff.
      </blockquote>
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-teal to-teal-dark text-white font-bold flex items-center justify-center">AP</div>
        <div>
          <div class="text-white font-semibold">Alicia Park</div>
          <div class="text-white/60 text-sm">Head of Revenue Operations · Ridgeview Logistics</div>
        </div>
        <div class="hidden sm:flex ml-auto items-center gap-1 bg-white/5 border border-white/10 rounded-full px-4 py-2">
          <svg class="w-4 h-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
          <svg class="w-4 h-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
          <svg class="w-4 h-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
          <svg class="w-4 h-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
          <svg class="w-4 h-4 text-amber-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section id="faq" class="bg-white py-24 lg:py-32">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-14">
        <div class="eyebrow-rule text-teal-dark font-bold tracking-widest uppercase text-xs mb-4">FAQ</div>
        <h2 class="text-4xl lg:text-5xl font-extrabold text-navy font-heading tracking-tight leading-[1.1]">Frequently asked questions</h2>
      </div>

      <div class="space-y-3">
        <details class="group bg-cream border border-gray-200 rounded-2xl p-6 hover:border-teal/40 transition-colors open:bg-white open:shadow-soft" open>
          <summary class="flex justify-between items-center cursor-pointer font-semibold text-navy font-heading">
            <span>Is Highland Core really free to use?</span>
            <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center group-open:bg-teal group-open:border-teal group-open:text-white transition-all">
              <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
          </summary>
          <p class="text-muted leading-relaxed mt-4 pr-12">Yes. We're in early access, so the platform is free while we refine features with our first users. Paid plans arrive later with generous grandfathering for early signups.</p>
        </details>

        <details class="group bg-cream border border-gray-200 rounded-2xl p-6 hover:border-teal/40 transition-colors open:bg-white open:shadow-soft">
          <summary class="flex justify-between items-center cursor-pointer font-semibold text-navy font-heading">
            <span>Do I need to install anything?</span>
            <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center group-open:bg-teal group-open:border-teal group-open:text-white transition-all">
              <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
          </summary>
          <p class="text-muted leading-relaxed mt-4 pr-12">No — everything runs in the browser, including calling via Twilio. Just sign up and start managing leads, deals, and tickets immediately.</p>
        </details>

        <details class="group bg-cream border border-gray-200 rounded-2xl p-6 hover:border-teal/40 transition-colors open:bg-white open:shadow-soft">
          <summary class="flex justify-between items-center cursor-pointer font-semibold text-navy font-heading">
            <span>Can I import data from another CRM?</span>
            <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center group-open:bg-teal group-open:border-teal group-open:text-white transition-all">
              <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
          </summary>
          <p class="text-muted leading-relaxed mt-4 pr-12">Yes. You can import contacts, leads, and deals via CSV or Excel. Native integrations with popular CRMs are on the roadmap.</p>
        </details>

        <details class="group bg-cream border border-gray-200 rounded-2xl p-6 hover:border-teal/40 transition-colors open:bg-white open:shadow-soft">
          <summary class="flex justify-between items-center cursor-pointer font-semibold text-navy font-heading">
            <span>Who is Highland Core built for?</span>
            <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center group-open:bg-teal group-open:border-teal group-open:text-white transition-all">
              <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
          </summary>
          <p class="text-muted leading-relaxed mt-4 pr-12">Small to mid-sized teams that want sales and support in one place — especially teams doing outbound calling alongside ticket-based support.</p>
        </details>

        <details class="group bg-cream border border-gray-200 rounded-2xl p-6 hover:border-teal/40 transition-colors open:bg-white open:shadow-soft">
          <summary class="flex justify-between items-center cursor-pointer font-semibold text-navy font-heading">
            <span>Is my data secure?</span>
            <div class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center group-open:bg-teal group-open:border-teal group-open:text-white transition-all">
              <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
          </summary>
          <p class="text-muted leading-relaxed mt-4 pr-12">All data is encrypted in transit and at rest. Role-based permissions, full audit logging, and encrypted call recordings ship by default.</p>
        </details>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="relative overflow-hidden">
    <div class="relative bg-gradient-to-br from-navy via-navy-dark to-navy text-white py-24 lg:py-28">
      <div class="absolute inset-0 bg-dots opacity-15"></div>
      <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-teal/20 blur-3xl"></div>
      <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-teal/10 blur-3xl"></div>

      <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-4 py-1.5 mb-6">
          <span class="w-2 h-2 rounded-full bg-teal-light animate-pulse"></span>
          <span class="text-xs font-semibold uppercase tracking-wider text-white/90">Early access · Free</span>
        </div>
        <h2 class="text-4xl md:text-5xl lg:text-6xl font-extrabold font-heading mb-6 leading-[1.05] tracking-tight">
          Ready to unify your <br class="hidden md:block"/>
          <span class="grad-text">customer experience?</span>
        </h2>
        <p class="text-lg md:text-xl text-white/70 mb-10 max-w-2xl mx-auto">
          Join the teams replacing their patchwork of sales and support tools with a single, purpose-built workspace.
        </p>
        <div class="flex flex-col sm:flex-row justify-center items-stretch sm:items-center gap-3 sm:gap-4">
          <a href="{{ $landlordLoginUrl }}" class="btn-shine bg-teal hover:bg-teal-light text-white px-8 py-4 rounded-xl font-bold text-lg transition-all shadow-glow inline-flex items-center justify-center gap-2">
            Open admin login
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </a>
          <a href="#features" class="bg-white/5 hover:bg-white/10 border border-white/20 text-white px-8 py-4 rounded-xl font-bold text-lg transition-all inline-flex items-center justify-center gap-2">
            Talk to sales
          </a>
        </div>
        <div class="mt-8 flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm text-white/60 font-medium">
          <div class="flex items-center gap-2"><svg class="w-4 h-4 text-teal-light" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>No credit card required</div>
          <div class="flex items-center gap-2"><svg class="w-4 h-4 text-teal-light" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>Setup in under 10 minutes</div>
          <div class="flex items-center gap-2"><svg class="w-4 h-4 text-teal-light" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>Cancel any time</div>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- Footer -->
<footer class="bg-dark text-white/60 py-16 border-t border-white/5">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-10 mb-12">
      <div class="col-span-2">
        <img src="{{ asset('images/logo.png') }}" alt="Highland Core" class="h-16 w-auto mix-blend-screen mb-4">
        <p class="text-sm leading-relaxed max-w-xs">The unified workspace for sales, support, and customer communication.</p>
      </div>
      <div>
        <div class="text-white text-xs font-bold uppercase tracking-widest mb-4">Product</div>
        <ul class="space-y-2 text-sm">
          <li><a href="#features" class="hover:text-teal transition-colors">Features</a></li>
          <li><a href="#product" class="hover:text-teal transition-colors">Product tour</a></li>
          <li><a href="#how-it-works" class="hover:text-teal transition-colors">How it works</a></li>
          <li><a href="#" class="hover:text-teal transition-colors">Changelog</a></li>
        </ul>
      </div>
      <div>
        <div class="text-white text-xs font-bold uppercase tracking-widest mb-4">Company</div>
        <ul class="space-y-2 text-sm">
          <li><a href="#" class="hover:text-teal transition-colors">About</a></li>
          <li><a href="#" class="hover:text-teal transition-colors">Customers</a></li>
          <li><a href="#" class="hover:text-teal transition-colors">Careers</a></li>
          <li><a href="#" class="hover:text-teal transition-colors">Contact</a></li>
        </ul>
      </div>
      <div>
        <div class="text-white text-xs font-bold uppercase tracking-widest mb-4">Legal</div>
        <ul class="space-y-2 text-sm">
          <li><a href="#" class="hover:text-teal transition-colors">Privacy</a></li>
          <li><a href="#" class="hover:text-teal transition-colors">Terms</a></li>
          <li><a href="#" class="hover:text-teal transition-colors">Security</a></li>
          <li><a href="#" class="hover:text-teal transition-colors">DPA</a></li>
        </ul>
      </div>
    </div>
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 pt-8 border-t border-white/10 text-xs">
      <div>&copy; {{ date('Y') }} Highland Core. All rights reserved.</div>
      <div class="flex items-center gap-2 text-white/50">
        <span class="w-2 h-2 rounded-full bg-green-400"></span>
        All systems operational
      </div>
    </div>
  </div>
</footer>

<script>
  // Sticky header border on scroll
  (function(){
    const hdr = document.getElementById('siteHeader');
    const onScroll = () => {
      if (window.scrollY > 8) hdr.classList.add('border-gray-200/80','shadow-sm');
      else hdr.classList.remove('border-gray-200/80','shadow-sm');
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  })();

  // Reveal on scroll
  (function(){
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: 0.15 });
    document.querySelectorAll('.reveal').forEach(el => io.observe(el));
  })();

  // Animated counters
  (function(){
    const nums = document.querySelectorAll('[data-count]');
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        const target = parseFloat(el.getAttribute('data-count'));
        const suffix = el.getAttribute('data-suffix') || '';
        const isFloat = target % 1 !== 0;
        const duration = 1400;
        const start = performance.now();
        const tick = (now) => {
          const t = Math.min(1, (now - start) / duration);
          const eased = 1 - Math.pow(1 - t, 3);
          const val = target * eased;
          el.textContent = (isFloat ? val.toFixed(1) : Math.round(val)) + suffix;
          if (t < 1) requestAnimationFrame(tick);
          else el.textContent = target + suffix;
        };
        requestAnimationFrame(tick);
        io.unobserve(el);
      });
    }, { threshold: 0.4 });
    nums.forEach(n => io.observe(n));
  })();
</script>

</body>
</html>
