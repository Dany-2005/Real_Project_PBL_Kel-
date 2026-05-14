<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-400">
            <a href="{{ route('dashboard') }}" class="hover:text-[#2d6a4f]">Dashboard</a>
            <span>›</span>
            <a href="{{ route('pembelian.index') }}" class="hover:text-[#2d6a4f]">Pembelian</a>
            <span>›</span>
            <span class="text-gray-600 font-medium">Detail Pembelian</span>
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 max-w-4xl">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Detail Pembelian</h2>
                <p class="text-xs text-gray-400">ID Pembelian: #{{ $pembelian->id_pembelian }}</p>
            </div>
            <a href="{{ route('pembelian.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
                ← Kembali
            </a>
        </div>

        {{-- Info Pembelian --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs text-gray-400 mb-1">Tanggal</p>
                <p class="text-sm font-semibold text-gray-700">{{ \Carbon\Carbon::parse($pembelian->tanggal)->format('d/m/Y') }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs text-gray-400 mb-1">Suplier</p>
                <p class="text-sm font-semibold text-gray-700">{{ $pembelian->suplier->nama_suplier ?? '-' }}</p>
            </div>
            <div class="bg-green-50 rounded-xl p-4">
                <p class="text-xs text-gray-400 mb-1">Total</p>
                <p class="text-sm font-bold text-[#2d6a4f]">Rp {{ number_format($pembelian->total, 0, ',', '.') }}</p>
            </div>
            @if($pembelian->keterangan)
            <div class="bg-gray-50 rounded-xl p-4 sm:col-span-3">
                <p class="text-xs text-gray-400 mb-1">Keterangan</p>
                <p class="text-sm text-gray-700">{{ $pembelian->keterangan }}</p>
            </div>
            @endif
        </div>

        {{-- Detail Produk --}}
        <h3 class="font-semibold text-gray-700 text-sm mb-3">Detail Produk</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-gray-500 text-xs">
                    <th class="pb-3 text-left font-semibold">Produk</th>
                    <th class="pb-3 text-left font-semibold">Jumlah</th>
                    <th class="pb-3 text-left font-semibold">Harga Beli</th>
                    <th class="pb-3 text-left font-semibold">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pembelian->detail as $d)
                <tr class="border-b border-gray-50">
                    <td class="py-3 text-gray-700">{{ $d->produk->nama_produk ?? '-' }}</td>
                    <td class="py-3 text-gray-600">{{ $d->jumlah }}</td>
                    <td class="py-3 text-gray-600">Rp {{ number_format($d->harga_beli, 0, ',', '.') }}</td>
                    <td class="py-3 font-medium text-gray-800">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>

</x-app-layout>