<h1>Data Supplier</h1>

<a href="/suplier/create">+ Tambah Supplier</a>

@if(session('success'))
<p style="color:green">{{ session('success') }}</p>
@endif

<table border="1" cellpadding="10">
<tr>
    <th>No</th>
    <th>Nama</th>
    <th>No HP</th>
    <th>Alamat</th>
    <th>Aksi</th>
</tr>

@foreach($suplier as $s)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $s->nama_suplier }}</td>
    <td>{{ $s->no_hp }}</td>
    <td>{{ $s->alamat }}</td>
    <td>
        <a href="/suplier/{{ $s->id_suplier }}/edit">Edit</a>

        <form action="/suplier/{{ $s->id_suplier }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit">Hapus</button>
        </form>
    </td>
</tr>
@endforeach
</table>