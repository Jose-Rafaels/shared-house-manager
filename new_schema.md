## **Skema Baru**

### 1. `members` — Daftar anggota rumah
| Kolom      | Tipe         | Keterangan               |
|------------|--------------|--------------------------|
| id         | INT (PK)     | ID anggota               |
| name       | VARCHAR(100) | Nama panggilan           |
| phone      | VARCHAR(20)  | Nomor HP (opsional)      |
| joined_at  | TIMESTAMP    | Tanggal bergabung        |

### 2. `categories` — Kategori pengeluaran
| Kolom | Tipe        | Keterangan              |
|-------|-------------|--------------------------|
| id    | INT (PK)    | ID kategori             |
| name  | VARCHAR(50) | Nama (Makanan, Listrik) |

### 3. `expenses` — Catatan pengeluaran
| Kolom        | Tipe           | Keterangan                            |
|--------------|----------------|----------------------------------------|
| id           | INT (PK)       | ID pengeluaran                        |
| payer_id     | INT (FK)       | **Yang membayar** (FK ke `members`)   |
| category_id  | INT (FK) NULL  | Kategori                |
| amount       | DECIMAL(10,2)  | Total nominal                          |
| description  | TEXT           | Keterangan                            |
| expense_date | DATE           | Tanggal transaksi                     |
| created_at   | TIMESTAMP      | Waktu input                           |

### 4. `expense_splits` — Beban yang ditanggung per anggota
| Kolom       | Tipe          | Keterangan                            |
|-------------|---------------|----------------------------------------|
| id          | INT (PK)      | ID split                              |
| expense_id  | INT (FK)      | Referensi `expenses.id`               |
| member_id   | INT (FK)      | Anggota yang menanggung bagian        |
| amount_owed | DECIMAL(10,2) | Jumlah yang harus dia ganti           |

**UNIQUE(expense_id, member_id)**  
Total `amount_owed` dalam satu `expense_id` harus = `expenses.amount`.  
Biasanya **payer tidak dimasukkan** ke splits, karena dia sudah mengeluarkan uang.

### 5. `settlements` — Pelunasan antar anggota
| Kolom           | Tipe          | Keterangan                            |
|-----------------|---------------|----------------------------------------|
| id              | INT (PK)      | ID pelunasan                          |
| from_member_id  | INT (FK)      | Yang membayar utang                   |
| to_member_id    | INT (FK)      | Yang menerima pembayaran              |
| amount          | DECIMAL(10,2) | Jumlah yang dibayarkan                |
| settlement_date | DATE          | Tanggal pembayaran                    |
| note            | TEXT          | Catatan (opsional)                    |
| created_at      | TIMESTAMP     | Waktu input                           |
