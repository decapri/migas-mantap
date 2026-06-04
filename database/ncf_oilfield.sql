-- ============================================================
--  NCF OIL FIELD MANAGEMENT — DATABASE SCHEMA
--  Engine  : MySQL 8+ / MariaDB 10.5+
--  Charset : utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS ncf_oilfield
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ncf_oilfield;

-- ------------------------------------------------------------
-- 1. LAPANGAN — master data per lapangan minyak
-- ------------------------------------------------------------
CREATE TABLE fields (
    id              INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nama            VARCHAR(100)    NOT NULL,
    cadangan_mbbl   DECIMAL(14,4)   NOT NULL  COMMENT 'Total cadangan minyak (Mbbl)',
    harga_minyak    DECIMAL(10,2)   NOT NULL  COMMENT 'Harga minyak asumsi ($/bbl)',
    tahun_hitung    SMALLINT        NOT NULL  DEFAULT 10 COMMENT 'Jumlah tahun analisis NCF',
    tax_rate        DECIMAL(5,2)    NOT NULL  DEFAULT 51.00 COMMENT 'Tax rate (%)',
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT = 'Master data lapangan minyak';

-- ------------------------------------------------------------
-- 2. INVESTASI — capital dan non-capital per lapangan
--    Di = (capital + non_capital) / tahun_hitung
-- ------------------------------------------------------------
CREATE TABLE investasi (
    id              INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    field_id        INT UNSIGNED    NOT NULL,
    capital         DECIMAL(14,4)   NOT NULL  COMMENT 'Investasi capital ($M)',
    non_capital     DECIMAL(14,4)   NOT NULL  COMMENT 'Investasi non-capital ($M)',
    total_investasi DECIMAL(14,4)   GENERATED ALWAYS AS (capital + non_capital) STORED
                                              COMMENT 'Total investasi = capital + non_capital ($M)',
    UNIQUE KEY uq_field (field_id),
    FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE CASCADE
) COMMENT = 'Investasi capital & non-capital. Di dihitung dari total_investasi / tahun_hitung';

-- ------------------------------------------------------------
-- 3. PRODUKSI MANUAL — tahun-tahun yang diketahui
-- ------------------------------------------------------------
CREATE TABLE production_manual (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    field_id    INT UNSIGNED    NOT NULL,
    tahun_ke    SMALLINT        NOT NULL  COMMENT 'Urutan tahun operasi (1, 2, 3, ...)',
    produksi    DECIMAL(14,4)   NOT NULL  COMMENT 'Produksi Mbbl tahun tersebut',
    UNIQUE KEY uq_field_year (field_id, tahun_ke),
    FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE CASCADE
) COMMENT = 'Produksi manual per tahun (input langsung dari data lapangan)';

-- ------------------------------------------------------------
-- 4. PARAMETER DECLINE PRODUKSI
-- ------------------------------------------------------------
CREATE TABLE production_decline (
    id              INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    field_id        INT UNSIGNED    NOT NULL UNIQUE,
    mulai_tahun_ke  SMALLINT        NOT NULL  COMMENT 'Produksi mulai decline pada tahun ke-N',
    laju_persen     DECIMAL(6,3)    NOT NULL  COMMENT 'Laju decline %/tahun',
    FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE CASCADE
) COMMENT = 'Parameter decline produksi eksponensial setelah tahun manual';

-- ------------------------------------------------------------
-- 5. PARAMETER OPEX
-- ------------------------------------------------------------
CREATE TABLE opex_params (
    id              INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    field_id        INT UNSIGNED    NOT NULL UNIQUE,
    base_usd_m      DECIMAL(14,4)   NOT NULL  COMMENT 'Opex base ($M/tahun)',
    base_hingga_thn SMALLINT        NOT NULL  COMMENT 'Opex base berlaku s.d. tahun ke-N',
    eskalasi_persen DECIMAL(6,3)    NOT NULL  DEFAULT 2.500 COMMENT 'Kenaikan Opex %/thn setelah base period',
    FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE CASCADE
) COMMENT = 'Parameter biaya operasi tahunan';

-- ------------------------------------------------------------
-- 6. HASIL NCF PER TAHUN — audit trail kalkulasi
-- ------------------------------------------------------------
CREATE TABLE ncf_results (
    id              INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    field_id        INT UNSIGNED    NOT NULL,
    tahun_ke        SMALLINT        NOT NULL,
    produksi_mbbl   DECIMAL(14,4)   NOT NULL,
    cum_produksi    DECIMAL(14,4)   NOT NULL,
    gross_revenue   DECIMAL(14,4)   NOT NULL  COMMENT 'Produksi × Harga ($M)',
    opex            DECIMAL(14,4)   NOT NULL  COMMENT 'Biaya operasi tahun ini ($M)',
    depresiasi_di   DECIMAL(14,4)   NOT NULL  COMMENT 'Di = (Capital+NonCapital)/TahunHitung ($M)',
    taxable_income  DECIMAL(14,4)   NOT NULL  COMMENT 'Gross Rev − Opex − Di ($M)',
    tax             DECIMAL(14,4)   NOT NULL  COMMENT 'Taxable Income × Tax Rate ($M)',
    ncf             DECIMAL(14,4)   NOT NULL  COMMENT 'Gross Rev − Opex − Tax ($M)',
    cum_ncf         DECIMAL(14,4)   NOT NULL  COMMENT 'Kumulatif NCF s.d. tahun ini ($M)',
    calculated_at   TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_field_tahun (field_id, tahun_ke),
    FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE CASCADE
) COMMENT = 'Hasil kalkulasi NCF after-tax per tahun';

-- ============================================================
-- VIEWS
-- ============================================================

CREATE OR REPLACE VIEW v_investasi_di AS
SELECT
    f.id,
    f.nama,
    f.tahun_hitung,
    i.capital,
    i.non_capital,
    i.total_investasi,
    ROUND(i.total_investasi / f.tahun_hitung, 4) AS di_per_tahun
FROM fields f
JOIN investasi i ON i.field_id = f.id;

CREATE OR REPLACE VIEW v_ncf_summary AS
SELECT
    r.field_id,
    f.nama,
    MAX(r.tahun_ke)       AS tahun_terakhir,
    SUM(r.produksi_mbbl)  AS total_produksi_mbbl,
    SUM(r.gross_revenue)  AS total_gross_revenue,
    SUM(r.opex)           AS total_opex,
    SUM(r.tax)            AS total_tax,
    MAX(r.cum_ncf)        AS total_ncf_kumulatif
FROM ncf_results r
JOIN fields f ON f.id = r.field_id
GROUP BY r.field_id, f.nama;

-- ============================================================
-- SAMPLE DATA — Gunung Bakaran
-- ============================================================

INSERT INTO fields (nama, cadangan_mbbl, harga_minyak, tahun_hitung, tax_rate)
VALUES ('Gunung Bakaran', 4320.00, 32.00, 10, 51.00);

SET @fid = LAST_INSERT_ID();

INSERT INTO investasi (field_id, capital, non_capital)
VALUES (@fid, 13000.00, 8000.00);

INSERT INTO production_manual (field_id, tahun_ke, produksi) VALUES
(@fid, 1, 175.00),
(@fid, 2, 201.00),
(@fid, 3, 217.00),
(@fid, 4, 198.00);

INSERT INTO production_decline (field_id, mulai_tahun_ke, laju_persen)
VALUES (@fid, 5, 3.000);

INSERT INTO opex_params (field_id, base_usd_m, base_hingga_thn, eskalasi_persen)
VALUES (@fid, 180.00, 3, 2.500);
