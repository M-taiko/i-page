<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $t['dir'] }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>i-Page — {{ $t['brand_tag'] }}</title>
    <meta name="description" content="{{ $t['hero']['sub'] }}">
    <meta name="theme-color" content="#6259ca">
    <link rel="icon" href="{{ asset('images/brand/logo-mark.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script>
        // Apply the saved theme (shared with the rest of the site) before first paint.
        try {
            var saved = localStorage.getItem('theme');
            if (!saved) saved = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', saved);
        } catch (e) {}
    </script>
    <style>
        :root {
            --bg: #f6f7fb; --bg2: #ffffff; --text: #0f172a; --muted: #5b6478; --line: rgba(15,23,42,.09);
            --p: #6259ca; --p2: #06b6d4; --p3: #ec4899; --ok: #22c55e; --warn: #f59e0b;
            --card: rgba(255,255,255,.72); --shadow: 0 10px 40px rgba(31,41,90,.10);
            --radius: 20px;
        }
        [data-theme="dark"] {
            --bg: #0b1020; --bg2: #11182e; --text: #e8ecf8; --muted: #9aa5c3; --line: rgba(255,255,255,.09);
            --card: rgba(22,30,56,.72); --shadow: 0 10px 40px rgba(0,0,0,.45);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Cairo', 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); line-height: 1.7; overflow-x: hidden; -webkit-font-smoothing: antialiased; }
        [dir="ltr"] body { font-family: 'Inter', 'Cairo', system-ui, sans-serif; }
        a { color: inherit; text-decoration: none; }
        .wrap { width: min(1180px, 100% - 40px); margin-inline: auto; }
        section { position: relative; padding: 96px 0; }
        h1, h2, h3 { line-height: 1.2; letter-spacing: -.01em; }
        .grad { background: linear-gradient(120deg, var(--p), var(--p2) 55%, var(--p3)); -webkit-background-clip: text; background-clip: text; color: transparent; background-size: 200% auto; animation: shine 6s linear infinite; }
        .sec-head { text-align: center; max-width: 720px; margin: 0 auto 56px; }
        .sec-head h2 { font-size: clamp(28px, 4.4vw, 44px); font-weight: 800; margin-bottom: 12px; }
        .sec-head p { color: var(--muted); font-size: 18px; }
        .pill { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 999px; background: var(--card); border: 1px solid var(--line); font-size: 13px; font-weight: 700; backdrop-filter: blur(10px); }
        .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--ok); position: relative; }
        .dot::after { content: ''; position: absolute; inset: -4px; border-radius: 50%; border: 2px solid var(--ok); animation: ping 1.8s ease-out infinite; }

        /* ---------- background ---------- */
        .aurora { position: fixed; inset: 0; z-index: -1; overflow: hidden; background: var(--bg); }
        .aurora i { position: absolute; width: 46vmax; height: 46vmax; border-radius: 50%; filter: blur(90px); opacity: .45; animation: drift 22s ease-in-out infinite alternate; }
        .aurora i:nth-child(1) { background: #6259ca; top: -18vmax; inset-inline-start: -10vmax; }
        .aurora i:nth-child(2) { background: #06b6d4; bottom: -22vmax; inset-inline-end: -8vmax; animation-duration: 28s; animation-delay: -6s; }
        .aurora i:nth-child(3) { background: #ec4899; top: 30%; inset-inline-start: 38%; width: 30vmax; height: 30vmax; opacity: .25; animation-duration: 34s; animation-delay: -12s; }
        [data-theme="dark"] .aurora i { opacity: .28; }
        .aurora::after { content: ''; position: absolute; inset: 0; background-image: radial-gradient(var(--line) 1px, transparent 1px); background-size: 26px 26px; mask-image: radial-gradient(ellipse at 50% 30%, #000 0%, transparent 70%); }
        #progress { position: fixed; top: 0; inset-inline-start: 0; height: 3px; width: 0; z-index: 100; background: linear-gradient(90deg, var(--p), var(--p2), var(--p3)); }

        /* ---------- nav ---------- */
        nav.top { position: sticky; top: 0; z-index: 90; backdrop-filter: blur(16px); background: color-mix(in srgb, var(--bg) 70%, transparent); border-bottom: 1px solid var(--line); }
        nav.top .wrap { display: flex; align-items: center; justify-content: space-between; height: 68px; gap: 16px; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 20px; }
        .brand img { width: 34px; height: 34px; border-radius: 10px; animation: bob 4s ease-in-out infinite; }
        .links { display: flex; align-items: center; gap: 22px; font-weight: 600; font-size: 15px; color: var(--muted); }
        .links a:hover { color: var(--p); }
        .actions { display: flex; align-items: center; gap: 10px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 22px; border-radius: 14px; font-weight: 700; font-size: 15px; border: 1px solid transparent; cursor: pointer; transition: transform .2s, box-shadow .2s; font-family: inherit; }
        .btn:hover { transform: translateY(-2px); }
        .btn-p { background: linear-gradient(120deg, var(--p), #8b5cf6); color: #fff; box-shadow: 0 8px 24px rgba(98,89,202,.4); }
        .btn-p:hover { box-shadow: 0 12px 30px rgba(98,89,202,.55); }
        .btn-o { background: var(--card); border-color: var(--line); color: var(--text); backdrop-filter: blur(10px); }
        .icon-btn { width: 40px; height: 40px; border-radius: 12px; background: var(--card); border: 1px solid var(--line); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; color: var(--text); font-family: inherit; font-weight: 700; }
        #burger { display: none; }
        @media (max-width: 980px) {
            .links { position: fixed; inset: 68px 0 auto 0; flex-direction: column; align-items: flex-start; padding: 20px; gap: 14px; background: var(--bg2); border-bottom: 1px solid var(--line); transform: translateY(-130%); transition: transform .3s; z-index: -1; }
            .links.open { transform: none; }
            #burger { display: inline-flex; }
            .actions .btn-p { padding: 9px 14px; font-size: 14px; }
        }

        /* ---------- hero ---------- */
        .hero { padding: 70px 0 90px; }
        .hero .wrap { display: grid; grid-template-columns: 1.1fr .9fr; gap: 50px; align-items: center; }
        .hero h1 { font-size: clamp(36px, 5.6vw, 66px); font-weight: 800; margin: 18px 0; }
        .rotator { display: inline-block; position: relative; height: 1.25em; overflow: hidden; vertical-align: bottom; min-width: 3ch; }
        .rotator span { display: block; transition: transform .6s cubic-bezier(.7,0,.2,1), opacity .6s; }
        .hero p.sub { font-size: 19px; color: var(--muted); max-width: 560px; }
        .cta-row { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 30px; }
        .hero-meta { display: flex; gap: 22px; margin-top: 34px; flex-wrap: wrap; color: var(--muted); font-size: 14px; font-weight: 600; }
        .hero-meta span::before { content: '✓'; color: var(--ok); margin-inline-end: 6px; font-weight: 800; }
        @media (max-width: 900px) { .hero .wrap { grid-template-columns: 1fr; } .stage { order: -1; } }

        .stage { position: relative; display: flex; justify-content: center; min-height: 560px; perspective: 1200px; }
        .phone { width: 290px; height: 560px; border-radius: 42px; background: #0f172a; padding: 10px; box-shadow: 0 40px 80px rgba(60,50,140,.35), inset 0 0 0 2px rgba(255,255,255,.08); position: relative; transform: rotateY(-10deg) rotateX(4deg); animation: tilt 9s ease-in-out infinite; transform-style: preserve-3d; }
        [dir="rtl"] .phone { transform: rotateY(10deg) rotateX(4deg); animation-name: tiltRtl; }
        .phone::before { content: ''; position: absolute; top: 18px; left: 50%; transform: translateX(-50%); width: 90px; height: 22px; background: #0f172a; border-radius: 0 0 14px 14px; z-index: 5; }
        .screen { width: 100%; height: 100%; border-radius: 34px; overflow: hidden; background: #fff; position: relative; color: #0f172a; }
        .scr { position: absolute; inset: 0; padding: 44px 16px 16px; opacity: 0; }
        .scr1 { animation: scr1 10s infinite; background: linear-gradient(160deg, #1e1b4b, #312e81); color: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; gap: 18px; }
        .scr2 { animation: scr2 10s infinite; background: #f4f5fb; }
        .qrbox { width: 168px; height: 168px; background: #fff; border-radius: 18px; padding: 12px; position: relative; overflow: hidden; box-shadow: 0 0 0 6px rgba(255,255,255,.12); }
        .qrbox svg { width: 100%; height: 100%; display: block; }
        .beam { position: absolute; inset-inline: 0; height: 3px; background: linear-gradient(90deg, transparent, #22d3ee, transparent); box-shadow: 0 0 18px #22d3ee; animation: beam 2.4s ease-in-out infinite; }
        .scr1 b { font-size: 15px; letter-spacing: .02em; }
        .corner { position: absolute; width: 22px; height: 22px; border: 3px solid #22d3ee; }
        .corner.a { top: 4px; left: 4px; border-right: 0; border-bottom: 0; border-top-left-radius: 8px; }
        .corner.b { top: 4px; right: 4px; border-left: 0; border-bottom: 0; border-top-right-radius: 8px; }
        .corner.c { bottom: 4px; left: 4px; border-right: 0; border-top: 0; border-bottom-left-radius: 8px; }
        .corner.d { bottom: 4px; right: 4px; border-left: 0; border-top: 0; border-bottom-right-radius: 8px; }
        .mhead { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .mav { width: 38px; height: 38px; border-radius: 12px; background: linear-gradient(135deg, var(--p), var(--p3)); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; }
        .mcard { background: #fff; border-radius: 16px; padding: 12px; margin-bottom: 10px; box-shadow: 0 4px 14px rgba(15,23,42,.06); font-size: 13px; }
        .mcard .tag { display: inline-block; background: #22c55e; color: #fff; font-size: 10px; font-weight: 800; padding: 1px 8px; border-radius: 999px; margin-bottom: 6px; }
        .mimg { height: 74px; border-radius: 10px; margin-top: 8px; background: linear-gradient(120deg, #06b6d4, #6259ca, #ec4899); background-size: 200% 100%; animation: shine 4s linear infinite; }
        .bar { position: relative; height: 26px; border-radius: 8px; background: #eef0f8; margin-top: 7px; overflow: hidden; font-size: 11px; display: flex; align-items: center; justify-content: space-between; padding: 0 9px; font-weight: 700; }
        .bar i { position: absolute; inset: 0 auto 0 0; background: rgba(98,89,202,.28); width: 0; animation: grow 10s infinite; }
        [dir="rtl"] .bar i { inset: 0 0 0 auto; }
        .bar span { position: relative; }
        .chip { position: absolute; background: var(--card); border: 1px solid var(--line); border-radius: 16px; padding: 10px 14px; box-shadow: var(--shadow); backdrop-filter: blur(12px); font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 8px; animation: float 6s ease-in-out infinite; z-index: 4; }
        .chip.c1 { top: 70px; inset-inline-start: -10px; }
        .chip.c2 { bottom: 150px; inset-inline-end: -14px; animation-delay: -2s; }
        .chip.c3 { bottom: 40px; inset-inline-start: 6px; animation-delay: -4s; }
        @media (max-width: 520px) { .chip.c1 { inset-inline-start: 0; } .chip.c2 { inset-inline-end: 0; } }

        /* ---------- stats ---------- */
        .stats { padding: 40px 0 70px; }
        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
        .stat { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 28px 20px; text-align: center; box-shadow: var(--shadow); backdrop-filter: blur(12px); }
        .stat b { display: block; font-size: clamp(32px, 4vw, 48px); font-weight: 800; background: linear-gradient(120deg, var(--p), var(--p2)); -webkit-background-clip: text; background-clip: text; color: transparent; font-variant-numeric: tabular-nums; }
        .stat span { color: var(--muted); font-weight: 600; }
        .live { display: flex; justify-content: center; margin-top: 22px; }
        @media (max-width: 760px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }

        /* ---------- how ---------- */
        .steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 26px; position: relative; }
        .steps::before { content: ''; position: absolute; top: 58px; inset-inline: 16%; height: 2px; background: repeating-linear-gradient(90deg, var(--p) 0 10px, transparent 10px 20px); opacity: .5; }
        .step { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 30px 26px; text-align: center; box-shadow: var(--shadow); position: relative; backdrop-filter: blur(12px); }
        .step .ic { width: 76px; height: 76px; margin: 0 auto 18px; border-radius: 24px; background: linear-gradient(135deg, rgba(98,89,202,.18), rgba(6,182,212,.18)); display: flex; align-items: center; justify-content: center; font-size: 36px; position: relative; z-index: 1; }
        .step.in .ic { animation: pop .7s cubic-bezier(.3,1.6,.5,1) both; }
        .step h3 { font-size: 22px; margin-bottom: 8px; }
        .step p { color: var(--muted); }
        .num { position: absolute; top: 14px; inset-inline-end: 18px; font-size: 40px; font-weight: 800; opacity: .08; }
        @media (max-width: 860px) { .steps { grid-template-columns: 1fr; } .steps::before { display: none; } }

        /* ---------- marquee ---------- */
        .industries { padding: 70px 0; }
        .marq { overflow: hidden; direction: ltr; mask-image: linear-gradient(90deg, transparent, #000 10%, #000 90%, transparent); margin-bottom: 14px; }
        .track { display: flex; gap: 14px; width: max-content; animation: marq 40s linear infinite; }
        .marq.rev .track { animation-direction: reverse; animation-duration: 48s; }
        .marq:hover .track { animation-play-state: paused; }
        .tag-chip { white-space: nowrap; padding: 14px 24px; border-radius: 999px; background: var(--card); border: 1px solid var(--line); font-weight: 700; font-size: 17px; box-shadow: var(--shadow); backdrop-filter: blur(10px); }

        /* ---------- features ---------- */
        .filters { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-bottom: 34px; }
        .filters button { padding: 8px 18px; border-radius: 999px; background: var(--card); border: 1px solid var(--line); color: var(--text); font-weight: 700; cursor: pointer; font-family: inherit; font-size: 14px; transition: .2s; }
        .filters button.on, .filters button:hover { background: var(--p); color: #fff; border-color: var(--p); }
        .fgrid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .feat { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 26px; box-shadow: var(--shadow); backdrop-filter: blur(12px); transform-style: preserve-3d; transition: transform .25s ease, border-color .25s, opacity .4s; position: relative; overflow: hidden; }
        .feat::before { content: ''; position: absolute; inset: 0; background: radial-gradient(240px circle at var(--mx, 50%) var(--my, 0%), rgba(98,89,202,.16), transparent 60%); opacity: 0; transition: opacity .3s; }
        .feat:hover { border-color: var(--p); }
        .feat:hover::before { opacity: 1; }
        .feat .ic { width: 54px; height: 54px; border-radius: 16px; background: linear-gradient(135deg, rgba(98,89,202,.16), rgba(236,72,153,.14)); display: flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 14px; transition: transform .35s; }
        .feat:hover .ic { transform: rotate(-8deg) scale(1.12); }
        .feat h3 { font-size: 18px; margin-bottom: 6px; position: relative; }
        .feat p { color: var(--muted); font-size: 15px; position: relative; }
        .feat .tg { position: absolute; top: 18px; inset-inline-end: 18px; font-size: 11px; font-weight: 800; padding: 2px 10px; border-radius: 999px; background: rgba(98,89,202,.14); color: var(--p); }
        .feat .tg.new { background: rgba(236,72,153,.15); color: var(--p3); }
        .feat.hide { display: none; }
        @media (max-width: 960px) { .fgrid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 620px) { .fgrid { grid-template-columns: 1fr; } }

        /* ---------- roles ---------- */
        .roles { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; }
        .role { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 30px 26px; box-shadow: var(--shadow); backdrop-filter: blur(12px); transition: transform .3s; }
        .role:hover { transform: translateY(-8px); }
        .role .ic { font-size: 40px; margin-bottom: 10px; display: inline-block; animation: bob 5s ease-in-out infinite; }
        .role h3 { font-size: 20px; margin-bottom: 14px; }
        .role ul { list-style: none; display: grid; gap: 10px; }
        .role li { display: flex; gap: 10px; color: var(--muted); font-size: 15px; }
        .role li::before { content: '✓'; color: var(--ok); font-weight: 800; flex-shrink: 0; }
        @media (max-width: 960px) { .roles { grid-template-columns: 1fr; } }

        /* ---------- designs ---------- */
        .dgrid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; }
        .dcard { border-radius: var(--radius); padding: 22px 16px; text-align: center; color: #fff; position: relative; overflow: hidden; transition: transform .3s; min-height: 170px; }
        .dcard:hover { transform: translateY(-8px) rotate(-1deg); }
        .dcard:nth-child(1) { background: linear-gradient(135deg, #0ea5e9, #6366f1); }
        .dcard:nth-child(2) { background: linear-gradient(135deg, #2563eb, #059669); }
        .dcard:nth-child(3) { background: linear-gradient(135deg, #0d9488, #16a34a); }
        .dcard:nth-child(4) { background: linear-gradient(135deg, #ef4444, #f97316); }
        .dcard:nth-child(5) { background: linear-gradient(135deg, #334155, #0f766e); }
        .dcard .e { font-size: 40px; display: block; margin-bottom: 8px; }
        .dcard b { font-size: 18px; display: block; }
        .dcard small { opacity: .88; font-size: 12px; line-height: 1.5; display: block; margin-top: 6px; }
        .dcard::after { content: ''; position: absolute; inset: 0; background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,.25) 50%, transparent 70%); transform: translateX(-120%); transition: transform .8s; }
        .dcard:hover::after { transform: translateX(120%); }
        .subrow { margin-top: 44px; display: grid; grid-template-columns: 1fr 1fr; gap: 22px; }
        .box { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 24px; backdrop-filter: blur(12px); }
        .box h4 { margin-bottom: 14px; font-size: 16px; color: var(--muted); }
        .row-w { display: flex; flex-wrap: wrap; gap: 10px; }
        .fmt { padding: 10px 16px; border-radius: 14px; background: rgba(98,89,202,.1); font-weight: 700; animation: bob 4s ease-in-out infinite; }
        .fmt:nth-child(2) { animation-delay: -.6s; } .fmt:nth-child(3) { animation-delay: -1.2s; } .fmt:nth-child(4) { animation-delay: -1.8s; } .fmt:nth-child(5) { animation-delay: -2.4s; }
        .sty { width: 92px; }
        .sty i { display: block; height: 58px; border-radius: 10px; margin-bottom: 6px; }
        .sty:nth-child(1) i { background: var(--bg2); border: 1px solid var(--line); }
        .sty:nth-child(2) i { background: linear-gradient(180deg, #94a3b8 0 60%, var(--bg2) 60%); border: 1px solid var(--line); }
        .sty:nth-child(3) i { background: linear-gradient(135deg, var(--p), #1e293b); }
        .sty:nth-child(4) i { background: var(--bg2); border: 1px solid var(--line); border-inline-start: 5px solid var(--p); }
        .sty small { font-size: 12px; font-weight: 700; color: var(--muted); }
        @media (max-width: 960px) { .dgrid { grid-template-columns: repeat(2, 1fr); } .subrow { grid-template-columns: 1fr; } }

        /* ---------- roadmap ---------- */
        .road { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; position: relative; }
        .phase { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius); padding: 28px 24px; box-shadow: var(--shadow); backdrop-filter: blur(12px); position: relative; }
        .phase h3 { font-size: 20px; margin-bottom: 18px; display: flex; align-items: center; gap: 10px; }
        .phase .badge { width: 12px; height: 12px; border-radius: 50%; }
        .phase.now .badge { background: var(--ok); box-shadow: 0 0 0 0 rgba(34,197,94,.6); animation: pulse 2s infinite; }
        .phase.next .badge { background: var(--warn); } .phase.later .badge { background: var(--p3); }
        .phase.now { border-top: 4px solid var(--ok); } .phase.next { border-top: 4px solid var(--warn); } .phase.later { border-top: 4px solid var(--p3); }
        .phase ul { list-style: none; display: grid; gap: 12px; }
        .phase li { display: flex; gap: 10px; font-size: 15px; opacity: 0; transform: translateX(-14px); transition: opacity .5s, transform .5s; transition-delay: calc(var(--i) * 90ms); }
        [dir="rtl"] .phase li { transform: translateX(14px); }
        .phase.in li { opacity: 1; transform: none; }
        .phase li::before { flex-shrink: 0; font-weight: 800; }
        .phase.now li::before { content: '✓'; color: var(--ok); } .phase.next li::before { content: '◔'; color: var(--warn); } .phase.later li::before { content: '✦'; color: var(--p3); }
        .note { text-align: center; color: var(--muted); margin-top: 26px; font-size: 14px; }
        @media (max-width: 960px) { .road { grid-template-columns: 1fr; } }

        /* ---------- principles ---------- */
        .princ { padding-top: 40px; }
        .cloud { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-bottom: 40px; }
        .cloud span { padding: 12px 22px; border-radius: 16px; background: var(--card); border: 1px solid var(--line); font-weight: 800; box-shadow: var(--shadow); animation: float 7s ease-in-out infinite; }
        .cloud span:nth-child(2n) { animation-delay: -2s; } .cloud span:nth-child(3n) { animation-delay: -4s; }
        .tech { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; }
        .tech span { padding: 6px 14px; border-radius: 10px; background: rgba(98,89,202,.1); color: var(--p); font-weight: 700; font-size: 13px; font-family: 'Inter', monospace; }
        .sub-h { text-align: center; color: var(--muted); font-weight: 700; margin: 8px 0 14px; }

        /* ---------- cta + footer ---------- */
        .cta { margin: 20px auto 80px; border-radius: 32px; padding: 70px 30px; text-align: center; color: #fff; position: relative; overflow: hidden; background: linear-gradient(120deg, #4f46e5, #7c3aed 45%, #db2777); background-size: 200% 200%; animation: gradmove 10s ease infinite; }
        .cta::before { content: ''; position: absolute; inset: -40%; background: conic-gradient(from 0deg, transparent, rgba(255,255,255,.18), transparent 30%); animation: spin 9s linear infinite; }
        .cta > * { position: relative; }
        .cta h2 { font-size: clamp(28px, 4.4vw, 46px); font-weight: 800; margin-bottom: 10px; }
        .cta p { opacity: .9; font-size: 18px; margin-bottom: 28px; }
        .cta .btn-w { background: #fff; color: #4f46e5; }
        .cta .btn-g { background: rgba(255,255,255,.16); color: #fff; border-color: rgba(255,255,255,.4); }
        footer { padding: 30px 0 50px; text-align: center; color: var(--muted); font-size: 14px; border-top: 1px solid var(--line); }

        /* ---------- reveal ---------- */
        .reveal { opacity: 0; transform: translateY(34px); transition: opacity .8s cubic-bezier(.2,.7,.2,1), transform .8s cubic-bezier(.2,.7,.2,1); transition-delay: var(--d, 0ms); }
        .reveal.in { opacity: 1; transform: none; }

        /* ---------- keyframes ---------- */
        @keyframes shine { to { background-position: 200% center; } }
        @keyframes ping { 0% { transform: scale(.6); opacity: .9; } 100% { transform: scale(1.8); opacity: 0; } }
        @keyframes drift { to { transform: translate(8vmax, 6vmax) scale(1.15); } }
        @keyframes bob { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-7px); } }
        @keyframes float { 0%, 100% { transform: translateY(0) rotate(0); } 50% { transform: translateY(-14px) rotate(1.2deg); } }
        @keyframes tilt { 0%, 100% { transform: rotateY(-10deg) rotateX(4deg) translateY(0); } 50% { transform: rotateY(-4deg) rotateX(2deg) translateY(-12px); } }
        @keyframes tiltRtl { 0%, 100% { transform: rotateY(10deg) rotateX(4deg) translateY(0); } 50% { transform: rotateY(4deg) rotateX(2deg) translateY(-12px); } }
        @keyframes beam { 0% { top: 8%; } 50% { top: 90%; } 100% { top: 8%; } }
        @keyframes scr1 { 0%, 40% { opacity: 1; transform: scale(1); } 46%, 100% { opacity: 0; transform: scale(1.06); } }
        @keyframes scr2 { 0%, 42% { opacity: 0; transform: translateY(16px); } 50%, 92% { opacity: 1; transform: none; } 98%, 100% { opacity: 0; } }
        @keyframes grow { 0%, 56% { width: 0; } 70%, 92% { width: var(--w); } 100% { width: 0; } }
        @keyframes pop { 0% { transform: scale(.4) rotate(-20deg); opacity: 0; } 100% { transform: none; opacity: 1; } }
        @keyframes marq { to { transform: translateX(-50%); } }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(34,197,94,.6); } 70%, 100% { box-shadow: 0 0 0 12px rgba(34,197,94,0); } }
        @keyframes gradmove { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        @keyframes spin { to { transform: rotate(360deg); } }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
            .reveal, .phase li { opacity: 1 !important; transform: none !important; }
            .scr1 { opacity: 0 !important; } .scr2 { opacity: 1 !important; } .bar i { width: var(--w) !important; }
            html { scroll-behavior: auto; }
        }
    </style>
</head>
<body>
<div class="aurora"><i></i><i></i><i></i></div>
<div id="progress"></div>

<nav class="top">
    <div class="wrap">
        <a href="{{ url('/') }}" class="brand"><img src="{{ asset('images/brand/logo-mark.svg') }}" alt="i-Page"> i-Page</a>
        <div class="links" id="links">
            <a href="#how">{{ $t['nav']['how'] }}</a>
            <a href="#features">{{ $t['nav']['features'] }}</a>
            <a href="#roles">{{ $t['nav']['roles'] }}</a>
            <a href="#designs">{{ $t['nav']['designs'] }}</a>
            <a href="#roadmap">{{ $t['nav']['roadmap'] }}</a>
        </div>
        <div class="actions">
            <a class="icon-btn" href="{{ url('/') }}?lang={{ $other }}" title="Language">{{ $other === 'ar' ? 'ع' : 'EN' }}</a>
            <button class="icon-btn" id="themeBtn" type="button" aria-label="Theme">🌙</button>
            @if ($home)
                <a class="btn btn-p" href="{{ $home }}">{{ $t['nav']['workspace'] }}</a>
            @else
                <a class="btn btn-p" href="{{ route('login') }}">{{ $t['nav']['signin'] }}</a>
            @endif
            <button class="icon-btn" id="burger" type="button" aria-label="Menu">☰</button>
        </div>
    </div>
</nav>

{{-- ================= HERO ================= --}}
<header class="hero">
    <div class="wrap">
        <div>
            <span class="pill reveal"><span class="dot"></span> {{ $t['hero']['eyebrow'] }}</span>
            <h1 class="reveal" style="--d:80ms">
                {{ $t['hero']['title_before'] }}
                <span class="grad">{{ $t['hero']['title_after'] }}</span>
                <br><span class="rotator" id="rotator"><span>{{ $t['hero']['words'][0] }}</span></span>
            </h1>
            <p class="sub reveal" style="--d:160ms">{{ $t['hero']['sub'] }}</p>
            <div class="cta-row reveal" style="--d:240ms">
                <a href="{{ route('guest.home') }}" class="btn btn-p">{{ $t['hero']['cta_primary'] }} →</a>
                <a href="#features" class="btn btn-o">{{ $t['hero']['cta_secondary'] }}</a>
            </div>
            <div class="hero-meta reveal" style="--d:320ms">
                @foreach ($t['principles']['items'] as $item)
                    @if ($loop->index < 4)<span>{{ $item }}</span>@endif
                @endforeach
            </div>
        </div>

        <div class="stage reveal" style="--d:200ms">
            <div class="chip c1">🔔 <span>{{ $t['hero']['phone_post'] }}</span></div>
            <div class="chip c2">📊 <span>+128</span></div>
            <div class="chip c3">⭐ <span>4.9</span></div>
            <div class="phone">
                <div class="screen">
                    <div class="scr scr1">
                        <b>{{ $t['hero']['scan'] }}</b>
                        <div class="qrbox"><div class="corner a"></div><div class="corner b"></div><div class="corner c"></div><div class="corner d"></div><div id="qr"></div><div class="beam"></div></div>
                        <small style="opacity:.7">i-Page</small>
                    </div>
                    <div class="scr scr2">
                        <div class="mhead"><div class="mav">{{ mb_substr($t['hero']['phone_org'], 0, 1) }}</div><div><b style="font-size:14px">{{ $t['hero']['phone_org'] }}</b><div style="font-size:11px;color:#64748b">● online</div></div></div>
                        <div class="mcard"><span class="tag">NEW</span><div style="font-weight:700">{{ $t['hero']['phone_post'] }}</div><div class="mimg"></div></div>
                        <div class="mcard"><div style="font-weight:700">{{ $t['hero']['phone_poll'] }}</div>
                            <div class="bar" style="--w:62%"><i></i><span>🥐</span><span>62%</span></div>
                            <div class="bar" style="--w:28%"><i></i><span>🍳</span><span>28%</span></div>
                            <div class="bar" style="--w:10%"><i></i><span>🥗</span><span>10%</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- ================= STATS ================= --}}
<section class="stats">
    <div class="wrap">
        <div class="stat-grid">
            @foreach ([['organizations', $stats['organizations']], ['channels', $stats['channels']], ['posts', $stats['posts']], ['scans', $stats['scans']]] as [$key, $value])
                <div class="stat reveal" style="--d:{{ $loop->index * 90 }}ms"><b data-count="{{ $value }}">0</b><span>{{ $t['stats'][$key] }}</span></div>
            @endforeach
        </div>
        <div class="live reveal"><span class="pill"><span class="dot"></span> {{ $t['stats']['live'] }}</span></div>
    </div>
</section>

{{-- ================= HOW ================= --}}
<section id="how">
    <div class="wrap">
        <div class="sec-head reveal"><h2>{{ $t['how']['title'] }}</h2><p>{{ $t['how']['sub'] }}</p></div>
        <div class="steps">
            @foreach ($t['how']['steps'] as $step)
                <div class="step reveal" style="--d:{{ $loop->index * 140 }}ms">
                    <span class="num">0{{ $loop->iteration }}</span>
                    <div class="ic">{{ $step['icon'] }}</div>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= INDUSTRIES ================= --}}
<section class="industries">
    <div class="wrap"><div class="sec-head reveal"><h2>{{ $t['industries']['title'] }}</h2><p>{{ $t['industries']['sub'] }}</p></div></div>
    @php $half = (int) ceil(count($t['industries']['items']) / 2); $rows = [array_slice($t['industries']['items'], 0, $half), array_slice($t['industries']['items'], $half)]; @endphp
    @foreach ($rows as $i => $row)
        <div class="marq {{ $i ? 'rev' : '' }}"><div class="track">
            @for ($k = 0; $k < 4; $k++) @foreach ($row as $item)<span class="tag-chip">{{ $item }}</span>@endforeach @endfor
        </div></div>
    @endforeach
</section>

{{-- ================= FEATURES ================= --}}
<section id="features">
    <div class="wrap">
        <div class="sec-head reveal"><h2>{{ $t['features']['title'] }}</h2><p>{{ $t['features']['sub'] }}</p></div>
        @php $tags = collect($t['features']['items'])->pluck('tag')->unique()->values(); @endphp
        <div class="filters reveal">
            <button class="on" data-f="*">{{ $lang === 'ar' ? 'الكل' : 'All' }}</button>
            @foreach ($tags as $tag)<button data-f="{{ $tag }}">{{ $tag }}</button>@endforeach
        </div>
        <div class="fgrid">
            @foreach ($t['features']['items'] as $item)
                <article class="feat reveal" data-tag="{{ $item['tag'] }}" style="--d:{{ ($loop->index % 3) * 90 }}ms">
                    <span class="tg {{ in_array($item['tag'], ['New', 'جديد'], true) ? 'new' : '' }}">{{ $item['tag'] }}</span>
                    <div class="ic">{{ $item['icon'] }}</div>
                    <h3>{{ $item['title'] }}</h3>
                    <p>{{ $item['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= ROLES ================= --}}
<section id="roles">
    <div class="wrap">
        <div class="sec-head reveal"><h2>{{ $t['roles']['title'] }}</h2><p>{{ $t['roles']['sub'] }}</p></div>
        <div class="roles">
            @foreach ($t['roles']['items'] as $role)
                <div class="role reveal" style="--d:{{ $loop->index * 120 }}ms">
                    <div class="ic">{{ $role['icon'] }}</div>
                    <h3>{{ $role['title'] }}</h3>
                    <ul>@foreach ($role['points'] as $point)<li>{{ $point }}</li>@endforeach</ul>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ================= DESIGNS ================= --}}
<section id="designs">
    <div class="wrap">
        <div class="sec-head reveal"><h2>{{ $t['designs']['title'] }}</h2><p>{{ $t['designs']['sub'] }}</p></div>
        <div class="dgrid">
            @foreach ($t['designs']['cards'] as $card)
                <div class="dcard reveal" style="--d:{{ $loop->index * 90 }}ms"><span class="e">{{ $card['icon'] }}</span><b>{{ $card['name'] }}</b><small>{{ $card['text'] }}</small></div>
            @endforeach
        </div>
        <div class="subrow">
            <div class="box reveal"><h4>{{ $t['designs']['formats_title'] }}</h4>
                <div class="row-w">@foreach ($t['designs']['formats'] as [$emoji, $label])<span class="fmt">{{ $emoji }} {{ $label }}</span>@endforeach</div></div>
            <div class="box reveal" style="--d:120ms"><h4>{{ $t['designs']['styles_title'] }}</h4>
                <div class="row-w">@foreach ($t['designs']['styles'] as $style)<div class="sty"><i></i><small>{{ $style }}</small></div>@endforeach</div></div>
        </div>
    </div>
</section>

{{-- ================= ROADMAP ================= --}}
<section id="roadmap">
    <div class="wrap">
        <div class="sec-head reveal"><h2>{{ $t['roadmap']['title'] }}</h2><p>{{ $t['roadmap']['sub'] }}</p></div>
        <div class="road">
            @foreach ($t['roadmap']['phases'] as $phase)
                <div class="phase {{ $phase['key'] }}">
                    <h3><span class="badge"></span> {{ $phase['label'] }}</h3>
                    <ul>@foreach ($phase['items'] as $item)<li style="--i:{{ $loop->index }}">{{ $item }}</li>@endforeach</ul>
                </div>
            @endforeach
        </div>
        <p class="note">{{ $t['roadmap']['note'] }}</p>
    </div>
</section>

{{-- ================= PRINCIPLES + TECH ================= --}}
<section class="princ">
    <div class="wrap">
        <div class="sec-head reveal"><h2>{{ $t['principles']['title'] }}</h2></div>
        <div class="cloud">@foreach ($t['principles']['items'] as $item)<span>{{ $item }}</span>@endforeach</div>
        <div class="sub-h">{{ $t['principles']['tech_title'] }}</div>
        <div class="tech">@foreach ($t['principles']['tech'] as $item)<span>{{ $item }}</span>@endforeach</div>
    </div>
</section>

{{-- ================= CTA ================= --}}
<div class="wrap">
    <div class="cta reveal">
        <h2>{{ $t['cta']['title'] }}</h2>
        <p>{{ $t['cta']['text'] }}</p>
        <div class="cta-row" style="justify-content:center;margin-top:0">
            <a class="btn btn-w" href="{{ route('guest.home') }}">{{ $t['cta']['browse'] }}</a>
            @if ($home)
                <a class="btn btn-g" href="{{ $home }}">{{ $t['nav']['workspace'] }}</a>
            @else
                <a class="btn btn-g" href="{{ route('login') }}">{{ $t['cta']['signin'] }}</a>
                @if (Route::has('register'))<a class="btn btn-g" href="{{ route('register') }}">{{ $t['cta']['register'] }}</a>@endif
            @endif
        </div>
    </div>
</div>

<footer><div class="wrap">© {{ date('Y') }} i-Page · {{ $t['footer'] }} · {{ $t['brand_tag'] }}</div></footer>

<script>
    (function () {
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var root = document.documentElement;

        // ----- theme toggle (shared 'theme' key with the rest of the site) -----
        var themeBtn = document.getElementById('themeBtn');
        function paintTheme() { themeBtn.textContent = root.getAttribute('data-theme') === 'dark' ? '☀️' : '🌙'; }
        themeBtn.addEventListener('click', function () {
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
            paintTheme();
        });
        paintTheme();

        // ----- mobile menu -----
        var links = document.getElementById('links');
        document.getElementById('burger').addEventListener('click', function () { links.classList.toggle('open'); });
        links.addEventListener('click', function (e) { if (e.target.tagName === 'A') links.classList.remove('open'); });

        // ----- scroll progress -----
        var bar = document.getElementById('progress');
        function onScroll() {
            var h = document.documentElement.scrollHeight - window.innerHeight;
            bar.style.width = (h > 0 ? (window.scrollY / h) * 100 : 0) + '%';
        }
        window.addEventListener('scroll', onScroll, { passive: true }); onScroll();

        // ----- reveal on scroll + count-up -----
        function countUp(el) {
            var target = parseInt(el.dataset.count, 10) || 0;
            if (reduce || target === 0) { el.textContent = target.toLocaleString(); return; }
            var start = null, dur = 1600;
            function tick(ts) {
                if (!start) start = ts;
                var p = Math.min((ts - start) / dur, 1), eased = 1 - Math.pow(1 - p, 3);
                el.textContent = Math.round(target * eased).toLocaleString();
                if (p < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (!en.isIntersecting) return;
                en.target.classList.add('in');
                en.target.querySelectorAll('[data-count]').forEach(countUp);
                io.unobserve(en.target);
            });
        }, { threshold: 0.15 });
        document.querySelectorAll('.reveal, .phase').forEach(function (el) { io.observe(el); });

        // ----- rotating industry word -----
        var words = {!! json_encode($t['hero']['words'], JSON_UNESCAPED_UNICODE) !!};
        var rot = document.getElementById('rotator'), i = 0;
        if (!reduce && words.length > 1) {
            setInterval(function () {
                var old = rot.firstElementChild;
                i = (i + 1) % words.length;
                var nxt = document.createElement('span');
                nxt.textContent = words[i];
                nxt.style.opacity = '0';
                rot.appendChild(nxt);   // sits right below the current word
                requestAnimationFrame(function () { requestAnimationFrame(function () {
                    old.style.transform = 'translateY(-100%)'; old.style.opacity = '0';
                    nxt.style.transform = 'translateY(-100%)'; nxt.style.opacity = '1';
                }); });
                setTimeout(function () {
                    // Once the old word is gone, the new one is at the top naturally: drop the transform without animating.
                    nxt.style.transition = 'none'; old.remove(); nxt.style.transform = '';
                    void nxt.offsetWidth; nxt.style.transition = '';
                }, 700);
            }, 2400);
        }

        // ----- decorative QR drawn in SVG -----
        var n = 21, cells = '', seed = 7;
        function rnd() { seed = (seed * 16807) % 2147483647; return seed / 2147483647; }
        function finder(x, y) { return x < 7 && y < 7 || x > n - 8 && y < 7 || x < 7 && y > n - 8; }
        function finderOn(x, y) {
            var fx = x > n - 8 ? x - (n - 7) : x, fy = y > n - 8 ? y - (n - 7) : y;
            return fx === 0 || fx === 6 || fy === 0 || fy === 6 || (fx >= 2 && fx <= 4 && fy >= 2 && fy <= 4);
        }
        for (var y = 0; y < n; y++) for (var x = 0; x < n; x++) {
            var on = finder(x, y) ? finderOn(x, y) : rnd() > 0.52;
            if (on) cells += '<rect x="' + x + '" y="' + y + '" width="1.02" height="1.02" rx=".18"/>';
        }
        document.getElementById('qr').innerHTML = '<svg viewBox="0 0 ' + n + ' ' + n + '" fill="#1e1b4b" shape-rendering="crispEdges">' + cells + '</svg>';

        // ----- feature filters -----
        var feats = document.querySelectorAll('.feat');
        document.querySelectorAll('.filters button').forEach(function (b) {
            b.addEventListener('click', function () {
                document.querySelectorAll('.filters button').forEach(function (x) { x.classList.remove('on'); });
                b.classList.add('on');
                feats.forEach(function (f) { f.classList.toggle('hide', b.dataset.f !== '*' && f.dataset.tag !== b.dataset.f); });
            });
        });

        // ----- spotlight + tilt on feature cards (pointer devices only) -----
        if (!reduce && window.matchMedia('(hover: hover)').matches) {
            feats.forEach(function (card) {
                card.addEventListener('mousemove', function (e) {
                    var r = card.getBoundingClientRect(), x = e.clientX - r.left, y = e.clientY - r.top;
                    card.style.setProperty('--mx', x + 'px'); card.style.setProperty('--my', y + 'px');
                    card.style.transform = 'perspective(700px) rotateX(' + ((y / r.height - .5) * -6) + 'deg) rotateY(' + ((x / r.width - .5) * 6) + 'deg) translateY(-4px)';
                });
                card.addEventListener('mouseleave', function () { card.style.transform = ''; });
            });
        }
    })();
</script>
</body>
</html>
