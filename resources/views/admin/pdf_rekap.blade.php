<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi - {{ $rapat->judul_rapat }}</title>
    <style>
        @page { margin: 36px 40px 56px 40px; }
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #111; }
        
        /* Kop instansi */
        .kop { width: 100%; border-collapse: collapse; border-bottom: 3px double #000; margin-bottom: 14px; }
        .kop td { padding-bottom: 8px; vertical-align: middle; }
        .kop .logo { width: 80px; }
        .kop .logo img { width: 70px; }
        .kop .teks { text-align: center; }
        .kop .nama1 { font-size: 15px; font-weight: bold; text-transform: uppercase; color: #0000ff; }
        .kop .nama2 { font-size: 16.5px; font-weight: medium; text-transform: uppercase; color: #0000ff; }
        .kop .unit { font-size: 15px; font-weight: bold; color: #0000ff; }
        .kop .alamat { font-size: 10px; color: #333; }
        .kop .kontak1, .kop .kontak2 { font-size: 10px; color: #333; }

        h1.judul { text-align: center; font-size: 13px; margin: 0 0 12px; text-transform: uppercase; text-decoration: underline; }

        /* Informasi rapat */
        .info { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .info td { padding: 2px 0; vertical-align: top; }
        .info td.label { width: 110px; }
        .info td.sep { width: 12px; }

        /* Tabel daftar hadir */
        table.hadir { width: 100%; border-collapse: collapse; }
        table.hadir th, table.hadir td { border: 1px solid #000; padding: 4px 5px; vertical-align: middle; }
        table.hadir th { background: #e9ecef; text-align: center; font-size: 9.5px; }
        table.hadir thead { display: table-header-group; }   /* ulangi header tiap halaman */
        table.hadir tr { page-break-inside: avoid; }
        .c { text-align: center; }
        .ttd img { width: 80px; }
        .foto img { height: 55px; }

        .cetak { margin-top: 14px; font-size: 8px; color: #555; }
    </style>
</head>
<body>

    {{-- Kop instansi --}}
        <table class="kop">
            <tr>
                <td class="logo">
                    @if ($logo) <img src="{{ $logo }}" alt="Logo"> @endif
                </td>
                <td class="teks">
                    <div class="nama1">{!! str_replace('|', '<br>', e($instansi['nama1'])) !!}</div>
                    <div class="nama2">{!! str_replace('|', '<br>', e($instansi['nama2'])) !!}</div>
                    <div class="unit">{!! str_replace('|', '<br>', e($instansi['unit'])) !!}</div>
                    <div class="alamat">{{ $instansi['alamat'] }}</div>
                    <div class="kontak1">{{ $instansi['kontak1'] }}</div>
                    <div class="kontak2">{{ $instansi['kontak2'] }}</div>
                </td>
                <td class="logo"></td>
            </tr>
        </table>

    <h1 class="judul">Daftar Hadir Rapat</h1>

    {{-- Informasi rapat --}}
    <table class="info">
        <tr><td class="label">Judul Rapat</td><td class="sep">:</td><td><strong>{{ $rapat->judul_rapat }}</strong></td></tr>
        <tr><td class="label">Hari / Tanggal</td><td class="sep">:</td><td>{{ $rapat->tanggal_rapat->translatedFormat('l, d F Y') }}</td></tr>
        <tr><td class="label">Waktu</td><td class="sep">:</td><td>{{ substr($rapat->jam_mulai, 0, 5) }} – {{ substr($rapat->jam_selesai, 0, 5) }} WIB</td></tr>
        <tr><td class="label">Jumlah Hadir</td><td class="sep">:</td><td>{{ $absensis->count() }} orang</td></tr>
    </table>

    {{-- Daftar hadir --}}
    @php
        $lebar = $rapat->minta_foto
            ? ['no' => 5, 'nama' => 22, 'jabatan' => 16, 'unit' => 16, 'waktu' => 9, 'ttd' => 16, 'foto' => 16]
            : ['no' => 6, 'nama' => 28, 'jabatan' => 20, 'unit' => 20, 'waktu' => 10, 'ttd' => 16];
    @endphp

    <table class="hadir">
        <thead>
            <tr>
                <th style="width:{{ $lebar['no'] }}%">No</th>
                <th style="width:{{ $lebar['nama'] }}%">Nama Lengkap</th>
                <th style="width:{{ $lebar['jabatan'] }}%">Jabatan</th>
                <th style="width:{{ $lebar['unit'] }}%">Unit Kerja</th>
                <th style="width:{{ $lebar['waktu'] }}%">Waktu Hadir</th>
                <th style="width:{{ $lebar['ttd'] }}%">Tanda Tangan</th>
                @if ($rapat->minta_foto)
                    <th style="width:{{ $lebar['foto'] }}%">Foto</th>
                @endif
            </tr>
        </thead>
        <tbody>
        @forelse ($absensis as $a)
            <tr>
                <td class="c">{{ $loop->iteration }}</td>
                <td>{{ $a->nama_lengkap }}</td>
                <td>{{ $a->jabatan }}</td>
                <td>{{ $a->unit_kerja }}</td>
                <td class="c">{{ $a->waktu_hadir->format('d/m/Y') }}<br>{{ $a->waktu_hadir->format('H:i') }}</td>
                <td class="c ttd">
                    @if ($a->tanda_tangan)
                        <img src="{{ $a->tanda_tangan }}" alt="">
                    @endif
                </td>
                @if ($rapat->minta_foto)
                    <td class="c foto">
                        @if ($a->foto)
                            <img src="{{ $a->foto }}" alt="">
                        @else
                            -
                        @endif
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ $rapat->minta_foto ? 7 : 6 }}" class="c" style="padding: 18px;">
                    Belum ada peserta yang hadir.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="cetak">
        Dicetak oleh {{ $dicetakOleh }} pada {{ $dicetakPada->translatedFormat('d F Y, H:i') }} WIB
    </div>
</body>
</html>