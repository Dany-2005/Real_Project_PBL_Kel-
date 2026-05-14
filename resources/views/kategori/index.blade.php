<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-400">
            <a href="{{ route('dashboard') }}" class="hover:text-[#2d6a4f]">Dashboard</a>
            <span>›</span>
            <span class="text-gray-600 font-medium">Kategori</span>
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 max-w-2xl">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Data Kategori</h2>
                <p class="text-xs text-gray-400">Kelola kategori produk toko</p>
            </div>
            <a href="{{ route('kategori.create') }}">
                <button class="bg-[#2d6a4f] hover:bg-[#1b4332] text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
                    + Tambah
                </button>
            </a>
        </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-500 text-xs">
                        <th class="pb-3 text-left font-semibold">ID</th>
                        <th class="pb-3 text-left font-semibold">Nama Kategori</th>
                        <th class="pb-3 text-left font-semibold">Aksi</th>
                    </tr>
                </thead>
            <tbody>
        @forelse($kategoris as $k)
            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                <td class="py-3 text-gray-500">{{ $loop->iteration }}</td>
                <td class="py-3 font-medium text-gray-800">{{ $k->nama_kategori }}</td>
                <td class="py-3">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('kategori.edit', $k->id_kategori) }}" class="text-blue-500">Edit</a>
                        
                        <form action="{{ route('kategori.destroy', $k->id_kategori) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center py-4 text-gray-500">Belum ada kategori yang ditambahkan.</td>
            </tr>
        @endforelse
    </tbody>
            </table>        

    </div>

</x-app-layout>