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
                <h2 class="text-xl font-bold text-gray-800">Data Transaksi</h2>
                <p class="text-xs text-gray-400">Riwayat semua transaksi</p>
            </div>
            <a href="{{ route('transaksi.create') }}">
                <button class="bg-[#2d6a4f] hover:bg-[#1b4332] text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
                    + Transaksi Baru
                </button>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-gray-500 text-xs">
                    <th class="pb-3 text-left font-semibold">ID</th>
                    <th class="pb-3 text-left font-semibold">Tanggal</th>
                    <th class="pb-3 text-left font-semibold">Pelanggan</th>
                    <th class="pb-3 text-left font-semibold">Kasir</th>
                    <th class="pb-3 text-left font-semibold">Metode</th>
                    <th class="pb-3 text-left font-semibold">Total</th>
                    <th class="pb-3 text-left font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksi as $t)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                    <td class="py-3 text-gray-400">#{{ $t->id_transaksi }}</td>
                    <td class="py-3 text-gray-600">{{ \Carbon\Carbon::parse($t->tanggal)->format('d/m/Y H:i') }}</td>
                    <td class="py-3 text-gray-700">{{ $t->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                    <td class="py-3 text-gray-700">{{ $t->kasir->name ?? '-' }}</td>
                    <td class="py-3">
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-lg text-xs capitalize">
                            {{ $t->metode_pembayaran }}
                        </span>
                    </td>
                    <td class="py-3 font-semibold text-gray-800">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                    <td class="py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('transaksi.show', $t->id_transaksi) }}"
                               class="w-8 h-8 bg-blue-100 hover:bg-blue-200 rounded-lg flex items-center justify-center transition">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <form action="{{ route('transaksi.destroy', $t->id_transaksi) }}" method="POST"
                                  onsubmit="return confirm('Yakin hapus transaksi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-8 h-8 bg-red-100 hover:bg-red-200 rounded-lg flex items-center justify-center transition">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-10 text-center text-gray-400">Belum ada data transaksi</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>