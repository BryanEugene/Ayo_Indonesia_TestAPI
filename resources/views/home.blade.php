<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Tim Sepakbola XYZ</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Database</h1>
    
    <h2>Daftar Tim</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Tim</th>
                <th>Logo</th>
                <th>Tahun Berdiri</th>
                <th>Alamat Markas</th>
                <th>Kota Markas</th>
                <th>Jumlah Pemain</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teams as $team)
            <tr>
                <td>{{ $team->id }}</td>
                <td>{{ $team->nama_tim }}</td>
                <td>{{ $team->logo_tim ?? 'N/A' }}</td>
                <td>{{ $team->tahun_berdiri }}</td>
                <td>{{ $team->alamat_markas }}</td>
                <td>{{ $team->kota_markas }}</td>
                <td>{{ $team->players->count() }} pemain</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <h2>Daftar Pemain per Tim</h2>
    @foreach($teams as $team)
    <h3>Pemain {{ $team->nama_tim }}</h3>
    @if($team->players->count() > 0)
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Pemain</th>
                <th>Tinggi (cm)</th>
                <th>Berat (kg)</th>
                <th>Posisi</th>
                <th>No. Punggung</th>
            </tr>
        </thead>
        <tbody>
            @foreach($team->players as $player)
            <tr>
                <td>{{ $player->id }}</td>
                <td>{{ $player->nama_pemain }}</td>
                <td>{{ $player->tinggi_badan }}</td>
                <td>{{ $player->berat_badan }}</td>
                <td>{{ $player->posisi_pemain }}</td>
                <td>{{ $player->nomor_punggung }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>Belum ada pemain di tim ini.</p>
    @endif
    @endforeach

    <h2>Jadwal Pertandingan</h2>
    @php
        $matches = \App\Models\MatchGame::with(['timTuanRumah', 'timTamu'])->orderBy('tanggal_pertandingan')->orderBy('waktu_pertandingan')->get();
    @endphp
    @if($matches->count() > 0)
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Tim Tuan Rumah</th>
                <th>Tim Tamu</th>
                <th>Tempat</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($matches as $match)
            <tr>
                <td>{{ $match->id }}</td>
                <td>{{ $match->tanggal_pertandingan->format('d/m/Y') }}</td>
                <td>{{ $match->waktu_pertandingan->format('H:i') }}</td>
                <td>{{ $match->timTuanRumah->nama_tim }}</td>
                <td>{{ $match->timTamu->nama_tim }}</td>
                <td>{{ $match->tempat_pertandingan ?? 'TBD' }}</td>
                <td>{{ $match->status_pertandingan }}</td>
                <td>{{ $match->catatan ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>Belum ada jadwal pertandingan</p>
    @endif

    <h2>Hasil Pertandingan</h2>
    @php
        $results = \App\Models\MatchResult::with(['matchGame.timTuanRumah', 'matchGame.timTamu', 'goals.player', 'goals.team'])->get();
    @endphp
    @if($results->count() > 0)
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pertandingan</th>
                <th>Tanggal</th>
                <th>Skor</th>
                <th>Status Akhir</th>
                <th>Total Gol</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
            <tr>
                <td>{{ $result->id }}</td>
                <td>{{ $result->matchGame->timTuanRumah->nama_tim }} vs {{ $result->matchGame->timTamu->nama_tim }}</td>
                <td>{{ $result->matchGame->tanggal_pertandingan->format('d/m/Y') }}</td>
                <td>{{ $result->skor_tim_tuan_rumah }} - {{ $result->skor_tim_tamu }}</td>
                <td>
                    @if($result->skor_tim_tuan_rumah > $result->skor_tim_tamu)
                        Tim Home Menang
                    @elseif($result->skor_tim_tamu > $result->skor_tim_tuan_rumah)
                        Tim Away Menang
                    @else
                        Draw
                    @endif
                </td>
                <td>{{ $result->goals->count() }} gol</td>
                <td>{{ $result->catatan_hasil ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>Belum ada hasil pertandingan</p>
    @endif

    <h2>Detail Gol</h2>
    @php
        $goals = \App\Models\Goal::with(['player', 'team', 'matchResult.matchGame.timTuanRumah', 'matchResult.matchGame.timTamu'])->orderBy('created_at', 'desc')->get();
    @endphp
    @if($goals->count() > 0)
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pertandingan</th>
                <th>Pencetak Gol</th>
                <th>Tim</th>
                <th>Waktu Gol</th>
                <th>Jenis Gol</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($goals as $goal)
            <tr>
                <td>{{ $goal->id }}</td>
                <td>{{ $goal->matchResult->matchGame->timTuanRumah->nama_tim }} vs {{ $goal->matchResult->matchGame->timTamu->nama_tim }}</td>
                <td>{{ $goal->player->nama_pemain }}</td>
                <td>{{ $goal->team->nama_tim }}</td>
                <td>{{ $goal->menit_gol }}'{{ $goal->detik_gol > 0 ? $goal->detik_gol.'"' : '' }}</td>
                <td>{{ $goal->jenis_gol }}</td>
                <td>{{ $goal->deskripsi_gol ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>Belum ada data gol</p>
    @endif

    <hr>
    <h2>Ringkasan Database</h2>
    <ul>
        <li>Total Tim: {{ $teams->count() }}</li>
        <li>Total Pemain: {{ $teams->sum(function($team) { return $team->players->count(); }) }}</li>
        <li>Total Pertandingan: {{ \App\Models\MatchGame::count() }}</li>
        <li>Total Hasil: {{ \App\Models\MatchResult::count() }}</li>
        <li>Total Gol: {{ \App\Models\Goal::count() }}</li>
        <li>Rata-rata Pemain per Tim: {{ $teams->count() > 0 ? round($teams->sum(function($team) { return $team->players->count(); }) / $teams->count(), 1) : 0 }}</li>
    </ul>

</body>
</html>