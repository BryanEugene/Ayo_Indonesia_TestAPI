## 📋 Overview

Test AYO Indonesia

## ✨ Key Features
### 🏆 Core Management
- **Team Management**: CRUD lengkap untuk data tim sepakbola
- **Player Management**: Manajemen pemain dengan relasi ke tim
- **Match Management**: Penjadwalan dan pengelolaan pertandingan
- **Match Results**: Pencatatan hasil pertandingan dengan detail skor
- **Goals Tracking**: Sistem pencatatan gol per pemain dan pertandingan

### 🔧 Advanced Features
- **Soft Delete**: Implementasi soft delete untuk semua entitas utama
- **Comprehensive Reporting**: Laporan statistik tim dan pemain
- **RESTful API**: API endpoints yang mengikuti standar REST
- **Data Validation**: Validasi input menggunakan Laravel Form Requests
- **Database Relationships**: Relasi Eloquent yang terstruktur dengan baik
- **Web Interface**: UI sederhana untuk melihat data database

## 🗄️ Database Schema

### Tables:
- `teams`
- `players`
- `match_games`
- `match_results`
- `goals`
- `users`

### Key Relationships:
- Team → hasMany → Players
- Team → hasMany → MatchGames (as home/away)
- MatchGame → hasOne → MatchResult
- MatchResult → hasMany → Goals
- Player → hasMany → Goals

### Installation Steps
1. **Clone Repository**
   ```bash
   git clone https://github.com/BryanEugene/Ayo_Indonesia_TestAPI.git
   cd TestWeb
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   # Configure your database in .env file
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run Migrations**
   ```bash
   php artisan migrate
   ```

6. **Start Development Server**
   ```bash
   php artisan serve
   ```

## 📊 Current Data Status

- **Teams**: 6 records
- **Players**: 15 records
- **Matches**: 10 records
- **Match Results**: 6 records
- **Goals**: 16 records

## 🔗 API Endpoints

### Base URL
```
http://127.0.0.1:8000
```

### Teams Management
```
GET    /api/teams                     # Get all teams
POST   /api/teams                     # Create new team
GET    /api/teams/{id}                # Get specific team
PUT    /api/teams/{id}                # Update team
DELETE /api/teams/{id}                # Soft delete team
GET    /api/teams/trashed             # Get deleted teams
POST   /api/teams/{id}/restore        # Restore deleted team
DELETE /api/teams/{id}/force-delete   # Permanent delete
```

### Players Management
```
GET    /api/players                   # Get all players
POST   /api/players                   # Create new player
GET    /api/players/{id}              # Get specific player
PUT    /api/players/{id}              # Update player
DELETE /api/players/{id}              # Soft delete player
GET    /api/teams/{team}/players      # Get players by team
```

### Match Management
```
GET    /api/matches                   # Get all matches
POST   /api/matches                   # Create new match
GET    /api/matches/{id}              # Get specific match
PUT    /api/matches/{id}              # Update match
DELETE /api/matches/{id}              # Soft delete match
GET    /api/matches/upcoming          # Get upcoming matches
```

### Match Results & Reporting
```
GET    /api/match-results             # Get all match results
POST   /api/match-results             # Create match result
GET    /api/matches/{id}/result       # Get result by match
GET    /api/players/{id}/goals        # Get goals by player
GET    /api/match-reports/comprehensive # Comprehensive reports
GET    /api/team-statistics           # Team statistics
```

## 👨‍💻 Author

**Bryan Eugene**
- GitHub: [@BryanEugene](https://github.com/BryanEugene)
- Repository: [Ayo_Indonesia_TestAPI](https://github.com/BryanEugene/Ayo_Indonesia_TestAPI)

**Made with ❤️ for Ayo Indonesia Technical Test**
