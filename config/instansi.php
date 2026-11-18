<?php

return [
    'nama'    => env('INSTANSI_NAMA', 'DIREKTORAT JENDERAL PENGAWASAN SUMBER DAYA KELAUTAN DAN PERIKANAN'),
    'unit'    => env('INSTANSI_UNIT', 'STASIUN PENGAWASAN SUMBER DAYA KELAUTAN DAN PERIKANAN CILACAP'),
    'alamat'  => env('INSTANSI_ALAMAT', 'Jl. Mutiara No. 1 Lomanis, Kec. Cilacap Tengah Kab. Cilacap'),
    'kontak'  => env('INSTANSI_KONTAK', 'Telp. (0282) 5569112 | Email: info@instansi.go.id'),
    'logo'    => 'images/logo_PSDKP.png', // relatif terhadap folder public/, boleh dikosongkan
    'kota'    => env('INSTANSI_KOTA', 'Cilacap'),

    // Blok pengesahan di bagian bawah PDF
    'penandatangan_jabatan' => env('INSTANSI_TTD_JABATAN', 'Penyelenggara Rapat'),
    'penandatangan_nama'    => env('INSTANSI_TTD_NAMA', ''),
];