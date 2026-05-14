<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-400">
            <a href="{{ route('dashboard') }}" class="hover:text-[#2d6a4f]">Dashboard</a>
            <span>›</span>
            <span class="text-gray-600 font-medium">Transaksi Baru</span>
        </div>
    </x-slot>

    <div class="flex gap-4 h-full">

        {{-- KIRI: Daftar Produk --}}
        <div class="flex-1 bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col">

            {{-- Filter & Search --}}
            <div class="flex gap-2 mb-4">
                <select id="filterKategori"
                        class="text-sm border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                    <option value="">Semua Kategori</option>
                    @foreach($kategori as $k)
                        <option value="{{ $k->id_kategori }}">{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>
                <div class="flex-1 flex items-center gap-2 border border-gray-200 rounded-xl px-3 py-2">
                    <input type="text" id="searchProduk" placeholder="Cari produk..."
                           class="flex-1 text-sm outline-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                    </svg>
                </div>
            </div>

            {{-- Tabel Produk --}}
            <div class="overflow-y-auto flex-1">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-gray-500 text-xs">
                            <th class="pb-2 text-left font-semibold">Nama Produk</th>
                            <th class="pb-2 text-left font-semibold">Harga Eceran</th>
                            <th class="pb-2 text-left font-semibold">Harga Grosir</th>
                            <th class="pb-2 text-left font-semibold">Stok Toko</th>
                            <th class="pb-2 text-left font-semibold">Stok Gudang</th>
                            <th class="pb-2 text-left font-semibold">Diskon</th>
                            <th class="pb-2 text-left font-semibold"></th>
                        </tr>
                    </thead>
                    <tbody id="tableProduk">
                        @foreach($produk as $p)
                        @php
                            $today = now()->toDateString();
                            $diskonAktif = $p->diskon()
                                ->where('is_aktif', true)
                                ->where('mulai_tgl', '<=', $today)
                                ->where('selesai_tgl', '>=', $today)
                                ->first();
                        @endphp
                        <tr class="border-b border-gray-50 hover:bg-green-50 transition cursor-pointer produk-row"
                            data-nama="{{ strtolower($p->nama_produk) }}"
                            data-kategori="{{ $p->id_kategori }}"
                            data-id="{{ $p->id_produk }}"
                            data-nama-display="{{ $p->nama_produk }}"
                            data-harga-satuan="{{ $p->harga_satuan }}"
                            data-harga-grosir="{{ $p->harga_grosir ?? $p->harga_satuan }}"
                            data-minimal-grosir="{{ $p->minimal_grosir ?? 0 }}"
                            data-stok-toko="{{ $p->stok_toko }}"
                            data-stok-gudang="{{ $p->stok_gudang }}"
                            data-diskon="{{ $diskonAktif ? $diskonAktif->besar_diskon : 0 }}"
                            data-minimal-diskon="{{ $diskonAktif ? $diskonAktif->minimal_beli : 0 }}">
                            <td class="py-3 font-medium text-gray-800">{{ $p->nama_produk }}</td>
                            <td class="py-3 text-gray-600">Rp {{ number_format($p->harga_satuan, 0, ',', '.') }}</td>
                            <td class="py-3 text-gray-600">Rp {{ number_format($p->harga_grosir ?? 0, 0, ',', '.') }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-lg text-xs font-semibold
                                    {{ $p->stok_toko <= 10 ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' }}">
                                    {{ $p->stok_toko }} pcs
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-lg text-xs font-semibold
                                    {{ $p->stok_gudang <= 10 ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-700' }}">
                                    {{ $p->stok_gudang }} pcs
                                </span>
                            </td>
                            <td class="py-3">
                                @if($diskonAktif)
                                    <span class="px-2 py-0.5 bg-orange-100 text-orange-600 rounded-lg text-xs font-semibold">
                                        {{ $diskonAktif->besar_diskon }}% (min {{ $diskonAktif->minimal_beli }})
                                    </span>
                                @else
                                    <span class="text-gray-300 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3">
                                <button type="button"
                                        onclick="tambahKeKeranjang(this)"
                                        class="bg-[#2d6a4f] hover:bg-[#1b4332] text-white text-xs px-3 py-1.5 rounded-lg transition">
                                    + Tambah
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- KANAN: Keranjang --}}
        <div class="w-80 bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800">Keranjang</h3>
                <div id="trashZone"
                     onclick="clearKeranjang()"
                     ondragover="event.preventDefault(); this.classList.add('scale-125', 'text-red-600')"
                     ondragleave="this.classList.remove('scale-125', 'text-red-600')"
                     ondrop="dropKeHapus(event)"
                     class="text-red-400 hover:text-red-600 transition-all duration-200 cursor-pointer p-1 rounded-lg hover:bg-red-50"
                     title="Klik untuk kosongkan semua / Drag produk ke sini untuk hapus">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition-transform duration-200" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/>
                    </svg>
                </div>
            </div>

            {{-- List keranjang --}}
            <div id="keranjangList" class="flex-1 overflow-y-auto space-y-3 mb-4">
                <p id="keranjangKosong" class="text-center text-gray-400 text-sm py-8">Belum ada produk</p>
            </div>

            {{-- Summary --}}
            <div class="border-t border-gray-100 pt-3 space-y-1">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Subtotal</span>
                    <span id="summarySubtotal">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm text-orange-500">
                    <span>Diskon</span>
                    <span id="summaryDiskon">- Rp 0</span>
                </div>
                <div class="flex justify-between text-base font-bold text-gray-800 pt-1 border-t border-gray-100">
                    <span>Total</span>
                    <span id="summaryTotal">Rp 0</span>
                </div>
            </div>

            <button onclick="checkout()"
                    class="mt-4 w-full bg-[#2d6a4f] hover:bg-[#1b4332] text-white font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Checkout
            </button>
        </div>
    </div>

    {{-- MODAL CHECKOUT --}}
    <div id="modalCheckout" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-5">Checkout Transaksi</h2>

            <form id="formCheckout" method="POST" action="{{ route('transaksi.store') }}">
                @csrf
                <input type="hidden" id="keranjangInput" name="keranjang">

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pelanggan</label>
                        <select name="id_pelanggan"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                            <option value="">-- Umum --</option>
                            @foreach($pelanggan as $p)
                                <option value="{{ $p->id_pelanggan }}">{{ $p->nama_pelanggan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kasir</label>
                        <input type="text" value="{{ auth()->user()->name }}" disabled
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50 text-gray-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                    <div class="flex gap-4">
                        @foreach(['tunai' => 'Tunai', 'transfer' => 'Transfer', 'kartu' => 'Kartu Debit/Kredit'] as $val => $label)
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="radio" name="metode_pembayaran" value="{{ $val }}"
                                       {{ $val === 'tunai' ? 'checked' : '' }}
                                       class="text-[#2d6a4f]">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bayar (Rp)</label>
                        <input type="number" name="bayar" id="inputBayar" min="0"
                               oninput="hitungKembalian()"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kembalian</label>
                        <input type="text" id="displayKembalian" disabled
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-gray-50 text-[#2d6a4f] font-bold">
                    </div>
                </div>

                {{-- Ringkasan --}}
                <div class="bg-gray-50 rounded-xl p-4 mb-4 space-y-1 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span id="modalSubtotal">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-orange-500">
                        <span>Diskon</span>
                        <span id="modalDiskon">- Rp 0</span>
                    </div>
                    <div class="flex justify-between font-bold text-gray-800 text-base border-t border-gray-200 pt-2 mt-2">
                        <span>Total</span>
                        <span id="modalTotal">Rp 0</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                    <textarea name="catatan" rows="2" placeholder="Tambahkan catatan..."
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#2d6a4f]"></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" id="btnSimpan"
                            class="flex-1 bg-[#2d6a4f] hover:bg-[#1b4332] text-white font-semibold py-3 rounded-xl transition">
                        Simpan Transaksi
                    </button>
                    <button type="button" onclick="tutupModal()"
                            class="px-6 py-3 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const logoUrl = "{{ asset('images/logotoko.png') }}";
        let keranjang = [];

        document.getElementById('searchProduk').addEventListener('input', filterProduk);
        document.getElementById('filterKategori').addEventListener('change', filterProduk);

        function filterProduk() {
            const keyword = document.getElementById('searchProduk').value.toLowerCase();
            const kategori = document.getElementById('filterKategori').value;
            document.querySelectorAll('.produk-row').forEach(row => {
                const matchNama = row.dataset.nama.includes(keyword);
                const matchKat  = kategori === '' || row.dataset.kategori === kategori;
                row.style.display = (matchNama && matchKat) ? '' : 'none';
            });
        }

        function tambahKeKeranjang(btn) {
            const row = btn.closest('tr');
            const id          = row.dataset.id;
            const nama        = row.dataset.namaDisplay;
            const hargaSatuan = parseInt(row.dataset.hargaSatuan);
            const hargaGrosir = parseInt(row.dataset.hargaGrosir);
            const minGrosir   = parseInt(row.dataset.minimalGrosir) || 0;
            const stokToko    = parseInt(row.dataset.stokToko);
            const stokGudang  = parseInt(row.dataset.stokGudang);
            const diskon      = parseInt(row.dataset.diskon) || 0;
            const minDiskon   = parseInt(row.dataset.minimalDiskon) || 0;

            const existing = keranjang.find(k => k.id_produk === id);
            if (existing) {
                existing.jumlah += 1;
            } else {
                keranjang.push({
                    id_produk: id, nama, hargaSatuan, hargaGrosir,
                    minGrosir, stokToko, stokGudang,
                    diskon, minDiskon,
                    jumlah: 1, tipe: 'eceran'
                });
            }
            renderKeranjang();
        }

        function renderKeranjang() {
            const list = document.getElementById('keranjangList');
            const kosong = document.getElementById('keranjangKosong');

            Array.from(list.children).forEach(child => {
                if (child.id !== 'keranjangKosong') child.remove();
            });

            if (keranjang.length === 0) {
                kosong.style.display = '';
                updateSummary();
                return;
            }

            kosong.style.display = 'none';

            keranjang.forEach((item, index) => {
                const harga = item.tipe === 'grosir' ? item.hargaGrosir : item.hargaSatuan;
                const nominalDiskon = (item.diskon > 0 && item.jumlah >= item.minDiskon)
                    ? Math.round((harga * item.jumlah) * item.diskon / 100)
                    : 0;
                const subtotal = (harga * item.jumlah) - nominalDiskon;
                const maxStok = item.tipe === 'grosir' ? item.stokGudang : item.stokToko;
                const stokKurang = item.jumlah > maxStok;

                const div = document.createElement('div');
                div.className = `${stokKurang ? 'bg-red-50 border-red-200' : 'bg-gray-100 border-gray-200'} border rounded-xl p-3 text-sm cursor-grab active:cursor-grabbing`;
                div.setAttribute('draggable', 'true');
                div.setAttribute('data-index', index);

                div.addEventListener('dragstart', (e) => {
                    e.dataTransfer.setData('text/plain', String(index));
                    setTimeout(() => div.classList.add('opacity-40'), 0);
                    document.getElementById('trashZone').classList.add('ring-2', 'ring-red-400', 'bg-red-50', 'text-red-600');
                });
                div.addEventListener('dragend', () => {
                    div.classList.remove('opacity-40');
                    document.getElementById('trashZone').classList.remove('ring-2', 'ring-red-400', 'bg-red-50', 'text-red-600', 'scale-125');
                });

                div.innerHTML = `
                    <div class="relative overflow-hidden">
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <img src="${logoUrl}" alt="" class="w-12 h-12 object-contain opacity-[0.08] select-none">
                        </div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-medium text-gray-800 text-xs">${item.nama}</span>
                                <button onclick="hapusItem(${index})" class="text-red-400 hover:text-red-600 text-xs">✕</button>
                            </div>
                            <div class="flex items-center gap-2 mb-2">
                                <select onchange="ubahTipe(${index}, this.value)"
                                        class="text-xs border border-gray-200 rounded-lg px-2 py-1 focus:outline-none">
                                    <option value="eceran" ${item.tipe === 'eceran' ? 'selected' : ''}>Eceran</option>
                                    <option value="grosir" ${item.tipe === 'grosir' ? 'selected' : ''}>Grosir</option>
                                </select>
                                <div class="flex items-center gap-1 ml-auto">
                                    <button onclick="ubahJumlah(${index}, -1)"
                                            class="w-6 h-6 bg-gray-200 hover:bg-gray-300 rounded text-xs font-bold">-</button>
                                    <input type="number" min="1"
                                           value="${item.jumlah}"
                                           onchange="setJumlah(${index}, this.value)"
                                           class="w-12 text-center text-xs font-semibold border ${stokKurang ? 'border-red-400' : 'border-gray-200'} rounded focus:outline-none focus:ring-1 focus:ring-[#2d6a4f]">
                                    <button onclick="ubahJumlah(${index}, 1)"
                                            class="w-6 h-6 bg-gray-200 hover:bg-gray-300 rounded text-xs font-bold">+</button>
                                </div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Rp ${formatRp(harga)} × ${item.jumlah}</span>
                                ${nominalDiskon > 0 ? `<span class="text-orange-500">-Rp ${formatRp(nominalDiskon)}</span>` : ''}
                            </div>
                            <div class="flex justify-between text-xs font-semibold text-gray-800 mt-1">
                                <span>Subtotal</span>
                                <span>Rp ${formatRp(subtotal)}</span>
                            </div>
                            ${stokKurang ? `<div class="text-xs text-red-500 mt-1">⚠ Stok ${item.tipe} hanya ${maxStok} pcs</div>` : ''}
                        </div>
                    </div>
                `;
                list.appendChild(div);
            });

            updateSummary();
        }

        function setJumlah(index, val) {
            const jumlah = parseInt(val) || 1;
            keranjang[index].jumlah = jumlah < 1 ? 1 : jumlah;
            renderKeranjang();
        }

        function validasiStok() {
            for (let i = 0; i < keranjang.length; i++) {
                const item = keranjang[i];
                const maxStok = item.tipe === 'grosir' ? item.stokGudang : item.stokToko;
                if (item.jumlah > maxStok) {
                    alert(`Stok ${item.tipe} "${item.nama}" tidak cukup!\nStok tersedia: ${maxStok} pcs`);
                    return false;
                }
            }
            return true;
        }

        function dropKeHapus(event) {
            event.preventDefault();
            const index = parseInt(event.dataTransfer.getData('text/plain'));
            const trash = document.getElementById('trashZone');
            trash.classList.remove('scale-125', 'text-red-600', 'ring-2', 'ring-red-400', 'bg-red-50');
            if (!isNaN(index)) {
                keranjang.splice(index, 1);
                renderKeranjang();
            }
        }

        function ubahTipe(index, tipe) {
            keranjang[index].tipe = tipe;
            renderKeranjang();
        }

        function ubahJumlah(index, delta) {
            keranjang[index].jumlah += delta;
            if (keranjang[index].jumlah <= 0) {
                keranjang.splice(index, 1);
            }
            renderKeranjang();
        }

        function hapusItem(index) {
            keranjang.splice(index, 1);
            renderKeranjang();
        }

        function clearKeranjang() {
            keranjang = [];
            renderKeranjang();
        }

        function updateSummary() {
            let subtotal = 0, totalDiskon = 0;
            keranjang.forEach(item => {
                const harga = item.tipe === 'grosir' ? item.hargaGrosir : item.hargaSatuan;
                const nom = (item.diskon > 0 && item.jumlah >= item.minDiskon)
                    ? Math.round((harga * item.jumlah) * item.diskon / 100) : 0;
                subtotal += harga * item.jumlah;
                totalDiskon += nom;
            });
            const total = subtotal - totalDiskon;
            document.getElementById('summarySubtotal').textContent = 'Rp ' + formatRp(subtotal);
            document.getElementById('summaryDiskon').textContent = '- Rp ' + formatRp(totalDiskon);
            document.getElementById('summaryTotal').textContent = 'Rp ' + formatRp(total);
        }

        function checkout() {
            if (keranjang.length === 0) {
                alert('Keranjang masih kosong!');
                return;
            }

            // Validasi stok sebelum buka modal
            if (!validasiStok()) return;

            let subtotal = 0, totalDiskon = 0;
            keranjang.forEach(item => {
                const harga = item.tipe === 'grosir' ? item.hargaGrosir : item.hargaSatuan;
                const nom = (item.diskon > 0 && item.jumlah >= item.minDiskon)
                    ? Math.round((harga * item.jumlah) * item.diskon / 100) : 0;
                subtotal += harga * item.jumlah;
                totalDiskon += nom;
            });
            const total = subtotal - totalDiskon;

            document.getElementById('modalSubtotal').textContent = 'Rp ' + formatRp(subtotal);
            document.getElementById('modalDiskon').textContent = '- Rp ' + formatRp(totalDiskon);
            document.getElementById('modalTotal').textContent = 'Rp ' + formatRp(total);
            document.getElementById('inputBayar').value = total;
            document.getElementById('displayKembalian').value = 'Rp 0';

            const keranjangFormatted = keranjang.map(item => ({
                id_produk: item.id_produk,
                jumlah: item.jumlah,
                tipe: item.tipe,
            }));
            document.getElementById('keranjangInput').value = JSON.stringify(keranjangFormatted);
            document.getElementById('modalCheckout').classList.remove('hidden');
        }

        function tutupModal() {
            document.getElementById('modalCheckout').classList.add('hidden');
        }

        function hitungKembalian() {
            const totalText = document.getElementById('modalTotal').textContent.replace(/[^0-9]/g, '');
            const total = parseInt(totalText) || 0;
            const bayar = parseInt(document.getElementById('inputBayar').value) || 0;
            const kembalian = bayar - total;

            const displayKembalian = document.getElementById('displayKembalian');
            const btnSimpan = document.getElementById('btnSimpan');

            if (bayar < total) {
                displayKembalian.value = 'Kurang Rp ' + formatRp(total - bayar);
                displayKembalian.classList.add('text-red-500');
                displayKembalian.classList.remove('text-[#2d6a4f]');
                btnSimpan.disabled = true;
                btnSimpan.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                displayKembalian.value = 'Rp ' + formatRp(kembalian);
                displayKembalian.classList.remove('text-red-500');
                displayKembalian.classList.add('text-[#2d6a4f]');
                btnSimpan.disabled = false;
                btnSimpan.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        function formatRp(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    </script>
</x-app-layout>