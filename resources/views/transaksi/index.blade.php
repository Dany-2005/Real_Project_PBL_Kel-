<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-400">
            <a href="{{ route('dashboard') }}" class="hover:text-[#2d6a4f]">Dashboard</a>
            <span>›</span>
            <span class="text-gray-600 font-medium">Transaksi</span>
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Riwayat Transaksi</h2>
                <p class="text-xs text-gray-400">Pantau semua aktivitas penjualan di sini</p>
            </div>
            <a href="{{ route('transaksi.create') }}" class="bg-[#2d6a4f] hover:bg-[#1b4332] text-white text-sm font-semibold px-4 py-2 rounded-xl transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Transaksi Baru
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-500 text-xs">
                        <th class="pb-3 text-left font-semibold uppercase tracking-wider">ID</th>
                        <th class="pb-3 text-left font-semibold uppercase tracking-wider">Tanggal</th>
                        <th class="pb-3 text-left font-semibold uppercase tracking-wider">Pelanggan</th>
                        <th class="pb-3 text-left font-semibold uppercase tracking-wider">Kasir</th>
                        <th class="pb-3 text-left font-semibold uppercase tracking-wider">Metode</th>
                        <th class="pb-3 text-left font-semibold uppercase tracking-wider">Total Akhir</th>
                        <th class="pb-3 text-center font-semibold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($transaksi as $t)
                    <tr class="hover:bg-gray-50 transition group">
                        <td class="py-4 text-gray-400 font-mono">#{{ $t->id_transaksi }}</td>
                        <td class="py-4 text-gray-600">
                            {{ \Carbon\Carbon::parse($t->tanggal)->format('d/m/y') }}
                            <span class="text-[10px] block text-gray-400">{{ \Carbon\Carbon::parse($t->tanggal)->format('H:i') }} WIB</span>
                        </td>
                        <td class="py-4 font-medium text-gray-700">{{ $t->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                        <td class="py-4 text-gray-600 text-xs">{{ $t->kasir->name ?? '-' }}</td>
                        <td class="py-4">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-md text-[10px] font-bold uppercase">
                                {{ $t->metode_pembayaran }}
                            </span>
                        </td>
                        <td class="py-4 font-bold text-gray-800">
                            Rp {{ number_format($t->total, 0, ',', '.') }}
                            @if($t->total_diskon > 0)
                                <span class="block text-[10px] text-orange-500 font-normal">Hemat Rp {{ number_format($t->total_diskon, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('transaksi.show', $t->id_transaksi) }}"
                                   title="Lihat Detail"
                                   class="w-9 h-9 bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white rounded-xl flex items-center justify-center transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('transaksi.destroy', $t->id_transaksi) }}" method="POST"
                                      onsubmit="return confirm('Yakin hapus transaksi ini? Stok akan otomatis dikembalikan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="Hapus Transaksi"
                                            class="w-9 h-9 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white rounded-xl flex items-center justify-center transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="bg-gray-50 p-4 rounded-full mb-3">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-gray-400">Belum ada data transaksi tersimpan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    
</x-app-layout>