<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NCF Oil Field Management</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@300;400;500&family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
/* ===== RESET & ROOT ===== */
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
:root{
  --bg:#f8fafc;
  --bg2:#ffffff;
  --bg3:#ffffff;

  --surface:#ffffff;
  --surface2:#f1f5f9;

  --border:#e2e8f0;
  --border2:#cbd5e1;

  --accent:#2563eb;
  --accent2:#2563eb;
  --accent3:#1d4ed8;

  --amber:#d97706;
  --red:#dc2626;
  --blue:#2563eb;

  --text:#0f172a;
  --text2:#475569;
  --text3:#94a3b8;

  --mono:'DM Mono',monospace;
  --display:'Syne',sans-serif;
  --body:'DM Sans',sans-serif;
}

body{
  background:var(--bg);
  color:var(--text);
  font-family:var(--body);
  font-size:14px;
  line-height:1.6;
  min-height:100vh;
}

/* ===== TOPBAR ===== */
.topbar{
  position:sticky;
  top:0;
  z-index:200;

  background:rgba(255,255,255,.95);
  backdrop-filter:blur(10px);

  border-bottom:1px solid var(--border);

  height:60px;
  padding:0 32px;
}

.logo{font-family:var(--display);font-size:15px;font-weight:800;letter-spacing:-.02em;color:var(--accent)}
.logo span{color:var(--text2);font-weight:400}
.nav{display:flex;gap:2px}
.nav-btn{
  padding:6px 16px;border-radius:6px;font-size:13px;font-weight:500;
  background:none;border:none;cursor:pointer;color:var(--text2);
  font-family:var(--body);transition:all .18s;
}
.nav-btn:hover{color:var(--text);background:var(--surface)}
.nav-btn.active{color:var(--accent);background:var(--surface2)}
.ver{font-family:var(--mono);font-size:10px;color:var(--text3);background:var(--surface);padding:3px 8px;border-radius:4px;border:1px solid var(--border)}

/* ===== PAGES ===== */
.page{display:none;padding:32px;max-width:1100px;margin:0 auto}
.page.active{display:block}

/* ===== HERO ===== */
.hero{
  border:1px solid var(--border);
  border-radius:20px;

  background:#ffffff;

  padding:40px;
  margin-bottom:24px;

  box-shadow:0 4px 20px rgba(15,23,42,.04);
}

.hero::after{
  background:radial-gradient(
    circle,
    rgba(37,99,235,.06) 0%,
    transparent 70%
  );
}

.hero-eye{font-family:var(--mono);font-size:11px;color:var(--accent);letter-spacing:.12em;text-transform:uppercase;margin-bottom:10px}
.hero-title{font-family:var(--display);font-size:30px;font-weight:800;letter-spacing:-.03em;margin-bottom:6px;line-height:1.15}
.hero-title span{color:var(--accent)}
.hero-sub{color:var(--text2);font-size:13px;max-width:460px}

/* ===== CARD ===== */
.card{
  background:#fff;
  border:1px solid var(--border);

  border-radius:16px;

  padding:24px;

  margin-bottom:16px;

  box-shadow:0 2px 12px rgba(15,23,42,.04);
}

.card-title{
  font-family:var(--mono);font-size:11px;font-weight:500;
  letter-spacing:.08em;text-transform:uppercase;
  color:var(--text2);margin-bottom:16px;
  display:flex;align-items:center;gap:8px;
}
.card-title .dot{width:5px;height:5px;border-radius:50%;background:var(--accent);flex-shrink:0}

/* ===== GRID ===== */
.g2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.g3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.g4{display:grid;grid-template-columns:repeat(auto-fit,minmax(0,1fr));gap:10px}

/* ===== FIELD GROUP ===== */
.fg{display:flex;flex-direction:column;gap:5px;margin-bottom:0}
.fg label{font-size:11px;color:var(--text2);font-family:var(--mono);letter-spacing:.04em}
input[type=number],
input[type=text],
select{
  width:100%;

  padding:10px 14px;

  background:#fff;

  border:1px solid var(--border);

  border-radius:10px;

  color:var(--text);

  font-family:var(--mono);

  transition:.2s;
}

input:focus,
select:focus{
  border-color:var(--accent);

  box-shadow:0 0 0 4px rgba(37,99,235,.08);
}
input:disabled{opacity:.35;cursor:not-allowed}
select option{background:var(--bg2)}

/* ===== INVEST BOX ===== */
.invest-box{
  background:var(--surface);border:1px solid var(--border2);
  border-radius:8px;padding:14px 16px;margin-top:8px;
}
.invest-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
.invest-total{
  display:flex;justify-content:space-between;align-items:center;
  padding-top:10px;border-top:1px solid var(--border);
}
.invest-total .il{font-size:12px;color:var(--text2);font-family:var(--mono)}
.invest-total .iv{font-size:15px;font-weight:500;color:var(--text);font-family:var(--mono)}
.di-note{font-size:11px;color:var(--text3);font-family:var(--mono);margin-top:6px;line-height:1.6}

/* ===== PROD ROWS ===== */
.prod-item{
  display:grid;grid-template-columns:72px 1fr 28px;
  gap:8px;align-items:center;margin-bottom:6px;
}
.prod-item span{font-size:12px;color:var(--text2);font-family:var(--mono)}
.btn-rm{
  width:26px;height:26px;border:1px solid var(--border);border-radius:6px;
  background:none;color:var(--text3);cursor:pointer;font-size:15px;
  display:flex;align-items:center;justify-content:center;transition:all .15s;
}
.btn-rm:hover{border-color:var(--red);color:var(--red);background:rgba(224,82,82,.08)}
.btn-add{
  border:1px dashed var(--border2);border-radius:7px;
  padding:5px 14px;background:none;color:var(--text2);
  font-size:12px;cursor:pointer;margin-top:4px;font-family:var(--body);
  transition:all .18s;
}
.btn-add:hover{border-color:var(--accent3);color:var(--accent)}

/* ===== DIVIDER ===== */
.divider{border:none;border-top:1px solid var(--border);margin:16px 0}

/* ===== BTN ===== */
.btn-primary{
  display:inline-flex;align-items:center;gap:8px;
  padding:10px 26px;background:var(--accent2);border:none;
  border-radius:8px;color:#081a0e;font-weight:600;font-size:14px;
  cursor:pointer;font-family:var(--body);transition:all .18s;
}
.btn-primary:hover{background:var(--accent);transform:translateY(-1px)}
.btn-sec{
  display:inline-flex;align-items:center;gap:6px;
  padding:9px 18px;background:var(--surface2);border:1px solid var(--border2);
  border-radius:8px;color:var(--text);font-size:13px;
  cursor:pointer;font-family:var(--body);transition:all .18s;
}
.btn-sec:hover{background:var(--surface);border-color:var(--border2)}
.btn-row{display:flex;gap:10px;align-items:center;margin-top:18px;flex-wrap:wrap}

/* ===== METRIC ===== */
.metric{
  background:#fff;

  border:1px solid var(--border);

  border-radius:14px;

  padding:18px;

  box-shadow:0 2px 8px rgba(15,23,42,.03);
}
.metric .ml{font-size:10px;color:var(--text3);font-family:var(--mono);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px}
.metric .mv{font-size:20px;font-weight:600;font-family:var(--mono)}
.mv.g{color:var(--accent)}
.mv.a{color:var(--amber)}
.mv.r{color:var(--red)}

/* ===== TABLE ===== */
.tbl-wrap{overflow-x:auto;border:1px solid var(--border);border-radius:8px;margin-top:12px}
table{width:100%;border-collapse:collapse;font-family:var(--mono);font-size:12px}
thead tr{background:var(--surface2)}
th{padding:9px 10px;text-align:right;font-weight:500;font-size:10px;
   letter-spacing:.06em;text-transform:uppercase;border-bottom:1px solid var(--border);
   white-space:nowrap;color:var(--text2)}
th:first-child{text-align:center;width:36px}
td{padding:7px 10px;border-bottom:1px solid var(--border);text-align:right;color:var(--text)}
td:first-child{text-align:center;color:var(--text3);font-weight:500;background:var(--surface)}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover td{background:rgba(61,220,132,.03)}
.tr-tot td{background:var(--surface2)!important;font-weight:500;color:var(--accent);border-top:1px solid var(--accent3)}
.tbl-note{font-size:11px;color:var(--text3);font-family:var(--mono);margin-top:8px;line-height:1.8}

/* ===== SQL BLOCK ===== */
.sql-pre{
  background:var(--bg);border:1px solid var(--border);border-radius:8px;
  padding:16px 20px;font-family:var(--mono);font-size:11px;
  color:var(--text2);line-height:1.7;overflow-x:auto;
  white-space:pre;margin-top:8px;max-height:320px;overflow-y:auto;
}
.sql-keyword{color:var(--blue)}
.sql-string{color:var(--amber)}
.sql-comment{color:var(--text3)}

/* ===== DB PAGE ===== */
.tbl-schema{width:100%;border-collapse:collapse;font-size:12px;font-family:var(--mono);margin-bottom:4px}
.tbl-schema th{background:var(--surface2);padding:7px 12px;text-align:left;
  font-size:10px;text-transform:uppercase;letter-spacing:.07em;color:var(--text2);
  border-bottom:1px solid var(--border)}
.tbl-schema td{padding:6px 12px;border-bottom:1px solid var(--border);color:var(--text)}
.tbl-schema tr:last-child td{border-bottom:none}
.tbl-schema tr:hover td{background:var(--surface)}
.col-pk{color:var(--amber)}
.col-fk{color:var(--accent)}
.col-type{color:var(--blue)}
.col-desc{color:var(--text3)}
.schema-name{
  font-family:var(--mono);font-size:12px;color:var(--accent);
  font-weight:500;letter-spacing:.06em;text-transform:uppercase;
  display:flex;align-items:center;gap:8px;
}
.schema-name .badge{
  font-size:10px;padding:2px 8px;border-radius:4px;font-weight:400;
  background:rgba(61,220,132,.1);color:var(--accent2);border:1px solid var(--accent3);
}

/* ===== TOAST ===== */
.toast{
  position:fixed;bottom:24px;right:24px;z-index:999;
  padding:11px 18px;border-radius:9px;font-size:13px;font-family:var(--body);
  background:var(--surface2);border:1px solid var(--accent3);color:var(--accent);
  display:none;gap:8px;align-items:center;
}
.toast.show{display:flex}

/* ===== TAG ===== */
.tag{display:inline-block;font-family:var(--mono);font-size:10px;padding:2px 8px;border-radius:4px;letter-spacing:.04em}
.tag-amber{background:rgba(245,166,35,.1);color:var(--amber);border:1px solid rgba(245,166,35,.25)}
.tag-blue{background:rgba(91,156,246,.1);color:var(--blue);border:1px solid rgba(91,156,246,.25)}

/* ===== RESPONSIVE ===== */
@media(max-width:700px){
  .g2,.g3,.g4{grid-template-columns:1fr}
  .page{padding:16px}
  .topbar{padding:0 16px}
  .hero{padding:24px 20px}
  .hero-title{font-size:22px}
}
</style>
</head>
<body>
<div class="card">
    <div class="card-title">
        <div class="dot"></div>
        Informasi Proyek
    </div>

    <div class="g2">
        <div class="fg">
            <label>Nama Proyek</label>
            <input type="text" id="nama_proyek"
                placeholder="Contoh: Pengembangan Lapangan Gunung Bakaran">
        </div>

        <div class="fg">
            <label>Nama Sumur</label>
            <input type="text" id="nama_sumur"
                placeholder="Contoh: GB-01">
        </div>
    </div>

    <div class="fg" style="margin-top:12px">
        <label>Lokasi Lapangan</label>
        <input type="text" id="lokasi"
            placeholder="Contoh: Kalimantan Timur">
    </div>
</div>

  <!-- IDENTITAS & PARAMETER -->
  <div class="card">
    <div class="card-title"><div class="dot"></div>Identitas &amp; Parameter</div>
    <div class="g3" style="margin-bottom:12px">
      <div class="fg"><label>Total cadangan (Mbbl)</label><input type="number" id="cadangan" value="4320"></div>
      <div class="fg"><label>Harga minyak ($/bbl)</label><input type="number"  id="harga" value="32" step="0.5"></div>
      <div class="fg"><label>Tahun perhitungan</label><input type="number" id="tahun" value="10" min="1" max="30" oninput="updDi()"></div>
    </div>
    <div class="g2">
      <div class="fg"><label>Tax rate (%)</label><input type="number" id="taxrate" value="51" step="0.1"></div>
      <div class="fg"><label>Metode depresiasi</label><input type="text" value="Straight-line" disabled></div>
    </div>
  </div>

  <!-- INVESTASI -->
  <div class="card">
    <div class="card-title"><div class="dot"></div>Investasi (Capex) <span class="tag tag-amber" style="margin-left:6px">Dasar Di</span></div>
    <div class="invest-box">
      <div class="invest-row">
        <div class="fg"><label>Capital ($M)</label><input type="number" id="capital" value="13000" oninput="updDi()"></div>
        <div class="fg"><label>Non-capital ($M)</label><input type="number" id="noncapital" value="8000" oninput="updDi()"></div>
      </div>
      <div class="invest-total">
        <span class="il">Total investasi</span>
        <span class="iv" id="total-invest">$21,000.00 M</span>
      </div>
      <div class="di-note" id="di-note">Di (depresiasi/thn) = $21,000.00 M ÷ 10 = $2,100.00 M</div>
    </div>
  </div>

  <!-- PRODUKSI -->
  <div class="card">
    <div class="card-title"><div class="dot"></div>Data Produksi</div>
    <div style="font-size:12px;color:var(--text2);font-family:var(--mono);margin-bottom:12px">
      Input manual tahun-tahun yang diketahui. Sisanya dihitung dengan decline rate.
    </div>
    <div id="prod-list"></div>
    <button class="btn-add" onclick="addProd()">+ Tambah tahun</button>
    <div class="divider"></div>
    <div class="g2">
      <div class="fg"><label>Mulai decline tahun ke-</label><input type="number" id="dec_start" value="5" min="1"></div>
      <div class="fg"><label>Laju decline (%/tahun)</label><input type="number" id="dec_rate" value="3" step="0.1"></div>
    </div>
  </div>

  <!-- OPEX -->
  <div class="card">
    <div class="card-title"><div class="dot"></div>Parameter Opex</div>
    <div class="g3">
      <div class="fg"><label>Opex base ($M/thn)</label><input type="number" id="opex_base" value="180"></div>
      <div class="fg"><label>Berlaku s.d. tahun ke-</label><input type="number" id="opex_until" value="3" min="1"></div>
      <div class="fg"><label>Eskalasi (%/thn)</label><input type="number" id="opex_esc" value="2.5" step="0.1"></div>
    </div>
    <div style="font-size:11px;color:var(--text3);font-family:var(--mono);margin-top:8px">
      Mulai tahun ke-(base+1): Opex = base × (1 + eskalasi%)^n
    </div>
  </div>

  <div class="btn-row">
<button class="btn-primary" onclick="calculate()">
   Simpan & Hitung NCF
</button>
  </div>

</main>

<!-- ===================================================== -->
<!-- PAGE: HASIL                                            -->
<!-- ===================================================== -->
<main id="pg-result" class="page">

  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
      <div style="font-family:var(--display);font-size:22px;font-weight:700;letter-spacing:-.02em" id="res-title">Hasil Kalkulasi NCF</div>
      <div style="color:var(--text2);font-size:13px;margin-top:4px" id="res-sub">Belum ada data — hitung dari tab Kalkulator.</div>
    </div>
    <button class="btn-sec" onclick="nav('calc',document.querySelector('.nav-btn'))">← Kembali</button>
  </div>

  <div id="res-content">
    <div style="color:var(--text3);font-family:var(--mono);font-size:13px;padding:40px 0;text-align:center">
      Belum ada hasil. Silakan klik "Hitung NCF" di tab Kalkulator.
    </div>
  </div>

</main>
<!-- TOAST -->
<div class="toast" id="toast">✓ <span id="toast-msg"></span></div>

<script>
// ===================== STATE =====================
let prods = [175, 201, 217, 198];
let lastResult = null;

// ===================== NAV =====================
function nav(page, el) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('pg-' + page).classList.add('active');
  // find matching nav button
  document.querySelectorAll('.nav-btn').forEach(b => {
    if ((page === 'calc' && b.textContent.includes('Kalkulator')) ||
        (page === 'result' && b.textContent.includes('Hasil')) ||
        (page === 'db' && b.textContent.includes('DB'))) {
      b.classList.add('active');
    }
  });
  if (page === 'db') renderSchema();
}

// ===================== FORMAT =====================
function fmt(n, d = 2) {
  return isNaN(n) ? '—' : Number(n).toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d });
}
function fM(n) { return '$' + fmt(n) + ' M'; }
function getVal(id) { return parseFloat(document.getElementById(id).value) || 0; }

// ===================== DI SUMMARY =====================
function updDi() {
  const cap = getVal('capital'), nc = getVal('noncapital');
  const yr = parseInt(document.getElementById('tahun').value) || 10;
  const tot = cap + nc;
  const di = tot / yr;
  document.getElementById('total-invest').textContent = fM(tot);
  document.getElementById('di-note').textContent =
    `Di (depresiasi/thn) = ${fM(tot)} ÷ ${yr} = ${fM(di)}`;
}

// ===================== PROD ROWS =====================
function renderProds() {
  document.getElementById('prod-list').innerHTML = prods.map((v, i) => `
    <div class="prod-item">
      <span>Tahun ${i + 1}</span>
      <input type="number" value="${v}" onchange="prods[${i}]=parseFloat(this.value)||0">
      ${i > 0 ? `<button class="btn-rm" onclick="prods.splice(${i},1);renderProds()">×</button>` : '<div></div>'}
    </div>`).join('');
}
function addProd() { prods.push(0); renderProds(); }

// ===================== CALCULATE =====================
function calculate() {
  const cadangan = getVal('cadangan');
  const harga = getVal('harga');
  const nYr = parseInt(document.getElementById('tahun').value) || 10;
  const taxRate = getVal('taxrate') / 100;
  const cap = getVal('capital');
  const nc = getVal('noncapital');
  const totalInvest = cap + nc;
  const di = totalInvest / nYr;
  const decStart = parseInt(document.getElementById('dec_start').value) || 5;
  const decRate = getVal('dec_rate') / 100;
  const opexBase = getVal('opex_base');
  const opexUntil = parseInt(document.getElementById('opex_until').value) || 3;
  const opexEsc = getVal('opex_esc') / 100;
  const namaProyek =
document.getElementById('nama_proyek').value || 'Gunung Bakaran';

const namaSumur =
document.getElementById('nama_sumur').value || '-';

const lokasi =
document.getElementById('lokasi').value || '-';

  let rows = [], cumProd = 0, cumNCF = 0;
  for (let y = 1; y <= nYr; y++) {
    let prod;
    if (y <= prods.length) {
      prod = prods[y - 1];
    } else if (y >= decStart) {
      prod = rows[rows.length - 1].prod * (1 - decRate);
    } else {
      prod = rows[rows.length - 1].prod;
    }
    prod = Math.max(0, prod);
    cumProd += prod;
    if(cumProd > cadangan){
    prod -= (cumProd - cadangan);
    cumProd = cadangan;
}

    const opex =
y <= opexUntil
? opexBase
: opexBase * Math.pow(1 + opexEsc, y - opexUntil);
    const gr = prod * harga;
    const ti = gr - opex - di;
    const tax = ti > 0 ? ti * taxRate : 0;
    const ncf = gr - opex - tax;
    cumNCF += ncf;
    rows.push({ y, prod, cumProd, gr, opex, di, ti, tax, ncf, cumNCF });
  }

  lastResult = {
  namaProyek,
  namaSumur,
  lokasi,
  cadangan,
  harga,
  nYr,
  taxRate,
  cap,
  nc,
  totalInvest,
  di,
  rows
};
  renderResult();
  nav('result', null);
  toast('Kalkulasi selesai — ' + nYr + ' tahun');
}



// ===================== RENDER RESULT =====================
function renderResult() {
  if (!lastResult) return;
  const { cadangan, nYr, taxRate, cap, nc, totalInvest, di, rows } = lastResult;
  const last = rows[rows.length - 1];
  const tRev = rows.reduce((s, r) => s + r.gr, 0);
  const tOpex = rows.reduce((s, r) => s + r.opex, 0);
  const tTax = rows.reduce((s, r) => s + r.tax, 0);

  document.getElementById('res-title').textContent = 'Hasil Kalkulasi NCF — ' + document.getElementById('cadangan').value + ' Mbbl cadangan';
  document.getElementById('res-sub').textContent = `${nYr} tahun | Tax ${(taxRate * 100).toFixed(0)}% | Di = ${fM(di)}/thn`;

  document.getElementById('res-content').innerHTML = `
    <!-- INVESTASI METRICS -->
    <div class="g4" style="margin-bottom:12px">
      <div class="metric"><div class="ml">Capital</div><div class="mv">${fM(cap)}</div></div>
      <div class="metric"><div class="ml">Non-capital</div><div class="mv">${fM(nc)}</div></div>
      <div class="metric"><div class="ml">Total investasi</div><div class="mv a">${fM(totalInvest)}</div></div>
      <div class="metric"><div class="ml">Di / tahun</div><div class="mv">${fM(di)}</div></div>
    </div>
    <!-- NCF METRICS -->
    <div class="g4" style="margin-bottom:20px">
      <div class="metric"><div class="ml">Total produksi</div><div class="mv">${fmt(last.cumProd)} Mbbl</div></div>
      <div class="metric"><div class="ml">Sisa cadangan</div><div class="mv a">${fmt(cadangan - last.cumProd)} Mbbl</div></div>
      <div class="metric"><div class="ml">Total gross revenue</div><div class="mv a">${fM(tRev)}</div></div>
      <div class="metric"><div class="ml">Total opex</div><div class="mv">${fM(tOpex)}</div></div>
      <div class="metric"><div class="ml">Total tax</div><div class="mv r">${fM(tTax)}</div></div>
      <div class="metric"><div class="ml">Total NCF</div><div class="mv g">${fM(last.cumNCF)}</div></div>
    </div>
    <!-- TABLE -->
    <div class="card" style="margin-bottom:14px">
      <div class="card-title"><div class="dot"></div>Tabel NCF Tahunan <span class="tag tag-blue" style="margin-left:6px">After Tax</span></div>
      <div class="tbl-wrap">
        <table>
          <thead><tr>
            <th>Thn</th>
            <th>Prod (Mbbl)</th>
            <th>Cum Prod</th>
            <th>Gross Rev ($M)</th>
            <th>Opex ($M)</th>
            <th>Di ($M)</th>
            <th>Taxable Inc ($M)</th>
            <th>Tax (${(taxRate*100).toFixed(0)}%) ($M)</th>
            <th>NCF ($M)</th>
            <th>Cum NCF ($M)</th>
          </tr></thead>
          <tbody>
            ${rows.map(r => `<tr>
              <td>${r.y}</td>
              <td>${fmt(r.prod)}</td>
              <td>${fmt(r.cumProd)}</td>
              <td>${fmt(r.gr)}</td>
              <td>${fmt(r.opex)}</td>
              <td>${fmt(r.di)}</td>
              <td style="color:${r.ti < 0 ? 'var(--red)' : 'inherit'}">${fmt(r.ti)}</td>
              <td style="color:var(--red)">${fmt(r.tax)}</td>
              <td style="color:${r.ncf < 0 ? 'var(--red)' : 'var(--accent)'}">${fmt(r.ncf)}</td>
              <td style="color:${r.cumNCF < 0 ? 'var(--red)' : 'var(--accent)'}">${fmt(r.cumNCF)}</td>
            </tr>`).join('')}
            <tr class="tr-tot">
              <td>Total</td>
              <td>${fmt(last.cumProd)}</td><td>—</td>
              <td>${fmt(tRev)}</td>
              <td>${fmt(tOpex)}</td>
              <td>${fmt(di * nYr)}</td>
              <td>—</td>
              <td>${fmt(tTax)}</td>
              <td>—</td>
              <td>${fmt(last.cumNCF)}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="tbl-note">
        Di = (Capital + Non-Capital) ÷ Tahun hitung &nbsp;|&nbsp;
        Taxable income = Gross Rev − Opex − Di &nbsp;|&nbsp;
        Tax = Taxable income × Tax rate &nbsp;|&nbsp;
        NCF = Gross Rev − Opex − Tax
      </div>
    </div>
    <!-- SQL RESULT -->
    <div class="card">
      <div class="card-title"><div class="dot"></div>SQL INSERT — ncf_results</div>
      <div class="sql-pre">${buildSQL(rows)}</div>
      
    </div>`;
}

document.getElementById('nama_proyek')
.addEventListener('input', function(){
    document.getElementById('hero-nama').textContent =
    this.value || 'Gunung Bakaran';
});

function buildSQL(rows) {
  return rows.map(r => `
INSERT INTO ncf_results
(year, production, cumulative_production, gross_revenue,
 opex, depreciation, taxable_income, tax, ncf, cumulative_ncf)
VALUES
(
${r.y},
${r.prod.toFixed(2)},
${r.cumProd.toFixed(2)},
${r.gr.toFixed(2)},
${r.opex.toFixed(2)},
${r.di.toFixed(2)},
${r.ti.toFixed(2)},
${r.tax.toFixed(2)},
${r.ncf.toFixed(2)},
${r.cumNCF.toFixed(2)}
);
`).join('\n');
}

const discount =
(getVal('discount') / 100);
let npv = 0;

rows.forEach(r=>{
   npv += r.ncf / Math.pow(1+discount,r.y);
});

// ===================== TOAST =====================
function toast(msg) {
  const el = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 2800);
}

// ===================== INIT =====================
renderProds();
updDi();
</script>
</body>
</html>