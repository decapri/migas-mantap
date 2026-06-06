<?php
// ================================================================
// form-proyek.php  — tampilan baru (2 kolom × 3 baris)
// Back-end / logika tidak diubah sama sekali
// ================================================================

// $isEdit = true jika halaman edit (variabel $project tersedia dan punya data dari DB)
$isEdit = !empty($project['id']);

// $v() — kembalikan value dari DB saat edit, string kosong saat tambah baru
$v = function(string $key) use (&$project, $isEdit) {
    if (!$isEdit) return '';
    return htmlspecialchars((string)($project[$key] ?? ''), ENT_QUOTES);
};

// $sel() — tandai option selected saat edit
$sel = function(string $opt) use (&$project, $isEdit) {
    if (!$isEdit) return '';
    return ($project['status_proyek'] ?? '') === $opt ? 'selected' : '';
};

// prods: kosong saat tambah baru, terisi dari DB saat edit
$produksiJS = isset($produksiManual) && count($produksiManual) > 0
    ? json_encode(array_values(array_map('floatval', $produksiManual)))
    : '[]';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Form Proyek — Migas Mantap</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ============================================================
   RESET & ROOT
   ============================================================ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }

:root {
  --bg:        #f0f2f5;
  --surface:   #ffffff;
  --border:    #e2e8f0;
  --border2:   #cbd5e1;

  --accent:    #F5A623;
  --accent2:   #F5A623;
  --accent3:   #D88912;

  --amber:     #d97706;
  --red:       #dc2626;
  --blue:      #2563eb;
  --green:     #16a34a;

  --text:      #0f172a;
  --text2:     #475569;
  --text3:     #94a3b8;

  --font: 'Plus Jakarta Sans', system-ui, sans-serif;

  --radius-card: 16px;
  --radius-input: 10px;
  --shadow-card: 0 2px 16px rgba(15,23,42,.07);
}

body {
  background: var(--bg);
  color: var(--text);
  font-family: var(--font);
  font-size: 14px;
  line-height: 1.6;
  min-height: 100vh;
}

/* ============================================================
   PAGE WRAPPER
   ============================================================ */
.form-page {
  max-width: 100%;
  margin: 0;
  padding: 32px 40px 64px;
}

/* ============================================================
   PAGE HEADER
   ============================================================ */
.page-header {
  margin-bottom: 28px;
}
.page-header h1 {
  font-size: 24px;
  font-weight: 800;
  letter-spacing: -.03em;
  color: var(--text);
}
.page-header h1 span { color: var(--accent); }
.page-header p {
  color: var(--text2);
  font-size: 13px;
  margin-top: 4px;
}

/* ============================================================
   2-COL × 3-ROW GRID
   ============================================================ */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 28px;
}

@media (max-width: 820px) {
  .form-grid { grid-template-columns: 1fr; }
}

/* ============================================================
   CARD
   ============================================================ */
.fcard {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-card);
  padding: 28px 28px 24px;
  box-shadow: var(--shadow-card);
  display: flex;
  flex-direction: column;
  gap: 0;
}

/* Card header */
.fcard-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--border);
}
.fcard-icon {
  width: 36px;
  height: 36px;
  border-radius: 9px;
  background: rgba(245,166,35,.12);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.fcard-icon svg { width: 18px; height: 18px; }
.fcard-title {
  font-size: 14px;
  font-weight: 700;
  color: var(--text);
  letter-spacing: -.01em;
}
.fcard-sub {
  font-size: 11px;
  color: var(--text3);
  font-weight: 400;
  margin-top: 1px;
}

/* ============================================================
   FIELD GROUP
   ============================================================ */
.fg {
  display: flex;
  flex-direction: column;
  gap: 5px;
  margin-bottom: 14px;
}
.fg:last-child { margin-bottom: 0; }

.fg label {
  font-size: 11px;
  font-weight: 600;
  color: var(--text2);
  letter-spacing: .04em;
  text-transform: uppercase;
}

.fg-row {
  display: grid;
  gap: 14px;
}
.fg-row.col2 { grid-template-columns: 1fr 1fr; }
.fg-row.col3 { grid-template-columns: 1fr 1fr 1fr; }

input[type="text"],
input[type="number"],
select,
textarea {
  width: 100%;
  padding: 11px 14px;
  background: #f8fafc;
  border: 1.5px solid var(--border);
  border-radius: var(--radius-input);
  color: var(--text);
  font-family: var(--font);
  font-size: 13.5px;
  transition: border-color .2s, box-shadow .2s, background .2s;
  appearance: none;
  -webkit-appearance: none;
  outline: none;
}
input[type="text"]:focus,
input[type="number"]:focus,
select:focus,
textarea:focus {
  border-color: var(--accent);
  background: #fff;
  box-shadow: 0 0 0 4px rgba(245,166,35,.12);
}
input:disabled {
  opacity: .45;
  cursor: not-allowed;
  background: #f1f5f9;
}
select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%2394a3b8' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  padding-right: 36px;
  cursor: pointer;
}
textarea {
  resize: vertical;
  min-height: 80px;
}
input::placeholder,
textarea::placeholder { color: var(--text3); }

/* ============================================================
   BADGE / TAG
   ============================================================ */
.badge {
  display: inline-block;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 4px;
  letter-spacing: .04em;
  text-transform: uppercase;
}
.badge-amber {
  background: rgba(245,166,35,.12);
  color: var(--amber);
  border: 1px solid rgba(245,166,35,.3);
}
.badge-blue {
  background: rgba(37,99,235,.09);
  color: var(--blue);
  border: 1px solid rgba(37,99,235,.2);
}

/* ============================================================
   INVEST / DEPRESIASI BOX (inset)
   ============================================================ */
.inset-box {
  background: #f8fafc;
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 16px;
  margin-top: 2px;
}
.inset-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 12px;
  margin-top: 4px;
  border-top: 1px solid var(--border);
}
.inset-total .itl { font-size: 11px; color: var(--text2); font-weight: 600; }
.inset-total .itv { font-size: 15px; font-weight: 700; color: var(--text); }
.hint-text {
  font-size: 11px;
  color: var(--text3);
  margin-top: 4px;
  line-height: 1.6;
}

/* ============================================================
   PROD ROWS
   ============================================================ */
.prod-item {
  display: grid;
  grid-template-columns: 80px 1fr 30px;
  gap: 8px;
  align-items: center;
  margin-bottom: 6px;
}
.prod-item span {
  font-size: 12px;
  color: var(--text2);
  font-weight: 600;
}
.btn-rm {
  width: 28px;
  height: 28px;
  border: 1.5px solid var(--border);
  border-radius: 7px;
  background: none;
  color: var(--text3);
  cursor: pointer;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .15s;
  line-height: 1;
}
.btn-rm:hover { border-color: var(--red); color: var(--red); background: rgba(220,38,38,.07); }

.btn-add {
  border: 1.5px dashed var(--border2);
  border-radius: 8px;
  padding: 7px 16px;
  background: none;
  color: var(--text2);
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 6px;
  font-family: var(--font);
  transition: all .18s;
  width: 100%;
}
.btn-add:hover { border-color: var(--accent3); color: var(--accent); background: rgba(245,166,35,.05); }

/* ============================================================
   DEPRESIASI NOTE
   ============================================================ */
.di-note {
  font-size: 11px;
  color: var(--text3);
  margin-top: 8px;
  line-height: 1.7;
  font-style: italic;
}

/* ============================================================
   DIVIDER
   ============================================================ */
.divider { border: none; border-top: 1px solid var(--border); margin: 16px 0; }

/* ============================================================
   SUBMIT BUTTON
   ============================================================ */
.submit-row {
  display: flex;
  justify-content: center;
  margin-top: 4px;
}
.btn-submit {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 15px 56px;
  background: var(--accent2);
  color: #fff;
  font-family: var(--font);
  font-size: 14px;
  font-weight: 700;
  letter-spacing: .01em;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  box-shadow: 0 8px 24px rgba(245,166,35,.30);
  transition: background .18s, transform .18s, box-shadow .18s;
  min-width: 260px;
}
.btn-submit:hover {
  background: var(--accent3);
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(245,166,35,.38);
}
.btn-submit svg { width: 17px; height: 17px; flex-shrink: 0; }

/* ============================================================
   OPEX NOTE
   ============================================================ */
.opex-note {
  font-size: 11px;
  color: var(--text3);
  margin-top: 8px;
  line-height: 1.7;
}
</style>
</head>
<body>

<div class="form-page">

  <!-- PAGE HEADER -->
  <div class="page-header">
    <h1>Form <span>Proyek Baru</span></h1>
    <p>Isi semua bagian untuk menghitung Net Cash Flow proyek migas.</p>
  </div>

  <!-- ========================================================
       2-COL × 3-ROW GRID
       ======================================================== -->
  <div class="form-grid">

    <!-- ── ROW 1, COL 1 : Informasi Awal Proyek ── -->
    <div class="fcard">
      <div class="fcard-header">
        <div class="fcard-icon">
          <svg fill="none" stroke="#F5A623" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <div>
          <div class="fcard-title">Informasi Awal Proyek</div>
          <div class="fcard-sub">Identitas dasar proyek</div>
        </div>
      </div>

      <div class="fg-row col2">
        <div class="fg">
          <label>Nama Proyek</label>
          <input type="text" id="nama_proyek" name="nama_proyek"
            value="<?= $v('nama') ?>"
            placeholder="Gunung Bakaran">
        </div>
        <div class="fg">
          <label>Nama Sumur</label>
          <input type="text" id="nama_sumur" name="nama_sumur"
            value="<?= $v('nama_sumur') ?>"
            placeholder="GB-01">
        </div>
      </div>

      <div class="fg-row col2">
        <div class="fg">
          <label>Lokasi Lapangan</label>
          <input type="text" id="lokasi" name="lokasi_lapangan"
            value="<?= $v('lokasi_lapangan') ?>"
            placeholder="Kalimantan Timur">
        </div>
        <div class="fg">
          <label>Status Proyek</label>
          <select id="status_proyek" name="status_proyek">
            <option value="Direncanakan" <?= $sel('Direncanakan') ?>>Direncanakan</option>
            <option value="Berjalan"     <?= $sel('Berjalan') ?>>Berjalan</option>
            <option value="Selesai"      <?= $sel('Selesai') ?>>Selesai</option>
          </select>
        </div>
      </div>

    </div>

    <!-- ── ROW 1, COL 2 : Depresiasi ── -->
    <div class="fcard">
      <div class="fcard-header">
        <div class="fcard-icon">
          <svg fill="none" stroke="#F5A623" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <div>
          <div class="fcard-title">Depresiasi</div>
          <div class="fcard-sub">Pengaturan metode &amp; nilai depresiasi aset</div>
        </div>
      </div>

      <div class="fg-row col2">
        <div class="fg">
          <label>Besar Depresiasi / Tahun ($M)</label>
          <input type="number" id="depresiasi-val" name="depresiasi_per_tahun"
            value="<?= $v('depresiasi_per_tahun') ?>"
            placeholder="Otomatis dari investasi ÷ tahun"
            oninput="updDi()">
        </div>
        <div class="fg">
          <label>Metode Depresiasi</label>
          <input type="text" value="Straight-line" disabled>
        </div>
      </div>

      <div class="di-note" id="di-note">—</div>
    </div>

    <!-- ── ROW 2, COL 1 : Data Produksi dan OPEX ── -->
    <div class="fcard">
      <div class="fcard-header">
        <div class="fcard-icon">
          <svg fill="none" stroke="#F5A623" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div>
          <div class="fcard-title">Data Produksi</div>
          <div class="fcard-sub">Bagian ini diperlukan agar grafik produksi dan perhitungan pendapatan dapat berjalan.</div>
        </div>
      </div>

      <div id="prod-list"></div>
      <button type="button" class="btn-add" onclick="addProd()">+ Tambah Tahun Produksi</button>

      <hr class="divider">

      <div class="fg-row col2">
        <div class="fg">
          <label>Mulai Decline Tahun ke-</label>
          <input type="number" id="dec_start" name="mulai_decline"
            value="<?= $v('mulai_tahun_ke') ?>" min="1">
        </div>
        <div class="fg">
          <label>Laju Decline (%/tahun)</label>
          <input type="number" id="dec_rate" name="decline_rate"
            value="<?= $v('laju_persen') ?>" step="0.1">
        </div>
      </div>
    </div>

    <!-- ── ROW 2, COL 2 : Data Keuangan ── -->
    <div class="fcard">
      <div class="fcard-header">
        <div class="fcard-icon">
          <svg fill="none" stroke="#F5A623" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </div>
        <div>
          <div class="fcard-title">Data Keuangan</div>
          <div class="fcard-sub">Investasi &amp; depresiasi <span class="badge badge-amber" style="margin-left:4px">Dasar Di</span></div>
        </div>
      </div>

      <div class="fg-row col2">
        <div class="fg">
          <label>Capital ($M)</label>
          <input type="number" id="capital" name="capital"
            value="<?= $v('capital') ?>" oninput="updDi()">
        </div>
        <div class="fg">
          <label>Non-Capital ($M)</label>
          <input type="number" id="noncapital" name="non_capital"
            value="<?= $v('non_capital') ?>" oninput="updDi()">
        </div>
      </div>

      <div class="fg">
        <label>Total Investasi ($M)</label>
        <input type="number" id="total-invest-input" name="total_investasi"
          value="<?= $v('total_investasi') ?>"
          style="font-weight:600"
          oninput="onManualTotal(this)"
          placeholder="Otomatis dari Capital + Non-Capital">
        <span class="hint-text" id="total-invest-hint">— otomatis dari Capital + Non-Capital</span>
      </div>

      <div class="hint-text" id="di-note2">—</div>
    </div>

    <!-- ── ROW 3, COL 1 : Identitas & Parameter (cadangan) ── -->
    <div class="fcard">
      <div class="fcard-header">
        <div class="fcard-icon">
          <svg fill="none" stroke="#F5A623" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <div>
          <div class="fcard-title">Identitas &amp; Parameter</div>
          <div class="fcard-sub">Parameter teknis tambahan</div>
        </div>
      </div>

      <div class="fg-row col2">
        <div class="fg">
          <label>Total Cadangan (Mbbl)</label>
          <input type="number" id="cadangan" name="cadangan_mbbl"
            value="<?= $v('cadangan_mbbl') ?>">
        </div>
        <div class="fg">
          <label>Harga Minyak ($/bbl)</label>
          <input type="number" id="harga" name="harga_minyak"
            value="<?= $v('harga_minyak') ?>" step="0.5">
        </div>
      </div>

      <div class="fg-row col2">
        <div class="fg">
          <label>Tax Rate (%)</label>
          <input type="number" id="taxrate" name="tax_rate"
            value="<?= $v('tax_rate') ?>" step="0.1">
        </div>
        <div class="fg">
          <label>Tahun Perhitungan</label>
          <input type="number" id="tahun" name="tahun_perhitungan"
            value="<?= $v('tahun_hitung') ?>" min="1" max="30"
            oninput="updDi()">
        </div>
      </div>
    </div>

    <!-- ── ROW 3, COL 2 : Parameter Opex ── -->
    <div class="fcard">
      <div class="fcard-header">
        <div class="fcard-icon">
          <svg fill="none" stroke="#F5A623" stroke-width="1.8" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
        </div>
        <div>
          <div class="fcard-title">Parameter Opex</div>
          <div class="fcard-sub">Biaya operasional &amp; eskalasi tahunan</div>
        </div>
      </div>

      <div class="fg">
        <label>Opex Base ($M/tahun)</label>
        <input type="number" id="opex_base" name="opex_base"
          value="<?= $v('base_usd_m') ?>"
          placeholder="Cth: 180">
      </div>

      <div class="fg">
        <label>Berlaku s.d. Tahun ke-</label>
        <input type="number" id="opex_until" name="opex_until"
          value="<?= $v('base_hingga_thn') ?>" min="1"
          placeholder="Cth: 3">
      </div>

      <div class="fg">
        <label>Eskalasi (%/tahun)</label>
        <input type="number" id="opex_esc" name="opex_eskalasi"
          value="<?= $v('eskalasi_persen') ?>" step="0.1"
          placeholder="Cth: 5">
      </div>

      <div class="opex-note">
        Mulai tahun ke-(base+1): Opex = base × (1 + eskalasi%)^n
      </div>

      <div style="flex:1; min-height:16px"></div>

      <div class="submit-row" style="margin-top:20px">
        <button type="button" class="btn-submit" onclick="calculate()">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
          Simpan &amp; Hitung NCF
        </button>
      </div>
    </div>

  </div><!-- /form-grid -->
</div><!-- /form-page -->

<!-- ============================================================
     JAVASCRIPT — sama persis dengan versi lama
     ============================================================ -->
<script>
let prods = <?= $produksiJS ?>;
let lastResult = null;
let manualTotal = false;

// ===================== FORMAT =====================
function fmt(n, d = 2) {
  return isNaN(n) ? '—' : Number(n).toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d });
}
function fM(n) { return '$' + fmt(n) + ' M'; }
function getVal(id) { return parseFloat(document.getElementById(id)?.value) || 0; }

// ===================== DI SUMMARY =====================
function updDi() {
  const cap = getVal('capital'), nc = getVal('noncapital');
  const yr = parseInt(document.getElementById('tahun')?.value) || 10;
  const autoTot = cap + nc;

  if (!manualTotal && (cap > 0 || nc > 0)) {
    document.getElementById('total-invest-input').value = autoTot || '';
    document.getElementById('total-invest-hint').textContent = '— otomatis dari Capital + Non-Capital';
  }

  const tot = manualTotal
    ? (parseFloat(document.getElementById('total-invest-input').value) || 0)
    : autoTot;

  const di = yr > 0 ? tot / yr : 0;

  // Update both depresiasi displays
  if (document.getElementById('depresiasi-val'))
    document.getElementById('depresiasi-val').value = di > 0 ? di.toFixed(2) : '';
  if (document.getElementById('depresiasi-disp'))
    document.getElementById('depresiasi-disp').value = di > 0 ? di.toFixed(2) : '';

  const noteText = tot > 0
    ? `Di = ${fM(tot)} ÷ ${yr} thn = ${fM(di)}`
    : '—';
  if (document.getElementById('di-note'))
    document.getElementById('di-note').textContent = noteText;
  if (document.getElementById('di-note2'))
    document.getElementById('di-note2').textContent = noteText;
}

function onManualTotal(el) {
  const val = parseFloat(el.value);
  const cap = getVal('capital'), nc = getVal('noncapital');
  const autoTot = cap + nc;

  if (el.value === '' || (!isNaN(val) && Math.abs(val - autoTot) < 0.001 && cap + nc > 0)) {
    manualTotal = false;
    document.getElementById('total-invest-hint').textContent = '— otomatis dari Capital + Non-Capital';
  } else {
    manualTotal = true;
    document.getElementById('total-invest-hint').textContent = '— diisi manual';
  }
  updDi();
}

// ===================== PROD ROWS =====================
function renderProds() {
  document.getElementById('prod-list').innerHTML = prods.map((v, i) => `
    <div class="prod-item">
      <span>Tahun ${i + 1}</span>
      <input type="number" name="produksi${i + 1}" value="${v}"
        onchange="prods[${i}]=parseFloat(this.value)||0">
      ${i > 0
        ? `<button type="button" class="btn-rm" onclick="prods.splice(${i},1);renderProds()">×</button>`
        : '<div></div>'}
    </div>`).join('');
}

function addProd() { prods.push(0); renderProds(); }

// ===================== CALCULATE =====================
function calculate() {
  const cadangan   = getVal('cadangan');
  const harga      = getVal('harga');
  const nYr        = parseInt(document.getElementById('tahun').value) || 10;
  const taxRate    = getVal('taxrate') / 100;
  const cap        = getVal('capital');
  const nc         = getVal('noncapital');
  const totalInvest = parseFloat(document.getElementById('total-invest-input').value) || (cap + nc);
  const di         = totalInvest / nYr;
  const decStart   = parseInt(document.getElementById('dec_start').value) || 5;
  const decRate    = getVal('dec_rate') / 100;
  const opexBase   = getVal('opex_base');
  const opexUntil  = parseInt(document.getElementById('opex_until').value) || 3;
  const opexEsc    = getVal('opex_esc') / 100;
  const namaProyek = document.getElementById('nama_proyek').value || 'Gunung Bakaran';
  const namaSumur  = document.getElementById('nama_sumur').value  || '-';
  const lokasi     = document.getElementById('lokasi').value      || '-';

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
    if (cumProd > cadangan) {
      prod -= (cumProd - cadangan);
      cumProd = cadangan;
    }

    const opex = y <= opexUntil
      ? opexBase
      : opexBase * Math.pow(1 + opexEsc, y - opexUntil);

    const gr  = prod * harga;
    const ti  = gr - opex - di;
    const tax = ti > 0 ? ti * taxRate : 0;
    const ncf = gr - opex - tax;
    cumNCF   += ncf;

    rows.push({ y, prod, cumProd, gr, opex, di, ti, tax, ncf, cumNCF });
  }

  lastResult = { namaProyek, namaSumur, lokasi, cadangan, harga, nYr, taxRate, cap, nc, totalInvest, di, rows };

  // Jika ada fungsi renderResult & nav dari halaman induk, panggil
  if (typeof renderResult === 'function') renderResult();
  if (typeof nav === 'function') { nav('result', null); }
  if (typeof toast === 'function') toast('Kalkulasi selesai — ' + nYr + ' tahun');
}

// ===================== INIT =====================
renderProds();
updDi();
</script>
</body>
</html>
