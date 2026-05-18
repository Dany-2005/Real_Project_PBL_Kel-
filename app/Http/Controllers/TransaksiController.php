<?php

    namespace App\Http\Controllers;

    use App\Models\Transaksi;
    use App\Models\DetailTransaksi;
    use App\Models\Produk;
    use App\Models\Pelanggan;
    use App\Models\Kategori;
    use App\Models\Diskon;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;
    use Carbon\Carbon;

    class TransaksiController extends Controller
    {
        public function index()
        {
    $transaksi = Transaksi::with(['detail.produk', 'pelanggan'])
        ->where('id_user', Auth::id())  // ← tambah ini
        ->latest('id_transaksi')
        ->get();
    return view('transaksi.index', compact('transaksi'));
        }

    public function create()
    {
        $today = now()->toDateString();

        $produk = Produk::with(['kategori', 'diskon' => function($query) use ($today) {
            $query->where('is_aktif', true)
                ->where('mulai_tgl', '<=', $today)
                ->where('selesai_tgl', '>=', $today);
        }])->get();

        $kategori = Kategori::all();

        $pelanggan = Pelanggan::all();    
        return view('transaksi.create', compact('produk', 'kategori', 'pelanggan'));}

    public function store(Request $request)
    {
        // Decode keranjang jika dikirim dalam bentuk string JSON
        if (is_string($request->keranjang)) {
            $request->merge([
                'keranjang' => json_decode($request->keranjang, true)
            ]);
        }

        $request->validate([
            'keranjang'             => 'required|array|min:1',
            'keranjang.*.id_produk' => 'required|exists:produk,id_produk',
            'keranjang.*.jumlah'    => 'required|integer|min:1',
            'keranjang.*.tipe'      => 'required|in:eceran,grosir',
            'metode_pembayaran'     => 'required|in:tunai,transfer,kartu',
            'bayar'                 => 'required|integer|min:0',
        ]);

        $subtotalKeseluruhan = 0;
        $totalDiskon         = 0;
        $items               = [];

        // Ambil data pelanggan untuk diskon (Sinkron dengan JS)
        $pelanggan = null;
        if ($request->id_pelanggan) {
            $pelanggan = Pelanggan::find($request->id_pelanggan);
        }

        foreach ($request->keranjang as $item) {
            $produk = Produk::findOrFail($item['id_produk']);
            $jumlah = $item['jumlah'];
            $tipe   = $item['tipe'];

            // 1. Tentukan harga dasar
            $harga = ($tipe === 'grosir') 
                ? ($produk->harga_grosir ?? $produk->harga_satuan) 
                : $produk->harga_satuan;

            // 2. Validasi Stok
            if ($tipe === 'grosir') {
                if ($jumlah > $produk->stok_gudang) {
                    return back()->withErrors(['stok' => "Stok gudang {$produk->nama_produk} tidak mencukupi."])->withInput();
                }
            } else {
                if ($jumlah > $produk->stok_toko) {
                    return back()->withErrors(['stok' => "Stok toko {$produk->nama_produk} tidak mencukupi."])->withInput();
                }
            }

            // 3. Logic Diskon (SINKRON DENGAN FRONTEND)
            // Menghitung diskon berdasarkan persentase diskon pelanggan
        // 3. Logic Diskon — baca dari tabel diskon (lewat diskon_produk)
            $nominalDiskonPerItem = 0;
            $today = now()->toDateString();

            $diskonAktif = $produk->diskon()
                ->where('is_aktif', true)
                ->where('mulai_tgl', '<=', $today)
                ->where('selesai_tgl', '>=', $today)
                ->where(function($q) use ($tipe) {
                    $q->where('lokasi_berlaku', 'semua')
                    ->orWhere('lokasi_berlaku', $tipe === 'grosir' ? 'gudang' : 'toko');
                })
                ->first();

                if ($diskonAktif) {
                    $minimalBeli = $tipe === 'grosir'
                        ? ($diskonAktif->minimal_beli_grosir ?? 0)
                        : ($diskonAktif->minimal_beli ?? 0);

                    if ($jumlah >= $minimalBeli) {
                        $nominalDiskonPerItem = round($harga * ($diskonAktif->besar_diskon / 100));
                    }
                }

            $subtotalItem         = ($harga * $jumlah) - $nominalDiskonPerItem;
            $subtotalKeseluruhan += ($harga * $jumlah);
            $totalDiskon         += $nominalDiskonPerItem;

            $items[] = [
                'id_produk'      => $produk->id_produk,
                'produk_obj'     => $produk,
                'jumlah'         => $jumlah,
                'tipe'           => $tipe,
                'harga'          => $harga,
                'nominal_diskon' => $nominalDiskonPerItem,
                'subtotal'       => $subtotalItem,
            ];
        }

        $total     = $subtotalKeseluruhan - $totalDiskon;
        $bayar     = (int) $request->bayar;
        $kembalian = $bayar - $total;

        if ($bayar < $total) {
            return back()->withErrors(['bayar' => 'Uang bayar tidak cukup.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $transaksi = Transaksi::create([
                'tanggal'           => now(),
                'id_user'           => Auth::id(),
                'id_pelanggan'      => $request->id_pelanggan ?: null,
                'subtotal'          => $subtotalKeseluruhan,
                'total_diskon'      => $totalDiskon,
                'total'             => $total,
                'bayar'             => $bayar,
                'kembalian'         => $kembalian,
                'metode_pembayaran' => $request->metode_pembayaran,
                'catatan'           => $request->catatan,
            ]);

            foreach ($items as $item) {
                DetailTransaksi::create([
                    'id_transaksi'   => $transaksi->id_transaksi,
                    'id_produk'      => $item['id_produk'],
                    'tipe'           => $item['tipe'],
                    'jumlah'         => $item['jumlah'],
                    'harga'          => $item['harga'],
                    'nominal_diskon' => $item['nominal_diskon'],
                    'subtotal'       => $item['subtotal'],
                ]);

                $p = $item['produk_obj'];
                if ($item['tipe'] === 'grosir') {
                    $p->stok_gudang -= $item['jumlah'];
                } else {
                    $p->stok_toko -= $item['jumlah'];
                }
                $p->save();
            }

            DB::commit();
            return redirect()->route('transaksi.show', $transaksi->id_transaksi)
                            ->with('success', 'Transaksi Berhasil!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

        public function show($id)
        {
            $transaksi = Transaksi::with(['detail.produk', 'pelanggan', 'kasir'])->findOrFail($id);
            return view('transaksi.show', compact('transaksi'));
        }

        public function destroy($id)
        {
            DB::beginTransaction();
            try {
                $transaksi = Transaksi::with('detail')->findOrFail($id);

                foreach ($transaksi->detail as $detail) {
                    $produk = Produk::find($detail->id_produk);
                    if ($produk) {
                        if ($detail->tipe === 'grosir') {
                            $produk->stok_gudang += $detail->jumlah;
                        } else {
                            $produk->stok_toko += $detail->jumlah;
                        }
                        $produk->save();
                    }
                }

                $transaksi->delete();
                DB::commit();
                return redirect()->route('transaksi.index')->with('success', 'Transaksi berhasil dihapus.');
            } catch (\Exception $e) {
                DB::rollback();
                return back()->with('error', 'Gagal menghapus transaksi.');
            }
        }
    }