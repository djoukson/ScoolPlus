<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
.sp{--ink:#16213e;--mut:#6b7590;--line:#e6e9f2;--bg:#fff;--cobalt:#2b4acb;--mint:#12a594;--amber:#e8a317;--coral:#e5484d;--sky:#2a9bd8;--slate:#5b6784;font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:var(--ink)}
.sp h6,.sp .sp-h{font-weight:700;font-size:.95rem;margin:0}
.sp-card{background:var(--bg);border:1px solid var(--line);border-radius:14px;box-shadow:0 1px 2px rgba(22,33,62,.04);padding:18px}
.sp-card>.sp-h{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
.sp-grid{display:grid;gap:14px;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))}
.sp-layout{display:grid;gap:16px;grid-template-columns:minmax(0,2fr) minmax(280px,1fr)}
@media(max-width:991px){.sp-layout{grid-template-columns:1fr}}
.sp-stack{display:grid;gap:16px;align-content:start}
/* stat */
.sp-stat{display:flex;gap:14px;align-items:center;background:var(--bg);border:1px solid var(--line);border-left:4px solid var(--c);border-radius:14px;padding:16px;box-shadow:0 1px 2px rgba(22,33,62,.04)}
.sp-stat-ico{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;background:color-mix(in srgb,var(--c) 12%,#fff);color:var(--c);font-size:1.15rem;flex:none}
.sp-stat-val{font-size:1.55rem;font-weight:800;line-height:1.1;font-variant-numeric:tabular-nums}
.sp-stat-lbl{color:var(--mut);font-size:.82rem;font-weight:500}
.sp-stat-link{display:inline-block;margin-top:4px;font-size:.78rem;font-weight:600;color:var(--c);text-decoration:none}
.sp-stat-link i{transition:transform .2s}.sp-stat-link:hover i{transform:translateX(3px)}
.sp-success{--c:var(--mint)}.sp-danger{--c:var(--coral)}.sp-info{--c:var(--sky)}.sp-warning{--c:var(--amber)}.sp-primary{--c:var(--cobalt)}.sp-secondary{--c:var(--slate)}
/* actions */
.sp-acts{display:grid;gap:10px;grid-template-columns:repeat(auto-fill,minmax(130px,1fr))}
.sp-act{display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center;padding:16px 10px;border-radius:12px;border:1px solid var(--line);color:var(--ink);text-decoration:none;font-size:.82rem;font-weight:600;transition:border-color .2s,box-shadow .2s}
.sp-act i{width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:color-mix(in srgb,var(--c) 12%,#fff);color:var(--c)}
.sp-act:hover{border-color:var(--c);box-shadow:0 6px 16px rgba(22,33,62,.08);color:var(--ink)}
/* donut / bars */
.sp-donut{--p:0;width:120px;height:120px;border-radius:50%;background:conic-gradient(var(--coral) calc(var(--p)*1%),var(--sky) 0);display:grid;place-items:center;flex:none}
.sp-donut>div{width:82px;height:82px;border-radius:50%;background:#fff;display:grid;place-items:center;text-align:center;font-weight:800;font-size:1.1rem;line-height:1}
.sp-donut small{display:block;font-size:.65rem;color:var(--mut);font-weight:500}
.sp-leg{font-size:.85rem;display:grid;gap:8px}.sp-leg i{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:8px}
.sp-bar{height:8px;border-radius:99px;background:#eef0f7;overflow:hidden}
.sp-bar>span{display:block;height:100%;width:0;background:var(--c,var(--cobalt));border-radius:99px;transition:width 1s cubic-bezier(.2,.7,.2,1)}
.sp-row{display:grid;gap:5px;margin-bottom:12px;font-size:.85rem}.sp-row b{font-variant-numeric:tabular-nums}
.sp-row div:first-child{display:flex;justify-content:space-between}
.sp-link{font-size:.8rem;font-weight:600;color:var(--cobalt);text-decoration:none}
/* kpi */
.sp-kpi{text-align:left}.sp-kpi i{color:var(--c);margin-bottom:8px}.sp-kpi .l{color:var(--mut);font-size:.8rem;font-weight:500}.sp-kpi .v{font-weight:800;font-size:1.1rem}
/* calendar (IDs/classes conservés) */
.calendar-header{font-weight:700;text-transform:capitalize}
.calendar-container{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;font-size:.8rem;user-select:none}
.calendar-container div{text-align:center;padding:7px 0;border-radius:8px}
.calendar-day{font-weight:600;color:var(--mut)}
.calendar-date:hover{background:#eef1ff;color:var(--cobalt)}
.calendar-today{background:var(--cobalt);color:#fff;font-weight:700}
#liveClock{font-variant-numeric:tabular-nums;color:var(--ink);font-weight:700}
.sp-list{list-style:none;margin:0;padding:0}.sp-list li{padding:10px 0;border-bottom:1px solid var(--line);font-size:.85rem}.sp-list li:last-child{border:0}
.sp-list i{color:var(--mint);margin-right:8px}
.sp a:focus-visible{outline:2px solid var(--cobalt);outline-offset:2px}
@media(prefers-reduced-motion:reduce){.sp *{transition:none!important}}
</style>
<script>
document.addEventListener('DOMContentLoaded',()=>{
  document.querySelectorAll('[data-count]').forEach(el=>{const t=+el.dataset.count,s=performance.now();
    (function f(n){const k=Math.min((n-s)/900,1);el.textContent=Math.round(t*(1-Math.pow(1-k,3)));if(k<1)requestAnimationFrame(f)})(s)});
  requestAnimationFrame(()=>document.querySelectorAll('.sp-bar>span[data-w]').forEach(b=>b.style.width=b.dataset.w+'%'));
});
</script>
