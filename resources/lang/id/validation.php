<?php

return [

  /*
    |--------------------------------------------------------------------------
    | Baris Bahasa untuk Validasi
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut ini berisi pesan error default yang digunakan oleh
    | kelas validator. Beberapa aturan di sini memiliki beberapa versi,
    | seperti aturan ukuran. Silakan sesuaikan setiap pesan di sini.
    |
    */

  'accepted'        => ':attribute harus diterima.',
  'active_url'      => ':attribute bukan URL yang valid.',
  'after'           => ':attribute harus berupa tanggal setelah :date.',
  'after_or_equal'  => ':attribute harus berupa tanggal setelah atau sama dengan :date.',
  'alpha'           => ':attribute hanya boleh berisi huruf.',
  'alpha_dash'      => ':attribute hanya boleh berisi huruf, angka, setrip, dan garis bawah.',
  'alpha_num'       => ':attribute hanya boleh berisi huruf dan angka.',
  'array'           => ':attribute harus berupa sebuah array.',
  'before'          => ':attribute harus berupa tanggal sebelum :date.',
  'before_or_equal' => ':attribute harus berupa tanggal sebelum atau sama dengan :date.',
  'between'         => [
    'numeric' => ':attribute harus antara :min dan :max.',
    'file'    => ':attribute harus antara :min dan :max kilobytes.',
    'string'  => ':attribute harus antara :min dan :max karakter.',
    'array'   => ':attribute harus memiliki :min sampai :max item.',
  ],
  'boolean'         => ':attribute harus berupa true atau false.',
  'confirmed'       => 'Konfirmasi :attribute tidak cocok.',
  'date'            => ':attribute bukan tanggal yang valid.',
  'date_equals'     => ':attribute harus berupa tanggal yang sama dengan :date.',
  'date_format'     => ':attribute tidak cocok dengan format :format.',
  'different'       => ':attribute dan :other harus berbeda.',
  'digits'          => ':attribute harus berupa :digits digit.',
  'digits_between'  => ':attribute harus antara :min dan :max digit.',
  'dimensions'      => ':attribute memiliki dimensi gambar yang tidak valid.',
  'distinct'        => ':attribute memiliki nilai yang duplikat.',
  'email'           => ':attribute harus berupa alamat surel yang valid.',
  'ends_with'       => ':attribute harus diakhiri salah satu dari berikut: :values',
  'exists'          => ':attribute tidak terpilih.', // <-- PENTING UNTUK PELANGGAN
  'file'            => ':attribute harus berupa sebuah berkas.',
  'filled'          => ':attribute harus memiliki nilai.',
  'gt'              => [
    'numeric' => ':attribute harus lebih besar dari :value.',
    'file'    => ':attribute harus lebih besar dari :value kilobytes.',
    'string'  => ':attribute harus lebih besar dari :value karakter.',
    'array'   => ':attribute harus memiliki lebih dari :value item.',
  ],
  'gte'             => [
    'numeric' => ':attribute harus lebih besar dari atau sama dengan :value.',
    'file'    => ':attribute harus lebih besar dari atau sama dengan :value kilobytes.',
    'string'  => ':attribute harus lebih besar dari atau sama dengan :value karakter.',
    'array'   => ':attribute harus memiliki :value item atau lebih.',
  ],
  'image'           => ':attribute harus berupa gambar.',
  'in'              => ':attribute yang dipilih tidak valid.',
  'in_array'        => ':attribute tidak ada di dalam :other.',
  'integer'         => ':attribute harus berupa bilangan bulat.',
  'ip'              => ':attribute harus berupa alamat IP yang valid.',
  'ipv4'            => ':attribute harus berupa alamat IPv4 yang valid.',
  'ipv6'            => ':attribute harus berupa alamat IPv6 yang valid.',
  'json'            => ':attribute harus berupa string JSON yang valid.',
  'lt'              => [
    'numeric' => ':attribute harus kurang dari :value.',
    'file'    => ':attribute harus kurang dari :value kilobytes.',
    'string'  => ':attribute harus kurang dari :value karakter.',
    'array'   => ':attribute harus memiliki kurang dari :value item.',
  ],
  'lte'             => [
    'numeric' => ':attribute harus kurang dari atau sama dengan :value.',
    'file'    => ':attribute harus kurang dari atau sama dengan :value kilobytes.',
    'string'  => ':attribute harus kurang dari atau sama dengan :value karakter.',
    'array'   => ':attribute harus tidak boleh memiliki lebih dari :value item.',
  ],
  'max'             => [
    'numeric' => ':attribute tidak boleh lebih dari :max.',
    'file'    => ':attribute tidak boleh lebih dari :max kilobytes.',
    'string'  => ':attribute tidak boleh lebih dari :max karakter.',
    'array'   => ':attribute tidak boleh memiliki lebih dari :max item.',
  ],
  'mimes'           => ':attribute harus berupa berkas berjenis: :values.',
  'mimetypes'       => ':attribute harus berupa berkas berjenis: :values.',
  'min'             => [
    'numeric' => ':attribute harus minimal :min.', // <-- PENTING UNTUK QTY
    'file'    => ':attribute harus minimal :min kilobytes.',
    'string'  => ':attribute harus minimal :min karakter.',
    'array'   => ':attribute harus memiliki minimal :min item.',
  ],
  'not_in'          => ':attribute yang dipilih tidak valid.',
  'not_regex'       => 'Format :attribute tidak valid.',
  'numeric'         => ':attribute harus berupa angka.', // <-- PENTING UNTUK HARGA
  'password'        => 'Kata sandi salah.',
  'present'         => ':attribute harus ada.',
  'regex'           => 'Format :attribute tidak valid.',
  'required'        => ':attribute wajib diisi.', // <-- INI YANG PALING PENTING
  'required_if'     => ':attribute wajib diisi bila :other adalah :value.',
  'required_unless' => ':attribute wajib diisi kecuali :other memiliki nilai :values.',
  'required_with'   => ':attribute wajib diisi bila terdapat :values.',
  'required_with_all' => ':attribute wajib diisi bila terdapat :values.',
  'required_without' => ':attribute wajib diisi bila tidak terdapat :values.',
  'required_without_all' => ':attribute wajib diisi bila tidak terdapat ada :values.',
  'same'            => ':attribute dan :other harus sama.',
  'size'            => [
    'numeric' => ':attribute harus berukuran :size.',
    'file'    => ':attribute harus berukuran :size kilobytes.',
    'string'  => ':attribute harus berukuran :size karakter.',
    'array'   => ':attribute harus mengandung :size item.',
  ],
  'starts_with'     => ':attribute harus diawali salah satu dari berikut: :values',
  'string'          => ':attribute harus berupa string.',
  'timezone'        => ':attribute harus berupa zona waktu yang valid.',
  'unique'          => ':attribute sudah ada sebelumnya.',
  'uploaded'        => ':attribute gagal diunggah.',
  'url'             => 'Format :attribute tidak valid.',
  'uuid'            => ':attribute harus merupakan UUID yang valid.',

  /*
    |--------------------------------------------------------------------------
    | Baris Bahasa untuk Validasi Kustom
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menentukan pesan validasi kustom untuk atribut dengan
    | menggunakan konvensi "attribute.rule" untuk memberi nama baris.
    | Ini membuatnya cepat untuk menentukan baris bahasa kustom tertentu
    | untuk aturan atribut yang diberikan.
    |
    */

  'custom' => [
    'nama_pelanggan' => [
      'required' => 'Nama pelanggan wajib dipilih.',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Atribut Validasi Kustom
    |--------------------------------------------------------------------------
    |
    | Baris bahasa berikut digunakan untuk menukar placeholder atribut
    | dengan sesuatu yang lebih mudah dibaca seperti "Alamat Surel"
    | daripada "email". Ini membantu kami membuat pesan kami lebih ekspresif.
    |
    */

  'attributes' => [
    'no_bukti' => 'No Bukti',
    'tgl_bukti' => 'Tanggal Bukti',
    'nama_pelanggan' => 'Pelanggan',
    'barang.*.nama_barang' => 'Nama Barang',
    'barang.*.qty' => 'Kuantitas',
    'barang.*.harga' => 'Harga',
  ],

];
