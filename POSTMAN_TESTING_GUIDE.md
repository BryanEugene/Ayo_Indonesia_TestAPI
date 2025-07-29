# 🧪 Panduan Testing API dengan Postman - Sistem Manajemen Tim Sepakbola XYZ

## 🚀 Setup Awal

### Base URL
```
http://localhost:8000/api
```

### Headers untuk semua request
```
Content-Type: application/json
Accept: application/json
```

---

## 📋 TESTING SEQUENCE YANG DIREKOMENDASIKAN

### 1. Health Check
### 2. Teams Management
### 3. Players Management  
### 4. Match Games Management
### 5. Match Results Management
### 6. Reports & Statistics

---

## 🔍 1. HEALTH CHECK

### Test API Status
**Method:** `GET`  
**URL:** `{{base_url}}/health`  
**Headers:** Default headers

**Expected Response:**
```json
{
    "success": true,
    "message": "API is running",
    "timestamp": "2025-07-30T10:30:00.000000Z"
}
```

---

## 🏆 2. TEAMS MANAGEMENT

### 2.1 Create Teams (Buat beberapa tim untuk testing)

#### Tim 1 - Persija Jakarta
**Method:** `POST`  
**URL:** `{{base_url}}/teams`  
**Body (JSON):**
```json
{
    "nama_tim": "Persija Jakarta",
    "logo_tim": "https://example.com/logos/persija.png",
    "tahun_berdiri": 1928,
    "alamat_markas": "Stadion Gelora Bung Karno, Jakarta Pusat",
    "kota_markas": "Jakarta"
}
```

#### Tim 2 - Persib Bandung
**Method:** `POST`  
**URL:** `{{base_url}}/teams`  
**Body (JSON):**
```json
{
    "nama_tim": "Persib Bandung",
    "logo_tim": "https://example.com/logos/persib.png",
    "tahun_berdiri": 1933,
    "alamat_markas": "Stadion Gelora Bandung Lautan Api, Bandung",
    "kota_markas": "Bandung"
}
```

#### Tim 3 - Arema FC
**Method:** `POST`  
**URL:** `{{base_url}}/teams`  
**Body (JSON):**
```json
{
    "nama_tim": "Arema FC",
    "logo_tim": "https://example.com/logos/arema.png",
    "tahun_berdiri": 1987,
    "alamat_markas": "Stadion Kanjuruhan, Malang",
    "kota_markas": "Malang"
}
```

#### Tim 4 - Bali United
**Method:** `POST`  
**URL:** `{{base_url}}/teams`  
**Body (JSON):**
```json
{
    "nama_tim": "Bali United",
    "logo_tim": "https://example.com/logos/bali.png",
    "tahun_berdiri": 2015,
    "alamat_markas": "Stadion Kapten I Wayan Dipta, Gianyar",
    "kota_markas": "Gianyar"
}
```

#### Tim 5 - PSM Makassar
**Method:** `POST`  
**URL:** `{{base_url}}/teams`  
**Body (JSON):**
```json
{
    "nama_tim": "PSM Makassar",
    "logo_tim": "https://example.com/logos/psm.png",
    "tahun_berdiri": 1915,
    "alamat_markas": "Stadion Gelora BJ Habibie, Makassar",
    "kota_markas": "Makassar"
}
```

### 2.2 Get All Teams
**Method:** `GET`  
**URL:** `{{base_url}}/teams`

### 2.3 Get All Teams with Pagination
**Method:** `GET`  
**URL:** `{{base_url}}/teams?per_page=3&page=1`

### 2.4 Search Teams
**Method:** `GET`  
**URL:** `{{base_url}}/teams?search=jakarta`

### 2.5 Sort Teams by Year
**Method:** `GET`  
**URL:** `{{base_url}}/teams?sort_by=tahun_berdiri&sort_direction=desc`

### 2.6 Get Team Detail
**Method:** `GET`  
**URL:** `{{base_url}}/teams/1`

### 2.7 Get Teams by City
**Method:** `GET`  
**URL:** `{{base_url}}/teams/city/Jakarta`

### 2.8 Update Team
**Method:** `PUT`  
**URL:** `{{base_url}}/teams/1`  
**Body (JSON):**
```json
{
    "nama_tim": "Persija Jakarta FC",
    "logo_tim": "https://example.com/logos/persija-updated.png",
    "tahun_berdiri": 1928,
    "alamat_markas": "Stadion Gelora Bung Karno, Jakarta Pusat",
    "kota_markas": "Jakarta"
}
```

### 2.9 Delete Team (Soft Delete)
**Method:** `DELETE`  
**URL:** `{{base_url}}/teams/5`

### 2.10 Get Deleted Teams
**Method:** `GET`  
**URL:** `{{base_url}}/teams/trashed`

### 2.11 Restore Deleted Team
**Method:** `POST`  
**URL:** `{{base_url}}/teams/5/restore`

### 2.12 Force Delete Team
**Method:** `DELETE`  
**URL:** `{{base_url}}/teams/5/force`

---

## 👥 3. PLAYERS MANAGEMENT

### 3.1 Create Players for Persija Jakarta (Team ID: 1)

#### Player 1 - Goalkeeper
**Method:** `POST`  
**URL:** `{{base_url}}/players`  
**Body (JSON):**
```json
{
    "team_id": 1,
    "nama_pemain": "Andritany Ardhiyasa",
    "tinggi_badan": 183,
    "berat_badan": 75,
    "posisi_pemain": "Penjaga Gawang",
    "nomor_punggung": 1
}
```

#### Player 2 - Defender
**Method:** `POST`  
**URL:** `{{base_url}}/players`  
**Body (JSON):**
```json
{
    "team_id": 1,
    "nama_pemain": "Rizky Dwi Febrianto",
    "tinggi_badan": 178,
    "berat_badan": 72,
    "posisi_pemain": "Bertahan",
    "nomor_punggung": 2
}
```

#### Player 3 - Midfielder
**Method:** `POST`  
**URL:** `{{base_url}}/players`  
**Body (JSON):**
```json
{
    "team_id": 1,
    "nama_pemain": "Rohit Chand",
    "tinggi_badan": 175,
    "berat_badan": 70,
    "posisi_pemain": "Gelandang",
    "nomor_punggung": 8
}
```

#### Player 4 - Forward
**Method:** `POST`  
**URL:** `{{base_url}}/players`  
**Body (JSON):**
```json
{
    "team_id": 1,
    "nama_pemain": "Marko Simic",
    "tinggi_badan": 190,
    "berat_badan": 85,
    "posisi_pemain": "Penyerang",
    "nomor_punggung": 10
}
```

### 3.2 Create Players for Persib Bandung (Team ID: 2)

#### Player 1 - Goalkeeper
**Method:** `POST`  
**URL:** `{{base_url}}/players`  
**Body (JSON):**
```json
{
    "team_id": 2,
    "nama_pemain": "Teja Paku Alam",
    "tinggi_badan": 180,
    "berat_badan": 78,
    "posisi_pemain": "Penjaga Gawang",
    "nomor_punggung": 1
}
```

#### Player 2 - Defender
**Method:** `POST`  
**URL:** `{{base_url}}/players`  
**Body (JSON):**
```json
{
    "team_id": 2,
    "nama_pemain": "Fabiano Beltrame",
    "tinggi_badan": 185,
    "berat_badan": 80,
    "posisi_pemain": "Bertahan",
    "nomor_punggung": 5
}
```

#### Player 3 - Forward
**Method:** `POST`  
**URL:** `{{base_url}}/players`  
**Body (JSON):**
```json
{
    "team_id": 2,
    "nama_pemain": "Wander Luiz",
    "tinggi_badan": 179,
    "berat_badan": 75,
    "posisi_pemain": "Penyerang",
    "nomor_punggung": 11
}
```

### 3.3 Get All Players
**Method:** `GET`  
**URL:** `{{base_url}}/players`

### 3.4 Get Players with Filters
**Method:** `GET`  
**URL:** `{{base_url}}/players?team_id=1&posisi_pemain=Penyerang`

### 3.5 Search Players
**Method:** `GET`  
**URL:** `{{base_url}}/players?search=marko`

### 3.6 Get Player Detail
**Method:** `GET`  
**URL:** `{{base_url}}/players/1`

### 3.7 Get Players by Team
**Method:** `GET`  
**URL:** `{{base_url}}/teams/1/players`

### 3.8 Get Players by Position
**Method:** `GET`  
**URL:** `{{base_url}}/players/position/Penyerang`

### 3.9 Update Player
**Method:** `PUT`  
**URL:** `{{base_url}}/players/4`  
**Body (JSON):**
```json
{
    "team_id": 1,
    "nama_pemain": "Marko Simic",
    "tinggi_badan": 191,
    "berat_badan": 86,
    "posisi_pemain": "Penyerang",
    "nomor_punggung": 10
}
```

### 3.10 Delete Player (Soft Delete)
**Method:** `DELETE`  
**URL:** `{{base_url}}/players/7`

### 3.11 Get Deleted Players
**Method:** `GET`  
**URL:** `{{base_url}}/players/trashed`

### 3.12 Restore Deleted Player
**Method:** `POST`  
**URL:** `{{base_url}}/players/7/restore`

---

## 📅 4. MATCH GAMES MANAGEMENT

### 4.1 Create Match Games

#### Match 1 - Persija vs Persib (Future Match)
**Method:** `POST`  
**URL:** `{{base_url}}/match-games`  
**Body (JSON):**
```json
{
    "tanggal_pertandingan": "2025-08-15",
    "waktu_pertandingan": "15:30",
    "tim_tuan_rumah_id": 1,
    "tim_tamu_id": 2,
    "tempat_pertandingan": "Stadion Gelora Bung Karno",
    "status_pertandingan": "Dijadwalkan",
    "catatan": "Derby Jakarta vs Bandung"
}
```

#### Match 2 - Arema vs Bali United (Future Match)
**Method:** `POST`  
**URL:** `{{base_url}}/match-games`  
**Body (JSON):**
```json
{
    "tanggal_pertandingan": "2025-08-20",
    "waktu_pertandingan": "19:00",
    "tim_tuan_rumah_id": 3,
    "tim_tamu_id": 4,
    "tempat_pertandingan": "Stadion Kanjuruhan",
    "status_pertandingan": "Dijadwalkan",
    "catatan": "Pertandingan malam"
}
```

#### Match 3 - Persib vs Arema (Past Match - for results)
**Method:** `POST`  
**URL:** `{{base_url}}/match-games`  
**Body (JSON):**
```json
{
    "tanggal_pertandingan": "2025-07-25",
    "waktu_pertandingan": "16:00",
    "tim_tuan_rumah_id": 2,
    "tim_tamu_id": 3,
    "tempat_pertandingan": "Stadion Gelora Bandung Lautan Api",
    "status_pertandingan": "Selesai",
    "catatan": "Pertandingan minggu lalu"
}
```

### 4.2 Get All Match Games
**Method:** `GET`  
**URL:** `{{base_url}}/match-games`

### 4.3 Get Match Games with Filters
**Method:** `GET`  
**URL:** `{{base_url}}/match-games?team_id=1&status=Dijadwalkan`

### 4.4 Get Today's Matches
**Method:** `GET`  
**URL:** `{{base_url}}/match-games/today`

### 4.5 Get Upcoming Matches
**Method:** `GET`  
**URL:** `{{base_url}}/match-games?filter=upcoming`

### 4.6 Get Past Matches
**Method:** `GET`  
**URL:** `{{base_url}}/match-games?filter=past`

### 4.7 Get Match by Date
**Method:** `GET`  
**URL:** `{{base_url}}/match-games?tanggal=2025-08-15`

### 4.8 Get Team Schedule
**Method:** `GET`  
**URL:** `{{base_url}}/teams/1/matches`

### 4.9 Get Match Detail
**Method:** `GET`  
**URL:** `{{base_url}}/match-games/1`

### 4.10 Update Match Game
**Method:** `PUT`  
**URL:** `{{base_url}}/match-games/1`  
**Body (JSON):**
```json
{
    "tanggal_pertandingan": "2025-08-16",
    "waktu_pertandingan": "16:00",
    "tim_tuan_rumah_id": 1,
    "tim_tamu_id": 2,
    "tempat_pertandingan": "Stadion Gelora Bung Karno",
    "status_pertandingan": "Dijadwalkan",
    "catatan": "Derby Jakarta vs Bandung - Jadwal diubah"
}
```

### 4.11 Delete Match Game
**Method:** `DELETE`  
**URL:** `{{base_url}}/match-games/2`

---

## 📋 5. MATCH RESULTS MANAGEMENT

### 5.1 Create Match Result with Goals

#### Result for Match 3 (Persib vs Arema)
**Method:** `POST`  
**URL:** `{{base_url}}/match-results`  
**Body (JSON):**
```json
{
    "match_game_id": 3,
    "skor_tim_tuan_rumah": 2,
    "skor_tim_tamu": 1,
    "catatan_hasil": "Pertandingan seru dengan 3 gol menarik",
    "goals": [
        {
            "player_id": 5,
            "team_id": 2,
            "menit_gol": 25,
            "detik_gol": 30,
            "jenis_gol": "Normal",
            "deskripsi_gol": "Tendangan keras dari luar kotak penalti"
        },
        {
            "player_id": 7,
            "team_id": 3,
            "menit_gol": 58,
            "detik_gol": 15,
            "jenis_gol": "Header",
            "deskripsi_gol": "Sundulan dari corner kick"
        },
        {
            "player_id": 6,
            "team_id": 2,
            "menit_gol": 87,
            "detik_gol": 0,
            "jenis_gol": "Penalti",
            "deskripsi_gol": "Penalti akibat pelanggaran di kotak penalti"
        }
    ]
}
```

### 5.2 Create Another Match Result

#### Create another past match first
**Method:** `POST`  
**URL:** `{{base_url}}/match-games`  
**Body (JSON):**
```json
{
    "tanggal_pertandingan": "2025-07-28",
    "waktu_pertandingan": "19:30",
    "tim_tuan_rumah_id": 1,
    "tim_tamu_id": 4,
    "tempat_pertandingan": "Stadion Gelora Bung Karno",
    "status_pertandingan": "Selesai",
    "catatan": "Pertandingan tiga hari lalu"
}
```

#### Then create result for it
**Method:** `POST`  
**URL:** `{{base_url}}/match-results`  
**Body (JSON):**
```json
{
    "match_game_id": 4,
    "skor_tim_tuan_rumah": 1,
    "skor_tim_tamu": 1,
    "catatan_hasil": "Hasil imbang yang adil",
    "goals": [
        {
            "player_id": 4,
            "team_id": 1,
            "menit_gol": 42,
            "detik_gol": 10,
            "jenis_gol": "Free Kick",
            "deskripsi_gol": "Tendangan bebas indah"
        },
        {
            "player_id": 8,
            "team_id": 4,
            "menit_gol": 78,
            "detik_gol": 45,
            "jenis_gol": "Normal",
            "deskripsi_gol": "Serangan balik cepat"
        }
    ]
}
```

### 5.3 Get All Match Results
**Method:** `GET`  
**URL:** `{{base_url}}/match-results`

### 5.4 Get Match Result Detail
**Method:** `GET`  
**URL:** `{{base_url}}/match-results/1`

### 5.5 Get Result by Match Game ID
**Method:** `GET`  
**URL:** `{{base_url}}/match-games/3/result`

### 5.6 Get Match Results by Team
**Method:** `GET`  
**URL:** `{{base_url}}/match-results?team_id=1`

### 5.7 Update Match Result
**Method:** `PUT`  
**URL:** `{{base_url}}/match-results/1`  
**Body (JSON):**
```json
{
    "match_game_id": 3,
    "skor_tim_tuan_rumah": 2,
    "skor_tim_tamu": 1,
    "catatan_hasil": "Pertandingan seru dengan 3 gol menarik - Updated",
    "goals": [
        {
            "player_id": 5,
            "team_id": 2,
            "menit_gol": 25,
            "detik_gol": 30,
            "jenis_gol": "Normal",
            "deskripsi_gol": "Tendangan keras dari luar kotak penalti"
        },
        {
            "player_id": 7,
            "team_id": 3,
            "menit_gol": 58,
            "detik_gol": 15,
            "jenis_gol": "Header",
            "deskripsi_gol": "Sundulan dari corner kick"
        },
        {
            "player_id": 6,
            "team_id": 2,
            "menit_gol": 87,
            "detik_gol": 0,
            "jenis_gol": "Penalti",
            "deskripsi_gol": "Penalti akibat pelanggaran di kotak penalti"
        }
    ]
}
```

### 5.8 Delete Match Result
**Method:** `DELETE`  
**URL:** `{{base_url}}/match-results/2`

---

## 🎯 6. GOALS MANAGEMENT

### 6.1 Get Goals by Player
**Method:** `GET`  
**URL:** `{{base_url}}/players/4/goals`

### 6.2 Get Goals by Player for Specific Match
**Method:** `GET`  
**URL:** `{{base_url}}/players/4/goals?match_game_id=4`

---

## 📊 7. REPORTS & STATISTICS

### 7.1 Get Comprehensive Reports
**Method:** `GET`  
**URL:** `{{base_url}}/match-reports`

### 7.2 Get Report by Match
**Method:** `GET`  
**URL:** `{{base_url}}/match-reports/1`

### 7.3 Get Team Statistics
**Method:** `GET`  
**URL:** `{{base_url}}/teams/1/statistics`

---

## 🧪 8. ERROR TESTING

### 8.1 Test Validation Errors

#### Invalid Team Creation (Missing required fields)
**Method:** `POST`  
**URL:** `{{base_url}}/teams`  
**Body (JSON):**
```json
{
    "nama_tim": "",
    "tahun_berdiri": 2026
}
```

#### Invalid Player Creation (Duplicate jersey number)
**Method:** `POST`  
**URL:** `{{base_url}}/players`  
**Body (JSON):**
```json
{
    "team_id": 1,
    "nama_pemain": "Test Player",
    "tinggi_badan": 175,
    "berat_badan": 70,
    "posisi_pemain": "Gelandang",
    "nomor_punggung": 1
}
```

#### Invalid Match Creation (Same team for home and away)
**Method:** `POST`  
**URL:** `{{base_url}}/match-games`  
**Body (JSON):**
```json
{
    "tanggal_pertandingan": "2025-08-15",
    "waktu_pertandingan": "15:30",
    "tim_tuan_rumah_id": 1,
    "tim_tamu_id": 1,
    "tempat_pertandingan": "Stadium Test",
    "status_pertandingan": "Dijadwalkan"
}
```

### 8.2 Test Not Found Errors

#### Get Non-existent Team
**Method:** `GET`  
**URL:** `{{base_url}}/teams/999`

#### Get Non-existent Player
**Method:** `GET`  
**URL:** `{{base_url}}/players/999`

#### Get Non-existent Match
**Method:** `GET`  
**URL:** `{{base_url}}/match-games/999`

---

## 📝 9. POSTMAN COLLECTION VARIABLES

Untuk mempermudah testing, setup variables berikut di Postman:

### Environment Variables:
```
base_url = http://localhost:8000/api
team_id_1 = 1
team_id_2 = 2
player_id_1 = 1
player_id_2 = 2
match_id_1 = 1
match_result_id_1 = 1
```

### Collection Variables:
```
Content-Type = application/json
Accept = application/json
```

---

## ✅ 10. TESTING CHECKLIST

### Teams API:
- [ ] Create team
- [ ] Get all teams
- [ ] Get team by ID
- [ ] Update team
- [ ] Delete team (soft delete)
- [ ] Get deleted teams
- [ ] Restore team
- [ ] Force delete team
- [ ] Search teams
- [ ] Get teams by city

### Players API:
- [ ] Create player
- [ ] Get all players
- [ ] Get player by ID
- [ ] Update player
- [ ] Delete player (soft delete)
- [ ] Get deleted players
- [ ] Restore player
- [ ] Get players by team
- [ ] Get players by position
- [ ] Search players

### Match Games API:
- [ ] Create match game
- [ ] Get all match games
- [ ] Get match game by ID
- [ ] Update match game
- [ ] Delete match game
- [ ] Get today's matches
- [ ] Get upcoming matches
- [ ] Get past matches
- [ ] Get team schedule
- [ ] Filter matches

### Match Results API:
- [ ] Create match result with goals
- [ ] Get all match results
- [ ] Get match result by ID
- [ ] Update match result
- [ ] Delete match result
- [ ] Get result by match ID
- [ ] Filter results by team

### Goals API:
- [ ] Get goals by player
- [ ] Get goals by player for specific match

### Reports API:
- [ ] Get comprehensive reports
- [ ] Get report by match
- [ ] Get team statistics

### Error Handling:
- [ ] Test validation errors
- [ ] Test not found errors
- [ ] Test unauthorized access

---

## 💡 TIPS UNTUK TESTING

1. **Jalankan dalam urutan:** Mulai dari Health Check, lalu Teams, Players, Matches, Results
2. **Simpan ID:** Catat ID yang didapat dari response untuk digunakan di request berikutnya
3. **Gunakan Variables:** Setup Postman variables untuk mempermudah
4. **Test Error Cases:** Jangan lupa test scenario error
5. **Check Relationships:** Pastikan relasi antar entitas berfungsi dengan benar
6. **Verify Soft Delete:** Test bahwa soft delete bekerja dengan benar
7. **Check Pagination:** Test parameter pagination pada endpoint list
8. **Validate JSON:** Pastikan semua response dalam format JSON yang valid

---

## 🔧 TROUBLESHOOTING

### Common Issues:
1. **Server not running:** Pastikan `php artisan serve` berjalan
2. **Database empty:** Jalankan `php artisan migrate` dan `php artisan db:seed`
3. **404 errors:** Periksa URL dan pastikan menggunakan `/api/` prefix
4. **Validation errors:** Periksa format data dan required fields
5. **Foreign key errors:** Pastikan ID tim/pemain yang direferensikan ada

### Server Commands:
```bash
# Start server
php artisan serve

# Reset database
php artisan migrate:fresh --seed

# Check routes
php artisan route:list
```

Selamat testing! 🚀
